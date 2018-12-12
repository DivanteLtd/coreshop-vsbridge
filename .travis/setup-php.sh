#!/bin/bash

echo "Setting up PHP..."

phpenv config-add .travis/php.ini

echo "Enabling PHP Redis extension..."
phpenv config-add .travis/php-redis.ini
