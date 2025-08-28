<?php
/**
 * Test for Contract Table Prefix Issues
 */

// Set environment for testing
$_SERVER['REQUEST_URI'] = '/tests/unit/ContractsPrefixTest.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$no_login = true;
$_PJ_debug = true;

require_once(__DIR__ . "/../../bootstrap.php");
include_once(__DIR__ . "/../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

class ContractsPrefixTest {
    private $testResults = [];
    
    public function runTests() {
        echo "<h2>Contracts Table Prefix Test</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
        
        $this->testTableExistence();
        $this->testContractClass();
        $this->testContractsPageAccess();
        
        $this->printResults();
        echo "</div>\n";
    }
    
    /**
     * Test 1: Check table existence with correct prefixes
     */
    private function testTableExistence() {
        debugLog("CONTRACTS_PREFIX_TEST", "Testing table existence");
        
        try {
            $db = new Database();
            
            $tables_to_check = [
                $GLOBALS['_PJ_table_prefix'] . 'customer_contracts',
                $GLOBALS['_PJ_customer_table'],
                $GLOBALS['_PJ_project_table']
            ];
            
            foreach ($tables_to_check as $table) {
                $query = "SHOW TABLES LIKE '$table'";
                $db->query($query);
                
                if ($db->next_record()) {
                    $this->testResults[] = "✅ TABLE: $table exists";
                } else {
                    $this->testResults[] = "❌ TABLE: $table missing";
                }
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ TABLE_CHECK: Exception - " . $e->getMessage();
        }
    }
    
    /**
     * Test 2: Test contract class methods
     */
    private function testContractClass() {
        debugLog("CONTRACTS_PREFIX_TEST", "Testing contract class");
        
        try {
            require_once(__DIR__ . "/../../include/contract.class.php");
            
            $db = new Database();
            $contract = new Contract($db, 1);
            
            $this->testResults[] = "✅ CONTRACT_CLASS: Instantiated successfully";
            
            // Test getCustomerContracts method (this was causing the error)
            try {
                $contracts = $contract->getCustomerContracts(1);
                $this->testResults[] = "✅ CONTRACT_METHOD: getCustomerContracts() works";
            } catch (Exception $e) {
                $this->testResults[] = "❌ CONTRACT_METHOD: getCustomerContracts() failed - " . $e->getMessage();
            }
            
            // Test getActiveContract method
            try {
                $active_contract = $contract->getActiveContract(1);
                $this->testResults[] = "✅ CONTRACT_METHOD: getActiveContract() works";
            } catch (Exception $e) {
                $this->testResults[] = "❌ CONTRACT_METHOD: getActiveContract() failed - " . $e->getMessage();
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ CONTRACT_CLASS: Exception - " . $e->getMessage();
        }
    }
    
    /**
     * Test 3: Test contracts page access
     */
    private function testContractsPageAccess() {
        debugLog("CONTRACTS_PREFIX_TEST", "Testing contracts page access");
        
        try {
            // Set required GET parameter
            $_GET['customer_id'] = 1;
            
            // Capture output to prevent headers already sent
            ob_start();
            
            include(__DIR__ . "/../../inventory/contracts.php");
            
            $output = ob_get_contents();
            ob_end_clean();
            
            // Check for mysqli_sql_exception
            if (strpos($output, 'mysqli_sql_exception') !== false) {
                $this->testResults[] = "❌ CONTRACTS_PAGE: mysqli_sql_exception detected";
                
                // Extract error details
                if (preg_match("/Table '([^']+)' doesn't exist/", $output, $matches)) {
                    $missing_table = $matches[1];
                    $this->testResults[] = "❌ MISSING_TABLE: $missing_table";
                }
            } elseif (strpos($output, 'Fatal error') !== false) {
                $this->testResults[] = "❌ CONTRACTS_PAGE: Fatal error detected";
            } elseif (strpos($output, 'Warning') !== false || strpos($output, 'Error') !== false) {
                $this->testResults[] = "⚠️ CONTRACTS_PAGE: PHP warnings/errors detected";
            } else {
                $this->testResults[] = "✅ CONTRACTS_PAGE: Loads without SQL errors";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ CONTRACTS_PAGE: Exception - " . $e->getMessage();
        }
    }
    
    /**
     * Print test results
     */
    private function printResults() {
        echo "<h3>Test Results:</h3>\n";
        foreach ($this->testResults as $result) {
            echo $result . "<br>\n";
        }
        
        $passed = count(array_filter($this->testResults, function($r) { return strpos($r, '✅') === 0; }));
        $failed = count(array_filter($this->testResults, function($r) { return strpos($r, '❌') === 0; }));
        $warnings = count(array_filter($this->testResults, function($r) { return strpos($r, '⚠️') === 0; }));
        
        echo "<br><strong>Summary: {$passed} passed, {$failed} failed, {$warnings} warnings</strong><br>\n";
        debugLog("CONTRACTS_PREFIX_TEST", "Test completed: {$passed} passed, {$failed} failed, {$warnings} warnings");
    }
}

// Auto-run tests
if (basename($_SERVER['PHP_SELF']) === 'ContractsPrefixTest.php' || php_sapi_name() === 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h1>Contracts Table Prefix Test Suite</h1>\n";
    
    try {
        $test = new ContractsPrefixTest();
        $test->runTests();
    } catch (Exception $e) {
        echo "<div style='color: red;'><strong>FATAL ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
        echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
    }
    
    echo "<h2>Test completed</h2>\n";
}
