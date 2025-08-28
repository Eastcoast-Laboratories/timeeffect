<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once("../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');
require_once('../include/invoice.class.php');
require_once('../include/contract.class.php');
require_once('../include/carryover.class.php');

$db = new Database();
$invoice = new Invoice($db, $_PJ_auth->giveValue('id'));
$contract = new Contract($db, $_PJ_auth->giveValue('id'));
$carryover = new Carryover($db, $_PJ_auth->giveValue('id'));

// Handle filters
$filters = [];
if (!empty($_GET['customer_id'])) {
    $filters['customer_id'] = $_GET['customer_id'];
}
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

$invoices = $invoice->getInvoices($filters);

// Get customers for filter dropdown
$customers_query = "SELECT id, customer_name as name FROM " . $GLOBALS['_PJ_customer_table'] . " ORDER BY name";
$db->query($customers_query);
$customers = [];
while ($db->next_record()) {
    $customers[] = $db->Record;
}

$page_title = 'Invoice Management';
include('../templates/invoice/list.ihtml.php');
