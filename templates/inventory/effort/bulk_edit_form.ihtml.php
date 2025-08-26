<?php
// Bulk Edit Form for Efforts
// Show form for editing multiple selected efforts
?>
<!-- inventory/effort/bulk_edit_form.ihtml - START -->
<TABLE ID="bulk_edit_form_table" WIDTH="100%"
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
						<H2><?= $_PJ_strings['bulk_edit_efforts'] ?></H2>
						<p><?= $_PJ_strings['bulk_edit_editing'] ?> <?= count($accessible_efforts) ?> <?= $_PJ_strings['bulk_edit_efforts_count'] ?>. <?= $_PJ_strings['bulk_edit_only_selected'] ?></p>
						<?php
						// Collect current values for display
						$current_values = [
							'access' => [],
							'billed' => [],
							'project_id' => [],
							'user' => [],
							'gid' => [],
							'rate' => [],
							'description' => []
						];
						
						foreach($accessible_efforts as $eid) {
							$effort = new Effort($eid, $_PJ_auth);
							$current_values['access'][] = $effort->giveValue('access');
							$current_values['billed'][] = $effort->giveValue('billed') ?: 'unbilled';
							$current_values['project_id'][] = $effort->giveValue('project_id');
							$current_values['user'][] = $effort->giveValue('user');
							$current_values['gid'][] = $effort->giveValue('gid');
							$current_values['rate'][] = $effort->giveValue('rate');
							$current_values['description'][] = $effort->giveValue('description');
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
					<TD CLASS="FormFieldName" WIDTH="200"><?= $_PJ_strings['access_rights'] ?>:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_access" value="1" onchange="toggleField('access_fields', this.checked)">
						<?= $_PJ_strings['update_access_permissions'] ?>
						<br><span class="FormFieldCurrentValues"><strong><?= $_PJ_strings['current_values'] ?>:</strong> <?= implode(', ', $current_values['access']) ?></span>
						<div id="access_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<table>
								<tr>
									<td><?= $_PJ_strings['owner'] ?>:</td>
									<td>
										<select name="bulk_access_owner">
											<option value="rw"><?= $_PJ_strings['read_write'] ?></option>
											<option value="r-"><?= $_PJ_strings['read_only'] ?></option>
											<option value="--"><?= $_PJ_strings['no_access'] ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td><?= $_PJ_strings['group'] ?>:</td>
									<td>
										<select name="bulk_access_group">
											<option value="rw"><?= $_PJ_strings['read_write'] ?></option>
											<option value="r-"><?= $_PJ_strings['read_only'] ?></option>
											<option value="--"><?= $_PJ_strings['no_access'] ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td><?= $_PJ_strings['access_world'] ?>:</td>
									<td>
										<select name="bulk_access_world">
											<option value="rw"><?= $_PJ_strings['read_write'] ?></option>
											<option value="r-"><?= $_PJ_strings['read_only'] ?></option>
											<option value="--"><?= $_PJ_strings['no_access'] ?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</TD>
				</TR>
				
				<!-- Billing Status Section -->
				<TR>
					<TD CLASS="FormFieldName"><?= $_PJ_strings['billing_status'] ?>:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_billed" value="1" onchange="toggleField('billing_fields', this.checked)">
						<?= $_PJ_strings['update_billing_status'] ?>
						<br><span class="FormFieldCurrentValues"><strong><?= $_PJ_strings['current_values'] ?>:</strong> <?= implode(', ', $current_values['billed']) ?></span>
						<div id="billing_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<label>
								<input type="radio" name="bulk_billed_action" value="mark_billed" checked>
								<?= $_PJ_strings['mark_as_billed'] ?>:
							</label>
							<input type="date" name="bulk_billed_date" value="<?= date('Y-m-d') ?>">
							<br><br>
							<label>
								<input type="radio" name="bulk_billed_action" value="mark_unbilled">
								<?= $_PJ_strings['mark_as_unbilled'] ?>
							</label>
						</div>
					</TD>
				</TR>
				
				<!-- Project Assignment Section -->
				<TR>
					<TD CLASS="FormFieldName"><?= $_PJ_strings['project_assignment'] ?>:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_project" value="1" onchange="toggleField('project_fields', this.checked)">
						<?= $_PJ_strings['change_project_assignment'] ?>
						<br><span class="FormFieldCurrentValues"><strong><?= $_PJ_strings['current_values'] ?>:</strong> 
						<?php 
						$project_names = [];
						foreach($current_values['project_id'] as $pid) {
							if($pid) {
								$project = new Project($customer, $_PJ_auth, $pid);
								if($project && $project->giveValue('id')) {
									// Get customer name from project's customer relationship
									$project_customer = $project->customer;
									if($project_customer && $project_customer->giveValue('id')) {
										$customer_name = $project_customer->giveValue('customer_name') ?: $_PJ_strings['no_customer'];
									} else {
										// Fallback: get customer name via database query
										$db = new Database();
										$safeProjectTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_project_table']);
										$safeCustomerTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
										$db->query("SELECT c.customer_name FROM {$safeProjectTable} p 
													LEFT JOIN {$safeCustomerTable} c ON p.customer_id = c.id 
													WHERE p.id = " . intval($pid));
										if($db->next_record()) {
											$customer_name = $db->f('customer_name') ?: $_PJ_strings['no_customer'];
										} else {
											$customer_name = $_PJ_strings['no_customer'];
										}
									}
									$project_name = $project->giveValue('project_name') ?: $_PJ_strings['unnamed_project'];
									$project_names[] = $customer_name . ' - ' . $project_name;
								} else {
									$project_names[] = $_PJ_strings['invalid_project'];
								}
							} else {
								$project_names[] = $_PJ_strings['unassigned'];
							}
						}
						echo implode(', ', array_unique($project_names));
						?>
						</span>
						<div id="project_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_project_id">
								<option value=""><?= $_PJ_strings['select_project'] ?></option>
								<?php
								// Show all accessible projects with customers
								$db = new Database();
								$safeProjectTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_project_table']);
								$safeCustomerTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
								
								// Build ACL query for accessible projects
								$access_query = buildProjectAclQuery($_PJ_auth, 'p');
								
								$query = "SELECT p.id, p.project_name, c.customer_name 
									 FROM {$safeProjectTable} p 
									 LEFT JOIN {$safeCustomerTable} c ON p.customer_id = c.id 
									 WHERE p.closed = 'No' {$access_query}
									 ORDER BY c.customer_name, p.project_name";
								
								debugLog('BULK_EDIT_DEBUG', 'Project dropdown query: ' . $query);
								$db->query($query);
								$project_count = 0;
								while($db->next_record()) {
									$customer_name = $db->f('customer_name') ?: $_PJ_strings['no_customer'];
									$project_name = $db->f('project_name') ?: $_PJ_strings['unnamed_project'];
									$display_name = $customer_name . ' - ' . $project_name;
									$project_id = $db->f('id');
									
									// Auto-select if only one unique project in current values
									$selected = '';
									if(isset($current_values['project_id']) && is_array($current_values['project_id']) &&
									   count(array_unique($current_values['project_id'])) == 1 && 
									   in_array($project_id, $current_values['project_id'])) {
										$selected = ' selected';
									}
									
									echo '<option value="' . $project_id . '"' . $selected . '>' . 
										 htmlspecialchars($display_name) . '</option>';
									$project_count++;
								}
								debugLog('BULK_EDIT_DEBUG', 'Projects loaded in dropdown: ' . $project_count);
								?>
							</select>
						</div>
					</TD>
				</TR>
				
				<!-- User Assignment Section -->
				<TR>
					<TD CLASS="FormFieldName"><?= $_PJ_strings['user_assignment'] ?>:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_user" value="1" onchange="toggleField('user_fields', this.checked)">
						<?= $_PJ_strings['change_user_assignment'] ?>
						<br><span class="FormFieldCurrentValues"><strong><?= $_PJ_strings['current_values'] ?>:</strong> 
						<?php 
						$user_names = [];
						foreach($current_values['user'] as $uid) {
							if($uid) {
								$user = new User($uid, $_PJ_auth);
								$firstname = $user->giveValue('firstname') ?: '';
								$lastname = $user->giveValue('lastname') ?: '';
								$display_name = trim($firstname . ' ' . $lastname) ?: "User $uid";
								$user_names[] = $display_name;
							} else {
								$user_names[] = $_PJ_strings['unassigned'];
							}
						}
						echo implode(', ', $user_names);
						?>
						</span>
						<div id="user_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_user_id">
								<option value=""><?= $_PJ_strings['select_user'] ?></option>
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
        	// Auto-select if only one unique user in current values
        	$selected = '';
        	if(isset($current_values['user']) && is_array($current_values['user']) &&
        	   count(array_unique($current_values['user'])) == 1 && 
        	   in_array($user_id, $current_values['user'])) {
        		$selected = ' selected';
        	}
            echo '<option value="' . $user_id . '"' . $selected . '>' . htmlspecialchars($display_name) . '</option>';
        }
								?>
							</select>
						</div>
					</TD>
				</TR>
				
				<!-- Group Assignment Section -->
				<TR>
					<TD CLASS="FormFieldName"><?= $_PJ_strings['group_assignment'] ?>:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_group" value="1" onchange="toggleField('group_fields', this.checked)">
						<?= $_PJ_strings['change_group_assignment'] ?>
						<br><span class="FormFieldCurrentValues"><strong><?= $_PJ_strings['current_values'] ?>:</strong> 
						<?php 
						$group_names = [];
						foreach(($current_values['gid'] ?? []) as $gid) {
							if($gid) {
								// Get group name from gids table (user groups)
								$db = new Database();
								$safeGid = DatabaseSecurity::escapeInt($gid);
								$safeGidsTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_gid_table']);
								$db->query("SELECT name FROM {$safeGidsTable} WHERE id = {$safeGid}");
								if($db->next_record()) {
									$group_names[] = $db->f('name');
								} else {
									$group_names[] = "Group $gid";
								}
							} else {
								$group_names[] = $_PJ_strings['no_group'];
							}
						}
						echo implode(', ', $group_names);
						?>
						</span>
						<div id="group_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_group_id">
								<option value=""><?= $_PJ_strings['select_group'] ?></option>
								<option value="0"><?= $_PJ_strings['no_group'] ?></option>
								<?php
								// Show user groups (gids) instead of global groups
								$db = new Database();
								$safeGidsTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_gid_table']);
								$db->query("SELECT id, name FROM {$safeGidsTable} ORDER BY name");
								while($db->next_record()) {
									$group_id = $db->f('id');
									$group_name = $db->f('name') ?: '';
									
									// Auto-select if only one unique group in current values
									$selected = '';
									if(isset($current_values['gid']) && is_array($current_values['gid']) &&
									   count(array_unique($current_values['gid'])) == 1 && 
									   in_array($group_id, $current_values['gid'])) {
										$selected = ' selected';
									}
									
									echo '<option value="' . $group_id . '"' . $selected . '>' . 
										 htmlspecialchars($group_name) . '</option>';
								}
								?>
							</select>
						</div>
					</TD>
				</TR>
				
				<!-- Description Section -->
				<TR>
					<TD CLASS="FormFieldName"><?= $_PJ_strings['description'] ?>:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_description" value="1" onchange="toggleField('description_fields', this.checked)">
						<?= $_PJ_strings['update_description'] ?>
						<br><span class="FormFieldCurrentValues"><strong><?= $_PJ_strings['current_values'] ?>:</strong> 
						<?php 
						$description_display = [];
						foreach($current_values['description'] as $desc) {
							if($desc) {
								$description_display[] = strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
							} else {
								$description_display[] = $_PJ_strings['empty'];
							}
						}
						echo implode(', ', array_unique($description_display));
						?>
						</span>
						<div id="description_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<textarea name="bulk_description" rows="3" cols="50" placeholder="<?= $_PJ_strings['description_placeholder'] ?>"><?php
								// Auto-fill with newest description (most recent effort)
								$newest_description = '';
								$newest_date = '';
								foreach($accessible_efforts as $eid) {
									$effort = new Effort($eid, $_PJ_auth);
									$effort_date = $effort->giveValue('date');
									if($effort_date > $newest_date) {
										$newest_date = $effort_date;
										$newest_description = $effort->giveValue('description');
									}
								}
								echo htmlspecialchars($newest_description);
							?></textarea>
							<br><small><?= $_PJ_strings['description_replace_note'] ?></small>
						</div>
					</TD>
				</TR>
				
				<!-- Rate Override Section -->
				<TR>
					<TD CLASS="FormFieldName"><?= $_PJ_strings['rate_override'] ?>:</TD>
					<TD CLASS="FormField">
						<input type="checkbox" name="update_rate" value="1" onchange="toggleField('rate_fields', this.checked)">
						<?= $_PJ_strings['apply_new_hourly_rate'] ?>
						<br><span class="FormFieldCurrentValues"><strong><?= $_PJ_strings['current_values'] ?>:</strong> 
						<?php 
						$rate_display = [];
						foreach($current_values['rate'] as $rate) {
							$rate_display[] = $rate . ' ' . $GLOBALS['_PJ_currency'];
						}
						echo implode(', ', $rate_display);
						?>
						</span>
						<div id="rate_fields" style="display: none; margin-top: 10px; padding-left: 20px;">
							<select name="bulk_rate">
								<option value=""><?= $_PJ_strings['select_rate'] ?></option>
								<?php
								// Get all unique project rates from selected efforts
								$project_rates = [];
								foreach($current_values['project_id'] as $pid) {
									if($pid) {
										$project = new Project($customer, $_PJ_auth, $pid);
										$rate = $project->giveValue('rate') ?: '0.00';
										$project_name = $project->giveValue('project_name') ?: $_PJ_strings['unnamed_project'];
										$customer_name = $project->customer ? $project->customer->giveValue('customer_name') : $_PJ_strings['no_customer'];
										$display_name = $customer_name . ' - ' . $project_name . ' (' . $rate . ' ' . $GLOBALS['_PJ_currency'] . ')';
										$project_rates[$rate] = $display_name;
									}
								}
								
								// Add common rates
								$common_rates = ['0.00', '25.00', '50.00', '75.00', '100.00'];
								foreach($common_rates as $rate) {
									if(!isset($project_rates[$rate])) {
										$project_rates[$rate] = $rate . ' ' . $GLOBALS['_PJ_currency'];
									}
								}
								
								// Sort by rate value
								ksort($project_rates, SORT_NUMERIC);
								
								foreach($project_rates as $rate => $display) {
									// Auto-select if only one unique rate in current values
									$selected = '';
									if(isset($current_values['rate']) && is_array($current_values['rate']) &&
									   count(array_unique($current_values['rate'])) == 1 && 
									   in_array(number_format($rate, 2), $current_values['rate'])) {
										$selected = ' selected';
									}
									
									echo '<option value="' . $rate . '"' . $selected . '>' . htmlspecialchars($display) . '</option>';
								}
								?>
							</select>
							<br><small><?= $_PJ_strings['rate_recalculate_note'] ?></small>
						</div>
					</TD>
				</TR>
				
				<TR>
					<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="20" WIDTH="1" BORDER="0"></TD>
				</TR>
				
				<!-- Submit Buttons -->
				<TR>
					<TD COLSPAN="2" ALIGN="center">
						<input type="submit" value="<?= $_PJ_strings['update_selected_efforts'] ?>" class="FormSubmit" style="background-color: #007cba; color: white; padding: 10px 20px; font-size: 14px;">
						&nbsp;&nbsp;
						<input type="button" value="<?= $_PJ_strings['cancel'] ?>" onclick="history.back()" class="FormSubmit" style="background-color: #666; color: white; padding: 10px 20px; font-size: 14px;">
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