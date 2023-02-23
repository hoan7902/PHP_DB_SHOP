<?php

class ErrorMiddleware
{
    public function __construct($errorName)
    {
        $this->{$errorName}();
    }
    public function BadRequest()
    {
        header('HTTP/1.1 400');
        exit;
    }
    public function NotFound()
    {
        header('HTTP/1.1 404');
        echo json_encode(array("message" => "API endpoint not found!"));
        exit;
    }
}
