<?php
// Incluir a conexão com a base de dados
include 'conexao.php';

// Verificar se o formulário foi submetido via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter os dados do formulário
    $nome = $_POST['nome'];
    $imagem = $_FILES['imagem'];

    // Verificar se já existe uma marca com o mesmo nome
    $queryCheckNome = "SELECT id_marca FROM marcas WHERE nome = '$nome'";
    $resultCheckNome = mysqli_query($conn, $queryCheckNome);
    
    if (mysqli_num_rows($resultCheckNome) > 0) {
        echo "<div class='alert alert-danger'>Erro: Já existe uma marca com este nome!</div>";
        echo "<a href='adicionar_marca.php' class='btn btn-primary'>Voltar</a>";
        exit;
    }

    // Obter o próximo ID de marca disponível
    $queryMaxId = "SELECT MAX(id_marca) as max_id FROM marcas";
    $resultMaxId = mysqli_query($conn, $queryMaxId);
    $rowMaxId = mysqli_fetch_assoc($resultMaxId);
    $nextId = ($rowMaxId['max_id'] ? $rowMaxId['max_id'] + 1 : 9001);

    // Verificar se a imagem foi carregada corretamente
    if ($imagem['error'] == 0) {
        // Definir o caminho onde a imagem será armazenada
        $diretorio_imagens = 'imagens/marcas/';  // Caminho onde as imagens serão armazenadas
        $nome_imagem = basename($imagem['name']);  // Nome do ficheiro da imagem
        $caminho_imagem = $diretorio_imagens . $nome_imagem;  // Caminho completo da imagem

        // Verificar se já existe uma marca com a mesma imagem
        $queryCheckImagem = "SELECT id_marca FROM marcas WHERE imagem = '$caminho_imagem'";
        $resultCheckImagem = mysqli_query($conn, $queryCheckImagem);
        
        if (mysqli_num_rows($resultCheckImagem) > 0) {
            echo "<div class='alert alert-danger'>Erro: Já existe uma marca com esta imagem!</div>";
            echo "<a href='adicionar_marca.php' class='btn btn-primary'>Voltar</a>";
            exit;
        }

        // Mover o ficheiro para o diretório especificado
        if (move_uploaded_file($imagem['tmp_name'], $caminho_imagem)) {
            // Inserir os dados na base de dados com ID gerado automaticamente
            $query = "INSERT INTO marcas (id_marca, nome, imagem) VALUES ($nextId, '$nome', '$caminho_imagem')";
            if (mysqli_query($conn, $query)) {
                // Redirecionar para a página de marcas com mensagem de sucesso
                header('Location: listar_marcas_fornecedores.php?msg=sucesso');
                exit;
            } else {
                echo "Erro: " . mysqli_error($conn);
            }
        } else {
            echo "Erro ao mover o ficheiro para o diretório.";
        }
    } else {
        echo "Erro no upload da imagem: " . $imagem['error'];
    }
}

// Fechar a conexão com a base de dados
mysqli_close($conn);
?>