<?php
// criar_pasta_produto.php
include 'conexao.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produto = $_POST['id_produto'];
    $codigo_base = $_POST['codigo_base'];
    $imagem = $_POST['imagem'];
    $msg = '';
    $pasta = "imagens/produtos/" . $codigo_base;
    // Criar pasta se não existir
    if (!is_dir($pasta)) {
        if (mkdir($pasta, 0777, true)) {
            $msg .= "Pasta '$codigo_base' criada com sucesso. ";
        } else {
            $msg .= "Erro ao criar a pasta '$codigo_base'. ";
        }
    } else {
        $msg .= "A pasta '$codigo_base' já existe. ";
    }
    // Mover imagem para a nova pasta se existir imagem e ficheiro
    if ($imagem && file_exists($imagem)) {
        $nome_imagem = basename($imagem);
        $novo_caminho = $pasta . "/" . $nome_imagem;
        if (realpath($imagem) !== realpath($novo_caminho)) {
            if (copy($imagem, $novo_caminho)) {
                // Atualizar caminho da imagem na base de dados
                $sql = "UPDATE produtos SET imagem = '" . $novo_caminho . "' WHERE id_produto = $id_produto";
                mysqli_query($conn, $sql);
                $msg .= "Imagem movida para a pasta do produto.";
            } else {
                $msg .= "Erro ao mover a imagem.";
            }
        } else {
            $msg .= "A imagem já está na pasta correta.";
        }
    } else {
        $msg .= "Produto sem imagem para mover.";
    }
    header('Location: mostrar_produtos_com_pasta.php?msg=' . urlencode($msg));
    exit;
} else {
    header('Location: mostrar_produtos_com_pasta.php');
    exit;
}
?>
