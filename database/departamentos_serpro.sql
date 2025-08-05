-- =====================================================
-- SISTEMA DE DEPARTAMENTOS COM MÚLTIPLAS CREDENCIAIS SERPRO
-- =====================================================

USE meu_framework;

-- =====================================================
-- TABELA: DEPARTAMENTOS
-- =====================================================
CREATE TABLE IF NOT EXISTS departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT DEFAULT NULL,
    cor VARCHAR(7) DEFAULT '#007bff', -- Cor do departamento (hex)
    icone VARCHAR(50) DEFAULT 'fas fa-building',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    prioridade INT DEFAULT 0, -- Ordem de exibição
    configuracoes JSON DEFAULT NULL, -- Configurações específicas do departamento
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade)
);

-- =====================================================
-- TABELA: CREDENCIAIS SERPRO POR DEPARTAMENTO
-- =====================================================
CREATE TABLE IF NOT EXISTS credenciais_serpro_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL, -- Nome da credencial (ex: "Credencial Principal", "Backup")
    client_id VARCHAR(255) NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    base_url VARCHAR(255) NOT NULL DEFAULT 'https://api.whatsapp.serpro.gov.br',
    waba_id VARCHAR(255) NOT NULL,
    phone_number_id VARCHAR(255) NOT NULL,
    webhook_verify_token VARCHAR(255) DEFAULT NULL,
    status ENUM('ativo', 'inativo', 'teste') DEFAULT 'ativo',
    prioridade INT DEFAULT 0, -- Ordem de uso (0 = principal)
    configuracoes JSON DEFAULT NULL, -- Configurações específicas
    token_cache TEXT DEFAULT NULL, -- Cache do token JWT
    token_expiracao DATETIME DEFAULT NULL,
    ultimo_teste DATETIME DEFAULT NULL,
    resultado_teste JSON DEFAULT NULL, -- Resultado do último teste
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_departamento (departamento_id),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade),
    CONSTRAINT fk_credencial_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA: ATENDENTES POR DEPARTAMENTO
-- =====================================================
CREATE TABLE IF NOT EXISTS atendentes_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    departamento_id INT NOT NULL,
    perfil ENUM('atendente', 'supervisor', 'admin') DEFAULT 'atendente',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    max_conversas INT DEFAULT 5,
    horario_inicio TIME DEFAULT '08:00:00',
    horario_fim TIME DEFAULT '18:00:00',
    dias_semana JSON DEFAULT NULL, -- [1,2,3,4,5] para segunda a sexta
    configuracoes JSON DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_departamento (usuario_id, departamento_id),
    INDEX idx_departamento (departamento_id),
    INDEX idx_status (status),
    CONSTRAINT fk_atendente_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_atendente_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA: TEMPLATES POR DEPARTAMENTO
-- =====================================================
CREATE TABLE IF NOT EXISTS templates_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    nome_serpro VARCHAR(100) NOT NULL, -- Nome do template na API Serpro
    descricao TEXT DEFAULT NULL,
    categoria VARCHAR(50) DEFAULT 'geral',
    parametros JSON DEFAULT NULL, -- Estrutura dos parâmetros
    exemplo_parametros JSON DEFAULT NULL, -- Exemplo de uso
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_departamento (departamento_id),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria),
    CONSTRAINT fk_template_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA: MENSAGENS AUTOMÁTICAS POR DEPARTAMENTO
-- =====================================================
CREATE TABLE IF NOT EXISTS mensagens_automaticas_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    tipo ENUM('boas_vindas', 'ausencia', 'encerramento', 'fora_horario', 'personalizada') NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    horario_inicio TIME DEFAULT NULL,
    horario_fim TIME DEFAULT NULL,
    dias_semana JSON DEFAULT NULL, -- Para mensagens de fora de horário
    configuracoes JSON DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_departamento (departamento_id),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo),
    CONSTRAINT fk_mensagem_auto_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
);

