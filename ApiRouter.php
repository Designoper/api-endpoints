<?php

final class ApiRouter
{
    private array $routes = [];
    private string $commonPath = '/api-endpoints/api';
    // private ApiResponse $a;

    public function __construct() {

        $this->addRoute('GET', $this->commonPath . '/libros/paginas/order-desc', function() {
            $libro = new Libro();
            return $libro->OrdenarPaginasDesc();
        });

        $this->addRoute('GET', $this->commonPath . '/libros/paginas/order-asc', function() {
            $libro = new Libro();
            return $libro->OrdenarPaginasAsc();
        });

        $this->addRoute('GET', $this->commonPath . '/libros/paginas/max', function() {
            $libro = new Libro();
            $maximoPaginas = $_GET["paginas"] ?? null;
            return $libro->maxPaginas($maximoPaginas);
        });

        $this->addRoute('GET', $this->commonPath . '/libros/paginas/min', function() {
            $libro = new Libro();
            $minimoPaginas = $_GET["paginas"] ?? null;
            return $libro->minPaginas($minimoPaginas);
        });

        $this->handleRequest();
    }

    private function addRoute(string $method, string $path, callable $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    private function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $response = $route['handler']();

                return;
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
