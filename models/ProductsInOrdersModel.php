<?php

require_once("./models/Model.php");

class ProductsInOrdersModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "ProductsInOrders";
    }
    public function insertProductsInOrder($orderId, $products /* $products: [[productId, size, quantity]] */)
    {
        $data = [];
        foreach ($products as $key => $value) {
            array_push($data, ['orderId' => $orderId, 'productId' => $value['productId'], 'size' => $value['size'], 'quantity' => $value['quantity']]);
        }
        $this->insertMul(['orderId', 'productId', 'size', 'quantity'], $data);
    }
    public function deleteProductsInOrder($orderId)
    {
    }
}
