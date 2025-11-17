-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05-Jun-2025 às 17:16
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `boxingforlife`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `blog`
--

CREATE TABLE `blog` (
  `id_post` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `conteudo` text NOT NULL,
  `imagem` varchar(500) DEFAULT NULL,
  `data_publicacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_utilizador` int(11) NOT NULL,
  `resumo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `blog`
--

INSERT INTO `blog` (`id_post`, `titulo`, `conteudo`, `imagem`, `data_publicacao`, `id_utilizador`, `resumo`) VALUES
(8, 'Como Escolher o Equipamento de Boxe Ideal', 'Escolher o equipamento certo é essencial para garantir segurança e desempenho no boxe. Desde luvas até protetores bucais, cada item deve ser seleccionado com base no seu nível de habilidade e necessidades específicas.\r\n\r\n**1. Luvas de Boxe**\r\nAs luvas são o equipamento mais importante para qualquer pugilista. Ao escolher luvas, considere:\r\n- **Peso**: As luvas variam de 8 oz a 16 oz. Para iniciantes, recomenda-se usar luvas de 10 oz a 12 oz.\r\n- **Material**: Opte por luvas feitas de couro ou materiais sintéticos de alta qualidade para maior durabilidade.\r\n- **Ajuste**: Certifique-se de que as luvas se ajustem confortavelmente às suas mãos sem apertar demasiado.\r\n\r\n**2. Protetores Bucais**\r\nOs protetores bucais são essenciais para evitar lesões nos dentes e na mandíbula. Escolha protetores ajustáveis ou moldáveis para garantir um ajuste perfeito.\r\n\r\n**3. Bandagens**\r\nAs bandagens protegem os pulsos e as articulações durante o treino. Opte por bandagens de algodão ou elastano, com comprimentos entre 2,5 m e 4 m.\r\n\r\n**4. Saco de Pancada**\r\nSe você treina em casa, um saco de pancada é uma óptima adição. Escolha um saco com peso adequado à sua força (geralmente entre 30 kg e 70 kg).\r\n\r\nCom estas dicas, estará bem equipado para praticar boxe com segurança e eficiência.', 'imagens/blog/8825899.avif', '2023-10-04 22:00:00', 2, 'Saiba como escolher o equipamento de boxe ideal para iniciantes e profissionais.'),
(9, 'Treino de Força para Pugilistas: Exercícios Essenciais', 'O treino de força é fundamental para pugilistas que desejam melhorar a sua potência e resistência. Neste artigo, apresentamos uma lista de exercícios essenciais que todo pugilista deve incluir na sua rotina de treino.\r\n\r\n**1. Agachamentos (Squats)**\r\nOs agachamentos trabalham os músculos das pernas e glúteos, essenciais para a estabilidade e explosão durante o combate. Execute 3 séries de 10 repetições, aumentando gradualmente o peso.\r\n\r\n**2. Levantamento Terra (Deadlifts)**\r\nEste exercício fortalece a parte inferior das costas, os glúteos e as pernas. É ideal para melhorar a postura e a força geral. Comece com um peso leve e aumente conforme ganha confiança.\r\n\r\n**3. Flexões (Push-Ups)**\r\nAs flexões são excelentes para fortalecer o peito, ombros e tríceps. Experimente variações como flexões diamante ou com apoio elevado para aumentar a dificuldade.\r\n\r\n**4. Peso Morto com Halteres**\r\nEste exercício melhora a força funcional e a coordenação. Use halteres leves no início e aumente o peso conforme progride.\r\n\r\n**5. Plank**\r\nO plank é um exercício isométrico que fortalece o core, essencial para manter a estabilidade durante o boxe. Mantenha a posição por 30 segundos a 1 minuto, aumentando gradualmente o tempo.\r\n\r\nIncorporar estes exercícios ao seu treino ajudará a melhorar o seu desempenho no ringue e reduzir o risco de lesões.', 'imagens/blog/53474350119_0edce7d2d9_h-1030x687.jpg', '2023-10-09 22:00:00', 3, 'Confira exercícios de força que todo pugilista deve incluir na sua rotina de treino.'),
(10, 'A Importância da Nutrição no Boxe', 'A nutrição desempenha um papel crucial no desempenho de qualquer atleta, especialmente no boxe. Uma dieta equilibrada, rica em proteínas, hidratos de carbono e gorduras saudáveis, é essencial para manter a energia e acelerar a recuperação.\r\n\r\n**1. Proteínas**\r\nAs proteínas são fundamentais para a construção e reparação muscular. Inclua fontes de proteína magra na sua dieta, como frango, peixe, ovos e leguminosas.\r\n\r\n**2. Hidratos de Carbono**\r\nOs hidratos de carbono fornecem energia para os treinos intensos. Opte por hidratos de carbono complexos, como arroz integral, aveia e batata-doce, para manter os níveis de energia estáveis.\r\n\r\n**3. Gorduras Saudáveis**\r\nAs gorduras saudáveis, encontradas em alimentos como abacate, nozes e azeite, são importantes para a saúde cardiovascular e a absorção de vitaminas.\r\n\r\n**4. Hidratação**\r\nManter-se hidratado é essencial para o desempenho e a recuperação. Beba água regularmente ao longo do dia e considere bebidas isotónicas durante treinos intensos.\r\n\r\n**5. Suplementos**\r\nEmbora uma dieta equilibrada seja suficiente para a maioria dos atletas, alguns suplementos, como whey protein e creatina, podem ser úteis para complementar a nutrição.\r\n\r\nPlanear uma dieta adequada é tão importante quanto o treino físico. Com uma nutrição correcta, maximizará o seu desempenho e alcançará os seus objectivos no boxe.', 'imagens/blog/blog-boxing-nutrition.webp', '2023-10-14 22:00:00', 2, 'Entenda por que a nutrição é tão importante para pugilistas e como planear uma dieta adequada.'),
(11, 'História do Boxe: De Desporto Antigo a Fenómeno Moderno', 'O boxe tem uma longa e fascinante história, remontando a civilizações antigas como o Egipto e a Grécia. Ao longo dos séculos, o desporto evoluiu, ganhando popularidade global e tornando-se num dos desportos mais praticados no mundo.\r\n\r\n**1. Origens Antigas**\r\nO boxe nasceu como uma forma de combate corporal em civilizações antigas. Na Grécia Antiga, era praticado como parte dos Jogos Olímpicos, com regras simples e pouca proteção.\r\n\r\n**2. Evolução nas Regras**\r\nNo século XVIII, o boxe começou a ganhar forma moderna na Inglaterra, com a introdução das regras do Marquês de Queensberry. Essas regras padronizaram o uso de luvas e definiram o formato das lutas.\r\n\r\n**3. Popularidade Global**\r\nNo século XX, o boxe tornou-se num fenómeno global, com ícones como Muhammad Ali, Mike Tyson e Floyd Mayweather Jr. a conquistarem milhões de fãs em todo o mundo.\r\n\r\n**4. Boxe Hoje**\r\nActualmente, o boxe é praticado tanto como desporto profissional quanto como actividade recreativa. Ele continua a evoluir, com novas técnicas e estratégias a serem desenvolvidas constantemente.\r\n\r\nExplorar a história do boxe ajuda-nos a entender a sua importância cultural e desportiva. Seja como passatempo ou carreira, o boxe continua a inspirar gerações.', 'imagens/blog/2333.jpg', '2023-10-19 22:00:00', 3, 'Explore a história do boxe, desde as suas origens até aos dias actuais.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `carrinho`
--

CREATE TABLE `carrinho` (
  `id_carrinho` int(11) NOT NULL,
  `id_utilizador` int(11) DEFAULT NULL,
  `session_id` varchar(500) DEFAULT NULL,
  `sku` varchar(500) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `data_adicao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `carrinho`
--

INSERT INTO `carrinho` (`id_carrinho`, `id_utilizador`, `session_id`, `sku`, `quantidade`, `data_adicao`) VALUES
(19, 5, NULL, '1002-9001-3002-4005-8000', 1, '2025-06-05 14:22:35'),
(21, 4, NULL, '1012-9001-3007-4011-8000', 1, '2025-06-05 15:14:23');

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nome` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nome`) VALUES
(3001, 'Luvas'),
(3002, 'Sacos'),
(3003, 'Protetores bocais'),
(3004, 'Ligaduras'),
(3005, 'Capacetes'),
(3006, 'Sapatilhas de treino'),
(3007, 'Roupa'),
(3008, 'Acessórios');

