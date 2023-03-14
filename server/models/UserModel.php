<?php

require_once("./models/Model.php");

class UserModel extends Model
{
    protected $table = 'User';

    public function insertUser($arrayData)
    {
        $result = $this->insert($arrayData);
        if ($result) {
            return true;
        } else {
            throw new Exception("Create user failed: " . $this->conn->error);
        }
    }
    public function getUserByEmail($email)
    {
        $users = $this->getBy(['email' => $email]);
        if (sizeof($users) == 1) {
            return $users[0];
        } else {
            return null;
        }
    }
    public function getUserById($id)
    {
        $users = $this->getBy(['userId' => $id]);
        if (sizeof($users) == 1) {
            return $users[0];
        } else {
            return null;
        }
    }
}
