<!-- Invoice View Content -->
<div class="page-header">
    <div class="actions">
        <a href="pdf.php?id=<?php echo $invoice_data['id']; ?>" class="btn btn-success">Download PDF</a>
        <?php if ($invoice_data['status'] === 'draft'): ?>
            <a href="edit.php?id=<?php echo $invoice_data['id']; ?>" class="btn btn-warning">Edit</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">Back to List</a>
    </div>
</div>

    <div class="invoice-details">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-info">
                <h2>Invoice <?php echo htmlspecialchars($invoice_data['invoice_number']); ?></h2>
                <div class="status">
                    <span class="status-badge status-<?php echo $invoice_data['status']; ?>">
                        <?php echo ucfirst($invoice_data['status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="invoice-dates">
                <div><strong>Invoice Date:</strong> <?php echo date('d.m.Y', strtotime($invoice_data['invoice_date'])); ?></div>
                <div><strong>Period:</strong> 
                    <?php echo date('d.m.Y', strtotime($invoice_data['period_start'])); ?> - 
                    <?php echo date('d.m.Y', strtotime($invoice_data['period_end'])); ?>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="section">
            <h3>Customer Information</h3>
            <div class="customer-info">
                <div><strong>Name:</strong> <?php echo htmlspecialchars($invoice_data['customer_name']); ?></div>
                <?php if ($invoice_data['customer_address']): ?>
                    <div><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($invoice_data['customer_address'])); ?></div>
                <?php endif; ?>
                <?php if ($invoice_data['project_name']): ?>
                    <div><strong>Project:</strong> <?php echo htmlspecialchars($invoice_data['project_name']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="section">
            <h3>Invoice Summary</h3>
            <div class="invoice-summary">
                <div class="summary-row">
                    <span>Contract Type:</span>
                    <span><?php echo ucfirst(str_replace('_', ' ', $invoice_data['contract_type'])); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Total Hours:</span>
                    <span><?php echo number_format($invoice_data['total_hours'], 2); ?>h</span>
                </div>
                
                <?php if ($invoice_data['contract_type'] === 'fixed_monthly'): ?>
                    <div class="summary-row">
                        <span>Fixed Hours:</span>
                        <span><?php echo number_format($invoice_data['fixed_hours'], 2); ?>h</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Previous Carryover:</span>
                        <span><?php echo number_format($invoice_data['carryover_previous'], 2); ?>h</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Current Carryover:</span>
                        <span><?php echo number_format($invoice_data['carryover_current'], 2); ?>h</span>
                    </div>
                <?php endif; ?>
                
                <div class="summary-row">
                    <span>Net Amount:</span>
                    <span><?php echo number_format($invoice_data['total_amount'], 2); ?>€</span>
                </div>
                
                <div class="summary-row">
                    <span>VAT (<?php echo number_format($invoice_data['vat_rate'], 1); ?>%):</span>
                    <span><?php echo number_format($invoice_data['vat_amount'], 2); ?>€</span>
                </div>
                
                <div class="summary-row total">
                    <span><strong>Total Amount:</strong></span>
                    <span><strong><?php echo number_format($invoice_data['gross_amount'], 2); ?>€</strong></span>
                </div>
            </div>
        </div>

        <?php if ($invoice_data['description']): ?>
            <!-- Description -->
            <div class="section">
                <h3>Description</h3>
                <div class="description">
                    <?php echo nl2br(htmlspecialchars($invoice_data['description'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($invoice_efforts)): ?>
            <!-- Linked Efforts -->
            <div class="section">
                <h3>Included Efforts (<?php echo count($invoice_efforts); ?> entries)</h3>
                <div class="efforts-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Hours</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice_efforts as $effort): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y', strtotime($effort['date'])); ?></td>
                                    <td><?php echo number_format($effort['hours'] ?? ($effort['minutes'] ?? 0) / 60, 2); ?>h</td>
                                    <td><?php echo htmlspecialchars($effort['description']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payments Section -->
        <div class="section">
            <h3>Payments</h3>
            
            <?php if (!empty($payments)): ?>
                <div class="payments-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Notes</th>
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
                                <td><strong>Total Paid:</strong></td>
                                <td><strong><?php echo number_format($total_paid, 2); ?>€</strong></td>
                                <td colspan="2">
                                    <strong>Outstanding: <?php echo number_format($invoice_data['gross_amount'] - $total_paid, 2); ?>€</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p>No payments recorded yet.</p>
            <?php endif; ?>

            <!-- Add Payment Form -->
            <?php if ($invoice_data['status'] !== 'cancelled'): ?>
                <div class="add-payment">
                    <h4>Add Payment</h4>
                    <form method="POST" class="payment-form">
                        <input type="hidden" name="action" value="add_payment">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_amount">Amount</label>
                                <input type="number" step="0.01" name="payment_amount" id="payment_amount" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_date">Date</label>
                                <input type="date" name="payment_date" id="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_method">Method</label>
                                <select name="payment_method" id="payment_method">
                                    <option value="">Select Method</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_notes">Notes</label>
                            <input type="text" name="payment_notes" id="payment_notes" placeholder="Optional notes">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Payment</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Status Management -->
        <div class="section">
            <h3>Status Management</h3>
            <form method="POST" class="status-form">
                <input type="hidden" name="action" value="update_status">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Change Status:</label>
                        <select name="status" id="status">
                            <option value="draft" <?php echo ($invoice_data['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="sent" <?php echo ($invoice_data['status'] === 'sent') ? 'selected' : ''; ?>>Sent</option>
                            <option value="paid" <?php echo ($invoice_data['status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="cancelled" <?php echo ($invoice_data['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Status</button>
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
}

.form-group input,
.form-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.description {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #007bff;
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
