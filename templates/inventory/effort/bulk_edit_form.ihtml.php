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
						<?php
						// Collect current values for display
						$current_values = [
							'access' => [],
							'billed' => [],
							'project_id' => [],
							'user' => [],
							'rate' => []
						];
						
						foreach($accessible_efforts as $eid) {
							$effort = new Effort($eid, $_PJ_auth);
							$current_values['access'][] = $effort->giveValue('access');
							$current_values['billed'][] = $effort->giveValue('billed') ?: 'unbilled';
							$current_values['project_id'][] = $effort->giveValue('project_id');
							$current_values['user'][] = $effort->giveValue('user');
							$current_values['rate'][] = $effort->giveValue('rate');
						}
						
						// Remove duplicates and format for display
						foreach($current_values as $key => $values) {
							$current_values[$key] = array_unique(array_filter($values));
						}
						?>
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
						<br><span style="font-size: 14px;"><strong>Current values:</strong> <?= implode(', ', $current_values['access']) ?></span>
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
						<br><span style="font-size: 14px;"><strong>Current values:</strong> <?= implode(', ', $current_values['billed']) ?></span>
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
						<br><span style="font-size: 14px;"><strong>Current values:</strong> 
						<?php 
						$project_names = [];
						foreach($current_values['project_id'] as $pid) {
							if($pid) {
								$project = new Project($customer, $_PJ_auth, $pid);
								$project_names[] = $project->giveValue('name');
							} else {
								$project_names[] = 'Unassigned';
							}
						}
						echo implode(', ', $project_names);
						?>
						</span>
						<div id="project_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_project_id">
								<option value="">-- Select Project --</option>
								<?php
								// Show all accessible projects with ACL filtering
								$projectList = new ProjectList($customer, $_PJ_auth);
								while($projectList->nextProject()) {
									$project = $projectList->projects[$projectList->project_cursor];
									if ($project && is_object($project)) {
										$customer_name = ($project->customer && is_object($project->customer)) ? 
											$project->customer->giveValue('customer_name') : 'No Customer';
										$project_name = $project->giveValue('project_name') ?: 'Unnamed Project';
										$display_name = $customer_name . ' - ' . $project_name;
										echo '<option value="' . $project->giveValue('id') . '">' . 
											 htmlspecialchars($display_name) . '</option>';
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
						<br><span style="font-size: 14px;"><strong>Current values:</strong> 
						<?php 
						$user_names = [];
						foreach($current_values['user'] as $uid) {
							if($uid) {
								$user = new User($uid, $_PJ_auth);
								$user_names[] = $user->giveValue('name');
							} else {
								$user_names[] = 'Unassigned';
							}
						}
						echo implode(', ', $user_names);
						?>
						</span>
						<div id="user_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_user_id">
								<option value="">-- Select User --</option>
								<?php
								// Get all active users for user assignment dropdown
								$db = new Database();
								$user_query = "SELECT id, firstname, lastname FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE confirmed = 1 ORDER BY firstname, lastname";
								$db->query($user_query);
        $users = [];
        while ($db->next_record()) {
            $user_id = $db->f('id');
            $firstname = $db->f('firstname') ?: '';
            $lastname = $db->f('lastname') ?: '';
            $display_name = trim($firstname . ' ' . $lastname) ?: "User $user_id";
            $users[$user_id] = $display_name;
        }
        foreach ($users as $user_id => $display_name) {
            echo '<option value="' . $user_id . '">' . htmlspecialchars($display_name) . '</option>';
        }
								?>
							</select>
						</div>
					</TD>
				</TR>
				
				<!-- Group Assignment Section -->
				<TR>
					<TD CLASS="FormFieldName">Group Assignment:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_group" value="1" onchange="toggleField('group_fields', this.checked)">
						Change group assignment
						<br><span style="font-size: 14px;"><strong>Current values:</strong> 
						<?php 
						$group_names = [];
						foreach(($current_values['gid'] ?? []) as $gid) {
							if($gid) {
								// Get group name from database
								$db = new Database();
								$safeGid = DatabaseSecurity::escapeInt($gid);
								$safeGroupTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_group_table']);
								$db->query("SELECT name FROM {$safeGroupTable} WHERE id = {$safeGid}");
								if($db->next_record()) {
									$group_names[] = $db->f('name');
								} else {
									$group_names[] = "Group $gid";
								}
							} else {
								$group_names[] = 'No Group';
							}
						}
						echo implode(', ', $group_names);
						?>
						</span>
						<div id="group_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_group_id">
								<option value="">-- Select Group --</option>
								<option value="0">No Group</option>
								<?php
								// Show user groups (gids) instead of global groups
								$db = new Database();
								$safeGidsTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_gid_table']);
								$db->query("SELECT id, name FROM {$safeGidsTable} ORDER BY name");
								while($db->next_record()) {
									echo '<option value="' . $db->f('id') . '">' . 
										 htmlspecialchars($db->f('name') ?: '') . '</option>';
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
						<br><span style="font-size: 14px;"><strong>Current values:</strong> <?= implode(', ', array_map(function($rate) { return $rate . ' ' . $GLOBALS['_PJ_currency']; }, $current_values['rate'])) ?></span>
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