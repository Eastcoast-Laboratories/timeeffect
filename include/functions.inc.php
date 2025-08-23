<?php
function formatNumber($number, $force_float = false) {
	$number = number_format($number, 2, $GLOBALS['_PJ_decimal_point'] , $GLOBALS['_PJ_thousands_seperator']);
	if(empty($force_float)) {
		$number = preg_replace("/\\" . $GLOBALS['_PJ_decimal_point'] . "00/", '', $number);
	}
	return $number;
}

function formatDate($date, $format = NULL) {
	if(empty($format)) {
		$format = $GLOBALS['_PJ_format_date'];
	}
	if($date == '') {
		return NULL;
	}
	list($year, $month, $day) = explode("-", $date);
	$timestamp = mktime(0, 0, 0, $month, $day, $year);
	return date($format, $timestamp);
}

function formatTime($time, $format = "H:i:s") {
	list($hour, $minute, $second) = explode(":", $time);
	$timestamp = mktime($hour, $minute, $second, 1, 1, 2002);
	return date($format, $timestamp);
}

function calculate($what, $date, $begin, $end) {
	list($year, $month, $day) = explode("-", $date);
	list($b_hour, $b_minute, $b_second) = explode(":", $begin);
	list($e_hour, $e_minute, $e_second) = explode(":", $end);
	$b_time = mktime($b_hour, $b_minute, $b_second, $month, $day, $year);
	$e_time = mktime($e_hour, $e_minute, $e_second, $month, $day, $year);

	switch ($what) {
		case 'seconds':
			return ($e_time - $b_time);
		default:
			return calculateFromSeconds($what, ($e_time - $b_time));
	}
}
function calculateFromSeconds($what, $seconds) {
	switch ($what) {
		case 'seconds':
			return ($seconds);
		case 'minutes':
			return ($seconds / 60);
		case 'hours':
			return ($seconds / 3600); // 60 * 60
		case 'days':
			return ($seconds / 28800); // 60 * 60 * 8
		case 'weeks':
			return ($seconds / 144000); // 60 * 60 * 8 * 5
		case 'months':
			return ($seconds / 604800); // 60 * 60 * 8 * 5 * 4.2
		case 'years':
			return ($seconds / 7257600); // 60 * 60 * 8 * 5 * 4.2 * 12
		default:
			return 0;
	}
}

function add_slashes($string) {
	// FIX: Null-Prüfung für PHP 8.4 Kompatibilität
	if ($string === null) {
		return '';
	}
	if(((bool) ini_get('magic_quotes_gpc'))) {
		return $string;
	}
	return addslashes($string);
}

function unhtmlentities($string) {
	$trans_tbl =get_html_translation_table (HTML_ENTITIES );
	$trans_tbl =array_flip ($trans_tbl );
	return strtr ($string ,$trans_tbl );
}

function debugLog($context, $message) {
	if (!empty($GLOBALS['_PJ_debug'])) {
		if(stristr($context, 'DEBUG')) {
			error_log("[$context] $message");
		}else{
			error_log("[DEBUG] [$context] $message");
		}
	}
}

/**
 * Validate password strength according to policy:
 * - Minimum 8 characters with at least one number and one special character
 * - OR minimum 12 characters without special requirements
 * 
 * @param string $password The password to validate
 * @return array Array with 'valid' (bool) and 'message' (string)
 */
function validatePasswordStrength($password) {
	$length = strlen($password);
	
	if ($length < 8) {
		return array(
			'valid' => false,
			'message' => 'Password must be at least 8 characters long'
		);
	}
	
	// Check for 12+ character rule (no special requirements)
	if ($length >= 12) {
		return array(
			'valid' => true,
			'message' => 'Password meets length requirement'
		);
	}
	
	// Check for 8+ character rule (needs number and special char)
	if ($length >= 8) {
		$hasNumber = preg_match('/[0-9]/', $password);
		$hasSpecial = preg_match('/[^a-zA-Z0-9]/', $password);
		
		if ($hasNumber && $hasSpecial) {
			return array(
				'valid' => true,
				'message' => 'Password meets complexity requirements'
			);
		} else {
			$missing = array();
			if (!$hasNumber) $missing[] = 'number';
			if (!$hasSpecial) $missing[] = 'special character';
			
			return array(
				'valid' => false,
				'message' => 'Password needs: ' . implode(' and ', $missing) . ' (or be 12+ characters)'
			);
		}
	}
	
	return array(
		'valid' => false,
		'message' => 'Password does not meet requirements'
	);
}

/**
 * Process group IDs from request data for user forms
 * DRY function to handle group assignment logic consistently
 * 
 * @param array|null $gids Group IDs from request
 * @param string $fallback_gids Fallback group IDs if no new ones provided
 * @return string Comma-separated group IDs
 */
function processGroupIds($gids, $fallback_gids = '') {
	// Filter out placeholder values and ensure we have a valid array
	if(isset($gids) && is_array($gids)) {
		$filtered_gids = array_filter($gids, function($gid) {
			return $gid !== 'new_personal_group' && !empty($gid);
		});
		return implode(',', $filtered_gids);
	}
	
	// Return fallback if no new gids provided
	return $fallback_gids;
}

/**
 * Generate statistics data for both CSV and PDF reports (DRY principle)
 * Processes effort data and creates summary arrays for consistent reporting
 * 
 * @param Statistics $statistic Statistics object with loaded data
 * @param string $cid Customer ID (empty, numeric, or 'unassigned')
 * @param string $pid Project ID 
 * @param string $mode Report mode ('billed' or other)
 * @param object $_PJ_auth Authentication object
 * @return array Structured data arrays for efforts and summaries
 */
