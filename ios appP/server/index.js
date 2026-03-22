
require('dotenv').config();

// LINHAS 20-26: Importação de dependências - cada const cria uma variável que referencia uma biblioteca
// require() é a função que importa módulos/bibliotecas em Node.js
const express = require('express');      // express: framework web que cria o servidor HTTP e gere rotas
const cors = require('cors');           // cors: middleware que permite requisições cross-origin (iOS para servidor)
const mysql = require('mysql2');        // mysql2: driver que permite conectar e fazer queries à base de dados MySQL
const path = require('path');           // path: utilitários para trabalhar com caminhos de ficheiros e diretórios
const nodemailer = require('nodemailer'); // nodemailer: biblioteca para enviar emails através de SMTP
const multer = require('multer');       // multer: middleware para processar uploads de ficheiros (imagens)
const fs = require('fs');               // fs: file system - permite ler, escrever, criar, eliminar ficheiros

const app = express();


const transporter = nodemailer.createTransport({
  host: 'smtp.gmail.com',        // host: endereço do servidor SMTP do Gmail
  port: 587,                     // port: porta 587 é para SMTP com encriptação STARTTLS
  secure: false,                 // secure: false porque porta 587 usa STARTTLS (não SSL direto)
  auth: {                        // auth: objeto com credenciais de autenticação
    user: process.env.EMAIL_USER, // user: email de quem envia (vem do ficheiro .env)
    pass: process.env.EMAIL_PASS  // pass: palavra-passe de aplicação do Gmail (vem do .env)
  },
  tls: {                         // tls: configurações de segurança TLS
    rejectUnauthorized: false    // rejectUnauthorized: false permite certificados auto-assinados
  }
});


transporter.verify(function (error, success) {
  // if (error) - se a verificação falhou (error contém detalhes do erro)
  if (error) {
    // console.log() - função que escreve mensagens no terminal/consola
    console.log('❌ Erro na configuração do email:', error);
  } else {
    // else - se não houve erro, a configuração está correta
    console.log('✅ Servidor de email pronto para enviar mensagens');
  }
});


async function sendWelcomeEmail(nome, email) {

  const mailOptions = {
    from: process.env.EMAIL_USER || 'seuemail@gmail.com', // from: endereço do remetente (|| significa "ou", fallback)
    to: email,                                            // to: endereço do destinatário (parâmetro da função)
    subject: 'Bem-vindo ao Boxing for Life! 🥊',         // subject: assunto do email
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <!-- Comentários HTML: Cabeçalho do email -->
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
          <h1 style="color: #333; margin: 0;">Boxing for Life</h1>
        </div>
        
        <!-- Corpo principal do email -->
        <div style="padding: 30px; background-color: white;">
          <h2 style="color: #333;">Olá ${nome}! 👋</h2>
          
          <p style="color: #666; line-height: 1.6;">
            Bem-vindo à nossa comunidade Boxing for Life! A tua conta foi criada com sucesso.
          </p>
          
          <p style="color: #666; line-height: 1.6;">
            Agora podes:
          </p>
          
          <!-- Lista de funcionalidades disponíveis -->
          <ul style="color: #666; line-height: 1.8;">
            <li>🛒 Explorar e comprar equipamentos de boxe</li>
            <li>📱 Gerir o teu carrinho e encomendas</li>
            <li>📖 Ler artigos no nosso blog</li>
            <li>⭐ Deixar reviews dos produtos</li>
            <li>❤️ Adicionar produtos aos favoritos</li>
          </ul>
          
          <!-- ${email} - template literal que insere o valor da variável email -->
          <div style="text-align: center; margin: 30px 0;">
            <div style="background-color: #007bff; color: white; padding: 15px 30px; border-radius: 5px; display: inline-block;">
              <strong>Email:</strong> ${email}
            </div>
          </div>
          
          <p style="color: #666; line-height: 1.6;">
            Se precisares de ajuda, não hesites em contactar-nos.
          </p>
          
          <p style="color: #666;">
            Bons treinos! 🥊<br>
            <strong>Equipa Boxing for Life</strong>
          </p>
        </div>
        
        <!-- Rodapé do email -->
        <div style="background-color: #f8f9fa; padding: 15px; text-align: center; color: #999; font-size: 12px;">
          © 2025 Boxing for Life. Todos os direitos reservados.
        </div>
      </div>
    ` // Fim da template string HTML - note a crase ` que fecha a string
  }; // Fim do objeto mailOptions

  try {

    console.log(`📧 Tentando enviar email para: ${email}`);
    console.log(`📧 Email configurado: ${process.env.EMAIL_USER}`);


    const info = await transporter.sendMail(mailOptions);
    console.log(`✅ Email de boas-vindas enviado para: ${email}`, info.messageId);
    // return true - retorna true para indicar que o email foi enviado com sucesso
    return true;
  } catch (error) {

    console.error(`❌ Erro ao enviar email para ${email}:`, error);
    return false;
  }
}

