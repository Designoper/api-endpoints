<?php

require_once __DIR__ . '/MysqliConnect.php';

abstract class Response extends MysqliConnect
{
	private int $statusCode;
	private string $message;
	private array $content = [];
	private array $newContent = [];
	private array $updatedContent = [];
	private array $validationErrors = [];
	private array $integrityErrors = [];
	private array $response = [];

	protected function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getStatusCode(): int
	{
		return $this->statusCode;
	}

	private function getMessage(): string
	{
		return $this->message;
	}

	private function getContent(): array
	{
		return $this->content;
	}

	private function getNewContent(): array
	{
		return $this->newContent;
	}

	private function getUpdatedContent(): array
	{
		return $this->updatedContent;
	}

	private function getValidationErrors(): array
	{
		return $this->validationErrors;
	}

	private function getIntegrityErrors(): array
	{
		return $this->integrityErrors;
	}

	protected function getResponse(): void
	{
		http_response_code($this->getStatusCode());
		header('Content-Type: application/json');
		echo json_encode($this->response);
	}

	//MARK: SETTERS

	protected function setStatusCode(int $statusCode): void
	{
		$this->statusCode = $statusCode;
	}

	protected function setMessage(string $message): void
	{
		$this->message = $message;
		$this->response['message'] = $this->getMessage();
	}

	protected function setContent(array $content): void
	{
		$this->content = $content;
		$this->response['content'] = $this->getContent();
	}

	protected function setNewContent(array $newContent): void
	{
		$this->newContent = $newContent;
		$this->response['newContent'] = $this->getNewContent();
	}

	protected function setUpdatedContent(array $updatedContent): void
	{
		$this->updatedContent = $updatedContent;
		$this->response['updatedContent'] = $this->getUpdatedContent();
	}








	protected function setValidationError(string $validationError): void
	{
		$this->validationErrors[] = $validationError;
	}

	private function setValidationErrors(): void
	{
		$this->response['validationErrors'] = $this->getValidationErrors();
	}

	protected function setIntegrityError(string $integrityError): void
	{
		$this->integrityErrors[] = $integrityError;
	}

	private function setIntegrityErrors(): void
	{
		$this->response['integrityErrors'] = $this->getIntegrityErrors();
	}

	protected function checkValidationErrors(): void
	{
		if (count($this->getValidationErrors()) > 0) {
			$this->setStatusCode(400);
			$this->setMessage("Hay errores de validación");
			$this->setValidationErrors();
			$this->getResponse();
			throw new Exception();
		}
	}

	protected function checkIntegrityErrors(): void
	{
		if (count($this->getIntegrityErrors()) > 0) {
			$this->setMessage("Hay errores de integridad");
			$this->setIntegrityErrors();
			$this->getResponse();
			throw new Exception();
		}
	}
}
