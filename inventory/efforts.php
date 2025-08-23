<?php
    require_once(__DIR__ . "/../bootstrap.php");
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');
	include_once($_PJ_include_path . '/aperetiv.inc.php'); // Fix: Include aperetiv for sbe parameter handling

	// Debug: Log request start
	debugLog("LOG_EFFORTS_START", "Request method: " . $_SERVER['REQUEST_METHOD'] . ", URI: " . $_SERVER['REQUEST_URI']);
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		debugLog("LOG_EFFORTS_POST", "POST data keys: " . implode(', ', array_keys($_POST)));
	}

	$eid = $_REQUEST['eid'] ?? null;
	$stop = $_REQUEST['stop'] ?? null;
	$stop_all = $_REQUEST['stop_all'] ?? null;
	
	// Handle stop_all BEFORE any output to avoid header issues
	if(!empty($stop_all)) {
		// Get all open efforts for the current user
		$open_efforts = new OpenEfforts($_PJ_auth);
		$stopped_count = 0;
		
		if($open_efforts->effortCount() > 0) {
			$open_efforts->reset();
			while($open_efforts->nextEffort()) {
				$open_effort = $open_efforts->giveEffort();
				if($open_effort->checkUserAccess('write')) {
					$open_effort->stop();
					$stopped_count++;
				}
			}
		}
		
		$redirect_url = $GLOBALS['_PJ_customer_inventory_script'];
		$favicon = '/images/stop.png';
		// Use modern template structure
		$center_title = $GLOBALS['_PJ_strings']['activities_stopped'];
		if ($stopped_count==0) $info_message = $GLOBALS['_PJ_strings']['no_activities_stopped'];
		else if ($stopped_count==1) $success_message = $GLOBALS['_PJ_strings']['one_activity_stopped'];
		else $success_message = sprintf($GLOBALS['_PJ_strings']['multiple_activities_stopped'], $stopped_count);

		include("$_PJ_root/templates/note.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	$pid = $_REQUEST['pid'] ?? null;
	$cid = $_REQUEST['cid'] ?? null;
	
	// Set variables expected by path.ihtml template early to prevent undefined warnings
	$p_id = $pid;
	$c_id = $cid;
	$cont = $_REQUEST['cont'] ?? null;
	$new = $_REQUEST['new'] ?? null;
	$edit = $_REQUEST['edit'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	debugLog("LOG_EFFORTS_VARS", "altered=" . ($altered ? 'SET' : 'NULL') . ", edit=" . ($edit ? 'SET' : 'NULL') . ", new=" . ($new ? 'SET' : 'NULL'));
	$year = $_REQUEST['year'] ?? null;
	$month = $_REQUEST['month'] ?? null;
	$day = $_REQUEST['day'] ?? null;
	$hour = $_REQUEST['hour'] ?? null;
	$minute = $_REQUEST['minute'] ?? null;
	$second = $_REQUEST['second'] ?? null;
	$description = $_REQUEST['description'] ?? null;
	$note = $_REQUEST['note'] ?? null;
	$selected_cid = $_REQUEST['selected_cid'] ?? null;
	$selected_pid = $_REQUEST['selected_pid'] ?? null;
	$rate = $_REQUEST['rate'] ?? null;
	$user = $_REQUEST['user'] ?? null;
	$gid = $_REQUEST['gid'] ?? null;
	$access_owner = $_REQUEST['access_owner'] ?? null;
	$access_group = $_REQUEST['access_group'] ?? null;
	$access_world = $_REQUEST['access_world'] ?? null;
	$billing_day = $_REQUEST['billing_day'] ?? null;
	$billing_month = $_REQUEST['billing_month'] ?? null;
	$billing_year = $_REQUEST['billing_year'] ?? null;
	$hours = $_REQUEST['hours'] ?? null;
	$minutes = $_REQUEST['minutes'] ?? null;
	$detail = $_REQUEST['detail'] ?? null;
	$pdf = $_REQUEST['pdf'] ?? null;
	$delete = $_REQUEST['delete'] ?? null;
	$cancel = $_REQUEST['cancel'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	// Preserve $shown array from aperetiv.inc.php (handles sbe parameter)
	// Only merge with $_REQUEST['shown'] if it exists, don't overwrite
	if(isset($_REQUEST['shown']) && is_array($_REQUEST['shown'])) {
		$shown = array_merge($shown, $_REQUEST['shown']);
	}
	$list = $_REQUEST['list'] ?? null;

	// AJAX endpoint removed - now using client-side filtering of pre-generated project options

	// Only create Effort object if valid eid is provided
	$effort = $eid ? new Effort($eid, $_PJ_auth) : null;
	if(!empty($stop)) {
		// Check if effort object exists (eid parameter required)
		if(!$effort) {
			$error_message = 'Error: No effort ID specified for stop operation.';
			$center_title = $GLOBALS['_PJ_strings']['error'];
			include("$_PJ_root/templates/error.ihtml.php");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		
		if($eid && !$effort->checkUserAccess('write')) {
			$error_message = $GLOBALS['_PJ_strings']['error_access'];
			$center_title = $GLOBALS['_PJ_strings']['error'];
			include("$_PJ_root/templates/error.ihtml.php");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$effort->stop();
	}
	if($pid == '') {
		if(isset($effort) && is_object($effort)) {
			$pid = $effort->giveValue('project_id');
		}
		// For new efforts, it's OK to have no project initially
		// Only exit if we're editing an existing effort without project context
	}
	
	// First, create customer object if we have a valid cid
	$customer = null;
	if($cid) {
		$customer = new Customer($cid, $_PJ_auth);
	}

	// Then create project object if we have a valid pid
	$project = null;
	if($pid) {
		$project = new Project($customer, $_PJ_auth, $pid);
		// If we didn't have a customer but got one from the project, create customer object
		if(!$customer && $project->giveValue('customer_id')) {
			$cid = $project->giveValue('customer_id');
			$customer = new Customer($cid, $_PJ_auth);
		}
	}
	$center_template	= "inventory/effort";

	if(!empty($cont)) {
		if($eid && !$project->checkUserAccess('new')) {
			$error_message = $GLOBALS['_PJ_strings']['error_access'];
			$center_title = $GLOBALS['_PJ_strings']['error'];
			include("$_PJ_root/templates/error.ihtml.php");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$new_effort = $effort->copy($_PJ_auth);
		$new_effort->save();
	}

	if(isset($new)) {
		// Fetch last 6 efforts for current user to show as suggestions
		$recent_efforts = array();
		$current_user_id = $_PJ_auth->giveValue('id');
		
		if($current_user_id) {
			$db = new Database();
			$db->connect();
			$safe_user_id = DatabaseSecurity::escapeString($current_user_id, $db->Link_ID);
			
			// Query last 6 efforts with project and customer info, only where user has 'new' rights and customer is active
			$query = "SELECT distinct concat(e.description, p.project_name, c.customer_name), e.description, e.project_id, p.project_name, p.customer_id, c.customer_name 
					  FROM " . $GLOBALS['_PJ_effort_table'] . " e 
					  INNER JOIN " . $GLOBALS['_PJ_project_table'] . " p ON e.project_id = p.id 
					  INNER JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON p.customer_id = c.id 
					  WHERE e.user = '$safe_user_id' AND e.project_id > 0 AND c.active = 'yes'
					  ORDER BY e.last DESC 
					  LIMIT 6";
			
			$db->query($query);
			while($db->next_record()) {
				// Check if user has 'new' rights for this project
				$test_customer = new Customer($_PJ_auth, $db->Record['customer_id']);
				$test_project = new Project($test_customer, $_PJ_auth, $db->Record['project_id']);
				
				if($test_project->checkUserAccess('new')) {
					$recent_efforts[] = array(
						'project_id' => $db->Record['project_id'],
						'customer_id' => $db->Record['customer_id'],
						'project_name' => $db->Record['project_name'],
						'customer_name' => $db->Record['customer_name'],
						'description' => $db->Record['description']
					);
				}
			}
			
			debugLog("LOG_RECENT_EFFORTS", "Found " . count($recent_efforts) . " recent efforts with 'new' access for user " . $current_user_id);
		}
		
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_effort'];
		include("$_PJ_root/templates/add.ihtml.php");
		exit;
	}

	if(isset($edit)) {
		// Check access only for existing efforts (when editing)
		if($eid && $effort && !$effort->checkUserAccess('write')) {
			$error_message = $GLOBALS['_PJ_strings']['error_access'];
			$center_title = $GLOBALS['_PJ_strings']['error'];
			include("$_PJ_root/templates/error.ihtml.php");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($altered)) {
    debugLog("LOG_EFFORTS_SAVE_REQUEST", "REQUEST: " . json_encode($_REQUEST) . ", description='" . ($description ?? '') . "', selected_cid='" . ($selected_cid ?? '') . "', selected_pid='" . ($selected_pid ?? '') . "', final_pid='" . ($final_pid ?? '') . "', final_cid='" . ($final_cid ?? '') . "'");
			debugLog("LOG_EFFORTS_ALTERED", "Starting save process");
			
			// Check if user is authenticated before saving
			if(!$_PJ_auth || !$_PJ_auth->giveValue('id')) {
				debugLog("LOG_EFFORT_SAVE_ERROR", "User not authenticated, redirecting to login");
				header("Location: /inventory/efforts.php");
				exit;
			}
			debugLog("LOG_EFFORTS_AUTH", "User authenticated: " . $_PJ_auth->giveValue('id'));
			
			// last_description mod by Ruben Barkow -- START
			$_SESSION['last_description'] = $description;
			// last_description mod by Ruben Barkow -- END

			$data = array();
			$data['id']				= $eid;
			
			// Auto-assignment logic for customer and project
			$final_pid = $pid;
			$final_cid = $cid;
			
			// Use selected values from form if available
			if(!empty($selected_pid)) {
				$final_pid = $selected_pid;
			} elseif(!empty($selected_cid)) {
				$final_cid = $selected_cid;
				// If customer is selected but no project, we'll use the customer's first project if available
			}
			
			// Auto-assignment based on description if no project is selected
			$cleaned_description = $description;
			if(empty($final_pid) && !empty($description)) {
				// Check if description starts with 'k' followed by customer ID
				if(preg_match('/^k(\d+)\s*/i', ltrim($description), $matches)) { // LOG_EFFORT_AUTOASSIGN: Try k<ID> at start (case-insensitive, trim left)
    				debugLog("LOG_EFFORT_AUTOASSIGN", "Detected k<ID> pattern: " . $matches[1]);
					$auto_cid = intval($matches[1]);
					// Try direct DB query first to check if customer exists
					$db = new Database();
					$db->connect();
					$safe_cid = DatabaseSecurity::escapeString($auto_cid, $db->Link_ID);
					$query = "SELECT id, customer_name FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id = '$safe_cid'";
					$db->query($query);
					if($db->next_record()) {
						debugLog("LOG_EFFORT_AUTOASSIGN", "Customer ID $auto_cid exists in DB: name = '" . $db->Record['customer_name'] . "'");
						// Skip Customer object creation due to ACL restrictions, use direct DB approach
						$final_cid = $auto_cid;
						debugLog("LOG_EFFORT_AUTOASSIGN", "Customer ID $auto_cid assigned directly (bypassing ACL).");
					} else {
						debugLog("LOG_EFFORT_AUTOASSIGN", "Customer ID $auto_cid does not exist in database.");
					}
					// Check if customer was assigned via direct DB approach
					if($final_cid == $auto_cid) {
						debugLog("LOG_EFFORT_AUTOASSIGN", "Customer ID $auto_cid valid via direct DB. Proceeding with project assignment.");
						// Auto-assign project from newest effort of this customer (effort table has no customer_id column)
						// Reuse existing DB connection
						$safe_cid = DatabaseSecurity::escapeString($auto_cid, $db->Link_ID);
						$query = "SELECT e.project_id FROM " . $GLOBALS['_PJ_effort_table'] . " e "
							. "INNER JOIN " . $GLOBALS['_PJ_project_table'] . " p ON e.project_id = p.id "
							. "WHERE p.customer_id = '$safe_cid' AND e.project_id > 0 "
							. "ORDER BY e.last DESC LIMIT 1";
						$db->query($query);
						if($db->next_record()) {
							$final_pid = $db->Record['project_id'];
							debugLog("LOG_EFFORT_AUTOASSIGN", "Auto-assigned project from newest effort of customer: pid=" . $final_pid);
						} else {
							// Fallback: Use first available project if no previous efforts exist
							$project_list = new ProjectList($test_customer, $_PJ_auth);
							if($project_list->nextProject()) {
								$first_project = $project_list->giveProject();
								$final_pid = $first_project->giveValue('id');
								debugLog("LOG_EFFORT_AUTOASSIGN", "Fallback: Auto-assigned first available project of customer: pid=" . $final_pid);
							}
						}
						// Remove the shortcode from the description
						$cleaned_description = preg_replace('/^k\d+\s*/i', '', ltrim($description));
						debugLog("LOG_EFFORT_AUTOASSIGN", "Cleaned description after k<ID>: '" . $cleaned_description . "'");
					} else {
						debugLog("LOG_EFFORT_AUTOASSIGN", "Customer ID $auto_cid assignment failed (not found via direct DB).");
					}
				}
				// Check if description starts with 'p' followed by project ID
				elseif(preg_match('/^p(\d+)\s*/i', ltrim($description), $matches)) { // LOG_EFFORT_AUTOASSIGN: Try p<ID> at start (case-insensitive, trim left)
    				debugLog("LOG_EFFORT_AUTOASSIGN", "Detected p<ID> pattern: " . $matches[1]);
					$auto_pid = intval($matches[1]);
					// Verify project exists and user has access
					// Fix: Create dummy customer variable for by-reference parameter
					$dummy_customer = null;
					$test_project = new Project($dummy_customer, $_PJ_auth, $auto_pid);
					if($test_project->giveValue('id')) {
						debugLog("LOG_EFFORT_AUTOASSIGN", "Project ID $auto_pid valid. Assigning.");
						$final_pid = $auto_pid;
						$final_cid = $test_project->giveValue('customer_id');
						// Remove the shortcode from the description
						$cleaned_description = preg_replace('/^p\d+\s*/i', '', ltrim($description));
						debugLog("LOG_EFFORT_AUTOASSIGN", "Cleaned description after p<ID>: '" . $cleaned_description . "'");
					}
				}
				// Check if customer name appears in description
				else {
					$customer_list = new CustomerList($_PJ_auth);
					while($customer_list->nextCustomer()) {
						$customer_check = $customer_list->giveCustomer();
						$customer_name = $customer_check->giveValue('customer_name');
						if(!empty($customer_name) && stripos($description, $customer_name) !== false) {
   			 			debugLog("LOG_EFFORT_AUTOASSIGN", "Found customer name '$customer_name' in description. Assigning customer ID " . $customer_check->giveValue('id'));
							$final_cid = $customer_check->giveValue('id');
							// Auto-assign project from newest effort of this customer (effort table has no customer_id column)
							$db = new Database();
							$db->connect();
							$safe_cid = DatabaseSecurity::escapeString($customer_check->giveValue('id'), $db->Link_ID);
							$query = "SELECT e.project_id FROM " . $GLOBALS['_PJ_effort_table'] . " e "
								. "INNER JOIN " . $GLOBALS['_PJ_project_table'] . " p ON e.project_id = p.id "
								. "WHERE p.customer_id = '$safe_cid' AND e.project_id > 0 "
								. "ORDER BY e.last DESC LIMIT 1";
							$db->query($query);
							if($db->next_record()) {
								$final_pid = $db->Record['project_id'];
								debugLog("LOG_EFFORT_AUTOASSIGN", "Auto-assigned project from newest effort of customer '$customer_name': pid=" . $final_pid);
							} else {
								// Fallback: Use first available project if no previous efforts exist
								$project_list = new ProjectList($customer_check, $_PJ_auth);
								if($project_list->nextProject()) {
									$first_project = $project_list->giveProject();
									$final_pid = $first_project->giveValue('id');
									debugLog("LOG_EFFORT_AUTOASSIGN", "Fallback: Auto-assigned first available project of customer '$customer_name': pid=" . $final_pid);
								}
							}
							break;
						}
					}
				}
			}
			
			// DISABLED: Do not automatically assign first project when customer is selected
			// This respects user's intention to leave project empty
			// if(!empty($final_cid) && empty($final_pid)) {
			//     $customer_for_project = new Customer($_PJ_auth, $final_cid);
			//     $project_list = new ProjectList($customer_for_project, $_PJ_auth);
			//     if($project_list->nextProject()) {
			//         $first_project = $project_list->giveProject();
			//         $final_pid = $first_project->giveValue('id');
			//     }
			// }
			
			// Update global variables with final values
			$pid = $final_pid;
			$cid = $final_cid;
			// Ensure cid/pid are available globally for all later logic (especially for object creation and downstream logic)
			$_REQUEST['cid'] = $cid;
			$_REQUEST['pid'] = $pid;
			debugLog("LOG_EFFORT_AUTOASSIGN", "Final assignment: pid=$pid, cid=$cid, cleaned_description='$cleaned_description'");
			// Recreate customer and project objects with updated values
			if($cid) {
				$customer = new Customer($_PJ_auth, $cid);
				debugLog("LOG_EFFORT_AUTOASSIGN", "Customer object created for cid=$cid, name='" . ($customer ? $customer->giveValue('customer_name') : '') . "'");
			} else {
				$customer = null;
				debugLog("LOG_EFFORT_AUTOASSIGN", "No customer object created (cid empty)");
			}
			if($pid) {
				$project = new Project($customer, $_PJ_auth, $pid);
				debugLog("LOG_EFFORT_AUTOASSIGN", "Project object created for pid=$pid, name='" . ($project ? $project->giveValue('project_name') : '') . "'");
			} else {
				$project = null;
				debugLog("LOG_EFFORT_AUTOASSIGN", "No project object created (pid empty)");
			}
			
			// Convert empty project_id to NULL for database compatibility
			// Important: Set to NULL (not string) to avoid MySQL integer constraint error
			if(!empty($final_pid) && is_numeric($final_pid)) {
				$data['project_id'] = intval($final_pid);
			} else {
				// Skip project_id entirely if empty - let Effort class handle it
				unset($data['project_id']);
			}
			
			// Note: customer_id field removed - effort table has no customer_id column, using project_id instead
			// Customer association is handled via project_id (projects belong to customers)
			$data['date']			= "$year-$month-$day";
			$data['begin']			= sprintf('%02d:%02d:%02d', intval($hour), intval($minute), intval($second));
			// LOG_EFFORT_AUTOASSIGN: Save cleaned description
			$data['description']	= add_slashes($cleaned_description);
			$data['note']			= add_slashes($note);
			$data['rate']			= $rate;
			$data['user']			= $user;
			$data['gid']			= $gid;
			$data['access']			= $access_owner . $access_group . $access_world;
			// LOG_EFFORT_SAVE: Set defaults for empty fields
			if($data['user'] == '') {
				if ($effort && $effort->giveValue('user')) {
					// Use existing effort's user
					$data['user'] = $effort->giveValue('user');
					debugLog("LOG_EFFORT_SAVE", "Using existing effort user: " . $data['user']);
				} else {
					// Use current user for new efforts
					$data['user'] = $_PJ_auth->giveValue('id');
					debugLog("LOG_EFFORT_SAVE", "Using current user for new effort: " . $data['user']);
				}
			}
			if($data['user'] == '') {
				$data['user']	= $_PJ_auth->giveValue('id');
			}
			if($data['gid'] == '') {
				if ($effort && $effort->giveValue('gid')) {
					// Use existing effort's gid
					$data['gid'] = $effort->giveValue('gid');
					debugLog("LOG_EFFORT_SAVE", "Using existing effort gid: " . $data['gid']);
				} else {
					// Use user's default gid for new efforts
					$data['gid'] = $_PJ_auth->giveValue('gid');
					debugLog("LOG_EFFORT_SAVE", "Using user default gid for new effort: " . $data['gid']);
				}
			}
			if($data['access'] == '') {
				if ($effort && $effort->giveValue('access')) {
					// Use existing effort's access
					$data['access'] = $effort->giveValue('access');
					debugLog("LOG_EFFORT_SAVE", "Using existing effort access: " . $data['access']);
				} else {
					// Use default access for new efforts (owner: read/write, group: read, world: none)
					$data['access'] = 'rw-r-----';
					debugLog("LOG_EFFORT_SAVE", "Using default access for new effort: " . $data['access']);
				}
			}
			if(date("Y", strtotime("$billing_day/$billing_month/$billing_year")) > 1970) {
				$data['billed']			= "'$billing_year-$billing_month-$billing_day'";
			} else {
				$data['billed']			= "NULL";
			}
	
			debugLog("LOG_EFFORTS_BEFORE_SAVE", "Creating new effort with data: " . json_encode(array_keys($data)));
			$new_effort = new Effort($data, $_PJ_auth);
			$new_effort->setEndTime("$hours:$minutes");
			debugLog("LOG_EFFORTS_CALLING_SAVE", "About to call save()");
			$message = $new_effort->save();
			debugLog("LOG_EFFORTS_AFTER_SAVE", "Save completed");
			
			// Debug: Log save result
			debugLog("LOG_EFFORT_SAVE_RESULT", "Save message: '" . $message . "', Effort ID: " . ($new_effort->giveValue('id') ?: 'NULL'));
			
			if($message != '') {
				// Save failed - show error message with redirect back to form
				debugLog("LOG_EFFORT_SAVE_ERROR", "Save failed with message: " . $message);
				
				$error_message = "Fehler beim Speichern des Aufwands:<br><strong>" . htmlspecialchars($message) . "</strong><br><br>";
				$error_message .= "<a href='" . $_SERVER['PHP_SELF'] . "?new=1'>Neuen Aufwand anlegen</a> | ";
				$error_message .= "<a href='" . $_SERVER['PHP_SELF'] . "'>Zurück zur Übersicht</a>";
				
				// Redirect with error message
				$redirect_url = $_SERVER['PHP_SELF'] . "?error=" . urlencode($error_message);
				header("Location: $redirect_url");
				exit;
			}
			
			// Show success message with effort details
			$effort_description = !empty($cleaned_description) ? $cleaned_description : 'Ohne Beschreibung';
			$customer_name = '';
			$project_name = '';
			
			if($final_cid) {
				$success_customer = new Customer($_PJ_auth, $final_cid);
				$customer_name = $success_customer->giveValue('customer_name');
			}
			
			if($final_pid) {
				// Project constructor expects parameters by reference
				$null_customer = null;
				$success_project = new Project($null_customer, $_PJ_auth, $final_pid);
				$project_name = $success_project->giveValue('project_name');
				if(!$customer_name && $success_project->giveValue('customer_id')) {
					$success_customer = new Customer($_PJ_auth, $success_project->giveValue('customer_id'));
					$customer_name = $success_customer->giveValue('customer_name');
				}
			}
			
			// Build success message with localized strings
			// Get the actual ID after save (for new efforts, use insert_id)
			$effort_id = $new_effort->giveValue('id');
			$is_new_entry=false;
			if(empty($effort_id)) {
				$is_new_entry=true;
				// For new efforts, get the auto-increment ID from the database
				// Use the same database instance that was used for the save
				if(isset($new_effort->db) && is_object($new_effort->db)) {
					$effort_id = $new_effort->db->insert_id();
					debugLog("LOG_EFFORT_ID", "Retrieved new effort ID from effort->db->insert_id(): " . $effort_id);
				} else {
					debugLog("LOG_EFFORT_ID", "No database connection available for insert_id()");
				}
			}
			
			$success_message = $GLOBALS['_PJ_strings']['effort_saved_successfully'] . ":<br>";
			
			// Add effort ID
			if($effort_id) {
				$success_message .= "<strong>" . $GLOBALS['_PJ_strings']['effort_id'] . ":</strong> " . htmlspecialchars($effort_id) . "<br>";
			}
			
			$success_message .= "<strong>" . $GLOBALS['_PJ_strings']['description'] . ":</strong> " . htmlspecialchars($effort_description) . "<br>";
			
			if($project_name) {
				$success_message .= "<strong>" . $GLOBALS['_PJ_strings']['project'] . ":</strong> " . htmlspecialchars($project_name) . "<br>";
			}
			
			if($customer_name) {
				$success_message .= "<strong>" . $GLOBALS['_PJ_strings']['customer'] . ":</strong> " . htmlspecialchars($customer_name) . "<br>";
			}
			
			if(!$customer_name && !$project_name) {
				$success_message .= "<br><em>" . $GLOBALS['_PJ_strings']['effort_assignment_hint'] . "</em>";
			}

			// Redirect with success message only when createing a new efford
			if($is_new_entry) {
				$redirect_url = $_SERVER['PHP_SELF'] . "?message=" . urlencode($success_message);
				header("Location: $redirect_url");
				exit;
			}
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_effort'];
			include("$_PJ_root/templates/edit.ihtml.php");
			exit;
		}
	}

	if(isset($detail)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $effort->giveValue('description');
		include("$_PJ_root/templates/note.ihtml.php");
		exit;
	}

	if(isset($pdf)) {
		$efforts = new EffortList($customer, $project, $_PJ_auth, $shown['be']);
		include("$_PJ_root/templates/effort/pdf.ihtml.php");
		exit;
	}

	if(isset($delete) && !isset($cancel)) {
		if(!$effort->checkUserAccess('write') || (!$_PJ_auth->checkPermission('accountant') && !$GLOBALS['_PJ_agents_allow_delete'])) {
			$error_message = $GLOBALS['_PJ_strings']['error_access'];
			$center_title = $GLOBALS['_PJ_strings']['error'];
			include("$_PJ_root/templates/error.ihtml.php");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($confirm)) {
			$effort->delete();
			unset($effort);
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort'] . " '" . $effort->giveValue('description') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml.php");
			exit;
		}
	}

	// LOG_PROJECT_ACCESS: Check project object before accessing checkUserAccess method
	if($pid && $project && !$project->checkUserAccess('read')) {
		debugLog("LOG_PROJECT_ACCESS", "Access denied for project $pid by user " . $_PJ_auth->giveValue('id'));
		$error_message = $GLOBALS['_PJ_strings']['error_access'];
		$center_title = $GLOBALS['_PJ_strings']['error'];
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	} elseif ($pid && !$project) {
		debugLog("LOG_PROJECT_ACCESS", "No project object available for pid=$pid, allowing access");
	}
	$sort_order = $_GET['sort'] ?? 'desc';
	// Handle SBE limit parameter for billed entries
	$billed_limit = null;
	if(isset($shown['be']) && isset($sbe) && is_numeric($sbe) && $sbe > 0) {
		$config_default = isset($GLOBALS['_PJ_default_billed_entries_limit']) ? $GLOBALS['_PJ_default_billed_entries_limit'] : 100;
		$billed_limit = ($sbe == 1) ? $config_default : intval($sbe); // Convert sbe=1 to config default, use numeric value otherwise
		debugLog("LOG_SBE_LIMIT", "SBE parameter: sbe=$sbe, billed_limit=$billed_limit, config_default=$config_default");
	}
	$efforts			= new EffortList($customer, $project, $_PJ_auth, isset($shown['be']) ? $shown['be'] : false, NULL, $sort_order, $billed_limit);
	// LOG_TITLE_GENERATION: Set appropriate title based on project context
	if ($project && $project->giveValue('project_name')) {
		// Single project view
		debugLog("LOG_TITLE_GENERATION", "Generating title for single project: " . $project->giveValue('project_name'));
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $project->giveValue('project_name');
	} elseif ($customer && $customer->giveValue('customer_name')) {
		// Customer-specific efforts view
		debugLog("LOG_TITLE_GENERATION", "Generating title for customer efforts: " . $customer->giveValue('customer_name'));
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $customer->giveValue('customer_name');
	} else {
		// All efforts view
		debugLog("LOG_TITLE_GENERATION", "Generating title for all efforts view");
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'];
	}
	
	// Display success message if present
	if(isset($_GET['message'])) {
		$success_message = urldecode($_GET['message']);
		echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px; border-radius: 5px; text-align: center;">';
		echo $success_message;
		echo '</div>';
	}
	
	// Display error message if present
	if(isset($_GET['error'])) {
		$error_message = urldecode($_GET['error']);
		echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px; text-align: center;">';
		echo '<strong>❌ Fehler!</strong><br>' . $error_message;
		echo '</div>';
	}
	
	include("$_PJ_root/templates/list.ihtml.php");

	include_once("$_PJ_include_path/degestiv.inc.php");
