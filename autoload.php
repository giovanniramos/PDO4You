<?php

declare(strict_types=1);

/**
 * PDO4You PSR-4 Autoloader (Composer + Standalone Fallback).
 *
 * Uses Composer's autoloader when available, and falls back to a native
 * PSR-4 autoloader if Composer is absent or does not map the PDO4You namespace.
 */
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if (class_exists(\PDO4You\PDO4You::class)) {
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
