<?php
/**
 * ETS MAMY BOY - Automated Web Database Installer & Migration Script
 * Access via browser: https://your-domain.com/install.php
 */

define('BASE_PATH', dirname(__DIR__));

// Load database config
require_once BASE_PATH . '/config/Database.php';

$message = '';
$status = 'pending';
$logs = [];

if (isset($_POST['run_migration'])) {
    try {
        $db = \Config\Database::getInstance()->getConnection();
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // 1. Create Tables
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('ADMIN', 'STAFF') NOT NULL DEFAULT 'STAFF',
                status ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS agents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                phone_number VARCHAR(50),
                notes TEXT,
                status ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS ristourne_rates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rate_per_crate DECIMAL(10, 2) NOT NULL,
                product_type ENUM('STANDARD', 'TOP') NOT NULL DEFAULT 'STANDARD',
                start_date DATE NOT NULL,
                end_date DATE,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS purchases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_number VARCHAR(50) NOT NULL UNIQUE,
                purchase_date DATE NOT NULL,
                crates INT NOT NULL DEFAULT 0,
                top_units INT NOT NULL DEFAULT 0,
                product_type ENUM('STANDARD', 'TOP') NOT NULL DEFAULT 'STANDARD',
                amount DECIMAL(15, 2) NOT NULL,
                agent_id INT NOT NULL,
                receipt_number VARCHAR(100),
                receipt_path VARCHAR(255),
                ristourne_rate_id INT NOT NULL,
                notes TEXT,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                is_reversed BOOLEAN NOT NULL DEFAULT FALSE,
                FOREIGN KEY (agent_id) REFERENCES agents(id),
                FOREIGN KEY (ristourne_rate_id) REFERENCES ristourne_rates(id),
                FOREIGN KEY (created_by) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS crate_returns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_number VARCHAR(50) NOT NULL UNIQUE,
                return_date DATE NOT NULL,
                crates INT NOT NULL,
                agent_id INT NULL,
                receipt_path VARCHAR(255),
                notes TEXT,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                is_reversed BOOLEAN NOT NULL DEFAULT FALSE,
                FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS goals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                goal_type ENUM('PURCHASE_CRATES', 'RETURN_CRATES', 'RISTOURNE_COLLECTION', 'BALANCE_REDUCTION') NOT NULL,
                target_value DECIMAL(15, 2) NOT NULL,
                start_value DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                status ENUM('ACTIVE', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'ACTIVE',
                notes TEXT,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }
        $logs[] = "✓ Database tables initialized successfully.";

        // 2. Seed Default Users
        $adminHash = password_hash('password', PASSWORD_BCRYPT);
        $staffHash = password_hash('password', PASSWORD_BCRYPT);

        $db->exec("INSERT IGNORE INTO users (id, name, username, password_hash, role, status) VALUES 
            (1, 'Administrator', 'admin', '$adminHash', 'ADMIN', 'ACTIVE'),
            (2, 'Staff User', 'staff', '$staffHash', 'STAFF', 'ACTIVE')");
        $logs[] = "✓ Default Admin ('admin') & Staff ('staff') accounts created.";

        // 3. Seed Ristourne Rates
        $db->exec("INSERT IGNORE INTO ristourne_rates (id, rate_per_crate, product_type, start_date, is_active) VALUES 
            (1, 314.00, 'STANDARD', '2026-09-01', 1),
            (2, 100.00, 'TOP', '2026-09-01', 1)");
        $logs[] = "✓ Ristourne rates seeded (Standard Glass: 314 FCFA, TOP Plastics: 100 FCFA).";

        // 4. Seed Agents
        $agents = [
            [1, 'Boris', '670 000 001', 'Primary partner & supplier depot'],
            [2, 'ABDEL', '670 000 002', 'Delivery agent'],
            [3, 'ROLAND', '670 000 003', 'Delivery agent'],
            [4, 'STEPHANE', '670 000 004', 'Delivery agent'],
            [5, "PA'A BLESS", '670 000 005', 'Delivery agent'],
            [6, 'ECHOSUR', '670 000 006', 'Delivery agent'],
            [7, 'Left Brasseries', '670 000 000', 'Personal depot drop-offs']
        ];

        $agentStmt = $db->prepare("INSERT IGNORE INTO agents (id, full_name, phone_number, notes, status) VALUES (?, ?, ?, ?, 'ACTIVE')");
        foreach ($agents as $ag) {
            $agentStmt->execute($ag);
        }
        $logs[] = "✓ Delivery agents seeded (ABDEL, ROLAND, STEPHANE, PA'A BLESS, ECHOSUR, Boris, Left Brasseries).";

        // 5. Seed Boris Opening Balance Transaction (321 borrowed crates on 2026-09-19)
        $db->exec("INSERT IGNORE INTO crate_returns (id, transaction_number, return_date, crates, agent_id, notes, created_by) VALUES 
            (1, 'CRT-20260919-0001', '2026-09-19', 321, 1, 'Opening balance - ETS Boris borrowed 321 empty crates to purchase drinks at brewery', 1)");
        $logs[] = "✓ Opening partnership crate balance logged (Boris owes 321 empty crates).";

        // 6. Seed Default Settings
        $db->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
            ('company_name', 'ETS MAMY BOY'),
            ('supplier_name', 'BORIS ET CRISTAL SARL'),
            ('location', 'Djoum, Cameroon'),
            ('currency', 'FCFA')");

        $status = 'success';
        $message = "Database migration and seeding completed successfully!";

    } catch (\Exception $e) {
        $status = 'danger';
        $message = "Migration Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETS MAMY BOY - Database Installer & Migration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f5f7fb; font-family: system-ui, -apple-system, sans-serif; }
        .install-card { max-width: 620px; margin: 60px auto; border: 0; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .brand-header { background: #102033; color: white; border-radius: 12px 12px 0 0; padding: 25px; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <div class="card install-card overflow-hidden">
        <div class="brand-header">
            <i class="bi bi-box-seam fs-1 text-primary"></i>
            <h3 class="fw-bold mb-1">ETS MAMY BOY</h3>
            <p class="text-white-50 mb-0">Database Setup & Automatic Migration Script</p>
        </div>
        <div class="card-body p-4">
            
            <?php if ($status === 'success'): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <div><strong>Success!</strong> <?= htmlspecialchars($message) ?></div>
                </div>

                <div class="bg-light p-3 rounded mb-4 border">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-terminal me-2 text-primary"></i>Execution Logs:</h6>
                    <ul class="list-unstyled mb-0 small text-success fw-bold">
                        <?php foreach ($logs as $log): ?>
                            <li class="mb-1"><?= htmlspecialchars($log) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="alert alert-info border-0 mb-4">
                    <strong>Default Admin Credentials:</strong><br>
                    • Username: <code>admin</code><br>
                    • Password: <code>password</code>
                </div>

                <a href="/login" class="btn btn-primary btn-lg w-100 fw-bold">
                    <i class="bi bi-box-arrow-in-right"></i> Go to Login Screen
                </a>

            <?php elseif ($status === 'danger'): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <div><?= htmlspecialchars($message) ?></div>
                </div>

                <form method="POST">
                    <button type="submit" name="run_migration" class="btn btn-primary btn-lg w-100 fw-bold">
                        <i class="bi bi-arrow-clockwise"></i> Retry Migration
                    </button>
                </form>

            <?php else: ?>
                <p class="text-muted">
                    Click the button below to automatically create database tables, set up official ristourne rates (314 FCFA / 100 FCFA), seed delivery agents, and log opening crate balances.
                </p>

                <form method="POST">
                    <button type="submit" name="run_migration" class="btn btn-primary btn-lg w-100 fw-bold py-3">
                        <i class="bi bi-database-gear me-2"></i> Run Database Migration & Setup
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
