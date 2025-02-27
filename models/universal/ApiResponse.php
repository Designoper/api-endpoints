<?php

require_once __DIR__ . '/MysqliConnect.php';

abstract class ApiResponse extends MysqliConnect
{
    private readonly int $status;
    private readonly string $message;
    private readonly array $content;
    private array $validationErrors = [];
    private array $integrityErrors = [];
    private array $response = [];

    protected function __construct()
    {
        parent::__construct();
    }

    //MARK: GETTERS

    private function getStatus(): int
    {
        return $this->status;
    }

    private function getMessage(): string
    {
        return $this->message;
    }

    private function getContent(): array
    {
        return $this->content;
    }

    private function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    private function getIntegrityErrors(): array
    {
        return $this->integrityErrors;
    }

    //MARK: SETTERS

    protected function setStatus(int $status): void
    {
        $this->status = $status;
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
            $this->setStatus(400);
            $this->setMessage("Hay errores de validación");
            $this->setValidationErrors();
            $this->getResponse();
            exit();
        }
    }

    protected function checkIntegrityErrors(): void
    {
        if (count($this->getIntegrityErrors()) > 0) {
            $this->setMessage("Hay errores de integridad");
            $this->setIntegrityErrors();
            $this->getResponse();
            exit();
        }
    }

    //MARK: FINAL

    protected function getResponse(): void
    {
        http_response_code($this->getStatus());
        header('Content-Type: application/json');
        echo json_encode($this->response);
    }
}
