<?php
session_start();
require_once 'Restaurante.php';
require_once 'Pedido.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$restaurante = new Restaurante();
$pedido = new Pedido();

// Obter nichos disponíveis
$nichos = ['pizzaria', 'hamburgueria', 'sorveteria', 'lanchonete', 'outro'];
$nicho_selecionado = $_GET['nicho'] ?? null;

$restaurantes = $restaurante->listarRestaurantesPorNicho($nicho_selecionado);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurantes - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filtros-nichos {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .btn-nicho {
            background: white;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-nicho:hover, .btn-nicho.ativo {
            background: #667eea;
            color: white;
        }
        
        .restaurantes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .restaurante-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .restaurante-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .restaurante-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .badge-nicho {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-pizzaria { background: #ffebee; color: #c62828; }
        .badge-hamburgueria { background: #fff3e0; color: #ef6c00; }
        .badge-sorveteria { background: #e3f2fd; color: #1565c0; }
        .badge-lanchonete { background: #e8f5e8; color: #2e7d32; }
        .badge-outro { background: #f3e5f5; color: #7b1fa2; }
        
        .restaurante-info p {
            margin: 8px 0;
            color: #666;
        }
        
        .btn-pedido {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-pedido:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Restaurantes Parceiros</h1>
            <a href="dashboard.php" class="btn">← Voltar</a>
        </div>

        <div class="filtros-nichos">
            <a href="restaurantes.php" class="btn-nicho <?php echo !$nicho_selecionado ? 'ativo' : ''; ?>">
                🍕 Todos
            </a>
            <?php foreach($nichos as $nicho): ?>
                <a href="restaurantes.php?nicho=<?php echo $nicho; ?>" 
                   class="btn-nicho <?php echo $nicho_selecionado == $nicho ? 'ativo' : ''; ?>">
                   <?php 
                   $icones = [
                       'pizzaria' => '🍕',
                       'hamburgueria' => '🍔',
                       'sorveteria' => '🍦',
                       'lanchonete' => '🥪',
                       'outro' => '🍽️'
                   ];
                   echo $icones[$nicho] . ' ' . ucfirst($nicho); 
                   ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="restaurantes-grid">
            <?php
            if($restaurantes->rowCount() > 0) {
                while ($row = $restaurantes->fetch()) {
                    // Verificar se a coluna nicho existe
                    $nicho = isset($row['nicho']) ? $row['nicho'] : 'outro';
                    
                    echo '
                    <div class="restaurante-card">
                        <div class="restaurante-header">
                            <h3>' . $row['nome'] . '</h3>
                            <span class="badge-nicho badge-' . $nicho . '">' . ucfirst($nicho) . '</span>
                        </div>
                        
                        <div class="restaurante-info">
                            <p>📍 ' . $row['endereco'] . '</p>
                            <p>📞 ' . $row['telefone'] . '</p>
                            <p>🏢 ' . $row['cnpj'] . '</p>
                        </div>
                        
                        <div class="restaurante-actions">';
                    
                    if($usuario['tipo'] == 'cliente') {
                        echo '<a href="fazer_pedido.php?restaurante_id=' . $row['idRestaurante'] . '" class="btn-pedido">
                                🛒 Fazer Pedido
                              </a>';
                    } else {
                        echo '<a href="fazer_pedido.php?restaurante_id=' . $row['idRestaurante'] . '" class="btn">
                                👀 Ver Cardápio
                              </a>';
                    }
                    
                    echo '</div>
                    </div>';
                }
            } else {
                echo '<div class="sem-restaurantes">';
                echo '<p>Nenhum restaurante encontrado';
                if($nicho_selecionado) {
                    echo ' para a categoria "' . ucfirst($nicho_selecionado) . '"';
                }
                echo '.</p>';
                echo '<a href="restaurantes.php" class="btn">Ver todos os restaurantes</a>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>