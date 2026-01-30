<?php
// Incluir a conexão com o banco de dados
include 'conexao.php';

// Verificar se o usuário está logado e tem nível de acesso adequado
// [Você pode descomentar este código se quiser restringir o acesso]
/*
if (!isset($_SESSION["id_utilizador"]) || $_SESSION["nivel_acesso"] != 9) {
    header("Location: login.php");
    exit();
}
*/

// Definir os nomes das marcas
$nome_antigo = 'Leone';
$novo_nome = 'Under Armor';

// Consultar se a marca Leone existe
$sql_check = "SELECT id_marca FROM marcas WHERE nome = '$nome_antigo'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    // A marca Leone existe, obter o ID
    $row = mysqli_fetch_assoc($result_check);
    $id_marca = $row['id_marca'];
    
    // Verificar se já existe uma marca com o novo nome
    $sql_novo_nome = "SELECT id_marca FROM marcas WHERE nome = '$novo_nome'";
    $result_novo_nome = mysqli_query($conn, $sql_novo_nome);
    
    if (mysqli_num_rows($result_novo_nome) > 0) {
        echo "<p>Erro: Já existe uma marca com o nome '$novo_nome'.</p>";
    } else {
        // Atualizar o nome da marca
        $sql_update = "UPDATE marcas SET nome = '$novo_nome' WHERE id_marca = $id_marca";
        if (mysqli_query($conn, $sql_update)) {
            echo "<p>Marca '$nome_antigo' atualizada para '$novo_nome' com sucesso!</p>";
        } else {
            echo "<p>Erro ao atualizar marca: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p>A marca '$nome_antigo' não foi encontrada no banco de dados.</p>";
}

// Fechar a conexão
mysqli_close($conn);
?>

<p><a href="listar_marcas_fornecedores.php">Voltar para a lista de marcas</a></p>
