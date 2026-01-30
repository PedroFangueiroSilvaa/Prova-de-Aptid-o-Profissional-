<?php 
include 'conexao.php';
include 'cabecalho.php'; // Incluindo o cabeçalho que já contém as tags DOCTYPE, html, head e body
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
          <span>Sobre <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Sobre Nós</h2>
      </div>
    </div>
  </div>
</div>

<section class="ftco-intro">
  <div class="container">
    <div class="row no-gutters">
      <div class="col-md-4 d-flex">
        <div class="intro d-lg-flex w-100 ftco-animate">
          <div class="icon">
            <i class="fas fa-headset"></i>
          </div>
          <div class="text">
            <h2>Suporte onnline 24/7</h2>
            <p>Temos um suporte de online sempre disponivel para ti e estamos prontos para responder a qualquer dúvida.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 d-flex">
        <div class="intro color-1 d-lg-flex w-100 ftco-animate">
          <div class="icon">
            <i class="fas fa-undo"></i>
          </div>
          <div class="text">
            <h2>Devolução de dinheiro garantida</h2>
            <p>Se por acaso não gostares de algum produto ou te tiveres enganado no tamanho ou cor podes sempre devolver o produto e damos todo o dinheiro de volta.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 d-flex">
        <div class="intro color-2 d-lg-flex w-100 ftco-animate">
          <div class="icon">
            <i class="fas fa-shipping-fast"></i>
          </div>
          <div class="text">
            <h2>Portes &amp; Devoluções gratuitos</h2>
            <p>Os portes para estes produtos são gratuitos e as devoluções também.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'historia.php'; ?>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <?php
            // Consulta para buscar os 6 produtos mais caros e pegar apenas a imagem de cada produto
            $sql = "
                SELECT nome, preco, imagem
                FROM produtos
                ORDER BY preco DESC
                LIMIT 6
            ";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // Loop pelos produtos
                while ($row = $result->fetch_assoc()) {
                    echo '
                    <div class="col-lg-2 col-md-4">
                        <div class="sort w-100 text-center ftco-animate">
                            <div class="img" style="background-image: url(\'' . htmlspecialchars($row['imagem'], ENT_QUOTES, 'UTF-8') . '\');"></div>
                            <h3>' . htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') . '</h3>
                            <p><strong>€' . number_format($row['preco'], 2, ',', '.') . '</strong></p>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="text-center">Não foram encontrados produtos.</p>';
            }
            ?>
        </div>
    </div>
</section>

<?php 
// This line includes the "reviews.php" file, which likely contains customer reviews.
?>

<section class="ftco-counter ftco-section ftco-no-pt ftco-no-pb img bg-light" id="section-counter">
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text align-items-center">
            <strong class="number" data-number="120">0</strong>
            <span>Os nossos clientes satisfeitos</span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text align-items-center">
            <strong class="number" data-number="14">0</strong>
            <span>Anos de expriência</span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text align-items-center">
            <strong class="number" data-number="1">1</strong>
            <span>Os melhores no ramo do boxing</span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
        <div class="block-18 py-4 mb-4">
          <div class="text align-items-center">
            <strong class="number" data-number="20">4</strong>
            <span>As nossas marcas</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
// This line includes the "rodape.php" file, which likely contains the footer of the website.
include 'rodape.php'; 
?>

<div id="ftco-loader" class="show fullscreen">
  <svg class="circular" width="48px" height="48px">
    <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
    <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
  </svg>
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

</body>
</html>