<?php

require_once('./models/Model.php');

class SizesModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "Sizes";
    }
    public function insertSizes($productId, $sizes)
    {
        $data = [];

        for ($j = 0; $j < count($sizes); $j++) {
            if (is_object($sizes[$j])) {
                array_push($data, ['productId' => $productId, 'quantity' => $sizes[$j]->quantity, 'price' => $sizes[$j]->price, 'sizeName' => $sizes[$j]->sizeName]);
            } else {
                array_push($data, ['productId' => $productId, 'quantity' => $sizes[$j]['quantity'], 'price' => $sizes[$j]['price'], 'sizeName' => $sizes[$j]['sizeName']]);
            }
        }
        return $this->insertMul(['productId', 'sizeName', 'price', 'quantity'], $data);
    }
    public function deleteSizes($keys)
    {
        return $this->delete($keys);
    }
    public function getByProductId($productId, $selects)
    {
        return $this->getBy(['productId' => $productId], $selects);
    }
    public function getSize($productId, $sizeName, $selects = ['*'])
    {
        return $this->getBy(['productId' => $productId, 'sizeName' => $sizeName], $selects);
    }
    public function updateQuantity($productId, $sizeName, $quantity)
    {
        return $this->updateOne(['productId' => $productId, 'sizeName' => $sizeName], ['quantity' => $quantity]);
    }
    public function changeQuantity($productId, $sizeName, $number, $increase = true)
    {
        try {
            $number = (int)$number;
            if ($number > 0) {
                if ($increase) {
                    $sign = "+";
                } else {
                    $sign = "-";
                }
                $sql = "UPDATE {$this->table} SET quantity = quantity {$sign} {$number} WHERE productId = {$productId} AND sizeName = '{$sizeName}';";
                $query = $this->query($sql);
                if ($query) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
