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
    public function getAll($selects = ['*'], $orderBys = [], $isLimited = false, $limit = 24, $frame = 1, $order = 'DESC')
    {
        $columns = implode(', ', $selects);
        $orderByString = implode(' ', $orderBys) . " " . $order;
        $offset = ($frame - 1) * $limit;
        if (count($orderBys) > 0) {
            $sql = "SELECT $columns FROM $this->table ORDER BY $orderByString";
        } else {
            $sql = "SELECT $columns FROM $this->table";
        }
        if ($isLimited) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            array_push($data, $row);
        }
        return $data;
    }
    public function getBy($keys = [], $selects = ['*'])
    {
        $columns = implode(', ', $selects);
        $sql = "SELECT $columns FROM $this->table WHERE ";
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
    public function insertMul($keys, $values)
    {
        $keyStr = implode(", ", $keys);
        $str = [];
        $temp = null;
        for ($i = 0; $i < count($values); $i++) {
            $subStr = "";
            for ($j = 0; $j < count($keys); $j++) {
                $temp = $values[$i]["$keys[$j]"];
                if ($j == 0) {
                    $subStr = "('$temp";
                } else if ($j > 0 && $j < count($keys) - 1) {
                    $subStr = $subStr . "', '$temp";
                } else {
                    $subStr = $subStr . "', '$temp')";
                }
            }
            array_push($str, $subStr);
        }
        $str = implode(", ", $str);
        $sql = "INSERT INTO $this->table ($keyStr) VALUES $str ;";
        $query = $this->query($sql);
        return $query;
    }
    public function getNRecords($selects = ['*'], $keys = [],  $orderBys = [], $frame = 1, $limit = 24)
    {
        $columns = implode(', ', $selects);
        $orderBysString = implode(' ', $orderBys);
        $whereArray = [];
        foreach ($keys as $key => $value) {
            array_push($whereArray, "$key = '$value' ");
        }
        $where = implode('AND ', $whereArray);
        if ($where != '') {
            $where = "WHERE " . $where;
        }
        $offset = ($frame - 1) * $limit;
        $sql = "SELECT $columns FROM $this->table $where ORDER BY $orderBysString LIMIT $limit OFFSET $offset ;";
        $query = $this->query($sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            array_push($data, $row);
        }
        return $data;
    }
    public function updateOne(array $condition, array $data)
    {
        try {
            $whereArr = [];
            $changeArr = [];
            foreach ($condition as $key => $value) {
                array_push($whereArr, "$key = '$value' ");
            }
            foreach ($data as $key => $value) {
                array_push($changeArr, "$key = '$value' ");
            }
            $whereStr = implode("AND ", $whereArr);
            $whereStr = $whereStr != "" ? "WHERE " . $whereStr : "";
            $setStr = implode(", ", $changeArr);
            $setStr = $setStr != "" ? "SET " . $setStr : "";
            if ($whereStr == "" || $setStr == "") {
                return false;
            }
            $sql = "UPDATE $this->table $setStr $whereStr ;";
            $query = $this->query($sql);
            if ($query) {
                return mysqli_affected_rows($this->conn);
            }
            return 0;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function delete($keys)
    {
        $whereArr = [];
        foreach ($keys as $key => $value) {
            array_push($whereArr, "$key = '$value' ");
        }
        $whereStr = implode('AND ', $whereArr);
        $sql = "DELETE FROM $this->table WHERE $whereStr ;";
        $query = $this->query($sql);
        return $query;
    }
    public function getConn()
    {
        return $this->conn;
    }
}
