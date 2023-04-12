<?php

class Controller
{
    public function model($model)
    {
        require_once "./models/" . $model . ".php";
        return new $model;
    }

    public function response($data, $flag = 0, $depth = 512)
    {
        $headers = array(
            'Content-Type: application/json',
        );
        foreach ($headers as $header) {
            header($header);
        }
        echo json_encode($data, $flag, $depth);
    }
    public function status($code)
    {
        // header('HTTP/1.1 ' . $code);
        http_response_code($code);
    }
}
