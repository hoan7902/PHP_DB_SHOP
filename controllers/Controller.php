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
            'Cache-Control: no-cache, must-revalidate'
        );
        foreach ($headers as $header) {
            header($header);
        }
        echo json_encode($data);
    }
    public function status($code)
    {
        header('HTTP/1.1 ' . $code);
    }
}
