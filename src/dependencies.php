<?php

// DIC configuration

use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

// TWIG
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../src/App/Views', [
        'cache' => false
    ]);

    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// eloquent
$container['db'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c->get('settings')['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['userSession'] = function ($c) {
    if (isset($_SESSION['user'])) {
        return unserialize($_SESSION['user']);
    }

    return null;
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $logger = $c['logger'];
        $logger->error("[url: ". $request->getUri() ."] [errorMessage: " . $exception->getMessage() ."]");
        return $response->withRedirect('/erro');
    };
};

$container['notFoundHandler'] = function ($c) {
    return function (Request $request,Response $response) use ($c) {
        $user = $c['userSession'];

        if (is_null($user)) {
            $user = new stdClass();
            $user->id = "Usuário não logado";
        }

        $logger = $c['logger'];
        $logger->error("[url: ". $request->getUri() ."] [userId: $user->id ]");
        
        return $response->withRedirect('/erro');
    };
};