cd ../..
composer update
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=DefaultSeeder --force
rm public/storage
php artisan storage:link
npm install
composer dump-autoload --optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
npm run build