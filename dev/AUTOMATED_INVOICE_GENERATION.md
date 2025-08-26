# Automated Invoice Generation System

## Overview
System for automated invoice generation with support for fixed monthly contracts, hour carryover tracking, and professional PDF invoices.

## Key Requirements
- Fixed monthly contracts (e.g., 1500€ for 15h)
- Hour carryover tracking (positive/negative balances)
- Integration with existing report system
- Professional PDF invoice generation
- Invoice numbering and storage
- VAT calculation and display
- Detailed effort appendix (optional)

## Database Schema Extensions

### New Tables

#### `invoices`
```sql
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    project_id INT NULL,
    invoice_date DATE NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    contract_type ENUM('hourly', 'fixed_monthly') DEFAULT 'hourly',
    fixed_amount DECIMAL(10,2) NULL,
    fixed_hours DECIMAL(8,2) NULL,
    total_hours DECIMAL(8,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    vat_rate DECIMAL(5,2) DEFAULT 19.00,
    vat_amount DECIMAL(10,2) NOT NULL,
    gross_amount DECIMAL(10,2) NOT NULL,
    carryover_previous DECIMAL(8,2) DEFAULT 0,
    carryover_current DECIMAL(8,2) DEFAULT 0,
    description TEXT,
    status ENUM('draft', 'sent', 'paid', 'cancelled') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);
```
 
#### `invoice_items`
```sql
CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(8,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);
```

#### `invoice_efforts`
```sql
CREATE TABLE invoice_efforts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    effort_id INT NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (effort_id) REFERENCES efforts(id)
);
```

#### `customer_contracts`
```sql
CREATE TABLE customer_contracts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    project_id INT NULL,
    contract_type ENUM('hourly', 'fixed_monthly') DEFAULT 'hourly',
    fixed_amount DECIMAL(10,2) NULL,
    fixed_hours DECIMAL(8,2) NULL,
    hourly_rate DECIMAL(10,2) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (project_id) REFERENCES projects(id)
);
```

#### `hour_carryovers`
```sql
CREATE TABLE hour_carryovers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    project_id INT NULL,
    period_year INT NOT NULL,
    period_month INT NOT NULL,
    contracted_hours DECIMAL(8,2) NOT NULL,
    actual_hours DECIMAL(8,2) NOT NULL,
    carryover_hours DECIMAL(8,2) NOT NULL,
    cumulative_carryover DECIMAL(8,2) NOT NULL,
    invoice_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    UNIQUE KEY unique_period (customer_id, project_id, period_year, period_month)
);
```

erweitere die eingeloggtn user daten um adresse, bank daten, ... und ein logo, Briefkopf und brieffuss images


## Implementation Plan

### Phase 1: Database Setup
1. integrate into existing migration for new tables
2. Add invoice settings to system configuration .env and config.inc.php
3. Extend customer/project forms for contract setup

