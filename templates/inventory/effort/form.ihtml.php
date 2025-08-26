<!-- inventory/effort/form.ihtml - START -->
<?php

	// Check if there are existing efforts with non-5-divisible minutes
	$show_all_minutes = false;
	$customer_id = 0; // Initialize customer_id variable
	if (isset($customer) && $customer && $customer->giveValue('id')) {
		$customer_id = $customer->giveValue('id');
	}
	if ($customer_id > 0) {
		$db = new Database();
		$db->connect();
		$safe_customer_id = DatabaseSecurity::escapeString($customer_id, $db->Link_ID);
		$query = "SELECT COUNT(*) as count FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE customer = '$safe_customer_id' AND (MINUTE(date) % 5 != 0 OR MINUTE(end) % 5 != 0)";
		$db->query($query);
		if ($db->next_record()) {
			$show_all_minutes = ($db->Record['count'] > 0);
		}
		debugLog("LOG_MINUTE_CHECK", "Customer ID: $customer_id, Non-5-divisible minutes found: " . ($show_all_minutes ? 'yes' : 'no'));
	}

	$a_gids						= $_PJ_auth->gids;
	if($_PJ_auth->checkPermission('admin')) {
		$u_gids						= @array_keys($a_gids);
	} else {
		$u_gids						= explode(',', $_PJ_auth->giveValue('gids'));
	}
	$users			= $_PJ_auth->listUsers();
	
	// LOG_EFFORT_FORM: Check customer and project objects before accessing giveValue
	// For editing efforts, prioritize customer_id from cid parameter
	if (isset($cid) && $cid > 0) {
		$customer_id = $cid;
		$rates = new Rates($cid);
		debugLog("LOG_EFFORT_FORM", "Using customer from cid parameter: $cid");
	} elseif ($customer && $customer->giveValue('id')) {
		$rates = new Rates($customer->giveValue("id"));
		$customer_id = $customer->giveValue('id');
		debugLog("LOG_EFFORT_FORM", "Using customer from customer object: " . $customer_id);
	} else {
		debugLog("LOG_EFFORT_FORM", "No customer object available, no rates for new efforts");
		$rates = new Rates(array()); // Empty rates for new efforts without customer/project
		$customer_id = 0;
	}
	$r_count = $rates->giveCount();
	
	if ($project && $project->giveValue('project_name')) {
		$project_input = '<INPUT TYPE="hidden" NAME="pid" VALUE="' . $pid . '"><b>' . $project->giveValue('project_name') . '</b>';
	} else {
		debugLog("LOG_EFFORT_FORM", "No project object available, using project ID only");
		$project_input = '<INPUT TYPE="hidden" NAME="pid" VALUE="' . $pid . '"><b>Project ID: ' . $pid . '</b>';
	}
	if(isset($effort) && is_object($effort) && $effort->giveValue('id')) {
		if($_PJ_auth->checkPermission('accountant')) {
			// Fix: Add isset check for array key 'cp' to prevent undefined array key warning
			$cp_value = isset($shown['cp']) ? $shown['cp'] : false;
			$__projects = new ProjectList($cid, $_PJ_auth, $cp_value);
			$project_input = '<SELECT NAME="pid">' . "\n";
			$__cid_buffer = NULL;
			while($__projects->nextProject()) {
				$__project = $__projects->giveProject();
				$project_input .= "\t\t\t\t\t\t<OPTION VALUE=\"" . $__project->giveValue('id') . "\"";
				if($__project->giveValue('id') == $pid) {
					$project_input .= ' SELECTED';
				}
				$project_input .= '>';
				if($__cid_buffer != $__project->giveValue('customer_id')) {
					// Fix: Use intermediate variable to avoid 'Only variables should be passed by reference' notice
					$customer_id = $__project->giveValue('customer_id');
					$__customer = new Customer($_PJ_auth, $customer_id);
					$__cid_buffer = $__project->giveValue('customer_id');
				}
				$project_input .= $__customer->giveValue('customer_name') . ':&nbsp;';
				$project_input .= $__project->giveValue('project_name') . "\n";
			}
			$project_input .=  "\n" . '</SELECT>' . "\n";
		}
		$id				= $effort->giveValue('id');
		$project_id		= $effort->giveValue('project_id');
		$date			= $effort->giveValue('date');
		list($year, $month, $day) = explode("-", $date);
	
		$begin			= $effort->giveValue('begin');
		list($hour, $minute, $second) = explode(":", $begin);
	
		$end			= $effort->giveValue('end');
		$b_time = mktime($hour, $minute, $second, $month, $day, $year);
		$e_time = mktime(date('H'), date('i'), date('s'));
		if($b_time < time() && $effort->giveValue('begin') == $effort->giveValue('end')) {
			if($b_time > $e_time) {
				$e_time = mktime(date('H'), date('i'), date('s'), date('m', $b_time+86400), date('d', $b_time+86400), date('Y', $b_time+86400));
			}
			$diff_time = $e_time - $b_time;
			$hours			= floor($diff_time / 3600);
			$minutes		= floor($diff_time / 60 -(floor($diff_time / 3600)*60));
			if($hours > 23) {
				$hours = 23;
				$minutes = 59;
			}
			$duration_message = $GLOBALS['_PJ_strings']['calculated_duration'];
		} else {
			$hours			= floor($effort->giveValue('hours'));
			$minutes		= floor($effort->giveValue('minutes')-($hours*60));
		}
		if($minutes != 59) {
			$minutes		= round($minutes/5)*5;
		}

		$description				= $effort->giveValue('description');
		$note						= $effort->giveValue('note');
		$billed						= $effort->giveValue('billed');
		$rate						= $effort->giveValue('rate');
		$user						= $effort->giveValue('user');
		$effort_gid					= $effort->giveValue('gid');
		$effort_access_owner		= substr($effort->giveValue('access'), 0, 3);
		$effort_access_group		= substr($effort->giveValue('access'), 3, 3);
		$effort_access_world		= substr($effort->giveValue('access'), 6, 3);
		// Fix: Add safe array handling for explode results to prevent undefined array key warnings
		$billing_parts = explode("-", $billed);
		$billing_year = isset($billing_parts[0]) ? $billing_parts[0] : date('Y');
		$billing_month = isset($billing_parts[1]) ? $billing_parts[1] : date('m');
		$billing_day = isset($billing_parts[2]) ? $billing_parts[2] : date('d');
		include($GLOBALS['_PJ_root'] . '/templates/inventory/effort/options/edit.ihtml.php');
	} else {
		// default settings for new efforts
		//$description				= $GLOBALS['_PJ_strings']['no_description'];
		if(isset($_SESSION['last_description'])) {
			$description	= $_SESSION['last_description'];
		} else {
			$description	= $GLOBALS['_PJ_strings']['no_description'];
		}
		$user						= $_PJ_auth->giveValue('id');
		
		// LOG_EFFORT_FORM: Check project object before accessing gid
		if ($project && $project->giveValue('gid')) {
			$effort_gid = $project->giveValue('gid');
			debugLog("LOG_EFFORT_FORM", "Using project gid: " . $effort_gid);
		} else {
			// Use user's default gid for new efforts
			$effort_gid = $_PJ_auth->giveValue('gid');
			debugLog("LOG_EFFORT_FORM", "No project gid available, using user default gid: " . $effort_gid);
		}
		$user_access['write']		= true;
		$effort_access_owner		= 'rw-';
		$effort_access_group		= 'r--';
		$effort_access_world		= '---';
		include($GLOBALS['_PJ_root'] . '/templates/inventory/effort/options/new.ihtml.php');
	}
