<?php

/**
 * [ LOGMODEL ] - Model para gerenciamento de logs do sistema
 * 
 * Esta classe gerencia:
 * - Logs de atividades dos usuários
 * - Logs de acesso ao sistema
 * - Estatísticas de logs
 * - Limpeza de logs antigos
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class LogModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ registrarAtividade ] - Registra uma atividade do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @param string $acao Ação realizada
     * @param string $descricao Descrição da atividade
     * @return bool Sucesso da operação
     */
    public function registrarAtividade($usuarioId, $acao, $descricao = null)
    {
        $sql = "
            INSERT INTO atividades (usuario_id, acao, descricao, data_hora)
            VALUES (:usuario_id, :acao, :descricao, NOW())
        ";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':acao', $acao);
        $this->db->bind(':descricao', $descricao);

        return $this->db->executa();
    }

    /**
     * [ registrarAcesso ] - Registra um acesso ao sistema
     * 
     * @param int $usuarioId ID do usuário (opcional)
     * @param string $email Email do usuário (opcional)
     * @param string $ip IP do acesso
     * @param string $userAgent User agent do navegador
     * @param bool $sucesso Se o acesso foi bem-sucedido
     * @return bool Sucesso da operação
     */
    public function registrarAcesso($usuarioId = null, $email = null, $ip, $userAgent, $sucesso = false)
    {
        $sql = "
            INSERT INTO log_acessos (usuario_id, email, ip, user_agent, sucesso, data_hora)
            VALUES (:usuario_id, :email, :ip, :user_agent, :sucesso, NOW())
        ";

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':email', $email);
        $this->db->bind(':ip', $ip);
        $this->db->bind(':user_agent', $userAgent);
        $this->db->bind(':sucesso', $sucesso ? 1 : 0);

        return $this->db->executa();
    }

    /**
     * [ getAtividades ] - Busca atividades com filtros
     * 
     * @param array $filtros Filtros de busca
     * @return array Lista de atividades
     */
    public function getAtividades($filtros = [])
    {
        $sql = "
            SELECT 
                a.id,
                a.usuario_id,
                a.acao,
                a.descricao,
                a.data_hora,
                u.nome as usuario_nome,
                u.email as usuario_email,
                u.perfil as usuario_perfil
            FROM atividades a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND a.usuario_id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(a.data_hora) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(a.data_hora) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['acao'])) {
            $sql .= " AND a.acao LIKE :acao";
            $params['acao'] = '%' . $filtros['acao'] . '%';
        }

        $sql .= " ORDER BY a.data_hora DESC";

        // Paginação
        if (!empty($filtros['limite'])) {
            $offset = ($filtros['pagina'] - 1) * $filtros['limite'];
            $sql .= " LIMIT :limite OFFSET :offset";
            $params['limite'] = $filtros['limite'];
            $params['offset'] = $offset;
        }

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getLogAcessos ] - Busca logs de acesso com filtros
     * 
     * @param array $filtros Filtros de busca
     * @return array Lista de acessos
     */
    public function getLogAcessos($filtros = [])
    {
        $sql = "
            SELECT 
                l.id,
                l.usuario_id,
                l.email,
                l.ip,
                l.user_agent,
                l.sucesso,
                l.data_hora,
                u.nome as usuario_nome,
                u.perfil as usuario_perfil
            FROM log_acessos l
            LEFT JOIN usuarios u ON l.usuario_id = u.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND l.usuario_id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(l.data_hora) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(l.data_hora) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        if (isset($filtros['sucesso'])) {
            $sql .= " AND l.sucesso = :sucesso";
            $params['sucesso'] = $filtros['sucesso'] ? 1 : 0;
        }

        $sql .= " ORDER BY l.data_hora DESC";

        // Paginação
        if (!empty($filtros['limite'])) {
            $offset = ($filtros['pagina'] - 1) * $filtros['limite'];
            $sql .= " LIMIT :limite OFFSET :offset";
            $params['limite'] = $filtros['limite'];
            $params['offset'] = $offset;
        }

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getEstatisticasLogs ] - Estatísticas dos logs
     * 
     * @param array $filtros Filtros de busca
     * @return object Estatísticas
     */
    public function getEstatisticasLogs($filtros = [])
    {
        // Estatísticas de atividades
        $sqlAtividades = "
            SELECT 
                COUNT(*) as total_atividades,
                COUNT(DISTINCT usuario_id) as usuarios_ativos,
                COUNT(DISTINCT DATE(data_hora)) as dias_atividade
            FROM atividades
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sqlAtividades .= " AND DATE(data_hora) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sqlAtividades .= " AND DATE(data_hora) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $this->db->query($sqlAtividades);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        $atividades = $this->db->resultado();

        // Estatísticas de acessos
        $sqlAcessos = "
            SELECT 
                COUNT(*) as total_acessos,
                SUM(CASE WHEN sucesso = 1 THEN 1 ELSE 0 END) as acessos_sucesso,
                SUM(CASE WHEN sucesso = 0 THEN 1 ELSE 0 END) as acessos_falha,
                COUNT(DISTINCT ip) as ips_unicos
            FROM log_acessos
            WHERE 1=1
        ";

        $this->db->query($sqlAcessos);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        $acessos = $this->db->resultado();

        // Combinar estatísticas
        return (object) [
            'atividades' => $atividades,
            'acessos' => $acessos,
            'total_logs' => ($atividades->total_atividades ?? 0) + ($acessos->total_acessos ?? 0)
        ];
    }

    /**
     * [ getAtividadesPorAcao ] - Atividades agrupadas por ação
     * 
     * @param array $filtros Filtros de busca
     * @return array Lista de ações
     */
    public function getAtividadesPorAcao($filtros = [])
    {
        $sql = "
            SELECT 
                acao,
                COUNT(*) as total,
                COUNT(DISTINCT usuario_id) as usuarios_unicos
            FROM atividades
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(data_hora) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(data_hora) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $sql .= " GROUP BY acao ORDER BY total DESC LIMIT 10";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getAcessosPorIP ] - Acessos agrupados por IP
     * 
     * @param array $filtros Filtros de busca
     * @return array Lista de IPs
     */
    public function getAcessosPorIP($filtros = [])
    {
        $sql = "
            SELECT 
                ip,
                COUNT(*) as total_acessos,
                SUM(CASE WHEN sucesso = 1 THEN 1 ELSE 0 END) as sucessos,
                SUM(CASE WHEN sucesso = 0 THEN 1 ELSE 0 END) as falhas,
                MAX(data_hora) as ultimo_acesso
            FROM log_acessos
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(data_hora) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(data_hora) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $sql .= " GROUP BY ip ORDER BY total_acessos DESC LIMIT 10";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ limparLogsAntigos ] - Remove logs antigos
     * 
     * @param int $dias Número de dias para manter
     * @return array Resultado da limpeza
     */
    public function limparLogsAntigos($dias = 30)
    {
        $dataLimite = date('Y-m-d H:i:s', strtotime("-{$dias} days"));

        // Limpar atividades antigas
        $sqlAtividades = "DELETE FROM atividades WHERE data_hora < :data_limite";
        $this->db->query($sqlAtividades);
        $this->db->bind(':data_limite', $dataLimite);
        $atividadesRemovidas = $this->db->executa();

        // Limpar acessos antigos
        $sqlAcessos = "DELETE FROM log_acessos WHERE data_hora < :data_limite";
        $this->db->query($sqlAcessos);
        $this->db->bind(':data_limite', $dataLimite);
        $acessosRemovidos = $this->db->executa();

        return [
            'atividades_removidas' => $atividadesRemovidas,
            'acessos_removidos' => $acessosRemovidos,
            'data_limite' => $dataLimite
        ];
    }

    /**
     * [ contarAtividades ] - Conta total de atividades
     * 
     * @param array $filtros Filtros de busca
     * @return int Total de atividades
     */
    public function contarAtividades($filtros = [])
    {
        $sql = "SELECT COUNT(*) as total FROM atividades WHERE 1=1";

        $params = [];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND usuario_id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(data_hora) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(data_hora) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * [ contarAcessos ] - Conta total de acessos
     * 
     * @param array $filtros Filtros de busca
     * @return int Total de acessos
     */
    public function contarAcessos($filtros = [])
    {
        $sql = "SELECT COUNT(*) as total FROM log_acessos WHERE 1=1";

        $params = [];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND usuario_id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(data_hora) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(data_hora) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }
} 