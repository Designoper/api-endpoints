<?php

require_once __DIR__ . '/../../../../utils/sanitize.php';
require_once __DIR__ . '/../../../../models/libro/final/Libro.php';

$minimoPaginas = $_GET["paginas"] ?? null;

try {
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$libro = new Libro();
			$libro->minPaginas($minimoPaginas);
			break;

		default:
			http_response_code(405);
			header('Allow: GET');
	}
} catch (Exception $error) {
	// http_response_code(500);
	// header('Content-Type: application/json');
	// echo json_encode(['error' => $error->getMessage()]);
}
