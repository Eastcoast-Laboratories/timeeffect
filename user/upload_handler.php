<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once(__DIR__ . "/../include/config.inc.php");

// Check authentication - users need 'admin' permission for file uploads
if (!$_PJ_auth->checkPermission('admin')) {
    http_response_code(403);
    echo json_encode([
        'error' => 'Access denied - admin permission required for file uploads',
        'debug' => [
            'auth_object' => isset($_PJ_auth) ? 'exists' : 'missing',
            'user_id' => $_PJ_auth ? $_PJ_auth->giveValue('id') : 'no auth',
            'session_data' => session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive',
            'has_user_perm' => $_PJ_auth ? $_PJ_auth->checkPermission('user') : false,
            'has_admin_perm' => $_PJ_auth ? $_PJ_auth->checkPermission('admin') : false
        ]
    ]);
    exit;
}

$user_id = $_PJ_auth->giveValue('id');
$upload_dir = $_PJ_root . "/uploads/user_" . $user_id;

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create upload directory']);
        exit;
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $upload_type = $_POST['type'] ?? 'logo'; // logo, letterhead, footer
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'File too large. Maximum size is 5MB.']);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $upload_type . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update user record with new file path
        $db = new DB_Sql();
        $relative_path = "/uploads/user_" . $user_id . "/" . $filename;
        
        $field_map = [
            'logo' => 'invoice_logo_path',
            'letterhead' => 'invoice_letterhead_path',
            'footer' => 'invoice_footer_path'
        ];
        
        if (isset($field_map[$upload_type])) {
            $field = $field_map[$upload_type];
            $query = "UPDATE " . $_PJ_table_prefix . "auth SET " . $field . " = '" . 
                     addslashes($relative_path) . "' WHERE id = " . intval($user_id);
            $db->query($query);
            
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'path' => $relative_path,
                'type' => $upload_type
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid upload type']);
        }
    } else {
        $error_msg = error_get_last();
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to save file to: ' . $filepath,
            'details' => 'Check directory permissions. Upload dir: ' . $upload_dir,
            'php_error' => $error_msg ? $error_msg['message'] : 'Unknown error'
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
}
