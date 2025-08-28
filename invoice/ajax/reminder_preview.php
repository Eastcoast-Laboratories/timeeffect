<?php
require_once(__DIR__ . "/../../bootstrap.php");
include_once("../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');
require_once('../../include/payment.class.php');

header('Content-Type: application/json');

try {
    $reminder_id = $_POST['reminder_id'] ?? 0;
    
    if (!$reminder_id) {
        echo json_encode(['success' => false, 'error' => 'Missing reminder ID']);
        exit;
    }
    
    $db = new Database();
    $invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
    $payment = new PaymentManager($db, $_PJ_auth->giveValue('id'));
    
    // Get reminder details
    $reminder_query = "SELECT pr.*, i.invoice_number, i.gross_amount, i.invoice_date,
                             c.name as customer_name
                      FROM " . $GLOBALS['_PJ_table_prefix'] . "payment_reminders pr
                      JOIN " . $GLOBALS['_PJ_table_prefix'] . "invoices i ON pr.invoice_id = i.id
                      JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON i.customer_id = c.id
                      WHERE pr.id = " . intval($reminder_id);
    
    $db->query($reminder_query);
    $reminder_data = false;
    if ($db->next_record()) {
        $reminder_data = $db->Record;
    }
    
    if (!$reminder_data) {
        echo json_encode(['success' => false, 'error' => 'Reminder not found']);
        exit;
    }
    
    // Generate reminder text
    $reminder_text = $payment->generateReminderText($reminder_data, $reminder_data['reminder_type']);
    
    echo json_encode(['success' => true, 'text' => $reminder_text]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
