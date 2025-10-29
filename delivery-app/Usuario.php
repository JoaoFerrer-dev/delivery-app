<?php
require_once 'database.php';

class Usuario {
    protected $conn;
    protected $table_name = "usuarios";

    public $idUsuario;
    public $nome;
    public $email;
    public $senha;
    public $tipo;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($email, $senha) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch();
            // Para demo, senha fixa - em produção usar password_verify()
            if($senha == "senha123" || password_verify($senha, $row['senha'])) {
                $this->idUsuario = $row['idUsuario'];
                $this->nome = $row['nome'];
                $this->email = $row['email'];
                $this->tipo = $row['tipo'];
                return true;
            }
        }
        return false;
    }

    public function cadastrar($nome, $email, $senha, $tipo = 'cliente') {
        // Verificar se email já existe
        if($this->emailExiste($email)) {
            return "Erro: Este email já está cadastrado.";
        }

        $query = "INSERT INTO " . $this->table_name . " 
                 SET nome=:nome, email=:email, senha=:senha, tipo=:tipo";

        $stmt = $this->conn->prepare($query);

        // Limpar dados
        $nome = htmlspecialchars(strip_tags($nome));
        $email = htmlspecialchars(strip_tags($email));
        
        // Hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha_hash);
        $stmt->bindParam(":tipo", $tipo);

        if($stmt->execute()) {
            return "Sucesso: Cadastro realizado com sucesso!";
        } else {
            return "Erro: Não foi possível realizar o cadastro.";
        }
    }

    private function emailExiste($email) {
        $query = "SELECT idUsuario FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function listarUsuarios() {
        $query = "SELECT idUsuario, nome, email, tipo, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>