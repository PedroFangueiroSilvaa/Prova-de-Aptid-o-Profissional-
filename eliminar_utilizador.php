<?php
// Validação de administrador
include 'validar_admin.php';

// Incluir a conexão com o banco de dados
include __DIR__ . '/conexao.php';

// Consultar os utilizadores
$sql = "SELECT * FROM utilizadores";
$resultado = mysqli_query($conn, $sql);
$nregistos = mysqli_num_rows($resultado);
?>

<body>

    <div class="cabecalho2">
        <?php include 'cabecalho2.php'; ?>
    </div>

    <div class="container mt-4">
        <h1>Eliminar Utilizadores</h1>
        <p>Número de registos encontrados: <?php echo $nregistos; ?></p>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($registo = mysqli_fetch_assoc($resultado)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($registo['id_utilizador']) . '</td>';
                        echo '<td>' . htmlspecialchars($registo['nome']) . '</td>';
                        echo '<td>' . htmlspecialchars($registo['email']) . '</td>';
                        echo '<td>';
                        echo '<a href="processar_eliminar.php?id=' . htmlspecialchars($registo['id_utilizador']) . '" ';
                        echo 'class="btn btn-danger btn-sm" ';
                        echo 'onclick="return confirm(\'Tem a certeza que deseja eliminar este utilizador?\');">';
                        echo '<i class="fas fa-trash"></i> Eliminar';
                        echo '</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    mysqli_free_result($resultado);
                    ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-3">
            <a href="index2.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Voltar à entrada
            </a>
        </div>
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .table {
            margin-top: 20px;
        }
        
        .table th {
            font-weight: 600;
            padding: 12px;
        }
        
        .table td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .table-dark th {
            background-color: #343a40;
            color: white;
            border-color: #454d55;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        
        .btn {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .table-responsive {
            border-radius: 4px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
