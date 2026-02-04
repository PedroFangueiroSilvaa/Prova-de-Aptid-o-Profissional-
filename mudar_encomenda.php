<?php
// Incluir o cabeçalho
include 'cabecalho2.php';
// Conexão com a base de dados
include "conexao.php"; // Certificar-se de que $conn é definida neste ficheiro
include "validar.php"; // Para validação adicional (opcional)

// Alterar o estado da encomenda (caso o formulário seja submetido)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_encomenda']) && isset($_POST['estado'])) {
        $id_encomenda = intval($_POST['id_encomenda']); // Garantir que o ID é um número inteiro
        $estado = mysqli_real_escape_string($conn, $_POST['estado']); // Proteger contra injeção SQL

        // Validar o estado permitido
        $estados_permitidos = ['pendente', 'pago', 'enviado', 'cancelado'];
        if (!in_array($estado, $estados_permitidos)) {
            echo "<p class='error'>Estado inválido.</p>";
            exit;
        }

        // Atualizar o estado da encomenda na base de dados
        $sql = "UPDATE encomendas SET status = '$estado' WHERE id_encomenda = $id_encomenda";
        if ($conn->query($sql) === TRUE) {
            echo "<p class='success'>Estado da encomenda alterado com sucesso!</p>";
        } else {
            echo "<p class='error'>Erro ao atualizar o estado: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='error'>Dados incompletos para atualização do estado.</p>";
    }
}

// Consultar todas as encomendas
$sql = "
    SELECT 
        e.id_encomenda,
        e.id_utilizador,
        u.nome AS nome_utilizador,
        e.data_encomenda,
        e.total,
        e.status 
    FROM encomendas e
    JOIN utilizadores u ON e.id_utilizador = u.id_utilizador
";
$result = $conn->query($sql);

// Verificar se há encomendas
if ($result->num_rows === 0) {
    echo "<p class='info'>Não existem encomendas registadas.</p>";
}
?>
<div class="container">
    <h2>Lista de Encomendas</h2>
    <table class="tabela-encomendas">
        <thead>
            <tr>
                <th>ID Encomenda</th>
                <th>Utilizador</th>
                <th>Data Encomenda</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_encomenda']) ?></td>
                    <td><?= htmlspecialchars($row['nome_utilizador']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['data_encomenda'])) ?></td>
                    <td>€<?= number_format($row['total'], 2, ',', '.') ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                    <td>
                        <!-- Formulário para alterar o estado da encomenda -->
                        <form method="POST" action="mudar_encomenda.php" class="alterar-estado-form">
                            <input type="hidden" name="id_encomenda" value="<?= htmlspecialchars($row['id_encomenda']) ?>">
                            <select name="estado" class="select-estado">
                                <option value="pendente" <?= ($row['status'] == 'pendente' ? 'selected' : '') ?>>Pendente</option>
                                <option value="pago" <?= ($row['status'] == 'pago' ? 'selected' : '') ?>>Pago</option>
                                <option value="enviado" <?= ($row['status'] == 'enviado' ? 'selected' : '') ?>>Enviado</option>
                                <option value="cancelado" <?= ($row['status'] == 'cancelado' ? 'selected' : '') ?>>Cancelado</option>
                            </select>
                            <button type="submit" class="btn-alterar">Alterar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php

// Fechar conexão com a base de dados
mysqli_close($conn);
?>

<!-- Estilos CSS -->
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 80%;
        margin: 50px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    h2 {
        text-align: center;
        font-size: 28px;
        color: #333;
        margin-bottom: 30px;
    }
    .tabela-encomendas {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }
    .tabela-encomendas th,
    .tabela-encomendas td {
        padding: 12px 15px;
        text-align: left;
        border: 1px solid #ddd;
    }
    .tabela-encomendas th {
        background-color: #2c3e50;
        color: #fff;
    }
    .tabela-encomendas tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .tabela-encomendas tr:hover {
        background-color: #f1f1f1;
    }
    .select-estado {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ddd;
        margin-right: 10px;
    }
    .btn-alterar {
        padding: 8px 15px;
        background-color: #3498db;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        font-size: 14px;
    }
    .btn-alterar:hover {
        background-color: #2980b9;
    }
    .success {
        color: green;
        text-align: center;
        font-weight: bold;
    }
    .error {
        color: red;
        text-align: center;
        font-weight: bold;
    }
    .info {
        color: #6c757d;
        text-align: center;
        font-weight: bold;
    }
</style>