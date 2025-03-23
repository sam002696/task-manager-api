#!/bin/bash

# Here it needs wait for MySQL by retrying migration
until php artisan migrate --force; do
    echo "Waiting for DB to be ready..."
    sleep 3
done

exec php-fpm
