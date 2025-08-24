# TimeEffect Application Architecture

This document provides a comprehensive overview of the TimeEffect application structure, components, and how everything works together.

## Application Overview

TimeEffect is a **PHP/MySQL-based multi-user time tracking system** designed for recording time employees spend on different projects. The application has been modernized from legacy PEAR code to **PHP 8.4** with modern **Composer dependencies** while maintaining backward compatibility.

### Core Functionality
- **Time Tracking**: Record time spent on projects with detailed effort logging
- **Project Management**: Manage customers, projects, and team assignments
- **User Management**: Multi-user system with role-based access control
- **Reporting**: Generate CSV and PDF reports for billing and analysis
- **Statistics**: View project statistics and time analytics

## Directory Structure

```
timeeffect/
├── 📁 admin/                 # Administrative functions
├── 📁 css/                   # Stylesheets (modern.css, project.css, layout.css)
├── 📁 dev/                   # Development tools and documentation
├── 📁 docker/                # Docker containerization files
├── 📁 docs/                  # Comprehensive documentation
├── 📁 groups/                # Group management functionality
├── 📁 icons/                 # Application icons and UI assets
├── 📁 images/                # Static images and graphics
├── 📁 include/               # Core PHP libraries and compatibility layers
├── 📁 install/               # Installation wizard and database setup
├── 📁 inventory/             # Core business logic (customers, projects, efforts)
├── 📁 js/                    # JavaScript files and client-side logic
├── 📁 report/                # Report generation functionality
├── 📁 sql/                   # Database migrations and schema files
├── 📁 statistic/             # Statistics and analytics
├── 📁 templates/             # HTML templates for all pages
├── 📁 tests/                 # PHPUnit tests
├── 📁 user/                  # User management and settings
├── 📁 vendor/                # Composer dependencies
├── 📄 bootstrap.php          # Modern application initialization
├── 📄 composer.json          # Dependency management
├── 📄 index.php              # Main application entry point
├── 📄 migrate.php            # Database migration interface
└── 📄 .env                   # Environment configuration
```

## Core Components

### 1. Application Bootstrap (`bootstrap.php`)
- **Purpose**: Modern PHP 8.4 initialization and Composer autoloading
- **Responsibilities**:
  - Load Composer autoloader
  - Initialize environment variables from `.env`
  - Set up error reporting based on environment
  - Configure logging with Monolog
  - Maintain backward compatibility with legacy PEAR code

### 2. Configuration System
- **Legacy**: `include/config.inc.php` - Main configuration loader
- **Modern**: `.env` file - Environment-based configuration
- **Features**:
  - Database connection settings
  - Application paths and URLs
  - Localization and formatting
  - Security and permission settings
  - Performance tuning parameters

### 3. Database Layer
- **Modern**: Doctrine DBAL for new code
- **Legacy**: PEAR DB compatibility layer (`include/compatibility.php`)
- **Migration System**: Automated database migrations via `migrations.inc.php`
- **Key Files**:
  - `include/database.inc.php` - Database connection management
  - `include/migrations.inc.php` - Migration manager
  - `sql/` - Migration scripts

### 4. Authentication & Security
- **Files**:
  - `include/auth.inc.php` - Authentication system
  - `include/security.inc.php` - Security utilities
  - `include/login_attempts.inc.php` - Brute force protection
- **Features**:
  - Session management
  - Role-based access control
  - Brute force protection
  - Password reset functionality
  - Registration system with email confirmation

## Application Modules

### Inventory Module (`inventory/`)
**Core business logic for time tracking**

- **`customer.php`** - Customer management (companies, contacts, billing info)
- **`projects.php`** - Project management (project details, assignments, rates)
- **`efforts.php`** - Time tracking (effort logging, time entries, billing)

### User Module (`user/`)
**User management and personalization**

- **`index.php`** - User profile and management
- **`settings.php`** - User preferences and configuration
- **`save-theme.php`** - Theme and appearance settings

### Report Module (`report/`)
**Report generation and export**

- **`index.php`** - Report selection and configuration
- Integration with PDF and CSV export functionality

### Statistic Module (`statistic/`)
**Analytics and statistics**

- **`customer.php`** - Customer-based statistics
- **`projects.php`** - Project analytics
- **`efforts.php`** - Time tracking statistics
- **`csv.php`** - CSV export functionality
- **`pdf.php`** - PDF report generation

### Groups Module (`groups/`)
**Team and group management**

- **`index.php`** - Group creation and member management
- Role-based permissions within groups

### Admin Module (`admin/`)
**Administrative functions**

- **`pdflayout.php`** - PDF report layout configuration
- System administration and configuration

## Application Flow

### 1. Request Lifecycle

```
1. HTTP Request → index.php
2. Bootstrap Loading → bootstrap.php
3. Environment Setup → .env + config.inc.php
4. Authentication Check → auth.inc.php
5. Route to Module → inventory/, user/, report/, etc.
6. Template Rendering → templates/
7. Response Output → HTML/JSON/PDF
```

### 2. Authentication Flow

```
1. User Access → Login Check
2. Session Validation → auth.inc.php
3. Permission Check → security.inc.php
4. Brute Force Check → login_attempts.inc.php
5. Access Granted/Denied
```

### 3. Database Operations

