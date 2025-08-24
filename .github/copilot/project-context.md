# TimeEffect Project Context for GitHub Copilot

## Project Overview
TimeEffect is a modernized PHP time tracking application that has been upgraded from legacy PEAR code to PHP 8.4 with Composer dependencies. It maintains backward compatibility while providing modern development practices.

## Key Technologies
- **Backend**: PHP 8.4, MySQL/MariaDB
- **Modern Stack**: Composer, Doctrine DBAL, Monolog, Symfony Components
- **Legacy Compatibility**: PEAR DB compatibility layer
- **Frontend**: HTML, CSS (modern.css, responsive), JavaScript
- **Testing**: PHPUnit
- **Deployment**: Docker support, environment-based configuration

## Code Patterns and Conventions

### File Organization
- Main application files in root directory (index.php, bootstrap.php)
- Core modules in subdirectories: inventory/, user/, admin/, report/, statistic/
- Shared libraries in include/ directory
- Templates in templates/ directory with modular structure
- Static assets in css/, js/, images/, icons/

### PHP Coding Style
- Legacy code uses PEAR-style conventions
- New code follows modern PSR standards
- Environment variables loaded via .env file
- Configuration in include/config.inc.php (legacy bridge)
- Composer autoloading with PSR-4 namespace "TimeEffect\\"

### Database Patterns
- Legacy code uses PEAR DB with compatibility layer
- New code uses Doctrine DBAL
- Automatic migrations via MigrationManager class
- Table prefix configurable via DB_PREFIX environment variable
- Connection management in include/database.inc.php

### Authentication & Security
- Session-based authentication in include/auth.inc.php
- Role-based permissions with access control strings
- Brute force protection in include/login_attempts.inc.php
- Input validation and sanitization throughout
- Secure defaults for new user registrations

## Common Code Patterns

### Database Queries (Modern)
```php
use Doctrine\DBAL\Connection;

$connection = DatabaseConnection::getInstance();
$queryBuilder = $connection->createQueryBuilder();
$result = $queryBuilder
    ->select('*')
    ->from('table_name')
    ->where('column = ?')
    ->setParameter(0, $value)
    ->execute();
```

### Database Queries (Legacy with Compatibility)
```php
// Uses PEAR DB compatibility layer
$db = new DB_Sql();
$db->query("SELECT * FROM {$_PJ_table_prefix}table WHERE column = '$value'");
```

### Template Rendering
```php
include_once($_PJ_include_path . '/layout.inc.php');
include($_PJ_template_path . '/module/template.ihtml');
```

### Configuration Access
```php
// Environment variables loaded in config.inc.php
$_PJ_db_host = $_ENV['DB_HOST'];
$_PJ_http_root = $_ENV['APP_HTTP_ROOT'] ?? '';
```

### Error Handling and Logging
```php
use Monolog\Logger;

// Application logging
$logger = new Logger('timeeffect');
$logger->info('Operation completed', ['context' => $data]);

// Legacy error handling
if (!$result) {
    die('Database error: ' . $db->error());
}
```

## Important Context

### Migration from Legacy
- Application was migrated from SourceForge CVS to GitHub
- Legacy PEAR DB calls are wrapped with compatibility layer
- Modern Composer dependencies added alongside legacy code
- Bootstrap system initializes both modern and legacy systems

### Key Modules
- **inventory/efforts.php**: Core time tracking functionality
- **inventory/customer.php**: Customer management
- **inventory/projects.php**: Project management
- **user/settings.php**: User preferences and theme management
- **include/migrations.inc.php**: Database migration system
- **include/auth.inc.php**: Authentication and session management

### Development Guidelines
- Add new features using modern PHP 8.4 and Composer dependencies
- Maintain backward compatibility with existing legacy code
- Use environment variables for configuration
- Follow existing template structure for UI consistency
- Add logging for debugging and monitoring
- Write tests for new functionality

### Database Schema
- Tables use configurable prefix (default: te_)
- User authentication with sessions and login attempts tracking
- Time tracking with efforts linked to projects and customers
- Role-based access control with groups and permissions
- Migration tracking table for schema versioning

### UI/UX Patterns
- Responsive design with modern.css and responsive.css
- Consistent navigation structure across modules
- Form validation with JavaScript and server-side checks
- Progressive enhancement for accessibility
- Theme support (light/dark mode)

## Files to Reference for Context
- `ARCHITECTURE.md` - Complete application architecture
- `dev/memories-for-AI.md` - Development history and context
- `docs/DATABASE_MIGRATIONS.md` - Migration system documentation
- `include/config.inc.php` - Configuration system
- `bootstrap.php` - Application initialization
- `.env.example` - Environment configuration template

## When Working on This Project
1. Check existing patterns in similar modules before implementing new features
2. Maintain compatibility between legacy and modern code
3. Use the migration system for database changes
4. Follow the template structure for UI consistency
5. Add appropriate logging and error handling
6. Consider both desktop and mobile users in UI changes
7. Test with existing PHPUnit test suite
8. Document significant changes in relevant .md files