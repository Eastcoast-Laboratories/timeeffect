<?php
// Include unified header
include_once(__DIR__ . '/../shared/header.ihtml.php');
?>

<body>
<div class="container">
    <div class="header">
        <h1><?php echo $page_title; ?></h1>
        <div class="actions">
            <a href="create.php" class="btn btn-primary">New Invoice</a>
            <a href="../index.php" class="btn btn-secondary">Back to Main</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="customer_id">Customer:</label>
                <select name="customer_id" id="customer_id">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>" 
                                <?php echo (isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All Status</option>
                    <option value="draft" <?php echo (isset($_GET['status']) && $_GET['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="sent" <?php echo (isset($_GET['status']) && $_GET['status'] == 'sent') ? 'selected' : ''; ?>>Sent</option>
                    <option value="paid" <?php echo (isset($_GET['status']) && $_GET['status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="date_from">From:</label>
                <input type="date" name="date_from" id="date_from" 
                       value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
            </div>

            <div class="filter-group">
                <label for="date_to">To:</label>
                <input type="date" name="date_to" id="date_to" 
                       value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
            </div>

            <div class="filter-group">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Invoice List -->
    <div class="invoice-list">
        <?php if (empty($invoices)): ?>
            <div class="no-data">
                <p>No invoices found.</p>
                <a href="create.php" class="btn btn-primary">Create your first invoice</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Project</th>
                        <th>Date</th>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr class="invoice-row status-<?php echo $inv['status']; ?>">
                            <td class="invoice-number">
                                <a href="view.php?id=<?php echo $inv['id']; ?>">
                                    <?php echo htmlspecialchars($inv['invoice_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($inv['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($inv['project_name'] ?? '-'); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($inv['invoice_date'])); ?></td>
                            <td>
                                <?php echo date('d.m.Y', strtotime($inv['period_start'])); ?> - 
                                <?php echo date('d.m.Y', strtotime($inv['period_end'])); ?>
                            </td>
                            <td class="amount">
                                <?php echo number_format($inv['gross_amount'], 2); ?>€
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $inv['status']; ?>">
                                    <?php echo ucfirst($inv['status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="view.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-info" title="View">👁</a>
                                <?php if ($inv['status'] === 'draft'): ?>
                                    <a href="edit.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-warning" title="Edit">✏</a>
                                <?php endif; ?>
                                <a href="pdf.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-success" title="Download PDF">📄</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Summary -->
            <div class="invoice-summary">
                <?php
                $total_amount = array_sum(array_column($invoices, 'gross_amount'));
                $status_counts = array_count_values(array_column($invoices, 'status'));
                ?>
                <div class="summary-item">
                    <strong>Total: <?php echo count($invoices); ?> invoices</strong>
                </div>
                <div class="summary-item">
                    <strong>Total Amount: <?php echo number_format($total_amount, 2); ?>€</strong>
                </div>
                <div class="summary-item">
                    Draft: <?php echo $status_counts['draft'] ?? 0; ?> |
                    Sent: <?php echo $status_counts['sent'] ?? 0; ?> |
                    Paid: <?php echo $status_counts['paid'] ?? 0; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
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

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-warning {
    background-color: #ffc107;
    color: black;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.filters {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 120px;
}

.filter-group label {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 14px;
}

.filter-group input,
.filter-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
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

.invoice-number a {
    font-weight: bold;
    color: #007bff;
    text-decoration: none;
}

.amount {
    text-align: right;
    font-weight: bold;
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

.invoice-summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.summary-item {
    font-size: 14px;
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
    }
    
    .filter-group {
        min-width: 100%;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px 4px;
    }
    
    .invoice-summary {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

</body>
</html>
