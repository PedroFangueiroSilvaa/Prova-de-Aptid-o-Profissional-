<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

include 'conexao.php';
include 'validar.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit;
}

// Verificar se os parâmetros necessários foram fornecidos
if (!isset($_GET['sku']) || !isset($_GET['encomenda'])) {
    header("Location: minhas_encomendas.php");
    exit;
}

$sku = $_GET['sku'];
$id_encomenda = $_GET['encomenda'];
$id_utilizador = $_SESSION['id_utilizador'];

// Verificar se o produto pertence à encomenda do usuário e obter o codigo_base
$sql_check = "SELECT ie.*, p.nome as nome_produto, p.imagem, p.codigo_base, vp.sku, c.descricao as cor, t.descricao as tamanho
              FROM itens_encomenda ie
              INNER JOIN variacoes_produto vp ON ie.sku = vp.sku
              INNER JOIN produtos p ON vp.codigo_base = p.codigo_base
              LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor
              LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
              WHERE ie.id_encomenda = $id_encomenda AND ie.sku = '$sku'";
$result_check = mysqli_query($conn, $sql_check);
$produto = mysqli_fetch_assoc($result_check);

if (!$produto) {
    header("Location: detalhes_encomenda.php?id=" . $id_encomenda);
    exit;
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $classificacao = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comentario = isset($_POST['comentario']) ? mysqli_real_escape_string($conn, $_POST['comentario']) : '';
    
    if ($classificacao > 0 && $classificacao <= 5) {
        // Verificar se já existe uma review para este produto nesta encomenda
        $sql_check_review = "SELECT id_review FROM reviews_produtos WHERE codigo_base = '{$produto['codigo_base']}' AND id_encomenda = $id_encomenda";
        $result_check_review = mysqli_query($conn, $sql_check_review);
        
        if (mysqli_num_rows($result_check_review) > 0) {
            // Atualizar review existente
            $sql = "UPDATE reviews_produtos SET classificacao = $classificacao, comentario = '$comentario', data_review = NOW() 
                    WHERE codigo_base = '{$produto['codigo_base']}' AND id_encomenda = $id_encomenda";
        } else {
            // Inserir nova review
            $sql = "INSERT INTO reviews_produtos (codigo_base, id_encomenda, id_utilizador, classificacao, comentario, data_review) 
                    VALUES ('{$produto['codigo_base']}', $id_encomenda, $id_utilizador, $classificacao, '$comentario', NOW())";
        }
        
        if (mysqli_query($conn, $sql)) {
            header("Location: detalhes_encomenda.php?id=" . $id_encomenda);
            exit;
        }
    }
}

// Buscar review existente, se houver
$sql_review = "SELECT * FROM reviews_produtos WHERE codigo_base = '{$produto['codigo_base']}' AND id_encomenda = $id_encomenda";
$result_review = mysqli_query($conn, $sql_review);
$review = mysqli_fetch_assoc($result_review);

// Incluir o cabeçalho após todos os redirecionamentos
include 'cabecalho.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Avaliar Produto</h3>
                </div>
                <div class="card-body">
                    <!-- Informações do Produto -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <?php if (!empty($produto['imagem'])): ?>
                                <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" 
                                     alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>" 
                                     class="img-fluid">
                            <?php else: ?>
                                <img src="imagens/default.jpg" alt="Sem imagem" class="img-fluid">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h4><?php echo htmlspecialchars($produto['nome_produto']); ?></h4>
                            <p>
                                <strong>Cor:</strong> <?php echo htmlspecialchars($produto['cor'] ?? 'Não especificada'); ?><br>
                                <strong>Tamanho:</strong> <?php echo htmlspecialchars($produto['tamanho'] ?? 'Não especificado'); ?><br>
                                <strong>SKU:</strong> <?php echo htmlspecialchars($produto['sku']); ?>
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="rating">Avaliação</label>
                            <div class="rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" 
                                           <?php echo ($review && $review['classificacao'] == $i) ? 'checked' : ''; ?>>
                                    <label for="star<?php echo $i; ?>">☆</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="comentario">Comentário</label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="4" 
                                      placeholder="Compartilhe sua experiência com este produto..."><?php echo $review ? htmlspecialchars($review['comentario']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                            <a href="detalhes_encomenda.php?id=<?php echo $id_encomenda; ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating input {
    display: none;
}

.rating label {
    font-size: 30px;
    color: #ddd;
    padding: 5px;
    cursor: pointer;
    transition: color 0.3s;
}

.rating input:checked ~ label,
.rating label:hover,
.rating label:hover ~ label {
    color: #ffd700;
}
</style>

<?php include 'rodape.php'; ?> 