<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once("../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');
require_once('../include/invoice.class.php');
require_once('../include/payment.class.php');

$invoice_id = $_GET['id'] ?? 0;

if (!$invoice_id) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
$payment = new PaymentManager($db, $_PJ_auth->giveValue('id'));

$invoice_data = $invoice->getInvoice($invoice_id);
if (!$invoice_data) {
    header('Location: index.php');
    exit;
}

$invoice_items = $invoice->getInvoiceItems($invoice_id);
$invoice_efforts = $invoice->getInvoiceEfforts($invoice_id);
$payments = $payment->getInvoicePayments($invoice_id);

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $new_status = $_POST['status'] ?? '';
        if (in_array($new_status, ['draft', 'sent', 'paid', 'cancelled'])) {
            $update_data = $invoice_data;
            $update_data['status'] = $new_status;
            $invoice->updateInvoice($invoice_id, $update_data);
            
            // Refresh data
            $invoice_data = $invoice->getInvoice($invoice_id);
        }
    } elseif ($_POST['action'] === 'add_payment') {
        $amount = floatval($_POST['payment_amount'] ?? 0);
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $payment_method = $_POST['payment_method'] ?? '';
        $notes = $_POST['payment_notes'] ?? '';
        
        if ($amount > 0) {
            $payment->addPayment($invoice_id, $amount, $payment_date, $payment_method, $notes);
            // Refresh data
            $payments = $payment->getInvoicePayments($invoice_id);
            $invoice_data = $invoice->getInvoice($invoice_id);
        }
    }
}


// Set up template variables for unified layout
$center_template = "invoice/view";
$center_title = (!empty($GLOBALS['_PJ_strings']['invoice']) ? $GLOBALS['_PJ_strings']['invoice'] : 'Invoice') . ' ' . $invoice_data['invoice_number'];

include("$_PJ_root/templates/list.ihtml.php");
