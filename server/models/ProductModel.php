<?php
require_once("./models/Model.php");

class ProductModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'Product';
    }
    public function insertProduct($arrayData)
    {
        return $this->insert($arrayData);
    }
    public function deleteProduct($productId)
    {
        return $this->delete(['productId' => $productId]);
    }
}
