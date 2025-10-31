<?php
session_start();
require_once 'Pedido.php';
require_once 'Entregador.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$pedido = new Pedido();
$entregador = new Entregador();
$mensagem = "";

if(isset($_POST['simular_pedidos']) && $usuario['tipo'] == 'cliente') {
    $quantidade = intval($_POST['quantidade']);
    $pedidos_criados = $pedido->simularMultiplosPedidos($quantidade, $usuario['idUsuario']);
    $mensagem = "Sucesso: {$pedidos_criados} pedidos simulados criados!";
}

if(isset($_POST['aceitar_entrega']) && $usuario['tipo'] == 'entregador') {
    $pedido_id = $_POST['pedido_id'];
    if($entregador->aceitarEntrega($pedido_id, $usuario['idUsuario'])) {
        $mensagem = "Entrega aceita com sucesso!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Gerenciar Pedidos</h1>
            <a href="dashboard.php" class="btn">← Voltar</a>
        </div>

        <?php if($mensagem): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <?php if($usuario['tipo'] == 'cliente'): ?>
        <div class="card">
            <h2>🎯 Simular Múltiplos Pedidos</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Quantidade de Pedidos (1-20):</label>
                    <input type="number" name="quantidade" min="1" max="20" value="5" required>
                </div>
                <button type="submit" name="simular_pedidos" class="btn btn-primary">
                    🚀 Simular Pedidos
                </button>
            </form>
            <p style="margin-top: 10px; color: #666;">
                <small>💡 Experimente a <a href="simulacao_pedidos.php" style="color: #4caf50;">versão completa de simulação</a></small>
            </p>
        </div>
        <?php endif; ?>

        <!-- SE FOR ENTREGADOR, MOSTRAR PEDIDOS PARA ENTREGA -->
        <?php if($usuario['tipo'] == 'entregador'): ?>
        <div class="card">
            <h2>🚗 Pedidos para Entrega</h2>
            <?php
            $pedido->exibirPedidosParaEntregadores();
            ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>
                <?php 
                if($usuario['tipo'] == 'cliente') echo '📋 Seus Pedidos';
                elseif($usuario['tipo'] == 'entregador') echo '📦 Todos os Pedidos';
                else echo '📦 Pedidos do Sistema';
                ?>
            </h2>

            <?php
            if($usuario['tipo'] == 'cliente') {
                $stmt = $pedido->listarPedidosPorCliente($usuario['id']);
            } else {
                // Para entregadores e outros, mostrar todos os pedidos
                $pedido->exibirListaFinalPedidos();
            }
            ?>
        </div>
    </div>
</body>
</html>