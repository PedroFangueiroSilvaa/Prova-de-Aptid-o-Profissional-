<?php
// Arquivo de validação específico para administradores
// Este arquivo deve ser incluído em todas as páginas administrativas

// Só inicia sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Primeiro verifica se o usuário está logado
if (!isset($_SESSION["nome_utilizador"])) {
    // Se não estiver logado, redirecionar para a página de login
    header("Location: login.php");
    exit();
}

// Depois verifica se tem nível de acesso de administrador (9)
if (!isset($_SESSION['nivel_acesso']) || $_SESSION['nivel_acesso'] != 9) {
    // Redirecionar para a página inicial com mensagem de erro
    header("Location: index.php?erro=acesso_negado");
    exit();
}
?>
