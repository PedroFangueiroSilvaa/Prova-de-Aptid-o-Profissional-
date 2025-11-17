<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php';

if (!isset($_SESSION['id_utilizador'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id_comentario = mysqli_real_escape_string($conn, $_GET['id']);
    $id_utilizador = $_SESSION['id_utilizador'];

    // Verifica se o comentário pertence ao utilizador
    $sql = "DELETE FROM comentarios_blog 
            WHERE id_comentario = '$id_comentario' 
            AND id_utilizador = '$id_utilizador'";

    mysqli_query($conn, $sql);
}

header('Location: meus_comentarios.php');
exit(); 