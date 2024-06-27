cd ../..
call composer install --no-dev --optimize-autoloader --no-interaction
call php artisan key:generate --force
call php artisan db:seed --class=InitSeeder --force
call php artisan migrate --force
rmdir /Q /S public\storage
call php artisan storage:link
call npm audit fix
call npm install
call npm audit fix
call npm ci --no-audit --no-progress
call php artisan route:cache
call php artisan view:cache
call php artisan optimize
call npm run build
call php artisan config:cache