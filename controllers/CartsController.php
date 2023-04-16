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
                $size = RestApi::bodyData('size');
                if ($size == null) {
                    $this->status(400);
                    throw new Exception('Missing size');
                }
                $quantity = RestApi::bodyData('quantity');
                $quantity = $quantity ? ((int)$quantity > 0 ? (int)$quantity : 1) : 1;
                if ($productId === null) {
                    $this->status(400);
                    throw new Exception('Product ID does not exist');
                }
                $checkProduct = $this->isValidProduct($productId, $size);
                if (count($checkProduct) == 1) {
                    if ($this->isValidInCart($userId, $productId, $size)) {
                        // Update cart
                        $updated = $this->cartsModel->updateProductInCart($userId, $productId, $size, $quantity);
                        if ($updated > 0) {
                            $this->status(200);
                            return $this->response(['status' => true, 'data' => ['name' => $checkProduct[0]['name'], 'size' => $checkProduct[0]['sizeName'], 'unitPrice' => (int)$checkProduct[0]['price'], 'quantity' => $quantity, 'price' => $checkProduct[0]['price'] * $quantity]]);
                        } else {
                            $this->status(200);
                            return $this->response(['status' => false, 'message' => 'Nothing updates']);
                        }
                    } else {
                        // Add to cart
                        $added = $this->cartsModel->addToCart($userId, $productId, $size, $quantity);
                        if ($added) {
                            $this->status(201);
                            return $this->response(['status' => true, 'data' => ['name' => $checkProduct[0]['name'], 'size' => $checkProduct[0]['sizeName'], 'unitPrice' => (int)$checkProduct[0]['price'], 'quantity' => $quantity, 'price' => $checkProduct[0]['price'] * $quantity]]);
                        } else {
                            $this->status(400);
                            throw new Exception('Add to cart failed');
                        }
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
                $size = RestApi::bodyData('size');
                if ($productId === null) {
                    $this->status(400);
                    throw new Exception('Product ID does not exist');
                }
                if ($this->isValidProduct($productId, $size)) {
                    if ($this->isValidInCart($userId, $productId, $size)) {
                        $this->cartsModel->removeOneFromCart($userId, $productId, $size);
                        if (mysqli_affected_rows($this->cartsModel->getConn()) == 1) {
                            $this->status(204);
                            return;
                        } else {
                            $this->status(400);
                            throw new Exception('Delete Failed');
                        }
                    } else {
                        $this->status(400);
                        throw new Exception('Product does not exist in your cart');
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
    private function isValidProduct($productId, $size)
    {
        try {
            $productsModel = new ProductsModel();
            $data = $productsModel->isValidProduct($productId, $size);
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }
    public function isValidInCart($userId, $productId, $size)
    {
        try {
            $cartsModel = new CartsModel();
            $cart = $cartsModel->getBy(['userId' => $userId, 'productId' => $productId, 'size' => $size]);
            if (count($cart) > 0) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
