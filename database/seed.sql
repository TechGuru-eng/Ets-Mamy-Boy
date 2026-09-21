USE mamy_boy;

-- Seed Admin User (Password is 'password')
INSERT INTO users (name, username, password_hash, role) VALUES 
('Admin User', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN'),
('Staff User', 'staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF');

-- Seed Agents
INSERT INTO agents (full_name, phone_number, notes) VALUES 
('Jean Dupont', '677123456', 'Reliable driver'),
('Paul Biya', '699987654', 'Morning shifts usually');

-- Seed Historical Ristourne Rates
INSERT INTO ristourne_rates (rate_per_crate, product_type, start_date, end_date, is_active) VALUES 
(40.00, 'STANDARD', '2025-01-01', '2025-12-31', FALSE),
(314.00, 'STANDARD', '2026-01-01', NULL, TRUE),
(100.00, 'TOP', '2026-01-01', NULL, TRUE);

-- Seed some transactions for demonstration
-- Assuming rate_id 2 (50 FCFA) is active
INSERT INTO purchases (transaction_number, purchase_date, crates, top_units, product_type, amount, agent_id, ristourne_rate_id, created_by) VALUES 
('PUR-20260701-0001', '2026-07-01', 500, 0, 'STANDARD', 2500000.00, 1, 2, 1),
('PUR-20260715-0002', '2026-07-15', 600, 0, 'STANDARD', 3000000.00, 2, 2, 1);

INSERT INTO crate_returns (transaction_number, return_date, crates, agent_id, created_by) VALUES 
('CRT-20260705-0001', '2026-07-05', 400, 1, 1),
('CRT-20260720-0002', '2026-07-20', 650, 2, 1);

-- Ristourne Quarter Seed (Q3 2026 - Pending)
INSERT INTO ristourne_quarters (year, quarter, status, total_crates, expected_amount) VALUES 
(2026, 3, 'PENDING', 1100, 55000.00);
