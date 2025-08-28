<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once("../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');
require_once('../include/invoice.class.php');
require_once('../include/contract.class.php');

$invoice_id = $_GET['id'] ?? 0;

if (!$invoice_id) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
$contract = new Contract($db, $_PJ_auth->giveValue('id'));
$carryover = new Carryover($db, $_PJ_auth->giveValue('id'));

$invoice_data = $invoice->getInvoice($invoice_id);
if (!$invoice_data || $invoice_data['status'] !== 'draft') {
    header('Location: view.php?id=' . $invoice_id);
    exit;
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $project_id = $_POST['project_id'] ?? null;
    $invoice_date = $_POST['invoice_date'] ?? '';
    $period_start = $_POST['period_start'] ?? '';
    $period_end = $_POST['period_end'] ?? '';
    $description = $_POST['description'] ?? '';
    $total_hours = floatval($_POST['total_hours'] ?? 0);
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $vat_rate = floatval($_POST['vat_rate'] ?? 19.00);
    
    // Validation
    if (empty($customer_id)) {
        $errors[] = !empty($GLOBALS['_PJ_strings']['customer_required']) ? $GLOBALS['_PJ_strings']['customer_required'] : 'Customer is required';
    }
    if (empty($invoice_date)) {
        $errors[] = !empty($GLOBALS['_PJ_strings']['invoice_date_required']) ? $GLOBALS['_PJ_strings']['invoice_date_required'] : 'Invoice date is required';
    }
    if (empty($period_start)) {
        $errors[] = !empty($GLOBALS['_PJ_strings']['period_start_required']) ? $GLOBALS['_PJ_strings']['period_start_required'] : 'Period start is required';
    }
    if (empty($period_end)) {
        $errors[] = !empty($GLOBALS['_PJ_strings']['period_end_required']) ? $GLOBALS['_PJ_strings']['period_end_required'] : 'Period end is required';
    }
    if ($total_amount <= 0) {
        $errors[] = !empty($GLOBALS['_PJ_strings']['total_amount_greater_zero']) ? $GLOBALS['_PJ_strings']['total_amount_greater_zero'] : 'Total amount must be greater than 0';
    }
    
    if (empty($errors)) {
        // Calculate totals
        $totals = $invoice->calculateInvoiceTotals($total_amount, $vat_rate);
        
        $update_data = [
            'customer_id' => $customer_id,
            'project_id' => $project_id,
            'invoice_date' => $invoice_date,
            'period_start' => $period_start,
            'period_end' => $period_end,
            'contract_type' => $invoice_data['contract_type'],
            'fixed_amount' => $invoice_data['fixed_amount'],
            'fixed_hours' => $invoice_data['fixed_hours'],
            'total_hours' => $total_hours,
            'total_amount' => $totals['net_amount'],
            'vat_rate' => $vat_rate,
            'vat_amount' => $totals['vat_amount'],
            'gross_amount' => $totals['gross_amount'],
            'carryover_previous' => $invoice_data['carryover_previous'],
            'carryover_current' => $invoice_data['carryover_current'],
            'description' => $description,
            'status' => $invoice_data['status']
        ];
        
        if ($invoice->updateInvoice($invoice_id, $update_data)) {
            $success = true;
            header("Location: view.php?id={$invoice_id}");
            exit;
        } else {
            $errors[] = !empty($GLOBALS['_PJ_strings']['failed_update_invoice']) ? $GLOBALS['_PJ_strings']['failed_update_invoice'] : 'Failed to update invoice';
        }
    }
}

// Get customers for dropdown
$customers_query = "SELECT id, name FROM " . $GLOBALS['_PJ_customer_table'] . " ORDER BY name";
$db->query($customers_query);
$customers = [];
while ($db->next_record()) {
    $customers[] = $db->Record;
}

// Get projects for dropdown
$projects_query = "SELECT id, customer_id, name FROM " . $GLOBALS['_PJ_project_table'] . " ORDER BY name";
$db->query($projects_query);
$projects = [];
while ($db->next_record()) {
    $projects[] = $db->Record;
}

// Set up template variables for unified layout
$center_template = "invoice/edit_form";
$center_title = (!empty($GLOBALS['_PJ_strings']['edit_invoice']) ? $GLOBALS['_PJ_strings']['edit_invoice'] : 'Edit Invoice') . ' ' . $invoice_data['invoice_number'];

include("$_PJ_root/templates/list.ihtml.php");
