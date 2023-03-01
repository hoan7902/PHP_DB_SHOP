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
    public function getAll($orderBys = [], $isLimited = false, $limit = 16)
    {
        $this->connect();
        $orderByString = implode(' ', $orderBys);
        if ($orderByString) {
            $sql = "SELECT * FROM $this->table WHERE status = '1' ORDER BY $orderByString";
        } else {
            $sql = "SELECT * FROM $this->table WHERE status = '1' LIMIT $limit ";
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
            array_push($where, "$key => '$value' ");
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
        echo $sql;
        $query = $this->query($sql);
        return $query;
    }
}
