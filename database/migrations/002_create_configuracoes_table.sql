-- ============================================================================
-- MIGRAÇÃO DO BANCO DE DADOS - TABELA DE CONFIGURAÇÕES
-- ============================================================================
-- Execute este SQL no seu banco de dados para criar a tabela de configurações
-- necessária para o sistema de configurações do ChatSerpro

-- Criação da tabela de configurações
CREATE TABLE IF NOT EXISTS `configuracoes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `chave` VARCHAR(100) NOT NULL,
    `valor` TEXT NOT NULL,
    `descricao` TEXT NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `chave_unique` (`chave`),
    INDEX `idx_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserção de configurações padrão
INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`) VALUES
('sistema_nome', '"ChatSerpro"', 'Nome do sistema'),
('sistema_versao', '"1.0.0"', 'Versão do sistema'),
('sistema_mantenedor', '"Equipe de Desenvolvimento"', 'Responsável pela manutenção'),
('chat_max_mensagens_historico', '1000', 'Máximo de mensagens no histórico'),
('chat_timeout_sessao', '1800', 'Timeout da sessão em segundos (30 minutos)'),
('webhook_timeout', '30', 'Timeout do webhook em segundos')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- Verificar se a tabela foi criada com sucesso
SELECT 'Tabela de configurações criada com sucesso!' as resultado; 