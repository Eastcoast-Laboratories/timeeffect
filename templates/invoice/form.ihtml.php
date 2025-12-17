<!-- Invoice Form Content -->
<div class="page-header">
    <div class="actions">
        <a href="index.php" class="btn btn-secondary"><?php if(!empty($GLOBALS['_PJ_strings']['back_to_list'])) echo $GLOBALS['_PJ_strings']['back_to_list']; else echo 'Back to List'; ?></a>
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

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php if(!empty($GLOBALS['_PJ_strings']['invoice_created_successfully'])) echo $GLOBALS['_PJ_strings']['invoice_created_successfully']; else echo 'Invoice created successfully!'; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="invoice-form">
        <input type="hidden" name="mode" value="<?php echo htmlspecialchars($_GET['mode'] ?? ''); ?>">
        <div class="form-section">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['basic_information'])) echo $GLOBALS['_PJ_strings']['basic_information']; else echo 'Basic Information'; ?></h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id"><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer']; else echo 'Customer'; ?> *</label>
                    <select name="customer_id" id="customer_id" required onchange="updateProjects()">
                        <option value=""><?php if(!empty($GLOBALS['_PJ_strings']['select_customer'])) echo $GLOBALS['_PJ_strings']['select_customer']; else echo 'Select Customer'; ?></option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" 
                                <?php echo (isset($_POST['customer_id']) && $_POST['customer_id'] == $customer['id']) || 
                                          (isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="project_id"><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project']; else echo 'Project'; ?></label>
                    <select name="project_id" id="project_id">
                        <option value=""><?php if(!empty($GLOBALS['_PJ_strings']['all_projects'])) echo $GLOBALS['_PJ_strings']['all_projects']; else echo 'All Projects'; ?></option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" 
                                    <?php echo (isset($_POST['project_id']) && $_POST['project_id'] == $project['id']) || 
                                              (isset($_GET['project_id']) && $_GET['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="invoice_date"><?php if(!empty($GLOBALS['_PJ_strings']['invoice_date'])) echo $GLOBALS['_PJ_strings']['invoice_date']; else echo 'Invoice Date'; ?></label>
                    <input type="date" name="invoice_date" id="invoice_date" 
                           value="<?php echo $_POST['invoice_date'] ?? date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="generate_type"><?php if(!empty($GLOBALS['_PJ_strings']['generation_type'])) echo $GLOBALS['_PJ_strings']['generation_type']; else echo 'Generation Type'; ?></label>
                    <select name="generate_type" id="generate_type" onchange="toggleGenerationType()">
                        <option value="manual" <?php echo (isset($_POST['generate_type']) && $_POST['generate_type'] == 'manual') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['manual_hourly'])) echo $GLOBALS['_PJ_strings']['manual_hourly']; else echo 'Manual (Hourly)'; ?></option>
                        <option value="fixed_contract" <?php echo (isset($_POST['generate_type']) && $_POST['generate_type'] == 'fixed_contract') ? 'selected' : ''; ?>><?php if(!empty($GLOBALS['_PJ_strings']['from_fixed_contract'])) echo $GLOBALS['_PJ_strings']['from_fixed_contract']; else echo 'From Fixed Contract'; ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['period'])) echo $GLOBALS['_PJ_strings']['period']; else echo 'Period'; ?></h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="period_start"><?php if(!empty($GLOBALS['_PJ_strings']['period_start'])) echo $GLOBALS['_PJ_strings']['period_start']; else echo 'Period Start'; ?> *</label>
                    <input type="date" name="period_start" id="period_start" required
                           value="<?php echo $_POST['period_start'] ?? $_GET['period_start'] ?? date('Y-m-01', strtotime('first day of last month')); ?>">
                </div>

                <div class="form-group">
                    <label for="period_end"><?php if(!empty($GLOBALS['_PJ_strings']['period_end'])) echo $GLOBALS['_PJ_strings']['period_end']; else echo 'Period End'; ?> *</label>
                    <input type="date" name="period_end" id="period_end" required
                           value="<?php echo $_POST['period_end'] ?? $_GET['period_end'] ?? date('Y-m-t', strtotime('last day of last month')); ?>">
                </div>
            </div>

            <div class="form-group">
                <button type="button" onclick="setCurrentMonth()" class="btn btn-info btn-sm"><?php if(!empty($GLOBALS['_PJ_strings']['set_current_month'])) echo $GLOBALS['_PJ_strings']['set_current_month']; else echo 'Set Current Month'; ?></button>
                <button type="button" onclick="setPreviousMonth()" class="btn btn-info btn-sm"><?php if(!empty($GLOBALS['_PJ_strings']['set_previous_month'])) echo $GLOBALS['_PJ_strings']['set_previous_month']; else echo 'Set Previous Month'; ?></button>
            </div>
        </div>

        <div class="form-section">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description']; else echo 'Description'; ?></h3>
            
            <div class="form-group">
                <label for="description"><?php if(!empty($GLOBALS['_PJ_strings']['invoice_description'])) echo $GLOBALS['_PJ_strings']['invoice_description']; else echo 'Invoice Description'; ?></label>
                <textarea name="description" id="description" rows="3" 
                          placeholder="<?php if(!empty($GLOBALS['_PJ_strings']['optional_description_invoice'])) echo $GLOBALS['_PJ_strings']['optional_description_invoice']; else echo 'Optional description for the invoice'; ?>"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Preview Section -->
        <div id="preview-section" class="form-section" style="display: none;">
            <h3><?php if(!empty($GLOBALS['_PJ_strings']['preview'])) echo $GLOBALS['_PJ_strings']['preview']; else echo 'Preview'; ?></h3>
            <div id="preview-content">
                <!-- Preview will be loaded here -->
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="previewInvoice()" class="btn btn-info"><?php if(!empty($GLOBALS['_PJ_strings']['preview'])) echo $GLOBALS['_PJ_strings']['preview']; else echo 'Preview'; ?></button>
            <button type="submit" class="btn btn-primary"><?php if(!empty($GLOBALS['_PJ_strings']['create_invoice'])) echo $GLOBALS['_PJ_strings']['create_invoice']; else echo 'Create Invoice'; ?></button>
            <a href="index.php" class="btn btn-secondary"><?php if(!empty($GLOBALS['_PJ_strings']['cancel'])) echo $GLOBALS['_PJ_strings']['cancel']; else echo 'Cancel'; ?></a>
        </div>
    </form>

