<?php
/**
 * Simple Contracts Test - Direct URL access simulation
 */

// Simulate web request environment
$_SERVER['REQUEST_URI'] = '/inventory/contracts.php?customer_id=2';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['customer_id'] = 2;

echo "<h1>Contracts Simple Test</h1>\n";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";

try {
    // Test 1: Check bootstrap loading
    echo "<h3>Test 1: Bootstrap Loading</h3>\n";
    ob_start();
    require_once(__DIR__ . "/../../bootstrap.php");
    $bootstrap_output = ob_get_contents();
    ob_end_clean();
    
    if (empty($bootstrap_output)) {
        echo "✅ Bootstrap loads without output<br>\n";
    } else {
        echo "❌ Bootstrap produces output: " . htmlspecialchars(substr($bootstrap_output, 0, 200)) . "<br>\n";
    }
    
    // Test 2: Check auth object
    echo "<h3>Test 2: Authentication Object</h3>\n";
    global $_PJ_auth;
    
    if (isset($_PJ_auth)) {
        echo "✅ \$_PJ_auth exists<br>\n";
        
        if (method_exists($_PJ_auth, 'giveValue')) {
            $user_id = $_PJ_auth->giveValue('id');
            if ($user_id) {
                echo "✅ giveValue('id') returns: $user_id<br>\n";
            } else {
                echo "❌ giveValue('id') returns empty<br>\n";
            }
        } else {
            echo "❌ giveValue method missing<br>\n";
        }
    } else {
        echo "❌ \$_PJ_auth not set<br>\n";
    }
    
    // Test 3: Load config and scripts
    echo "<h3>Test 3: Config and Scripts Loading</h3>\n";
    ob_start();
    include_once(__DIR__ . "/../../include/config.inc.php");
    include_once($_PJ_include_path . '/scripts.inc.php');
    $config_output = ob_get_contents();
    ob_end_clean();
    
    if (empty($config_output)) {
        echo "✅ Config and scripts load without output<br>\n";
    } else {
        echo "❌ Config/scripts produce output: " . htmlspecialchars(substr($config_output, 0, 200)) . "<br>\n";
    }
    
    // Test 4: Test contracts.php access
    echo "<h3>Test 4: Contracts Page Access</h3>\n";
    
    // Capture all output including headers
    ob_start();
    
    try {
        include(__DIR__ . "/../../inventory/contracts.php");
        $contracts_output = ob_get_contents();
    } catch (Exception $e) {
        $contracts_output = "Exception: " . $e->getMessage();
    } catch (Error $e) {
        $contracts_output = "Error: " . $e->getMessage();
    }
    
    ob_end_clean();
    
    // Analyze output
    $has_warnings = strpos($contracts_output, 'Warning') !== false;
    $has_errors = strpos($contracts_output, 'Error') !== false;
    $has_headers_sent = strpos($contracts_output, 'headers already sent') !== false;
    $has_undefined = strpos($contracts_output, 'Undefined') !== false;
    
    if ($has_warnings || $has_errors || $has_headers_sent || $has_undefined) {
        echo "❌ Contracts page has issues:<br>\n";
        
        // Extract first 500 chars of errors
        $error_lines = explode("\n", $contracts_output);
        $error_count = 0;
        foreach ($error_lines as $line) {
            if ((strpos($line, 'Warning') !== false || 
                 strpos($line, 'Error') !== false || 
                 strpos($line, 'Undefined') !== false) && 
                $error_count < 5) {
                echo "&nbsp;&nbsp;• " . htmlspecialchars(trim($line)) . "<br>\n";
                $error_count++;
            }
        }
        
        if ($error_count >= 5) {
            echo "&nbsp;&nbsp;• ... (more errors)<br>\n";
        }
    } else {
        echo "✅ Contracts page loads without PHP errors<br>\n";
    }
    
    // Test 5: Check if page redirects (Location header)
    if (strpos($contracts_output, 'Location:') !== false) {
        echo "⚠️ Page attempts redirect (authentication failed)<br>\n";
    } else {
        echo "✅ No redirect detected<br>\n";
    }
    
    // Test 6: Check for actual content
    if (strlen($contracts_output) > 100 && !$has_errors && !$has_warnings) {
        echo "✅ Page generates content (" . strlen($contracts_output) . " bytes)<br>\n";
    } elseif (strlen($contracts_output) < 100) {
        echo "⚠️ Page generates minimal content (" . strlen($contracts_output) . " bytes)<br>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'><strong>FATAL ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
    echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
} catch (Error $e) {
    echo "<div style='color: red;'><strong>FATAL ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
    echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
}

echo "</div>\n";
echo "<h2>Test completed</h2>\n";