?>

<?php
// Show recent efforts suggestions for new efforts (when no existing effort is being edited)
if(!isset($effort) || !is_object($effort) || !$effort->giveValue('id')) {
	if(isset($recent_efforts) && count($recent_efforts) > 0) {
?>
	<div class="recent-efforts-container">
		<h4 class="recent-efforts-title">🕒 Zuletzt genutzt</h4>
		<div class="recent-efforts-buttons">
			<?php foreach($recent_efforts as $index => $recent): ?>
				<button type="button" 
						class="recent-effort-btn"
						onclick="applyRecentEffort(<?= htmlspecialchars(json_encode($recent)) ?>)">
					<strong><?= htmlspecialchars($recent['customer_name']) ?></strong>: <?= htmlspecialchars($recent['project_name']) ?> 
					— <?= htmlspecialchars(strlen($recent['description']) > 40 ? substr($recent['description'], 0, 40) . '...' : $recent['description']) ?>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
<?php
	}
}
?>

<FORM ACTION="<? print $PHP_SELF; ?>" METHOD="<? print $_PJ_form_method; ?>">
<INPUT TYPE="hidden" NAME="edit" VALUE="1">
<INPUT TYPE="hidden" NAME="altered" VALUE="1">
<INPUT TYPE="hidden" NAME="cid" VALUE="<?php if(isset($cid)) echo $cid; ?>">
<INPUT TYPE="hidden" NAME="eid" VALUE="<?php if(isset($eid)) echo $eid; ?>">
<INPUT TYPE="hidden" NAME="id" VALUE="<?php if(isset($id)) echo $id; ?>">

	<CENTER>
	<TABLE ID="effort-form-table" 
			BORDER="<?php if(isset($_PJ_inner_frame_border)) echo $_PJ_inner_frame_border; ?>"
			CELLPADDING="<?php if(isset($_PJ_inner_frame_cellpadding)) echo $_PJ_inner_frame_cellpadding; ?>"
			CELLSPACING="<?php if(isset($_PJ_inner_frame_cellspacing)) echo $_PJ_inner_frame_cellspacing; ?>">
		<TR>
			<TD CLASS="content">
			<TABLE ID="effort-form-inner-table" BORDER="0" CELLPADDING="3" CELLSPACING="0" WIDTH="98%">
				<COLGROUP>
					<COL style="width: 80px;">
					<COL style="width: auto;">
				</COLGROUP>
				<TR>
					<TD CLASS="Error" COLSPAN="2"><?php if(isset($message)) echo $message; ?></TD>
				</TR><TR>
					<TD CLASS="FormFieldName" WIDTH="<?php if(isset($_PJ_form_field_name_width)) echo $_PJ_form_field_name_width; ?>"><b><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer'] ?>:</b></TD>
					<TD CLASS="FormField" WIDTH="<?php if(isset($_PJ_form_field_width)) echo $_PJ_form_field_width; ?>">
						<SELECT CLASS="FormField" NAME="selected_cid" ID="customer-select" onchange="updateProjectList()">
							<OPTION VALUE="">-- <?php echo $GLOBALS['_PJ_strings']['select_customer']; ?> --</OPTION>
							<?php
								// Generate customer options - only customers where user has 'new' rights in at least one project
								$customer_list = new CustomerList($_PJ_auth);
								while($customer_list->nextCustomer()) {
									$customer_option = $customer_list->giveCustomer();
									
									// Check if user has 'new' rights in at least one project of this customer
									$has_new_rights = false;
									$project_list = new ProjectList($customer_option, $_PJ_auth);
									while($project_list->nextProject()) {
										$project_check = $project_list->giveProject();
										if($project_check->checkUserAccess('new')) {
											$has_new_rights = true;
											break; // Found at least one project with 'new' rights
										}
									}
									
									// Only show customer if user has 'new' rights in at least one project
									if($has_new_rights) {
										$selected = (isset($cid) && $customer_option->giveValue('id') == $cid) ? ' SELECTED' : '';
										echo '<OPTION VALUE="' . $customer_option->giveValue('id') . '"' . $selected . '>' . 
											 htmlspecialchars($customer_option->giveValue('customer_name')) . '</OPTION>';
									}
								}
							?>
						</SELECT>
					</TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><b><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project'] ?>:</b></TD>
					<TD CLASS="FormField">
						<SELECT CLASS="FormField" NAME="selected_pid" ID="project-select">
							<OPTION VALUE="">-- <?php echo $GLOBALS['_PJ_strings']['select_project']; ?> --</OPTION>
							<?php
								// Pre-generate all projects with 'new' rights for JavaScript filtering
								$all_customers = new CustomerList($_PJ_auth);
								while($all_customers->nextCustomer()) {
									$customer_for_projects = $all_customers->giveCustomer();
									$customer_id_for_projects = $customer_for_projects->giveValue('id');
									
									$project_list_for_all = new ProjectList($customer_for_projects, $_PJ_auth);
									while($project_list_for_all->nextProject()) {
										$project_for_all = $project_list_for_all->giveProject();
										// Only include projects where user has 'new' rights
										if($project_for_all->checkUserAccess('new')) {
											$project_id_for_all = $project_for_all->giveValue('id');
											$project_name_for_all = $project_for_all->giveValue('project_name');
											$selected_project = (isset($pid) && $project_id_for_all == $pid) ? ' SELECTED' : '';
											echo '<OPTION VALUE="' . $project_id_for_all . '" data-customer-id="' . $customer_id_for_projects . '"' . $selected_project . ' style="display:none;">' . 
												 htmlspecialchars($project_name_for_all) . '</OPTION>';
										}
									}
								}
							?>
							<?php
								if(!empty($cid)) {
									// Show projects for current customer
									$customer_for_projects = new Customer($_PJ_auth, $cid);
									$project_list = new ProjectList($customer_for_projects, $_PJ_auth);
									while($project_list->nextProject()) {
										$project_option = $project_list->giveProject();
										$selected = (isset($pid) && $project_option->giveValue('id') == $pid) ? ' SELECTED' : '';
										echo '<OPTION VALUE="' . $project_option->giveValue('id') . '"' . $selected . '>' . 
											 htmlspecialchars($project_option->giveValue('project_name')) . '</OPTION>';
									}
								}
							?>
						</SELECT>
						<!-- Keep the old project input for backward compatibility -->
						<INPUT TYPE="hidden" NAME="pid" VALUE="<?php if(isset($pid)) echo $pid; ?>">
					</TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description'] ?>:</TD>
					<TD CLASS="FormField"><TEXTAREA CLASS="FormField" NAME="description" ID="description-field" COLS="35" ROWS="5" WRAP autofocus><?php print $description; ?></TEXTAREA></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"></TD>
					<TD CLASS="FormField">
						<button type="button" id="toggle-note-btn" class="btn btn-secondary" onclick="toggleNoteField()">
							📝 Notiz einfügen
						</button>
					</TD>
				</TR><TR id="note-row" style="display: none;">
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['note'])) echo $GLOBALS['_PJ_strings']['note'] ?>:</TD>
					<TD CLASS="FormField"><TEXTAREA CLASS="FormField" NAME="note" COLS="35" ROWS="5" WRAP><?php print $note; ?></TEXTAREA></TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date'] ?>:</TD>
					<TD CLASS="FormField">
					<SELECT CLASS="FormSelect date-field date-day" NAME="day" title="<?php if(!empty($GLOBALS['_PJ_strings']['day'])) echo $GLOBALS['_PJ_strings']['day'] ?>">
					<?php
						$a_day = $day;
						if(empty($day)) {
							$a_day = date("d");
						}
	
						for($i=1; $i <= 31; $i++) {
							print "<OPTION ";
							if($a_day == $i)
								print " SELECTED";
							printf(" VALUE='%02d'>%02d", $i, $i);
						}
					?>
					</SELECT>
					<SELECT CLASS="FormSelect date-field date-month" NAME="month" title="<?php if(!empty($GLOBALS['_PJ_strings']['month'])) echo $GLOBALS['_PJ_strings']['month'] ?>">
					<?php
						$a_month = $month;
						if(empty($month)) {
							$a_month = date("m");
						}
	
						for($i=1; $i <= 12; $i++) {
							print "<OPTION ";
							if($a_month == $i)
								print " SELECTED";
							printf(" VALUE='%02d'>%02d", $i, $i);
						}
					?>
					</SELECT>
					<SELECT CLASS="FormSelect date-field date-year" NAME="year" title="<?php if(!empty($GLOBALS['_PJ_strings']['year'])) echo $GLOBALS['_PJ_strings']['year'] ?>">
					<?php
						$a_year = $year;
						if(empty($year)) {
							$a_year = date("Y");
						}
						$max_year = date("Y");
	
						for($i=$a_year-1; $i <= $max_year; $i++) {
							print "<OPTION ";
							if($a_year == $i)
								print " SELECTED";
							printf(" VALUE='%04d'>%04d", $i, $i);
						}
					?>
					</SELECT>
					</TD>
				</TR><TR>
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['time_of_beginning'])) echo $GLOBALS['_PJ_strings']['time_of_beginning'] ?>:</TD>
					<TD CLASS="FormField">
					<button type="button" class="time-btn time-btn-minus" onclick="adjustTime('hour', -1)">−</button>
					<SELECT CLASS="FormSelect time-field time-hour" NAME="hour" title="<?php if(!empty($GLOBALS['_PJ_strings']['hour'])) echo $GLOBALS['_PJ_strings']['hour'] ?>">
					<?php
					    // ############# Beginn: hour
						$a_hour = $hour;
						if(empty($hour)) {
							$a_hour = date("H");
						}
	
						for($i=0; $i < 24; $i++) {
							print "<OPTION ";
							if($a_hour == $i)
								print " SELECTED";
							printf(" VALUE='%02d'>%02d", $i, $i);
						}
					?>
					</SELECT><button type="button" class="time-btn time-btn-plus" onclick="adjustTime('hour', 1)">+</button>

					<button type="button" class="time-btn time-btn-minus" onclick="adjustTime('minute', <?= $show_all_minutes ? -1 : -5 ?>)">−</button>
					<SELECT CLASS="FormSelect time-field time-minute" NAME="minute" title="<?php if(!empty($GLOBALS['_PJ_strings']['minute'])) echo $GLOBALS['_PJ_strings']['minute'] ?>">
					<?php
					    // ############# Beginn: minute
						$a_minute = $minute;
						if(empty($minute)) {
							$current_minute = date("i");
							// Round to nearest 5-minute step if not showing all minutes
							if (!$show_all_minutes) {
								$a_minute = round($current_minute / 5) * 5;
							} else {
								$a_minute = $current_minute;
							}
						}
	
						if ($show_all_minutes) {
							// Show all minutes (0-59) if non-5-divisible minutes exist
							for($i=0; $i < 60; $i++) {
								print "<OPTION ";
								if($a_minute == $i)
									print " SELECTED";
								printf(" VALUE='%02d'>%02d", $i, $i);
							}
						} else {
							// Show only 5-minute steps (0, 5, 10, 15, ..., 55)
							for($i=0; $i < 60; $i += 5) {
								print "<OPTION ";
								if($a_minute == $i)
									print " SELECTED";
								printf(" VALUE='%02d'>%02d", $i, $i);
							}
						}
					?>
					</SELECT><button type="button" class="time-btn time-btn-plus" onclick="adjustTime('minute', <?= $show_all_minutes ? 1 : 5 ?>)">+</button>
					</TD>
				</TR><TR>
					<TD CLASS="FormFieldName"></TD>
					<TD CLASS="FormField">
						<button type="button" id="toggle-advanced-btn" class="btn btn-secondary" onclick="toggleAdvancedFields()">
							⚙️ Erweitert
						</button>
					</TD>
				</TR><TR id="duration-row"<?php if(!(isset($effort) && is_object($effort) && $effort->giveValue('id'))) { ?> class="advanced-field" style="display: none;"<?php } ?>>
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['duration'])) echo $GLOBALS['_PJ_strings']['duration'] ?>:</TD>
					<TD CLASS="FormField">
					<button type="button" class="time-btn time-btn-minus" onclick="adjustTime('hours', -1)">−</button>
					<SELECT CLASS="FormSelect time-field time-hour" NAME="hours" title="<?php if(!empty($GLOBALS['_PJ_strings']['hours'])) echo $GLOBALS['_PJ_strings']['hours'] ?>">
					<?php
					    // ############# Duration: hours
						$a_hours = $hours;
	
						for($i=0; $i < 24; $i++) {
							print "<OPTION ";
							if($a_hours == $i)
								print " SELECTED";
							printf(" VALUE='%02d'>%02d", $i, $i);
						}
					?>
					</SELECT><button type="button" class="time-btn time-btn-plus" onclick="adjustTime('hours', 1)">+</button>
					<button type="button" class="time-btn time-btn-minus" onclick="adjustTime('minutes', -5)">−</button>
					<SELECT CLASS="FormSelect time-field time-minute" NAME="minutes" title="<?php if(!empty($GLOBALS['_PJ_strings']['minutes'])) echo $GLOBALS['_PJ_strings']['minutes'] ?>">
					<?php
					    // ############# Duration: minutes
						$a_minutes = $minutes;
	
						for($i=0; $i <= 11; $i++) {
							print "<OPTION ";
							if($a_minutes != 59 && floor($a_minutes/5) == $i)
								print " SELECTED";
							printf(' VALUE="%02d">%02d' ."\n", $i*5, $i*5);
						}
						print '<OPTION ';
						if($a_minutes == 59)
								print " SELECTED";
						print ' VALUE="59">59' . "\n";
					?>
					</SELECT><button type="button" class="time-btn time-btn-plus" onclick="adjustTime('minutes', 5)">+</button>
					<br>
					<?php
						if(!empty($duration_message)) {
							?><br><span class="warning"><?=$duration_message?><span>
								&nbsp;<button type="button" value="reset" onclick="document.getElementsByName('hours')[0].value='00'; document.getElementsByName('minutes')[0].value='00'; return false" id="reset_time">reset</button>
							<?php
						}
					?>
					</TD>
				</TR><TR class="advanced-field" style="display: none;">
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['rate'])) echo $GLOBALS['_PJ_strings']['rate'] ?>:</TD>
					<TD CLASS="FormField">
					<SELECT CLASS="FormField" NAME="rate" ID="rate-select">
						<OPTION VALUE="">Kein Tarif</OPTION>
					<?php
						$a_rate = $rate;
						$rates->resetList();
						$rate_found = false;
						while($data = $rates->giveNext()) {
							print "<OPTION ";
							if($a_rate == $data['price']) {
								print " SELECTED";
								$rate_found = true;
							}
							printf(' VALUE="%s" data-customer-id="%s">%s (%s %s)' . "\n" , $data['price'], $customer_id, $data['name'], $GLOBALS['_PJ_currency'], formatNumber($data['price'], true));
						}
						if($rate != '' && $rate != 0 && !$rate_found) {
							printf('<OPTION VALUE="%s" SELECTED>Individueller Tarif: %s %s' , $rate, $GLOBALS['_PJ_currency'], formatNumber($rate));
						}
						
						// Generate all rates for JavaScript filtering
						$all_customers = new CustomerList($_PJ_auth);
						while($all_customers->nextCustomer()) {
							$customer_for_rates = $all_customers->giveCustomer();
							$customer_id_for_rates = $customer_for_rates->giveValue('id');
							$rates_for_all = new Rates($customer_id_for_rates);
							while($data_for_all = $rates_for_all->giveNext()) {
								// Only add if not already added above
								if($customer_id_for_rates != $customer_id) {
									printf('<OPTION VALUE="%s" data-customer-id="%s" style="display:none;">%s (%s %s)' . "\n" , 
										$data_for_all['price'], $customer_id_for_rates, $data_for_all['name'], 
										$GLOBALS['_PJ_currency'], formatNumber($data_for_all['price'], true));
								}
							}
						}
					?>
					</SELECT>
					<small style="margin-left: 10px;" id="rate-management-link">
						<!-- Initial PHP link will be replaced by JavaScript -->
						<span style="color: #999;">⚙️ <?php echo $GLOBALS['_PJ_strings']['edit_rates'] ?? 'Tarife bearbeiten'; ?> (<?php echo $GLOBALS['_PJ_strings']['select_customer'] ?? 'Kunde wählen'; ?>)</span>
					</small>
					</TD>