-- =====================================================
-- INSERIR DEPARTAMENTOS PADRÃO
-- =====================================================
INSERT INTO departamentos (nome, descricao, cor, icone, prioridade) VALUES
('Geral', 'Departamento padrão do sistema', '#007bff', 'fas fa-building', 0),
('Suporte Técnico', 'Atendimento técnico e suporte', '#28a745', 'fas fa-tools', 1),
('Comercial', 'Vendas e atendimento comercial', '#ffc107', 'fas fa-shopping-cart', 2),
('Financeiro', 'Cobranças e assuntos financeiros', '#dc3545', 'fas fa-dollar-sign', 3),
('RH', 'Recursos Humanos', '#6f42c1', 'fas fa-users', 4),
('Jurídico', 'Assuntos jurídicos e legais', '#fd7e14', 'fas fa-gavel', 5);

-- =====================================================
-- INSERIR CREDENCIAIS SERPRO PADRÃO (EXEMPLO)
-- =====================================================
-- Nota: Estas são credenciais de exemplo. Substitua pelas reais
INSERT INTO credenciais_serpro_departamento (departamento_id, nome, client_id, client_secret, waba_id, phone_number_id, prioridade) VALUES
(1, 'Credencial Principal', 'client_id_geral', 'client_secret_geral', 'waba_id_geral', 'phone_number_id_geral', 0),
(2, 'Credencial Suporte', 'client_id_suporte', 'client_secret_suporte', 'waba_id_suporte', 'phone_number_id_suporte', 0),
(3, 'Credencial Comercial', 'client_id_comercial', 'client_secret_comercial', 'waba_id_comercial', 'phone_number_id_comercial', 0);

-- =====================================================
-- INSERIR TEMPLATES PADRÃO
-- =====================================================
INSERT INTO templates_departamento (departamento_id, nome, nome_serpro, descricao, categoria) VALUES
(1, 'Boas-vindas Geral', 'boas_vindas_geral', 'Template de boas-vindas para o departamento geral', 'boas_vindas'),
(2, 'Boas-vindas Suporte', 'boas_vindas_suporte', 'Template de boas-vindas para suporte técnico', 'boas_vindas'),
(3, 'Boas-vindas Comercial', 'boas_vindas_comercial', 'Template de boas-vindas para comercial', 'boas_vindas');

-- =====================================================
-- INSERIR MENSAGENS AUTOMÁTICAS PADRÃO
-- =====================================================
INSERT INTO mensagens_automaticas_departamento (departamento_id, tipo, titulo, mensagem) VALUES
(1, 'boas_vindas', 'Boas-vindas', 'Olá! Bem-vindo ao nosso atendimento. Como posso ajudá-lo hoje?'),
(1, 'encerramento', 'Encerramento', 'Obrigado por entrar em contato conosco. Tenha um ótimo dia!'),
(1, 'fora_horario', 'Fora do Horário', 'Estamos fora do horário de atendimento. Retornaremos em breve.'),
(2, 'boas_vindas', 'Suporte Técnico', 'Olá! Você está no suporte técnico. Como posso ajudá-lo?'),
(2, 'encerramento', 'Encerramento Suporte', 'Agradecemos seu contato. Se precisar de mais ajuda, estamos à disposição.'),
(3, 'boas_vindas', 'Comercial', 'Olá! Você está no departamento comercial. Como posso ajudá-lo?'),
(3, 'encerramento', 'Encerramento Comercial', 'Obrigado pelo contato. Até a próxima!');

-- =====================================================
-- ATUALIZAR TABELA CONVERSAS PARA REFERENCIAR DEPARTAMENTO
-- =====================================================
-- Adicionar índice para departamento se não existir
ALTER TABLE conversas ADD INDEX idx_departamento (departamento);

