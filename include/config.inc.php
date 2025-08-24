<?php
/* vim: set expandtab shiftwidth=4 softtabstop=4 tabstop=4: */

/* ******************************************************** */
/* TimeEffect Configuration - .env First Approach          */
/* ******************************************************** */

// Load Composer autoloader and .env
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file - required for operation
if (!file_exists(__DIR__ . '/../.env')) {
    die('ERROR: .env file not found. Please run the installation or create .env from .env.example');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Validate required environment variables
$required_vars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PREFIX'];
foreach ($required_vars as $var) {
    if (!isset($_ENV[$var])) {
        die("ERROR: Required environment variable $var not found in .env file");
    }
}

/* ******************************************************** */
/* Legacy Variables from .env                               */
/* ******************************************************** */

// Application settings
$_PJ_http_root = $_ENV['APP_HTTP_ROOT'] ?? '';
$_PJ_root = $_ENV['APP_ROOT_PATH'] ?? $_SERVER['DOCUMENT_ROOT'] . $_PJ_http_root;
$_PJ_default_language = $_ENV['APP_LANGUAGE'] ?? 'en';
$_PJ_debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

// Formatting settings (fallback to defaults if not in .env)
$_PJ_decimal_point = $_ENV['DECIMAL_POINT'] ?? '.';
$_PJ_thousands_seperator = $_ENV['THOUSANDS_SEPARATOR'] ?? ',';
$_PJ_currency = $_ENV['CURRENCY'] ?? 'EUR';

// Database configuration
$_PJ_db_host = $_ENV['DB_HOST'];
$_PJ_db_database = $_ENV['DB_DATABASE'];
$_PJ_db_user = $_ENV['DB_USERNAME'];
$_PJ_db_password = $_ENV['DB_PASSWORD'];
$_PJ_table_prefix = $_ENV['DB_PREFIX'];

// Session configuration
$_PJ_session_length = (int)($_ENV['SESSION_LIFETIME'] ?? 36000);

// Permission settings
$_PJ_agents_allow_delete = ($_ENV['ALLOW_DELETE'] ?? 'true') === 'true' ? 1 : 0;

// Registration settings
$_PJ_allow_registration = ($_ENV['ALLOW_REGISTRATION'] ?? 'false') === 'true';
$_PJ_registration_email_confirm = ($_ENV['REGISTRATION_EMAIL_CONFIRM'] ?? 'false') === 'true';
$_PJ_allow_password_recovery = ($_ENV['ALLOW_PASSWORD_RECOVERY'] ?? 'true') === 'true';
$_PJ_registration_secure_defaults = ($_ENV['REGISTRATION_SECURE_DEFAULTS'] ?? 'true') === 'true';
$_PJ_registration_default_access = $_ENV['REGISTRATION_DEFAULT_ACCESS'] ?? 'rwxr-----';

// Performance settings (optional)
if (isset($_ENV['DEFAULT_BILLED_ENTRIES_LIMIT'])) {
    $_PJ_default_billed_entries_limit = (int)$_ENV['DEFAULT_BILLED_ENTRIES_LIMIT'];
}
if (isset($_ENV['MAX_EFFORTS_TOTAL'])) {
    $_PJ_max_efforts_total = (int)$_ENV['MAX_EFFORTS_TOTAL'];
}

// Database charset encoding
$GLOBALS['mysql_charset'] = 'utf8';

/* ******************************************************** */
/* Legacy compatibility - END                               */
/* ******************************************************** */

require_once('fix_php7.php');
require_once('aperetiv.inc.php');
