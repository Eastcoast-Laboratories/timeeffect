TIMEEFFECT
==========

**Modern PHP 8.4 Time Tracking & Project Management System**

TIMEEFFECT is a comprehensive, multi-user time tracking and project management system designed for teams and businesses. Built with modern PHP 8.4 and featuring a responsive web interface, it provides complete solutions for time tracking, customer management, invoicing, and reporting.

## Key Features

### 🕒 **Time Tracking**
- Start/stop time tracking with precise effort logging
- Bulk editing capabilities for multiple time entries
- Project-based time allocation and categorization
- Real-time tracking with automatic calculations

### 👥 **Multi-User Management**
- Role-based access control (admin, user, agent permissions)
- Team and group management with hierarchical permissions
- Individual user settings and preferences
- Secure authentication with brute force protection

### 📊 **Project & Customer Management**
- Complete customer database with contact information
- Project management with contracts and rate definitions
- Customer-specific billing rates and terms
- Project profitability analysis and tracking

### 🧾 **Professional Invoicing**
- Automated PDF invoice generation with custom branding
- Multi-language invoice support (German, English, French)
- Automated payment reminders and follow-up system
- Customizable invoice templates with logo and letterhead

### 📈 **Reporting & Analytics**
- Comprehensive time reports in CSV and PDF formats
- Project profitability and performance analytics
- Team productivity insights and statistics
- Customizable reporting periods and filters

### 🌐 **Modern Interface**
- Responsive design for desktop, tablet, and mobile
- Dark mode support with automatic theme switching
- Multi-language interface (DE, EN, FR)
- Modern CSS with accessibility features

### 🔒 **Enterprise Security**
- Session-based authentication with configurable timeout
- Brute force protection with progressive delays
- Role-based access control for data protection
- Secure password reset and user registration

## Quick Start

### 🐳 **Docker (Recommended)**
```bash
git clone https://github.com/rubo77/timeeffect.git
cd docker
docker-compose up -d
```

Install with https://localhost/install/

Then access at: https://localhost (admin/admin)

### 📋 **Manual Installation**
1. **Requirements**: PHP 8.4+, MySQL 5.7+, Apache/Nginx
2. **Setup**: Copy `.env.example` to `.env` and configure database
3. **Install**: Run web installer at `/install/` or use `migrate.php`
4. **Configure**: Edit `.env` for production settings

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed installation instructions.

## System Requirements

- **PHP**: 8.4+ with extensions: mysqli, gd, mbstring, json
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Browser**: Modern browsers with JavaScript enabled

## Configuration

### Environment Variables (`.env`)
```bash
# Database Configuration
DB_HOST=localhost
DB_NAME=timeeffect_db
DB_USER=timeeffect
DB_PASS=your_secure_password

# Application Settings
APP_ENV=production
APP_DEBUG=false
CURRENCY=EUR
TIMEZONE=Europe/Berlin
```

### PHP Configuration
Ensure `short_open_tag = On` in your `php.ini` for legacy template compatibility.

-----

## Documentation

For developers and system administrators:

- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Comprehensive application architecture and code structure guide
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment instructions
- **[docs/TIMEEFECT Installation Manual.md](docs/TIMEEFECT%20Installation%20Manual.md)** - Complete installation guide
- **[docs/DATABASE_MIGRATIONS.md](docs/DATABASE_MIGRATIONS.md)** - Database migration system documentation

## History of this project
Imported from https://sourceforge.net/projects/timeeffect/ with this script https://gist.github.com/rubo77/8f22193cf940837d000a996c7132dae0
initially 
