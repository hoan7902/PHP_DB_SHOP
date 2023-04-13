<?php

require_once("./utils/RestApi.php");
require_once("./utils/JWTHelper.php");
require_once("./utils/HandleUri.php");
require_once("./models/ProductsModel.php");

class CartsController extends Controller
{
    private $cartsModel;
    public function __construct()
    {
        $this->cartsModel = $this->model('CartsModel');
    }
    public function addToCart()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['customer', 'self', 'admin'])) {
            try {

                $userId = getUserId($authHeader);
                $params = HandleUri::sliceUri();
                $productId = $params ? ($params[2] ? $params[2] : null) : null;
                if ($productId === null) {
                    $this->status(400);
                    throw new Exception('Product ID does not exist');
                }
                if ($this->isValidProduct($productId)) {
                    if (!$this->isValidInCart($userId, $productId)) {
                        $query = $this->cartsModel->addToCart($userId, $productId);
                        if ($query) {
                            if (mysqli_affected_rows($this->cartsModel->getConn()) > 0) {
                                $this->status(201);
                                return $this->response(['status' => true, 'message' => 'Add successfully']);
                            } else {
                                $this->status(400);
                                throw new Exception('Add failed');
                            }
                        }
                    } else {
                        $this->status(400);
                        throw new Exception('Product exists in cart');
                    }
                } else {
                    $this->status(400);
                    throw new Exception('Product does not exist');
                }
            } catch (Exception $e) {
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        } else {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        }
    }
    public function removeFromCart()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['customer', 'self', 'admin'])) {
            try {
                $userId = getUserId($authHeader);
                $params = HandleUri::sliceUri();
                $productId = $params ? ($params[2] ? $params[2] : null) : null;
                if ($productId === null) {
                    $this->status(400);
                    throw new Exception('Product ID does not exist');
                }
                if ($this->isValidProduct($productId)) {
                    if ($this->isValidInCart($userId, $productId)) {
                        $this->cartsModel->removeOneFromCart($userId, $productId);
                        if (mysqli_affected_rows($this->cartsModel->getConn()) == 1) {
                            $this->status(204);
                            return;
                        } else {
                            $this->status(400);
                            throw new Exception('Delete Failed');
                        }
                    } else {
                        $this->status(400);
                        throw new Exception('Product does not exist');
                    }
                } else {
                    $this->status(400);
                    throw new Exception('Product does not exist');
                }
            } catch (Exception $e) {
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        } else {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        }
    }
    public function queryMyCart()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['customer', 'self', 'admin'])) {
            try {
                $userId = getUserId($authHeader);
                $limit = RestApi::getParams('limit');
                $frame = RestApi::getParams('frame');
                $limit = $limit ? ((int)$limit > 0 ? (int)$limit : 12) : 12;
                $frame = $frame ? ((int)$frame > 0 ? (int)$frame : 1) : 1;
                $data = $this->cartsModel->getProductsInCart($userId, $limit, $frame);
                $this->status(200);
                return $this->response(['status' => true, 'data' => $data]);
            } catch (Exception $e) {
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        } else {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        }
    }
    public function isValidProduct($productId)
    {
        try {
            $productsModel = new ProductsModel();
            $product = $productsModel->getById($productId, ['*']);
            if (count($product) == 1) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    public function isValidInCart($userId, $productId)
    {
        try {
            $cartsModel = new CartsModel();
            $cart = $cartsModel->getBy(['userId' => $userId, 'productId' => $productId]);
            if (count($cart) > 0) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
