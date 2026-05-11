<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once("../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

// Check authentication
if(!$_PJ_auth || !$_PJ_auth->giveValue('id')) {
	header("Location: /index.php");
	exit;
}

$export_customers = $_REQUEST['export_customers'] ?? null;
$download = $_REQUEST['download'] ?? null;
$file = $_REQUEST['file'] ?? null;
$format = $_REQUEST['format'] ?? 'csv';

// Handle download request
if(!empty($download) && !empty($file)) {
	if($file === 'customers' && !empty($_SESSION['kimai_export_customers'])) {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="kimai_export_customers.csv"');
		echo $_SESSION['kimai_export_customers'];
		exit;
	} elseif($file === 'projects' && !empty($_SESSION['kimai_export_projects'])) {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="kimai_export_projects.csv"');
		echo $_SESSION['kimai_export_projects'];
		exit;
	} else {
		header("Location: kimai_export.php");
		exit;
	}
}

// Show download page if export was successful
if(!empty($download)) {
	$export_count = $_SESSION['kimai_export_count'] ?? 0;
	$center_title = 'Export Complete';
	include("$_PJ_root/templates/kimai_export_download.ihtml.php");
	include_once("$_PJ_include_path/degestiv.inc.php");
	exit;
}

// Handle export request
if(!empty($export_customers)) {
	$selected_customer_ids = $_REQUEST['customer_ids'] ?? [];
	
	if(empty($selected_customer_ids)) {
		$error_message = 'Please select at least one customer to export.';
		$center_title = 'Error';
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	
	// Generate CSV files
	$customers_csv = [];
	$projects_csv = [];
	
	// Header for customers CSV
	$customers_csv[] = 'Name,Company,Number,Comment';
	
	// Header for projects CSV
	$projects_csv[] = 'Name,Customer,Comment';
	
	$db = new Database();
	$db->connect();
	
	foreach($selected_customer_ids as $customer_id) {
		$safe_customer_id = DatabaseSecurity::escapeString($customer_id, $db->Link_ID);
		
		// Get customer data
		$query = "SELECT id, customer_name, customer_desc, customer_address 
				  FROM " . $GLOBALS['_PJ_customer_table'] . " 
				  WHERE id = '$safe_customer_id'";
		$db->query($query);
		
		if($db->next_record()) {
			$customer_name = $db->Record['customer_name'];
			$customer_desc = $db->Record['customer_desc'] ?? '';
			$customer_address = $db->Record['customer_address'] ?? '';
			
			// Add customer to CSV
			$comment = trim($customer_desc . "\n" . $customer_address);
			$customers_csv[] = '"' . str_replace('"', '""', $customer_name) . '","' . str_replace('"', '""', $customer_name) . '","' . $customer_id . '","' . str_replace('"', '""', $comment) . '"';
			
			// Get projects for this customer
			$project_query = "SELECT id, project_name, project_desc 
							  FROM " . $GLOBALS['_PJ_project_table'] . " 
							  WHERE customer_id = '$safe_customer_id' AND closed = 'No'";
			$db->query($project_query);
			
			while($db->next_record()) {
				$project_name = $db->Record['project_name'];
				$project_desc = $db->Record['project_desc'] ?? '';
				
				// Add project to CSV
				$projects_csv[] = '"' . str_replace('"', '""', $project_name) . '","' . str_replace('"', '""', $customer_name) . '","' . str_replace('"', '""', $project_desc) . '"';
			}
		}
	}
	
	// Generate CSV content
	$customers_content = implode("\n", $customers_csv);
	$projects_content = implode("\n", $projects_csv);
	
	// Store CSV content in session for separate downloads
	$_SESSION['kimai_export_customers'] = $customers_content;
	$_SESSION['kimai_export_projects'] = $projects_content;
	$_SESSION['kimai_export_count'] = count($selected_customer_ids);
	
	// Redirect to download page
	header("Location: kimai_export.php?download=1");
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

$center_title = 'Export to Kimai';
include("$_PJ_root/templates/kimai_export.ihtml.php");
include_once("$_PJ_include_path/degestiv.inc.php");
