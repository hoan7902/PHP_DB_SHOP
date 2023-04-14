<?php

require_once("./utils/RestApi.php");
require_once("./utils/JWTHelper.php");
require_once("./utils/HandleUri.php");

class RatingsController extends Controller
{
    private $ratingsModel;
    public function __construct()
    {
        $this->ratingsModel = $this->model("RatingsModel");
    }
    public function createRating()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['customer', 'self', 'admin'])) {
            try {
            } catch (Exception $e) {
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
            $userId = getUserId($authHeader);
            $params = HandleUri::sliceUri();
            $productId = $params ? ((int)$params[2] >= 0 ? (int)$params[2] : null) : null;

            print_r($userId);
            print_r($productId);
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }
    public function editRating()
    {
    }
    private function isValidPermission($userId, $productId)
    {
    }
}
