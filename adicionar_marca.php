<!DOCTYPE html>
<html lang="pt"> <!-- This line defines the document as an HTML file and sets the language to Portuguese -->
<head>
    <meta charset="UTF-8"> <!-- Specifies the character encoding for the document to support special characters -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensures the page is responsive on different devices -->
    <title>Adicionar Marca</title> <!-- Sets the title of the page that appears on the browser tab -->
    <style>
        /* Estilo global */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9e7d6; /* Cor de fundo laranja suave */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header, footer {
            background-color: #ff914d; /* Cor laranja vibrante */
            color: white;
            text-align: center;
            padding: 10px 0;
            font-weight: bold;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Estilo do formulário */
        form {
            background: #fffaf3; /* Fundo claro com tom laranja */
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 25px;
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            border: 2px solid #ff914d; /* Borda laranja */
        }

        h2 {
            text-align: center;
            color: #ff914d; /* Título em laranja */
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #b36b00; /* Cor escura de laranja para os rótulos */
        }

        input, textarea, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ff914d; /* Borda laranja */
            border-radius: 5px;
            font-size: 16px;
        }

        input:focus, textarea:focus {
            border-color: #ff914d;
            box-shadow: 0 0 5px rgba(255, 145, 77, 0.5); /* Efeito ao focar */
            outline: none;
        }

        button {
            background-color: #ff914d; /* Botão laranja */
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border-radius: 5px;
        }

        button:hover {
            background-color: #e67e22; /* Efeito de hover */
        }

        .file-input {
            padding: 10px;
            border: 2px dashed #ff914d; /* Borda pontilhada laranja */
            text-align: center;
            color: #b36b00; /* Cor escura de laranja */
            cursor: pointer;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }
    </style>
</head>
<body>
<!-- This section includes the header of the page -->
<?php include 'cabecalho2.php'; ?> 
<!-- The line above inserts the content of another file called 'cabecalho2.php'. This is used to reuse the header across multiple pages. -->

<!-- Main content of the page -->
<main>
    <!-- This is a form where users can input information to add a new brand -->
    <form action="processar_adicionar_marca.php" method="post" enctype="multipart/form-data">
        <!-- The 'action' specifies the file that will handle the form submission ('processar_adicionar_marca.php').
             The 'method' is set to 'post', which means the data will be sent securely.
             The 'enctype' allows the form to handle file uploads. -->

        <h2>Adicionar Marca</h2> <!-- This is the title of the form, displayed prominently -->

        <!-- Input field for the brand name -->
        <label for="nome">Nome da Marca:</label> <!-- Label for the brand name input field -->
        <input type="text" id="nome" name="nome" required> 
        <!-- A text box where the user can type the brand name. 
             The 'required' attribute ensures the user cannot submit the form without filling this field. -->

        <!-- File input for uploading an image -->
        <label for="imagem">Imagem da Marca:</label> <!-- Label for the image upload field -->
        <div class="file-input">
            <input type="file" id="imagem" name="imagem" accept="image/*" required>
            <!-- A file upload field where the user can select an image file for the brand.
                 The 'accept' attribute ensures only image files can be uploaded.
                 The 'required' attribute ensures the user must upload an image. -->
        </div>

        <!-- Submit button -->
        <button type="submit">Adicionar Marca</button>
        <!-- A button that submits the form. When clicked, the data entered in the form is sent to 'processar_adicionar_marca.php'. -->
    </form>
</main>
</body>
</html>