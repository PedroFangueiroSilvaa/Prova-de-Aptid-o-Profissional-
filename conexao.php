<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Estabelece a conexão com o banco de dados usando MySQLi
$conn = mysqli_connect('localhost', 'root', '', 'boxingforlife');

// Verifica se a conexão foi bem-sucedida
if (!$conn) {
    echo "<br>Erro: Não foi possível estabelecer ligação com o MySQL";
    exit;
}

// Define o charset para UTF-8
mysqli_set_charset($conn, "utf8");
?>
