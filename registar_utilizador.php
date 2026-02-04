<?php
// Incluir a conexão com o banco de dados
include __DIR__ . '/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $palavra_pass = $_POST['palavra_pass'];
    $confirmar_pass = $_POST['confirmar_pass'];
    $local_envio = $_POST['local_envio'];
    $id_nivel = $_POST['id_nivel'];

    // Verificar se as palavras-passe correspondem
    if ($palavra_pass === $confirmar_pass) {
        // Inserir o utilizador na base de dados
        $sql = "INSERT INTO utilizadores (nome, email, palavra_passe, local_envio, id_nivel) VALUES ('$nome', '$email', '$palavra_pass', '$local_envio', '$id_nivel')";
        if (mysqli_query($conn, $sql)) {
            echo "<div class='alert alert-success'>Utilizador registado com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao registar utilizador: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>As palavras-passe não correspondem!</div>";
    }
}
?>

<?php include 'cabecalho2.php'; ?>

<div class="container mt-4">
    <h1>Registar Utilizador</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="palavra_pass">Palavra-Passe:</label>
            <input type="password" class="form-control" id="palavra_pass" name="palavra_pass" required>
        </div>
        <div class="form-group">
            <label for="confirmar_pass">Confirmar Palavra-Passe:</label>
            <input type="password" class="form-control" id="confirmar_pass" name="confirmar_pass" required>
        </div>
        <div class="form-group">
            <label for="local_envio">Local de Envio:</label>
            <input type="text" class="form-control" id="local_envio" name="local_envio" placeholder="Ex: Rua das Flores, 123, Lisboa" required>
        </div>
        <div class="form-group">
            <label for="id_nivel">Nível de Acesso:</label>
            <select class="form-control" id="id_nivel" name="id_nivel" required>
                <?php
                // Consultar os níveis de acesso
                $sql_niveis = "SELECT id_nivel, descricao FROM nivel_acesso";
                $result_niveis = mysqli_query($conn, $sql_niveis);
                while ($nivel = mysqli_fetch_assoc($result_niveis)): ?>
                    <option value="<?php echo $nivel['id_nivel']; ?>"><?php echo $nivel['descricao']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registar</button>
    </form>

    <!-- Botão Voltar -->
    <div class="text-center mt-3">
        <a href="index2.php" class="btn btn-success">Voltar à entrada</a>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
