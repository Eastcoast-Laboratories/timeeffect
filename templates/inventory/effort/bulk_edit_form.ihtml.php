<?php
// Bulk Edit Form for Efforts
// Show form for editing multiple selected efforts
?>
<!-- inventory/effort/bulk_edit_form.ihtml - START -->
<TABLE	WIDTH="100%"
		BORDER="<?php print($_PJ_inner_frame_border); ?>"
		CELLPADDING="<?php print($_PJ_inner_frame_cellpadding); ?>"
		CELLSPACING="<?php print($_PJ_inner_frame_cellspacing ); ?>">
	<TR>
		<TD CLASS="content">
		<FORM METHOD="POST" ACTION="<?= $GLOBALS['_PJ_efforts_inventory_script'] ?>">
			<input type="hidden" name="bulk_update" value="1">
			<?php foreach($accessible_efforts as $eid) { ?>
				<input type="hidden" name="effort_ids[]" value="<?= htmlspecialchars($eid) ?>">
			<?php } ?>
			
			<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
				<TR>
					<TD COLSPAN="2">
						<H2>Bulk Edit Efforts</H2>
						<p>Editing <?= count($accessible_efforts) ?> effort(s). Only selected fields will be updated.</p>
					</TD>
				</TR>
				<TR>
					<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="10" WIDTH="1" BORDER="0"></TD>
				</TR>
				
				<!-- Access Rights Section -->
				<TR>
					<TD CLASS="FormFieldName" WIDTH="200">Access Rights:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_access" value="1" onchange="toggleField('access_fields', this.checked)">
						Update access permissions
						<div id="access_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<table>
								<tr>
									<td>Owner:</td>
									<td>
										<select name="bulk_access_owner">
											<option value="rw">Read/Write</option>
											<option value="r-">Read Only</option>
											<option value="--">No Access</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>Group:</td>
									<td>
										<select name="bulk_access_group">
											<option value="rw">Read/Write</option>
											<option value="r-">Read Only</option>
											<option value="--">No Access</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>World:</td>
									<td>
										<select name="bulk_access_world">
											<option value="rw">Read/Write</option>
											<option value="r-">Read Only</option>
											<option value="--">No Access</option>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</TD>
				</TR>
				
				<!-- Billing Status Section -->
				<TR>
					<TD CLASS="FormFieldName">Billing Status:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_billed" value="1" onchange="toggleField('billing_fields', this.checked)">
						Update billing status
						<div id="billing_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<label>
								<input type="radio" name="bulk_billed_action" value="mark_billed" checked>
								Mark as billed on date:
							</label>
							<input type="date" name="bulk_billed_date" value="<?= date('Y-m-d') ?>">
							<br><br>
							<label>
								<input type="radio" name="bulk_billed_action" value="mark_unbilled">
								Mark as unbilled (clear billing date)
							</label>
						</div>
					</TD>
				</TR>
				
				<!-- Project Assignment Section -->
				<TR>
					<TD CLASS="FormFieldName">Project Assignment:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_project" value="1" onchange="toggleField('project_fields', this.checked)">
						Change project assignment
						<div id="project_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_project_id">
								<option value="">-- Select Project --</option>
								<?php
								// Show projects where user has 'new' permission
								$projects = new ProjectList($_PJ_auth);
								$projects->reset();
								while($projects->nextProject()) {
									$project = $projects->giveProject();
									if($project->checkUserAccess('new')) {
										echo '<option value="' . $project->giveValue('id') . '">' . 
											 htmlspecialchars($project->giveValue('name')) . '</option>';
									}
								}
								?>
							</select>
						</div>
					</TD>
				</TR>
				
				<!-- User Assignment Section -->
				<TR>
					<TD CLASS="FormFieldName">User Assignment:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_user" value="1" onchange="toggleField('user_fields', this.checked)">
						Change user assignment
						<div id="user_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_user_id">
								<option value="">-- Select User --</option>
								<?php
								// Show all active users
								$users = $_PJ_auth->giveUsers();
								foreach($users as $user) {
									if($user['active'] == 1) {
										echo '<option value="' . $user['id'] . '">' . 
											 htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . '</option>';
									}
								}
								?>
							</select>
						</div>
					</TD>
				</TR>
				
				<!-- Rate Override Section -->
				<TR>
					<TD CLASS="FormFieldName">Rate Override:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_rate" value="1" onchange="toggleField('rate_fields', this.checked)">
						Apply new hourly rate
						<div id="rate_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<input type="number" name="bulk_rate" step="0.01" min="0" placeholder="0.00">
							<?= $GLOBALS['_PJ_currency'] ?> per hour
							<br><small>This will recalculate costs for all selected efforts</small>
						</div>
					</TD>
				</TR>
				
				<TR>
					<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="20" WIDTH="1" BORDER="0"></TD>
				</TR>
				
				<!-- Submit Buttons -->
				<TR>
					<TD COLSPAN="2" ALIGN="center">
						<input type="submit" value="Update Selected Efforts" class="FormSubmit" style="background-color: #007cba; color: white; padding: 10px 20px; font-size: 14px;">
						&nbsp;&nbsp;
						<input type="button" value="Cancel" onclick="history.back()" class="FormSubmit" style="background-color: #666; color: white; padding: 10px 20px; font-size: 14px;">
					</TD>
				</TR>
			</TABLE>
		</FORM>
		</TD>
	</TR>
</TABLE>

<script>
function toggleField(fieldId, show) {
	var field = document.getElementById(fieldId);
	if (field) {
		field.style.display = show ? 'block' : 'none';
	}
}
</script>
<!-- inventory/effort/bulk_edit_form.ihtml - END -->