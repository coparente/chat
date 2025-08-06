SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `meu_framework` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `meu_framework`;

DELIMITER $$
--
-- Procedimentos
--
CREATE PROCEDURE `sp_atendentes_departamento` (IN `p_departamento_id` INT)  
BEGIN
    SELECT 
        u.id,
        u.nome,
        u.email,
        u.perfil,
        u.status,
        ad.perfil as perfil_departamento,
        ad.max_conversas,
        ad.horario_inicio,
        ad.horario_fim
    FROM usuarios u
    JOIN atendentes_departamento ad ON u.id = ad.usuario_id
    WHERE ad.departamento_id = p_departamento_id 
    AND ad.status = 'ativo'
    AND u.status = 'ativo'
    ORDER BY ad.perfil DESC, u.nome ASC;
END$$

CREATE PROCEDURE `sp_obter_credencial_departamento` (IN `p_departamento_id` INT)  
BEGIN
    SELECT 
        cs.*,
        d.nome as departamento_nome
    FROM credenciais_serpro_departamento cs
    JOIN departamentos d ON cs.departamento_id = d.id
    WHERE cs.departamento_id = p_departamento_id 
    AND cs.status = 'ativo'
    ORDER BY cs.prioridade ASC
    LIMIT 1;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- Estrutura das tabelas
-- --------------------------------------------------------

