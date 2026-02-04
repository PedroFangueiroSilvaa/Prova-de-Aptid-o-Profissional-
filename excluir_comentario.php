<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id_utilizador"]) || $_SESSION["nivel_acesso"] != 9) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

if (isset($_GET['id'])) {
    $id_comentario = mysqli_real_escape_string($conn, $_GET['id']);
    
    $sql = "DELETE FROM comentarios_blog WHERE id_comentario = '$id_comentario'";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['sucesso'] = "Comentário excluído com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao excluir comentário.";
    }
}

header("Location: comentarios_admin.php");
exit(); 