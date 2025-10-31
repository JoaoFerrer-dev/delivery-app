<?php
session_start();
require_once 'Restaurante.php';
require_once 'Pedido.php';

if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 'cliente') {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$restaurante = new Restaurante();
$pedido_class = new Pedido();

$restaurante_id = $_GET['restaurante_id'] ?? null;
$restaurante_info = null;
$cardapio = null;
$carrinho = $_SESSION['carrinho'] ?? [];

if($restaurante_id) {
    $restaurante_info = $restaurante->getRestaurante($restaurante_id);
    $cardapio = $restaurante->listarCardapio($restaurante_id);
}

// Adicionar item ao carrinho
if(isset($_POST['adicionar_carrinho'])) {
    $item_id = $_POST['item_id'];
    $quantidade = $_POST['quantidade'];
    $observacoes = $_POST['observacoes'] ?? '';
    
    // Buscar informações do item
    $query = "SELECT * FROM cardapio WHERE idItem = :item_id";
    $stmt = $restaurante->conn->prepare($query);
    $stmt->bindParam(":item_id", $item_id);
    $stmt->execute();
    $item = $stmt->fetch();
    
    if($item) {
        $carrinho[] = [
            'item_id' => $item_id,
            'nome' => $item['nome'],
            'preco' => $item['preco'],
            'quantidade' => $quantidade,
            'observacoes' => $observacoes
        ];
        $_SESSION['carrinho'] = $carrinho;
    }
}

// Remover item do carrinho
if(isset($_GET['remover'])) {
    $index = $_GET['remover'];
    if(isset($carrinho[$index])) {
        unset($carrinho[$index]);
        $carrinho = array_values($carrinho); // Reindexar array
        $_SESSION['carrinho'] = $carrinho;
    }
}

// Finalizar pedido
if(isset($_POST['finalizar_pedido'])) {
    $endereco_entrega = $_POST['endereco_entrega'];
    $observacoes_gerais = $_POST['observacoes_gerais'] ?? '';
    
    if(!empty($carrinho) && $restaurante_id) {
        $resultado = $pedido_class->criarPedido(
            $usuario['id'],
            $restaurante_id,
            $carrinho,
            $endereco_entrega,
            $observacoes_gerais
        );
        
        if($resultado) {
            // Limpar carrinho
            $_SESSION['carrinho'] = [];
            $carrinho = [];
            
            // Redirecionar para página de confirmação
            header("Location: pedido_confirmado.php?pedido_id=" . $resultado['pedido_id']);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer Pedido - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .fazer-pedido-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .cardapio-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .carrinho-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .categoria-cardapio {
            margin-bottom: 30px;
        }
        
        .categoria-titulo {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .item-cardapio {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-acoes {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .quantidade-input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        
        .carrinho-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 10px;
        }
        
        .btn-remover {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .total-carrinho {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
        
        .form-endereco {
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .fazer-pedido-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍕 Fazer Pedido</h1>
            <div>
                <a href="restaurantes.php" class="btn">← Voltar</a>
                <a href="index.php?logout=true" class="btn btn-logout">🚪 Sair</a>
            </div>
        </div>

        <?php if($restaurante_info): ?>
        <div class="restaurante-info">
            <h2><?php echo $restaurante_info['nome']; ?></h2>
            <p>📍 <?php echo $restaurante_info['endereco']; ?> | 📞 <?php echo $restaurante_info['telefone']; ?></p>
        </div>

        <div class="fazer-pedido-container">
            <!-- Cardápio -->
            <div class="cardapio-section">
                <h3>📋 Cardápio</h3>
                
                <?php
                if($cardapio->rowCount() > 0) {
                    $categoria_atual = '';
                    while ($item = $cardapio->fetch()) {
                        if($item['categoria'] != $categoria_atual) {
                            if($categoria_atual != '') echo '</div>';
                            echo '<div class="categoria-cardapio">';
                            echo '<div class="categoria-titulo">' . $item['categoria'] . '</div>';
                            $categoria_atual = $item['categoria'];
                        }
                        ?>
                        <div class="item-cardapio">
                            <div class="item-info">
                                <h4><?php echo $item['nome']; ?></h4>
                                <p><?php echo $item['descricao']; ?></p>
                                <p><strong>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></strong></p>
                            </div>
                            <div class="item-acoes">
                                <form method="POST" class="form-adicionar">
                                    <input type="hidden" name="item_id" value="<?php echo $item['idItem']; ?>">
                                    <input type="number" name="quantidade" value="1" min="1" max="10" class="quantidade-input">
                                    <textarea name="observacoes" placeholder="Observações" rows="2" style="width: 150px; margin: 5px 0;"></textarea>
                                    <button type="submit" name="adicionar_carrinho" class="btn btn-success">➕ Add</button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div>';
                } else {
                    echo '<p>Cardápio não disponível.</p>';
                }
                ?>
            </div>

            <!-- Carrinho -->
            <div class="carrinho-section">
                <h3>🛒 Seu Carrinho</h3>
                
                <?php if(empty($carrinho)): ?>
                    <p>Seu carrinho está vazio.</p>
                <?php else: ?>
                    <?php
                    $total = 0;
                    foreach($carrinho as $index => $item):
                        $subtotal = $item['preco'] * $item['quantidade'];
                        $total += $subtotal;
                    ?>
                    <div class="carrinho-item">
                        <div>
                            <strong><?php echo $item['nome']; ?></strong>
                            <br>
                            <small>Qtd: <?php echo $item['quantidade']; ?> x R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></small>
                            <?php if($item['observacoes']): ?>
                                <br><small>Obs: <?php echo $item['observacoes']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></strong>
                            <br>
                            <a href="?restaurante_id=<?php echo $restaurante_id; ?>&remover=<?php echo $index; ?>" class="btn-remover">❌</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="total-carrinho">
                        Total: R$ <?php echo number_format($total, 2, ',', '.'); ?>
                    </div>

                    <!-- Formulário de finalização -->
                    <form method="POST" class="form-endereco">
                        <div class="form-group">
                            <label><strong>📍 Endereço de Entrega:</strong></label>
                            <input type="text" name="endereco_entrega" required 
                                   placeholder="Ex: Rua Exemplo, 123 - Bairro - Cidade"
                                   value="<?php echo $_SESSION['usuario']['endereco'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label><strong>📝 Observações Gerais:</strong></label>
                            <textarea name="observacoes_gerais" rows="3" placeholder="Alguma observação adicional?"></textarea>
                        </div>
                        <button type="submit" name="finalizar_pedido" class="btn btn-primary btn-block">
                            ✅ Finalizar Pedido
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
            <div class="alert alert-error">
                Restaurante não encontrado. <a href="restaurantes.php">Voltar para restaurantes</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>