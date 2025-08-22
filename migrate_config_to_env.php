<?php
/**
 * Automatic Migration Script: Legacy config.inc.php to .env
 * 
 * This script detects if config.inc.php contains legacy values and automatically
 * migrates them to a .env file, then replaces config.inc.php with the new .env-reader version.
 */

echo "🔄 TimeEffect Config Migration Tool\n";
echo "===================================\n";
echo "⚠️  WICHTIG: Führe dieses Script VOR 'git pull' aus!\n";
echo "    Sonst gehen alle produktiven Werte verloren!\n\n";

$base_dir = __DIR__;
$config_file = $base_dir . '/include/config.inc.php';
$env_file = $base_dir . '/.env';
$backup_file = $base_dir . '/include/config.inc.php.backup-migration-' . date('Y-m-d-H-i-s');

// Check if config.inc.php exists
if (!file_exists($config_file)) {
    die("❌ Config file not found: $config_file\n");
}

// Read current config.inc.php
$config_content = file_get_contents($config_file);

// Check if this is already the new .env-reader version
if (strpos($config_content, 'Dotenv::createImmutable') !== false) {
    echo "✅ Config is already using .env system - no migration needed!\n";
    
    // Check if .env exists
    if (!file_exists($env_file)) {
        echo "⚠️  WARNING: .env file missing but config expects it!\n";
        echo "📝 Creating .env from .env.example...\n";
        
        if (file_exists($base_dir . '/.env.example')) {
            copy($base_dir . '/.env.example', $env_file);
            echo "✅ .env created from .env.example\n";
            echo "🔧 Please edit .env with your actual values!\n";
        } else {
            echo "❌ .env.example not found - manual .env creation required\n";
        }
    } else {
        echo "✅ .env file exists - system ready!\n";
    }
    exit(0);
}

echo "🔍 Legacy config.inc.php detected - starting migration...\n\n";

// Extract values from legacy config
$config = array(
    'db_host' => 'localhost',
    'db_name' => 'timeeffect',
    'db_user' => 'root',
    'db_password' => '',
    'db_prefix' => 'te_',
    'debug' => 'false',
    'http_root' => '',
    'root_path' => $base_dir,
    'language' => 'en',
    'decimal_point' => '.',
    'thousands_seperator' => ',',
    'currency' => 'EUR',
    'session_length' => '3600',
    'allow_delete' => '1',
    'allow_registration' => 'false',
    'registration_email_confirm' => 'false',
    'allow_password_recovery' => 'true',
    'registration_secure_defaults' => 'true',
    'registration_default_access' => 'rwxr-----'
);

