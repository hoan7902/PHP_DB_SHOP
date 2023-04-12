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
            return $this->updateOne(['orderId' => $orderId], ['status' => $status, 'deliveryTime' => $currentTime]);
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
        try {
            $query = $this->query($sql);
            $data = [];
            while ($row = mysqli_fetch_assoc($query)) {
                array_push($data, $row);
            }
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
