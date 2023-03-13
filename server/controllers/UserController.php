<?php

require_once("./config/RestApi.php");
require_once("./utils/PasswordHelper.php");
require_once("./utils/JWTHelper.php");

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
        if (!$email || !$password) {
            $this->status(400);
            return $this->response(['status' => false, 'error' => "Less data"]);
        }
        try {
            $this->validateEmail($email);
            $this->validatePassword($password);
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(["status" => false, "error" => $e->getMessage()]);
        }
        try {
            $user = $this->userModel->getUserByEmail($email);
            if ($user) {
                if (verifyPassword($password, $user['password'])) {
                    $this->status(200);
                    return $this->response(['status' => true, 'token' => genToken(["userID" => $user['userId'], "role" => $user['role']])]);
                } else {
                    $this->status(400);
                    return $this->response(['status' => false, 'error' => "Login failed!"]);
                }
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'error' => 'Email is not exist!']);
            }
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function register()
    {
        $restAPI = new RestApi();
        $name = $restAPI->bodyData('name');
        $email = $restAPI->bodyData('email');
        $password = $restAPI->bodyData('password');

        if (!$name || !$email || !$password) {
            $this->status(400);
            return $this->response(['status' => false, "error" => "Less data"]);
        }
        try {
            $this->validateEmail($email);
            $this->validateName($name);
            $this->validatePassword($password);
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'error' => $e->getMessage()]);
        }
        $password = hashPassword($password);
        try {

            $this->userModel->insertUser(['name' => $name, 'email' => $email, 'password' => $password]);
            $this->status(201);
            return $this->response(["status" => true]);
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getUserById()
    {
        require_once('./config/HandleUri.php');
        $handleUri = new HandleUri();
        $params = $handleUri->sliceUri();
        $this->status(200);
        $this->response(array('message' => 'This is API to get a user by Id', 'Id' => $params[2]));
    }

    public function getUsers()
    {
        $this->status(200);
        return $this->response(['message' => 'API get all users']);
    }

    private function validateEmail(&$email)
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->status(400);
            throw new Exception("Email is invalid");
        }
        return $email;
    }
    private function validateName(&$name)
    {
        $name = trim($name);
        if (strlen($name) < 6 || strlen($name) > 100) {
            throw new Exception("Length of name must be in range [6, 100]");
        }
        return $name;
    }
    private function validatePassword(&$password, $min = 6, $max = 100)
    {
        $password = trim($password);
        if (strlen($password) < $min || strlen($password) > $max) {
            throw new Exception("Length of password must be in range [$min, $max]");
        }
        return $password;
    }
}
