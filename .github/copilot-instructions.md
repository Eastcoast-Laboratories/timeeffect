# TimeEffect PHP Time Tracking Application

TimeEffect is a PHP 8.4 web application for multi-user time tracking with project management, customer billing, and reporting capabilities. Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Bootstrap, Build, and Test the Repository

**CRITICAL**: Set appropriate timeouts for all build commands. DO NOT use default timeouts that may cause premature cancellation.

#### Environment Setup (Required First)
```bash
# Copy environment configuration template
cp .env.example .env

# Edit .env with your database credentials (required for full functionality)
nano .env
```

#### Dependency Installation
```bash
# Install modern PHP dependencies via Composer
composer install --no-dev --optimize-autoloader
# NEVER CANCEL: Takes 5-10 minutes. Set timeout to 900+ seconds (15 minutes).
# Network issues may require GitHub token: composer config --global --auth github-oauth.github.com <token>
```

#### Docker Development Environment (Recommended)
```bash
# Navigate to Docker directory  
cd docker/

# Automated setup (builds and starts all services)
./setup.sh
# NEVER CANCEL: Takes 10-20 minutes including image build. Set timeout to 1200+ seconds (20 minutes).

# Alternative: Manual Docker commands
sudo docker compose build --no-cache
sudo docker compose up -d
# Each step takes 5-15 minutes. NEVER CANCEL.
```

#### Manual PHP Development Server (Alternative)
```bash
# Start PHP built-in web server for testing
php -S localhost:8080
# Ready in ~3 seconds. Access: http://localhost:8080/
```

### Test the Application

#### PHPUnit Test Suite
```bash
# Run complete test suite
./vendor/bin/phpunit
# NEVER CANCEL: Takes 2-5 minutes. Set timeout to 600+ seconds (10 minutes).

# Alternative: Run individual tests
php tests/ACLTest.php
php tests/LoginTest.php  
php tests/effort-save-without-project-test.php
```

#### Database Migration Testing
```bash
# Run database migrations
php migrate.php
# Takes 1-3 minutes. Set timeout to 300+ seconds (5 minutes).
```

#### Validation Commands
```bash
# Check PHP syntax on key files
php -l bootstrap.php
php -l index.php

# Count project size (261 PHP files)
find . -name "*.php" | wc -l
```

## Installation and Setup

### Web-Based Installation (Primary Method)
1. **Access installer**: Navigate to `http://localhost:8080/install/` or `http://localhost/install/`
2. **Configure database**: Use installer web interface to set up database connection
3. **Default credentials**: 
   - Database: `timeeffect_db`
   - User: `timeeffect`
   - Password: `very_unsecure_timeeffect_PW1` (change in production)
   - Host: `localhost` or `db` (Docker)
   - Prefix: `te_`

### Manual Environment Configuration (.env)
```bash
# Database settings
DB_HOST=localhost
DB_DATABASE=timeeffect_db  
DB_USERNAME=timeeffect
DB_PASSWORD=your_secure_password

# Application settings
APP_ENV=development
APP_DEBUG=true
APP_HTTP_ROOT=/timeeffect
APP_LANGUAGE=en

# Required PHP settings
# In php.ini: short_open_tag = On
```

## Validation Scenarios

**MANUAL VALIDATION REQUIREMENT**: After building and running the application, ALWAYS test actual functionality by executing complete user scenarios.

### Core User Workflows to Test
1. **Installation Flow**:
   - Access `/install/` directory
   - Complete database configuration
   - Verify installation success
   
2. **User Authentication**:
   - Register new user account
   - Login with credentials
   - Test access permissions

3. **Time Tracking Workflow**:
   - Create customer record
   - Create project under customer
   - Add time entry with start/stop times
   - Edit and save time entries

4. **Reporting Functions**:
   - Generate time reports
   - Export CSV/PDF formats
   - View project statistics

5. **Administrative Tasks**:
   - User management
   - Project permissions
   - Database migration execution

## Key Components and Structure

### Critical Files and Directories
- **`bootstrap.php`** - Modern application initialization with Composer autoloading
- **`include/config.inc.php`** - Main configuration file that loads `.env` settings
- **`index.php`** - Application entry point
- **`install/`** - Web-based installation system
- **`migrate.php`** - Database migration tool
- **`vendor/`** - Composer dependencies (Doctrine DBAL, Monolog, Symfony)
- **`tests/`** - PHPUnit test suite
- **`docker/`** - Docker development environment

### Database Tables (with `te_` prefix)
- `te_customer` - Customer/client records
- `te_project` - Project organization
- `te_effort` - Time tracking entries
- `te_auth` - User authentication
- `te_migrations` - Schema version tracking

### Modern Infrastructure Components
- **Doctrine DBAL** - Database abstraction layer
- **Monolog** - Logging system (writes to `logs/app.log`)
- **Symfony Components** - HTTP foundation, environment handling
- **PEAR DB Compatibility** - Legacy database code support

## Common Development Tasks

### Adding New Features
1. **Always start by running validation steps**:
   ```bash
   composer install --no-dev --optimize-autoloader
   php -l your-new-file.php
   ```

2. **Test your changes**:
   ```bash
   php -S localhost:8080
   # Navigate to affected functionality and test manually
   ```

3. **Run related tests**:
   ```bash
   php tests/related-test.php
   ./vendor/bin/phpunit --filter=YourTestClass
   ```

### Database Changes
1. **Create migration**: Add new method to `include/migrations.inc.php`
2. **Test migration**: `php migrate.php`
3. **Verify schema**: Check database manually
4. **Test application**: Run full user scenario validation

