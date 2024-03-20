cd ../..
call composer update
call php artisan key:generate --force
call php artisan migrate --force
call rmdir public\storage
call php artisan storage:link
call npm install
call composer dump-autoload --optimize
call php artisan route:cache
call php artisan view:cache
call php artisan optimize
call npm run build
call php artisan config:cache
call php artisan config:clear
