<?php

// Disable XDebug
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}

define('DEFAULT_LOADER', '../../../vendor/autoload.php');
define('COMPOSER_LOADER', __DIR__ . '/../vendor/autoload.php');
define('INSTALL', '' . __DIR__ . '/../install.sh');
putenv('DIRECTORY='. __DIR__ . '/../');

// AUTOLOADER
if (file_exists(COMPOSER_LOADER)) {
    $loader = require COMPOSER_LOADER;
} else {
    if (!file_exists(DEFAULT_LOADER)) {
        if (!isset($_GET['install'])) {
            echo '<h1>AUTOLOADER NOT FOUND</h1>';
            echo '<h2>To continue the installation, click <a href="?install">INSTALL</a> and wait, or run <i>install.sh</i> from the command line.</h2>';
            exit;
        } else {
            $output = shell_exec(INSTALL);
            exit("<pre>{$output}</pre>");
        }
    } else {
        $loader = require_once DEFAULT_LOADER;
    }
}