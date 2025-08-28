<?php

class PaymentManager {
    private $db;
    private $user_id;
    
    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
    }
    
    /**
     * Add payment to invoice
     */
    public function addPayment($invoice_id, $amount, $payment_date, $payment_method = null, $notes = null) {
        $sql = "INSERT INTO " . $GLOBALS['_PJ_table_prefix'] . "invoice_payments (invoice_id, payment_date, amount, payment_method, notes)
                VALUES (" . intval($invoice_id) . ", '" . addslashes($payment_date) . "', " . floatval($amount) . ", " . 
                ($payment_method ? "'" . addslashes($payment_method) . "'" : 'NULL') . ", " . 
                ($notes ? "'" . addslashes($notes) . "'" : 'NULL') . ")";
        
        $result = $this->db->query($sql);
        
        if ($result) {
            // Check if invoice is fully paid
            $this->updateInvoicePaymentStatus($invoice_id);
            return $this->db->insert_id();
        }
        
        return false;
    }
    
    /**
     * Get payments for invoice
     */
    public function getInvoicePayments($invoice_id) {
        $sql = "SELECT * FROM " . $GLOBALS['_PJ_table_prefix'] . "invoice_payments WHERE invoice_id = " . intval($invoice_id) . " ORDER BY payment_date DESC";
        $this->db->query($sql);
        $payments = [];
        while ($this->db->next_record()) {
            $payments[] = $this->db->Record;
        }
        return $payments;
    }
    
    /**
     * Update invoice payment status based on payments
     */
    public function updateInvoicePaymentStatus($invoice_id) {
        // Get invoice total
        $sql = "SELECT gross_amount FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices WHERE id = " . intval($invoice_id);
        $this->db->query($sql);
        $invoice_total = 0;
        if ($this->db->next_record()) {
            $invoice_total = floatval($this->db->Record['gross_amount']);
        }
        
        // Get total payments
        $sql = "SELECT SUM(amount) as total_paid FROM " . $GLOBALS['_PJ_table_prefix'] . "invoice_payments WHERE invoice_id = " . intval($invoice_id);
        $this->db->query($sql);
        $total_paid = 0;
        if ($this->db->next_record()) {
            $total_paid = floatval($this->db->Record['total_paid']) ?: 0;
        }
        
        // Update status
        $status = 'sent';
        if ($total_paid >= $invoice_total) {
            $status = 'paid';
        }
        
        $sql = "UPDATE " . $GLOBALS['_PJ_table_prefix'] . "invoices SET status = '" . addslashes($status) . "' WHERE id = " . intval($invoice_id);
        return $this->db->query($sql);
    }
    
    /**
     * Generate reminder text based on customer history and reminder type
     */
    public function generateReminderText($invoice, $reminder_type, $customer_history = null) {
        $customer_name = $invoice['customer_name'];
        $invoice_number = $invoice['invoice_number'];
        $gross_amount = number_format($invoice['gross_amount'], 2);
        $invoice_date = date('d.m.Y', strtotime($invoice['invoice_date']));
        
        switch ($reminder_type) {
            case 'first':
                $text = "Sehr geehrte Damen und Herren,\n\n";
                $text .= "wir möchten Sie freundlich daran erinnern, dass die Rechnung {$invoice_number} ";
                $text .= "vom {$invoice_date} über {$gross_amount}€ noch nicht beglichen wurde.\n\n";
                $text .= "Falls die Zahlung bereits erfolgt ist, betrachten Sie diese Erinnerung als gegenstandslos.\n\n";
                $text .= "Mit freundlichen Grüßen";
                break;
                
            case 'second':
                $text = "Sehr geehrte Damen und Herren,\n\n";
                $text .= "trotz unserer ersten Zahlungserinnerung ist die Rechnung {$invoice_number} ";
                $text .= "vom {$invoice_date} über {$gross_amount}€ noch immer offen.\n\n";
                $text .= "Wir bitten Sie höflich, den ausstehenden Betrag innerhalb der nächsten 7 Tage zu begleichen.\n\n";
                $text .= "Bei Fragen stehen wir Ihnen gerne zur Verfügung.\n\n";
                $text .= "Mit freundlichen Grüßen";
                break;
                
            case 'final':
                $text = "Sehr geehrte Damen und Herren,\n\n";
                $text .= "leider müssen wir feststellen, dass die Rechnung {$invoice_number} ";
                $text .= "vom {$invoice_date} über {$gross_amount}€ trotz mehrfacher Mahnung noch immer nicht beglichen wurde.\n\n";
                $text .= "Wir fordern Sie hiermit letztmalig auf, den Betrag innerhalb von 5 Werktagen zu begleichen. ";
                $text .= "Andernfalls sehen wir uns gezwungen, weitere rechtliche Schritte einzuleiten.\n\n";
                $text .= "Mit freundlichen Grüßen";
                break;
                
            default:
                return null;
        }
        
        return $text;
    }
    
    /**
     * Schedule payment reminders for invoice
     */
    public function scheduleReminders($invoice_id) {
        // Get user's payment terms
        $sql = "SELECT payment_terms_days FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id = " . intval($this->user_id);
        $this->db->query($sql);
        $payment_terms = 14;
        if ($this->db->next_record()) {
            $payment_terms = intval($this->db->Record['payment_terms_days']) ?: 14;
        }
        
        // Get invoice date
        $sql = "SELECT invoice_date FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices WHERE id = " . intval($invoice_id);
        $this->db->query($sql);
        $invoice_date = '';
        if ($this->db->next_record()) {
            $invoice_date = $this->db->Record['invoice_date'];
        }
        
        $due_date = date('Y-m-d', strtotime($invoice_date . " + {$payment_terms} days"));
        $first_reminder = date('Y-m-d', strtotime($due_date . " + 7 days"));
        $second_reminder = date('Y-m-d', strtotime($due_date . " + 21 days"));
        $final_reminder = date('Y-m-d', strtotime($due_date . " + 35 days"));
        
        // Create reminder records
        $reminders = [
            ['type' => 'first', 'due_date' => $first_reminder],
            ['type' => 'second', 'due_date' => $second_reminder],
            ['type' => 'final', 'due_date' => $final_reminder]
        ];
        
        foreach ($reminders as $reminder) {
            $sql = "INSERT INTO " . $GLOBALS['_PJ_table_prefix'] . "payment_reminders (invoice_id, reminder_type, due_date, status)
                    VALUES (" . intval($invoice_id) . ", '" . addslashes($reminder['type']) . "', '" . addslashes($reminder['due_date']) . "', 'pending')";
            $this->db->query($sql);
        }
        
        return true;
    }
    
    /**
     * Get pending reminders
     */
    public function getPendingReminders() {
        $sql = "SELECT pr.*, i.invoice_number, i.gross_amount, i.invoice_date,
                       c.name as customer_name
                FROM " . $GLOBALS['_PJ_table_prefix'] . "payment_reminders pr
                JOIN " . $GLOBALS['_PJ_table_prefix'] . "invoices i ON pr.invoice_id = i.id
                JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON i.customer_id = c.id
                WHERE pr.status = 'pending' AND pr.due_date <= CURDATE()
                ORDER BY pr.due_date ASC";
        
        $this->db->query($sql);
        $reminders = [];
        while ($this->db->next_record()) {
            $reminders[] = $this->db->Record;
        }
        return $reminders;
    }
    
    /**
     * Mark reminder as sent
     */
    public function markReminderSent($reminder_id, $reminder_text = null) {
        $sql = "UPDATE " . $GLOBALS['_PJ_table_prefix'] . "payment_reminders SET 
                status = 'sent', sent_date = CURDATE(), reminder_text = " . 
                ($reminder_text ? "'" . addslashes($reminder_text) . "'" : 'NULL') . "
                WHERE id = " . intval($reminder_id);
        
        return $this->db->query($sql);
    }
    
    /**
     * Analyze payment patterns for customer
     */
    public function analyzePaymentPatterns($customer_id) {
        $sql = "SELECT i.invoice_date, i.gross_amount, 
                       MIN(p.payment_date) as first_payment_date,
                       DATEDIFF(MIN(p.payment_date), i.invoice_date) as days_to_payment
                FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices i
                LEFT JOIN " . $GLOBALS['_PJ_table_prefix'] . "invoice_payments p ON i.id = p.invoice_id
                WHERE i.customer_id = " . intval($customer_id) . " AND i.status = 'paid'
                GROUP BY i.id
                ORDER BY i.invoice_date DESC
                LIMIT 12";
        
        $this->db->query($sql);
        $payments = [];
        while ($this->db->next_record()) {
            $payments[] = $this->db->Record;
        }
        
        if (empty($payments)) {
            return null;
        }
        
        $total_days = 0;
        $payment_count = 0;
        
        foreach ($payments as $payment) {
            if ($payment['days_to_payment'] !== null) {
                $total_days += $payment['days_to_payment'];
                $payment_count++;
            }
        }
        
        $average_days = $payment_count > 0 ? $total_days / $payment_count : null;
        
        return [
            'average_payment_days' => $average_days,
            'total_invoices' => count($payments),
            'paid_invoices' => $payment_count,
            'payment_reliability' => $payment_count / count($payments) * 100,
            'recent_payments' => $payments
        ];
    }
    
    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices() {
        $sql = "SELECT i.*, c.name as customer_name,
                       DATEDIFF(CURDATE(), DATE_ADD(i.invoice_date, INTERVAL u.payment_terms_days DAY)) as days_overdue
                FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices i
                JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON i.customer_id = c.id
                JOIN " . $GLOBALS['_PJ_auth_table'] . " u ON u.id = " . intval($this->user_id) . "
                WHERE i.status IN ('draft', 'sent')
                AND DATE_ADD(i.invoice_date, INTERVAL u.payment_terms_days DAY) < CURDATE()
                ORDER BY days_overdue DESC";
        
        $this->db->query($sql);
        $overdue = [];
        while ($this->db->next_record()) {
            $overdue[] = $this->db->Record;
        }
        return $overdue;
    }
}
