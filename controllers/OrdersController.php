<?php

require_once("./utils/RestApi.php");
require_once("./utils/JWTHelper.php");
require_once("./models/SizesModel.php");
require_once("./models/UsersHaveOrdersModel.php");
require_once("./models/ProductsInOrdersModel.php");

class OrdersController extends Controller
{
    private $ordersModel;
    public function __construct()
    {
        $this->ordersModel = $this->model("OrdersModel");
    }
    public function createAnOrder()
    {
        return $this->response(['status' => false, 'message' => 'test']);
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['admin', 'customer'])) {
            $userId = getUserId($authHeader);
            $products = RestApi::bodyData('products');
            $phone = RestApi::bodyData('phone');
            $note = RestApi::bodyData('note');
            $address = RestApi::bodyData('address');
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
                } else if (!(strlen($address) > 6 && strlen($address) < 500)) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Length of address must be in range [6, 500]']);
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
                $this->ordersModel->insertOrder($phone, $cost, $note, $address);
                $orderId = $this->ordersModel->getConn()->insert_id;
                print_r($orderId);
                // Insert UsersHaveOrders
                $usersHaveOrdersModel = new UsersHaveOrdersModel();
                $usersHaveOrdersModel->insertOrder($orderId, $userId);
                // Insert ProductsInOrders
                $productsInOrdersModel = new ProductsInOrdersModel();
                $productsInOrdersModel->insertProductsInOrder($orderId, $products);
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
            if (!in_array($status, ['Pending', 'Accepted', 'Shipping', 'Done'])) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Status is invalid']);
            }
            $done = false;
            if ($status == 'Done') {
                $done = true;
            }
            $query = $this->ordersModel->updateStatus($orderId, $status, $done);
            if ($query > 0) {
                $this->status(200);
                return $this->response(['status' => true, 'message' => 'Update successfully']);
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Update failed']);
            }
        } else if (in_array($role, ['customer'])) {
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
                        if ($stt == 'pending') {
                            array_push($listStt, 'Pending');
                        } else if ($stt == 'accepted') {
                            array_push($listStt, 'Accepted');
                        } else if ($stt == 'shipping') {
                            array_push($listStt, 'Shipping');
                        } else if ($stt == 'done') {
                            array_push($listStt, 'Done');
                        }
                    }
                }
                $orderBy = $orderBy == 'asc' ? 'ASC' : 'DESC';
                $limit = $limit ? ((int)$limit > 0 ? (int)$limit : 12) : 12;
                $frame = $frame ? ((int)$frame > 0 ? (int)$frame : 1) : 1;
                $data = $this->ordersModel->getOrders($userId, $listStt, $orderBy, $limit, $frame);
                $this->status(200);
                return $this->response(['status' => true, 'data' => $data]);
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
                        if ($stt == 'pending') {
                            array_push($listStt, 'Pending');
                        } else if ($stt == 'accepted') {
                            array_push($listStt, 'Accepted');
                        } else if ($stt == 'shipping') {
                            array_push($listStt, 'Shipping');
                        } else if ($stt == 'done') {
                            array_push($listStt, 'Done');
                        }
                    }
                }
                $orderBy = $orderBy == 'asc' ? 'ASC' : 'DESC';
                $limit = $limit ? ((int)$limit > 0 ? (int)$limit : 12) : 12;
                $frame = $frame ? ((int)$frame > 0 ? (int)$frame : 1) : 1;
                $data = $this->ordersModel->getOrders($userId, $listStt, $orderBy, $limit, $frame);
                $this->status(200);
                return $this->response(['status' => true, 'data' => $data]);
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
        echo "Cancel Orders";
    }

    public function orderDetail()
    {
        echo "Orders Details";
    }

    private function validatePhone(&$phone)
    {
        $phone = trim($phone);
        if (preg_match('/^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/', $phone)) return true;
        return false;
    }
}
