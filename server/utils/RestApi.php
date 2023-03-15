<?php

class RestApi
{
    static public function bodyData($key = null)
    {
        $requestBody = file_get_contents('php://input');
        if (!$key) {
            return json_decode($requestBody, true);
        } else {
            return isset(json_decode($requestBody, true)[$key]) ? json_decode($requestBody, true)[$key] : null;
        }
    }
    static public function headerData($key = null)
    {
        $headers = getallheaders();
        if (!$key) {
            return $headers;
        } else {
            return isset($headers[$key]) ? $headers[$key] : null;
        }
    }
}
