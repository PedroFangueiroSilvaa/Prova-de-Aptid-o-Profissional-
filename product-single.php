<?php
// Incluir a conexão com o banco de dados
include 'conexao.php';

// Iniciar sessão
session_start();
if (!isset($_SESSION['id_utilizador'])) {
    if (!isset($_SESSION['session_id'])) {
        $_SESSION['session_id'] = bin2hex(random_bytes(16));
    }
}

// Obter o código base do produto via URL
$codigoBase = isset($_GET['codigo_base']) ? $_GET['codigo_base'] : '';
if (empty($codigoBase)) {
    echo "Produto não encontrado.";
    exit;
}

// Consultar os detalhes do produto usando 'codigo_base'
$sqlProduto = "SELECT produtos.*, marcas.nome AS nome_marca, marcas.id_marca, fornecedores.nome AS nome_fornecedor, 
                      categorias.nome AS nome_categoria, categorias.id_categoria 
               FROM produtos
               INNER JOIN marcas ON produtos.id_marca = marcas.id_marca
               INNER JOIN fornecedores ON produtos.id_fornecedor = fornecedores.id_fornecedor
               INNER JOIN categorias ON produtos.id_categoria = categorias.id_categoria
               WHERE produtos.codigo_base = '$codigoBase'";
$resultProduto = mysqli_query($conn, $sqlProduto);

if (mysqli_num_rows($resultProduto) == 0) {
    echo "Produto não encontrado.";
    exit;
}

$produto = mysqli_fetch_assoc($resultProduto);

// Obter o caminho da imagem do produto
$imagemProduto = htmlspecialchars($produto['imagem'], ENT_QUOTES, 'UTF-8');

// Consultar as cores disponíveis para o produto
$sqlCores = "SELECT DISTINCT c.codigo_cor, c.descricao AS cor 
             FROM variacoes_produto vp
             INNER JOIN cores c ON vp.codigo_cor = c.codigo_cor
             WHERE vp.codigo_base = '$codigoBase'";
$resultCores = mysqli_query($conn, $sqlCores);
$cores = mysqli_fetch_all($resultCores, MYSQLI_ASSOC);

// Consultar os tamanhos disponíveis para o produto
$sqlTamanhos = "SELECT DISTINCT t.codigo_tamanho, t.descricao AS tamanho 
                FROM variacoes_produto vp
                INNER JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
                WHERE vp.codigo_base = '$codigoBase'";
$resultTamanhos = mysqli_query($conn, $sqlTamanhos);
$tamanhos = mysqli_fetch_all($resultTamanhos, MYSQLI_ASSOC);

// Obter o caminho da pasta de imagens do produto
$pastaImagens = "imagens/produtos/" . $codigoBase;
$imagens = [];
if (is_dir($pastaImagens)) {
    $ficheiros = scandir($pastaImagens);
    foreach ($ficheiros as $ficheiro) {
        if ($ficheiro !== '.' && $ficheiro !== '..') {
            $caminhoCompleto = $pastaImagens . '/' . $ficheiro;
            // Apenas ficheiros de imagem
            if (is_file($caminhoCompleto) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $ficheiro)) {
                $imagens[] = $caminhoCompleto;
            }
        }
    }
}
// Se não houver imagens na pasta, usar a imagem principal do produto
if (empty($imagens) && !empty($produto['imagem'])) {
    $imagens[] = $produto['imagem'];
}
?>

