<!-- Invoice View Content -->
<div class="page-header">
    <div class="actions">
        <a href="pdf.php?id=<?php echo $invoice_data['id']; ?>" class="btn btn-success"><?php if(!empty($GLOBALS['_PJ_strings']['download_pdf'])) echo $GLOBALS['_PJ_strings']['download_pdf']; else echo 'Download PDF'; ?></a>
        <?php if ($invoice_data['status'] === 'draft'): ?>
            <a href="edit.php?id=<?php echo $invoice_data['id']; ?>" class="btn btn-warning"><?php if(!empty($GLOBALS['_PJ_strings']['edit'])) echo $GLOBALS['_PJ_strings']['edit']; else echo 'Edit'; ?></a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary"><?php if(!empty($GLOBALS['_PJ_strings']['back_to_list'])) echo $GLOBALS['_PJ_strings']['back_to_list']; else echo 'Back to List'; ?></a>
    </div>
</div>

    <div class="invoice-details">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-info">
                <h2><?php if(!empty($GLOBALS['_PJ_strings']['invoice'])) echo $GLOBALS['_PJ_strings']['invoice']; else echo 'Invoice'; ?> <?php echo htmlspecialchars($invoice_data['invoice_number']); ?></h2>
                <div class="status">
                    <span class="status-badge status-<?php echo $invoice_data['status']; ?>">
                        <?php echo ucfirst($invoice_data['status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="invoice-dates">
                <div><strong><?php if(!empty($GLOBALS['_PJ_strings']['invoice_date'])) echo $GLOBALS['_PJ_strings']['invoice_date']; else echo 'Invoice Date'; ?>:</strong> <?php echo date('d.m.Y', strtotime($invoice_data['invoice_date'])); ?></div>
                <div><strong><?php if(!empty($GLOBALS['_PJ_strings']['period'])) echo $GLOBALS['_PJ_strings']['period']; else echo 'Period'; ?>:</strong> 
                    <?php echo date('d.m.Y', strtotime($invoice_data['period_start'])); ?> - 
                    <?php echo date('d.m.Y', strtotime($invoice_data['period_end'])); ?>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="section">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['customer_information'])) echo $GLOBALS['_PJ_strings']['customer_information']; else echo 'Customer Information'; ?></h3>
            <div class="customer-info">
                <div><strong><?php if(!empty($GLOBALS['_PJ_strings']['name'])) echo $GLOBALS['_PJ_strings']['name']; else echo 'Name'; ?>:</strong> <?php echo htmlspecialchars($invoice_data['customer_name']); ?></div>
                <?php if ($invoice_data['customer_address']): ?>
                    <div><strong><?php if(!empty($GLOBALS['_PJ_strings']['address'])) echo $GLOBALS['_PJ_strings']['address']; else echo 'Address'; ?>:</strong> <?php echo nl2br(htmlspecialchars($invoice_data['customer_address'])); ?></div>
                <?php endif; ?>
                <?php if ($invoice_data['project_name']): ?>
                    <div><strong><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project']; else echo 'Project'; ?>:</strong> <?php echo htmlspecialchars($invoice_data['project_name']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="section">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['invoice_summary'])) echo $GLOBALS['_PJ_strings']['invoice_summary']; else echo 'Invoice Summary'; ?></h3>
            <div class="invoice-summary">
                <div class="summary-row">
                    <span><?php if(!empty($GLOBALS['_PJ_strings']['contract_type'])) echo $GLOBALS['_PJ_strings']['contract_type']; else echo 'Contract Type'; ?>:</span>
                    <span><?php echo ucfirst(str_replace('_', ' ', $invoice_data['contract_type'])); ?></span>
                </div>
                
                <div class="summary-row">
                    <span><?php if(!empty($GLOBALS['_PJ_strings']['total_hours'])) echo $GLOBALS['_PJ_strings']['total_hours']; else echo 'Total Hours'; ?>:</span>
                    <span><?php echo number_format($invoice_data['total_hours'], 2); ?>h</span>
                </div>
                
                <?php if ($invoice_data['contract_type'] === 'fixed_monthly'): ?>
                    <div class="summary-row">
                        <span><?php if(!empty($GLOBALS['_PJ_strings']['fixed_hours'])) echo $GLOBALS['_PJ_strings']['fixed_hours']; else echo 'Fixed Hours'; ?>:</span>
                        <span><?php echo number_format($invoice_data['fixed_hours'], 2); ?>h</span>
                    </div>
                    
                    <div class="summary-row">
                        <span><?php if(!empty($GLOBALS['_PJ_strings']['previous_carryover'])) echo $GLOBALS['_PJ_strings']['previous_carryover']; else echo 'Previous Carryover'; ?>:</span>
                        <span><?php echo number_format($invoice_data['carryover_previous'], 2); ?>h</span>
                    </div>
                    
                    <div class="summary-row">
                        <span><?php if(!empty($GLOBALS['_PJ_strings']['current_carryover'])) echo $GLOBALS['_PJ_strings']['current_carryover']; else echo 'Current Carryover'; ?>:</span>
                        <span><?php echo number_format($invoice_data['carryover_current'], 2); ?>h</span>
                    </div>
                <?php endif; ?>
                
                <div class="summary-row">
                    <span><?php if(!empty($GLOBALS['_PJ_strings']['net_amount'])) echo $GLOBALS['_PJ_strings']['net_amount']; else echo 'Net Amount'; ?>:</span>
                    <span><?php echo number_format(floatval($invoice_data['total_amount']), 2); ?>€</span>
                </div>
                
                <div class="summary-row">
                    <span><?php if(!empty($GLOBALS['_PJ_strings']['vat_rate'])) echo $GLOBALS['_PJ_strings']['vat_rate']; else echo 'VAT'; ?> (<?php echo number_format(floatval($invoice_data['vat_rate']), 1); ?>%):</span>
                    <span><?php echo number_format(floatval($invoice_data['vat_amount']), 2); ?>€</span>
                </div>
                
                <div class="summary-row total">
                    <span><strong><?php if(!empty($GLOBALS['_PJ_strings']['total_amount'])) echo $GLOBALS['_PJ_strings']['total_amount']; else echo 'Total Amount'; ?>:</strong></span>
                    <span><strong><?php echo number_format(floatval($invoice_data['gross_amount']), 2); ?>€</strong></span>
                </div>
            </div>
        </div>

        <?php if ($invoice_data['description']): ?>
            <!-- Description -->
            <div class="section">
                <h3><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description']; else echo 'Description'; ?></h3>
                <div class="description">
                    <?php echo nl2br(htmlspecialchars($invoice_data['description'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($invoice_efforts)): ?>
            <!-- Linked Efforts -->
            <div class="section">
                <h3><?php if(!empty($GLOBALS['_PJ_strings']['included_efforts'])) echo sprintf($GLOBALS['_PJ_strings']['included_efforts'], count($invoice_efforts)); else echo 'Included Efforts (' . count($invoice_efforts) . ' entries)'; ?></h3>
                <form method="post" action="view.php?id=<?php echo $invoice_data['id']; ?>" id="efforts-form">
                    <input type="hidden" name="action" value="">
                    <div class="efforts-table">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-efforts" title="<?php echo !empty($GLOBALS['_PJ_strings']['select_all']) ? $GLOBALS['_PJ_strings']['select_all'] : 'Select All'; ?>"></th>
                                    <th><?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date']; else echo 'Date'; ?></th>
                                    <th><?php if(!empty($GLOBALS['_PJ_strings']['hours'])) echo $GLOBALS['_PJ_strings']['hours']; else echo 'Hours'; ?></th>
                                    <th><?php if(!empty($GLOBALS['_PJ_strings']['hourly_rate'])) echo $GLOBALS['_PJ_strings']['hourly_rate']; else echo 'Rate'; ?></th>
                                    <th><?php if(!empty($GLOBALS['_PJ_strings']['amount'])) echo $GLOBALS['_PJ_strings']['amount']; else echo 'Amount'; ?></th>
                                    <th><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description']; else echo 'Description'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoice_efforts as $effort): 
                                    $effort_hours = floatval($effort['hours'] ?? 0);
                                    $effort_rate = floatval($effort['rate'] ?? 0);
                                    $effort_amount = $effort_hours * $effort_rate;
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="effort_ids[]" value="<?php echo $effort['id']; ?>" class="effort-checkbox"></td>
                                        <td><?php echo date('d.m.Y', strtotime($effort['date'])); ?></td>
                                        <td><?php echo number_format($effort_hours, 2); ?>h</td>
                                        <td><?php echo number_format($effort_rate, 2); ?>€/h</td>
                                        <td><?php echo number_format($effort_amount, 2); ?>€</td>
                                        <td><?php echo htmlspecialchars($effort['description']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="effort-actions" style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <button type="button" class="btn btn-danger" onclick="removeSelectedEfforts()" id="btn-remove-efforts" disabled>
                            <?php echo !empty($GLOBALS['_PJ_strings']['remove_from_invoice']) ? $GLOBALS['_PJ_strings']['remove_from_invoice'] : 'Remove from Invoice'; ?>
                        </button>
                        <a href="../report/index.php?customer_budget_currency=EUR&report=1&cid=<?php echo $invoice_data['customer_id']; ?>&pid=<?php echo $invoice_data['project_id'] ?? ''; ?>&syear=<?php echo date('Y', strtotime($invoice_data['period_start'])); ?>&smonth=<?php echo date('n', strtotime($invoice_data['period_start'])); ?>&sday=<?php echo date('j', strtotime($invoice_data['period_start'])); ?>&eyear=<?php echo date('Y', strtotime($invoice_data['period_end'])); ?>&emonth=<?php echo date('n', strtotime($invoice_data['period_end'])); ?>&eday=<?php echo date('j', strtotime($invoice_data['period_end'])); ?>" class="btn btn-primary" id="btn-view-report">
                            <?php echo !empty($GLOBALS['_PJ_strings']['view_in_report']) ? $GLOBALS['_PJ_strings']['view_in_report'] : 'View in Report'; ?>
                        </a>
                    </div>
                </form>
            </div>
            <script>
            document.getElementById('select-all-efforts').addEventListener('change', function() {
                document.querySelectorAll('.effort-checkbox').forEach(cb => cb.checked = this.checked);
                updateEffortButtons();
            });
            document.querySelectorAll('.effort-checkbox').forEach(cb => {
                cb.addEventListener('change', updateEffortButtons);
            });
            function updateEffortButtons() {
                const checked = document.querySelectorAll('.effort-checkbox:checked').length;
                document.getElementById('btn-remove-efforts').disabled = checked === 0;
            }
            function removeSelectedEfforts() {
                if (confirm('<?php echo !empty($GLOBALS['_PJ_strings']['confirm_remove_efforts']) ? addslashes($GLOBALS['_PJ_strings']['confirm_remove_efforts']) : 'Remove selected efforts from this invoice?'; ?>')) {
                    document.querySelector('input[name="action"]').value = 'remove_efforts';
                    document.getElementById('efforts-form').submit();
                }
            }
            </script>
        <?php endif; ?>

        <!-- Payments Section -->
        <div class="section">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['payments'])) echo $GLOBALS['_PJ_strings']['payments']; else echo 'Payments'; ?></h3>
            
            <?php if (!empty($payments)): ?>
                <div class="payments-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date']; else echo 'Date'; ?></th>
                                <th><?php if(!empty($GLOBALS['_PJ_strings']['amount'])) echo $GLOBALS['_PJ_strings']['amount']; else echo 'Amount'; ?></th>
                                <th><?php if(!empty($GLOBALS['_PJ_strings']['method'])) echo $GLOBALS['_PJ_strings']['method']; else echo 'Method'; ?></th>
                                <th><?php if(!empty($GLOBALS['_PJ_strings']['notes'])) echo $GLOBALS['_PJ_strings']['notes']; else echo 'Notes'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_paid = 0;
                            foreach ($payments as $pmt): 
                                $total_paid += $pmt['amount'];
                            ?>
                                <tr>
                                    <td><?php echo date('d.m.Y', strtotime($pmt['payment_date'])); ?></td>
                                    <td><?php echo number_format($pmt['amount'], 2); ?>€</td>
                                    <td><?php echo htmlspecialchars($pmt['payment_method'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($pmt['notes'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total">
                                <td><strong><?php if(!empty($GLOBALS['_PJ_strings']['total_paid'])) echo $GLOBALS['_PJ_strings']['total_paid']; else echo 'Total Paid'; ?>:</strong></td>
                                <td><strong><?php echo number_format($total_paid, 2); ?>€</strong></td>
                                <td colspan="2">
                                    <strong><?php if(!empty($GLOBALS['_PJ_strings']['outstanding'])) echo $GLOBALS['_PJ_strings']['outstanding']; else echo 'Outstanding'; ?>: <?php echo number_format($invoice_data['gross_amount'] - $total_paid, 2); ?>€</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p><?php if(!empty($GLOBALS['_PJ_strings']['no_payments_recorded'])) echo $GLOBALS['_PJ_strings']['no_payments_recorded']; else echo 'No payments recorded yet.'; ?></p>
            <?php endif; ?>

            <!-- Add Payment Form -->
            <?php if ($invoice_data['status'] !== 'cancelled'): ?>
                <div class="add-payment">
                    <h4><?php if(!empty($GLOBALS['_PJ_strings']['add_payment'])) echo $GLOBALS['_PJ_strings']['add_payment']; else echo 'Add Payment'; ?></h4>
                    <form method="POST" class="payment-form">
                        <input type="hidden" name="action" value="add_payment">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_amount"><?php if(!empty($GLOBALS['_PJ_strings']['amount'])) echo $GLOBALS['_PJ_strings']['amount']; else echo 'Amount'; ?></label>
                                <input type="number" step="0.01" name="payment_amount" id="payment_amount" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_date"><?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date']; else echo 'Date'; ?></label>
                                <input type="date" name="payment_date" id="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_method"><?php if(!empty($GLOBALS['_PJ_strings']['method'])) echo $GLOBALS['_PJ_strings']['method']; else echo 'Method'; ?></label>
                                <select name="payment_method" id="payment_method">
                                    <option value=""><?php if(!empty($GLOBALS['_PJ_strings']['select_method'])) echo $GLOBALS['_PJ_strings']['select_method']; else echo 'Select Method'; ?></option>
                                    <option value="bank_transfer"><?php if(!empty($GLOBALS['_PJ_strings']['bank_transfer'])) echo $GLOBALS['_PJ_strings']['bank_transfer']; else echo 'Bank Transfer'; ?></option>
                                    <option value="cash"><?php if(!empty($GLOBALS['_PJ_strings']['cash'])) echo $GLOBALS['_PJ_strings']['cash']; else echo 'Cash'; ?></option>
                                    <option value="check"><?php if(!empty($GLOBALS['_PJ_strings']['check'])) echo $GLOBALS['_PJ_strings']['check']; else echo 'Check'; ?></option>
                                    <option value="paypal"><?php if(!empty($GLOBALS['_PJ_strings']['paypal'])) echo $GLOBALS['_PJ_strings']['paypal']; else echo 'PayPal'; ?></option>
                                    <option value="other"><?php if(!empty($GLOBALS['_PJ_strings']['other'])) echo $GLOBALS['_PJ_strings']['other']; else echo 'Other'; ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_notes"><?php if(!empty($GLOBALS['_PJ_strings']['notes'])) echo $GLOBALS['_PJ_strings']['notes']; else echo 'Notes'; ?></label>
                            <input type="text" name="payment_notes" id="payment_notes" placeholder="<?php if(!empty($GLOBALS['_PJ_strings']['optional_notes'])) echo $GLOBALS['_PJ_strings']['optional_notes']; else echo 'Optional notes'; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><?php if(!empty($GLOBALS['_PJ_strings']['add_payment'])) echo $GLOBALS['_PJ_strings']['add_payment']; else echo 'Add Payment'; ?></button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Status Management -->
        <div class="section">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['status_management'])) echo $GLOBALS['_PJ_strings']['status_management']; else echo 'Status Management'; ?></h3>
            <form method="POST" class="status-form">
                <input type="hidden" name="action" value="update_status">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status"><?php if(!empty($GLOBALS['_PJ_strings']['change_status'])) echo $GLOBALS['_PJ_strings']['change_status']; else echo 'Change Status'; ?>:</label>
                        <select name="status" id="status">
                            <option value="draft" <?php echo ($invoice_data['status'] === 'draft') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['draft'])) echo $GLOBALS['_PJ_strings']['draft']; else echo 'Draft'; ?></option>
                            <option value="sent" <?php echo ($invoice_data['status'] === 'sent') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['sent'])) echo $GLOBALS['_PJ_strings']['sent']; else echo 'Sent'; ?></option>
                            <option value="paid" <?php echo ($invoice_data['status'] === 'paid') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['paid'])) echo $GLOBALS['_PJ_strings']['paid']; else echo 'Paid'; ?></option>
                            <option value="cancelled" <?php echo ($invoice_data['status'] === 'cancelled') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['cancelled'])) echo $GLOBALS['_PJ_strings']['cancelled']; else echo 'Cancelled'; ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?php if(!empty($GLOBALS['_PJ_strings']['update_status'])) echo $GLOBALS['_PJ_strings']['update_status']; else echo 'Update Status'; ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<style>
