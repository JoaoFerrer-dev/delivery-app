<?php
require_once 'database.php';

class Restaurante {
    private $conn;
    private $table_name = "restaurantes";
    private $pratos_table = "pratos";

    public $idRestaurante;
    public $usuario_id;
    public $nome;
    public $endereco;
    public $telefone;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function listarRestaurantes() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function listarPratos($restaurante_id) {
        $query = "SELECT * FROM " . $this->pratos_table . " 
                  WHERE restaurante_id = :restaurante_id AND disponivel = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();
        return $stmt;
    }
}
?>