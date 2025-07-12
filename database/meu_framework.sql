-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 11-Jul-2025 às 22:16
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `meu_framework`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `descricao`, `criado_em`, `atualizado_em`) VALUES
(1, 'sistema_nome', '\"ChatSerpro\"', 'Nome do sistema', '2025-07-11 17:14:09', '2025-07-11 17:14:09'),
(2, 'sistema_versao', '\"1.0.0\"', 'Versão do sistema', '2025-07-11 17:14:09', '2025-07-11 17:14:09'),
(3, 'sistema_mantenedor', '\"Equipe de Desenvolvimento\"', 'Responsável pela manutenção', '2025-07-11 17:14:09', '2025-07-11 17:14:09'),
(4, 'chat_max_mensagens_historico', '1000', 'Máximo de mensagens no histórico', '2025-07-11 17:14:09', '2025-07-11 17:14:09'),
(5, 'chat_timeout_sessao', '1800', 'Timeout da sessão em segundos (30 minutos)', '2025-07-11 17:14:09', '2025-07-11 17:14:09'),
(6, 'webhook_timeout', '30', 'Timeout do webhook em segundos', '2025-07-11 17:14:09', '2025-07-11 17:14:09'),
(7, 'teste_sistema', '\"funcionando\"', 'Teste do sistema', '2025-07-11 17:17:46', '2025-07-11 17:17:46'),
(9, 'serpro_api', '{\"client_id\":\"4r9WNte1WA90GK+UVDYc70lDUHM0KzE5Mk9NUFlUSnZReDlReUE9PQ==\",\"client_secret\":\"PoyYLxDu1zeoBczW9uRbNjJFSEwvRXl1bVovMVpGWWZuSnJlVkVmQ2h1WnNtNWNVSGZRWWhUL2RWSzh6bjVGSU95Y0VZRVRmNlB6ek9TbzA=\",\"base_url\":\"https:\\/\\/api.whatsapp.serpro.gov.br\",\"waba_id\":\"472202335973627\",\"phone_number_id\":\"642958872237822\",\"webhook_verify_token\":\"bnu+7xdNqnibQMq0fKdVFkJTQmVVeTBWS2hScExWS2xiMXo1QkE9PQ==\"}', NULL, '2025-07-11 17:40:48', '2025-07-11 18:55:36'),
(10, 'mensagens_automaticas', '{\"mensagem_boas_vindas\":\"Ol\\u00e1! Seja bem-vindo(a) ao nosso atendimento. Em que posso ajud\\u00e1-lo(a)?\",\"mensagem_ausencia\":\"No momento n\\u00e3o h\\u00e1 atendentes dispon\\u00edveis. Deixe sua mensagem que retornaremos em breve.\",\"mensagem_encerramento\":\"Obrigado pelo contato! Se precisar de mais alguma coisa, estarei aqui para ajudar.\",\"horario_funcionamento\":\"Segunda a Sexta: 08:00 \\u00e0s 18:00\",\"ativar_boas_vindas\":true,\"ativar_ausencia\":true,\"ativar_encerramento\":true}', NULL, '2025-07-11 17:41:23', '2025-07-11 17:41:23'),
(12, 'serpro_token_cache', '{\"access_token\":\"eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIzeGQ1U0gtY1NOMDRuZVBDdWtrT2M0LUZocm8wcGxTTlFPQ3p1SDBITFVJIn0.eyJleHAiOjE3NTIyNjUwNzcsImlhdCI6MTc1MjI2NDQ3NywianRpIjoidHJydGNjOmZhZjZmOGIwLTliM2EtNGY2NS04NWFkLWM4N2Q4MWNhZmIyYyIsImlzcyI6Imh0dHBzOi8vbG9naW4uaWRhYXMuc2VycHJvLmdvdi5ici9hdXRoL3JlYWxtcy93aGF0c2FwcCIsImF1ZCI6ImFjY291bnQiLCJzdWIiOiJhNjVkNjc0ZS1iMGE1LTQ5MTgtYmM0NS01NDY2MTNmNGU2MmYiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiI2NDI5NTg4NzIyMzc4MjIiLCJyZWFsbV9hY2Nlc3MiOnsicm9sZXMiOlsiZGVmYXVsdC1yb2xlcy13aGF0c2FwcCIsIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iXX0sInJlc291cmNlX2FjY2VzcyI6eyJhY2NvdW50Ijp7InJvbGVzIjpbIm1hbmFnZS1hY2NvdW50IiwibWFuYWdlLWFjY291bnQtbGlua3MiLCJ2aWV3LXByb2ZpbGUiXX19LCJzY29wZSI6IiIsIndhYmFJZCI6IjQ3MjIwMjMzNTk3MzYyNyIsImNsaWVudEhvc3QiOiIxMC4xNzYuMTcxLjEyMyIsImNsaWVudElkIjoiNjQyOTU4ODcyMjM3ODIyIiwiZnJvbVBob25lTnVtYmVySWQiOiI2NDI5NTg4NzIyMzc4MjIiLCJjbGllbnRBZGRyZXNzIjoiMTAuMTc2LjE3MS4xMjMifQ.IDwYRLqFez7qFPvqoeoxWh_iwjsFvuTvo8MlYVVHa2jyEqo8g1YGzPoO7K_0i4FiIpK7NzDbU_9GQhC2NWf1ZWYnK9wiAyaRGHgWyJXu6m1MCsKJu7O9s36Zpi7ZJ1txdF6sKIodbgZQx2KycrR2YqKCmWQpPMBHR9XKt0cg1aTHp5A5772IEf3Owm7kzTKYHp4Q3Qi94dNYPK8v7Pbtma7BR_0iZrGPl0WuCOM8VqWcvjm0FL9sVSiM5vUvNCtI2lT2B1ptt8b7BNA5B1rUK--yS32hMVnuHo66wVeKHqJIQ0oHPtmJD7LMceCCGRGwhRf0qb6Pl81JnFCYD7EcJA\",\"expires_in\":600,\"token_type\":\"Bearer\",\"expires_at\":1752265077,\"created_at\":1752264477}', 'Cache do token JWT da API Serpro', '2025-07-11 18:58:30', '2025-07-11 20:07:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `contatos`
--

CREATE TABLE `contatos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `numero` varchar(20) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `fonte` enum('manual','whatsapp','importacao','api') DEFAULT 'manual',
  `ultimo_contato` timestamp NULL DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `notas` text DEFAULT NULL,
  `sessao_id` int(11) NOT NULL,
  `bloqueado` tinyint(1) DEFAULT 0,
  `ultima_mensagem` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `contatos`
--

INSERT INTO `contatos` (`id`, `nome`, `numero`, `telefone`, `foto_perfil`, `email`, `empresa`, `observacoes`, `fonte`, `ultimo_contato`, `tags`, `notas`, `sessao_id`, `bloqueado`, `ultima_mensagem`, `criado_em`, `atualizado_em`) VALUES
(18, 'Edneia de Oliveira Parente', '5562925565226', '5562925565226', NULL, NULL, 'teste', 'ss', 'manual', '2025-07-11 17:02:53', NULL, NULL, 1, 0, NULL, '2025-07-11 14:02:53', NULL),
(19, 'Romulo', '5562983070508', '5562983070508', NULL, NULL, NULL, NULL, '', '2025-07-11 19:25:45', NULL, NULL, 1, 0, NULL, '2025-07-11 16:25:45', NULL),
(24, 'Cleyton', '5562996185892', '5562996185892', NULL, NULL, NULL, NULL, '', '2025-07-11 19:50:02', NULL, NULL, 1, 0, NULL, '2025-07-11 16:50:02', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `contato_tags`
--

CREATE TABLE `contato_tags` (
  `id` int(11) NOT NULL,
  `contato_id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `contato_tags`
--

INSERT INTO `contato_tags` (`id`, `contato_id`, `tag`, `criado_em`) VALUES
(27, 18, 'Suporte', '2025-07-11 17:02:53');

-- --------------------------------------------------------

--
-- Estrutura da tabela `conversas`
--

CREATE TABLE `conversas` (
  `id` int(11) NOT NULL,
  `contato_id` int(11) NOT NULL,
  `atendente_id` int(11) DEFAULT NULL,
  `sessao_id` int(11) NOT NULL,
  `status` enum('aberto','pendente','fechado','transferindo') DEFAULT 'pendente',
  `prioridade` enum('baixa','normal','alta','urgente') DEFAULT 'normal',
  `departamento` varchar(100) DEFAULT 'Geral',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `notas_internas` text DEFAULT NULL,
  `tempo_resposta_medio` int(11) DEFAULT 0,
  `ultima_mensagem` datetime DEFAULT NULL,
  `fechado_em` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `conversas`
--

INSERT INTO `conversas` (`id`, `contato_id`, `atendente_id`, `sessao_id`, `status`, `prioridade`, `departamento`, `tags`, `notas_internas`, `tempo_resposta_medio`, `ultima_mensagem`, `fechado_em`, `criado_em`, `atualizado_em`) VALUES
(7, 19, 1, 1, 'fechado', 'normal', 'Geral', NULL, NULL, 0, '2025-07-11 16:28:08', NULL, '2025-07-11 16:28:07', '2025-07-11 17:01:09'),
(12, 24, 1, 1, 'aberto', 'normal', 'Geral', NULL, NULL, 0, '2025-07-11 16:50:03', NULL, '2025-07-11 16:50:02', '2025-07-11 16:50:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `log_acessos`
--

CREATE TABLE `log_acessos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `sucesso` tinyint(1) NOT NULL DEFAULT 0,
  `data_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `log_acessos`
--

INSERT INTO `log_acessos` (`id`, `usuario_id`, `email`, `ip`, `user_agent`, `sucesso`, `data_hora`) VALUES
(1, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 10:48:01'),
(2, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 10:51:58'),
(3, NULL, 'admin@gmail.com', '127.0.0.1', NULL, 0, '2025-07-11 10:52:04'),
(4, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 10:52:52'),
(5, NULL, 'cleytonparente@gmail.com', '127.0.0.1', NULL, 0, '2025-07-11 10:56:30'),
(6, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 10:57:07'),
(7, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 10:59:35'),
(8, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 11:00:19'),
(9, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 11:08:04'),
(10, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 11:12:29'),
(11, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 11:12:47'),
(12, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 11:19:14'),
(13, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 11:44:56'),
(14, 3, NULL, '127.0.0.1', NULL, 1, '2025-07-11 12:25:15'),
(15, NULL, 'admin@gmail.com', '127.0.0.1', NULL, 0, '2025-07-11 12:26:18'),
(16, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 12:26:22'),
(17, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 12:33:54'),
(18, 3, NULL, '127.0.0.1', NULL, 1, '2025-07-11 14:02:00'),
(19, 3, NULL, '127.0.0.1', NULL, 1, '2025-07-11 14:03:17'),
(20, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 14:03:30'),
(21, 2, NULL, '127.0.0.1', NULL, 1, '2025-07-11 14:03:53'),
(22, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 14:06:16'),
(23, 3, NULL, '127.0.0.1', NULL, 1, '2025-07-11 16:36:35'),
(24, 1, NULL, '127.0.0.1', NULL, 1, '2025-07-11 16:37:07');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `conversa_id` int(11) NOT NULL,
  `contato_id` int(11) NOT NULL,
  `atendente_id` int(11) DEFAULT NULL,
  `serpro_message_id` varchar(255) DEFAULT NULL,
  `tipo` enum('texto','imagem','audio','video','documento','localizacao','contato','sistema') DEFAULT 'texto',
  `conteudo` text NOT NULL,
  `midia_url` varchar(500) DEFAULT NULL,
  `midia_nome` varchar(255) DEFAULT NULL,
  `midia_tipo` varchar(100) DEFAULT NULL,
  `direcao` enum('entrada','saida') NOT NULL,
  `status_entrega` enum('enviando','enviado','entregue','lido','erro') DEFAULT 'enviando',
  `lida` tinyint(1) DEFAULT 0,
  `lida_em` datetime DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `mensagens`
--

INSERT INTO `mensagens` (`id`, `conversa_id`, `contato_id`, `atendente_id`, `serpro_message_id`, `tipo`, `conteudo`, `midia_url`, `midia_nome`, `midia_tipo`, `direcao`, `status_entrega`, `lida`, `lida_em`, `metadata`, `criado_em`) VALUES
(7, 7, 19, 1, '39e9f8d2-1398-4dcb-b16f-bc1ef2080acc', 'texto', 'Template: central_intimacao_remota | Parâmetros: Array', NULL, NULL, NULL, 'saida', 'enviado', 0, NULL, '{\"tipo\":\"template\",\"template\":\"central_intimacao_remota\",\"parametros\":[{\"tipo\":\"text\",\"valor\":\"Testete s  ss s\"}],\"serpro_response\":{\"id\":\"39e9f8d2-1398-4dcb-b16f-bc1ef2080acc\",\"acoes\":[{\"rel\":\"status\",\"uri\":\"#\\/client\\/642958872237822\\/v2\\/requisicao\\/39e9f8d2-1398-4dcb-b16f-bc1ef2080acc\",\"method\":\"GET\"}]}}', '2025-07-11 16:28:08'),
(12, 12, 24, 1, 'd74236f0-e290-4274-a311-fa7cff11a185', 'texto', 'Template: central_intimacao_remota | Parâmetros: cxcxc', NULL, NULL, NULL, 'saida', 'enviado', 0, NULL, '{\"tipo\":\"template\",\"template\":\"central_intimacao_remota\",\"parametros\":[{\"tipo\":\"text\",\"valor\":\"cxcxc\"}],\"serpro_response\":{\"id\":\"d74236f0-e290-4274-a311-fa7cff11a185\",\"acoes\":[{\"rel\":\"status\",\"uri\":\"#\\/client\\/642958872237822\\/v2\\/requisicao\\/d74236f0-e290-4274-a311-fa7cff11a185\",\"method\":\"GET\"}]}}', '2025-07-11 16:50:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `rota` varchar(255) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `pai_id` int(11) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `modulos`
--

INSERT INTO `modulos` (`id`, `nome`, `descricao`, `rota`, `icone`, `pai_id`, `status`, `criado_em`, `atualizado_em`) VALUES
(1, 'Dashboard', 'Painel principal com estatísticas', 'dashboard', 'fas fa-chart-line', NULL, 'ativo', '2025-07-11 10:46:54', NULL),
(2, 'Chat', 'Sistema de conversas', 'chat', 'fas fa-comments', NULL, 'ativo', '2025-07-11 10:46:54', NULL),
(3, 'Contatos', 'Gerenciamento de contatos', 'contatos', 'fas fa-address-book', NULL, 'ativo', '2025-07-11 10:46:54', NULL),
(4, 'Relatórios', 'Relatórios e estatísticas', 'relatorios', 'fas fa-chart-bar', NULL, 'ativo', '2025-07-11 10:46:54', NULL),
(5, 'Configurações', 'Configurações do sistema', 'configuracoes', 'fas fa-cog', NULL, 'ativo', '2025-07-11 10:46:54', NULL),
(6, 'Usuários', 'Gerenciamento de usuários', 'usuarios', 'fas fa-users', NULL, 'ativo', '2025-07-11 10:46:54', NULL),
(7, 'Conversas Ativas', 'Conversas em andamento', 'chat/ativas', 'fas fa-comment-dots', 2, 'ativo', '2025-07-11 10:46:54', NULL),
(8, 'Conversas Pendentes', 'Conversas aguardando atendimento', 'chat/pendentes', 'fas fa-clock', 2, 'ativo', '2025-07-11 10:46:54', NULL),
(9, 'Conversas Fechadas', 'Histórico de conversas', 'chat/fechadas', 'fas fa-archive', 2, 'ativo', '2025-07-11 10:46:54', NULL),
(10, 'Conexões WhatsApp', 'Gerenciar conexões', 'configuracoes/conexoes', 'fab fa-whatsapp', 5, 'ativo', '2025-07-11 10:46:54', NULL),
(11, 'API Serpro', 'Configurações da API', 'configuracoes/serpro', 'fas fa-plug', 5, 'ativo', '2025-07-11 10:46:54', NULL),
(12, 'Mensagens Automáticas', 'Configurar respostas automáticas', 'configuracoes/mensagens', 'fas fa-robot', 5, 'ativo', '2025-07-11 10:46:54', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `permissoes_usuario`
--

CREATE TABLE `permissoes_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `permissoes_usuario`
--

INSERT INTO `permissoes_usuario` (`id`, `usuario_id`, `modulo_id`, `criado_em`, `atualizado_em`) VALUES
(1, 1, 1, '2025-07-11 10:46:54', NULL),
(2, 1, 2, '2025-07-11 10:46:54', NULL),
(3, 1, 3, '2025-07-11 10:46:54', NULL),
(4, 1, 4, '2025-07-11 10:46:54', NULL),
(5, 1, 5, '2025-07-11 10:46:54', NULL),
(6, 1, 6, '2025-07-11 10:46:54', NULL),
(7, 1, 7, '2025-07-11 10:46:54', NULL),
(8, 1, 8, '2025-07-11 10:46:54', NULL),
(9, 1, 9, '2025-07-11 10:46:54', NULL),
(10, 1, 10, '2025-07-11 10:46:54', NULL),
(11, 1, 11, '2025-07-11 10:46:54', NULL),
(12, 1, 12, '2025-07-11 10:46:54', NULL),
(16, 2, 1, '2025-07-11 10:46:54', NULL),
(17, 2, 2, '2025-07-11 10:46:54', NULL),
(18, 2, 3, '2025-07-11 10:46:54', NULL),
(19, 2, 4, '2025-07-11 10:46:54', NULL),
(20, 2, 6, '2025-07-11 10:46:54', NULL),
(21, 2, 7, '2025-07-11 10:46:54', NULL),
(22, 2, 8, '2025-07-11 10:46:54', NULL),
(23, 2, 9, '2025-07-11 10:46:54', NULL),
(24, 3, 1, '2025-07-11 10:46:54', NULL),
(25, 3, 2, '2025-07-11 10:46:54', NULL),
(26, 3, 3, '2025-07-11 10:46:54', NULL),
(27, 3, 7, '2025-07-11 10:46:54', NULL),
(28, 3, 8, '2025-07-11 10:46:54', NULL),
(29, 3, 9, '2025-07-11 10:46:54', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `sessoes_whatsapp`
--

CREATE TABLE `sessoes_whatsapp` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `serpro_session_id` varchar(255) DEFAULT NULL,
  `serpro_waba_id` varchar(255) DEFAULT NULL,
  `serpro_phone_number_id` varchar(255) DEFAULT NULL,
  `webhook_token` varchar(255) DEFAULT NULL,
  `status` enum('conectado','desconectado','conectando','erro') DEFAULT 'desconectado',
  `qr_code` text DEFAULT NULL,
  `ultima_conexao` datetime DEFAULT NULL,
  `configuracoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracoes`)),
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `sessoes_whatsapp`
--

INSERT INTO `sessoes_whatsapp` (`id`, `nome`, `numero`, `serpro_session_id`, `serpro_waba_id`, `serpro_phone_number_id`, `webhook_token`, `status`, `qr_code`, `ultima_conexao`, `configuracoes`, `criado_em`, `atualizado_em`) VALUES
(1, 'Principal', '5511999999999', NULL, NULL, NULL, NULL, 'desconectado', NULL, NULL, NULL, '2025-07-11 10:46:54', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','supervisor','atendente') DEFAULT 'atendente',
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo','ausente','ocupado') DEFAULT 'ativo',
  `max_chats` int(11) DEFAULT 5,
  `ultimo_acesso` datetime DEFAULT NULL,
  `token_recuperacao` varchar(64) DEFAULT NULL,
  `token_expiracao` datetime DEFAULT NULL,
  `configuracoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracoes`)),
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `avatar`, `status`, `max_chats`, `ultimo_acesso`, `token_recuperacao`, `token_expiracao`, `configuracoes`, `criado_em`, `atualizado_em`) VALUES
(1, 'Administrador', 'admin@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'admin', NULL, 'ativo', 20, '2025-07-11 17:15:19', NULL, NULL, NULL, '2025-07-11 10:46:54', '2025-07-11 17:15:19'),
(2, 'Supervisor', 'supervisor@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'supervisor', NULL, 'ativo', 8, '2025-07-11 14:05:55', NULL, NULL, NULL, '2025-07-11 10:46:54', '2025-07-11 14:05:55'),
(3, 'Atendente 1', 'atendente1@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'atendente', NULL, 'ativo', 5, '2025-07-11 16:36:55', NULL, NULL, NULL, '2025-07-11 10:46:54', '2025-07-11 16:36:55'),
(5, 'atendente2', 'atendente2@gmail.com', '$2y$10$iTgLIoF8VaRm9xyA86Aolep3Pg7EDPp5GKSdDiPBilbi5/L96I/Z2', 'atendente', NULL, 'ativo', 5, NULL, NULL, NULL, NULL, '2025-07-11 12:22:51', '2025-07-11 12:34:17');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave_unique` (`chave`),
  ADD KEY `idx_chave` (`chave`);

--
-- Índices para tabela `contatos`
--
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_numero` (`numero`),
  ADD KEY `idx_sessao` (`sessao_id`),
  ADD KEY `idx_telefone` (`telefone`),
  ADD KEY `idx_ultimo_contato` (`ultimo_contato`);

--
-- Índices para tabela `contato_tags`
--
ALTER TABLE `contato_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contato_tag_unique` (`contato_id`,`tag`),
  ADD KEY `idx_tag` (`tag`);

--
-- Índices para tabela `conversas`
--
ALTER TABLE `conversas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_atendente` (`atendente_id`),
  ADD KEY `idx_contato` (`contato_id`),
  ADD KEY `idx_sessao` (`sessao_id`);

--
-- Índices para tabela `log_acessos`
--
ALTER TABLE `log_acessos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario_log` (`usuario_id`);

--
-- Índices para tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversa` (`conversa_id`),
  ADD KEY `idx_contato` (`contato_id`),
  ADD KEY `idx_direcao` (`direcao`),
  ADD KEY `idx_data` (`criado_em`),
  ADD KEY `fk_mensagem_atendente` (`atendente_id`);

--
-- Índices para tabela `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pai_modulo` (`pai_id`);

--
-- Índices para tabela `permissoes_usuario`
--
ALTER TABLE `permissoes_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_modulo` (`usuario_id`,`modulo_id`),
  ADD KEY `fk_modulo_permissao` (`modulo_id`);

--
-- Índices para tabela `sessoes_whatsapp`
--
ALTER TABLE `sessoes_whatsapp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `contatos`
--
ALTER TABLE `contatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `contato_tags`
--
ALTER TABLE `contato_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `conversas`
--
ALTER TABLE `conversas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `log_acessos`
--
ALTER TABLE `log_acessos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `permissoes_usuario`
--
ALTER TABLE `permissoes_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `sessoes_whatsapp`
--
ALTER TABLE `sessoes_whatsapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `contatos`
--
ALTER TABLE `contatos`
  ADD CONSTRAINT `fk_contato_sessao` FOREIGN KEY (`sessao_id`) REFERENCES `sessoes_whatsapp` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `contato_tags`
--
ALTER TABLE `contato_tags`
  ADD CONSTRAINT `contato_tags_ibfk_1` FOREIGN KEY (`contato_id`) REFERENCES `contatos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `conversas`
--
ALTER TABLE `conversas`
  ADD CONSTRAINT `fk_conversa_atendente` FOREIGN KEY (`atendente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_conversa_contato` FOREIGN KEY (`contato_id`) REFERENCES `contatos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversa_sessao` FOREIGN KEY (`sessao_id`) REFERENCES `sessoes_whatsapp` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `log_acessos`
--
ALTER TABLE `log_acessos`
  ADD CONSTRAINT `fk_usuario_log` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `fk_mensagem_atendente` FOREIGN KEY (`atendente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mensagem_contato` FOREIGN KEY (`contato_id`) REFERENCES `contatos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mensagem_conversa` FOREIGN KEY (`conversa_id`) REFERENCES `conversas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `modulos`
--
ALTER TABLE `modulos`
  ADD CONSTRAINT `fk_pai_modulo` FOREIGN KEY (`pai_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `permissoes_usuario`
--
ALTER TABLE `permissoes_usuario`
  ADD CONSTRAINT `fk_modulo_permissao` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuario_permissao` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
