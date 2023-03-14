<?php

require_once('./utils/JWTHelper.php');
require_once('./utils/RestApi.php');

class ProductController extends Controller
{
    private $productModel;
    public function __construct()
    {
        $this->productModel = $this->model("ProductModel");
    }
    public function addProduct()
    {
        $this->response(['message' => 'Add a product API']);
    }
}
