# TimeEffect Code Patterns and Examples

This file provides GitHub Copilot with specific code patterns and examples used throughout the TimeEffect application.

## Common Variable Naming Conventions

### Global Configuration Variables
```php
// All global config variables start with $_PJ_
$_PJ_db_host = $_ENV['DB_HOST'];
$_PJ_http_root = $_ENV['APP_HTTP_ROOT'] ?? '';
$_PJ_include_path = $_SERVER['DOCUMENT_ROOT'] . $_PJ_http_root . '/include';
$_PJ_template_path = $_SERVER['DOCUMENT_ROOT'] . $_PJ_http_root . '/templates';
$_PJ_css_path = $_PJ_http_root . '/css';
$_PJ_image_path = $_PJ_http_root . '/images';
```

### Database Table Prefixes
```php
// Tables use configurable prefix
$table_name = $_PJ_table_prefix . 'efforts';
$customer_table = $_PJ_table_prefix . 'customer';
$project_table = $_PJ_table_prefix . 'project';
```

## Database Access Patterns

### Legacy Database Pattern (PEAR DB Compatibility)
```php
// Initialize database connection
$db = new DB_Sql();
$db->connect($_PJ_db_host, $_PJ_db_user, $_PJ_db_password, $_PJ_db_database);

// Simple query
$db->query("SELECT * FROM {$_PJ_table_prefix}customer WHERE id = '$customer_id'");
if ($db->next_record()) {
    $customer_name = $db->f('name');
}

// Insert with error handling
$query = "INSERT INTO {$_PJ_table_prefix}efforts (description, start_time, customer_id) VALUES ('$description', '$start_time', '$customer_id')";
if (!$db->query($query)) {
    die('Database error: ' . $db->error());
}
```

### Modern Database Pattern (Doctrine DBAL)
```php
use Doctrine\DBAL\Connection;

// Get connection
$connection = DatabaseConnection::getInstance();

// Query builder pattern
$queryBuilder = $connection->createQueryBuilder();
$efforts = $queryBuilder
    ->select('e.*', 'c.name as customer_name', 'p.name as project_name')
    ->from($_PJ_table_prefix . 'efforts', 'e')
    ->leftJoin('e', $_PJ_table_prefix . 'customer', 'c', 'e.customer_id = c.id')
    ->leftJoin('e', $_PJ_table_prefix . 'project', 'p', 'e.project_id = p.id')
    ->where('e.user_id = ?')
    ->setParameter(0, $user_id)
    ->execute()
    ->fetchAllAssociative();
```

## Class-Based Object Patterns

### Data Objects (Legacy Pattern)
```php
class Customer extends Data {
    var $table_fields = array(
        'id' => '',
        'name' => '',
        'contact' => '',
        'address' => '',
        'access' => 'rwxr-----'
    );
    
    function Customer($id = '') {
        $this->Data($_PJ_table_prefix . 'customer', $id);
    }
    
    function save() {
        // Apply secure defaults for new records only
        if (!isset($this->data['id']) && $_PJ_registration_secure_defaults) {
            $this->data['access'] = $_PJ_registration_default_access;
        }
        return parent::save();
    }
}
```

### Usage Pattern for Data Objects
```php
// Load existing record
$customer = new Customer($customer_id);
if ($customer->load()) {
    echo $customer->f('name');
}

// Create new record
$customer = new Customer();
$customer->assign($_REQUEST);  // Assign form data
if ($customer->save()) {
    echo "Customer saved successfully";
}
```

## Authentication and Security Patterns

### Login Check Pattern
```php
// At the top of protected pages
if (!$no_login) {
    include_once($_PJ_include_path . '/auth.inc.php');
    if (!$auth->auth['uid']) {
        header('Location: ' . $_PJ_http_root . '/');
        exit;
    }
}
```

### Permission Check Pattern
```php
// Check user permissions for an object
if (!$customer->may('read', $auth->auth['uid'])) {
    die('Access denied');
}

// Check project access
if (!$project->may('write', $auth->auth['uid'])) {
    echo "You don't have permission to edit this project";
    exit;
}
```

### Brute Force Protection
```php
// Login attempt tracking
$login_attempts = new LoginAttempts();
if ($login_attempts->isBlocked($_SERVER['REMOTE_ADDR'])) {
    die('Too many failed login attempts. Please try again later.');
}

if (!$auth->start()) {
    $login_attempts->recordFailedAttempt($_SERVER['REMOTE_ADDR']);
} else {
    $login_attempts->clearFailedAttempts($_SERVER['REMOTE_ADDR']);
}
```

## Template and UI Patterns

