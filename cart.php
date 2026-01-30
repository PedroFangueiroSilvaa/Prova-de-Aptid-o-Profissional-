<?php
// Start a session to track user-specific data
session_start();

// Include the database connection file to allow communication with the database
include 'conexao.php';
// Include the header to properly display favicon
include 'cabecalho.php';

// Get the current session ID (used to identify the user if they are not logged in)
$sessionId = session_id();

// Check if the user is logged in by looking for their user ID in the session
$idUtilizador = $_SESSION['id_utilizador'] ?? null;

// Create a SQL query to fetch the items in the user's cart
// This query retrieves product details (like name, price, image, color, and size) from the database
// Buscar todos os itens do carrinho do utilizador ou sessão
$sqlCarrinho = "SELECT * FROM carrinho WHERE " . ($idUtilizador ? "id_utilizador = $idUtilizador" : "session_id = '$sessionId'");

// Execute the SQL query and store the result
$resultCarrinho = $conn->query($sqlCarrinho);

// Initialize a variable to calculate the total price of all items in the cart
$subtotal = 0;
?>

<!-- Adicionando estilos específicos para esta página -->
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
.product-image {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}
.product-image:hover {
    transform: scale(1.05);
}
.table td {
    vertical-align: middle;
}
</style>

<!-- Hero com vídeo de fundo -->
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
          <span>Carrinho <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">O Meu Carrinho</h2>
      </div>
    </div>
  </div>
</div>

<!-- Section displaying the shopping cart -->
<section class="ftco-section">
    <div class="container">
        <div class="row">
            <div class="table-wrap">
                <!-- Table showing the items in the cart -->
                <table class="table">
                    <thead class="thead-primary">
                        <tr>
                            <th>&nbsp;</th> <!-- Empty column for checkboxes -->
                            <th>&nbsp;</th> <!-- Empty column for product images -->
                            <th>Produto</th> <!-- Column for product names -->
                            <th>Preço</th> <!-- Column for product prices -->
                            <th>Quantidade</th> <!-- Column for product quantities -->
                            <th>Cor</th> <!-- Column for product colors -->
                            <th>Tamanho</th> <!-- Column for product sizes -->
                            <th>Total</th> <!-- Column for total price per product -->
                            <th>&nbsp;</th> <!-- Empty column for the remove button -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Check if there are items in the cart
                        if ($resultCarrinho && $resultCarrinho->num_rows > 0): 
                            // Loop through each item in the cart
                            while ($row = $resultCarrinho->fetch_assoc()): 
                                // Extrair codigo_base do SKU
                                $skuParts = explode('-', $row['sku']);
                                // O identificador do produto é a junção dos 3 primeiros blocos do SKU
                                $codigo_base = isset($skuParts[0], $skuParts[1], $skuParts[2]) ? ($skuParts[0] . '-' . $skuParts[1] . '-' . $skuParts[2]) : '';
                                $produto = null;
                                if ($codigo_base !== '') {
                                    $query_produto = mysqli_query($conn, "SELECT nome, preco, imagem FROM produtos WHERE codigo_base = '".mysqli_real_escape_string($conn, $codigo_base)."' LIMIT 1");
                                    if ($query_produto && mysqli_num_rows($query_produto) > 0) {
                                        $produto = mysqli_fetch_assoc($query_produto);
                                    }
                                }
                                $preco = $produto['preco'] ?? 0;
                                $imagem = $produto && !empty($produto['imagem']) ? htmlspecialchars($produto['imagem'], ENT_QUOTES, 'UTF-8') : 'imagens/default.jpg';
                                $nome = $produto['nome'] ?? 'Produto não encontrado';
                                $totalProduto = $preco * $row['quantidade'];
                                $subtotal += $totalProduto;
                                // Buscar cor e tamanho se existirem nas tabelas
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
                                <tr class="alert" role="alert">
                                    <td>
                                        <!-- Checkbox for selecting the product -->
                                        <label class="checkbox-wrap checkbox-primary">
                                            <input type="checkbox">
                                            <span class="checkmark"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <!-- Display the product image -->
                                        <div class="product-image" style="width: 100px; height: 100px; background-image: url('<?= $imagem ?>'); background-size: cover; background-position: center;"></div>
                                    </td>
                                    <td>
                                        <!-- Display the product name -->
                                        <div class="email">
                                            <span><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </td>
                                    <td>€<?= number_format($preco, 2, ',', '.') ?></td> <!-- Product price -->
                                    <td class="quantity">
                                        <!-- Input field for changing the product quantity -->
                                        <div class="input-group">
                                            <input type="text" name="quantity" class="quantity form-control input-number" value="<?= htmlspecialchars($row['quantidade'], ENT_QUOTES, 'UTF-8') ?>" min="1" max="100">
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($cor_nome, ENT_QUOTES, 'UTF-8') ?></td> <!-- Product color -->
                                    <td><?= htmlspecialchars($tamanho_nome, ENT_QUOTES, 'UTF-8') ?></td> <!-- Product size -->
                                    <td>€<?= number_format($totalProduto, 2, ',', '.') ?></td> <!-- Total price for the product -->
                                    <td>
                                        <!-- Link to remove the product from the cart -->
                                        <a href="remover_do_carrinho.php?sku=<?= urlencode($row['sku']) ?>" class="close" aria-label="Close">
                                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <!-- Message displayed if the cart is empty -->
                            <tr><td colspan="9" class="text-center">O seu carrinho está vazio.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col col-lg-5 col-md-6 mt-5 cart-wrap ftco-animate">
                <div class="cart-total mb-3">
                    <!-- Section showing the cart totals -->
                    <h3>Totais do Carrinho</h3>
                    <p class="d-flex">
                        <span>Subtotal</span> <!-- Subtotal label -->
                        <span>€<?= number_format($subtotal, 2, ',', '.') ?></span> <!-- Subtotal value -->
                    </p>
                    <p class="d-flex">
                        <span>Entrega</span> <!-- Delivery cost label -->
                        <span>€0.00</span> <!-- Delivery cost value -->
                    </p>
                    <p class="d-flex">
                        <span>Desconto</span> <!-- Discount label -->
                        <span>€0.00</span> <!-- Discount value -->
                    </p>
                    <hr>
                    <p class="d-flex total-price">
                        <span>Total</span> <!-- Total label -->
                        <span>€<?= number_format($subtotal, 2, ',', '.') ?></span> <!-- Total value -->
                    </p>
                </div>
                <p class="text-center">
                    <?php if ($idUtilizador): ?>
                        <!-- Button to proceed to checkout if the user is logged in -->
                        <a href="processar_compra.php" class="btn btn-primary py-3 px-4">Finalizar Compra</a>
                    <?php else: ?>
                        <!-- Button to prompt the user to log in if they are not logged in -->
                        <a href="login.php" class="btn btn-primary py-3 px-4">Inicie Sessão para Finalizar</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<?php 
// Include the footer of the website (likely contains additional links and information)
include 'rodape.php'; 
?> 

<!-- Scripts -->
<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<script src="js/main.js"></script>

<!-- Fechar a conexão com o banco de dados -->
</body>
</html>