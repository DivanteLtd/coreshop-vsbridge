#!/bin/bash

if [[ "$TRAVIS_SUDO" == "true" ]]
then
    echo "Setting up environment for functional tests (install webserver)"
    .travis/setup-sudo.sh
fi
