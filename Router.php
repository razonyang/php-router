<?php

namespace RazonYang\Router;

class Router
{
    /**
     * @var array mapping from group prefix to group router.
     */
    private $groups = [];

    /**
     * @var array a set of route.
     */
    private $routes = [];

    /**
     * @var array extra data for extending.
     */
    private $settings = [];

    /**
     * @var int a trick for dispatch and handle a request.
     * @see handle()
     * @see dispatch()
     */
    private $routesNextIndex = 1;

    /**
     * @var array a set of route's patterns.
     */
    private $patterns = [];

    /**
     * @var null|string a combined pattern of all patterns.
     */
    private $combinedPattern;

    /**
     * @var mixed
     * @see handle()
     */
    public $replacePatterns = [
        '/<([^:]+)>/',
        '/<([^:]+):([^>]+)>?/',
    ];

    /**
     * @var mixed
     * @see handle()
     */
    public $replacements = [
        '([^/]+)',
        '($2)',
    ];

    public static $methods = 'GET|DELETE|HEAD|OPTIONS|PATCH|POST|PUT';

    /**
     * Router constructor.
     *
     * @param array $settings
     * @param mixed $replacePatterns
     * @param mixed $replacements
     */
    public function __construct($settings = [], $replacePatterns = null, $replacements = null)
    {
        $this->settings = $settings;
        if ($replacePatterns) {
            $this->replacePatterns = $replacePatterns;
        }
        if ($replacements) {
            $this->replacements = $replacements;
        }
    }

    /**
     * Create a group router.
     *
     * @param string $prefix group router's prefix.
     * @param callable $callback a closure to initialize group router.
     * @param array $settings
     *
     * For example:
     *     $router = new Router();
     *     $router->group('admin', function(Router $group){
     *         group->get('hello', 'Backend Dashboard Panel');
     *     })
     *
     *     It will matches "admin/hello"
     */
    public function group($prefix, callable $callback, array $settings = [])
    {
        $router = new static($settings);
        $router->settings = array_merge_recursive($this->settings, $settings);
        $callback($router);
        $this->groups[$prefix] = $router;
    }

    /**
     * Register a handler for handling the specific request which
     * relevant to the given method and path.
     *
     * @param string|array $method request method
     * Please convert method to uppercase(recommended) or lowercase, since it is case sensitive.
     *
     * Examples:
     *     method              validity
     *     "GET"               valid
     *     "GET|POST"          valid
     *     "GET,POST"          invalid
     *     ['GET', 'POST']     valid
     *
     * @param string $path the regular expression.
     * The first character of path should not be "/".
     * Param pattern should be one of "<name>" and "<name:regex>", in default, it will be converted to "([^/]+)" and "(regex)" respectively.
     * The path will format by @see $replacePatterns and @see $replacements, you can change it in need.
     * @param mixed $handler request handler.
     * @param array $settings extra data for extending.
     *
     * Examples:
     *     path                              matched
     *     "users"                           "users"
     *     "users/<id:\d+>"                  "users/123"
     *     "users/<id:\d+>/posts"            "users/123/posts"
     *     "users/<id:\d+>/posts/<post>"     "users/123/posts/456", "users/123/posts/post-title"
     */
    public function handle($method, $path, $handler, array $settings = [])
    {
        if (is_array($method)) {
            $method = implode('|', $method);
        }

        // format path to regular expression.
        $pattern = preg_replace($this->replacePatterns, $this->replacements, $path);
        // collect param's name.
        preg_match_all('/<([^:]+)(:[^>]+)?>/', $path, $matches);
        $params = empty($matches[1]) ? [] : $matches[1];
        $this->patterns[$this->routesNextIndex] = "({$method})\\ {$pattern}";
        $this->routes[$this->routesNextIndex] = [$handler, $params, $settings];
        // calculate the next index of routes.
        $this->routesNextIndex += count($params) + 1;
        // set combinedPattern as null when routes has changed.
        $this->combinedPattern = null;
    }

    /**
     * A shortcut for registering a handler to handle GET request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function get($path, $handler, $setting = [])
    {
        $this->handle("GET", $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle DELETE request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function delete($path, $handler, $setting = [])
    {
        $this->handle("DELETE", $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle HEAD request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function head($path, $handler, $setting = [])
    {
        $this->handle("HEAD", $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle OPTIONS request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function options($path, $handler, $setting = [])
    {
        $this->handle("OPTIONS", $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle PATCH request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function patch($path, $handler, $setting = [])
    {
        $this->handle("PATCH", $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle POST request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function post($path, $handler, $setting = [])
    {
        $this->handle("POST", $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle PUT request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function put($path, $handler, $setting = [])
    {
        $this->handle("PUT", $path, $handler, $setting);
    }

    /**
     * A shortcut for registering a handler to handle any methods request.
     *
     * @see handle()
     *
     * @param $path
     * @param $handler
     * @param array $setting
     */
    public function any($path, $handler, $setting = [])
    {
        $this->handle($this->methods, $path, $handler, $setting);
    }

    /**
     * @param string $method request method
     * @param string $path request URL without the query string. It's first character should not be "/".
     * @return mixed|null if matched, returns a route which contains handler, params and settings,
     * otherwise null will be returned.
     *
     * For example:
     * $route = [
     *     'handler', // first elements
     *     'params', // second elements
     *     'settings', // third elements
     * ];
     */
    public function dispatch($method, $path)
    {
        // look for group router via the prefix.
        if ($this->groups) {
            if (false === $pos = strpos($path, '/')) {
                $prefix = $path;
            } else {
                $prefix = substr($path, 0, $pos);
            }
            if (isset($this->groups[$prefix])) {
                // dispatch recursive.
                $group = $this->groups[$prefix];
                $path = ($pos === false) ? '' : substr($path, $pos + 1);
                return $group->dispatch($method, $path);
            }
        }

        return $this->dispatchInternal($method, $path, $this->settings);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $settings router's setting.
     * @return mixed|null
     * @throws \Exception throws an exception if no routes.
     */
    private function dispatchInternal($method, $path, $settings)
    {
        if ($this->combinedPattern === null) {
            if (empty($this->patterns)) {
                throw new \Exception('no routes.');
            }
            $this->combinedPattern = "~^(?:" . implode("|", $this->patterns) . ")$~x";
        }

        $path = "{$method} {$path}";
        if (preg_match($this->combinedPattern, $path, $matches)) {
            for ($i = 1; $i < count($matches) && $matches[$i] === ''; ++$i) ;
            $route = $this->routes[$i];
            $params = [];
            foreach ($route[1] as $param) {
                $params[$param] = $matches[++$i];
            }
            $route[1] = $params;
            $route[2] = array_merge_recursive($settings, $route[2]);
            return $route;
        }


        return null;
    }
}