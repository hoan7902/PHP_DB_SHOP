<?php
require_once('./models/Model.php');

class ProductsInCategoryModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'ProductsInCategory';
    }
    public function insertProductsInCategory($productId, $categories)
    {
        $data = [];
        foreach ($categories as $cat) {
            array_push($data, ["productId" => $productId, "categoryId" => $cat]);
        }
        return $this->insertMul(['productId', 'categoryId'], $data);
    }
}
