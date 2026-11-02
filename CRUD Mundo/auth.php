<?php

// Inicia a sessão, caso ainda não tenha sido iniciada.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verifica se existe um usuário logado. Se não existir, manda pro login.
function verificarLogin() {

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }

    // Se este usuário tá no "primeiro acesso", ele é obrigado a trocar a
    // senha antes de usar qualquer outra parte do sistema.
    $paginaAtual = basename($_SERVER['PHP_SELF']);

    if (!empty($_SESSION['forcar_troca_senha'])
        && $paginaAtual !== 'trocar_senha.php'
        && $paginaAtual !== 'logout.php') {

        header("Location: trocar_senha.php");
        exit;
    }
}
