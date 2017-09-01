# PHP Router

A fast and high scalable HTTP router for PHP.


## Requirements

- PHP >= 5.4


## Install

```
composer require razonyang/php-router
```


## Features

- **Grouping router**
- Friendly to **RESTful API**
- No third-party library dependencies
- High scalable


## Usage

```
use RazonYang\Router\Router;

$router = new Router();

$router->handle('GET', '', 'homepage');

// RESTful API
$router->get('users', 'users list'); // GET "users" matched
$router->post('users', 'create user account'); // POST "users" matched
$router->get('users/<username>', 'users profile'); // GET "users/foo" matched
$router->delete('users/<username>', 'delete user'); // DELETE "users/bar" matched
$router->get('posts/<post_id:\d+>', 'post content'); // GET "posts/1" matched, GET "posts/nan" unmatched

// Grouping
$router->group('admin', function (Router $group) {
    $group->get('', 'admin dashboard');
});

// Dispatch request
$requestMethod = 'GET';
$requestPath = 'users/foo';

// If matched, $route contains handler, params and settings, otherwise $route is null.
// In this case:
// $route = [
//     'handler', // first elements
//     'params', // second elements
//     'settings', // third elements
// ];
$route = $router->dispatch($requestMethod, $requestPath);
```