async function sendOrderConfirmationEmail(userId, orderId, items, total, shippingAddress) {
  try {
    console.log('🚀 Iniciando envio de email para:', { userId, orderId, total });

    // Primeiro, buscar dados do utilizador
    const userQuery = 'SELECT nome, email FROM utilizadores WHERE id_utilizador = ?';
    db.query(userQuery, [userId], async (err, userResults) => {
      if (err || !userResults.length) {
        console.error('❌ Erro ao buscar dados do utilizador para email:', err);
        return;
      }

      const user = userResults[0];
      console.log('👤 Dados do utilizador encontrados:', { nome: user.nome, email: user.email });

      // Gerar HTML da lista de produtos
      const itemsHtml = items.map(item => `
        <tr style="border-bottom: 1px solid #eee;">
          <td style="padding: 15px 10px;">
            <strong>${item.nome || 'Produto'}</strong><br>
            <small style="color: #666;">SKU: ${item.sku}</small>
          </td>
          <td style="padding: 15px 10px; text-align: center;">${item.quantidade}</td>
          <td style="padding: 15px 10px; text-align: right;">€${item.preco_unitario.toFixed(2)}</td>
          <td style="padding: 15px 10px; text-align: right;">€${(item.quantidade * item.preco_unitario).toFixed(2)}</td>
        </tr>
      `).join('');

      const mailOptions = {
        from: process.env.EMAIL_USER || 'seuemail@gmail.com',
        to: user.email,
        subject: `Confirmação de Encomenda #${orderId} - Boxing for Life 🥊`,
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <!-- Cabeçalho -->
            <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
              <h1 style="color: #333; margin: 0;">Boxing for Life</h1>
              <p style="color: #666; margin: 5px 0 0 0;">Confirmação de Encomenda</p>
            </div>
            
            <!-- Corpo principal -->
            <div style="padding: 30px; background-color: white;">
              <h2 style="color: #333;">Olá ${user.nome}! 🎉</h2>
              
              <p style="color: #666; line-height: 1.6;">
                A sua encomenda foi confirmada e está a ser processada. Obrigado pela sua compra!
              </p>
              
              <!-- Detalhes da encomenda -->
              <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="color: #333; margin-top: 0;">Detalhes da Encomenda</h3>
                <p><strong>Número da Encomenda:</strong> #${orderId}</p>
                <p><strong>Data:</strong> ${new Date().toLocaleDateString('pt-PT')}</p>
                <p><strong>Status:</strong> Pago ✅</p>
                <p><strong>Endereço de Entrega:</strong> ${shippingAddress}</p>
              </div>
              
              <!-- Lista de produtos -->
              <h3 style="color: #333;">Produtos Comprados</h3>
              <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <thead>
                  <tr style="background-color: #f8f9fa;">
                    <th style="padding: 15px 10px; text-align: left;">Produto</th>
                    <th style="padding: 15px 10px; text-align: center;">Qtd</th>
                    <th style="padding: 15px 10px; text-align: right;">Preço Unit.</th>
                    <th style="padding: 15px 10px; text-align: right;">Total</th>
                  </tr>
                </thead>
                <tbody>
                  ${itemsHtml}
                </tbody>
                <tfoot>
                  <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td colspan="3" style="padding: 15px 10px; text-align: right;">Total da Encomenda:</td>
                    <td style="padding: 15px 10px; text-align: right; color: #007bff;">€${total.toFixed(2)}</td>
                  </tr>
                </tfoot>
              </table>
              
              <!-- Próximos passos -->
              <div style="background-color: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="color: #333; margin-top: 0;">Próximos Passos</h3>
                <ul style="color: #666; line-height: 1.8;">
                  <li>🔄 A sua encomenda será processada em 1-2 dias úteis</li>
                  <li>📦 Receberá uma notificação quando a encomenda for enviada</li>
                  <li>🚚 Tempo estimado de entrega: 3-5 dias úteis</li>
                  <li>📱 Pode acompanhar o status na aplicação</li>
                </ul>
              </div>
              
              <p style="color: #666; line-height: 1.6;">
                Se tiver alguma questão sobre a sua encomenda, não hesite em contactar-nos.
              </p>
              
              <p style="color: #666;">
                Obrigado pela sua preferência! 🥊<br>
                <strong>Equipa Boxing for Life</strong>
              </p>
            </div>
            
            <!-- Rodapé -->
            <div style="background-color: #f8f9fa; padding: 15px; text-align: center; color: #999; font-size: 12px;">
              © 2025 Boxing for Life. Todos os direitos reservados.<br>
              Encomenda #${orderId} • ${new Date().toLocaleDateString('pt-PT')}
            </div>
          </div>
        `
      };

      try {
        console.log('📤 Tentando enviar email para:', user.email);
        console.log('📧 Configurações do email:', {
          from: mailOptions.from,
          to: mailOptions.to,
          subject: mailOptions.subject
        });

        const info = await transporter.sendMail(mailOptions);
        console.log(`✅ Email de confirmação de encomenda enviado para: ${user.email}`, info.messageId);
      } catch (error) {
        console.error(`❌ Erro ao enviar email de confirmação de encomenda:`, error);
      }
    });
  } catch (error) {
    console.error('❌ Erro geral no envio de email de confirmação:', error);
  }
}

const stripeRoutes = require('./routes/stripe');


app.use(cors({
  origin: '*',                                    // origin: '*' significa aceitar requisições de qualquer domínio/IP
  methods: ['GET', 'POST', 'PUT', 'DELETE'],     // methods: array com métodos HTTP permitidos
  allowedHeaders: ['Content-Type', 'Authorization'] // allowedHeaders: cabeçalhos que a app iOS pode enviar
}));


app.use(express.json());


const storage = multer.diskStorage({
  // destination - função que define onde os ficheiros são guardados
  destination: function (req, file, cb) {
    // path.join() - junta caminhos de diretórios de forma segura
    // __dirname - variável que contém o caminho do diretório atual (onde está este ficheiro)
    // '..' - sobe um nível no diretório
    const uploadPath = path.join(__dirname, '..', '..', 'Applications', 'XAMPP', 'xamppfiles', 'htdocs', 'PAP', 'imagens', 'blog');

    // fs.existsSync() - verifica se o diretório existe (Sync = síncrono)
    if (!fs.existsSync(uploadPath)) {
      // fs.mkdirSync() - cria o diretório se não existir
      // recursive: true - cria diretórios pais se necessário
      fs.mkdirSync(uploadPath, { recursive: true });
    }

    // cb() - callback function que confirma onde guardar o ficheiro
    // cb(erro, caminho) - primeiro parâmetro é erro (null = sem erro), segundo é o caminho
    cb(null, uploadPath);
  },

  filename: function (req, file, cb) {
    // Date.now() - timestamp atual em milissegundos (garante nome único)
    // file.originalname - nome original do ficheiro enviado pelo utilizador
    const uniqueSuffix = Date.now() + '_' + file.originalname;
    // cb() - confirma o nome do ficheiro
    cb(null, uniqueSuffix);
  }
});


const upload = multer({
  storage: storage,                    // storage: usa a configuração de armazenamento criada acima
  limits: {                           // limits: objeto com limitações
    fileSize: 5 * 1024 * 1024         // fileSize: 5MB em bytes (5 * 1024 * 1024 = 5.242.880 bytes)
  },
  fileFilter: function (req, file, cb) {
    // fileFilter - função que valida o tipo de ficheiro antes de aceitar
    // file.mimetype - tipo MIME do ficheiro (ex: 'image/jpeg', 'image/png', 'text/plain')
    // .startsWith('image/') - verifica se o tipo começa com 'image/'
    if (file.mimetype.startsWith('image/')) {
      // cb(null, true) - aceita o ficheiro (null = sem erro, true = aceitar)
      cb(null, true);
    } else {

      cb(new Error('Apenas arquivos de imagem são permitidos!'), false);
    }
  }
});


if (process.env.NODE_ENV !== 'production') {

  app.use((req, res, next) => {

    if (!req.originalUrl.includes('/payments/')) {

      console.log(`[${new Date().toISOString()}] ${req.method} ${req.url}`);
      // req.body - dados enviados no corpo da requisição (JSON)
      console.log('Body:', req.body);
    } else {
      // Para requisições de pagamento, não mostra dados sensíveis
      console.log(`[${new Date().toISOString()}] ${req.method} ${req.url} [conteúdo sensível]`);
    }
    // next() - chama o próximo middleware na cadeia (obrigatório para continuar)
    next();
  });
}


const db = mysql.createConnection({
  host: process.env.DB_HOST,         // host: endereço do servidor MySQL (ex: 'localhost', '127.0.0.1')
  user: process.env.DB_USER,         // user: nome de utilizador da base de dados (ex: 'root')
  password: process.env.DB_PASSWORD, // password: palavra-passe do utilizador da base de dados
  database: process.env.DB_NAME      // database: nome da base de dados a conectar (ex: 'boxingforlife')
});


db.connect((err) => {
  // if (err) - se houve erro na conexão
  if (err) {
    // console.error() - escreve erro no terminal (vermelho, mais visível que console.log)
    console.error('Erro ao conectar ao banco de dados:', err);
    // return - para a execução da função aqui (não continua)
    return;
  }
  // Se chegou aqui, conexão foi bem-sucedida
  console.log('Conectado ao banco de dados MySQL');
});


app.post('/api/auth/login', (req, res) => {
  // console.log() - mostra no terminal que recebeu tentativa de login
  console.log('Login attempt received:', req.body);


  const { email, palavra_passe } = req.body;


  if (!email || !palavra_passe) {
    console.log('Missing credentials');

    return res.status(400).json({ error: 'Email e senha são obrigatórios' });
  }

  console.log('Attempting to find user with email:', email);


  db.query(
    'SELECT * FROM utilizadores WHERE email = ?',
    [email],
    (err, results) => {
      // if (err) - se houve erro na query SQL
      if (err) {
        console.error('Database error:', err);
        // res.status(500) - código HTTP 500 (Internal Server Error)
        return res.status(500).json({ error: 'Erro no servidor' });
      }

      // console.log() - mostra o resultado da query no terminal para debug
      console.log('Query results:', results);


      if (results.length === 0) {
        console.log('No user found with email:', email);
        // res.status(401) - código HTTP 401 (Unauthorized)
        return res.status(401).json({ error: 'Credenciais inválidas' });
      }


      const user = results[0];
      // {...user, palavra_passe: '***'} - spread operator que copia user mas oculta password no log
      console.log('User found:', { ...user, palavra_passe: '***' });


      if (palavra_passe !== user.palavra_passe) {
        console.log('Invalid password');
        // 401 - Unauthorized (credenciais incorrectas)
        return res.status(401).json({ error: 'Credenciais inválidas' });
      }

      // LOGIN BEM-SUCEDIDO
      console.log('Login successful for user:', user.nome);
      // res.json() - envia resposta JSON com dados do utilizador
      res.json({
        id: user.id_utilizador,         // ID único para identificar utilizador em futuras requisições
        nome: user.nome,                // Nome para mostrar na interface da app
        email: user.email,              // Email para mostrar no perfil
        nivel: user.id_nivel,           // 1=utilizador normal, 2=administrador
        local_envio: user.local_envio   // Endereço de envio guardado (pode ser null)
      });
    } // Fim do callback da query
  ); // Fim da chamada db.query()
}); // Fim da rota app.post('/api/auth/login')

app.post('/api/auth/register', (req, res) => {
  console.log('Register attempt received:', req.body);


  const { nome, email, palavra_passe, local_envio } = req.body;


  if (!nome || !email || !palavra_passe) {
    console.log('Missing registration data');
    // status(400) - Bad Request (dados em falta ou inválidos)
    // success: false - indica que a operação falhou
    return res.status(400).json({ success: false, message: 'Nome, email e palavra-passe são obrigatórios.' });
  }

  console.log('Attempting to register user with email:', email);


  db.query('SELECT * FROM utilizadores WHERE email = ?', [email], (err, results) => {
    // if (err) - se houve erro na query
    if (err) {
      console.error('Database error during email check:', err);
      // status(500) - Internal Server Error (erro do servidor)
      return res.status(500).json({ success: false, message: 'Erro ao verificar email.' });
    }


    if (results.length > 0) {
      console.log('Email already exists:', email);
      // status(409) - Conflict (recurso já existe)
      return res.status(409).json({ success: false, message: 'Email já registado.' });
    }

    console.log('Creating new user with email:', email);


    db.query(
      'INSERT INTO utilizadores (nome, email, palavra_passe, id_nivel, local_envio) VALUES (?, ?, ?, 1, ?)',
      [nome, email, palavra_passe, local_envio || null], // || null - se local_envio for vazio, usa null
      (err, result) => {
        // if (err) - se houve erro ao inserir na base de dados
        if (err) {
          console.error('Database error during user creation:', err);
          // status(500) - Internal Server Error
          return res.status(500).json({ success: false, message: 'Erro ao criar utilizador.' });
        }


        console.log('User created successfully with ID:', result.insertId);


        sendWelcomeEmail(nome, email);


        return res.json({ success: true, message: 'Conta criada com sucesso.' });
      } // Fim do callback da query INSERT
    ); // Fim da query INSERT
  }); // Fim da query SELECT para verificar email
}); // Fim da rota app.post('/api/auth/register')


app.get('/api/products', (req, res) => {

  const query = `
    SELECT p.*, m.nome as marca_nome, c.nome as categoria_nome,
           CAST(p.preco AS DECIMAL(10,2)) as preco
    FROM produtos p 
    LEFT JOIN marcas m ON p.id_marca = m.id_marca 
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
  `;

  db.query(query, (err, results) => {
    // if (err) - se houve erro na execução da query
    if (err) {
      console.error('Erro ao buscar produtos:', err);
      // status(500) - Internal Server Error
      res.status(500).json({ error: err.message });
    } else {

      res.json(results);
    } // Fim do if-else
  }); // Fim do callback da query
}); // Fim da rota app.get('/api/products')


app.get('/api/products/:codigo_base', (req, res) => {

  const codigo_base = req.params.codigo_base;


  const query = `
    SELECT 
      vp.sku,                     -- SKU: código único da variação (ex: "LUV001-M-PRETO")
      vp.stock,                   -- Stock: quantidade disponível desta variação específica
      vp.codigo_base,             -- Código base do produto (ex: "LUV001")
      t.codigo_tamanho,           -- Código do tamanho (ex: "M")
      t.descricao as tamanho,     -- Descrição legível do tamanho (ex: "Médio")
      cor.codigo_cor,             -- Código da cor (ex: "PRETO")
      cor.descricao as cor,       -- Descrição legível da cor (ex: "Preto")
      p.nome,                     -- Nome do produto (ex: "Luvas de Boxing")
      p.descricao,                -- Descrição detalhada do produto
      p.preco,                    -- Preço base do produto
      p.imagem,                   -- Caminho da imagem do produto
      m.nome as marca_nome,       -- Nome da marca (ex: "Everlast")
      c.nome as categoria_nome    -- Nome da categoria (ex: "Luvas")
    FROM variacoes_produto vp     -- Tabela principal: variações dos produtos
    LEFT JOIN produtos p ON vp.codigo_base = p.codigo_base           -- Junta dados base do produto
    LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho     -- Junta descrição do tamanho
    LEFT JOIN cores cor ON vp.codigo_cor = cor.codigo_cor           -- Junta descrição da cor
    LEFT JOIN marcas m ON p.id_marca = m.id_marca                    -- Junta nome da marca
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria        -- Junta nome da categoria
    WHERE vp.codigo_base = ?     -- WHERE: filtra resultados pelo código base específico fornecido
  `;


  db.query(query, [codigo_base], (err, results) => {
    if (err) {
      console.error('Erro ao buscar variações do produto:', err);
      return res.status(500).json({ error: 'Erro na base de dados' });
    }


    if (results.length === 0) {
      // status(404) - Not Found (produto não existe)
      return res.status(404).json({ error: 'Produto não encontrado' });
    }


    const variations = results.map(row => ({
      sku: row.sku,                        // Mantém SKU como string
      stock: row.stock,                    // Mantém stock como número
      tamanho: row.tamanho,                // Descrição do tamanho
      cor: row.cor,                        // Descrição da cor
      nome: row.nome,                      // Nome do produto
      descricao: row.descricao,            // Descrição do produto
      preco: parseFloat(row.preco),        // parseFloat() - converte string para número decimal
      imagem: row.imagem,                  // Caminho da imagem
      marca_nome: row.marca_nome,          // Nome da marca
      categoria_nome: row.categoria_nome   // Nome da categoria
    }));

    // res.json(variations) - envia array formatado como resposta
    res.json(variations);
  });
});


app.post('/api/cart', (req, res) => {

  const { id_utilizador, session_id, sku, quantidade } = req.body;

  // console.log() - mostra dados recebidos para debug
  console.log('📦 Recebendo requisição para adicionar ao carrinho:', {
    id_utilizador,    // null se utilizador não está logado
    session_id,       // ID da sessão temporária (para utilizadores anónimos)
    sku,              // Código da variação específica
    quantidade        // Quantidade a adicionar
  });


  if (!sku || !quantidade) {
    console.log('❌ SKU ou quantidade não fornecidos');
    // status(400) - Bad Request (dados em falta)
    return res.status(400).json({ error: 'SKU e quantidade são obrigatórios' });
  }


  const checkQuery = `
    SELECT id_carrinho, quantidade 
    FROM carrinho 
    WHERE ${id_utilizador ? 'id_utilizador = ?' : 'session_id = ?'} 
    AND sku = ?
  `;

  const checkParams = id_utilizador ? [id_utilizador, sku] : [session_id, sku];

  console.log('🔍 Verificando item existente:', {
    query: checkQuery,      // Mostra a query gerada
    params: checkParams     // Mostra os parâmetros que serão usados
  });

  // EXECUTAR QUERY PARA VERIFICAR SE ITEM JÁ EXISTE NO CARRINHO
  db.query(checkQuery, checkParams, (err, results) => {
    if (err) {
      console.error('❌ Erro ao verificar carrinho:', err);
      return res.status(500).json({ error: err.message });
    }


    if (results.length > 0) {
      // results[0] - primeiro (e único) registo encontrado
      const currentQuantity = results[0].quantidade;     // Quantidade atual no carrinho
      // parseInt() - converte string para inteiro
      const newQuantity = currentQuantity + parseInt(quantidade); // Soma nova à existente

      console.log('📝 Atualizando quantidade:', {
        id_carrinho: results[0].id_carrinho,    // ID do registo no carrinho
        quantidade_atual: currentQuantity,       // Quantidade antes da atualização
        quantidade_nova: newQuantity            // Quantidade após somar a nova
      });


      const updateQuery = `
        UPDATE carrinho 
        SET quantidade = ? 
        WHERE id_carrinho = ?
      `;


      db.query(updateQuery, [newQuantity, results[0].id_carrinho], (err, result) => {

        if (err) {
          console.error('❌ Erro ao atualizar carrinho:', err);

          return res.status(500).json({ error: err.message });

        }        // ========================================

        console.log('✅ Carrinho atualizado com sucesso');


        res.json({                      // { - inicia objeto JavaScript literal

          success: true,                                    // Boolean: flag de operação bem-sucedida


          message: 'Quantidade atualizada no carrinho',     // String: mensagem para o utilizador final


          id_carrinho: results[0].id_carrinho               // Number: ID único do registo atualizado

        });                             // } - fecha objeto literal | ) - fecha parâmetros de .json()
      }); // Fim do callback da query UPDATE
    } else {

      const insertQuery = `
        INSERT INTO carrinho (${id_utilizador ? 'id_utilizador' : 'session_id'}, sku, quantidade)
        VALUES (?, ?, ?)
      `;


      const insertParams = id_utilizador ?
        [id_utilizador, sku, quantidade] :     // Array para utilizadores logados
        [session_id, sku, quantidade];         // Array para utilizadores anónimos


      console.log('➕ Inserindo novo item:', {
        query: insertQuery,      // String: a query SQL construída dinamicamente
        params: insertParams     // Array: os parâmetros na ordem correta
      });


      db.query(insertQuery, insertParams, (err, result) => {

        if (err) {
          console.error('❌ Erro ao inserir no carrinho:', err);
          // HTTP 500 Internal Server Error - problema no servidor/base de dados
          return res.status(500).json({ error: err.message });
        }


        console.log('✅ Item adicionado ao carrinho com sucesso:', {
          id_carrinho: result.insertId    // Number: ID único do novo registo criado
        });


        res.status(201).json({
          success: true,                          // Boolean: operação bem-sucedida
          message: 'Item adicionado ao carrinho', // String: mensagem descritiva
          id_carrinho: result.insertId           // Number: ID do novo registo criado
        });
      }); // Fim do callback da query INSERT
    } // Fim do else (inserção de novo item)
  });
});


app.get('/api/cart/session/:session_id', (req, res) => {
  const { session_id } = req.params;

  console.log('🛒 Buscando carrinho para session_id:', session_id);

  if (!session_id) {
    console.log('❌ Session ID não fornecido');
    return res.status(400).json({ error: 'Session ID é obrigatório' });
  }

  // Query complexa que junta carrinho com informações completas dos produtos
  const query = `
    SELECT 
      c.id_carrinho,              -- ID único do item no carrinho
      c.session_id,               -- ID da sessão temporária
      c.sku,                      -- Código único da variação do produto
      c.quantidade,               -- Quantidade selecionada
      p.nome,                     -- Nome do produto
      p.imagem,                   -- Imagem do produto
      t.descricao as tamanho,     -- Tamanho selecionado
      cor.descricao as cor,       -- Cor selecionada
      CAST(p.preco AS DECIMAL(10,2)) as preco_unitario,    -- Preço por unidade
      CAST(p.preco * c.quantidade AS DECIMAL(10,2)) as preco_total  -- Preço total (preço × quantidade)
    FROM carrinho c
    LEFT JOIN variacoes_produto vp ON c.sku = vp.sku
    LEFT JOIN produtos p ON vp.codigo_base = p.codigo_base
    LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
    LEFT JOIN cores cor ON vp.codigo_cor = cor.codigo_cor
    WHERE c.session_id = ?       -- Filtra pela sessão específica
  `;

  console.log('🔍 Executando query:', {
    query,
    params: [session_id]
  });

  db.query(query, [session_id], (err, results) => {
    if (err) {
      console.error('❌ Erro ao buscar carrinho:', err);
      return res.status(500).json({ error: err.message });
    }

    // Formata os resultados para garantir tipos de dados corretos
    const formattedResults = results.map(item => ({
      id_carrinho: item.id_carrinho,
      sku: item.sku,
      quantidade: parseInt(item.quantidade || '0'),        // Garante que é número
      nome: item.nome,
      preco_unitario: parseFloat(item.preco_unitario || '0'), // Garante que é número
      preco_total: parseFloat(item.preco_total || '0'),       // Garante que é número
      imagem: item.imagem,
      tamanho: item.tamanho,
      cor: item.cor
    }));

    console.log('✅ Carrinho encontrado:', {
      total_items: formattedResults.length,
      items: formattedResults
    });

    res.json(formattedResults);
  });
});


app.post('/api/cart/transfer', (req, res) => {
  // Extrai os dados do corpo da requisição HTTP
  // Usa destructuring assignment para obter session_id e id_utilizador do req.body
  const { session_id, id_utilizador } = req.body;

  // Validação obrigatória: verifica se ambos os parâmetros foram enviados
  // O operador || (OR lógico) retorna true se qualquer um dos valores for falsy (null, undefined, '', 0, false)
  if (!session_id || !id_utilizador) {
    // Se algum parâmetro está em falta, retorna erro HTTP 400 (Bad Request)
    // e termina a execução da função com 'return'
    return res.status(400).json({ error: 'Session ID e ID do usuário são obrigatórios' });
  }


  db.beginTransaction((err) => {
    // Se não conseguir iniciar a transação, retorna erro
    if (err) {
      console.error('Erro ao iniciar transação:', err);
      return res.status(500).json({ error: "Erro ao iniciar transação" });
    }


    db.query(
      'UPDATE carrinho SET id_utilizador = ?, session_id = NULL WHERE session_id = ?',
      [id_utilizador, session_id], // Array com os valores a substituir nos placeholders
      (err, result) => {           // Callback executado quando a query termina
        // Se houver erro na query UPDATE
        if (err) {
          console.error('Erro ao transferir carrinho:', err);

          // ROLLBACK: desfaz todas as alterações feitas na transação
          // Como houve erro, queremos voltar ao estado original
          return db.rollback(() => {
            // Depois do rollback, envia resposta de erro ao cliente
            res.status(500).json({ error: "Erro ao transferir carrinho" });
          });
        }


        db.commit((err) => {
          // Se o commit falhar (situação rara mas possível)
          if (err) {
            console.error('Erro ao fazer commit:', err);

            // Mesmo assim fazemos rollback para limpar a transação
            return db.rollback(() => {
              res.status(500).json({ error: "Erro ao finalizar transferência" });
            });
          }


          res.json({
            success: true,                        // Flag booleana de sucesso
            message: 'Carrinho transferido com sucesso'  // Mensagem descritiva
          });
        });
      }
    );
  });
});


app.get('/api/cart/:id_utilizador', (req, res) => {
  // Extrai o id_utilizador dos parâmetros da URL
  // Por exemplo, se a URL for '/api/cart/123', o id_utilizador será '123'
  const { id_utilizador } = req.params;

  // Log para debugging - ajuda a rastrear as requisições no servidor
  console.log('🛒 Buscando carrinho para id_utilizador:', id_utilizador);

  // Validação obrigatória: verifica se o ID foi fornecido na URL
  if (!id_utilizador) {
    console.log('❌ ID do usuário não fornecido');
    // Retorna erro HTTP 400 (Bad Request) se o parâmetro estiver em falta
    return res.status(400).json({ error: 'ID do usuário é obrigatório' });
  }


  const query = `
    SELECT 
      c.id_carrinho,              -- ID único do item no carrinho (chave primária)
      c.id_utilizador,            -- ID do utilizador logado (chave estrangeira)
      c.sku,                      -- Código único da variação do produto (Ex: LUVA001-M-AZUL)
      c.quantidade,               -- Quantidade que o utilizador quer comprar
      p.nome,                     -- Nome do produto (Ex: "Luvas de Boxe Premium")
      p.imagem,                   -- Caminho para a imagem do produto
      t.descricao as tamanho,     -- Descrição do tamanho (Ex: "Médio")
      cor.descricao as cor,       -- Descrição da cor (Ex: "Azul Marinho")
      CAST(p.preco AS DECIMAL(10,2)) as preco_unitario,    -- Preço por unidade (conversão para decimal)
      CAST(p.preco * c.quantidade AS DECIMAL(10,2)) as preco_total  -- Preço total = preço × quantidade
    FROM carrinho c
    LEFT JOIN variacoes_produto vp ON c.sku = vp.sku           -- Liga carrinho às variações
    LEFT JOIN produtos p ON vp.codigo_base = p.codigo_base     -- Liga variações aos produtos
    LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho  -- Liga variações aos tamanhos
    LEFT JOIN cores cor ON vp.codigo_cor = cor.codigo_cor      -- Liga variações às cores
    WHERE c.id_utilizador = ?    -- Filtra apenas os itens deste utilizador específico
  `;

  // Log para debugging - mostra a query e parâmetros que serão executados
  console.log('🔍 Executando query:', {
    query,                // A query SQL completa
    params: [id_utilizador]  // Array com os valores a substituir no placeholder ?
  });


  db.query(query, [id_utilizador], (err, results) => {
    // Se houver erro na execução da query
    if (err) {
      console.error('❌ Erro ao buscar carrinho:', err);
      // Retorna erro HTTP 500 (Internal Server Error) com a mensagem do erro
      return res.status(500).json({ error: err.message });
    }


    const formattedResults = results.map(item => ({
      id_carrinho: item.id_carrinho,                        // Mantém como está (número)
      sku: item.sku,                                        // Mantém como está (string)
      quantidade: parseInt(item.quantidade || '0'),         // Converte para número inteiro, usa 0 se for null/undefined
      nome: item.nome,                                      // Mantém como está (string)
      preco_unitario: parseFloat(item.preco_unitario || '0'), // Converte para número decimal
      preco_total: parseFloat(item.preco_total || '0'),       // Converte para número decimal
      imagem: item.imagem,                                  // Mantém como está (string - caminho para imagem)
      tamanho: item.tamanho,                               // Mantém como está (string)
      cor: item.cor                                        // Mantém como está (string)
    }));

    // Log para debugging - mostra o resultado final que será enviado
    console.log('✅ Carrinho encontrado:', {
      total_items: formattedResults.length,    // Quantos itens estão no carrinho
      items: formattedResults                  // Array com todos os itens formatados
    });


    res.json(formattedResults);
  });
});


app.get('/api/users/:id', (req, res) => {

  const userId = req.params.id;


  console.log(`🔍 Buscando perfil do usuário com ID: ${userId}`);


  if (!userId) {
    console.log('❌ ID do usuário não fornecido');
    // HTTP 400 Bad Request - cliente enviou dados inválidos/incompletos
    return res.status(400).json({ error: 'ID do usuário é obrigatório' });
  }


  const query = `
    SELECT id_utilizador, nome, email, local_envio
    FROM utilizadores
    WHERE id_utilizador = ?
  `;


  db.query(query, [userId], (err, results) => {

    if (err) {
      console.error('❌ Erro ao buscar perfil do usuário:', err);
      // HTTP 500 Internal Server Error - problema no servidor/base de dados
      return res.status(500).json({ error: err.message });
    }


    if (results.length === 0) {
      // Log específico para não encontrado
      console.log(`❌ Usuário com ID ${userId} não encontrado`);
      // HTTP 404 Not Found - recurso solicitado não existe
      return res.status(404).json({ error: 'Usuário não encontrado' });
    }


    const user = results[0];


    console.log('✅ Perfil do usuário encontrado:', {
      id: user.id_utilizador,                           // ID para confirmação
      nome: user.nome,                                  // Nome para log
      local_envio: user.local_envio || '(não definido)' // Status do endereço
    });


    res.json({
      id: user.id_utilizador,      // ID único (número)
      nome: user.nome,             // Nome completo (string)
      email: user.email,           // Email de contacto (string)
      local_envio: user.local_envio // Endereço de envio (string ou null)
    });
  }); // Fim do callback da query
}); // Fim da rota GET /api/users/:id


// Esta rota permite atualizar informações do perfil (nome, email, endereço)
app.put('/api/users/:id', (req, res) => {

  const userId = req.params.id;
  const { local_envio, nome, email } = req.body;

  // Logs para debugging - mostram que dados foram recebidos
  console.log(`📝 Atualizando perfil do usuário com ID: ${userId}`);
  console.log('Dados recebidos:', req.body);


  if (!userId) {
    console.log('❌ ID do usuário não fornecido');
    // HTTP 400 Bad Request - parâmetro obrigatório em falta
    return res.status(400).json({ error: 'ID do usuário é obrigatório' });
  }



  // Array com nomes dos campos que podem ser atualizados
  const camposValidos = ['local_envio', 'nome', 'email'];


  const camposEnviados = camposValidos.filter(campo =>
    Object.prototype.hasOwnProperty.call(req.body, campo)
  );

  // Se nenhum campo válido foi enviado, não há nada para atualizar
  if (camposEnviados.length === 0) {
    console.log('ℹ️ Nenhum campo válido para atualizar.');
    // HTTP 400 Bad Request - dados inválidos/inexistentes
    return res.status(400).json({ error: 'Nenhum campo válido para atualizar' });
  }



  const updateFields = [];    // Array para armazenar "campo = ?"
  const queryParams = [];     // Array para armazenar valores dos parâmetros


  if (Object.prototype.hasOwnProperty.call(req.body, 'local_envio')) {
    updateFields.push('local_envio = ?');    // Adiciona "local_envio = ?" à query
    queryParams.push(local_envio);           // Adiciona valor aos parâmetros
    // Log específico para rastreamento de alterações de endereço
    console.log(`📍 Atualizando endereço de envio para: "${local_envio}"`);
  }

  // PROCESSAMENTO DO NOME
  if (Object.prototype.hasOwnProperty.call(req.body, 'nome')) {
    updateFields.push('nome = ?');           // Adiciona "nome = ?" à query
    queryParams.push(nome);                  // Adiciona valor aos parâmetros
    // Log específico para rastreamento de alterações de nome
    console.log(`📝 Atualizando nome para: "${nome}"`);
  }

  // PROCESSAMENTO DO EMAIL
  if (Object.prototype.hasOwnProperty.call(req.body, 'email')) {
    updateFields.push('email = ?');          // Adiciona "email = ?" à query
    queryParams.push(email);                 // Adiciona valor aos parâmetros
    // Log específico para rastreamento de alterações de email
    console.log(`📝 Atualizando email para: "${email}"`);
  }


  queryParams.push(userId);


  const query = `
    UPDATE utilizadores
    SET ${updateFields.join(', ')}     -- Junta os campos a atualizar
    WHERE id_utilizador = ?            -- Especifica qual utilizador atualizar
  `;


  db.query(query, queryParams, (err, result) => {

    if (err) {
      console.error('❌ Erro ao atualizar perfil do usuário:', err);
      // HTTP 500 Internal Server Error - problema no servidor/base de dados
      return res.status(500).json({ error: err.message });
    }


    db.query(
      'SELECT id_utilizador, nome, email, local_envio FROM utilizadores WHERE id_utilizador = ?',
      [userId],
      (err, results) => {

        if (err || results.length === 0) {
          console.error('❌ Erro ao buscar perfil atualizado:', err);
          // Resposta de sucesso sem dados detalhados
          return res.json({
            success: true,                        // Atualização foi bem-sucedida
            message: 'Perfil atualizado',         // Mensagem de confirmação
            user: null                           // Sem dados detalhados devido ao erro
          });
        }


        const updatedUser = results[0];
        res.json({
          success: true,                          // Flag booleana de sucesso
          message: 'Perfil atualizado',           // Mensagem descritiva
          user: {                                 // Dados atualizados do utilizador
            id: updatedUser.id_utilizador,        // ID (confirmação)
            nome: updatedUser.nome,               // Nome atualizado
            email: updatedUser.email,             // Email atualizado
            local_envio: updatedUser.local_envio  // Endereço atualizado
          }
        });
      } // Fim do callback da query de confirmação
    ); // Fim da query de confirmação
  }); // Fim do callback da query de atualização
}); // Fim da rota PUT /api/users/:id


app.get('/api/blog', (req, res) => {

  const query = `
    SELECT b.*,                         -- Todos os campos da tabela blog
           u.nome as autor_nome         -- Nome do autor (renomeado para clareza)
    FROM blog b                         -- Tabela principal: posts do blog
    JOIN utilizadores u ON b.id_utilizador = u.id_utilizador  -- Junta com dados do autor
    ORDER BY b.data_publicacao DESC     -- Ordena por data (mais recentes primeiro)
  `;


  db.query(query, (err, results) => {

    if (err) {
      console.error('Erro ao buscar posts do blog:', err);
      // HTTP 500 Internal Server Error - problema interno do servidor
      res.status(500).json({ error: err.message });
    } else {

      res.json(results);
    } // Fim do else (sucesso)
  }); // Fim do callback da query
}); // Fim da rota GET /api/blog


app.get('/api/blog/:id', (req, res) => {
  // Query similar à anterior, mas filtra por ID específico
  const query = `
    SELECT b.*, u.nome as autor_nome    -- Todos os campos do post + nome do autor
    FROM blog b
    JOIN utilizadores u ON b.id_utilizador = u.id_utilizador
    WHERE b.id_post = ?                 -- Filtra pelo ID do post
  `;

  db.query(query, [req.params.id], (err, results) => {
    if (err) {
      console.error('Erro ao buscar post do blog:', err);
      res.status(500).json({ error: err.message });
    } else if (results.length === 0) {
      res.status(404).json({ error: 'Post não encontrado' });
    } else {
      // Retorna o post encontrado (primeiro e único resultado)
      res.json(results[0]);
    }
  });
});


app.post('/api/blog', upload.single('imagem'), (req, res) => {

  const { titulo, resumo, conteudo, id_utilizador } = req.body;


  if (!titulo || !resumo || !conteudo || !id_utilizador) {
    // HTTP 400 Bad Request - dados obrigatórios em falta
    return res.status(400).json({
      error: 'Título, resumo, conteúdo e id_utilizador são obrigatórios'
    });
  }


  let caminhoImagem = null;  // Por padrão, sem imagem

  if (req.file) {

    caminhoImagem = 'imagens/blog/' + req.file.filename;
  }


  const dataPublicacao = new Date();


  const query = `
    INSERT INTO blog (titulo, resumo, conteudo, imagem, data_publicacao, id_utilizador) 
    VALUES (?, ?, ?, ?, ?, ?)
  `;


  db.query(query, [titulo, resumo, conteudo, caminhoImagem, dataPublicacao, id_utilizador], (err, result) => {

    if (err) {
      console.error('Erro ao criar blog:', err);

      if (req.file) {
        fs.unlink(req.file.path, (unlinkErr) => {
          if (unlinkErr) console.error('Erro ao remover arquivo:', unlinkErr);
        });
      }

      // HTTP 500 Internal Server Error - problema no servidor
      return res.status(500).json({ error: 'Erro ao criar blog: ' + err.message });
    }


    res.status(201).json({
      success: true,                    // Flag booleana de sucesso
      id_blog: result.insertId,         // ID do post criado (para referência)
      message: 'Blog criado com sucesso!'  // Mensagem de confirmação
    });
  }); // Fim do callback da query INSERT
}); // Fim da rota POST /api/blog

app.get('/api/blog/:id/comments', (req, res) => {
  const id_post = req.params.id;

  const query = `
    SELECT c.id_comentario, c.id_post, c.id_utilizador, u.nome as nome_utilizador, c.conteudo, c.data_comentario
    FROM comentarios_blog c
    JOIN utilizadores u ON c.id_utilizador = u.id_utilizador  -- Junta com dados do utilizador
    WHERE c.id_post = ?                                       -- Filtra pelo post específico
    ORDER BY c.data_comentario DESC                           -- Ordena por data (mais recentes primeiro)
  `;

  db.query(query, [id_post], (err, results) => {
    if (err) {
      console.error('Erro ao buscar comentários do blog:', err);
      return res.status(500).json({ error: 'Erro ao buscar comentários' });
    }
    // Retorna todos os comentários do post
    res.json(results);
  });
});

// ROTA PARA ADICIONAR COMENTÁRIO A UM POST
// Esta rota permite aos utilizadores adicionar comentários aos posts do blog
app.post('/api/blog/:id/comments', (req, res) => {
  const id_post = req.params.id || req.body.id_post || req.body.postId;
  const id_utilizador = req.body.id_utilizador || req.body.userId;
  const conteudo = req.body.conteudo || req.body.content;

  // Validação de campos obrigatórios
  if (!id_post || !id_utilizador || !conteudo) {
    return res.status(400).json({ error: 'id_post, id_utilizador e conteudo são obrigatórios' });
  }

  // Query para inserir novo comentário
  const query = 'INSERT INTO comentarios_blog (id_post, id_utilizador, conteudo) VALUES (?, ?, ?)';

  db.query(query, [id_post, id_utilizador, conteudo], (err, result) => {
    if (err) {
      console.error('Erro ao inserir comentário:', err);
      return res.status(500).json({ error: 'Erro ao inserir comentário' });
    }

    res.status(201).json({
      success: true,
      id_comentario: result.insertId,
      message: 'Comentário adicionado com sucesso'
    });
  });
});


app.delete('/api/blog/:id', (req, res) => {

  const blogId = req.params.id;
  const userId = req.body.id_utilizador || req.query.id_utilizador;


  if (!userId) {

    return res.status(400).json({ error: 'ID do utilizador é obrigatório' });
  }


  db.query(
    'SELECT * FROM blog WHERE id_post = ? AND id_utilizador = ?',
    [blogId, userId],

    (err, results) => {

      if (err) {
        console.error('Erro ao verificar blog:', err);
        return res.status(500).json({ error: 'Erro no servidor' });
      }


      if (results.length === 0) {
        // status(404): Not Found - recurso não encontrado
        return res.status(404).json({ error: 'Blog não encontrado ou não pertence ao utilizador' });
      }


      const blog = results[0];


      db.query(
        // SQL DELETE: remove todos os comentários associados a este post
        'DELETE FROM comentarios_blog WHERE id_post = ?',
        // Array com o ID do post (só um parâmetro desta vez)
        [blogId],
        // Callback para quando a eliminação dos comentários terminar
        (err) => {

          if (err) {
            console.error('Erro ao eliminar comentários:', err);
            return res.status(500).json({ error: 'Erro ao eliminar comentários' });
          }


          db.query(
            'DELETE FROM blog WHERE id_post = ? AND id_utilizador = ?',
            [blogId, userId],

            (err, result) => {

              if (err) {
                console.error('Erro ao eliminar blog:', err);
                return res.status(500).json({ error: 'Erro ao eliminar blog' });
              }


              if (blog.imagem) {

                const imagePath = path.join(__dirname, '..', '..', 'Applications', 'XAMPP', 'xamppfiles', 'htdocs', 'PAP', blog.imagem);

                // fs.unlink(): elimina ficheiro do sistema de ficheiros (assíncrono)
                fs.unlink(imagePath, (unlinkErr) => {

                  if (unlinkErr) console.error('Erro ao remover imagem:', unlinkErr);
                });
              }


              res.json({ success: true, message: 'Blog eliminado com sucesso' });
            }
          );
        }
      );
    }
  );
});


app.delete('/api/blog/comments/:id', (req, res) => {

  const commentId = req.params.id;

  const userId = req.body.id_utilizador || req.query.id_utilizador;


  if (!userId) {

    return res.status(400).json({ error: 'ID do utilizador é obrigatório' });
  }


  db.query(

    'DELETE FROM comentarios_blog WHERE id_comentario = ? AND id_utilizador = ?',
    [commentId, userId],

    (err, result) => {

      if (err) {

        console.error('Erro ao eliminar comentário:', err);
        return res.status(500).json({ error: 'Erro ao eliminar comentário' });
      }


      if (result.affectedRows === 0) {
        // status(404): Not Found - recurso não encontrado ou sem permissão
        return res.status(404).json({ error: 'Comentário não encontrado ou não pertence ao utilizador' });
      }


      res.json({ success: true, message: 'Comentário eliminado com sucesso' });
    }
  );
});


app.get('/api/reviews', (req, res) => {

  const query = `
    SELECT r.*, u.nome as autor_nome    -- Seleciona todos os campos da tabela reviews (*) MAIS o nome do utilizador
    FROM reviews r                      -- reviews: tabela principal (alias 'r' para simplificar)
    JOIN utilizadores u ON r.id_utilizador = u.id_utilizador  -- INNER JOIN: combina reviews com dados do autor
    ORDER BY r.data_review DESC         -- Ordena por data de review: mais recentes primeiro (DESC = descendente)
  `;

  db.query(query, (err, results) => {

    if (err) {
      console.error('Erro ao buscar reviews:', err);

      res.status(500).json({ error: err.message });
    } else {

      res.json(results);
    }
  });
});


app.post('/api/reviews', (req, res) => {

  const { id_utilizador, mensagem, avaliacao, id_encomenda } = req.body;


  const query = 'INSERT INTO reviews (id_utilizador, mensagem, avaliacao, id_encomenda) VALUES (?, ?, ?, ?)';


  db.query(query, [id_utilizador, mensagem, avaliacao, id_encomenda], (err, result) => {

    if (err) {

      console.error('Erro ao adicionar review:', err);

      res.status(500).json({ error: err.message });
    } else {

      res.status(201).json({
        message: 'Review adicionada com sucesso',    // Mensagem de confirmação
        id: result.insertId                          // ID da nova review criada
      });
    }
  });
});


app.get('/api/products/:codigo_base/reviews', (req, res) => {
  const { codigo_base } = req.params;

  const query = `
    SELECT rp.*, u.nome as nome_utilizador
    FROM reviews_produtos rp
    JOIN utilizadores u ON rp.id_utilizador = u.id_utilizador
    WHERE rp.codigo_base = ?
    ORDER BY rp.data_review DESC
  `;

  db.query(query, [codigo_base], (err, results) => {
    if (err) {
      console.error('Erro ao buscar reviews do produto:', err);
      res.status(500).json({ error: err.message });
    } else {
      res.json(results);
    }
  });
});

// CRIAR REVIEW DE PRODUTO (POST /api/products/reviews)
app.post('/api/products/reviews', (req, res) => {
  const { id_encomenda, codigo_base, id_utilizador, classificacao, comentario } = req.body;

  // Verificar se já existe review para este produto/utilizador/encomenda
  const checkQuery = `
    SELECT id_review FROM reviews_produtos 
    WHERE id_encomenda = ? AND codigo_base = ? AND id_utilizador = ?
  `;

  db.query(checkQuery, [id_encomenda, codigo_base, id_utilizador], (err, existing) => {
    if (err) {
      console.error('Erro ao verificar duplicata de review:', err);
      return res.status(500).json({ error: err.message });
    }

    if (existing.length > 0) {
      return res.status(409).json({ error: 'Review já existe para este produto nesta encomenda' });
    }

    const insertQuery = `
      INSERT INTO reviews_produtos (id_encomenda, codigo_base, id_utilizador, classificacao, comentario) 
      VALUES (?, ?, ?, ?, ?)
    `;

    db.query(insertQuery, [id_encomenda, codigo_base, id_utilizador, classificacao, comentario], (err, result) => {
      if (err) {
        console.error('Erro ao adicionar review do produto:', err);
        res.status(500).json({ error: err.message });
      } else {
        res.status(201).json({
          message: 'Review do produto adicionada com sucesso',
          id: result.insertId
        });
      }
    });
  });
});

// VERIFICAR SE JÁ TEM REVIEW DE PRODUTO (GET /api/products/reviews/check)
app.get('/api/products/reviews/check', (req, res) => {
  const { id_encomenda, codigo_base, id_utilizador } = req.query;

  const query = `
    SELECT id_review FROM reviews_produtos 
    WHERE id_encomenda = ? AND codigo_base = ? AND id_utilizador = ?
  `;

  db.query(query, [id_encomenda, codigo_base, id_utilizador], (err, results) => {
    if (err) {
      console.error('Erro ao verificar review do produto:', err);
      res.status(500).json({ error: err.message });
    } else {
      res.json({ hasReview: results.length > 0 });
    }
  });
});

// ===================================================================
// ROTAS DE REVIEWS DE ENCOMENDAS - SISTEMA SEPARADO PARA ENCOMENDAS
// ===================================================================

// OBTER REVIEWS DE UMA ENCOMENDA ESPECÍFICA (GET /api/orders/:id/reviews)
app.get('/api/orders/:id/reviews', (req, res) => {
  const { id } = req.params;

  const query = `
    SELECT re.*, u.nome as nome_utilizador
    FROM reviews_encomendas re
    JOIN utilizadores u ON re.id_utilizador = u.id_utilizador
    WHERE re.id_encomenda = ?
    ORDER BY re.data_review DESC
  `;

  db.query(query, [id], (err, results) => {
    if (err) {
      console.error('Erro ao buscar reviews da encomenda:', err);
      res.status(500).json({ error: err.message });
    } else {
      res.json(results);
    }
  });
});

// CRIAR REVIEW DE ENCOMENDA (POST /api/orders/reviews)
app.post('/api/orders/reviews', (req, res) => {
  const { id_encomenda, id_utilizador, classificacao, comentario } = req.body;

  // Verificar se já existe review para esta encomenda/utilizador
  const checkQuery = `
    SELECT id_review FROM reviews_encomendas 
    WHERE id_encomenda = ? AND id_utilizador = ?
  `;

  db.query(checkQuery, [id_encomenda, id_utilizador], (err, existing) => {
    if (err) {
      console.error('Erro ao verificar duplicata de review de encomenda:', err);
      return res.status(500).json({ error: err.message });
    }

    if (existing.length > 0) {
      return res.status(409).json({ error: 'Review já existe para esta encomenda' });
    }

    const insertQuery = `
      INSERT INTO reviews_encomendas (id_encomenda, id_utilizador, classificacao, comentario) 
      VALUES (?, ?, ?, ?)
    `;

    db.query(insertQuery, [id_encomenda, id_utilizador, classificacao, comentario], (err, result) => {
      if (err) {
        console.error('Erro ao adicionar review da encomenda:', err);
        res.status(500).json({ error: err.message });
      } else {
        res.status(201).json({
          message: 'Review da encomenda adicionada com sucesso',
          id: result.insertId
        });
      }
    });
  });
});

// VERIFICAR SE JÁ TEM REVIEW DE ENCOMENDA (GET /api/orders/reviews/check)
app.get('/api/orders/reviews/check', (req, res) => {
  const { id_encomenda, id_utilizador } = req.query;

  const query = `
    SELECT id_review FROM reviews_encomendas 
    WHERE id_encomenda = ? AND id_utilizador = ?
  `;

  db.query(query, [id_encomenda, id_utilizador], (err, results) => {
    if (err) {
      console.error('Erro ao verificar review da encomenda:', err);
      res.status(500).json({ error: err.message });
    } else {
      res.json({ hasReview: results.length > 0 });
    }
  });
});


app.post('/api/orders', (req, res) => {

  console.log('📦 Recebendo ordem:', JSON.stringify(req.body, null, 2));


  const { id_utilizador, itens, total, local_envio } = req.body;

  if (!id_utilizador || !itens || !total || !local_envio) {
    // status(400): Bad Request - dados inválidos ou em falta
    return res.status(400).json({
      error: "Dados incompletos",
      // Array.filter(Boolean): remove elementos falsy (null, undefined, false)
      // Operador ternário (? :) : se condição é true retorna string, senão null
      details: [
        !id_utilizador ? "ID do usuário é obrigatório" : null,
        !itens ? "Itens são obrigatórios" : null,
        !total ? "Total é obrigatório" : null,
        !local_envio ? "Local de envio é obrigatório" : null
      ].filter(Boolean)  // Remove valores null, mantém apenas strings de erro
    });
  }


  if (!Array.isArray(itens) || itens.length === 0) {
    return res.status(400).json({
      error: "Dados inválidos",
      details: ["Lista de itens deve ser um array não vazio"]
    });
  }


  for (const item of itens) {
    if (!item.sku || !item.quantidade || !item.preco_unitario) {
      return res.status(400).json({
        error: "Dados incompletos",
        details: [
          !item.sku ? "SKU é obrigatório" : null,
          !item.quantidade ? "Quantidade é obrigatória" : null,
          !item.preco_unitario ? "Preço unitário é obrigatório" : null
        ].filter(Boolean)  // Remove valores null do array
      });
    }
  }

  db.beginTransaction((err) => {

    if (err) {
      console.error('Erro ao iniciar transação:', err);
      return res.status(500).json({ error: "Erro ao iniciar transação" });
    }


    db.query(
      'INSERT INTO encomendas (id_utilizador, total, status, local_envio) VALUES (?, ?, "pago", ?)',
      [id_utilizador, total, local_envio],
      // Callback executado quando a inserção da encomenda principal termina
      (err, result) => {

        if (err) {
          console.error('Erro ao criar encomenda:', err);
          // db.rollback(): desfaz todas as operações da transação
          return db.rollback(() => {
            res.status(500).json({ error: "Erro ao criar encomenda" });
          });
        }


        const orderId = result.insertId;
        console.log('✅ Ordem criada com ID:', orderId);

        // Função recursiva para processar itens sequencialmente
        function processarItem(index) {
          // Se processamos todos os itens, finalizar transação
          if (index >= itens.length) {
            console.log('✅ Todos os itens processados. Limpando carrinho...');

            db.query(
              'DELETE FROM carrinho WHERE id_utilizador = ?',
              [id_utilizador],
              (err) => {
                if (err) {
                  console.error('Erro ao limpar carrinho:', err);
                  return db.rollback(() => {
                    res.status(500).json({ error: "Erro ao limpar carrinho" });
                  });
                }

                db.commit((err) => {
                  if (err) {
                    console.error('Erro ao fazer commit:', err);
                    return db.rollback(() => {
                      res.status(500).json({ error: "Erro ao finalizar ordem" });
                    });
                  }

                  console.log('✅ Encomenda criada com sucesso! Enviando email de confirmação...');

                  // Enviar email de confirmação após sucesso
                  sendOrderConfirmationEmail(id_utilizador, orderId, itens, total, local_envio);

                  res.status(201).json({
                    success: true,
                    message: 'Ordem criada com sucesso',
                    orderId: orderId
                  });
                });
              }
            );
            return;
          }

          const item = itens[index];

          // Log detalhado do item para debug
          console.log(`🔍 DEBUG - Processando item ${index + 1}/${itens.length}:`, {
            sku: item.sku,
            quantidade: item.quantidade,
            preco_unitario: item.preco_unitario,
            nome: item.nome || 'N/A'
          });

          // Verificar se o SKU existe na base de dados antes de inserir
          db.query(
            'SELECT sku, stock FROM variacoes_produto WHERE sku = ?',
            [item.sku],
            (err, stockCheck) => {
              if (err) {
                console.error('Erro ao verificar SKU:', err);
                return db.rollback(() => {
                  res.status(500).json({ error: "Erro ao verificar produto" });
                });
              }

              if (stockCheck.length === 0) {
                console.error(`❌ SKU não encontrado na base de dados: ${item.sku}`);
                return db.rollback(() => {
                  res.status(400).json({
                    error: "Produto não encontrado",
                    details: `SKU ${item.sku} não existe na base de dados`
                  });
                });
              }

              const currentStock = stockCheck[0].stock;
              console.log(`📊 Stock atual para SKU ${item.sku}: ${currentStock} unidades`);

              if (currentStock < item.quantidade) {
                console.error(`❌ Stock insuficiente para SKU: ${item.sku} (disponível: ${currentStock}, pedido: ${item.quantidade})`);
                return db.rollback(() => {
                  res.status(400).json({
                    error: "Stock insuficiente",
                    details: `Produto ${item.sku} tem apenas ${currentStock} unidades disponíveis, mas foram pedidas ${item.quantidade}`
                  });
                });
              }

              // INSERT para cada item individual na tabela 'itens_encomenda'
              db.query(
                'INSERT INTO itens_encomenda (id_encomenda, sku, quantidade, preco_unitario) VALUES (?, ?, ?, ?)',
                [orderId, item.sku, item.quantidade, item.preco_unitario],
                (err) => {
                  if (err) {
                    console.error('Erro ao inserir item:', err);
                    return db.rollback(() => {
                      res.status(500).json({ error: "Erro ao inserir item" });
                    });
                  }

                  // Atualizar stock da variação do produto
                  console.log(`📦 Atualizando stock para SKU: ${item.sku}, diminuindo ${item.quantidade} unidades (${currentStock} → ${currentStock - item.quantidade})`);

                  db.query(
                    'UPDATE variacoes_produto SET stock = stock - ? WHERE sku = ? AND stock >= ?',
                    [item.quantidade, item.sku, item.quantidade],
                    (err, updateResult) => {
                      if (err) {
                        console.error('Erro ao atualizar stock:', err);
                        return db.rollback(() => {
                          res.status(500).json({ error: "Erro ao atualizar stock" });
                        });
                      }

                      if (updateResult.affectedRows === 0) {
                        console.error(`❌ Falha ao atualizar stock para SKU: ${item.sku} - verificar se stock mudou durante a transação`);
                        return db.rollback(() => {
                          res.status(400).json({
                            error: "Falha ao atualizar stock",
                            details: `Não foi possível atualizar o stock para ${item.sku} - pode ter mudado durante a transação`
                          });
                        });
                      }

                      console.log(`✅ Stock atualizado com sucesso para SKU: ${item.sku} (${updateResult.affectedRows} linha(s) afetada(s))`);

                      // Processar próximo item
                      processarItem(index + 1);
                    }
                  );
                }
              );
            }
          );
        }

        // Iniciar processamento do primeiro item
        processarItem(0);
      }
    );
  });
});


// VERIFICAR STOCK DE UM SKU ESPECÍFICO (GET /api/stock/:sku)
// Esta rota é útil para debug e verificação de stock
app.get('/api/stock/:sku', (req, res) => {
  const { sku } = req.params;

  console.log(`🔍 Verificando stock para SKU: ${sku}`);

  const query = `
    SELECT 
      vp.sku,
      vp.stock,
      vp.codigo_base,
      vp.codigo_tamanho,
      vp.codigo_cor,
      p.nome as produto_nome,
      t.descricao as tamanho_descricao,
      c.descricao as cor_descricao
    FROM variacoes_produto vp
    LEFT JOIN produtos p ON vp.codigo_base = p.codigo_base
    LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
    LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor
    WHERE vp.sku = ?
  `;

  db.query(query, [sku], (err, results) => {
    if (err) {
      console.error('Erro ao verificar stock:', err);
      return res.status(500).json({ error: err.message });
    }

    if (results.length === 0) {
      console.log(`❌ SKU não encontrado: ${sku}`);
      return res.status(404).json({
        error: 'SKU não encontrado',
        sku: sku
      });
    }

    const result = results[0];
    console.log(`✅ Stock encontrado:`, result);

    res.json({
      sku: result.sku,
      stock: result.stock,
      produto: {
        codigo_base: result.codigo_base,
        nome: result.produto_nome,
        tamanho: {
          codigo: result.codigo_tamanho,
          descricao: result.tamanho_descricao
        },
        cor: {
          codigo: result.codigo_cor,
          descricao: result.cor_descricao
        }
      }
    });
  });
});

// LISTAR TODOS OS SKUS DISPONÍVEIS (GET /api/stock/list/all)
// Esta rota é útil para debug e ver todos os SKUs que existem
app.get('/api/stock/list/all', (req, res) => {
  console.log('📋 Listando todos os SKUs disponíveis');

  const query = `
    SELECT 
      vp.sku,
      vp.stock,
      vp.codigo_base,
      p.nome as produto_nome,
      t.descricao as tamanho_descricao,
      c.descricao as cor_descricao
    FROM variacoes_produto vp
    LEFT JOIN produtos p ON vp.codigo_base = p.codigo_base
    LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
    LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor
    ORDER BY vp.codigo_base, vp.codigo_tamanho, vp.codigo_cor
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Erro ao listar SKUs:', err);
      return res.status(500).json({ error: err.message });
    }

    console.log(`✅ Encontrados ${results.length} SKUs`);

    res.json({
      total: results.length,
      skus: results.map(item => ({
        sku: item.sku,
        stock: item.stock,
        produto_nome: item.produto_nome,
        tamanho: item.tamanho_descricao,
        cor: item.cor_descricao
      }))
    });
  });
});


