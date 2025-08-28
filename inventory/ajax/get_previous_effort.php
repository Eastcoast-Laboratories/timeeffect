<?php
// AJAX endpoint to get previous effort for overlap detection
require_once(__DIR__ . "/../../bootstrap.php");
include_once("../../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

header('Content-Type: application/json');


// Check authentication
if (!$_PJ_auth->checkAuth()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get parameters
$date = $_GET['date'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$exclude_id = $_GET['exclude_id'] ?? '';

if (empty($date) || empty($user_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

$db = new Database();
$db->connect();

// Escape parameters
$safe_date = DatabaseSecurity::escapeString($date, $db->Link_ID);
$safe_user_id = DatabaseSecurity::escapeString($user_id, $db->Link_ID);
$safe_exclude_id = !empty($exclude_id) ? DatabaseSecurity::escapeString($exclude_id, $db->Link_ID) : '';

// Build query to get the most recent effort on the same date for the same user
$query = "SELECT e.id, e.description, e.begin, e.end, p.project_name, c.customer_name 
          FROM " . $GLOBALS['_PJ_effort_table'] . " e
          LEFT JOIN " . $GLOBALS['_PJ_project_table'] . " p ON e.project_id = p.id
          LEFT JOIN " . $GLOBALS['_PJ_customer_table'] . " c ON p.customer_id = c.id
          WHERE DATE(e.date) = '$safe_date' 
          AND e.user = '$safe_user_id'
          AND e.end IS NOT NULL 
          AND e.end != '00:00:00'";

// Exclude current effort if editing
if (!empty($safe_exclude_id)) {
    $query .= " AND e.id != '$safe_exclude_id'";
}

$query .= " ORDER BY e.end DESC LIMIT 1";

debugLog("LOG_OVERLAP_CHECK", "Query: " . $query);

$db->query($query);

if ($db->next_record()) {
    $effort = [
        'id' => $db->Record['id'],
        'description' => $db->Record['description'],
        'begin' => $db->Record['begin'],
        'end' => $db->Record['end'],
        'project_name' => $db->Record['project_name'] ?? 'No Project',
        'customer_name' => $db->Record['customer_name'] ?? 'No Customer'
    ];
    
    debugLog("LOG_OVERLAP_CHECK", "Found previous effort: ID=" . $effort['id'] . ", End=" . $effort['end']);
    echo json_encode(['success' => true, 'effort' => $effort]);
} else {
    debugLog("LOG_OVERLAP_CHECK", "No previous effort found for date: " . $date);
    echo json_encode(['success' => true, 'effort' => null]);
}
?>
