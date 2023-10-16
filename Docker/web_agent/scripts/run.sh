#!/bin/bash

set -xeu

# Install packages
cp /configs/.env ${PHP_APP_PATH}
/scripts/install_app.sh

# Give owner back to www-data
chown -R www-data:www-data ${PHP_APP_PATH}

# Configure Nginx and PHP
cd /etc/nginx/sites-available
cp /configs/nginx.conf taide.conf
cd /etc/nginx/sites-enabled
rm * -rf
ln -s ../sites-available/taide.conf .
service nginx restart
cp /configs/php-www.conf /etc/php/8.1/fpm/pool.d/www.conf
service php8.1-fpm start

# Start a worker
/scripts/work.sh &

# Start the agent program
cd /agent
pip install -r requirements.txt
python3 main.py
