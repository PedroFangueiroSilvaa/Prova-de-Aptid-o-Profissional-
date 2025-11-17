<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Carrega o autoload do Composer

function enviarEmail($destinatario, $assunto, $corpo) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'pedrofangueirosilva19@gmail.com'; // Coloque seu email correto
$mail->Password = 'bmfl trcp mtpd uvmm'; // Senha de aplicativo gerada
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Mude para TLS
$mail->Port = 587; // Use a porta 587 para TLS


        // Remetente e destinatário
        $mail->setFrom('pedrofangueirosilva19@gmail.com', 'José Pedro');
        $mail->addAddress($destinatario);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpo;
        $mail->AltBody = strip_tags($corpo); // Versão em texto simples

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Erro ao enviar email: " . $e->getMessage(); // Exibe o erro completo
    }
}
?>