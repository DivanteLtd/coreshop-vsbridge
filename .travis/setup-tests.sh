#!/bin/bash

set -eu

echo "Starting Install-Script"

git clone https://github.com/pimcore/skeleton.git /tmp/www

rm -r -f ~/build/DivanteLtd/coreshop-vsbridge/.git

mv ~/build/DivanteLtd/coreshop-vsbridge/src/CoreShop2VueStorefrontBundle /tmp/www/src/
mv ~/build/DivanteLtd/coreshop-vsbridge/tests /tmp/www/
mv ~/build/DivanteLtd/coreshop-vsbridge/phpunit.xml.dist /tmp/www/phpunit.xml
mv ~/build/DivanteLtd/coreshop-vsbridge/composer.json /tmp/www/composer.local.json

cd /tmp/www
COMPOSER_DISCARD_CHANGES=true COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --optimize-autoloader

vendor/bin/pimcore-install \
    --ignore-existing-config \
    --admin-username admin \
    --admin-password admin \
    --mysql-username root \
    --mysql-database coreshop_vsbridge_test \
    --mysql-host-socket localhost \
    --mysql-port 3306 \
    --no-debug \
    --no-interaction \

bin/console pimcore:bundle:enable CoreShopCoreBundle -n
bin/console coreshop:install -n
ln -s /tmp/www/src/CoreShop2VueStorefrontBundle ~/build/DivanteLtd/coreshop-vsbridge

mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:enterYourPhrase 4096
openssl rsa -pubout -in config/jwt/private.pem -passin pass:enterYourPhrase -out config/jwt/public.pem

rm -Rf /tmp/www/var/cache/*

bin/console pimcore:bundle:enable CoreShop2VueStorefrontBundle -n > /dev/null 2>&1 || true

cp -f ~/build/DivanteLtd/coreshop-vsbridge/.travis/AppKernel.php.template /tmp/www/app/AppKernel.php
cp -f ~/build/DivanteLtd/coreshop-vsbridge/.travis/config.yml.template /tmp/www/app/config/config.yml

rm -Rf /tmp/www/var/cache/*

bin/console pimcore:bundle:enable CoreShop2VueStorefrontBundle -n
bin/console doctrine:schema:update -f -n

cd ~/build/DivanteLtd/coreshop-vsbridge