<?php
if($_PJ_auth->checkPermission('accountant')) {
?>
				</TR><TR class="advanced-field" style="display: none;">
					<TD CLASS="FormFieldName">Berechnet am:</TD>
					<TD CLASS="FormField">
					<input type="checkbox" id="billed-checkbox" name="billed_status" value="1" 
						   <?php echo (!empty($billed) && $billed != '0000-00-00') ? 'checked' : ''; ?>
						   onchange="toggleBilledDate()" style="margin-right: 10px;">
					<label for="billed-checkbox" style="margin-right: 15px;">Berechnet</label>
					<div id="billed-date-fields" style="<?php echo (!empty($billed) && $billed != '0000-00-00') ? '' : 'display: none;'; ?>">
					<SELECT CLASS="FormSelect date-field date-day" NAME="billing_day" title="<?php if(!empty($GLOBALS['_PJ_strings']['day'])) echo $GLOBALS['_PJ_strings']['day'] ?>">
						<OPTION VALUE="">
					<?php
						$a_billing_day = $billing_day;
	
						for($i=1; $i <= 31; $i++) {
							print "<OPTION ";
							if($a_billing_day == $i)
								print " SELECTED";
							printf(" VALUE='%02d'>%02d", $i, $i);
						}
					?>
					</SELECT>
					<SELECT CLASS="FormSelect date-field date-month" NAME="billing_month" title="<?php if(!empty($GLOBALS['_PJ_strings']['month'])) echo $GLOBALS['_PJ_strings']['month'] ?>">
						<OPTION VALUE="">
					<?php
						$a_billing_month = $billing_month;
	
						for($i=1; $i <= 12; $i++) {
							print "<OPTION ";
							if($a_billing_month == $i)
								print " SELECTED";
							printf(" VALUE='%02d'>%02d", $i, $i);
						}
					?>
					</SELECT>
					<SELECT CLASS="FormSelect date-field date-year" NAME="billing_year" title="<?php if(!empty($GLOBALS['_PJ_strings']['year'])) echo $GLOBALS['_PJ_strings']['year'] ?>">
						<OPTION VALUE="">
					<?php
						$a_billing_year = $billing_year;
						if(empty($billing_year)) {
							$a_billing_year = date("Y");
						}
						$max_billing_year = date("Y");
	
						for($i=$a_billing_year-1; $i <= $max_billing_year; $i++) {
							print "<OPTION ";
							if($billing_year && $a_billing_year == $i)
								print " SELECTED";
							printf(" VALUE='%04d'>%04d", $i, $i);
						}
					?>
					</SELECT>
					</div>
					</TD>
<?php
}
if($_PJ_auth->checkPermission('admin')) {
?>
				</TR><TR>
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['agent'])) echo $GLOBALS['_PJ_strings']['agent'] ?>:</TD>
					<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="user">
					<?php
						$a_user = $user;
						if(isset($users) && is_array($users)) {
							foreach($users as $cnt => $o_user) {
								// Build display name with username in parentheses if different
								$display_name = trim($o_user['firstname'] . ' ' . $o_user['lastname']);
								$username = $o_user['username'] ?? '';
								if (!empty($username) && strtolower($username) !== strtolower(str_replace(' ', '', $display_name))) {
									$display_name .= ' (' . htmlspecialchars($username) . ')';
								}
					?>
						<OPTION VALUE="<?php if(!empty($o_user['id'])) echo $o_user['id'] ?>"<?php if($a_user == $o_user['id']) print ' SELECTED'; ?>><?= htmlspecialchars($display_name) ?>
<?php
							}
						}
					?>
					</SELECT>
					</TD>
