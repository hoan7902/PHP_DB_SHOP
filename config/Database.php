<?php

require_once('./config/configDB.php');

class Database
{
    private $host = HOST;
    private $username = USERNAME;
    private $password = PASSWORD;
    private $database = DATABASE;

    protected $conn;

    public function connect()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Connect database failed: " . $this->conn->connect_error);
        }
        return $this->conn;
    }

    public function close()
    {
        $this->conn->close();
    }
}
