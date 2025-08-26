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

### Phase 1: Add Checkbox Column to Effort Lists ✅

#### 1.1 Modify Effort List Template ✅
**File:** `/templates/inventory/effort/list.ihtml.php`

- ✅ Add new column header with "Select All" checkbox
- ✅ Add individual checkboxes for each effort row
- ✅ Include effort ID in checkbox name attribute: `bulk_edit[{effort_id}]`

#### 1.2 JavaScript Functionality ✅
Add JavaScript functions for:
- ✅ **Toggle All:** Select/deselect all checkboxes
- ✅ **Selection Counter:** Show number of selected efforts
- ✅ **Bulk Edit Button:** Enable/disable based on selection

### Phase 2: Bulk Edit Interface ✅

#### 2.1 New Bulk Edit Script ✅
**File:** `/inventory/efforts.php` (integrated into existing script)

**URL Pattern:** `efforts.php?bulk_edit=1&effort_ids[]=123&effort_ids[]=456`

#### 2.2 Bulk Edit Form ✅
**Template:** `/templates/inventory/effort/bulk_edit_form.ihtml.php`

**Editable Fields:**
- ✅ **Access Rights (ACP):** Owner, Group, World permissions
- ✅ **Billed Status:** Mark as unbilled/billed with new date
- ✅ **Project Assignment:** Move efforts to different project
- ✅ **Customer Assignment:** Move efforts to different customer (implemented as project assignment)
- ✅ **User Assignment:** Reassign efforts to different user
- ✅ **Group Assignment:** Move efforts to different group
- ✅ **Rate Override:** Apply new hourly rate to selected efforts
For each Field:
- ✅ show all distinct different values in a list ( e.g. "Current values: [value1, value2]" )
- ✅ add the option to change all efforts to the new value or keep existing values
1. 
die hourly rates sollen den tatsächlich entsprechenden rates entsprechen, die in ndem momentanen projekt orhanden sind

2. ✅ Current values display fixed for all sections:
- ✅ Access Rights
- ✅ Billed status  
- ✅ Project Assignment (shows "Customer - Project" format with fallback database query)
- ✅ User Assignment (shows "Firstname Lastname" format)
- ✅ Group Assignment (shows user group names from gids table)
 - missing: Current values
- ✅ Rate Override (dropdown with actual project rates and common rates, showing project context)

3. ✅ Project Assignment dropdown now shows customers with projects:
- ✅ Uses ACL filtering for accessible projects only with table alias 'p'
- ✅ Shows format "Customer Name - Project Name" 
- Includes all open projects with proper access rights (closed = 0 filter)
 - missing: the dropdown is still empty

4. global: falls nur einwert bei current values ist, dann sol das dropdown diesen gleich vor auswählen

#### 2.3 Validation & Security ✅
- ✅ Verify user has edit permissions for all selected efforts

### Phase 3: User Interface Enhancements ✅

#### 3.1 Effort List Modifications ✅
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

#### 3.2 Action Buttons ✅
```html
<div id="bulk-edit-controls" style="margin: 10px 0;">
    <button type="button" id="bulk-edit-btn" onclick="bulkEditSelected()" disabled>
        Edit Selected Efforts (<span id="selected-count">0</span>)
    </button>
    <button type="button" onclick="clearSelection()">Clear Selection</button>
</div>
```

### Phase 4: JavaScript Implementation ✅

#### 4.1 Selection Management ✅
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

### Phase 5: Backend Processing ✅

#### 5.1 Bulk Edit Handler ✅
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
        if ($effort->checkUserAccess('write')) {
            $accessible_efforts[] = $eid;
        }
    }
    
    // Show bulk edit form with proper template structure
    $center_title = 'Bulk Edit Efforts';
    $center_template = 'inventory/effort';
    $center_content = 'bulk_edit_form';
    include("$_PJ_root/templates/edit.ihtml.php");
    exit;
}
```

#### 5.2 Bulk Update Processing ✅
```php
// Process bulk edit form submission
if (isset($_REQUEST['bulk_update']) && $_REQUEST['bulk_update'] == '1') {
    $effort_ids = $_REQUEST['effort_ids'] ?? [];
    $updates_applied = 0;
    
    // Apply updates to all selected efforts
    foreach ($effort_ids as $eid) {
        $effort = new Effort($eid, $_PJ_auth);
        if (!$effort->checkUserAccess('write')) continue;
        
        $updated = false;
        
        // Update access rights, billing status, project assignment, 
        // user assignment, and rate override based on form data
        // ... (full implementation in efforts.php)
        
        if ($updated) {
            $effort->save();
            $updates_applied++;
        }
    }
    
    // Redirect back to effort list with success message
    $redirect_url = $GLOBALS['_PJ_efforts_inventory_script'] . '?' . http_build_query($redirect_params);
    header("Location: $redirect_url");
    exit;
}
```

## Security Considerations

### Permission Checks
- Verify user has 'w' (write) permission for each selected effort
- Only show efforts the user has access to in the selection

### Input Validation
- Sanitize all form inputs
- Prevent SQL injection through proper escaping

### Rate Limiting
- don't limit number of efforts that can be bulk edited at once
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

## Files to Modify

### Templates ✅
- ✅ `/templates/inventory/effort/list.ihtml.php` - Add checkbox column
- ✅ `/templates/inventory/effort/bulk_edit_form.ihtml.php` - New bulk edit form

### Scripts ✅
- ✅ `/inventory/efforts.php` - Add bulk edit handling

### JavaScript ✅
- ✅ Add bulk edit functions to existing JS files or create new dedicated file

### CSS ✅
- ✅ css/modern.css
- ✅ Add styling for bulk edit controls and selection indicators, use the same style as for the single effort edit and make sure dark mode is supported

## Success Metrics

- Users can select multiple efforts efficiently
- Bulk edit operations complete successfully
- No performance degradation on effort lists
- Positive user feedback on usability
- Zero security incidents related to bulk edit functionality

## Future Enhancements

- Bulk time adjustment capabilities
