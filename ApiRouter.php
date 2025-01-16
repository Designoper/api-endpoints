<?php

class ApiRouter
{
    private $routes = [];

    public function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                try {
                    $result = $route['handler']();
                    return; // Let the model handle response
                } catch (Exception $e) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => $e->getMessage()]);
                    return;
                }
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Route not found',
            'requested_path' => $path
        ]);
    }
}
