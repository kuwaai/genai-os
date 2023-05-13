cd ../..
sudo apt install php-pgsql php-xml php php-curl php-redis composer redis
composer update
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DefaultSeeder
rm public/storage
php artisan storage:link
npm install
