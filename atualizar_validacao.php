<?php
// Este script vai procurar por todas as páginas administrativas da pasta PAP
// que não incluem diretamente validar_admin.php e adicionará o include

// Lista de arquivos a serem verificados (você pode modificar manualmente esta lista)
$arquivos = [
    'excluir_comentario.php',
    'editar_blog.php',
    'comentarios_admin.php',
    'atualizar_marca.php',
    'historico_encomendas.php',
    'relatorio_reviews.php',
    'relatorio_vendas.php',
    // Adicione mais arquivos conforme necessário
];

foreach ($arquivos as $arquivo) {
    $conteudo = file_get_contents($arquivo);
    
    // Verifica se já tem include do validar_admin.php
    if (strpos($conteudo, "include 'validar_admin.php'") === false) {
        // Verifica se já tem verificação de nível de acesso
        if (strpos($conteudo, 'nivel_acesso') !== false) {
            // Substitui a verificação atual pelo include do validar_admin.php
            $pattern = '/\<\?php.*?if\s*\(\s*\!isset\s*\(\s*\$_SESSION\s*\[\s*["\']nivel_acesso["\']\s*\]\s*\).*?\s*exit\(\);\s*\)/s';
            $replace = "<?php\ninclude 'validar_admin.php';\n";
            $novo_conteudo = preg_replace($pattern, $replace, $conteudo);
            
            if ($novo_conteudo != $conteudo) {
                file_put_contents($arquivo, $novo_conteudo);
                echo "Arquivo $arquivo atualizado com sucesso!<br>";
            } else {
                echo "Não foi possível atualizar $arquivo.<br>";
            }
        } else {
            // Adiciona o include do validar_admin.php no início do arquivo
            $novo_conteudo = "<?php\ninclude 'validar_admin.php';\n?>\n" . $conteudo;
            file_put_contents($arquivo, $novo_conteudo);
            echo "Arquivo $arquivo atualizado com sucesso!<br>";
        }
    } else {
        echo "Arquivo $arquivo já possui include do validar_admin.php.<br>";
    }
}

echo "Processo concluído!";
?>
