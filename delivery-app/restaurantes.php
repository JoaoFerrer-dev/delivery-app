<?php
session_start();
require_once 'Restaurante.php';
require_once 'Prato.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$restaurante = new Restaurante();
$prato = new Prato();

$restaurantes = $restaurante->listarRestaurantes();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurantes - Sistema Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Restaurantes Cadastrados</h1>
            <a href="dashboard.php" class="btn">← Voltar</a>
        </div>

        <div class="card">
            <h2>📋 Lista de Restaurantes</h2>
            
            <?php
            if($restaurantes->rowCount() > 0) {
                echo '<div class="restaurantes-grid">';
                while ($row = $restaurantes->fetch()) {
                    $pratos = $restaurante->listarPratos($row['idRestaurante']);
                    
                    echo '
                    <div class="restaurante-card">
                        <h3>' . $row['nome'] . '</h3>
                        <p><strong>📍 Endereço:</strong> ' . $row['endereco'] . '</p>
                        <p><strong>📞 Telefone:</strong> ' . $row['telefone'] . '</p>
                        
                        <div class="cardapio">
                            <h4>🍴 Cardápio:</h4>';
                    
                    if($pratos->rowCount() > 0) {
                        while ($prato_row = $pratos->fetch()) {
                            echo '
                            <div class="prato-item">
                                <strong>' . $prato_row['nome'] . '</strong>
                                <span>R$ ' . number_format($prato_row['preco'], 2, ',', '.') . '</span>
                                <p>' . $prato_row['descricao'] . '</p>
                            </div>';
                        }
                    } else {
                        echo '<p>Nenhum prato cadastrado.</p>';
                    }
                    
                    echo '</div></div>';
                }
                echo '</div>';
            } else {
                echo '<p>Nenhum restaurante cadastrado.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>