<?php
// Standalone PDF test without full bootstrap
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PDF Standalone Test ===\n";

// Set required global variable
$_PJ_include_path = __DIR__ . "/../include"; // usually defined in from aperetiv.inc.php

// Test 1: Basic PDF library loading
try {
    require_once(__DIR__ . "/../include/cpdf.inc.php");
    echo "✓ cpdf.inc.php loaded successfully\n";
} catch (Exception $e) {
    echo "✗ cpdf.inc.php failed: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    require_once(__DIR__ . "/../include/pdfreport.inc.php");
    echo "✓ pdfreport.inc.php loaded successfully\n";
} catch (Exception $e) {
    echo "✗ pdfreport.inc.php failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Basic PDF creation
try {
    $pdf = new PDFReport('a4', 'portrait');
    echo "✓ PDFReport object created\n";
    
    // Initialize margins manually if not set
    if (!isset($pdf->ez['leftMargin'])) {
        $pdf->ez['leftMargin'] = 30;
        $pdf->ez['rightMargin'] = 30;
        $pdf->ez['topMargin'] = 30;
        $pdf->ez['bottomMargin'] = 30;
        $pdf->ez['pageWidth'] = 595;
        $pdf->ez['pageHeight'] = 842;
    }
    
    $pdf->ezText("Test PDF Document", 16, array('left' => 0, 'right' => 0));
    $pdf->ezText("This is a test of PDF generation functionality.", 12, array('left' => 0, 'right' => 0));
    echo "✓ Text added to PDF\n";
    
    // Test output capture
    $output = $pdf->ezOutput();
    
    if (strlen($output) > 100) {
        echo "✓ PDF content generated (" . strlen($output) . " bytes)\n";
        
        // Check PDF header
        if (substr($output, 0, 4) === '%PDF') {
            echo "✓ Valid PDF header detected\n";
        } else {
            echo "✗ Invalid PDF header\n";
        }
    } else {
        echo "✗ PDF content too small or empty\n";
    }
    
} catch (Exception $e) {
    echo "✗ PDF creation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: PDF Generator class (minimal test)
try {
    // Mock minimal globals needed
    $GLOBALS['_PJ_table_prefix'] = 'pj_';
    
    // Mock database class
    class MockDatabase {
        public function query($sql) { return true; }
        public function next_record() { return false; }
        public function f($field) { return null; }
    }
    
    require_once(__DIR__ . "/../include/pdf_generator.class.php");
    
    $mockDb = new MockDatabase();
    $pdf_generator = new InvoicePDFGenerator($mockDb, 1);
    echo "✓ InvoicePDFGenerator instantiated\n";
    
} catch (Exception $e) {
    echo "✗ PDF Generator failed: " . $e->getMessage() . "\n";
}

echo "=== Test Complete ===\n";
