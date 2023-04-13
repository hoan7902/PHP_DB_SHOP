<?php
require_once("./models/Model.php");

class CartsModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "Carts";
    }
    public function addToCart($userId, $productId)
    {
        return $this->insert(['userId' => $userId, 'productId' => $productId]);
    }
    public function removeFromCartByCartId($cartId)
    {
        return $this->delete(['cartId' => $cartId]);
    }
    public function removeFromCartByUserId($userId)
    {
        return $this->delete(['userId' => $userId]);
    }
    public function removeOneFromCart($userId, $productId)
    {
        return $this->delete(['userId' => $userId, 'productId' => $productId]);
    }
    public function getProductsInCart($userId, $limit = 12, $frame = 1)
    {
        $offset = ($frame - 1) * $limit;
        $sql = "
            SELECT c.productId, c.time, p.name, p.description, i.imageLink FROM Carts c
            INNER JOIN Products p ON c.productId = p.productId
            INNER JOIN Images i ON c.productId = i.productId
            WHERE c.userId = {$userId}
            GROUP BY c.productId
            ORDER BY c.time DESC
            LIMIT {$limit}
            OFFSET {$offset}
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
