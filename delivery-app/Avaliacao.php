<?php
require_once 'database.php';

class Avaliacao {
    private $conn;
    private $table_name = "avaliacoes";

    public $idAvaliacao;
    public $pedido_id;
    public $cliente_id;
    public $restaurante_id;
    public $nota;
    public $comentario;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
}
?>