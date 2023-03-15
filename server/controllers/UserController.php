<?php

use function PHPSTORM_META\type;

require_once("./utils/RestApi.php");
require_once("./utils/PasswordHelper.php");
require_once("./utils/JWTHelper.php");
require_once("./utils/HandleUri.php");

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
                    return $this->response(['status' => true, 'token' => genToken(["userId" => $user['userId'], "role" => $user['role']])]);
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
        $handleUri = new HandleUri();
        $params = $handleUri->sliceUri();
        $restAPI = new RestApi();
        $authHeader = $restAPI->headerData('Authorization');
        $role = authHeader($authHeader, $params[2]);
        if ($role == 'admin' || $role == "self") {
            $user = $this->userModel->getUserById($params[2]);
            if ($user) {
                $this->status(200);
                $data = array("userId" => $user['userId'], "name" => $user["name"], "phone" => $user['phone'], "sex" => $user['sex'], "email" => $user['email'], "avatar" => $user['avatar'], "address" => $user['address']);
                return $this->response(["status" => true, "user" => $data]);
            } else {
                $this->status(404);
                return $this->response(["status" => false, 'error' => "User is not valid"]);
            }
        } else if ($role == 'Not Authorization') {
            $this->status(401);
            return $this->response(["status" => false, 'error' => "Not Authorization"]);
        } else {
            $this->status(403);
            return $this->response(["status" => false, 'error' => "Not Authentication"]);
        }
    }

    public function getUsers()
    {
        $params = HandleUri::sliceUri();
        $frame = $params[2] ? (int)$params[2] : 1;
        $restAPI = new RestApi();
        $authHeader = $restAPI->headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $users = $this->userModel->getNRecords(['userId', 'name', 'phone', 'sex', 'email', 'avatar', 'address', 'role'], [], ['userId'], $frame);
            $this->status(200);
            return $this->response(["status" => true, "users" => $users]);
        } else if ($role == 'Not Authorization') {
            $this->status(401);
            return $this->response(["status" => false, 'error' => "Not Authorization"]);
        } else {
            $this->status(403);
            return $this->response(["status" => false, 'error' => "Not Authentication"]);
        }
    }

    public function updateProfile()
    {
        $authHeader = RestApi::headerData('Authorization');
        $token = getTokenFromAuthHeader($authHeader);
        try {
            $payload = decodeToken($token);
            $userId = $payload['userId'];
        } catch (Exception $e) {
            $this->status(401);
            return $this->response(['status' => false, 'error' => 'Not Authentication']);
        }
        $role = authHeader($authHeader, $userId);
        if ($role == 'admin' || $role == 'self') {
            $name = RestApi::bodyData('name');
            $phone = RestApi::bodyData('phone');
            $sex = RestApi::bodyData('sex');
            $address = RestApi::bodyData('address');
            $avatar = RestApi::bodyData('avatar');
            if ($name) {
                try {
                    $this->validateName($name);
                } catch (Exception $e) {
                    $this->status(400);
                    return $this->response(['status' => false, 'error' => $e->getMessage()]);
                }
            }
            if ($phone) {
                if (!$this->validatePhone($phone)) {
                    $this->status(400);
                    return $this->response(['status' => false, 'error' => 'Phone number is wrong']);
                }
            }
            if ($sex != null) {
                if ((int)$sex == 0) {
                    $sex = "male";
                } else if ((int)$sex == 1) {
                    $sex = "female";
                } else {
                    $this->status(400);
                    return $this->response(['status' => false, 'error' => 'Sex is wrong']);
                }
            }
            if ($address) {
                $address = trim($address);
                if ($address == '') $address = null;
            }
            if ($avatar) {
                $avatar = trim($avatar);
                if ($avatar == '') $avatar = null;
            }
            $data = ['name' => $name, 'phone' => $phone, 'address' => $address, 'sex' => $sex, 'avatar' => $avatar];
            $data = array_filter($data, function ($value) {
                return !is_null($value) || !$value;
            });
            if ($this->userModel->updateOne(['userId' => $userId], $data)) {
                $this->status(200);
                return $this->response(['status' => true, 'message' => 'Update successful']);
            } else {
                $this->status(500);
                return $this->response(['status' => false, 'message' => 'Update failed']);
            }
        } else if ($role == 'Not Authentication') {
            $this->status(401);
            return $this->response(['status' => false, 'error' => 'Not Authorization']);
        } else {
            $this->status(403);
            return $this->response(['status' => false, 'error' => 'Not Authentication']);
        }
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

    private function validatePhone(&$phone)
    {
        $phone = trim($phone);
        if (preg_match('/^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/', $phone)) return true;
        return false;
    }
}
