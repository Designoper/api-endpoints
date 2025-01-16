<?php

require_once __DIR__ . '/ApiRouter.php';
require_once __DIR__ . '/models/libro/final/Libro.php';

$api = new ApiRouter();

// Define all routes in one place
$api->addRoute('GET', '/api-endpoints/api/libros/paginas/order-desc', function() {
    $libro = new Libro();
    return $libro->OrdenarPaginasDesc();
});

$api->addRoute('GET', '/api-endpoints/api/libros/paginas/order-asc', function() {
    $libro = new Libro();
    return $libro->OrdenarPaginasAsc();
});

$api->addRoute('GET', '/api-endpoints/api/libros/paginas/max', function() {
    $libro = new Libro();
    $maximoPaginas = $_GET["paginas"] ?? null;
    return $libro->maxPaginas($maximoPaginas);
});

$api->addRoute('GET', '/api-endpoints/api/libros/paginas/min', function() {
    $libro = new Libro();
    $minimoPaginas = $_GET["paginas"] ?? null;
    return $libro->minPaginas($minimoPaginas);
});

// Handle the request
$api->handleRequest();