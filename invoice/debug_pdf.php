<?php
// Debug PDF endpoint - demonstrates array output instead of PDF generation
require_once(__DIR__ . '/../bootstrap.php');
include_once(__DIR__ . '/../include/config.inc.php');
include_once($_PJ_include_path . '/scripts.inc.php');
require_once(__DIR__ . '/../include/invoice.class.php');
require_once(__DIR__ . '/../include/pdf_generator.class.php');

// Check authentication
if (!$_PJ_auth->auth['uid']) {
    header('Location: /');
    exit;
}

// Get invoice ID
$invoice_id = intval($_GET['id'] ?? 0);
if (!$invoice_id) {
    die('Invoice ID required');
}

// Get include details flag
$include_details = isset($_GET['details']) && $_GET['details'] == '1';

// Initialize database
$db = new Database();

// Verify invoice exists and belongs to user
$invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
$invoice_data = $invoice->getInvoice($invoice_id);

if (!$invoice_data) {
    die('Invoice not found');
}

try {
    // Generate debug content (array instead of PDF)
    $pdf_generator = new InvoicePDFGenerator($db, $_PJ_auth->giveValue('id'), true);
    $debug_content = $pdf_generator->generateInvoice($invoice_id, $include_details);
    
    if (!$debug_content) {
        throw new Exception('Failed to generate debug content');
    }
    
    // Output debug content as formatted JSON
    $pdf_generator->output('debug_invoice_' . $invoice_data['invoice_number'] . '.json', 'I');
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    debugLog("PDF_DEBUG_ERROR", "PDF Debug Generation Error: " . $e->getMessage());
    
    die('Debug content generation failed: ' . $e->getMessage());
}
