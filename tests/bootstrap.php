<?php

define('APP_ENV', 'test');
define('PIMCORE_ENVIRONMENT', 'test');
define('PIMCORE_PROJECT_ROOT', '.');

include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../vendor/mockery/mockery/library/helpers.php';
include __DIR__ . '/../src/CoreShop2VueStorefrontBundle/Tests/helpers.php';

\Pimcore\Bootstrap::setProjectRoot();
\Pimcore\Bootstrap::boostrap();
