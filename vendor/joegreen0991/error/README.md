Error - A simple, stackable, closure based error handler
=====

This library makes it easier to handle exceptions of different types in different ways.

This library automatically converts errors triggered within your code to an instance of `ErrorException` which is thrown.

Installation
------------
Install via composer

```
{
    "require": {
        "joegreen0991/error": "1.*"
    }
}

```

Usage
-----

~~~PHP

$handler = new Error\Handler();

$handler->error(function(HttpRouteNotFoundError $e)
{
    echo "Route does not exist";

})->error(function(PDOException $e)
{
    echo "Database is down!";

})->error(function(ErrorException $e)
{
    echo "A notice level error has occurred";

})->error(function(Exception $e)
{
    echo "Whoops - Something has gone terribly wrong";
});

~~~