<?php
// Obter informações do usuário
$id_utilizador = $_SESSION['id_utilizador'];
$sql = "SELECT nome, email, local_envio FROM utilizadores WHERE id_utilizador = $id_utilizador";
$resultado = mysqli_query($conn, $sql);
$utilizador = mysqli_fetch_assoc($resultado);

// Inserir a encomenda
$sql = "INSERT INTO encomendas (id_utilizador, data_encomenda, estado, local_envio) 
        VALUES ($id_utilizador, NOW(), 'Pendente', '" . mysqli_real_escape_string($conn, $utilizador['local_envio']) . "')";

// ... existing code ... 