<?php
// Iniciar a sessão
session_start();

// Verificar se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

// Incluir a conexão com o banco de dados
include 'conexao.php';

// Capturar o ID do utilizador logado
$id_utilizador = $_SESSION['id_utilizador'];

// Capturar os dados enviados pelo formulário
$nome = mysqli_real_escape_string($conn, $_POST['nome']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$palavra_passe = $_POST['palavra_passe']; // Senha em texto simples

// Verificar se o email já existe para outro utilizador
$queryVerificarEmail = "SELECT * FROM utilizadores WHERE email = '$email' AND id_utilizador != $id_utilizador";
$resultadoEmail = mysqli_query($conn, $queryVerificarEmail);

if (mysqli_num_rows($resultadoEmail) > 0) {
    // Email já está em uso por outro utilizador
    $_SESSION['mensagem'] = "Este email já está em uso.";
    header("Location: editar_conta.php");
    exit;
}

// Construir a query de atualização
$queryAtualizar = "UPDATE utilizadores SET nome = '$nome', email = '$email'";

// Atualizar a senha apenas se um novo valor for fornecido
if (!empty($palavra_passe)) {
    $senhaLimpa = mysqli_real_escape_string($conn, $palavra_passe); // Sem criptografia
    $queryAtualizar .= ", palavra_passe = '$senhaLimpa'";
}

$queryAtualizar .= " WHERE id_utilizador = $id_utilizador";

// Executar a query
if (mysqli_query($conn, $queryAtualizar)) {
    $_SESSION['mensagem'] = "Dados atualizados com sucesso!";
} else {
    $_SESSION['mensagem'] = "Erro ao atualizar os dados.";
}

// Redirecionar de volta para a página de edição da conta
header("Location: editar_conta.php");
exit;
?>