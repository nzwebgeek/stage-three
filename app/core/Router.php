<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Router
{
    /**
     * Registered application routes.
     *
     * Each route is stored as:
     *
     * [
     *     'GET' => [
     *         '/login' => [Controller::class, 'method']
     *     ],
     *     'POST' => [
     *         '/login' => [Controller::class, 'method']
     *     ]
     * ]
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function __construct(
        private readonly Container $container
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Register GET Route
    |--------------------------------------------------------------------------
    */

    public function get(
        string $route,
        array $action
    ): void {
        $route = $this->normalizeRoute($route);

        $this->routes['GET'][$route] = $action;
    }

    /*
    |--------------------------------------------------------------------------
    | Register POST Route
    |--------------------------------------------------------------------------
    */

    public function post(
        string $route,
        array $action
    ): void {
        $route = $this->normalizeRoute($route);

        $this->routes['POST'][$route] = $action;
    }

    /*
    |--------------------------------------------------------------------------
    | Dispatch
    |--------------------------------------------------------------------------
    */

    public function dispatch(): void
    {
        $method = strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        $uri = parse_url(
            $requestUri,
            PHP_URL_PATH
        );

        if (!is_string($uri) || $uri === '') {
            $uri = '/';
        }

        $uri = $this->normalizeRoute($uri);

        /*
        |--------------------------------------------------------------------------
        | Exact Route Match
        |--------------------------------------------------------------------------
        */

        $route = $this->routes[$method][$uri] ?? null;

        if ($route !== null) {
            $this->callAction($route);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Dynamic Route Match
        |--------------------------------------------------------------------------
        |
        | Currently supports:
        |
        |     /page/{slug}
        |
        | Example:
        |
        |     /page/about
        |
        | This prevents the router from guessing parameters based
        | on the controller method name.
        |
        */

        foreach ($this->routes[$method] ?? [] as $routePattern => $action) {

            $parameters = $this->matchRoute(
                $routePattern,
                $uri
            );

            if ($parameters !== null) {
                $this->callAction(
                    $action,
                    $parameters
                );

                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 404
        |--------------------------------------------------------------------------
        */

        $errorController = $this->container->get(
            \App\Controllers\ErrorController::class
        );

        $errorController->notFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Call Controller Action
    |--------------------------------------------------------------------------
    */

    private function callAction(
        array $action,
        array $parameters = []
    ): void {

        if (count($action) !== 2) {
            throw new RuntimeException(
                'Invalid route action.'
            );
        }

        [
            $controllerClass,
            $controllerMethod
        ] = $action;

        $controller = $this->container->get(
            $controllerClass
        );

        if (!method_exists(
            $controller,
            $controllerMethod
        )) {
            throw new RuntimeException(
                'Controller method not found: '
                . $controllerClass
                . '::'
                . $controllerMethod
            );
        }

        $controller->$controllerMethod(...$parameters);
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Route Matching
    |--------------------------------------------------------------------------
    */

    private function matchRoute(
        string $routePattern,
        string $uri
    ): ?array {

        /*
         * Convert:
         *
         *     /page/{slug}
         *
         * Into:
         *
         *     #^/page/([^/]+)$#
         */

        $routePattern = $this->normalizeRoute(
            $routePattern
        );

        $segments = explode(
            '/',
            trim($routePattern, '/')
        );

        $parameters = [];

        $regex = [];

        foreach ($segments as $segment) {

            if (
                strlen($segment) >= 2
                && $segment[0] === '{'
                && $segment[strlen($segment) - 1] === '}'
            ) {

                $parameterName = substr(
                    $segment,
                    1,
                    -1
                );

                if ($parameterName === '') {
                    return null;
                }

                $regex[] = '([^/]+)';

                $parameters[] = $parameterName;

                continue;
            }

            $regex[] = preg_quote(
                $segment,
                '#'
            );
        }

        $pattern = '#^/'
            . implode('/', $regex)
            . '$#';

        if (!preg_match(
            $pattern,
            $uri,
            $matches
        )) {
            return null;
        }

        /*
         * Remove the complete URI match.
         */
        array_shift($matches);

        return array_map(
            static fn(string $value): string =>
                rawurldecode($value),
            $matches
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Route
    |--------------------------------------------------------------------------
    */

    private function normalizeRoute(
        string $route
    ): string {

        $route = trim($route);

        if ($route === '') {
            return '/';
        }

        /*
         * Make sure route starts with /.
         */
        if ($route[0] !== '/') {
            $route = '/' . $route;
        }

        /*
         * Remove trailing slash except for root.
         */
        if ($route !== '/') {
            $route = rtrim(
                $route,
                '/'
            );
        }

        return $route;
    }
}