```
1. Legacy Code → PEAR DB compatibility layer
2. Modern Code → Doctrine DBAL
3. Migrations → Automatic on login via migrations.inc.php
4. Connection Management → database.inc.php
```

## Frontend Architecture

### CSS Structure
- **`project.css`** - Core application styles
- **`modern.css`** - Modern UI enhancements
- **`layout.css`** - Layout and responsive design
- **`responsive.css`** - Mobile and tablet optimization

### Template System
Located in `templates/` directory:
- **`shared/`** - Common templates (headers, navigation, footers)
- **`inventory/`** - Business logic templates
- **`user/`** - User management templates
- **`admin/`** - Administrative templates

### JavaScript
- **`include/functions.js`** - Core JavaScript utilities
- **`js/`** - Additional client-side functionality
- Modern ES6+ features with fallbacks for older browsers

## Database Architecture

### Migration System
- **Automatic Migrations**: Run during login, managed by `MigrationManager`
- **Manual Migrations**: SQL scripts in `sql/` directory
- **Version Tracking**: `{prefix}migrations` table tracks applied migrations
- **Safety Features**: Idempotent operations with rollback support

### Key Tables
- **Users & Authentication**: User accounts, sessions, login attempts
- **Business Logic**: Customers, projects, efforts (time entries)
- **Access Control**: Groups, permissions, role assignments
- **System**: Configuration, migrations, audit logs

## Modern Infrastructure

### Composer Dependencies
```json
{
  "doctrine/dbal": "^3.7",           // Modern database abstraction
  "symfony/http-foundation": "^6.4",  // HTTP handling
  "monolog/monolog": "^3.5",         // Logging system
  "vlucas/phpdotenv": "^5.6"         // Environment configuration
}
```

### Compatibility Layer
- **`include/compatibility.php`** - PEAR DB → Doctrine DBAL bridge
- **`include/fix_php7.php`** - Legacy PHP compatibility
- Maintains backward compatibility while enabling modern development

## Development Workflow

### Local Development
1. **Setup**: Copy `.env.example` to `.env` and configure
2. **Dependencies**: Run `composer install`
3. **Database**: Set up MySQL/MariaDB database
4. **Installation**: Use web-based installer or migrate.php
5. **Testing**: PHPUnit tests in `tests/` directory

### Production Deployment
1. **Environment**: Configure `.env` with production settings
2. **Dependencies**: `composer install --no-dev --optimize-autoloader`
3. **Database**: Automatic migrations on first login
4. **Security**: Proper file permissions and secure configuration
5. **Monitoring**: Check `logs/app.log` for issues

### Development Tools
Located in `dev/` directory:
- **`plan.md`** - Current development roadmap
- **`memories-for-AI.md`** - AI development context
- **`TODO.md`** - Development task list
- Analysis and modernization tools

## Security Features

### Authentication
- Session-based authentication with configurable timeout
- Password hashing with modern algorithms
- Brute force protection with progressive delays
- Optional two-factor authentication support

### Access Control
- Role-based permissions (read, write, execute, admin)
- Project-level access control
- Group-based permissions
- Secure defaults for new users

### Data Protection
- SQL injection prevention
- XSS protection in templates
- CSRF protection for forms
- Input validation and sanitization

## Performance Considerations

### Database
- Connection pooling and optimization
- Indexed queries for large datasets
- Efficient pagination for reports
- Database migration performance monitoring

### Frontend
- CSS and JavaScript minification
- Image optimization
- Responsive design for mobile devices
- Progressive enhancement approach

### Caching
- OPcache for PHP bytecode
- Template caching where appropriate
- Database query optimization
- Static asset optimization

## Integration Points

### External Systems
- **Email**: SMTP integration for notifications and password reset
- **LDAP**: Optional LDAP authentication
- **APIs**: RESTful endpoints for external integration
- **Export**: CSV and PDF generation for external systems

### File System
- **Logs**: `logs/` directory for application logging
- **Uploads**: Secure file upload handling
- **Backups**: Database backup integration
- **Templates**: Modular template system

## Troubleshooting & Monitoring

### Logging
- **Application Logs**: `logs/app.log` with Monolog
- **Error Logging**: PHP error logs and custom error handling
- **Audit Logs**: User activity and security events
- **Performance Logs**: Database query timing and optimization

### Common Issues
- **Database Connection**: Check `.env` configuration and MySQL service
- **File Permissions**: Ensure web server can read/write required directories
- **PHP Configuration**: Verify `short_open_tag = On` and required extensions
- **Migration Errors**: Check database logs and migration status

### Debug Mode
Enable in `.env`:
```
APP_ENV=development
APP_DEBUG=true
```

This provides detailed error messages and debugging information for development.

---

## Getting Started

For new developers joining the project:

1. **Read Documentation**: Start with `README.md` and `DEPLOYMENT.md`
2. **Understand Legacy**: Review `dev/memories-for-AI.md` for context
3. **Set Up Environment**: Follow installation guide
4. **Explore Code**: Start with `index.php` and `bootstrap.php`
5. **Run Tests**: Execute PHPUnit tests to verify setup
6. **Check Issues**: Review GitHub issues and `dev/TODO.md`

The application successfully bridges legacy PHP development practices with modern standards, providing a robust time tracking solution while maintaining upgradeability and developer experience.