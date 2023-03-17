<?php
require_once('./models/Model.php');

class ProductsInCategoriesModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'ProductsInCategories';
    }
    public function insertProductsInCategory($productId, $categories)
    {
        $data = [];
        foreach ($categories as $cat) {
            array_push($data, ["productId" => $productId, "categoryId" => $cat]);
        }
        return $this->insertMul(['productId', 'categoryId'], $data);
    }
    public function deleteProductsInCategory($productId, $categoryId)
    {
        $keys = ['productId' => $productId, 'categoryId' => $categoryId];
        return $this->delete($keys);
    }
}
