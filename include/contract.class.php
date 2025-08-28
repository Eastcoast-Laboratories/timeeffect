<?php

class Contract {
    private $db;
    private $user_id;
    
    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
    }
    
    /**
     * Create new contract
     */
    public function createContract($data) {
        $sql = "INSERT INTO " . $GLOBALS['_PJ_table_prefix'] . "customer_contracts (
            customer_id, project_id, contract_type, fixed_amount, fixed_hours,
            hourly_rate, start_date, end_date, description, active
        ) VALUES (
            " . intval($data['customer_id']) . ",
            " . ($data['project_id'] ? intval($data['project_id']) : 'NULL') . ",
            '" . addslashes($data['contract_type']) . "',
            " . ($data['fixed_amount'] ? floatval($data['fixed_amount']) : 'NULL') . ",
            " . ($data['fixed_hours'] ? floatval($data['fixed_hours']) : 'NULL') . ",
            " . ($data['hourly_rate'] ? floatval($data['hourly_rate']) : 'NULL') . ",
            '" . addslashes($data['start_date']) . "',
            " . ($data['end_date'] ? "'" . addslashes($data['end_date']) . "'" : 'NULL') . ",
            '" . addslashes($data['description']) . "',
            " . (($data['active'] ?? true) ? 1 : 0) . "
        )";
        
        $result = $this->db->query($sql);
        
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }
    
    /**
     * Get active contract for customer/project
     */
    public function getActiveContract($customer_id, $project_id = null) {
        $sql = "SELECT * FROM " . $GLOBALS['_PJ_table_prefix'] . "customer_contracts 
                WHERE customer_id = " . intval($customer_id) . " AND active = 1
                AND (end_date IS NULL OR end_date >= CURDATE())
                AND start_date <= CURDATE()";
        
        if ($project_id) {
            $sql .= " AND (project_id = " . intval($project_id) . " OR project_id IS NULL)";
        }
        
        $sql .= " ORDER BY project_id DESC, start_date DESC LIMIT 1";
        
        $this->db->query($sql);
        if ($this->db->next_record()) {
            return $this->db->Record;
        }
        return false;
    }
    
    /**
     * Get all contracts for customer
     */
    public function getCustomerContracts($customer_id) {
        $sql = "SELECT cc.*, p.project_name as project_name
                FROM " . $GLOBALS['_PJ_table_prefix'] . "customer_contracts cc
                LEFT JOIN " . $GLOBALS['_PJ_project_table'] . " p ON cc.project_id = p.id
                WHERE cc.customer_id = " . intval($customer_id) . "
                ORDER BY cc.start_date DESC";
        
        $this->db->query($sql);
        $contracts = [];
        while ($this->db->next_record()) {
            $contracts[] = $this->db->Record;
        }
        return $contracts;
    }
    
    /**
     * Update contract
     */
    public function updateContract($contract_id, $data) {
        $sql = "UPDATE " . $GLOBALS['_PJ_table_prefix'] . "customer_contracts SET
                customer_id = " . intval($data['customer_id']) . ",
                project_id = " . ($data['project_id'] ? intval($data['project_id']) : 'NULL') . ",
                contract_type = '" . addslashes($data['contract_type']) . "',
                fixed_amount = " . ($data['fixed_amount'] ? floatval($data['fixed_amount']) : 'NULL') . ",
                fixed_hours = " . ($data['fixed_hours'] ? floatval($data['fixed_hours']) : 'NULL') . ",
                hourly_rate = " . ($data['hourly_rate'] ? floatval($data['hourly_rate']) : 'NULL') . ",
                start_date = '" . addslashes($data['start_date']) . "',
                end_date = " . ($data['end_date'] ? "'" . addslashes($data['end_date']) . "'" : 'NULL') . ",
                description = '" . addslashes($data['description']) . "',
                active = " . intval($data['active']) . "
                WHERE id = " . intval($contract_id);
        
        return $this->db->query($sql);
    }
    
    /**
     * Deactivate contract
     */
    public function deactivateContract($contract_id) {
        $sql = "UPDATE " . $GLOBALS['_PJ_table_prefix'] . "customer_contracts SET active = 0 WHERE id = " . intval($contract_id);
        return $this->db->query($sql);
    }
    
    /**
     * Get contract by ID
     */
    public function getContract($contract_id) {
        $sql = "SELECT cc.*, c.customer_name as customer_name, p.project_name as project_name
                FROM " . $GLOBALS['_PJ_table_prefix'] . "customer_contracts cc
                JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON cc.customer_id = c.id
                LEFT JOIN " . $GLOBALS['_PJ_project_table'] . " p ON cc.project_id = p.id
                WHERE cc.id = " . intval($contract_id);
        
        $this->db->query($sql);
        if ($this->db->next_record()) {
            return $this->db->Record;
        }
        return false;
    }
    
    /**
     * Validate contract data
     */
    public function validateContract($data) {
        $errors = [];
        
        if (empty($data['customer_id'])) {
            $errors[] = 'Customer is required';
        }
        
        if (empty($data['contract_type'])) {
            $errors[] = 'Contract type is required';
        }
        
        if ($data['contract_type'] === 'fixed_monthly') {
            if (empty($data['fixed_amount']) || $data['fixed_amount'] <= 0) {
                $errors[] = 'Fixed amount must be greater than 0';
            }
            if (empty($data['fixed_hours']) || $data['fixed_hours'] <= 0) {
                $errors[] = 'Fixed hours must be greater than 0';
            }
        } elseif ($data['contract_type'] === 'hourly') {
            if (empty($data['hourly_rate']) || $data['hourly_rate'] <= 0) {
                $errors[] = 'Hourly rate must be greater than 0';
            }
        }
        
        if (empty($data['start_date'])) {
            $errors[] = 'Start date is required';
        }
        
        if (!empty($data['end_date']) && !empty($data['start_date'])) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                $errors[] = 'End date must be after start date';
            }
        }
        
        return $errors;
    }
    
    /**
     * Check for overlapping contracts
     */
    public function hasOverlappingContract($customer_id, $project_id, $start_date, $end_date, $exclude_id = null) {
        $sql = "SELECT id FROM " . $GLOBALS['_PJ_table_prefix'] . "customer_contracts 
                WHERE customer_id = " . intval($customer_id) . " AND active = 1";
        
        if ($project_id) {
            $sql .= " AND project_id = " . intval($project_id);
        } else {
            $sql .= " AND project_id IS NULL";
        }
        
        $safe_start_date = addslashes($start_date);
        $safe_end_date = $end_date ? addslashes($end_date) : '9999-12-31';
        
        $sql .= " AND ((start_date <= '" . $safe_start_date . "' AND (end_date IS NULL OR end_date >= '" . $safe_start_date . "'))
                      OR (start_date <= '" . $safe_end_date . "' AND (end_date IS NULL OR end_date >= '" . $safe_end_date . "')))";
        
        if ($exclude_id) {
            $sql .= " AND id != " . intval($exclude_id);
        }
        
        $this->db->query($sql);
        return $this->db->next_record();
    }
}
