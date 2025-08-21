<?php
/**
 * TimeEffect Migration Manager
 * 
 * Handles database schema migrations according to DATABASE_MIGRATIONS.md specification
 * Provides version-based sequential migration execution with proper tracking and error handling
 */

class MigrationManager {
    private $db;
    private $current_version = 3; // Current target version - increment for new migrations
    private $migrations_table;

    public function __construct() {
        $this->db = new Database();
        $this->migrations_table = $GLOBALS['_PJ_db_prefix'] . 'migrations';
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
