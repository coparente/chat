<?php

/**
 * [ CONVERSAMODEL ] - Model para gerenciamento de conversas
 * 
 * Esta classe gerencia todas as operações relacionadas às conversas:
 * - CRUD de conversas
 * - Estatísticas de conversas
 * - Consultas por status, atendente, etc.
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class ConversaModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ getEstatisticasConversas ] - Busca estatísticas de conversas
     * 
     * @param int $dias Número de dias para análise (padrão 30)
     * @return object Estatísticas das conversas
     */
    public function getEstatisticasConversas($dias = 30)
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'aberto' THEN 1 ELSE 0 END) as abertas,
                SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = 'fechado' THEN 1 ELSE 0 END) as fechadas
            FROM conversas 
            WHERE criado_em >= DATE_SUB(NOW(), INTERVAL :dias DAY)
        ";
        
        $this->db->query($sql);
        $this->db->bind(':dias', $dias);
        return $this->db->resultado();
    }

    /**
     * [ getConversasAtivas ] - Busca conversas em andamento
     * 
     * @param int $limite Limite de resultados
     * @return array Lista de conversas ativas
     */
    public function getConversasAtivas($limite = 10)
    {
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                u.nome as atendente_nome,
                c.status,
                c.criado_em,
                c.ultima_mensagem,
                c.departamento_id,
                d.nome as departamento_nome,
                d.cor as departamento_cor,
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            WHERE c.status IN ('aberto', 'pendente')
            ORDER BY c.ultima_mensagem DESC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }

    /**
     * [ getConversasPorAtendente ] - Busca conversas de um atendente específico
     * 
     * @param int $atendenteId ID do atendente
     * @param array $status Status das conversas a buscar
     * @return array Lista de conversas
     */
    public function getConversasPorAtendente($atendenteId, $status = ['aberto', 'pendente'])
    {
        $statusPlaceholders = implode(',', array_fill(0, count($status), '?'));
        
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                c.status,
                c.criado_em,
                c.ultima_mensagem,
                c.departamento_id,
                d.nome as departamento_nome,
                d.cor as departamento_cor,
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            WHERE c.atendente_id = ? 
            AND c.status IN ($statusPlaceholders)
            ORDER BY c.ultima_mensagem DESC
        ";
        
        $this->db->query($sql);
        $this->db->bind(1, $atendenteId);
        
        // Bind dos status
        foreach ($status as $index => $st) {
            $this->db->bind($index + 2, $st);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ getConversasPendentes ] - Busca conversas sem atendente
     * 
     * @param int $limite Limite de resultados
     * @return array Lista de conversas pendentes
     */
    public function getConversasPendentes($limite = 5)
    {
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                c.criado_em,
                c.ultima_mensagem,
                c.departamento_id,
                d.nome as departamento_nome,
                d.cor as departamento_cor,
                u.nome as atendente_nome,
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
            WHERE c.atendente_id IS NULL 
            AND c.status = 'pendente'
            ORDER BY c.criado_em ASC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }

    /**
     * [ getEstatisticasAtendente ] - Estatísticas de um atendente específico
     * 
     * @param int $atendenteId ID do atendente
     * @param string $data Data para análise (formato Y-m-d)
     * @return object Estatísticas do atendente
     */
    public function getEstatisticasAtendente($atendenteId, $data = null)
    {
        if (!$data) {
            $data = date('Y-m-d');
        }
        
        $sql = "
            SELECT 
                COUNT(DISTINCT c.id) as conversas_atendidas,
                COUNT(DISTINCT m.id) as mensagens_enviadas,
                AVG(c.tempo_resposta_medio) as tempo_medio_resposta
            FROM conversas c
            LEFT JOIN mensagens m ON c.id = m.conversa_id AND m.atendente_id = :atendente_id
            WHERE c.atendente_id = :atendente_id 
            AND DATE(c.criado_em) = :data
        ";
        
        $this->db->query($sql);
        $this->db->bind(':atendente_id', $atendenteId);
        $this->db->bind(':data', $data);
        return $this->db->resultado();
    }

    /**
     * [ getEstatisticasGerais ] - Estatísticas gerais do dia
     * 
     * @param string $data Data para análise
     * @return object Estatísticas gerais
     */
    public function getEstatisticasGerais($data = null)
    {
        if (!$data) {
            $data = date('Y-m-d');
        }
        
        $sql = "
            SELECT 
                COUNT(DISTINCT c.id) as conversas_hoje,
                COUNT(DISTINCT m.id) as mensagens_hoje,
                AVG(c.tempo_resposta_medio) as tempo_medio_resposta
            FROM conversas c
            LEFT JOIN mensagens m ON c.id = m.conversa_id
            WHERE DATE(c.criado_em) = :data OR DATE(m.criado_em) = :data
        ";
        
        $this->db->query($sql);
        $this->db->bind(':data', $data);
        return $this->db->resultado();
    }

    /**
     * [ getPerformanceAtendentes ] - Performance dos atendentes
     * 
     * @param string $data Data para análise
     * @return array Lista com performance dos atendentes
     */
    public function getPerformanceAtendentes($data = null)
    {
        if (!$data) {
            $data = date('Y-m-d');
        }
        
        $sql = "
            SELECT 
                u.nome,
                u.status,
                COUNT(DISTINCT c.id) as total_conversas,
                COUNT(DISTINCT m.id) as total_mensagens,
                AVG(c.tempo_resposta_medio) as tempo_medio
            FROM usuarios u
            LEFT JOIN conversas c ON u.id = c.atendente_id AND DATE(c.criado_em) = :data
            LEFT JOIN mensagens m ON u.id = m.atendente_id AND DATE(m.criado_em) = :data
            WHERE u.perfil = 'atendente'
            GROUP BY u.id, u.nome, u.status
            ORDER BY total_conversas DESC
        ";
        
        $this->db->query($sql);
        $this->db->bind(':data', $data);
        return $this->db->resultados();
    }

    /**
     * [ contarConversasAbertas ] - Conta conversas abertas/pendentes
     * 
     * @return int Número de conversas abertas
     */
    public function contarConversasAbertas()
    {
        $sql = "SELECT COUNT(*) as total FROM conversas WHERE status IN ('aberto', 'pendente')";
        $this->db->query($sql);
        return $this->db->resultado()->total;
    }

    /**
     * [ contarConversasPorAtendente ] - Conta conversas de um atendente
     * 
     * @param int $atendenteId ID do atendente
     * @return int Número de conversas
     */
    public function contarConversasPorAtendente($atendenteId)
    {
        $sql = "SELECT COUNT(*) as total FROM conversas WHERE atendente_id = :id AND status IN ('aberto', 'pendente')";
        $this->db->query($sql);
        $this->db->bind(':id', $atendenteId);
        return $this->db->resultado()->total;
    }

    /**
     * [ verificarConversaAtiva ] - Verifica se existe conversa ativa para um número
     * 
     * @param string $numero Número do contato
     * @return object|null Conversa encontrada ou null
     */
    public function verificarConversaAtiva($numero)
    {
        $sql = "
            SELECT c.*, ct.numero 
            FROM conversas c
            JOIN contatos ct ON c.contato_id = ct.id
            WHERE ct.numero = :numero 
            AND c.status IN ('aberto', 'pendente')
            AND c.criado_em >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY c.criado_em DESC
            LIMIT 1
        ";

        $this->db->query($sql);
        $this->db->bind(':numero', $numero);
        return $this->db->resultado();
    }

    /**
     * [ verificarConversaPorId ] - Verifica se conversa existe e retorna dados
     * 
     * @param int $conversaId ID da conversa
     * @return object|null Conversa encontrada ou null
     */
    public function verificarConversaPorId($conversaId)
    {
        $sql = "
            SELECT c.*, ct.numero, ct.nome as contato_nome, c.departamento_id
            FROM conversas c
            JOIN contatos ct ON c.contato_id = ct.id
            WHERE c.id = :id
        ";

        $this->db->query($sql);
        $this->db->bind(':id', $conversaId);
        return $this->db->resultado();
    }

    /**
     * [ conversaAindaAtiva ] - Verifica se conversa ainda está dentro do prazo de 24h
     * 
     * @param object $conversa Dados da conversa
     * @return bool True se ainda está ativa
     */
    public function conversaAindaAtiva($conversa)
    {
        $agora = time();
        $criadoEm = strtotime($conversa->criado_em);
        
        // Buscar a última mensagem (enviada ou recebida) para resetar o timer
        $sql = "
            SELECT MAX(criado_em) as ultima_mensagem_geral
            FROM mensagens 
            WHERE conversa_id = :conversa_id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversa->id);
        $resultado = $this->db->resultado();
        
        // Se há mensagem, usar ela como referência
        if ($resultado && $resultado->ultima_mensagem_geral) {
            $ultimaMensagem = strtotime($resultado->ultima_mensagem_geral);
            $tempoLimite = $ultimaMensagem + (24 * 60 * 60); // 24 horas da última mensagem
        } else {
            // Se não há mensagem, usar a criação da conversa
            $tempoLimite = $criadoEm + (24 * 60 * 60); // 24 horas da criação
        }
        
        return $agora < $tempoLimite;
    }

    /**
     * [ atualizarConversa ] - Atualiza uma conversa existente
     * 
     * @param int $id ID da conversa
     * @param array $dados Dados para atualização
     * @return bool Sucesso da operação
     */
    public function atualizarConversa($id, $dados)
    {
        $campos = [];
        $valores = [];
        
        foreach ($dados as $campo => $valor) {
            $campos[] = "$campo = :$campo";
            $valores[":$campo"] = $valor;
        }
        
        $sql = "UPDATE conversas SET " . implode(', ', $campos) . " WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        foreach ($valores as $param => $valor) {
            $this->db->bind($param, $valor);
        }
        
        return $this->db->executa();
    }

    /**
     * [ criarConversa ] - Cria uma nova conversa
     * 
     * @param array $dados Dados da conversa
     * @return int|false ID da conversa criada ou false
     */
    public function criarConversa($dados)
    {
        // Buscar uma sessão ativa para usar como sessao_id
        $sessaoId = $this->obterSessaoAtiva();
        
        $sql = "
            INSERT INTO conversas (
                contato_id, atendente_id, sessao_id, status, departamento_id, criado_em
            ) VALUES (
                :contato_id, :atendente_id, :sessao_id, :status, :departamento_id, NOW()
            )
        ";
        
        $this->db->query($sql);
        $this->db->bind(':contato_id', $dados['contato_id']);
        $this->db->bind(':atendente_id', $dados['atendente_id'] ?? null);
        $this->db->bind(':sessao_id', $sessaoId);
        $this->db->bind(':status', $dados['status'] ?? 'pendente');
        $this->db->bind(':departamento_id', $dados['departamento_id'] ?? null);
        if ($this->db->executa()) {
            return $this->db->ultimoIdInserido();
        }
        
        return false;
    }

    /**
     * [ reativarConversa ] - Reativa uma conversa expirada
     * 
     * @param int $conversaId ID da conversa
     * @return bool Sucesso da operação
     */
    public function reativarConversa($conversaId)
    {
        $sql = "
            UPDATE conversas 
            SET status = 'aberto', 
                ultima_mensagem = NOW(),
                atualizado_em = NOW()
            WHERE id = :id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':id', $conversaId);
        
        return $this->db->executa();
    }

    /**
     * [ verificarConversaReativada ] - Verifica se uma conversa foi reativada recentemente
     * 
     * @param int $conversaId ID da conversa
     * @return bool True se foi reativada recentemente
     */
    public function verificarConversaReativada($conversaId)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM mensagens 
            WHERE conversa_id = :conversa_id 
            AND direcao = 'saida'
            AND criado_em >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversaId);
        $resultado = $this->db->resultado();
        
        return $resultado && $resultado->total > 0;
    }

    /**
     * [ buscarConversaAtivaContato ] - Busca conversa ativa para um contato específico
     * 
     * @param int $contatoId ID do contato
     * @return object|null Conversa encontrada ou null
     */
    public function buscarConversaAtivaContato($contatoId)
    {
        $sql = "
            SELECT c.*, ct.numero, ct.nome as contato_nome
            FROM conversas c
            JOIN contatos ct ON c.contato_id = ct.id
            WHERE c.contato_id = :contato_id 
            AND c.status IN ('aberto', 'pendente')
            ORDER BY c.criado_em DESC
            LIMIT 1
        ";

        $this->db->query($sql);
        $this->db->bind(':contato_id', $contatoId);
        return $this->db->resultado();
    }

    /**
     * [ getConversasPorDepartamento ] - Busca conversas de um departamento específico
     * 
     * @param int $departamentoId ID do departamento
     * @param array $status Status das conversas a buscar
     * @param array $filtros Filtros adicionais
     * @return array Lista de conversas
     */
    public function getConversasPorDepartamento($departamentoId, $status = ['aberto', 'pendente'], $filtros = [])
    {
        $statusPlaceholders = implode(',', array_fill(0, count($status), '?'));
        
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                u.nome as atendente_nome,
                c.status,
                c.criado_em,
                c.ultima_mensagem,
                d.nome as departamento_nome,
                d.cor as departamento_cor,
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            WHERE c.departamento_id = ? 
            AND c.status IN ($statusPlaceholders)
        ";
        
        // Adicionar filtros adicionais
        if (!empty($filtros['atendente_id'])) {
            $sql .= " AND c.atendente_id = ?";
        }
        
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= ?";
        }
        
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= ?";
        }
        
        $sql .= " ORDER BY c.ultima_mensagem DESC";
        
        if (!empty($filtros['limite'])) {
            $sql .= " LIMIT ?";
        }
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        $paramIndex = 1;
        $this->db->bind($paramIndex++, $departamentoId);
        
        foreach ($status as $st) {
            $this->db->bind($paramIndex++, $st);
        }
        
        // Bind dos filtros adicionais
        if (!empty($filtros['atendente_id'])) {
            $this->db->bind($paramIndex++, $filtros['atendente_id']);
        }
        
        if (!empty($filtros['data_inicio'])) {
            $this->db->bind($paramIndex++, $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $this->db->bind($paramIndex++, $filtros['data_fim']);
        }
        
        if (!empty($filtros['limite'])) {
            $this->db->bind($paramIndex++, $filtros['limite']);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ getConversasAtivasPorDepartamento ] - Busca conversas ativas de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $limite Limite de resultados
     * @return array Lista de conversas ativas
     */
    public function getConversasAtivasPorDepartamento($departamentoId, $limite = 10)
    {
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                u.nome as atendente_nome,
                c.status,
                c.criado_em,
                c.ultima_mensagem,
                d.nome as departamento_nome,
                d.cor as departamento_cor,
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            WHERE c.departamento_id = :departamento_id
            AND c.status IN ('aberto', 'pendente')
            ORDER BY c.ultima_mensagem DESC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }

    /**
     * [ getConversasPendentesPorDepartamento ] - Busca conversas pendentes de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $limite Limite de resultados
     * @return array Lista de conversas pendentes
     */
    public function getConversasPendentesPorDepartamento($departamentoId, $limite = 5)
    {
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                c.criado_em,
                c.ultima_mensagem,
                d.nome as departamento_nome,
                d.cor as departamento_cor,
                u.nome as atendente_nome,
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
            WHERE c.departamento_id = :departamento_id
            AND c.atendente_id IS NULL 
            AND c.status = 'pendente'
            ORDER BY c.criado_em ASC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }

    /**
     * [ criarConversaComDepartamento ] - Cria uma nova conversa com departamento
     * 
     * @param array $dados Dados da conversa
     * @param int $departamentoId ID do departamento
     * @return int|false ID da conversa criada ou false em caso de erro
     */
    public function criarConversaComDepartamento($dados, $departamentoId)
    {
        // Buscar uma sessão ativa para usar como sessao_id
        $sessaoId = $this->obterSessaoAtiva();
        
        $sql = "
            INSERT INTO conversas 
            (contato_id, atendente_id, sessao_id, status, prioridade, departamento_id, criado_em) 
            VALUES 
            (:contato_id, :atendente_id, :sessao_id, :status, :prioridade, :departamento_id, NOW())
        ";
        
        $this->db->query($sql);
        $this->db->bind(':contato_id', $dados['contato_id']);
        $this->db->bind(':atendente_id', $dados['atendente_id'] ?? null);
        $this->db->bind(':sessao_id', $sessaoId);
        $this->db->bind(':status', $dados['status'] ?? 'pendente');
        $this->db->bind(':prioridade', $dados['prioridade'] ?? 'normal');
        $this->db->bind(':departamento_id', $departamentoId);
        
        if ($this->db->executa()) {
            return $this->db->ultimoIdInserido();
        }
        
        return false;
    }

    /**
     * [ obterSessaoAtiva ] - Obtém uma sessão ativa para usar na conversa
     * 
     * @return int ID da sessão ativa
     */
    private function obterSessaoAtiva()
    {
        // Primeiro, tentar buscar uma sessão conectada
        $sql = "SELECT id FROM sessoes_whatsapp WHERE status = 'conectado' LIMIT 1";
        $this->db->query($sql);
        $sessao = $this->db->resultado();
        
        if ($sessao) {
            return $sessao->id;
        }
        
        // Se não encontrar sessão conectada, buscar qualquer sessão ativa
        $sql = "SELECT id FROM sessoes_whatsapp WHERE status = 'ativo' LIMIT 1";
        $this->db->query($sql);
        $sessao = $this->db->resultado();
        
        if ($sessao) {
            return $sessao->id;
        }
        
        // Se não encontrar nenhuma sessão, buscar a primeira disponível
        $sql = "SELECT id FROM sessoes_whatsapp LIMIT 1";
        $this->db->query($sql);
        $sessao = $this->db->resultado();
        
        if ($sessao) {
            return $sessao->id;
        }
        
        // Fallback para ID 1 (assumindo que sempre existe)
        return 1;
    }

    /**
     * [ determinarDepartamentoConversa ] - Determina departamento para uma conversa
     * 
     * @param string $numero Número do telefone
     * @param string $mensagem Mensagem inicial (opcional)
     * @return int ID do departamento
     */
    public function determinarDepartamentoConversa($numero, $mensagem = '')
    {
        // Verificar se a classe já foi carregada
        if (!class_exists('DepartamentoHelper')) {
            require_once __DIR__ . '/../Libraries/DepartamentoHelper.php';
        }
        
        $departamentoHelper = new DepartamentoHelper();
        
        $departamentoId = $departamentoHelper->identificarDepartamento($numero, $mensagem);
        
        // Log da identificação
        $departamentoHelper->logIdentificacao($numero, $mensagem, $departamentoId, 'ConversaModel');
        
        return $departamentoId;
    }

    /**
     * [ contarConversasPorDepartamento ] - Conta conversas de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param array $status Status das conversas a contar
     * @return int Número de conversas
     */
    public function contarConversasPorDepartamento($departamentoId, $status = ['aberto', 'pendente'])
    {
        $statusPlaceholders = implode(',', array_fill(0, count($status), '?'));
        
        $sql = "SELECT COUNT(*) as total FROM conversas 
                WHERE departamento_id = ? 
                AND status IN ($statusPlaceholders)";
        
        $this->db->query($sql);
        
        $paramIndex = 1;
        $this->db->bind($paramIndex++, $departamentoId);
        
        foreach ($status as $st) {
            $this->db->bind($paramIndex++, $st);
        }
        
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->total : 0;
    }

    /**
     * [ getEstatisticasPorDepartamento ] - Estatísticas de conversas por departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $dias Número de dias para análise
     * @return object Estatísticas do departamento
     */
    public function getEstatisticasPorDepartamento($departamentoId, $dias = 30)
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'aberto' THEN 1 ELSE 0 END) as abertas,
                SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = 'fechado' THEN 1 ELSE 0 END) as fechadas,
                AVG(TIMESTAMPDIFF(MINUTE, criado_em, COALESCE(ultima_mensagem, NOW()))) as duracao_media_minutos
            FROM conversas 
            WHERE departamento_id = :departamento_id
            AND criado_em >= DATE_SUB(NOW(), INTERVAL :dias DAY)
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':dias', $dias);
        return $this->db->resultado();
    }

    /**
     * [ getConversasPendentesPorAtendente ] - Busca conversas pendentes de um atendente específico
     * 
     * @param int $atendenteId ID do atendente
     * @param int $limite Limite de resultados
     * @return array Lista de conversas pendentes
     */
    public function getConversasPendentesPorAtendente($atendenteId, $limite = 5)
    {
        $sql = "
            SELECT 
                c.id,
                ct.nome as contato_nome,
                ct.numero,
                c.criado_em,
                c.ultima_mensagem,
                c.departamento_id,
                d.nome as departamento_nome,
                d.cor as departamento_cor,
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            WHERE c.atendente_id = :atendente_id 
            AND c.status = 'pendente'
            ORDER BY c.criado_em ASC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':atendente_id', $atendenteId);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }
} 