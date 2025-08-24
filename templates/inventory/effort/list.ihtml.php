<!-- inventory/effort/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/inventory/effort/options/list.ihtml.php');
?>
<TABLE	WIDTH="100%"
		BORDER="<?php print($_PJ_inner_frame_border); ?>"
		CELLPADDING="<?php print($_PJ_inner_frame_cellpadding); ?>"
		CELLSPACING="<?php print($_PJ_inner_frame_cellspacing ); ?>">
	<TR>
		<TD CLASS="content">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
			<TR>
				<TD COLSPAN="3"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
					<TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
							<TR VALIGN="center">
								<TH CLASS="list"><?php
if($pid && $project && $project->checkUserAccess('new')) {
?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?new=1&cid=$cid&pid=$pid"; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['new_effort'])) echo $GLOBALS['_PJ_strings']['new_effort'] ?>" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['new_effort'])) echo $GLOBALS['_PJ_strings']['new_effort'] ?></A><?php
} else { ?>&nbsp;<?php
} ?></TH>
							</TR>
						</TABLE></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD ALIGN="center">
							<div id="bulk-edit-controls" style="margin: 10px 0; display: none;">
								<button type="button" id="bulk-edit-btn" onclick="bulkEditSelected()" disabled style="background-color: #007cba; color: white; border: 1px solid #005a87; padding: 5px 10px; cursor: pointer;">
									Edit Selected Efforts (<span id="selected-count">0</span>)
								</button>
								<button type="button" onclick="clearSelection()" style="background-color: #666; color: white; border: 1px solid #333; padding: 5px 10px; cursor: pointer; margin-left: 5px;">
									Clear Selection
								</button>
							</div>
						</TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD COLSPAN="3" BGCOLOR="#DDDDDD"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="1" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TH CLASS="list" WIDTH="60">
							<input type="checkbox" id="select-all-efforts" onchange="toggleAllEfforts()">
							Select All
						</TH>
						<TH CLASS="list" WIDTH="30%"><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description'] ?></TH>
						<?php if(empty($cid)) { ?><TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer']; ?></TH><?php } ?>
						<?php if(empty($pid)) { ?><TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project']; ?></TH><?php } ?>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['agent'])) echo $GLOBALS['_PJ_strings']['agent']; ?></TH>
						<TH CLASS="list" WIDTH="150">
							<?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date'] ?>
							<a href="<?= $_SERVER['PHP_SELF'] ?>?<?= http_build_query(array_merge($_GET, ['sort' => ($_GET['sort'] ?? 'desc') === 'desc' ? 'asc' : 'desc'])) ?>" class="sort-toggle" title="Sortierung umkehren">
								<?= ($_GET['sort'] ?? 'desc') === 'desc' ? '↓' : '↑' ?>
							</a>
						</TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['workinghours'])) echo $GLOBALS['_PJ_strings']['workinghours']; ?></TH>
						<TH CLASS="listNumeric" WIDTH="200"><?= $GLOBALS['_PJ_strings']['costs'] . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TH>
						<TH CLASS="list">&nbsp;</TH>
						<TH CLASS="list">&nbsp;</TH>
					</TR>
<?php
	$rowclass = 1;
	while($efforts->nextEffort()) {
		$rowclass = !$rowclass;
		$effort = $efforts->giveEffort();
		$row_class = !$row_class;
		include("$_PJ_root/templates/inventory/effort/row.ihtml.php");
	}
?>
					<TR>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 9; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
<?php
	// Calculate and display totals
	$total_hours = $efforts->getTotalHours();
	$total_costs = $efforts->getTotalCosts();
	$total_days = $efforts->getTotalDays();
	if($total_hours > 0 || $total_costs > 0) {
?>
					<TR>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 9; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="2" BORDER="0"></TD>
					</TR>
					<TR HEIGHT="25">
						<TD CLASS="listSum">&nbsp;</TD>
						<TD CLASS="listSum" WIDTH="35%">&nbsp;<B><?= $GLOBALS['_PJ_strings']['sum'] ?>:</B></TD>
						<TD CLASS="listSum">&nbsp;</TD>
						<TD CLASS="listSumNumeric">&nbsp;<B><?= formatNumber($total_days, true) ?> <?= $GLOBALS['_PJ_strings']['workingdays'] ?> (<?= formatNumber($total_hours, true) ?> h)</B>&nbsp;</TD>
						<TD CLASS="listSumNumeric" WIDTH="200">&nbsp;<B><?= formatNumber($total_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency'] ?></B></TD>
						<TD CLASS="listSum">&nbsp;</TD>
						<TD CLASS="listSum">&nbsp;</TD>
					</TR>
<?php
	}
?>
					<TR>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 9; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="3" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD CLASS="listFoot" ALIGN="left"><?php
if(empty($shown['be'])) {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?sbe=" . (isset($GLOBALS['_PJ_default_billed_entries_limit']) ? $GLOBALS['_PJ_default_billed_entries_limit'] : 100) . "&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/show-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['show_closed_efforts'])) echo $GLOBALS['_PJ_strings']['show_closed_efforts'] ?></A><?php
} else {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?sbe=0&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/hide-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['hide_closed_efforts'])) echo $GLOBALS['_PJ_strings']['hide_closed_efforts'] ?></A><?php
	// Add "Show All" link when showing limited billed entries
	$current_sbe = $_GET['sbe'] ?? null;
	if(isset($shown['be']) && is_numeric($current_sbe) && $current_sbe < 9999 && $efforts && $efforts->giveEffortCount() >= $current_sbe) {
						?><br><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?sbe=9999&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>">ALLE abgerechnete Aufwände anzeigen</A><?php
	}
}
						?></TD>
						<TD CLASS="listFoot" ALIGN="right">&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>
<script>
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
    const controlsDiv = document.getElementById('bulk-edit-controls');
    
    if (countSpan) countSpan.textContent = selected.length;
    if (bulkEditBtn) bulkEditBtn.disabled = selected.length === 0;
    
    // Show/hide bulk edit controls based on selection
    if (controlsDiv) {
        controlsDiv.style.display = selected.length > 0 ? 'block' : 'none';
    }
}

function bulkEditSelected() {
    const selected = document.querySelectorAll('.effort-checkbox:checked');
    const effortIds = Array.from(selected).map(cb => cb.value);
    
    if (effortIds.length === 0) {
        alert('Please select at least one effort to edit.');
        return;
    }
    
    // Build URL with selected effort IDs
    const params = effortIds.map(id => 'effort_ids[]=' + encodeURIComponent(id)).join('&');
    window.location.href = '<?= $GLOBALS['_PJ_efforts_inventory_script'] ?>?bulk_edit=1&' + params;
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.effort-checkbox');
    const selectAll = document.getElementById('select-all-efforts');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    if (selectAll) selectAll.checked = false;
    updateBulkEditButton();
}
</script>
<!-- inventory/effort/list.ihtml - END -->
