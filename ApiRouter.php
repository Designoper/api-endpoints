<?php

require_once __DIR__ . '/Sanitizer.php';

final class ApiRouter extends Sanitizer
{
    private array $routes = [];
    private readonly string $commonPath;

    public function __construct()
    {
        parent::__construct();

        $this->setCommonPath('/api-endpoints/api');

        $this->setRoute(
            'GET',
            $this->getCommonPath() . '/libros',
            function (): void {
                $libro = new Libro();
                $libro->greatFilter();
            }
        );

        $this->setRoute(
            'POST',
            $this->getCommonPath() . '/libros',
            function (): void {
                $libro = new LibroWrite();
                $libro->createLibro();
            }
        );


        $this->handleRequest();
    }

    private function getCommonPath(): string
    {
        return $this->commonPath;
    }

    private function setCommonPath(string $commonPath): void
    {
        $this->commonPath = $commonPath;
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
        switch ($method) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                foreach ($this->routes as $route) {
                    if ($route['method'] === $method && $route['path'] === $path) {
                        $route['handler']();
                        exit();
                    }
                }

                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Route not found',
                    'requested_path' => $path
                ]);
                break;

            default:
                http_response_code(405);
                header('Content-Type: application/json');
                header('Allow: GET, POST, PUT, DELETE');
        }
    }
}
