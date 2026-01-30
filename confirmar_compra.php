<?php
session_start();
$encomendaID = $_GET['encomenda'] ?? null;
if (!$encomendaID) {
    echo "Erro ao processar a encomenda.";
    exit();
}
include 'conexao.php';

// Buscar detalhes da encomenda
$sqlDetalhes = "SELECT e.id_encomenda, e.data_encomenda, e.status, u.nome AS utilizador
                FROM encomendas e
                INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador
                WHERE e.id_encomenda = $encomendaID";
$resultDetalhes = mysqli_query($conn, $sqlDetalhes);

if (!$resultDetalhes) {
    echo "<div class='alert alert-danger'>Erro na consulta de detalhes: " . mysqli_error($conn) . "</div>";
    exit();
}

$encomenda = mysqli_fetch_assoc($resultDetalhes);

if (!$encomenda) {
    echo "<div class='alert alert-danger'>Encomenda #$encomendaID não encontrada.</div>";
    exit();
}

// Primeiro, vamos verificar se há itens na encomenda
$checkItems = "SELECT sku FROM itens_encomenda WHERE id_encomenda = $encomendaID";
$checkResult = mysqli_query($conn, $checkItems);

// Buscar itens da encomenda com uma abordagem simplificada
$sqlItens = "SELECT i.id_item, p.nome AS produto, p.imagem, i.sku, 
                    c.descricao AS cor, t.descricao AS tamanho, 
                    i.quantidade, i.preco_unitario
             FROM itens_encomenda i
             LEFT JOIN produtos p ON p.codigo_base = SUBSTRING_INDEX(i.sku, '/', 1)
             LEFT JOIN cores c ON c.codigo_cor = SUBSTRING_INDEX(SUBSTRING_INDEX(i.sku, '/', 4), '/', -1)
             LEFT JOIN tamanhos t ON t.codigo_tamanho = SUBSTRING_INDEX(i.sku, '/', -1)
             WHERE i.id_encomenda = $encomendaID";

// Tratamento de erro para a consulta SQL
$resultItens = mysqli_query($conn, $sqlItens);
if (!$resultItens) {
    echo "<div class='alert alert-danger'>Erro na consulta SQL: " . mysqli_error($conn) . "<br>Consulta: " . $sqlItens . "</div>";
}

include 'cabecalho.php'; // Inclusão do cabeçalho
?>

<style>
.bg-video {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  object-fit: cover;
  z-index: 0;
  pointer-events: none;
}
.hero-wrap .overlay,
.hero-wrap .container {
  position: relative;
  z-index: 1;
}
.hero-wrap {
  position: relative;
  overflow: hidden;
}
</style>
<!-- Banner -->
<div class="hero-wrap position-relative hero-wrap-2" data-stellar-background-ratio="0.5">
  <video autoplay muted loop playsinline class="bg-video">
    <source src="imagens/INDEX.mp4" type="video/mp4">
    O seu navegador não suporta vídeo em HTML5.
  </video>
  <div class="overlay"></div>
  <div class="container">
    <div class="row no-gutters slider-text align-items-end justify-content-center">
      <div class="col-md-9 ftco-animate mb-5 text-center">
        <p class="breadcrumbs mb-0"><span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span> <span>Confirmação <i class="fa fa-chevron-right"></i></span></p>
        <h2 class="mb-0 bread">Compra Confirmada</h2>
      </div>
    </div>
  </div>
</div>

<!-- Conteúdo Principal -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body">                    <h1 class="text-center text-primary mb-4">Compra Confirmada!</h1>
                    <p class="text-center lead">Obrigado, <?php echo htmlspecialchars($encomenda['utilizador'], ENT_QUOTES, 'UTF-8'); ?>. A sua compra foi processada com sucesso.</p>
                    <div class="text-center">
                        <p class="text-muted">Encomenda #<?php echo htmlspecialchars($encomenda['id_encomenda'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-muted mb-5">Data da Encomenda: <?php echo htmlspecialchars($encomenda['data_encomenda'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="badge badge-success p-2">Status: <?php echo htmlspecialchars($encomenda['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    
                    <h3 class="text-center mb-4">Itens da Encomenda:</h3>
                    <div class="table-responsive">                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col" style="color: black;">Imagem</th>
                                    <th scope="col" style="color: black;">Produto</th>
                                    <th scope="col" style="color: black;">Cor</th>
                                    <th scope="col" style="color: black;">Tamanho</th>
                                    <th scope="col" style="color: black;">Quantidade</th>
                                    <th scope="col" style="color: black;">Preço Unitário</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = mysqli_fetch_assoc($resultItens)):
        // Lógica igual ao cart.php para extrair dados do SKU
        $skuParts = explode('-', $item['sku']);
        $codigo_base = isset($skuParts[0], $skuParts[1], $skuParts[2]) ? ($skuParts[0] . '-' . $skuParts[1] . '-' . $skuParts[2]) : '';
        $produto = null;
        if ($codigo_base !== '') {
            $query_produto = mysqli_query($conn, "SELECT nome, imagem FROM produtos WHERE codigo_base = '".mysqli_real_escape_string($conn, $codigo_base)."' LIMIT 1");
            if ($query_produto && mysqli_num_rows($query_produto) > 0) {
                $produto = mysqli_fetch_assoc($query_produto);
            }
        }
        $imagem = $produto && !empty($produto['imagem']) ? htmlspecialchars($produto['imagem'], ENT_QUOTES, 'UTF-8') : 'imagens/no-image.png';
        $nome = $produto['nome'] ?? 'Produto não encontrado';
        // Buscar cor e tamanho
        $cor_nome = 'Não especificado';
        $tamanho_nome = 'Não especificado';
        if (isset($skuParts[4]) && $skuParts[4] !== '') {
            $query_cor = mysqli_query($conn, "SELECT descricao FROM cores WHERE codigo_cor = '".mysqli_real_escape_string($conn, $skuParts[4])."' LIMIT 1");
            if ($query_cor && mysqli_num_rows($query_cor) > 0) {
                $cor_row = mysqli_fetch_assoc($query_cor);
                $cor_nome = $cor_row['descricao'] ?? $cor_nome;
            }
        }
        if (isset($skuParts[3]) && $skuParts[3] !== '') {
            $query_tamanho = mysqli_query($conn, "SELECT descricao FROM tamanhos WHERE codigo_tamanho = '".mysqli_real_escape_string($conn, $skuParts[3])."' LIMIT 1");
            if ($query_tamanho && mysqli_num_rows($query_tamanho) > 0) {
                $tamanho_row = mysqli_fetch_assoc($query_tamanho);
                $tamanho_nome = $tamanho_row['descricao'] ?? $tamanho_nome;
            }
        }
    ?>
    <tr>
        <td>
            <?php if (!empty($imagem)): ?>
                <img src="<?php echo $imagem; ?>" alt="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="max-width: 80px;">
            <?php else: ?>
                <img src="imagens/no-image.png" alt="Sem imagem" class="img-thumbnail" style="max-width: 80px;">
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($cor_nome, ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($tamanho_nome, ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($item['quantidade'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?> €</td>
    </tr>
    <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg">Voltar para a página inicial</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rodapé -->
<div class="footer mt-5">
    <?php include 'rodape.php'; // Inclusão do rodapé ?>
</div>

<?php
mysqli_close($conn);
?>