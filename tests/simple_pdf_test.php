<?php
// Minimal test to check PDF generation without headers
echo "Testing PDF generation components...\n";

// Test basic PHP file loading
try {
    require_once(__DIR__ . "/../include/cpdf.inc.php");
    echo "✓ cpdf.inc.php loaded\n";
} catch (Exception $e) {
    echo "✗ cpdf.inc.php failed: " . $e->getMessage() . "\n";
}

try {
    require_once(__DIR__ . "/../include/pdfreport.inc.php");
    echo "✓ pdfreport.inc.php loaded\n";
} catch (Exception $e) {
    echo "✗ pdfreport.inc.php failed: " . $e->getMessage() . "\n";
}

// Test PDF creation without database
try {
    $pdf = new PDFReport();
    $pdf->ezText("Test PDF", 12);
    echo "✓ PDF object created and text added\n";
    
    // Test output capture
    ob_start();
    $output = $pdf->ezOutput();
    $pdf_content = ob_get_contents();
    ob_end_clean();
    
    if (strlen($pdf_content) > 0) {
        echo "✓ PDF content generated (" . strlen($pdf_content) . " bytes)\n";
    } else {
        echo "✗ No PDF content generated\n";
    }
    
} catch (Exception $e) {
    echo "✗ PDF creation failed: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
