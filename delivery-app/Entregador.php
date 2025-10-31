<?php
require_once 'database.php';

class Entregador {
    private $conn;
    private $table_name = "entregadores";
    private $pedidos_table = "pedidos";

    public $idEntregador;
    public $usuario_id;
    public $veiculo;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function aceitarEntrega($pedido_id, $entregador_id) {
        $query = "UPDATE " . $this->pedidos_table . " 
                  SET entregador_id = :entregador_id, status = 'em_entrega'
                  WHERE idPedido = :pedido_id AND status = 'pronto'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":entregador_id", $entregador_id);
        $stmt->bindParam(":pedido_id", $pedido_id);

        return $stmt->execute();
    }
}
?>