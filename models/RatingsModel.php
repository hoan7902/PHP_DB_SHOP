<?php

require_once("./models/Model.php");

class RatingsModel extends Model
{
    protected $table;
    public function __construct()
    {
        $this->table = "Ratings";
    }
}