app.get('/api/orders/:id', (req, res) => {

  console.log(`📦 Buscando detalhes da encomenda ID: ${req.params.id}, Utilizador ID: ${req.query.id_utilizador}`);


  const id_encomenda = req.params.id;          // ID da encomenda da URL
  const id_utilizador = req.query.id_utilizador; // ID do utilizador dos query params


  if (!id_encomenda || !id_utilizador) {
    console.log('❌ Erro: id_encomenda ou id_utilizador não fornecidos');
    // status(400): Bad Request - parâmetros obrigatórios em falta
    return res.status(400).json({ error: 'id_encomenda e id_utilizador são obrigatórios' });
  }


  const sql_encomenda = `
    SELECT e.id_encomenda,                                              -- ID único da encomenda
           DATE_FORMAT(e.data_encomenda, '%Y-%m-%d %H:%i:%s') as data_encomenda, -- Data formatada: YYYY-MM-DD HH:MM:SS
           e.status,                                                   -- Status da encomenda (pago, pendente, enviado, etc.)
           e.total,                                                    -- Valor total da encomenda
           e.local_envio,                                              -- Endereço de envio
           e.id_utilizador,                                            -- ID do cliente
           u.nome as nome_utilizador,                                  -- Nome do cliente
           u.email                                                     -- Email do cliente
    FROM encomendas e                                                   -- Tabela principal (alias 'e')
    INNER JOIN utilizadores u ON e.id_utilizador = u.id_utilizador     -- JOIN: combina encomenda com dados do cliente
    WHERE e.id_encomenda = ? AND e.id_utilizador = ?                   -- Filtros: ID da encomenda E cliente específico
  `;


  console.log('🔍 Executando SQL para buscar encomenda:', sql_encomenda);


  db.query(sql_encomenda, [id_encomenda, id_utilizador], (err, encomendaResult) => {

    if (err) {
      console.error('❌ Erro ao buscar encomenda:', err);
      return res.status(500).json({ error: 'Erro ao buscar encomenda' });
    }


    console.log(`📊 Resultado da busca da encomenda: ${JSON.stringify(encomendaResult)}`);


    if (!encomendaResult || encomendaResult.length === 0) {
      console.log('❌ Encomenda não encontrada');
      // status(404): Not Found - recurso não existe ou não pertence ao utilizador
      return res.status(404).json({ error: 'Encomenda não encontrada' });
    }


    const encomenda = encomendaResult[0];
    console.log('✅ Encomenda encontrada:', JSON.stringify(encomenda));


    const sql_itens = `
      SELECT ie.*,                                        -- Todos os campos de itens_encomenda
             p.nome as nome_produto,                      -- Nome do produto
             p.imagem,                                    -- Caminho da imagem do produto
             vp.sku,                                      -- SKU (código único da variação)
             p.codigo_base,                               -- Código base do produto
             c.descricao as cor,                          -- Descrição da cor
             t.descricao as tamanho                       -- Descrição do tamanho
      FROM itens_encomenda ie                             -- Tabela principal dos itens
      INNER JOIN variacoes_produto vp ON ie.sku = vp.sku -- JOIN: liga item à sua variação via SKU
      INNER JOIN produtos p ON vp.codigo_base = p.codigo_base -- JOIN: liga variação ao produto base
      LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor  -- LEFT JOIN: cor pode ser NULL
      LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho -- LEFT JOIN: tamanho pode ser NULL
      WHERE ie.id_encomenda = ?                           -- Filtra pelos itens desta encomenda específica
    `;


    console.log('🔍 Executando SQL para buscar itens:', sql_itens);
    console.log('🔍 Parâmetros da query itens:', id_encomenda);


    db.query(sql_itens, [id_encomenda], (err, itensResult) => {

      if (err) {
        console.error('❌ Erro ao buscar itens da encomenda:', err);
        return res.status(500).json({ error: 'Erro ao buscar itens da encomenda' });
      }


      console.log(`📊 Itens encontrados: ${itensResult ? itensResult.length : 0}`);


      if (itensResult && itensResult.length > 0) {
        console.log('📦 Amostra do primeiro item:', JSON.stringify(itensResult[0]));


        const totalValue = parseFloat(encomenda.total) || 0;


        const response = {
          ...encomenda,           // Copia: id_encomenda, data_encomenda, status, etc.
          itens: itensResult || [], // Adiciona array de itens (ou array vazio se null)
          total: totalValue       // Substitui total string por número
        };

        console.log('✅ Retornando resposta com itens encontrados');
        console.log(`💰 Valor total original: ${encomenda.total} (${typeof encomenda.total}), convertido para: ${totalValue} (${typeof totalValue})`);
        // res.json(): serializa objeto JavaScript para JSON e envia resposta HTTP
        res.json(response);
      } else {

        console.log('⚠️ Nenhum item encontrado em itens_encomenda, verificando items_encomenda...');

        // Query idêntica mas com nome de tabela diferente
        const sql_items_alternativo = `
          SELECT ie.*, p.nome as nome_produto, p.imagem, vp.sku, p.codigo_base, c.descricao as cor, t.descricao as tamanho
          FROM items_encomenda ie                             -- Tabela alternativa: 'items_encomenda' vs 'itens_encomenda'
          INNER JOIN variacoes_produto vp ON ie.sku = vp.sku
          INNER JOIN produtos p ON vp.codigo_base = p.codigo_base
          LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor
          LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
          WHERE ie.id_encomenda = ?
        `;


        db.query(sql_items_alternativo, [id_encomenda], (err, itemsResult) => {
          if (err) {
            // Se a query alternativa também falhar, retorna encomenda sem itens
            console.error('❌ Erro ao buscar items_encomenda alternativo:', err);
            const response = {
              ...encomenda,
              itens: []  // Array vazio - encomenda existe mas sem itens
            };
            console.log('⚠️ Retornando resposta sem itens (erro na busca alternativa)');
            res.json(response);
          } else {
            // Logging dos resultados da tabela alternativa
            console.log(`📊 Itens encontrados na tabela alternativa: ${itemsResult ? itemsResult.length : 0}`);


            if (itemsResult && itemsResult.length > 0) {
              console.log('🔄 Usando itens da tabela alternativa items_encomenda');
              console.log('📦 Amostra do primeiro item alternativo:', JSON.stringify(itemsResult[0]));

              const response = {
                ...encomenda,                              // Dados da encomenda principal
                itens: itemsResult,                        // Itens da tabela alternativa
                total: parseFloat(encomenda.total) || 0    // Total convertido para número
              };

              console.log('✅ Retornando resposta com itens alternativos');
              res.json(response);
            } else {
              // ========================================
              // CENÁRIO 2B: NENHUM ITEM ENCONTRADO EM NENHUMA TABELA
              // ========================================
              const response = {
                ...encomenda,                              // Dados da encomenda principal
                itens: [],                                 // Array vazio - sem itens
                total: parseFloat(encomenda.total) || 0    // Total convertido para número
              };

              console.log('⚠️ Retornando resposta sem itens (não encontrados em nenhuma tabela)');
              res.json(response);
            }
          }
        });
      }
    });
  });
});


