<?php
/**
 * Migration Test - Forces migration execution and verifies invoice system
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../include/migrations.inc.php';

class MigrationTest {
    private $testResults = [];
    
    public function runTests() {
        echo "<h2>Migration Test Results</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
        
        $this->testMigrationExecution();
        $this->testInvoiceTableCreation();
        $this->testInvoiceSystemFunctionality();
        
        $this->printResults();
        echo "</div>\n";
    }
    
    /**
     * Test 1: Force migration execution
     */
    private function testMigrationExecution() {
        if (function_exists('debugLog')) {
            debugLog("MIGRATION_TEST", "Testing migration execution");
        }
        
        try {
            $migration_manager = new MigrationManager();
            
            // Check current version
            $current_version = $migration_manager->getCurrentVersion();
            if (function_exists('debugLog')) {
                debugLog("MIGRATION_TEST", "Current migration version: $current_version");
            }
            
            // Force run pending migrations
            $migrations_run = $migration_manager->runPendingMigrations();
            
            if ($migrations_run !== false) {
                if (!empty($migrations_run)) {
                    $this->testResults[] = "✅ MIGRATIONS: Executed - " . implode(', ', $migrations_run);
                    if (function_exists('debugLog')) {
                        debugLog("MIGRATION_TEST", "Migrations executed: " . implode(', ', $migrations_run));
                    }
                } else {
                    $this->testResults[] = "ℹ️ MIGRATIONS: No pending migrations";
                    if (function_exists('debugLog')) {
                        debugLog("MIGRATION_TEST", "No pending migrations");
                    }
                }
            } else {
                $this->testResults[] = "❌ MIGRATIONS: Failed to execute";
                if (function_exists('debugLog')) {
                    debugLog("MIGRATION_TEST", "Migration execution failed");
                }
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ MIGRATIONS: Exception - " . $e->getMessage();
            if (function_exists('debugLog')) {
                debugLog("MIGRATION_TEST", "Migration exception: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Test 2: Verify invoice table creation
     */
    private function testInvoiceTableCreation() {
        if (function_exists('debugLog')) {
            debugLog("MIGRATION_TEST", "Testing invoice table creation");
        }
        
        try {
            global $db;
            if (!isset($db)) {
                $db = new DB_Sql();
                $db->connect();
            }
            $invoices_table = $GLOBALS['_PJ_table_prefix'] . 'invoices';
            
            // Check if invoices table exists
            $query = "SHOW TABLES LIKE '$invoices_table'";
            $db->query($query);
            
            if ($db->next_record()) {
                $this->testResults[] = "✅ INVOICE_TABLE: Table '$invoices_table' exists";
                if (function_exists('debugLog')) {
                    debugLog("MIGRATION_TEST", "Invoice table exists");
                }
                
                // Check table structure
                $query = "DESCRIBE $invoices_table";
                $db->query($query);
                $columns = [];
                while ($db->next_record()) {
                    $columns[] = $db->Record['Field'];
                }
                
                $required_columns = ['id', 'invoice_number', 'customer_id', 'total_amount', 'status'];
                $missing_columns = array_diff($required_columns, $columns);
                
                if (empty($missing_columns)) {
                    $this->testResults[] = "✅ INVOICE_SCHEMA: All required columns present";
                } else {
                    $this->testResults[] = "⚠️ INVOICE_SCHEMA: Missing columns - " . implode(', ', $missing_columns);
                }
                
            } else {
                $this->testResults[] = "❌ INVOICE_TABLE: Table '$invoices_table' does not exist";
                if (function_exists('debugLog')) {
                    debugLog("MIGRATION_TEST", "Invoice table missing");
                }
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ INVOICE_TABLE: Exception - " . $e->getMessage();
            if (function_exists('debugLog')) {
                debugLog("MIGRATION_TEST", "Invoice table check exception: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Test 3: Test invoice system functionality
     */
    private function testInvoiceSystemFunctionality() {
        if (function_exists('debugLog')) {
            debugLog("MIGRATION_TEST", "Testing invoice system functionality");
        }
        
        try {
            // Test invoice class instantiation
            if (class_exists('Invoice')) {
                $invoice = new Invoice();
                $this->testResults[] = "✅ INVOICE_CLASS: Successfully instantiated";
                
                // Test invoice number generation
                try {
                    $invoice_number = $invoice->generateInvoiceNumber(1);
                    if (!empty($invoice_number)) {
                        $this->testResults[] = "✅ INVOICE_NUMBER: Generated successfully - $invoice_number";
                        if (function_exists('debugLog')) {
                            debugLog("MIGRATION_TEST", "Invoice number generated: $invoice_number");
                        }
                    } else {
                        $this->testResults[] = "❌ INVOICE_NUMBER: Empty result";
                    }
                } catch (Exception $e) {
                    $this->testResults[] = "❌ INVOICE_NUMBER: Exception - " . $e->getMessage();
                    if (function_exists('debugLog')) {
                        debugLog("MIGRATION_TEST", "Invoice number generation failed: " . $e->getMessage());
                    }
                }
                
            } else {
                $this->testResults[] = "❌ INVOICE_CLASS: Class not found";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ INVOICE_SYSTEM: Exception - " . $e->getMessage();
            if (function_exists('debugLog')) {
                debugLog("MIGRATION_TEST", "Invoice system test exception: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Print test results
     */
    private function printResults() {
        echo "<h3>Test Results:</h3>\n";
        foreach ($this->testResults as $result) {
            echo "<div>$result</div>\n";
        }
        
        $passed = count(array_filter($this->testResults, function($r) { return strpos($r, '✅') === 0; }));
        $failed = count(array_filter($this->testResults, function($r) { return strpos($r, '❌') === 0; }));
        $warnings = count(array_filter($this->testResults, function($r) { return strpos($r, '⚠️') === 0; }));
        
        echo "<hr>\n";
        echo "<strong>Summary: $passed passed, $failed failed, $warnings warnings</strong>\n";
    }
}

// Run the test
$test = new MigrationTest();
$test->runTests();
