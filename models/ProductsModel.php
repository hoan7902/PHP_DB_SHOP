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
    public function deleteByHideProduct($prodcutId)
    {
        return $this->updateOne(['productId' => $prodcutId], ['deleted' => 1]);
    }
    public function getById($productId, $selects, $deleted = false)
    {
        if ($deleted) {
            return $this->getBy(['productId' => $productId], $selects);
        }
        return $this->getBy(['productId' => $productId, 'deleted' => 0], $selects);
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
        $sql = "SELECT Products.productId, Products.createdAt, Products.name, Products.description, MIN(Sizes.price) as minPrice, MAX(Sizes.price) as maxPrice FROM Sizes INNER JOIN Products ON Sizes.productId = Products.productId INNER JOIN Productsincategories ON Productsincategories.productId = Products.productId WHERE Products.deleted = 0 GROUP BY products.productId ORDER BY minPrice ASC";
        return false;
    }
}
