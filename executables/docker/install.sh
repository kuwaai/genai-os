cd ../..
composer update
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=DefaultSeeder --force
rm public/storage
php artisan storage:link
npm install
