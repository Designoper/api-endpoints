<?php

require_once __DIR__ . '/../../../ApiResponse.php';

abstract class LibroValidationErrors extends ApiResponse
{
	protected function __construct()
	{
		parent::__construct();
	}

	protected function validateIdLibro(mixed $idLibro): void
	{
		if (!is_int($idLibro) || $idLibro <= 0) {
			$this->setValidationError('El campo "idLibro" debe ser un número entero superior o igual a 1');
		}
	}

	protected function validateTitulo(mixed $titulo): void
	{
		if (empty($titulo)) {
			$this->setValidationError('El campo "titulo" no puede estar vacío');
		}
	}

	protected function validateDescripcion(mixed $descripcion): void
	{
		if (empty($descripcion)) {
			$this->setValidationError('El campo "descripcion" no puede estar vacío');
		}
	}

	protected function validatePaginas(mixed $paginas): void
	{
		if (filter_var($paginas, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) === false) {
			$this->setValidationError("El campo 'paginas' debe ser un número entero superior o igual a 1");
		}
	}

	protected function validateFechaPublicacion(mixed $fechaPublicacion): void
	{
		$dateTime = DateTime::createFromFormat('Y-m-d', $fechaPublicacion);

		if (!$dateTime || $dateTime->format('Y-m-d') !== $fechaPublicacion) {
			$this->setValidationError('El campo "fechaPublicacion" debe tener el formato yyyy-mm-dd');
		}
	}

	protected function validateIdCategoria(mixed $idCategoria): void
	{
		if (!is_int($idCategoria) || $idCategoria <= 0) {
			$this->setValidationError('El campo "idCategoria" debe ser un número entero superior o igual a 1');
		}
	}

	// protected function validatePortadaNombre(mixed $portadaNombre): void
	// {
	// 	if (empty($portadaNombre)) {
	// 		$this->setValidationError('El nombre de la imagen no puede estar vacío');
	// 	}
	// }
}
