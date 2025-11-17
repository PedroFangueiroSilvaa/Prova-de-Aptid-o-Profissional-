<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Estabelecer ligação à base de dados
include 'conexao.php';

// Verificar se foi fornecido um ID de variação
if (!isset($_GET['id_variacao'])) {
    header('Location: listar_produtos.php');
    exit();
}

$id_variacao = $_GET['id_variacao'];

// Buscar informações da variação para redirecionar após a exclusão
$queryVariacao = "SELECT codigo_base FROM variacoes_produto WHERE id_variacao = $id_variacao";
$resultVariacao = mysqli_query($conn, $queryVariacao);
$variacao = mysqli_fetch_assoc($resultVariacao);

if ($variacao) {
    // Apagar a variação
    $queryDelete = "DELETE FROM variacoes_produto WHERE id_variacao = $id_variacao";
    if (mysqli_query($conn, $queryDelete)) {
        header("Location: gerenciar_variacoes.php?id_produto=" . $variacao['codigo_base']);
        exit();
    } else {
        $erro = "Erro ao apagar a variação: " . mysqli_error($conn);
    }
} else {
    header('Location: listar_produtos.php');
    exit();
}

// Incluir o cabeçalho
include 'cabecalho2.php';

// Fechar conexão com a base de dados
mysqli_close($conn);
?>