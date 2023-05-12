cd ../..
composer update
php artisan key:generate --force
php artisan migrate --force
rm public/storage
php artisan storage:link
npm install
