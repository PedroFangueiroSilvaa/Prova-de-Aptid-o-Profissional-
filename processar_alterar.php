<?php
// Incluir a conexão com o banco de dados
include __DIR__ . '/conexao.php';

// Verificar o nível de acesso do utilizador atual (assumindo que está guardado na sessão)
session_start();
$id_utilizador_sessao = $_SESSION['id_utilizador'];
$sql_acesso = "SELECT id_nivel FROM utilizadores WHERE id_utilizador = $id_utilizador_sessao";
$resultado_acesso = mysqli_query($conn, $sql_acesso);
$utilizador_atual = mysqli_fetch_assoc($resultado_acesso);
$id_nivel_atual = $utilizador_atual['id_nivel'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_utilizador'])) {
    $id_utilizador = $_POST['id_utilizador'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $palavra_pass = $_POST['palavra_pass'];
    $local_envio = $_POST['local_envio'];

    if ($id_nivel_atual == 9) {
        // Se o utilizador atual for administrador (nível 9), permitir alteração de todos os campos
        $id_nivel = $_POST['id_nivel'];
        if (!empty($palavra_pass)) {
            $sql = "UPDATE utilizadores SET nome = '$nome', email = '$email', palavra_passe = '$palavra_pass', id_nivel = $id_nivel, local_envio = '$local_envio' WHERE id_utilizador = $id_utilizador";
        } else {
            $sql = "UPDATE utilizadores SET nome = '$nome', email = '$email', id_nivel = $id_nivel, local_envio = '$local_envio' WHERE id_utilizador = $id_utilizador";
        }
    } elseif ($id_nivel_atual == 2) {
        // Se o utilizador atual for colaborador (nível 2), permitir alteração de todos os campos exceto o nível de acesso
        if (!empty($palavra_pass)) {
            $sql = "UPDATE utilizadores SET nome = '$nome', email = '$email', palavra_passe = '$palavra_pass', local_envio = '$local_envio' WHERE id_utilizador = $id_utilizador";
        } else {
            $sql = "UPDATE utilizadores SET nome = '$nome', email = '$email', local_envio = '$local_envio' WHERE id_utilizador = $id_utilizador";
        }
    } else {
        // Caso contrário, não permitir alterações
        header("Location: alterar_utilizador.php?msg=Não tens permissão para alterar utilizadores.");
        exit;
    }

    // Executar a consulta
    if (mysqli_query($conn, $sql)) {
        header("Location: alterar_utilizador.php?msg=Utilizador alterado com sucesso!");
        exit;
    } else {
        header("Location: alterar_utilizador.php?msg=Erro ao alterar utilizador.");
        exit;
    }
}

if (isset($_GET['id'])) {
    $id_utilizador = $_GET['id'];

    // Consultar os dados do utilizador
    $sql = "SELECT * FROM utilizadores WHERE id_utilizador = $id_utilizador";
    $resultado = mysqli_query($conn, $sql);
    $utilizador = mysqli_fetch_assoc($resultado);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Alterar Utilizador</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .cabecalho, .rodape {
            background-color: #343a40;
            color: white;
            padding: 10px;
        }
        .container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="cabecalho2">
        <?php include 'cabecalho2.php'; ?>
    </div>

    <div class="container mt-4">
        <h1>Alterar Utilizador</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="id_utilizador">ID Utilizador:</label>
                <input type="text" class="form-control" id="id_utilizador" name="id_utilizador" value="<?php echo $utilizador['id_utilizador']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $utilizador['nome']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $utilizador['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="palavra_pass">Palavra-Passe:</label>
                <input type="password" class="form-control" id="palavra_pass" name="palavra_pass">
                <small class="form-text text-muted">Deixe em branco se não quiser alterar a palavra-passe.</small>
            </div>
            <div class="form-group">
                <label for="local_envio">Local de Envio:</label>
                <input type="text" class="form-control" id="local_envio" name="local_envio" value="<?php echo $utilizador['local_envio']; ?>" required>
            </div>
            <?php if ($id_nivel_atual == 9): ?>
                <div class="form-group">
                    <label for="id_nivel">Nível de Acesso:</label>
                    <select class="form-control" id="id_nivel" name="id_nivel">
                        <option value="1" <?php if ($utilizador['id_nivel'] == 1) echo 'selected'; ?>>Cliente</option>
                        <option value="2" <?php if ($utilizador['id_nivel'] == 2) echo 'selected'; ?>>Colaborador</option>
                        <option value="9" <?php if ($utilizador['id_nivel'] == 9) echo 'selected'; ?>>Administrador</option>
                    </select>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Alterar</button>
            <a href="alterar_utilizador.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

   

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
