<?php
require_once("./models/Model.php");

class CartsModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "Carts";
    }
    public function addToCart($userId, $productId, $size, $quantity)
    {
        return $this->insert(['userId' => $userId, 'productId' => $productId, 'size' => $size, 'quantity' => $quantity]);
    }
    public function removeFromCartByCartId($cartId)
    {
        return $this->delete(['cartId' => $cartId]);
    }
    public function removeFromCartByUserId($userId)
    {
        return $this->delete(['userId' => $userId]);
    }
    public function removeOneFromCart($userId, $productId, $size)
    {
        return $this->delete(['userId' => $userId, 'productId' => $productId, 'size' => $size]);
    }
    public function updateProductInCart($userId, $productId, $size, $quantity)
    {
        return $this->updateOne(['userId' => $userId, 'productId' => $productId, 'size' => $size], ['quantity' => $quantity]);
    }
    public function getProductsInCart($userId, $limit = 12, $frame = 1)
    {
        $offset = ($frame - 1) * $limit;
        $sql = "
            SELECT c.*, s.price AS unitPrice, pi.name as name, pi.imageLink AS image FROM Carts c 
            INNER JOIN 
            (SELECT p.*, i.imageLink FROM Products p 
            INNER JOIN Images i ON p.productId = i.productId 
            GROUP BY p.productId) pi ON pi.productId = c.productId
            INNER JOIN Sizes s ON s.productId = pi.productId AND s.sizeName = c.size
            WHERE c.userId = {$userId}
            ORDER BY c.time DESC
            LIMIT {$limit}
            OFFSET {$offset};
        ";
        $query = $this->query($sql);
        $data = [];
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                array_push($data, $row);
            }
            return $data;
        }
        return $data;
    }
}
