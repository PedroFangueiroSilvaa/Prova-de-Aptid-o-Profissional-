<?php
session_start();
include __DIR__ . '/conexao.php';

$email = urldecode($_GET['email']);

// Inserir o novo usuário no banco de dados
$sql = "INSERT INTO utilizadores (email, palavra_passe, nome, id_nivel) VALUES ('$email', 'senha_padrao', 'Novo Usuário', 2)";
if (mysqli_query($conn, $sql)) {
    echo "Conta criada com sucesso para o email: $email";
} else {
    echo "Erro ao criar conta: " . mysqli_error($conn);
}

mysqli_close($conn);
?>