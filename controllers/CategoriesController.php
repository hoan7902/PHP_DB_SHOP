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
            $limit = RestApi::getParams('limit');
            $frame = RestApi::getParams('frame');
            $orderBy = RestApi::getParams('order_by');
            $sortBy = 'categoryId';
            if ($orderBy == 'asc') {
                $orderBy = 'ASC';
            } else {
                $orderBy = 'DESC';
            }
            $limit = $limit ? ((int)$limit >= 0 ? (int)$limit : 12) : 12;
            $frame = $frame ? ((int)$frame >= 0 ? (int)$frame : 1) : 1;
            $data = $this->categoryModel->getAll(['*'], [$sortBy], true, $limit, $frame, $orderBy);
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
    public function getCategory()
    {
        try {
            $params = HandleUri::sliceUri();
            $categoryId = $params ? ((int)$params[2] >= 0 ? (int)$params[2] : null) : null;
            if ($categoryId === null) {
                $this->status(400);
                throw new Exception('Category ID does not exist');
            }
            $cats = $this->categoryModel->getCategory($categoryId);
            if (count($cats) == 1) {
                $this->status(200);
                return $this->response(['status' => true, 'data' => $cats[0]]);
            } else {
                $this->status(400);
                throw new Exception('Category does not exist');
            }
        } catch (Exception $e) {
            return $this->response(['status' => false, 'message' => $e->getMessage()]);
        }
    }
    public function deleteCategory()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            try {
                $params = HandleUri::sliceUri();
                $categoryId = $params ? ($params[2] ? $params[2] : null) : null;
                if ($categoryId) {
                    $deleted = $this->categoryModel->delete(['categoryId' => $categoryId]);
                    if ($deleted) {
                        if (mysqli_affected_rows($this->categoryModel->getConn()) > 0) {
                            $this->status(204);
                        } else {
                            throw new Exception('Nothing changes');
                        }
                    } else {
                        throw new Exception('Delete Failed');
                    }
                } else {
                    throw new Exception('Delete Failed');
                }
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['customer'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }
    public function updateCategory()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if ($role == 'admin') {
            try {
                $params = HandleUri::sliceUri();
                $categoryId = $params ? ($params[2] ? $params[2] : null) : null;
                $name = RestApi::bodyData('name');
                $desc = RestApi::bodyData('description');
                if ($categoryId) {
                    $name = trim($name);
                    $desc = trim($desc);
                    if (strlen($name) < 1 || strlen($name) > 500) {
                        throw new Exception('Length of name must be in range [1, 500]');
                    }
                    $updated = $this->categoryModel->updateCategory($categoryId, ['name' => $name, 'description' => $desc]);
                    if ($updated > 0) {
                        $this->status(200);
                        return $this->response(['status' => true, 'message' => 'Update successfully']);
                    } else {
                        throw new Exception('Update failed: Nothing changes');
                    }
                } else {
                    throw new Exception('Category does not exsit');
                }
            } catch (Exception $e) {
                $this->status(400);
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['customer'])) {
            $this->status(403);
            return $this->response(['status' => false, 'message' => 'Not Authorized']);
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }
}
