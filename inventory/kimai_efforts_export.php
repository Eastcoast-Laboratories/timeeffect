<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once("../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

// Check authentication
if(!$_PJ_auth || !$_PJ_auth->giveValue('id')) {
	header("Location: /index.php");
	exit;
}

$export_efforts = $_REQUEST['export_efforts'] ?? null;
$download = $_REQUEST['download'] ?? null;

// Handle download request
if(!empty($download)) {
	if(!isset($_SESSION['kimai_efforts_csv'])) {
		$error_message = "No CSV data available";
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	
	$csv = $_SESSION['kimai_efforts_csv'];
	
	// Send CSV as download
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="kimai_timesheets.csv"');
	header('Content-Length: ' . strlen($csv));
	
	echo $csv;
	exit;
}

// Handle export request
if(!empty($export_efforts)) {
	$selected_customer_ids = $_REQUEST['customer_ids'] ?? [];
	
	if(empty($selected_customer_ids)) {
		$error_message = 'Please select at least one customer to export.';
		$center_title = 'Error';
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	
	// Override global limit to export all efforts
	$GLOBALS['_PJ_max_efforts_total'] = 999999999;
	
	// Generate CSV
	$csv = '"Date","From","To","Duration","Rate","User","Email","Customer","Project","Activity","Description","Exported","Tags","HourlyRate","FixedRate","InternalRate","meta.timesheet_foo"' . "\n";
	
	// Build customer IDs for SQL query
	$safe_customer_ids = array_map(function($id) {
		return (int)$id;
	}, $selected_customer_ids);
	$customer_ids_string = implode(',', $safe_customer_ids);
	
	// Use direct SQL to load efforts for selected customers without limit
	$db = new Database();
	$db->connect();
	
	$safeEffortTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_effort_table']);
	$safeProjectTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_project_table']);
	$safeCustomerTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
	
	$query = "SELECT {$safeEffortTable}.*, {$safeProjectTable}.project_name, {$safeCustomerTable}.customer_name
			  FROM {$safeEffortTable}
			  LEFT JOIN {$safeProjectTable} ON {$safeEffortTable}.project_id = {$safeProjectTable}.id
			  LEFT JOIN {$safeCustomerTable} ON {$safeProjectTable}.customer_id = {$safeCustomerTable}.id
			  WHERE {$safeCustomerTable}.id IN ($customer_ids_string)
			  ORDER BY date DESC, begin DESC";
	
	$db->query($query);
	
	while($db->next_record()) {
		$date = date('Y-m-d', strtotime($db->Record['date']));
		$from = substr($db->Record['begin'], 0, 5); // HH:MM
		$to = substr($db->Record['end'], 0, 5); // HH:MM
		
		// Calculate duration in seconds
		$begin_time = strtotime($db->Record['begin']);
		$end_time = strtotime($db->Record['end']);
		$duration = $end_time - $begin_time;
		
		$rate = $db->Record['rate'];
		$user = $_PJ_auth->giveValue('username');
		$email = $_PJ_auth->giveValue('email') ?? '';
		$customer_name = $db->Record['customer_name'] ?? '';
		$project_name = $db->Record['project_name'] ?? '';
		
		// Use default project name if no project is assigned
		if(empty($project_name)) {
			$project_name = 'Unassigned';
		}
		
		$activity = 'global'; // Default activity
		$description = $db->Record['description'] ?? '';
		$exported = (!empty($db->Record['billed']) && $db->Record['billed'] != '0000-00-00') ? '1' : '0';
		$tags = '';
		$hourly_rate = $rate;
		$fixed_rate = '0';
		$internal_rate = '0';
		$meta_timesheet_foo = '';
		
		// Escape fields for CSV (comma delimiter with quotes)
		$csv .= '"' . $date . '","' . $from . '","' . $to . '","' . $duration . '","' . $rate . '","' . $user . '","' . $email . '","' . $customer_name . '","' . $project_name . '","' . $activity . '","' . $description . '","' . $exported . '","' . $tags . '","' . $hourly_rate . '","' . $fixed_rate . '","' . $internal_rate . '","' . $meta_timesheet_foo . '"' . "\n";
	}
	
	// Store CSV in session
	$_SESSION['kimai_efforts_csv'] = $csv;
	
	// Redirect to download page
	header('Location: kimai_efforts_export.php?download=1');
	exit;
}

// Get all customers for selection
$customer_list = new CustomerList($_PJ_auth);
$customers = [];

while($customer_list->nextCustomer()) {
	$customer = $customer_list->giveCustomer();
	if($customer->checkUserAccess('read')) {
		$customers[] = [
			'id' => $customer->giveValue('id'),
			'name' => $customer->giveValue('customer_name'),
			'active' => $customer->giveValue('active')
		];
	}
}

$center_title = $GLOBALS['_PJ_strings']['kimai_efforts_export'] ?? 'Kimai Efforts Export';
include("$_PJ_root/templates/kimai_efforts_export/list.ihtml.php");
include_once("$_PJ_include_path/degestiv.inc.php");
