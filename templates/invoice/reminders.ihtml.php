<!-- Payment Reminders Content -->
<div class="page-header">
    <div class="actions">
        <a href="index.php" class="btn btn-secondary"><?php if(!empty($GLOBALS['_PJ_strings']['back_to_invoices'])) echo $GLOBALS['_PJ_strings']['back_to_invoices']; else echo 'Back to Invoices'; ?></a>
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
            <?php if(!empty($GLOBALS['_PJ_strings']['reminder_success'])) echo sprintf($GLOBALS['_PJ_strings']['reminder_success'], htmlspecialchars($_GET['success'])); else echo 'Reminder ' . htmlspecialchars($_GET['success']) . ' successfully!'; ?>
        </div>
    <?php endif; ?>

    <!-- Pending Reminders -->
    <div class="section">
        <h3><?php if(!empty($GLOBALS['_PJ_strings']['pending_reminders'])) echo sprintf($GLOBALS['_PJ_strings']['pending_reminders'], count($pending_reminders)); else echo 'Pending Reminders (' . count($pending_reminders) . ')'; ?></h3>
        
        <?php if (empty($pending_reminders)): ?>
            <div class="no-data">
                <p><?php if(!empty($GLOBALS['_PJ_strings']['no_pending_reminders'])) echo $GLOBALS['_PJ_strings']['no_pending_reminders']; else echo 'No pending reminders.'; ?></p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['invoice'])) echo $GLOBALS['_PJ_strings']['invoice']; else echo 'Invoice'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer']; else echo 'Customer'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['amount'])) echo $GLOBALS['_PJ_strings']['amount']; else echo 'Amount'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['type'])) echo $GLOBALS['_PJ_strings']['type']; else echo 'Type'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['due_date'])) echo $GLOBALS['_PJ_strings']['due_date']; else echo 'Due Date'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['actions'])) echo $GLOBALS['_PJ_strings']['actions']; else echo 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_reminders as $reminder): ?>
                        <tr class="reminder-<?php echo $reminder['reminder_type']; ?>">
                            <td>
                                <a href="view.php?id=<?php echo $reminder['invoice_id']; ?>">
                                    <?php echo htmlspecialchars($reminder['invoice_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($reminder['customer_name']); ?></td>
                            <td><?php echo number_format($reminder['gross_amount'], 2); ?>€</td>
                            <td>
                                <span class="reminder-type reminder-<?php echo $reminder['reminder_type']; ?>">
                                    <?php echo ucfirst($reminder['reminder_type']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($reminder['due_date'])); ?></td>
                            <td class="actions">
                                <button onclick="showReminderPreview(<?php echo $reminder['id']; ?>)" 
                                        class="btn btn-sm btn-info"><?php if(!empty($GLOBALS['_PJ_strings']['preview'])) echo $GLOBALS['_PJ_strings']['preview']; else echo 'Preview'; ?></button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="send_reminder">
                                    <input type="hidden" name="id" value="<?php echo $reminder['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary" 
                                            onclick="return confirm('<?php if(!empty($GLOBALS['_PJ_strings']['confirm_send_reminder'])) echo $GLOBALS['_PJ_strings']['confirm_send_reminder']; else echo 'Send this reminder?'; ?>')"><?php if(!empty($GLOBALS['_PJ_strings']['send'])) echo $GLOBALS['_PJ_strings']['send']; else echo 'Send'; ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Overdue Invoices -->
    <div class="section">
        <h3><?php if(!empty($GLOBALS['_PJ_strings']['overdue_invoices'])) echo sprintf($GLOBALS['_PJ_strings']['overdue_invoices'], count($overdue_invoices)); else echo 'Overdue Invoices (' . count($overdue_invoices) . ')'; ?></h3>
        
        <?php if (empty($overdue_invoices)): ?>
            <div class="no-data">
                <p><?php if(!empty($GLOBALS['_PJ_strings']['no_overdue_invoices'])) echo $GLOBALS['_PJ_strings']['no_overdue_invoices']; else echo 'No overdue invoices.'; ?></p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['invoice'])) echo $GLOBALS['_PJ_strings']['invoice']; else echo 'Invoice'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer']; else echo 'Customer'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['amount'])) echo $GLOBALS['_PJ_strings']['amount']; else echo 'Amount'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['invoice_date'])) echo $GLOBALS['_PJ_strings']['invoice_date']; else echo 'Invoice Date'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['days_overdue'])) echo $GLOBALS['_PJ_strings']['days_overdue']; else echo 'Days Overdue'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['status'])) echo $GLOBALS['_PJ_strings']['status']; else echo 'Status'; ?></th>
                        <th><?php if(!empty($GLOBALS['_PJ_strings']['actions'])) echo $GLOBALS['_PJ_strings']['actions']; else echo 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overdue_invoices as $overdue): ?>
                        <tr class="overdue-<?php echo min($overdue['days_overdue'], 30) > 15 ? 'critical' : 'warning'; ?>">
                            <td>
                                <a href="view.php?id=<?php echo $overdue['id']; ?>">
                                    <?php echo htmlspecialchars($overdue['invoice_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($overdue['customer_name']); ?></td>
                            <td><?php echo number_format($overdue['gross_amount'], 2); ?>€</td>
                            <td><?php echo date('d.m.Y', strtotime($overdue['invoice_date'])); ?></td>
                            <td class="days-overdue">
                                <?php if(!empty($GLOBALS['_PJ_strings']['days_count'])) echo sprintf($GLOBALS['_PJ_strings']['days_count'], $overdue['days_overdue']); else echo $overdue['days_overdue'] . ' days'; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $overdue['status']; ?>">
                                    <?php echo ucfirst($overdue['status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="view.php?id=<?php echo $overdue['id']; ?>" class="btn btn-sm btn-info"><?php if(!empty($GLOBALS['_PJ_strings']['view'])) echo $GLOBALS['_PJ_strings']['view']; else echo 'View'; ?></a>
                                <button onclick="scheduleReminders(<?php echo $overdue['id']; ?>)" 
                                        class="btn btn-sm btn-warning"><?php if(!empty($GLOBALS['_PJ_strings']['schedule_reminders'])) echo $GLOBALS['_PJ_strings']['schedule_reminders']; else echo 'Schedule Reminders'; ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<!-- Reminder Preview Modal -->
<div id="reminderModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4><?php if(!empty($GLOBALS['_PJ_strings']['reminder_preview'])) echo $GLOBALS['_PJ_strings']['reminder_preview']; else echo 'Reminder Preview'; ?></h4>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="reminderPreview"></div>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal()" class="btn btn-secondary"><?php if(!empty($GLOBALS['_PJ_strings']['close'])) echo $GLOBALS['_PJ_strings']['close']; else echo 'Close'; ?></button>
        </div>
    </div>
</div>

<script>
function showReminderPreview(reminderId) {
    fetch('ajax/reminder_preview.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'reminder_id=' + reminderId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('reminderPreview').innerHTML = '<pre>' + data.text + '</pre>';
            document.getElementById('reminderModal').style.display = 'block';
        } else {
            alert('<?php if(!empty($GLOBALS['_PJ_strings']['failed_load_reminder_preview'])) echo $GLOBALS['_PJ_strings']['failed_load_reminder_preview']; else echo 'Failed to load reminder preview'; ?>: ' + (data.error || '<?php if(!empty($GLOBALS['_PJ_strings']['unknown_error'])) echo $GLOBALS['_PJ_strings']['unknown_error']; else echo 'Unknown error'; ?>'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?php if(!empty($GLOBALS['_PJ_strings']['failed_load_reminder_preview'])) echo $GLOBALS['_PJ_strings']['failed_load_reminder_preview']; else echo 'Failed to load reminder preview'; ?>');
    });
}

function scheduleReminders(invoiceId) {
    if (confirm('<?php if(!empty($GLOBALS['_PJ_strings']['confirm_schedule_reminders'])) echo $GLOBALS['_PJ_strings']['confirm_schedule_reminders']; else echo 'Schedule automatic reminders for this invoice?'; ?>')) {
        fetch('ajax/schedule_reminders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'invoice_id=' + invoiceId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php if(!empty($GLOBALS['_PJ_strings']['reminders_scheduled_successfully'])) echo $GLOBALS['_PJ_strings']['reminders_scheduled_successfully']; else echo 'Reminders scheduled successfully'; ?>');
                location.reload();
            } else {
                alert('<?php if(!empty($GLOBALS['_PJ_strings']['failed_schedule_reminders'])) echo $GLOBALS['_PJ_strings']['failed_schedule_reminders']; else echo 'Failed to schedule reminders'; ?>: ' + (data.error || '<?php if(!empty($GLOBALS['_PJ_strings']['unknown_error'])) echo $GLOBALS['_PJ_strings']['unknown_error']; else echo 'Unknown error'; ?>'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php if(!empty($GLOBALS['_PJ_strings']['failed_schedule_reminders'])) echo $GLOBALS['_PJ_strings']['failed_schedule_reminders']; else echo 'Failed to schedule reminders'; ?>');
        });
    }
}

function closeModal() {
    document.getElementById('reminderModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('reminderModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
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

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-warning {
    background-color: #ffc107;
    color: black;
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

.section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
    margin-bottom: 30px;
}

.section h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
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

.reminder-type {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.reminder-first {
    background-color: #ffc107;
    color: #000;
}

.reminder-second {
    background-color: #fd7e14;
    color: #fff;
}

.reminder-final {
    background-color: #dc3545;
    color: #fff;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-sent {
    background-color: #17a2b8;
    color: #fff;
}

.status-draft {
    background-color: #ffc107;
    color: #000;
}

.overdue-warning {
    background-color: #fff3cd;
}

.overdue-critical {
    background-color: #f8d7da;
}

.days-overdue {
    font-weight: bold;
    color: #dc3545;
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
    color: #6c757d;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h4 {
    margin: 0;
}

.close {
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.modal-body pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    white-space: pre-wrap;
    font-family: Arial, sans-serif;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px 4px;
    }
    
    .actions {
        flex-direction: column;
        gap: 2px;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}
</style>
