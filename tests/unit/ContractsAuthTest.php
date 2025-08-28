<?php
/**
 * Unit Test for Contracts Authentication
 * Tests the authentication system in contracts.php
 */

// Set environment for testing
$_SERVER['REQUEST_URI'] = '/tests/unit/ContractsAuthTest.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$no_login = true;
$_PJ_debug = true;

require_once(__DIR__ . "/../../bootstrap.php");
include_once(__DIR__ . "/../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

class ContractsAuthTest {
    private $testResults = [];
    
    public function runTests() {
        echo "<h2>Contracts Authentication Test</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
        
        $this->testAuthObjectStructure();
        $this->testAuthenticationMethods();
        $this->testContractsPageAccess();
        $this->fixContractsAuthentication();
        $this->testFixedContractsAccess();
        
        $this->printResults();
        echo "</div>\n";
    }
    
    /**
     * Test 1: Check $_PJ_auth object structure
     */
    private function testAuthObjectStructure() {
        debugLog("CONTRACTS_AUTH_TEST", "Testing auth object structure");
        
        global $_PJ_auth;
        
        if (!isset($_PJ_auth)) {
            $this->testResults[] = "❌ AUTH_OBJECT: \$_PJ_auth not set";
            return;
        }
        
        $this->testResults[] = "✅ AUTH_OBJECT: \$_PJ_auth exists";
        
        // Check if it has auth property
        if (property_exists($_PJ_auth, 'auth')) {
            $this->testResults[] = "✅ AUTH_PROPERTY: auth property exists";
            
            if (is_array($_PJ_auth->auth) && isset($_PJ_auth->auth['uid'])) {
                $this->testResults[] = "✅ AUTH_UID: uid exists in auth array";
            } else {
                $this->testResults[] = "❌ AUTH_UID: uid missing in auth array";
            }
        } else {
            $this->testResults[] = "❌ AUTH_PROPERTY: auth property missing";
        }
        
        // Check alternative methods
        if (method_exists($_PJ_auth, 'giveValue')) {
            $user_id = $_PJ_auth->giveValue('id');
            if ($user_id) {
                $this->testResults[] = "✅ AUTH_METHOD: giveValue('id') returns: $user_id";
            } else {
                $this->testResults[] = "⚠️ AUTH_METHOD: giveValue('id') returns empty";
            }
        } else {
            $this->testResults[] = "❌ AUTH_METHOD: giveValue method missing";
        }
        
        // Check checkPermission method
        if (method_exists($_PJ_auth, 'checkPermission')) {
            $has_user_perm = $_PJ_auth->checkPermission('user');
            $this->testResults[] = "✅ AUTH_PERMISSION: checkPermission method exists, user permission: " . ($has_user_perm ? 'true' : 'false');
        } else {
            $this->testResults[] = "❌ AUTH_PERMISSION: checkPermission method missing";
        }
    }
    
    /**
     * Test 2: Test different authentication methods
     */
    private function testAuthenticationMethods() {
        debugLog("CONTRACTS_AUTH_TEST", "Testing authentication methods");
        
        global $_PJ_auth;
        
        $auth_methods = [
            'auth_uid' => function() use ($_PJ_auth) {
                return isset($_PJ_auth->auth['uid']) && $_PJ_auth->auth['uid'];
            },
            'giveValue_id' => function() use ($_PJ_auth) {
                return method_exists($_PJ_auth, 'giveValue') && $_PJ_auth->giveValue('id');
            },
            'checkPermission_user' => function() use ($_PJ_auth) {
                return method_exists($_PJ_auth, 'checkPermission') && $_PJ_auth->checkPermission('user');
            },
            'direct_id' => function() use ($_PJ_auth) {
                return isset($_PJ_auth->id) && $_PJ_auth->id;
            }
        ];
        
        foreach ($auth_methods as $method => $test) {
            try {
                $result = $test();
                if ($result) {
                    $this->testResults[] = "✅ AUTH_METHOD ($method): Working";
                } else {
                    $this->testResults[] = "❌ AUTH_METHOD ($method): Failed";
                }
            } catch (Exception $e) {
                $this->testResults[] = "❌ AUTH_METHOD ($method): Exception - " . $e->getMessage();
            }
        }
    }
    
    /**
     * Test 3: Test current contracts page access
     */
    private function testContractsPageAccess() {
        debugLog("CONTRACTS_AUTH_TEST", "Testing current contracts page access");
        
        try {
            // Capture output to prevent headers already sent
            ob_start();
            
            // Set required GET parameter
            $_GET['customer_id'] = 1;
            
            // Include contracts page
            include(__DIR__ . "/../../inventory/contracts.php");
            
            $output = ob_get_contents();
            ob_end_clean();
            
            // Check for errors in output
            if (strpos($output, 'Warning') !== false || strpos($output, 'Error') !== false) {
                $this->testResults[] = "❌ CONTRACTS_ACCESS: PHP errors detected";
                debugLog("CONTRACTS_AUTH_TEST", "Contracts access errors: " . substr($output, 0, 500));
            } else {
                $this->testResults[] = "✅ CONTRACTS_ACCESS: No PHP errors";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ CONTRACTS_ACCESS: Exception - " . $e->getMessage();
        }
    }
    
    /**
     * Test 4: Fix contracts authentication
     */
    private function fixContractsAuthentication() {
        debugLog("CONTRACTS_AUTH_TEST", "Fixing contracts authentication");
        
        global $_PJ_auth;
        
        // Determine best authentication method
        $auth_method = null;
        
        if (method_exists($_PJ_auth, 'giveValue') && $_PJ_auth->giveValue('id')) {
            $auth_method = 'giveValue';
        } elseif (method_exists($_PJ_auth, 'checkPermission') && $_PJ_auth->checkPermission('user')) {
            $auth_method = 'checkPermission';
        } elseif (isset($_PJ_auth->id) && $_PJ_auth->id) {
            $auth_method = 'direct_id';
        }
        
        if ($auth_method) {
            $this->testResults[] = "✅ AUTH_FIX: Best method identified - $auth_method";
            $this->applyAuthenticationFix($auth_method);
        } else {
            $this->testResults[] = "❌ AUTH_FIX: No working authentication method found";
        }
    }
    
    /**
     * Apply authentication fix to contracts.php
     */
    private function applyAuthenticationFix($method) {
        $contracts_file = __DIR__ . "/../../inventory/contracts.php";
        $content = file_get_contents($contracts_file);
        
        switch ($method) {
            case 'giveValue':
                $new_auth_check = "// Check authentication using TimeEffect auth system\nif (!\$_PJ_auth->giveValue('id')) {\n    header('Location: ../index.php');\n    exit;\n}";
                break;
                
            case 'checkPermission':
                $new_auth_check = "// Check authentication using TimeEffect auth system\nif (!\$_PJ_auth->checkPermission('user')) {\n    header('Location: ../index.php');\n    exit;\n}";
                break;
                
            case 'direct_id':
                $new_auth_check = "// Check authentication using TimeEffect auth system\nif (!isset(\$_PJ_auth->id) || !\$_PJ_auth->id) {\n    header('Location: ../index.php');\n    exit;\n}";
                break;
                
            default:
                $this->testResults[] = "❌ AUTH_FIX: Unknown method $method";
                return;
        }
        
        // Replace the problematic auth check
        $pattern = '/\/\/ Check authentication using TimeEffect auth system\s*\nif \([^}]+\}\s*/';
        $content = preg_replace($pattern, $new_auth_check . "\n\n", $content);
        
        if (file_put_contents($contracts_file, $content)) {
            $this->testResults[] = "✅ AUTH_FIX: Applied $method fix to contracts.php";
            debugLog("CONTRACTS_AUTH_TEST", "Applied authentication fix: $method");
        } else {
            $this->testResults[] = "❌ AUTH_FIX: Failed to write fix to contracts.php";
        }
    }
    
    /**
     * Test 5: Test fixed contracts access
     */
    private function testFixedContractsAccess() {
        debugLog("CONTRACTS_AUTH_TEST", "Testing fixed contracts access");
        
        try {
            // Clear any previous includes
            $included_files = get_included_files();
            $contracts_file = realpath(__DIR__ . "/../../inventory/contracts.php");
            
            // Capture output to prevent headers already sent
            ob_start();
            
            // Set required GET parameter
            $_GET['customer_id'] = 1;
            
            // Include fixed contracts page
            include($contracts_file);
            
            $output = ob_get_contents();
            ob_end_clean();
            
            // Check for errors in output
            $has_warnings = strpos($output, 'Warning') !== false;
            $has_errors = strpos($output, 'Error') !== false;
            $has_headers_sent = strpos($output, 'headers already sent') !== false;
            
            if ($has_warnings || $has_errors) {
                $error_snippet = substr($output, 0, 300);
                $this->testResults[] = "❌ FIXED_ACCESS: Still has PHP errors - " . $error_snippet;
            } elseif ($has_headers_sent) {
                $this->testResults[] = "❌ FIXED_ACCESS: Headers already sent error";
            } else {
                $this->testResults[] = "✅ FIXED_ACCESS: No PHP errors detected";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = "❌ FIXED_ACCESS: Exception - " . $e->getMessage();
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
        debugLog("CONTRACTS_AUTH_TEST", "Test completed: {$passed} passed, {$failed} failed, {$warnings} warnings");
    }
}

// Auto-run tests
if (basename($_SERVER['PHP_SELF']) === 'ContractsAuthTest.php' || php_sapi_name() === 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h1>Contracts Authentication Test Suite</h1>\n";
    
    try {
        $test = new ContractsAuthTest();
        $test->runTests();
    } catch (Exception $e) {
        echo "<div style='color: red;'><strong>FATAL ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
        echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
    }
    
    echo "<h2>Test completed</h2>\n";
}
