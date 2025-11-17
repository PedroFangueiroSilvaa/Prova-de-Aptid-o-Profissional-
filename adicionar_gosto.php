<?php
// Inicia uma sessão para armazenar informações do usuário enquanto ele navega no site.
session_start();

// Inclui o arquivo de conexão com o banco de dados para que possamos interagir com ele.
include 'conexao.php';

// Verifica se o usuário está logado. Caso contrário, redireciona para outra página com uma mensagem de erro.
if (!isset($_SESSION['id_utilizador'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Responde com JSON informando que é necessário iniciar sessão para adicionar aos gostos.
        echo json_encode(['success' => false, 'message' => 'Precisa de iniciar sessão para adicionar aos gostos.']);
        exit(); // Interrompe a execução do código.
    } else {
        // Redireciona o usuário para a página "meus_gostos.php" com um erro indicando que ele não está identificado.
        header('Location: meus_gostos.php?error=user_not_identified');
        exit(); // Interrompe a execução do código.
    }
}

// Obter dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se a requisição for via POST, obtém os dados do produto e a ação do corpo da requisição.
    $codigo_base = isset($_POST['codigo_base']) ? $_POST['codigo_base'] : '';
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';
} else {
    // Se não, obtém os dados da URL (GET).
    $codigo_base = isset($_GET['codigo_base']) ? $_GET['codigo_base'] : '';
    $acao = isset($_GET['acao']) ? $_GET['acao'] : '';
}
$id_utilizador = $_SESSION['id_utilizador']; // ID do usuário logado, obtido da sessão.

// Verifica se o produto existe
$query = "SELECT * FROM produtos WHERE codigo_base = '" . mysqli_real_escape_string($conn, $codigo_base) . "'";
$result = mysqli_query($conn, $query); // Executa a consulta no banco de dados.
if (!$result || mysqli_num_rows($result) == 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Se a requisição for via POST e o produto não for encontrado, retorna uma mensagem de erro em formato JSON.
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado.']);
        exit(); // Interrompe a execução do código.
    } else {
        // Caso contrário, redireciona o usuário com uma mensagem de erro.
        header('Location: meus_gostos.php?error=product_not_found');
        exit(); // Interrompe a execução do código.
    }
}

// Verifica se o código do produto está vazio. Caso esteja, retorna uma mensagem de erro em formato JSON.
if (empty($codigo_base)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Código do produto não fornecido']);
        exit(); // Interrompe a execução do código.
    } else {
        header('Location: meus_gostos.php?error=invalid_data');
        exit(); // Interrompe a execução do código.
    }
}

// Verifica qual ação o usuário quer realizar.
if ($acao === 'adicionar') {
    // Se a ação for "adicionar", verifica se o produto já está nos favoritos do usuário.
    $check_query = "SELECT id_gosto FROM gostos WHERE id_utilizador = $id_utilizador AND codigo_base = '" . mysqli_real_escape_string($conn, $codigo_base) . "'";
    $result_check = mysqli_query($conn, $check_query); // Usar variável separada

    if (mysqli_num_rows($result_check) > 0) {
        // Se o produto já estiver nos favoritos, retorna uma mensagem de erro em formato JSON.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo json_encode(['success' => false, 'message' => 'Produto já está nos favoritos.']);
            exit(); // Interrompe a execução do código.
        } else {
            header('Location: meus_gostos.php?error=already_favorite');
            exit(); // Interrompe a execução do código.
        }
    }

    // Caso o produto não esteja nos favoritos, adiciona-o à lista de favoritos do usuário.
    $query_insert = "INSERT INTO gostos (id_utilizador, codigo_base) VALUES ($id_utilizador, '" . mysqli_real_escape_string($conn, $codigo_base) . "')";
    if (mysqli_query($conn, $query_insert)) {
        $novo_id_gosto = mysqli_insert_id($conn); // Obter o id_gosto inserido
        // Debug temporário caso continue a dar 0
        if ($novo_id_gosto == 0) {
            error_log('DEBUG id_gosto: ' . $novo_id_gosto . ' | MySQL error: ' . mysqli_error($conn));
        }
        // Se a inserção for bem-sucedida, retorna uma mensagem de sucesso em formato JSON.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo json_encode(['success' => true, 'message' => 'Produto adicionado aos favoritos!', 'id_gosto' => $novo_id_gosto]);
            exit(); // Interrompe a execução do código.
        } else {
            header('Location: meus_gostos.php?success=added');
            exit(); // Interrompe a execução do código.
        }
    } else {
        // Caso ocorra um erro ao adicionar, retorna uma mensagem de erro em formato JSON.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar aos favoritos.']);
            exit(); // Interrompe a execução do código.
        } else {
            header('Location: meus_gostos.php?error=database_error');
            exit(); // Interrompe a execução do código.
        }
    }
} elseif ($acao === 'remover') {
    // Se a ação for "remover", exclui o produto da lista de favoritos do usuário.
    $query = "DELETE FROM gostos WHERE id_utilizador = $id_utilizador AND codigo_base = '" . mysqli_real_escape_string($conn, $codigo_base) . "'";
    if (mysqli_query($conn, $query)) {
        // Se a exclusão for bem-sucedida, retorna uma mensagem de sucesso em formato JSON.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo json_encode(['success' => true, 'message' => 'Produto removido dos favoritos!']);
            exit(); // Interrompe a execução do código.
        } else {
            header('Location: meus_gostos.php?success=removed');
            exit(); // Interrompe a execução do código.
        }
    } else {
        // Caso ocorra um erro ao remover, retorna uma mensagem de erro em formato JSON.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover dos favoritos.']);
            exit(); // Interrompe a execução do código.
        } else {
            header('Location: meus_gostos.php?error=database_error');
            exit(); // Interrompe a execução do código.
        }
    }
} else {
    // Se a ação enviada não for "adicionar" nem "remover", retorna uma mensagem de erro em formato JSON.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
        exit(); // Interrompe a execução do código.
    } else {
        header('Location: meus_gostos.php?error=invalid_data');
        exit(); // Interrompe a execução do código.
    }
}

// Fecha a conexão com o banco de dados para liberar recursos.
mysqli_close($conn);
?>