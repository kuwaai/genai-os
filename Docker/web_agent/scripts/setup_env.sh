#!/bin/bash

set -xeu

apt-get update
apt-get install -y software-properties-common ca-certificates curl gnupg
add-apt-repository ppa:ondrej/php

# Node.js 18
mkdir -p /etc/apt/keyrings
curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | \
    gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
cat > /etc/apt/sources.list.d/nodesource.list << EOT
deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_18.x nodistro main
EOT

apt-get update
apt-get install -y nginx nodejs
apt-get install -y php8.1 php8.1-curl php8.1-intl \
                   php8.1-cli php8.1-fpm php8.1-mbstring \
                   php8.1-xml php8.1-zip php8.1-mysql php8.1-pgsql \
                   php8.1-sqlite3 php8.1-redis python3-pip composer