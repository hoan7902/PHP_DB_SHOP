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
        $data = $sizes;
        for ($j = 0; $j < count($sizes); $j++) {
            $data[$j]['productId'] = $productId;
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
