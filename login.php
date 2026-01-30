<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff3e0; /* Cor de fundo suave laranja */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #ffffff; /* Fundo branco para o container */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 300px; /* Largura fixa para o container */
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #e65100; /* Cor do título laranja escuro */
            text-align: center; /* Centraliza o título */
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #fb8c00; /* Borda laranja */
            border-radius: 5px;
            transition: border-color 0.3s; /* Transição suave na borda */
        }

        .login-container input:focus {
            border-color: #e65100; /* Cor da borda ao focar (laranja escuro) */
            outline: none; /* Remove o contorno padrão */
        }

        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #fb8c00; /* Cor do botão laranja */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s; /* Transição suave na cor do botão */
        }

        .login-container button:hover {
            background-color: #e65100; /* Cor do botão ao passar o mouse (laranja escuro) */
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <form action="processar_login.php" method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="palavra_pass" name="palavra_pass" placeholder="Senha" required>
        <button type="submit">Entrar</button>

    </form>
</div>

</body>
</html>