<?php
/**
 * Web-based Migration Test - Forces migration execution via web interface
 */

require_once __DIR__ . '/../../bootstrap.php';

// Force migration execution
if (function_exists('checkAndRunMigrations')) {
    $migrations_run = checkAndRunMigrations();
    
    echo "<h2>Migration Execution Results</h2>\n";
    echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
    
    if ($migrations_run === false) {
        echo "<div>❌ MIGRATION: Execution failed</div>\n";
    } elseif (empty($migrations_run)) {
        echo "<div>ℹ️ MIGRATION: No pending migrations</div>\n";
    } else {
        echo "<div>✅ MIGRATION: Executed - " . implode(', ', $migrations_run) . "</div>\n";
    }
    
    // Check if invoices table exists
    global $db;
    $invoices_table = $GLOBALS['_PJ_table_prefix'] . 'invoices';
    $query = "SHOW TABLES LIKE '$invoices_table'";
    $db->query($query);
    
    if ($db->next_record()) {
        echo "<div>✅ INVOICE_TABLE: Table '$invoices_table' exists</div>\n";
        
        // Test invoice number generation
        try {
            require_once __DIR__ . '/../../include/invoice.class.php';
            $invoice = new Invoice();
            $invoice_number = $invoice->generateInvoiceNumber($_PJ_auth->giveValue('id'));
            echo "<div>✅ INVOICE_NUMBER: Generated successfully - $invoice_number</div>\n";
        } catch (Exception $e) {
            echo "<div>❌ INVOICE_NUMBER: Exception - " . $e->getMessage() . "</div>\n";
        }
        
    } else {
        echo "<div>❌ INVOICE_TABLE: Table '$invoices_table' does not exist</div>\n";
    }
    
    echo "</div>\n";
} else {
    echo "<div>❌ MIGRATION: checkAndRunMigrations function not found</div>\n";
}