.page-header {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 20px;
}

.actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
    display: inline-block;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-warning {
    background-color: #ffc107;
    color: black;
}

.invoice-details {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    color: #333;
}

.invoice-header {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.invoice-info h2 {
    margin: 0 0 10px 0;
    color: #333;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-draft {
    background-color: #ffc107;
    color: #000;
}

.status-sent {
    background-color: #17a2b8;
    color: #fff;
}

.status-paid {
    background-color: #28a745;
    color: #fff;
}

.status-cancelled {
    background-color: #dc3545;
    color: #fff;
}

.section {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.section:last-child {
    border-bottom: none;
}

.section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.customer-info div,
.invoice-dates div {
    margin-bottom: 8px;
    color: #333;
}

.invoice-summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #ddd;
    color: #333;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    border-top: 2px solid #333;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 16px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.data-table th,
.data-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    color: #333;
}

.data-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.data-table tfoot td {
    background-color: #f8f9fa;
    font-weight: bold;
}

.add-payment,
.status-form {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin-top: 15px;
}

.add-payment h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

.form-group input,
.form-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    color: #333;
}

.description {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #007bff;
    color: #333;
}

/* Dark mode styles for invoice view */
html[data-theme="dark"] .invoice-details {
    background: var(--surface-color) !important;
    color: var(--text-primary) !important;
}