app.get('/api/orders', (req, res) => {

  const id_utilizador = req.query.id_utilizador;


  if (!id_utilizador) {
    // HTTP 400 Bad Request - ID do utilizador é obrigatório para filtrar as encomendas
    return res.status(400).json({ error: 'ID do utilizador é obrigatório' });
  }


  const query = `
    SELECT e.id_encomenda,                                        -- ID único da encomenda
           DATE_FORMAT(e.data_encomenda, '%Y-%m-%d %H:%i:%s') as data_encomenda,  -- Data formatada para legibilidade
           e.total,                                               -- Valor total da encomenda
           e.status,                                              -- Status atual (pago, enviado, etc.)
           e.local_envio,                                         -- Endereço de entrega
           ie.sku,                                                -- SKU do item comprado (ex: "LUVA001-M-AZUL")
           ie.quantidade,                                         -- Quantidade comprada deste item
           ie.preco_unitario,                                     -- Preço unitário na altura da compra
           
           -- ========================================
           -- EXTRAÇÃO INTELIGENTE DE PARTES DO SKU
           -- ========================================
           /*
            * O SKU tem formato: "CODIGO_BASE-TAMANHO-COR" (ex: "LUVA001-M-AZUL")
            * Usamos SUBSTRING_INDEX() para extrair cada parte:
            * 
            * SUBSTRING_INDEX(string, delimiter, count):
            * - string: o texto a dividir
            * - delimiter: caracter separador ('-')
            * - count: quantas partes extrair (positivo = da esquerda, negativo = da direita)
            */
           SUBSTRING_INDEX(ie.sku, '-', 3) as codigo_base,        -- Primeiras 3 partes (ou menos) = código base
           SUBSTRING_INDEX(SUBSTRING_INDEX(ie.sku, '-', 4), '-', -1) as codigo_tamanho,  -- 4ª parte = tamanho
           SUBSTRING_INDEX(ie.sku, '-', -1) as codigo_cor,        -- Última parte = cor
           
           -- Informações do produto
           p.nome as nome_produto,                                -- Nome do produto
           p.imagem,                                              -- Caminho da imagem
           t.descricao as tamanho,                               -- Descrição do tamanho (ex: "Médio")
           cor.descricao as cor                                  -- Descrição da cor (ex: "Azul")
           
    FROM encomendas e                                            -- Tabela principal de encomendas
    LEFT JOIN itens_encomenda ie ON e.id_encomenda = ie.id_encomenda     -- Junta com itens da encomenda
    LEFT JOIN variacoes_produto vp ON ie.sku = vp.sku           -- Junta com variações de produto
    LEFT JOIN produtos p ON SUBSTRING_INDEX(ie.sku, '-', 3) = p.codigo_base  -- Junta com produtos (usando código extraído)
    LEFT JOIN tamanhos t ON t.codigo_tamanho = SUBSTRING_INDEX(SUBSTRING_INDEX(ie.sku, '-', 4), '-', -1)  -- Junta tamanhos
    LEFT JOIN cores cor ON cor.codigo_cor = SUBSTRING_INDEX(ie.sku, '-', -1)  -- Junta cores
    WHERE e.id_utilizador = ?                                    -- Filtra apenas encomendas deste utilizador
    ORDER BY e.data_encomenda DESC                               -- Ordena por data (mais recentes primeiro)
  `;

  db.query(query, [id_utilizador], (err, results) => {
    if (err) {
      console.error('Erro ao buscar encomendas:', err);
      res.status(500).json({ error: err.message });
    } else {

      const orders = {};


      results.forEach(row => {

        if (!orders[row.id_encomenda]) {
          // Cria nova encomenda se ainda não existe no objeto orders
          orders[row.id_encomenda] = {
            id_encomenda: row.id_encomenda,                    // ID da encomenda
            data_encomenda: row.data_encomenda || null,        // Data (ou null se não houver)
            total: parseFloat(row.total || 0),                 // Total convertido para número
            status: row.status,                                // Status da encomenda
            local_envio: row.local_envio,                     // Endereço de entrega
            items: []                                          // Array vazio para os itens (será preenchido abaixo)
          };
        }


        if (row.sku) {
          orders[row.id_encomenda].items.push({
            sku: row.sku,                                      // SKU do item
            quantidade: parseInt(row.quantidade || 0),         // Quantidade convertida para número
            preco_unitario: parseFloat(row.preco_unitario || 0), // Preço convertido para número
            nome: row.nome_produto,                            // Nome do produto
            imagem: row.imagem,                               // Caminho da imagem
            tamanho: row.tamanho,                             // Descrição do tamanho
            cor: row.cor                                      // Descrição da cor
          });
        }
      }); // Fim do forEach

      Object.values(orders).forEach(order => {
        if (!order.items) order.items = [];  // Se não tem items, cria array vazio
      });


      res.json(Object.values(orders));
    } // Fim do else (sem erro)
  }); // Fim da db.query
}); // Fim da rota GET /api/orders


