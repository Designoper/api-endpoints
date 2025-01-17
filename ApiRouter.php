<?php

final class ApiRouter
{
    private array $routes = [];
    private string $commonPath = '/api-endpoints/api';

    public function __construct()
    {
        $this->setRoute('GET', $this->commonPath . '/libros/paginas/order-desc', function (): void {
            $libro = new Libro();
            $libro->OrdenarPaginasDesc();
        });

        $this->setRoute('GET', $this->commonPath . '/libros/paginas/order-asc', function (): void {
            $libro = new Libro();
            $libro->OrdenarPaginasAsc();
        });

        $this->setRoute('GET', $this->commonPath . '/libros/paginas/max', function (): void {
            $libro = new Libro();
            $maximoPaginas = $_GET["paginas"] ?? null;
            $libro->maxPaginas($maximoPaginas);
        });

        $this->setRoute('GET', $this->commonPath . '/libros/paginas/min', function (): void {
            $libro = new Libro();
            $minimoPaginas = $_GET["paginas"] ?? null;
            $libro->minPaginas($minimoPaginas);
        });

        $this->handleRequest();
    }

    private function setRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    private function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $route['handler']();
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
