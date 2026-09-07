<?php
require_once 'conexao.php';
require_once 'auth.php';
verificarLogin();

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$mensagem = '';
$primeiroAcesso = !empty($_SESSION['forcar_troca_senha']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    // Busca o usuário logado no banco para conferir a senha atual
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();

    if (!password_verify($senhaAtual, $usuario['senha'])) {

        $mensagem = "A senha atual digitada está incorreta.";

    } elseif ($novaSenha === '') {

        $mensagem = "Digite a nova senha.";

    } elseif (strlen($novaSenha) < 6) {

        $mensagem = "A nova senha deve ter pelo menos 6 caracteres.";

    } elseif ($novaSenha !== $confirmarSenha) {

        $mensagem = "A nova senha e a confirmação precisam ser iguais.";

    } else {

        // Tudo certo, gera um novo hash e atualiza a tabela usuarios.
        $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $upd = $pdo->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 0 WHERE id = ?");
        $upd->execute([$novoHash, $usuario['id']]);

        $log = $pdo->prepare("INSERT INTO logs (usuario_id, login_tentado, acao) VALUES (?, ?, ?)");
        $log->execute([$usuario['id'], $usuario['login'], "Senha alterada pelo usuário"]);

        // Não precisa mais forçar a troca de senha.
        unset($_SESSION['forcar_troca_senha']);

        header("Location: index.php?msg=" . urlencode("Senha alterada com sucesso."));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar senha - CRUD Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="tela-login">
        <div class="caixa-login">
            <h1>Trocar senha</h1>

            <?php if ($primeiroAcesso): ?>
                <div class="aviso-primeiro-acesso">
                    Este é o seu primeiro acesso. Por segurança, você precisa
                    trocar a senha antes de continuar.
                </div>
            <?php else: ?>
                <p>Preencha os campos abaixo para alterar sua senha.</p>
            <?php endif; ?>

            <?php if ($mensagem): ?>
                <div class="mensagem erro"><?= h($mensagem) ?></div>
            <?php endif; ?>

            <form action="trocar_senha.php" method="post" class="formulario">
                <label>Senha atual
                    <input type="password" name="senha_atual" required autofocus>
                </label>
                <label>Nova senha
                    <input type="password" name="nova_senha" id="novaSenha" required minlength="6">
                </label>
                <label>Confirmar nova senha
                    <input type="password" name="confirmar_senha" id="confirmarSenha" required minlength="6">
                    <small id="avisoSenha"></small>
                </label>

                <button type="submit">Salvar nova senha</button>
            </form>

            <?php if (!$primeiroAcesso): ?>
                <p><a href="index.php">Voltar para o sistema</a></p>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
