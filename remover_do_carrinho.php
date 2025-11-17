<?php
session_start();
include 'conexao.php';

// Função para logging
function log_debug($message) {
    $log_file = 'remover_carrinho_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

log_debug("Script inicializado");

if (isset($_GET['sku'])) {
    $sku = $_GET['sku'];
    log_debug("SKU recebido: $sku");

    // Verifica se o utilizador está logado
    if (isset($_SESSION['id_utilizador'])) {
        $idUtilizador = $_SESSION['id_utilizador'];
        $sqlRemover = "DELETE FROM carrinho WHERE sku = '" . $conn->real_escape_string($sku) . "' AND id_utilizador = $idUtilizador";
    } else {
        $sessionId = session_id();
        $sqlRemover = "DELETE FROM carrinho WHERE sku = '" . $conn->real_escape_string($sku) . "' AND session_id = '$sessionId'";
    }
    
    log_debug("SQL para remover: $sqlRemover");

    if ($conn->query($sqlRemover)) {
        log_debug("Produto removido com sucesso");
        header("Location: cart.php"); // Redireciona para o carrinho
        exit;
    } else {
        log_debug("Erro ao remover produto: " . $conn->error);
        echo "Erro ao remover o produto do carrinho: " . $conn->error;
    }
} else {
    log_debug("Produto não encontrado - parâmetro SKU não recebido");
    echo "Produto não encontrado.";
}
// Fechando a conexão com o banco de dados
$conn->close();
?>
