<?php
session_start();
include 'conexao.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];

// Verificar se o ID do blog foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: meus_blogs.php");
    exit;
}

$id_post = (int)$_GET['id'];

// Verificar se o blog pertence ao utilizador antes de excluir
$check_query = "SELECT id_post FROM blog WHERE id_post = $id_post AND id_utilizador = $id_utilizador";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    // Iniciar transação para garantir consistência
    mysqli_autocommit($conn, FALSE);
    
    try {
        // Primeiro, apagar todos os comentários associados ao post
        $delete_comments_query = "DELETE FROM comentarios_blog WHERE id_post = $id_post";
        if (!mysqli_query($conn, $delete_comments_query)) {
            throw new Exception("Erro ao excluir comentários: " . mysqli_error($conn));
        }
        
        // Depois, apagar o post do blog
        $delete_blog_query = "DELETE FROM blog WHERE id_post = $id_post AND id_utilizador = $id_utilizador";
        if (!mysqli_query($conn, $delete_blog_query)) {
            throw new Exception("Erro ao excluir blog: " . mysqli_error($conn));
        }
        
        // Confirmar transação
        mysqli_commit($conn);
        $_SESSION['success_message'] = "Blog e comentários excluídos com sucesso!";
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        mysqli_rollback($conn);
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    // Restaurar autocommit
    mysqli_autocommit($conn, TRUE);
    
} else {
    $_SESSION['error_message'] = "Blog não encontrado ou não pertence ao seu utilizador.";
}

// Redirecionar de volta para a página de blogs do utilizador
header("Location: meus_blogs.php");
exit;
?>
