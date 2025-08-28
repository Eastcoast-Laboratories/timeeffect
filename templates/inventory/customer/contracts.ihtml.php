<?php
// Include unified header
include_once(__DIR__ . '/../../shared/header.ihtml.php');
?>

<body>
<div class="container">
    <div class="header">
        <h1><?php echo $page_title; ?></h1>
        <!-- inventory/customer/contracts/list.ihtml - START -->
        <div class="actions">
            <a href="../customer.php" class="btn btn-secondary">Back to Customers</a>
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

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Contract <?php echo htmlspecialchars($_GET['success']); ?> successfully!
        </div>
    <?php endif; ?>

    <!-- Contract Form -->
    <?php if ($action === 'create' || $action === 'edit'): ?>
        <div class="form-section">
            <h3><?php echo $action === 'create' ? 'Create New Contract' : 'Edit Contract'; ?></h3>
            
            <form method="POST" class="contract-form">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $contract_id; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="project_id">Project</label>
                        <select name="project_id" id="project_id">
                            <option value="">All Projects</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id']; ?>" 
                                        <?php echo (isset($contract_data) && $contract_data['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($project['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contract_type">Contract Type *</label>
                        <select name="contract_type" id="contract_type" required onchange="toggleContractFields()">
                            <option value="hourly" <?php echo (isset($contract_data) && $contract_data['contract_type'] === 'hourly') ? 'selected' : ''; ?>>Hourly</option>
                            <option value="fixed_monthly" <?php echo (isset($contract_data) && $contract_data['contract_type'] === 'fixed_monthly') ? 'selected' : ''; ?>>Fixed Monthly</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" id="hourly_fields">
                    <div class="form-group">
                        <label for="hourly_rate">Hourly Rate (€) *</label>
                        <input type="number" step="0.01" name="hourly_rate" id="hourly_rate"
                               value="<?php echo isset($contract_data) ? number_format($contract_data['hourly_rate'], 2) : ''; ?>">
                    </div>
                </div>

                <div class="form-row" id="fixed_fields" style="display: none;">
                    <div class="form-group">
                        <label for="fixed_amount">Fixed Amount (€) *</label>
                        <input type="number" step="0.01" name="fixed_amount" id="fixed_amount"
                               value="<?php echo isset($contract_data) ? number_format($contract_data['fixed_amount'], 2) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="fixed_hours">Fixed Hours *</label>
                        <input type="number" step="0.01" name="fixed_hours" id="fixed_hours"
                               value="<?php echo isset($contract_data) ? number_format($contract_data['fixed_hours'], 2) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" required
                               value="<?php echo isset($contract_data) ? $contract_data['start_date'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date"
                               value="<?php echo isset($contract_data) ? $contract_data['end_date'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3"><?php echo isset($contract_data) ? htmlspecialchars($contract_data['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" value="1" 
                               <?php echo (isset($contract_data) && $contract_data['active']) || !isset($contract_data) ? 'checked' : ''; ?>>
                        Active
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'create' ? 'Create Contract' : 'Update Contract'; ?>
                    </button>
                    <a href="contracts.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Contract List -->
        <div class="contracts-section">
            <div class="section-header">
                <h3>Contracts</h3>
                <a href="contracts.php?customer_id=<?php echo $customer_id; ?>&action=create" class="btn btn-primary">New Contract</a>
            </div>

            <?php if (empty($contracts)): ?>
                <div class="no-data">
                    <p>No contracts found for this customer.</p>
                    <a href="contracts.php?customer_id=<?php echo $customer_id; ?>&action=create" class="btn btn-primary">Create first contract</a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Rate/Amount</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $contract_item): ?>
                            <tr class="<?php echo $contract_item['active'] ? 'active' : 'inactive'; ?>">
                                <td><?php echo htmlspecialchars($contract_item['project_name'] ?? 'All Projects'); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $contract_item['contract_type'])); ?></td>
                                <td>
                                    <?php if ($contract_item['contract_type'] === 'fixed_monthly'): ?>
                                        <?php echo number_format($contract_item['fixed_amount'], 2); ?>€ 
                                        (<?php echo number_format($contract_item['fixed_hours'], 2); ?>h)
                                    <?php else: ?>
                                        <?php echo number_format($contract_item['hourly_rate'], 2); ?>€/h
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('d.m.Y', strtotime($contract_item['start_date'])); ?>
                                    <?php if ($contract_item['end_date']): ?>
                                        - <?php echo date('d.m.Y', strtotime($contract_item['end_date'])); ?>
                                    <?php else: ?>
                                        - ongoing
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $contract_item['active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $contract_item['active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="contracts.php?customer_id=<?php echo $customer_id; ?>&action=edit&id=<?php echo $contract_item['id']; ?>" 
                                       class="btn btn-sm btn-warning">Edit</a>
                                    <?php if ($contract_item['active']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="deactivate">
                                            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                                            <input type="hidden" name="id" value="<?php echo $contract_item['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Deactivate this contract?')">Deactivate</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleContractFields() {
    const contractType = document.getElementById('contract_type').value;
    const hourlyFields = document.getElementById('hourly_fields');
    const fixedFields = document.getElementById('fixed_fields');
    const hourlyRate = document.getElementById('hourly_rate');
    const fixedAmount = document.getElementById('fixed_amount');
    const fixedHours = document.getElementById('fixed_hours');
    
    if (contractType === 'fixed_monthly') {
        hourlyFields.style.display = 'none';
        fixedFields.style.display = 'flex';
        hourlyRate.required = false;
        fixedAmount.required = true;
        fixedHours.required = true;
    } else {
        hourlyFields.style.display = 'flex';
        fixedFields.style.display = 'none';
        hourlyRate.required = true;
        fixedAmount.required = false;
        fixedHours.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleContractFields();
});
</script>

<style>
.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #eee;
    padding-bottom: 15px;
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

.btn-warning {
    background-color: #ffc107;
    color: black;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
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

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

.form-section, .contracts-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h3 {
    margin: 0;
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

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.data-table tr:hover {
    background-color: #f5f5f5;
}

.data-table tr.inactive {
    opacity: 0.6;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-active {
    background-color: #28a745;
    color: white;
}

.status-inactive {
    background-color: #6c757d;
    color: white;
}

.actions {
    display: flex;
    gap: 5px;
}

.no-data {
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 8px;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .section-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px 4px;
    }
}
</style>

</body>
</html>
