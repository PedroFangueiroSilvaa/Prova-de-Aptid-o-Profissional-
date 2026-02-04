<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['id_utilizador'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = mysqli_real_escape_string($conn, $_POST['id_post']);
    $conteudo = mysqli_real_escape_string($conn, $_POST['conteudo']);
    $id_utilizador = $_SESSION['id_utilizador'];

    if (empty($conteudo)) {
        header("Location: blog-single.php?id=" . $id_post);
        exit();
    }

    $sql = "INSERT INTO comentarios_blog (id_post, id_utilizador, conteudo) 
            VALUES ('$id_post', '$id_utilizador', '$conteudo')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: blog-single.php?id=" . $id_post);
    } else {
        header("Location: blog-single.php?id=" . $id_post);
    }
    exit();
} 