<?php
}
// LOG_EFFORT_FORM: Check effort object before accessing giveValue method
if($_PJ_auth->checkPermission('admin') || (!$effort || !$effort->giveValue('id')) || $user == $_PJ_auth->giveValue('id')) {
?>
				</TR><TR class="advanced-field" style="display: none;">
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['gid'])) echo $GLOBALS['_PJ_strings']['gid'] ?>:</TD>
					<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="gid">
<?php
	reset($u_gids);
	foreach($u_gids as $id) {
?>
						<OPTION<?php if($id == $effort_gid) print ' SELECTED'; ?> value="<?php if(isset($id)) echo $id; ?>"><?= $a_gids[$id] ?>
<?php
	}
?>
					 </SELECT></TD>
				</TR><TR class="advanced-field" style="display: none;">
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['access_owner'])) echo $GLOBALS['_PJ_strings']['access_owner'] ?>:</TD>
					<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="access_owner">
						<OPTION VALUE="rw-"<?php if($effort_access_owner == 'rw-') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_effort_rw'])) echo $GLOBALS['_PJ_strings']['access_effort_rw'] ?>
						<OPTION VALUE="r--"<?php if($effort_access_owner == 'r--') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_effort_r'])) echo $GLOBALS['_PJ_strings']['access_effort_r'] ?>
						<OPTION VALUE="---"<?php if($effort_access_owner == '---') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_na'])) echo $GLOBALS['_PJ_strings']['access_na'] ?>
					</SELECT>
					</TD>
				</TR><TR class="advanced-field" style="display: none;">
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['access_group'])) echo $GLOBALS['_PJ_strings']['access_group'] ?>:</TD>
					<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="access_group">
						<OPTION VALUE="rw-"<?php if($effort_access_group == 'rw-') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_effort_rw'])) echo $GLOBALS['_PJ_strings']['access_effort_rw'] ?>
						<OPTION VALUE="r--"<?php if($effort_access_group == 'r--') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_effort_r'])) echo $GLOBALS['_PJ_strings']['access_effort_r'] ?>
						<OPTION VALUE="---"<?php if($effort_access_group == '---') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_na'])) echo $GLOBALS['_PJ_strings']['access_na'] ?>
					</SELECT>
					</TD>
				</TR><TR class="advanced-field" style="display: none;">
					<TD CLASS="FormFieldName"><?php if(!empty($GLOBALS['_PJ_strings']['access_world'])) echo $GLOBALS['_PJ_strings']['access_world'] ?>:</TD>
					<TD CLASS="FormField"><SELECT CLASS="FormSelect" NAME="access_world">
						<OPTION VALUE="rw-"<?php if($effort_access_world == 'rw-') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_effort_rw'])) echo $GLOBALS['_PJ_strings']['access_effort_rw'] ?>
						<OPTION VALUE="r--"<?php if($effort_access_world == 'r--') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_effort_r'])) echo $GLOBALS['_PJ_strings']['access_effort_r'] ?>
						<OPTION VALUE="---"<?php if($effort_access_world == '---') print ' SELECTED' ?>><?php if(!empty($GLOBALS['_PJ_strings']['access_na'])) echo $GLOBALS['_PJ_strings']['access_na'] ?>
					</SELECT>
					</TD>
