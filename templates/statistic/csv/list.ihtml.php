<?php
// Fix: Start output buffering to prevent 'headers already sent' error
ob_start();

function tableHead($fields) {
	$table_head='';
	foreach($fields as $field_name) {
		if(!empty($table_head)) {
			$table_head .= ';';
		}
		$table_head .= '"' . str_replace('"', '""', $field_name) . '"';
	}
	return $table_head . "\n";
}

$GLOBALS['fields'] = array(
//		<field name>	=> <string>
		'count'			=> unhtmlentities($GLOBALS['_PJ_strings']['numbershort']),
		'customer'		=> unhtmlentities($GLOBALS['_PJ_strings']['customer']),
		'project'		=> unhtmlentities($GLOBALS['_PJ_strings']['project']),
		'agent'			=> unhtmlentities($GLOBALS['_PJ_strings']['agent']),
		'date'			=> unhtmlentities($GLOBALS['_PJ_strings']['date']),
		'billed'		=> unhtmlentities($GLOBALS['_PJ_strings']['billed']),
		'time' 			=> unhtmlentities($GLOBALS['_PJ_strings']['from_to']),
		'description'	=> unhtmlentities($GLOBALS['_PJ_strings']['description']),
		'effort'		=> unhtmlentities($GLOBALS['_PJ_strings']['hours_short']),
		'price'			=> unhtmlentities($GLOBALS['_PJ_strings']['costs'])
);

// Detect unassigned efforts
$show_unassigned = isset($cid) && $cid === 'unassigned';

if(intval(@$year) && intval(@$month)) {
	$statistic	= new Statistics($_PJ_auth, false, $customer, $project, @$users, $mode, $show_unassigned);
	$statistic->loadMonth($year, $month, $mode);
} elseif(intval($syear) && intval($eyear)) {
	if(empty($smonth)) {
		$smonth = '01';
	}
	if(empty($sday)) {
		$sday = '01';
	}
	if(empty($emonth)) {
		$emonth = date('m');
	}
	if(empty($eday)) {
		$eday = date('d');
	}
	$statistic	= new Statistics($_PJ_auth, false, $customer, $project, @$users, $mode, $show_unassigned);
	$statistic->loadTime("$syear-$smonth-$sday", "$eyear-$emonth-$eday", $mode);
} else {
	$statistic	= new Statistics($_PJ_auth, true, $customer, $project, @$users, $mode, $show_unassigned);
}

if(!empty($cid) && $cid !== 'unassigned') {
	unset($GLOBALS['fields']['customer']);
} else {
	unset($GLOBALS['fields']['count']);
}
if(!empty($pid)) {
	unset($GLOBALS['fields']['project']);
}
if($mode != 'billed') {
	unset($GLOBALS['fields']['billed']);
}

// Use central data generation function (DRY principle)
$data = generateStatisticsData($statistic, $cid, $pid, $mode, $_PJ_auth);
$efforts = $data['efforts'];
$PROJECT_SUM = $data['project_sum'];
$CUSTOMER_SUM = $data['customer_sum'];
$AGENT_PROJECT_SUM = $data['agent_project_sum'];
$AGENT_SUM = $data['agent_sum'];

$output = tableHead($GLOBALS['fields']);

// Generate CSV rows from processed effort data
foreach($efforts as $effort_data) {
	if(empty($cid)) {
		$output .= '"' . str_replace('"', '""', $effort_data['customer_name']) . '";';
	} elseif($cid === 'unassigned') {
		// For unassigned efforts, show customer name instead of count
		$output .= '"' . str_replace('"', '""', $effort_data['customer_name']) . '";';
	} else {
		$output .= '"' . $effort_data['id'] . '";';
	}

	if(empty($pid)) {
		$output .= '"' . str_replace('"', '""', $effort_data['project_name']) . '";';
	}

	$output .= '"' . str_replace('"', '""', $effort_data['agent_name']) . '";';
	$output .= '"' . formatDate($effort_data['date'], $GLOBALS['_PJ_format_date']) . '";';

	if(!empty($mode) and $mode == 'billed') {
		if($effort_data['billed']) {
			$formatted_billed = formatDate($effort_data['billed'], $GLOBALS['_PJ_format_date']);
		} else {
			$formatted_billed = '';
		}
		$output .= '"' . $formatted_billed . '";';
	}

	$output .= '"' . formatTime($effort_data['begin'], "H:i") . " - " . formatTime($effort_data['end'], "H:i") . '";';

	// Clean description for CSV
	$string = preg_replace("/\<br\>/", '', $effort_data['description']);
	$string = preg_replace("/\<li\>/", " - ", $string);
	$string = preg_replace("/<[^>]+>/", '', $string);
	$string = preg_replace("/<[^>]+>/", '', $string);
	$string = str_replace("\r", '', $string);
	$string = str_replace("\n", ' ', $string);

	$output .= '"' . str_replace('"', '""', $string) . '";';
	$output .= '"' . formatNumber($effort_data['hours'], true) . '";';
	$output .= '"' . formatNumber($effort_data['costs'], true) . '"';
	$output .= "\n";
}

// Fix: Clear output buffer before setting headers
ob_clean();

if(isset($HTTP_ENV_VARS['HTTP_USER_AGENT']) and strpos($HTTP_ENV_VARS['HTTP_USER_AGENT'],'MSIE 5.5')) {
	Header('Content-Type: application/dummy');
} else {
	Header('Content-Type: application/octet-stream');
	// Guard against null objects when exporting unassigned efforts
	if (is_object($project) && $project->giveValue('project_name')) {
		$customer_name = is_object($customer) ? $customer->giveValue('customer_name') : 'unassigned';
		$file_name = str_replace(' ', '_', $customer_name . "-" . $project->giveValue('project_name') . '.csv');
	} elseif (is_object($customer) && $customer->giveValue('customer_name')) {
		$file_name = str_replace(' ', '_', $customer->giveValue('customer_name') . '.csv');
	} else {
		$file_name = "effort.csv";
	}
	Header('Content-disposition: attachment; filename=' . $file_name);
}

