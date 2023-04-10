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
        if ($sortBy == 'price' && $orderBy == 'asc') {
            $sortBy = 'minPrice';
        } else if ($sortBy == 'price' && $orderBy == 'desc') {
            $sortBy = 'maxPrice';
        }
        if (is_array($categories) && count($categories) > 0) {
            $catsString = "(" . implode(", ", $categories) . ")";
        } else {
            $catsString = "()";
        }
        $offset = ($page - 1) * $limit;
        if ($catsString == '()') {
            $sql = "
            SELECT 
                Products.productId, 
                Products.createdAt, 
                Products.name,
                Products.description, 
                MIN(Sizes.price) as minPrice, 
                MAX(Sizes.price) as maxPrice
            FROM 
                Sizes 
                INNER JOIN Products ON Sizes.productId = Products.productId 
                INNER JOIN ProductsInCategories ON ProductsInCategories.productId = Products.productId
            WHERE 
                Products.deleted = 0 
            GROUP BY 
                products.productId
            HAVING 
                minPrice >= {$minPrice} AND maxPrice <= {$maxPrice}
            ORDER BY 
                {$sortBy} {$orderBy}
            LIMIT {$limit}
            OFFSET {$offset};
            ";
        } else {
            $sql = "
            SELECT 
                Products.productId, 
                Products.createdAt, 
                Products.name,
                Products.description, 
                MIN(Sizes.price) as minPrice, 
                MAX(Sizes.price) as maxPrice
            FROM 
                Sizes 
                INNER JOIN Products ON Sizes.productId = Products.productId 
                INNER JOIN ProductsInCategories ON ProductsInCategories.productId = Products.productId
            WHERE 
                Products.deleted = 0
                AND ProductsInCategories.categoryId in {$catsString}
            GROUP BY 
                products.productId
            HAVING 
                minPrice >= {$minPrice} AND maxPrice <= {$maxPrice}
            ORDER BY 
                minPrice ASC
            LIMIT {$limit}
            OFFSET {$offset};
            ";
        }
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            array_push($data, $row);
        }
        return $data;
    }
}