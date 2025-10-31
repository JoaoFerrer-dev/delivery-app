<?php
require_once 'database.php';

class Prato {
    private $conn;
    private $table_name = "pratos";

    public $idPrato;
    public $restaurante_id;
    public $nome;
    public $descricao;
    public $preco;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function listarPratos() {
        $query = "SELECT p.*, r.nome as restaurante_nome 
                  FROM " . $this->table_name . " p
                  JOIN restaurantes r ON p.restaurante_id = r.idRestaurante
                  WHERE p.disponivel = 1
                  ORDER BY r.nome, p.nome";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>