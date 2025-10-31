<?php
session_start();
require_once 'Pedido.php';
require_once 'Restaurante.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$pedido_id = $_GET['pedido_id'] ?? null;

if(!$pedido_id) {
    header("Location: dashboard.php");
    exit;
}

$pedido_class = new Pedido();
$restaurante_class = new Restaurante();

$pedido = $pedido_class->getPedido($pedido_id);
$itens = $pedido_class->getItensPedido($pedido_id);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .confirmacao-pedido {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .card-confirmacao {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .icone-sucesso {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .codigo-entrega {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            padding: 20px;
            border-radius: 10px;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        
        .info-pedido {
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .lista-itens {
            text-align: left;
            margin: 20px 0;
        }
        
        .item-pedido {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .acoes-pedido {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .status-timeline {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        
        .status-timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            z-index: 1;
        }
        
        .status-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .status-bubble {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        
        .status-step.ativo .status-bubble {
            background: #4caf50;
            color: white;
        }
        
        .status-step.concluido .status-bubble {
            background: #4caf50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmacao-pedido">
            <div class="card-confirmacao">
                <div class="icone-sucesso">✅</div>
                <h1>Pedido Confirmado!</h1>
                <p>Seu pedido foi recebido e está sendo preparado.</p>
                
                <div class="codigo-entrega">
                    <?php echo $pedido['codigo_entrega']; ?>
                </div>
                <p><strong>Guarde este código para confirmar a entrega</strong></p>
                
                <div class="info-pedido">
                    <h3>📦 Detalhes do Pedido</h3>
                    <p><strong>Número do Pedido:</strong> #<?php echo $pedido['idPedido']; ?></p>
                    <p><strong>Restaurante:</strong> <?php echo $pedido['restaurante_nome']; ?></p>
                    <p><strong>Endereço de Entrega:</strong> <?php echo $pedido['endereco_entrega']; ?></p>
                    <p><strong>Valor Total:</strong> R$ <?php echo number_format($pedido['valorTotal'], 2, ',', '.'); ?></p>
                    <p><strong>Status:</strong> <span class="status status-<?php echo $pedido['status']; ?>"><?php echo ucfirst($pedido['status']); ?></span></p>
                </div>
                
                <div class="lista-itens">
                    <h3>🍽️ Itens do Pedido</h3>
                    <?php while($item = $itens->fetch()): ?>
                    <div class="item-pedido">
                        <div>
                            <strong><?php echo $item['item_nome']; ?></strong>
                            <br>
                            <small>Qtd: <?php echo $item['quantidade']; ?> x R$ <?php echo number_format($item['precoUnitario'], 2, ',', '.'); ?></small>
                            <?php if($item['observacoes']): ?>
                                <br><small>Obs: <?php echo $item['observacoes']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong>R$ <?php echo number_format($item['quantidade'] * $item['precoUnitario'], 2, ',', '.'); ?></strong>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Timeline do Pedido -->
                <div class="status-timeline">
                    <div class="status-step <?php echo in_array($pedido['status'], ['pendente', 'confirmado', 'preparando', 'pronto', 'em_entrega', 'entregue']) ? 'concluido' : 'ativo'; ?>">
                        <div class="status-bubble">1</div>
                        <div>Confirmado</div>
                    </div>
                    <div class="status-step <?php echo in_array($pedido['status'], ['preparando', 'pronto', 'em_entrega', 'entregue']) ? 'concluido' : ''; echo $pedido['status'] == 'preparando' ? ' ativo' : ''; ?>">
                        <div class="status-bubble">2</div>
                        <div>Preparando</div>
                    </div>
                    <div class="status-step <?php echo in_array($pedido['status'], ['pronto', 'em_entrega', 'entregue']) ? 'concluido' : ''; echo $pedido['status'] == 'pronto' ? ' ativo' : ''; ?>">
                        <div class="status-bubble">3</div>
                        <div>Pronto</div>
                    </div>
                    <div class="status-step <?php echo in_array($pedido['status'], ['em_entrega', 'entregue']) ? 'concluido' : ''; echo $pedido['status'] == 'em_entrega' ? ' ativo' : ''; ?>">
                        <div class="status-bubble">4</div>
                        <div>Saiu p/ Entrega</div>
                    </div>
                    <div class="status-step <?php echo $pedido['status'] == 'entregue' ? 'concluido' : ''; echo $pedido['status'] == 'entregue' ? ' ativo' : ''; ?>">
                        <div class="status-bubble">5</div>
                        <div>Entregue</div>
                    </div>
                </div>
                
                <div class="acoes-pedido">
                    <a href="dashboard.php" class="btn btn-primary">📊 Ver Meus Pedidos</a>
                    <a href="restaurantes.php" class="btn">🍽️ Fazer Novo Pedido</a>
                    <a href="index.php" class="btn btn-logout">🚪 Sair</a>
                </div>
            </div>
            
            <div class="card-info">
                <h3>ℹ️ Como funciona a entrega?</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>1. Preparação</strong>
                        <p>O restaurante está preparando seu pedido</p>
                    </div>
                    <div class="info-item">
                        <strong>2. Código de Entrega</strong>
                        <p>Guarde o código <strong><?php echo $pedido['codigo_entrega']; ?></strong></p>
                    </div>
                    <div class="info-item">
                        <strong>3. Confirmação</strong>
                        <p>O entregador pedirá o código para confirmar</p>
                    </div>
                    <div class="info-item">
                        <strong>4. Aproveite!</strong>
                        <p>Confirme o código e aproveite seu pedido!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>