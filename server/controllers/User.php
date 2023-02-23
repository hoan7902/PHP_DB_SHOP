<?php

require_once("./config/RestApi.php");

class User extends Controller
{

    public function Login()
    {
        $restAPI = new RestApi();
        $email = $restAPI->bodyData('email');
        $password = $restAPI->bodyData('password');
        $this->response(200, array('message' => 'This is login API', 'email' => $email, 'password' => $password));
    }

    public function Register()
    {
        $restAPI = new RestApi();
        $email = $restAPI->bodyData('email');
        $password = $restAPI->bodyData('password');
        $this->response(200, array('message' => 'This is register API', 'email' => $email, 'password' => $password));
    }

    public function GetUserById()
    {
        require_once('./config/HandleUri.php');
        $handleUri = new HandleUri();
        $params = $handleUri->SliceUri();
        $this->response(200, array('message' => 'This is API to get a user by Id', 'Id' => $params[2]));
    }
}
