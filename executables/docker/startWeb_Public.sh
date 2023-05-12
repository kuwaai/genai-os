cd ../..
composer dump-autoload --optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
npm run build
php artisan serve --host 0.0.0.0 --port=80
