CREATE DATABASE IF NOT EXISTS mamy_boy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mamy_boy;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'STAFF') NOT NULL DEFAULT 'STAFF',
    status ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Agents Table
CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(50),
    notes TEXT,
    status ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Ristourne Rates Table
CREATE TABLE IF NOT EXISTS ristourne_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rate_per_crate DECIMAL(10, 2) NOT NULL,
    product_type ENUM('STANDARD', 'TOP') NOT NULL DEFAULT 'STANDARD',
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ristourne Quarters Table
CREATE TABLE IF NOT EXISTS ristourne_quarters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    quarter TINYINT NOT NULL CHECK (quarter IN (1, 2, 3, 4)),
    status ENUM('PENDING', 'PARTIALLY_PAID', 'PAID') NOT NULL DEFAULT 'PENDING',
    total_crates INT NOT NULL DEFAULT 0,
    expected_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    actual_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    payment_date DATE,
    notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_quarter (year, quarter)
);

-- Purchases Table
CREATE TABLE IF NOT EXISTS purchases (
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
    FOREIGN KEY (created_by) REFERENCES users(id),
    CHECK (crates > 0 OR top_units > 0),
    CHECK (amount >= 0)
);

-- Crate Returns Table
CREATE TABLE IF NOT EXISTS crate_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(50) NOT NULL UNIQUE,
    return_date DATE NOT NULL,
    crates INT NOT NULL CHECK (crates > 0),
    agent_id INT NULL,
    receipt_path VARCHAR(255),
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_reversed BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Monthly Closings Table
CREATE TABLE IF NOT EXISTS monthly_closings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    closing_month DATE NOT NULL UNIQUE, -- Store as YYYY-MM-01
    opening_balance INT NOT NULL,
    crates_purchased INT NOT NULL,
    empty_crates_returned INT NOT NULL,
    closing_balance INT NOT NULL,
    total_purchase_amount DECIMAL(15, 2) NOT NULL,
    ristourne_earned DECIMAL(15, 2) NOT NULL,
    transaction_count INT NOT NULL,
    closed_by INT NOT NULL,
    closed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (closed_by) REFERENCES users(id)
);

-- Goals Table
CREATE TABLE IF NOT EXISTS goals (
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
);

-- Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT
);

-- Initial Settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('company_name', 'ETS MAMY BOY'),
('supplier_name', 'BORIS ET CRISTAL SARL'),
('location', 'Djoum, Cameroon'),
('currency', 'FCFA'),
('timezone', 'Africa/Douala'),
('receipt_max_size', '5242880'); -- 5MB