app.put('/api/orders/:id/status', async (req, res) => {

  const connection = await db.promise().getConnection();


  try {

    const { id } = req.params;
    const { status } = req.body;

    if (!['pendente', 'pago', 'enviado', 'cancelado'].includes(status)) {
      // HTTP 400 Bad Request - valor inválido fornecido
      return res.status(400).json({ error: 'Status inválido' });
    }


    await connection.query(
      'UPDATE encomendas SET status = ? WHERE id_encomenda = ?',
      [status, id]
    );


    res.json({
      success: true,                              // Flag booleana de sucesso
      message: 'Status da encomenda atualizado com sucesso'  // Mensagem descritiva
    });

  } catch (error) {

    console.error('Erro ao atualizar status:', error);

    // HTTP 500 Internal Server Error - erro interno do servidor
    res.status(500).json({
      error: "Erro ao atualizar status",    // Mensagem genérica para o utilizador
      message: error.message               // Mensagem técnica detalhada
    });
  } finally {

    connection.release();
  }
});
app.on('error', (err) => {

  console.error('Erro no servidor:', err);
});


process.on('uncaughtException', (err) => {
  console.error('Uncaught Exception:', err);
  // err.stack - stack trace completo do erro (mostra onde aconteceu)
  // Essencial para debugging - mostra sequência de chamadas de funções
  console.error('Stack trace:', err.stack);
  // NOTA: Em produção, deveria fazer graceful shutdown após uncaughtException
  // process.exit(1); // Termina processo após registar erro
});


