# Automated Invoice Generation System

## Implementation Status Overview
✅ **COMPLETED:**
- Database schema (all tables created via migration)
- PDF generation engine (FPDF-based with UTF-8 support)
- Invoice management interface (list, create, edit, view)
- Basic invoice templates and forms
- Payment tracking and reminders system
- User settings integration for invoice data
- Report integration (Generate Invoice button)

🚧 **PARTIALLY IMPLEMENTED:**
- User profile extensions (fields exist, UI incomplete)
- Contract management (tables exist, UI missing)
- Carryover calculations (logic exists, needs testing)

❌ **NOT IMPLEMENTED:**
- File upload system for logos/branding
- Template customization interface
- Advanced payment analytics
- Email integration

## Key Requirements
- ✅ Fixed monthly contracts (e.g., 1500€ for 15h)
- ✅ Hour carryover tracking (positive/negative balances)
- ✅ Integration with existing report system
- ✅ Professional PDF invoice generation
- ✅ Invoice numbering and storage
- ✅ VAT calculation and display
- ✅ Detailed effort appendix (optional)

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

### User Profile Extensions for Invoice Generation

#### Extended User Table
```sql
ALTER TABLE user ADD COLUMN company_name VARCHAR(255) NULL;
ALTER TABLE user ADD COLUMN company_address TEXT NULL;
ALTER TABLE user ADD COLUMN company_postal_code VARCHAR(20) NULL;
ALTER TABLE user ADD COLUMN company_city VARCHAR(100) NULL;
ALTER TABLE user ADD COLUMN company_country VARCHAR(100) DEFAULT 'Deutschland';
ALTER TABLE user ADD COLUMN tax_number VARCHAR(50) NULL;
ALTER TABLE user ADD COLUMN vat_number VARCHAR(50) NULL;
ALTER TABLE user ADD COLUMN bank_name VARCHAR(255) NULL;
ALTER TABLE user ADD COLUMN bank_iban VARCHAR(34) NULL;
ALTER TABLE user ADD COLUMN bank_bic VARCHAR(11) NULL;
ALTER TABLE user ADD COLUMN invoice_logo_path VARCHAR(500) NULL;
ALTER TABLE user ADD COLUMN letterhead_image_path VARCHAR(500) NULL;
ALTER TABLE user ADD COLUMN footer_image_path VARCHAR(500) NULL;
ALTER TABLE user ADD COLUMN invoice_number_prefix VARCHAR(20) DEFAULT 'INV';
ALTER TABLE user ADD COLUMN invoice_number_format VARCHAR(50) DEFAULT '{prefix}-{year}-{counter:4}';
ALTER TABLE user ADD COLUMN default_vat_rate DECIMAL(5,2) DEFAULT 19.00;
ALTER TABLE user ADD COLUMN payment_terms_days INT DEFAULT 14;
ALTER TABLE user ADD COLUMN payment_terms_text TEXT DEFAULT 'Zahlbar innerhalb von 14 Tagen ohne Abzug.';
```

#### User Settings Interface Extensions
```php
// user/settings.php - Add invoice settings section
class InvoiceSettings {
    public function renderCompanyInfoSection($user);
    public function renderBankDetailsSection($user);
    public function renderInvoiceTemplateSection($user);
    public function handleLogoUpload($userId, $uploadedFile);
    public function validateIBAN($iban);
    public function validateVATNumber($vatNumber, $country);
}
```

#### File Upload Structure
```
/uploads/user_{user_id}/
  ├── invoice_logo.{ext}        # Company logo for invoices
  ├── letterhead.{ext}          # Letterhead background image
  ├── footer.{ext}              # Footer background/signature
  └── templates/                # Custom invoice templates
      ├── template_1.json
      └── template_2.json
```

#### Invoice Template Data Structure
```json
{
    "template_id": "user_123_default",
    "template_name": "Standard Invoice Template",
    "company_info": {
        "name": "Max Mustermann Consulting",
        "address": "Musterstraße 123",
        "postal_code": "12345",
        "city": "Berlin",
        "country": "Deutschland",
        "tax_number": "123/456/78901",
        "vat_number": "DE123456789",
        "logo_path": "/uploads/user_123/invoice_logo.png"
    },
    "bank_details": {
        "bank_name": "Musterbank AG",
        "iban": "DE89 3704 0044 0532 0130 00",
        "bic": "COBADEFFXXX"
    },
    "layout_settings": {
        "primary_color": "#2c3e50",
        "secondary_color": "#3498db",
        "font_family": "Arial",
        "letterhead_enabled": true,
        "footer_enabled": true
    },
    "payment_terms": {
        "days": 14,
        "text": "Zahlbar innerhalb von 14 Tagen ohne Abzug.",
        "late_fee_enabled": false,
        "late_fee_percentage": 8.0
    }
}
```


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
   if applicable: Carryover: Previous balance, current period, new balance
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

