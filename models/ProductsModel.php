<?php
require_once("./models/Model.php");

class ProductsModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'Products';
    }
    public function insertProduct($arrayData)
    {
        return $this->insert($arrayData);
    }
    public function deleteProduct($productId)
    {
        return $this->delete(['productId' => $productId]);
    }
    public function getById($productId, $selects)
    {
        return $this->getBy(['productId' => $productId], $selects);
    }
    public function getCategoriesOfProduct($prodcutId)
    {
        $sql = "SELECT `Categories`.`categoryId`, `Categories`.`name`, `Categories`.`description` FROM `Categories`
        INNER JOIN `ProductsInCategories` ON `Categories`.`categoryId` = `ProductsInCategories`.`categoryId`
        INNER JOIN `Products` ON `ProductsInCategories`.`productId` = `Products`.`productId` WHERE `ProductsInCategories`.`productId` = $prodcutId;";
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
        return $data;
    }
    public function getProducts($sortBy, $orderBy, $limit, $page, $minPrice, $maxPrice, $categories, $collections)
    {
        if ($sortBy == 'price') {
            $sql = "SELECT Product.productId, Product.createdAt, Product.name, Product.description, MIN(Size.price) as minPrice FROM Size
            INNER JOIN Product ON Size.productId = Product.productId
            INNER JOIN Productsincategory ON Productsincategory.productId = Product.productId
            WHERE Product.deleted = 0
            GROUP BY product.productId
            ORDER BY minPrice $orderBy";
        } else if ($sortBy == 'order_count') {
        } else {
        }
    }
}
