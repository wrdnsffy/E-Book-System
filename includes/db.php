<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql) {
        $result = $this->conn->query($sql);
        if ($result === false) {
            throw new Exception("Query failed: " . $this->conn->error);
        }
        return $result;
    }

    public function prepare($sql) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        return $stmt;
    }

    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

function db_connect() {
    return Database::getInstance()->getConnection();
}

function db_query($sql) {
    return Database::getInstance()->query($sql);
}

function db_prepare($sql) {
    return Database::getInstance()->prepare($sql);
}

function db_escape($string) {
    return Database::getInstance()->escape($string);
}
?>