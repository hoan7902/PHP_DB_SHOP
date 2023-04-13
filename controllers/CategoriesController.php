<?php

require_once('./utils/JWTHelper.php');
require_once('./utils/RestApi.php');
require_once('./utils/HandleUri.php');

class CategoriesController extends Controller
{
    private $categoryModel;
    public function __construct()
    {
        $this->categoryModel = $this->model('CategoriesModel');
    }
    public function addCategory()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            $name = RestApi::bodyData('name');
            $desc = RestApi::bodyData('description');
            if (!$name) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Missing data']);
            }
            $name = trim($name);
            $desc = trim($desc);
            if (strlen($name) < 1 || strlen($name) > 500) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => 'Length of name must be in range [1, 500]']);
            }
            try {
                if ($this->categoryModel->insertCategory(['name' => $name, 'description' => $desc])) {
                    $this->status(201);
                    return $this->response(['status' => true, 'message' => 'Create category successful']);
                }
            } catch (Exception $e) {
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
    public function getCategories()
    {
        try {
            $data = $this->categoryModel->getAll();
            if (count($data) > 0) {
                $this->status(200);
                return $this->response(['status' => true, 'categories' => $data]);
            } else {
                $this->status(400);
                return $this->response(['status' => false, 'No categories']);
            }
        } catch (Exception $e) {
            $this->status(500);
            $this->response(['status' => false, 'message' => 'Get category error: ' . $e->getMessage()]);
        }
    }
    public function deleteCategory()
    {
        echo "Delete Category";
    }
}
