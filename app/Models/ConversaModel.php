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
                c.ultima_mensagem
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
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
                (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = 0 AND direcao = 'entrada') as mensagens_nao_lidas
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
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
                c.ultima_mensagem
            FROM conversas c
            LEFT JOIN contatos ct ON c.contato_id = ct.id
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
            SELECT c.*, ct.numero, ct.nome as contato_nome
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
        
        // Buscar a última mensagem recebida (entrada) para resetar o timer
        $sql = "
            SELECT MAX(criado_em) as ultima_mensagem_recebida
            FROM mensagens 
            WHERE conversa_id = :conversa_id 
            AND direcao = 'entrada'
        ";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversa->id);
        $resultado = $this->db->resultado();
        
        // Se há mensagem recebida, usar ela como referência
        if ($resultado && $resultado->ultima_mensagem_recebida) {
            $ultimaMensagemRecebida = strtotime($resultado->ultima_mensagem_recebida);
            $tempoLimite = $ultimaMensagemRecebida + (24 * 60 * 60); // 24 horas da última mensagem recebida
        } else {
            // Se não há mensagem recebida, usar a criação da conversa
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
        $sql = "
            INSERT INTO conversas (
                contato_id, atendente_id, sessao_id, status, criado_em
            ) VALUES (
                :contato_id, :atendente_id, :sessao_id, :status, NOW()
            )
        ";
        
        $this->db->query($sql);
        $this->db->bind(':contato_id', $dados['contato_id']);
        $this->db->bind(':atendente_id', $dados['atendente_id'] ?? null);
        $this->db->bind(':sessao_id', $dados['sessao_id'] ?? 1);
        $this->db->bind(':status', $dados['status'] ?? 'pendente');
        
        if ($this->db->executa()) {
            return $this->db->ultimoIdInserido();
        }
        
        return false;
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
} 