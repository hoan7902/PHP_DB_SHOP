<?php

require_once('./utils/JWTHelper.php');
require_once('./models/ProductsInCategoryModel.php');

class ProductsInCategoryController extends Controller
{
    private $productsInOrderModel;
    public function __construct()
    {
        $this->productsInOrderModel = $this->model('ProductsInCategoryModel');
    }
    public function addProductsInCategory($productId, $categories)
    {
        try {
            $res = $this->productsInOrderModel->insertProductsInCategory($productId, $categories);
        } catch (Exception $e) {
            throw new Exception("Add Products In Category Failed");
        }
        return $res;
    }
}
