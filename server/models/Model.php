<?php
require_once('./config/Database.php');
class Model extends Database
{
    protected $table;
    public function query($sql)
    {
        $this->connect();
        return $this->conn->query($sql);
    }
    public function getAll($selects = ['*'], $orderBys = [], $isLimited = false, $limit = 24)
    {
        $this->connect();
        $columns = implode(', ', $selects);
        $orderByString = implode(' ', $orderBys);
        if ($orderByString) {
            $sql = "SELECT $columns FROM $this->table ORDER BY $orderByString";
        } else {
            $sql = "SELECT $columns FROM $this->table LIMIT $limit ";
        }
        if ($isLimited) {
            $sql .= " LIMIT $limit";
        }
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            array_push($data, $row);
        }
        $this->close();
        return $data;
    }
    public function getBy($keys = [])
    {
        $sql = "SELECT * FROM $this->table WHERE ";
        $where = [];
        foreach ($keys as $key => $value) {
            array_push($where, "$key = '$value' ");
        }
        $sql .= implode('AND ', $where);
        $sql .= '; ';
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            array_push($data, $row);
        }
        return $data;
    }
    public function insert($arrayData = [])
    {
        foreach ($arrayData as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }
        $keys = implode(' , ', $keys);
        $values = "'" . implode("' , '", $values) . "'";
        $sql = "INSERT INTO $this->table ($keys) VALUES ($values) ; ";
        $query = $this->query($sql);
        return $query;
    }
}
