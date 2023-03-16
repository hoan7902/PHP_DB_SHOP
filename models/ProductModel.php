<?php
require_once("./models/Model.php");

class ProductModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'Product';
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
        $sql = "SELECT `Category`.`categoryId`, `Category`.`name`, `Category`.`description` FROM `Category`
        INNER JOIN `ProductsInCategory` ON `Category`.`categoryId` = `ProductsInCategory`.`categoryId`
        INNER JOIN `Product` ON `ProductsInCategory`.`productId` = `Product`.`productId` WHERE `ProductsInCategory`.`productId` = $prodcutId;";
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
        return $data;
    }
}
