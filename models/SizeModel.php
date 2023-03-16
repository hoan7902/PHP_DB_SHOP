<?php

require_once('./models/Model.php');

class SizeModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "Size";
    }
    public function insertSizes($productId, $sizes)
    {
        $data = $sizes;
        for ($j = 0; $j < count($sizes); $j++) {
            $data[$j]['productId'] = $productId;
        }
        return $this->insertMul(['productId', 'sizename', 'price', 'quantity'], $data);
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
