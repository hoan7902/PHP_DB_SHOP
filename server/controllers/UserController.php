<?php

require_once("./config/RestApi.php");

class UserController extends Controller
{
    private $userModel;
    public function __construct()
    {
        $this->userModel = $this->model("UserModel");
    }

    public function login()
    {
        $restAPI = new RestApi();
        $email = $restAPI->bodyData('email');
        $password = $restAPI->bodyData('password');
        $header = $restAPI->headerData('Authorization');
        $this->response(200, array('message' => 'This is login API', 'email' => $email, 'password' => $password, 'Authorization' => $header));
    }

    public function register()
    {
        $restAPI = new RestApi();
        $email = $restAPI->bodyData('email');
        $password = $restAPI->bodyData('password');
        $name = $restAPI->bodyData('name');

        try {
            $this->userModel->insertUser(['name' => $name, 'email' => $email, 'password' => $password]);
        } catch (Exception $e) {
            $this->response(400, ["Error" => $e->getMessage()]);
        }
    }

    public function getUserById()
    {
        require_once('./config/HandleUri.php');
        $handleUri = new HandleUri();
        $params = $handleUri->sliceUri();
        $this->response(200, array('message' => 'This is API to get a user by Id', 'Id' => $params[2]));
    }

    public function getUsers()
    {
        $this->response(200, ['message' => 'API get all users']);
    }
}
