<?php include 'conexao.php'; ?>
<?php include 'cabecalho.php'; ?> <!-- Inclusão do cabeçalho -->

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
        <p class="breadcrumbs mb-0"><span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span> <span>Conta <i class="fa fa-chevron-right"></i></span></p>
        <h2 class="mb-0 bread">Detalhes da Conta</h2>
      </div>
    </div>
  </div>
</div>

<!-- Detalhes da Conta -->
<section class="ftco-section">
  <div class="container">
    <div class="row">      <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
          <h2>A Minha Conta</h2>
          <div>
            <a href="meus_comentarios.php" class="btn btn-info mr-2">
              <i class="fas fa-comments"></i> Os meus Comentários
            </a>
            <a href="meus_blogs.php" class="btn btn-success">
              <i class="fas fa-blog"></i> Os meus Blogs
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-6 d-flex align-items-stretch ftco-animate">
        <div class="text p-4 bg-light">
          <?php
          // Verificar se o utilizador está logado
          if (!isset($_SESSION['id_utilizador'])) {
              header("Location: login.php");
              exit;
          }

          // Consultar os detalhes do utilizador no banco de dados
          $id_utilizador = $_SESSION['id_utilizador'];
          $queryUtilizador = "SELECT * FROM utilizadores WHERE id_utilizador = $id_utilizador";
          $resultado = mysqli_query($conn, $queryUtilizador);

          if (!$resultado || mysqli_num_rows($resultado) === 0) {
              echo '<p>Erro ao carregar os detalhes da conta.</p>';
          } else {
              $utilizador = mysqli_fetch_assoc($resultado);
              echo '
              <h3 class="heading mb-3">Olá, ' . htmlspecialchars($utilizador['nome']) . '</h3>
              <p><strong>Email:</strong> ' . htmlspecialchars($utilizador['email']) . '</p>
              <p><a href="editar_conta.php" class="btn btn-primary">Editar Dados</a></p>
              <p><a href="minhas_encomendas.php" class="btn btn-primary">Ver Encomendas</a></p>
              <p><a href="meus_gostos.php" class="btn btn-primary">Gostos</a></p>
              ';
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'rodape.php'; ?> <!-- Inclusão do rodapé -->

<!-- Scripts -->
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