#!/bin/sh
if [ ! -f "composer.phar" ]; then
	curl -sS https://getcomposer.org/installer | php
fi
php composer.phar install --dev
echo ""
echo ""
if [ -f "vendor/autoload.php" ]; then
	echo "The autoloader and all project dependencies have been installed by the plugin composer."
	echo ""
fi
echo -n "PRESS [F5] TO RE-RUN"
echo ""