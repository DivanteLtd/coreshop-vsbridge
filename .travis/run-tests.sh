#!/bin/bash

set -e

cd /tmp/www
vendor/bin/phpunit -c phpunit.xml
