<?php
include 'conexao.php';
include 'cabecalho2.php';

// Buscar todas as encomendas com informações dos usuários
$sql = "SELECT e.*, u.nome as nome_utilizador, u.email 
        FROM encomendas e 
        JOIN utilizadores u ON e.id_utilizador = u.id_utilizador 
        ORDER BY e.data_encomenda DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Erro na query: ' . mysqli_error($conn));
}
?>

<style>
.badge {
    font-size: 12px !important;
    font-weight: bold !important;
    color: white !important;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
    padding: 6px 12px;
}
.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important; /* Texto escuro para fundo amarelo */
}
.badge-success {
    background-color: #28a745 !important;
    color: white !important;
}
.badge-info {
    background-color: #17a2b8 !important;
    color: white !important;
}
.badge-danger {
    background-color: #dc3545 !important;
    color: white !important;
}
.badge-secondary {
    background-color: #6c757d !important;
    color: white !important;
}
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-shopping-cart"></i> Gerenciar Encomendas</h4>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Local de Envio</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?php echo $row['id_encomenda']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle fa-2x mr-2 text-primary"></i>
                                            <?php echo htmlspecialchars($row['nome_utilizador']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>€<?php echo number_format($row['total'], 2); ?></td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pendente' => 'warning',
                                            'pago' => 'success',
                                            'enviado' => 'info',
                                            'cancelado' => 'danger'
                                        ];
                                        $status_text = [
                                            'pendente' => 'Pendente',
                                            'pago' => 'Pago',
                                            'enviado' => 'Enviado',
                                            'cancelado' => 'Cancelado'
                                        ];
                                        $class = $status_class[$row['status']] ?? 'secondary';
                                        $text = $status_text[$row['status']] ?? ucfirst($row['status']);
                                        ?>
                                        <span class="badge badge-<?php echo $class; ?>"><?php echo $text; ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['data_encomenda'])); ?></td>
                                    <td><?php echo $row['local_envio']; ?></td>
                                    <td>
                                        <a href="historico_encomendas.php?id=<?php echo $row['id_encomenda']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detalhes
                                        </a>
                                        <?php if ($row['status'] == 'pendente'): ?>
                                            <a href="?atualizar_status=<?php echo $row['id_encomenda']; ?>&status=pago" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Confirmar pagamento desta encomenda?')">
                                                <i class="fas fa-check"></i> Confirmar Pagamento
                                            </a>
                                            <a href="?atualizar_status=<?php echo $row['id_encomenda']; ?>&status=cancelado" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Tem certeza que deseja cancelar esta encomenda?')">
                                                <i class="fas fa-times"></i> Cancelar
                                            </a>
                                        <?php elseif ($row['status'] == 'pago'): ?>
                                            <a href="?atualizar_status=<?php echo $row['id_encomenda']; ?>&status=enviado" 
                                               class="btn btn-sm btn-info"
                                               onclick="return confirm('Confirmar envio desta encomenda?')">
                                                <i class="fas fa-truck"></i> Confirmar Envio
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma encomenda encontrada.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Processar atualização de status
if (isset($_GET['atualizar_status']) && isset($_GET['status'])) {
    $id_encomenda = $_GET['atualizar_status'];
    $status = $_GET['status'];
    
    $sql = "UPDATE encomendas SET status = '$status' WHERE id_encomenda = $id_encomenda";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Status da encomenda atualizado com sucesso!'); window.location='gerenciar_encomendas.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar o status da encomenda.');</script>";
    }
}
?>