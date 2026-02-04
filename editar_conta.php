<?php include 'conexao.php'; ?>
<?php include 'cabecalho.php'; ?> <!-- Inclusão do cabeçalho -->

<!-- Banner -->
<section class="hero-wrap hero-wrap-2" data-stellar-background-ratio="0.5">
  <video autoplay muted loop playsinline class="bg-video">
    <source src="imagens/INDEX.mp4" type="video/mp4">
    O seu navegador não suporta vídeo em HTML5.
  </video>
  <div class="overlay"></div>
  <div class="container">
    <div class="row no-gutters slider-text align-items-end justify-content-center">
      <div class="col-md-9 ftco-animate mb-5 text-center">
        <p class="breadcrumbs mb-0"><span class="mr-2"><a href="index.html">Home <i class="fa fa-chevron-right"></i></a></span> <span>Editar Conta <i class="fa fa-chevron-right"></i></span></p>
        <h2 class="mb-0 bread">Editar Conta</h2>
      </div>
    </div>
  </div>
</section>

<!-- Formulário de Edição da Conta -->
<section class="ftco-section">
  <div class="editar-conta-container">
    <div class="row d-flex">
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
              <form action="atualizar_conta.php" method="POST">
                <div class="mb-3">
                  <label for="nome" class="form-label">Nome</label>
                  <input type="text" class="form-control" id="nome" name="nome" value="' . htmlspecialchars($utilizador['nome']) . '" required>
                </div>
                <div class="mb-3">
                  <label for="em   m-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" value="' . htmlspecialchars($utilizador['email']) . '" required>
                </div>
                <div class="mb-3">
                  <label for="palavra_passe" class="form-label">Nova Password (opcional)</label>
                  <input type="password" class="form-control" id="palavra_passe" name="palavra_passe" placeholder="Deixe em branco para manter a senha atual">
                </div>
                <button type="submit" class="btn btn-primary">Atualizar Dados</button>
              </form>';
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
<style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .editar-conta-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 30px 30px 20px 30px;
        }
        .alert {
            margin-bottom: 20px;
        }
        .btn-voltar {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 18px;
            background: #ff914d;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-voltar:hover {
            background: #e65100;
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
</body>
</html>