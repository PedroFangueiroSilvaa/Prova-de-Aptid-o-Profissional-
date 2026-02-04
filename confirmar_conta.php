<?php
session_start();
include __DIR__ . '/conexao.php'; // Inclui o arquivo de conexão com o banco de dados

// Verificar se o token foi passado na URL
$token = $_GET['token'] ?? null;

if (empty($token)) {
    header("Location: index.php?erro=token_invalido");
    exit();
}

// Verificar se os dados temporários estão na sessão
if (!isset($_SESSION['dados_temp']) || empty($_SESSION['dados_temp'])) {
    header("Location: index.php?erro=dados_nao_encontrados");
    exit();
}

// Comparar o token da URL com o token armazenado na sessão
if ($_SESSION['dados_temp']['token'] !== $token) {
    header("Location: index.php?erro=token_invalido");
    exit();
}

// Extrair os dados temporários
$nome = $_SESSION['dados_temp']['nome'];
$email = $_SESSION['dados_temp']['email'];
$palavra_passe = $_SESSION['dados_temp']['palavra_passe'];
$local_envio = $_SESSION['dados_temp']['local_envio'];

// Inserir o novo usuário no banco de dados
$sql = "INSERT INTO utilizadores (nome, email, palavra_passe, id_nivel, local_envio) VALUES ('$nome', '$email', '$palavra_passe', 1, '$local_envio')";

if (mysqli_query($conn, $sql)) {
    // Limpar os dados temporários da sessão
    unset($_SESSION['dados_temp']);
    header("Location: index.php?sucesso=conta_confirmada");
} else {
    header("Location: index.php?erro=erro_banco_dados");
}

mysqli_close($conn);
?>