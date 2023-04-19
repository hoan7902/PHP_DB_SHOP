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
    public function getCategoriesOfProduct($productId)
    {
        $sql = "SELECT `Categories`.`categoryId`, `Categories`.`name`, `Categories`.`description` FROM `Categories`
        INNER JOIN `ProductsInCategories` ON `Categories`.`categoryId` = `ProductsInCategories`.`categoryId`
        INNER JOIN `Products` ON `ProductsInCategories`.`productId` = `Products`.`productId` WHERE `ProductsInCategories`.`productId` = $productId;";
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
        return $data;
    }
    public function getProducts($sortBy, $orderBy, $limit, $page, $minPrice, $maxPrice, $categories, $collections)
    {
        if ($sortBy == 'price' && $orderBy == 'ASC') {
            $sortBy = 'minPrice';
        } else if ($sortBy == 'price' && $orderBy == 'DESC') {
            $sortBy = 'minPrice';
        }
        $catsString = "";
        if (is_array($categories) && count($categories) > 0) {
            foreach ($categories as $value) {
                $catsString = $catsString . "AND ProductsInCategories.categoryId = '{$value}' ";
            }
        }
        $offset = ($page - 1) * $limit;
        if (in_array($sortBy, ['minPrice', 'maxPrice', 'createdAt'])) {
            $sql = "
                SELECT 
                    Products.productId, 
                    Products.createdAt, 
                    Products.name,
                    Products.description, 
                    MIN(Sizes.price) as minPrice, 
                    MAX(Sizes.price) as maxPrice,
                    COALESCE(SUM(DISTINCT po.quantity), 0) AS soldQuantity
                FROM 
                    Sizes 
                    INNER JOIN Products ON Sizes.productId = Products.productId 
                    INNER JOIN ProductsInCategories ON ProductsInCategories.productId = Products.productId
                    LEFT JOIN (SELECT productId, SUM(quantity) AS quantity FROM ProductsInOrders GROUP BY productId) po ON Products.productId = po.productId
                WHERE 
                    Products.deleted = 0
                    {$catsString}
                GROUP BY 
                    Products.productId
                HAVING 
                    minPrice >= {$minPrice} AND maxPrice <= {$maxPrice}
                ORDER BY 
                    {$sortBy} {$orderBy}
                LIMIT {$limit}
                OFFSET {$offset};
                ";
            $countSql = "
                SELECT 
                    Products.productId, 
                    Products.createdAt, 
                    Products.name,
                    Products.description, 
                    MIN(Sizes.price) as minPrice, 
                    MAX(Sizes.price) as maxPrice,
                    COALESCE(SUM(DISTINCT po.quantity), 0) AS soldQuantity
                FROM 
                    Sizes 
                    INNER JOIN Products ON Sizes.productId = Products.productId 
                    INNER JOIN ProductsInCategories ON ProductsInCategories.productId = Products.productId
                    LEFT JOIN (SELECT productId, SUM(quantity) AS quantity FROM ProductsInOrders GROUP BY productId) po ON Products.productId = po.productId
                WHERE 
                    Products.deleted = 0
                    {$catsString}
                GROUP BY 
                    products.productId
                HAVING 
                    minPrice >= {$minPrice} AND maxPrice <= {$maxPrice}
                ORDER BY 
                    {$sortBy} {$orderBy};
            ";
        } else if (in_array($sortBy, ['orderCount'])) {
            $sql = "
                SELECT 
                    p.productId, 
                    p.createdAt, 
                    p.name,
                    p.description, 
                    MIN(s.price) as minPrice, 
                    MAX(s.price) as maxPrice,
                    COALESCE(SUM(DISTINCT po.quantity), 0) AS soldQuantity
                FROM products p
                    LEFT JOIN (SELECT productId, SUM(quantity) AS quantity FROM ProductsInOrders GROUP BY productId) po ON p.productId = po.productId
                    INNER JOIN sizes s ON s.productId = p.productId
                    INNER JOIN ProductsInCategories pc ON pc.productId = p.productId
                WHERE 
                    p.deleted = 0 
                    {$catsString}
                GROUP BY p.productId
                HAVING minPrice >= {$minPrice} AND maxPrice <= {$maxPrice}
                ORDER BY `soldQuantity` {$orderBy}
                LIMIT {$limit}
                OFFSET {$offset};
            ";
            $countSql = "
                SELECT 
                    p.productId, 
                    p.createdAt, 
                    p.name,
                    p.description, 
                    MIN(s.price) as minPrice, 
                    MAX(s.price) as maxPrice,
                    COALESCE(SUM(DISTINCT po.quantity), 0) AS soldQuantity
                FROM products p
                    LEFT JOIN (SELECT productId, SUM(quantity) AS quantity FROM ProductsInOrders GROUP BY productId) po ON p.productId = po.productId
                    INNER JOIN sizes s ON s.productId = p.productId
                    INNER JOIN ProductsInCategories pc ON pc.productId = p.productId
                WHERE 
                    p.deleted = 0 
                    {$catsString}
                GROUP BY p.productId
                HAVING minPrice >= {$minPrice} AND maxPrice <= {$maxPrice}
                ORDER BY `soldQuantity` {$orderBy};
            ";
        }
        $query = $this->query($sql);
        $countQuery = $this->query($countSql);
        $count = $countQuery->num_rows;
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            array_push($data, $row);
        }
        $mergeData = ['count' => $count, 'data' => $data];
        return $mergeData;
    }
    public function isValidProduct($productId, $sizeName)
    {
        $sql = "
            SELECT * FROM products
            INNER JOIN sizes ON products.productId = sizes.productId
            WHERE products.productId = {$productId} AND sizes.sizeName = '{$sizeName}';
        ";
        $query = $this->query($sql);
        $data = [];
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                array_push($data, $row);
            }
        }
        return $data;
    }
}
