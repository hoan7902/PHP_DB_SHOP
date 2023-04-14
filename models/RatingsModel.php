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
    public function canRating($userId, $productId)
    {
        $sql = "
            SELECT * FROM Users  u
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
}