<?php
}
?>
				</TR><TR>
					<TD>&nbsp;</TD>
					<TD>&nbsp;</TD>
				</TR><TR>
					<TD COLSPAN="2"><INPUT CLASS="FormSubmit" TYPE="SUBMIT" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['save'])) echo $GLOBALS['_PJ_strings']['save'] ?> >>" onclick="saveLastUsedRate()"></TD>
				</TR>
			</TABLE>
			</TD>
		</TR>
	</TABLE>
	</CENTER>

	<!-- help toggle button -->
	<div style="text-align: center; margin: 1rem 0;">
		<button type="button" class="help-toggle-btn" onclick="toggleHelpSection()">
			<span id="help-icon">❓</span>
			<span id="help-text">Hilfe anzeigen</span>
		</button>
	</div>

	<!-- help section for auto-assignment -->
	<div id="help-section" class="help-section">
		<h4>💡 <?php echo $GLOBALS['_PJ_strings']['auto_assignment_help_title']; ?></h4>
		<p><strong><?php echo $GLOBALS['_PJ_strings']['auto_assignment_help_desc']; ?></strong></p>
		<ul>
			<li><?php echo $GLOBALS['_PJ_strings']['auto_assignment_help_1']; ?></li>
			<li><?php echo $GLOBALS['_PJ_strings']['auto_assignment_help_2']; ?></li>
			<li><?php echo $GLOBALS['_PJ_strings']['auto_assignment_help_3']; ?></li>
		</ul>
		<p><em><?php echo $GLOBALS['_PJ_strings']['auto_assignment_examples']; ?></em></p>
		<ul>
			<li><code><?php echo $GLOBALS['_PJ_strings']['auto_assignment_example_1']; ?></code></li>
			<li><code><?php echo $GLOBALS['_PJ_strings']['auto_assignment_example_2']; ?></code></li>
			<li><code><?php echo $GLOBALS['_PJ_strings']['auto_assignment_example_3']; ?></code></li>
		</ul>
	</div>

