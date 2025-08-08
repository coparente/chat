
DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=CURRENT_USER PROCEDURE `sp_atendentes_departamento` (IN `p_departamento_id` INT)   BEGIN
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

CREATE DEFINER=CURRENT_USER PROCEDURE `sp_obter_credencial_departamento` (IN `p_departamento_id` INT)   BEGIN
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
-- Estrutura das tabelas (ORDEM CORRIGIDA)
-- --------------------------------------------------------

-- Tabela independente (deve vir primeiro)
CREATE TABLE departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT DEFAULT NULL,
    cor VARCHAR(7) DEFAULT '#007bff',
    icone VARCHAR(50) DEFAULT 'fas fa-building',
    status ENUM('ativo','inativo') DEFAULT 'ativo',
    prioridade INT DEFAULT 0,
    configuracoes LONGTEXT DEFAULT NULL CHECK (json_valid(configuracoes)),
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela independente
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('admin','supervisor','atendente') DEFAULT 'atendente',
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('ativo','inativo','ausente','ocupado') DEFAULT 'ativo',
    max_chats INT DEFAULT 5,
    ultimo_acesso DATETIME DEFAULT NULL,
    token_recuperacao VARCHAR(64) DEFAULT NULL,
    token_expiracao DATETIME DEFAULT NULL,
    configuracoes LONGTEXT DEFAULT NULL CHECK (json_valid(configuracoes)),
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Depende de departamentos
CREATE TABLE sessoes_whatsapp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT DEFAULT NULL,
    nome VARCHAR(100) NOT NULL,
    numero VARCHAR(20) NOT NULL UNIQUE,
    serpro_session_id VARCHAR(255) DEFAULT NULL,
    serpro_waba_id VARCHAR(255) DEFAULT NULL,
    serpro_phone_number_id VARCHAR(255) DEFAULT NULL,
    webhook_token VARCHAR(255) DEFAULT NULL,
    status ENUM('conectado','desconectado','conectando','erro') DEFAULT 'desconectado',
    qr_code TEXT DEFAULT NULL,
    ultima_conexao DATETIME DEFAULT NULL,
    configuracoes LONGTEXT DEFAULT NULL CHECK (json_valid(configuracoes)),
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sessao_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Depende de sessoes_whatsapp
CREATE TABLE contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) DEFAULT NULL,
    numero VARCHAR(20) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    empresa VARCHAR(255) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    fonte ENUM('manual','whatsapp','importacao','api') DEFAULT 'manual',
    ultimo_contato TIMESTAMP NULL DEFAULT NULL,
    tags LONGTEXT DEFAULT NULL CHECK (json_valid(tags)),
    notas TEXT DEFAULT NULL,
    sessao_id INT NOT NULL,
    bloqueado TINYINT(1) DEFAULT 0,
    ultima_mensagem DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_numero_sessao (numero, sessao_id),
    CONSTRAINT fk_contato_sessao FOREIGN KEY (sessao_id) REFERENCES sessoes_whatsapp(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Depende de contatos, usuarios, sessoes_whatsapp e departamentos
CREATE TABLE conversas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contato_id INT NOT NULL,
    atendente_id INT DEFAULT NULL,
    sessao_id INT NOT NULL,
    status ENUM('aberto','pendente','fechado','transferindo') DEFAULT 'pendente',
    prioridade ENUM('baixa','normal','alta','urgente') DEFAULT 'normal',
    departamento_id INT DEFAULT NULL,
    tags LONGTEXT DEFAULT NULL CHECK (json_valid(tags)),
    notas_internas TEXT DEFAULT NULL,
    tempo_resposta_medio INT DEFAULT 0,
    ultima_mensagem DATETIME DEFAULT NULL,
    fechado_em DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_conversa_contato FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE,
    CONSTRAINT fk_conversa_atendente FOREIGN KEY (atendente_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_conversa_sessao FOREIGN KEY (sessao_id) REFERENCES sessoes_whatsapp(id) ON DELETE CASCADE,
    CONSTRAINT fk_conversa_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demais tabelas (já podem ser criadas na ordem original)
CREATE TABLE atendentes_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    departamento_id INT NOT NULL,
    perfil ENUM('atendente','supervisor','admin') DEFAULT 'atendente',
    status ENUM('ativo','inativo') DEFAULT 'ativo',
    max_conversas INT DEFAULT 5,
    horario_inicio TIME DEFAULT '08:00:00',
    horario_fim TIME DEFAULT '18:00:00',
    dias_semana LONGTEXT DEFAULT NULL CHECK (json_valid(dias_semana)),
    configuracoes LONGTEXT DEFAULT NULL CHECK (json_valid(configuracoes)),
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_departamento (usuario_id, departamento_id),
    CONSTRAINT fk_atendente_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
    CONSTRAINT fk_atendente_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT DEFAULT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_usuario (usuario_id),
    CONSTRAINT fk_atividade_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descricao TEXT DEFAULT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contato_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contato_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_contato_tag (contato_id, tag),
    CONSTRAINT fk_contato_tags_contato FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE credenciais_serpro_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    client_id VARCHAR(255) NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    base_url VARCHAR(255) DEFAULT 'https://api.whatsapp.serpro.gov.br',
    waba_id VARCHAR(255) NOT NULL,
    phone_number_id VARCHAR(255) NOT NULL,
    webhook_verify_token VARCHAR(255) DEFAULT NULL,
    status ENUM('ativo','inativo','teste') DEFAULT 'ativo',
    prioridade INT DEFAULT 0,
    configuracoes LONGTEXT DEFAULT NULL CHECK (json_valid(configuracoes)),
    token_cache TEXT DEFAULT NULL,
    token_expiracao DATETIME DEFAULT NULL,
    ultimo_teste DATETIME DEFAULT NULL,
    resultado_teste LONGTEXT DEFAULT NULL CHECK (json_valid(resultado_teste)),
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_credencial_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE log_acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    ip VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    sucesso TINYINT(1) DEFAULT 0,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_usuario (usuario_id),
    CONSTRAINT fk_usuario_log FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversa_id INT NOT NULL,
    contato_id INT NOT NULL,
    atendente_id INT DEFAULT NULL,
    serpro_message_id VARCHAR(255) DEFAULT NULL,
    tipo ENUM('texto','imagem','audio','video','documento','localizacao','contato','sistema') DEFAULT 'texto',
    conteudo TEXT NOT NULL,
    midia_url VARCHAR(500) DEFAULT NULL,
    midia_nome VARCHAR(255) DEFAULT NULL,
    midia_tipo VARCHAR(100) DEFAULT NULL,
    direcao ENUM('entrada','saida') NOT NULL,
    status_entrega ENUM('enviando','enviado','entregue','lido','erro') DEFAULT 'enviando',
    lida TINYINT(1) DEFAULT 0,
    lida_em DATETIME DEFAULT NULL,
    metadata LONGTEXT DEFAULT NULL CHECK (json_valid(metadata)),
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensagem_conversa FOREIGN KEY (conversa_id) REFERENCES conversas(id) ON DELETE CASCADE,
    CONSTRAINT fk_mensagem_contato FOREIGN KEY (contato_id) REFERENCES contatos(id) ON DELETE CASCADE,
    CONSTRAINT fk_mensagem_atendente FOREIGN KEY (atendente_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mensagens_automaticas_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    tipo ENUM('boas_vindas','ausencia','encerramento','fora_horario','personalizada') NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    horario_inicio TIME DEFAULT NULL,
    horario_fim TIME DEFAULT NULL,
    dias_semana LONGTEXT DEFAULT NULL CHECK (json_valid(dias_semana)),
    configuracoes LONGTEXT DEFAULT NULL CHECK (json_valid(configuracoes)),
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensagem_auto_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT DEFAULT NULL,
    rota VARCHAR(255) NOT NULL,
    icone VARCHAR(50) DEFAULT NULL,
    pai_id INT DEFAULT NULL,
    status ENUM('ativo','inativo') DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL,
    CONSTRAINT fk_pai_modulo FOREIGN KEY (pai_id) REFERENCES modulos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE templates_departamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    nome_serpro VARCHAR(100) NOT NULL,
    descricao TEXT DEFAULT NULL,
    categoria VARCHAR(50) DEFAULT 'geral',
    parametros LONGTEXT DEFAULT NULL CHECK (json_valid(parametros)),
    exemplo_parametros LONGTEXT DEFAULT NULL CHECK (json_valid(exemplo_parametros)),
    status ENUM('ativo','inativo') DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_template_departamento FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Views (mantidas no final)
-- --------------------------------------------------------

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW v_credenciais_ativas AS 
SELECT 
    d.id AS departamento_id,
    d.nome AS departamento_nome,
    cs.id AS credencial_id,
    cs.nome AS credencial_nome,
    cs.status AS credencial_status,
    cs.prioridade AS credencial_prioridade,
    cs.ultimo_teste AS ultimo_teste,
    cs.resultado_teste AS resultado_teste
FROM departamentos d
LEFT JOIN credenciais_serpro_departamento cs 
    ON d.id = cs.departamento_id
WHERE d.status = 'ativo' 
AND cs.status = 'ativo'
ORDER BY d.prioridade ASC, cs.prioridade ASC;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW v_estatisticas_departamento AS 
SELECT 
    d.id AS departamento_id,
    d.nome AS departamento_nome,
    d.cor AS departamento_cor,
    COUNT(c.id) AS total_conversas,
    SUM(CASE WHEN c.status = 'aberto' THEN 1 ELSE 0 END) AS conversas_abertas,
    SUM(CASE WHEN c.status = 'pendente' THEN 1 ELSE 0 END) AS conversas_pendentes,
    SUM(CASE WHEN c.status = 'fechado' THEN 1 ELSE 0 END) AS conversas_fechadas,
    COUNT(DISTINCT c.atendente_id) AS atendentes_ativos,
    AVG(c.tempo_resposta_medio) AS tempo_resposta_medio
FROM departamentos d
LEFT JOIN conversas c 
    ON d.id = c.departamento_id
WHERE d.status = 'ativo'
GROUP BY d.id, d.nome, d.cor;