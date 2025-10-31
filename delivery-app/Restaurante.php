<?php
require_once 'database.php';

class Restaurante {
    private $conn;
    private $table_name = "restaurantes";
    private $cardapio_table = "cardapio";
    private $produtos_table = "produtos_predefinidos";
    private $categorias_table = "categorias";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Cadastrar restaurante
    public function cadastrar($usuario_id, $nome, $endereco, $telefone, $cnpj, $nicho) {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET usuario_id=:usuario_id, nome=:nome, endereco=:endereco, 
                     telefone=:telefone, cnpj=:cnpj, nicho=:nicho";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":endereco", $endereco);
        $stmt->bindParam(":telefone", $telefone);
        $stmt->bindParam(":cnpj", $cnpj);
        $stmt->bindParam(":nicho", $nicho);

        if($stmt->execute()) {
            $restaurante_id = $this->conn->lastInsertId();
            $this->popularCardapioPredefinido($restaurante_id, $nicho);
            return $restaurante_id;
        }
        return false;
    }

    // Popular cardápio com produtos pré-definidos baseado no nicho
    private function popularCardapioPredefinido($restaurante_id, $nicho) {
        $query = "INSERT INTO " . $this->cardapio_table . " 
                  (restaurante_id, produto_id, nome, descricao, preco, categoria, is_predefinido)
                  SELECT :restaurante_id, idProduto, nome, descricao, preco, 
                         (SELECT nome FROM categorias WHERE idCategoria = produtos_predefinidos.categoria_id), TRUE 
                  FROM produtos_predefinidos 
                  WHERE nicho = :nicho";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->bindParam(":nicho", $nicho);
        return $stmt->execute();
    }

    // Adicionar item personalizado ao cardápio
    public function adicionarItemCardapio($restaurante_id, $nome, $descricao, $preco, $categoria) {
        $query = "INSERT INTO " . $this->cardapio_table . " 
                 SET restaurante_id=:restaurante_id, nome=:nome, descricao=:descricao, 
                     preco=:preco, categoria=:categoria, is_predefinido=FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":preco", $preco);
        $stmt->bindParam(":categoria", $categoria);

        return $stmt->execute();
    }

    // Listar cardápio do restaurante
    public function listarCardapio($restaurante_id) {
        $query = "SELECT * FROM " . $this->cardapio_table . " 
                  WHERE restaurante_id = :restaurante_id AND disponivel = 1
                  ORDER BY categoria, nome";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();
        return $stmt;
    }

    // Listar restaurantes por nicho
    public function listarRestaurantesPorNicho($nicho = null) {
        if($nicho) {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE nicho = :nicho ORDER BY nome";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nicho", $nicho);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Obter restaurante por ID
    public function getRestaurante($restaurante_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE idRestaurante = :restaurante_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":restaurante_id", $restaurante_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Listar categorias por nicho
    public function listarCategoriasPorNicho($nicho) {
        $query = "SELECT * FROM " . $this->categorias_table . " 
                  WHERE nicho = :nicho ORDER BY nome";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nicho", $nicho);
        $stmt->execute();
        return $stmt;
    }
}
?>