<?php
require_once 'database.php';

class Pedido {
    private $conn;
    private $table_name = "pedidos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // SIMULAÇÃO DE MÚLTIPLOS PEDIDOS COM LAÇO
    public function simularMultiplosPedidos($quantidade, $cliente_id) {
        $pedidosCriados = 0;
        
        // Dados para simulação
        $restaurantes = [1, 2];
        $produtos = [
            ["nome" => "Pizza Margherita", "valor" => 45.90],
            ["nome" => "Pizza Calabresa", "valor" => 49.90],
            ["nome" => "Burger Clássico", "valor" => 24.90],
            ["nome" => "Burger Bacon", "valor" => 29.90],
            ["nome" => "Lasagna Bolonhesa", "valor" => 38.50],
            ["nome" => "Batata Frita", "valor" => 12.90]
        ];
        
        $enderecos = [
            "Rua das Flores, 123 - Centro, São Paulo - SP",
            "Avenida Principal, 456 - Jardim, São Paulo - SP", 
            "Rua Secundária, 789 - Vila Nova, São Paulo - SP"
        ];

        $statusPossiveis = ['pendente', 'confirmado', 'preparando', 'pronto'];

        echo "<div class='simulacao-progresso'>";
        echo "<h3>🏃‍♂️ Iniciando Simulação de $quantidade Pedidos...</h3>";
        echo "<div class='progresso-lista'>";

        // LAÇO PARA GERAR MÚLTIPLOS PEDIDOS
        for($i = 1; $i <= $quantidade; $i++) {
            $restaurante_id = $restaurantes[array_rand($restaurantes)];
            $produto = $produtos[array_rand($produtos)];
            $quantidade_itens = rand(1, 3);
            $valorTotal = $produto['valor'] * $quantidade_itens;
            $endereco = $enderecos[array_rand($enderecos)];
            $status = $statusPossiveis[array_rand($statusPossiveis)];
            
            // Inserir no banco de dados
            $query = "INSERT INTO " . $this->table_name . " 
                     SET cliente_id=:cliente_id, restaurante_id=:restaurante_id, 
                         valorTotal=:valorTotal, endereco_entrega=:endereco, status=:status";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cliente_id", $cliente_id);
            $stmt->bindParam(":restaurante_id", $restaurante_id);
            $stmt->bindParam(":valorTotal", $valorTotal);
            $stmt->bindParam(":endereco", $endereco);
            $stmt->bindParam(":status", $status);

            if($stmt->execute()) {
                $pedido_id = $this->conn->lastInsertId();
                $pedidosCriados++;

                echo "<div class='item-processado'>";
                echo "✅ Pedido #$i criado: {$produto['nome']} (x$quantidade_itens) - R$ " . number_format($valorTotal, 2, ',', '.');
                echo "<span class='status status-$status'>" . ucfirst($status) . "</span>";
                echo "</div>";

                usleep(100000); // 100ms

            } else {
                echo "<div class='item-erro'>";
                echo "❌ Erro ao criar pedido #$i";
                echo "</div>";
            }
        }

        echo "</div>";
        echo "<div class='resumo-simulacao'>";
        echo "<h4>📊 Resumo da Simulação:</h4>";
        echo "<p>Total de pedidos solicitados: <strong>$quantidade</strong></p>";
        echo "<p>Pedidos criados com sucesso: <strong>$pedidosCriados</strong></p>";
        echo "</div>";
        echo "</div>";

