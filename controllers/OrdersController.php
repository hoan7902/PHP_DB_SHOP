<?php

require_once("./utils/RestApi.php");
require_once("./utils/JWTHelper.php");
require_once("./utils/HandleUri.php");
require_once("./models/SizesModel.php");
require_once('./models/CartsModel.php');
require_once("./models/UsersHaveOrdersModel.php");
require_once("./models/ProductsInOrdersModel.php");
require_once('./controllers/CartsController.php');

class OrdersController extends Controller
{
    private $ordersModel;
    public function __construct()
    {
        $this->ordersModel = $this->model("OrdersModel");
    }
    public function createAnOrder()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['admin', 'customer'])) {
            $userId = getUserId($authHeader);
            $products = RestApi::bodyData('products');
            $phone = RestApi::bodyData('phone');
            $note = RestApi::bodyData('note');
            $address = RestApi::bodyData('address');
            $paymentMethod = RestApi::bodyData('paymentMethod');
            // Validate payment method
            if (!in_array($paymentMethod, ['Cash', 'Momo Pay'])) {
                $paymentMethod = 'Cash';
            }
            // Validate products data
            if ($products == null) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'No content request']);
            } else if (is_array($products) && count($products) > 0) {
                foreach ($products as $value) {
                    if (is_array($value) && array_key_exists('productId', $value) && array_key_exists('size', $value) && array_key_exists('quantity', $value)) {
                        if ($value['quantity'] <= 0) {
                            $this->status(400);
                            return $this->response(['status' => false, 'message' => 'Wrong data']);
                        }
                    } else {
                        $this->status(400);
                        return $this->response(['status' => false, 'message' => 'Wrong data']);
                    }
                }
                // Validate phone number
                if (!$this->validatePhone($phone)) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Phone number is invalid']);
                }
                // Validate address
                if ($address == null) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Address is not null']);
                } else if (!(strlen($address) > 0 && strlen($address) < 500)) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Length of address must be in range [1, 500]']);
                }
                // Validate note
                if ($note == null) {
                    $note = "";
                } else if (strlen($note) > 255) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Length of note must be in range [0, 255]']);
                }
                // Check quantity
                $sizesModel = new SizesModel();
                foreach ($products as $key => $value) {
                    $size = $sizesModel->getSize($value['productId'], $value['size']);
                    if (is_array($size) && count($size) > 0) {
                        if ($size[0]['quantity'] < $value['quantity']) {
                            $this->status(400);
                            return $this->response(['status' => false, 'message' => 'Not enough products']);
                        }
                    } else {
                        $this->status(400);
                        return $this->response(['status' => false, 'message' => 'Product is not valid']);
                    }
                }
                // This code is stupid and potentially error-prone. But that's it for now
                // Calculate cost and update quantity
                $cost = 0;
                foreach ($products as $key => $value) {
                    $size = $sizesModel->getSize($value['productId'], $value['size'])[0];
                    $cost += $size['price'] * $value['quantity'];
                    // Update quantity
                    $sizesModel->updateQuantity($value['productId'], $value['size'], $size['quantity'] - $value['quantity']);
                }
                // Insert Order
                $this->ordersModel->insertOrder($phone, $cost, $note, $address, $paymentMethod);
                $orderId = $this->ordersModel->getConn()->insert_id;
                // Insert UsersHaveOrders
                $usersHaveOrdersModel = new UsersHaveOrdersModel();
                $usersHaveOrdersModel->insertOrder($orderId, $userId);
                // Insert ProductsInOrders
                $productsInOrdersModel = new ProductsInOrdersModel();
                $productsInOrdersModel->insertProductsInOrder($orderId, $products);
                // Check and update cart
                $this->checkAndRemoveProductsInCart($userId, $products);
                $this->status(201);
                return $this->response(['status' => true, 'message' => 'Order successfully']);
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Wrong data']);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'You have to login before order']);
        }
        $this->status(400);
        return $this->response(['status' => false, 'message' => 'Failed']);
    }

    public function updateStatusOrder()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $status = RestApi::bodyData('status');
            $orderId = RestApi::bodyData('orderId');
            // Validate status
            if (!in_array($status, ['Pending', 'Accepted', 'Shipping', 'Done', 'Canceled'])) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Status is invalid']);
            }
            $done = null;
            if ($status == 'Done') {
                $done = 'Done';
            } else if ($status == 'Canceled') {
                $done = 'Canceled';
            }
            $od = $this->ordersModel->getBy(['orderId' => $orderId]);
            $returnProduct = true;
            if (count($od) > 0) {
                if (in_array($od[0]['status'], ['Done', 'Canceled'])) {
                    $returnProduct = false;
                }
            }
            $query = $this->ordersModel->updateStatus($orderId, $status, $done);
            if ($query > 0) {
                if ($done == 'Canceled') {
                    // Check and update product
                    if ($returnProduct) {
                        $this->checkAndUpdateProductsInOrder($orderId);
                    }
                }
                $this->status(200);
                return $this->response(['status' => true, 'message' => 'Update successfully']);
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Update failed']);
            }
        } else if (in_array($role, ['customer', 'self'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }

    public function myOrders()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['customer', 'admin', 'self'])) {
            try {
                $userId = getUserId($authHeader);
                $limit = RestApi::getParams('limit');
                $frame = RestApi::getParams('frame');
                $status = RestApi::getParams('status');
                $orderBy = RestApi::getParams('order_by');
                $statusArr = explode(",", $status);
                $listStt = [];
                if (is_array($statusArr) && count($statusArr) > 0) {
                    foreach ($statusArr as $stt) {
                        if (in_array($stt, ['pending', 'Pending'])) {
                            array_push($listStt, 'Pending');
                        } else if (in_array($stt, ['accepted', 'Accepted'])) {
                            array_push($listStt, 'Accepted');
                        } else if (in_array($stt, ['shipping', 'Shipping'])) {
                            array_push($listStt, 'Shipping');
                        } else if (in_array($stt, ['done', 'Done'])) {
                            array_push($listStt, 'Done');
                        } else if (in_array($stt, ['canceled', 'Canceled'])) {
                            array_push($listStt, 'Canceled');
                        }
                    }
                }
                $orderBy = $orderBy == 'asc' ? 'ASC' : 'DESC';
                $limit = $limit ? ((int)$limit > 0 ? (int)$limit : 12) : 12;
                $frame = $frame ? ((int)$frame > 0 ? (int)$frame : 1) : 1;
                $data = $this->ordersModel->getOrders($userId, $listStt, $orderBy, $limit, $frame);
                $this->status(200);
                return $this->response(['status' => true, 'count' => $data['count'], 'data' => $data['data']]);
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }

    public function getOrders()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['admin'])) {
            try {
                $userId = RestApi::getParams('user_id');
                $limit = RestApi::getParams('limit');
                $frame = RestApi::getParams('frame');
                $status = RestApi::getParams('status');
                $orderBy = RestApi::getParams('order_by');
                $statusArr = explode(",", $status);
                $listStt = [];
                if (is_array($statusArr) && count($statusArr) > 0) {
                    foreach ($statusArr as $stt) {
                        if (in_array($stt, ['pending', 'Pending'])) {
                            array_push($listStt, 'Pending');
                        } else if (in_array($stt, ['accepted', 'Accepted'])) {
                            array_push($listStt, 'Accepted');
                        } else if (in_array($stt, ['shipping', 'Shipping'])) {
                            array_push($listStt, 'Shipping');
                        } else if (in_array($stt, ['done', 'Done'])) {
                            array_push($listStt, 'Done');
                        } else if (in_array($stt, ['canceled', 'Canceled'])) {
                            array_push($listStt, 'Canceled');
                        }
                    }
                }
                $orderBy = $orderBy == 'asc' ? 'ASC' : 'DESC';
                $limit = $limit ? ((int)$limit > 0 ? (int)$limit : 12) : 12;
                $frame = $frame ? ((int)$frame > 0 ? (int)$frame : 1) : 1;
                $data = $this->ordersModel->getOrders($userId, $listStt, $orderBy, $limit, $frame);
                $this->status(200);
                return $this->response(['status' => true, 'count' => $data['count'], 'data' => $data['data']]);
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['customer', 'self'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }

    public function cancelAnOrder()
    {
        echo "Cancel An Order API: Updating....";
    }

    public function orderDetail()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['admin', 'customer', 'self'])) {
            try {
                $params = HandleUri::sliceUri();
                $orderId = $params ? ($params[2] ? $params[2] : null) : null;
                $userId = getUserId($authHeader);
                if ($role != 'admin') {
                    if (count($this->ordersModel->validOrder($userId, $orderId)) != 1) {
                        throw new Exception("You don't have permission to do this action");
                    }
                }
                $order = $this->ordersModel->getBy(['orderId' => $orderId]);
                if (count($order) > 0) {
                    $data = $order[0];
                    $detail = $this->ordersModel->getDetail($orderId);
                    $data['products'] = $detail;
                    $this->status(200);
                    return $this->response(['status' => true, 'data' => $data]);
                } else {
                    $this->status(400);
                    throw new Exception('This order does not exist');
                }
            } catch (Exception $e) {
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }

    private function validatePhone(&$phone)
    {
        $phone = trim($phone);
        if (preg_match('/^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/', $phone)) return true;
        return false;
    }
    private function checkAndRemoveProductsInCart($userId, $products)
    {
        try {
            $cartsContrller = new CartsController();
            $cartsModel = new CartsModel();
            foreach ($products as $product) {
                if ($cartsContrller->isValidInCart($userId, $product['productId'], $product['size'])) {
                    $cartsModel->removeOneFromCart($userId, $product['productId'], $product['size']);
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    private function checkAndUpdateProductsInOrder($orderId)
    {
        $products = $this->ordersModel->getProductsInAnOrder($orderId);
        $sizesModel = new SizesModel();
        foreach ($products as $product) {
            $sizesModel->changeQuantity($product['productId'], $product['size'], $product['quantity'], true);
        }
    }
}
