<?php

use Core\JustArray\JustArray;
use Core\Validator;
use Routes\Request;
use Routes\Router;

require_once __DIR__ . '/../includes/app.php';

$router = new Router();

//Registering routes.
$router->get('/', function(Request $request) {
    view('index', []);
});

/**
 * if you want use a controller instead
 * $router->get(
 *      'route', 
 *      [Controller::class, 'methodToHandleTheAction'],
 *      [Middleware::class, ...](optional)
 * );
 */

//Handling the incoming request.
$router->checkRoutes();