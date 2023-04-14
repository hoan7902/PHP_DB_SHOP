<?php

require_once("./utils/RestApi.php");
require_once("./utils/JWTHelper.php");
require_once("./utils/HandleUri.php");

class RatingsController extends Controller
{
    private $ratingsModel;
    public function __construct()
    {
        $this->ratingsModel = $this->model("RatingsModel");
    }
    public function createRating()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['customer', 'self', 'admin'])) {
            try {
                $userId = getUserId($authHeader);
                $params = HandleUri::sliceUri();
                $star = RestApi::bodyData('star');
                $comment = RestApi::bodyData('comment');
                $productId = $params ? ((int)$params[2] >= 0 ? (int)$params[2] : null) : null;
                if ($this->ratingsModel->canRating($userId, $productId)) {
                    if (!$this->ratingsModel->alreadyRated($userId, $productId)) {
                        if ($star && (int)$star >= 1 && (int)$star <= 5) {
                            $star = (int)$star;
                        } else {
                            throw new Exception('Star must be in range [1, 5]');
                        }
                        $this->ratingsModel->createARating($userId, $productId, $star, $comment);
                        if (mysqli_affected_rows($this->ratingsModel->getConn()) > 0) {
                            $this->status(201);
                            return $this->response(['status' => true, 'message' => 'Rating successfully']);
                        } else {
                            $this->status(400);
                            throw new Exception('Rating Error');
                        }
                    } else {
                        throw new Exception("You rated this product");
                    }
                } else {
                    throw new Exception('You can not rate this product');
                }
            } catch (Exception $e) {
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }
    public function editRating()
    {
        $authHeader = RestApi::headerData('Authorization');
        $role = authHeader($authHeader);
        if (in_array($role, ['customer', 'self', 'admin'])) {
            try {
                $userId = getUserId($authHeader);
                $params = HandleUri::sliceUri();
                $star = RestApi::bodyData('star');
                $comment = RestApi::bodyData('comment');
                $productId = $params ? ((int)$params[2] >= 0 ? (int)$params[2] : null) : null;
                if ($this->ratingsModel->canRating($userId, $productId)) {
                    if ($this->ratingsModel->alreadyRated($userId, $productId)) {
                        $updateStar = false;
                        $updateComment = false;
                        if ($star !== null) {
                            $updateStar = true;
                            if ($star && (int)$star >= 1 && (int)$star <= 5) {
                                $star = (int)$star;
                            } else {
                                throw new Exception('Star must be in range [1, 5]');
                            }
                        }
                        if ($comment !== null) {
                            $updateComment = true;
                        }
                        $data = [];
                        if ($updateStar) {
                            $data['star'] = $star;
                        }
                        if ($updateComment) {
                            $data['comment'] = $comment;
                        }
                        $this->ratingsModel->updateARating(['userId' => $userId, 'productId' => $productId], $data);
                        if (mysqli_affected_rows($this->ratingsModel->getConn()) > 0) {
                            $this->status(200);
                            return $this->response(['status' => true, 'message' => 'Update rating successfully']);
                        } else {
                            $this->status(400);
                            throw new Exception('Rating failed');
                        }
                    } else {
                        throw new Exception("You have not rated this product");
                    }
                } else {
                    throw new Exception('You can not rate this product');
                }
            } catch (Exception $e) {
                return $this->response(['status' => false, 'message' => $e->getMessage()]);
            }
        } else if (in_array($role, ['Not Authenticated'])) {
            $this->status(401);
            return $this->response(['status' => false, 'message' => 'Not Authenticated']);
        }
    }
}
