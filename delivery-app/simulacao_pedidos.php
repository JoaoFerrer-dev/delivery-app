<?php
session_start();
require_once 'Pedido.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$pedido = new Pedido();
$resultadoSimulacao = null;

// Debug para verificar a estrutura do usuário
if(!isset($usuario['idUsuario']) && !isset($usuario['id'])) {
    echo "<div class='alert alert-error'>Erro: Sessão do usuário inválida. Por favor, faça login novamente.</div>";
    header("refresh:3;url=index.php");
    exit;
}

// Garantir que temos um ID do usuário
if(!isset($usuario['idUsuario'])) {
    $usuario['idUsuario'] = $usuario['id'];
}

// Processar simulação
if(isset($_POST['iniciar_simulacao'])) {
    $quantidade = intval($_POST['quantidade']);
    
    if($quantidade > 0 && $quantidade <= 100) {
        $resultadoSimulacao = $pedido->simularMultiplosPedidos($quantidade, $usuario['idUsuario']);
    } else {
        echo "<div class='alert alert-error'>Quantidade deve ser entre 1 e 100 pedidos.</div>";
    }
}

// Processar aceitação de entrega
if(isset($_POST['aceitar_entrega'])) {
    $pedido_id = $_POST['pedido_id'];
    if($pedido->aceitarEntrega($pedido_id, $usuario['idUsuario'])) {
        echo "<div class='alert alert-success'>✅ Entrega aceita com sucesso!</div>";
    }
}

// Processar finalização de entrega
if(isset($_POST['finalizar_entrega'])) {
    $pedido_id = $_POST['pedido_id'];
    if($pedido->finalizarEntrega($pedido_id)) {
        echo "<div class='alert alert-success'>🏁 Entrega finalizada com sucesso!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulação de Pedidos - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Simulação de Múltiplos Pedidos</h1>
            <p>Sistema de teste com geração em massa de pedidos</p>
            <div>
                <a href="dashboard.php" class="btn btn-voltar">← Voltar</a>
                <a href="index.php?logout=true" class="btn btn-logout">🚪 Sair</a>
            </div>
        </div>

        <div class="simulacao-container">
            <!-- Resumo da Simulação -->
            <div class="card-simulacao">
                <h2>📊 Resumo da Simulação</h2>
                <div class="resumo-grid">
                    <div class="resumo-item">
                        <span class="numero"><?php 
                            $totalPedidos = $pedido->contarTotalPedidos();
                            echo $totalPedidos; 
                        ?></span>
                        <span class="label">Total de Pedidos</span>
                    </div>
                    <?php if(isset($quantidade)): ?>
                    <div class="resumo-item highlight">
                        <span class="numero"><?php echo $quantidade; ?></span>
                        <span class="label">Pedidos Simulados Agora</span>
                    </div>
                    <?php endif; ?>
                    <div class="resumo-item">
                        <span class="numero"><?php 
                            $pedidosEntrega = $pedido->contarPedidosEmEntrega();
                            echo $pedidosEntrega; 
                        ?></span>
                        <span class="label">Em Entrega</span>
                    </div>
                    <div class="resumo-item">
                        <span class="numero"><?php 
                            $pedidosConcluidos = $pedido->contarPedidosConcluidos();
                            echo $pedidosConcluidos; 
                        ?></span>
                        <span class="label">Concluídos</span>
                    </div>
                </div>
            </div>

            <!-- Resultado da Simulação -->
            <?php if($resultadoSimulacao): ?>
                <div class="card-simulacao">
                    <h2>📊 Resultado da Simulação</h2>
                    <div class="alert alert-success">
                        ✅ Simulação concluída com sucesso!
                        <br>
                        📦 <?php echo $quantidade; ?> pedidos foram criados no sistema.
                    </div>
                    
                    <div class="pedidos-grid">
                    <?php
                    if(is_array($resultadoSimulacao)) {
                        foreach($resultadoSimulacao as $pedido_info) {
                            echo '<div class="pedido-card">
                                    <div class="pedido-header">
                                        <strong>Pedido #' . $pedido_info['idPedido'] . '</strong>
                                        <span class="status status-' . $pedido_info['status'] . '">' . 
                                        ucfirst($pedido_info['status']) . '</span>
                                    </div>
                                    <p><strong>Restaurante:</strong> ' . $pedido_info['restaurante_nome'] . '</p>
                                    <p><strong>Valor:</strong> R$ ' . number_format($pedido_info['valor'], 2, ',', '.') . '</p>
                                    <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($pedido_info['data'])) . '</p>
                                </div>';
                        }
                    }
                    ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- LISTA FINAL DE PEDIDOS PROCESSADOS -->
            <div class="card-simulacao">
                <?php
                $pedido->exibirListaFinalPedidos();
                ?>
            </div>

            <!-- PEDIDOS PARA ENTREGADORES (se for entregador) -->
            <?php if($usuario['tipo'] == 'entregador'): ?>
            <div class="card-simulacao">
                <?php
                $totalEntregas = $pedido->exibirPedidosParaEntregadores();
                ?>
                
                <div class="estatisticas-entregador">
                    <h4>📊 Minhas Estatísticas</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong>Entregas Disponíveis:</strong> <?php echo $totalEntregas; ?>
                        </div>
                        <div class="stat-item">
                            <strong>Status:</strong> <span class="status status-disponivel">Disponível</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnSimular = document.querySelector('.btn-simular');
            if(btnSimular) {
                btnSimular.addEventListener('click', function() {
                    const quantidade = document.getElementById('quantidade').value;
                    this.innerHTML = '⏳ Processando ' + quantidade + ' pedidos...';
                    this.disabled = true;
                    
                    setTimeout(() => {
                        this.innerHTML = '🚀 Iniciar Simulação de Pedidos';
                        this.disabled = false;
                    }, 5000);
                });
            }
        });
    </script>
</body>
</html>