<style>
    /* Container principal do produto */
    .product-container {
        display: grid;
        grid-template-columns: 70% 30%; /* Ajustado para melhor distribuição */
        gap: 30px;
        padding: 20px;
        max-width: 100%;
        margin: 0 auto;
    }

    /* Estilos para a galeria de imagens */
    .product-gallery {
        position: relative;
        margin-bottom: 30px;
        max-width: 100%;
        margin: 0;  /* Removido margin auto */
        padding-right: 50px; /* Adicionado padding à direita */
    }

    .main-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        width: 100%;
        height: 800px;
        background-color: #fff;
        margin-left: -50px; /* Puxar para a esquerda */
    }

    .main-image {
        position: relative;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background-color: #fff;
        transition: transform 0.3s ease;
        padding: 20px;
        margin-left: -20px; /* Puxar a imagem mais para a esquerda */
    }

    .main-image:hover {
        transform: scale(1.05);
    }

    /* Ajuste do container das miniaturas */
    .thumbnail-container {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px 0;
        scrollbar-width: thin;
        justify-content: flex-start; /* Alinhado à esquerda */
        margin-left: -50px; /* Alinhar com a imagem principal */
    }

    .thumbnail-container::-webkit-scrollbar {
        height: 6px;
    }

    .thumbnail-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .thumbnail-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .thumbnail {
        width: 150px; /* Ajustado para um tamanho mais proporcional */
        height: 150px;
        object-fit: contain;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        background-color: #fff;
        padding: 10px;
    }

    .thumbnail:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .thumbnail.active {
        border-color: #007bff;
    }

    /* Zoom effect container */
    .zoom-container {
        position: relative;
        overflow: hidden;
        background-color: #fff;
    }

    .zoom-lens {
        position: absolute;
        border: 1px solid #d4d4d4;
        width: 100px;
        height: 100px;
        background-repeat: no-repeat;
        cursor: crosshair;
        display: none;
    }

    .zoom-result {
        position: absolute;
        top: 0;
        left: 105%;
        width: 1000px; /* Aumentado ainda mais */
        height: 1000px;
        border: 1px solid #d4d4d4;
        display: none;
        overflow: hidden;
        background-repeat: no-repeat;
        z-index: 1000;
        border-radius: 5px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        background-color: #fff;
    }

    /* Ajuste dos detalhes do produto */
    .product-details {
        padding-left: 20px;
        padding-right: 40px;
    }

    /* Responsividade */
    @media (max-width: 1200px) {
        .main-image-container {
            height: 600px; /* Altura menor para telas menores */
        }

        .product-container {
            grid-template-columns: 75% 25%;
        }
    }

    @media (max-width: 768px) {
        .main-image-container {
            height: 400px;
        }

        .product-container {
            grid-template-columns: 1fr;
        }

        .thumbnail {
            width: 100px;
            height: 100px;
        }
    }

    /* Estilos para a seção de reviews */
    .reviews-section {
        padding: 40px 0;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin-top: 50px;
    }

    .average-rating {
        padding: 20px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .stars {
        font-size: 1.2rem;
        margin: 10px 0;
    }

    .rating-bar .progress {
        background-color: #e9ecef;
        border-radius: 5px;
    }

    .review-item {
        transition: transform 0.2s;
    }

    .review-item:hover {
        transform: translateY(-2px);
    }

    .review-header {
        display: flex;
        flex-direction: column;
    }

    .review-header .stars {
        font-size: 0.9rem;
        margin-top: 5px;
    }

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

<?php include 'cabecalho.php'; ?>
<div class="hero-wrap position-relative hero-wrap-2" data-stellar-background-ratio="0.5">
  <video autoplay muted loop playsinline class="bg-video">
    <source src="imagens/INDEX.mp4" type="video/mp4">
    O seu navegador não suporta vídeo em HTML5.
  </video>
  <div class="overlay"></div>
  <div class="container">
    <div class="row no-gutters slider-text align-items-end justify-content-center">
      <div class="col-md-9 ftco-animate mb-5 text-center">
        <p class="breadcrumbs mb-0"><span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span> <span>Produto <i class="fa fa-chevron-right"></i></span></p>
        <h2 class="mb-0 bread">Detalhes do Produto</h2>
      </div>
    </div>
  </div>
</div>

<div class="container">
    <div class="product-header">
    </div>
    <div class="product-container">
        <div class="product-gallery">
            <div class="zoom-container">
                <div class="main-image-container">
                    <img src="<?php echo htmlspecialchars($imagens[0], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8'); ?>" class="main-image" id="mainImage">
                    <div class="zoom-lens"></div>
                    <div class="zoom-result"></div>
                </div>
            </div>
            
            <div class="thumbnail-container">
                <?php
                // Mostrar todas as imagens da pasta do produto
                foreach ($imagens as $index => $imagem) {
                    echo '<img src="' . htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') . '" 
                               alt="Thumbnail ' . ($index + 1) . '" 
                               class="thumbnail ' . ($index === 0 ? 'active' : '') . '"
                               onclick="changeMainImage(this.src, this)">';
                }
                ?>
            </div>
        </div>
        <div class="product-details">
            <h2><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><strong>Marca:</strong> <?= htmlspecialchars($produto['nome_marca'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Categoria:</strong> <?= htmlspecialchars($produto['nome_categoria'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Preço:</strong> €<?= number_format($produto['preco'], 2, ',', '.'); ?></p>
            
            <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($produto['descricao'], ENT_QUOTES, 'UTF-8')); ?></p>

            <!-- Exibir Mensagens de Erro -->
            <?php
            if (isset($_GET['error'])) {
                switch ($_GET['error']) {
                    case 'invalid_data':
                        echo '<p class="text-danger">Erro: Dados inválidos.</p>';
                        break;
                    case 'variation_not_found':
                        echo '<p class="text-danger">Erro: Variação do produto não encontrada.</p>';
                        break;
                    case 'exceed_stock':
                        $stock = isset($_GET['stock']) ? (int)$_GET['stock'] : 0;
                        echo '<p class="text-danger">Erro: A quantidade solicitada excede o stock disponível (' . $stock . ').</p>';
                        break;
                    case 'user_not_identified':
                        echo '<p class="text-danger">Erro: Usuário ou sessão não identificada.</p>';
                        break;
                    case 'database_error':
                        echo '<p class="text-danger">Erro: Problema ao adicionar ao carrinho. Tente novamente mais tarde.</p>';
                        break;
                }
            }
            ?>

            <form action="adicionar_carrinho.php" method="post">
                <!-- Campos ocultos para enviar o codigo_base, id_categoria e id_marca -->
                <input type="hidden" name="codigo_base" value="<?= htmlspecialchars($produto['codigo_base'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id_categoria" value="<?= htmlspecialchars($produto['id_categoria'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id_marca" value="<?= htmlspecialchars($produto['id_marca'], ENT_QUOTES, 'UTF-8'); ?>">

                <!-- Seleção de Cor -->
                <label for="cor">Escolha a cor:</label>
                <select name="codigo_cor" id="cor" class="form-control" required>
                    <option value="">Selecione uma cor</option>
                    <?php if (!empty($cores)): ?>
                        <?php foreach ($cores as $cor): ?>
                            <option value="<?= htmlspecialchars($cor['codigo_cor'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($cor['cor'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option disabled>Nenhuma cor disponível</option>
                    <?php endif; ?>
                </select>

                <!-- Seleção de Tamanho -->
                <label for="tamanho">Escolha o tamanho:</label>
                <select name="codigo_tamanho" id="tamanho" class="form-control" required>
                    <option value="">Selecione primeiro uma cor</option>
                </select>
                
                <!-- Quantidade -->
                <label for="quantidade">Quantidade:</label>
                <input type="number" name="quantidade" id="quantidade" class="form-control" value="1" min="1" max="26" required>

                <!-- Botão de Adicionar ao Carrinho -->
                <button type="submit" class="btn-success mt-3">Adicionar ao carrinho</button>
            </form>
        </div>
    </div>

    <!-- Seção de Reviews -->
    <div class="reviews-section mt-5">
        <h2 class="text-center mb-4">Avaliações do Produto</h2>
        
        <?php
        // Buscar todas as reviews do produto
        $sqlReviews = "SELECT r.*, u.nome as nome_utilizador 
                      FROM reviews_produtos r 
                      JOIN utilizadores u ON r.id_utilizador = u.id_utilizador 
                      WHERE r.codigo_base = '$codigoBase' 
                      ORDER BY r.data_review DESC";
        $resultReviews = mysqli_query($conn, $sqlReviews);
        
        // Calcular média das avaliações
        $sqlMedia = "SELECT AVG(classificacao) as media_classificacao, COUNT(*) as total_reviews 
                    FROM reviews_produtos 
                    WHERE codigo_base = '$codigoBase'";
        $resultMedia = mysqli_query($conn, $sqlMedia);
        $mediaReviews = mysqli_fetch_assoc($resultMedia);
        $mediaClassificacao = $mediaReviews['media_classificacao'] !== null ? round($mediaReviews['media_classificacao'], 1) : 0;
        $totalReviews = $mediaReviews['total_reviews'];
        ?>

        <!-- Resumo das Avaliações -->
        <div class="reviews-summary mb-4">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <div class="average-rating">
                        <h1 class="display-4"><?php echo $mediaClassificacao; ?></h1>
                        <div class="stars">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $mediaClassificacao) {
                                    echo '<i class="fas fa-star text-warning"></i>';
                                } elseif ($i - 0.5 <= $mediaClassificacao) {
                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                } else {
                                    echo '<i class="far fa-star text-warning"></i>';
                                }
                            }
                            ?>
                        </div>
                        <p class="text-muted"><?php echo $totalReviews; ?> avaliações</p>
                    </div>
                </div>
                <div class="col-md-8">
                    <?php
                    // Contar reviews por classificação
                    $sqlDistribuicao = "SELECT classificacao, COUNT(*) as total 
                                      FROM reviews_produtos 
                                      WHERE codigo_base = '$codigoBase' 
                                      GROUP BY classificacao 
                                      ORDER BY classificacao DESC";
                    $resultDistribuicao = mysqli_query($conn, $sqlDistribuicao);
                    
                    while ($row = mysqli_fetch_assoc($resultDistribuicao)) {
                        $percentagem = ($totalReviews > 0) ? ($row['total'] / $totalReviews) * 100 : 0;
                        echo '<div class="rating-bar mb-2">';
                        echo '<div class="d-flex align-items-center">';
                        echo '<span class="me-2">' . $row['classificacao'] . ' <i class="fas fa-star text-warning"></i></span>';
                        echo '<div class="progress flex-grow-1" style="height: 10px;">';
                        echo '<div class="progress-bar bg-warning" role="progressbar" style="width: ' . $percentagem . '%"></div>';
                        echo '</div>';
                        echo '<span class="ms-2">' . $row['total'] . '</span>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Lista de Reviews -->
        <div class="reviews-list">
            <?php
            if (mysqli_num_rows($resultReviews) > 0) {
                while ($review = mysqli_fetch_assoc($resultReviews)) {
                    $data = date('d/m/Y', strtotime($review['data_review']));
                    echo '<div class="review-item card mb-3">';
                    echo '<div class="card-body">';
                    echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                    echo '<div class="review-header">';
                    echo '<h5 class="card-title mb-0">' . htmlspecialchars($review['nome_utilizador']) . '</h5>';
                    echo '<div class="stars">';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $review['classificacao']) {
                            echo '<i class="fas fa-star text-warning"></i>';
                        } else {
                            echo '<i class="far fa-star text-warning"></i>';
                        }
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '<small class="text-muted">' . $data . '</small>';
                    echo '</div>';
                    echo '<p class="card-text">' . nl2br(htmlspecialchars($review['comentario'])) . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="alert alert-info text-center">Ainda não há avaliações para este produto.</div>';
            }
            ?>
        </div>
    </div>
</div>

<script>
function changeImagePosition(position) {
    const image = document.querySelector('.product-image');
    // Remove todas as classes de posição
    image.classList.remove('product-image-left', 'product-image-right', 'product-image-center');
    // Adiciona a nova classe de posição
    image.classList.add('product-image-' + position);
}

// Função para carregar os tamanhos via AJAX
function carregarTamanhos(codigoBase, codigoCor) {
    const tamanhoSelect = document.getElementById('tamanho');
    tamanhoSelect.innerHTML = '<option value="">Carregando...</option>';
    
    fetch(`buscar_tamanhos.php?codigo_base=${codigoBase}&codigo_cor=${codigoCor}`)
        .then(response => response.text())
        .then(html => {
            tamanhoSelect.innerHTML = html;
        })
        .catch(error => {
            console.error('Erro:', error);
            tamanhoSelect.innerHTML = '<option disabled>Erro ao carregar tamanhos</option>';
        });
}

// Adicionar evento de mudança na seleção de cor
document.getElementById('cor').addEventListener('change', function() {
    const codigoBase = document.querySelector('input[name="codigo_base"]').value;
    const codigoCor = this.value;
    
    if (codigoCor) {
        carregarTamanhos(codigoBase, codigoCor);
    } else {
        document.getElementById('tamanho').innerHTML = '<option value="">Selecione um tamanho</option>';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('mainImage');
    // Função para trocar a imagem principal
    window.changeMainImage = function(src, thumbnail) {
        mainImage.src = src;
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        thumbnail.classList.add('active');
    }
});
</script>
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