<script>
// Projects data for filtering
const projects = <?php echo json_encode($projects); ?>;

function updateProjects() {
    const customerId = document.getElementById('customer_id').value;
    const projectSelect = document.getElementById('project_id');
    
    // Get project_id from URL params to preserve selection
    const urlParams = new URLSearchParams(window.location.search);
    const selectedProjectId = urlParams.get('project_id') || '';
    
    // Clear existing options
    projectSelect.innerHTML = '<option value=""><?php if(!empty($GLOBALS['_PJ_strings']['all_projects'])) echo $GLOBALS['_PJ_strings']['all_projects']; else echo 'All Projects'; ?></option>';
    
    if (customerId) {
        // Filter projects by customer
        const customerProjects = projects.filter(p => p.customer_id == customerId);
        
        customerProjects.forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.name;
            if (project.id == selectedProjectId) {
                option.selected = true;
            }
            projectSelect.appendChild(option);
        });
    }
}

function toggleGenerationType() {
    const generateType = document.getElementById('generate_type').value;
    // Could add specific UI changes based on generation type
}

function setCurrentMonth() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    
    document.getElementById('period_start').value = firstDay.toISOString().split('T')[0];
    document.getElementById('period_end').value = lastDay.toISOString().split('T')[0];
}

function setPreviousMonth() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
    
    document.getElementById('period_start').value = firstDay.toISOString().split('T')[0];
    document.getElementById('period_end').value = lastDay.toISOString().split('T')[0];
}

function previewInvoice() {
    const form = document.querySelector('.invoice-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Add mode parameter from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('mode')) {
        params.set('mode', urlParams.get('mode'));
    }
    
    fetch('ajax/preview.php?' + params.toString(), {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('preview-content').innerHTML = data.html;
            document.getElementById('preview-section').style.display = 'block';
        } else {
            alert('<?php if(!empty($GLOBALS['_PJ_strings']['preview_failed'])) echo $GLOBALS['_PJ_strings']['preview_failed']; else echo 'Preview failed'; ?>: ' + (data.error || '<?php if(!empty($GLOBALS['_PJ_strings']['unknown_error'])) echo $GLOBALS['_PJ_strings']['unknown_error']; else echo 'Unknown error'; ?>'));
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
        alert('<?php if(!empty($GLOBALS['_PJ_strings']['preview_failed'])) echo $GLOBALS['_PJ_strings']['preview_failed']; else echo 'Preview failed'; ?>: ' + error.message);
    });
}

// Initialize projects on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProjects();
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

.btn-info {
    background-color: #17a2b8;
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

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

#preview-section {
    background: #fff;
    border: 1px solid #ddd;
}

#preview-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #ddd;
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
