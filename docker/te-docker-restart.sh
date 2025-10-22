#!/bin/bash
# Stop local nginx and mysql services
sudo service nginx stop
sudo service mysql stop
sudo systemctl disable mysql
sudo systemctl disable nginx

# Restart only TimeEffect Docker containers (keeps other Docker services running)
cd /var/www/timeeffect/docker
sudo docker compose restart