<?php

require_once __DIR__ . '/../utils/sanitize.php';
require_once __DIR__ . '/../models/libro/final/LibroRead.php';
require_once __DIR__ . '/../models/libro/final/LibroWrite.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? [];

foreach ($data as $key => $value) {
	$data[$key] = sanitizeInput($value);
}

$image = $_FILES["image"] ?? null;

try {
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$libro = new LibroRead($_GET);
			empty($_GET) ? $libro->readLibros() : $libro->filterLibros();
			break;

		case 'POST':
			$libro = new LibroWrite(
				titulo: $_POST["titulo"],
				descripcion: $_POST["descripcion"],
				paginas: $_POST["paginas"],
				fechaPublicacion: $_POST["fechaPublicacion"],
				idCategoria: $_POST["idCategoria"],
				portada: $image
			);
			$libro->createLibro();
			break;

		case 'PUT':
			$libro = new LibroWrite(
				idLibro: $_POST["idLibro"],
				titulo: $_POST["titulo"],
				descripcion: $_POST["descripcion"],
				paginas: $_POST["paginas"],
				fechaPublicacion: $_POST["fechaPublicacion"],
				idCategoria: $_POST["idCategoria"]
			);
			$libro->updateLibro();
			break;

		case 'DELETE':
			$libro = new LibroWrite(
				idLibro: $data["idLibro"]
			);
			$libro->deleteLibro();
			break;

		default:
			http_response_code(405);
			header('Allow: GET, POST, PUT, DELETE');
	}
} catch (Exception $error) {
	// http_response_code(500);
	// header('Content-Type: application/json');
	// echo json_encode(['error' => $error->getMessage()]);
}
