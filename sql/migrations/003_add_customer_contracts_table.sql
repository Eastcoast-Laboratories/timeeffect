-- Create customer_contracts table for contract management with proper prefix
CREATE TABLE IF NOT EXISTS te_customer_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    project_id INT NULL,
    contract_type ENUM('hourly', 'fixed', 'retainer') DEFAULT 'hourly',
    hourly_rate DECIMAL(10,2) NULL,
    fixed_amount DECIMAL(10,2) NULL,
    retainer_hours INT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_id (customer_id),
    INDEX idx_project_id (project_id),
    INDEX idx_active (active),
    INDEX idx_dates (start_date, end_date)
);
