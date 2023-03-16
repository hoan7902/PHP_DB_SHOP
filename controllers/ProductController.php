<?php

require_once('./utils/JWTHelper.php');
require_once('./utils/RestApi.php');
require_once('./models/ImageModel.php');
require_once('./models/SizeModel.php');

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
            if (!$name || !$desc || !$sizes || !$imgs) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Missing data']);
            }
            $checkSizeDatatype = true;
            foreach ($sizes as $value) {
                if (!array_key_exists("sizename", $value) || !array_key_exists("quantity", $value) || !array_key_exists("price", $value)) {
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
            try {
                $res = $this->productModel->insertProduct(['name' => $name, 'description' => $desc]);
                if ($res) {
                    $productId = $this->productModel->getConn()->insert_id;
                    if (!$this->addImages((int)$productId, $imgs)) {
                        $this->productModel->deleteProduct($productId);
                        $this->status(500);
                        return $this->response(['status' => false, 'message' => 'Post failed']);
                    }
                    if (!$this->addSizes((int)$productId, $sizes)) {
                        $this->productModel->deleteProduct($productId);
                        $this->status(500);
                        return $this->response(['status' => false, 'message' => 'Post failed']);
                    }
                    $this->status(201);
                    return $this->response(['status' => true, 'message' => 'Post successful']);
                }
            } catch (Exception $e) {
                $this->status(500);
                return $this->response(['status' => false, 'message' => 'Post failed']);
            }
        } else if (in_array($role, ['Not Authentication', 'self', 'customer'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authentication']);
        } else {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authorization']);
        }
    }

    static private function addImages($productId, $imgs)
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
