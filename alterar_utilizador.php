<?php
// Validação de administrador
include 'validar_admin.php';

// Inclui o arquivo de conexão com o banco de dados para permitir a comunicação com o banco
include __DIR__ . '/conexao.php';

// Executa uma consulta no banco de dados para obter todos os utilizadores
$sql = "SELECT * FROM utilizadores";
$resultado = mysqli_query($conn, $sql); // Armazena o resultado da consulta
$nregistos = mysqli_num_rows($resultado); // Conta o número de registos encontrados
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Alterar Utilizadores</title>
    <!-- Inclui bibliotecas externas para estilização e ícones -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 30px 30px 20px 30px;
        }
        .alert {
            margin-bottom: 20px;
        }
        .btn-voltar {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 18px;
            background: #ff914d;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-voltar:hover {
            background: #e65100;
        }
    </style>
</head>
<body>
    <!-- Inclui o cabeçalho do site a partir de outro arquivo -->
    <?php include 'cabecalho2.php'; ?>

    <!-- Área principal do conteúdo -->
    <main>
        <div class="container mt-4">
            <!-- Título da página -->
            <h1>Alterar Utilizadores</h1>

            <!-- Mostra o número de registos encontrados no banco de dados -->
            <p>Número de registos encontrados: <?php echo $nregistos; ?></p>

            <!-- Verifica se existe uma mensagem na URL (via GET) e a exibe -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <?php echo ($_GET['msg']); // Exibe a mensagem recebida ?>
                </div>
            <?php endif; ?>

            <!-- Cria uma tabela para listar os utilizadores -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <!-- Cabeçalhos da tabela com ícones -->
                        <th>ID Acesso <i class="fas fa-id-badge"></i></th>
                        <th>ID Utilizador <i class="fas fa-id-badge"></i></th>
                        <th>Nome <i class="fas fa-user"></i></th>
                        <th>Email <i class="fas fa-envelope"></i></th>
                        <th>Ação <i class="fas fa-edit"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop para percorrer cada registo retornado pela consulta
                    while ($registo = mysqli_fetch_assoc($resultado)) {
                        echo '<tr>'; // Inicia uma nova linha na tabela
                        echo '<td>' . ($registo['id_nivel']) . '</td>'; // Mostra o ID de acesso do utilizador
                        echo '<td>' . ($registo['id_utilizador']) . '</td>'; // Mostra o ID do utilizador
                        echo '<td>' . ($registo['nome']) . '</td>'; // Mostra o nome do utilizador
                        echo '<td>' . ($registo['email']) . '</td>'; // Mostra o email do utilizador
                        // Cria um botão para alterar o utilizador, passando o ID do utilizador na URL
                        echo '<td><a href="processar_alterar.php?id=' . ($registo['id_utilizador']) . '" class="btn btn-danger">Alterar</a></td>';
                        echo '</tr>'; // Fecha a linha da tabela
                    }
                    // Libera a memória usada pelos resultados da consulta
                    mysqli_free_result($resultado);
                    ?>
                </tbody>
            </table>

            <!-- Botão para voltar à página inicial -->
            <a href="index2.php" class="btn btn-primary">Voltar à entrada</a>
        </div>
    </main>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>