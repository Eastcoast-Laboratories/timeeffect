<?php
require_once(__DIR__ . "/../../bootstrap.php");
include_once("../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');
require_once('../../include/payment.class.php');

header('Content-Type: application/json');

try {
    $invoice_id = $_POST['invoice_id'] ?? 0;
    
    if (!$invoice_id) {
        echo json_encode(['success' => false, 'error' => !empty($GLOBALS['_PJ_strings']['missing_invoice_id']) ? $GLOBALS['_PJ_strings']['missing_invoice_id'] : 'Missing invoice ID']);
        exit;
    }
    
    $db = new Database();
    $invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
    $payment = new PaymentManager($db, $_PJ_auth->giveValue('id'));
    
    // Check if reminders already exist
    $existing_query = "SELECT COUNT(*) as count FROM " . $GLOBALS['_PJ_table_prefix'] . "payment_reminders WHERE invoice_id = " . intval($invoice_id);
    $db->query($existing_query);
    $existing_count = 0;
    if ($db->next_record()) {
        $existing_count = intval($db->Record['count']);
    }
    
    if ($existing_count > 0) {
        echo json_encode(['success' => false, 'error' => !empty($GLOBALS['_PJ_strings']['reminders_already_scheduled']) ? $GLOBALS['_PJ_strings']['reminders_already_scheduled'] : 'Reminders already scheduled for this invoice']);
        exit;
    }
    
    // Schedule reminders
    if ($payment->scheduleReminders($invoice_id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => !empty($GLOBALS['_PJ_strings']['failed_schedule_reminders']) ? $GLOBALS['_PJ_strings']['failed_schedule_reminders'] : 'Failed to schedule reminders']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