        return $pedidosCriados;
    }

    // RECUPERAR E EXIBIR LISTA FINAL DE PEDIDOS PROCESSADOS
    public function exibirListaFinalPedidos() {
        $query = "SELECT p.*, r.nome as restaurante_nome, u.nome as cliente_nome
                  FROM " . $this->table_name . " p
                  JOIN restaurantes r ON p.restaurante_id = r.idRestaurante
                  JOIN usuarios u ON p.cliente_id = u.idUsuario
                  ORDER BY p.data_pedido DESC
                  LIMIT 50";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        echo "<div class='lista-final-pedidos'>";
        echo "<h3>📦 LISTA FINAL DE PEDIDOS PROCESSADOS</h3>";
        echo "<div class='estatisticas-gerais'>";
        
        $totalPedidos = $stmt->rowCount();
        $valorTotal = 0;
        $statusCount = [
            'pendente' => 0,
            'confirmado' => 0,
            'preparando' => 0,
            'pronto' => 0,
            'em_entrega' => 0,
            'entregue' => 0
        ];
        
        // Calcular estatísticas
        $pedidos = $stmt->fetchAll();
        foreach($pedidos as $pedido) {
            $valorTotal += $pedido['valorTotal'];
            $statusCount[$pedido['status']]++;
        }
        
        echo "<div class='stats-grid'>";
        echo "<div class='stat-item'><strong>Total de Pedidos:</strong> $totalPedidos</div>";
        echo "<div class='stat-item'><strong>Valor Total:</strong> R$ " . number_format($valorTotal, 2, ',', '.') . "</div>";
        echo "<div class='stat-item'><strong>Pendentes:</strong> " . $statusCount['pendente'] . "</div>";
        echo "<div class='stat-item'><strong>Prontos para Entrega:</strong> " . $statusCount['pronto'] . "</div>";
        echo "</div>";
        echo "</div>";
        
        if($totalPedidos > 0) {
            echo "<div class='tabela-pedidos'>";
            echo "<table>";
            echo "<thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Restaurante</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Endereço</th>
                    </tr>
                  </thead>";
            echo "<tbody>";
            
            foreach($pedidos as $row) {
                echo "<tr>";
                echo "<td><strong>#" . $row['idPedido'] . "</strong></td>";
                echo "<td>" . $row['cliente_nome'] . "</td>";
                echo "<td>" . $row['restaurante_nome'] . "</td>";
                echo "<td>R$ " . number_format($row['valorTotal'], 2, ',', '.') . "</td>";
                echo "<td><span class='status status-" . $row['status'] . "'>" . ucfirst($row['status']) . "</span></td>";
                echo "<td>" . date('d/m/Y H:i', strtotime($row['data_pedido'])) . "</td>";
                echo "<td class='endereco'>" . substr($row['endereco_entrega'], 0, 30) . "...</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<p class='sem-pedidos'>Nenhum pedido processado encontrado.</p>";
        }
        
        echo "</div>";
        
        return $totalPedidos;
    }

    // EXIBIR PEDIDOS PARA ENTREGADORES
    public function exibirPedidosParaEntregadores() {
        $query = "SELECT p.*, r.nome as restaurante_nome, r.endereco as restaurante_endereco, 
                         u.nome as cliente_nome
                  FROM " . $this->table_name . " p
                  JOIN restaurantes r ON p.restaurante_id = r.idRestaurante
                  JOIN usuarios u ON p.cliente_id = u.idUsuario
                  WHERE p.status IN ('pronto', 'em_entrega')
                  ORDER BY 
                    CASE 
                        WHEN p.status = 'pronto' THEN 1
                        WHEN p.status = 'em_entrega' THEN 2
                        ELSE 3
                    END,
                    p.data_pedido ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        echo "<div class='pedidos-entregadores'>";
        echo "<h3>🚗 PEDIDOS PARA ENTREGA</h3>";
        
        if($stmt->rowCount() > 0) {
            echo "<div class='grid-entregas'>";
            
            while ($row = $stmt->fetch()) {
                $badge = $row['status'] == 'pronto' ? '🆕 NOVO' : '🚗 EM ENTREGA';
                $badgeClass = $row['status'] == 'pronto' ? 'badge-novo' : 'badge-em-entrega';
                
                echo "<div class='card-entrega'>";
                echo "<div class='entrega-header'>";
                echo "<strong>Pedido #" . $row['idPedido'] . "</strong>";
                echo "<span class='badge $badgeClass'>$badge</span>";
                echo "</div>";
                
                echo "<div class='entrega-info'>";
                echo "<p><strong>👤 Cliente:</strong> " . $row['cliente_nome'] . "</p>";
                echo "<p><strong>🏪 Restaurante:</strong> " . $row['restaurante_nome'] . "</p>";
                echo "<p><strong>📍 Retirar em:</strong> " . $row['restaurante_endereco'] . "</p>";
                echo "<p><strong>🎯 Entregar em:</strong> " . $row['endereco_entrega'] . "</p>";
                echo "<p><strong>💰 Valor:</strong> R$ " . number_format($row['valorTotal'], 2, ',', '.') . "</p>";
                echo "<p><strong>⏰ Pedido feito:</strong> " . date('d/m/Y H:i', strtotime($row['data_pedido'])) . "</p>";
                echo "</div>";
                
                echo "<div class='entrega-actions'>";
                if($row['status'] == 'pronto') {
                    echo "<form method='POST' class='form-entrega'>";
                    echo "<input type='hidden' name='pedido_id' value='" . $row['idPedido'] . "'>";
                    echo "<button type='submit' name='aceitar_entrega' class='btn btn-success btn-block'>
                            ✅ Aceitar Entrega
                          </button>";
                    echo "</form>";
                } elseif($row['status'] == 'em_entrega') {
                    echo "<form method='POST' class='form-entrega'>";
                    echo "<input type='hidden' name='pedido_id' value='" . $row['idPedido'] . "'>";
                    echo "<button type='submit' name='finalizar_entrega' class='btn btn-primary btn-block'>
                            🏁 Finalizar Entrega
                          </button>";
                    echo "</form>";
                }
                echo "</div>";
                
                echo "</div>";
            }
            
            echo "</div>";
        } else {
            echo "<div class='sem-entregas'>";
            echo "<p>📭 Nenhum pedido disponível para entrega no momento.</p>";
            echo "<p>Os pedidos aparecerão aqui quando estiverem prontos para serem entregues.</p>";
            echo "</div>";
        }
        
        echo "</div>";
        
        return $stmt->rowCount();
    }

    // Aceitar entrega
    public function aceitarEntrega($pedido_id, $entregador_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET entregador_id = :entregador_id, status = 'em_entrega'
                  WHERE idPedido = :pedido_id AND status = 'pronto'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":entregador_id", $entregador_id);
        $stmt->bindParam(":pedido_id", $pedido_id);

        return $stmt->execute();
    }

    // Finalizar entrega
    public function finalizarEntrega($pedido_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'entregue'
                  WHERE idPedido = :pedido_id AND status = 'em_entrega'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pedido_id", $pedido_id);

        return $stmt->execute();
    }

    public function listarPedidosPorCliente($cliente_id) {
        $query = "SELECT p.*, r.nome as restaurante_nome 
                  FROM " . $this->table_name . " p
                  JOIN restaurantes r ON p.restaurante_id = r.idRestaurante
                  WHERE p.cliente_id = :cliente_id
                  ORDER BY p.data_pedido DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->execute();
        return $stmt;
    }
}
?>