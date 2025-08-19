<?php
/**
 * Unit Test for SBE Parameter Functionality
 * Tests the show billed entries (sbe) parameter in efforts.php
 */

// Set CLI environment variables to prevent bootstrap errors
$_SERVER['REQUEST_URI'] = '/tests/unit/SbeParameterTest.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';

require_once(__DIR__ . "/../../include/config.inc.php");
require_once(__DIR__ . "/../../include/effort.inc.php");
require_once(__DIR__ . "/../../include/project.inc.php");
require_once(__DIR__ . "/../../include/customer.inc.php");

class SbeParameterTest {
    private $testResults = [];
    private $testCustomer = null;
    private $testProject = null;
    private $testEfforts = [];
    private $testAuth = null;
    
    public function __construct() {
        // Enable debug logging for tests
        $GLOBALS['_PJ_debug'] = true;
        debugLog("TEST_INIT", "Starting SBE Parameter Tests");
        
        // Create test auth user (admin for full access)
        $this->testAuth = $this->createTestAuth();
    }
    
    /**
     * Create a test auth object with admin permissions
     */
    private function createTestAuth() {
        // Mock auth object with admin permissions
        $auth = new stdClass();
        $auth->id = 1;
        $auth->username = 'test_admin';
        $auth->permissions = 'admin';
        $auth->gid = 1;
        
        // Add required methods
        $auth->giveValue = function($key) use ($auth) {
            return isset($auth->$key) ? $auth->$key : null;
        };
        
        $auth->checkPermission = function($permission) {
            return $permission === 'admin' || $permission === 'agent';
        };
        
        return $auth;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "<h2>SBE Parameter Unit Tests</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
        
        $this->setupTestData();
        $this->testSbeParameterOff();
        $this->testSbeParameterOn();
        $this->testSbeWithInvalidProject();
        $this->testSbeWithValidProject();
        $this->cleanup();
        
        $this->printResults();
        echo "</div>\n";
    }
    
