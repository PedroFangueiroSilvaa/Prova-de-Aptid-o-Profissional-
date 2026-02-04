<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id_utilizador"]) || $_SESSION["nivel_acesso"] != 9) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';
include 'cabecalho2.php';

// Verificar se o ID da encomenda foi fornecido
if (!isset($_GET['id'])) {
    echo "<script>alert('ID da encomenda não fornecido.'); window.location='gerenciar_encomendas.php';</script>";
    exit;
}

$id_encomenda = $_GET['id'];

// Buscar detalhes da encomenda
$sql_encomenda = "SELECT e.*, u.nome as nome_utilizador, u.email 
                  FROM encomendas e 
                  INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador 
                  WHERE e.id_encomenda = $id_encomenda";
$result_encomenda = mysqli_query($conn, $sql_encomenda);
$encomenda = mysqli_fetch_assoc($result_encomenda);

if (!$encomenda) {
    echo "<script>alert('Encomenda não encontrada.'); window.location='gerenciar_encomendas.php';</script>";
    exit;
}

// Buscar itens da encomenda
$sql_itens = "SELECT ie.*, p.nome as nome_produto, p.imagem, vp.sku, c.descricao as cor, t.descricao as tamanho
              FROM itens_encomenda ie
              INNER JOIN variacoes_produto vp ON ie.sku = vp.sku
              INNER JOIN produtos p ON vp.codigo_base = p.codigo_base
              LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor
              LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
              WHERE ie.id_encomenda = $id_encomenda";
$result_itens = mysqli_query($conn, $sql_itens);
?>

<div class="container mt-5">
    <h2>Detalhes da Encomenda #<?php echo $encomenda['id_encomenda']; ?></h2>
    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($encomenda['nome_utilizador']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($encomenda['email']); ?></p>
    <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($encomenda['data_encomenda'])); ?></p>
    <p><strong>Total:</strong> €<?php echo number_format($encomenda['total'], 2, ',', '.'); ?></p>
    <p><strong>Status:</strong> <?php echo ucfirst($encomenda['status']); ?></p>

    <h3>Itens da Encomenda</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Cor</th>
                <th>Tamanho</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($result_itens)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nome_produto']); ?></td>
                    <td><?php echo htmlspecialchars($item['cor'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($item['tamanho'] ?? 'N/A'); ?></td>
                    <td><?php echo $item['quantidade']; ?></td>
                    <td>€<?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                    <td>€<?php echo number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="gerenciar_encomendas.php" class="btn btn-secondary">Voltar</a>
</div>

<?php
mysqli_close($conn);
?>