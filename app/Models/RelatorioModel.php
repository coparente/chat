<?php

/**
 * [ RELATORIOMODEL ] - Model para relatórios do ChatSerpro
 * 
 * Esta classe gerencia todas as consultas para relatórios:
 * - Relatórios de conversas
 * - Performance de atendentes
 * - Utilização de templates
 * - Volume de mensagens
 * - Tempo de resposta
 * - Estatísticas gerais
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class RelatorioModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ getRelatorioConversas ] - Relatório detalhado de conversas
     */
    public function getRelatorioConversas($filtros = [])
    {
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                u.nome as atendente_nome,
                c.status,
                c.prioridade,
                c.departamento,
                c.criado_em,
                c.ultima_mensagem,
                c.atualizado_em,
                COUNT(m.id) as total_mensagens,
                SUM(CASE WHEN m.direcao = 'entrada' THEN 1 ELSE 0 END) as mensagens_recebidas,
                SUM(CASE WHEN m.direcao = 'saida' THEN 1 ELSE 0 END) as mensagens_enviadas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
            LEFT JOIN mensagens m ON c.id = m.conversa_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filtros['status'];
        }

        if (!empty($filtros['atendente_id'])) {
            $sql .= " AND c.atendente_id = :atendente_id";
            $params['atendente_id'] = $filtros['atendente_id'];
        }

        $sql .= " GROUP BY c.id ORDER BY c.criado_em DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getEstatisticasConversas ] - Estatísticas resumidas das conversas
     */
    public function getEstatisticasConversas($filtros = [])
    {
        $sql = "
            SELECT 
                COUNT(*) as total_conversas,
                SUM(CASE WHEN status = 'aberto' THEN 1 ELSE 0 END) as abertas,
                SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = 'fechado' THEN 1 ELSE 0 END) as fechadas,
                AVG(TIMESTAMPDIFF(MINUTE, criado_em, COALESCE(ultima_mensagem, NOW()))) as duracao_media_minutos,
                COUNT(DISTINCT contato_id) as contatos_unicos,
                COUNT(DISTINCT atendente_id) as atendentes_envolvidos
            FROM conversas c
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultado();
    }

    /**
     * [ getPerformanceAtendentes ] - Performance detalhada dos atendentes
     */
    public function getPerformanceAtendentes($filtros = [])
    {
        $sql = "
            SELECT 
                u.id,
                u.nome,
                u.email,
                COUNT(DISTINCT c.id) as total_conversas,
                SUM(CASE WHEN c.status = 'aberto' THEN 1 ELSE 0 END) as conversas_abertas,
                SUM(CASE WHEN c.status = 'fechado' THEN 1 ELSE 0 END) as conversas_fechadas,
                COUNT(DISTINCT m.id) as total_mensagens,
                AVG(c.tempo_resposta_medio) as tempo_medio_resposta,
                0 as avaliacoes_positivas,
                0 as total_avaliacoes,
                0 as avaliacao_media,
                MAX(c.atualizado_em) as ultima_atividade
            FROM usuarios u
            LEFT JOIN conversas c ON u.id = c.atendente_id
            LEFT JOIN mensagens m ON u.id = m.atendente_id
            WHERE u.perfil = 'atendente'
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND (c.criado_em IS NULL OR DATE(c.criado_em) >= :data_inicio)";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND (c.criado_em IS NULL OR DATE(c.criado_em) <= :data_fim)";
            $params['data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['atendente_id'])) {
            $sql .= " AND u.id = :atendente_id";
            $params['atendente_id'] = $filtros['atendente_id'];
        }

        $sql .= " GROUP BY u.id ORDER BY total_conversas DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getRankingAtendentes ] - Ranking dos atendentes por performance
     */
    public function getRankingAtendentes($filtros = [])
    {
        $sql = "
            SELECT 
                u.nome,
                COUNT(DISTINCT c.id) as conversas,
                COUNT(DISTINCT m.id) as mensagens,
                0 as avaliacao_media,
                AVG(c.tempo_resposta_medio) as tempo_medio
            FROM usuarios u
            LEFT JOIN conversas c ON u.id = c.atendente_id
            LEFT JOIN mensagens m ON u.id = m.atendente_id
            WHERE u.perfil = 'atendente'
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND (c.criado_em IS NULL OR DATE(c.criado_em) >= :data_inicio)";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND (c.criado_em IS NULL OR DATE(c.criado_em) <= :data_fim)";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $sql .= " GROUP BY u.id HAVING COUNT(DISTINCT c.id) > 0 ORDER BY conversas DESC, mensagens DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        $resultados = $this->db->resultados();
        
        // Calcular ranking no PHP
        $ranking_conversas = [];
        $ranking_mensagens = [];
        
        // Separar dados para ranking
        foreach ($resultados as $row) {
            $ranking_conversas[] = $row->conversas;
            $ranking_mensagens[] = $row->mensagens;
        }
        
        // Ordenar para calcular ranking
        rsort($ranking_conversas);
        rsort($ranking_mensagens);
        
        // Aplicar ranking aos resultados
        foreach ($resultados as $row) {
            $row->ranking_conversas = array_search($row->conversas, $ranking_conversas) + 1;
            $row->ranking_mensagens = array_search($row->mensagens, $ranking_mensagens) + 1;
        }
        
        return $resultados;
    }

    /**
     * [ getUtilizacaoTemplates ] - Relatório de utilização de templates
     */
    public function getUtilizacaoTemplates($filtros = [])
    {
        $sql = "
            SELECT 
                SUBSTRING_INDEX(SUBSTRING_INDEX(metadata, '\"template\":\"', -1), '\"', 1) as template,
                COUNT(*) as total_utilizacoes,
                SUM(CASE WHEN status_entrega = 'entregue' THEN 1 ELSE 0 END) as sucessos,
                SUM(CASE WHEN status_entrega = 'erro' THEN 1 ELSE 0 END) as falhas,
                (SUM(CASE WHEN status_entrega = 'entregue' THEN 1 ELSE 0 END) / COUNT(*) * 100) as taxa_sucesso,
                MAX(criado_em) as ultima_utilizacao,
                COUNT(DISTINCT conversa_id) as conversas_unicas
            FROM mensagens m
            WHERE metadata LIKE '%\"tipo\":\"template\"%'
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(m.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(m.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['template'])) {
            $sql .= " AND metadata LIKE :template";
            $params['template'] = '%"template":"' . $filtros['template'] . '"%';
        }

        $sql .= " GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(metadata, '\"template\":\"', -1), '\"', 1) ORDER BY total_utilizacoes DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getEstatisticasTemplates ] - Estatísticas gerais dos templates
     */
    public function getEstatisticasTemplates($filtros = [])
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(metadata, '\"template\":\"', -1), '\"', 1)) as templates_diferentes,
                COUNT(*) as total_envios,
                SUM(CASE WHEN status_entrega = 'entregue' THEN 1 ELSE 0 END) as sucessos,
                SUM(CASE WHEN status_entrega = 'erro' THEN 1 ELSE 0 END) as falhas,
                (SUM(CASE WHEN status_entrega = 'entregue' THEN 1 ELSE 0 END) / COUNT(*) * 100) as taxa_sucesso_geral
            FROM mensagens m
            WHERE metadata LIKE '%\"tipo\":\"template\"%'
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(m.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(m.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultado();
    }

    /**
     * [ getVolumeMensagens ] - Volume de mensagens por período
     */
    public function getVolumeMensagens($filtros = [])
    {
        $sql = "
            SELECT 
                DATE(criado_em) as data,
                COUNT(*) as total,
                SUM(CASE WHEN direcao = 'entrada' THEN 1 ELSE 0 END) as entrada,
                SUM(CASE WHEN direcao = 'saida' THEN 1 ELSE 0 END) as saida,
                SUM(CASE WHEN tipo = 'texto' THEN 1 ELSE 0 END) as texto,
                SUM(CASE WHEN tipo IN ('imagem', 'audio', 'video', 'documento') THEN 1 ELSE 0 END) as midia,
                SUM(CASE WHEN metadata LIKE '%\"tipo\":\"template\"%' THEN 1 ELSE 0 END) as templates
            FROM mensagens m
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(m.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(m.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['direcao'])) {
            $sql .= " AND m.direcao = :direcao";
            $params['direcao'] = $filtros['direcao'];
        }

        $sql .= " GROUP BY DATE(criado_em) ORDER BY data DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getMensagensPorDia ] - Mensagens agrupadas por dia
     */
    public function getMensagensPorDia($filtros = [])
    {
        $sql = "
            SELECT 
                DATE(criado_em) as data,
                COUNT(*) as total,
                SUM(CASE WHEN direcao = 'entrada' THEN 1 ELSE 0 END) as entrada,
                SUM(CASE WHEN direcao = 'saida' THEN 1 ELSE 0 END) as saida
            FROM mensagens m
            WHERE DATE(m.criado_em) BETWEEN :data_inicio AND :data_fim
            GROUP BY DATE(criado_em)
            ORDER BY data ASC
        ";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $filtros['data_inicio']);
        $this->db->bind(':data_fim', $filtros['data_fim']);

        return $this->db->resultados();
    }

    /**
     * [ getMensagensPorHora ] - Mensagens agrupadas por hora do dia
     */
    public function getMensagensPorHora($filtros = [])
    {
        $sql = "
            SELECT 
                HOUR(criado_em) as hora,
                COUNT(*) as total
            FROM mensagens m
            WHERE DATE(m.criado_em) BETWEEN :data_inicio AND :data_fim
            GROUP BY HOUR(criado_em)
            ORDER BY hora ASC
        ";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $filtros['data_inicio']);
        $this->db->bind(':data_fim', $filtros['data_fim']);

        $resultados = $this->db->resultados();
        
        // Calcular média no PHP
        $total_mensagens = 0;
        $total_horas = count($resultados);
        
        foreach ($resultados as $row) {
            $total_mensagens += $row->total;
        }
        
        $media_por_hora = $total_horas > 0 ? $total_mensagens / $total_horas : 0;
        
        // Adicionar média a cada resultado
        foreach ($resultados as $row) {
            $row->media_por_hora = round($media_por_hora, 2);
        }

        return $resultados;
    }

    /**
     * [ getEstatisticasMensagens ] - Estatísticas gerais das mensagens
     */
    public function getEstatisticasMensagens($filtros = [])
    {
        $sql = "
            SELECT 
                COUNT(*) as total_mensagens,
                SUM(CASE WHEN direcao = 'entrada' THEN 1 ELSE 0 END) as total_entrada,
                SUM(CASE WHEN direcao = 'saida' THEN 1 ELSE 0 END) as total_saida,
                AVG(CHAR_LENGTH(conteudo)) as tamanho_medio_caracteres,
                COUNT(DISTINCT conversa_id) as conversas_com_mensagens,
                COUNT(DISTINCT DATE(criado_em)) as dias_com_atividade
            FROM mensagens m
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(m.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(m.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultado();
    }

    /**
     * [ getTempoResposta ] - Relatório de tempo de resposta
     */
    public function getTempoResposta($filtros = [])
    {
        $sql = "
            SELECT 
                u.nome as atendente_nome,
                COUNT(DISTINCT c.id) as total_conversas,
                AVG(c.tempo_resposta_medio) as tempo_medio,
                MIN(c.tempo_resposta_medio) as tempo_minimo,
                MAX(c.tempo_resposta_medio) as tempo_maximo,
                SUM(CASE WHEN c.tempo_resposta_medio <= 5 THEN 1 ELSE 0 END) / COUNT(*) * 100 as dentro_sla
            FROM usuarios u
            LEFT JOIN conversas c ON u.id = c.atendente_id
            WHERE u.perfil = 'atendente' AND c.tempo_resposta_medio > 0
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['atendente_id'])) {
            $sql .= " AND u.id = :atendente_id";
            $params['atendente_id'] = $filtros['atendente_id'];
        }

        $sql .= " GROUP BY u.id HAVING COUNT(DISTINCT c.id) > 0 ORDER BY tempo_medio ASC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getTempoRespostaPorAtendente ] - Tempo de resposta detalhado por atendente
     */
    public function getTempoRespostaPorAtendente($filtros = [])
    {
        $sql = "
            SELECT 
                u.nome as atendente_nome,
                DATE(c.criado_em) as data,
                AVG(c.tempo_resposta_medio) as tempo_medio,
                COUNT(*) as conversas_dia
            FROM usuarios u
            LEFT JOIN conversas c ON u.id = c.atendente_id
            WHERE u.perfil = 'atendente' AND c.tempo_resposta_medio > 0
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $sql .= " GROUP BY u.id, DATE(c.criado_em) ORDER BY data DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getEvolucaoTempoResposta ] - Evolução do tempo de resposta ao longo do tempo
     */
    public function getEvolucaoTempoResposta($filtros = [])
    {
        $sql = "
            SELECT 
                DATE(c.criado_em) as data,
                AVG(c.tempo_resposta_medio) as tempo_medio,
                COUNT(*) as total_conversas
            FROM conversas c
            WHERE c.tempo_resposta_medio > 0
        ";

        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }

        $sql .= " GROUP BY DATE(c.criado_em) ORDER BY data ASC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }

        return $this->db->resultados();
    }

    /**
     * [ getConversasPorDia ] - Conversas iniciadas por dia
     */
    public function getConversasPorDia($filtros = [])
    {
        $sql = "
            SELECT 
                DATE(criado_em) as data,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'fechado' THEN 1 ELSE 0 END) as finalizadas
            FROM conversas
            WHERE DATE(criado_em) BETWEEN :data_inicio AND :data_fim
            GROUP BY DATE(criado_em)
            ORDER BY data ASC
        ";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $filtros['data_inicio']);
        $this->db->bind(':data_fim', $filtros['data_fim']);

        return $this->db->resultados();
    }

    /**
     * [ getConversasPorStatus ] - Distribuição de conversas por status
     */
    public function getConversasPorStatus($filtros = [])
    {
        $sql = "
            SELECT 
                status,
                COUNT(*) as total
            FROM conversas
            WHERE DATE(criado_em) BETWEEN :data_inicio AND :data_fim
            GROUP BY status
            ORDER BY total DESC
        ";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $filtros['data_inicio']);
        $this->db->bind(':data_fim', $filtros['data_fim']);

        $resultados = $this->db->resultados();
        
        // Calcular percentual no PHP
        $total_conversas = 0;
        foreach ($resultados as $row) {
            $total_conversas += $row->total;
        }
        
        foreach ($resultados as $row) {
            $row->percentual = $total_conversas > 0 ? round(($row->total / $total_conversas) * 100, 2) : 0;
        }

        return $resultados;
    }

    /**
     * [ getTopAtendentes ] - Top atendentes por conversas
     */
    public function getTopAtendentes($filtros = [])
    {
        $sql = "
            SELECT 
                u.nome,
                COUNT(DISTINCT c.id) as conversas,
                0 as avaliacao_media
            FROM usuarios u
            LEFT JOIN conversas c ON u.id = c.atendente_id
            WHERE u.perfil = 'atendente'
            AND DATE(c.criado_em) BETWEEN :data_inicio AND :data_fim
            GROUP BY u.id
            HAVING COUNT(DISTINCT c.id) > 0
            ORDER BY conversas DESC
            LIMIT 10
        ";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $filtros['data_inicio']);
        $this->db->bind(':data_fim', $filtros['data_fim']);

        return $this->db->resultados();
    }

    /**
     * [ getTemplatesMaisUsados ] - Templates mais utilizados
     */
    public function getTemplatesMaisUsados($filtros = [])
    {
        $sql = "
            SELECT 
                SUBSTRING_INDEX(SUBSTRING_INDEX(metadata, '\"template\":\"', -1), '\"', 1) as template,
                COUNT(*) as utilizacoes
            FROM mensagens m
            WHERE metadata LIKE '%\"tipo\":\"template\"%'
            AND DATE(m.criado_em) BETWEEN :data_inicio AND :data_fim
            GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(metadata, '\"template\":\"', -1), '\"', 1)
            ORDER BY utilizacoes DESC
            LIMIT 10
        ";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $filtros['data_inicio']);
        $this->db->bind(':data_fim', $filtros['data_fim']);

        return $this->db->resultados();
    }

    /**
     * [ getTempoRespostaGeral ] - Tempo de resposta geral do sistema
     */
    public function getTempoRespostaGeral($filtros = [])
    {
        $sql = "
            SELECT 
                AVG(c.tempo_resposta_medio) as tempo_medio,
                MIN(c.tempo_resposta_medio) as tempo_minimo,
                MAX(c.tempo_resposta_medio) as tempo_maximo,
                COUNT(*) as total_conversas
            FROM conversas c
            WHERE c.tempo_resposta_medio > 0
            AND DATE(c.criado_em) BETWEEN :data_inicio AND :data_fim
        ";

        $this->db->query($sql);
        $this->db->bind(':data_inicio', $filtros['data_inicio']);
        $this->db->bind(':data_fim', $filtros['data_fim']);

        return $this->db->resultado();
    }
}
?> 