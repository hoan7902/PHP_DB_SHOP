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
}
