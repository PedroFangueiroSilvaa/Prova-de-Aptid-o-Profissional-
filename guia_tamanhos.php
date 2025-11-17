<?php 
include 'conexao.php';
include 'cabecalho.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guia de Tamanhos - Boxing for Life</title>
    <style>
        .size-guide-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px 15px;
        }
        .size-guide-section {
            margin-bottom: 40px;
        }
        .size-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 30px;
        }
        .size-table th, .size-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .size-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .size-guide-section h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .measurement-image {
            max-width: 100%;
            height: auto;
            margin: 20px 0;
        }
        .tip-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
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
</head>
<body>
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
            <p class="breadcrumbs mb-0"><span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span> <span>Guia de Tamanhos <i class="fa fa-chevron-right"></i></span></p>
            <h2 class="mb-0 bread">Guia de Tamanhos</h2>
          </div>
        </div>
      </div>
    </div>

    <div class="size-guide-container">
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading"><i class="fa fa-info-circle"></i> Como usar este guia</h4>
            <p>Este guia de tamanhos serve como referência para ajudá-lo a encontrar o tamanho perfeito para os produtos Boxing for Life. Para obter o melhor ajuste, recomendamos que você meça conforme as instruções em cada seção.</p>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <!-- Luvas de Boxe -->
                <div class="size-guide-section">
                    <h3>Luvas de Boxe</h3>
                    <p>As luvas de boxe são classificadas pelo peso (em onças ou oz). A escolha certa depende do seu peso corporal, experiência e tipo de treino.</p>
                    
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Tamanho (oz)</th>
                                <th>Peso do Usuário</th>
                                <th>Tipo de Treino</th>
                                <th>Recomendação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>8oz</td>
                                <td>Até 55kg</td>
                                <td>Competição/Velocidade</td>
                                <td>Iniciantes leves, competição</td>
                            </tr>
                            <tr>
                                <td>10oz</td>
                                <td>55-65kg</td>
                                <td>Competição/Treino</td>
                                <td>Competição, treino leve</td>
                            </tr>
                            <tr>
                                <td>12oz</td>
                                <td>65-80kg</td>
                                <td>Treino/Sparring leve</td>
                                <td>Treino regular, sparring técnico</td>
                            </tr>
                            <tr>
                                <td>14oz</td>
                                <td>80-90kg</td>
                                <td>Treino/Sparring</td>
                                <td>Treino intenso, sparring</td>
                            </tr>
                            <tr>
                                <td>16oz</td>
                                <td>Acima de 90kg</td>
                                <td>Sparring/Proteção</td>
                                <td>Sparring pesado, maior proteção</td>
                            </tr>
                            <tr>
                                <td>18oz+</td>
                                <td>Qualquer</td>
                                <td>Condicionamento</td>
                                <td>Treino de resistência</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="tip-box">
                        <h5><i class="fa fa-lightbulb"></i> Dica:</h5>
                        <p>Para sparring, sempre opte por luvas maiores (14-16oz) para maior proteção, independentemente do seu peso.</p>
                    </div>
                </div>
                
                <!-- Protetor Bucal -->
                <div class="size-guide-section">
                    <h3>Protetor Bucal</h3>
                    <p>A maioria dos protetores bucais vem em tamanho único moldável, mas alguns modelos oferecem tamanhos diferentes.</p>
                    
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Tamanho</th>
                                <th>Descrição</th>
                                <th>Recomendação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Júnior</td>
                                <td>Para adolescentes e pessoas com bocas menores</td>
                                <td>Jovens, mulheres com bocas menores</td>
                            </tr>
                            <tr>
                                <td>Adulto Padrão</td>
                                <td>Tamanho único moldável</td>
                                <td>Maioria dos adultos</td>
                            </tr>
                            <tr>
                                <td>Grande</td>
                                <td>Para bocas maiores</td>
                                <td>Homens com arcada dentária maior</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Roupas -->
                <div class="size-guide-section">
                    <h3>Roupas de Treino</h3>
                    <p>Use uma fita métrica para medir conforme as instruções abaixo. Se estiver entre dois tamanhos, opte pelo maior para mais conforto.</p>
                    
                    <h4>Como medir:</h4>
                    <ul>
                        <li><strong>Peito:</strong> Meça a circunferência na parte mais larga do peito, mantendo a fita paralela ao chão.</li>
                        <li><strong>Cintura:</strong> Meça a circunferência da cintura natural (a parte mais estreita do torso).</li>
                        <li><strong>Quadril:</strong> Meça a circunferência na parte mais larga dos quadris.</li>
                    </ul>
                    
                    <h5>Tamanhos Masculinos (cm)</h5>
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Tamanho</th>
                                <th>Peito</th>
                                <th>Cintura</th>
                                <th>Quadril</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>S</td>
                                <td>88-96</td>
                                <td>73-81</td>
                                <td>88-96</td>
                            </tr>
                            <tr>
                                <td>M</td>
                                <td>96-104</td>
                                <td>81-89</td>
                                <td>96-104</td>
                            </tr>
                            <tr>
                                <td>L</td>
                                <td>104-112</td>
                                <td>89-97</td>
                                <td>104-112</td>
                            </tr>
                            <tr>
                                <td>XL</td>
                                <td>112-120</td>
                                <td>97-105</td>
                                <td>112-120</td>
                            </tr>
                            <tr>
                                <td>XXL</td>
                                <td>120-128</td>
                                <td>105-113</td>
                                <td>120-128</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h5>Tamanhos Femininos (cm)</h5>
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Tamanho</th>
                                <th>Peito</th>
                                <th>Cintura</th>
                                <th>Quadril</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>XS</td>
                                <td>76-84</td>
                                <td>58-66</td>
                                <td>82-90</td>
                            </tr>
                            <tr>
                                <td>S</td>
                                <td>84-92</td>
                                <td>66-74</td>
                                <td>90-98</td>
                            </tr>
                            <tr>
                                <td>M</td>
                                <td>92-100</td>
                                <td>74-82</td>
                                <td>98-106</td>
                            </tr>
                            <tr>
                                <td>L</td>
                                <td>100-108</td>
                                <td>82-90</td>
                                <td>106-114</td>
                            </tr>
                            <tr>
                                <td>XL</td>
                                <td>108-116</td>
                                <td>90-98</td>
                                <td>114-122</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Bandagens -->
                <div class="size-guide-section">
                    <h3>Bandagens</h3>
                    <p>As bandagens são classificadas pelo comprimento. A escolha depende do tamanho da mão e do tipo de proteção desejada.</p>
                    
                    <table class="size-table">
                        <thead>
                            <tr>
                                <th>Comprimento</th>
                                <th>Recomendação</th>
                                <th>Melhor para</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2,5m</td>
                                <td>Mãos pequenas, treinos leves</td>
                                <td>Iniciantes, treino casual</td>
                            </tr>
                            <tr>
                                <td>3,5m</td>
                                <td>Mãos médias, treinos regulares</td>
                                <td>Amadores, treino regular</td>
                            </tr>
                            <tr>
                                <td>4,5m</td>
                                <td>Mãos grandes, proteção completa</td>
                                <td>Profissionais, competições</td>
                            </tr>
                            <tr>
                                <td>5,5m</td>
                                <td>Proteção máxima</td>
                                <td>Competições profissionais</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tip-box">
                    <h5><i class="fa fa-question-circle"></i> Ainda com dúvidas?</h5>
                    <p>Se você ainda não tem certeza sobre qual tamanho escolher, entre em contato com nossa equipe de suporte pelo email <strong>BoxingForLife@gmail.com</strong> ou pelo telefone <strong>+351 919 548 782</strong> para obter ajuda personalizada.</p>
                </div>
            </div>
        </div>
    </div>

