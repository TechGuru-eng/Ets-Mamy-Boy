<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $env = parse_ini_file(__DIR__ . '/../.env');
            
            $host = $env['DB_HOST'] ?? 'localhost';
            $dbName = $env['DB_NAME'] ?? 'mamy_boy';
            $username = $env['DB_USER'] ?? 'root';
            $password = $env['DB_PASS'] ?? '';

            try {
                $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$instance = new PDO($dsn, $username, $password, $options);
                self::ensureSchemaCompatibility();
            } catch (PDOException $e) {
                // Do not expose detailed DB errors to the user
                error_log("Connection failed: " . $e->getMessage());
                die("Database connection failed. Please check the error logs.");
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