#### Additional Tables for AI-Driven Features
```sql
-- Payment tracking
CREATE TABLE invoice_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    payment_date DATE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- Payment reminders
CREATE TABLE payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    reminder_type ENUM('first', 'second', 'final') NOT NULL,
    sent_date DATE,
    due_date DATE,
    reminder_text TEXT,
    status ENUM('pending', 'sent', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- Invoice templates
CREATE TABLE invoice_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    header_html TEXT,
    footer_html TEXT,
    css_styles TEXT,
    personalization_data JSON,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

### Phase 2: Contract Management
1. **Customer Contract Setup**
   - Add contract tab to customer edit form
   - Support multiple contract types per customer
   - Contract validation and date ranges

2. **Contract Types**
   - **Hourly**: Traditional time-based billing
   - **Fixed Monthly**: Fixed amount for fixed hours with carryover

### Phase 3: Report Integration
1. **Enhanced Report Generation**
   - Modify existing report system
   - Add "Generate Invoice" option when marking efforts as billed
   - Pre-calculate carryover before invoice generation
   - suggest invoice-number, but make it editable before submitting

2. **Invoice Generation Workflow**
   ```
   Report → Select Efforts → Mark as Billed → Preview → Generate Invoice
   ```

### Phase 4: Invoice Generation Engine
1. **PDF Generation**
   - Based on your provided invoice example
   - Professional layout with company branding
   - Customizable templates per user/customer
   - Summary section with totals
   - Optional detailed effort appendix which is based on the current report as csv or pdf but erweitert um eventuelles fixed-rate übertrag, ...

2. **Template Customization**
   - Automatic brand color extraction from user logo
   - Smart layout adjustments based on content length
   - Customer-specific template variations
   - Generated professional descriptions for line items

3. **Payment Tracking**
   - Automatic payment status detection via bank API integration
   - Smart payment reminder generation with personalized text
   - Overdue invoice alerts with escalation levels
   - Payment pattern analysis for customer risk assessment

### Phase 5: Payment Management
1. **Automated Payment Reminders**
   ```php
   class PaymentManager {
       public function generateReminderText($invoice, $reminderType, $customerHistory);
       public function scheduleReminders($invoiceId);
       public function analyzePaymentPatterns($customerId);
   }
   ```

2. **Fixed Contract Invoice Logic**
   ```php
   function generateFixedContractInvoice($customerId, $projectId, $periodStart, $periodEnd) {
       $contract = getActiveContract($customerId, $projectId);
       $efforts = getEffortsForPeriod($customerId, $projectId, $periodStart, $periodEnd);
       
       $actualHours = array_sum(array_column($efforts, 'hours'));
       $contractedHours = $contract['fixed_hours'];
       $fixedAmount = $contract['fixed_amount'];
       
       // Calculate carryover
       $previousCarryover = getPreviousCarryover($customerId, $projectId);
       $currentCarryover = $actualHours - $contractedHours;
       $cumulativeCarryover = $previousCarryover + $currentCarryover;
       
       // Create invoice
       $invoice = [
           'customer_id' => $customerId,
           'project_id' => $projectId,
           'total_hours' => $actualHours,
           'total_amount' => $fixedAmount,
           'carryover_previous' => $previousCarryover,
           'carryover_current' => $cumulativeCarryover,
           'description' => "Bestandsreintraumes Verpack - Monat " . date('m/Y', strtotime($periodStart))
       ];
   ```

2. **Template Structure**
   ```
   Header: Company info, invoice number, date
   Customer: Address and project details
   Summary: Hours, amount, VAT calculation
   Footer: Payment terms, bank details
   Appendix: Detailed effort list (optional)
   ```

3. **PDF Generation Classes**
   ```php
   class InvoicePDFGenerator {
       public function generateInvoice($invoiceId, $includeEffortDetails = false);
       public function generateSummaryPage($invoice);
       public function generateEffortAppendix($efforts);
   }
   ```

### Phase 6: Invoice Management Interface
1. **Invoice List View**
   - Searchable/filterable invoice list
   - Status tracking (draft, sent, paid)
   - Quick actions (view, edit, resend)

2. **Invoice Detail View**
   - Full invoice information
   - Edit capabilities for drafts
   - PDF download/preview
   - Status change options

3. **Integration Points**
   - Link from customer/project views
   - Integration with effort statistics
   - Report generation workflow

## User Interface Flow

### 1. Report Generation with Invoice
```
Statistics → Generate Report → Select Period → Mark Efforts as Billed
↓
[Generate Invoice] Button appears
↓
Invoice Preview → Confirm → Generate PDF → Save Invoice
```

### 2. Manual Invoice Creation
```
Invoices → New Invoice → Select Customer/Project → Choose Period
↓
System calculates hours and carryover → Preview → Generate
```

### 3. Invoice Management
```
Invoices → List View → Filter/Search → Select Invoice
↓
View Details → Edit (if draft) → Download PDF → Change Status
```

## Configuration Settings

### System Settings (per user)
- Invoice number format
- VAT rate (default 19%)
- Company information for invoices
- Payment terms text
- Bank details

### Customer/Project Settings
- Contract type and parameters
- Invoice description templates
- Specific VAT rates (if different)
- Payment terms overrides

## Carryover Calculation Example

### Monthly Fixed Contract: 1500€ for 15h
```
Month 1: Worked 17h → Carryover: +2h
Month 2: Worked 13h → Carryover: -2h → Cumulative: 0h
Month 3: Worked 12h → Carryover: -3h → Cumulative: -3h
```

### Invoice Display
```
Übertrag aus Vormonat: -3h (-300€)
Vereinbarte Stunden pro Monat: 15h (1500€)
Geleistete Stunden: 12h
Übertrag in Folgemonat: -6h (-600€)

Rechnungsbetrag: 1500€ (Pauschal)
```

## Technical Implementation Notes

### File Structure
```
/invoice/
  ├── index.php (Invoice list)
  ├── create.php (New invoice form)
  ├── edit.php (Edit invoice)
  ├── view.php (Invoice details)
  ├── pdf.php (PDF generation)
  └── ajax/ (AJAX handlers)

/include/
  ├── invoice.class.php
  ├── contract.class.php
  └── carryover.class.php

/templates/invoice/
  ├── list.ihtml.php
  ├── form.ihtml.php
  ├── view.ihtml.php
  └── pdf_template.php
```

### Integration with Existing System
1. Extend report generation to include invoice option
2. Add invoice links to customer/project views  
3. Update effort billing to link with invoices
4. Extend user permissions for invoice management

## Security Considerations
- Invoice number uniqueness enforcement
- User permission checks for invoice operations
- Audit trail for invoice changes
- Secure PDF generation and storage

## Migration Strategy
1. Deploy database changes and update `timeeffect.sql`
2. Update existing system with invoice integration points:
   - Add "Generate Invoice" button to report/index.php
   - Extend statistic/efforts.php with invoice generation workflow
   - Update templates/statistic/customer/project/effort/row.ihtml.php for invoice linking
   - Modify inventory/customer.php and inventory/projects.php for contract management
   - Add invoice management pages (list, view, edit invoices)
   - Integrate invoice settings into user/settings.php


### Phase 6: Template System
1. **Dynamic Template Generation**
   ```php
   class TemplateEngine {
       public function generateCustomTemplate($userId, $customerPreferences);
       public function extractBrandColors($logoPath);
       public function optimizeLayoutForContent($contentLength);
       public function personalizeForCustomer($templateId, $customerId);
   }
   ```

2. **Template Management Interface**
   - Visual template editor with smart suggestions
   - Brand consistency checker
   - A/B testing for template effectiveness
   - Customer-specific template assignments

## Future Enhancements
- Email integration for invoice sending
- Multi-currency support
- Recurring invoice automation
- Advanced analytics for payment predictions
- Integration with accounting systems (DATEV, etc.)