html[data-theme="dark"] .invoice-header {
    background: #334155 !important;
    border-color: var(--border-color) !important;
}

html[data-theme="dark"] .invoice-info h2,
html[data-theme="dark"] .section h3,
html[data-theme="dark"] .add-payment h4 {
    color: var(--text-primary) !important;
}

html[data-theme="dark"] .customer-info div,
html[data-theme="dark"] .invoice-dates div,
html[data-theme="dark"] .summary-row,
html[data-theme="dark"] .description {
    color: var(--text-primary) !important;
}

html[data-theme="dark"] .invoice-summary,
html[data-theme="dark"] .add-payment,
html[data-theme="dark"] .status-form {
    background: #334155 !important;
}

html[data-theme="dark"] .data-table th,
html[data-theme="dark"] .data-table td {
    background: var(--surface-color) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}

html[data-theme="dark"] .data-table th,
html[data-theme="dark"] .data-table tfoot td {
    background: #475569 !important;
}

html[data-theme="dark"] .form-group label {
    color: var(--text-primary) !important;
}

html[data-theme="dark"] .form-group input,
html[data-theme="dark"] .form-group select {
    background: var(--surface-color) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
}

html[data-theme="dark"] .summary-row {
    border-color: var(--border-color) !important;
}

html[data-theme="dark"] .summary-row.total {
    border-color: var(--text-primary) !important;
}

html[data-theme="dark"] .section {
    border-color: var(--border-color) !important;
}

@media (max-width: 768px) {
    .invoice-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .actions {
        flex-wrap: wrap;
    }
    
    .summary-row {
        font-size: 14px;
    }
}
</style>
