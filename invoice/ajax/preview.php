<?php
require_once(__DIR__ . "/../../bootstrap.php");
include_once("../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');
require_once('../../include/invoice.class.php');
require_once('../../include/contract.class.php');
require_once('../../include/carryover.class.php');

// Initialize database
$db = new Database();
$db->connect();

if (!isset($GLOBALS['suppress_headers'])) {
    header('Content-Type: application/json');
}

try {
    $customer_id = $_GET['customer_id'] ?? '';
    $project_id = $_GET['project_id'] ?? null;
    $period_start = $_GET['period_start'] ?? '';
    $period_end = $_GET['period_end'] ?? '';
    $generate_type = $_GET['generate_type'] ?? 'manual';
    $invoice_date = $_GET['invoice_date'] ?? date('Y-m-d');
    $mode = $_GET['mode'] ?? '';
    
    if (empty($customer_id) || empty($period_start) || empty($period_end)) {
        if (empty($period_start)) {
            $errormessage="Set a Month for the invoice";
        }else {
            $errormessage="Missing required fields";
        }
        echo json_encode(['success' => false, 'error' => $errormessage, 'debug' => [
            'customer_id' => $customer_id,
            'period_start' => $period_start, 
            'period_end' => $period_end,
            'get_data' => $_GET
        ]]);
        exit;
    }
    
    $db = new Database();
    $invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
    $contract = new Contract($db, $_PJ_auth->giveValue('id'));
    $carryover = new Carryover($db, $_PJ_auth->giveValue('id'));
    
    // Get customer info
    $customer_query = "SELECT customer_name as name FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id = " . intval($customer_id);
    $db->query($customer_query);
    $customer = false;
    if ($db->next_record()) {
        $customer = $db->Record;
    }
    
    // Get project info if specified
    $project = null;
    if ($project_id) {
        $project_query = "SELECT project_name as name FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id = " . intval($project_id);
        $db->query($project_query);
        $project = false;
        if ($db->next_record()) {
            $project = $db->Record;
        }
    }
    
    // Get efforts for period
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
    $total_minutes = 0;
    while ($db->next_record()) {
        $effort = $db->Record;
        
        // Calculate hours from begin/end time difference
        $begin_time = strtotime($effort['begin']);
        $end_time = strtotime($effort['end']);
        $minutes = ($end_time - $begin_time) / 60;
        
        $effort['hours'] = round($minutes / 60, 2);
        $efforts[] = $effort;
        $total_minutes += $minutes;
    }
    
    $total_hours = round($total_minutes / 60, 2);
    
    // Get contract info
    $activeContract = $contract->getActiveContract($customer_id, $project_id);
    
    $html = '<div class="preview-invoice" style="max-width: 800px; margin: 20px auto; padding: 30px; border: 1px solid #ddd; font-family: Arial, sans-serif; background: white;">';
    
    // Invoice Header
    $html .= '<div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">';
    $html .= '<h2 style="margin: 0; color: #333;">INVOICE PREVIEW</h2>';
    $html .= '<p style="margin: 5px 0; color: #666;">Invoice Date: ' . date('d.m.Y', strtotime($invoice_date)) . '</p>';
    $html .= '</div>';
    
    // Customer Information
    $html .= '<div style="margin-bottom: 25px;">';
    $html .= '<h4 style="margin-bottom: 10px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px;">Bill To:</h4>';
    $html .= '<div style="font-size: 14px; line-height: 1.6;">';
    $html .= '<strong>' . htmlspecialchars($customer['name']) . '</strong><br>';
    if ($project) {
        $html .= 'Project: ' . htmlspecialchars($project['name']) . '<br>';
    }
    $html .= '</div>';
    $html .= '</div>';
    
    // Invoice Period
    $html .= '<div style="margin-bottom: 25px; background: #f9f9f9; padding: 15px; border-left: 4px solid #007cba;">';
    $html .= '<strong>Service Period:</strong> ' . date('d.m.Y', strtotime($period_start)) . ' - ' . date('d.m.Y', strtotime($period_end)) . '<br>';
    $html .= '<strong>Total Hours:</strong> ' . number_format($total_hours, 2) . 'h';
    $html .= '</div>';
    
    if ($generate_type === 'fixed_contract' && $activeContract && $activeContract['contract_type'] === 'fixed_monthly') {
        // Fixed contract preview
        $contracted_hours = $activeContract['fixed_hours'];
        $fixed_amount = $activeContract['fixed_amount'];
        $previous_carryover = $carryover->getPreviousCarryover($customer_id, $project_id, $period_start);
        $current_carryover = $total_hours - $contracted_hours;
        $cumulative_carryover = $previous_carryover + $current_carryover;
        
        // Fixed contract table
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; border: 1px solid #ddd;">';
        $html .= '<thead><tr style="background: #f5f5f5;"><th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Description</th><th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Hours</th><th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Amount</th></tr></thead>';
        $html .= '<tbody>';
        $html .= '<tr><td style="padding: 12px; border: 1px solid #ddd;">Fixed Monthly Contract</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . number_format($contracted_hours, 2) . 'h</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . number_format($fixed_amount, 2) . '€</td></tr>';
        $html .= '<tr><td style="padding: 12px; border: 1px solid #ddd;">Previous Carryover</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . number_format($previous_carryover, 2) . 'h</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">-</td></tr>';
        $html .= '<tr><td style="padding: 12px; border: 1px solid #ddd;">Current Carryover</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . number_format($current_carryover, 2) . 'h</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">-</td></tr>';
        $html .= '</tbody></table>';
        
        $net_amount = $fixed_amount;
    } else {
        // Hourly billing table
        $hourly_rate = $activeContract['hourly_rate'] ?? 50.00;
        $net_amount = $total_hours * $hourly_rate;
        
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; border: 1px solid #ddd;">';
        $html .= '<thead><tr style="background: #f5f5f5;"><th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Description</th><th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Hours</th><th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Rate</th><th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Amount</th></tr></thead>';
        $html .= '<tbody>';
        $html .= '<tr><td style="padding: 12px; border: 1px solid #ddd;">Professional Services</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . number_format($total_hours, 2) . 'h</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . number_format($hourly_rate, 2) . '€</td><td style="padding: 12px; text-align: right; border: 1px solid #ddd;">' . number_format($net_amount, 2) . '€</td></tr>';
        $html .= '</tbody></table>';
    }
    
    // Get VAT rate
    $vat_query = "SELECT default_vat_rate FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id = " . intval($_PJ_auth->giveValue('id'));
    $db->query($vat_query);
    $vat_rate = 19.00;
    if ($db->next_record()) {
        $vat_rate = $db->f('default_vat_rate') ?: 19.00;
    }
    
    $totals = $invoice->calculateInvoiceTotals($net_amount, $vat_rate);
    
    // Invoice totals table
    $html .= '<div style="margin-top: 30px; border-top: 2px solid #333; padding-top: 20px;">';
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">';
    $html .= '<tbody>';
    $html .= '<tr><td style="padding: 8px; text-align: right; font-weight: bold;">Subtotal:</td><td style="padding: 8px; text-align: right; width: 120px;">' . number_format($totals['net_amount'], 2) . '€</td></tr>';
    $html .= '<tr><td style="padding: 8px; text-align: right;">VAT (' . number_format($vat_rate, 1) . '%):</td><td style="padding: 8px; text-align: right;">' . number_format($totals['vat_amount'], 2) . '€</td></tr>';
    $html .= '<tr style="border-top: 2px solid #333; font-weight: bold; font-size: 16px;"><td style="padding: 12px; text-align: right;">TOTAL:</td><td style="padding: 12px; text-align: right; background: #f0f0f0;">' . number_format($totals['gross_amount'], 2) . '€</td></tr>';
    $html .= '</tbody></table>';
    $html .= '</div>';
    
    if (!empty($efforts)) {
        $html .= '<div style="margin-top: 30px;">';
        $html .= '<h4 style="margin-bottom: 15px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px;">Work Details (' . count($efforts) . ' entries)</h4>';
        $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">';
        $html .= '<thead><tr style="background: #f8f8f8;"><th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Date</th><th style="padding: 8px; text-align: right; border: 1px solid #ddd;">Hours</th><th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Description</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($efforts as $effort) {
            $html .= '<tr>';
            $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . date('d.m.Y', strtotime($effort['date'])) . '</td>';
            $html .= '<td style="padding: 8px; text-align: right; border: 1px solid #ddd;">' . number_format($effort['hours'], 2) . 'h</td>';
            $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($effort['description']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
