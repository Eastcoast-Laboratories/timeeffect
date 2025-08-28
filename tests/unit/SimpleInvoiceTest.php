<?php
/**
 * Simple Invoice Test - minimal dependencies
 */

// Set environment
$_SERVER['REQUEST_URI'] = '/tests/unit/SimpleInvoiceTest.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$no_login = true;
$_PJ_debug = true;

// Basic includes only
require_once(__DIR__ . "/../../bootstrap.php");
include_once(__DIR__ . "/../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

class SimpleInvoiceTest {
    private $db = null;
    
    public function __construct() {
        echo "<h2>Simple Invoice Test</h2>\n";
        
        // Create auth mock
        global $_PJ_auth;
        $_PJ_auth = new stdClass();
        $_PJ_auth->id = 1;
        $_PJ_auth->gid = 1;
        $_PJ_auth->gids = '1';
        $_PJ_auth->username = 'test_admin';
        $_PJ_auth->permissions = 'admin';
        
        $_PJ_auth->giveValue = function($key) use ($_PJ_auth) {
            return property_exists($_PJ_auth, $key) ? $_PJ_auth->$key : null;
        };
        
        $_PJ_auth->checkPermission = function($permission) {
            return $permission === 'admin' || $permission === 'agent';
        };
        
        $this->db = new Database();
        echo "✅ Database connection established<br>\n";
    }
    
    public function testDatabaseConnection() {
        echo "<h3>Testing Database Connection</h3>\n";
        
        try {
            $query = "SELECT 1 as test";
            $this->db->query($query);
            if ($this->db->next_record()) {
                $result = $this->db->f('test');
                if ($result == 1) {
                    echo "✅ Database query successful<br>\n";
                    return true;
                } else {
                    echo "❌ Database query returned unexpected result: $result<br>\n";
                    return false;
                }
            } else {
                echo "❌ Database query returned no results<br>\n";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ Database error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
            return false;
        }
    }
    
    public function testInvoiceTables() {
        echo "<h3>Testing Invoice Tables</h3>\n";
        
        $prefix = $GLOBALS['_PJ_table_prefix'];
        $tables = ['invoices', 'invoice_items', 'invoice_efforts', 'invoice_payments', 'payment_reminders'];
        
        $allExist = true;
        foreach ($tables as $table) {
            $fullTableName = $prefix . $table;
            try {
                $query = "SHOW TABLES LIKE '$fullTableName'";
                $this->db->query($query);
                if ($this->db->next_record()) {
                    echo "✅ Table $fullTableName exists<br>\n";
                } else {
                    echo "❌ Table $fullTableName missing<br>\n";
                    $allExist = false;
                }
            } catch (Exception $e) {
                echo "❌ Error checking table $fullTableName: " . htmlspecialchars($e->getMessage()) . "<br>\n";
                $allExist = false;
            }
        }
        
        return $allExist;
    }
    
    public function testInvoiceClass() {
        echo "<h3>Testing Invoice Class</h3>\n";
        
        try {
            require_once(__DIR__ . "/../../include/invoice.class.php");
            $invoice = new Invoice($this->db, 1);
            echo "✅ Invoice class instantiated successfully<br>\n";
            return true;
        } catch (Exception $e) {
            echo "❌ Invoice class error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
            return false;
        } catch (Error $e) {
            echo "❌ Invoice class fatal error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
            return false;
        }
    }
    
    public function testPDFGeneration() {
        echo "<h3>Testing PDF Generation</h3>\n";
        
        try {
            // Set required global variable for PDF classes
            global $_PJ_include_path;
            if (!isset($_PJ_include_path)) {
                $_PJ_include_path = __DIR__ . "/../../include"; // usually defined in from aperetiv.inc.php
            }
            
            // Load PDF classes
            require_once(__DIR__ . "/../../include/cpdf.inc.php");
            require_once(__DIR__ . "/../../include/pdfreport.inc.php");
            require_once(__DIR__ . "/../../include/pdf_generator.class.php");
            echo "✅ PDF classes loaded<br>\n";
            
            // Test InvoicePDFGenerator with required parameters
            try {
                $db = new Database();
                $generator = new InvoicePDFGenerator($db, 1);
                echo "✅ InvoicePDFGenerator instantiated<br>\n";
                
                // Test basic PDF creation without generating actual invoice
                $pdf = new PDFReport('a4', 'portrait');
                echo "✅ PDFReport created successfully<br>\n";
                
                return true;
                
            } catch (Exception $e) {
                echo "❌ PDF generation error: " . $e->getMessage() . "<br>\n";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ PDF generation error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
            return false;
        } catch (Error $e) {
            echo "❌ PDF generation fatal error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
            return false;
        }
    }
    
    public function runAllTests() {
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>\n";
        
        $tests = [
            'Database Connection' => [$this, 'testDatabaseConnection'],
            'Invoice Tables' => [$this, 'testInvoiceTables'],
            'Invoice Class' => [$this, 'testInvoiceClass'],
            'PDF Generation' => [$this, 'testPDFGeneration']
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $testName => $testMethod) {
            echo "<hr><h4>Running: $testName</h4>\n";
            try {
                if (call_user_func($testMethod)) {
                    $passed++;
                    echo "<div style='color: green;'>✅ $testName PASSED</div>\n";
                } else {
                    $failed++;
                    echo "<div style='color: red;'>❌ $testName FAILED</div>\n";
                }
            } catch (Exception $e) {
                $failed++;
                echo "<div style='color: red;'>❌ $testName EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</div>\n";
            } catch (Error $e) {
                $failed++;
                echo "<div style='color: red;'>❌ $testName ERROR: " . htmlspecialchars($e->getMessage()) . "</div>\n";
            }
            flush();
        }
        
        echo "<hr><h3>Summary</h3>\n";
        echo "<strong>Passed: $passed, Failed: $failed</strong><br>\n";
        echo "</div>\n";
    }
}

// Auto-run tests
if (basename($_SERVER['PHP_SELF']) === 'SimpleInvoiceTest.php' || php_sapi_name() === 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h1>Simple Invoice Test Suite</h1>\n";
    
    try {
        $test = new SimpleInvoiceTest();
        $test->runAllTests();
    } catch (Exception $e) {
        echo "<div style='color: red;'><strong>FATAL ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
        echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
    } catch (Error $e) {
        echo "<div style='color: red;'><strong>FATAL ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</div>\n";
        echo "<div><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " <strong>Line:</strong> " . $e->getLine() . "</div>\n";
    }
    
    echo "<h2>Test completed</h2>\n";
}