function generateStatisticsData($statistic, $cid, $pid, $mode, $_PJ_auth) {
    $efforts = array();
    $PROJECT_SUM = array();
    $CUSTOMER_SUM = array(); 
    $AGENT_PROJECT_SUM = array();
    $AGENT_SUM = array();
    
    $i = 0;
    while($statistic->nextEffort()) {
        $effort = $statistic->giveEffort();
        
        // Apply same filtering logic as in original templates
        // Skip efforts that don't match the current filter criteria
        if(!empty($cid) && $cid !== 'unassigned') {
            // For specific customer, only show efforts from that customer
            if($effort->giveValue('customer_id') != $cid) {
                continue;
            }
        } elseif($cid === 'unassigned') {
            // For unassigned, only show efforts without valid customer/project
            if($effort->giveValue('customer_id') && $effort->giveValue('project_id')) {
                continue;
            }
        }
        
        if(!empty($pid)) {
            // For specific project, only show efforts from that project
            if($effort->giveValue('project_id') != $pid) {
                continue;
            }
        }
        
        $i++;
        
        // Get agent info
        $agent = $_PJ_auth->giveUserById($effort->giveValue('user'));
        $agent_name = $agent['firstname'] . ' ' . $agent['lastname'];
        
        // Store effort data with processed fields
        $effort_data = array(
            'id' => $effort->giveValue('id'),
            'customer_name' => $effort->giveValue('customer_name') ?: 'Unassigned',
            'project_name' => $effort->giveValue('project_name') ?: 'Unassigned',
            'customer_id' => $effort->giveValue('customer_id'),
            'project_id' => $effort->giveValue('project_id'),
            'user' => $effort->giveValue('user'),
            'agent_name' => $agent_name,
            'date' => $effort->giveValue('date'),
            'begin' => $effort->giveValue('begin'),
            'end' => $effort->giveValue('end'),
            'description' => $effort->giveValue('description'),
            'hours' => $effort->giveValue('hours'),
            'costs' => $effort->giveValue('costs'),
            'billed' => $effort->giveValue('billed')
        );
        
        $efforts[] = $effort_data;
        
        // Build summary arrays - only set names once per project/customer to avoid overwriting
        $project_id = $effort->giveValue('project_id');
        $customer_id = $effort->giveValue('customer_id');
        $user_id = $effort->giveValue('user');
        
        if(!isset($PROJECT_SUM[$project_id])) {
            $PROJECT_SUM[$project_id] = array('hours' => 0, 'costs' => 0);
        }
        if(!isset($PROJECT_SUM[$project_id]['customer'])) {
            $PROJECT_SUM[$project_id]['customer'] = $effort_data['customer_name'];
        }
        if(!isset($PROJECT_SUM[$project_id]['project'])) {
            $PROJECT_SUM[$project_id]['project'] = $effort_data['project_name'];
        }
        $PROJECT_SUM[$project_id]['hours'] += $effort->giveValue('hours');
        $PROJECT_SUM[$project_id]['costs'] += $effort->giveValue('costs');
        
        if(!isset($CUSTOMER_SUM[$customer_id])) {
            $CUSTOMER_SUM[$customer_id] = array('hours' => 0, 'costs' => 0);
        }
        if(!isset($CUSTOMER_SUM[$customer_id]['customer'])) {
            $CUSTOMER_SUM[$customer_id]['customer'] = $effort_data['customer_name'];
        }
        $CUSTOMER_SUM[$customer_id]['hours'] += $effort->giveValue('hours');
        $CUSTOMER_SUM[$customer_id]['costs'] += $effort->giveValue('costs');
        
        if(!isset($AGENT_PROJECT_SUM[$project_id])) {
            $AGENT_PROJECT_SUM[$project_id] = array();
        }
        if(!isset($AGENT_PROJECT_SUM[$project_id][$user_id])) {
            $AGENT_PROJECT_SUM[$project_id][$user_id] = array('hours' => 0, 'costs' => 0);
        }
        if(!isset($AGENT_PROJECT_SUM[$project_id][$user_id]['customer'])) {
            $AGENT_PROJECT_SUM[$project_id][$user_id]['customer'] = $effort_data['customer_name'];
        }
        if(!isset($AGENT_PROJECT_SUM[$project_id][$user_id]['project'])) {
            $AGENT_PROJECT_SUM[$project_id][$user_id]['project'] = $effort_data['project_name'];
        }
        $AGENT_PROJECT_SUM[$project_id][$user_id]['agent'] = $agent_name;
        $AGENT_PROJECT_SUM[$project_id][$user_id]['hours'] += $effort->giveValue('hours');
        $AGENT_PROJECT_SUM[$project_id][$user_id]['costs'] += $effort->giveValue('costs');
        
        if(!isset($AGENT_SUM[$user_id])) {
            $AGENT_SUM[$user_id] = array('hours' => 0, 'costs' => 0);
        }
        $AGENT_SUM[$user_id]['agent'] = $agent_name;
        $AGENT_SUM[$user_id]['hours'] += $effort->giveValue('hours');
        $AGENT_SUM[$user_id]['costs'] += $effort->giveValue('costs');
    }
    
    return array(
        'efforts' => $efforts,
        'project_sum' => $PROJECT_SUM,
        'customer_sum' => $CUSTOMER_SUM,
        'agent_project_sum' => $AGENT_PROJECT_SUM,
        'agent_sum' => $AGENT_SUM,
        'effort_count' => $i
    );
}