    /**
     * Setup test data: customer, project, and efforts (billed and unbilled)
     */
    private function setupTestData() {
        debugLog("TEST_SETUP", "Creating test customer, project, and efforts");
        
        try {
            // Create test customer
            $customerData = [
                'customer_name' => 'SBE Test Customer ' . time(),
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----'
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
                'project_name' => 'SBE Test Project ' . time(),
                'customer_id' => $customerId,
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----'
            ];
            
            $this->testProject = new Project($this->testCustomer, $this->testAuth, $projectData);
            $projectResult = $this->testProject->save();
            
            if (!empty($projectResult)) {
                $this->testResults[] = "❌ SETUP: Failed to create test project - " . $projectResult;
                return;
            }
            
            $projectId = $this->testProject->giveValue('id');
            
            // Create test efforts (both billed and unbilled)
            $effortData1 = [
                'project_id' => $projectId,
                'date' => date('Y-m-d'),
                'begin' => '09:00:00',
                'end' => '10:00:00',
                'description' => 'Unbilled test effort',
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----',
                'billed' => null // Unbilled
            ];
            
            $effortData2 = [
                'project_id' => $projectId,
                'date' => date('Y-m-d'),
                'begin' => '10:00:00',
                'end' => '11:00:00',
                'description' => 'Billed test effort',
                'user' => 1,
                'gid' => 1,
                'access' => 'rwxr-----',
                'billed' => date('Y-m-d') // Billed
            ];
            
            $effort1 = new Effort($effortData1, $this->testAuth);
            $effort2 = new Effort($effortData2, $this->testAuth);
            
            $result1 = $effort1->save();
            $result2 = $effort2->save();
            
            if (empty($result1) && empty($result2)) {
                $this->testEfforts[] = $effort1;
                $this->testEfforts[] = $effort2;
                $this->testResults[] = "✅ SETUP: Test data created successfully";
                debugLog("TEST_SETUP", "Created customer ID: $customerId, project ID: $projectId");
            } else {
                $this->testResults[] = "❌ SETUP: Failed to create test efforts - $result1 $result2";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ SETUP: Exception - " . $e->getMessage();
            debugLog("TEST_SETUP", "Setup exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 1: SBE Parameter Off (sbe=0 or not set) - Should show only unbilled entries
     */
    private function testSbeParameterOff() {
        debugLog("TEST_SBE_OFF", "Testing sbe=0 (show only unbilled entries)");
        
        if (!$this->testProject) {
            $this->testResults[] = "⚠️ SBE OFF: SKIPPED - No test project available";
            return;
        }
        
        try {
            // Test with sbe=false (show only unbilled)
            $effortList = new EffortList($this->testCustomer, $this->testProject, $this->testAuth, false);
            
            $unbilledCount = 0;
            $billedCount = 0;
            
            while ($effortList->nextEffort()) {
                $effort = $effortList->giveEffort();
                $billed = $effort->giveValue('billed');
                
                if (empty($billed) || $billed === '0000-00-00') {
                    $unbilledCount++;
                } else {
                    $billedCount++;
                }
            }
            
            if ($unbilledCount > 0 && $billedCount === 0) {
                $this->testResults[] = "✅ SBE OFF: SUCCESS - Shows only unbilled entries ($unbilledCount unbilled, $billedCount billed)";
                debugLog("TEST_SBE_OFF", "Correctly shows only unbilled entries");
            } else {
                $this->testResults[] = "❌ SBE OFF: FAILED - Shows billed entries when sbe=0 ($unbilledCount unbilled, $billedCount billed)";
                debugLog("TEST_SBE_OFF", "Incorrectly shows billed entries when sbe=0");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ SBE OFF: EXCEPTION - " . $e->getMessage();
            debugLog("TEST_SBE_OFF", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 2: SBE Parameter On (sbe=1) - Should show both billed and unbilled entries
     */
    private function testSbeParameterOn() {
        debugLog("TEST_SBE_ON", "Testing sbe=1 (show both billed and unbilled entries)");
        
        if (!$this->testProject) {
            $this->testResults[] = "⚠️ SBE ON: SKIPPED - No test project available";
            return;
        }
        
        try {
            // Test with sbe=true (show both billed and unbilled)
            $effortList = new EffortList($this->testCustomer, $this->testProject, $this->testAuth, true);
            
            $unbilledCount = 0;
            $billedCount = 0;
            
            while ($effortList->nextEffort()) {
                $effort = $effortList->giveEffort();
                $billed = $effort->giveValue('billed');
                
                if (empty($billed) || $billed === '0000-00-00') {
                    $unbilledCount++;
                } else {
                    $billedCount++;
                }
            }
            
            if ($unbilledCount > 0 && $billedCount > 0) {
                $this->testResults[] = "✅ SBE ON: SUCCESS - Shows both billed and unbilled entries ($unbilledCount unbilled, $billedCount billed)";
                debugLog("TEST_SBE_ON", "Correctly shows both billed and unbilled entries");
            } else {
                $this->testResults[] = "❌ SBE ON: FAILED - Doesn't show both types ($unbilledCount unbilled, $billedCount billed)";
                debugLog("TEST_SBE_ON", "Failed to show both billed and unbilled entries");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ SBE ON: EXCEPTION - " . $e->getMessage();
            debugLog("TEST_SBE_ON", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 3: SBE with Invalid Project - Should show no entries, not all entries
     */
    private function testSbeWithInvalidProject() {
        debugLog("TEST_SBE_INVALID_PROJECT", "Testing sbe=1 with invalid project ID");
        
        try {
            // Create invalid project object (non-existent ID)
            $invalidProject = new Project($this->testCustomer, $this->testAuth, 99999);
            
            // Test with sbe=true and invalid project
            $effortList = new EffortList($this->testCustomer, $invalidProject, $this->testAuth, true);
            
            $totalCount = 0;
            while ($effortList->nextEffort()) {
                $totalCount++;
            }
            
            if ($totalCount === 0) {
                $this->testResults[] = "✅ SBE INVALID PROJECT: SUCCESS - Shows no entries for invalid project";
                debugLog("TEST_SBE_INVALID_PROJECT", "Correctly shows no entries for invalid project");
            } else {
                $this->testResults[] = "❌ SBE INVALID PROJECT: FAILED - Shows $totalCount entries for invalid project (should be 0)";
                debugLog("TEST_SBE_INVALID_PROJECT", "Incorrectly shows entries for invalid project");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ SBE INVALID PROJECT: EXCEPTION - " . $e->getMessage();
            debugLog("TEST_SBE_INVALID_PROJECT", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 4: SBE with Valid Project - Should show only entries from that project
     */
    private function testSbeWithValidProject() {
        debugLog("TEST_SBE_VALID_PROJECT", "Testing sbe=1 with valid project ID (project filtering)");
        
        if (!$this->testProject) {
            $this->testResults[] = "⚠️ SBE VALID PROJECT: SKIPPED - No test project available";
            return;
        }
        
        try {
            // Test with sbe=true and valid project
            $effortList = new EffortList($this->testCustomer, $this->testProject, $this->testAuth, true);
            
            $projectId = $this->testProject->giveValue('id');
            $correctProjectCount = 0;
            $wrongProjectCount = 0;
            
            while ($effortList->nextEffort()) {
                $effort = $effortList->giveEffort();
                $effortProjectId = $effort->giveValue('project_id');
                
                if ($effortProjectId == $projectId) {
                    $correctProjectCount++;
                } else {
                    $wrongProjectCount++;
                }
            }
            
            if ($correctProjectCount > 0 && $wrongProjectCount === 0) {
                $this->testResults[] = "✅ SBE VALID PROJECT: SUCCESS - Shows only entries from specified project ($correctProjectCount entries)";
                debugLog("TEST_SBE_VALID_PROJECT", "Correctly filters by project");
            } else {
                $this->testResults[] = "❌ SBE VALID PROJECT: FAILED - Shows entries from wrong projects ($correctProjectCount correct, $wrongProjectCount wrong)";
                debugLog("TEST_SBE_VALID_PROJECT", "Failed to filter by project correctly");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ SBE VALID PROJECT: EXCEPTION - " . $e->getMessage();
            debugLog("TEST_SBE_VALID_PROJECT", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Cleanup test data
     */
    private function cleanup() {
        debugLog("TEST_CLEANUP", "Cleaning up test data");
        
        try {
            $db = new Database();
            $db->connect();
            
            // Delete test efforts
            foreach ($this->testEfforts as $effort) {
                $effortId = $effort->giveValue('id');
                if ($effortId) {
                    $safeId = DatabaseSecurity::escapeInt($effortId);
                    $db->query("DELETE FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE id=$safeId");
                }
            }
            
            // Delete test project
            if ($this->testProject) {
                $projectId = $this->testProject->giveValue('id');
                if ($projectId) {
                    $safeId = DatabaseSecurity::escapeInt($projectId);
                    $db->query("DELETE FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id=$safeId");
                }
            }
            
            // Delete test customer
            if ($this->testCustomer) {
                $customerId = $this->testCustomer->giveValue('id');
                if ($customerId) {
                    $safeId = DatabaseSecurity::escapeInt($customerId);
                    $db->query("DELETE FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id=$safeId");
                }
            }
            
            $this->testResults[] = "🧹 CLEANUP: Test data removed";
            debugLog("TEST_CLEANUP", "Test data cleaned up successfully");
            
        } catch (Exception $e) {
            $this->testResults[] = "⚠️ CLEANUP: Failed to remove test data - " . $e->getMessage();
            debugLog("TEST_CLEANUP", "Cleanup failed: " . $e->getMessage());
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
        debugLog("TEST_SUMMARY", "SBE tests completed: {$passed} passed, {$failed} failed, {$skipped} skipped");
    }
}

// Auto-run tests if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'SbeParameterTest.php') {
    $test = new SbeParameterTest();
    $test->runAllTests();
}