<script type="text/javascript">
// Apply recent effort data to form fields
function applyRecentEffort(recentData) {
	console.log('LOG_RECENT_EFFORTS_UI: Applying recent effort:', recentData);
	
	var customerSelect = document.getElementById('customer-select');
	var projectSelect = document.getElementById('project-select');
	var descriptionField = document.getElementById('description-field');
	var pidHidden = document.getElementsByName('pid')[0];
	
	// Set customer
	if (customerSelect && recentData.customer_id) {
		customerSelect.value = recentData.customer_id;
		console.log('LOG_RECENT_EFFORTS_UI: Set customer to:', recentData.customer_id);
	}
	
	// Update project list to show projects for selected customer
	updateProjectList();
	
	// Set project after a small delay to ensure project list is updated
	setTimeout(function() {
		if (projectSelect && recentData.project_id) {
			projectSelect.value = recentData.project_id;
			console.log('LOG_RECENT_EFFORTS_UI: Set project to:', recentData.project_id);
		}
		
		// Sync hidden pid field
		if (pidHidden && recentData.project_id) {
			pidHidden.value = recentData.project_id;
		}
	}, 50);
	
	// Set description
	if (descriptionField && recentData.description) {
		descriptionField.value = recentData.description;
		descriptionField.focus();
		console.log('LOG_RECENT_EFFORTS_UI: Set description to:', recentData.description);
	}
}

