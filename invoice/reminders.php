<?php
require_once('../bootstrap.php');
require_once('../include/payment.class.php');
require_once('../include/invoice.class.php');

// Check authentication
$user_id = $_PJ_auth->giveValue('id');
if (!$user_id) {
    header('Location: ../index.php');
    exit;
}

$payment = new PaymentManager($db, $user_id);
$invoice = new Invoice($db, $user_id);

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$reminder_id = $_GET['id'] ?? $_POST['id'] ?? 0;

$errors = [];
$success = false;

// Handle reminder actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'send_reminder' && $reminder_id) {
        // Get reminder details
        $reminder_query = "SELECT pr.*, i.invoice_number, i.gross_amount, i.invoice_date,
                                 c.customer_name as customer_name
                          FROM " . $GLOBALS['_PJ_table_prefix'] . "payment_reminders pr
                          JOIN " . $GLOBALS['_PJ_table_prefix'] . "invoices i ON pr.invoice_id = i.id
                          JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON i.customer_id = c.id
                          WHERE pr.id = " . intval($reminder_id);
        
        $db->query($reminder_query);
        $reminder_data = false;
        if ($db->next_record()) {
            $reminder_data = $db->Record;
        }
        
        if ($reminder_data) {
            // Generate reminder text
            $reminder_text = $payment->generateReminderText($reminder_data, $reminder_data['reminder_type']);
            
            // Mark as sent
            if ($payment->markReminderSent($reminder_id, $reminder_text)) {
                $success = true;
                header("Location: reminders.php?success=sent");
                exit;
            } else {
                $errors[] = !empty($GLOBALS['_PJ_strings']['failed_mark_reminder_sent']) ? $GLOBALS['_PJ_strings']['failed_mark_reminder_sent'] : 'Failed to mark reminder as sent';
            }
        } else {
            $errors[] = !empty($GLOBALS['_PJ_strings']['reminder_not_found']) ? $GLOBALS['_PJ_strings']['reminder_not_found'] : 'Reminder not found';
        }
    }
}

// Get pending reminders
$pending_reminders = $payment->getPendingReminders();

// Get overdue invoices
$overdue_invoices = $payment->getOverdueInvoices();

// Set up template variables for unified layout
$center_template = "invoice/reminders";
$center_title = !empty($GLOBALS['_PJ_strings']['payment_reminders']) ? $GLOBALS['_PJ_strings']['payment_reminders'] : 'Payment Reminders';

include("$_PJ_root/templates/list.ihtml.php");
