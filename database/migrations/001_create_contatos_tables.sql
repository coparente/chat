-- ============================================================================
-- MIGRAÇÃO DO BANCO DE DADOS - SISTEMA DE CONTATOS CHATSERPRO
-- ============================================================================
-- Execute este SQL no seu banco de dados para criar as tabelas necessárias
-- para o sistema de gerenciamento de contatos

-- Criação da tabela de contatos
CREATE TABLE IF NOT EXISTS `contatos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL,
    `telefone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(255) NULL,
    `empresa` VARCHAR(255) NULL,
    `observacoes` TEXT NULL,
    `fonte` ENUM('manual', 'whatsapp', 'importacao', 'api') DEFAULT 'manual',
    `bloqueado` TINYINT(1) DEFAULT 0,
    `ultimo_contato` TIMESTAMP NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `telefone_unique` (`telefone`),
    INDEX `idx_nome` (`nome`),
    INDEX `idx_telefone` (`telefone`),
    INDEX `idx_email` (`email`),
    INDEX `idx_bloqueado` (`bloqueado`),
    INDEX `idx_ultimo_contato` (`ultimo_contato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criação da tabela de tags dos contatos
CREATE TABLE IF NOT EXISTS `contato_tags` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `contato_id` INT(11) NOT NULL,
    `tag` VARCHAR(50) NOT NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`contato_id`) REFERENCES `contatos`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `contato_tag_unique` (`contato_id`, `tag`),
    INDEX `idx_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criação da tabela de conversas (se não existir)
CREATE TABLE IF NOT EXISTS `conversas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `contato_id` INT(11) NOT NULL,
    `usuario_id` INT(11) NULL,
    `status` ENUM('pendente', 'ativa', 'pausada', 'fechada') DEFAULT 'pendente',
    `origem` ENUM('whatsapp', 'manual', 'sistema') DEFAULT 'whatsapp',
    `protocolo` VARCHAR(20) NULL,
    `prioridade` ENUM('baixa', 'normal', 'alta', 'urgente') DEFAULT 'normal',
    `tags` TEXT NULL,
    `iniciada_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `finalizada_em` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`contato_id`) REFERENCES `contatos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_contato_id` (`contato_id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_protocolo` (`protocolo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criação da tabela de mensagens (se não existir)
CREATE TABLE IF NOT EXISTS `mensagens` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `conversa_id` INT(11) NOT NULL,
    `usuario_id` INT(11) NULL,
    `tipo` ENUM('recebida', 'enviada', 'sistema') NOT NULL,
    `tipo_conteudo` ENUM('texto', 'imagem', 'audio', 'video', 'documento', 'localizacao') DEFAULT 'texto',
    `conteudo` TEXT NOT NULL,
    `arquivo_url` VARCHAR(500) NULL,
    `arquivo_nome` VARCHAR(255) NULL,
    `arquivo_tamanho` INT(11) NULL,
    `whatsapp_id` VARCHAR(100) NULL,
    `status` ENUM('pendente', 'enviada', 'entregue', 'lida', 'erro') DEFAULT 'pendente',
    `lida_em` TIMESTAMP NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`conversa_id`) REFERENCES `conversas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_conversa_id` (`conversa_id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_whatsapp_id` (`whatsapp_id`),
    INDEX `idx_criado_em` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo para contatos (opcional)
INSERT IGNORE INTO `contatos` (`nome`, `telefone`, `email`, `empresa`, `observacoes`, `fonte`) VALUES
('João Silva', '(11) 99999-1234', 'joao@exemplo.com', 'Empresa ABC', 'Cliente preferencial', 'manual'),
('Maria Santos', '(11) 98888-5678', 'maria@exemplo.com', 'Empresa XYZ', 'Interesse em produtos', 'whatsapp'),
('Pedro Oliveira', '(11) 97777-9012', NULL, NULL, 'Suporte técnico', 'manual');

-- Inserir tags de exemplo
INSERT IGNORE INTO `contato_tags` (`contato_id`, `tag`) VALUES
(1, 'cliente'),
(1, 'vip'),
(2, 'prospect'),
(2, 'interessado'),
(3, 'suporte'); 