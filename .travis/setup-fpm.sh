#!/bin/bash

echo "Setting up FPM ..."

sudo cp -f .travis/php-fpm.conf ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf

echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

sudo cp -f .travis/nginx-vhost.conf /etc/nginx/sites-available/coreshop-vsbridge-test.dev.conf
