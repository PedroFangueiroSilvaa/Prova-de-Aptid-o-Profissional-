<?php
// Validação de administrador
include 'validar_admin.php';

// Incluir a conexão com o banco de dados
include __DIR__ . '/conexao.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: eliminar_utilizador.php?msg=ID do utilizador não fornecido');
    exit;
}

// Obter o ID do utilizador a ser eliminado
$id_utilizador = mysqli_real_escape_string($conn, $_GET['id']);

// Verificar se o utilizador existe
$sql_verificar = "SELECT * FROM utilizadores WHERE id_utilizador = '$id_utilizador'";
$resultado_verificar = mysqli_query($conn, $sql_verificar);

if (mysqli_num_rows($resultado_verificar) == 0) {
    header('Location: eliminar_utilizador.php?msg=Utilizador não encontrado');
    exit;
}

// Excluir todas as relações dependentes do utilizador
// Verificar e eliminar dados relacionados apenas se as tabelas existirem

// 1. Excluir entradas na tabela de reviews_produtos (se existir)
$sql_check_reviews_produtos = "SHOW TABLES LIKE 'reviews_produtos'";
$result_check = mysqli_query($conn, $sql_check_reviews_produtos);
if (mysqli_num_rows($result_check) > 0) {
    $sql_reviews_produtos = "DELETE FROM reviews_produtos WHERE id_utilizador = '$id_utilizador'";
    mysqli_query($conn, $sql_reviews_produtos);
}

// 2. Excluir entradas na tabela de reviews_encomendas (se existir)
$sql_check_reviews_encomendas = "SHOW TABLES LIKE 'reviews_encomendas'";
$result_check = mysqli_query($conn, $sql_check_reviews_encomendas);
if (mysqli_num_rows($result_check) > 0) {
    $sql_reviews_encomendas = "DELETE FROM reviews_encomendas WHERE id_utilizador = '$id_utilizador'";
    mysqli_query($conn, $sql_reviews_encomendas);
}

// 3. Excluir comentários do blog (se existir)
$sql_check_comentarios = "SHOW TABLES LIKE 'comentarios_blog'";
$result_check = mysqli_query($conn, $sql_check_comentarios);
if (mysqli_num_rows($result_check) > 0) {
    $sql_comentarios = "DELETE FROM comentarios_blog WHERE id_utilizador = '$id_utilizador'";
    mysqli_query($conn, $sql_comentarios);
}

// 3.1. Excluir posts do blog do utilizador (se existir)
$sql_check_blog = "SHOW TABLES LIKE 'blog'";
$result_check = mysqli_query($conn, $sql_check_blog);
if (mysqli_num_rows($result_check) > 0) {
    // Primeiro, buscar os posts do utilizador para excluir os comentários associados
    $sql_posts_ids = "SELECT id_post FROM blog WHERE id_utilizador = '$id_utilizador'";
    $result_posts_ids = mysqli_query($conn, $sql_posts_ids);
    if ($result_posts_ids && mysqli_num_rows($result_posts_ids) > 0) {
        while ($row = mysqli_fetch_assoc($result_posts_ids)) {
            $id_post = $row['id_post'];
            // Excluir comentários do post
            $sql_delete_comentarios_post = "DELETE FROM comentarios_blog WHERE id_post = '$id_post'";
            mysqli_query($conn, $sql_delete_comentarios_post);
        }
    }
    // Agora excluir os posts do blog
    $sql_blog = "DELETE FROM blog WHERE id_utilizador = '$id_utilizador'";
    mysqli_query($conn, $sql_blog);
}

// 4. Excluir registros de carrinho (se existir)
$sql_check_carrinho = "SHOW TABLES LIKE 'carrinho'";
$result_check = mysqli_query($conn, $sql_check_carrinho);
if (mysqli_num_rows($result_check) > 0) {
    $sql_carrinho = "DELETE FROM carrinho WHERE id_utilizador = '$id_utilizador'";
    mysqli_query($conn, $sql_carrinho);
}

// 5. Excluir itens_encomenda relacionados às encomendas do utilizador
$sql_check_encomendas = "SHOW TABLES LIKE 'encomendas'";
$result_check = mysqli_query($conn, $sql_check_encomendas);
if (mysqli_num_rows($result_check) > 0) {
    $sql_encomendas_ids = "SELECT id_encomenda FROM encomendas WHERE id_utilizador = '$id_utilizador'";
    $result_encomendas_ids = mysqli_query($conn, $sql_encomendas_ids);
    if ($result_encomendas_ids && mysqli_num_rows($result_encomendas_ids) > 0) {
        while ($row = mysqli_fetch_assoc($result_encomendas_ids)) {
            $id_encomenda = $row['id_encomenda'];
            
            // Verificar se tabela itens_encomenda existe
            $sql_check_itens = "SHOW TABLES LIKE 'itens_encomenda'";
            $result_check_itens = mysqli_query($conn, $sql_check_itens);
            if (mysqli_num_rows($result_check_itens) > 0) {
                $sql_delete_itens = "DELETE FROM itens_encomenda WHERE id_encomenda = '$id_encomenda'";
                mysqli_query($conn, $sql_delete_itens);
            }
        }
    }
    
    // 6. Excluir encomendas associadas ao utilizador
    $sql_encomendas = "DELETE FROM encomendas WHERE id_utilizador = '$id_utilizador'";
    mysqli_query($conn, $sql_encomendas);
}


// Finalmente, excluir o utilizador
$sql_excluir = "DELETE FROM utilizadores WHERE id_utilizador = '$id_utilizador'";

if (mysqli_query($conn, $sql_excluir)) {
    // Verificar se realmente foi eliminado
    if (mysqli_affected_rows($conn) > 0) {
        header('Location: eliminar_utilizador.php?msg=Utilizador eliminado com sucesso');
    } else {
        header('Location: eliminar_utilizador.php?msg=Nenhum utilizador foi eliminado');
    }
    exit;
} else {
    // Erro na exclusão
    $erro = mysqli_error($conn);
    header('Location: eliminar_utilizador.php?msg=Erro ao eliminar utilizador: ' . urlencode($erro));
    exit;
}

// Fechar a conexão
mysqli_close($conn);
?>