# Bulk Edit Efforts - Implementation Plan

## Overview
This document outlines the implementation plan for bulk editing efforts in the TimeEffect system. The goal is to allow users to select multiple efforts from the project effort list and edit common properties simultaneously.

## Current State Analysis

### Existing Checkbox Implementation
Based on analysis of `/templates/report/row.ihtml.php`, the system already has a checkbox implementation for billing:

```php
<td><input name="charge[<?= $effort->giveValue('id') ?>]" type="checkbox" checked></td>
<td><a href="<?= $GLOBALS['_PJ_efforts_inventory_script'] . '?edit=1&eid=' . $effort->giveValue('id') ?>">[ID: <?= $effort->giveValue('id') ?>]</a></td>
```

This shows the pattern we need to follow for bulk selection.

## Implementation Plan

### Phase 1: Add Checkbox Column to Effort Lists

#### 1.1 Modify Effort List Template
**File:** `/templates/inventory/effort/list.ihtml.php`

- Add new column header with "Select All" checkbox
- Add individual checkboxes for each effort row
- Include effort ID in checkbox name attribute: `bulk_edit[{effort_id}]`

#### 1.2 JavaScript Functionality
Add JavaScript functions for:
- **Toggle All:** Select/deselect all checkboxes
- **Selection Counter:** Show number of selected efforts
- **Bulk Edit Button:** Enable/disable based on selection

### Phase 2: Bulk Edit Interface

#### 2.1 New Bulk Edit Script
**File:** `/inventory/bulk_edit_efforts.php`

**URL Pattern:** `efforts.php?bulk_edit=1&effort_ids[]=123&effort_ids[]=456`

#### 2.2 Bulk Edit Form
**Template:** `/templates/inventory/effort/bulk_edit_form.ihtml.php`

**Editable Fields:**
- **Access Rights (ACP):** Owner, Group, World permissions
- **Billed Status:** Mark as billed/unbilled with date
- **Project Assignment:** Move efforts to different project
- **User Assignment:** Reassign efforts to different user
- **Rate Override:** Apply new hourly rate to selected efforts

#### 2.3 Validation & Security
- Verify user has edit permissions for all selected efforts
- Validate that all selected efforts exist and are accessible
- Log all bulk edit operations for audit trail

### Phase 3: User Interface Enhancements

#### 3.1 Effort List Modifications
**Location:** Project effort overview (`efforts.php?sbe=100&cid=2&pid=3&eid=`)

**New Elements:**
```html
<!-- Header row -->
<th class="list">
    <input type="checkbox" id="select-all-efforts" onchange="toggleAllEfforts()">
    Select All
</th>
<th class="list">ID</th>
<!-- existing columns... -->

<!-- Data rows -->
<td class="list">
    <input type="checkbox" name="bulk_edit[<?= $effort->giveValue('id') ?>]" 
           value="<?= $effort->giveValue('id') ?>" 
           class="effort-checkbox"
           onchange="updateBulkEditButton()">
</td>
<td class="list">
    <a href="<?= $GLOBALS['_PJ_efforts_inventory_script'] . '?edit=1&eid=' . $effort->giveValue('id') ?>">
        [<?= $effort->giveValue('id') ?>]
    </a>
</td>
```

#### 3.2 Action Buttons
```html
<div id="bulk-edit-controls" style="margin: 10px 0;">
    <button type="button" id="bulk-edit-btn" onclick="bulkEditSelected()" disabled>
        Edit Selected Efforts (<span id="selected-count">0</span>)
    </button>
    <button type="button" onclick="clearSelection()">Clear Selection</button>
</div>
```

### Phase 4: JavaScript Implementation

#### 4.1 Selection Management
```javascript
function toggleAllEfforts() {
    const selectAll = document.getElementById('select-all-efforts');
    const checkboxes = document.querySelectorAll('.effort-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkEditButton();
}

function updateBulkEditButton() {
    const selected = document.querySelectorAll('.effort-checkbox:checked');
    const bulkEditBtn = document.getElementById('bulk-edit-btn');
    const countSpan = document.getElementById('selected-count');
    
    countSpan.textContent = selected.length;
    bulkEditBtn.disabled = selected.length === 0;
}

function bulkEditSelected() {
    const selected = document.querySelectorAll('.effort-checkbox:checked');
    const effortIds = Array.from(selected).map(cb => cb.value);
    
    if (effortIds.length === 0) {
        alert('Please select at least one effort to edit.');
        return;
    }
    
    // Build URL with selected effort IDs
    const params = effortIds.map(id => `effort_ids[]=${id}`).join('&');
    window.location.href = `<?= $GLOBALS['_PJ_efforts_inventory_script'] ?>?bulk_edit=1&${params}`;
}
```