// Update project list when customer is selected
function updateProjectList() {
	var customerSelect = document.getElementById('customer-select');
	var projectSelect = document.getElementById('project-select');
	var customerId = customerSelect.value;
	
	// Hide all project options first
	var allOptions = projectSelect.querySelectorAll('option');
	for (var i = 0; i < allOptions.length; i++) {
		var option = allOptions[i];
		if (option.value === '') {
			// Keep the default "-- Projekt wählen --" option visible
			option.style.display = 'block';
		} else {
			// Hide all project options initially
			option.style.display = 'none';
			option.selected = false;
		}
	}
	
	if (customerId) {
		// Show only projects for the selected customer
		var projectOptions = projectSelect.querySelectorAll('option[data-customer-id="' + customerId + '"]');
		for (var j = 0; j < projectOptions.length; j++) {
			projectOptions[j].style.display = 'block';
		}
		
		// Log for debugging
		console.log('LOG_PROJECT_FILTER: Customer ID:', customerId, 'Found projects:', projectOptions.length);
	}
	
	// Update rate management link when customer changes
	updateRateManagementLink(customerId);
	
	// Update rate dropdown when customer changes
	updateRateDropdownForCustomer();
}

// Update rate management link based on selected customer
function updateRateManagementLink(customerId) {
	var rateLinkContainer = document.getElementById('rate-management-link');
	if (!rateLinkContainer) return;
	
	if (customerId && customerId !== '') {
		// Show active link for selected customer
		rateLinkContainer.innerHTML = '<a href="' + 
			'/inventory/customer.php' + 
			'?edit=1&rates=1&cid=' + customerId + 
			'" target="_blank" title="Stundensätze verwalten">⚙️ Tarife bearbeiten</a>';
	} else {
		// Show disabled text when no customer selected
		rateLinkContainer.innerHTML = '<span style="color: #999;">⚙️ Tarife bearbeiten (Kunde wählen)</span>';
	}
}

// Update rate dropdown based on selected customer (legacy function)
function updateRateDropdown(customerId) {
	// This function is kept for backward compatibility but now delegates to customer-based filtering
	updateRateDropdownForCustomer();
}

// Update rate dropdown based on selected customer (rates are customer-based, not project-based)
function updateRateDropdownForCustomer() {
	var rateSelect = document.getElementById('rate-select');
	var customerSelect = document.getElementById('customer-select');
	if (!rateSelect || !customerSelect) return;
	
	var selectedCustomerId = customerSelect.value;
	
	// Hide all rate options first
	var allRateOptions = rateSelect.querySelectorAll('option');
	for (var i = 0; i < allRateOptions.length; i++) {
		if (allRateOptions[i].getAttribute('data-customer-id')) {
			allRateOptions[i].style.display = 'none';
		}
	}
	
	// Reset selection
	rateSelect.selectedIndex = 0;
	
	if (selectedCustomerId && selectedCustomerId !== '') {
		// Show rates for the selected customer
		var customerRateOptions = rateSelect.querySelectorAll('option[data-customer-id="' + selectedCustomerId + '"]');
		for (var j = 0; j < customerRateOptions.length; j++) {
			customerRateOptions[j].style.display = 'block';
		}
		
		// Log for debugging
		console.log('LOG_RATE_FILTER: Customer ID:', selectedCustomerId, 'Found customer rates:', customerRateOptions.length);
	} else {
		// No customer selected - show no rates
		console.log('LOG_RATE_FILTER: No customer selected, hiding all rates');
	}
}

// Legacy function for project changes - now delegates to customer-based filtering
function updateRateDropdownForProject() {
	updateRateDropdownForCustomer();
}

// Show advanced fields and hide button (no toggle)
function toggleAdvancedFields() {
	var advancedFields = document.querySelectorAll('.advanced-field');
	var toggleBtn = document.getElementById('toggle-advanced-btn');
	
	for (var i = 0; i < advancedFields.length; i++) {
		advancedFields[i].style.display = 'table-row';
	}
	
	// Hide the button after expanding
	toggleBtn.style.display = 'none';
}

// Toggle billed date fields based on checkbox
function toggleBilledDate() {
	var checkbox = document.getElementById('billed-checkbox');
	var dateFields = document.getElementById('billed-date-fields');
	
	if (checkbox.checked) {
		dateFields.style.display = '';
		// Set current date if any field is empty
		var dayField = document.getElementsByName('billing_day')[0];
		var monthField = document.getElementsByName('billing_month')[0];
		var yearField = document.getElementsByName('billing_year')[0];
		
		if (!dayField.value || !monthField.value || !yearField.value) {
			var today = new Date();
			dayField.value = String(today.getDate()).padStart(2, '0');
			monthField.value = String(today.getMonth() + 1).padStart(2, '0');
			yearField.value = today.getFullYear();
		}
	} else {
		dateFields.style.display = 'none';
		// Clear date fields when unchecked
		document.getElementsByName('billing_day')[0].value = '';
		document.getElementsByName('billing_month')[0].value = '';
		document.getElementsByName('billing_year')[0].value = '';
	}
}