// Extract database settings
if (preg_match('/\$_PJ_db_host\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['db_host'] = $matches[1];
    echo "📋 DB Host: {$matches[1]}\n";
}

if (preg_match('/\$_PJ_db_database\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['db_name'] = $matches[1];
    echo "📋 DB Database: {$matches[1]}\n";
}

if (preg_match('/\$_PJ_db_user\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['db_user'] = $matches[1];
    echo "📋 DB User: {$matches[1]}\n";
}

if (preg_match('/\$_PJ_db_password\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['db_password'] = $matches[1];
    echo "📋 DB Password: " . (strlen($matches[1]) > 0 ? '[SET]' : '[EMPTY]') . "\n";
}

if (preg_match('/\$_PJ_table_prefix\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['db_prefix'] = $matches[1];
    echo "📋 DB Prefix: {$matches[1]}\n";
}

// Extract application settings
if (preg_match('/\$_PJ_debug\s*=\s*(true|false|1|0)/i', $config_content, $matches)) {
    $config['debug'] = ($matches[1] === 'true' || $matches[1] === '1') ? 'true' : 'false';
    echo "📋 Debug: {$config['debug']}\n";
}

if (preg_match('/\$_PJ_http_root\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['http_root'] = $matches[1];
    echo "📋 HTTP Root: '{$matches[1]}'\n";
}

if (preg_match('/\$_PJ_default_language\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['language'] = $matches[1];
    echo "📋 Language: {$matches[1]}\n";
}

if (preg_match('/\$_PJ_decimal_point\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['decimal_point'] = $matches[1];
    echo "📋 Decimal Point: '{$matches[1]}'\n";
}

if (preg_match('/\$_PJ_thousands_seperator\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['thousands_seperator'] = $matches[1];
    echo "📋 Thousands Separator: '{$matches[1]}'\n";
}

if (preg_match('/\$_PJ_currency\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['currency'] = $matches[1];
    echo "📋 Currency: {$matches[1]}\n";
}

if (preg_match('/\$_PJ_session_length\s*=\s*(\d+)/i', $config_content, $matches)) {
    $config['session_length'] = $matches[1];
    echo "📋 Session Length: {$matches[1]}\n";
}

// Extract permission settings
if (preg_match('/\$_PJ_agents_allow_delete\s*=\s*(\d+|true|false)/i', $config_content, $matches)) {
    $config['allow_delete'] = ($matches[1] === '1' || $matches[1] === 'true') ? '1' : '0';
    echo "📋 Allow Delete: {$config['allow_delete']}\n";
}

// Extract registration settings (may not exist in older configs)
if (preg_match('/\$_PJ_allow_registration\s*=\s*(true|false|1|0)/i', $config_content, $matches)) {
    $config['allow_registration'] = ($matches[1] === 'true' || $matches[1] === '1') ? 'true' : 'false';
    echo "📋 Allow Registration: {$config['allow_registration']}\n";
}

if (preg_match('/\$_PJ_registration_email_confirm\s*=\s*(true|false|1|0)/i', $config_content, $matches)) {
    $config['registration_email_confirm'] = ($matches[1] === 'true' || $matches[1] === '1') ? 'true' : 'false';
    echo "📋 Registration Email Confirm: {$config['registration_email_confirm']}\n";
}

if (preg_match('/\$_PJ_allow_password_recovery\s*=\s*(true|false|1|0)/i', $config_content, $matches)) {
    $config['allow_password_recovery'] = ($matches[1] === 'true' || $matches[1] === '1') ? 'true' : 'false';
    echo "📋 Allow Password Recovery: {$config['allow_password_recovery']}\n";
}

if (preg_match('/\$_PJ_registration_secure_defaults\s*=\s*(true|false|1|0)/i', $config_content, $matches)) {
    $config['registration_secure_defaults'] = ($matches[1] === 'true' || $matches[1] === '1') ? 'true' : 'false';
    echo "📋 Registration Secure Defaults: {$config['registration_secure_defaults']}\n";
}

if (preg_match('/\$_PJ_registration_default_access\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['registration_default_access'] = $matches[1];
    echo "📋 Registration Default Access: {$matches[1]}\n";
}

// Detect root path
if (preg_match('/\$_PJ_root\s*=\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['root_path'] = $matches[1];
    echo "📋 Root Path: {$matches[1]}\n";
} elseif (preg_match('/\$_PJ_root\s*=\s*\$_SERVER\[[\'\"](.*?)[\'"]\]\s*\.\s*[\'\"](.*?)[\'\"]/i', $config_content, $matches)) {
    $config['root_path'] = $_SERVER[$matches[1]] . $matches[2];
    echo "📋 Root Path (calculated): {$config['root_path']}\n";
}

// Generate secure key
function generate_secure_key() {
    if (function_exists('random_bytes')) {
        try {
            return base64_encode(random_bytes(32));
        } catch (Exception $e) {
            // Fallback
        }
    }
    
    $bytes = '';
    for ($i = 0; $i < 32; $i++) {
        $bytes .= chr(mt_rand(0, 255));
    }
    return base64_encode($bytes);
}

$secure_key = generate_secure_key();
$current_date = date('Y-m-d H:i:s');

echo "\n🔧 Creating .env file...\n";

// Create .env content
$env_content = <<<EOT
# TimeEffect Environment Configuration
# Migrated from legacy config.inc.php on: {$current_date}

# Database Configuration
DB_HOST={$config['db_host']}
DB_DATABASE={$config['db_name']}
DB_USERNAME={$config['db_user']}
DB_PASSWORD={$config['db_password']}
DB_PREFIX={$config['db_prefix']}

# Application Settings
APP_ENV=production
APP_DEBUG={$config['debug']}
APP_HTTP_ROOT={$config['http_root']}
APP_ROOT_PATH={$config['root_path']}
APP_LANGUAGE={$config['language']}

# Formatting Settings
DECIMAL_POINT={$config['decimal_point']}
THOUSANDS_SEPARATOR={$config['thousands_seperator']}
CURRENCY={$config['currency']}

# Session Configuration
SESSION_LIFETIME={$config['session_length']}
SESSION_SECURE=false

# User Registration Settings
ALLOW_REGISTRATION={$config['allow_registration']}
REGISTRATION_EMAIL_CONFIRM={$config['registration_email_confirm']}
ALLOW_PASSWORD_RECOVERY={$config['allow_password_recovery']}
REGISTRATION_SECURE_DEFAULTS={$config['registration_secure_defaults']}
REGISTRATION_DEFAULT_ACCESS={$config['registration_default_access']}

# Other Settings
ALLOW_DELETE={$config['allow_delete']}

# Security
APP_KEY=base64:{$secure_key}

# Logging Configuration
LOG_LEVEL=info
LOG_CHANNEL=single

EOT;

// Write .env file
if (file_put_contents($env_file, $env_content)) {
    echo "✅ .env file created successfully!\n";
} else {
    die("❌ Failed to create .env file!\n");
}

echo "\n💾 Creating backup of old config.inc.php...\n";

// Backup old config
if (copy($config_file, $backup_file)) {
    echo "✅ Backup created: $backup_file\n";
} else {
    die("❌ Failed to create backup!\n");
}

echo "\n🔄 Replacing config.inc.php with .env-reader version...\n";

// Create new config.inc.php content
$new_config_content = <<<'EOT'
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
$_PJ_session_length = (int)($_ENV['SESSION_LIFETIME'] ?? 3600);

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
EOT;

// Write new config.inc.php
if (file_put_contents($config_file, $new_config_content)) {
    echo "✅ config.inc.php updated to .env-reader version!\n";
} else {
    die("❌ Failed to update config.inc.php!\n");
}

echo "\n🎉 Migration completed successfully!\n";
echo "===================================\n";
echo "✅ Legacy config.inc.php backed up to: $backup_file\n";
echo "✅ New .env file created with all your settings\n";
echo "✅ config.inc.php now reads from .env\n";
echo "\n🔧 Next steps for deployment:\n";
echo "1. ✅ Migration done - all values extracted!\n";
echo "2. 🚀 Now run: git pull origin master\n";
echo "3. 📦 Then run: composer install --no-dev --optimize-autoloader\n";
echo "4. 🔒 Set permissions: chmod 600 .env\n";
echo "5. 🧪 Test application\n";
echo "\n⚠️  WICHTIG: .env-Datei wird von git pull NICHT überschrieben!\n";
echo "    Alle produktiven Werte sind sicher in .env gespeichert.\n";
echo "\n🚀 Ready for deployment - all original values preserved!\n";
