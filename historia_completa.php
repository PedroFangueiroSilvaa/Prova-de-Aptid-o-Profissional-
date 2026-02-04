<?php include 'conexao.php'; ?>
<?php include 'cabecalho.php'; ?> <!-- Inclusão do cabeçalho -->

<style>
    .historia-section {
        font-family: 'Poppins', sans-serif;
        background-color: #f9f9f9;
        color: #333;
    }
    .historia-container {
        max-width: 1140px;
        margin: 0 auto;
        padding: 30px;
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

    <!-- Hero Section -->
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
          <span>História <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">A nossa História</h2>
      </div>
    </div>
  </div>
</div>

     <!-- História Completa -->
     <section class="ftco-section">
      <div class="container">
        <div class="row justify-content-center mb-5">
          <div class="col-md-7 heading-section text-center ftco-animate">
            <span class="subheading">Nossa Jornada</span>
            <h2>Conheça a Nossa História</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 ftco-animate">
            <p>
              Tudo começou em 2010, quando dois amigos, João e Maria, decidiram transformar sua paixão por produtos de qualidade em um negócio. Eles começaram pequeno, vendendo produtos artesanais em feiras locais, mas rapidamente ganharam a atenção de muitos clientes que apreciavam a dedicação e o cuidado que colocavam em cada item.
            </p>
            <p>
              Com o passar dos anos, a demanda por seus produtos cresceu, e eles decidiram expandir seu negócio para uma loja online. Em 2015, lançaram a BoxingForLife, uma loja virtual que oferece uma variedade de produtos, desde roupas até acessórios, todos com a mesma qualidade e atenção aos detalhes que os clientes já conheciam.
            </p>
            <p>
              Hoje, a BoxingForLife é uma das lojas online mais reconhecidas do país, com milhares de clientes satisfeitos e uma equipe dedicada que trabalha incansavelmente para garantir a melhor experiência de compra. Nossa missão é simples: oferecer produtos de alta qualidade a preços acessíveis, com um atendimento ao cliente excepcional.
            </p>
            <p>
              Acreditamos que cada produto que vendemos conta uma história, e estamos orgulhosos de fazer parte da jornada dos nossos clientes. Seja bem-vindo à BoxingForLife, onde a qualidade e a satisfação do cliente estão sempre em primeiro lugar.
            </p>
          </div>
        </div>
      </div>
    </section>    <!-- Seção com Nova Imagem e Texto -->
    <section class="ftco-section ftco-no-pb">
        <div class="container">
            <div class="row">
                <div class="col-md-6 d-flex justify-content-center align-items-center">
                    <!-- Nova Imagem -->
                    <img src="imagens/marcas/Under_armour_logo.svg.png" alt="Under Armour" class="img-fluid">
                </div>
                <div class="col-md-6 wrap-about pl-md-5 ftco-animate py-5">
                    <div class="heading-section">
                        <span class="subheading">Marcas que pode conhecer que temos produtos</span>                        <h2 class="mb-4">Under Armour</h2>
                        <p class="year">
                            Under Armour é uma marca americana de equipamentos desportivos de alta performance, conhecida pelas suas tecnologias inovadoras em tecidos e produtos para diversos desportos, incluindo boxe e artes marciais.                        </p>                        <!-- Botão "Ver Produtos" -->
                        <a href="product.php?marca=9005" class="btn btn-primary mt-3">Ver Produtos</a>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- Seção com Nova Imagem e Texto -->
    <section class="ftco-section ftco-no-pb">
        <div class="container">
            <div class="row">
                <div class="col-md-6 d-flex justify-content-center align-items-center">
                    <!-- Nova Imagem -->
                    <img src="imagens/marcas/Everlast-Logo-1978-present.png" alt="Everlast" class="img-fluid">
                </div>
                <div class="col-md-6 wrap-about pl-md-5 ftco-animate py-5">
                    <div class="heading-section">
                        <span class="subheading">Marcas que pode conhecer que temos produtos</span>
                        <h2 class="mb-4">Everlast</h2>
                        <p class="year">
                            Fundada em 1910, a Everlast é líder mundial em equipamentos de boxe e fitness, oferecendo produtos de alta performance para atletas amadores e profissionais.                        </p>                        <!-- Botão "Ver Produtos" -->
                        <a href="product.php?marca=9001" class="btn btn-primary mt-3">Ver Produtos</a>
                    </div>
                </div>
            </div>
        </div>
    </section><section class="ftco-section ftco-no-pb">
        <div class="container">
            <div class="row">
                <div class="col-md-6 d-flex justify-content-center align-items-center">
                    <!-- Nova Imagem -->
                    <img src="imagens/marcas/002-nike-logos-swoosh-white.jpg" alt="Nike" class="img-fluid">
                </div>
                <div class="col-md-6 wrap-about pl-md-5 ftco-animate py-5">
                    <div class="heading-section">
                        <span class="subheading">Marcas que pode conhecer que temos produtos</span>
                        <h2 class="mb-4">Nike</h2>
                        <p class="year">
                            A Nike é uma das maiores fabricantes de artigos desportivos do mundo, conhecida pela inovação em equipamentos de treino e performance.                        </p>                        <!-- Botão "Ver Produtos" -->
                        <a href="product.php?marca=9002" class="btn btn-primary mt-3">Ver Produtos</a>
                    </div>
                </div>
            </div>
        </div>
    </section><section class="ftco-section ftco-no-pb">
        <div class="container">
            <div class="row">
                <div class="col-md-6 d-flex justify-content-center align-items-center">
                    <!-- Nova Imagem -->
                    <img src="imagens/marcas/Adidas_Logo.svg" alt="Adidas" class="img-fluid">
                </div>
                <div class="col-md-6 wrap-about pl-md-5 ftco-animate py-5">
                    <div class="heading-section">
                        <span class="subheading">Marcas que pode conhecer que temos produtos</span>
                        <h2 class="mb-4">Adidas</h2>
                        <p class="year">
                            A Adidas é uma das maiores fabricantes de artigos desportivos do mundo, com produtos que combinam tecnologia avançada e design inovador para todos os desportos, incluindo boxe e artes marciais.                        </p>                        <!-- Botão "Ver Produtos" -->
                        <a href="product.php?marca=9003" class="btn btn-primary mt-3">Ver Produtos</a>
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