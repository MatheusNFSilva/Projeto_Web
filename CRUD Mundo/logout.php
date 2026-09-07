<?php
require_once 'auth.php';

// Apaga todas as informações da sessão e encerra o login.
$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;