CREATE TABLE `atendentes_departamento` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `perfil` enum('atendente','supervisor','admin') DEFAULT 'atendente',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `max_conversas` int(11) DEFAULT 5,
  `horario_inicio` time DEFAULT '08:00:00',
  `horario_fim` time DEFAULT '18:00:00',
  `dias_semana` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dias_semana`)),
  `configuracoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracoes`)),
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `atividades` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `contato_tags` (
  `id` int(11) NOT NULL,
  `contato_id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `departamento_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `credenciais_serpro_departamento` (
  `id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `base_url` varchar(255) NOT NULL DEFAULT 'https://api.whatsapp.serpro.gov.br',
  `waba_id` varchar(255) NOT NULL,
  `phone_number_id` varchar(255) NOT NULL,
  `webhook_verify_token` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo','teste') DEFAULT 'ativo',
  `prioridade` int(11) DEFAULT 0,
  `configuracoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracoes`)),
  `token_cache` text DEFAULT NULL,
  `token_expiracao` datetime DEFAULT NULL,
  `ultimo_teste` datetime DEFAULT NULL,
  `resultado_teste` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resultado_teste`)),
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cor` varchar(7) DEFAULT '#007bff',
  `icone` varchar(50) DEFAULT 'fas fa-building',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `prioridade` int(11) DEFAULT 0,
  `configuracoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracoes`)),
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `log_acessos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `sucesso` tinyint(1) NOT NULL DEFAULT 0,
  `data_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `mensagens_automaticas_departamento` (
  `id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `tipo` enum('boas_vindas','ausencia','encerramento','fora_horario','personalizada') NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensagem` text NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `horario_inicio` time DEFAULT NULL,
  `horario_fim` time DEFAULT NULL,
  `dias_semana` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dias_semana`)),
  `configuracoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuracoes`)),
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `rota` varchar(255) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `pai_id` int(11) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessoes_whatsapp` (
  `id` int(11) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL,
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

CREATE TABLE `templates_departamento` (
  `id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `nome_serpro` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` varchar(50) DEFAULT 'geral',
  `parametros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parametros`)),
  `exemplo_parametros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`exemplo_parametros`)),
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------
-- Views
-- --------------------------------------------------------

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_credenciais_ativas` AS 
SELECT 
    `d`.`id` AS `departamento_id`,
    `d`.`nome` AS `departamento_nome`,
    `cs`.`id` AS `credencial_id`,
    `cs`.`nome` AS `credencial_nome`,
    `cs`.`status` AS `credencial_status`,
    `cs`.`prioridade` AS `credencial_prioridade`,
    `cs`.`ultimo_teste` AS `ultimo_teste`,
    `cs`.`resultado_teste` AS `resultado_teste`
FROM `departamentos` `d`
LEFT JOIN `credenciais_serpro_departamento` `cs` 
    ON `d`.`id` = `cs`.`departamento_id`
WHERE `d`.`status` = 'ativo' 
AND `cs`.`status` = 'ativo'
ORDER BY `d`.`prioridade` ASC, `cs`.`prioridade` ASC;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_estatisticas_departamento` AS 
SELECT 
    `d`.`id` AS `departamento_id`,
    `d`.`nome` AS `departamento_nome`,
    `d`.`cor` AS `departamento_cor`,
    COUNT(`c`.`id`) AS `total_conversas`,
    SUM(CASE WHEN `c`.`status` = 'aberto' THEN 1 ELSE 0 END) AS `conversas_abertas`,
    SUM(CASE WHEN `c`.`status` = 'pendente' THEN 1 ELSE 0 END) AS `conversas_pendentes`,
    SUM(CASE WHEN `c`.`status` = 'fechado' THEN 1 ELSE 0 END) AS `conversas_fechadas`,
    COUNT(DISTINCT `c`.`atendente_id`) AS `atendentes_ativos`,
    AVG(`c`.`tempo_resposta_medio`) AS `tempo_resposta_medio`
FROM `departamentos` `d`
LEFT JOIN `conversas` `c` 
    ON `d`.`id` = `c`.`departamento_id`
WHERE `d`.`status` = 'ativo'
GROUP BY `d`.`id`, `d`.`nome`, `d`.`cor`;

-- --------------------------------------------------------
-- Índices
-- --------------------------------------------------------

ALTER TABLE `atendentes_departamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_departamento` (`usuario_id`,`departamento_id`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_status` (`status`);

ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave_unique` (`chave`),
  ADD KEY `idx_chave` (`chave`);

ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_numero` (`numero`),
  ADD KEY `idx_sessao` (`sessao_id`),
  ADD KEY `idx_telefone` (`telefone`),
  ADD KEY `idx_ultimo_contato` (`ultimo_contato`);

ALTER TABLE `contato_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contato_tag_unique` (`contato_id`,`tag`),
  ADD KEY `idx_tag` (`tag`);

ALTER TABLE `conversas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_atendente` (`atendente_id`),
  ADD KEY `idx_contato` (`contato_id`),
  ADD KEY `idx_sessao` (`sessao_id`),
  ADD KEY `idx_departamento` (`departamento`),
  ADD KEY `fk_conversa_departamento` (`departamento_id`);

ALTER TABLE `credenciais_serpro_departamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_prioridade` (`prioridade`);

ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_prioridade` (`prioridade`);

ALTER TABLE `log_acessos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario_log` (`usuario_id`);

ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversa` (`conversa_id`),
  ADD KEY `idx_contato` (`contato_id`),
  ADD KEY `idx_direcao` (`direcao`),
  ADD KEY `idx_data` (`criado_em`),
  ADD KEY `fk_mensagem_atendente` (`atendente_id`);

ALTER TABLE `mensagens_automaticas_departamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_ativo` (`ativo`);

ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pai_modulo` (`pai_id`);

ALTER TABLE `sessoes_whatsapp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `idx_departamento` (`departamento_id`);

ALTER TABLE `templates_departamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_categoria` (`categoria`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
-- --------------------------------------------------------

ALTER TABLE `atendentes_departamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contato_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `conversas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `credenciais_serpro_departamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `log_acessos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mensagens_automaticas_departamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sessoes_whatsapp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `templates_departamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Relacionamentos
-- --------------------------------------------------------

ALTER TABLE `atendentes_departamento`
  ADD CONSTRAINT `fk_atendente_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_atendente_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

ALTER TABLE `contatos`
  ADD CONSTRAINT `fk_contato_sessao` FOREIGN KEY (`sessao_id`) REFERENCES `sessoes_whatsapp` (`id`) ON DELETE CASCADE;

ALTER TABLE `contato_tags`
  ADD CONSTRAINT `fk_contato_tags_contato` FOREIGN KEY (`contato_id`) REFERENCES `contatos` (`id`) ON DELETE CASCADE;

ALTER TABLE `conversas`
  ADD CONSTRAINT `fk_conversa_atendente` FOREIGN KEY (`atendente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_conversa_contato` FOREIGN KEY (`contato_id`) REFERENCES `contatos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversa_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`),
  ADD CONSTRAINT `fk_conversa_sessao` FOREIGN KEY (`sessao_id`) REFERENCES `sessoes_whatsapp` (`id`) ON DELETE CASCADE;

ALTER TABLE `credenciais_serpro_departamento`
  ADD CONSTRAINT `fk_credencial_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE;

ALTER TABLE `log_acessos`
  ADD CONSTRAINT `fk_usuario_log` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

ALTER TABLE `mensagens`
  ADD CONSTRAINT `fk_mensagem_atendente` FOREIGN KEY (`atendente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mensagem_contato` FOREIGN KEY (`contato_id`) REFERENCES `contatos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mensagem_conversa` FOREIGN KEY (`conversa_id`) REFERENCES `conversas` (`id`) ON DELETE CASCADE;

ALTER TABLE `mensagens_automaticas_departamento`
  ADD CONSTRAINT `fk_mensagem_auto_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE;

ALTER TABLE `modulos`
  ADD CONSTRAINT `fk_pai_modulo` FOREIGN KEY (`pai_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;

ALTER TABLE `sessoes_whatsapp`
  ADD CONSTRAINT `fk_sessao_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;

ALTER TABLE `templates_departamento`
  ADD CONSTRAINT `fk_template_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------
-- Triggers
-- --------------------------------------------------------

DELIMITER $$
CREATE TRIGGER `tr_credenciais_update` BEFORE UPDATE ON `credenciais_serpro_departamento` FOR EACH ROW 
BEGIN
    SET NEW.atualizado_em = NOW();
END
$$

CREATE TRIGGER `tr_departamentos_update` BEFORE UPDATE ON `departamentos` FOR EACH ROW 
BEGIN
    SET NEW.atualizado_em = NOW();
END
$$
DELIMITER ;

COMMIT;