<?php
session_start();
require_once 'Prato.php';
require_once 'Restaurante.php';

// Verificar se usuário está logado e é um restaurante
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 'restaurante') {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$prato = new Prato();
$restaurante = new Restaurante();

// Buscar ID do restaurante baseado no ID do usuário
$restaurante_info = $restaurante->buscarPorUsuarioId($usuario['id']);
$mensagem = '';

// Processar adição de novo prato
if(isset($_POST['adicionar_prato'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = str_replace(',', '.', $_POST['preco']);
    $categoria = $_POST['categoria'];
    
    if($prato->adicionar($restaurante_info['idRestaurante'], $nome, $descricao, $preco, $categoria)) {
        $mensagem = "<div class='alert alert-success'>✅ Prato adicionado com sucesso!</div>";
    } else {
        $mensagem = "<div class='alert alert-error'>❌ Erro ao adicionar prato.</div>";
    }
}

// Processar exclusão de prato
if(isset($_POST['excluir_prato'])) {
    $id_prato = $_POST['id_prato'];
    if($prato->excluir($id_prato, $restaurante_info['idRestaurante'])) {
        $mensagem = "<div class='alert alert-success'>✅ Prato excluído com sucesso!</div>";
    } else {
        $mensagem = "<div class='alert alert-error'>❌ Erro ao excluir prato.</div>";
    }
}

// Buscar pratos do restaurante
$pratos = $prato->listarPorRestaurante($restaurante_info['idRestaurante']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cardápio - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Gerenciar Cardápio</h1>
            <p>Restaurante: <?php echo $usuario['nome']; ?></p>
            <div>
                <a href="dashboard.php" class="btn btn-voltar">← Voltar</a>
                <a href="index.php?logout=true" class="btn btn-logout">🚪 Sair</a>
            </div>
        </div>

        <?php echo $mensagem; ?>

        <!-- Formulário para adicionar novo prato -->
        <div class="card">
            <h2>🍽️ Adicionar Novo Prato</h2>
            <form method="POST" class="form-cardapio">
                <div class="form-group">
                    <label>Nome do Prato:</label>
                    <input type="text" name="nome" required placeholder="Ex: Pizza Margherita">
                </div>
                
                <div class="form-group">
                    <label>Descrição:</label>
                    <textarea name="descricao" required placeholder="Descreva os ingredientes e o preparo"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Preço (R$):</label>
                    <input type="text" name="preco" required placeholder="29,90">
                </div>
                
                <div class="form-group">
                    <label>Categoria:</label>
                    <select name="categoria" required>
                        <option value="entrada">🥗 Entrada</option>
                        <option value="principal">🍽️ Prato Principal</option>
                        <option value="sobremesa">🍰 Sobremesa</option>
                        <option value="bebida">🥤 Bebida</option>
                    </select>
                </div>
                
                <button type="submit" name="adicionar_prato" class="btn btn-success">
                    ✨ Adicionar ao Cardápio
                </button>
            </form>
        </div>

        <!-- Lista de pratos -->
        <div class="card">
            <h2>📋 Meu Cardápio</h2>
            <div class="cardapio-grid">
                <?php
                if($pratos && $pratos->rowCount() > 0) {
                    while($prato = $pratos->fetch()) {
                        echo '
                        <div class="prato-card">
                            <div class="prato-header">
                                <h3>' . htmlspecialchars($prato['nome']) . '</h3>
                                <span class="categoria-' . $prato['categoria'] . '">' . 
                                    ucfirst($prato['categoria']) . '</span>
                            </div>
                            <p class="descricao">' . htmlspecialchars($prato['descricao']) . '</p>
                            <p class="preco">R$ ' . number_format($prato['preco'], 2, ',', '.') . '</p>
                            <form method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="id_prato" value="' . $prato['idPrato'] . '">
                                <button type="submit" name="excluir_prato" class="btn btn-danger btn-sm" 
                                        onclick="return confirm(\'Tem certeza que deseja excluir este prato?\')">
                                    🗑️ Excluir
                                </button>
                            </form>
                        </div>';
                    }
                } else {
                    echo '<p>Nenhum prato cadastrado ainda. Adicione seu primeiro prato!</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <style>
        .form-cardapio {
            max-width: 600px;
            margin: 0 auto;
        }
        .cardapio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        .prato-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .prato-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .prato-header h3 {
            margin: 0;
            color: #333;
        }
        .categoria-entrada { background: #4caf50; }
        .categoria-principal { background: #2196f3; }
        .categoria-sobremesa { background: #ff9800; }
        .categoria-bebida { background: #9c27b0; }
        [class^="categoria-"] {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        .preco {
            font-size: 1.2em;
            font-weight: bold;
            color: #2196f3;
            margin: 10px 0;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.9em;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
    </style>
</body>
</html>