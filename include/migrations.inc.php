<?php
/**
 * TimeEffect Migration Manager
 * 
 * Handles database schema migrations according to DATABASE_MIGRATIONS.md specification
 * Provides version-based sequential migration execution with proper tracking and error handling
 */

class MigrationManager {
    private $db;
    private $current_version = 7; // Current target version - increment for new migrations
    private $migrations_table;

    public function __construct() {
        global $db;
        if (!isset($db)) {
            require_once(__DIR__ . '/functions.inc.php');
            require_once(__DIR__ . '/db_mysql.inc.php');
            $db = new DB_Sql();
            $db->connect();
        }
        $this->db = $db;
        $this->migrations_table = $GLOBALS['_PJ_table_prefix'] . 'migrations';
    }

    /**
     * Get the current database version from migrations table
     */
    public function getCurrentVersion() {
        try {
            // Check if migrations table exists
            $query = "SHOW TABLES LIKE '" . $this->migrations_table . "'";
            $this->db->query($query);
            
            if (!$this->db->next_record()) {
                return 0; // No migrations table exists yet
            }
            
            // Get highest executed migration version
            $query = "SELECT MAX(version) as max_version FROM " . $this->migrations_table;
            $this->db->query($query);
            
            if ($this->db->next_record()) {
                return (int)$this->db->Record['max_version'];
            }
            
            return 0;
        } catch (Exception $e) {
            error_log("Error getting current migration version: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if migrations are needed
     */
    public function migrationsNeeded() {
        return $this->getCurrentVersion() < $this->current_version;
    }

    /**
     * Create the migrations tracking table
     */
    private function createMigrationsTable() {
        try {
            $query = "CREATE TABLE " . $this->migrations_table . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version INT NOT NULL UNIQUE,
                migration_name VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX(version)
            ) ENGINE=MyISAM";
            
            return $this->db->query($query);
        } catch (Exception $e) {
            error_log("Failed to create migrations table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record a completed migration
     */
    private function recordMigration($version, $name) {
        $query = "INSERT INTO " . $this->migrations_table . " (version, migration_name) 
                  VALUES (" . intval($version) . ", '" . addslashes($name) . "')";
        return $this->db->query($query);
    }

    /**
     * Run all pending migrations
     */
    public function runPendingMigrations() {
        $current_version = $this->getCurrentVersion();
        $migrations_run = array();
        
        // Ensure migrations table exists
        if ($current_version === 0) {
            if (!$this->createMigrationsTable()) {
                return false;
            }
        }
        
        // Migration 1: User registration fields (if not already present)
        if ($current_version < 1) {
            if ($this->runMigration1()) {
                $migrations_run[] = 'User registration fields';
                $this->recordMigration(1, 'User registration fields');
            }
        }
        
        // Migration 2: Theme preference field
        if ($current_version < 2) {
            if ($this->runMigration2()) {
                $migrations_run[] = 'Theme preference field';
                $this->recordMigration(2, 'Theme preference field');
            }
        }
        
        // Migration 3: Login attempts table for brute force protection
        if ($current_version < 3) {
            if ($this->runMigration3()) {
                $migrations_run[] = 'Login attempts table for brute force protection';
                $this->recordMigration(3, 'Login attempts table for brute force protection');
            }
        }
        
        // Migration 4: Invoice system tables and user extensions
        if ($current_version < 4) {
            if ($this->runMigration4()) {
                $migrations_run[] = 'Invoice system tables and user extensions';
                $this->recordMigration(4, 'Invoice system tables and user extensions');
            }
        }
        
        // Migration 5: Customer contracts table
        if ($current_version < 5) {
            if ($this->runMigration5()) {
                $migrations_run[] = 'Customer contracts table';
                $this->recordMigration(5, 'Customer contracts table');
            }
        }
        
        // Migration 6: Complete invoice system with all tables
        if ($current_version < 6) {
            if ($this->runMigration6()) {
                $migrations_run[] = 'Complete invoice system with all tables';
                $this->recordMigration(6, 'Complete invoice system with all tables');
            }
        }
        
        // Migration 7: Fix customer_contracts table schema
        if ($current_version < 7) {
            if ($this->runMigration7()) {
                $migrations_run[] = 'Fix customer_contracts table schema';
                $this->recordMigration(7, 'Fix customer_contracts table schema');
            }
        }
        
        return $migrations_run;
    }

    /**
     * Migration 1: User registration fields
     */
    private function runMigration1() {
        try {
            // Check each field individually and add only missing ones
            $fields_to_add = array();
            
            // Check confirmed field
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'confirmed'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $fields_to_add[] = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN confirmed TINYINT(1) NOT NULL DEFAULT 1 AFTER facsimile";
            }
            
            // Check confirmation_token field
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'confirmation_token'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $fields_to_add[] = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN confirmation_token VARCHAR(64) NULL AFTER " . (in_array('confirmed', array_column($fields_to_add, 0)) ? 'confirmed' : 'facsimile');
            }
            
            // Check reset_token field
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'reset_token'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $last_field = 'facsimile';
                if (strpos(implode(' ', $fields_to_add), 'confirmation_token') !== false) {
                    $last_field = 'confirmation_token';
                } elseif (strpos(implode(' ', $fields_to_add), 'confirmed') !== false) {
                    $last_field = 'confirmed';
                }
                $fields_to_add[] = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN reset_token VARCHAR(64) NULL AFTER " . $last_field;
            }
            
            // Check reset_expires field
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'reset_expires'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $last_field = 'facsimile';
                if (strpos(implode(' ', $fields_to_add), 'reset_token') !== false) {
                    $last_field = 'reset_token';
                } elseif (strpos(implode(' ', $fields_to_add), 'confirmation_token') !== false) {
                    $last_field = 'confirmation_token';
                } elseif (strpos(implode(' ', $fields_to_add), 'confirmed') !== false) {
                    $last_field = 'confirmed';
                }
                $fields_to_add[] = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN reset_expires DATETIME NULL AFTER " . $last_field;
            }
            
            // If no fields to add, migration already complete
            if (empty($fields_to_add)) {
                return true;
            }
            
            // Execute queries for missing fields only
            foreach ($fields_to_add as $query) {
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Migration 1 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migration 2: Theme preference field
     */
    private function runMigration2() {
        try {
            // Check if field already exists
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'theme_preference'";
            $this->db->query($query);
            if ($this->db->next_record()) {
                return true; // Already exists
            }
            
            // Add theme preference field
            $query = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN theme_preference VARCHAR(10) DEFAULT 'system' AFTER facsimile";
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Migration 2 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migration 3: Login attempts table for brute force protection
     */
    private function runMigration3() {
        try {
            // Check if table already exists
            $query = "SHOW TABLES LIKE '" . $this->migrations_table . "'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                // Migrations table doesn't exist yet, create it first
                if (!$this->createMigrationsTable()) {
                    return false;
                }
            }
            
            // Check if login_attempts table already exists
            $login_attempts_table = $GLOBALS['_PJ_table_prefix'] . 'login_attempts';
            $query = "SHOW TABLES LIKE '" . $login_attempts_table . "'";
            $this->db->query($query);
            if ($this->db->next_record()) {
                return true; // Already exists
            }
            
            // Create login_attempts table
            $query = "CREATE TABLE " . $login_attempts_table . " (
                id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                ip_address VARCHAR(45) NOT NULL COMMENT 'IP address of the attempt',
                username VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Username attempted',
                attempt_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                success TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                KEY ip_time (ip_address, attempt_time),
                KEY username_time (username, attempt_time)
            ) ENGINE=MyISAM COMMENT='Tracks login attempts for brute force protection'";
            
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Migration 3 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migration 4: Invoice system tables and user extensions
     */
    private function runMigration4() {
        try {
            // First, execute the main SQL file for invoice tables
            $migration_file = dirname(__FILE__) . '/../sql/migrations/002_add_invoice_system.sql';
            
            if (!file_exists($migration_file)) {
                error_log("Migration 4: SQL file not found at " . $migration_file);
                return false;
            }
            
            $sql_content = file_get_contents($migration_file);
            if ($sql_content === false) {
                error_log("Migration 4: Could not read SQL file");
                return false;
            }
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql_content)));
            
            foreach ($statements as $statement) {
                if (empty($statement) || strpos($statement, '--') === 0) {
                    continue; // Skip empty lines and comments
                }
                
                if (!$this->db->query($statement)) {
                    error_log("Migration 4: Failed to execute statement: " . substr($statement, 0, 100) . "...");
                    return false;
                }
            }
            
            // Now handle user table extensions dynamically
            if (!$this->addUserTableExtensions()) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Migration 4 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add invoice-related columns to the user/auth table
     */
    private function addUserTableExtensions() {
        $auth_table = $GLOBALS['_PJ_auth_table'];
        
        $columns_to_add = array(
            'company_name' => "ALTER TABLE $auth_table ADD COLUMN company_name VARCHAR(255) NULL",
            'company_address' => "ALTER TABLE $auth_table ADD COLUMN company_address TEXT NULL",
            'company_postal_code' => "ALTER TABLE $auth_table ADD COLUMN company_postal_code VARCHAR(20) NULL",
            'company_city' => "ALTER TABLE $auth_table ADD COLUMN company_city VARCHAR(100) NULL",
            'company_country' => "ALTER TABLE $auth_table ADD COLUMN company_country VARCHAR(100) NULL",
            'tax_number' => "ALTER TABLE $auth_table ADD COLUMN tax_number VARCHAR(50) NULL",
            'vat_number' => "ALTER TABLE $auth_table ADD COLUMN vat_number VARCHAR(50) NULL",
            'bank_name' => "ALTER TABLE $auth_table ADD COLUMN bank_name VARCHAR(100) NULL",
            'bank_iban' => "ALTER TABLE $auth_table ADD COLUMN bank_iban VARCHAR(34) NULL",
            'bank_bic' => "ALTER TABLE $auth_table ADD COLUMN bank_bic VARCHAR(11) NULL",
            'invoice_logo_path' => "ALTER TABLE $auth_table ADD COLUMN invoice_logo_path VARCHAR(255) NULL",
            'invoice_letterhead_path' => "ALTER TABLE $auth_table ADD COLUMN invoice_letterhead_path VARCHAR(255) NULL",
            'invoice_footer_path' => "ALTER TABLE $auth_table ADD COLUMN invoice_footer_path VARCHAR(255) NULL",
            'invoice_number_format' => "ALTER TABLE $auth_table ADD COLUMN invoice_number_format VARCHAR(50) DEFAULT 'R-{YYYY}-{MM}-{###}'",
            'default_vat_rate' => "ALTER TABLE $auth_table ADD COLUMN default_vat_rate DECIMAL(5,2) DEFAULT 19.00",
            'payment_terms_days' => "ALTER TABLE $auth_table ADD COLUMN payment_terms_days INT DEFAULT 14",
            'payment_terms_text' => "ALTER TABLE $auth_table ADD COLUMN payment_terms_text TEXT NULL"
        );
        
        foreach ($columns_to_add as $column_name => $alter_statement) {
            // Check if column already exists
            $check_query = "SHOW COLUMNS FROM $auth_table LIKE '$column_name'";
            $this->db->query($check_query);
            
            if (!$this->db->next_record()) {
                // Column doesn't exist, add it
                if (!$this->db->query($alter_statement)) {
                    error_log("Migration 4: Failed to add column $column_name to $auth_table");
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Migration 5: Customer contracts table
     */
    private function runMigration5() {
        try {
            $contracts_table = $GLOBALS['_PJ_table_prefix'] . 'customer_contracts';
            
            // Check if table already exists
            $query = "SHOW TABLES LIKE '$contracts_table'";
            $this->db->query($query);
            if ($this->db->next_record()) {
                return true; // Already exists
            }
            
            // Create customer_contracts table
            $query = "CREATE TABLE $contracts_table (
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
            )";
            
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Migration 5 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migration 6: Complete invoice system with all tables
     */
    private function runMigration6() {
        try {
            $prefix = $GLOBALS['_PJ_table_prefix'];
            
            // Check and create invoices table
            $invoices_table = $prefix . 'invoices';
            $query = "SHOW TABLES LIKE '$invoices_table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $query = "CREATE TABLE $invoices_table (
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
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            // Check and create invoice_items table
            $items_table = $prefix . 'invoice_items';
            $query = "SHOW TABLES LIKE '$items_table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $query = "CREATE TABLE $items_table (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    invoice_id INT NOT NULL,
                    description TEXT NOT NULL,
                    quantity DECIMAL(8,2) NOT NULL,
                    unit_price DECIMAL(10,2) NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL
                )";
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            // Check and create invoice_efforts table
            $efforts_table = $prefix . 'invoice_efforts';
            $query = "SHOW TABLES LIKE '$efforts_table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $query = "CREATE TABLE $efforts_table (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    invoice_id INT NOT NULL,
                    effort_id INT NOT NULL
                )";
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            // Check and create hour_carryovers table
            $carryovers_table = $prefix . 'hour_carryovers';
            $query = "SHOW TABLES LIKE '$carryovers_table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $query = "CREATE TABLE $carryovers_table (
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
                    UNIQUE KEY unique_period (customer_id, project_id, period_year, period_month)
                )";
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            // Check and create invoice_payments table
            $payments_table = $prefix . 'invoice_payments';
            $query = "SHOW TABLES LIKE '$payments_table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $query = "CREATE TABLE $payments_table (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    invoice_id INT NOT NULL,
                    payment_date DATE,
                    amount DECIMAL(10,2) NOT NULL,
                    payment_method VARCHAR(50),
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            // Check and create payment_reminders table
            $reminders_table = $prefix . 'payment_reminders';
            $query = "SHOW TABLES LIKE '$reminders_table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $query = "CREATE TABLE $reminders_table (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    invoice_id INT NOT NULL,
                    reminder_type ENUM('first', 'second', 'final') NOT NULL,
                    sent_date DATE,
                    due_date DATE,
                    reminder_text TEXT,
                    status ENUM('pending', 'sent', 'cancelled') DEFAULT 'pending'
                )";
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            debugLog("MIGRATION_6", "Migration 6 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get migration history
     */
    public function getMigrationHistory() {
        $query = "SELECT * FROM " . $this->migrations_table . " ORDER BY version";
        $this->db->query($query);
        
        $history = array();
        while ($this->db->next_record()) {
            $history[] = $this->db->Record;
        }
        
        return $history;
    }

    /**
     * Migration 7: Fix customer_contracts table schema
     */
    private function runMigration7() {
        try {
            $contracts_table = $GLOBALS['_PJ_table_prefix'] . 'customer_contracts';
            
            // Check if table exists first
            $query = "SHOW TABLES LIKE '$contracts_table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                // Table doesn't exist, create it with correct schema
                $query = "CREATE TABLE $contracts_table (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    project_id INT NULL,
                    contract_type ENUM('hourly', 'fixed', 'retainer') DEFAULT 'hourly',
                    hourly_rate DECIMAL(10,2) NULL,
                    fixed_amount DECIMAL(10,2) NULL,
                    fixed_hours DECIMAL(8,2) NULL,
                    description TEXT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NULL,
                    active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_customer_id (customer_id),
                    INDEX idx_project_id (project_id),
                    INDEX idx_active (active),
                    INDEX idx_dates (start_date, end_date)
                ) ENGINE=MyISAM";
                
                return $this->db->query($query);
            }
            
            // Table exists, check and add missing columns
            $columns_to_check = array(
                'fixed_hours' => "ALTER TABLE $contracts_table ADD COLUMN fixed_hours DECIMAL(8,2) NULL AFTER fixed_amount",
                'description' => "ALTER TABLE $contracts_table ADD COLUMN description TEXT NULL AFTER fixed_hours"
            );
            
            foreach ($columns_to_check as $column_name => $alter_statement) {
                $check_query = "SHOW COLUMNS FROM $contracts_table LIKE '$column_name'";
                $this->db->query($check_query);
                
                if (!$this->db->next_record()) {
                    // Column doesn't exist, add it
                    if (!$this->db->query($alter_statement)) {
                        debugLog("MIGRATION_7", "Failed to add column $column_name to $contracts_table");
                        return false;
                    }
                }
            }
            
            // Remove retainer_hours column if it exists (was incorrectly added in migration 5)
            $check_query = "SHOW COLUMNS FROM $contracts_table LIKE 'retainer_hours'";
            $this->db->query($check_query);
            if ($this->db->next_record()) {
                $drop_query = "ALTER TABLE $contracts_table DROP COLUMN retainer_hours";
                if (!$this->db->query($drop_query)) {
                    debugLog("MIGRATION_7", "Failed to drop retainer_hours column from $contracts_table");
                    // Don't fail the migration for this, just log it
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            debugLog("MIGRATION_7", "Migration 7 failed: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Check and run migrations if needed
 * Call this function during login/bootstrap
 */
function checkAndRunMigrations() {
    try {
        $migration_manager = new MigrationManager();
        
        if ($migration_manager->migrationsNeeded()) {
            $migrations_run = $migration_manager->runPendingMigrations();
            
            // Log successful migrations
            if (!empty($migrations_run)) {
                error_log("TimeEffect: Automatic migrations completed: " . implode(', ', $migrations_run));
            }
            
            return $migrations_run;
        }
        
        return array(); // No migrations needed
        
    } catch (Exception $e) {
        error_log("TimeEffect: Migration check failed: " . $e->getMessage());
        return false;
    }
}
?>
