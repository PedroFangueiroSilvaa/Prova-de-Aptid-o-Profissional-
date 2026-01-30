<?php 
include 'conexao.php';
include 'config_phpmailer.php';
include 'cabecalho.php'; // Incluindo o cabeçalho que já contém as tags DOCTYPE, html, head e body

// Processar o formulário quando for submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $assunto = $_POST['subject'] ?? '';
    $mensagem = $_POST['message'] ?? '';

    // Validar os campos
    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Preparar o corpo do email
        $corpo = "
            <h2>Nova mensagem de contato</h2>
            <p><strong>Nome:</strong> $nome</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Assunto:</strong> $assunto</p>
            <p><strong>Mensagem:</strong></p>
            <p>$mensagem</p>
        ";

        // Enviar o email
        $resultado = enviarEmail('pedrofangueirosilva19@gmail.com', "Nova mensagem de contato: $assunto", $corpo);

        if ($resultado === true) {
            $sucesso = "Mensagem enviada com sucesso! Entraremos em contato em breve.";
        } else {
            $erro = "Erro ao enviar mensagem: " . $resultado;
        }
    }
}
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
          <span>Contacto <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Contacte-nos</h2>
      </div>
    </div>
  </div>
</div>

<section class="ftco-section bg-light">
  <div class="container">
    <!-- Título da seção centralizado -->
    <div class="row mb-5">
      <div class="col-md-12 text-center">
        <h2 class="heading-section">Entre em Contato</h2>
        <p class="text-muted">Estamos prontos para atender suas dúvidas, sugestões ou propostas de parceria</p>
      </div>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-md-11 col-lg-10">
        <!-- Formulário e informações de contato centralizados -->
        <div class="card shadow">
          <div class="card-body p-md-5 p-4">
            <?php if (isset($erro)): ?>
              <div class="alert alert-danger" role="alert">
                <?php echo $erro; ?>
              </div>
            <?php endif; ?>
            <?php if (isset($sucesso)): ?>
              <div class="alert alert-success" role="alert">
                <?php echo $sucesso; ?>
              </div>
            <?php endif; ?>
            
            <div class="row">
              <!-- Formulário de contato -->
              <div class="col-lg-5 col-md-5 pr-md-4 mb-4 mb-md-0">
                <h3 class="mb-4">Envie-nos uma mensagem</h3>
                <form method="POST" id="contactForm" name="contactForm" class="contactForm">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="label" for="name">Nome completo</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Seu nome">
                      </div>
                    </div>
                    <div class="col-md-6"> 
                      <div class="form-group">
                        <label class="label" for="email">Email</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="Seu email">
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="label" for="subject">Assunto</label>
                        <input type="text" class="form-control" name="subject" id="subject" placeholder="Assunto da mensagem">
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="label" for="message">Mensagem</label>
                        <textarea name="message" class="form-control" id="message" cols="30" rows="4" placeholder="Escreva sua mensagem aqui"></textarea>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="form-group">
                        <input type="submit" value="Enviar Mensagem" class="btn btn-primary btn-block">
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              
              <!-- Informações de contato -->
              <div class="col-lg-7 col-md-7 pl-md-4">
                <div class="bg-white shadow-sm p-4 h-100">
                  <h3 class="mb-4 text-center">Informações de Contato</h3>
                  
                  <div class="dbox d-flex align-items-start mb-4">
                    <div class="icon d-flex align-items-center justify-content-center mr-4" style="min-width: 50px;">
                      <span class="fa fa-phone fa-2x"></span>
                    </div>
                    <div class="text">
                      <h5 class="mb-2">Telefone</h5>
                      <p class="mb-0"><a href="tel://+351919547691" class="text-dark">+351 919 547 691</a></p>
                    </div>
                  </div>
                  
                  <div class="dbox d-flex align-items-start mb-4">
                    <div class="icon d-flex align-items-center justify-content-center mr-4" style="min-width: 50px;">
                      <span class="fa fa-paper-plane fa-2x"></span>
                    </div>
                    <div class="text">
                      <h5 class="mb-2">Email</h5>
                      <p class="mb-0"><a href="mailto:BoxingForLife@gmail.com" class="text-dark">BoxingForLife@gmail.com</a></p>
                    </div>
                  </div>
                  
                  <div class="dbox d-flex align-items-start mb-4">
                    <div class="icon d-flex align-items-center justify-content-center mr-4" style="min-width: 50px;">
                      <span class="fa fa-globe fa-2x"></span>
                    </div>
                    <div class="text">
                      <h5 class="mb-2">Website</h5>
                      <p class="mb-0"><a href="#" class="text-dark">BoxingForLife.com</a></p>
                    </div>
                  </div>
                  
                  <!-- Mapa ou informação adicional de contato -->
                  <div class="mt-5">
                    <h5 class="mb-3">Horário de Atendimento</h5>
                    <p class="mb-1"><strong>Segunda a Sexta:</strong> 09:00 - 18:00</p>
                    <p><strong>Sábados:</strong> 10:00 - 14:00</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include 'rodape.php'; ?> <!-- Inclusão do cabeçalho -->
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