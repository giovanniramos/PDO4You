<?php

// Disable XDebug
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}

// Load Composer autoloader.
// Assumes vendor directory is at the project root (one level above src).
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    echo '<h1>Autoloader not found.</h1>';
    echo '<p>Please run <code>composer install</code> at the project root.</p>';
    exit;
}

require_once $autoloadPath;
