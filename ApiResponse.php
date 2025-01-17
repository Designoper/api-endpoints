<?php

require_once __DIR__ . '/models/universal/MysqliConnect.php';

abstract class ApiResponse extends MysqliConnect
{
    private array $data;
    private int $status;
    private string $message;
    private array $content;
    private array $headers = [];

    protected function __construct()
    {
        parent::__construct();
    }

    private function getData(): array
    {
        return $this->data;
    }

    private function getStatus(): int
    {
        return $this->status;
    }

    private function getHeaders(): array
    {
        return $this->headers;
    }

    protected function setData(array $data): void
    {
        $this->data = $data;
    }

    protected function setStatus(int $status): void
    {
        $this->status = $status;
    }

    protected function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    protected function getResponse(): void
    {
        http_response_code($this->getStatus());
        $this->headers = array_merge(['Content-Type' => 'application/json'], $this->getHeaders());
        echo json_encode($this->getData());
    }
}
