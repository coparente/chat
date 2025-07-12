-- =====================================================
-- BANCO DE DADOS: SISTEMA DE CHAT MULTIATENDIMENTO 
-- =====================================================

-- Criar o banco meu_framework se não existir
CREATE DATABASE IF NOT EXISTS meu_framework CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meu_framework;

-- Remover tabelas existentes na ordem correta (respeitando foreign keys)
DROP TABLE IF EXISTS mensagens;
DROP TABLE IF EXISTS conversas;
DROP TABLE IF EXISTS contatos;
DROP TABLE IF EXISTS sessoes_whatsapp;
DROP TABLE IF EXISTS permissoes_usuario;
DROP TABLE IF EXISTS log_acessos;
DROP TABLE IF EXISTS modulos;
DROP TABLE IF EXISTS usuarios;

-- =====================================================
-- TABELA: USUÁRIOS (ATENDENTES, SUPERVISORES, ADMINS)
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('admin', 'supervisor', 'atendente') DEFAULT 'atendente',
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('ativo', 'inativo', 'ausente', 'ocupado') DEFAULT 'ativo',
    max_chats INT DEFAULT 5,
    ultimo_acesso DATETIME DEFAULT NULL,
    token_recuperacao VARCHAR(64) DEFAULT NULL,
    token_expiracao DATETIME DEFAULT NULL,
    configuracoes JSON DEFAULT NULL, -- Para guardar preferências do usuário
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABELA: SESSÕES WHATSAPP (CONEXÕES ATIVAS)
-- =====================================================
CREATE TABLE sessoes_whatsapp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    numero VARCHAR(20) NOT NULL UNIQUE,
    serpro_session_id VARCHAR(255) DEFAULT NULL,
    serpro_waba_id VARCHAR(255) DEFAULT NULL,
    serpro_phone_number_id VARCHAR(255) DEFAULT NULL,
    webhook_token VARCHAR(255) DEFAULT NULL,
    status ENUM('conectado', 'desconectado', 'conectando', 'erro') DEFAULT 'desconectado',
    qr_code TEXT DEFAULT NULL,
    ultima_conexao DATETIME DEFAULT NULL,
    configuracoes JSON DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABELA: CONTATOS
