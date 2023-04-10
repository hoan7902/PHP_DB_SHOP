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
    public function updateStatus($orderId, $status)
    {
        return $this->updateOne(['orderId' => $orderId], ['status' => $status]);
    }
}
