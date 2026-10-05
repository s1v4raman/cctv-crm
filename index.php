<?php

/**
 * Precision IT Systems CRM - Universal HTTP Entrypoint
 * Forwards requests to Laravel's public directory when DocumentRoot points to the project root.
 */

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request via public/index.php
require_once __DIR__.'/public/index.php';
