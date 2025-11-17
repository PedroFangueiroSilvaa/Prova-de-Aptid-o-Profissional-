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

// Verificar se o ID da encomenda foi fornecido
if (!isset($_GET['id'])) {
    header("Location: minhas_encomendas.php");
    exit;
}

$id_encomenda = $_GET['id'];
$id_utilizador = $_SESSION['id_utilizador'];

// Verificar se a encomenda pertence ao usuário
$sql_check = "SELECT * FROM encomendas WHERE id_encomenda = $id_encomenda AND id_utilizador = $id_utilizador";
$result_check = mysqli_query($conn, $sql_check);
if (!$result_check || mysqli_num_rows($result_check) == 0) {
    header("Location: minhas_encomendas.php");
    exit;
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $classificacao = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comentario = isset($_POST['comentario']) ? mysqli_real_escape_string($conn, $_POST['comentario']) : '';
    
    if ($classificacao > 0 && $classificacao <= 5) {
        // Verificar se já existe uma review para esta encomenda
        $sql_check_review = "SELECT id_review FROM reviews_encomendas WHERE id_encomenda = $id_encomenda";
        $result_check_review = mysqli_query($conn, $sql_check_review);
        
        if (mysqli_num_rows($result_check_review) > 0) {
            // Atualizar review existente
            $sql = "UPDATE reviews_encomendas SET classificacao = $classificacao, comentario = '$comentario', data_review = NOW() WHERE id_encomenda = $id_encomenda";
        } else {
            // Inserir nova review
            $sql = "INSERT INTO reviews_encomendas (id_encomenda, id_utilizador, classificacao, comentario, data_review) 
                    VALUES ($id_encomenda, $id_utilizador, $classificacao, '$comentario', NOW())";
        }
        
        if (mysqli_query($conn, $sql)) {
            header("Location: detalhes_encomenda.php?id=" . $id_encomenda);
            exit;
        }
    }
}

// Buscar review existente, se houver
$sql_review = "SELECT * FROM reviews_encomendas WHERE id_encomenda = $id_encomenda";
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
                    <h3 class="mb-0">Avaliar Encomenda #<?php echo $id_encomenda; ?></h3>
                </div>
                <div class="card-body">
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
                                      placeholder="Compartilhe sua experiência com esta encomenda..."><?php echo $review ? htmlspecialchars($review['comentario']) : ''; ?></textarea>
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