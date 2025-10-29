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

// Estatísticas
$total_pedidos = 0;
if($usuario['tipo'] == 'cliente') {
    $stmt = $pedido->listarPedidosPorCliente($usuario['id']);
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
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>📊 Estatísticas</h3>
                <p><strong>Total de Pedidos:</strong> <?php echo $total_pedidos; ?></p>
                <p><strong>Seu Tipo:</strong> <?php echo ucfirst($usuario['tipo']); ?></p>
                <p><strong>Usuários Cadastrados:</strong> <?php echo $usuarios->rowCount(); ?></p>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #4caf50, #45a049);">
                <h3>🎯 Nova Funcionalidade</h3>
                <p><strong>Simulação de Pedidos</strong></p>
                <p>Teste o sistema com múltiplos pedidos simultâneos</p>
                <a href="simulacao_pedidos.php" class="btn" style="background: white; color: #4caf50; margin-top: 10px;">
                    🚀 Testar Agora
                </a>
            </div>
        </div>

        <?php if($usuario['tipo'] == 'cliente'): ?>
        <div class="card">
            <h2>🎯 Simular Pedidos</h2>
            <form method="POST" action="pedidos.php">
                <div class="form-group">
                    <label>Quantidade de Pedidos a Simular:</label>
                    <input type="number" name="quantidade" min="1" max="20" value="5" required>
                </div>
                <button type="submit" name="simular_pedidos" class="btn btn-primary">
                    🚀 Iniciar Simulação
                </button>
            </form>
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
            if($usuario['tipo'] == 'cliente') {
                $stmt = $pedido->listarPedidosPorCliente($usuario['id']);
            } else {
                $stmt = $pedido->listarPedidosDisponiveis();
            }
            
            if($stmt->rowCount() > 0) {
                echo '<div class="pedidos-grid">';
                while ($row = $stmt->fetch()) {
                    echo '
                    <div class="pedido-card">
                        <div class="pedido-header">
                            <strong>Pedido #' . $row['idPedido'] . '</strong>
                            <span class="status status-' . $row['status'] . '">' . 
                            ucfirst($row['status']) . '</span>
                        </div>
                        <p><strong>Restaurante:</strong> ' . $row['restaurante_nome'] . '</p>
                        <p><strong>Valor:</strong> R$ ' . number_format($row['valorTotal'], 2, ',', '.') . '</p>
                        <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($row['data_pedido'])) . '</p>
                    </div>';
                }
                echo '</div>';
            } else {
                echo '<p>Nenhum pedido encontrado.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>