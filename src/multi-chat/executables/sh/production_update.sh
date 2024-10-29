cd ../..
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate
php artisan db:seed --class=InitSeeder --force
php artisan migrate --force
rm public/storage
rm storage/app/public/root/custom
rm storage/app/public/root/database
rm storage/app/public/root/bin
rm storage/app/public/root/bot
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