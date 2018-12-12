#!/bin/bash

set -e

sudo chmod 0755 /home/travis
sudo chmod 0755 /tmp/www

sudo apt-get update
sudo apt-get install -y nginx
sudo rm -f /etc/nginx/sites-available/*
sudo rm -f /etc/nginx/sites-enabled/*

.travis/setup-fpm.sh

sudo ln -s /etc/nginx/sites-available/coreshop-vsbridge-test.dev.conf /etc/nginx/sites-enabled/coreshop-vsbridge-test.dev.conf

VHOSTCFG=/etc/nginx/sites-available/coreshop-vsbridge-test.dev.conf

sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" -i $VHOSTCFG
sudo sed -e "s?%PIMCORE_ENVIRONMENT%?$PIMCORE_ENVIRONMENT?g" -i $VHOSTCFG
sudo sed -e "s?%PIMCORE_TEST_DB_DSN%?$PIMCORE_TEST_DB_DSN?g" -i $VHOSTCFG
sudo sed -e "s?%PIMCORE_TEST_CACHE_REDIS_DATABASE%?$PIMCORE_TEST_CACHE_REDIS_DATABASE?g" -i $VHOSTCFG

sudo service nginx restart
