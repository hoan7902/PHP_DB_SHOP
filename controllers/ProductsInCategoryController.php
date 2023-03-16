<?php

require_once('./utils/JWTHelper.php');
require_once('./models/ProductsInCategoryModel.php');
require_once('./utils/RestApi.php');

class ProductsInCategoryController extends Controller
{
    private $productsInOrderModel;
    public function __construct()
    {
        $this->productsInOrderModel = $this->model('ProductsInCategoryModel');
    }
    public function addProductInCat($productId, $categories)
    {
        try {
            $res = $this->productsInOrderModel->insertProductsInCategory($productId, $categories);
        } catch (Exception $e) {
            throw new Exception("Add Products In Category Failed");
        }
        return $res;
    }
    public function removeProductOutOfCat()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $productId = RestApi::bodyData('productId');
            $categoryId = RestApi::bodyData('categoryId');

            if (!$productId || !$categoryId) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Missing data']);
            }
            try {
                $query = $this->productsInOrderModel->deleteProductsInCategory($productId, $categoryId);
                if (mysqli_affected_rows($this->productsInOrderModel->getConn()) < 1) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Nothing changes']);
                }
                $this->status(200);
                return $this->response(['status' => true, 'message' => 'Remove product out of category successful']);
            } catch (Exception $e) {
                $this->status(500);
                return $this->response(['status' => false, 'message' => 'Post failed: ' . $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authentication', 'self', 'customer'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authentication']);
        } else {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authorization']);
        }
    }
}
