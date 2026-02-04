<?php
// Incluir o cabeçalho
include 'cabecalho2.php';
// Estabelecer ligação à base de dados
include 'conexao.php';

// Verificar se foi passado um código base do produto
if (isset($_GET['id_produto'])) {
    $codigo_base = $_GET['id_produto'];
    // Consultar as informações do produto
    $queryProduto = "
        SELECT 
            produtos.codigo_base,
            produtos.nome,
            produtos.id_marca,
            produtos.id_fornecedor,
            produtos.id_categoria,
            produtos.preco,
            produtos.descricao,
            produtos.imagem
        FROM produtos
        WHERE produtos.codigo_base = '$codigo_base'
    ";
    $resultProduto = mysqli_query($conn, $queryProduto);
    if (mysqli_num_rows($resultProduto) == 1) {
        $produto = mysqli_fetch_assoc($resultProduto);
    } else {
        echo "Produto não encontrado.";
        exit;
    }
}

// Atualizar as informações do produto após o envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $marca_id = $_POST['marca'];
    $fornecedor_id = $_POST['fornecedor'];
    $categoria_id = $_POST['categoria'];
    $preco = $_POST['preco'];
    $descricao = isset($_POST['descricao']) ? mysqli_real_escape_string($conn, $_POST['descricao']) : '';
    $imagem = $_FILES['imagem']['name'];

    // Verificar se o campo de imagem foi preenchido e fazer o upload
    if ($imagem) {
        $target_dir = "imagens/produtos/";
        
        // Criar o diretório se não existir
        if (!file_exists($target_dir)) {
            if (!@mkdir($target_dir, 0755, true)) {
                echo "<script>alert('Erro: Não foi possível criar o diretório de imagens. Verifique as permissões.');</script>";
                exit;
            }
        }

        // Verificar se o diretório tem permissões de escrita
        if (!is_writable($target_dir)) {
            echo "<script>alert('Erro: O diretório " . $target_dir . " não tem permissão de escrita.');</script>";
            exit;
        }

        $target_file = $target_dir . basename($_FILES["imagem"]["name"]);
        
        // Verificar se o upload foi bem sucedido
        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
            // Atualizar os dados do produto na base de dados com a nova imagem
            $queryUpdate = "
                UPDATE produtos 
                SET 
                    nome = '$nome', 
                    id_marca = $marca_id, 
                    id_fornecedor = $fornecedor_id,
                    id_categoria = $categoria_id, 
                    preco = '$preco', 
                    descricao = '$descricao', 
                    imagem = '$target_file'
                WHERE codigo_base = '$codigo_base'
            ";
        } else {
            echo "<script>alert('Erro ao fazer upload da imagem. Verifique as permissões do diretório.');</script>";
            exit;
        }
    } else {
        // Se não foi carregada uma nova imagem, atualizar os dados sem alterar a imagem
        $queryUpdate = "
            UPDATE produtos 
            SET 
                nome = '$nome', 
                id_marca = $marca_id, 
                id_fornecedor = $fornecedor_id,
                id_categoria = $categoria_id, 
                preco = '$preco', 
                descricao = '$descricao'
            WHERE codigo_base = '$codigo_base'
        ";
    }

    if (mysqli_query($conn, $queryUpdate)) {
        echo "<script>
            alert('Produto atualizado com sucesso.');
            window.location.href = 'listar_produtos.php';
        </script>";
    } else {
        echo "<script>alert('Erro ao atualizar produto: " . mysqli_error($conn) . "');</script>";
    }
    exit;
}
?>
<main>    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container-produto {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 0;
            overflow: hidden;
        }
        .header-produto {
            background: linear-gradient(135deg, #ff914d 0%, #f76e11 100%);
            color: white;
            padding: 25px 30px;
            position: relative;
            margin-bottom: 30px;
        }
        .header-produto h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
        }
        .card-form {
            padding: 0 30px 30px;
        }
        .form-section {
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 25px;
        }
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .form-section h3 {
            color: #ff914d;
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: #ff914d;
            box-shadow: 0 0 0 3px rgba(255, 145, 77, 0.2);
            outline: none;
        }
        .btn-action {
            display: inline-block;
            padding: 12px 25px;
            background: #ff914d;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-action:hover {
            background: #e65100;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(230, 81, 0, 0.2);
        }
        .btn-secondary {
            background: #6c757d;
            margin-right: 10px;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .current-image {
            margin: 20px 0;
            text-align: center;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 10px;
        }
        .current-image img {
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .current-image p {
            margin-bottom: 10px;
            font-weight: 500;
            color: #555;
        }
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-upload-label {
            display: block;
            padding: 12px;
            background: #f0f0f0;
            border: 1px dashed #ccc;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload-label:hover {
            background: #e9e9e9;
            border-color: #aaa;
        }
        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .section-divider {
            height: 1px;
            background: #eee;
            margin: 25px 0;
        }
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
    </style>
    
    <div class="container-produto">
        <div class="header-produto">
            <h2><i class="fas fa-edit me-2"></i> Editar Produto</h2>
        </div>
        
        <div class="card-form">
            <form action="editar_produto.php?id_produto=<?= $produto['codigo_base'] ?>" method="POST" enctype="multipart/form-data">
                
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Informações Básicas</h3>
                    
                    <div class="form-group">
                        <label for="nome">Nome do Produto:</label>
                        <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($produto['nome']) ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="marca">Marca:</label>
                                <select id="marca" name="marca" class="form-control" required>
                                    <?php
                                    // Obter lista de marcas
                                    $queryMarcas = "SELECT id_marca, nome FROM marcas";
                                    $resultMarcas = mysqli_query($conn, $queryMarcas);
                                    while ($marca = mysqli_fetch_assoc($resultMarcas)) {
                                        echo "<option value='{$marca['id_marca']}'" . ($marca['id_marca'] == $produto['id_marca'] ? ' selected' : '') . ">{$marca['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fornecedor">Fornecedor:</label>
                                <select id="fornecedor" name="fornecedor" class="form-control" required>
                                    <?php
                                    // Obter lista de fornecedores
                                    $queryFornecedores = "SELECT id_fornecedor, nome FROM fornecedores";
                                    $resultFornecedores = mysqli_query($conn, $queryFornecedores);
                                    while ($fornecedor = mysqli_fetch_assoc($resultFornecedores)) {
                                        echo "<option value='{$fornecedor['id_fornecedor']}'" . ($fornecedor['id_fornecedor'] == $produto['id_fornecedor'] ? ' selected' : '') . ">{$fornecedor['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="categoria">Categoria:</label>
                                <select id="categoria" name="categoria" class="form-control" required>
                                    <?php
                                    // Obter lista de categorias
                                    $queryCategorias = "SELECT id_categoria, nome FROM categorias";
                                    $resultCategorias = mysqli_query($conn, $queryCategorias);
                                    while ($categoria = mysqli_fetch_assoc($resultCategorias)) {
                                        echo "<option value='{$categoria['id_categoria']}'" . ($categoria['id_categoria'] == $produto['id_categoria'] ? ' selected' : '') . ">{$categoria['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-euro-sign"></i> Preço e Detalhes</h3>
                    
                    <div class="form-group">
                        <label for="preco">Preço (€):</label>
                        <div class="input-group">
                            <input type="number" id="preco" name="preco" class="form-control" step="0.01" value="<?= $produto['preco'] ?>" required>
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao">Descrição do Produto:</label>
                        <textarea id="descricao" name="descricao" class="form-control"><?= htmlspecialchars($produto['descricao']) ?></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-image"></i> Imagem do Produto</h3>
                    
                    <?php if (!empty($produto['imagem'])): ?>
                        <div class="current-image">
                            <p>Imagem atual do produto:</p>
                            <img src="<?= $produto['imagem'] ?>" alt="Imagem do produto">
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Alterar imagem:</label>
                        <div class="file-upload">
                            <label for="imagem" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt me-2"></i> Clique aqui para selecionar uma nova imagem
                            </label>
                            <input type="file" id="imagem" name="imagem">
                        </div>
                        <small class="text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5 MB.</small>
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <div class="actions">
                    <a href="listar_produtos.php" class="btn-action btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-action">
                        <i class="fas fa-save me-2"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Adiciona os scripts para melhorar a UI -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostra o nome do arquivo quando selecionado
    document.getElementById('imagem').addEventListener('change', function() {
        const fileName = this.files[0]?.name;
        const label = this.previousElementSibling;
        
        if (fileName) {
            label.innerHTML = `<i class="fas fa-file-image me-2"></i> ${fileName}`;
            label.style.backgroundColor = '#e8f4fc';
            label.style.borderColor = '#7cb9e8';
        } else {
            label.innerHTML = `<i class="fas fa-cloud-upload-alt me-2"></i> Clique aqui para selecionar uma nova imagem`;
            label.style.backgroundColor = '#f0f0f0';
            label.style.borderColor = '#ccc';
        }
    });
});
</script>

<?php
// Fechar a conexão com a base de dados
mysqli_close($conn);
?>
