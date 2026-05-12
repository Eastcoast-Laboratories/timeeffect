<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once("../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

// Check authentication
if(!$_PJ_auth || !$_PJ_auth->giveValue('id')) {
	header("Location: /index.php");
	exit;
}

$center_template = "kimai_efforts_export";
$center_title = $GLOBALS['_PJ_strings']['kimai_efforts_export'] ?? 'Kimai Efforts Export';

// Handle export request
if(isset($_REQUEST['export'])) {
	// Load all efforts - pass null for customer and project to get all efforts
	$customer = null;
	$project = null;
	$effort_list = new EffortList($customer, $project, $_PJ_auth, true);
	
	// Generate CSV
	$csv = '"Date","From","To","Duration","Rate","User","Customer","Project","Activity","Description","Exported","Tags","Hourly rate","Fixed rate"' . "\n";
	
	while($effort_list->nextEffort()) {
		$effort = $effort_list->giveEffort();
		
		$date = date('Y-m-d', strtotime($effort->giveValue('date')));
		$from = substr($effort->giveValue('begin'), 0, 5); // HH:MM
		$to = substr($effort->giveValue('end'), 0, 5); // HH:MM
		
		// Calculate duration in seconds
		$begin_time = strtotime($effort->giveValue('begin'));
		$end_time = strtotime($effort->giveValue('end'));
		$duration = $end_time - $begin_time;
		
		$rate = $effort->giveValue('rate');
		$user = $_PJ_auth->giveValue('email') ? $_PJ_auth->giveValue('email') : $_PJ_auth->giveValue('username');
		$customer_name = $effort->giveValue('customer_name') ? $effort->giveValue('customer_name') : '';
		$project_name = $effort->giveValue('project_name') ? $effort->giveValue('project_name') : '';
		$activity = 'global'; // Default activity
		$description = $effort->giveValue('description') ? $effort->giveValue('description') : '';
		$exported = $effort->giveValue('billed') ? '1' : '0';
		$tags = '';
		$hourly_rate = $rate;
		$fixed_rate = '0';
		
		// Escape fields for CSV
		$csv .= '"' . $date . '","' . $from . '","' . $to . '","' . $duration . '","' . $rate . '","' . $user . '","' . $customer_name . '","' . $project_name . '","' . $activity . '","' . $description . '","' . $exported . '","' . $tags . '","' . $hourly_rate . '","' . $fixed_rate . '"' . "\n";
	}
	
	// Store CSV in session
	$_SESSION['kimai_efforts_csv'] = $csv;
	
	// Redirect to download page
	header('Location: kimai_efforts_export.php?download=1');
	exit;
}

// Handle download request
if(isset($_REQUEST['download'])) {
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

include("$_PJ_root/templates/list.ihtml.php");
include_once("$_PJ_include_path/degestiv.inc.php");
