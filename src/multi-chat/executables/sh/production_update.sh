cd ../..
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate
php artisan db:seed --class=InitSeeder --force
php artisan migrate --force
rm public/storage
php artisan storage:link
npm audit fix
npm install
npm audit fix
npm ci --no-audit --no-progress
php artisan route:cache
php artisan view:cache
php artisan optimize
npm run build
php artisan config:cache