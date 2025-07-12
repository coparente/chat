<?php

/**
 * [ MENSAGEMMODEL ] - Model para gerenciamento de mensagens
 * 
 * Esta classe gerencia todas as operações relacionadas às mensagens:
 * - CRUD de mensagens
 * - Estatísticas de mensagens
 * - Contagem de mensagens não lidas
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class MensagemModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ getEstatisticasMensagens ] - Estatísticas de mensagens por período
     * 
     * @param string $data Data para análise (formato Y-m-d)
     * @return object Estatísticas das mensagens
     */
    public function getEstatisticasMensagens($data = null)
    {
        if (!$data) {
            $data = date('Y-m-d');
        }
        
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN direcao = 'entrada' THEN 1 ELSE 0 END) as recebidas,
                SUM(CASE WHEN direcao = 'saida' THEN 1 ELSE 0 END) as enviadas
            FROM mensagens 
            WHERE DATE(criado_em) = :data
        ";
        
        $this->db->query($sql);
        $this->db->bind(':data', $data);
        return $this->db->resultado();
    }

    /**
     * [ contarMensagensNaoLidas ] - Conta mensagens não lidas por atendente
     * 
     * @param int $atendenteId ID do atendente
     * @return int Número de mensagens não lidas
     */
    public function contarMensagensNaoLidas($atendenteId)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM mensagens m
            JOIN conversas c ON m.conversa_id = c.id
            WHERE c.atendente_id = :atendente_id 
            AND m.lida = 0 
            AND m.direcao = 'entrada'
        ";
        
        $this->db->query($sql);
        $this->db->bind(':atendente_id', $atendenteId);
        return $this->db->resultado()->total;
    }

    /**
     * [ contarMensagensHoje ] - Conta mensagens do dia
     * 
     * @return int Número de mensagens de hoje
     */
    public function contarMensagensHoje()
    {
        $sql = "SELECT COUNT(*) as total FROM mensagens WHERE DATE(criado_em) = CURDATE()";
        $this->db->query($sql);
        return $this->db->resultado()->total;
    }

    /**
     * [ getMensagensPorConversa ] - Busca mensagens de uma conversa
     * 
     * @param int $conversaId ID da conversa
     * @param int $limite Limite de mensagens
     * @param int $offset Offset para paginação
     * @return array Lista de mensagens
     */
    public function getMensagensPorConversa($conversaId, $limite = 50, $offset = 0)
    {
        $sql = "
            SELECT 
                m.*,
                u.nome as atendente_nome,
                ct.nome as contato_nome
            FROM mensagens m
            LEFT JOIN usuarios u ON m.atendente_id = u.id
            LEFT JOIN contatos ct ON m.contato_id = ct.id
            WHERE m.conversa_id = :conversa_id
            ORDER BY m.criado_em ASC
            LIMIT :limite OFFSET :offset
        ";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversaId);
        $this->db->bind(':limite', $limite);
        $this->db->bind(':offset', $offset);
        return $this->db->resultados();
    }

    /**
     * [ criarMensagem ] - Cria uma nova mensagem
     * 
     * @param array $dados Dados da mensagem
     * @return bool Sucesso da operação
     */
    public function criarMensagem($dados)
    {
        $sql = "
            INSERT INTO mensagens (
                conversa_id, contato_id, atendente_id, serpro_message_id, 
                tipo, conteudo, midia_url, midia_nome, midia_tipo, 
                direcao, status_entrega, metadata, criado_em
            ) VALUES (
                :conversa_id, :contato_id, :atendente_id, :serpro_message_id,
                :tipo, :conteudo, :midia_url, :midia_nome, :midia_tipo,
                :direcao, :status_entrega, :metadata, NOW()
            )
        ";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $dados['conversa_id']);
        $this->db->bind(':contato_id', $dados['contato_id']);
        $this->db->bind(':atendente_id', $dados['atendente_id'] ?? null);
        $this->db->bind(':serpro_message_id', $dados['serpro_message_id'] ?? null);
        $this->db->bind(':tipo', $dados['tipo'] ?? 'texto');
        $this->db->bind(':conteudo', $dados['conteudo']);
        $this->db->bind(':midia_url', $dados['midia_url'] ?? null);
        $this->db->bind(':midia_nome', $dados['midia_nome'] ?? null);
        $this->db->bind(':midia_tipo', $dados['midia_tipo'] ?? null);
        $this->db->bind(':direcao', $dados['direcao']);
        $this->db->bind(':status_entrega', $dados['status_entrega'] ?? 'enviando');
        $this->db->bind(':metadata', $dados['metadata'] ?? null);
        
        return $this->db->executa();
    }

    /**
     * [ marcarComoLida ] - Marca mensagem como lida
     * 
     * @param int $mensagemId ID da mensagem
     * @return bool Sucesso da operação
     */
    public function marcarComoLida($mensagemId)
    {
        $sql = "UPDATE mensagens SET lida = 1, lida_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $mensagemId);
        return $this->db->executa();
    }

    /**
     * [ marcarMensagensConversaLidas ] - Marca todas mensagens de uma conversa como lidas
     * 
     * @param int $conversaId ID da conversa
     * @return bool Sucesso da operação
     */
    public function marcarMensagensConversaLidas($conversaId)
    {
        $sql = "UPDATE mensagens SET lida = 1, lida_em = NOW() WHERE conversa_id = :conversa_id AND lida = 0";
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversaId);
        return $this->db->executa();
    }

    /**
     * [ atualizarStatusEntrega ] - Atualiza status de entrega da mensagem
     * 
     * @param int $mensagemId ID da mensagem
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function atualizarStatusEntrega($mensagemId, $status)
    {
        $statusValidos = ['enviando', 'enviado', 'entregue', 'lido', 'erro'];
        
        if (!in_array($status, $statusValidos)) {
            return false;
        }
        
        $sql = "UPDATE mensagens SET status_entrega = :status WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $mensagemId);
        return $this->db->executa();
    }

    /**
     * [ buscarMensagensPorTexto ] - Busca mensagens por conteúdo
     * 
     * @param string $texto Texto a ser buscado
     * @param int $conversaId ID da conversa (opcional)
     * @return array Lista de mensagens encontradas
     */
    public function buscarMensagensPorTexto($texto, $conversaId = null)
    {
        $sql = "
            SELECT 
                m.*,
                c.id as conversa_id,
                ct.nome as contato_nome,
                u.nome as atendente_nome
            FROM mensagens m
            JOIN conversas c ON m.conversa_id = c.id
            LEFT JOIN contatos ct ON m.contato_id = ct.id
            LEFT JOIN usuarios u ON m.atendente_id = u.id
            WHERE m.conteudo LIKE :texto
        ";
        
        if ($conversaId) {
            $sql .= " AND m.conversa_id = :conversa_id";
        }
        
        $sql .= " ORDER BY m.criado_em DESC LIMIT 50";
        
        $this->db->query($sql);
        $this->db->bind(':texto', "%$texto%");
        
        if ($conversaId) {
            $this->db->bind(':conversa_id', $conversaId);
        }
        
        return $this->db->resultados();
    }
} 