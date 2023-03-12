<?php

class Controller
{
    public function model($model)
    {
        require_once "./models/" . $model . ".php";
        return new $model;
    }

    public function response($code, $data)
    {
        $headers = array(
            'Content-Type: application/json',
            'Cache-Control: no-cache, must-revalidate'
        );
        foreach ($headers as $header) {
            header($header);
        }
        header('HTTP/1.1 ' . $code);
        echo json_encode($data);
    }
}
