<?php
session_start();
require_once 'Pedido.php';

if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 'entregador') {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$pedido_class = new Pedido();
$mensagem = '';

// Processar confirmação de entrega
if(isset($_POST['confirmar_entrega'])) {
    $pedido_id = $_POST['pedido_id'];
    $codigo_digitado = strtoupper($_POST['codigo_confirmacao']);
    
    $pedido = $pedido_class->getPedido($pedido_id);
    
    if($pedido && $pedido['codigo_entrega'] === $codigo_digitado) {
        if($pedido_class->confirmarEntrega($pedido_id, $codigo_digitado, $usuario['id'])) {
            $mensagem = "✅ Entrega confirmada com sucesso!";
        } else {
            $mensagem = "❌ Erro ao confirmar entrega.";
        }
    } else {
        $mensagem = "❌ Código inválido. Verifique e tente novamente.";
    }
}

// Listar entregas do entregador
$entregas = $pedido_class->listarPedidosEntregador($usuario['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Entrega - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .confirmar-entrega-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card-confirmacao {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .codigo-input {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 5px;
            padding: 15px;
            border: 3px solid #667eea;
            border-radius: 10px;
            width: 200px;
            margin: 20px auto;
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
        }
        
        .entregas-list {
            margin-top: 30px;
        }
        
        .entrega-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #4caf50;
        }
        
        .entrega-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .btn-confirmar {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .sem-entregas {
            text-align: center;
            padding: 40px;
            color: #666;
            background: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Confirmar Entrega</h1>
            <div>
                <a href="dashboard.php" class="btn">📊 Dashboard</a>
                <a href="pedidos.php" class="btn">📦 Pedidos</a>
                <a href="index.php?logout=true" class="btn btn-logout">🚪 Sair</a>
            </div>
        </div>

        <?php if($mensagem): ?>
            <div class="alert <?php echo strpos($mensagem, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="confirmar-entrega-container">
            <div class="card-confirmacao">
                <h2>🔢 Confirmação de Entrega</h2>
                <p>Peça ao cliente o código de entrega e digite abaixo:</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label><strong>Selecione o Pedido:</strong></label>
                        <select name="pedido_id" required class="form-control">
                            <option value="">-- Selecione um pedido --</option>
                            <?php while($entrega = $entregas->fetch()): ?>
                                <?php if($entrega['status'] == 'em_entrega'): ?>
                                    <option value="<?php echo $entrega['idPedido']; ?>">
                                        Pedido #<?php echo $entrega['idPedido']; ?> - 
                                        <?php echo $entrega['cliente_nome']; ?> - 
                                        R$ <?php echo number_format($entrega['valorTotal'], 2, ',', '.'); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Código de Entrega:</strong></label>
                        <input type="text" name="codigo_confirmacao" 
                               class="codigo-input" maxlength="6" 
                               placeholder="ABCD12" required
                               style="text-transform: uppercase;">
                    </div>
                    
                    <button type="submit" name="confirmar_entrega" class="btn-confirmar">
                        ✅ Confirmar Entrega
                    </button>
                </form>
            </div>

            <div class="entregas-list">
                <h3>🚗 Minhas Entregas em Andamento</h3>
                
                <?php 
                $entregas = $pedido_class->listarPedidosEntregador($usuario['id']);
                $tem_entregas = false;
                
                while($entrega = $entregas->fetch()): 
                    if($entrega['status'] == 'em_entrega'):
                        $tem_entregas = true;
                ?>
                    <div class="entrega-item">
                        <div class="entrega-header">
                            <div>
                                <strong>Pedido #<?php echo $entrega['idPedido']; ?></strong>
                                <span class="status status-<?php echo $entrega['status']; ?>">
                                    <?php echo ucfirst($entrega['status']); ?>
                                </span>
                            </div>
                            <div>
                                <strong>R$ <?php echo number_format($entrega['valorTotal'], 2, ',', '.'); ?></strong>
                            </div>
                        </div>
                        
                        <div class="entrega-info">
                            <p><strong>👤 Cliente:</strong> <?php echo $entrega['cliente_nome']; ?></p>
                            <p><strong>🏪 Restaurante:</strong> <?php echo $entrega['restaurante_nome']; ?></p>
                            <p><strong>📍 Endereço:</strong> <?php echo $entrega['endereco_entrega']; ?></p>
                            <p><strong>📦 Código:</strong> <code style="background: #f8f9fa; padding: 5px; border-radius: 4px;"><?php echo $entrega['codigo_entrega']; ?></code></p>
                        </div>
                    </div>
                <?php 
                    endif;
                endwhile; 
                
                if(!$tem_entregas): 
                ?>
                    <div class="sem-entregas">
                        <p>📭 Nenhuma entrega em andamento no momento.</p>
                        <p>Quando aceitar uma entrega, ela aparecerá aqui para confirmação.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-uppercase para código de entrega
        document.querySelector('.codigo-input').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
        
        // Focar no campo de código quando selecionar um pedido
        document.querySelector('select[name="pedido_id"]').addEventListener('change', function() {
            if(this.value) {
                document.querySelector('.codigo-input').focus();
            }
        });
    </script>
</body>
</html>