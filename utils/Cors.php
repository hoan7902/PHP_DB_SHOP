<?php

class Cors
{
    private $allowedOrigins = [];
    private $allowedMethods = [];
    private $allowedHeaders = [];

    public function __construct(array $allowedOrigins, array $allowedMethods, array $allowedHeaders)
    {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
    }

    public function handlePreflight()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $this->allowedOrigins)) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                header('Access-Control-Allow-Methods: ' . implode(',', $this->allowedMethods));
                header('Access-Control-Allow-Headers: ' . implode(',', $this->allowedHeaders));
                header('Access-Control-Max-Age: 86400');
            }
            exit;
        }
    }

    public function handleRequest()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $this->allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Methods: ' . implode(',', $this->allowedMethods));
            header('Access-Control-Allow-Headers: ' . implode(',', $this->allowedHeaders));
        }
    }
}
