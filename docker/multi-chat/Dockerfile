FROM php:8.1-cli as build

# Install nodejs and composer
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - &&\
    apt-get install -y nodejs &&\
    echo Version of Node.js: $(node -v) &&\
    echo Version of NPM: $(npm -v)
RUN curl -s https://getcomposer.org/installer | php &&\
    mv composer.phar /usr/local/bin/composer &&\
    echo Version of Composer: $(composer --version)

# Install dependency of PHP extension for building
RUN apt-get install -y libldap-dev libzip-dev &&\
    docker-php-ext-install ldap zip

# Build the multi-chat application
COPY src/multi-chat /app
WORKDIR /app
## Install dependencies using npm and composer
RUN composer update --no-interaction &&\
    composer install --no-interaction --optimize-autoloader --no-dev &&\
    npm install
## Optimize composer dependency
RUN php artisan clear-compiled &&\
    composer dump-autoload --optimize
## Build the front-end assets
RUN npm run build
## Reconstruct the symbolic link
WORKDIR /app/public
RUN rm -f storage &&\
    ln -s ../storage/app/public storage

# Cleanup
WORKDIR /app
RUN rm -rf node_modules storage &&\
    mkdir -p storage

#============================================
FROM php:8.1-fpm

# Align the UID and PID with Nginx
RUN usermod -u 101 www-data &&\
    groupmod -g 101 www-data

# Configure PHP
COPY docker/multi-chat/php-override.ini ${PHP_INI_DIR}/conf.d/00-override.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "${PHP_INI_DIR}/php.ini"

# Install required PHP extensions
RUN apt-get update &&\
    apt-get install -y libldap-dev libzip-dev libcurl4-openssl-dev libonig-dev \
                       libxml2-dev libpq-dev &&\
    docker-php-ext-install curl mbstring xml zip ldap mysqli pdo_mysql pgsql pdo_pgsql
RUN pecl install redis-6.0.2 &&\
    docker-php-ext-enable redis

# Install tools to check service status
RUN apt-get install -y netcat-openbsd wget &&\
    wget -O /usr/local/bin/php-fpm-healthcheck \
      https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck &&\
    chmod +x /usr/local/bin/php-fpm-healthcheck

COPY --from=build /app /app
COPY docker/multi-chat/app.env /app/.env
COPY docker/multi-chat/add-executor.php /app/add-executor.php

# Copy the scripts
COPY docker/multi-chat/docker-entrypoint docker/multi-chat/multi-chat /usr/local/bin/
WORKDIR /usr/local/bin
RUN chmod +x docker-entrypoint multi-chat

# Setup the entry point
ENV KUWA_MULTI_CHAT_VERSION="0.3.0-beta1-docker1"

ENV KUWA_KERNEL_URL="http://kernel:9000"
ENV APP_URL="http://localhost/"
ENV APP_ENV="production"
ENV APP_DEBUG="false"

ENV DB_CONNECTION="pgsql"
ENV DB_HOST="db"
ENV DB_PORT="5432"

ENV LOGIN_WITH_EMAIL="true"
ENV INFORMATION_URL="https://kuwaai.org/"
ENV ALLOWED_IPS="0.0.0.0/0"
ENV DEFAULT_LOCALE="en_us"
ENV FALLBACK_LOCALE="en_us"

ENV ADMIN_NAME="Kuwa Admin"
ENV ADMIN_EMAIL="admin@localhost"
ENV ADMIN_PASSWORD=""
ENV ADMIN_PASSWORD_FILE=""

WORKDIR /app
ENTRYPOINT ["docker-entrypoint"]
CMD ["multi-chat", "--num-worker", "10"]