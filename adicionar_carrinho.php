<?php
// Inclui o arquivo de conexão com o banco de dados para que possamos interagir com ele.
include 'conexao.php';

// Inicia uma sessão para identificar o usuário ou visitante que está acessando o site.
session_start();

// Função para logging
function log_debug($message) {
    $log_file = 'carrinho_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

log_debug("Script inicializado");    // Verifica se o método de envio do formulário é POST (ou seja, se o formulário foi enviado corretamente).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    log_debug("Requisição POST recebida");
    // Obtém os dados enviados pelo formulário (código do produto, cor, tamanho e quantidade).
    $codigoBase = $_POST['codigo_base'] ?? '';
    $codigoCor = $_POST['codigo_cor'] ?? '';
    $codigoTamanho = $_POST['codigo_tamanho'] ?? '';
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    
    log_debug("Dados recebidos: codigo_base=$codigoBase, codigo_cor=$codigoCor, codigo_tamanho=$codigoTamanho, quantidade=$quantidade");
    
    // Verifica se os dados obrigatórios foram preenchidos corretamente.
    if (!$codigoBase || !$codigoCor || !$codigoTamanho || $quantidade <= 0) {
        header("Location: product-single.php?codigo_base=$codigoBase&error=invalid_data");
        exit;
    }
    
    // Consulta no banco de dados para verificar se existe uma variação do produto com os códigos fornecidos.
    $sqlSKU = "SELECT sku, stock FROM variacoes_produto WHERE codigo_base = '$codigoBase' AND codigo_cor = '$codigoCor' AND codigo_tamanho = '$codigoTamanho'";
    $resultSKU = $conn->query($sqlSKU);

    // Se não encontrar a variação do produto, redireciona o usuário com uma mensagem de erro.
    if ($resultSKU->num_rows === 0) {
        header("Location: product-single.php?codigo_base=$codigoBase&error=variation_not_found");
        exit;
    }    // Obtém os dados da variação do produto (SKU antigo e quantidade em estoque).
    $variacao = $resultSKU->fetch_assoc();
    $sku = $variacao['sku']; // Agora usa o SKU real da base de dados
    $stockDisponivel = (int)$variacao['stock'];
    log_debug("SKU obtido: $sku, Stock disponível: $stockDisponivel");
    
    // Verifica se a quantidade solicitada pelo usuário é maior do que o estoque disponível.
    // Se for, redireciona o usuário com uma mensagem de erro informando o estoque disponível.
    if ($quantidade > $stockDisponivel) {
        header("Location: product-single.php?codigo_base=$codigoBase&error=exceed_stock&stock=$stockDisponivel");
        exit;
    }    // Verifica se o usuário está logado. Se estiver, usa o ID do usuário; caso contrário, usa o ID da sessão.
    $idUtilizador = $_SESSION['id_utilizador'] ?? null;
    $sessionId = $idUtilizador ? null : session_id();
    $condicaoCarrinho = $idUtilizador ? "id_utilizador = '$idUtilizador'" : "session_id = '$sessionId'";
    log_debug("Informações do usuário: id_utilizador=" . ($idUtilizador ? $idUtilizador : "NULL") . ", session_id=" . ($sessionId ? $sessionId : "NULL"));    // Verifica no banco de dados se o produto já está no carrinho do usuário ou visitante.
    $sqlVerificarCarrinho = "SELECT quantidade FROM carrinho WHERE sku = '$sku' AND $condicaoCarrinho";
    log_debug("SQL verificar carrinho: $sqlVerificarCarrinho");
    $resultVerificarCarrinho = $conn->query($sqlVerificarCarrinho);
    log_debug("Resultado da verificação: " . ($resultVerificarCarrinho->num_rows > 0 ? "Produto encontrado no carrinho" : "Produto não está no carrinho"));

    // Se o produto já estiver no carrinho, atualiza a quantidade.
    if ($resultVerificarCarrinho->num_rows > 0) {
        // Obtém a quantidade atual do produto no carrinho.
        $quantidadeAtual = (int)$resultVerificarCarrinho->fetch_assoc()['quantidade'];        // Calcula a nova quantidade somando a quantidade atual com a quantidade solicitada.
        $novaQuantidade = $quantidadeAtual + $quantidade;
        
        // Verifica novamente se a nova quantidade excede o estoque disponível.
        if ($novaQuantidade > $stockDisponivel) {
            header("Location: product-single.php?codigo_base=$codigoBase&error=exceed_stock&stock=$stockDisponivel");
            exit;
        }        // Atualiza a quantidade do produto no carrinho no banco de dados.
        $sqlAtualizarCarrinho = "UPDATE carrinho SET quantidade = $novaQuantidade WHERE sku = '$sku' AND $condicaoCarrinho";
        log_debug("SQL atualizar carrinho: $sqlAtualizarCarrinho");
        $query = $sqlAtualizarCarrinho;
    } else {
        // Se o produto não estiver no carrinho, insere um novo registro no banco de dados.
        // Define os valores de ID do usuário ou ID da sessão para o carrinho.
        $idUtilizadorSQL = $idUtilizador ? "'$idUtilizador'" : "NULL";
        $sessionIdSQL = $sessionId ? "'$sessionId'" : "NULL";        // Insere o produto no carrinho com a quantidade solicitada.
        $sqlInserirCarrinho = "INSERT INTO carrinho (id_utilizador, session_id, sku, quantidade) 
                               VALUES ($idUtilizadorSQL, $sessionIdSQL, '$sku', $quantidade)";
        log_debug("SQL inserir no carrinho: $sqlInserirCarrinho");
        $query = $sqlInserirCarrinho;
    }    // Executa a consulta (inserção ou atualização) no banco de dados.
    if ($conn->query($query)) {
        log_debug("Operação bem-sucedida: produto adicionado/atualizado no carrinho");
        // Se a operação for bem-sucedida, redireciona o usuário para a página do carrinho.
        header("Location: cart.php");
    } else {
        log_debug("ERRO ao executar query: " . $conn->error);
        // Se houver um erro no banco de dados, redireciona o usuário com uma mensagem de erro.
        header("Location: product-single.php?codigo_base=$codigoBase&error=database_error");
    }
    exit;
} else {
    // Se o método de envio não for POST, redireciona o usuário para a página inicial com uma mensagem de erro.
    header("Location: index.php?error=invalid_request");
    exit;
}
?>