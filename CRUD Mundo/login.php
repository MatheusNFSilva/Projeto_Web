<?php
require_once 'conexao.php';
require_once 'auth.php';

// Se o usuário já estiver logado, não precisa ver a tela de login de novo
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = ?");
    $stmt->execute([$login]);
    $usuario = $stmt->fetch();

    if (!$usuario) {

        // Não existe usuário com esse login.
        $mensagem = "Usuário ou senha inválidos.";

        $log = $pdo->prepare("INSERT INTO logs (usuario_id, login_tentado, acao) VALUES (?, ?, ?)");
        $log->execute([null, $login, "Tentativa de login com usuário inexistente"]);

    } elseif ($usuario['bloqueado'] == 1) {

        // Usuário já está bloqueado (3 senhas erradas).
        $mensagem = "Este usuário está bloqueado após 3 tentativas incorretas. Fale com o administrador do sistema.";

        $log = $pdo->prepare("INSERT INTO logs (usuario_id, login_tentado, acao) VALUES (?, ?, ?)");
        $log->execute([$usuario['id'], $login, "Tentativa de login em usuário bloqueado"]);

    } elseif (password_verify($senha, $usuario['senha'])) {

        // Senha certa, zera as tentativas erradas.
        $upd = $pdo->prepare("UPDATE usuarios SET tentativas_erradas = 0 WHERE id = ?");
        $upd->execute([$usuario['id']]);

        $log = $pdo->prepare("INSERT INTO logs (usuario_id, login_tentado, acao) VALUES (?, ?, ?)");
        $log->execute([$usuario['id'], $login, "Login efetuado com sucesso"]);

        // Guarda os dados do usuário logado na sessão.
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_login'] = $usuario['login'];

        if ($usuario['primeiro_acesso'] == 1) {
            // obriga a trocar a senha antes de entrar no sistema se for a primeira vez.
            $_SESSION['forcar_troca_senha'] = true;
            header("Location: trocar_senha.php");
        } else {
            header("Location: index.php");
        }
        exit;

    } else {

        // Senha errada, soma mais uma tentativa.
        $tentativas = (int)$usuario['tentativas_erradas'] + 1;

        if ($tentativas >= 3) {

            // Chegou a 3 tentativas erradas, bloqueia o usuário.
            $upd = $pdo->prepare("UPDATE usuarios SET tentativas_erradas = ?, bloqueado = 1 WHERE id = ?");
            $upd->execute([$tentativas, $usuario['id']]);

            $mensagem = "Senha incorreta pela 3ª vez. Seu usuário foi bloqueado.";

            $log = $pdo->prepare("INSERT INTO logs (usuario_id, login_tentado, acao) VALUES (?, ?, ?)");
            $log->execute([$usuario['id'], $login, "Usuário bloqueado após 3 tentativas incorretas"]);

        } else {

            $upd = $pdo->prepare("UPDATE usuarios SET tentativas_erradas = ? WHERE id = ?");
            $upd->execute([$tentativas, $usuario['id']]);

            $restantes = 3 - $tentativas;
            $mensagem = "Senha incorreta. Você tem mais " . $restantes . " tentativa(s) antes do bloqueio.";

            $log = $pdo->prepare("INSERT INTO logs (usuario_id, login_tentado, acao) VALUES (?, ?, ?)");
            $log->execute([$usuario['id'], $login, "Tentativa de login com senha incorreta"]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRUD Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="tela-login">
        <div class="caixa-login">
            <h1>CRUD Mundo</h1>
            <p>Informe seu login e senha para entrar no sistema.</p>

            <?php if ($mensagem): ?>
                <div class="mensagem erro"><?= h($mensagem) ?></div>
            <?php endif; ?>

            <form action="login.php" method="post" class="formulario">
                <label>Login
                    <input type="text" name="login" required autofocus value="<?= h($login ?? '') ?>">
                </label>
                <label>Senha
                    <input type="password" name="senha" required>
                </label>

                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
