<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

include 'conexao.php';
include 'validar.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

// Verificar se o ID da encomenda foi fornecido
if (!isset($_GET['id'])) {
    header("Location: minhas_encomendas.php");
    exit;
}

$id_encomenda = $_GET['id'];
$id_utilizador = $_SESSION['id_utilizador'];

// Buscar detalhes da encomenda
$sql_encomenda = "SELECT e.*, u.nome as nome_utilizador, u.email 
                  FROM encomendas e 
                  INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador 
                  WHERE e.id_encomenda = $id_encomenda AND e.id_utilizador = $id_utilizador";
$result_encomenda = mysqli_query($conn, $sql_encomenda);
$encomenda = mysqli_fetch_assoc($result_encomenda);

if (!$encomenda) {
    header("Location: minhas_encomendas.php");
    exit;
}

// Buscar itens da encomenda
$sql_itens = "SELECT ie.*, p.nome as nome_produto, p.imagem, vp.sku, p.codigo_base, c.descricao as cor, t.descricao as tamanho
              FROM itens_encomenda ie
              INNER JOIN variacoes_produto vp ON ie.sku = vp.sku
              INNER JOIN produtos p ON vp.codigo_base = p.codigo_base
              LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor
              LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
              WHERE ie.id_encomenda = $id_encomenda";
$result_itens = mysqli_query($conn, $sql_itens);

// Verificar se o usuário já fez review desta encomenda
$sql_review = "SELECT * FROM reviews_encomendas WHERE id_encomenda = $id_encomenda AND id_utilizador = $id_utilizador";
$result_review = mysqli_query($conn, $sql_review);
$review = mysqli_fetch_assoc($result_review);

// Verificar se o usuário já fez review de cada produto
$sql_reviews_produtos = "SELECT rp.*, p.nome as nome_produto, p.imagem 
                        FROM reviews_produtos rp 
                        JOIN produtos p ON rp.codigo_base = p.codigo_base 
                        WHERE rp.id_encomenda = $id_encomenda AND rp.id_utilizador = $id_utilizador";
$result_reviews_produtos = mysqli_query($conn, $sql_reviews_produtos);
$reviews_produtos = [];
while ($row = mysqli_fetch_assoc($result_reviews_produtos)) {
    $reviews_produtos[$row['codigo_base']] = $row;
}

// Incluir o cabeçalho após todos os redirecionamentos
include 'cabecalho.php';
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
        <p class="breadcrumbs mb-0">
          <span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span>
          <span>Detalhes da Encomenda <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Detalhes da Encomenda</h2>
      </div>
    </div>
  </div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Detalhes da Encomenda #<?php echo $encomenda['id_encomenda']; ?></h2>
            </div>
            
            <!-- Informações da Encomenda -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informações do Cliente</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($encomenda['nome_utilizador']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($encomenda['email']); ?></p>
                            <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($encomenda['data_encomenda'])); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($encomenda['status']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Endereço de Entrega</h5>
                        </div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($encomenda['local_envio'] ?? '')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Review da Encomenda -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Avaliação da Encomenda</h5>
                </div>
                <div class="card-body">
                    <?php if ($review): ?>
                        <!-- Mostrar review existente -->
                        <div class="review-content">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Sua Avaliação</h6>
                                <div>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star <?php echo $i <= $review['classificacao'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['comentario'])); ?></p>
                            <small class="text-muted">Avaliado em <?php echo date('d/m/Y H:i', strtotime($review['data_review'])); ?></small>
                        </div>
                    <?php else: ?>
                        <!-- Botão para dar review -->
                        <a href="review_encomenda.php?id=<?php echo $encomenda['id_encomenda']; ?>" class="btn btn-success">
                            <i class="fa fa-star"></i> Dar Review à Encomenda
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lista de Produtos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Produtos da Encomenda</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Variação</th>
                                    <th>Quantidade</th>
                                    <th>Preço</th>
                                    <th>Total</th>
                                    <th>Avaliação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = mysqli_fetch_assoc($result_itens)): ?>
                                    <?php 
                                    // Verificar se o produto está cancelado
                                    $status_item = isset($item['status']) ? $item['status'] : '';
                                    $mostrar_item = ($status_item !== 'Cancelado');
                                    
                                    // Se o produto não estiver cancelado, exibir normalmente
                                    if ($mostrar_item):
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['imagem'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['imagem']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['nome_produto']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($item['nome_produto']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['tamanho'] . ' - ' . $item['cor']); ?></td>
                                        <td><?php echo $item['quantidade']; ?></td>
                                        <td>€<?php echo number_format($item['preco_unitario'], 2); ?></td>
                                        <td>€<?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2); ?></td>
                                        <td>
                                            <?php if (isset($reviews_produtos[$item['codigo_base']])): ?>
                                                <!-- Mostrar review existente do produto -->
                                                <div class="review-content">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fa fa-star <?php echo $i <= $reviews_produtos[$item['codigo_base']]['classificacao'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($reviews_produtos[$item['codigo_base']]['data_review'])); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <!-- Botão para dar review do produto -->
                                                <a href="review_produto.php?sku=<?php echo $item['sku']; ?>&encomenda=<?php echo $encomenda['id_encomenda']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fa fa-star"></i> Avaliar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; // Fim do if que verifica se o item não está cancelado ?>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="minhas_encomendas.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
  <script src="js/jquery-migrate-3.0.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.easing.1.3.js"></script>
  <script src="js/jquery.waypoints.min.js"></script>
  <script src="js/jquery.stellar.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/jquery.animateNumber.min.js"></script>
  <script src="js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>

<?php include 'rodape.php'; ?>