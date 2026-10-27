<?php

declare(strict_types=1);

/**
 * PDO4You PSR-4 Autoloader (Composer + Standalone Fallback).
 *
 * Uses Composer's autoloader when available, or falls back to a native
 * PSR-4 autoloader so PDO4You works out-of-the-box without Composer.
 */
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    return;
}

spl_autoload_register(static function (string $class): void {
    $namespaces = [
        'PDO4You\\Tests\\' => __DIR__ . '/tests/',
        'PDO4You\\'        => __DIR__ . '/src/',
    ];

    foreach ($namespaces as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});