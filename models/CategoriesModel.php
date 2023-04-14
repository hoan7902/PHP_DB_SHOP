<?php
require_once('./models/Model.php');

class CategoriesModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'Categories';
    }
    public function insertCategory($data)
    {
        return $this->insert($data);
    }
    public function updateCategory($categoryId, $data)
    {
        return $this->updateOne(['categoryId' => $categoryId], $data);
    }
    public function getCategory($categoryId)
    {
        return $this->getBy(['categoryId' => $categoryId]);
    }
}
