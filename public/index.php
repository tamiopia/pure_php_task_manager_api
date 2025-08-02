<?php

// Simple autoloader
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../';
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration and routes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../routes/api.php';