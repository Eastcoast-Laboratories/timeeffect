<?php
    // Fix: Start output buffering immediately to prevent 'headers already sent' error
    ob_start();
    
    require_once(__DIR__ . "/../bootstrap.php");
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Fix: Initialize variables to prevent undefined variable warnings
	$cid = isset($cid) ? $cid : (isset($_REQUEST['cid']) ? $_REQUEST['cid'] : '');
	$pid = isset($pid) ? $pid : (isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '');
	$eid = isset($eid) ? $eid : (isset($_REQUEST['eid']) ? $_REQUEST['eid'] : '');

	// Additional parameters needed by CSV template
	$year   = $_REQUEST['year']   ?? null;
	$month  = $_REQUEST['month']  ?? null;
	$syear  = $_REQUEST['syear']  ?? null;
	$smonth = $_REQUEST['smonth'] ?? null;
	$sday   = $_REQUEST['sday']   ?? null;
	$eyear  = $_REQUEST['eyear']  ?? null;
	$emonth = $_REQUEST['emonth'] ?? null;
	$eday   = $_REQUEST['eday']   ?? null;
	$mode   = $_REQUEST['mode']   ?? null;
	$users  = $_REQUEST['users']  ?? null;

	// Create customer object only for valid cid and not for special 'unassigned'
	$customer	= ($cid && $cid !== 'unassigned') ? new Customer($_PJ_auth, $cid) : null;
	// Create project object only if customer object exists and project id provided
	$project	= ($customer && $pid) ? new Project($customer, $_PJ_auth, $pid) : null;

	$center_template	= "statistic/csv";
	include("$_PJ_root/templates/statistic/csv/list.ihtml.php");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>