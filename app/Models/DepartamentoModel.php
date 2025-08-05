<?php

/**
 * [ DEPARTAMENTOMODEL ] - Model para gerenciar departamentos
 * 
 * Esta classe gerencia:
 * - CRUD de departamentos
 * - Associação de atendentes
 * - Configurações específicas
 * - Estatísticas por departamento
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class DepartamentoModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ listarTodos ] - Lista todos os departamentos
     * 
     * @param bool $apenasAtivos Se true, retorna apenas departamentos ativos
     * @return array Lista de departamentos
     */
    public function listarTodos($apenasAtivos = true)
    {
        $sql = "SELECT * FROM departamentos";
        
        if ($apenasAtivos) {
            $sql .= " WHERE status = 'ativo'";
        }
        
        $sql .= " ORDER BY prioridade ASC, nome ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ listarTodosComContagens ] - Lista todos os departamentos com contagens
     * 
     * @param bool $apenasAtivos Se true, retorna apenas departamentos ativos
     * @return array Lista de departamentos com contagens
     */
    public function listarTodosComContagens($apenasAtivos = true)
    {
        $sql = "SELECT 
                    d.*,
                    COUNT(DISTINCT cs.id) as credenciais_count,
                    COUNT(DISTINCT ad.usuario_id) as atendentes_count,
                    COUNT(DISTINCT c.id) as conversas_count
                FROM departamentos d
                LEFT JOIN credenciais_serpro_departamento cs ON d.id = cs.departamento_id
                LEFT JOIN atendentes_departamento ad ON d.id = ad.departamento_id
                LEFT JOIN conversas c ON d.id = c.departamento_id";
        
        if ($apenasAtivos) {
            $sql .= " WHERE d.status = 'ativo'";
        }
        
        $sql .= " GROUP BY d.id ORDER BY d.prioridade ASC, d.nome ASC";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ getEstatisticasDepartamentos ] - Obtém estatísticas gerais dos departamentos
     * 
     * @return object Estatísticas dos departamentos
     */
    public function getEstatisticasDepartamentos()
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN d.status = 'ativo' THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN d.status = 'inativo' THEN 1 ELSE 0 END) as inativos,
                COUNT(DISTINCT cs.departamento_id) as com_credenciais,
                (
                    SELECT COUNT(DISTINCT ad2.departamento_id) 
                    FROM atendentes_departamento ad2 
                    WHERE ad2.status = 'ativo'
                ) as com_atendentes
            FROM departamentos d
            LEFT JOIN credenciais_serpro_departamento cs ON d.id = cs.departamento_id AND cs.status = 'ativo'
        ";
        
        $this->db->query($sql);
        return $this->db->resultado();
    }

    /**
     * [ buscarPorId ] - Busca departamento por ID
     * 
     * @param int $id ID do departamento
     * @return object|null Departamento encontrado
     */
    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM departamentos WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * [ buscarPorNome ] - Busca departamento por nome
     * 
     * @param string $nome Nome do departamento
     * @return object|null Departamento encontrado
     */
    public function buscarPorNome($nome)
    {
        $sql = "SELECT * FROM departamentos WHERE nome = :nome";
        $this->db->query($sql);
        $this->db->bind(':nome', $nome);
        return $this->db->resultado();
    }

    /**
     * [ criar ] - Cria novo departamento
     * 
     * @param array $dados Dados do departamento
     * @return bool Sucesso da operação
     */
    public function criar($dados)
    {
        $sql = "INSERT INTO departamentos (nome, descricao, cor, icone, prioridade, status, configuracoes) 
                VALUES (:nome, :descricao, :cor, :icone, :prioridade, :status, :configuracoes)";
        
        $this->db->query($sql);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao'] ?? null);
        $this->db->bind(':cor', $dados['cor'] ?? '#007bff');
        $this->db->bind(':icone', $dados['icone'] ?? 'fas fa-building');
        $this->db->bind(':prioridade', $dados['prioridade'] ?? 0);
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        $this->db->bind(':configuracoes', json_encode($dados['configuracoes'] ?? []));
        
        return $this->db->executa();
    }

    /**
     * [ atualizar ] - Atualiza departamento
     * 
     * @param int $id ID do departamento
     * @param array $dados Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function atualizar($id, $dados)
    {
        $sql = "UPDATE departamentos SET 
                nome = :nome, 
                descricao = :descricao, 
                cor = :cor, 
                icone = :icone, 
                prioridade = :prioridade, 
                configuracoes = :configuracoes,
                status = :status
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao'] ?? null);
        $this->db->bind(':cor', $dados['cor'] ?? '#007bff');
        $this->db->bind(':icone', $dados['icone'] ?? 'fas fa-building');
        $this->db->bind(':prioridade', $dados['prioridade'] ?? 0);
        $this->db->bind(':configuracoes', json_encode($dados['configuracoes'] ?? []));
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        
        return $this->db->executa();
    }

    /**
     * [ excluir ] - Exclui departamento
     * 
     * @param int $id ID do departamento
     * @return bool Sucesso da operação
     */
    public function excluir($id)
    {
        // Primeiro, buscar o departamento para verificar se existe
        $departamento = $this->buscarPorId($id);
        if (!$departamento) {
            return false;
        }

        // Verificar se há conversas associadas
        $sql = "SELECT COUNT(*) as total FROM conversas WHERE departamento = :departamento_nome";
        $this->db->query($sql);
        $this->db->bind(':departamento_nome', $departamento->nome);
        $resultado = $this->db->resultado();
        
        if ($resultado->total > 0) {
            return false; // Não pode excluir se há conversas
        }

        // Verificar se há credenciais associadas
        $sql = "SELECT COUNT(*) as total FROM credenciais_serpro_departamento WHERE departamento_id = :departamento_id";
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $id);
        $resultado = $this->db->resultado();
        
        if ($resultado->total > 0) {
            // Excluir credenciais primeiro (devido à foreign key)
            $sql = "DELETE FROM credenciais_serpro_departamento WHERE departamento_id = :departamento_id";
            $this->db->query($sql);
            $this->db->bind(':departamento_id', $id);
            $this->db->executa();
        }

        // Verificar se há atendentes associados
        $sql = "SELECT COUNT(*) as total FROM atendentes_departamento WHERE departamento_id = :departamento_id";
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $id);
        $resultado = $this->db->resultado();
        
        if ($resultado->total > 0) {
            // Excluir atendentes primeiro (devido à foreign key)
            $sql = "DELETE FROM atendentes_departamento WHERE departamento_id = :departamento_id";
            $this->db->query($sql);
            $this->db->bind(':departamento_id', $id);
            $this->db->executa();
        }

        // Verificar se há templates associados
        $sql = "SELECT COUNT(*) as total FROM templates_departamento WHERE departamento_id = :departamento_id";
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $id);
        $resultado = $this->db->resultado();
        
        if ($resultado->total > 0) {
            // Excluir templates primeiro (devido à foreign key)
            $sql = "DELETE FROM templates_departamento WHERE departamento_id = :departamento_id";
            $this->db->query($sql);
            $this->db->bind(':departamento_id', $id);
            $this->db->executa();
        }

        // Verificar se há mensagens automáticas associadas
        $sql = "SELECT COUNT(*) as total FROM mensagens_automaticas_departamento WHERE departamento_id = :departamento_id";
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $id);
        $resultado = $this->db->resultado();
        
        if ($resultado->total > 0) {
            // Excluir mensagens automáticas primeiro (devido à foreign key)
            $sql = "DELETE FROM mensagens_automaticas_departamento WHERE departamento_id = :departamento_id";
            $this->db->query($sql);
            $this->db->bind(':departamento_id', $id);
            $this->db->executa();
        }

        // Agora excluir o departamento
        $sql = "DELETE FROM departamentos WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * [ alterarStatus ] - Altera status do departamento
     * 
     * @param int $id ID do departamento
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function alterarStatus($id, $status)
    {
        $sql = "UPDATE departamentos SET status = :status WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        
        return $this->db->executa();
    }

    /**
     * [ getEstatisticas ] - Obtém estatísticas do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $dias Número de dias para filtrar
     * @return object Estatísticas do departamento
     */
    public function getEstatisticas($departamentoId, $dias = 30)
    {
        $sql = "SELECT 
                    d.nome as departamento_nome,
                    d.cor as departamento_cor,
                    COUNT(c.id) as total_conversas,
                    SUM(CASE WHEN c.status = 'aberto' THEN 1 ELSE 0 END) as conversas_abertas,
                    SUM(CASE WHEN c.status = 'pendente' THEN 1 ELSE 0 END) as conversas_pendentes,
                    SUM(CASE WHEN c.status = 'fechado' THEN 1 ELSE 0 END) as conversas_fechadas,
                    COUNT(DISTINCT c.atendente_id) as atendentes_ativos,
                    AVG(c.tempo_resposta_medio) as tempo_resposta_medio,
                    COUNT(DISTINCT m.id) as total_mensagens
                FROM departamentos d
                LEFT JOIN conversas c ON d.id = c.departamento_id 
                    AND c.criado_em >= DATE_SUB(NOW(), INTERVAL :dias DAY)
                LEFT JOIN mensagens m ON c.id = m.conversa_id
                WHERE d.id = :departamento_id
                GROUP BY d.id, d.nome, d.cor";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':dias', $dias);
        
        return $this->db->resultado();
    }

    /**
     * [ getAtendentes ] - Obtém atendentes do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param bool $apenasAtivos Se true, retorna apenas atendentes ativos
     * @return array Lista de atendentes
     */
    public function getAtendentes($departamentoId, $apenasAtivos = true)
    {
        $sql = "SELECT 
                    u.id,
                    u.nome,
                    u.email,
                    u.perfil,
                    u.status,
                    ad.perfil as perfil_departamento,
                    ad.max_conversas,
                    ad.horario_inicio,
                    ad.horario_fim,
                    ad.dias_semana
                FROM usuarios u
                JOIN atendentes_departamento ad ON u.id = ad.usuario_id
                WHERE ad.departamento_id = :departamento_id";
        
        if ($apenasAtivos) {
            $sql .= " AND ad.status = 'ativo' AND u.status = 'ativo'";
        }
        
        $sql .= " ORDER BY ad.perfil DESC, u.nome ASC";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        
        return $this->db->resultados();
    }

    /**
     * [ adicionarAtendente ] - Adiciona um atendente ao departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $usuarioId ID do usuário
     * @param string $perfil Perfil no departamento
     * @param int $maxConversas Máximo de conversas
     * @param string $horarioInicio Horário de início
     * @param string $horarioFim Horário de fim
     * @param array $diasSemana Dias da semana
     * @return bool Sucesso da operação
     */
    public function adicionarAtendente($departamentoId, $usuarioId, $perfil = 'atendente', $maxConversas = 5, $horarioInicio = '08:00:00', $horarioFim = '18:00:00', $diasSemana = [1,2,3,4,5])
    {
        $sql = "
            INSERT INTO atendentes_departamento 
            (departamento_id, usuario_id, perfil, max_conversas, horario_inicio, horario_fim, dias_semana, status, criado_em) 
            VALUES 
            (:departamento_id, :usuario_id, :perfil, :max_conversas, :horario_inicio, :horario_fim, :dias_semana, 'ativo', NOW())
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':perfil', $perfil);
        $this->db->bind(':max_conversas', $maxConversas);
        $this->db->bind(':horario_inicio', $horarioInicio);
        $this->db->bind(':horario_fim', $horarioFim);
        $this->db->bind(':dias_semana', json_encode($diasSemana));
        
        return $this->db->executa();
    }

    /**
     * [ removerAtendente ] - Remove um atendente do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $usuarioId ID do usuário
     * @return bool Sucesso da operação
     */
    public function removerAtendente($departamentoId, $usuarioId)
    {
        $sql = "DELETE FROM atendentes_departamento WHERE departamento_id = :departamento_id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':usuario_id', $usuarioId);
        
        return $this->db->executa();
    }

    /**
     * [ atualizarConfiguracaoAtendente ] - Atualiza configuração de um atendente no departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $usuarioId ID do usuário
     * @param string $perfil Perfil no departamento
     * @param int $maxConversas Máximo de conversas
     * @param string $horarioInicio Horário de início
     * @param string $horarioFim Horário de fim
     * @param array $diasSemana Dias da semana
     * @param string $status Status do atendente
     * @return bool Sucesso da operação
     */
    public function atualizarConfiguracaoAtendente($departamentoId, $usuarioId, $perfil = 'atendente', $maxConversas = 5, $horarioInicio = '08:00:00', $horarioFim = '18:00:00', $diasSemana = [1,2,3,4,5], $status = 'ativo')
    {
        $sql = "
            UPDATE atendentes_departamento 
            SET perfil = :perfil,
                max_conversas = :max_conversas,
                horario_inicio = :horario_inicio,
                horario_fim = :horario_fim,
                dias_semana = :dias_semana,
                status = :status
            WHERE departamento_id = :departamento_id AND usuario_id = :usuario_id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':perfil', $perfil);
        $this->db->bind(':max_conversas', $maxConversas);
        $this->db->bind(':horario_inicio', $horarioInicio);
        $this->db->bind(':horario_fim', $horarioFim);
        $this->db->bind(':dias_semana', json_encode($diasSemana));
        $this->db->bind(':status', $status);
        
        return $this->db->executa();
    }

    /**
     * [ buscarConfiguracaoAtendente ] - Busca configuração de um atendente no departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $usuarioId ID do usuário
     * @return object|null Configuração do atendente
     */
    public function buscarConfiguracaoAtendente($departamentoId, $usuarioId)
    {
        $sql = "
            SELECT 
                ad.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM atendentes_departamento ad
            JOIN usuarios u ON ad.usuario_id = u.id
            WHERE ad.departamento_id = :departamento_id 
            AND ad.usuario_id = :usuario_id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':usuario_id', $usuarioId);
        
        $resultado = $this->db->resultado();
        
        if ($resultado) {
            // Decodificar dias da semana
            $resultado->dias_semana = json_decode($resultado->dias_semana, true);
        }
        
        return $resultado;
    }

    /**
     * [ getConversas ] - Obtém conversas do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param array $filtros Filtros adicionais
     * @return array Lista de conversas
     */
    public function getConversas($departamentoId, $filtros = [])
    {
        $sql = "SELECT 
                    c.*,
                    ct.nome as contato_nome,
                    ct.numero,
                    u.nome as atendente_nome,
                    COUNT(m.id) as total_mensagens
                FROM conversas c
                LEFT JOIN contatos ct ON c.contato_id = ct.id
                LEFT JOIN usuarios u ON c.atendente_id = u.id
                LEFT JOIN mensagens m ON c.id = m.conversa_id
                WHERE c.departamento_id = :departamento_id";
        
        $params = [':departamento_id' => $departamentoId];
        
        // Aplicar filtros
        if (!empty($filtros['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filtros['status'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= :data_inicio";
            $params[':data_inicio'] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= :data_fim";
            $params[':data_fim'] = $filtros['data_fim'];
        }
        
        $sql .= " GROUP BY c.id ORDER BY c.criado_em DESC";
        
        // Aplicar limite se especificado
        if (!empty($filtros['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $filtros['limit'];
        }
        
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ getDepartamentosDisponiveis ] - Obtém departamentos disponíveis para um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array Lista de departamentos
     */
    public function getDepartamentosDisponiveis($usuarioId)
    {
        $sql = "SELECT DISTINCT 
                    d.*
                FROM departamentos d
                JOIN atendentes_departamento ad ON d.id = ad.departamento_id
                WHERE ad.usuario_id = :usuario_id 
                AND ad.status = 'ativo' 
                AND d.status = 'ativo'
                ORDER BY d.prioridade ASC, d.nome ASC";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        
        return $this->db->resultados();
    }

    /**
     * [ getEstatisticasAdicionais ] - Obtém estatísticas adicionais por período
     * 
     * @param int $departamentoId ID do departamento
     * @return array Estatísticas por período
     */
    public function getEstatisticasAdicionais($departamentoId)
    {
        // Estatísticas de hoje
        $sql = "SELECT 
                    COUNT(*) as conversas_hoje,
                    COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertas_hoje,
                    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes_hoje,
                    COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechadas_hoje
                FROM conversas 
                WHERE departamento_id = :departamento_id 
                AND DATE(criado_em) = CURDATE()";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $hoje = $this->db->resultado();

        // Estatísticas de ontem
        $sql = "SELECT 
                    COUNT(*) as conversas_ontem,
                    COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertas_ontem,
                    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes_ontem,
                    COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechadas_ontem
                FROM conversas 
                WHERE departamento_id = :departamento_id 
                AND DATE(criado_em) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $ontem = $this->db->resultado();

        // Estatísticas da semana
        $sql = "SELECT 
                    COUNT(*) as conversas_semana,
                    COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertas_semana,
                    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes_semana,
                    COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechadas_semana
                FROM conversas 
                WHERE departamento_id = :departamento_id 
                AND criado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $semana = $this->db->resultado();

        // Estatísticas do mês
        $sql = "SELECT 
                    COUNT(*) as conversas_mes,
                    COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertas_mes,
                    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes_mes,
                    COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechadas_mes
                FROM conversas 
                WHERE departamento_id = :departamento_id 
                AND criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $mes = $this->db->resultado();

        return [
            'hoje' => $hoje,
            'ontem' => $ontem,
            'semana' => $semana,
            'mes' => $mes
        ];
    }

    /**
     * [ getTaxaAtendimento ] - Calcula taxa de atendimento do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return float Taxa de atendimento em porcentagem
     */
    public function getTaxaAtendimento($departamentoId)
    {
        $estatisticas = $this->getEstatisticas($departamentoId, 30);
        
        if (!$estatisticas || $estatisticas->total_conversas == 0) {
            return 0;
        }
        
        return round(($estatisticas->conversas_abertas / $estatisticas->total_conversas) * 100, 1);
    }

    /**
     * [ getEstatisticasCompletas ] - Obtém estatísticas completas do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return object Estatísticas completas
     */
    public function getEstatisticasCompletas($departamentoId)
    {
        $estatisticas = $this->getEstatisticas($departamentoId, 30);
        $estatisticasAdicionais = $this->getEstatisticasAdicionais($departamentoId);
        $taxaAtendimento = $this->getTaxaAtendimento($departamentoId);
        
        return (object) [
            'basicas' => $estatisticas,
            'adicionais' => $estatisticasAdicionais,
            'taxa_atendimento' => $taxaAtendimento
        ];
    }
} 