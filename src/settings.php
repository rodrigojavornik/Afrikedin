<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'mysql669.umbler.com',
            'username' => 'afrikedin',
            'password' => "1gu4ld4d3",
            'charset' => '',
            'collation' => 'utf8_unicode_ci',
	        'prefix' => '',
        ],
    ],
];
