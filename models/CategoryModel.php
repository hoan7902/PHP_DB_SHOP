<?php
require_once('./models/Model.php');

class CategoryModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = 'Category';
    }
    public function insertCategory($data)
    {
        return $this->insert($data);
    }
}
