<!-- Invoice Edit Form Content -->
<div class="page-header">
    <div class="actions">
        <a href="view.php?id=<?php echo $invoice_data['id']; ?>" class="btn btn-secondary">Back to View</a>
    </div>
</div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="invoice-form">
        <div class="form-section">
            <h3>Basic Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id">Customer *</label>
                    <select name="customer_id" id="customer_id" required onchange="updateProjects()">
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" 
                                    <?php echo ($invoice_data['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="project_id">Project</label>
                    <select name="project_id" id="project_id">
                        <option value="">All Projects</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="invoice_date">Invoice Date *</label>
                    <input type="date" name="invoice_date" id="invoice_date" required
                           value="<?php echo $invoice_data['invoice_date']; ?>">
                </div>

                <div class="form-group">
                    <label>Contract Type</label>
                    <input type="text" value="<?php echo ucfirst(str_replace('_', ' ', $invoice_data['contract_type'])); ?>" readonly class="readonly">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Period</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="period_start">Period Start *</label>
                    <input type="date" name="period_start" id="period_start" required
                           value="<?php echo !empty($invoice_data['period_start']) ? $invoice_data['period_start'] : (isset($_GET['period_start']) ? $_GET['period_start'] : ''); ?>">
                </div>

                <div class="form-group">
                    <label for="period_end">Period End *</label>
                    <input type="date" name="period_end" id="period_end" required
                           value="<?php echo !empty($invoice_data['period_end']) ? $invoice_data['period_end'] : (isset($_GET['period_end']) ? $_GET['period_end'] : ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Invoice Details</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="total_hours">Total Hours</label>
                    <input type="number" step="0.01" name="total_hours" id="total_hours"
                           value="<?php echo number_format($invoice_data['total_hours'], 2); ?>">
                </div>

                <div class="form-group">
                    <label for="total_amount">Net Amount (€) *</label>
                    <input type="number" step="0.01" name="total_amount" id="total_amount" required
                           value="<?php echo number_format($invoice_data['total_amount'], 2); ?>"
                           onchange="calculateTotals()">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="vat_rate">VAT Rate (%)</label>
                    <input type="number" step="0.01" name="vat_rate" id="vat_rate"
                           value="<?php echo number_format($invoice_data['vat_rate'], 2); ?>"
                           onchange="calculateTotals()">
                </div>

                <div class="form-group">
                    <label>Gross Amount (€)</label>
                    <input type="text" id="gross_amount_display" readonly class="readonly"
                           value="<?php echo number_format($invoice_data['gross_amount'], 2); ?>">
                </div>
            </div>

            <?php if ($invoice_data['contract_type'] === 'fixed_monthly'): ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fixed Hours</label>
                        <input type="text" value="<?php echo number_format($invoice_data['fixed_hours'], 2); ?>" readonly class="readonly">
                    </div>

                    <div class="form-group">
                        <label>Current Carryover</label>
                        <input type="text" value="<?php echo number_format($invoice_data['carryover_current'], 2); ?>h" readonly class="readonly">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-section">
            <h3>Description</h3>
            
            <div class="form-group">
                <label for="description">Invoice Description</label>
                <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars($invoice_data['description']); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Invoice</button>
            <a href="view.php?id=<?php echo $invoice_data['id']; ?>" class="btn btn-secondary">Cancel</a>
            <?php if ($invoice_data['status'] === 'draft'): ?>
                <button type="button" onclick="confirmDelete()" class="btn btn-danger">Delete Invoice</button>
            <?php endif; ?>
        </div>
    </form>

<script>
// Projects data for filtering
const projects = <?php echo json_encode($projects); ?>;
const currentProjectId = <?php echo $invoice_data['project_id'] ?: 'null'; ?>;

function updateProjects() {
    const customerId = document.getElementById('customer_id').value;
    const projectSelect = document.getElementById('project_id');
    
    // Clear existing options
    projectSelect.innerHTML = '<option value="">All Projects</option>';
    
    if (customerId) {
        // Filter projects by customer
        const customerProjects = projects.filter(p => p.customer_id == customerId);
        
        customerProjects.forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.name;
            if (project.id == currentProjectId) {
                option.selected = true;
            }
            projectSelect.appendChild(option);
        });
    }
}

function calculateTotals() {
    const netAmount = parseFloat(document.getElementById('total_amount').value) || 0;
    const vatRate = parseFloat(document.getElementById('vat_rate').value) || 0;
    
    const vatAmount = netAmount * (vatRate / 100);
    const grossAmount = netAmount + vatAmount;
    
    document.getElementById('gross_amount_display').value = grossAmount.toFixed(2);
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
        window.location.href = 'delete.php?id=<?php echo $invoice_data['id']; ?>';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProjects();
    calculateTotals();
});
</script>

<style>
.page-header {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 20px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
    display: inline-block;
    margin-right: 10px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

.invoice-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.form-section {
    margin-bottom: 30px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.form-section h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.form-row {
    display: flex;
    gap: 20px;
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
.form-group select,
.form-group textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.readonly {
    background-color: #f8f9fa;
    color: #6c757d;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>
