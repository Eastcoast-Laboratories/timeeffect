<?php
// Debug PDF test - demonstrates array output instead of PDF generation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PDF Debug Mode Test ===\n";

// Set required global variable
$_PJ_include_path = __DIR__ . "/../include";

// Minimal bootstrap for testing
$GLOBALS['_PJ_CLI_MODE'] = true;
require_once(__DIR__ . "/../bootstrap.php");
include_once(__DIR__ . "/../include/config.inc.php");
include_once($_PJ_include_path . "/scripts.inc.php");
require_once(__DIR__ . "/../include/invoice.class.php");
require_once(__DIR__ . "/../include/pdf_generator.class.php");

// Mock auth object
$_PJ_auth = new class {
    public $id = 1;
    public function giveValue($key) {
        return 1;
    }
};

try {
    $db = new Database();
    
    // Test 1: Normal PDF generation (should return PDF object)
    echo "\n--- Test 1: Normal PDF Mode ---\n";
    $pdf_generator = new InvoicePDFGenerator($db, 1, false);
    $result = $pdf_generator->generateInvoice(5, false);
    
    if (is_object($result)) {
        echo "✓ Normal mode: PDF object returned\n";
    } else {
        echo "✗ Normal mode failed\n";
    }
    
    // Test 2: Debug mode (should return array)
    echo "\n--- Test 2: Debug Mode ---\n";
    $debug_generator = new InvoicePDFGenerator($db, 1, true);
    $debug_result = $debug_generator->generateInvoice(5, false);
    
    if (is_array($debug_result)) {
        echo "✓ Debug mode: Content array returned\n";
        echo "Available sections: " . implode(", ", array_keys($debug_result['content_sections'])) . "\n";
        echo "Invoice number: " . $debug_result['invoice_data']['invoice_number'] . "\n";
        echo "Customer: " . $debug_result['invoice_data']['customer_name'] . "\n";
        
        echo "\n--- Debug Content Structure ---\n";
        echo "Header content:\n";
        print_r($debug_result['content_sections']['header']);
        
        echo "\nCustomer info:\n";
        print_r($debug_result['content_sections']['customer_info']);
        
        echo "\nInvoice details:\n";
        print_r($debug_result['content_sections']['invoice_details']);
        
        echo "\nSummary:\n";
        print_r($debug_result['content_sections']['summary']);
        
        echo "\nFooter:\n";
        print_r($debug_result['content_sections']['footer']);
        
    } else {
        echo "✗ Debug mode failed - returned: " . gettype($debug_result) . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
