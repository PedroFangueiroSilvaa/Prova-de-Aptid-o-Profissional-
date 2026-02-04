<?php
// Validação de administrador
include 'validar_admin.php';

// Incluir a conexão com a base de dados
include __DIR__ . '/conexao.php';

// Consulta à base de dados para buscar os utilizadores
$sql = "SELECT id_utilizador, nome, email, data_registo, id_nivel, palavra_passe, local_envio FROM utilizadores";
$resultado = mysqli_query($conn, $sql);
$nregistos = mysqli_num_rows($resultado);
?>

<!-- Cabeçalho -->
<?php include 'cabecalho2.php'; ?>

<div class="container mt-4">
    <h1>Lista de Utilizadores</h1>
    <p>Número de registos encontrados: <?php echo $nregistos; ?></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Data Registo</th>
                <th>Nível</th>
                <th>Local Envio</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($registo = mysqli_fetch_assoc($resultado)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($registo['id_utilizador']); ?></td>
                    <td><?php echo htmlspecialchars($registo['nome']); ?></td>
                    <td><?php echo htmlspecialchars($registo['email']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($registo['data_registo'])); ?></td>
                    <td>
                        <?php 
                        if ($registo['id_nivel'] == 1) {
                            echo 'Admin';
                        } else {
                            echo 'Utilizador';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($registo['local_envio'] ?: 'Não definido'); ?></td>
                </tr>
            <?php } ?>
            <?php mysqli_free_result($resultado); ?>
        </tbody>
    </table>

    <!-- Botão Voltar -->
    <div class="text-center mt-3">
        <a href="index2.php" class="btn btn-primary">Voltar à Entrada</a>
    </div>
</div>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        padding: 20px;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    h1 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .table {
        margin-top: 20px;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-top: 2px solid #dee2e6;
    }
    
    .table td {
        padding: 12px;
    }
    
    .btn {
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 4px;
        display: inline-block;
    }
    
    .btn-primary {
        background-color: #007bff;
        color: white;
        border: 1px solid #007bff;
    }
    
    .btn-primary:hover {
        background-color: #0056b3;
        text-decoration: none;
        color: white;
    }
</style>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
