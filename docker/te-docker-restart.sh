#!/bin/bash
set -e

echo "[TimeEffect] Checking local nginx and mysql services..."

if systemctl is-active --quiet nginx; then
	echo "[TimeEffect] Stopping local nginx service..."
	sudo service nginx stop
fi

if systemctl is-active --quiet mysql; then
	echo "[TimeEffect] Stopping local mysql service..."
	sudo service mysql stop
fi

if systemctl is-enabled --quiet mysql 2>/dev/null; then
	echo "[TimeEffect] Disabling mysql service autostart..."
	sudo systemctl disable mysql
fi

if systemctl is-enabled --quiet nginx 2>/dev/null; then
	echo "[TimeEffect] Disabling nginx service autostart..."
	sudo systemctl disable nginx
fi

echo "[TimeEffect] Restarting TimeEffect Docker containers (timeeffect-db, timeeffect-app)..."
cd /var/www/timeeffect/docker
sudo docker compose down
sudo docker compose up -d

echo "[TimeEffect] TimeEffect containers are up."
echo "[TimeEffect] Open TimeEffect in your browser: http://localhost/"