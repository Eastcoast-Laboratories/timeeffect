# TimeEffect Deployment Migration Guide

## Automatic migration for online deployment

**IMPORTANT:** Run the migration BEFORE `git pull` so values from the existing `config.inc.php` are extracted before it is overwritten!

### 1. Before deployment - safeguard values

```bash
# Backup current installation
tar -czf timeeffect-backup-$(date +%Y%m%d).tar.gz /path/to/timeeffect/

# FIRST: Fetch only the migration script from remote (without a full pull)
cd /path/to/timeeffect/
git fetch origin
git restore -s origin/master -- migrate_config_to_env.php
# Alternative for older Git versions:
# git checkout origin/master -- migrate_config_to_env.php

# Then run the migration (BEFORE git pull!)
php migrate_config_to_env.php

# Verify .env was created
ls -la .env
```

### 2. Update code (after migration)

```bash
# Now safely update the Git repository
git pull origin master

# .env is preserved with all original values!
```

The script automatically handles:
- ✅ **Legacy config.inc.php** → Migrates all values to .env
- ✅ **Already migrated** → Checks .env existence
- ✅ **New installation** → Creates .env from .env.example

### 3. What the migration script does

**Automatic detection:**
- Checks whether `config.inc.php` is already an .env reader
- Extracts all values from legacy config
- Creates a complete .env with all settings

**Security:**
- Backup: `config.inc.php.backup-migration-DATE`
- Validation of all required variables
- Secure key generation

**Migration covers:**
- Database configuration
- Application settings  
- Formatting (decimal separator, currency)
- Session configuration
- User registration settings
- Permissions

### 3. After the migration

```bash
# Set .env file permissions
chmod 600 .env

# Install/update Composer dependencies
composer install --no-dev --optimize-autoloader

# Test application
curl -I https://your-domain.com/

# Optional: Remove backup file (after successful test)
# rm include/config.inc.php.backup-migration-*
```

### 5. Troubleshooting

**Problem: .env not found**
```bash
# Create .env from template
cp .env.example .env
# Edit with correct values
nano .env
```

**Problem: Path issue**
```bash
# Check APP_ROOT_PATH in .env
grep APP_ROOT_PATH .env
# Fix if needed
sed -i 's|APP_ROOT_PATH=.*|APP_ROOT_PATH=/correct/path|' .env
```

**Problem: Database connection**
```bash
# Check DB settings in .env
grep ^DB_ .env
# Test connection
php -r "
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable('.');
$dotenv->load();
echo 'DB: ' . $_ENV['DB_HOST'] . '/' . $_ENV['DB_DATABASE'] . PHP_EOL;
"
```

### 6. Rollback (if needed)

```bash
# Restore from backup
cp include/config.inc.php.backup-migration-* include/config.inc.php
rm .env
# Application should work again with old config
```

## Benefits of the migration

- ✅ **Modern standards** - Docker/CI/CD ready
- ✅ **Security** - .env not in Git
- ✅ **Flexibility** - Easy environment configuration  
- ✅ **Compatibility** - All legacy PHP files unchanged
- ✅ **Automatic** - Zero manual configuration needed

## Zero-downtime deployment - correct order

```bash
# 1. FIRST: Fetch only the migration script and run it (without a full pull)
git fetch origin
git restore -s origin/master -- migrate_config_to_env.php
# Alternative (older Git versions):
# git checkout origin/master -- migrate_config_to_env.php
php migrate_config_to_env.php

# 2. Update code (config.inc.php will be overwritten, .env stays!)
git pull origin master

# 3. Update dependencies
composer install --no-dev --optimize-autoloader

# 4. Done - Application runs with all original values in .env
```

## Why this order matters

**Problem with wrong order:**
- `git pull` → Overwrites `config.inc.php` with the new .env reader version
- `migrate_config_to_env.php` → Can no longer find legacy values!
- Result: .env contains only default values instead of production configuration

**Solution with correct order:**
- `migrate_config_to_env.php` → Extracts ALL values from legacy config
- `git pull` → Overwrites `config.inc.php`, but .env remains untouched
- Result: .env contains all original production values

The migration is **backwards compatible** and **automatic** – perfect for production environments!

