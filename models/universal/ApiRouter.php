<?php

declare(strict_types=1);

require_once __DIR__ . '/Sanitizer.php';
require_once __DIR__ . '/../../models/libro/Libro.php';
require_once __DIR__ . '/../../models/libro/LibroFilter.php';
require_once __DIR__ . '/../../models/libro/LibroWrite.php';
require_once __DIR__ . '/../../models/categoria/Categoria.php';
require_once __DIR__ . '/../../models/usuario/Usuario.php';

final class ApiRouter extends Sanitizer
{
    private const string COMMON_PATH = '/api/';
    private array $routes = [];

    public function __construct()
    {
        parent::__construct();

        $this->setRoute(
            'GET',
            self::COMMON_PATH . 'libros/',
            function (): void {
                $libro = new Libro();
                $libro->readLibros();
            }
        );

        $this->setRoute(
            'GET',
            self::COMMON_PATH . 'libros/filter/',
            function (): void {
                $libro = new LibroFilter();
                $libro->filterLibros();
            }
        );

        $this->setRoute(
            'GET',
            self::COMMON_PATH . 'categorias/',
            function (): void {
                $categoria = new Categoria();
                $categoria->readCategorias();
            }
        );

        $this->setRoute(
            'POST',
            self::COMMON_PATH . 'usuarios/',
            function (): void {
                $usuario = new Usuario();
                $usuario->createUsuario();
            }
        );

        $this->setRoute(
            'POST',
            self::COMMON_PATH . 'libros/create/',
            function (): void {
                // $usuario = new Usuario();
                // $usuario->login();
                $libro = new LibroWrite();
                $libro->createLibro();
            }
        );

        $this->setRoute(
            'POST',
            self::COMMON_PATH . 'libros/update/',
            function (): void {
                // $usuario = new Usuario();
                // $usuario->login();
                $libro = new LibroWrite();
                $libro->updateLibro();
            }
        );

        $this->setRoute(
            'POST',
            self::COMMON_PATH . 'libros/delete/',
            function (): void {
                // $usuario = new Usuario();
                // $usuario->login();
                $libro = new LibroWrite();
                $libro->deleteLibro();
            }
        );

        $this->setRoute(
            'POST',
            self::COMMON_PATH . 'libros/delete-all/',
            function (): void {
                $libro = new LibroWrite();
                $libro->deleteAllLibros();
            }
        );

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
        switch ($method) {
            case 'GET':
            case 'POST':
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
                header('Allow: GET, POST');
        }
    }
}
