apt-get update
apt install -y nginx curl apt-utils
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs software-properties-common
add-apt-repository ppa:ondrej/php
apt update
apt install -y php8.1 php8.1-curl php8.1-intl php8.1-cli php8.1-fpm php8.1-mbstring php8.1-xml php8.1-zip php8.1-mysql php8.1-pgsql php8.1-sqlite3 php8.1-redis python3-pip composer screen