process.on('unhandledRejection', (err) => {
  console.error('Unhandled Rejection:', err);

  if (err instanceof Error) {
    console.error('Stack trace:', err.stack);
  }
});


app.use('/api/payments', stripeRoutes);


const PORT = process.env.PORT || 8080;


app.listen(PORT, () => {

  console.log(`Servidor a correr na porta ${PORT}`);

});


app.post('/api/favorites', (req, res) => {

  const { id_utilizador, codigo_base } = req.body;


  if (!id_utilizador || !codigo_base) {
    // HTTP 400 Bad Request - dados obrigatórios em falta
    return res.status(400).json({
      success: false,
      message: 'ID do utilizador e código base são obrigatórios'
    });
  }


  db.query(
    'SELECT * FROM gostos WHERE id_utilizador = ? AND codigo_base = ?',  // Query para verificar existência
    [id_utilizador, codigo_base],  // Array com valores para substituir os ? (prepared statement)
    (err, results) => {           // Callback executado quando a verificação termina
      // Se houve erro na query de verificação
      if (err) {
        console.error('Erro ao verificar favorito:', err);
        // HTTP 500 Internal Server Error - problema no servidor/base de dados
        return res.status(500).json({
          success: false,
          message: 'Erro no servidor'
        });
      }


      if (results.length > 0) {
        // HTTP 409 Conflict - recurso já existe (produto já está nos favoritos)
        // success: false - indica que a operação não teve sucesso
        return res.status(409).json({ success: false, message: 'Produto já está nos favoritos' });
      }


      db.query(
        'INSERT INTO gostos (id_utilizador, codigo_base) VALUES (?, ?)',  // Query SQL para inserir novo favorito
        [id_utilizador, codigo_base],  // Array com valores para substituir os ? (prepared statement)
        (err, result) => {            // Callback executado quando a inserção termina
          // Se houve erro na inserção
          if (err) {
            console.error('Erro ao adicionar favorito:', err);
            // HTTP 500 Internal Server Error - problema no servidor/base de dados
            return res.status(500).json({ success: false, message: 'Erro ao adicionar favorito' });
          }


          console.log(`✅ Produto ${codigo_base} adicionado aos favoritos do utilizador ${id_utilizador}`);

          // Resposta de sucesso para o cliente
          res.json({
            success: true,                              // Flag booleana de sucesso
            message: 'Produto adicionado aos favoritos'  // Mensagem descritiva
          });
        }
      ); // Fim da query INSERT
    } // Fim do callback da query SELECT
  ); // Fim da query SELECT principal
}); // Fim da rota POST /api/favorites


