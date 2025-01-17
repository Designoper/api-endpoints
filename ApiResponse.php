<?php

final class ApiResponse {
    public array $data;
    public int $status;
    public array $headers;

    public function __construct(array $data, int $status = 200, array $headers = []) {
        $this->data = $data;
        $this->status = $status;
        $this->headers = array_merge(['Content-Type' => 'application/json'], $headers);
    }
}