// Toggle note field visibility
function toggleNoteField() {
	var noteRow = document.getElementById('note-row');
	var toggleBtn = document.getElementById('toggle-note-btn');
	var isVisible = noteRow.style.display !== 'none';
	
	noteRow.style.display = isVisible ? 'none' : 'table-row';
	
	// Hide button when note is shown
	if (!isVisible) {
		toggleBtn.style.display = 'none';
		var noteTextarea = noteRow.querySelector('textarea[name="note"]');
		if (noteTextarea) {
			noteTextarea.focus();
		}
	} else {
		toggleBtn.innerHTML = '📝 Notiz einfügen';
	}
}

// Auto-select description text if it contains "Ohne Beschreibung"
function setupDescriptionAutoSelect() {
	var descField = document.getElementById('description-field');
	if (descField && descField.value.trim() === 'Ohne Beschreibung') {
		descField.addEventListener('focus', function() {
			this.select();
		}, { once: true }); // Only select on first focus
	}
}

// Initialize UI enhancements when page loads
document.addEventListener('DOMContentLoaded', function() {
	setupDescriptionAutoSelect();
	
	// Auto-expand advanced fields for existing efforts (not new entries)
	<?php if (!empty($eid) && $eid > 0): ?>
	var advancedFields = document.querySelectorAll('.advanced-field');
	var toggleBtn = document.getElementById('toggle-advanced-btn');
	if (advancedFields.length > 0 && toggleBtn) {
		for (var i = 0; i < advancedFields.length; i++) {
			advancedFields[i].style.display = 'table-row';
		}
		toggleBtn.style.display = 'none';
	}
	
	// Update rate management link with correct customer ID for existing efforts
	var customerSelect = document.getElementById('customer-select');
	if (customerSelect && customerSelect.value) {
		updateRateManagementLink(customerSelect.value);
	}
	<?php endif; ?>
	
	// Auto-expand note field if it has content or hide button if note exists
	var noteTextarea = document.querySelector('textarea[name="note"]');
	var toggleNoteBtn = document.getElementById('toggle-note-btn');
	if (noteTextarea && noteTextarea.value.trim() !== '') {
		var noteRow = document.getElementById('note-row');
		if (noteRow) {
			noteRow.style.display = 'table-row';
		}
		if (toggleNoteBtn) {
			toggleNoteBtn.style.display = 'none';
		}
	}
	
	// For new efforts (new=1), trigger rate filtering and restore last used rate
	<?php if (isset($_GET['new']) && $_GET['new'] == 1): ?>
	// Trigger rate filtering for new efforts
	updateRateDropdownForCustomer();
	
	// Try to restore last used rate for this project
	var projectSelect = document.getElementById('project-select');
	var rateSelect = document.getElementById('rate-select');
	if (projectSelect && rateSelect && projectSelect.value) {
		var lastRate = localStorage.getItem('lastRate_project_' + projectSelect.value);
		if (lastRate && rateSelect.querySelector('option[value="' + lastRate + '"]')) {
			rateSelect.value = lastRate;
			console.log('LOG_RATE_RESTORE: Restored last rate for project', projectSelect.value, ':', lastRate);
		}
	}
	<?php endif; ?>
});

// Save last used rate to localStorage when form is submitted
function saveLastUsedRate() {
	var projectSelect = document.getElementById('project-select');
	var rateSelect = document.getElementById('rate-select');
	
	if (projectSelect && rateSelect && projectSelect.value && rateSelect.value) {
		localStorage.setItem('lastRate_project_' + projectSelect.value, rateSelect.value);
		console.log('LOG_RATE_SAVE: Saved last rate for project', projectSelect.value, ':', rateSelect.value);
	}
}

// Toggle help section visibility
function toggleHelpSection() {
	var helpSection = document.getElementById('help-section');
	var helpIcon = document.getElementById('help-icon');
	var helpText = document.getElementById('help-text');
	
	if (helpSection.classList.contains('show')) {
		helpSection.classList.remove('show');
		helpIcon.textContent = '❓';
		helpText.textContent = 'Hilfe anzeigen';
	} else {
		helpSection.classList.add('show');
		helpIcon.textContent = '❌';
		helpText.textContent = 'Hilfe ausblenden';
	}
}

// JavaScript function to adjust time values with plus/minus buttons
function adjustTime(fieldName, increment) {
	var select = document.getElementsByName(fieldName)[0];
	if (!select) return;
	
	var currentValue = parseInt(select.value) || 0;
	var newValue = currentValue + increment;
	
	// Handle different field types with appropriate limits
	if (fieldName === 'hour' || fieldName === 'hours') {
		// Hours: 0-23
		if (newValue < 0) newValue = 23;
		if (newValue > 23) newValue = 0;
	} else if (fieldName === 'minute') {
		// Minutes: 0-59 (for start time) or 5-minute steps
		if (newValue < 0) newValue = 55; // Go to 55 when going below 0
		if (newValue > 59) newValue = 0;
		// If increment is 5 or -5, ensure it's a valid 5-minute step
		if (Math.abs(increment) === 5 && newValue % 5 !== 0) {
			newValue = Math.round(newValue / 5) * 5;
		}
	} else if (fieldName === 'minutes') {
		// Minutes: 0,5,10,15...55,59 (for duration, 5-minute steps)
		if (newValue < 0) newValue = 59;
		if (newValue > 59) newValue = 0;
		// Ensure it's a valid option in the select
		if (newValue !== 59 && newValue % 5 !== 0) {
			newValue = Math.round(newValue / 5) * 5;
		}
	}
	
	// Format value with leading zero
	var formattedValue = (newValue < 10) ? '0' + newValue : newValue.toString();
	
	// Set the new value
	select.value = formattedValue;
}
</script>

<!-- inventory/effort/form.ihtml - END -->
