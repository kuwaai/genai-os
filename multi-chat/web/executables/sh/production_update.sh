cd ../..
sudo apt install php-pgsql php-xml php php-curl php-ldap php-redis composer redis
composer update
php artisan key:generate --force
php artisan migrate --force
rm public/storage
php artisan storage:link
npm install
composer dump-autoload --optimize
php artisan route:cache
php artisan view:cache
php artisan optimize
npm run build
php artisan config:cache
php artisan config:clear