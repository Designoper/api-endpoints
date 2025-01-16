<?php

class ApiResponse {
    public $data;
    public $status;
    public $headers;

    public function __construct($data, $status = 200, $headers = []) {
        $this->data = $data;
        $this->status = $status;
        $this->headers = array_merge(['Content-Type' => 'application/json'], $headers);
    }
}