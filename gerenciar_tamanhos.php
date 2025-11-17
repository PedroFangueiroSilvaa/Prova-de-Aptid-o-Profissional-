<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Verificar se a conexão foi estabelecida
if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Processar o formulário de adição de tamanho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_tamanho'])) {
    $descricao = trim($_POST['descricao']);
    
    // Validar descrição
    if (empty($descricao)) {
        $erro = "A descrição do tamanho não pode estar vazia.";
    } else {
        // Verificar se o tamanho já existe
        $queryCheck = "SELECT codigo_tamanho FROM tamanhos WHERE descricao = ?";
        $stmt = mysqli_prepare($conn, $queryCheck);
        if (!$stmt) {
            $erro = "Erro na preparação da consulta: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $descricao);
            mysqli_stmt_execute($stmt);
            $resultCheck = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($resultCheck) > 0) {
                $erro = "Este tamanho já existe no sistema.";
            } else {
                // Obter o próximo código de tamanho disponível (mínimo 4014)
                $queryMaxCode = "SELECT MAX(CAST(codigo_tamanho AS UNSIGNED)) as max_code FROM tamanhos WHERE codigo_tamanho REGEXP '^[0-9]+$'";
                $resultMaxCode = mysqli_query($conn, $queryMaxCode);
                $rowMaxCode = mysqli_fetch_assoc($resultMaxCode);
                $nextCode = max(4014, ($rowMaxCode['max_code'] ? $rowMaxCode['max_code'] + 1 : 4014));
                
                // Inserir novo tamanho com código automático
                $queryInsert = "INSERT INTO tamanhos (codigo_tamanho, descricao) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) {
                    $erro = "Erro na preparação da inserção: " . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($stmt, "ss", $nextCode, $descricao);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $sucesso = "Tamanho adicionado com sucesso! (Código: $nextCode)";
                    } else {
                        $erro = "Erro ao adicionar o tamanho: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
}

// Processar a exclusão de tamanho
if (isset($_GET['apagar'])) {
    $codigo_tamanho = $_GET['apagar'];
    
    // Verificar se o tamanho está em uso
    $queryCheck = "SELECT id_variacao FROM variacoes_produto WHERE codigo_tamanho = ?";
    $stmt = mysqli_prepare($conn, $queryCheck);
    if (!$stmt) {
        $erro = "Erro na preparação da consulta: " . mysqli_error($conn);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $codigo_tamanho);
        mysqli_stmt_execute($stmt);
        $resultCheck = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultCheck) > 0) {
            $erro = "Não é possível apagar este tamanho pois está em uso em variações de produtos.";
        } else {
            // Apagar o tamanho
            $queryDelete = "DELETE FROM tamanhos WHERE codigo_tamanho = ?";
            $stmt = mysqli_prepare($conn, $queryDelete);
            if (!$stmt) {
                $erro = "Erro na preparação da exclusão: " . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($stmt, "s", $codigo_tamanho);
                
                if (mysqli_stmt_execute($stmt)) {
                    $sucesso = "Tamanho apagado com sucesso!";
                } else {
                    $erro = "Erro ao apagar o tamanho: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Buscar todos os tamanhos ordenados pelo código (menor ao maior)
$queryTamanhos = "SELECT * FROM tamanhos ORDER BY CAST(codigo_tamanho AS UNSIGNED)";
$resultTamanhos = mysqli_query($conn, $queryTamanhos);

// Verificar se a consulta foi bem sucedida
if (!$resultTamanhos) {
    die("Erro na consulta: " . mysqli_error($conn));
}

// Verificar se existem tamanhos
$numTamanhos = mysqli_num_rows($resultTamanhos);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="listar_variacoes.php">Variações</a></li>
                    <li class="breadcrumb-item active">Gerenciar Tamanhos</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Gerenciar Tamanhos</h2>
            <a href="listar_variacoes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar para Variações
            </a>
        </div>
    </div>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
        </div>
    <?php endif; ?>

    <?php if (isset($sucesso)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $sucesso ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Adicionar Novo Tamanho</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="descricao">Descrição do Tamanho</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" required>
                        </div>
                        <button type="submit" name="adicionar_tamanho" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Adicionar Tamanho
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tamanhos Disponíveis</h5>
                </div>
                <div class="card-body">
                    <?php if ($numTamanhos == 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Não existem tamanhos cadastrados no sistema.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Descrição</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($tamanho = mysqli_fetch_assoc($resultTamanhos)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($tamanho['codigo_tamanho']) ?></td>
                                            <td><?= htmlspecialchars($tamanho['descricao']) ?></td>
                                            <td>
                                                <a href="?apagar=<?= $tamanho['codigo_tamanho'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja apagar este tamanho?')" title="Apagar">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Fechar conexão com a base de dados
mysqli_close($conn);
?> 