### Phase 5: Backend Processing

#### 5.1 Bulk Edit Handler
**File:** `/inventory/efforts.php`

```php
// Handle bulk edit request
if (isset($_REQUEST['bulk_edit']) && $_REQUEST['bulk_edit'] == '1') {
    $effort_ids = $_REQUEST['effort_ids'] ?? [];
    
    if (empty($effort_ids)) {
        // Redirect with error
        header("Location: " . $_SERVER['HTTP_REFERER'] . "&error=no_selection");
        exit;
    }
    
    // Validate permissions for all selected efforts
    $accessible_efforts = [];
    foreach ($effort_ids as $eid) {
        $effort = new Effort($eid, $_PJ_auth);
        if ($effort->checkPermission('w')) {
            $accessible_efforts[] = $eid;
        }
    }
    
    // Show bulk edit form
    include($GLOBALS['_PJ_root'] . '/templates/inventory/effort/bulk_edit_form.ihtml.php');
    exit;
}
```

#### 5.2 Bulk Update Processing
```php
// Process bulk edit form submission
if (isset($_REQUEST['bulk_update']) && $_REQUEST['bulk_update'] == '1') {
    $effort_ids = $_REQUEST['effort_ids'] ?? [];
    $updates = [];
    
    // Collect updates based on form data
    if (!empty($_REQUEST['bulk_access'])) {
        $updates['access'] = $_REQUEST['bulk_access'];
    }
    
    if (!empty($_REQUEST['bulk_billed'])) {
        $updates['billed'] = $_REQUEST['bulk_billed_date'];
    }
    
    // Apply updates to all selected efforts
    foreach ($effort_ids as $eid) {
        $effort = new Effort($eid, $_PJ_auth);
        if ($effort->checkPermission('w')) {
            foreach ($updates as $field => $value) {
                $effort->setValue($field, $value);
            }
            $effort->save();
        }
    }
    
    // Log bulk edit operation
    debugLog("BULK_EDIT", "Updated " . count($effort_ids) . " efforts: " . implode(',', $effort_ids));
    
    // Redirect back to effort list
    header("Location: " . $_SERVER['HTTP_REFERER'] . "&success=bulk_updated");
    exit;
}
```

## Security Considerations

### Permission Checks
- Verify user has 'w' (write) permission for each selected effort
- Only show efforts the user has access to in the selection
- Log all bulk operations for audit trail

### Input Validation
- Sanitize all form inputs
- Validate effort IDs exist and are numeric
- Prevent SQL injection through proper escaping

### Rate Limiting
- Limit number of efforts that can be bulk edited at once (e.g., max 100)
- Add confirmation dialog for large bulk operations

## Database Schema Considerations

No database schema changes required. The existing effort table structure supports all planned bulk edit operations.

## Testing Plan

### Unit Tests
- Test bulk edit permission validation
- Test bulk update operations
- Test JavaScript selection functionality

### Integration Tests
- Test complete bulk edit workflow
- Test with different user permission levels
- Test error handling and edge cases

### User Acceptance Tests
- Test usability of selection interface
- Test bulk edit form functionality
- Test performance with large effort lists

## Rollout Plan

### Phase 1: Core Implementation (Week 1)
- Implement checkbox column in effort lists
- Add JavaScript selection functionality
- Create basic bulk edit form

### Phase 2: Advanced Features (Week 2)
- Add all bulk edit options (ACP, billing, etc.)
- Implement security and validation
- Add logging and audit trail

### Phase 3: Testing & Polish (Week 3)
- Comprehensive testing
- UI/UX improvements
- Performance optimization

## Files to Modify

### Templates
- `/templates/inventory/effort/list.ihtml.php` - Add checkbox column
- `/templates/inventory/effort/bulk_edit_form.ihtml.php` - New bulk edit form

### Scripts
- `/inventory/efforts.php` - Add bulk edit handling

### JavaScript
- Add bulk edit functions to existing JS files or create new dedicated file

### CSS
- Add styling for bulk edit controls and selection indicators

## Success Metrics

- Users can select multiple efforts efficiently
- Bulk edit operations complete successfully
- No performance degradation on effort lists
- Positive user feedback on usability
- Zero security incidents related to bulk edit functionality

## Future Enhancements

- Bulk delete functionality
- Bulk export of selected efforts
- Saved selection sets for repeated operations
- Bulk time adjustment capabilities
- Integration with project management workflows
