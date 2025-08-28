-- Automated Invoice Generation System Migration
-- Creates all tables needed for invoice management, contracts, and payment tracking

-- Main invoices table
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    project_id INT NULL,
    invoice_date DATE NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    contract_type ENUM('hourly', 'fixed_monthly') DEFAULT 'hourly',
    fixed_amount DECIMAL(10,2) NULL,
    fixed_hours DECIMAL(8,2) NULL,
    total_hours DECIMAL(8,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    vat_rate DECIMAL(5,2) DEFAULT 19.00,
    vat_amount DECIMAL(10,2) NOT NULL,
    gross_amount DECIMAL(10,2) NOT NULL,
    carryover_previous DECIMAL(8,2) DEFAULT 0,
    carryover_current DECIMAL(8,2) DEFAULT 0,
    description TEXT,
    status ENUM('draft', 'sent', 'paid', 'cancelled') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- Invoice line items
CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(8,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Link invoices to specific efforts
CREATE TABLE invoice_efforts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    effort_id INT NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (effort_id) REFERENCES efforts(id)
);

-- Customer contract definitions
CREATE TABLE customer_contracts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    project_id INT NULL,
    contract_type ENUM('hourly', 'fixed_monthly') DEFAULT 'hourly',
    fixed_amount DECIMAL(10,2) NULL,
    fixed_hours DECIMAL(8,2) NULL,
    hourly_rate DECIMAL(10,2) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- Hour carryover tracking for fixed contracts
CREATE TABLE hour_carryovers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    project_id INT NULL,
    period_year INT NOT NULL,
    period_month INT NOT NULL,
    contracted_hours DECIMAL(8,2) NOT NULL,
    actual_hours DECIMAL(8,2) NOT NULL,
    carryover_hours DECIMAL(8,2) NOT NULL,
    cumulative_carryover DECIMAL(8,2) NOT NULL,
    invoice_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    UNIQUE KEY unique_period (customer_id, project_id, period_year, period_month)
);

-- Payment tracking
CREATE TABLE invoice_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    payment_date DATE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- Payment reminders
CREATE TABLE payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    reminder_type ENUM('first', 'second', 'final') NOT NULL,
    sent_date DATE,
    due_date DATE,
    reminder_text TEXT,
    status ENUM('pending', 'sent', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- Invoice templates
CREATE TABLE invoice_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    header_html TEXT,
    footer_html TEXT,
    css_styles TEXT,
    personalization_data JSON,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- Note: User table extensions will be handled dynamically by migration code
-- to use the correct table name from $_PJ_auth_table
