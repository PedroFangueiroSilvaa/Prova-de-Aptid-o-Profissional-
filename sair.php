<?php
include 'validar.php'; 
// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: index.php");
exit();
?>