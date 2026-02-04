<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

include 'conexao.php';
include 'validar.php';
include 'cabecalho.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

$idUtilizador = $_SESSION['id_utilizador'];

// Buscar todas as encomendas do usuário
$sql = "SELECT * FROM encomendas WHERE id_utilizador = $idUtilizador ORDER BY data_encomenda DESC";
$result = mysqli_query($conn, $sql);
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
          <span>Minhas Encomendas <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Minhas Encomendas</h2>
      </div>
    </div>
  </div>
</div>

<!-- Conteúdo Principal -->
<div class="container mt-5">
    <h2 class="mb-4">Minhas Encomendas</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($encomenda = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $encomenda['id_encomenda']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($encomenda['data_encomenda'])); ?></td>
                            <td><?php echo number_format($encomenda['total'], 2, ',', '.'); ?> €</td>
                            <td><?php 
                                echo $encomenda['status']; 
                                // Debug temporário para verificar o status exato
                                $status_valor = $encomenda['status'];
                                $status_trim = trim($encomenda['status']);
                            ?></td>
                            <td>
                                <?php 
                                // Verificações mais rigorosas para o status
                                $status_lower = strtolower(trim($encomenda['status']));
                                if ($status_lower != 'cancelado' && $status_lower != 'cancelada'): 
                                ?>
                                <a href="detalhes_encomenda.php?id=<?php echo $encomenda['id_encomenda']; ?>" class="btn btn-primary btn-sm">
                                    Ver Detalhes
                                </a>
                                <?php else: ?>
                                <span class="text-danger">Cancelada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Você ainda não fez nenhuma encomenda.
        </div>
    <?php endif; ?>
</div>

<script>
function carregarDetalhes(idEncomenda) {
    window.location.href = 'detalhes_encomenda.php?id=' + idEncomenda;
}
</script>

<?php include 'rodape.php'; ?>

<?php
mysqli_close($conn);
?>

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