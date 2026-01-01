# TimeEffect PHP 8.5 Docker Image
# Based on php:8.5-apache with TimeEffect-specific configurations

FROM php:8.5-apache

# Set maintainer
LABEL maintainer="TimeEffect Team"
LABEL description="TimeEffect PHP 8.5 with Apache and MySQL support"

# Copy and set entrypoint script to fix Apache config at startup
COPY entrypoint-fix.sh /entrypoint-fix.sh

# Enable PHP short tags for legacy compatibility and error logging
RUN printf '%s\n' \
    'short_open_tag = On' \
    'log_errors = On' \
    'error_log = /proc/self/fd/2' \
    'display_errors = On' \
    'display_startup_errors = On' \
    'error_reporting = E_ALL' \
    'date.timezone = Europe/Berlin' \
    > /usr/local/etc/php/conf.d/timeeffect.ini

# Remove default Apache Alias for /icons/ (prevents conflict with project icons)
RUN sed -i 's|^Alias /icons/.*|# Alias /icons/ removed by Dockerfile for TimeEffect|' /etc/apache2/mods-available/alias.conf

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/logs \
    && mkdir -p /var/www/html/install/include \
    && chown -R www-data:www-data /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/install/include \
    && chmod 755 /var/www/html/logs \
    && chmod 755 /var/www/html/install/include

# Install additional packages if needed
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite ssl headers

# Enable PHP extensions required by the application
RUN docker-php-ext-install mysqli pdo_mysql calendar

# Set working directory
WORKDIR /var/www/html

# Expose ports
EXPOSE 80 443

# Set custom entrypoint that fixes Apache config
ENTRYPOINT ["bash", "/entrypoint-fix.sh"]
CMD ["apache2-foreground"]

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1
