<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>História</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Atualiza o caminho para os teus ficheiros CSS -->
    <style>
       .img-3 {
    background-image: url('imagens/1.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    width: 100%;
    height: 100vh;
}

/* Para ecrãs menores */
@media (max-width: 768px) {
    .img-3 {
        background-size: contain; /* Em dispositivos móveis, mostra a imagem toda */
    }
}


    </style>
</head>
<body>
    <section class="ftco-section ftco-no-pb">
        <div class="container">
            <div class="row">
                <div class="col-md-6 img img-3 d-flex justify-content-center align-items-center">
                </div>
                <div class="col-md-6 wrap-about pl-md-5 ftco-animate py-5">
                    <div class="heading-section">
                        <span class="subheading">Mais de 10 marcas diferentes para escolher</span>
                        <h2 class="mb-4">Todos os produtos que podes imaginar pelo melhor preço possível</h2>
                        <p>Desde sacos de box, até sapatilhas para poderes ter os melhores treinos de todos.</p>
                        <p class="year">
                            <strong class="number" data-number="1000">0</strong>
                            <span>100 produtos</span>
                        </p>
                        <!-- Botão "Ler Mais" -->
                        <a href="historia_completa.php" class="btn btn-primary mt-3">Ler Mais</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
