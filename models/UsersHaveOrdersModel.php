<?php

require_once("./models/Model.php");

class UsersHaveOrdersModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'UsersHaveOrders';
    }
    public function deleteOrder($orderId)
    {
        return $this->delete(['orderId' => $orderId]);
    }
    public function insertOrder($orderId, $userId)
    {
        return $this->insert(['userId' => $userId, 'orderId' => $orderId]);
    }
}
