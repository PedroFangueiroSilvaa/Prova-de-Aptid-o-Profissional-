<?php
/**
 * Script para verificar e corrigir permissões de diretórios
 * Execute este script para diagnosticar problemas de permissões
 */

echo "<h2>Diagnóstico de Permissões - Boxing for Life</h2>";

// Diretórios importantes
$diretorios = [
    'imagens/',
    'imagens/blog/',
    'imagens/produtos/',
];

echo "<h3>Status dos Diretórios:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Diretório</th><th>Existe</th><th>Legível</th><th>Gravável</th><th>Permissões</th><th>Ação</th></tr>";

foreach ($diretorios as $dir) {
    $existe = is_dir($dir);
    $legivel = is_readable($dir);
    $gravavel = is_writable($dir);
    $permissoes = $existe ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
    
    echo "<tr>";
    echo "<td>$dir</td>";
    echo "<td>" . ($existe ? "✅ Sim" : "❌ Não") . "</td>";
    echo "<td>" . ($legivel ? "✅ Sim" : "❌ Não") . "</td>";
    echo "<td>" . ($gravavel ? "✅ Sim" : "❌ Não") . "</td>";
    echo "<td>$permissoes</td>";
    
    // Tentar criar/corrigir
    if (!$existe) {
        if (mkdir($dir, 0755, true)) {
            echo "<td>✅ Diretório criado</td>";
        } else {
            echo "<td>❌ Erro ao criar</td>";
        }
    } elseif (!$gravavel) {
        if (chmod($dir, 0755)) {
            echo "<td>✅ Permissões corrigidas</td>";
        } else {
            echo "<td>❌ Erro ao corrigir permissões</td>";
        }
    } else {
        echo "<td>✅ OK</td>";
    }
    echo "</tr>";
}

echo "</table>";

// Informações do sistema
echo "<h3>Informações do Sistema:</h3>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Sistema Operacional:</strong> " . PHP_OS . "</li>";
echo "<li><strong>Usuário do processo PHP:</strong> " . get_current_user() . "</li>";
echo "<li><strong>Diretório atual:</strong> " . getcwd() . "</li>";
echo "<li><strong>Upload max filesize:</strong> " . ini_get('upload_max_filesize') . "</li>";
echo "<li><strong>Post max size:</strong> " . ini_get('post_max_size') . "</li>";
echo "<li><strong>Diretório temporário:</strong> " . sys_get_temp_dir() . "</li>";
echo "</ul>";

// Teste de criação de arquivo
echo "<h3>Teste de Criação de Arquivo:</h3>";
$teste_arquivo = 'imagens/blog/teste_' . time() . '.txt';
if (file_put_contents($teste_arquivo, 'Teste de escrita')) {
    echo "✅ Conseguiu criar arquivo de teste: $teste_arquivo<br>";
    // Limpar arquivo de teste
    unlink($teste_arquivo);
    echo "✅ Arquivo de teste removido com sucesso<br>";
} else {
    echo "❌ Falha ao criar arquivo de teste<br>";
}

echo "<hr>";
echo "<h3>Comandos para executar no terminal (se necessário):</h3>";
echo "<pre>";
echo "# Navegar para o diretório do projeto:\n";
echo "cd /Applications/XAMPP/xamppfiles/htdocs/PAP\n\n";
echo "# Criar diretórios necessários:\n";
echo "mkdir -p imagens/blog\n";
echo "mkdir -p imagens/produtos\n\n";
echo "# Definir permissões corretas:\n";
echo "chmod 755 imagens/\n";
echo "chmod 755 imagens/blog/\n";
echo "chmod 755 imagens/produtos/\n\n";
echo "# Verificar propriedade (pode ser necessário ajustar):\n";
echo "ls -la imagens/\n";
echo "</pre>";
?>
