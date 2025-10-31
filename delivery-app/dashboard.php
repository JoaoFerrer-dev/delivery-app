<?php
session_start();
require_once 'Pedido.php';
require_once 'Restaurante.php';
require_once 'Usuario.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$pedido = new Pedido();
$userManager = new Usuario();

# Estatísticas - CORREÇÃO AQUI
$total_pedidos = 0;

// Garantir que temos um ID do usuário
$usuario_id = isset($usuario['idUsuario']) ? $usuario['idUsuario'] : (isset($usuario['id']) ? $usuario['id'] : null);

if($usuario['tipo'] == 'cliente' && $usuario_id) {
    $stmt = $pedido->listarPedidosPorCliente($usuario_id);
    $total_pedidos = $stmt->rowCount();
} elseif($usuario['tipo'] == 'entregador') {
    $stmt = $pedido->listarPedidosDisponiveis();
    $total_pedidos = $stmt->rowCount();
}

// Listar usuários
$usuarios = $userManager->listarUsuarios();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍕 Sistema Delivery</h1>
            <p>Bem-vindo, <?php echo $usuario['nome']; ?> (<?php echo $usuario['tipo']; ?>)</p>
            <a href="index.php?logout=true" class="btn btn-logout">🚪 Sair</a>
        </div>

        <div class="nav">
            <a href="dashboard.php" class="btn nav-btn active">📊 Dashboard</a>
            <a href="pedidos.php" class="btn nav-btn">📦 Pedidos</a>
            <a href="restaurantes.php" class="btn nav-btn">🍽️ Restaurantes</a>
            <a href="simulacao_pedidos.php" class="btn nav-btn" style="background: #4caf50; color: white;">🎯 Simular Pedidos</a>
            
            <?php if($usuario['tipo'] == 'entregador'): ?>
                <a href="confirmar_entrega.php" class="btn nav-btn" style="background: #2196f3; color: white;">✅ Confirmar Entrega</a>
            <?php endif; ?>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>📊 Estatísticas</h3>
                <p><strong>Total de Pedidos:</strong> <?php echo $total_pedidos; ?></p>
                <p><strong>Seu Tipo:</strong> <?php echo ucfirst($usuario['tipo']); ?></p>
                <p><strong>Usuários Cadastrados:</strong> <?php echo $usuarios->rowCount(); ?></p>
            </div>
            
            <?php if($usuario['tipo'] == 'cliente'): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #4caf50, #45a049);">
                <h3>🎯 Nova Funcionalidade</h3>
                <p><strong>Fazer Pedidos Reais</strong></p>
                <p>Agora você pode fazer pedidos reais com cardápios!</p>
                <a href="restaurantes.php" class="btn" style="background: white; color: #4caf50; margin-top: 10px;">
                    🍽️ Fazer Pedido
                </a>
            </div>
            <?php endif; ?>
            
            <?php if($usuario['tipo'] == 'entregador'): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #2196f3, #1976d2);">
                <h3>🚗 Entregas</h3>
                <p><strong>Entregas Disponíveis:</strong> <?php echo $total_pedidos; ?></p>
                <p>Confirme entregas com código</p>
                <a href="confirmar_entrega.php" class="btn" style="background: white; color: #2196f3; margin-top: 10px;">
                    ✅ Confirmar Entrega
                </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if($usuario['tipo'] == 'cliente'): ?>
        <div class="card">
            <h2>🎯 Simular Pedidos (Teste)</h2>
            <form method="POST" action="simulacao_pedidos.php">
                <div class="form-group">
                    <label>Quantidade de Pedidos a Simular:</label>
                    <input type="number" name="quantidade" min="1" max="20" value="5" required>
                </div>
                <button type="submit" name="iniciar_simulacao" class="btn btn-primary">
                    🚀 Iniciar Simulação
                </button>
            </form>
            <p style="margin-top: 10px; color: #666;">
                <small>💡 Experimente a nova <a href="simulacao_pedidos.php" style="color: #4caf50;">versão completa de simulação</a> ou <a href="restaurantes.php" style="color: #2196f3;">faça pedidos reais</a></small>
            </p>
        </div>
        <?php endif; ?>

        <!-- Lista de Usuários Cadastrados -->
        <div class="card">
            <h2>👥 Usuários do Sistema</h2>
            <div class="usuarios-grid">
                <?php
                if($usuarios->rowCount() > 0) {
                    while ($user = $usuarios->fetch()) {
                        echo '
                        <div class="usuario-card">
                            <div class="usuario-header">
                                <strong>' . $user['nome'] . '</strong>
                                <span class="tipo tipo-' . $user['tipo'] . '">' . 
                                ucfirst($user['tipo']) . '</span>
                            </div>
                            <p><strong>Email:</strong> ' . $user['email'] . '</p>
                            <p><strong>Cadastro:</strong> ' . date('d/m/Y', strtotime($user['created_at'])) . '</p>
                        </div>';
                    }
                } else {
                    echo '<p>Nenhum usuário cadastrado.</p>';
                }
                ?>
            </div>
        </div>

                
        <div class="card">
            <h2>📋 Pedidos Recentes</h2>
            <?php
            try {
                if($usuario['tipo'] == 'cliente' && $usuario_id) {
                    $stmt = $pedido->listarPedidosPorCliente($usuario_id);
                    
                    if($stmt && $stmt->rowCount() > 0) {
                        echo '<div class="pedidos-grid">';
                        while ($row = $stmt->fetch()) {
                            echo '
                            <div class="pedido-card">
                                <div class="pedido-header">
                                    <strong>Pedido #' . htmlspecialchars($row['idPedido']) . '</strong>
                                    <span class="status status-' . htmlspecialchars($row['status']) . '">' . 
                                    ucfirst(htmlspecialchars($row['status'])) . '</span>
                                </div>
                                <p><strong>Restaurante:</strong> ' . htmlspecialchars($row['restaurante_nome']) . '</p>
                                <p><strong>Valor:</strong> R$ ' . number_format($row['valorTotal'], 2, ',', '.') . '</p>
                                <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($row['data_pedido'])) . '</p>';
                            
                            if(isset($row['codigo_entrega']) && $row['codigo_entrega']) {
                                echo '<p><strong>Código:</strong> <code>' . htmlspecialchars($row['codigo_entrega']) . '</code></p>';
                            }
                            
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>Nenhum pedido encontrado. <a href="restaurantes.php">Faça seu primeiro pedido!</a></p>';
                    }
                } elseif($usuario['tipo'] == 'entregador') {
                    // Para entregadores, mostrar pedidos disponíveis
                    $stmt = $pedido->listarPedidosDisponiveis();
                    
                    if($stmt && $stmt->rowCount() > 0) {
                        echo '<div class="pedidos-grid">';
                        while ($row = $stmt->fetch()) {
                            echo '
                            <div class="pedido-card">
                                <div class="pedido-header">
                                    <strong>Pedido #' . htmlspecialchars($row['idPedido']) . '</strong>
                                    <span class="status status-' . htmlspecialchars($row['status']) . '">' . 
                                    ucfirst(htmlspecialchars($row['status'])) . '</span>
                                </div>
                                <p><strong>Restaurante:</strong> ' . htmlspecialchars($row['restaurante_nome']) . '</p>
                                <p><strong>Valor:</strong> R$ ' . number_format($row['valorTotal'], 2, ',', '.') . '</p>
                                <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($row['data_pedido'])) . '</p>';
                            
                            if($row['status'] == 'pronto') {
                                echo '<form method="POST" action="pedidos.php" style="margin-top: 10px;">
                                        <input type="hidden" name="pedido_id" value="' . htmlspecialchars($row['idPedido']) . '">
                                        <button type="submit" name="aceitar_entrega" class="btn btn-success">
                                            ✅ Aceitar Entrega
                                        </button>
                                      </form>';
                            }
                            
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>Nenhum pedido disponível para entrega no momento.</p>';
                    }
                }
            } catch(Exception $e) {
                echo '<p class="alert alert-error">Erro ao carregar pedidos: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>