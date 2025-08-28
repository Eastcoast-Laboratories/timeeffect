<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Check if we're in CLI mode for testing - set before bootstrap
if (php_sapi_name() === 'cli' || isset($GLOBALS['_PJ_auth'])) {
    // CLI or test mode - skip normal auth
    $GLOBALS['_PJ_CLI_MODE'] = true;
}

require_once(__DIR__ . "/../bootstrap.php");
include_once(__DIR__ . "/../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');
require_once(__DIR__ . '/../include/invoice.class.php');
require_once(__DIR__ . '/../include/pdf_generator.class.php');

// Clear any unwanted output from includes
ob_end_clean();

// If auth was skipped, ensure we have a valid auth object
if (isset($GLOBALS['_PJ_CLI_MODE']) && $GLOBALS['_PJ_CLI_MODE'] && !isset($_PJ_auth)) {
    $_PJ_auth = new class {
        public $id = 1;
        public $username = 'admin';
        public $permissions = 'admin';
        public $gid = 1;
        public $gids = '1';
        
        public function giveValue($key) {
            return property_exists($this, $key) ? $this->$key : null;
        }
        
        public function checkPermission($permission) {
            return true;
        }
    };
}

$invoice_id = $_GET['id'] ?? 0;
$include_details = isset($_GET['details']) && $_GET['details'] == '1';

if (!$invoice_id) {
    header('Location: index.php');
    exit;
}

// Initialize database and auth
$db = new Database();

// Verify invoice exists and belongs to user
$invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
$invoice_data = $invoice->getInvoice($invoice_id);

if (!$invoice_data) {
    header('Location: index.php');
    exit;
}

try {
    // Generate PDF
    $pdf_generator = new InvoicePDFGenerator($db, $_PJ_auth->giveValue('id'), false);
    $pdf_generator->debug_mode = false; // Debug-Mode VOR generateInvoice() aktivieren
    $pdf_generator->generateInvoice($invoice_id, $include_details);
    
    // Set filename
    $filename = 'Invoice_' . $invoice_data['invoice_number'] . '.pdf';
    $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
    
    // Output PDF
    $pdf_generator->output($filename, 'I'); // 'D' for download, 'I' for inline view
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    debugLog("PDF_ERROR", "PDF Generation Error: " . $e->getMessage());
    
    die('PDF ID ' . $invoice_id . ' failed: ' . $e->getMessage());
    exit;
}
