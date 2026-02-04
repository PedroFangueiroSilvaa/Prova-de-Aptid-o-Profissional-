<?php
session_start();
include __DIR__ . '/conexao.php'; // Inclui o arquivo de conexão com o banco de dados
include __DIR__ . '/config_phpmailer.php'; // Inclui o arquivo de configuração do PHPMailer

// Receber os dados do formulário
$nome = $_POST['nome'] ?? null;
$email = $_POST['email'] ?? null;
$palavra_passe = $_POST['palavra_passe'] ?? null;
$local_envio = $_POST['local_envio'] ?? null;

// Validar entrada
if (empty($nome) || empty($email) || empty($palavra_passe) || empty($local_envio)) {
    header("Location: index.php?erro=campos_vazios");
    exit();
}

// Verificar se o email já está registrado
$sql = "SELECT * FROM utilizadores WHERE email = '$email'";
$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) > 0) {
    header("Location: index.php?erro=email_existente");
    exit();
}

// Gerar um token de confirmação
$token = bin2hex(random_bytes(32));

// Armazenar os dados temporariamente na sessão
$_SESSION['dados_temp'] = [
    'nome' => $nome,
    'email' => $email,
    'palavra_passe' => $palavra_passe, // Sem hash da senha
    'local_envio' => $local_envio,
    'token' => $token // Token gerado
];

// Garantir encoding UTF-8 no nome
$nome_utf8 = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
$assunto = "Confirme sua conta";
$linkConfirmacao = "http://localhost/PAP/confirmar_conta.php?token=$token";
$bannerUrl = "http://localhost/PAP/imagens/banner_boxing.png"; // URL pública para o banner
$corpo = "<div style='text-align:center;'>"
       . "<img src='$bannerUrl' alt='Boxing For Life' style='max-width:100%;height:auto;'><br><br>"
       . "<div style='font-family:Arial,sans-serif;font-size:16px;color:#222;text-align:left;max-width:600px;margin:0 auto;'>"
       . "Olá $nome_utf8,<br><br>Clique no link abaixo para confirmar sua conta:<br>"
       . "<a href='$linkConfirmacao' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#e65100;color:#fff;text-decoration:none;border-radius:5px;'>Confirmar Conta</a>"
       . "</div></div>";

$resultadoEmail = enviarEmail($email, $assunto, $corpo);

if ($resultadoEmail === true) {
    header("Location: index.php?sucesso=email_enviado");
} else {
    echo "Erro ao enviar email: " . $resultadoEmail; // Exibe o erro detalhado
    exit();
}

mysqli_close($conn);
?>