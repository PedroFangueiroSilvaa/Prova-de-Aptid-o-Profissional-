<?php
session_start();
include 'validar.php';
include 'conexao.php';
require_once 'vendor/autoload.php'; // Carregar o autoload do Composer

// Definir a chave secreta da Stripe
\Stripe\Stripe::setApiKey('sk_test_51QsoQIGP21KyXclQSrOlFKunYO2X9U0weNvvrFvXRjcH0P9KCCp5Bbz98CmGzsoIDpGcisoZC5iUwujs94lm5oFO00gvpLahcr'); // Chave de teste do Stripe

// Obter o ID do utilizador
$idUtilizador = $_SESSION['id_utilizador'];

// Calcular o total da encomenda
$totalEncomenda = 0.00;

// Buscar todos os itens do carrinho do utilizador ou sessão
$sqlCarrinho = "SELECT * FROM carrinho WHERE " . ($idUtilizador ? "id_utilizador = $idUtilizador" : "session_id = '$sessionId'");
$resultCarrinho = mysqli_query($conn, $sqlCarrinho);

// Verificar se a consulta retornou resultados
if ($resultCarrinho && mysqli_num_rows($resultCarrinho) > 0) {
    while ($item = mysqli_fetch_assoc($resultCarrinho)) {
        // Extrair identificador do produto (codigo_base) do SKU: 3 primeiros blocos
        $skuParts = explode('-', $item['sku']);
        $codigo_base = isset($skuParts[0], $skuParts[1], $skuParts[2]) ? ($skuParts[0] . '-' . $skuParts[1] . '-' . $skuParts[2]) : '';
        $produto = null;
        if ($codigo_base !== '') {
            $query_produto = mysqli_query($conn, "SELECT nome, preco, imagem FROM produtos WHERE codigo_base = '".mysqli_real_escape_string($conn, $codigo_base)."' LIMIT 1");
            if ($query_produto && mysqli_num_rows($query_produto) > 0) {
                $produto = mysqli_fetch_assoc($query_produto);
            }
        }
        $preco_unitario = $produto['preco'] ?? 0;

        // Calcular o total da encomenda
        $totalEncomenda += $item['quantidade'] * $preco_unitario;
    }
} else {
    echo "Erro: Carrinho vazio ou problema ao obter os itens.";
    exit();
}

// Criar uma encomenda com status "pendente"
$dataEncomenda = date('Y-m-d H:i:s');
$sqlEncomenda = "INSERT INTO encomendas (id_utilizador, total, status, data_encomenda) 
                 VALUES ('$idUtilizador', '$totalEncomenda', 'pendente', '$dataEncomenda')";
if (!mysqli_query($conn, $sqlEncomenda)) {
    echo "Erro ao inserir encomenda: " . mysqli_error($conn);
    exit();
}
$idEncomenda = mysqli_insert_id($conn); // Obter o ID da encomenda gerada

// Guardar os itens da encomenda e atualizar o stock
mysqli_data_seek($resultCarrinho, 0); // Reset ao resultado da query do carrinho

// Array para rastrear SKUs já processados
$skusProcessados = [];

while ($item = mysqli_fetch_assoc($resultCarrinho)) {
    $sku = $item['sku'];
    // Verificar se este SKU já foi processado para evitar duplicações
    if (in_array($sku, $skusProcessados)) {
        continue; // Pula este item, já foi processado
    }
    // Adiciona o SKU à lista de processados
    $skusProcessados[] = $sku;
    $quantidade = $item['quantidade'];

    // Extrair codigo_base, cor e tamanho do SKU (formato: bloco1-bloco2-bloco3-bloco4-bloco5)
    $skuParts = explode('-', $sku);
    $codigo_base = isset($skuParts[0], $skuParts[1], $skuParts[2]) ? ($skuParts[0] . '-' . $skuParts[1] . '-' . $skuParts[2]) : '';
    $codigo_tamanho = $skuParts[3] ?? '';
    $codigo_cor = $skuParts[4] ?? '';

    // Buscar preço unitário do produto
    $precoUnitario = 0;
    if ($codigo_base !== '') {
        $query_produto = mysqli_query($conn, "SELECT preco FROM produtos WHERE codigo_base = '".mysqli_real_escape_string($conn, $codigo_base)."' LIMIT 1");
        if ($query_produto && mysqli_num_rows($query_produto) > 0) {
            $produto = mysqli_fetch_assoc($query_produto);
            $precoUnitario = $produto['preco'] ?? 0;
        }
    }

    // Buscar stock disponível da variação
    $stockDisponivel = 0;
    if ($codigo_base !== '' && $codigo_cor !== '' && $codigo_tamanho !== '') {
        $query_stock = mysqli_query($conn, "SELECT stock FROM variacoes_produto WHERE codigo_base = '".mysqli_real_escape_string($conn, $codigo_base)."' AND codigo_cor = '".mysqli_real_escape_string($conn, $codigo_cor)."' AND codigo_tamanho = '".mysqli_real_escape_string($conn, $codigo_tamanho)."' LIMIT 1");
        if ($query_stock && mysqli_num_rows($query_stock) > 0) {
            $row_stock = mysqli_fetch_assoc($query_stock);
            $stockDisponivel = $row_stock['stock'] ?? 0;
        }
    }

    // Verificar se há stock suficiente
    if ($quantidade > $stockDisponivel) {
        echo "Erro: Stock insuficiente para o SKU $sku.";
        exit();
    }

    // Inserir o item na encomenda
    $skuEscaped = mysqli_real_escape_string($conn, $sku);
    $sqlItem = "INSERT INTO itens_encomenda (id_encomenda, sku, quantidade, preco_unitario) 
                VALUES ('$idEncomenda', '$skuEscaped', '$quantidade', '$precoUnitario')";
    if (!mysqli_query($conn, $sqlItem)) {
        echo "Erro ao inserir item na encomenda: " . mysqli_error($conn) . "<br>Query: " . $sqlItem;
        exit();
    }

    // Atualizar o stock do produto na tabela `variacoes_produto`
    $sqlUpdateStock = "UPDATE variacoes_produto 
                      SET stock = stock - '$quantidade' 
                      WHERE codigo_base = '$codigo_base' 
                      AND codigo_cor = '$codigo_cor' 
                      AND codigo_tamanho = '$codigo_tamanho'";
    if (!mysqli_query($conn, $sqlUpdateStock)) {
        echo "Erro ao atualizar stock: " . mysqli_error($conn);
        exit();
    }
}

// Limpar o carrinho após finalizar a compra
$sqlLimparCarrinho = "DELETE FROM carrinho WHERE id_utilizador = '$idUtilizador'";
mysqli_query($conn, $sqlLimparCarrinho);

// Criar uma sessão de pagamento no Stripe
$checkoutSession = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [
        [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Compra na Loja de Boxing',
                ],
                'unit_amount' => $totalEncomenda * 100, // Valor em cêntimos
            ],
            'quantity' => 1,
        ]
    ],
    'mode' => 'payment',
    'success_url' => 'http://localhost/PAP/sucesso.php?session_id={CHECKOUT_SESSION_ID}&encomenda=' . $idEncomenda,
    'cancel_url' => 'http://localhost/PAP/cart.php',
]);

// Fechar a conexão com a base de dados
mysqli_close($conn);

// Redirecionar para a página de checkout do Stripe
header("Location: " . $checkoutSession->url);
exit();
?>