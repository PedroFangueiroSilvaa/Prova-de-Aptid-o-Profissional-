<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

include 'cabecalho2.php';
include 'conexao.php';

// Buscar produtos
$query = "SELECT codigo_base, nome, imagem FROM produtos ORDER BY nome";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Atualizar Imagens dos Produtos</h2>
            <p>Adicione as imagens na pasta 'imagens' e atualize os caminhos abaixo.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Imagem Atual</th>
                                <th>Caminho</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($produto = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                                    <td>
                                        <?php if (!empty($produto['imagem'])): ?>
                                            <img src="<?= $produto['imagem'] ?>" alt="Imagem atual" style="max-width: 100px;">
                                        <?php else: ?>
                                            <span class="text-muted">Sem imagem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="text" name="imagem[<?= $produto['codigo_base'] ?>]" 
                                               class="form-control" value="<?= htmlspecialchars($produto['imagem']) ?>"
                                               placeholder="Ex: imagens/produto_123.jpg">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Processar atualizações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['imagem'] as $codigo_base => $imagem) {
        $imagem = mysqli_real_escape_string($conn, $imagem);
        $codigo_base = mysqli_real_escape_string($conn, $codigo_base);
        $update_query = "UPDATE produtos SET imagem = '$imagem' WHERE codigo_base = '$codigo_base'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Imagem atualizada com sucesso para o produto $codigo_base!');</script>";
        } else {
            echo "<script>alert('Erro ao atualizar imagem para o produto $codigo_base: " . mysqli_error($conn) . "');</script>";
        }
    }
}

mysqli_close($conn);
?>