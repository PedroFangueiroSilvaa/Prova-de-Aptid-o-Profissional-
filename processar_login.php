<?php
// Iniciar a sessão
session_start();

// Incluir o ficheiro de conexão
include __DIR__ . '/conexao.php'; // Certifique-se de que este arquivo contém a conexão com o banco de dados

// Receber os dados do formulário
$email = $_POST['email'] ?? null;
$palavra_passe = $_POST['palavra_pass'] ?? null;

// Validar entrada
if (empty($email) || empty($palavra_passe)) {
    header("Location: login.php?erro=campos_vazios");
    exit();
}

// Consulta SQL para verificar o utilizador
$sql = "SELECT * FROM utilizadores WHERE email = '$email' AND palavra_passe = '$palavra_passe'";
$resultado = mysqli_query($conn, $sql);

$mensagem = ''; // Variável para armazenar a mensagem de feedback

if (mysqli_num_rows($resultado) === 1) {
    // Obter o registo do utilizador
    $registo = mysqli_fetch_assoc($resultado);
    
    // Definir variáveis de sessão
    $_SESSION["id_utilizador"] = $registo["id_utilizador"];
    $_SESSION["nome_utilizador"] = $registo["nome"];
    $_SESSION["nivel_acesso"] = $registo["id_nivel"];

    // Verificar se existe um session_id
    $session_id = session_id();  // Obtém o session_id atual

    // Verifica se o session_id existe na tabela do carrinho
    $sqlCheckCart = "SELECT * FROM carrinho WHERE session_id = '$session_id'";
    $resultCheckCart = mysqli_query($conn, $sqlCheckCart);

    if (mysqli_num_rows($resultCheckCart) > 0) {
        // O session_id existe, então atualiza o id_utilizador
        $sqlUpdateCart = "UPDATE carrinho SET id_utilizador = " . $registo["id_utilizador"] . ", session_id = NULL WHERE session_id = '$session_id'";
        mysqli_query($conn, $sqlUpdateCart);

        if (mysqli_affected_rows($conn) > 0) {
            // Sucesso na atualização do carrinho
            $mensagem = "Carrinho atualizado com sucesso para o utilizador " . $registo["id_utilizador"];
        } else {
            // Erro ao atualizar o carrinho
            $mensagem = "Erro ao atualizar o carrinho.";
        }
    } else {
        // Nenhum carrinho encontrado para este session_id
        $mensagem = "Nenhum carrinho encontrado para o session_id: " . $session_id;
    }

    // Redirecionar o utilizador para a página inicial
    if ($registo["id_nivel"] == 1) {
        header("Location: index.php");
    } else {
        header("Location: index2.php");
    } 
} else {
    // Caso o login seja inválido
    $mensagem = "Login inválido.";
    header("Location: login.php?erro=login_invalido");
    exit();
}

// Fechar a conexão
mysqli_close($conn);

// Exibir a mensagem de feedback
echo $mensagem;
?>
