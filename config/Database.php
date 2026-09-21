<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $credsFile = __DIR__ . '/db_credentials.php';
            $envPath = __DIR__ . '/../.env';
            
            if (file_exists($credsFile)) {
                $creds = require $credsFile;
                $host = $creds['host'] ?? 'localhost';
                $dbName = $creds['db_name'] ?? '';
                $username = $creds['username'] ?? '';
                $password = $creds['password'] ?? '';
            } else {
                $env = (file_exists($envPath)) ? @parse_ini_file($envPath) : [];
                $host = $env['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
                $dbName = $env['DB_NAME'] ?? getenv('DB_NAME') ?: 'mamy_boy';
                $username = $env['DB_USER'] ?? getenv('DB_USER') ?: 'root';
                $password = $env['DB_PASS'] ?? getenv('DB_PASS') ?: '';
            }

            try {
                $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                self::$instance = new PDO($dsn, $username, $password, $options);
                self::$instance->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$instance->exec("SET collation_connection = utf8mb4_unicode_ci");
                self::ensureSchemaCompatibility();
            } catch (PDOException $e) {
                error_log("Connection failed: " . $e->getMessage());
                // If running install script, throw Exception to allow installer UI to catch & handle credentials
                if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'install.php') {
                    throw new PDOException($e->getMessage(), (int)$e->getCode());
                }
                die("<div style='font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border:1px solid #fecaca;background:#fff5f5;border-radius:8px;'>
                    <h3 style='color:#dc2626;margin-top:0;'>Database Connection Failed</h3>
                    <p>Could not connect to MySQL database <strong>" . htmlspecialchars($dbName) . "</strong> on <strong>" . htmlspecialchars($host) . "</strong>.</p>
                    <p><a href='/install.php' style='display:inline-block;padding:10px 18px;background:#1f6feb;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>Click Here to Run 1-Click Installer (/install.php)</a></p>
                </div>");
            }
        }

        return self::$instance;
    }

    private static function ensureSchemaCompatibility(): void {
        if (self::$instance === null) {
            return;
        }

        self::addColumnIfMissing('purchases', 'top_units', "INT NOT NULL DEFAULT 0 AFTER crates");
        self::addColumnIfMissing('purchases', 'product_type', "ENUM('STANDARD', 'TOP') NOT NULL DEFAULT 'STANDARD' AFTER top_units");
        self::addColumnIfMissing('ristourne_rates', 'product_type', "ENUM('STANDARD', 'TOP') NOT NULL DEFAULT 'STANDARD' AFTER rate_per_crate");
        self::replacePurchaseQuantityConstraint();
    }

    private static function addColumnIfMissing(string $table, string $column, string $definition): void {
        $quotedColumn = self::$instance->quote($column);
        $stmt = self::$instance->query("SHOW COLUMNS FROM `$table` LIKE $quotedColumn");

        if (!$stmt->fetch()) {
            self::$instance->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    private static function replacePurchaseQuantityConstraint(): void {
        $stmt = self::$instance->query(
            "SELECT cc.CONSTRAINT_NAME, cc.CHECK_CLAUSE
             FROM information_schema.CHECK_CONSTRAINTS cc
             JOIN information_schema.TABLE_CONSTRAINTS tc
               ON cc.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
              AND cc.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
             WHERE cc.CONSTRAINT_SCHEMA = DATABASE()
               AND tc.TABLE_NAME = 'purchases'"
        );

        $hasCombinedConstraint = false;
        foreach ($stmt->fetchAll() as $constraint) {
            $name = $constraint['CONSTRAINT_NAME'];
            $clause = strtolower(str_replace(['`', ' ', '(', ')'], '', $constraint['CHECK_CLAUSE']));

            if (str_contains($clause, 'crates>0') && !str_contains($clause, 'top_units')) {
                self::$instance->exec("ALTER TABLE purchases DROP CHECK `$name`");
                continue;
            }

            if (str_contains($clause, 'crates>0') && str_contains($clause, 'top_units>0')) {
                $hasCombinedConstraint = true;
            }
        }

        if (!$hasCombinedConstraint) {
            self::$instance->exec("ALTER TABLE purchases ADD CONSTRAINT purchases_quantity_chk CHECK (crates > 0 OR top_units > 0)");
        }
    }
}
