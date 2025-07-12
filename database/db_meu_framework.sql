-- Criar o banco meu_framework se não existir
CREATE DATABASE IF NOT EXISTS meu_framework CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meu_framework;

-- Estrutura para tabela usuarios
DROP TABLE IF EXISTS permissoes_usuario;
DROP TABLE IF EXISTS log_acessos;
DROP TABLE IF EXISTS modulos;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('usuario', 'analista', 'admin') DEFAULT 'usuario',
    biografia TEXT DEFAULT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    ultimo_acesso DATETIME DEFAULT NULL,
    token_recuperacao VARCHAR(64) DEFAULT NULL,
    token_expiracao DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL
);

-- Estrutura para tabela log_acessos
CREATE TABLE log_acessos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    email VARCHAR(255) DEFAULT NULL,
    ip VARCHAR(45) NOT NULL,
    sucesso BOOLEAN NOT NULL DEFAULT FALSE,
    data_hora DATETIME NOT NULL,
    CONSTRAINT fk_usuario_log FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Estrutura para tabela modulos
CREATE TABLE modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT DEFAULT NULL,
    rota VARCHAR(255) NOT NULL,
    icone VARCHAR(50) DEFAULT NULL,
    pai_id INT DEFAULT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL,
    CONSTRAINT fk_pai_modulo FOREIGN KEY (pai_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Estrutura para tabela permissoes_usuario
CREATE TABLE permissoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo_id INT NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT NULL,
    UNIQUE KEY uq_usuario_modulo (usuario_id, modulo_id),
    CONSTRAINT fk_usuario_permissao FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_modulo_permissao FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Inserção de dados iniciais para a tabela usuarios
INSERT INTO usuarios (nome, email, senha, perfil, biografia, status, criado_em) VALUES
('Administrador', 'admin@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'admin', 'Administrador do Sistema', 'ativo', NOW()),
('Analista', 'analista@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'analista', 'Analista do Sistema', 'ativo', NOW()),
('Usuário', 'usuario@gmail.com', '$2y$10$0W8VdFyOmiXd3eFvyndgg.dj9nSbnttGvJYW29VcfU4TOrbM1cSfi', 'usuario', 'Usuário do Sistema', 'ativo', NOW());

-- Inserção de dados iniciais para a tabela modulos
INSERT INTO modulos (nome, descricao, rota, icone, pai_id, status, criado_em) VALUES
('Dashboard', 'Painel principal do sistema', 'dashboard', 'fas fa-tachometer-alt', NULL, 'ativo', NOW()),
('Usuários', 'Gerenciamento de usuários', 'usuarios', 'fas fa-users', NULL, 'ativo', NOW()),
('Módulos', 'Gerenciamento de módulos', 'modulos', 'fas fa-cubes', NULL, 'ativo', NOW()),
('Configurações', 'Configurações do sistema', 'configuracoes', 'fas fa-cog', NULL, 'ativo', NOW());

-- Inserção de submódulos
INSERT INTO modulos (nome, descricao, rota, icone, pai_id, status, criado_em) VALUES
('Listar Usuários', 'Visualizar lista de usuários', 'usuarios/listar', 'fas fa-list', 2, 'ativo', NOW()),
('Cadastrar Usuário', 'Cadastrar novo usuário', 'usuarios/cadastrar', 'fas fa-user-plus', 2, 'ativo', NOW()),
('Permissões', 'Gerenciar permissões', 'usuarios/permissoes', 'fas fa-key', 2, 'ativo', NOW());

-- Inserir permissões para o administrador (todos os módulos)
INSERT INTO permissoes_usuario (usuario_id, modulo_id, criado_em) VALUES
(1, 1, NOW()), -- Dashboard
(1, 2, NOW()), -- Usuários
(1, 3, NOW()), -- Módulos
(1, 4, NOW()), -- Configurações
(1, 5, NOW()), -- Listar Usuários
(1, 6, NOW()), -- Cadastrar Usuário
(1, 7, NOW()); -- Permissões

-- Inserir permissões para o analista (módulos limitados)
INSERT INTO permissoes_usuario (usuario_id, modulo_id, criado_em) VALUES
(2, 1, NOW()), -- Dashboard
(2, 2, NOW()), -- Usuários
(2, 5, NOW()); -- Listar Usuários

-- Inserir permissões para o usuário comum (apenas dashboard)
INSERT INTO permissoes_usuario (usuario_id, modulo_id, criado_em) VALUES
(3, 1, NOW()); -- Dashboard
