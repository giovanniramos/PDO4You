#!/bin/sh
_COMPOSER=$DIRECTORY"composer.phar"
_AUTOLOAD=$DIRECTORY"vendor/autoload.php"

if [ ! -z $DIRECTORY ]
	then
		if [ ! -f $_COMPOSER ]; then
			curl -sS https://getcomposer.org/installer | php -- --install-dir=$DIRECTORY
		fi
		php $_COMPOSER install --dev --working-dir=$DIRECTORY
	else
		if [ ! -f $_COMPOSER ]; then
			curl -sS https://getcomposer.org/installer | php
		fi
		php $_COMPOSER install --dev
fi
echo ""
echo ""
if [ -f $_AUTOLOAD ]; then
	echo "-- The autoloader and all project dependencies have been installed --"
	echo ""
fi