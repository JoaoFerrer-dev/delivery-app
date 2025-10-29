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

// Processar simulação
if(isset($_POST['iniciar_simulacao'])) {
    $quantidade = intval($_POST['quantidade']);
    
    if($quantidade > 0 && $quantidade <= 100) {
        $resultadoSimulacao = $pedido->simularMultiplosPedidos($quantidade, $usuario['id']);
    } else {
        echo "<div class='alert alert-error'>Quantidade deve ser entre 1 e 100 pedidos.</div>";
    }
}

// Processar aceitação de entrega
if(isset($_POST['aceitar_entrega'])) {
    $pedido_id = $_POST['pedido_id'];
    if($pedido->aceitarEntrega($pedido_id, $usuario['id'])) {
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
            <!-- Controles de Simulação -->
            <div class="controles-simulacao">
                <div class="form-simulacao">
                    <h3>⚙️ Configurar Simulação</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="quantidade"><strong>Quantidade de Pedidos:</strong></label>
                            <input type="number" id="quantidade" name="quantidade" 
                                   min="1" max="100" value="10" required>
                            <small>Máximo: 100 pedidos por simulação</small>
                        </div>
                        
                        <button type="submit" name="iniciar_simulacao" class="btn-simular">
                            🚀 Iniciar Simulação de Pedidos
                        </button>
                    </form>
                </div>
                
                <div class="info-simulacao">
                    <h3>📋 Sobre a Simulação</h3>
                    <p><strong>Funcionalidades testadas:</strong></p>
                    <ul>
                        <li>✅ Laço para gerar múltiplos pedidos</li>
                        <li>✅ Inserção no banco de dados</li>
                        <li>✅ Recuperação e exibição dos pedidos</li>
                        <li>✅ Visualização para entregadores</li>
                    </ul>
                </div>
            </div>

            <!-- Resultado da Simulação -->
            <?php if($resultadoSimulacao): ?>
                <div class="card-simulacao">
                    <h2>📊 Resultado da Simulação</h2>
                    <?php
                    // Já exibido durante a simulação
                    ?>
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