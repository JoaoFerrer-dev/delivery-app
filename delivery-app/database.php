<?php
class Database {
    private $host = "localhost";
    private $db_name = "delivery_app";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Se o banco não existir, criar
            if($exception->getCode() == 1049) {
                $this->createDatabase();
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                    $this->username,
                    $this->password
                );
            } else {
                echo "Erro de conexão: " . $exception->getMessage();
            }
        }
        return $this->conn;
    }

    private function createDatabase() {
        try {
            $temp_conn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password
            );
            
            $sql = file_get_contents('database.sql');
            $temp_conn->exec($sql);
            
        } catch(PDOException $e) {
            die("Erro ao criar banco: " . $e->getMessage());
        }
    }
}
?>