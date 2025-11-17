<?php 
session_start();
include 'conexao.php';
include 'cabecalho.php'; // Incluindo o cabeçalho que já contém as tags DOCTYPE, html, head e body
?>

<style>
/* CSS ESPECÍFICO PARA ÍCONES DE PRODUTOS E HOVER EFFECTS */
.product .desc .meta-prod a i {
  color: #ff914d;
  transition: color 0.2s;
}
.product .desc .meta-prod a.favorite-btn.active i {
  color: #fff !important;
}
.product .desc .meta-prod a.favorite-btn.active:hover i {
  color: #fff !important;
  text-shadow: 0 0 6px #ff914d, 0 0 2px #fff;
}
.product .desc .meta-prod a:hover i {
  color: #fff !important;
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
          <span>Produtos <i class="fa fa-chevron-right"></i></span>
        </p>
        <h2 class="mb-0 bread">Produtos</h2>
      </div>
    </div>
  </div>
</div>

<section class="ftco-section">
  <div class="container">
    <div class="row">
      <div class="col-md-9">
        <div class="row">
          <?php
          // Construindo a consulta SQL base
          $sql_base = "SELECT p.codigo_base, p.nome, p.preco, p.imagem, c.nome AS categoria_nome,
                  c.id_categoria, m.id_marca, m.nome AS marca_nome
                  FROM produtos p 
                  JOIN categorias c ON p.id_categoria = c.id_categoria 
                  JOIN marcas m ON p.id_marca = m.id_marca";
          
          // Adicionando filtros baseados nos parâmetros GET
          $where_clauses = array();
          
          // Filtro por categoria
          if (isset($_GET['categoria'])) {
            $categoria = mysqli_real_escape_string($conn, $_GET['categoria']);
            $where_clauses[] = "c.nome = '$categoria'";
          }
          
          // Filtro por marca (id_marca)
          if (isset($_GET['marca'])) {
            $id_marca = mysqli_real_escape_string($conn, $_GET['marca']);
            $where_clauses[] = "m.id_marca = '$id_marca'";
          }
          
          // Montando a consulta final com os filtros
          if (!empty($where_clauses)) {
            $sql = $sql_base . " WHERE " . implode(" AND ", $where_clauses);
          } else {
            $sql = $sql_base;
          }

          $result = mysqli_query($conn, $sql);

          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              $nome = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
              $preco = number_format($row['preco'], 2, ',', '.'); // Formatação monetária
              $categoria_nome = htmlspecialchars($row['categoria_nome'], ENT_QUOTES, 'UTF-8');
              $imagem = htmlspecialchars($row['imagem'], ENT_QUOTES, 'UTF-8'); // Caminho completo da imagem
              $codigo_base = htmlspecialchars($row['codigo_base'], ENT_QUOTES, 'UTF-8');
              $id_categoria = $row['id_categoria'];
              $id_marca = $row['id_marca'];
              $marca_nome = htmlspecialchars($row['marca_nome'], ENT_QUOTES, 'UTF-8');
              // Verificar se o produto está nos favoritos do usuário
              $is_favorite = false;
              if (isset($_SESSION['id_utilizador'])) {
                $id_utilizador = $_SESSION['id_utilizador'];
                $sql_check_favorite = "SELECT COUNT(*) as count FROM gostos WHERE id_utilizador = $id_utilizador AND codigo_base = '$codigo_base'";
                $result_check = mysqli_query($conn, $sql_check_favorite);
                $row_check = mysqli_fetch_assoc($result_check);
                $is_favorite = $row_check['count'] > 0;
              }
          ?>
              <div class="col-md-4 d-flex">
                <div class="product ftco-animate">
                  <div class="img d-flex align-items-center justify-content-center" style="background-image: url('<?php echo $imagem; ?>');">
                    <div class="desc">
                      <p class="meta-prod d-flex">
                        <a href="product-single.php?codigo_base=<?php echo $codigo_base; ?>&id_categoria=<?php echo $id_categoria; ?>&id_marca=<?php echo $id_marca; ?>" class="d-flex align-items-center justify-content-center"><i class="fas fa-shopping-bag"></i></a>
                        <a href="adicionar_gosto.php?codigo_base=<?php echo $codigo_base; ?>&id_categoria=<?php echo $id_categoria; ?>&id_marca=<?php echo $id_marca; ?>&acao=<?php echo $is_favorite ? 'remover' : 'adicionar'; ?>" class="d-flex align-items-center justify-content-center favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" data-codigo="<?php echo $codigo_base; ?>" data-favorite="<?php echo $is_favorite ? 'true' : 'false'; ?>"><i class="fas fa-heart"></i></a>
                      </p>
                    </div>
                  </div>
                  <div class="text text-center">
                    <span class="category"><?php echo $categoria_nome; ?></span>
                    <h2><a href="product-single.php?codigo_base=<?php echo $codigo_base; ?>&id_categoria=<?php echo $id_categoria; ?>&id_marca=<?php echo $id_marca; ?>"><?php echo $nome; ?></a></h2> <!-- Alterado para usar codigo_base e ids -->
                    <span class="price">€<?php echo $preco; ?></span>
                    <div class="product-data" data-categoria="<?php echo $id_categoria; ?>" data-marca="<?php echo $id_marca; ?>" style="display: none;"></div>
                  </div>
                </div>
              </div>
          <?php
            }
          } else {
            echo "<p>No products found for this category.</p>";
          }
          ?>
        </div>
      </div>
      <div class="col-md-3">
        <div class="sidebar-box ftco-animate">
          <div class="categories">
            <h3>Categorias</h3>
            <ul class="p-0">
              <li><a href="product.php">Todas as Categorias <span class="fas fa-chevron-right"></span></a></li>
              <?php
                $sql_sidebar = "SELECT * FROM categorias";
                $result_sidebar = mysqli_query($conn, $sql_sidebar);
                while ($categoria_sidebar = mysqli_fetch_assoc($result_sidebar)) {
                  $nome_categoria_sidebar = htmlspecialchars($categoria_sidebar['nome'], ENT_QUOTES, 'UTF-8');
                  $url_categoria = "product.php?categoria=" . urlencode($nome_categoria_sidebar);
                  echo "<li><a href='$url_categoria'>$nome_categoria_sidebar <span class='fas fa-chevron-right'></span></a></li>";
                }
              ?>
            </ul>
          </div>
        </div>
        
        <div class="sidebar-box ftco-animate">
          <div class="categories">
            <h3>Marcas</h3>
            <ul class="p-0">
              <li><a href="product.php">Todas as Marcas <span class="fas fa-chevron-right"></span></a></li>
              <?php
                $sql_marcas_sidebar = "SELECT * FROM marcas";
                $result_marcas_sidebar = mysqli_query($conn, $sql_marcas_sidebar);
                while ($marca_sidebar = mysqli_fetch_assoc($result_marcas_sidebar)) {
                  $id_marca = $marca_sidebar['id_marca'];
                  $nome_marca_sidebar = htmlspecialchars($marca_sidebar['nome'], ENT_QUOTES, 'UTF-8');
                  $url_marca = "product.php?marca=" . $id_marca;
                  echo "<li><a href='$url_marca'>$nome_marca_sidebar <span class='fas fa-chevron-right'></span></a></li>";
                }
              ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include 'rodape.php'; ?>
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
<script src="js/main.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// JavaScript functions for product page functionality

$(document).ready(function() {
    $('.favorite-btn').click(function(e) {
        e.preventDefault();
        
        if (!<?php echo isset($_SESSION['id_utilizador']) ? 'true' : 'false'; ?>) {
            window.location.href = 'login.php';
            return;
        }

        var btn = $(this);
        var codigo = btn.data('codigo');
        var isFavorite = btn.data('favorite') === 'true';
        var acao = isFavorite ? 'remover' : 'adicionar';
        var id_categoria = btn.closest('.product').find('.product-data').data('categoria');
        var id_marca = btn.closest('.product').find('.product-data').data('marca');

        $.ajax({
            url: 'adicionar_gosto.php',
            type: 'POST',
            data: {
                codigo_base: codigo,
                id_categoria: id_categoria,
                id_marca: id_marca,
                acao: acao
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    btn.data('favorite', !isFavorite);
                    btn.toggleClass('active');
                    if (acao === 'adicionar') {
                        alert('Produto adicionado aos favoritos!');
                    } else {
                        alert('Produto removido dos favoritos!');
                    }
                } else {
                    alert(data.message);
                }
            }
        });
    });
});
</script>
</body>
</html>