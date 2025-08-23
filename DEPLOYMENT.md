# TimeEffect Online Deployment Guide

## 🚀 Deployment on online server (PHP 8.4)

### Step 1: Update repository
```bash
# On the online server
cd /path/to/your/timeeffect
git pull origin master
```

### Step 2: Install Composer dependencies
```bash
# Install Composer if not available
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install dependencies
composer install --no-dev --optimize-autoloader
```

### Step 3: Configure environment

#### Option A: Use the installer (for DB setup; does not create `.env`)
```bash
# Run the web installer (database setup and checks)
# Navigate to: https://your-domain.com/install/
# Note: The installer does not write `.env`. Create it manually (see Option B).

# After installer: create/edit your .env
 nano .env
```

#### Option B: Create manually
```bash
# Create .env file
cp .env.example .env

# Edit .env with correct database credentials:
nano .env
```


### Step 4: Check PHP configuration
```bash
# Check PHP version (should be 8.4+)
php -v

# Check required extensions
php -m | grep -E "(mysqli|pdo|json|mbstring)"

# Enable PHP short_open_tag (if needed)
# In php.ini: short_open_tag = On
```

### Step 5: Set permissions
```bash
# During installation: installer needs read access; write access only if generating .env
chmod 755 install

# After installation: lock down .env
chmod 600 .env

# Create logs directory (if not present) and set permissions
mkdir -p logs
chmod 755 logs
chown www-data:www-data logs  # or the corresponding web server user
```

### Step 6: Run the installation
1. Visit: `https://your-domain.com/install/`
2. Follow the installation wizard
3. The database connection should work automatically
4. If you see a "TimeEffect Migration Required" banner, click the link to run migrations (`migrate.php`).

> Note (legacy upgrade): If you are upgrading from a version using `include/config.inc.php`, migrate config to `.env` BEFORE pulling new code.
> ```bash
> git fetch origin
> git restore -s origin/master -- migrate_config_to_env.php
> # Alternative (older Git): git checkout origin/master -- migrate_config_to_env.php
> php migrate_config_to_env.php
> git pull origin master
> ```

## 🔧 Troubleshooting

### Problem: "mysql_* function not found"
**Solution**: All mysql_* functions were migrated to mysqli_*. The fix is already in the repository.

### Problem: "short_open_tag" error
**Solution**:
```bash
# Enable in php.ini:
short_open_tag = On

# Restart Apache/Nginx
systemctl restart apache2  # or nginx
```

### Problem: Composer not found
**Solution**:
```bash
# Install Composer globally
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### Problem: Database connection fails
**Solution**:
1. Check `.env` file
2. Verify database user and permissions
3. MySQL service running: `systemctl status mysql`

## 📋 Deployment checklist

- [ ] Git repository updated (`git pull`)
- [ ] Composer dependencies installed
- [ ] PHP 8.4+ running with mysqli extension
- [ ] `short_open_tag = On` in php.ini
- [ ] `.env` file configured and permissions locked down (`chmod 600 .env`)
- [ ] Installation completed via web interface
- [ ] Database migrations executed (automatic or via migrate.php)
- [ ] Functionality tested

## 🎯 After deployment

1. **Security**: Delete or protect the `/install/` directory
2. **Performance**: Enable OPcache in php.ini
3. **Monitoring**: Check logs in the `/logs/` directory
4. **Backup**: Create regular database backups

## 🆘 Support

If you run into issues:
1. Check PHP error logs
2. Check `/logs/app.log` (if present)
3. Test the database connection separately
4. Check Apache/Nginx error logs

