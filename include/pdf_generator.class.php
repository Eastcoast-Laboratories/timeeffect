<?php
require_once(__DIR__ . '/print.inc.php');

class InvoicePDFGenerator {
    private $db;
    private $user_id;
    private $pdf;
    public $debug_mode = false;
    private $debug_content = array();
    
    public function __construct($db, $user_id, $debug_mode = false) {
        $this->db = $db;
        $this->user_id = $user_id;
        $this->debug_mode = $debug_mode;
        $this->debug_content = array();
    }
    
    /**
     * Generate PDF for invoice
     */
    public function generateInvoice($invoice_id, $include_effort_details = false) {
        // Get invoice data
        $invoice = $this->getInvoiceData($invoice_id);
        if (!$invoice) {
            return false;
        }
        
        // Get user company data
        $user_data = $this->getUserData();
        
        if ($this->debug_mode) {
            // Debug mode: collect content as array
            $this->debug_content = array(
                'invoice_data' => $invoice,
                'user_data' => $user_data,
                'content_sections' => array(),
                'debug_info' => array(
                    'invoice_id' => $invoice_id,
                    'user_id' => $this->user_id,
                    'invoice_data_loaded' => $invoice ? 'YES' : 'NO',
                    'user_data_loaded' => $user_data ? 'YES' : 'NO'
                )
            );
            
            // Generate content sections for debugging
            $this->generateHeader($user_data, $invoice);
            $this->generateCustomerInfo($invoice);
            $this->generateInvoiceDetails($invoice);
            $this->generateSummary($invoice);
            
            if ($include_effort_details) {
                $this->generateEffortAppendix($invoice_id);
            }
            
            $this->generateFooter($user_data);
            
            return $this->debug_content;
        } else {
            // Normal PDF generation using FPDF
            $this->pdf = new PJPDF('P', 'pt');
            $this->pdf->AliasNbPages();
            $this->pdf->AddPage();
            $this->pdf->SetAutoPageBreak(false, 0);
            
            // Generate invoice content
            $this->generateHeader($user_data, $invoice);
            $this->generateCustomerInfo($invoice);
            $this->generateInvoiceDetails($invoice);
            $this->generateSummary($invoice);
            
            if ($include_effort_details) {
                $this->generateEffortAppendix($invoice_id);
            }
            
            $this->generateFooter($user_data);
            // var_dump($this->pdf ); die;
            return $this->pdf;
        }
    }
    
    /**
     * Get complete invoice data
     */
    private function getInvoiceData($invoice_id) {
        $prefix = $GLOBALS['_PJ_table_prefix'];
        $sql = "SELECT i.*, c.customer_name as customer_name, c.customer_address as customer_address,
                       p.project_name as project_name
                FROM {$prefix}invoices i
                JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON i.customer_id = c.id
                LEFT JOIN " . $GLOBALS['_PJ_project_table'] . " p ON i.project_id = p.id
                WHERE i.id = " . intval($invoice_id);
        
        if ($this->debug_mode) {
            $this->debug_content['debug_info']['sql_query'] = $sql;
            $this->debug_content['debug_info']['table_prefix'] = $prefix;
            $this->debug_content['debug_info']['customer_table'] = $GLOBALS['_PJ_customer_table'];
            $this->debug_content['debug_info']['project_table'] = $GLOBALS['_PJ_project_table'];
        }
        
        $result = $this->db->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            if ($this->debug_mode) {
                $this->debug_content['debug_info']['sql_result'] = 'SUCCESS';
                $this->debug_content['debug_info']['row_count'] = $result->num_rows;
            }
            return $row;
        }
        