app.delete('/api/favorites', (req, res) => {
  // Extrai dados do corpo da requisição HTTP
  // Usa destructuring assignment para obter ambos os campos de uma vez
  const { id_utilizador, codigo_base } = req.body;

  if (!id_utilizador || !codigo_base) {
    // HTTP 400 Bad Request - parâmetros obrigatórios em falta
    return res.status(400).json({
      success: false,
      message: 'ID do utilizador e código base são obrigatórios'
    });
  }


  db.query(
    'DELETE FROM gostos WHERE id_utilizador = ? AND codigo_base = ?',
    [id_utilizador, codigo_base],  // Array com valores para substituir os ? (segurança)
    (err, result) => {            // Callback executado quando a eliminação termina
      // Se houve erro na query DELETE
      if (err) {
        console.error('Erro ao remover favorito:', err);
        // HTTP 500 Internal Server Error - problema no servidor/base de dados
        return res.status(500).json({
          success: false,
          message: 'Erro no servidor'
        });
      }


      if (result.affectedRows === 0) {
        // HTTP 404 Not Found - o recurso a eliminar não foi encontrado
        return res.status(404).json({
          success: false,
          message: 'Favorito não encontrado'
        });
      }


      console.log(`✅ Produto ${codigo_base} removido dos favoritos do utilizador ${id_utilizador}`);

      // Resposta de sucesso para o cliente
      res.json({
        success: true,                               // Flag booleana de sucesso
        message: 'Produto removido dos favoritos'    // Mensagem descritiva
      });
    }
  ); // Fim da query DELETE
}); // Fim da rota DELETE /api/favorites


