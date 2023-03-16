<?php

require_once('./utils/JWTHelper.php');
require_once('./utils/RestApi.php');
require_once('./utils/HandleUri.php');
require_once('./models/ImageModel.php');
require_once('./models/SizeModel.php');
require_once('./controllers/ProductsInCategoryController.php');

class ProductController extends Controller
{
    private $productModel;
    public function __construct()
    {
        $this->productModel = $this->model("ProductModel");
    }
    public function addProduct()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $name = RestApi::bodyData('name');
            $desc = RestApi::bodyData('description');
            $sizes = RestApi::bodyData('sizes');
            $imgs = RestApi::bodyData('images');
            $categories = RestApi::bodyData('categories');
            if (!$name || !$desc || !$sizes || !$imgs) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Missing data']);
            }
            $checkSizeDatatype = true;
            foreach ($sizes as $value) {
                if (!array_key_exists("sizeName", $value) || !array_key_exists("quantity", $value) || !array_key_exists("price", $value)) {
                    $checkSizeDatatype = false;
                    break;
                }
            }
            if (!$checkSizeDatatype) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Data type of sizes is wrong']);
            }
            $name = trim($name);
            $desc = trim($desc);
            if (strlen($name) < 12 || strlen($name) > 500) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Length of name must be in range [12, 500]']);
            }
            $productId = null;
            try {
                $res = $this->productModel->insertProduct(['name' => $name, 'description' => $desc]);
                if ($res) {
                    $productId = $this->productModel->getConn()->insert_id;
                    if (!$this->addImages((int)$productId, $imgs)) {
                        $this->productModel->deleteProduct($productId);
                        $this->status(500);
                        return $this->response(['status' => false, 'message' => 'Post failed with images']);
                    }
                    if (!$this->addSizes((int)$productId, $sizes)) {
                        $this->productModel->deleteProduct($productId);
                        $this->status(500);
                        return $this->response(['status' => false, 'message' => 'Post failed with sizes']);
                    }
                    if (is_array($categories) && count($categories) > 0) {
                        $prodInCat = new ProductsInCategoryController();
                        $prodInCat->addProductsInCategory($productId, $categories);
                    }
                    $this->status(201);
                    return $this->response(['status' => true, 'message' => 'Post successful']);
                }
            } catch (Exception $e) {
                $this->productModel->deleteProduct($productId);
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

    public function getOneProduct()
    {
        $params = HandleUri::sliceUri();
        $productId = $params ? ($params[2] ? $params[2] : null) : null;
        try {
            $data = $this->productModel->getById($productId, ['productId', 'name', 'description']);
            if (count($data) > 0) {
                $sizeModel = new SizeModel();
                $imageModel = new ImageModel();
                $sizes = $sizeModel->getByProductId($productId, ['sizeName', 'quantity', 'price']);
                $images = $imageModel->getByProductId($productId, ['imageLink']);
                $categories = $this->productModel->getCategoriesOfProduct($productId);
                $images = array_map(function ($image) {
                    return $image['imageLink'];
                }, $images);
                $data = [...$data[0], 'sizes' => $sizes, 'images' => $images, 'categories' => $categories];
                $this->status(200);
                return $this->response(['status' => true, ...$data]);
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'User does not exist']);
            }
        } catch (Exception $e) {
            $this->status(500);
            return $this->response(['status' => false, 'message' => "Get Product Failed: " . $e->getMessage()]);
        }
    }

    public function getProducts()
    {
        // order_by: [asc, desc]
        // sort_by: [price, order_count]
        $orderBy = RestApi::getParams('order_by');
        $sortBy = RestApi::getParams('sort_by');
        $categories = RestApi::getParams('categories');
        $page = RestApi::getParams('page');
        $maxPrice = RestApi::getParams('max_price');
        $minPrice = RestApi::getParams('min_price');
        $collections = RestApi::getParams('collections');
        if ($orderBy == "desc") {
            $orderBy = "DESC";
        } else {
            $orderBy = "ASC";
        }
        $page = $page ? (int)$page : 1;
        if ($page < 1) $page = 1;
        $minPrice = $minPrice ? (float)$minPrice : 0.0;
        if ($minPrice < 0.0) $minPrice = 0.0;
        $maxPrice = $maxPrice ? (float)$maxPrice : 0.0;
        if ($maxPrice < 0.0) $maxPrice = null;
        if ($sortBy == 'price') {
        } else if ($sortBy == 'order_count') {
            $sortBy = 'orderCount';
        } else if ($sortBy == 'created_at') {
            $sortBy = 'createdAt';
        } else {
            $sortBy = 'createdAt';
        }
    }

    private function addImages($productId, $imgs)
    {
        if (count($imgs) > 0) {
            $imageModel = new ImageModel();
            $res = $imageModel->insertImages($productId, $imgs);
            if ($res) return true;
        }
        return false;
    }

    private function addSizes($productId, $sizes)
    {
        $sizeModel = new SizeModel();
        try {
            if ($sizeModel->insertSizes($productId, $sizes))
                return true;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }
}
