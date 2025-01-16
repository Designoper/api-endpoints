<?php

require_once __DIR__ . '/../utils/sanitize.php';
require_once __DIR__ . '/../models/categoria/CategoriaRead.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

	try {
		$categoria = new CategoriaRead($_GET);
		$categoria->readCategorias();
	} catch (Exception $error) {
		$response = $error->getMessage();
	}
}
