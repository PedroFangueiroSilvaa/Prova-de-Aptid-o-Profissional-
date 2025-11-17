<?php
// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Verificar se a conexão foi estabelecida
if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Processar o formulário de adição de cor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_cor'])) {
    $descricao = trim($_POST['descricao']);
    
    // Validar descrição
    if (empty($descricao)) {
        $erro = "A descrição da cor não pode estar vazia.";
    } else {
        // Verificar se a cor já existe
        $queryCheck = "SELECT codigo_cor FROM cores WHERE descricao = ?";
        $stmt = mysqli_prepare($conn, $queryCheck);
        if (!$stmt) {
            $erro = "Erro na preparação da consulta: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $descricao);
            mysqli_stmt_execute($stmt);
            $resultCheck = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($resultCheck) > 0) {
                $erro = "Esta cor já existe no sistema.";
            } else {
                // Obter o próximo código de cor disponível (começando em 8000)
                $queryMaxCode = "SELECT MAX(CAST(codigo_cor AS UNSIGNED)) as max_code FROM cores WHERE codigo_cor REGEXP '^[0-9]+$'";
                $resultMaxCode = mysqli_query($conn, $queryMaxCode);
                $rowMaxCode = mysqli_fetch_assoc($resultMaxCode);
                $nextCode = max(8000, ($rowMaxCode['max_code'] ? $rowMaxCode['max_code'] + 1 : 8000));
                
                // Inserir nova cor com código automático
                $queryInsert = "INSERT INTO cores (codigo_cor, descricao) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $queryInsert);
                if (!$stmt) {
                    $erro = "Erro na preparação da inserção: " . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($stmt, "ss", $nextCode, $descricao);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $sucesso = "Cor adicionada com sucesso! (Código: $nextCode)";
                    } else {
                        $erro = "Erro ao adicionar a cor: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
}

// Processar a exclusão de cor
if (isset($_GET['apagar'])) {
    $codigo_cor = $_GET['apagar'];
    
    // Verificar se a cor está em uso
    $queryCheck = "SELECT id_variacao FROM variacoes_produto WHERE codigo_cor = ?";
    $stmt = mysqli_prepare($conn, $queryCheck);
    if (!$stmt) {
        $erro = "Erro na preparação da consulta: " . mysqli_error($conn);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $codigo_cor);
        mysqli_stmt_execute($stmt);
        $resultCheck = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultCheck) > 0) {
            $erro = "Não é possível apagar esta cor pois está em uso em variações de produtos.";
        } else {
            // Apagar a cor
            $queryDelete = "DELETE FROM cores WHERE codigo_cor = ?";
            $stmt = mysqli_prepare($conn, $queryDelete);
            if (!$stmt) {
                $erro = "Erro na preparação da exclusão: " . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($stmt, "s", $codigo_cor);
                
                if (mysqli_stmt_execute($stmt)) {
                    $sucesso = "Cor apagada com sucesso!";
                } else {
                    $erro = "Erro ao apagar a cor: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Buscar todas as cores ordenadas pelo código (menor ao maior)
$queryCores = "SELECT * FROM cores ORDER BY CAST(codigo_cor AS UNSIGNED)";
$resultCores = mysqli_query($conn, $queryCores);

// Verificar se a consulta foi bem sucedida
if (!$resultCores) {
    die("Erro na consulta: " . mysqli_error($conn));
}

// Verificar se existem cores
$numCores = mysqli_num_rows($resultCores);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="listar_variacoes.php">Variações</a></li>
                    <li class="breadcrumb-item active">Gerenciar Cores</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Gerenciar Cores</h2>
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
                    <h5 class="mb-0">Adicionar Nova Cor</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="descricao">Descrição da Cor</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" required>
                        </div>
                        <button type="submit" name="adicionar_cor" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Adicionar Cor
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cores Disponíveis</h5>
                </div>
                <div class="card-body">
                    <?php if ($numCores == 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Não existem cores cadastradas no sistema.
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
                                    <?php while ($cor = mysqli_fetch_assoc($resultCores)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cor['codigo_cor']) ?></td>
                                            <td><?= htmlspecialchars($cor['descricao']) ?></td>
                                            <td>
                                                <a href="?apagar=<?= $cor['codigo_cor'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja apagar esta cor?')" title="Apagar">
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