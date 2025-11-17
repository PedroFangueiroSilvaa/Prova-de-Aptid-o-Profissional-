<?php
// Incluir a ligação à base de dados
include 'conexao.php';

// Processar o formulário ANTES de qualquer saída
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $id_marca = $_POST['id_marca'];
    $id_fornecedor = $_POST['id_fornecedor'];
    $id_categoria = $_POST['id_categoria'];
    $preco = $_POST['preco'];
    $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : null;

    $codigo_base_query = "SELECT codigo_base FROM produtos ORDER BY codigo_base DESC LIMIT 1";
    $result = mysqli_query($conn, $codigo_base_query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $ultimo_codigo = $row['codigo_base'];
        preg_match('/([A-Za-z]*)(\d+)/', $ultimo_codigo, $matches);
        if (count($matches) >= 3) {
            $prefixo = $matches[1];
            $numero = intval($matches[2]) + 1;
            $novo_codigo_base = $prefixo . $numero;
        } else {
            $novo_codigo_base = $ultimo_codigo . '1';
        }
    } else {
        $novo_codigo_base = 'PROD1';
    }

    $pasta_produto = "imagens/produtos/" . $novo_codigo_base;
    if (!is_dir($pasta_produto)) {
        mkdir($pasta_produto, 0777, true);
    }

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagem_tmp = $_FILES['imagem']['tmp_name'];
        $imagem_nome = basename($_FILES['imagem']['name']);
        $destino = $pasta_produto . "/" . $imagem_nome;
        if (move_uploaded_file($imagem_tmp, $destino)) {
            $imagem = $destino;
        } else {
            echo "<p>Erro ao carregar a imagem. Por favor, tente novamente.</p>";
            exit;
        }
    } else {
        $imagem = null;
    }

    $query = "INSERT INTO produtos (
                nome, id_marca, id_fornecedor, id_categoria, 
                preco, descricao, imagem, codigo_base
              ) VALUES (
                '$nome', $id_marca, $id_fornecedor, $id_categoria,
                $preco, " . ($descricao !== null ? "'$descricao'" : "NULL") . ",
                " . ($imagem ? "'$imagem'" : "NULL") . ",
                '$novo_codigo_base'
              )";

    if (mysqli_query($conn, $query)) {
        header('Location: adicionar_produto.php?msg=sucesso');
        exit;
    } else {
        $erro_adicionar = "Erro ao adicionar o produto: " . mysqli_error($conn);
    }
}
// Incluir o cabeçalho
include 'cabecalho2.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto</title>
    <style>
        /* Estilo básico para a página */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        /* Estilo para o título */
        h1 {
            text-align: center;
            color: #333;
        }
        /* Estilo para o botão */
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
        }
        button:hover {
            background-color: #45a049;
        }
        /* Estilo para o link de voltar */
        a {
            text-decoration: none;
            color: #007BFF;
            font-size: 16px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <?php
    // Verificar se o formulário foi submetido
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obter os dados do formulário
        $nome = $_POST['nome'];
        $id_marca = $_POST['id_marca'];
        $id_fornecedor = $_POST['id_fornecedor'];
        $id_categoria = $_POST['id_categoria'];
        $preco = $_POST['preco'];
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : null;

        // Encontrar o último codigo_base e incrementá-lo
        $codigo_base_query = "SELECT codigo_base FROM produtos ORDER BY codigo_base DESC LIMIT 1";
        $result = mysqli_query($conn, $codigo_base_query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $ultimo_codigo = $row['codigo_base'];
            
            // Extrair a parte numérica
            preg_match('/([A-Za-z]*)(\d+)/', $ultimo_codigo, $matches);
            if (count($matches) >= 3) {
                $prefixo = $matches[1];
                $numero = intval($matches[2]) + 1;
                $novo_codigo_base = $prefixo . $numero;
            } else {
                // Se não conseguir extrair o número, apenas adiciona 1
                $novo_codigo_base = $ultimo_codigo . '1';
            }
        } else {
            // Se não houver produtos, criar um código base inicial
            $novo_codigo_base = 'PROD1';
        }

        // Criar a pasta para o produto dentro de imagens/produtos
        $pasta_produto = "imagens/produtos/" . $novo_codigo_base;
        if (!is_dir($pasta_produto)) {
            mkdir($pasta_produto, 0777, true);
        }

        // Verificar se um ficheiro de imagem foi enviado
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem_tmp = $_FILES['imagem']['tmp_name'];
            $imagem_nome = basename($_FILES['imagem']['name']);
            $destino = $pasta_produto . "/" . $imagem_nome; // Guardar na pasta do produto

            // Mover o ficheiro carregado para a pasta do produto
            if (move_uploaded_file($imagem_tmp, $destino)) {
                $imagem = $destino;
            } else {
                echo "<p>Erro ao carregar a imagem. Por favor, tente novamente.</p>";
                exit;
            }
        } else {
            $imagem = null;
        }

        // Inserir os dados na base de dados
        $query = "INSERT INTO produtos (
                    nome, id_marca, id_fornecedor, id_categoria, 
                    preco, descricao, imagem, codigo_base
                  ) VALUES (
                    '$nome', $id_marca, $id_fornecedor, $id_categoria,
                    $preco, " . ($descricao !== null ? "'$descricao'" : "NULL") . ",
                    " . ($imagem ? "'$imagem'" : "NULL") . ",
                    '$novo_codigo_base'
                  )";

        if (mysqli_query($conn, $query)) {
            header('Location: adicionar_produto.php?msg=sucesso');
            exit;
        } else {
            echo "<p>Erro ao adicionar o produto: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>O formulário não foi submetido corretamente.</p>";
    }
    ?>
    <!-- Botão para voltar ao início -->
    <a href="index2.php">Voltar ao Início</a>
</div>
<?php
// Incluir o rodapé
?>
</body>
</html>
<?php
// Fechar ligação à base de dados
mysqli_close($conn);
?>