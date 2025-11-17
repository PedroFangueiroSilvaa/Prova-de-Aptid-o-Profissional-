<?php 
include 'conexao.php';
include 'cabecalho.php'; // Usando o cabeçalho normal para área do usuário
?>

<!DOCTYPE html>
<html lang="en">
  <body>
    <?php
    // Sistema de exibição de mensagens como modal
    $mostrarModal = false;
    $tipoModal = '';
    $tituloModal = '';
    $mensagem = '';
    $icone = '';
    $corModal = '';
    
    // Verificar mensagens de erro
    if (isset($_GET['erro'])) {
        $mostrarModal = true;
        $tipoModal = 'erro';
        $corModal = 'danger';
        
        switch ($_GET['erro']) {
            case 'acesso_negado':
                $tituloModal = 'Acesso Negado';
                $mensagem = 'Você não tem permissão para acessar a área administrativa.';
                $icone = 'fas fa-lock';
                break;
            case 'campos_vazios':
                $tituloModal = 'Campos Obrigatórios';
                $mensagem = 'Por favor, preencha todos os campos obrigatórios para criar a conta.';
                $icone = 'fas fa-exclamation-triangle';
                break;
            case 'email_existente':
                $tituloModal = 'Email Já Existe';
                $mensagem = 'Este email já está registrado. Tente fazer login ou use outro email.';
                $icone = 'fas fa-envelope-open';
                break;
            case 'token_invalido':
                $tituloModal = 'Link Inválido';
                $mensagem = 'O link de confirmação é inválido ou expirou. Tente criar a conta novamente.';
                $icone = 'fas fa-exclamation-triangle';
                break;
            case 'dados_nao_encontrados':
                $tituloModal = 'Dados Não Encontrados';
                $mensagem = 'Os dados da conta não foram encontrados. Tente criar a conta novamente.';
                $icone = 'fas fa-search';
                break;
            case 'erro_banco_dados':
                $tituloModal = 'Erro no Sistema';
                $mensagem = 'Ocorreu um erro ao criar a conta. Tente novamente mais tarde.';
                $icone = 'fas fa-database';
                break;
            default:
                $tituloModal = 'Erro';
                $mensagem = 'Ocorreu um erro inesperado. Tente novamente.';
                $icone = 'fas fa-exclamation-circle';
        }
    }
    
    // Verificar mensagens de sucesso
    if (isset($_GET['sucesso'])) {
        $mostrarModal = true;
        $tipoModal = 'sucesso';
        $corModal = 'success';
        
        switch ($_GET['sucesso']) {
            case 'email_enviado':
                $tituloModal = 'Email Enviado';
                $mensagem = 'Um email de confirmação foi enviado para o seu endereço. Verifique a sua caixa de entrada e clique no link para ativar a conta.';
                $icone = 'fas fa-check-circle';
                break;
            case 'conta_confirmada':
                $tituloModal = 'Conta Criada';
                $mensagem = 'A sua conta foi criada com sucesso! Já pode fazer login e começar a comprar.';
                $icone = 'fas fa-user-check';
                break;
            default:
                $tituloModal = 'Sucesso';
                $mensagem = 'Operação realizada com sucesso!';
                $icone = 'fas fa-check';
        }
    }
    ?>
    
    <!-- Modal de mensagens (popup) -->
    <?php if ($mostrarModal): ?>
    <div class="modal fade" id="modalMensagem" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalMensagemLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="box-shadow: 0 0 20px rgba(<?php echo $tipoModal == 'sucesso' ? '0, 255, 0' : '255, 0, 0'; ?>, 0.3);">
                <div class="modal-header bg-<?php echo $corModal; ?> text-white border-0">
                    <h5 class="modal-title fw-bold" id="modalMensagemLabel">
                        <i class="<?php echo $icone; ?> me-2"></i>
                        <?php echo $tituloModal; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <div class="mb-3 text-<?php echo $corModal; ?>" style="font-size: 3rem;">
                        <i class="<?php echo $icone; ?>"></i>
                    </div>
                    <p class="fs-5"><?php echo $mensagem; ?></p>
                    
                    <?php if ($tipoModal == 'sucesso' && $_GET['sucesso'] == 'email_enviado'): ?>
                        <div class="alert alert-info mt-3">
                            <small><i class="fas fa-info-circle me-1"></i> 
                            Não se esqueça de verificar também a pasta de spam/lixo eletrônico.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-<?php echo $corModal; ?> px-4" data-bs-dismiss="modal">
                        <i class="fas fa-<?php echo $tipoModal == 'sucesso' ? 'check' : 'times'; ?> me-2"></i>
                        <?php echo $tipoModal == 'sucesso' ? 'Entendi' : 'Fechar'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para abrir o modal automaticamente -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof bootstrap === 'undefined') {
                // Se o bootstrap não estiver definido, carregá-lo
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js';
                script.onload = function() {
                    // Depois que o script for carregado, mostrar o modal
                    var myModal = new bootstrap.Modal(document.getElementById('modalMensagem'));
                    myModal.show();
                };
                document.head.appendChild(script);
            } else {
                // Se o bootstrap já estiver carregado, mostrar o modal diretamente
                var myModal = new bootstrap.Modal(document.getElementById('modalMensagem'));
                myModal.show();
            }
            
            // Limpar a URL após mostrar a mensagem (opcional)
            setTimeout(function() {
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }, 1000);
        });
    </script>
    <?php endif; ?>
    
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
    <div class="hero-wrap position-relative" data-stellar-background-ratio="0.5">
      <video autoplay muted loop playsinline class="bg-video">
        <source src="imagens/INDEX.mp4" type="video/mp4">
        O seu navegador não suporta vídeo em HTML5.
      </video>
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-8 ftco-animate d-flex align-items-end">
          	<div class="text w-100 text-center">
	            <h1 class="mb-4"> <span>Os melhores produtos</span>  <span>pelos melhores preços.</span></h1>
              <p><a href="product.php" class="btn btn-primary py-2 px-4">Compre agora</a> <a href="historia_completa.php" class="btn btn-white btn-outline-white py-2 px-4">Se quiser ler mais</a></p>
              </div>
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
    						<span class="fas fa-headset"></span>
    					</div>
    					<div class="text">
    						<h2>Suporte onnline 24/7</h2>
    						<p>Temos um suporte online sempre disponivel para ti e estamos prontos para responder a qualquer dúvida.</p>
    					</div>
    				</div>
    			</div>
    			<div class="col-md-4 d-flex">
    				<div class="intro color-1 d-lg-flex w-100 ftco-animate">
    					<div class="icon">
    						<span class="fas fa-money-bill-wave"></span>
    					</div>
    					<div class="text">
    						<h2>Devolução garantida</h2>
    						<p>Se por acaso não gostares de algum produto ou se te tiveres enganado no tamanho ou na cor podes sempre devolver o produto e devolvemos todo o dinheiro.</p>
    					</div>
    				</div>
    			</div>
    			<div class="col-md-4 d-flex">
    				<div class="intro color-2 d-lg-flex w-100 ftco-animate">
    					<div class="icon">
    						<span class="fas fa-shipping-fast"></span>
    					</div>
    					<div class="text">
    						<h2>Portes &amp; Portes gratuitos</h2>
    						<p>Os portes são gratuitos tal e qual como as devoluções.</p>
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

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center pb-5">
            <div class="col-md-7 heading-section text-center ftco-animate">
                <span class="subheading">Os Nossos Produtos</span>
                <h2>Acabados de Chegar</h2>
            </div>
        </div>
        <div class="row">
            <?php
            // Query para buscar os 8 produtos mais recentes com apenas uma imagem
            $sql = "
                SELECT p.codigo_base AS codigo_base, 
                       p.nome AS nome_produto, 
                       p.preco, 
                       p.imagem, 
                       c.nome AS nome_categoria, 
                       c.id_categoria AS id_categoria,
                       m.nome AS nome_marca,
                       m.id_marca AS id_marca
                FROM produtos p
                INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                INNER JOIN marcas m ON p.id_marca = m.id_marca
                ORDER BY p.codigo_base DESC
                LIMIT 8
            ";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0): 
                while ($row = mysqli_fetch_assoc($result)):
                    // Verificar se há imagem associada, senão usar uma padrão
                       // Construir o caminho da imagem
                       $imagemProduto = !empty($row['imagem']) ? htmlspecialchars($row['imagem'], ENT_QUOTES, 'UTF-8') : 'imagens/default.jpg';
            ?>
                    <div class="col-md-3 d-flex">
                        <div class="product ftco-animate">
                            <div class="img d-flex align-items-center justify-content-center" style="background-image: url('<?= $imagemProduto ?>');">
                                <div class="desc">
                                    <p class="meta-prod d-flex">
                                        <a href="product-single.php?codigo_base=<?= $row['codigo_base'] ?>&id_categoria=<?= $row['id_categoria'] ?>&id_marca=<?= $row['id_marca'] ?>" class="d-flex align-items-center justify-content-center"><i class="fas fa-shopping-bag"></i></a>
                                        <a href="adicionar_gosto.php?codigo_base=<?= $row['codigo_base'] ?>&id_categoria=<?= $row['id_categoria'] ?>&id_marca=<?= $row['id_marca'] ?>&acao=adicionar" class="d-flex align-items-center justify-content-center favorite-btn"><i class="fas fa-heart"></i></a>
                                    </p>
                                </div>
                            </div>
                            <div class="text text-center">
                                <span class="category"><?= htmlspecialchars($row['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                                <h2><?= htmlspecialchars($row['nome_produto'], ENT_QUOTES, 'UTF-8') ?></h2>
                                <p class="mb-0">
                                    <span class="price"><?= number_format($row['preco'], 2, ',', '.') ?> €</span>
                                </p>
                            </div>
                        </div>
                    </div>
            <?php endwhile; 
            else: ?>
                <p class="text-center">Nenhum produto encontrado.</p>
            <?php endif;
            ?>
        </div>
    </div>
</section>

    <div class="row justify-content-center">
      <div class="col-md-4">
        <a href="product.php" class="btn btn-primary d-block">Ver Todos os Produtos <span class="fa fa-long-arrow-right"></span></a>
      </div>
    </div>
  </div>
</section>

<?php include 'reviews.php'; ?>

<section class="ftco-section">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-md-7 heading-section text-center ftco-animate">
        <span class="subheading">Blog</span>
        <h2>Recent Blog</h2>
        <div class="mt-3">
          <a href="blog.php" class="btn btn-primary">Ver Todos os Blogs</a>
        </div>
      </div>
    </div>
    <div class="row d-flex">
      <?php
      // Consulta para buscar os 4 blogs mais recentes do banco de dados
      $query = "SELECT id_post, titulo, conteudo, imagem, data_publicacao FROM blog ORDER BY data_publicacao DESC LIMIT 4";
      $result = mysqli_query($conn, $query);

      if ($result && mysqli_num_rows($result) > 0) {
          while ($blog = mysqli_fetch_assoc($result)) {
              // Gerar um resumo curto do conteúdo
              $resumo = strlen($blog['conteudo']) > 150 ? substr($blog['conteudo'], 0, 150) . '...' : $blog['conteudo'];

              echo '
              <div class="col-lg-6 d-flex align-items-stretch ftco-animate">
                <div class="blog-entry d-flex">
                  <a href="blog-single.php?id=' . htmlspecialchars($blog['id_post'], ENT_QUOTES, 'UTF-8') . '" class="block-20 img" style="background-image: url(\'' . htmlspecialchars($blog['imagem'], ENT_QUOTES, 'UTF-8') . '\');"></a>
                  <div class="text p-4 bg-light">
                    <div class="meta">
                      <p><span class="fa fa-calendar"></span> ' . date("d F Y", strtotime($blog['data_publicacao'])) . '</p>
                    </div>
                    <h3 class="heading mb-3"><a href="blog-single.php?id=' . htmlspecialchars($blog['id_post'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($blog['titulo'], ENT_QUOTES, 'UTF-8') . '</a></h3>
                    <p>' . htmlspecialchars($resumo, ENT_QUOTES, 'UTF-8') . '</p>
                    <a href="blog-single.php?id=' . htmlspecialchars($blog['id_post'], ENT_QUOTES, 'UTF-8') . '" class="btn-custom">Continue <span class="fa fa-long-arrow-right"></span></a>
                  </div>
                </div>
              </div>';
          }
      } else {
          echo '<p>Nenhum blog disponível no momento.</p>';
      }
      ?>
    </div>
  </div>
</section>

<?php include 'rodape.php'; ?>

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