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
    static public function formData($key = null)
    {
        if (!$key) {
            return $_POST;
        } else {
            return isset($_POST[$key]) ? $_POST[$key] : null;
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
    static public function getParams($key = null)
    {
        if (!$key) {
            return $_GET;
        } else {
            return isset($_GET[$key]) ? $_GET[$key] : null;
        }
    }
    static public function fileData($key = null)
    {
        if (!$key) {
            return $_FILES;
        } else {
            return isset($_FILES[$key]) ? $_FILES[$key] : null;
        }
    }
}
