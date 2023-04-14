<?php

require_once('./utils/JWTHelper.php');
require_once('./utils/RestApi.php');
require_once('./utils/HandleUri.php');
require_once('./models/ImagesModel.php');
require_once('./models/SizesModel.php');
require_once('./models/CategoriesModel.php');
require_once('./controllers/ProductsInCategoriesController.php');
require_once('./utils/FirebaseImageUploader.php');

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
            $name = RestApi::formData('name');
            $desc = RestApi::formData('description');
            $sizes = RestApi::formData('sizes');
            $imgs = RestApi::fileData('images');
            $categories = RestApi::formData('categories');
            if ($sizes != null) {
                $sizes = json_decode($sizes);
            }
            if ($categories != null && !is_array($categories)) {
                $categories = json_decode($categories);
            }
            if (!$name || !$desc || !$sizes || !$imgs) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Missing data']);
            }
            $checkSizeDatatype = true;
            foreach ($sizes as $value) {
                if (!property_exists($value, "sizeName") || !property_exists($value, "quantity") || !property_exists($value, "price")) {
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
            if (strlen($name) < 1 || strlen($name) > 500) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Length of name must be in range [1, 500]']);
            }
            $productId = null;
            try {
                $imgUrls = FirebaseStorageUploader::uploadImages($imgs);
                $res = $this->productsModel->insertProduct(['name' => $name, 'description' => $desc]);
                if ($res) {
                    $productId = $this->productsModel->getConn()->insert_id;
                    if (!$this->addImages((int)$productId, $imgUrls)) {
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
        } else if (in_array($role, ['self', 'customer'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        } else {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }

    public function updateProduct()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $productId = RestApi::formData('productId');
            $name = RestApi::formData('name');
            $desc = RestApi::formData('description');
            $sizes = RestApi::formData('sizes');
            $imgs = RestApi::fileData('images');
            $categories = RestApi::formData('categories');
            if ($productId) {
                $product = $this->productsModel->getById($productId, ['*']);
                if (count($product) != 1) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Product is not exist']);
                }
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Missing ProductId']);
            }
            $newProduct = [];
            $updateSizes = false;
            $updateImages = false;
            $updateCats = false;
            if ($sizes != null) {
                $sizes = json_decode($sizes);
                $updateSizes = true;
            }
            if ($categories != null && !is_array($categories)) {
                $categories = json_decode($categories);
            }
            if ($categories) {
                $updateCats = true;
            }
            if ($imgs != null) {
                $updateImages = true;
            }
            $name = trim($name);
            $desc = trim($desc);
            if ($name != null) {
                if (strlen($name) < 1 || strlen($name) > 500) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Length of name must be in range [1, 500]']);
                }
                $newProduct['name'] = $name;
            }
            if ($desc != null) {
                $newProduct['description'] = $desc;
            }
            $checkSizeDatatype = true;
            if ($updateSizes) {
                foreach ($sizes as $value) {
                    if (!property_exists($value, "sizeName") || !property_exists($value, "quantity") || !property_exists($value, "price")) {
                        $checkSizeDatatype = false;
                        break;
                    }
                }
                if (!$checkSizeDatatype) {
                    $this->status(400);
                    return $this->response(['status' => false, 'message' => 'Data type of sizes is wrong']);
                }
            }
            try {
                if ($updateCats && is_array($categories) && count($categories) > 0) {
                    foreach ($categories as $categoryId) {
                        if (!$this->isValidCategory($categoryId)) {
                            throw new Exception('Category does not exist');
                        }
                    }
                    $productsInCategoriesModel = new ProductsInCategoriesModel();
                    // Hold old categories
                    $oldCategories = $productsInCategoriesModel->getCatsOfProduct($productId);
                    // Delete old categories
                    $productsInCategoriesModel->deleteAllCatsOfProduct($productId);
                    // Update new categories
                    $productsInCategoriesModel->insertProductsInCategory($productId, $categories);
                }
                if ($updateImages) {
                    $imagesModel = new ImagesModel();
                    // Hold old images
                    $oldImgs = $imagesModel->getImages($productId);
                    // Delete old images
                    $imagesModel->deleteImages(['productId' => $productId]);
                    // Upload new images
                    $imgUrls = FirebaseStorageUploader::uploadImages($imgs);
                    // Reinsert images
                    if (!$this->addImages($productId, $imgUrls)) {
                        $oldImgsLink = [];
                        foreach ($oldImgs as $key => $value) {
                            array_push($oldImgsLink, $value['imageLink']);
                        }
                        $this->addImages($productId, $oldImgsLink);
                        $this->status(400);
                        return $this->response(['status' => false, 'message' => 'Update failed']);
                    }
                }
                if ($updateSizes) {
                    $sizesModel = new SizesModel();
                    // Delete old sizes
                    $sizesModel->deleteSizes(['productId' => $productId]);
                    // Update new sizes
                    $this->addSizes((int)$productId, $sizes);
                }
                // Update product
                $this->productsModel->updateOne(['productId' => $productId], $newProduct);
                $this->status(200);
                return $this->response(['status' => true, 'message' => 'Update successfully']);
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Post failed: ' . $e->getMessage()]);
            }
        } else if (in_array($role, ['self', 'customer'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        } else {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
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
                // $data = [...$data[0], 'sizes' => $sizes, 'images' => $images, 'categories' => $categories];
                // $this->status(200);
                // return $this->response(['status' => true, ...$data]);
                $data = array_merge($data[0], ['sizes' => $sizes, 'images' => $images, 'categories' => $categories]);
                $this->status(200);
                return $this->response(array_merge(['status' => true], $data));
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Product does not exist']);
            }
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'message' => "Get Product Failed: " . $e->getMessage()]);
        }
    }

    public function deleteOneProduct()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $params = HandleUri::sliceUri();
            $productId = $params ? ($params[2] ? $params[2] : null) : null;
            try {
                $this->productsModel->deleteByHideProduct($productId);
                $this->status(204);
                return;
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['customer', 'self'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }

    public function getProducts()
    {
        try {
            $orderBy = RestApi::getParams('order_by');
            $sortBy = RestApi::getParams('sort_by');
            $categories = RestApi::getParams('categories');
            $page = RestApi::getParams('page');
            $maxPrice = RestApi::getParams('max_price');
            $minPrice = RestApi::getParams('min_price');
            $collections = RestApi::getParams('collections');
            $limit = RestApi::getParams('limit');
            if ($orderBy == 'asc') {
                $orderBy = 'ASC';
            } else {
                $orderBy = 'DESC';
            }
            if ($sortBy == 'price') {
            } else if ($sortBy == 'order_count') {
                $sortBy = 'orderCount';
            } else {
                $sortBy = 'createdAt';
            }
            if ($page) {
                $page = (int)$page < 1 ? 1 : (int)$page;
            } else {
                $page = 1;
            }
            if ($limit) {
                $limit = (int)$limit < 0 ? 24 : (int)$limit;
            } else {
                $limit = 24;
            }
            $minPrice = $minPrice ? ((int)$minPrice < 0 ? 0 : (int)$minPrice) : 0;
            $maxPrice = $maxPrice ? ((int)$maxPrice < 0 ? 3e38 : (int)$maxPrice) : 3e38;
            if ($categories) {
                $categories = explode(',', $categories);
            }
            if ($collections) {
                $collections = explode(',', $collections);
            }
            $data = $this->productsModel->getProducts($sortBy, $orderBy, $limit, $page, $minPrice, $maxPrice, $categories, $collections);
            if (count($data['data']) > 0) {
                $imagesModel = new ImagesModel();
                for ($i = 0; $i < count($data); $i++) {
                    $images = $imagesModel->getImages($data['data'][$i]['productId']);
                    $data['data'][$i]['images'] = [];
                    foreach ($images as $key => $img) {
                        array_push($data['data'][$i]['images'], $img['imageLink']);
                    }
                }
            }
            $this->status(200);
            return $this->response(['status' => true, 'count' => $data['count'], 'data' => $data['data']]);
        } catch (Exception $e) {
            $this->status(400);
            return $this->response(['status' => false, 'message' => $e->getMessage()]);
        }
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
    public function isValidCategory($categoryId)
    {
        $categoriesModel = new CategoriesModel();
        $cat = $categoriesModel->getBy(['categoryId' => $categoryId]);
        if (count($cat) > 0) {
            return true;
        }
        return false;
    }
}
