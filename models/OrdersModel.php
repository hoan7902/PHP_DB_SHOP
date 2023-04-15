<?php

require_once('./models/Model.php');

class OrdersModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'Orders';
    }
    public function insertOrder($phone, $cost, $note, $address)
    {
        return $this->insert(['phone' => $phone, 'cost' => $cost, 'note' => $note, 'address' => $address]);
    }
    public function deleteOrder($orderId)
    {
        return $this->delete(['orderId' => $orderId]);
    }
    public function updateStatus($orderId, $status, $deliveryTime = false)
    {
        if ($deliveryTime) {
            $currentTime = date('Y-m-d H:i:s');
            return $this->updateOne(['orderId' => $orderId], ['status' => $status, 'deliveryTime' => $currentTime, 'paid' => 1, 'paymentDate' => $currentTime]);
        }
        return $this->updateOne(['orderId' => $orderId], ['status' => $status]);
    }
    public function getOrders($userId = null, $status = [], $orderBy = 'DESC', $limit = 12, $frame = 1)
    {
        $offset = ($frame - 1) * $limit;
        if ($userId !== null) {
            $whereExpr = "WHERE UsersHaveOrders.userId = {$userId} ";
        } else {
            $whereExpr = "";
        }
        $sttcond = "";
        if (is_array($status) && count($status) > 0) {
            $sttcond = "(" . "'" . implode("', '", $status) . "'" . ")";
        }
        if ($sttcond != "") {
            if ($whereExpr == "") {
                $whereExpr = "WHERE Orders.status IN {$sttcond} ";
            } else {
                $whereExpr .= "AND Orders.status IN {$sttcond} ";
            }
        }
        $sql = "
            SELECT * FROM Orders
            INNER JOIN UsersHaveOrders ON UsersHaveOrders.orderId = Orders.orderId
            {$whereExpr}
            GROUP BY Orders.orderId
            ORDER BY Orders.orderTime {$orderBy}
            LIMIT {$limit}
            OFFSET {$offset};
        ";
        $sqlCount = "
            SELECT * FROM Orders
            INNER JOIN UsersHaveOrders ON UsersHaveOrders.orderId = Orders.orderId
            {$whereExpr}
            GROUP BY Orders.orderId
            ORDER BY Orders.orderTime {$orderBy};";
        try {
            $query = $this->query($sql);
            $queryCount = $this->query($sqlCount);
            if ($queryCount) {
                $count = $queryCount->num_rows;
            } else {
                $count = 0;
            }
            $data = [];
            while ($row = mysqli_fetch_assoc($query)) {
                array_push($data, $row);
            }
            return ['count' => $count, 'data' => $data];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function getDetail($orderId)
    {
        $sql = "
            SELECT po.productId, po.size, po.quantity, s.price FROM ProductsInOrders po
                INNER JOIN Sizes s ON po.size = s.sizeName AND po.productId = s.productId
                WHERE po.orderId = {$orderId};
        ";
        $query = $this->query($sql);
        $data = [];
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                array_push($data, $row);
            }
        }
        return $data;
    }
    public function validOrder($userId, $orderId)
    {
        $sql = "SELECT * FROM UsersHaveOrders WHERE userId = $userId AND orderId = $orderId";
        $query = $this->query($sql);
        $data = [];
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                array_push($data, $row);
            }
            return $data;
        }
        return $data;
    }
}