-- =====================================================
CREATE TABLE contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) DEFAULT NULL,
    numero VARCHAR(20) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    tags JSON DEFAULT NULL, -- ['cliente', 'vip', 'suporte']
    notas TEXT DEFAULT NULL,
    sessao_id INT NOT NULL,
    bloqueado BOOLEAN DEFAULT FALSE,
    ultima_mensagem DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_numero (numero),
    INDEX idx_sessao (sessao_id),
    CONSTRAINT fk_contato_sessao FOREIGN KEY (sessao_id) REFERENCES sessoes_whatsapp(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA: CONVERSAS (TICKETS/ATENDIMENTOS)
-- =====================================================
CREATE TABLE conversas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contato_id INT NOT NULL,
    atendente_id INT DEFAULT NULL,
    sessao_id INT NOT NULL,
    status ENUM('aberto', 'pendente', 'fechado', 'transferindo') DEFAULT 'pendente',
    prioridade ENUM('baixa', 'normal', 'alta', 'urgente') DEFAULT 'normal',
    departamento VARCHAR(100) DEFAULT 'Geral',
    tags JSON DEFAULT NULL,
    notas_internas TEXT DEFAULT NULL,
    tempo_resposta_medio INT DEFAULT 0, -- em segundos
    ultima_mensagem DATETIME DEFAULT NULL,
    fechado_em DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_atendente (atendente_id),
    INDEX idx_contato (contato_id),
    INDEX idx_sessao (sessao_id),
    CONSTRAINT fk_conversa_contato FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE,
    CONSTRAINT fk_conversa_atendente FOREIGN KEY (atendente_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_conversa_sessao FOREIGN KEY (sessao_id) REFERENCES sessoes_whatsapp(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA: MENSAGENS
-- =====================================================
CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversa_id INT NOT NULL,
    contato_id INT NOT NULL,
    atendente_id INT DEFAULT NULL,
    serpro_message_id VARCHAR(255) DEFAULT NULL,
    tipo ENUM('texto', 'imagem', 'audio', 'video', 'documento', 'localizacao', 'contato', 'sistema') DEFAULT 'texto',
    conteudo TEXT NOT NULL,
    midia_url VARCHAR(500) DEFAULT NULL,
    midia_nome VARCHAR(255) DEFAULT NULL,
    midia_tipo VARCHAR(100) DEFAULT NULL,
    direcao ENUM('entrada', 'saida') NOT NULL,
    status_entrega ENUM('enviando', 'enviado', 'entregue', 'lido', 'erro') DEFAULT 'enviando',
    lida BOOLEAN DEFAULT FALSE,
    lida_em DATETIME DEFAULT NULL,
    metadata JSON DEFAULT NULL, -- Para guardar dados extras da API
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conversa (conversa_id),
    INDEX idx_contato (contato_id),
    INDEX idx_direcao (direcao),
    INDEX idx_data (criado_em),
    CONSTRAINT fk_mensagem_conversa FOREIGN KEY (conversa_id) REFERENCES conversas(id) ON DELETE CASCADE,
    CONSTRAINT fk_mensagem_contato FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE,
    CONSTRAINT fk_mensagem_atendente FOREIGN KEY (atendente_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =====================================================
-- TABELA: MÓDULOS (SISTEMA DE PERMISSÕES)
-- =====================================================
CREATE TABLE modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT DEFAULT NULL,
    rota VARCHAR(255) NOT NULL,
    icone VARCHAR(50) DEFAULT NULL,
    pai_id INT DEFAULT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pai_modulo FOREIGN KEY (pai_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA: PERMISSÕES DE USUÁRIO
-- =====================================================
CREATE TABLE permissoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo_id INT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_modulo (usuario_id, modulo_id),
    CONSTRAINT fk_usuario_permissao FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_modulo_permissao FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA: LOG DE ACESSOS
-- =====================================================
CREATE TABLE log_acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    email VARCHAR(255) DEFAULT NULL,
    ip VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    sucesso BOOLEAN NOT NULL DEFAULT FALSE,
    data_hora DATETIME NOT NULL,
    CONSTRAINT fk_usuario_log FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- =====================================================
-- INSERÇÃO DE DADOS INICIAIS
-- =====================================================

-- Usuários padrão do sistema
INSERT INTO usuarios (nome, email, senha, perfil, status, max_chats, criado_em) VALUES
('Administrador', 'admin@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'admin', 'ativo', 10, NOW()),
('Supervisor', 'supervisor@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'supervisor', 'ativo', 8, NOW()),
('Atendente 1', 'atendente1@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'atendente', 'ativo', 5, NOW()),
('Atendente 2', 'atendente2@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'atendente', 'ativo', 5, NOW());

-- Módulos do sistema de chat
INSERT INTO modulos (nome, descricao, rota, icone, pai_id, status, criado_em) VALUES
-- Módulos principais
('Dashboard', 'Painel principal com estatísticas', 'dashboard', 'fas fa-chart-line', NULL, 'ativo', NOW()),
('Chat', 'Sistema de conversas', 'chat', 'fas fa-comments', NULL, 'ativo', NOW()),
('Contatos', 'Gerenciamento de contatos', 'contatos', 'fas fa-address-book', NULL, 'ativo', NOW()),
('Relatórios', 'Relatórios e estatísticas', 'relatorios', 'fas fa-chart-bar', NULL, 'ativo', NOW()),
('Configurações', 'Configurações do sistema', 'configuracoes', 'fas fa-cog', NULL, 'ativo', NOW()),
('Usuários', 'Gerenciamento de usuários', 'usuarios', 'fas fa-users', NULL, 'ativo', NOW()),

-- Submódulos de Chat
('Conversas Ativas', 'Conversas em andamento', 'chat/ativas', 'fas fa-comment-dots', 2, 'ativo', NOW()),
('Conversas Pendentes', 'Conversas aguardando atendimento', 'chat/pendentes', 'fas fa-clock', 2, 'ativo', NOW()),
('Conversas Fechadas', 'Histórico de conversas', 'chat/fechadas', 'fas fa-archive', 2, 'ativo', NOW()),

-- Submódulos de Configurações  
('Conexões WhatsApp', 'Gerenciar conexões', 'configuracoes/conexoes', 'fab fa-whatsapp', 5, 'ativo', NOW()),
('API Serpro', 'Configurações da API', 'configuracoes/serpro', 'fas fa-plug', 5, 'ativo', NOW()),
('Mensagens Automáticas', 'Configurar respostas automáticas', 'configuracoes/mensagens', 'fas fa-robot', 5, 'ativo', NOW());

-- Sessão WhatsApp padrão
INSERT INTO sessoes_whatsapp (nome, numero, status, criado_em) VALUES
('Principal', '5511999999999', 'desconectado', NOW());

-- Permissões para Administrador (acesso total)
INSERT INTO permissoes_usuario (usuario_id, modulo_id, criado_em)
SELECT 1, id, NOW() FROM modulos WHERE status = 'ativo';

-- Permissões para Supervisor (tudo exceto configurações avançadas)
INSERT INTO permissoes_usuario (usuario_id, modulo_id, criado_em) VALUES
(2, 1, NOW()), -- Dashboard
(2, 2, NOW()), -- Chat
(2, 3, NOW()), -- Contatos
(2, 4, NOW()), -- Relatórios
(2, 6, NOW()), -- Usuários
(2, 7, NOW()), -- Conversas Ativas
(2, 8, NOW()), -- Conversas Pendentes
(2, 9, NOW()); -- Conversas Fechadas

-- Permissões para Atendentes (apenas chat e contatos)
INSERT INTO permissoes_usuario (usuario_id, modulo_id, criado_em) VALUES
(3, 1, NOW()), -- Dashboard
(3, 2, NOW()), -- Chat
(3, 3, NOW()), -- Contatos
(3, 7, NOW()), -- Conversas Ativas
(3, 8, NOW()), -- Conversas Pendentes
(3, 9, NOW()), -- Conversas Fechadas
(4, 1, NOW()), -- Dashboard
(4, 2, NOW()), -- Chat  
(4, 3, NOW()), -- Contatos
(4, 7, NOW()), -- Conversas Ativas
(4, 8, NOW()), -- Conversas Pendentes
(4, 9, NOW()); -- Conversas Fechadas 