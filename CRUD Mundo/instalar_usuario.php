<?php
// ATENÇÃO: este arquivo serve só para criar o primeiro usuário do sistema.
// Rodar ele UMA VEZ no navegador, pelo amor de Deus, não roda mais de uma vez


require_once 'conexao.php';

$loginInicial = 'admin';
$senhaInicial = '123456';

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE login = ?");
$stmt->execute([$loginInicial]);

if ($stmt->fetch()) {
    echo "O usuário '$loginInicial' já existe. Nenhuma alteração foi feita.";
} else {
    $hash = password_hash($senhaInicial, PASSWORD_DEFAULT);

    $insert = $pdo->prepare("
        INSERT INTO usuarios (nome, login, senha, primeiro_acesso)
        VALUES (?, ?, ?, 1)
    ");
    $insert->execute(['Administrador', $loginInicial, $hash]);

    echo "Usuário criado com sucesso!<br>";
    echo "Login: $loginInicial<br>";
    echo "Senha: $senhaInicial<br>";
    echo "Como é o primeiro acesso, o sistema vai pedir a troca de senha.<br>";
    echo "Depois de entrar, você pode cadastrar outros usuários direto pelo card \"Novo usuário\" na tela principal.<br><br>";
    echo "<a href='login.php'>Ir para a tela de login</a>";
}
