<?php

require_once('./utils/JWTHelper.php');
require_once('./utils/RestApi.php');
require_once('./utils/HandleUri.php');
require_once('./models/ImagesModel.php');
require_once('./models/SizesModel.php');
require_once('./controllers/ProductsInCategoriesController.php');

class ProductsController extends Controller
{
    private $productsModel;
    public function __construct()
    {
        $this->productsModel = $this->model("ProductsModel");
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
                $res = $this->productsModel->insertProduct(['name' => $name, 'description' => $desc]);
                if ($res) {
                    $productId = $this->productsModel->getConn()->insert_id;
                    if (!$this->addImages((int)$productId, $imgs)) {
                        $this->productsModel->deleteProduct($productId);
                        $this->status(500);
                        return $this->response(['status' => false, 'message' => 'Post failed with images']);
                    }
                    if (!$this->addSizes((int)$productId, $sizes)) {
                        $this->productsModel->deleteProduct($productId);
                        $this->status(500);
                        return $this->response(['status' => false, 'message' => 'Post failed with sizes']);
                    }
                    if (is_array($categories) && count($categories) > 0) {
                        $prodInCat = new ProductsInCategoriesController();
                        $prodInCat->addProductInCat($productId, $categories);
                    }
                    $this->status(201);
                    return $this->response(['status' => true, 'message' => 'Post successful']);
                }
            } catch (Exception $e) {
                $this->productsModel->deleteProduct($productId);
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
            $data = $this->productsModel->getById($productId, ['productId', 'name', 'description']);
            if (count($data) > 0) {
                $sizesModel = new SizesModel();
                $imagesModel = new ImagesModel();
                $sizes = $sizesModel->getByProductId($productId, ['sizeName', 'quantity', 'price']);
                $images = $imagesModel->getByProductId($productId, ['imageLink']);
                $categories = $this->productsModel->getCategoriesOfProduct($productId);
                $images = array_map(function ($image) {
                    return $image['imageLink'];
                }, $images);
                $data = [...$data[0], 'sizes' => $sizes, 'images' => $images, 'categories' => $categories];
                $this->status(200);
                return $this->response(['status' => true, ...$data]);
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Product does not exist']);
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
        $limit = RestApi::getParams('limit');
        /* sort_by: Theo giá -> Hai chiều + order_by
        sort_by: Bán chạy -> 1 chiều -> order_by: Desc
        Kết hợp với min_price, max_price, $collections
        Mặc định: Danh sách theo thời gian -> Mới nhất lấy trước.
         */
        if ($orderBy == 'asc') {
            $orderBy = 'ASC';
        } else {
            $orderBy = 'DESC';
        }
        if ($sortBy == 'price') {
        } else if ($sortBy == 'order_count') {
        } else {
            $sortBy = 'time';
        }
        if ($page) {
            $page = (int)$page < 1 ? 1 : (int)$page;
        } else {
            $page = 1;
        }
        if ($limit) {
            $limit = (int)$limit < 1 ? 24 : (int)$limit;
        } else {
            $limit = 24;
        }
        if ($minPrice) {
            $minPrice = (int)$minPrice < 0 ? 0 : (int)$minPrice;
        }
        if ($maxPrice) {
            $maxPrice = (int)$maxPrice < 0 ? 3e38 : (int)$maxPrice;
        }
        if ($categories) {
            $categories = explode('%C2', $categories);
        }
        if ($collections) {
            $collections = explode('%C2', $collections);
        }
        $data = $this->productsModel->getProducts($sortBy, $orderBy, $limit, $page, $minPrice, $maxPrice, $categories, $collections);
    }

    private function addImages($productId, $imgs)
    {
        if (count($imgs) > 0) {
            $imagesModel = new ImagesModel();
            $res = $imagesModel->insertImages($productId, $imgs);
            if ($res) return true;
        }
        return false;
    }

    private function addSizes($productId, $sizes)
    {
        $sizesModel = new SizesModel();
        try {
            if ($sizesModel->insertSizes($productId, $sizes))
                return true;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }
}
