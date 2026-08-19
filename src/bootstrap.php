<?php

// Disable XDebug
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}

// Carrega o autoloader do Composer.
// Assume que o vendor está na raiz do projeto (uma pasta acima da pasta src).
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    echo '<h1>Autoloader não encontrado.</h1>';
    echo '<p>Execute <code>composer install</code> na raiz do projeto.</p>';
    exit;
}

require_once $autoloadPath;
