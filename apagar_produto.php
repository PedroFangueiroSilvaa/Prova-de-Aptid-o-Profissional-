<?php
// Incluir a conexão com a base de dados
include 'conexao.php';

// Verificar se o codigo_base foi passado por GET
if (isset($_GET['codigo_base'])) {
    $codigo_base = $_GET['codigo_base'];

    // Consultar o produto para verificar se ele existe
    $query = "SELECT imagem FROM produtos WHERE codigo_base = '$codigo_base'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $produto = mysqli_fetch_assoc($result);

        if ($produto) {
            // Começar uma transação para garantir que todas as operações sejam concluídas com sucesso
            mysqli_begin_transaction($conn);
            
            try {
                // 1. Primeiro, verificar e excluir itens de carrinho relacionados às variações deste produto
                $deleteCarrinhoQuery = "DELETE FROM carrinho WHERE sku IN (
                    SELECT sku FROM variacoes_produto WHERE codigo_base = '$codigo_base'
                )";
                mysqli_query($conn, $deleteCarrinhoQuery);
                
                // 2. Verificar e excluir reviews do produto
                $deleteReviewsQuery = "DELETE FROM reviews_produtos WHERE codigo_base = '$codigo_base'";
                mysqli_query($conn, $deleteReviewsQuery);
                
                // 3. Excluir todas as variações do produto
                $deleteVariationsQuery = "DELETE FROM variacoes_produto WHERE codigo_base = '$codigo_base'";
                mysqli_query($conn, $deleteVariationsQuery);
                
                // 4. Apagar a imagem associada (se existir)
                if (!empty($produto['imagem']) && file_exists($produto['imagem'])) {
                    unlink($produto['imagem']);
                }
                
                // 5. Finalmente, apagar o produto da base de dados
                $deleteQuery = "DELETE FROM produtos WHERE codigo_base = '$codigo_base'";
                if (mysqli_query($conn, $deleteQuery)) {
                    mysqli_commit($conn);
                    // Mensagem de sucesso com alerta visual
                    $message = "Produto apagado com sucesso!";
                    $messageType = "success";
                    
                    // Fechar a conexão com a base de dados
                    mysqli_close($conn);
                    
                    // Redirecionar com alerta JavaScript
                    echo "<script>
                        alert('$message');
                        window.location.href = 'listar_produtos.php';
                    </script>";
                    exit();
                } else {
                    throw new Exception("Erro ao apagar produto: " . mysqli_error($conn));
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $message = $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "Produto não encontrado!";
            $messageType = "error";
        }
    } else {
        $message = "Erro ao executar a consulta de seleção: " . mysqli_error($conn);
        $messageType = "error";
    }
} else {
    $message = "Código base do produto não fornecido!";
    $messageType = "error";
}

// Fechar a conexão com a base de dados
mysqli_close($conn);

// Redirecionar para a página de listagem com a mensagem
header("Location: listar_produtos.php?message=" . urlencode($message) . "&type=" . urlencode($messageType));
exit();
?>