-- =====================================================
-- ATUALIZAR TABELA SESSÕES WHATSAPP PARA SUPORTAR DEPARTAMENTOS
-- =====================================================
-- Adicionar coluna departamento_id se necessário
ALTER TABLE sessoes_whatsapp ADD COLUMN departamento_id INT DEFAULT NULL AFTER id;
ALTER TABLE sessoes_whatsapp ADD INDEX idx_departamento (departamento_id);
ALTER TABLE sessoes_whatsapp ADD CONSTRAINT fk_sessao_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL;

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View para estatísticas por departamento
CREATE OR REPLACE VIEW v_estatisticas_departamento AS
SELECT 
    d.id as departamento_id,
    d.nome as departamento_nome,
    d.cor as departamento_cor,
    COUNT(c.id) as total_conversas,
    SUM(CASE WHEN c.status = 'aberto' THEN 1 ELSE 0 END) as conversas_abertas,
    SUM(CASE WHEN c.status = 'pendente' THEN 1 ELSE 0 END) as conversas_pendentes,
    SUM(CASE WHEN c.status = 'fechado' THEN 1 ELSE 0 END) as conversas_fechadas,
    COUNT(DISTINCT c.atendente_id) as atendentes_ativos,
    AVG(c.tempo_resposta_medio) as tempo_resposta_medio
FROM departamentos d
LEFT JOIN conversas c ON d.nome = c.departamento
WHERE d.status = 'ativo'
GROUP BY d.id, d.nome, d.cor;

-- View para credenciais ativas por departamento
CREATE OR REPLACE VIEW v_credenciais_ativas AS
SELECT 
    d.id as departamento_id,
    d.nome as departamento_nome,
    cs.id as credencial_id,
    cs.nome as credencial_nome,
    cs.status as credencial_status,
    cs.prioridade as credencial_prioridade,
    cs.ultimo_teste,
    cs.resultado_teste
FROM departamentos d
LEFT JOIN credenciais_serpro_departamento cs ON d.id = cs.departamento_id
WHERE d.status = 'ativo' AND cs.status = 'ativo'
ORDER BY d.prioridade, cs.prioridade;

-- =====================================================
-- PROCEDURES ÚTEIS
-- =====================================================

-- Procedure para obter credencial ativa de um departamento
DELIMITER //
CREATE PROCEDURE sp_obter_credencial_departamento(IN p_departamento_id INT)
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
END //
DELIMITER ;

-- Procedure para listar atendentes de um departamento
DELIMITER //
CREATE PROCEDURE sp_atendentes_departamento(IN p_departamento_id INT)
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
END //
DELIMITER ;

-- =====================================================
-- TRIGGERS ÚTEIS
-- =====================================================

-- Trigger para atualizar timestamp de atualização
DELIMITER //
CREATE TRIGGER tr_departamentos_update 
BEFORE UPDATE ON departamentos
FOR EACH ROW
BEGIN
    SET NEW.atualizado_em = NOW();
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER tr_credenciais_update 
BEFORE UPDATE ON credenciais_serpro_departamento
FOR EACH ROW
BEGIN
    SET NEW.atualizado_em = NOW();
END //
DELIMITER ;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================
CREATE INDEX idx_credenciais_departamento_status ON credenciais_serpro_departamento(departamento_id, status);
CREATE INDEX idx_templates_departamento_status ON templates_departamento(departamento_id, status);
CREATE INDEX idx_mensagens_auto_departamento_tipo ON mensagens_automaticas_departamento(departamento_id, tipo, ativo);
CREATE INDEX idx_atendentes_departamento_status ON atendentes_departamento(departamento_id, status); 


-- Adicionar departamento_id na tabela conversas
ALTER TABLE conversas ADD COLUMN departamento_id INT DEFAULT NULL;
ALTER TABLE conversas ADD CONSTRAINT fk_conversa_departamento 
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id);

-- Migrar dados existentes
UPDATE conversas SET departamento_id = 1 WHERE departamento = 'Geral';