<?php

use \Slim\Http\Request;
use \Slim\Http\Response;

$auth = function (Request $request, Response $response, $next) {
    if (isset($_SESSION['user']) && (unserialize($_SESSION['user']))->linkedin_token_expires_in > (new DateTime('now'))) {
        $response = $next($request, $response);
    } else {
        $response = $response->withRedirect('/logout');
    }
    return $response;
};