app.get('/api/favorites/:id_utilizador', (req, res) => {

  const { id_utilizador } = req.params;


  if (!id_utilizador) {
    // HTTP 400 Bad Request - parâmetro obrigatório em falta
    return res.status(400).json({ error: 'ID do utilizador é obrigatório' });
  }


  const query = `
    SELECT g.*,                                               -- Todos os campos da tabela gostos (id_gosto, data_gosto, etc.)
           p.nome,                                            -- Nome do produto (ex: "Luvas de Boxe Premium")
           p.preco,                                           -- Preço do produto
           p.imagem,                                          -- Caminho para a imagem do produto
           p.descricao,                                       -- Descrição detalhada do produto
           m.nome as marca_nome,                              -- Nome da marca (ex: "Everlast")
           c.nome as categoria_nome                           -- Nome da categoria (ex: "Luvas")
    FROM gostos g                                             -- Tabela principal de favoritos
    LEFT JOIN produtos p ON g.codigo_base = p.codigo_base    -- Junta com dados do produto
    LEFT JOIN marcas m ON p.id_marca = m.id_marca            -- Junta com marca do produto
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria  -- Junta com categoria do produto
    WHERE g.id_utilizador = ?                                 -- Filtra apenas favoritos deste utilizador
    ORDER BY g.data_gosto DESC                                -- Ordena por data (favoritos mais recentes primeiro)
  `;

  db.query(query, [id_utilizador], (err, results) => {
    // Se houve erro na execução da query
    if (err) {
      console.error('Erro ao buscar favoritos:', err);
      // HTTP 500 Internal Server Error - problema no servidor/base de dados
      return res.status(500).json({ error: 'Erro no servidor' });
    }


    console.log(`📋 Encontrados ${results.length} favoritos para utilizador ${id_utilizador}`);


    res.json(results);
  });
}); // Fim da rota GET /api/favorites/:id_utilizador


app.get('/api/favorites/:id_utilizador/check/:codigo_base', (req, res) => {

  const { id_utilizador, codigo_base } = req.params;


  db.query(
    'SELECT COUNT(*) as count FROM gostos WHERE id_utilizador = ? AND codigo_base = ?',
    [id_utilizador, codigo_base],  // Array com valores para substituir os ? (prepared statement)
    (err, results) => {           // Callback executado quando a query termina
      // Se houve erro na execução da query
      if (err) {
        console.error('Erro ao verificar favorito:', err);
        // HTTP 500 Internal Server Error - problema no servidor/base de dados
        return res.status(500).json({ error: 'Erro no servidor' });
      }


      const isFavorite = results[0].count > 0;


      res.json({ isFavorite });
    }
  ); // Fim da query COUNT
}); // Fim da rota GET /api/favorites/:id_utilizador/check/:codigo_base

// VERIFICAR STOCK DE UMA VARIAÇÃO (GET /api/stock/:sku)
app.get('/api/stock/:sku', (req, res) => {
  const { sku } = req.params;

  const query = `
    SELECT vp.sku, vp.stock, p.nome as produto_nome, 
           c.descricao as cor, t.descricao as tamanho
    FROM variacoes_produto vp
    JOIN produtos p ON vp.codigo_base = p.codigo_base
    LEFT JOIN cores c ON vp.codigo_cor = c.codigo_cor
    LEFT JOIN tamanhos t ON vp.codigo_tamanho = t.codigo_tamanho
    WHERE vp.sku = ?
  `;

  db.query(query, [sku], (err, results) => {
    if (err) {
      console.error('Erro ao buscar stock:', err);
      res.status(500).json({ error: err.message });
    } else if (results.length === 0) {
      res.status(404).json({ error: 'Variação não encontrada' });
    } else {
      res.json(results[0]);
    }
  });
});