        if ($this->debug_mode) {
            $this->debug_content['debug_info']['sql_result'] = 'FAILED';
            $this->debug_content['debug_info']['sql_error'] = $this->db->error;
        }
        return false;
    }
    
    /**
     * Get user company data
     */
    private function getUserData() {
        $sql = "SELECT * FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id = " . intval($this->user_id);
        $result = $this->db->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return $row;
        }
        return false;
    }
    
    /**
     * Generate PDF header with company info and logo
     */
    private function generateHeader($user_data, $invoice) {
        $header_content = array(
            'company_name' => $user_data['company_name'] ?: $user_data['name'] ?: 'Company Name',
            'company_address' => $user_data['company_address'] ?: '',
            'company_location' => trim(($user_data['company_postal_code'] ?: '') . ' ' . ($user_data['company_city'] ?: '')),
            'company_country' => $user_data['company_country'] ?: '',
            'tax_number' => $user_data['tax_number'] ?: '',
            'vat_number' => $user_data['vat_number'] ?: '',
            'invoice_title' => !empty($GLOBALS['_PJ_strings']['invoice']) ? strtoupper($GLOBALS['_PJ_strings']['invoice']) : 'INVOICE',
            'invoice_number' => $invoice['invoice_number'],
            'invoice_date' => date('d.m.Y', strtotime($invoice['invoice_date'])),
            'invoice_period' => date('d.m.Y', strtotime($invoice['period_start'])) . ' - ' . date('d.m.Y', strtotime($invoice['period_end']))
        );
        
        if ($this->debug_mode) {
            $this->debug_content['content_sections']['header'] = $header_content;
            return;
        }
        
        // PDF generation code using FPDF
        $this->pdf->SetFont('Arial', 'B', 16);
        $this->pdf->SetX($this->pdf->GetPageWidth() - 200);
        $this->pdf->Cell(170, 20, mb_convert_encoding($header_content['company_name'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        
        if ($header_content['company_address']) {
            $this->pdf->SetFont('Arial', '', 10);
            $this->pdf->SetX($this->pdf->GetPageWidth() - 200);
            $this->pdf->Cell(170, 15, mb_convert_encoding($header_content['company_address'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        }
        
        if ($header_content['company_location']) {
            $this->pdf->SetX($this->pdf->GetPageWidth() - 200);
            $this->pdf->Cell(170, 15, mb_convert_encoding($header_content['company_location'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        }
        
        if ($header_content['company_country']) {
            $this->pdf->SetX($this->pdf->GetPageWidth() - 200);
            $this->pdf->Cell(170, 15, mb_convert_encoding($header_content['company_country'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        }
        
        if ($header_content['tax_number']) {
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->SetX($this->pdf->GetPageWidth() - 200);
            $this->pdf->Cell(170, 12, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['tax_no']) ? $GLOBALS['_PJ_strings']['tax_no'] : 'Tax No') . ': ' . $header_content['tax_number'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        }
        
        if ($header_content['vat_number']) {
            $this->pdf->SetX($this->pdf->GetPageWidth() - 200);
            $this->pdf->Cell(170, 12, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['vat_no']) ? $GLOBALS['_PJ_strings']['vat_no'] : 'VAT No') . ': ' . $header_content['vat_number'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        }
        
        // Add some space
        $this->pdf->Ln(10);
        
        // Invoice title and details
        $this->pdf->SetFont('Arial', 'B', 20);
        $this->pdf->SetX(($this->pdf->GetPageWidth() - $this->pdf->GetStringWidth($header_content['invoice_title'])) / 2);
        $this->pdf->Cell($this->pdf->GetStringWidth($header_content['invoice_title']), 25, $header_content['invoice_title'], 0, 1, 'C');
        
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->SetX(30);
        $this->pdf->Cell(200, 18, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['invoice_no']) ? $GLOBALS['_PJ_strings']['invoice_no'] : 'Invoice No') . ': ' . $header_content['invoice_number'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetX(30);
        $this->pdf->Cell(200, 15, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['date']) ? $GLOBALS['_PJ_strings']['date'] : 'Date') . ': ' . $header_content['invoice_date'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->pdf->SetX(30);
        $this->pdf->Cell(200, 15, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['period']) ? $GLOBALS['_PJ_strings']['period'] : 'Period') . ': ' . $header_content['invoice_period'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
    }
    
    /**
     * Generate customer information section
     */
    private function generateCustomerInfo($invoice) {
        $customer_content = array(
            'bill_to_label' => (!empty($GLOBALS['_PJ_strings']['bill_to']) ? $GLOBALS['_PJ_strings']['bill_to'] : 'Bill To') . ':',
            'customer_name' => $invoice['customer_name'],
            'customer_address' => $invoice['customer_address'] ?: '',
            'project_name' => $invoice['project_name'] ?: ''
        );
        
        if ($this->debug_mode) {
            $this->debug_content['content_sections']['customer_info'] = $customer_content;
            return;
        }
        
        // PDF generation code using FPDF
        $this->pdf->Ln(10); // Spacer
        
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->SetX(30);
        $this->pdf->Cell(200, 18, mb_convert_encoding($customer_content['bill_to_label'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->SetX(30);
        $this->pdf->Cell(200, 15, mb_convert_encoding($customer_content['customer_name'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        if ($customer_content['customer_address']) {
            $this->pdf->SetX(30);
            $this->pdf->Cell(200, 15, mb_convert_encoding($customer_content['customer_address'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        }
        
        if ($customer_content['project_name']) {
            $this->pdf->SetX(30);
            $this->pdf->Cell(200, 15, mb_convert_encoding('Project: ' . $customer_content['project_name'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        }
    }
    
    /**
     * Generate invoice details and line items
     */
    private function generateInvoiceDetails($invoice) {
        // Prepare table data
        $table_data = array();
        
        if ($invoice['contract_type'] === 'fixed_monthly') {
            // Fixed contract line
            $description = $invoice['description'] ?: (!empty($GLOBALS['_PJ_strings']['fixed_monthly_contract']) ? $GLOBALS['_PJ_strings']['fixed_monthly_contract'] : 'Fixed monthly contract');
            
            $table_data[] = array(
                'description' => $description,
                'hours' => number_format($invoice['fixed_hours'], 2),
                'rate' => !empty($GLOBALS['_PJ_strings']['fixed']) ? $GLOBALS['_PJ_strings']['fixed'] : 'Fixed',
                'amount' => number_format($invoice['total_amount'], 2) . ' ' . ($GLOBALS['_PJ_currency'] ?? '€')
            );
            
        } else {
            // Hourly billing
            $hourly_rate = $invoice['total_hours'] > 0 ? $invoice['total_amount'] / $invoice['total_hours'] : 0;
            $description = $invoice['description'] ?: (!empty($GLOBALS['_PJ_strings']['professional_services']) ? $GLOBALS['_PJ_strings']['professional_services'] : 'Professional services');
            
            $table_data[] = array(
                'description' => $description,
                'hours' => number_format($invoice['total_hours'], 2),
                'rate' => number_format($hourly_rate, 2) . ' ' . ($GLOBALS['_PJ_currency'] ?? '€'),
                'amount' => number_format($invoice['total_amount'], 2) . ' ' . ($GLOBALS['_PJ_currency'] ?? '€')
            );
        }
        
        $details_content = array(
            'table_data' => $table_data,
            'table_headers' => array(
                'description' => (!empty($GLOBALS['_PJ_strings']['description']) ? $GLOBALS['_PJ_strings']['description'] : 'Description'),
                'hours' => (!empty($GLOBALS['_PJ_strings']['hours']) ? $GLOBALS['_PJ_strings']['hours'] : 'Hours'),
                'rate' => (!empty($GLOBALS['_PJ_strings']['rate']) ? $GLOBALS['_PJ_strings']['rate'] : 'Rate'),
                'amount' => (!empty($GLOBALS['_PJ_strings']['amount']) ? $GLOBALS['_PJ_strings']['amount'] : 'Amount')
            ),
            'carryover_info' => array()
        );
        
        // Carryover information for fixed contracts
        if ($invoice['contract_type'] === 'fixed_monthly' && 
            ($invoice['carryover_previous'] != 0 || $invoice['carryover_current'] != 0)) {
            $details_content['carryover_info'] = array(
                'hours_worked' => number_format($invoice['total_hours'], 2) . 'h',
                'previous_carryover' => number_format($invoice['carryover_previous'], 2) . 'h',
                'current_carryover' => number_format($invoice['carryover_current'], 2) . 'h'
            );
        }
        
        if ($this->debug_mode) {
            $this->debug_content['content_sections']['invoice_details'] = $details_content;
            return;
        }
        
        // PDF generation code using FPDF
        $this->pdf->Ln(10); // Spacer
        
        // Table headers
        $this->pdf->SetFillColor(200, 200, 200);
        $this->pdf->SetFont('Arial', 'B', 10);
        
        $col_widths = array(250, 80, 80, 90);
        $col_positions = array(42.5, 292.5, 372.5, 452.5);
        
        // Header row
        $this->pdf->SetX($col_positions[0]);
        $this->pdf->Cell($col_widths[0], 15, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['description']) ? $GLOBALS['_PJ_strings']['description'] : 'Description'), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
        $this->pdf->SetX($col_positions[1]);
        $this->pdf->Cell($col_widths[1], 15, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['hours']) ? $GLOBALS['_PJ_strings']['hours'] : 'Hours'), 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
        $this->pdf->SetX($col_positions[2]);
        $this->pdf->Cell($col_widths[2], 15, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['rate']) ? $GLOBALS['_PJ_strings']['rate'] : 'Rate'), 'ISO-8859-1', 'UTF-8'), 1, 0, 'R', true);
        $this->pdf->SetX($col_positions[3]);
        $this->pdf->Cell($col_widths[3], 15, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['amount']) ? $GLOBALS['_PJ_strings']['amount'] : 'Amount'), 'ISO-8859-1', 'UTF-8'), 1, 1, 'R', true);
        
        // Data rows
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->SetFont('Arial', '', 10);
        
        foreach ($table_data as $row) {
            $this->pdf->SetX($col_positions[0]);
            $this->pdf->Cell($col_widths[0], 15, mb_convert_encoding($row['description'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', false);
            $this->pdf->SetX($col_positions[1]);
            $this->pdf->Cell($col_widths[1], 15, $row['hours'], 1, 0, 'C', false);
            $this->pdf->SetX($col_positions[2]);
            $this->pdf->Cell($col_widths[2], 15, $row['rate'], 1, 0, 'R', false);
            $this->pdf->SetX($col_positions[3]);
            $this->pdf->Cell($col_widths[3], 15, $row['amount'], 1, 1, 'R', false);
        }
        
        // Carryover information
        if (!empty($details_content['carryover_info'])) {
            $this->pdf->Ln(5);
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->SetX(30);
            $this->pdf->Cell(200, 12, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['hours_worked_period']) ? $GLOBALS['_PJ_strings']['hours_worked_period'] : 'Hours worked this period') . ': ' . $details_content['carryover_info']['hours_worked'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
            $this->pdf->SetX(30);
            $this->pdf->Cell(200, 12, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['previous_carryover']) ? $GLOBALS['_PJ_strings']['previous_carryover'] : 'Previous carryover') . ': ' . $details_content['carryover_info']['previous_carryover'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
            $this->pdf->SetX(30);
            $this->pdf->Cell(200, 12, mb_convert_encoding((!empty($GLOBALS['_PJ_strings']['current_carryover']) ? $GLOBALS['_PJ_strings']['current_carryover'] : 'Current carryover') . ': ' . $details_content['carryover_info']['current_carryover'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        }
    }
    
    /**
     * Generate summary with totals
     */
    private function generateSummary($invoice) {
        // Summary data with localization
        $currency = $GLOBALS['_PJ_currency'] ?? '€';
        $summary_data = array(
            array('label' => (!empty($GLOBALS['_PJ_strings']['net_amount']) ? $GLOBALS['_PJ_strings']['net_amount'] : 'Net Amount') . ':', 'amount' => number_format($invoice['total_amount'], 2) . ' ' . $currency),
            array('label' => (!empty($GLOBALS['_PJ_strings']['vat']) ? $GLOBALS['_PJ_strings']['vat'] : 'VAT') . ' (' . number_format($invoice['vat_rate'], 1) . '%):', 'amount' => number_format($invoice['vat_amount'], 2) . ' ' . $currency),
            array('label' => (!empty($GLOBALS['_PJ_strings']['total_amount']) ? $GLOBALS['_PJ_strings']['total_amount'] : 'Total Amount') . ':', 'amount' => number_format($invoice['gross_amount'], 2) . ' ' . $currency)
        );
        
        $summary_content = array(
            'summary_data' => $summary_data,
            'net_amount' => number_format($invoice['total_amount'], 2) . ' €',
            'vat_rate' => number_format($invoice['vat_rate'], 1) . '%',
            'vat_amount' => number_format($invoice['vat_amount'], 2) . ' €',
            'gross_amount' => number_format($invoice['gross_amount'], 2) . ' €'
        );
        
        if ($this->debug_mode) {
            $this->debug_content['content_sections']['summary'] = $summary_content;
            return;
        }
        
        // PDF generation code using FPDF
        $this->pdf->Ln(10); // Spacer
        
        // Generate summary table (right-aligned)
        $this->pdf->SetFillColor(200, 200, 200);
        $this->pdf->SetFont('Arial', '', 10);
        
        $summary_x = 380; // Adjusted position to fit within page margins
        $label_width = 100;
        $amount_width = 70;
        
        foreach ($summary_data as $row) {
            $this->pdf->SetX($summary_x);
            $this->pdf->Cell($label_width, 11, mb_convert_encoding($row['label'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', true);
            $this->pdf->SetX($summary_x + $label_width);
            $this->pdf->Cell($amount_width, 11, mb_convert_encoding($row['amount'], 'ISO-8859-1', 'UTF-8'), 1, 1, 'R', false);
        }
    }
    
    /**
     * Generate effort details appendix
     */
    private function generateEffortAppendix($invoice_id) {
        // Get efforts
        $prefix = $GLOBALS['_PJ_table_prefix'];
        $sql = "SELECT e.*, ie.id as link_id
                FROM " . $GLOBALS['_PJ_effort_table'] . " e
                JOIN {$prefix}invoice_efforts ie ON e.id = ie.effort_id
                WHERE ie.invoice_id = " . intval($invoice_id) . "
                ORDER BY e.date, e.id";
        
        $this->db->query($sql);
        $efforts = [];
        while ($this->db->next_record()) {
            $efforts[] = $this->db->Record;
        }
        
        if (empty($efforts)) {
            return;
        }
        
        $this->pdf->ezNewPage();
        
        $this->pdf->ezText('Effort Details', 14, array('left' => 0));
        $this->pdf->ezText('', 5, array('left' => 0)); // Spacer
        
        // Prepare effort table data
        $effort_data = array();
        $total_hours = 0;
        
        foreach ($efforts as $effort) {
            $hours = $effort['hours'] ?: ($effort['minutes'] ? $effort['minutes'] / 60 : 0);
            $total_hours += $hours;
            
            $effort_data[] = array(
                'date' => date('d.m.Y', strtotime($effort['date'])),
                'hours' => number_format($hours, 2),
                'description' => $effort['description']
            );
        }
        
        // Generate effort table
        $this->pdf->ezTable($effort_data, array(
            'date' => 'Date',
            'hours' => 'Hours',
            'description' => 'Description'
        ), '', array(
            'width' => 500,
            'cols' => array(
                'date' => array('width' => 80, 'justification' => 'center'),
                'hours' => array('width' => 60, 'justification' => 'center'),
                'description' => array('width' => 360, 'justification' => 'left')
            )
        ));
        
        // Total
        $this->pdf->ezText('', 5, array('left' => 0));
        $this->pdf->ezText('Total: ' . number_format($total_hours, 2) . ' hours (' . count($efforts) . ' entries)', 10, array('left' => 0));
    }
    
    /**
     * Generate footer with payment terms and bank details
     */
    private function generateFooter($user_data) {
        $payment_days = $user_data['payment_terms_days'] ?: 14;
        
        $footer_content = array(
            'payment_info_title' => (!empty($GLOBALS['_PJ_strings']['payment_information']) ? $GLOBALS['_PJ_strings']['payment_information'] : 'Payment Information'),
            'payment_terms' => (!empty($GLOBALS['_PJ_strings']['payment_due_within']) ? $GLOBALS['_PJ_strings']['payment_due_within'] : 'Payment due within') . ' ' . $payment_days . ' ' . (!empty($GLOBALS['_PJ_strings']['days_of_invoice_date']) ? $GLOBALS['_PJ_strings']['days_of_invoice_date'] : 'days of invoice date') . '.',
            'payment_terms_text' => $user_data['payment_terms_text'] ?: '',
            'bank_details' => array(
                'bank_name' => $user_data['bank_name'] ?: '',
                'bank_iban' => $user_data['bank_iban'] ?: '',
                'bank_bic' => $user_data['bank_bic'] ?: ''
            )
        );
        
        if ($this->debug_mode) {
            $this->debug_content['content_sections']['footer'] = $footer_content;
            return;
        }
        
        // PDF generation code using FPDF
        $this->pdf->Ln(10); // Spacer
        
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->SetX(30);
        $this->pdf->Cell(200, 18, mb_convert_encoding($footer_content['payment_info_title'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetX(30);
        $this->pdf->Cell(200, 15, mb_convert_encoding($footer_content['payment_terms'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        if ($footer_content['payment_terms_text']) {
            $this->pdf->SetX(30);
            $this->pdf->MultiCell(500, 15, mb_convert_encoding($footer_content['payment_terms_text'], 'ISO-8859-1', 'UTF-8'), 0, 'L');
        }
        
        $this->pdf->Ln(5);
        
        // Bank details
        if ($footer_content['bank_details']['bank_name'] || $footer_content['bank_details']['bank_iban']) {
            $this->pdf->Ln(5);
            
            $this->pdf->SetFont('Arial', 'B', 11);
            $this->pdf->SetX(30);
            $this->pdf->Cell(200, 15, mb_convert_encoding('Bank Details:', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
            
            $this->pdf->SetFont('Arial', '', 9);
            if ($footer_content['bank_details']['bank_name']) {
                $this->pdf->SetX(30);
                $this->pdf->Cell(200, 12, mb_convert_encoding('Bank: ' . $footer_content['bank_details']['bank_name'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
            }
            if ($footer_content['bank_details']['bank_iban']) {
                $this->pdf->SetX(30);
                $this->pdf->Cell(200, 12, mb_convert_encoding('IBAN: ' . $footer_content['bank_details']['bank_iban'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
            }
            if ($footer_content['bank_details']['bank_bic']) {
                $this->pdf->SetX(30);
                $this->pdf->Cell(200, 12, mb_convert_encoding('BIC: ' . $footer_content['bank_details']['bank_bic'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
            }
        }
    }
    
    /**
     * Output PDF to browser
     */
    public function output($filename = 'invoice.pdf', $dest = 'I') {
        if ($this->debug_mode) {
            // Debug mode: output debug content as JSON
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($this->debug_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if ($dest === 'D') {
            // Download
            $this->pdf->Output('D', $filename);
        } else {
            // Inline view
            $this->pdf->Output('I', $filename);
        }
    }
    
    /**
     * Save PDF to file
     */
    public function save($filepath) {
        return file_put_contents($filepath, $this->pdf->Output('S'));
    }
}