### Page Structure Pattern
```php
<?php
// Bootstrap and configuration
require_once(__DIR__ . "/bootstrap.php");
include_once("include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

// Authentication check (if needed)
if (!$no_login) {
    include_once($_PJ_include_path . '/auth.inc.php');
}

// Process form data
if ($_REQUEST['action'] == 'save') {
    // Handle form submission
}

// Set page variables
$center_title = "Page Title";
?>

<!DOCTYPE html>
<html lang="<?= $_PJ_language ?>">
<head>
    <title>TimeEffect - <?= $center_title ?></title>
    <link rel="stylesheet" href="<?= $_PJ_css_path ?>/project.css">
    <link rel="stylesheet" href="<?= $_PJ_css_path ?>/modern.css">
</head>
<body>
    <?php include($_PJ_template_path . '/shared/header.ihtml'); ?>
    
    <main class="main-content">
        <!-- Page content -->
    </main>
    
    <?php include($_PJ_template_path . '/shared/footer.ihtml'); ?>
</body>
</html>
```

### Form Processing Pattern
```php
// Form validation and processing
if ($_REQUEST['action'] == 'save') {
    $errors = array();
    
    // Validate required fields
    if (empty($_REQUEST['name'])) {
        $errors[] = "Name is required";
    }
    
    if (empty($errors)) {
        $customer = new Customer($_REQUEST['id']);
        $customer->assign($_REQUEST);
        
        if ($customer->save()) {
            $success_message = "Customer saved successfully";
            // Redirect to prevent resubmission
            header("Location: {$_SERVER['PHP_SELF']}?id={$customer->f('id')}&success=1");
            exit;
        } else {
            $errors[] = "Failed to save customer";
        }
    }
}
```

## Migration Patterns

### Database Migration Structure
```php
class MigrationManager {
    private $current_version = 1; // Increment for new migrations
    
    public function runPendingMigrations() {
        $migrations = [];
        
        // Check if migration 1 is needed
        if ($this->getCurrentVersion() < 1) {
            $migrations[] = $this->runMigration1();
        }
        
        return $migrations;
    }
    
    private function runMigration1() {
        // Create login attempts table for brute force protection
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_prefix}login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_time (ip_address, attempt_time)
        )";
        
        if ($this->connection->executeStatement($sql)) {
            $this->updateVersion(1);
            return "Migration 1: Created login_attempts table";
        }
        return false;
    }
}
```

## Error Handling Patterns

### Error Logging
```php
// Using Monolog for modern logging
use Monolog\Logger;

$logger = new Logger('timeeffect');
$logger->error('Database connection failed', [
    'host' => $_PJ_db_host,
    'database' => $_PJ_db_database,
    'error' => $connection_error
]);

// Legacy error handling
if (!$result) {
    error_log("TimeEffect Error: " . $db->error() . " in " . __FILE__ . " line " . __LINE__);
    die('A database error occurred. Please contact the administrator.');
}
```

### Input Validation
```php
// Sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Usage in forms
$name = sanitize_input($_REQUEST['name'] ?? '');
$email = sanitize_input($_REQUEST['email'] ?? '');

if (!validate_email($email)) {
    $errors[] = "Invalid email format";
}
```

## JavaScript Patterns

### AJAX Request Pattern
```javascript
// Modern fetch API usage
function saveData(formData) {
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Data saved successfully', 'success');
        } else {
            showMessage('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Network error occurred', 'error');
    });
}
```

### Form Validation
```javascript
// Client-side validation
function validateForm(form) {
    const errors = [];
    
    // Check required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            errors.push(`${field.name} is required`);
        }
    });
    
    if (errors.length > 0) {
        alert('Please fix the following errors:\n' + errors.join('\n'));
        return false;
    }
    
    return true;
}
```

## CSS Class Patterns

### Common CSS Classes
```css
/* Layout classes */
.main-content { /* Main content area */ }
.sidebar { /* Side navigation */ }
.form-container { /* Form wrapper */ }
.button-group { /* Button grouping */ }

/* State classes */
.error { color: #d32f2f; }
.success { color: #388e3c; }
.warning { color: #f57c00; }
.info { color: #1976d2; }

/* Component classes */
.effort-row { /* Time entry row */ }
.customer-card { /* Customer display */ }
.project-item { /* Project list item */ }
.stat-widget { /* Statistics widget */ }
```

## Configuration Patterns

### Environment Variable Usage
```php
// Boolean configuration
$_PJ_debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
$_PJ_allow_registration = ($_ENV['ALLOW_REGISTRATION'] ?? 'false') === 'true';

// Numeric configuration with defaults
$_PJ_session_length = (int)($_ENV['SESSION_LIFETIME'] ?? 36000);
$_PJ_max_efforts_total = (int)($_ENV['MAX_EFFORTS_TOTAL'] ?? 1000);

// String configuration with fallbacks
$_PJ_default_language = $_ENV['APP_LANGUAGE'] ?? 'en';
$_PJ_currency = $_ENV['CURRENCY'] ?? 'EUR';
```

These patterns should help GitHub Copilot understand the coding conventions and suggest appropriate code completions for the TimeEffect project.