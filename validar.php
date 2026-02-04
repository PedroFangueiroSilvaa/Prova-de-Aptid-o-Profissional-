<?php
//Só inicia sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o utilizador está logado
if (!isset($_SESSION["nome_utilizador"])) {
    // Se não estiver logado, redirecionar para a página de login
    header("Location: login.php");
    exit();
}
?>