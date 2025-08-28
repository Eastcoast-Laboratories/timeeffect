<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once(__DIR__ . "/../include/config.inc.php");
include_once($GLOBALS['_PJ_include_path'] . '/scripts.inc.php');
require_once(__DIR__ . "/../include/contract.class.php");

// Check authentication using TimeEffect auth system
if (!$_PJ_auth->giveValue('id')) {
    header('Location: ../index.php');
    exit;
}

$contract = new Contract($db, $_PJ_auth->giveValue('id'));

$customer_id = $_GET['customer_id'] ?? $_POST['customer_id'] ?? 0;
$contract_id = $_GET['id'] ?? $_POST['id'] ?? 0;
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if (!$customer_id) {
    header('Location: customer.php');
    exit;
}

// Get customer info
$customer_query = "SELECT customer_name as name FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id = " . intval($customer_id);
$db->query($customer_query);
$customer_data = false;
if ($db->next_record()) {
    $customer_data = $db->Record;
}

if (!$customer_data) {
    header('Location: customer.php');
    exit;
}

$errors = [];
$success = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'update') {
        $contract_data = [
            'customer_id' => $customer_id,
            'project_id' => $_POST['project_id'] ?: null,
            'contract_type' => $_POST['contract_type'] ?? 'hourly',
            'fixed_amount' => $_POST['fixed_amount'] ?: null,
            'fixed_hours' => $_POST['fixed_hours'] ?: null,
            'hourly_rate' => $_POST['hourly_rate'] ?: null,
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?: null,
            'description' => $_POST['description'] ?? '',
            'active' => isset($_POST['active']) ? 1 : 0
        ];
        
        // Validate contract data
        $errors = $contract->validateContract($contract_data);
        
        // Check for overlapping contracts
        if (empty($errors)) {
            $exclude_id = ($action === 'update') ? $contract_id : null;
            if ($contract->hasOverlappingContract(
                $customer_id, 
                $contract_data['project_id'], 
                $contract_data['start_date'], 
                $contract_data['end_date'], 
                $exclude_id
            )) {
                $errors[] = 'Contract period overlaps with existing contract';
            }
        }
        
        if (empty($errors)) {
            if ($action === 'create') {
                $new_id = $contract->createContract($contract_data);
                if ($new_id) {
                    $success = true;
                    header("Location: contracts.php?customer_id={$customer_id}&success=created");
                    exit;
                } else {
                    $errors[] = 'Failed to create contract';
                }
            } else {
                if ($contract->updateContract($contract_id, $contract_data)) {
                    $success = true;
                    header("Location: contracts.php?customer_id={$customer_id}&success=updated");
                    exit;
                } else {
                    $errors[] = 'Failed to update contract';
                }
            }
        }
    } elseif ($action === 'deactivate') {
        if ($contract->deactivateContract($contract_id)) {
            header("Location: contracts.php?customer_id={$customer_id}&success=deactivated");
            exit;
        } else {
            $errors[] = 'Failed to deactivate contract';
        }
    }
}

// Get contracts for customer
$contracts = $contract->getCustomerContracts($customer_id);

// Get projects for dropdown
$projects_query = "SELECT id, project_name as name FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id = " . intval($customer_id) . " ORDER BY project_name";
$db->query($projects_query);
$projects = [];
while ($db->next_record()) {
    $projects[] = $db->Record;
}

// Get specific contract for editing
$contract_data = null;
if ($action === 'edit' && $contract_id) {
    $contract_data = $contract->getContract($contract_id);
    if (!$contract_data || $contract_data['customer_id'] != $customer_id) {
        header("Location: contracts.php?customer_id={$customer_id}");
        exit;
    }
}

// Set up template variables for unified layout
$center_template = "inventory/customer/contracts";
$center_title = 'Contract Management - ' . $customer_data['name'];

include("$_PJ_root/templates/list.ihtml.php");
include_once("$_PJ_include_path/degestiv.inc.php");