### Phase 7: User Profile Management for Invoicing

#### Settings Interface Implementation
```php
// user/settings.php - Extended invoice settings section
class UserInvoiceSettings {
    public function renderInvoiceSettingsTab($user) {
        return [
            'company_info' => $this->renderCompanySection($user),
            'bank_details' => $this->renderBankSection($user),
            'invoice_template' => $this->renderTemplateSection($user),
            'branding' => $this->renderBrandingSection($user)
        ];
    }
    
    private function renderCompanySection($user) {
        // Company name, address, tax numbers
        // Form validation for required fields
        // Auto-completion suggestions
    }
    
    private function renderBankSection($user) {
        // Bank details with IBAN/BIC validation
        // Real-time validation feedback
        // Secure storage handling
    }
    
    private function renderBrandingSection($user) {
        // Logo upload with preview
        // Letterhead/footer image management
        // Color scheme selection
        // Template preview generation
    }
}
```

#### File Upload Management
```php
class InvoiceFileManager {
    private $uploadPath = '/uploads/user_invoices/';
    private $allowedTypes = ['png', 'jpg', 'jpeg', 'pdf', 'svg'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    public function handleLogoUpload($userId, $file) {
        // Validate file type and size
        // Generate secure filename
        // Create user directory if not exists
        // Optimize image for PDF use
        // Update user profile with new path
    }
    
    public function generateThumbnail($imagePath, $maxWidth = 200) {
        // Create thumbnail for settings preview
        // Maintain aspect ratio
        // Optimize for web display
    }
    
    public function validateAndProcessImage($file, $type) {
        // Image validation and processing
        // Convert to PDF-compatible format
        // Compress for optimal file size
    }
}
```

#### Database Migration for User Extensions
```sql
-- Migration: Add invoice-related columns to user table
-- File: sql/migrations/005_add_user_invoice_fields.sql

-- Company Information
ALTER TABLE user ADD COLUMN company_name VARCHAR(255) NULL COMMENT 'Company name for invoices';
ALTER TABLE user ADD COLUMN company_address TEXT NULL COMMENT 'Full company address';
ALTER TABLE user ADD COLUMN company_postal_code VARCHAR(20) NULL;
ALTER TABLE user ADD COLUMN company_city VARCHAR(100) NULL;
ALTER TABLE user ADD COLUMN company_country VARCHAR(100) DEFAULT 'Deutschland';

-- Tax Information
ALTER TABLE user ADD COLUMN tax_number VARCHAR(50) NULL COMMENT 'Tax identification number';
ALTER TABLE user ADD COLUMN vat_number VARCHAR(50) NULL COMMENT 'VAT registration number';

-- Banking Information
ALTER TABLE user ADD COLUMN bank_name VARCHAR(255) NULL;
ALTER TABLE user ADD COLUMN bank_iban VARCHAR(34) NULL COMMENT 'International Bank Account Number';
ALTER TABLE user ADD COLUMN bank_bic VARCHAR(11) NULL COMMENT 'Bank Identifier Code';

-- Branding Assets
ALTER TABLE user ADD COLUMN invoice_logo_path VARCHAR(500) NULL COMMENT 'Path to company logo';
ALTER TABLE user ADD COLUMN letterhead_image_path VARCHAR(500) NULL COMMENT 'Path to letterhead background';
ALTER TABLE user ADD COLUMN footer_image_path VARCHAR(500) NULL COMMENT 'Path to footer image/signature';

-- Invoice Configuration
ALTER TABLE user ADD COLUMN invoice_number_prefix VARCHAR(20) DEFAULT 'INV' COMMENT 'Prefix for invoice numbers';
ALTER TABLE user ADD COLUMN invoice_number_format VARCHAR(50) DEFAULT '{prefix}-{year}-{counter:4}' COMMENT 'Format template for invoice numbers';
ALTER TABLE user ADD COLUMN default_vat_rate DECIMAL(5,2) DEFAULT 19.00 COMMENT 'Default VAT rate percentage';

-- Payment Terms
ALTER TABLE user ADD COLUMN payment_terms_days INT DEFAULT 14 COMMENT 'Default payment terms in days';
ALTER TABLE user ADD COLUMN payment_terms_text TEXT DEFAULT 'Zahlbar innerhalb von 14 Tagen ohne Abzug.' COMMENT 'Payment terms text for invoices';

-- Template Settings
ALTER TABLE user ADD COLUMN invoice_template_id VARCHAR(100) NULL COMMENT 'Active invoice template identifier';
ALTER TABLE user ADD COLUMN brand_primary_color VARCHAR(7) DEFAULT '#2c3e50' COMMENT 'Primary brand color (hex)';
ALTER TABLE user ADD COLUMN brand_secondary_color VARCHAR(7) DEFAULT '#3498db' COMMENT 'Secondary brand color (hex)';

-- Timestamps
ALTER TABLE user ADD COLUMN invoice_settings_updated_at TIMESTAMP NULL COMMENT 'Last update of invoice settings';

-- Indexes for performance
CREATE INDEX idx_user_company_name ON user(company_name);
CREATE INDEX idx_user_vat_number ON user(vat_number);
```

