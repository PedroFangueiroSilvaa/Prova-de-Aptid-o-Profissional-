<?php
session_start();
include 'conexao.php';
include 'config_phpmailer.php'; // Incluir configuração de email
require_once 'vendor/autoload.php';

// Definir a chave secreta da Stripe
\Stripe\Stripe::setApiKey('sk_test_51QsoQIGP21KyXclQSrOlFKunYO2X9U0weNvvrFvXRjcH0P9KCCp5Bbz98CmGzsoIDpGcisoZC5iUwujs94lm5oFO00gvpLahcr');

// Função para registrar logs em arquivo
function log_payment($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'payment.log');
}

// Função para gerar o conteúdo HTML do email de confirmação
function gerarEmailConfirmacao($encomendaID, $conn) {
    // Buscar detalhes da encomenda
    $sqlDetalhes = "SELECT e.id_encomenda, e.data_encomenda, e.status, e.total, u.nome AS utilizador, u.email
                    FROM encomendas e
                    INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador
                    WHERE e.id_encomenda = '$encomendaID'";
    $resultDetalhes = mysqli_query($conn, $sqlDetalhes);
    
    if (!$resultDetalhes || mysqli_num_rows($resultDetalhes) == 0) {
        return false;
    }
    
    $encomenda = mysqli_fetch_assoc($resultDetalhes);
    
    // Buscar itens da encomenda
    $sqlItens = "SELECT i.id_item, p.nome AS produto, p.imagem, i.sku, 
                        i.quantidade, i.preco_unitario
                 FROM itens_encomenda i
                 LEFT JOIN produtos p ON p.codigo_base = SUBSTRING_INDEX(i.sku, '-', 3)
                 WHERE i.id_encomenda = '$encomendaID'";
    $resultItens = mysqli_query($conn, $sqlItens);
    
    if (!$resultItens) {
        return false;
    }
    
    // Gerar HTML do email
    $html = '
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmação de Compra - Boxing for Life</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background-color: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .order-info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .item { border-bottom: 1px solid #eee; padding: 15px 0; }
            .item:last-child { border-bottom: none; }
            .total { font-size: 18px; font-weight: bold; color: #007bff; text-align: right; margin-top: 20px; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            .btn { display: inline-block; background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🥊 Boxing for Life</h1>
                <h2>Compra Confirmada!</h2>
            </div>
            
            <div class="content">
                <p>Olá <strong>' . htmlspecialchars($encomenda['utilizador']) . '</strong>,</p>
                
                <p>A sua compra foi processada com <strong>sucesso</strong>! Obrigado por escolher a Boxing for Life.</p>
                
                <div class="order-info">
                    <h3>Detalhes da Encomenda:</h3>
                    <p><strong>Número da Encomenda:</strong> #' . htmlspecialchars($encomenda['id_encomenda']) . '</p>
                    <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($encomenda['data_encomenda'])) . '</p>
                    <p><strong>Status:</strong> <span style="color: #28a745;">' . htmlspecialchars($encomenda['status']) . '</span></p>
                </div>
                
                <h3>Itens Comprados:</h3>';
    
    $totalCalculado = 0;
    while ($item = mysqli_fetch_assoc($resultItens)) {
        // Extrair informações do SKU
        $skuParts = explode('-', $item['sku']);
        $codigo_base = isset($skuParts[0], $skuParts[1], $skuParts[2]) ? ($skuParts[0] . '-' . $skuParts[1] . '-' . $skuParts[2]) : '';
        
        // Buscar informações de cor e tamanho
        $cor_nome = 'Não especificado';
        $tamanho_nome = 'Não especificado';
        
        if (isset($skuParts[4]) && $skuParts[4] !== '') {
            $query_cor = mysqli_query($conn, "SELECT descricao FROM cores WHERE codigo_cor = '".mysqli_real_escape_string($conn, $skuParts[4])."' LIMIT 1");
            if ($query_cor && mysqli_num_rows($query_cor) > 0) {
                $cor_row = mysqli_fetch_assoc($query_cor);
                $cor_nome = $cor_row['descricao'];
            }
        }
        
        if (isset($skuParts[3]) && $skuParts[3] !== '') {
            $query_tamanho = mysqli_query($conn, "SELECT descricao FROM tamanhos WHERE codigo_tamanho = '".mysqli_real_escape_string($conn, $skuParts[3])."' LIMIT 1");
            if ($query_tamanho && mysqli_num_rows($query_tamanho) > 0) {
                $tamanho_row = mysqli_fetch_assoc($query_tamanho);
                $tamanho_nome = $tamanho_row['descricao'];
            }
        }
        
        $subtotal = $item['quantidade'] * $item['preco_unitario'];
        $totalCalculado += $subtotal;
        
        $html .= '
                <div class="item">
                    <p><strong>' . htmlspecialchars($item['produto']) . '</strong></p>
                    <p>Cor: ' . htmlspecialchars($cor_nome) . ' | Tamanho: ' . htmlspecialchars($tamanho_nome) . '</p>
                    <p>Quantidade: ' . $item['quantidade'] . ' x ' . number_format($item['preco_unitario'], 2, ',', '.') . ' € = ' . number_format($subtotal, 2, ',', '.') . ' €</p>
                </div>';
    }
    
    // Usar o total da base de dados se disponível, senão usar o calculado
    $totalFinal = isset($encomenda['total']) && $encomenda['total'] > 0 ? $encomenda['total'] : $totalCalculado;
    
    $html .= '
                <div class="total">
                    <p>Total: ' . number_format($totalFinal, 2, ',', '.') . ' €</p>
                </div>
                
                <p>A sua encomenda será processada e enviada nos próximos dias úteis. Receberá uma notificação quando o produto for enviado.</p>
                
                <div style="text-align: center;">
                    <a href="http://localhost/PAP/conta_utilizador.php" class="btn">Ver Minhas Encomendas</a>
                </div>
                
                <div class="footer">
                    <p>Obrigado por escolher a Boxing for Life!</p>
                    <p>Em caso de dúvidas, contacte-nos através do nosso site.</p>
                    <p><em>Este é um email automático, não responda a esta mensagem.</em></p>
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    return [
        'html' => $html,
        'email' => $encomenda['email'],
        'nome' => $encomenda['utilizador']
    ];
}

// Obter o session_id e o id da encomenda
$session_id = $_GET['session_id'] ?? null;
$encomendaID = $_GET['encomenda'] ?? null;

log_payment("Acessando página de sucesso com session_id: $session_id, encomenda: $encomendaID");

if (!$session_id || !$encomendaID) {
    echo "Erro ao processar o pagamento. Parâmetros incompletos.";
    exit();
}

// Verificar a sessão de pagamento no Stripe
try {
    log_payment("Tentando recuperar sessão do Stripe: $session_id");
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    log_payment("Sessão recuperada com sucesso. Status de pagamento: " . $session->payment_status);

    if ($session->payment_status === 'paid') {
        log_payment("Pagamento confirmado como pago para encomenda $encomendaID");
        
        // Obter o local_envio do utilizador
        $idUtilizador = $_SESSION['id_utilizador'] ?? null;
        
        if (!$idUtilizador) {
            log_payment("ERRO: ID do utilizador não encontrado na sessão");
            echo "Erro: Sessão do utilizador expirada. Por favor faça login novamente.";
            echo "<p><a href='login.php'>Ir para página de login</a></p>";
            exit();
        }
        
        $sqlUtilizador = "SELECT local_envio FROM utilizadores WHERE id_utilizador = '$idUtilizador'";
        $resultUtilizador = mysqli_query($conn, $sqlUtilizador);
        
        if ($resultUtilizador && $rowUtilizador = mysqli_fetch_assoc($resultUtilizador)) {
            $localEnvio = mysqli_real_escape_string($conn, $rowUtilizador['local_envio']);
            log_payment("Local de envio encontrado: $localEnvio");
            
            // Atualizar o estado da encomenda para "paga" e adicionar o local_envio
            $sqlUpdate = "UPDATE encomendas SET status = 'pago', local_envio = '$localEnvio' WHERE id_encomenda = '$encomendaID'";
        } else {
            log_payment("Local de envio não encontrado para utilizador $idUtilizador");
            // Se não conseguir obter o local_envio, apenas atualiza o status
            $sqlUpdate = "UPDATE encomendas SET status = 'pago' WHERE id_encomenda = '$encomendaID'";
        }
        
        if (!mysqli_query($conn, $sqlUpdate)) {
            echo "Erro ao atualizar estado da encomenda: " . mysqli_error($conn);
            exit();
        }

        log_payment("Estado da encomenda atualizado com sucesso para encomenda $encomendaID");

        // Limpar o carrinho do utilizador
        $sqlLimparCarrinho = "DELETE FROM carrinho WHERE id_utilizador = '$idUtilizador'";
        mysqli_query($conn, $sqlLimparCarrinho);
        
        log_payment("Carrinho limpo para utilizador $idUtilizador");

        // Enviar email de confirmação
        log_payment("Tentando enviar email de confirmação para encomenda $encomendaID");
        $emailData = gerarEmailConfirmacao($encomendaID, $conn);
        
        if ($emailData) {
            $assunto = "Confirmação de Compra #$encomendaID - Boxing for Life";
            $resultadoEmail = enviarEmail($emailData['email'], $assunto, $emailData['html']);
            
            if ($resultadoEmail === true) {
                log_payment("Email de confirmação enviado com sucesso para: " . $emailData['email']);
            } else {
                log_payment("Erro ao enviar email: " . $resultadoEmail);
            }
        } else {
            log_payment("Erro ao gerar conteúdo do email para encomenda $encomendaID");
        }

        // Adicionar log antes do redirecionamento
        log_payment("Redirecionando para confirmação de compra. Encomenda ID: " . $encomendaID);
        
        // Redirecionar para a página de confirmação da compra
        header("Location: confirmar_compra.php?encomenda=" . $encomendaID);
        exit();
    } else {
        echo "Pagamento não foi concluído.";
        exit();
    }
} catch (Exception $e) {
    echo "Erro ao verificar pagamento: " . $e->getMessage();
    exit();
}

// Fechar conexão
mysqli_close($conn);
?>