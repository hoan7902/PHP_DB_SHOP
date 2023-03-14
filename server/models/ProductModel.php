<?php
require_once("./models/Model.php");

class ProductModel extends Model
{
    protected $productModel;
    public function __construct()
    {
        $this->productModel = 'Product';
    }
    public function insertProduct($arrayData)
    {
    }
}