#### Template System Integration
```php
class InvoiceTemplateManager {
    public function createUserTemplate($userId, $templateData) {
        // Generate template from user settings
        // Extract colors from uploaded logo
        // Create PDF template configuration
        // Store template in user directory
    }
    
    public function updatePDFGenerator($userId) {
        // Update PDF generator with user settings
        // Load branding assets
        // Apply color scheme
        // Configure layout parameters
    }
    
    public function previewTemplate($userId, $sampleData) {
        // Generate preview PDF with sample data
        // Show how invoice will look
        // Allow real-time template adjustments
    }
}
```

#### Integration with Existing PDF Generator
```php
// Modify include/pdf_generator.class.php to use user settings
class InvoicePDFGenerator {
    private $userSettings;
    
    public function __construct($userId) {
        $this->userSettings = $this->loadUserInvoiceSettings($userId);
        $this->initializePDFWithUserSettings();
    }
    
    private function loadUserInvoiceSettings($userId) {
        // Load all user invoice settings from database
        // Include file paths for branding assets
        // Prepare data for PDF generation
    }
    
    private function generateHeaderWithBranding($invoice) {
        // Use user's company information
        // Include logo if available
        // Apply user's color scheme
        // Use letterhead background if set
    }
    
    private function generateFooterWithBranding() {
        // Use user's bank details
        // Include footer image/signature
        // Apply consistent styling
    }
}
```

#### User Interface Components
```html
<!-- user/settings.php - Invoice Settings Tab -->
<div class="invoice-settings-section">
    <h3>Rechnungseinstellungen</h3>
    
    <!-- Company Information -->
    <fieldset>
        <legend>Firmeninformationen</legend>
        <div class="form-group">
            <label for="company_name">Firmenname *</label>
            <input type="text" id="company_name" name="company_name" required>
        </div>
        <div class="form-group">
            <label for="company_address">Adresse *</label>
            <textarea id="company_address" name="company_address" required></textarea>
        </div>
        <!-- Additional company fields -->
    </fieldset>
    
    <!-- Tax Information -->
    <fieldset>
        <legend>Steuerliche Angaben</legend>
        <div class="form-group">
            <label for="tax_number">Steuernummer</label>
            <input type="text" id="tax_number" name="tax_number">
        </div>
        <div class="form-group">
            <label for="vat_number">USt-IdNr.</label>
            <input type="text" id="vat_number" name="vat_number">
        </div>
    </fieldset>
    
    <!-- Bank Details -->
    <fieldset>
        <legend>Bankverbindung</legend>
        <div class="form-group">
            <label for="bank_iban">IBAN *</label>
            <input type="text" id="bank_iban" name="bank_iban" pattern="[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}">
        </div>
        <!-- Additional bank fields -->
    </fieldset>
    
    <!-- Branding -->
    <fieldset>
        <legend>Corporate Design</legend>
        <div class="form-group">
            <label for="invoice_logo">Firmenlogo</label>
            <input type="file" id="invoice_logo" name="invoice_logo" accept="image/*">
            <div class="logo-preview"></div>
        </div>
        <!-- Color picker, template selection -->
    </fieldset>
</div>
```

## Future Enhancements
- Email integration for invoice sending
- Multi-currency support  
- Recurring invoice automation
- Advanced analytics for payment predictions
- Integration with accounting systems (DATEV, etc.)
- QR code generation for payment links
- Multi-language invoice templates
- Advanced template designer with drag-and-drop
- Customer portal for invoice viewing and payment
- API integration for external accounting software