<?php
session_start();

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Does not use the App namespace
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Also autoload config if needed
spl_autoload_register(function ($class) {
    $prefix = 'App\\Config\\';
    $base_dir = BASE_PATH . '/config/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Helper for views
function view($name, $data = []) {
    extract($data);
    $path = BASE_PATH . '/views/' . str_replace('.', '/', $name) . '.php';
    if (file_exists($path)) {
        require $path;
    } else {
        echo "View not found: $name";
    }
}

// Router
require BASE_PATH . '/routes/web.php';
