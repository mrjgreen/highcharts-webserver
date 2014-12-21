<?php

use Pimple\Container as Pimple;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Exception\HttpMethodNotAllowedException;

function app($key = null)
{
    static $app;

    isset($app) or $app = new Pimple();

    return $key ? $app[$key] : $app;
}

$app = app();

(new Error\Handler())->error(function(HttpRouteNotFoundException $e)
{
    Response::create("Route not found", 404)->send();
})->error(function(HttpMethodNotAllowedException $e)
{
    Response::create("Method not allowed", 404, array(
        'Allow' => substr($e->getMessage(), 7)
    ))->send();
})->error(function(Exception $e)
{
    Response::create((string)$e, 500)->send();
});

$app['paths'] = array(
    'app'       => __DIR__,
    'config'    => __DIR__ . '/config',
    'public'    => __DIR__ . '/../public',
);

$app['config'] = function(Pimple $app)
{
    return new Config\Repository(new Config\FileLoader($app['paths']['config']));
};

$app['request'] = function()
{
    return Request::createFromGlobals();
};

$app['router'] = new Phroute\RouteCollector();

$app['router']->controller('/', 'Controllers\IndexController');

$response = (new Phroute\Dispatcher($app['router']))
    ->dispatch($app['request']->getMethod(), $app['request']->getPathInfo());

$response instanceof Response or $response = Response::create($response);

$response->send();