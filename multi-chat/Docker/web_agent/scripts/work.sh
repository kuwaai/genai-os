#!/bin/bash

cd ${PHP_APP_PATH}
php artisan queue:work --timeout=0
