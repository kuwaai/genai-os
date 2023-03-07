cd ../..
sudo apt install php-pgsql php-xml php php-curl 
composer update
php artisan key:generate
php artisan migrate
php artisan storage:link
npm install
