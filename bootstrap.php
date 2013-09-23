<?php

define('XLOADER', '../../../vendor/autoload.php');
define('LOADER', __DIR__ . '/vendor/autoload.php');
define('INSTALL', __DIR__ . '/install.sh');

// Composer autoloading
if (file_exists(LOADER)) {
    require_once LOADER;
} else {
    if (file_exists(XLOADER)) {
        require_once XLOADER;
    } else {
        if (!isset($_GET['install'])) {
            echo '<h1>AUTOLOADER NOT FOUND</h1>';
            echo '<h2>To continue the installation, click <a href="?install">INSTALL</a> and wait, or run <i>install.sh</i> from the command line.</h2>';
            exit;
        } else {
            $output = shell_exec(INSTALL);
            exit("<pre>{$output}</pre>");
        }
    }
}