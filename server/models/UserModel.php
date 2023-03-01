<?php

require_once("./models/Model.php");

class UserModel extends Model
{
    protected $table = 'users';

    public function insertUser($arrayData)
    {
        $result = $this->insert($arrayData);
        if ($result) {
            return true;
        } else {
            throw new Exception("Create user failed: " . $this->conn->error);
        }
    }
}
