<?php
include 'conexao.php';

// Verificar se os parâmetros necessários foram fornecidos
if (!isset($_GET['codigo_base']) || !isset($_GET['codigo_cor'])) {
    echo '<option value="">Parâmetros inválidos</option>';
    exit;
}

$codigoBase = $_GET['codigo_base'];
$codigoCor = $_GET['codigo_cor'];

// Consultar os tamanhos disponíveis para a combinação de produto e cor
$sql = "SELECT DISTINCT t.codigo_tamanho, t.descricao AS tamanho 
        FROM variacoes_produto vp
        INNER JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
        WHERE vp.codigo_base = '$codigoBase' AND vp.codigo_cor = '$codigoCor'
        ORDER BY t.descricao";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo '<option value="">Erro ao buscar tamanhos</option>';
    exit;
}

if (mysqli_num_rows($result) == 0) {
    echo '<option value="">Nenhum tamanho disponível para esta cor</option>';
    exit;
}

// Gerar as opções de tamanho
echo '<option value="">Selecione um tamanho</option>';
while ($row = mysqli_fetch_assoc($result)) {
    echo '<option value="' . htmlspecialchars($row['codigo_tamanho'], ENT_QUOTES, 'UTF-8') . '">' . 
         htmlspecialchars($row['tamanho'], ENT_QUOTES, 'UTF-8') . '</option>';
}
?> 