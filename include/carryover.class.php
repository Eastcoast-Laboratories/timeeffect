<?php

class Carryover {
    private $db;
    private $user_id;
    
    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
    }
    
    /**
     * Save carryover record
     */
    public function saveCarryover($customer_id, $project_id, $period_date, $contracted_hours, $actual_hours, $carryover_hours, $cumulative_carryover, $invoice_id = null) {
        $period_year = date('Y', strtotime($period_date));
        $period_month = date('n', strtotime($period_date));
        
        // Check if record exists
        $check_sql = "SELECT id FROM " . $GLOBALS['_PJ_table_prefix'] . "hour_carryovers 
                      WHERE customer_id = " . intval($customer_id) . " 
                      AND project_id = " . intval($project_id) . " 
                      AND period_year = " . intval($period_year) . " 
                      AND period_month = " . intval($period_month);
        
        $this->db->query($check_sql);
        if ($this->db->next_record()) {
            // Update existing record
            $sql = "UPDATE " . $GLOBALS['_PJ_table_prefix'] . "hour_carryovers SET
                    contracted_hours = " . floatval($contracted_hours) . ",
                    actual_hours = " . floatval($actual_hours) . ",
                    carryover_hours = " . floatval($carryover_hours) . ",
                    cumulative_carryover = " . floatval($cumulative_carryover) . ",
                    invoice_id = " . ($invoice_id ? intval($invoice_id) : 'NULL') . "
                    WHERE customer_id = " . intval($customer_id) . " 
                    AND project_id = " . intval($project_id) . " 
                    AND period_year = " . intval($period_year) . " 
                    AND period_month = " . intval($period_month);
        } else {
            // Insert new record
            $sql = "INSERT INTO " . $GLOBALS['_PJ_table_prefix'] . "hour_carryovers (
                customer_id, project_id, period_year, period_month, contracted_hours,
                actual_hours, carryover_hours, cumulative_carryover, invoice_id
            ) VALUES (
                " . intval($customer_id) . ", " . intval($project_id) . ", " . intval($period_year) . ", " . intval($period_month) . ",
                " . floatval($contracted_hours) . ", " . floatval($actual_hours) . ", " . floatval($carryover_hours) . ",
                " . floatval($cumulative_carryover) . ", " . ($invoice_id ? intval($invoice_id) : 'NULL') . "
            )";
        }
        
        return $this->db->query($sql);
    }
    
    /**
     * Get previous carryover for customer/project
     */
    public function getPreviousCarryover($customer_id, $project_id, $current_period) {
        $current_year = date('Y', strtotime($current_period));
        $current_month = date('n', strtotime($current_period));
        
        // Calculate previous month
        $prev_month = $current_month - 1;
        $prev_year = $current_year;
        
        if ($prev_month < 1) {
            $prev_month = 12;
            $prev_year--;
        }
        
        $sql = "SELECT cumulative_carryover FROM hour_carryovers 
                WHERE customer_id = " . intval($customer_id) . " AND project_id = " . intval($project_id) . "
                AND period_year = " . intval($prev_year) . " AND period_month = " . intval($prev_month);
        
        $this->db->query($sql);
        if ($this->db->next_record()) {
            return floatval($this->db->Record['cumulative_carryover']);
        }
        
        return 0.0;
    }
    
    /**
     * Get carryover history for customer/project
     */
    public function getCarryoverHistory($customer_id, $project_id, $limit = 12) {
        $sql = "SELECT * FROM " . $GLOBALS['_PJ_table_prefix'] . "hour_carryovers 
                WHERE customer_id = " . intval($customer_id) . " AND project_id = " . intval($project_id) . "
                ORDER BY period_year DESC, period_month DESC
                LIMIT " . intval($limit);
        
        $this->db->query($sql);
        $history = [];
        while ($this->db->next_record()) {
            $history[] = $this->db->Record;
        }
        return $history;
    }
    
    /**
     * Calculate carryover for period
     */
    public function calculateCarryover($customer_id, $project_id, $period_start, $period_end) {
        // Get contract
        $contract = new Contract($this->db, $this->user_id);
        $activeContract = $contract->getActiveContract($customer_id, $project_id);
        
        if (!$activeContract || $activeContract['contract_type'] !== 'fixed_monthly') {
            return null;
        }
        
        // Get efforts for period
        $sql = "SELECT SUM(hours) as total_hours FROM " . $GLOBALS['_PJ_table_prefix'] . "efforts 
                WHERE customer_id = " . intval($customer_id) . " AND project_id = " . intval($project_id) . "
                AND date >= '" . addslashes($period_start) . "' AND date <= '" . addslashes($period_end) . "'";
        
        $this->db->query($sql);
        $actual_hours = 0.0;
        if ($this->db->next_record()) {
            $actual_hours = floatval($this->db->Record['total_hours']) ?: 0.0;
        }
        
        $contracted_hours = floatval($activeContract['fixed_hours']);
        $carryover_hours = $actual_hours - $contracted_hours;
        
        // Get previous carryover
        $previous_carryover = $this->getPreviousCarryover($customer_id, $project_id, $period_start);
        $cumulative_carryover = $previous_carryover + $carryover_hours;
        
        return [
            'contracted_hours' => $contracted_hours,
            'actual_hours' => $actual_hours,
            'carryover_hours' => $carryover_hours,
            'previous_carryover' => $previous_carryover,
            'cumulative_carryover' => $cumulative_carryover
        ];
    }
    
    /**
     * Get current carryover balance
     */
    public function getCurrentBalance($customer_id, $project_id) {
        $sql = "SELECT cumulative_carryover FROM " . $GLOBALS['_PJ_table_prefix'] . "hour_carryovers 
                WHERE customer_id = " . intval($customer_id) . " AND project_id = " . intval($project_id) . "
                ORDER BY period_year DESC, period_month DESC
                LIMIT 1";
        
        $this->db->query($sql);
        if ($this->db->next_record()) {
            return floatval($this->db->Record['cumulative_carryover']);
        }
        
        return 0.0;
    }
    
    /**
     * Format carryover display
     */
    public function formatCarryoverDisplay($carryover_hours, $hourly_rate = null) {
        $sign = $carryover_hours >= 0 ? '+' : '';
        $display = $sign . number_format($carryover_hours, 2) . 'h';
        
        if ($hourly_rate) {
            $amount = $carryover_hours * $hourly_rate;
            $display .= ' (' . $sign . number_format($amount, 2) . '€)';
        }
        
        return $display;
    }
}
