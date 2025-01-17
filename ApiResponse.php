<?php

require_once __DIR__ . '/models/universal/MysqliConnect.php';

abstract class ApiResponse extends MysqliConnect
{
    private array $data = [];
    private int $status;
    private string $message;
    private array $content = [];

    protected function __construct()
    {
        parent::__construct();
    }

    //MARK: GETTERS

    private function getData(): array
    {
        return $this->data;
    }

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

    //MARK: SETTERS

    protected function setData(array $data): void
    {
        $this->data = $data;
    }

    protected function setStatus(int $status): void
    {
        $this->status = $status;
    }

    protected function setMessage(string $message): void
    {
        $this->message = $message;
		$this->data['message'] = $this->getMessage();
    }

    protected function setContent(array $content): void
    {
		$this->content = $content;
		$this->data['content'] = $this->getContent();
    }

    //MARK: FINAL

    protected function getResponse(): void
    {
        http_response_code($this->getStatus());
		header('Content-Type: application/json');
        echo json_encode($this->getData());
    }
}
