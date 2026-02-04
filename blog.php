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
          <span>Blog <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Blog</h2>
      </div>
    </div>
  </div>
</div>

<section class="ftco-section">
  <div class="container">
    <div class="row mb-4">
      <div class="col text-center">
        <a href="criar_blog.php" class="btn btn-primary">Faz o teu próprio blog</a>
      </div>
    </div>
    <div class="row d-flex">
      <?php
      // Consulta para buscar os blogs do banco de dados
      $query = "SELECT * FROM blog ORDER BY data_publicacao DESC";
      $result = mysqli_query($conn, $query);

      if (mysqli_num_rows($result) > 0) {
          while ($blog = mysqli_fetch_assoc($result)) {
              echo '
              <div class="col-lg-6 d-flex align-items-stretch ftco-animate">
                <div class="blog-entry d-md-flex">
                  <a href="blog-single.php?id=' . $blog['id_post'] . '" class="block-20 img" style="background-image: url(\'' . $blog['imagem'] . '\');"></a>
                  <div class="text p-4 bg-light">
                    <div class="meta">
                      <p><span class="fa fa-calendar"></span> ' . date("d F Y", strtotime($blog['data_publicacao'])) . '</p>
                    </div>
                    <h3 class="heading mb-3"><a href="blog-single.php?id=' . $blog['id_post'] . '">' . $blog['titulo'] . '</a></h3>
                    <p>' . $blog['resumo'] . '</p>
                    <a href="blog-single.php?id=' . $blog['id_post'] . '" class="btn-custom">Continue <span class="fa fa-long-arrow-right"></span></a>
                  </div>
                </div>
              </div>';
          }
      } else {
          echo '<p>Nenhum blog disponível no momento.</p>';
      }
      ?>
    </div>
    <div class="row mt-5">
      <div class="col text-center">
        <div class="block-27">
          <ul>
            <li><a href="#"><</a></li>
            <li class="active"><span>1</span></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">4</a></li>
            <li><a href="#">5</a></li>
            <li><a href="#">></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'rodape.php'; ?> <!-- Inclusão do rodapé -->
<!-- loader -->
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
<script src="js/main.js"></script>
</body>
</html>
