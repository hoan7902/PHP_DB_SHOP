<?php

require_once("./models/Model.php");

class RatingsModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "UsersRatingProducts";
    }
    public function getARating($userId, $productId)
    {
        return $this->getBy(['userId' => $userId, 'productId' => $productId]);
    }
    public function createARating($userId, $productId, $star, $comment)
    {
        return $this->insert(['userId' => $userId, 'productId' => $productId, 'star' => $star, 'comment' => $comment]);
    }
    public function updateARating($condition, $data)
    {
        return $this->updateOne($condition, $data);
    }
    public function getRatingPointAProduct($productId)
    {
        $sql = "SELECT AVG(star) as ratingPoint, COALESCE(COUNT(*), 0) as numberRating FROM UsersRatingProducts 
        WHERE productId = {$productId};";
        $query = $this->query($sql);
        $data = ['ratingPoint' => 0, 'numberRating' => 0];
        if ($query) {
            $data = mysqli_fetch_assoc($query);
        }
        return $data;
    }
    public function myRating($userId, $orderBy, $limit = 12, $frame = 1)
    {
        $offset = ($frame - 1) * $limit;
        $sql = "
            SELECT ur.userId, ur.productId, ur.star, ur.comment, ur.time, p.name, p.description, p.createdAt, p.quantity FROM UsersRatingProducts ur
            INNER JOIN Products p ON p.productId = ur.productId
            WHERE p.deleted = 0 AND ur.userId = {$userId}
            ORDER BY ur.time {$orderBy}
            LIMIT {$limit}
            OFFSET {$offset}
        ;";
        $query = $this->query($sql);
        $data = [];
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                array_push($data, $row);
            }
        }
        return $data;
    }
    public function myRatingCount($userId)
    {
        $sql = "
            SELECT ur.userId, ur.productId, ur.star, ur.comment, ur.time, p.name, p.description, p.createdAt, p.quantity FROM UsersRatingProducts ur
            INNER JOIN Products p ON p.productId = ur.productId
            WHERE p.deleted = 0 AND ur.userId = {$userId}
        ";
        $query = $this->query($sql);
        if ($query) {
            return $query->num_rows;
        }
        return 0;
    }
    public function canRating($userId, $productId)
    {
        $sql = "
            SELECT * FROM Users u
            INNER JOIN UsersHaveOrders uo ON u.userId = uo.userId
            INNER JOIN ProductsInOrders po ON po.orderId = uo.orderId
            WHERE uo.userId = {$userId}  AND po.productId = {$productId};
        ";
        $query = $this->query($sql);
        if ($query && $query->num_rows > 0) {
            return true;
        }
        return false;
    }
    public function alreadyRated($userId, $productId)
    {
        $sql = "
            SELECT * FROM UsersRatingProducts WHERE userId = {$userId} AND productId = {$productId};
        ";
        $query = $this->query($sql);
        if ($query && $query->num_rows > 0) {
            return true;
        }
        return false;
    }
    public function getRatingsOfProduct($productId, $limit, $frame)
    {
        $offset = ($frame - 1) * $limit;
        $sql = "
            SELECT urp.userId, u.name, urp.star, urp.comment, urp.time, u.avatar FROM UsersRatingProducts urp
            INNER JOIN Users u ON u.userId = urp.userId
            WHERE urp.productId = {$productId}
            ORDER BY urp.time DESC
            LIMIT {$limit}
            OFFSET {$offset};
        ";
        $query = $this->query($sql);
        $data = [];
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $row['star'] = (int)$row['star'];
                $row['userId'] = (int)$row['userId'];
                array_push($data, $row);
            }
        }
        return $data;
    }
}
