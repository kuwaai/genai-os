cd ../..
composer update
php artisan key:generate
php artisan migrate
rm public/storage
php artisan storage:link
npm install
