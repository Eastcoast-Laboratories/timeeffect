#!/bin/bash
# Fix Apache DocumentRoot at container startup
if [ -f /etc/apache2/sites-enabled/000-default.conf ]; then
    sed -i 's|DocumentRoot /var/www/html/webroot|DocumentRoot /var/www/html|g' /etc/apache2/sites-enabled/000-default.conf
    sed -i 's|<Directory /var/www/html/webroot>|<Directory /var/www/html>|g' /etc/apache2/sites-enabled/000-default.conf
fi

# Execute the container command
exec "$@"