<?php include 'rodape.php'; ?>

<!-- Modal do Guia de Tamanhos -->
<div class="modal fade" id="sizeGuideModal" tabindex="-1" aria-labelledby="sizeGuideModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sizeGuideModalLabel">Guia de Tamanhos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Conteúdo do modal será carregado por AJAX -->
        <div id="sizeGuideContent">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
// Script para carregar o guia de tamanhos no modal
function loadSizeGuideContent(category) {
    const guideContent = document.getElementById('sizeGuideContent');
    
    // Na prática, você pode usar AJAX para carregar o conteúdo específico para cada categoria
    // Aqui, estamos simplesmente definindo o conteúdo baseado na categoria
    
    if (category === 'luvas') {
        guideContent.innerHTML = `
            <h3>Guia de Tamanhos - Luvas de Boxe</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tamanho (oz)</th>
                        <th>Peso do Usuário</th>
                        <th>Tipo de Treino</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>8oz</td>
                        <td>Até 55kg</td>
                        <td>Competição/Velocidade</td>
                    </tr>
                    <tr>
                        <td>10oz</td>
                        <td>55-65kg</td>
                        <td>Competição/Treino</td>
                    </tr>
                    <tr>
                        <td>12oz</td>
                        <td>65-80kg</td>
                        <td>Treino/Sparring leve</td>
                    </tr>
                    <tr>
                        <td>14oz</td>
                        <td>80-90kg</td>
                        <td>Treino/Sparring</td>
                    </tr>
                    <tr>
                        <td>16oz</td>
                        <td>Acima de 90kg</td>
                        <td>Sparring/Proteção</td>
                    </tr>
                </tbody>
            </table>
        `;
    } else if (category === 'roupas') {
        guideContent.innerHTML = `
            <h3>Guia de Tamanhos - Roupas</h3>
            <h5>Tamanhos Masculinos (cm)</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tamanho</th>
                        <th>Peito</th>
                        <th>Cintura</th>
                        <th>Quadril</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>S</td>
                        <td>88-96</td>
                        <td>73-81</td>
                        <td>88-96</td>
                    </tr>
                    <tr>
                        <td>M</td>
                        <td>96-104</td>
                        <td>81-89</td>
                        <td>96-104</td>
                    </tr>
                    <tr>
                        <td>L</td>
                        <td>104-112</td>
                        <td>89-97</td>
                        <td>104-112</td>
                    </tr>
                    <tr>
                        <td>XL</td>
                        <td>112-120</td>
                        <td>97-105</td>
                        <td>112-120</td>
                    </tr>
                </tbody>
            </table>
            
            <h5 class="mt-4">Tamanhos Femininos (cm)</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tamanho</th>
                        <th>Peito</th>
                        <th>Cintura</th>
                        <th>Quadril</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>XS</td>
                        <td>76-84</td>
                        <td>58-66</td>
                        <td>82-90</td>
                    </tr>
                    <tr>
                        <td>S</td>
                        <td>84-92</td>
                        <td>66-74</td>
                        <td>90-98</td>
                    </tr>
                    <tr>
                        <td>M</td>
                        <td>92-100</td>
                        <td>74-82</td>
                        <td>98-106</td>
                    </tr>
                    <tr>
                        <td>L</td>
                        <td>100-108</td>
                        <td>82-90</td>
                        <td>106-114</td>
                    </tr>
                </tbody>
            </table>
        `;
    } else if (category === 'bandagens') {
        guideContent.innerHTML = `
            <h3>Guia de Tamanhos - Bandagens</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Comprimento</th>
                        <th>Recomendação</th>
                        <th>Melhor para</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2,5m</td>
                        <td>Mãos pequenas, treinos leves</td>
                        <td>Iniciantes, treino casual</td>
                    </tr>
                    <tr>
                        <td>3,5m</td>
                        <td>Mãos médias, treinos regulares</td>
                        <td>Amadores, treino regular</td>
                    </tr>
                    <tr>
                        <td>4,5m</td>
                        <td>Mãos grandes, proteção completa</td>
                        <td>Profissionais, competições</td>
                    </tr>
                </tbody>
            </table>
        `;
    } else {
        // Conteúdo padrão para outras categorias
        guideContent.innerHTML = `
            <div class="alert alert-info">
                <h4>Guia de Tamanhos</h4>
                <p>Selecione uma categoria específica para ver o guia de tamanhos correspondente.</p>
                
                <div class="mt-4">
                    <p>Para mais informações, consulte nosso <a href="guia_tamanhos.php" target="_blank">guia completo de tamanhos</a> ou entre em contato com nossa equipe de suporte.</p>
                </div>
            </div>
        `;
    }
}

// Inicializar a função quando o modal for aberto
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('sizeGuideModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const category = button.getAttribute('data-category');
            loadSizeGuideContent(category);
        });
    }
});
</script>

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