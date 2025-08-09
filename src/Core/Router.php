<?php

namespace App\Core;

final class Router
{
    private $routes = [];

    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, $action): void
    {
        // Clean and store route with action
        $this->routes[$method][$this->clean($uri)] = $action;
    }

    public function dispatch(): void
    {
        $uri = $this->clean($_GET['url'] ?? '/');
        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$method])) {
            http_response_code(405);
            echo "Method Not Allowed";
            return;
        }

        foreach ($this->routes[$method] as $route => $action) {
            // Convert route pattern /test/{id} to regex
            $pattern = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match

                if (is_callable($action)) {
                    call_user_func_array($action, $matches);
                    return;
                }

                if (is_array($action)) {
                    [$controller, $methodName] = $action;
                    $controllerInstance = new $controller();
                    call_user_func_array([$controllerInstance, $methodName], $matches);
                    return;
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    private function clean(string $uri): string
    {
        return '/' . trim($uri, '/');
    }
}
