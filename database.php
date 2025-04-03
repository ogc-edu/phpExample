<?php

class Database
{
  private $host;
  private $dbName;
  private $username;
  private $password;
  public $conn;

  public function __construct($host, $username, $password, $dbName)
  {
    $this->host = $host;
    $this->username = $username;
    $this->password = $password;
    $this->dbName = $dbName;
    $this->connect(); //automatically connect when database object is created
  }

  public function connect()
  {
    $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->dbName);
    if (!$this->conn) {
      die("Connection Error" . mysqli_connect_error());
    }
    return $this->conn;
  }

  public function query($sql)
  {
    $result = mysqli_query($this->conn, $sql);
    if (!$result) {
      die("Query Error:" . mysqli_error($this->conn));
    }
    return $result;
  }

  public function close()
  {
    mysqli_close($this->conn);
  }

  public function fetchAll($result)
  {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
    }
    return $data;
  }
  public function getLastInsertId()
  {
    return mysqli_insert_id($this->conn);
  }

}