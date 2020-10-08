<?php

ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7 * 12);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7 * 12);
//ini_set('session.save_path', __DIR__ . '/../_sessions');

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

if (!isset($_SESSION['random'])) {
    $_SESSION['random'] = rand(1, 2500);
}

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

$app->getContainer()->get('db');

// Run app
$app->run();