-- --------------------------------------------------------

--
-- Estrutura da tabela `comentarios_blog`
--

CREATE TABLE `comentarios_blog` (
  `id_comentario` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `id_utilizador` int(11) NOT NULL,
  `conteudo` text NOT NULL,
  `data_comentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `comentarios_blog`
--

INSERT INTO `comentarios_blog` (`id_comentario`, `id_post`, `id_utilizador`, `conteudo`, `data_comentario`) VALUES
(2, 11, 4, '123', '2025-06-05 09:03:35'),
(4, 10, 5, 'Adorei este blog', '2025-06-05 14:39:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cores`
--

CREATE TABLE `cores` (
  `codigo_cor` varchar(4) NOT NULL,
  `descricao` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `cores`
--

INSERT INTO `cores` (`codigo_cor`, `descricao`) VALUES
('8000', 'Preto'),
('8001', 'Branco'),
('8002', 'Azul'),
('8003', 'Vermelho'),
('8004', 'Cinza'),
('8005', 'Amarelo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `encomendas`
--

CREATE TABLE `encomendas` (
  `id_encomenda` int(11) NOT NULL,
  `id_utilizador` int(11) NOT NULL,
  `data_encomenda` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `status` enum('pendente','pago','enviado','cancelado') DEFAULT 'pago',
  `local_envio` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `encomendas`
--

INSERT INTO `encomendas` (`id_encomenda`, `id_utilizador`, `data_encomenda`, `total`, `status`, `local_envio`) VALUES
(4, 4, '2025-06-05 09:09:13', 459.90, 'cancelado', NULL),
(5, 4, '2025-06-05 09:10:43', 459.90, 'cancelado', NULL),
(6, 4, '2025-06-05 09:13:11', 459.90, 'cancelado', NULL),
(7, 4, '2025-06-05 09:38:08', 459.90, 'pago', 'Rua do Sol, 321'),
(8, 4, '2025-06-05 09:51:57', 299.95, 'cancelado', NULL),
(9, 4, '2025-06-05 09:52:59', 299.95, 'pago', 'Rua do Sol, 321'),
(10, 4, '2025-06-05 09:54:15', 24.99, 'pago', 'Rua do Sol, 321'),
(11, 4, '2025-06-05 09:57:47', 12.99, 'pago', 'Rua do Sol, 321'),
(12, 4, '2025-06-05 13:25:34', 128.96, 'pendente', NULL),
(13, 4, '2025-06-05 13:28:36', 128.96, 'pendente', NULL),
(14, 4, '2025-06-05 13:28:38', 128.96, 'pendente', NULL),
(15, 4, '2025-06-05 13:28:41', 128.96, 'pendente', NULL),
(16, 4, '2025-06-05 13:29:28', 128.96, 'pendente', NULL),
(17, 4, '2025-06-05 13:31:07', 128.96, 'pago', 'Rua do Sol, 321'),
(18, 4, '2025-06-05 15:48:23', 49.99, 'pago', 'Rua do Sol, 321');

-- --------------------------------------------------------

--
-- Estrutura da tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id_fornecedor` int(11) NOT NULL,
  `nome` varchar(500) NOT NULL,
  `contato` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id_fornecedor`, `nome`, `contato`) VALUES
(1, 'Everlast Oficial', '');

-- --------------------------------------------------------

--
-- Estrutura da tabela `gostos`
--

CREATE TABLE `gostos` (
  `id_gosto` int(11) NOT NULL,
  `id_utilizador` int(11) NOT NULL,
  `codigo_base` varchar(500) NOT NULL,
  `data_gosto` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `gostos`
--

INSERT INTO `gostos` (`id_gosto`, `id_utilizador`, `codigo_base`, `data_gosto`) VALUES
(1, 5, '1002', '2025-03-16 01:21:23'),
(3, 4, '1005', '2025-04-07 09:05:19'),
(5, 4, '1002', '2025-06-02 07:22:19'),
(7, 4, '1003', '2025-06-03 15:24:38'),
(8, 5, '1004', '2025-06-04 08:35:25'),
(9, 4, '1004-9003-3003', '2025-06-04 14:54:25'),
(10, 4, '1012-9001-3007', '2025-06-05 13:27:26'),
(11, 4, '1007-9002-3004', '2025-06-05 13:28:24'),
(12, 4, '1003-9002-3002', '2025-06-05 13:30:58'),
(13, 4, '1010-9003-3006', '2025-06-05 13:32:22'),
(14, 4, '1005-9004-3003', '2025-06-05 13:34:19'),
(20, 5, '1002-9001-3002', '2025-06-05 14:43:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_encomenda`
--

CREATE TABLE `itens_encomenda` (
  `id_item` int(11) NOT NULL,
  `id_encomenda` int(11) NOT NULL,
  `sku` varchar(500) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `itens_encomenda`
--

INSERT INTO `itens_encomenda` (`id_item`, `id_encomenda`, `sku`, `quantidade`, `preco_unitario`) VALUES
(1, 4, '1001-9002-3001/3001/9002/8001/4003', 6, 59.99),
(2, 7, '1001-9002-3001/3001/9002/8001/4003', 6, 59.99),
(3, 7, '1005-9004-3003/3003/9004/8002/4015', 4, 24.99),
(4, 8, '1001-9002-3001-4002-8001', 5, 59.99),
(5, 9, '1001-9002-3001-4002-8001', 5, 59.99),
(6, 10, '1005-9004-3003/3003/9004/8001/4015', 1, 24.99),
(7, 11, '1014-9001-3008/3008/9001/8002/4021', 1, 12.99),
(8, 17, '1014-9001-3008-4022-8000', 3, 12.99),
(9, 17, '1002-9001-3002-4005-8001', 1, 89.99),
(10, 18, '1000-9001-3001-4002-8000', 1, 49.99);

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL,
  `nome` varchar(500) NOT NULL,
  `imagem` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nome`, `imagem`) VALUES
(9001, 'Everlast', 'imagens/marcas/Everlast-Logo-1978-present.png'),
(9002, 'Nike', 'imagens/marcas/002-nike-logos-swoosh-white.jpg'),
(9003, 'Adidas', 'imagens/marcas/Adidas_Logo.svg'),
(9005, 'Under Armour', 'imagens/marcas/Under_armour_logo.svg.png');

-- --------------------------------------------------------

--
-- Estrutura da tabela `nivel_acesso`
--

CREATE TABLE `nivel_acesso` (
  `id_nivel` int(11) NOT NULL,
  `descricao` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `nivel_acesso`
--

INSERT INTO `nivel_acesso` (`id_nivel`, `descricao`) VALUES
(1, 'Cliente'),
(2, 'Colaborador'),
(9, 'Administrador');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `codigo_base` varchar(500) NOT NULL,
  `nome` varchar(500) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`codigo_base`, `nome`, `id_marca`, `id_fornecedor`, `id_categoria`, `preco`, `descricao`, `imagem`) VALUES
('1000-9001-3001', 'Luvas Everlast Pro', 9001, 1, 3001, 49.99, 'Luvas de boxe profissionais da Everlast.', 'imagens/produtos/1000/everlast-boxing-gloves-elite-2-black-gold-12-oz.jpg'),
('1001-9002-3001', 'Luvas Adidas Combat', 9003, 1, 3001, 59.99, 'Luvas de boxe Adidas, ideais para competições.', 'imagens/produtos/1001/picture.jpg'),
('1002-9001-3002', 'Saco de Pancada Everlast', 9001, 1, 3002, 89.99, 'Saco de pancada resistente da Everlast.', 'imagens/produtos/1002/06ad0ba1c5e28963f6f50084d355ebfd37d3f2b3.webp'),
('1003-9002-3002', 'Saco de Pancada Adidas', 9003, 1, 3002, 99.99, 'Saco de pancada durável da Adidas.', 'imagens/produtos/1003/6ea5cdff0003fa2ec9fe5cfeed33ef6d19f090aa_adibag11_20kg_1200x.webp'),
('1004-9003-3003', 'Protetor Bucal Nike', 9002, 1, 3003, 19.99, 'Protetor bucal confortável da Nike.', 'imagens/produtos/1004/fd6006-010_01_2.webp'),
('1005-9004-3003', 'Protetor Bucal Under Armour', 9005, 1, 3003, 24.99, 'Protetor bucal ajustável da Under Armour.', 'imagens/produtos/1005/61BYWpDeuwL.jpg'),
('1006-9001-3004', 'Bandagens Everlast', 9001, 1, 3004, 14.99, 'Bandagens de boxe de alta qualidade da Everlast.', 'imagens/produtos/1006/341984.webp'),
('1007-9002-3004', 'Bandagens Adidas', 9003, 1, 3004, 16.99, 'Bandagens de boxe leves e resistentes da Adidas.', 'imagens/produtos/1007/Y1_0cdab306-9cdf-4206-ad8d-f90879706de1.webp'),
('1008-9001-3005', 'Capacete Everlast Pro', 9001, 1, 3005, 79.99, 'Capacete de boxe profissional da Everlast.', 'imagens/produtos/1008/76217503_o.webp'),
('1009-9002-3005', 'Capacete Adidas Elite', 9003, 1, 3005, 89.99, 'Capacete de boxe premium da Adidas.', 'imagens/produtos/1009/protector-boxeo_706e4e7d8b4740ea8c3be2328b6d50b4_2203000495.jpg'),
('1010-9003-3006', 'Tênis Nike Training', 9002, 1, 3006, 109.99, 'Tênis de treino leve e confortável da Nike.', 'imagens/produtos/1010/nike-hyperko-2-boxing-shoes.webp'),
('1011-9002-3006', 'Tênis Adidas Performance', 9003, 1, 3006, 119.99, 'Tênis de desempenho para treinos intensos da Adidas.', 'imagens/produtos/1011/1-1.png'),
('1012-9001-3007', 'Camiseta Everlast DryFit', 9001, 1, 3007, 29.99, 'Camiseta de treino respirável da Everlast.', 'imagens/produtos/1012/S51af754f230c400189bc063428fdf4ddw.avif'),
('1013-9002-3007', 'Shorts Adidas Training', 9003, 1, 3007, 39.99, 'Shorts de treino confortável da Adidas.', 'imagens/produtos/1013/calcoes-de-treino-entrada-22.avif'),
('1014-9001-3008', 'Corda de Pular Everlast', 9001, 1, 3008, 12.99, 'Corda de pular ajustável da Everlast.', 'imagens/produtos/1014/soga-para-saltar-training-evjr1y526-everlast.webp'),
('1015-9002-3008', 'Timer de Treino Adidas', 9003, 1, 3008, 29.99, 'Timer digital para treinos de boxe da Adidas.', 'imagens/produtos/1015/relogio_adidas_performance_digital_38947_1_20210806234027.webp');

-- --------------------------------------------------------

--
-- Estrutura da tabela `reviews_encomendas`
--

CREATE TABLE `reviews_encomendas` (
  `id_review` int(11) NOT NULL,
  `id_encomenda` int(11) DEFAULT NULL,
  `id_utilizador` int(11) DEFAULT NULL,
  `classificacao` int(11) DEFAULT NULL CHECK (`classificacao` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `data_review` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `reviews_encomendas`
--

INSERT INTO `reviews_encomendas` (`id_review`, `id_encomenda`, `id_utilizador`, `classificacao`, `comentario`, `data_review`) VALUES
(1, 5, 5, 5, 'adorei esta expriencia', '2025-03-20 16:29:19'),
(2, 4, 5, 1, 'odeiei esta expriencia', '2025-03-20 17:07:48'),
(3, 12, 4, 2, 'quero é ver outra coisa', '2025-04-08 12:07:40'),
(4, 23, 4, 2, 'Mais o menos', '2025-05-09 08:37:24'),
(5, 32, 5, 5, '123456789', '2025-06-04 09:41:44'),
(6, 17, 4, 4, 'RAYMOND', '2025-06-05 12:32:49');

-- --------------------------------------------------------

--
-- Estrutura da tabela `reviews_produtos`
--

CREATE TABLE `reviews_produtos` (
  `id_review` int(11) NOT NULL,
  `id_encomenda` int(11) DEFAULT NULL,
  `codigo_base` varchar(500) DEFAULT NULL,
  `id_utilizador` int(11) DEFAULT NULL,
  `classificacao` int(11) DEFAULT NULL CHECK (`classificacao` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `data_review` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `reviews_produtos`
--

INSERT INTO `reviews_produtos` (`id_review`, `id_encomenda`, `codigo_base`, `id_utilizador`, `classificacao`, `comentario`, `data_review`) VALUES
(1, 5, '1001-9002-3001', 5, 5, 'adorei o produto', '2025-03-20 16:25:08'),
(2, 12, '1001-9002-3001', 4, 5, 'uipiiiiii', '2025-04-08 12:14:49'),
(3, 15, '1001-9002-3001', 5, 5, 'gostei imenso deste produto', '2025-05-23 13:41:04'),
(4, 18, '1004-9003-3003', 4, 4, 'Gostei, estava quase perfeita.', '2025-06-03 15:07:04'),
(5, 17, '1014-9001-3008', 4, 5, 'Rymond', '2025-06-05 12:32:59');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tamanhos`
--

CREATE TABLE `tamanhos` (
  `codigo_tamanho` varchar(500) NOT NULL,
  `descricao` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `tamanhos`
--

INSERT INTO `tamanhos` (`codigo_tamanho`, `descricao`) VALUES
('4000', '8 oz'),
('4001', '10 oz'),
('4002', '12 oz'),
('4003', '14 oz'),
('4004', '16 oz'),
('4005', '30 kg'),
('4006', '40 kg'),
('4007', '50 kg'),
('4008', '60 kg'),
('4009', '70 kg'),
('4010', 'S'),
('4011', 'M'),
('4012', 'L'),
('4013', 'XL'),
('4014', 'XXL'),
('4015', 'Tamanho Único'),
('4019', '2.5 m'),
('4020', '3.0 m'),
('4021', '3.5 m'),
('4022', '4.0 m');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id_utilizador` int(11) NOT NULL,
  `nome` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `palavra_passe` varchar(500) NOT NULL,
  `data_registo` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_nivel` int(11) NOT NULL,
  `local_envio` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id_utilizador`, `nome`, `email`, `palavra_passe`, `data_registo`, `id_nivel`, `local_envio`) VALUES
(2, 'Maria Costa123', 'maria.costa@gmail.com', 'pass1234', '2025-02-24 21:48:24', 2, 'Avenida Central, 456'),
(3, 'Rita Lopes', 'rita.lopes@gmail.com', 'minhasenha', '2025-02-24 21:48:24', 9, 'Rua das Flores, 789'),
(4, 'Pedrinho', 'alert0.2gafawfaw@gmail.com', '1234', '2025-02-27 14:49:55', 9, 'Rua do Sol, 321'),
(5, 'Gabi', 'pedrofangueirosilva19@gmail.com', '1234', '2025-02-27 14:50:34', 1, 'Rua do Norte, 654');

-- --------------------------------------------------------

--
-- Estrutura da tabela `variacoes_produto`
--

CREATE TABLE `variacoes_produto` (
  `id_variacao` int(11) NOT NULL,
  `codigo_base` varchar(500) NOT NULL,
  `codigo_tamanho` varchar(500) NOT NULL,
  `codigo_cor` varchar(500) NOT NULL,
  `sku` varchar(500) NOT NULL,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `variacoes_produto`
--

INSERT INTO `variacoes_produto` (`id_variacao`, `codigo_base`, `codigo_tamanho`, `codigo_cor`, `sku`, `stock`) VALUES
(1, '1000-9001-3001', '4000', '8000', '1000-9001-3001-4000-8000', 1000),
(2, '1000-9001-3001', '4000', '8001', '1000-9001-3001-4000-8001', 1000),
(3, '1000-9001-3001', '4000', '8002', '1000-9001-3001-4000-8002', 1000),
(4, '1000-9001-3001', '4001', '8000', '1000-9001-3001-4001-8000', 1000),
(5, '1000-9001-3001', '4001', '8001', '1000-9001-3001-4001-8001', 1000),
(6, '1000-9001-3001', '4001', '8002', '1000-9001-3001-4001-8002', 1000),
(7, '1000-9001-3001', '4002', '8000', '1000-9001-3001-4002-8000', 999),
(8, '1000-9001-3001', '4002', '8001', '1000-9001-3001-4002-8001', 1000),
(9, '1000-9001-3001', '4002', '8002', '1000-9001-3001-4002-8002', 1000),
(10, '1000-9001-3001', '4003', '8000', '1000-9001-3001-4003-8000', 1000),
(11, '1000-9001-3001', '4003', '8001', '1000-9001-3001-4003-8001', 1000),
(12, '1000-9001-3001', '4003', '8002', '1000-9001-3001-4003-8002', 1000),
(13, '1000-9001-3001', '4004', '8000', '1000-9001-3001-4004-8000', 1000),
(14, '1000-9001-3001', '4004', '8001', '1000-9001-3001-4004-8001', 1000),
(15, '1000-9001-3001', '4004', '8002', '1000-9001-3001-4004-8002', 1000),
(16, '1001-9002-3001', '4000', '8000', '1001-9002-3001-4000-8000', 1000),
(17, '1001-9002-3001', '4000', '8001', '1001-9002-3001-4000-8001', 1000),
(18, '1001-9002-3001', '4000', '8002', '1001-9002-3001-4000-8002', 1000),
(19, '1001-9002-3001', '4001', '8000', '1001-9002-3001-4001-8000', 1000),
(20, '1001-9002-3001', '4001', '8001', '1001-9002-3001-4001-8001', 1000),
(21, '1001-9002-3001', '4001', '8002', '1001-9002-3001-4001-8002', 1000),
(22, '1001-9002-3001', '4002', '8000', '1001-9002-3001-4002-8000', 1000),
(23, '1001-9002-3001', '4002', '8001', '1001-9002-3001-4002-8001', 1000),
(24, '1001-9002-3001', '4002', '8002', '1001-9002-3001-4002-8002', 1000),
(25, '1001-9002-3001', '4003', '8000', '1001-9002-3001-4003-8000', 1000),
(26, '1001-9002-3001', '4003', '8001', '1001-9002-3001-4003-8001', 1000),
(27, '1001-9002-3001', '4003', '8002', '1001-9002-3001-4003-8002', 1000),
(28, '1001-9002-3001', '4004', '8000', '1001-9002-3001-4004-8000', 1000),
(29, '1001-9002-3001', '4004', '8001', '1001-9002-3001-4004-8001', 1000),
(30, '1001-9002-3001', '4004', '8002', '1001-9002-3001-4004-8002', 1000),
(31, '1002-9001-3002', '4005', '8000', '1002-9001-3002-4005-8000', 1000),
(32, '1002-9001-3002', '4005', '8001', '1002-9001-3002-4005-8001', 999),
(33, '1002-9001-3002', '4005', '8002', '1002-9001-3002-4005-8002', 1000),
(34, '1002-9001-3002', '4006', '8000', '1002-9001-3002-4006-8000', 1000),
(35, '1002-9001-3002', '4006', '8001', '1002-9001-3002-4006-8001', 1000),
(36, '1002-9001-3002', '4006', '8002', '1002-9001-3002-4006-8002', 1000),
(37, '1003-9002-3002', '4005', '8000', '1003-9002-3002-4005-8000', 1000),
(38, '1003-9002-3002', '4005', '8001', '1003-9002-3002-4005-8001', 1000),
(39, '1003-9002-3002', '4005', '8002', '1003-9002-3002-4005-8002', 1000),
(40, '1003-9002-3002', '4006', '8000', '1003-9002-3002-4006-8000', 1000),
(41, '1003-9002-3002', '4006', '8001', '1003-9002-3002-4006-8001', 1000),
(42, '1003-9002-3002', '4006', '8002', '1003-9002-3002-4006-8002', 1000),
(43, '1004-9003-3003', '4015', '8000', '1004-9003-3003-4015-8000', 1000),
(44, '1004-9003-3003', '4015', '8001', '1004-9003-3003-4015-8001', 1000),
(45, '1004-9003-3003', '4015', '8002', '1004-9003-3003-4015-8002', 1000),
(46, '1005-9004-3003', '4015', '8000', '1005-9004-3003-4015-8000', 1000),
(47, '1005-9004-3003', '4015', '8001', '1005-9004-3003-4015-8001', 1000),
(48, '1005-9004-3003', '4015', '8002', '1005-9004-3003-4015-8002', 1000),
(49, '1006-9001-3004', '4020', '8000', '1006-9001-3004-4020-8000', 1000),
(50, '1006-9001-3004', '4020', '8001', '1006-9001-3004-4020-8001', 1000),
(51, '1006-9001-3004', '4020', '8002', '1006-9001-3004-4020-8002', 1000),
(52, '1006-9001-3004', '4021', '8000', '1006-9001-3004-4021-8000', 1000),
(53, '1006-9001-3004', '4021', '8001', '1006-9001-3004-4021-8001', 1000),
(54, '1006-9001-3004', '4021', '8002', '1006-9001-3004-4021-8002', 1000),
(55, '1006-9001-3004', '4022', '8000', '1006-9001-3004-4022-8000', 1000),
(56, '1006-9001-3004', '4022', '8001', '1006-9001-3004-4022-8001', 1000),
(57, '1006-9001-3004', '4022', '8002', '1006-9001-3004-4022-8002', 1000),
(58, '1007-9002-3004', '4019', '8000', '1007-9002-3004-4019-8000', 1000),
(59, '1007-9002-3004', '4019', '8001', '1007-9002-3004-4019-8001', 1000),
(60, '1007-9002-3004', '4019', '8002', '1007-9002-3004-4019-8002', 1000),
(61, '1007-9002-3004', '4020', '8000', '1007-9002-3004-4020-8000', 1000),
(62, '1007-9002-3004', '4020', '8001', '1007-9002-3004-4020-8001', 1000),
(63, '1007-9002-3004', '4020', '8002', '1007-9002-3004-4020-8002', 1000),
(64, '1007-9002-3004', '4021', '8000', '1007-9002-3004-4021-8000', 1000),
(65, '1007-9002-3004', '4021', '8001', '1007-9002-3004-4021-8001', 1000),
(66, '1007-9002-3004', '4021', '8002', '1007-9002-3004-4021-8002', 1000),
(67, '1007-9002-3004', '4022', '8000', '1007-9002-3004-4022-8000', 1000),
(68, '1007-9002-3004', '4022', '8001', '1007-9002-3004-4022-8001', 1000),
(69, '1007-9002-3004', '4022', '8002', '1007-9002-3004-4022-8002', 1000),
(70, '1012-9001-3007', '4010', '8000', '1012-9001-3007-4010-8000', 1000),
(71, '1012-9001-3007', '4010', '8001', '1012-9001-3007-4010-8001', 1000),
(72, '1012-9001-3007', '4010', '8002', '1012-9001-3007-4010-8002', 1000),
(73, '1012-9001-3007', '4011', '8000', '1012-9001-3007-4011-8000', 1000),
(74, '1012-9001-3007', '4011', '8001', '1012-9001-3007-4011-8001', 1000),
(75, '1012-9001-3007', '4011', '8002', '1012-9001-3007-4011-8002', 1000),
(76, '1012-9001-3007', '4012', '8000', '1012-9001-3007-4012-8000', 1000),
(77, '1012-9001-3007', '4012', '8001', '1012-9001-3007-4012-8001', 1000),
(78, '1012-9001-3007', '4012', '8002', '1012-9001-3007-4012-8002', 1000),
(79, '1012-9001-3007', '4013', '8000', '1012-9001-3007-4013-8000', 1000),
(80, '1012-9001-3007', '4013', '8001', '1012-9001-3007-4013-8001', 1000),
(81, '1012-9001-3007', '4013', '8002', '1012-9001-3007-4013-8002', 1000),
(82, '1012-9001-3007', '4014', '8000', '1012-9001-3007-4014-8000', 1000),
(83, '1012-9001-3007', '4014', '8001', '1012-9001-3007-4014-8001', 1000),
(84, '1012-9001-3007', '4014', '8002', '1012-9001-3007-4014-8002', 1000),
(85, '1013-9002-3007', '4010', '8000', '1013-9002-3007-4010-8000', 1000),
(86, '1013-9002-3007', '4010', '8001', '1013-9002-3007-4010-8001', 1000),
(87, '1013-9002-3007', '4010', '8002', '1013-9002-3007-4010-8002', 1000),
(88, '1013-9002-3007', '4011', '8000', '1013-9002-3007-4011-8000', 1000),
(89, '1013-9002-3007', '4011', '8001', '1013-9002-3007-4011-8001', 1000),
(90, '1013-9002-3007', '4011', '8002', '1013-9002-3007-4011-8002', 1000),
(91, '1013-9002-3007', '4012', '8000', '1013-9002-3007-4012-8000', 1000),
(92, '1013-9002-3007', '4012', '8001', '1013-9002-3007-4012-8001', 1000),
(93, '1013-9002-3007', '4012', '8002', '1013-9002-3007-4012-8002', 1000),
(94, '1014-9001-3008', '4020', '8002', '1014-9001-3008-4020-8002', 1000),
(95, '1014-9001-3008', '4021', '8000', '1014-9001-3008-4021-8000', 1000),
(96, '1014-9001-3008', '4021', '8001', '1014-9001-3008-4021-8001', 1000),
(97, '1014-9001-3008', '4021', '8002', '1014-9001-3008-4021-8002', 1000),
(98, '1014-9001-3008', '4022', '8000', '1014-9001-3008-4022-8000', 997),
(99, '1014-9001-3008', '4022', '8001', '1014-9001-3008-4022-8001', 1000),
(100, '1014-9001-3008', '4022', '8002', '1014-9001-3008-4022-8002', 1000),
(101, '1015-9002-3008', '4015', '8000', '1015-9002-3008-4015-8000', 1000),
(102, '1015-9002-3008', '4015', '8001', '1015-9002-3008-4015-8001', 1000),
(103, '1015-9002-3008', '4015', '8002', '1015-9002-3008-4015-8002', 1000),
(104, '1008-9001-3005', '4015', '8004', '1008-9001-3005-4015-8004', 1000);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Índices para tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id_carrinho`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Índices para tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Índices para tabela `comentarios_blog`
--
ALTER TABLE `comentarios_blog`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_post` (`id_post`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Índices para tabela `cores`
--
ALTER TABLE `cores`
  ADD PRIMARY KEY (`codigo_cor`);

--
-- Índices para tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD PRIMARY KEY (`id_encomenda`),
  ADD KEY `id_utilizador` (`id_utilizador`);

--
-- Índices para tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id_fornecedor`);

--
-- Índices para tabela `gostos`
--
ALTER TABLE `gostos`
  ADD PRIMARY KEY (`id_gosto`);

--
-- Índices para tabela `itens_encomenda`
--
ALTER TABLE `itens_encomenda`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `fk_id_encomenda` (`id_encomenda`),
  ADD KEY `fk_sku` (`sku`);

--
-- Índices para tabela `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marca`);

--
-- Índices para tabela `nivel_acesso`
--
ALTER TABLE `nivel_acesso`
  ADD PRIMARY KEY (`id_nivel`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`codigo_base`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_fornecedor` (`id_fornecedor`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Índices para tabela `reviews_encomendas`
--
ALTER TABLE `reviews_encomendas`
  ADD PRIMARY KEY (`id_review`);

--
-- Índices para tabela `reviews_produtos`
--
ALTER TABLE `reviews_produtos`
  ADD PRIMARY KEY (`id_review`);

--
-- Índices para tabela `tamanhos`
--
ALTER TABLE `tamanhos`
  ADD PRIMARY KEY (`codigo_tamanho`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id_utilizador`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_nivel` (`id_nivel`);

--
-- Índices para tabela `variacoes_produto`
--
ALTER TABLE `variacoes_produto`
  ADD PRIMARY KEY (`id_variacao`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `codigo_base` (`codigo_base`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `blog`
--
ALTER TABLE `blog`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id_carrinho` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3009;

--
-- AUTO_INCREMENT de tabela `comentarios_blog`
--
ALTER TABLE `comentarios_blog`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `encomendas`
--
ALTER TABLE `encomendas`
  MODIFY `id_encomenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `gostos`
--
ALTER TABLE `gostos`
  MODIFY `id_gosto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `itens_encomenda`
--
ALTER TABLE `itens_encomenda`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `reviews_encomendas`
--
ALTER TABLE `reviews_encomendas`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `reviews_produtos`
--
ALTER TABLE `reviews_produtos`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id_utilizador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `variacoes_produto`
--
ALTER TABLE `variacoes_produto`
  MODIFY `id_variacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id_utilizador`);

--
-- Limitadores para a tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD CONSTRAINT `carrinho_ibfk_1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id_utilizador`);

--
-- Limitadores para a tabela `comentarios_blog`
--
ALTER TABLE `comentarios_blog`
  ADD CONSTRAINT `comentarios_blog_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `blog` (`id_post`),
  ADD CONSTRAINT `comentarios_blog_ibfk_2` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id_utilizador`);

--
-- Limitadores para a tabela `encomendas`
--
ALTER TABLE `encomendas`
  ADD CONSTRAINT `encomendas_ibfk_1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizadores` (`id_utilizador`);

--
-- Limitadores para a tabela `itens_encomenda`
--
ALTER TABLE `itens_encomenda`
  ADD CONSTRAINT `fk_id_encomenda` FOREIGN KEY (`id_encomenda`) REFERENCES `encomendas` (`id_encomenda`);

--
-- Limitadores para a tabela `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`),
  ADD CONSTRAINT `produtos_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id_fornecedor`),
  ADD CONSTRAINT `produtos_ibfk_3` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Limitadores para a tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD CONSTRAINT `utilizadores_ibfk_1` FOREIGN KEY (`id_nivel`) REFERENCES `nivel_acesso` (`id_nivel`);

--
-- Limitadores para a tabela `variacoes_produto`
--
ALTER TABLE `variacoes_produto`
  ADD CONSTRAINT `variacoes_produto_ibfk_1` FOREIGN KEY (`codigo_base`) REFERENCES `produtos` (`codigo_base`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
