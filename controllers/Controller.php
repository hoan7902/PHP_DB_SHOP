<?php

class Controller
{
    public function model($model)
    {
        require_once "./models/" . $model . ".php";
        return new $model;
    }

    public function response($data)
    {
        $headers = array(
            'Content-Type: application/json',
            'Access-Control-Allow-Origin: *',
            'Access-Control-Allow-Headers: *',
            'Access-Control-Allow-Methods: GET, POST, PUT, DELETE',
            'Access-Control-Allow-Headers: Content-Type, Authorization',
            'Access-Control-Allow-Credentials: *'
        );
        foreach ($headers as $header) {
            header($header);
        }
        echo json_encode($data);
    }
    public function status($code)
    {
        header('HTTP/1.1 ' . $code);
        // http_response_code($code);
    }
}
