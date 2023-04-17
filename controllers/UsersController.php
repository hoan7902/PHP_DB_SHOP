<?php

require_once("./utils/RestApi.php");
require_once("./utils/PasswordHelper.php");
require_once("./utils/JWTHelper.php");
require_once("./utils/HandleUri.php");
require_once("./utils/FirebaseImageUploader.php");

class UsersController extends Controller
{
    private $usersModel;
    public function __construct()
    {
        $this->usersModel = $this->model("UsersModel");
    }

    public function login()
    {
        $restAPI = new RestApi();
        $email = $restAPI->bodyData('email');
        $password = $restAPI->bodyData('password');
        if (!$email || !$password) {
            $this->status(400);
            return $this->response(['status' => false, 'message' => "Missing data"]);
        }
        try {
            $this->validateEmail($email);
            $this->validatePassword($password);
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(["status" => false, "message" => $e->getMessage()]);
        }
        try {
            $user = $this->usersModel->getUserByEmail($email);
            if ($user) {
                if (verifyPassword($password, $user['password'])) {
                    $this->status(200);
                    return $this->response(['status' => true, 'token' => genToken(["userId" => $user['userId'], "role" => $user['role']]), 'data' => ['userId' => $user['userId'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'], 'avatar' => $user['avatar']]]);
                } else {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => "Login failed!"]);
                }
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Email is not exist!']);
            }
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'message' => $e->getMessage()]);
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
            return $this->response(['status' => false, "message" => "Missing data"]);
        }
        try {
            $this->validateEmail($email);
            $this->validateName($name);
            $this->validatePassword($password);
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'message' => $e->getMessage()]);
        }
        $password = hashPassword($password);
        try {

            $this->usersModel->insertUser(['name' => $name, 'email' => $email, 'password' => $password]);
            $user = $this->usersModel->getUserByEmail($email);
            $this->status(201);
            return $this->response(['status' => true, 'token' => genToken(["userId" => $user['userId'], "role" => $user['role']]), 'data' => ['name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'], 'avatar' => $user['avatar']]]);
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'message' => $e->getMessage()]);
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
            $user = $this->usersModel->getUserById($params[2]);
            if ($user) {
                $this->status(200);
                $data = array("userId" => $user['userId'], "name" => $user["name"], "phone" => $user['phone'], "sex" => $user['sex'], "email" => $user['email'], "avatar" => $user['avatar'], "address" => $user['address']);
                return $this->response(["status" => true, "user" => $data]);
            } else {
                $this->status(404);
                return $this->response(["status" => false, 'message' => "User is not valid"]);
            }
        } else if ($role == 'Not Authorization') {
            $this->status(401);
            return $this->response(["status" => false, 'message' => "Not Authenticated"]);
        } else {
            $this->status(403);
            return $this->response(["status" => false, 'message' => "Not Authorized"]);
        }
    }

    public function getUsers()
    {
        $limit = RestApi::getParams('limit');
        $limit = $limit ? ((int)$limit > 0 ? (int)$limit : 24) : 24;
        $frame = RestApi::getParams('frame');
        $frame = $frame ? ((int)$frame > 0 ? (int)$frame : 1) : 1;
        $orderBy = RestApi::getParams('order_by');
        if ($orderBy == 'desc') {
            $orderBy = 'DESC';
        } else {
            $orderBy = 'ASC';
        }
        $restAPI = new RestApi();
        $authHeader = $restAPI->headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $users = $this->usersModel->getNRecords(['userId', 'name', 'phone', 'sex', 'email', 'avatar', 'address', 'role'], [], ['userId'], $frame, $limit, $orderBy);
            $this->status(200);
            return $this->response(["status" => true, "users" => $users]);
        } else if ($role == 'Not Authenticated') {
            $this->status(401);
            return $this->response(["status" => false, 'message' => "Not Authenticated"]);
        } else {
            $this->status(403);
            return $this->response(["status" => false, 'message' => "Not Authorized"]);
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
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
        $role = authHeader($authHeader, $userId);
        if ($role == 'admin' || $role == 'self') {
            $name = RestApi::bodyData('name');
            $phone = RestApi::bodyData('phone');
            $sex = RestApi::bodyData('sex');
            $address = RestApi::bodyData('address');
            // $avatar = RestApi::bodyData('avatar');
            $data = [];
            if ($name != null) {
                try {
                    $this->validateName($name);
                    $data['name'] = $name;
                } catch (Exception $e) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => $e->getMessage()]);
                }
            }
            if ($phone != null) {
                if (!$this->validatePhone($phone)) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Phone number is wrong']);
                }
                $data['phone'] = $phone;
            }
            if ($sex != null) {
                if ((int)$sex == 1) {
                    $sex = "male";
                    $data['sex'] = $sex;
                } else if ((int)$sex == 2) {
                    $sex = "female";
                    $data['sex'] = $sex;
                } else {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Sex is wrong']);
                }
            }
            if ($address != null) {
                $address = trim($address);
                if ($address == '') {
                    $address = null;
                } else {
                    $data['address'] = $address;
                }
            }
            // if ($avatar != null) {
            //     $avatar = trim($avatar);
            //     if ($avatar == '') {
            //         $avatar = null;
            //     } else {
            //         $data['avatar'] = $avatar;
            //     }
            // }
            if (!$name && !$phone && !$sex && !$address /* && !$avatar */) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Nothing changes']);
            }
            try {
                if ($this->usersModel->updateOne(['userId' => $userId], $data)) {
                    $this->status(200);
                    return $this->response(['status' => true, 'message' => 'Update successfully']);
                } else {
                    $this->status(200);
                    return $this->response(['status' => true, 'message' => 'Nothing changes']);
                }
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if ($role == 'Not Authenticated') {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        } else {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        }
    }

    public function updateAvatar()
    {
        $authHeader = RestApi::headerData("Authorization");
        $token = getTokenFromAuthHeader($authHeader);
        try {
            $payload = decodeToken($token);
            $userId = $payload['userId'];
        } catch (Exception $e) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
        $role = authHeader($authHeader, $userId);
        if ($role == 'admin' || $role == 'self') {
            try {
                $avatar = RestApi::fileData('avatar');
                $imageUrl = FirebaseStorageUploader::uploadImage($avatar);
                if ($avatar == null) {
                    $this->status(200);
                    return $this->response(['status' => false, 'message' => 'Nothing Changes']);
                }
                if ($this->usersModel->updateOne(['userId' => $userId], ['avatar' => $imageUrl])) {
                    $this->status(200);
                    return $this->response(['status' => true, 'message' => 'Update successfully']);
                } else {
                    $this->status(400);
                    return $this->response(['status' => true, 'message' => 'Update failed']);
                }
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if ($role == 'Not Authenticated') {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        } else {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
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
        if (strlen($name) < 1 || strlen($name) > 100) {
            throw new Exception("Length of name must be in range [1, 100]");
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