Header('Pragma: no-cache');

print $output;
print "\n\n" . unhtmlentities($GLOBALS['_PJ_strings']['subtotals']) . ';' . unhtmlentities($GLOBALS['_PJ_strings']['projects']) . "\n";

$GLOBALS['fields'] = array(
		'customer'		=> unhtmlentities($GLOBALS['_PJ_strings']['customer']),
		'project'		=> unhtmlentities($GLOBALS['_PJ_strings']['project']),
		'effort'		=> unhtmlentities($GLOBALS['_PJ_strings']['hours_short']),
		'price'			=> unhtmlentities($GLOBALS['_PJ_strings']['costs'])
);
if(!empty($cid) && $cid !== 'unassigned') {
	unset($GLOBALS['fields']['customer']);
}
if(!empty($pid)) {
	unset($GLOBALS['fields']['project']);
}
$output = tableHead($GLOBALS['fields']);
foreach($PROJECT_SUM as $project_id => $project_values) {
	if(empty($cid) || $cid === 'unassigned') {
		$output .= '"' . str_replace('"', '""', $project_values['customer']) . '";';
	}
	$output .= '"' . str_replace('"', '""', $project_values['project']) . '";';
	$output .= '"' . formatNumber($project_values['hours'], true) . '";';
	$output .= '"' . formatNumber($project_values['costs'], true) . '"';
	$output .= "\n";
}
print $output;

if(empty($pid)) {
	print "\n\n" . unhtmlentities($GLOBALS['_PJ_strings']['subtotals']) . ';' . unhtmlentities($GLOBALS['_PJ_strings']['customers']) . "\n";
	
	$GLOBALS['fields'] = array(
			'customer'		=> unhtmlentities($GLOBALS['_PJ_strings']['customer']),
			'effort'		=> unhtmlentities($GLOBALS['_PJ_strings']['hours_short']),
			'price'			=> unhtmlentities($GLOBALS['_PJ_strings']['costs'])
	);
	if(!empty($cid) && $cid !== 'unassigned') {
		unset($GLOBALS['fields']['customer']);
	}
	
	$output = tableHead($GLOBALS['fields']);
	foreach($CUSTOMER_SUM as $customer_id => $customer_values) {
		if(empty($cid) || $cid === 'unassigned') {
			$output .= '"' . str_replace('"', '""', $customer_values['customer']) . '";';
		}
		$output .= '"' . formatNumber($customer_values['hours'], true) . '";';
		$output .= '"' . formatNumber($customer_values['costs'], true) . '"';
		$output .= "\n";
	}
	
	print $output;
}
print "\n\n" . unhtmlentities($GLOBALS['_PJ_strings']['subtotals']) . ';' . unhtmlentities($GLOBALS['_PJ_strings']['projects']) . '/' . unhtmlentities($GLOBALS['_PJ_strings']['agent']) . "\n";

$GLOBALS['fields'] = array(
		'customer'		=> unhtmlentities($GLOBALS['_PJ_strings']['customer']),
		'project'		=> unhtmlentities($GLOBALS['_PJ_strings']['project']),
		'agent'			=> unhtmlentities($GLOBALS['_PJ_strings']['agent']),
		'effort'		=> unhtmlentities($GLOBALS['_PJ_strings']['hours_short']),
		'price'			=> unhtmlentities($GLOBALS['_PJ_strings']['costs'])
);
if(!empty($cid) && $cid !== 'unassigned') {
	unset($GLOBALS['fields']['customer']);
}
if(!empty($pid)) {
	unset($GLOBALS['fields']['project']);
}

$output = tableHead($GLOBALS['fields']);
foreach($AGENT_PROJECT_SUM as $project_id => $project_values) {
	foreach($project_values as $agent_id => $agent_values) {
		if(empty($cid) || $cid === 'unassigned') {
			$output .= '"' . str_replace('"', '""', $agent_values['customer']) . '";';
		}
		if(empty($pid)) {
			$output .= '"' . str_replace('"', '""', $agent_values['project']) . '";';
		}
		$output .= '"' . str_replace('"', '""', $agent_values['agent']) . '";';
		$output .= '"' . formatNumber($agent_values['hours'], true) . '";';
		$output .= '"' . formatNumber($agent_values['costs'], true) . '"';
		$output .= "\n";
	}
}

print $output;
print "\n\n" . unhtmlentities($GLOBALS['_PJ_strings']['subtotals']) . ';' . unhtmlentities($GLOBALS['_PJ_strings']['agent']) . "\n";

$GLOBALS['fields'] = array(
		'agent'			=> unhtmlentities($GLOBALS['_PJ_strings']['agent']),
		'effort'		=> unhtmlentities($GLOBALS['_PJ_strings']['hours_short']),
		'price'			=> unhtmlentities($GLOBALS['_PJ_strings']['costs'])
);
$output = tableHead($GLOBALS['fields']);
foreach($AGENT_SUM as $agent_id => $agent_values) {
	$output .= '"' . str_replace('"', '""', $agent_values['agent']) . '";';
	$output .= '"' . formatNumber($agent_values['hours'], true) . '";';
	$output .= '"' . formatNumber($agent_values['costs'], true) . '"';
	$output .= "\n";
}

print $output;
?>
