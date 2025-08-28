<?php
// Skip authentication for direct access
$no_login = true;

require_once(__DIR__ . "/../bootstrap.php");
include_once(__DIR__ . "/../include/config.inc.php");
include_once(__DIR__ . "/../include/scripts.inc.php");
require_once(__DIR__ . "/../include/invoice.class.php");
require_once(__DIR__ . "/../include/contract.class.php");
require_once(__DIR__ . "/../include/carryover.class.php");

$db = new Database();

// Check if auth is available, otherwise use default user ID 1
$user_id = (isset($_PJ_auth) && $_PJ_auth) ? $_PJ_auth->giveValue('id') : 1;

$invoice = new Invoice($db, $user_id);
$contract = new Contract($db, $user_id);
$carryover = new Carryover($db, $user_id);

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $project_id = $_POST['project_id'] ?? null;
    $period_start = $_POST['period_start'] ?? '';
    $period_end = $_POST['period_end'] ?? '';
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $description = $_POST['description'] ?? '';
    $generate_type = $_POST['generate_type'] ?? 'manual';
    $mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
    
    // Validation
    if (empty($customer_id)) {
        $errors[] = 'Customer is required';
    }
    if (empty($period_start)) {
        $errors[] = 'Period start is required';
    }
    if (empty($period_end)) {
        $errors[] = 'Period end is required';
    }
    
    if (empty($errors)) {
        if ($generate_type === 'fixed_contract') {
            // Generate from fixed contract
            $invoice_id = $invoice->generateFixedContractInvoice($customer_id, $project_id, $period_start, $period_end);
            if ($invoice_id) {
                $success = true;
                header("Location: view.php?id={$invoice_id}");
                exit;
            } else {
                $errors[] = 'Failed to generate invoice from contract. Please check if an active fixed contract exists.';
            }
        } else {
            // Manual invoice creation
            $safe_period_start = addslashes($period_start);
            $safe_period_end = addslashes($period_end);
            $efforts_query = "SELECT e.* FROM " . $GLOBALS['_PJ_effort_table'] . " e
                             JOIN " . $GLOBALS['_PJ_project_table'] . " p ON e.project_id = p.id
                             WHERE p.customer_id = " . intval($customer_id) . " 
                             AND e.date >= '" . $safe_period_start . "' 
                             AND e.date <= '" . $safe_period_end . "'";
            
            // Filter by billed status based on mode
            if ($mode === 'billed') {
                $efforts_query .= " AND e.billed IS NOT NULL AND e.billed != ''";
            } else {
                // Default: exclude already billed efforts
                $efforts_query .= " AND (e.billed IS NULL OR e.billed = '')";
            }
            
            if ($project_id) {
                $efforts_query .= " AND e.project_id = " . intval($project_id);
            }
            
            $db->query($efforts_query);
            $efforts = [];
            while ($db->next_record()) {
                $efforts[] = $db->Record;
            }
            
            // Calculate total hours from begin/end time difference
            $total_minutes = 0;
            foreach ($efforts as $effort) {
                $begin_time = strtotime($effort['begin']);
                $end_time = strtotime($effort['end']);
                $minutes = ($end_time - $begin_time) / 60;
                $total_minutes += $minutes;
            }
            $total_hours = round($total_minutes / 60, 2);
            
            // Get hourly rate from contract or default
            $activeContract = $contract->getActiveContract($customer_id, $project_id);
            $hourly_rate = $activeContract['hourly_rate'] ?? 50.00; // Default rate
            
            $net_amount = $total_hours * $hourly_rate;
            
            // Get user's default VAT rate
            $vat_query = "SELECT default_vat_rate FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id = " . intval($user_id);
            $db->query($vat_query);
            $vat_rate = 19.00;
            if ($db->next_record()) {
                $vat_rate = $db->f('default_vat_rate') ?: 19.00;
            }
            
            $totals = $invoice->calculateInvoiceTotals($net_amount, $vat_rate);
            
            // Add debug logging for hourly invoice creation
            $GLOBALS['_PJ_debug'] = true;
            debugLog('INVOICE_CREATE', 'Hourly invoice - Total hours: ' . $total_hours . ', Rate: ' . $hourly_rate . ', Net amount: ' . $net_amount);
            debugLog('INVOICE_CREATE', 'VAT rate: ' . $vat_rate . ', Totals: ' . json_encode($totals));
            
            $invoice_data = [
                'invoice_number' => $invoice->generateInvoiceNumber($user_id),
                'customer_id' => $customer_id,
                'project_id' => $project_id,
                'invoice_date' => $invoice_date,
                'period_start' => $period_start,
                'period_end' => $period_end,
                'contract_type' => 'hourly',
                'fixed_amount' => null,
                'fixed_hours' => null,
                'total_hours' => $total_hours,
                'total_amount' => $totals['net_amount'],
                'vat_rate' => $vat_rate,
                'vat_amount' => $totals['vat_amount'],
                'gross_amount' => $totals['gross_amount'],
                'carryover_previous' => 0,
                'carryover_current' => 0,
                'description' => $description ?: "Services for period " . date('m/Y', strtotime($period_start)),
                'status' => 'draft'
            ];
            
            $invoice_id = $invoice->createInvoice($invoice_data);
            
            if ($invoice_id) {
                // Link efforts to invoice
                $effort_ids = array_column($efforts, 'id');
                $invoice->linkEffortsToInvoice($invoice_id, $effort_ids);
                
                $success = true;
                header("Location: view.php?id={$invoice_id}");
                exit;
            } else {
                $errors[] = 'Failed to create invoice';
            }
        }
    }
}

// Get customers for dropdown
$customers_query = "SELECT id, customer_name as name FROM " . $GLOBALS['_PJ_customer_table'] . " ORDER BY customer_name";
$db->query($customers_query);
$customers = [];
while ($db->next_record()) {
    $customers[] = $db->Record;
}

// Get projects for dropdown (will be filtered by JavaScript)
$projects_query = "SELECT id, customer_id, project_name as name FROM " . $GLOBALS['_PJ_project_table'] . " ORDER BY project_name";
$db->query($projects_query);
$projects = [];
while ($db->next_record()) {
    $projects[] = $db->Record;
}

// Set up template variables for unified layout
$center_template = "invoice/form";
$center_title = 'Create Invoice';

include("$_PJ_root/templates/list.ihtml.php");
