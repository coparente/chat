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
     * @return int|false ID da mensagem criada ou false
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
        
        if ($this->db->executa()) {
            return $this->db->ultimoIdInserido();
        }
        
        return false;
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

    /**
     * [ contatoJaRespondeu ] - Verifica se o contato já respondeu ao template mais recente
     * 
     * @param int $conversaId ID da conversa
     * @return bool True se o contato respondeu ao template mais recente
     */
    public function contatoJaRespondeu($conversaId)
    {
        // Primeiro, buscar o template mais recente enviado (verificando metadata)
        $sql = "
            SELECT MAX(criado_em) as ultimo_template_enviado
            FROM mensagens 
            WHERE conversa_id = :conversa_id 
            AND direcao = 'saida'
            AND metadata LIKE '%\"tipo\":\"template\"%'
        ";

        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversaId);
        $resultado = $this->db->resultado();

        if (!$resultado || !$resultado->ultimo_template_enviado) {
            // Se não há template enviado, verificar se há qualquer resposta
            $sql = "
                SELECT COUNT(*) as total
                FROM mensagens 
                WHERE conversa_id = :conversa_id 
                AND direcao = 'entrada'
            ";

            $this->db->query($sql);
            $this->db->bind(':conversa_id', $conversaId);
            return $this->db->resultado()->total > 0;
        }

        // Se há template enviado, verificar se há resposta após o template
        $sql = "
            SELECT COUNT(*) as total
            FROM mensagens 
            WHERE conversa_id = :conversa_id 
            AND direcao = 'entrada'
            AND criado_em > :ultimo_template_enviado
        ";

        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversaId);
        $this->db->bind(':ultimo_template_enviado', $resultado->ultimo_template_enviado);
        return $this->db->resultado()->total > 0;
    }

    /**
     * [ buscarPorSerproId ] - Busca mensagem pelo ID do Serpro
     * 
     * @param string $serproId ID da mensagem no Serpro
     * @return object|null Dados da mensagem
     */
    public function buscarPorSerproId($serproId)
    {
        $sql = "SELECT * FROM mensagens WHERE serpro_message_id = :serpro_id";
        $this->db->query($sql);
        $this->db->bind(':serpro_id', $serproId);
        return $this->db->resultado();
    }

    /**
     * [ contarMensagensConversa ] - Conta mensagens de uma conversa
     * 
     * @param int $conversaId ID da conversa
     * @return int Número de mensagens
     */
    public function contarMensagensConversa($conversaId)
    {
        $sql = "SELECT COUNT(*) as total FROM mensagens WHERE conversa_id = :conversa_id";
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversaId);
        return $this->db->resultado()->total;
    }
    
    /**
     * [ buscarMensagensComIdRequisicao ] - Busca mensagens de saída com idRequisicao
     * 
     * @param int $conversaId ID da conversa
     * @return array Lista de mensagens com idRequisicao
     */
    public function buscarMensagensComIdRequisicao($conversaId)
    {
        $sql = "
            SELECT 
                m.id,
                m.serpro_message_id,
                m.status_entrega,
                m.metadata,
                m.criado_em
            FROM mensagens m
            WHERE m.conversa_id = :conversa_id 
            AND m.direcao = 'saida'
            AND m.metadata IS NOT NULL
            ORDER BY m.criado_em DESC
        ";
        
        $this->db->query($sql);
        $this->db->bind(':conversa_id', $conversaId);
        $mensagens = $this->db->resultados();
        
        // Extrair idRequisicao do metadata (buscar em diferentes estruturas)
        $mensagensComId = [];
        foreach ($mensagens as $mensagem) {
            $metadata = json_decode($mensagem->metadata, true);
            $idRequisicao = null;
            
            // Tentar diferentes estruturas para encontrar idRequisicao
            if (isset($metadata['serpro_response']['idRequisicao'])) {
                $idRequisicao = $metadata['serpro_response']['idRequisicao'];
            } elseif (isset($metadata['serpro_response']['id'])) {
                // Algumas APIs podem usar 'id' em vez de 'idRequisicao'
                $idRequisicao = $metadata['serpro_response']['id'];
            } elseif (isset($metadata['idRequisicao'])) {
                // idRequisicao pode estar diretamente no metadata
                $idRequisicao = $metadata['idRequisicao'];
            } elseif (isset($metadata['id'])) {
                // id pode estar diretamente no metadata
                $idRequisicao = $metadata['id'];
            }
            
            if ($idRequisicao) {
                $mensagensComId[] = [
                    'id' => $mensagem->id,
                    'serpro_message_id' => $mensagem->serpro_message_id,
                    'status_entrega' => $mensagem->status_entrega,
                    'id_requisicao' => $idRequisicao,
                    'criado_em' => $mensagem->criado_em,
                    'metadata_structure' => $this->detectarEstrutura($metadata)
                ];
            }
        }
        
        return $mensagensComId;
    }
    
    /**
     * [ detectarEstrutura ] - Detecta a estrutura do metadata para debug
     * 
     * @param array $metadata Metadata da mensagem
     * @return string Descrição da estrutura
     */
    private function detectarEstrutura($metadata)
    {
        if (isset($metadata['serpro_response']['idRequisicao'])) {
            return 'serpro_response.idRequisicao';
        } elseif (isset($metadata['serpro_response']['id'])) {
            return 'serpro_response.id';
        } elseif (isset($metadata['idRequisicao'])) {
            return 'metadata.idRequisicao';
        } elseif (isset($metadata['id'])) {
            return 'metadata.id';
        } elseif (isset($metadata['serpro_response'])) {
            return 'serpro_response (sem id): ' . implode(', ', array_keys($metadata['serpro_response']));
        } else {
            return 'metadata: ' . implode(', ', array_keys($metadata));
        }
    }
    
    /**
     * [ atualizarStatusPorSerproId ] - Atualiza status de entrega pelo ID do Serpro
     * 
     * @param string $serproId ID da mensagem no Serpro
     * @param string $status Novo status (enviando, enviado, entregue, lido, erro)
     * @return bool Resultado da operação
     */
    public function atualizarStatusPorSerproId($serproId, $status)
    {
        $sql = "UPDATE mensagens SET status_entrega = :status WHERE serpro_message_id = :serpro_id";
        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':serpro_id', $serproId);
        return $this->db->executa();
    }
} 