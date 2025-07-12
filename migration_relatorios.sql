-- Migration para campos necessários nos relatórios
-- Execute este SQL no seu banco de dados

-- Adicionar campos para tempo de resposta se não existirem
ALTER TABLE mensagens 
ADD COLUMN IF NOT EXISTS tempo_resposta INT DEFAULT NULL COMMENT 'Tempo de resposta em minutos';

-- Adicionar campos para avaliação nas conversas se não existirem
ALTER TABLE conversas 
ADD COLUMN IF NOT EXISTS avaliacao DECIMAL(2,1) DEFAULT NULL COMMENT 'Avaliação da conversa (1-5)',
ADD COLUMN IF NOT EXISTS tempo_resposta_medio DECIMAL(10,2) DEFAULT NULL COMMENT 'Tempo médio de resposta em minutos';

-- Criar índices para melhorar performance dos relatórios
CREATE INDEX IF NOT EXISTS idx_mensagens_data_tipo ON mensagens(criado_em, tipo);
CREATE INDEX IF NOT EXISTS idx_mensagens_atendente_direcao ON mensagens(atendente_id, direcao);
CREATE INDEX IF NOT EXISTS idx_conversas_data_status ON conversas(criado_em, status);
CREATE INDEX IF NOT EXISTS idx_conversas_atendente ON conversas(atendente_id);

-- Exemplo de como calcular tempo de resposta (execute após mensagens serem enviadas)
-- UPDATE mensagens m1 
-- SET tempo_resposta = (
--     SELECT TIMESTAMPDIFF(MINUTE, m2.criado_em, m1.criado_em)
--     FROM mensagens m2 
--     WHERE m2.conversa_id = m1.conversa_id 
--     AND m2.direcao = 'entrada' 
--     AND m2.criado_em < m1.criado_em 
--     ORDER BY m2.criado_em DESC 
--     LIMIT 1
-- )
-- WHERE m1.direcao = 'saida' AND m1.tempo_resposta IS NULL; 