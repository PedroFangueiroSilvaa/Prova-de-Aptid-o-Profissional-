<?php
include 'conexao.php';

// Buscar reviews de encomendas com 4 ou 5 estrelas
$sql = "SELECT re.*, u.nome as nome_utilizador 
        FROM reviews_encomendas re 
        JOIN utilizadores u ON re.id_utilizador = u.id_utilizador 
        WHERE re.classificacao >= 4
        ORDER BY re.data_review DESC LIMIT 5";
$result = mysqli_query($conn, $sql);
$reviews = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = [
            'mensagem' => $row['comentario'],
            'utilizador' => $row['nome_utilizador'],
            'classificacao' => $row['classificacao']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="path_to_your_stylesheet.css">
    <title>Testemunhos</title>
</head>
<body>
    <section class="ftco-section testimony-section img" style="background-image: url(imagens/equipamento.jpg);">
        <div class="overlay"></div>
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-md-7 text-center heading-section heading-section-white ftco-animate">
                    <span class="subheading">Testemunhos</span>
                    <h2 class="mb-3">Clientes Satisfeitos</h2>
                </div>
            </div>
            <div class="row ftco-animate">
                <div class="col-md-12">
                    <div class="carousel-testimony owl-carousel ftco-owl">
                        <?php if (empty($reviews)): ?>
                            <div class="item">
                                <div class="testimony-wrap py-4">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="fa fa-quote-left"></span>
                                    </div>
                                    <div class="text">
                                        <p class="mb-4">Ainda não há avaliações de clientes.</p>
                                        <div class="d-flex align-items-center">
                                            <div class="user-img" style="background-image: url(imagens/anonimo.jpg)"></div>
                                            <div class="pl-3">
                                                <p class="name">Sistema</p>
                                                <span class="position">Informação</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="item">
                                    <div class="testimony-wrap py-4">
                                        <div class="icon d-flex align-items-center justify-content-center">
                                            <span class="fa fa-quote-left"></span>
                                        </div>
                                        <div class="text">
                                            <p class="mb-4"><?php echo htmlspecialchars($review['mensagem']); ?></p>
                                            
                                            <!-- Exibir estrelas da avaliação -->
                                            <div class="rating mb-3">
                                                <?php 
                                                $estrelas = (int)$review['classificacao'];
                                                for ($i = 1; $i <= 5; $i++): 
                                                ?>
                                                    <span class="fa fa-star<?php echo $i <= $estrelas ? '' : '-o'; ?>" style="color: #ffc107;"></span>
                                                <?php endfor; ?>
                                                <span class="ml-2 text-white">(<?php echo $estrelas; ?>/5)</span>
                                            </div>
                                            
                                            <div class="d-flex align-items-center">
                                                <div class="user-img" style="background-image: url(imagens/anonimo.jpg)"></div>
                                                <div class="pl-3">
                                                    <p class="name"><?php echo htmlspecialchars($review['utilizador']); ?></p>
                                                    <span class="position">Cliente</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
