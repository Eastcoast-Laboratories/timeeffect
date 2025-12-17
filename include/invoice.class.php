<?php

class Invoice {
    private $db;
    private $user_id;
    
    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
    }
    
    /**
     * Generate next invoice number based on user format
     */
    public function generateInvoiceNumber($user_id) {
        // Get user's invoice number format
        $query = "SELECT invoice_number_format FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id = " . intval($user_id);
        $this->db->query($query);
        $format = 'R-{YYYY}-{MM}-{###}';
        if ($this->db->next_record()) {
            $format = $this->db->Record['invoice_number_format'] ?: 'R-{YYYY}-{MM}-{###}';
        }
        
        // Get current date components
        $year = date('Y');
        $month = date('m');
        
        // Find highest invoice number for current year/month
        $pattern = str_replace(['{YYYY}', '{MM}', '{###}'], [$year, $month, '%'], $format);
        $query = "SELECT invoice_number FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices WHERE invoice_number LIKE '" . addslashes($pattern) . "' ORDER BY invoice_number DESC LIMIT 1";
        $this->db->query($query);
        $lastNumber = '';
        if ($this->db->next_record()) {
            $lastNumber = $this->db->Record['invoice_number'];
        }
        
        if ($lastNumber) {
            // Extract number from last invoice
            preg_match('/(\d+)$/', $lastNumber, $matches);
            $nextNum = intval($matches[1]) + 1;
        } else {
            $nextNum = 1;
        }
        
        // Generate new invoice number
        $invoiceNumber = str_replace(
            ['{YYYY}', '{MM}', '{###}'],
            [$year, $month, str_pad($nextNum, 3, '0', STR_PAD_LEFT)],
            $format
        );
        
        return $invoiceNumber;
    }
    
    /**
     * Create new invoice
     */
    public function createInvoice($data) {
        $sql = "INSERT INTO " . $GLOBALS['_PJ_table_prefix'] . "invoices (
            invoice_number, customer_id, project_id, invoice_date, period_start, period_end,
            contract_type, fixed_amount, fixed_hours, total_hours, total_amount,
            vat_rate, vat_amount, gross_amount, carryover_previous, carryover_current,
            description, status
        ) VALUES (
            '" . addslashes($data['invoice_number']) . "',
            " . intval($data['customer_id']) . ",
            " . ($data['project_id'] ? intval($data['project_id']) : 'NULL') . ",
            '" . addslashes($data['invoice_date']) . "',
            '" . addslashes($data['period_start']) . "',
            '" . addslashes($data['period_end']) . "',
            '" . addslashes($data['contract_type']) . "',
            " . ($data['fixed_amount'] ? floatval($data['fixed_amount']) : 'NULL') . ",
            " . ($data['fixed_hours'] ? floatval($data['fixed_hours']) : 'NULL') . ",
            " . floatval($data['total_hours']) . ",
            " . floatval($data['total_amount']) . ",
            " . floatval($data['vat_rate']) . ",
            " . floatval($data['vat_amount']) . ",
            " . floatval($data['gross_amount']) . ",
            " . floatval($data['carryover_previous']) . ",
            " . floatval($data['carryover_current']) . ",
            '" . addslashes($data['description']) . "',
            '" . addslashes($data['status'] ?? 'draft') . "'
        )";
        
        $result = $this->db->query($sql);
        
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Get invoice by ID
     */
    public function getInvoice($invoice_id) {
        $sql = "SELECT i.*, c.customer_name as customer_name, customer_address,
                       p.project_name as project_name
                FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices i
                JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON i.customer_id = c.id
                LEFT JOIN " . $GLOBALS['_PJ_project_table'] . " p ON i.project_id = p.id
                WHERE i.id = " . intval($invoice_id);
        
        $this->db->query($sql);
        if ($this->db->next_record()) {
            return $this->db->Record;
        }
        return false;
    }
    
    /**
     * Get all invoices for user with filtering
     */
    public function getInvoices($filters = []) {
        $sql = "SELECT i.*, c.customer_name as customer_name, p.project_name as project_name
                FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices i
                JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON i.customer_id = c.id
                LEFT JOIN " . $GLOBALS['_PJ_project_table'] . " p ON i.project_id = p.id
                WHERE 1=1";
        
        if (!empty($filters['customer_id'])) {
            $sql .= " AND i.customer_id = " . intval($filters['customer_id']);
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND i.status = '" . addslashes($filters['status']) . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND i.invoice_date >= '" . addslashes($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND i.invoice_date <= '" . addslashes($filters['date_to']) . "'";
        }
        
        $sql .= " ORDER BY i.invoice_date DESC, i.id DESC";
        
        $this->db->query($sql);
        $invoices = [];
        while ($this->db->next_record()) {
            $invoices[] = $this->db->Record;
        }
        return $invoices;
    }
    
    /**
     * Update invoice
     */
    public function updateInvoice($invoice_id, $data) {
        $sql = "UPDATE " . $GLOBALS['_PJ_table_prefix'] . "invoices SET
                customer_id = " . intval($data['customer_id']) . ",
                project_id = " . ($data['project_id'] ? intval($data['project_id']) : 'NULL') . ",
                invoice_date = '" . addslashes($data['invoice_date']) . "',
                period_start = '" . addslashes($data['period_start']) . "',
                period_end = '" . addslashes($data['period_end']) . "',
                contract_type = '" . addslashes($data['contract_type']) . "',
                fixed_amount = " . ($data['fixed_amount'] ? floatval($data['fixed_amount']) : 'NULL') . ",
                fixed_hours = " . ($data['fixed_hours'] ? floatval($data['fixed_hours']) : 'NULL') . ",
                total_hours = " . floatval($data['total_hours']) . ",
                total_amount = " . floatval($data['total_amount']) . ",
                vat_rate = " . floatval($data['vat_rate']) . ",
                vat_amount = " . floatval($data['vat_amount']) . ",
                gross_amount = " . floatval($data['gross_amount']) . ",
                carryover_previous = " . floatval($data['carryover_previous']) . ",
                carryover_current = " . floatval($data['carryover_current']) . ",
                description = '" . addslashes($data['description']) . "',
                status = '" . addslashes($data['status']) . "',
                updated_at = CURRENT_TIMESTAMP
                WHERE id = " . intval($invoice_id);
        
        return $this->db->query($sql);
    }
    
    /**
     * Delete invoice (only if draft)
     */
    public function deleteInvoice($invoice_id) {
        // Check if invoice is draft
        $sql = "SELECT status FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices WHERE id = " . intval($invoice_id);
        $this->db->query($sql);
        if (!$this->db->next_record()) {
            return false;
        }
        $status = $this->db->Record['status'];
        
        if ($status !== 'draft') {
            return false;
        }
        
        $sql = "DELETE FROM " . $GLOBALS['_PJ_table_prefix'] . "invoices WHERE id = " . intval($invoice_id);
        return $this->db->query($sql);
    }
    
    /**
     * Add invoice item
     */
    public function addInvoiceItem($invoice_id, $description, $quantity, $unit_price) {
        $total_amount = $quantity * $unit_price;
        
        $sql = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_amount)
                VALUES (" . intval($invoice_id) . ", '" . addslashes($description) . "', " . floatval($quantity) . ", " . floatval($unit_price) . ", " . floatval($total_amount) . ")";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get invoice items
     */
    public function getInvoiceItems($invoice_id) {
        $sql = "SELECT * FROM " . $GLOBALS['_PJ_table_prefix'] . "invoice_items WHERE invoice_id = " . intval($invoice_id) . " ORDER BY id";
        $this->db->query($sql);
        $items = [];
        while ($this->db->next_record()) {
            $items[] = $this->db->Record;
        }
        return $items;
    }
    
    /**
     * Link efforts to invoice
     */
    public function linkEffortsToInvoice($invoice_id, $effort_ids) {
        // First clear existing links
        $sql = "DELETE FROM " . $GLOBALS['_PJ_table_prefix'] . "invoice_efforts WHERE invoice_id = " . intval($invoice_id);
        $this->db->query($sql);
        
        // Add new links
        foreach ($effort_ids as $effort_id) {
            $sql = "INSERT INTO " . $GLOBALS['_PJ_table_prefix'] . "invoice_efforts (invoice_id, effort_id) VALUES (" . intval($invoice_id) . ", " . intval($effort_id) . ")";
            $this->db->query($sql);
        }
        
        return true;
    }
    
    /**
     * Get efforts linked to invoice
     */
    public function getInvoiceEfforts($invoice_id) {
        $sql = "SELECT e.*, ie.id as link_id,
                       TIME_TO_SEC(TIMEDIFF(e.end, e.begin)) / 3600 as hours
                FROM " . $GLOBALS['_PJ_table_prefix'] . "effort e
                JOIN " . $GLOBALS['_PJ_table_prefix'] . "invoice_efforts ie ON e.id = ie.effort_id
                WHERE ie.invoice_id = " . intval($invoice_id) . "
                ORDER BY e.date, e.id";
        
        $this->db->query($sql);
        $efforts = [];
        while ($this->db->next_record()) {
            $efforts[] = $this->db->Record;
        }
        return $efforts;
    }
    
    /**
     * Calculate invoice totals
     */
    public function calculateInvoiceTotals($net_amount, $vat_rate) {
        $vat_amount = $net_amount * ($vat_rate / 100);
        $gross_amount = $net_amount + $vat_amount;
        
        return [
            'net_amount' => round($net_amount, 2),
            'vat_amount' => round($vat_amount, 2),
            'gross_amount' => round($gross_amount, 2)
        ];
    }
    
    /**
     * Generate invoice from efforts for fixed contract
     */
    public function generateFixedContractInvoice($customer_id, $project_id, $period_start, $period_end) {
        // Get active contract
        $contract = new Contract($this->db, $this->user_id);
        $activeContract = $contract->getActiveContract($customer_id, $project_id);
        
        if (!$activeContract || $activeContract['contract_type'] !== 'fixed_monthly') {
            return false;
        }
        
        // Get efforts for period
        $sql = "SELECT * FROM " . $GLOBALS['_PJ_table_prefix'] . "efforts 
                WHERE customer_id = " . intval($customer_id) . " AND project_id = " . intval($project_id) . "
                AND date >= '" . addslashes($period_start) . "' AND date <= '" . addslashes($period_end) . "'
                ORDER BY date";
        
        $this->db->query($sql);
        $efforts = [];
        $total_hours = 0;
        while ($this->db->next_record()) {
            $efforts[] = $this->db->Record;
            $total_hours += floatval($this->db->Record['hours']);
        }
        
        // Calculate hours
        $actual_hours = $total_hours;
        $contracted_hours = $activeContract['fixed_hours'];
        $fixed_amount = $activeContract['fixed_amount'];
        
        // Get previous carryover
        $carryover = new Carryover($this->db, $this->user_id);
        $previous_carryover = $carryover->getPreviousCarryover($customer_id, $project_id, $period_start);
        
        // Calculate current carryover
        $current_carryover = $actual_hours - $contracted_hours;
        $cumulative_carryover = $previous_carryover + $current_carryover;
        
        // Get user's default VAT rate
        $sql = "SELECT default_vat_rate FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id = " . intval($this->user_id);
        $this->db->query($sql);
        $vat_rate = 19.00;
        if ($this->db->next_record()) {
            $vat_rate = floatval($this->db->Record['default_vat_rate']) ?: 19.00;
        }
        
        // Calculate totals
        $totals = $this->calculateInvoiceTotals($fixed_amount, $vat_rate);
        
        // Create invoice data
        $invoice_data = [
            'invoice_number' => $this->generateInvoiceNumber($this->user_id),
            'customer_id' => $customer_id,
            'project_id' => $project_id,
            'invoice_date' => date('Y-m-d'),
            'period_start' => $period_start,
            'period_end' => $period_end,
            'contract_type' => 'fixed_monthly',
            'fixed_amount' => $fixed_amount,
            'fixed_hours' => $contracted_hours,
            'total_hours' => $actual_hours,
            'total_amount' => $totals['net_amount'],
            'vat_rate' => $vat_rate,
            'vat_amount' => $totals['vat_amount'],
            'gross_amount' => $totals['gross_amount'],
            'carryover_previous' => $previous_carryover,
            'carryover_current' => $cumulative_carryover,
            'description' => "Fixed monthly contract - " . date('m/Y', strtotime($period_start)),
            'status' => 'draft'
        ];
        
        // Create invoice
        $invoice_id = $this->createInvoice($invoice_data);
        
        if ($invoice_id) {
            // Link efforts to invoice
            $effort_ids = array_column($efforts, 'id');
            $this->linkEffortsToInvoice($invoice_id, $effort_ids);
            
            // Save carryover record
            $carryover->saveCarryover($customer_id, $project_id, $period_start, $contracted_hours, $actual_hours, $current_carryover, $cumulative_carryover, $invoice_id);
            
            return $invoice_id;
        }
        
        return false;
    }
}
