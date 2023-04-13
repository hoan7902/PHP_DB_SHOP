<?php

require_once("./models/Model.php");

class ImagesModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "Images";
    }
    public function insertImages($productId, $imgs)
    {
        $str = "";
        if (count($imgs) == 1) {
            $str = "$imgs[0]";
        } else {
            for ($i = 0; $i < count($imgs); $i++) {
                if ($i == 0) {
                    $str = "$imgs[0]'), (";
                } else if ($i > 0 && $i < count($imgs) - 1) {
                    $str = $str . "'$productId', " . "'$imgs[$i]'), (";
                } else {
                    $str = $str . "'$productId', " . "'$imgs[$i]";
                }
            }
        }
        return $this->insert(['productId' => $productId, 'imageLink' => $str]);
    }
    public function deleteImages($keys)
    {
        return $this->delete($keys);
    }
    public function getByProductId($productId, $selects)
    {
        return $this->getBy(['productId' => $productId], $selects);
    }
    public function getImages($productId)
    {
        return $this->getBy(['productId' => $productId]);
    }
}
