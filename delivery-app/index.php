<?php
session_start();
require_once 'Usuario.php';

$mensagem = "";
$mostrarCadastro = false;

// Alternar entre Login e Cadastro
if(isset($_GET['acao']) && $_GET['acao'] == 'cadastrar') {
    $mostrarCadastro = true;
}

if(isset($_GET['acao']) && $_GET['acao'] == 'login') {
    $mostrarCadastro = false;
}

// Processar cadastro
if(isset($_POST['cadastrar'])) {
    $usuario = new Usuario();
    $mensagem = $usuario->cadastrar($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['tipo']);
    
    if(strpos($mensagem, 'Sucesso') !== false) {
        $mostrarCadastro = false;
    }
}

// Processar login
if(isset($_POST['login'])) {
    $usuario = new Usuario();
    if($usuario->login($_POST['email'], $_POST['senha'])) {
        $_SESSION['usuario'] = [
            'id' => $usuario->idUsuario,
            'nome' => $usuario->nome,
            'email' => $usuario->email,
            'tipo' => $usuario->tipo
        ];
        header("Location: dashboard.php");
        exit;
    } else {
        $mensagem = "Erro: Email ou senha incorretos.";
    }
}

// Processar logout
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Delivery - Login e Cadastro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>🍕 Sistema Delivery</h1>
            
            <?php if($mensagem): ?>
                <div class="alert <?php echo strpos($mensagem, 'Sucesso') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <?php if(!$mostrarCadastro): ?>
                <!-- FORMULÁRIO DE LOGIN -->
                <div class="form-section">
                    <h2>Login no Sistema</h2>
                    <form method="POST">
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Seu email" required value="joao@email.com">
                        </div>
                        <div class="form-group">
                            <input type="password" name="senha" placeholder="Sua senha" required value="senha123">
                        </div>
                        <button type="submit" name="login" class="btn btn-primary btn-block">
                            Entrar no Sistema
                        </button>
                    </form>
                    <div class="switch-form">
                        <p>Não tem conta? <a href="?acao=cadastrar">Cadastre-se aqui</a></p>
                    </div>
                </div>

            <?php else: ?>
                <!-- FORMULÁRIO DE CADASTRO -->
                <div class="form-section">
                    <h2>Criar Nova Conta</h2>
                    <form method="POST">
                        <div class="form-group">
                            <input type="text" name="nome" placeholder="Seu nome completo" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Seu melhor email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="senha" placeholder="Crie uma senha" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Tipo de Conta:</label>
                            <select name="tipo" required>
                                <option value="cliente">👤 Cliente</option>
                                <option value="entregador">🚗 Entregador</option>
                                <option value="restaurante">🍽️ Restaurante</option>
                            </select>
                        </div>
                        <button type="submit" name="cadastrar" class="btn btn-success btn-block">
                            📝 Criar Conta
                        </button>
                    </form>
                    <div class="switch-form">
                        <p>Já tem conta? <a href="?acao=login">Faça login aqui</a></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="demo-accounts">
                <h3>Contas de Demonstração:</h3>
                <p><strong>Cliente:</strong> joao@email.com / senha123</p>
                <p><strong>Entregador:</strong> carlos@email.com / senha123</p>
                <p><strong>Restaurante:</strong> bella@email.com / senha123</p>
            </div>
        </div>
    </div>
</body>
</html>