### Code Style and Validation
```bash
# Always validate syntax before committing
php -l modified-file.php

# Check for common issues
grep -r "mysql_" include/  # Find legacy MySQL usage
grep -r "\$_PJ_" include/  # Find global configuration variables
```

## Environment Limitations and Workarounds

### Network-Restricted Environments
- **Composer install may fail** due to GitHub API limits
- **Workaround**: Configure GitHub token or use offline mode
- **Command**: `composer config --global --auth github-oauth.github.com <token>`

### Docker Unavailable
- **Use PHP built-in server**: `php -S localhost:8080`
- **Manual database setup**: Install MySQL/MariaDB separately
- **Testing limitations**: Some features require full web server

### Missing Dependencies
- **Symptom**: Missing Symfony polyfill errors
- **Workaround**: Create empty bootstrap files or reinstall vendor packages
- **Command**: `mkdir -p vendor/symfony/polyfill-mbstring && echo "<?php" > vendor/symfony/polyfill-mbstring/bootstrap.php`

## Important File Patterns

### Global Configuration Variables
All configuration uses `$_PJ_` prefix:
```php
$_PJ_db_host = $_ENV['DB_HOST'];
$_PJ_http_root = $_ENV['APP_HTTP_ROOT'] ?? '';
$_PJ_include_path = $_SERVER['DOCUMENT_ROOT'] . $_PJ_http_root . '/include';
```

### Database Table Naming
All tables use configurable prefix:
```php
$table_name = $_PJ_table_prefix . 'efforts';  // becomes 'te_efforts'
$customer_table = $_PJ_table_prefix . 'customer';  // becomes 'te_customer'
```

### Authentication Pattern
```php
// Check if user is logged in
if (!$no_login) {
    include_once($_PJ_include_path . '/auth.inc.php');
    if (!$auth->auth['uid']) {
        header('Location: ' . $_PJ_http_root . '/');
        exit;
    }
}
```

## Timing Expectations and Timeouts

**CRITICAL**: Always set appropriate timeouts and NEVER CANCEL long-running operations.

- **Composer install**: 5-10 minutes normal, up to 15 minutes with network issues
  - **Timeout setting**: 900+ seconds (15 minutes)
  - **NEVER CANCEL**: May appear to hang but is downloading dependencies

- **Docker build**: 10-20 minutes for full build including image creation
  - **Timeout setting**: 1200+ seconds (20 minutes) 
  - **NEVER CANCEL**: Building PHP 8.4 image and installing dependencies

- **PHPUnit tests**: 2-5 minutes for full suite
  - **Timeout setting**: 600+ seconds (10 minutes)
  - **Individual tests**: 5-30 seconds each

- **Database migration**: 1-3 minutes depending on schema complexity
  - **Timeout setting**: 300+ seconds (5 minutes)

- **Web server startup**: 3-5 seconds
- **File operations**: <1 second
- **Syntax validation**: <1 second

## Troubleshooting Common Issues

### "Failed opening required" errors
- **Cause**: Missing Composer dependencies
- **Solution**: Run `composer install` or create placeholder files
- **Always check**: `vendor/` directory exists and is populated

### Database connection errors
- **Check**: `.env` file configuration
- **Verify**: Database server is running and accessible
- **Test**: Access installer at `/install/` to configure database

### Docker issues
- **Check**: `docker --version` and `docker compose --version`
- **Alternative**: Use PHP built-in server for development
- **Logs**: `docker compose logs app` for debugging

### Permission errors
- **Set**: `chmod 755 logs/` for log directory
- **Set**: `chmod 600 .env` for security
- **Check**: Web server user has access to application files

Always validate that EVERY command works before adding it to your development workflow. If a command does not work in your environment, document the limitation and provide alternative approaches.

## Localization Integration

### Standard Localization Pattern
All user-facing strings must use the standard TimeEffect localization pattern:

```php
<?php if(!empty($GLOBALS['_PJ_strings']['key'])) echo $GLOBALS['_PJ_strings']['key'] ?>
```

### Language Files Structure
- **Location**: `/include/languages/`
- **Files**: `de.inc.php`, `en.inc.php`, `fr.inc.php`
- **Format**: `$GLOBALS['_PJ_strings']['key'] = 'Localized Text';`

### Implementation Rules
1. **Always check for existing strings** before creating new ones
2. **Use consistent key naming**: lowercase with underscores (e.g., `new_contract`, `hourly_rate`)
3. **Add strings to all language files** (de, en, fr)
4. **Never use hardcoded text** in templates or user-facing PHP files

### Common Localization Keys
```php
// Basic UI elements
'back' => 'Zurück'
'new' => 'Neu'
'edit' => 'Bearbeiten'
'delete' => 'Löschen'
'save' => 'Speichern'
'cancel' => 'Abbrechen'
'create' => 'Erstellen'
'update' => 'Aktualisieren'

// Status and actions
'active' => 'Aktiv'
'inactive' => 'Inaktiv'
'status' => 'Status'
'actions' => 'Aktionen'

// Common entities
'customer' => 'Kunde'
'project' => 'Projekt'
'contract' => 'Vertrag'
'effort' => 'Aufwand'
```

### Testing Localization
1. **Check language loading**: Verify `$GLOBALS['_PJ_strings']` is populated
2. **Test all languages**: Switch between de/en/fr in user settings
3. **Validate fallbacks**: Ensure graceful handling of missing strings