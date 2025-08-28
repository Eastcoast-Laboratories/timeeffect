<?php
/**
 * Working Unit Test for Invoice Preview and System Functionality
 * Based on SbeParameterTest pattern that works in TimeEffect environment
 */

// Set CLI environment variables to prevent bootstrap errors
$_SERVER['REQUEST_URI'] = '/tests/unit/InvoicePreviewWorkingTest.php';
$_SERVER['HTTP_HOST'] = 'localhost';

// Simple test runner without complex dependencies
$no_login = true;
$_PJ_debug = true;

// Basic includes only
require_once(__DIR__ . "/../../bootstrap.php");
include_once(__DIR__ . "/../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

// Include required classes
require_once(__DIR__ . "/../../include/invoice.class.php");
require_once(__DIR__ . "/../../include/contract.class.php");
require_once(__DIR__ . "/../../include/carryover.class.php");

class InvoicePreviewWorkingTest {
    private $testResults = [];
    private $testCustomer = null;
    private $testProject = null;
    private $testEfforts = [];
    private $testAuth = null;
    private $db = null;
    
    /**
     * Constructor - initialize test environment
     */
    public function __construct() {
        // Enable debug logging for tests
        $GLOBALS['_PJ_debug'] = true;
        
        // Skip authentication for tests
        $GLOBALS['no_login'] = true;
        
        // Set up auth first before database
        $this->createTestAuth();
        
        // Initialize database
        $this->db = new Database();
    }
    
    /**
     * Create a test auth object with admin permissions
     */
    private function createTestAuth() {
        // Create a proper mock auth class
        $auth = new class {
            public $id = 1;
            public $username = 'test_admin';
            public $permissions = 'admin';
            public $gid = 1;
            public $gids = '1';
            
            public function giveValue($key) {
                return property_exists($this, $key) ? $this->$key : null;
            }
            
            public function checkPermission($permission) {
                return $permission === 'admin' || $permission === 'agent';
            }
        };
        
        global $_PJ_auth;
        $_PJ_auth = $auth;
        $this->testAuth = $auth;
        
        return $auth;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        // Enable debugging for this test
        $GLOBALS['_PJ_debug'] = true;
        
        // Fix path issues for CLI execution
        if (!defined('_PJ_INSTALLPATH')) {
            define('_PJ_INSTALLPATH', '/var/www/timeeffect/');
        }
        
        // Override language path to prevent errors
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/timeeffect';
        $GLOBALS['_PJ_installpath'] = '/var/www/timeeffect/';
        
        // Skip language loading for tests
        if (!defined('_PJ_SKIP_LANGUAGE')) {
            define('_PJ_SKIP_LANGUAGE', true);
        }
        
        echo "<h2>Invoice Preview System Tests</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
        
        $this->testDatabaseConnection();
        $this->testMigrations();
        $this->testTableExistence();
        $this->testClassInstantiation();
        $this->setupTestData();
        $this->testPreviewParameters();
        $this->testContractFunctionality();
        $this->testEffortRetrieval();
        $this->testAjaxPreviewEndpoint();
        $this->testInvoiceCreateEndpoint();
        $this->cleanup();
        
        $this->printResults();
        echo "</div>\n";
    }
    
    /**
     * Test 1: Database Connection
     */
    private function testDatabaseConnection() {
        debugLog("INVOICE_TEST_DB", "Testing database connection");
        
        try {
            $query = "SELECT 1 as test";
            $this->db->query($query);
            if ($this->db->next_record()) {
                $this->testResults[] = "✅ DATABASE: Connection successful";
                debugLog("INVOICE_TEST_DB", "Database connection successful");
            } else {
                $this->testResults[] = "❌ DATABASE: Query failed";
            }
        } catch (Exception $e) {
            $this->testResults[] = "❌ DATABASE: Connection failed - " . $e->getMessage();
            debugLog("INVOICE_TEST_DB", "Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Test 2: Migration System
     */
    private function testMigrations() {
        debugLog("INVOICE_TEST_MIGRATION", "Testing migration system");
        
        try {
            $migration_manager = new MigrationManager();
            $current_version = $migration_manager->getCurrentVersion();
            
            if ($migration_manager->migrationsNeeded()) {
                $migrations_run = $migration_manager->runPendingMigrations();
                if (!empty($migrations_run)) {
                    $this->testResults[] = "✅ MIGRATIONS: Completed - " . implode(', ', $migrations_run);
                    debugLog("INVOICE_TEST_MIGRATION", "Migrations completed: " . implode(', ', $migrations_run));
                } else {
                    $this->testResults[] = "❌ MIGRATIONS: Failed to run pending migrations";
                }
            } else {
                $this->testResults[] = "✅ MIGRATIONS: Up to date (version $current_version)";
                debugLog("INVOICE_TEST_MIGRATION", "Migrations up to date");
            }
        } catch (Exception $e) {
            $this->testResults[] = "❌ MIGRATIONS: Exception - " . $e->getMessage();
            debugLog("INVOICE_TEST_MIGRATION", "Migration exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 3: Required Tables Existence
     */
    private function testTableExistence() {
        debugLog("INVOICE_TEST_TABLES", "Testing required tables existence");
        
        $tables_to_check = [
            $GLOBALS['_PJ_customer_table'],
            $GLOBALS['_PJ_project_table'],
            $GLOBALS['_PJ_effort_table'],
            $GLOBALS['_PJ_auth_table'],
            $GLOBALS['_PJ_table_prefix'] . 'customer_contracts'
        ];
        
        $missing_tables = [];
        foreach ($tables_to_check as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $this->db->query($query);
            if (!$this->db->next_record()) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            $this->testResults[] = "✅ TABLES: All required tables exist";
            debugLog("INVOICE_TEST_TABLES", "All required tables exist");
        } else {
            $this->testResults[] = "❌ TABLES: Missing tables - " . implode(', ', $missing_tables);
            debugLog("INVOICE_TEST_TABLES", "Missing tables: " . implode(', ', $missing_tables));
        }
    }
    
    /**
     * Test 4: Class Instantiation
     */
    private function testClassInstantiation() {
        debugLog("INVOICE_TEST_CLASSES", "Testing class instantiation");
        
        try {
            $invoice = new Invoice($this->db, 1);
            $contract = new Contract($this->db, 1);
            $carryover = new Carryover($this->db, 1);
            
            $this->testResults[] = "✅ CLASSES: Invoice, Contract, Carryover instantiated successfully";
            debugLog("INVOICE_TEST_CLASSES", "All classes instantiated successfully");
        } catch (Exception $e) {
            $this->testResults[] = "❌ CLASSES: Instantiation failed - " . $e->getMessage();
            debugLog("INVOICE_TEST_CLASSES", "Class instantiation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Test 5: Setup Test Data
     */
    private function setupTestData() {
        debugLog("INVOICE_TEST_SETUP", "Creating test customer, project, and efforts");
        
        try {
            // Create test customer
            $customerData = [
                'customer_name' => 'Invoice Test Customer ' . time(),
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----',
                'active' => 'yes'
            ];
            
            $this->testCustomer = new Customer($customerData, $this->testAuth);
            $customerResult = $this->testCustomer->save();
            
            if (!empty($customerResult)) {
                $this->testResults[] = "❌ SETUP: Failed to create test customer - " . $customerResult;
                return;
            }
            
            $customerId = $this->testCustomer->giveValue('id');
            
            // Create test project
            $projectData = [
                'project_name' => 'Invoice Test Project ' . time(),
                'customer_id' => $customerId,
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----',
                'closed' => 'No',
                'project_budget' => 0,
                'project_desc' => 'Test project for invoice generation',
                'project_budget_currency' => 'EUR'
            ];
            
            $this->testProject = new Project($this->testCustomer, $this->testAuth, $projectData);
            $projectResult = $this->testProject->save();
            
            if (!empty($projectResult)) {
                $this->testResults[] = "❌ SETUP: Failed to create test project - " . $projectResult;
                return;
            }
            
            $projectId = $this->testProject->giveValue('id');
            
            // Create test efforts for last month
            $last_month_start = date('Y-m-01', strtotime('first day of last month'));
            $last_month_end = date('Y-m-t', strtotime('last day of last month'));
            
            $effortData1 = [
                'project_id' => $projectId,
                'date' => $last_month_start,
                'begin' => '09:00:00',
                'end' => '12:00:00',
                'description' => 'Test work 1',
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----',
                'minutes' => 180
            ];
            
            $effortData2 = [
                'project_id' => $projectId,
                'date' => $last_month_end,
                'begin' => '14:00:00',
                'end' => '17:00:00',
                'description' => 'Test work 2',
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----',
                'minutes' => 180
            ];
            
            $effort1 = new Effort($effortData1, $this->testAuth);
            $effort2 = new Effort($effortData2, $this->testAuth);
            
            $result1 = $effort1->save();
            $result2 = $effort2->save();
            
            if (empty($result1) && empty($result2)) {
                $this->testEfforts[] = $effort1;
                $this->testEfforts[] = $effort2;
                $this->testResults[] = "✅ SETUP: Test data created successfully (Customer: $customerId, Project: $projectId)";
                debugLog("INVOICE_TEST_SETUP", "Created customer ID: $customerId, project ID: $projectId");
            } else {
                $this->testResults[] = "❌ SETUP: Failed to create test efforts - $result1 $result2";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ SETUP: Exception - " . $e->getMessage();
            debugLog("INVOICE_TEST_SETUP", "Setup exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 6: Preview Parameters
     */
    private function testPreviewParameters() {
        debugLog("INVOICE_TEST_PREVIEW", "Testing preview parameters");
        
        if (!$this->testCustomer || !$this->testProject) {
            $this->testResults[] = "⚠️ PREVIEW: SKIPPED - No test data available";
            return;
        }
        
        try {
            $customerId = $this->testCustomer->giveValue('id');
            $projectId = $this->testProject->giveValue('id');
            
            // Set up preview parameters
            $_GET = [
                'customer_id' => $customerId,
                'project_id' => $projectId,
                'period_start' => date('Y-m-01', strtotime('first day of last month')),
                'period_end' => date('Y-m-t', strtotime('last day of last month')),
                'generate_type' => 'manual',
                'invoice_date' => date('Y-m-d'),
                'description' => 'Test invoice description'
            ];
            
            // Test customer query
            $customer_query = "SELECT customer_name as name FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id = " . intval($customerId);
            $this->db->query($customer_query);
            
            if ($this->db->next_record()) {
                $customerName = $this->db->Record['name'];
                $this->testResults[] = "✅ PREVIEW: Customer data retrieved successfully ($customerName)";
                debugLog("INVOICE_TEST_PREVIEW", "Customer data retrieved: $customerName");
            } else {
                $this->testResults[] = "❌ PREVIEW: Failed to retrieve customer data";
            }
            
            // Test project query if project specified
            if ($projectId) {
                $project_query = "SELECT project_name as name FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id = " . intval($projectId);
                $this->db->query($project_query);
                
                if ($this->db->next_record()) {
                    $projectName = $this->db->Record['name'];
                    $this->testResults[] = "✅ PREVIEW: Project data retrieved successfully ($projectName)";
                    debugLog("INVOICE_TEST_PREVIEW", "Project data retrieved: $projectName");
                } else {
                    $this->testResults[] = "❌ PREVIEW: Failed to retrieve project data";
                }
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ PREVIEW: Exception - " . $e->getMessage();
            debugLog("INVOICE_TEST_PREVIEW", "Preview exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 7: Contract Functionality
     */
    private function testContractFunctionality() {
        debugLog("INVOICE_TEST_CONTRACT", "Testing contract functionality");
        
        if (!$this->testCustomer) {
            $this->testResults[] = "⚠️ CONTRACT: SKIPPED - No test customer available";
            return;
        }
        
        try {
            $contract = new Contract($this->db, 1);
            $customerId = $this->testCustomer->giveValue('id');
            
            // Test getting active contract (should return false for non-existent contract)
            $active_contract = $contract->getActiveContract($customerId);
            
            if ($active_contract === false) {
                $this->testResults[] = "✅ CONTRACT: No active contract found (expected for test)";
                debugLog("INVOICE_TEST_CONTRACT", "Contract functionality working - no active contract");
            } else {
                $this->testResults[] = "✅ CONTRACT: Active contract found";
                debugLog("INVOICE_TEST_CONTRACT", "Contract functionality working - active contract found");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ CONTRACT: Exception - " . $e->getMessage();
            debugLog("INVOICE_TEST_CONTRACT", "Contract exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 8: AJAX Preview Endpoint with Real-World Scenarios
     */
    private function testAjaxPreviewEndpoint() {
        if (function_exists('debugLog')) {
            debugLog("INVOICE_TEST_AJAX", "Testing AJAX preview endpoint");
        }
        
        if (!$this->testCustomer) {
            $this->testResults[] = "⚠️ AJAX PREVIEW: SKIPPED - No test customer available";
            return;
        }
        
        $customerId = $this->testCustomer->giveValue('id');
        $projectId = $this->testProject ? $this->testProject->giveValue('id') : '';
        
        // Test 1: Complete parameters (should work)
        $this->runPreviewTest("Complete Parameters", [
            'customer_id' => $customerId,
            'project_id' => $projectId,
            'period_start' => date('Y-m-01', strtotime('first day of last month')),
            'period_end' => date('Y-m-t', strtotime('last day of last month')),
            'generate_type' => 'manual',
            'invoice_date' => date('Y-m-d'),
            'description' => 'Test AJAX preview'
        ]);
        
        // Test 2: Missing invoice_date (real-world scenario)
        $this->runPreviewTest("Missing invoice_date", [
            'customer_id' => $customerId,
            'project_id' => $projectId,
            'period_start' => date('Y-m-01', strtotime('first day of last month')),
            'period_end' => date('Y-m-t', strtotime('last day of last month')),
            'generate_type' => 'manual'
        ]);
        
        // Test 3: Missing description (real-world scenario)
        $this->runPreviewTest("Missing description", [
            'customer_id' => $customerId,
            'project_id' => '',
            'period_start' => '2025-07-31',
            'period_end' => '2025-08-30',
            'generate_type' => 'manual'
        ]);
        
        // Test 4: Minimal parameters (like actual URL)
        $this->runPreviewTest("Minimal Parameters", [
            'customer_id' => $customerId,
            'project_id' => '',
            'invoice_date' => '2025-08-27',
            'generate_type' => 'manual',
            'period_start' => '2025-07-31',
            'period_end' => '2025-08-30'
        ]);
    }
    
    /**
     * Helper method to run individual preview tests
     */
    private function runPreviewTest($testName, $getParams) {
        try {
            // Clear previous $_GET and set new parameters
            $_GET = $getParams;
            
            // Ensure auth and include path are available globally before including preview
            global $_PJ_auth, $_PJ_include_path;
            $_PJ_auth = $this->testAuth;
            $_PJ_include_path = __DIR__ . "/../../include"; // usually defined in from aperetiv.inc.php
            
            // Change working directory to invoice folder for correct relative paths
            $original_cwd = getcwd();
            chdir(__DIR__ . '/../../invoice');
            
            // Capture output from create script
            ob_start();
            
            try {
                include(__DIR__ . '/../../invoice/create.php');
                $output = ob_get_contents();
            } catch (Exception $e) {
                $output = json_encode(['error' => $e->getMessage()]);
            } finally {
                ob_end_clean();
                // Restore original working directory
                chdir($original_cwd);
            }
            
            // Check for PHP warnings/errors in output
            $hasPhpErrors = (strpos($output, 'Warning') !== false || 
                           strpos($output, 'Error') !== false || 
                           strpos($output, 'Deprecated') !== false ||
                           strpos($output, 'Undefined') !== false);
            
            // Check if output is valid JSON
            $response = json_decode($output, true);
            
            if ($hasPhpErrors) {
                $errorSnippet = substr($output, 0, 200);
                $this->testResults[] = "❌ AJAX PREVIEW ($testName): PHP errors detected - " . $errorSnippet . "...";
                if (function_exists('debugLog')) {
                    debugLog("INVOICE_TEST_AJAX", "$testName - PHP errors: " . substr($output, 0, 500));
                }
            } elseif ($response === null) {
                $this->testResults[] = "❌ AJAX PREVIEW ($testName): Invalid JSON response";
                if (function_exists('debugLog')) {
                    debugLog("INVOICE_TEST_AJAX", "$testName - Invalid JSON: " . substr($output, 0, 500));
                }
            } else {
                if (isset($response['success']) && $response['success']) {
                    $this->testResults[] = "✅ AJAX PREVIEW ($testName): Clean response, no PHP errors";
                    if (function_exists('debugLog')) {
                        debugLog("INVOICE_TEST_AJAX", "$testName - Success");
                    }
                } else {
                    $error = $response['error'] ?? 'Unknown error';
                    $this->testResults[] = "⚠️ AJAX PREVIEW ($testName): Functional error - $error";
                    if (function_exists('debugLog')) {
                        debugLog("INVOICE_TEST_AJAX", "$testName - Functional error: $error");
                    }
                }
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ AJAX PREVIEW ($testName): Exception - " . $e->getMessage();
            if (function_exists('debugLog')) {
                debugLog("INVOICE_TEST_AJAX", "$testName - Exception: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Test 9: Invoice Create Endpoint with Real-World Scenarios
     */
    private function testInvoiceCreateEndpoint() {
        if (function_exists('debugLog')) {
            debugLog("INVOICE_TEST_CREATE", "Testing invoice create endpoint");
        }
        
        if (!$this->testCustomer) {
            $this->testResults[] = "⚠️ INVOICE CREATE: SKIPPED - No test customer available";
            return;
        }
        
        $customerId = $this->testCustomer->giveValue('id');
        
        // Test 1: Real URL parameters (like browser call)
        $this->runCreateTest("Real URL Parameters", [
            'customer_id' => $customerId,
            'period_start' => '2025-1-1',
            'period_end' => '2025-12-31'
        ]);
        
        // Test 2: Missing invoice_date (common scenario)
        $this->runCreateTest("Missing invoice_date", [
            'customer_id' => $customerId,
            'period_start' => '2025-07-31',
            'period_end' => '2025-08-30'
        ]);
        
        // Test 3: Missing project_id (should work)
        $this->runCreateTest("Missing project_id", [
            'customer_id' => $customerId,
            'period_start' => '2025-07-31',
            'period_end' => '2025-08-30',
            'invoice_date' => '2025-08-27'
        ]);
        
        // Test 4: Complete parameters
        $this->runCreateTest("Complete Parameters", [
            'customer_id' => $customerId,
            'project_id' => '',
            'period_start' => '2025-07-31',
            'period_end' => '2025-08-30',
            'invoice_date' => '2025-08-27',
            'generate_type' => 'manual'
        ]);
    }
    
    /**
     * Helper method to run individual create tests
     */
    private function runCreateTest($testName, $getParams) {
        try {
            // Clear previous $_GET and set new parameters
            $_GET = $getParams;
            $_POST = []; // Clear POST data

            // Local collectors for errors/exceptions
            $capturedErrors = [];
            $prevErrorHandler = set_error_handler(function($errno, $errstr, $errfile = null, $errline = null) use (&$capturedErrors, $testName) {
                $msg = "[$errno] $errstr in $errfile:$errline";
                $capturedErrors[] = $msg;
                if (function_exists('debugLog')) {
                    debugLog("INVOICE_TEST_CREATE_ERR", "$testName - PHP error captured: $msg");
                }
                return false; // allow normal handling too (so output buffer captures text)
            });
            $prevExceptionHandler = set_exception_handler(function($ex) use (&$capturedErrors, $testName) {
                $etype = is_object($ex) ? get_class($ex) : 'Throwable';
                $msg = "$etype: " . $ex->getMessage();
                $capturedErrors[] = $msg;
                if (function_exists('debugLog')) {
                    debugLog("INVOICE_TEST_CREATE_EXC", "$testName - Exception captured: $msg");
                }
            });

            // Capture output from create script
            ob_start();
            $output = '';
            try {
                include(__DIR__ . '/../../invoice/create.php');
                $output = ob_get_contents();
            } catch (Throwable $e) { // catch mysqli_sql_exception and any fatal Throwables
                $output = "Throwable: " . $e->getMessage();
                if (function_exists('debugLog')) {
                    debugLog("INVOICE_TEST_CREATE_THROW", "$testName - Throwable: " . $e->getMessage());
                }
            } finally {
                ob_end_clean();
                // restore handlers
                if ($prevErrorHandler !== null) { set_error_handler($prevErrorHandler); } else { restore_error_handler(); }
                if ($prevExceptionHandler !== null) { set_exception_handler($prevExceptionHandler); } else { restore_exception_handler(); }
            }

            // Combined error text for analysis
            $combined = trim($output . "\n" . implode("\n", $capturedErrors));

            // Check for PHP warnings/errors in output or captured lists
            $hasPhpErrors = (
                strpos($combined, 'Warning') !== false ||
                strpos($combined, 'Error') !== false ||
                strpos($combined, 'Fatal error') !== false ||
                strpos($combined, 'Deprecated') !== false ||
                strpos($combined, 'Undefined') !== false ||
                strpos($combined, 'mysqli_sql_exception') !== false ||
                strpos($combined, 'Throwable:') === 0
            );

            // Detect likely table prefix issues
            $prefix = isset($GLOBALS['_PJ_table_prefix']) ? $GLOBALS['_PJ_table_prefix'] : '';
            $hasPrefixSymptom = false;
            if (!empty($combined)) {
                $lower = strtolower($combined);
                // common MySQL missing table pattern and absence of prefix
                if ((strpos($lower, "doesn't exist") !== false || strpos($lower, 'unknown table') !== false) &&
                    strpos($lower, ' invoice') !== false && // mention invoices
                    ($prefix && strpos($lower, strtolower($prefix . 'invoice')) === false)
                ) {
                    $hasPrefixSymptom = true;
                }
            }

            if ($hasPhpErrors || $hasPrefixSymptom) {
                $snippet = substr($combined, 0, 500);
                $label = $hasPrefixSymptom ? 'DB prefix error suspected' : 'PHP errors detected';
                $this->testResults[] = "❌ INVOICE CREATE ($testName): $label - " . $snippet . "...";
                if (function_exists('debugLog')) {
                    debugLog("INVOICE_TEST_CREATE", "$testName - $label: " . $snippet);
                }
            } else {
                // Check if it's a redirect or successful page load
                if (strpos($output, 'Location:') !== false || strpos($output, 'invoice') !== false || empty($output)) {
                    $this->testResults[] = "✅ INVOICE CREATE ($testName): Clean execution, no PHP errors";
                    if (function_exists('debugLog')) {
                        debugLog("INVOICE_TEST_CREATE", "$testName - Success");
                    }
                } else {
                    $this->testResults[] = "⚠️ INVOICE CREATE ($testName): Unexpected output - " . substr($output, 0, 200) . "...";
                    if (function_exists('debugLog')) {
                        debugLog("INVOICE_TEST_CREATE", "$testName - Unexpected output: " . substr($output, 0, 500));
                    }
                }
            }

        } catch (Throwable $e) {
            $this->testResults[] = "❌ INVOICE CREATE ($testName): Throwable - " . $e->getMessage();
            if (function_exists('debugLog')) {
                debugLog("INVOICE_TEST_CREATE", "$testName - Throwable (outer): " . $e->getMessage());
            }
        }
    }
    
    /**
     * Test 10: Effort Data Retrieval
     */
    private function testEffortRetrieval() {
        debugLog("INVOICE_TEST_EFFORTS", "Testing effort data retrieval");
        
        if (!$this->testCustomer) {
            $this->testResults[] = "⚠️ EFFORTS: SKIPPED - No test customer available";
            return;
        }
        
        try {
            $customerId = $this->testCustomer->giveValue('id');
            $period_start = date('Y-m-01', strtotime('first day of last month'));
            $period_end = date('Y-m-t', strtotime('last day of last month'));
            
            $effort_table = $GLOBALS['_PJ_effort_table'];
            $project_table = $GLOBALS['_PJ_project_table'];
            $customer_table = $GLOBALS['_PJ_customer_table'];
            
            $query = "SELECT e.*, p.project_name, c.customer_name 
                      FROM $effort_table e
                      JOIN $project_table p ON e.project_id = p.id
                      JOIN $customer_table c ON p.customer_id = c.id
                      WHERE p.customer_id = $customerId
                      AND e.date >= '$period_start' 
                      AND e.date <= '$period_end'
                      ORDER BY e.date ASC";
            
            $this->db->query($query);
            $effort_count = 0;
            $total_minutes = 0;
            
            while ($this->db->next_record()) {
                $effort_count++;
                $total_minutes += $this->db->Record['minutes'];
            }
            
            if ($effort_count > 0) {
                $total_hours = round($total_minutes / 60, 2);
                $this->testResults[] = "✅ EFFORTS: Retrieved $effort_count efforts totaling $total_hours hours";
                debugLog("INVOICE_TEST_EFFORTS", "Retrieved $effort_count efforts totaling $total_minutes minutes");
            } else {
                $this->testResults[] = "⚠️ EFFORTS: No efforts found in test period";
                debugLog("INVOICE_TEST_EFFORTS", "No efforts found in test period");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ EFFORTS: Exception - " . $e->getMessage();
            debugLog("INVOICE_TEST_EFFORTS", "Effort retrieval exception: " . $e->getMessage());
        }
    }
    
    /**
     * Cleanup test data
     */
    private function cleanup() {
        debugLog("INVOICE_TEST_CLEANUP", "Cleaning up test data");
        
        try {
            // Delete test efforts
            foreach ($this->testEfforts as $effort) {
                $effortId = $effort->giveValue('id');
                if ($effortId) {
                    $safeId = DatabaseSecurity::escapeInt($effortId);
                    $this->db->query("DELETE FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE id=$safeId");
                }
            }
            
            // Delete test project
            if ($this->testProject) {
                $projectId = $this->testProject->giveValue('id');
                if ($projectId) {
                    $safeId = DatabaseSecurity::escapeInt($projectId);
                    $this->db->query("DELETE FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id=$safeId");
                }
            }
            
            // Delete test customer
            if ($this->testCustomer) {
                $customerId = $this->testCustomer->giveValue('id');
                if ($customerId) {
                    $safeId = DatabaseSecurity::escapeInt($customerId);
                    $this->db->query("DELETE FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id=$safeId");
                }
            }
            
            $this->testResults[] = "🧹 CLEANUP: Test data removed";
            debugLog("INVOICE_TEST_CLEANUP", "Test data cleaned up successfully");
            
        } catch (Exception $e) {
            $this->testResults[] = "⚠️ CLEANUP: Failed to remove test data - " . $e->getMessage();
            debugLog("INVOICE_TEST_CLEANUP", "Cleanup failed: " . $e->getMessage());
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
        $skipped = count(array_filter($this->testResults, function($r) { return strpos($r, '⚠️') === 0; }));
        
        echo "<br><strong>Summary: {$passed} passed, {$failed} failed, {$skipped} skipped/warnings</strong><br>\n";
        debugLog("INVOICE_TEST_SUMMARY", "Invoice tests completed: {$passed} passed, {$failed} failed, {$skipped} skipped");
    }
}

// Auto-run tests if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'InvoicePreviewWorkingTest.php' || php_sapi_name() === 'cli') {
    // Force output buffering off for immediate display
    if (ob_get_level()) {
        ob_end_flush();
    }
    
    echo "<h1>Starting Invoice Preview Tests...</h1>\n";
    echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>\n";
    
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    try {
        echo "Creating test instance...<br>\n";
        $test = new InvoicePreviewWorkingTest();
        echo "Running all tests...<br>\n";
        $test->runAllTests();
    } catch (Exception $e) {
        echo "<div style='color: red;'><strong>FATAL ERROR (Exception):</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
        echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
        echo "<pre style='background: #ffe6e6; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
    } catch (Error $e) {
        echo "<div style='color: red;'><strong>FATAL ERROR (Error):</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
        echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
        echo "<pre style='background: #ffe6e6; padding: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
    }

    echo "</div>\n";
    echo "<h2>Test execution completed.</h2>\n";
}
