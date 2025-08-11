<?php

/**
 * [ CONTATOMODEL ] - Model para gerenciamento de contatos do ChatSerpro
 * 
 * Esta classe gerencia todas as operações relacionadas aos contatos:
 * - CRUD de contatos
 * - Sistema de tags
 * - Bloqueio/desbloqueio
 * - Histórico de conversas
 * - Integração com WhatsApp
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class ContatoModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ getEstatisticasContatos ] - Estatísticas dos contatos
     * 
     * @return object Estatísticas dos contatos
     */
    public function getEstatisticasContatos()
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN bloqueado = 0 THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN bloqueado = 1 THEN 1 ELSE 0 END) as bloqueados,
                SUM(CASE WHEN DATE(ultimo_contato) = CURDATE() THEN 1 ELSE 0 END) as hoje,
                SUM(CASE WHEN DATE(ultimo_contato) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as semana
            FROM contatos
        ";
        
        $this->db->query($sql);
        return $this->db->resultado();
    }

    /**
     * [ listarContatos ] - Lista contatos com filtros e paginação
     * 
     * @param array $filtros Filtros de busca
     * @param int $pagina Página atual
     * @param int $limite Itens por página
     * @return array Lista de contatos
     */
    public function listarContatos($filtros = [], $pagina = 1, $limite = 15)
    {
        $sql = "
            SELECT c.*, 
                   COUNT(cv.id) as total_conversas,
                   MAX(cv.atualizado_em) as ultima_conversa,
                   GROUP_CONCAT(DISTINCT ct.tag SEPARATOR ', ') as tags,
                   d.nome as ultimo_departamento_nome,
                   d.cor as ultimo_departamento_cor,
                   u.nome as ultimo_agente_nome,
                   ultima_cv.status as status_conversa_atual,
                   ultima_cv.id as conversa_atual_id
            FROM contatos c
            LEFT JOIN conversas cv ON c.id = cv.contato_id
            LEFT JOIN contato_tags ct ON c.id = ct.contato_id
            LEFT JOIN (
                SELECT contato_id, departamento_id, atendente_id, status, id
                FROM conversas 
                WHERE (contato_id, atualizado_em) IN (
                    SELECT contato_id, MAX(atualizado_em)
                    FROM conversas 
                    GROUP BY contato_id
                )
            ) ultima_cv ON c.id = ultima_cv.contato_id
            LEFT JOIN departamentos d ON ultima_cv.departamento_id = d.id
            LEFT JOIN usuarios u ON ultima_cv.atendente_id = u.id
        ";
        
        $where = [];
        $params = [];
        
        // Filtro por texto (nome, telefone, email)
        if (!empty($filtros['busca'])) {
            $where[] = "(c.nome LIKE :busca_nome OR c.telefone LIKE :busca_telefone OR c.numero LIKE :busca_numero OR c.email LIKE :busca_email)";
            $like = '%' . $filtros['busca'] . '%'; $params['busca_nome'] = $like; $params['busca_telefone'] = $like; $params['busca_numero'] = $like; $params['busca_email'] = $like;
        }
        
        // Filtro por status (bloqueado/ativo)
        if (isset($filtros['bloqueado']) && $filtros['bloqueado'] !== '') {
            $where[] = "c.bloqueado = :bloqueado";
            $params['bloqueado'] = $filtros['bloqueado'];
        }
        
        // Filtro por tag
        if (!empty($filtros['tag'])) {
            $where[] = "ct.tag = :tag";
            $params['tag'] = $filtros['tag'];
        }
        
        // Filtro por período de último contato
        if (!empty($filtros['periodo'])) {
            switch ($filtros['periodo']) {
                case 'hoje':
                    $where[] = "DATE(c.ultimo_contato) = CURDATE()";
                    break;
                case 'semana':
                    $where[] = "DATE(c.ultimo_contato) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'mes':
                    $where[] = "DATE(c.ultimo_contato) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
            }
        }
        
        // Filtro por agente da última conversa
        if (!empty($filtros['agente'])) {
            $where[] = "ultima_cv.atendente_id = :agente";
            $params['agente'] = $filtros['agente'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " GROUP BY c.id ORDER BY c.ultimo_contato DESC";
        
        // Paginação
        $offset = ($pagina - 1) * $limite;
        $sql .= " LIMIT :limite OFFSET :offset";
        
        $this->db->query($sql);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        
        $this->db->bind(':limite', $limite);
        $this->db->bind(':offset', $offset);
        
        return $this->db->resultados();
    }

    /**
     * [ contarContatos ] - Conta total de contatos com filtros
     * 
     * @param array $filtros Filtros de busca
     * @return int Total de contatos
     */
    public function contarContatos($filtros = [])
    {
        $sql = "SELECT COUNT(DISTINCT c.id) as total FROM contatos c";
        
        if (!empty($filtros['tag'])) {
            $sql .= " LEFT JOIN contato_tags ct ON c.id = ct.contato_id";
        }
        
        $where = [];
        $params = [];
        
        if (!empty($filtros['busca'])) {
            $where[] = "(c.nome LIKE :busca_nome OR c.telefone LIKE :busca_telefone OR c.numero LIKE :busca_numero OR c.email LIKE :busca_email)";
            $like = '%' . $filtros['busca'] . '%'; $params['busca_nome'] = $like; $params['busca_telefone'] = $like; $params['busca_numero'] = $like; $params['busca_email'] = $like;
        }
        
        if (isset($filtros['bloqueado']) && $filtros['bloqueado'] !== '') {
            $where[] = "c.bloqueado = :bloqueado";
            $params['bloqueado'] = $filtros['bloqueado'];
        }
        
        if (!empty($filtros['tag'])) {
            $where[] = "ct.tag = :tag";
            $params['tag'] = $filtros['tag'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        
        return $this->db->resultado()->total;
    }

    /**
     * [ lerContatoPorId ] - Busca contato por ID
     * 
     * @param int $id ID do contato
     * @return object|null Dados do contato
     */
    public function lerContatoPorId($id)
    {
        $sql = "
            SELECT c.*, 
                   GROUP_CONCAT(DISTINCT ct.tag SEPARATOR ', ') as tags
            FROM contatos c
            LEFT JOIN contato_tags ct ON c.id = ct.contato_id
            WHERE c.id = :id
            GROUP BY c.id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * [ lerContatoPorTelefone ] - Busca contato por telefone
     * 
     * @param string $telefone Telefone do contato
     * @return object|null Dados do contato
     */
    public function lerContatoPorTelefone($telefone)
    {
        // Limpar telefone (apenas números)
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        $sql = "
            SELECT c.*, 
                   GROUP_CONCAT(DISTINCT ct.tag SEPARATOR ', ') as tags
            FROM contatos c
            LEFT JOIN contato_tags ct ON c.id = ct.contato_id
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(c.telefone, '(', ''), ')', ''), '-', ''), ' ', '') = :telefone1
               OR REPLACE(REPLACE(REPLACE(REPLACE(c.numero, '(', ''), ')', ''), '-', ''), ' ', '') = :telefone2
            GROUP BY c.id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':telefone1', $telefone);
        $this->db->bind(':telefone2', $telefone);
        return $this->db->resultado();
    }

    /**
     * [ getSessaoPadrao ] - Busca a sessão WhatsApp padrão
     * 
     * @return int ID da sessão padrão
     */
    private function getSessaoPadrao()
    {
        $sql = "SELECT id FROM sessoes_whatsapp ORDER BY id ASC LIMIT 1";
        $this->db->query($sql);
        $resultado = $this->db->resultado();
        return $resultado ? $resultado->id : 1; // Fallback para ID 1
    }

    /**
     * [ criarContato ] - Cria um novo contato
     * 
     * @param array $dados Dados do contato
     * @return int|false ID do contato criado ou false em caso de erro
     */
    public function criarContato($dados)
    {
        // Usar sessão padrão se não fornecida
        $sessaoId = $dados['sessao_id'] ?? $this->getSessaoPadrao();
        
        // Aceitar tanto 'telefone' quanto 'numero'
        $telefone = $dados['telefone'] ?? $dados['numero'] ?? null;
        
        if (!$telefone) {
            return false; // Telefone é obrigatório
        }
        
        $sql = "
            INSERT INTO contatos (nome, numero, telefone, email, empresa, observacoes, fonte, sessao_id, criado_em, ultimo_contato) 
            VALUES (:nome, :numero, :telefone, :email, :empresa, :observacoes, :fonte, :sessao_id, NOW(), NOW())
        ";
        
        $this->db->query($sql);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':numero', $telefone); // Usar telefone como numero também
        $this->db->bind(':telefone', $telefone);
        $this->db->bind(':email', $dados['email'] ?? null);
        $this->db->bind(':empresa', $dados['empresa'] ?? null);
        $this->db->bind(':observacoes', $dados['observacoes'] ?? null);
        $this->db->bind(':fonte', $dados['fonte'] ?? 'chat');
        $this->db->bind(':sessao_id', $sessaoId);
        
        if ($this->db->executa()) {
            return $this->db->ultimoIdInserido();
        }
        
        return false;
    }

    /**
     * [ atualizarContato ] - Atualiza um contato
     * 
     * @param int $id ID do contato
     * @param array $dados Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function atualizarContato($id, $dados)
    {
        $campos = [];
        $valores = [];
        
        $camposPermitidos = ['nome', 'telefone', 'numero', 'email', 'empresa', 'observacoes'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($dados[$campo])) {
                $campos[] = "$campo = :$campo";
                $valores[$campo] = $dados[$campo];
            }
        }
        
        // Se telefone foi fornecido, atualizar numero também
        if (isset($dados['telefone'])) {
            $campos[] = "numero = :numero";
            $valores['numero'] = $dados['telefone'];
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $sql = "UPDATE contatos SET " . implode(', ', $campos) . ", atualizado_em = NOW() WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        foreach ($valores as $campo => $valor) {
            $this->db->bind(":$campo", $valor);
        }
        
        return $this->db->executa();
    }

    /**
     * [ excluirContato ] - Remove um contato
     * 
     * @param int $id ID do contato
     * @return bool Sucesso da operação
     */
    public function excluirContato($id)
    {
        // Remover tags associadas primeiro
        $this->removerTodasTags($id);
        
        $sql = "DELETE FROM contatos WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ bloquearContato ] - Bloqueia um contato
     * 
     * @param int $id ID do contato
     * @return bool Sucesso da operação
     */
    public function bloquearContato($id)
    {
        $sql = "UPDATE contatos SET bloqueado = 1, atualizado_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ desbloquearContato ] - Desbloqueia um contato
     * 
     * @param int $id ID do contato
     * @return bool Sucesso da operação
     */
    public function desbloquearContato($id)
    {
        $sql = "UPDATE contatos SET bloqueado = 0, atualizado_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ atualizarUltimoContato ] - Atualiza timestamp do último contato
     * 
     * @param int $id ID do contato
     * @return bool Sucesso da operação
     */
    public function atualizarUltimoContato($id)
    {
        $sql = "UPDATE contatos SET ultimo_contato = NOW(), atualizado_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ adicionarTag ] - Adiciona uma tag ao contato
     * 
     * @param int $id ID do contato
     * @param string $tag Nome da tag
     * @return bool Sucesso da operação
     */
    public function adicionarTag($id, $tag)
    {
        // Verificar se tag já existe
        $sql = "SELECT COUNT(*) as total FROM contato_tags WHERE contato_id = :id AND tag = :tag";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':tag', $tag);
        
        if ($this->db->resultado()->total > 0) {
            return true; // Tag já existe
        }
        
        $sql = "INSERT INTO contato_tags (contato_id, tag, criado_em) VALUES (:id, :tag, NOW())";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':tag', $tag);
        return $this->db->executa();
    }

    /**
     * [ removerTag ] - Remove uma tag do contato
     * 
     * @param int $id ID do contato
     * @param string $tag Nome da tag
     * @return bool Sucesso da operação
     */
    public function removerTag($id, $tag)
    {
        $sql = "DELETE FROM contato_tags WHERE contato_id = :id AND tag = :tag";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':tag', $tag);
        return $this->db->executa();
    }

    /**
     * [ removerTodasTags ] - Remove todas as tags do contato
     * 
     * @param int $id ID do contato
     * @return bool Sucesso da operação
     */
    public function removerTodasTags($id)
    {
        $sql = "DELETE FROM contato_tags WHERE contato_id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ listarTags ] - Lista todas as tags disponíveis
     * 
     * @return array Lista de tags
     */
    public function listarTags()
    {
        $sql = "
            SELECT tag, COUNT(*) as total 
            FROM contato_tags 
            GROUP BY tag 
            ORDER BY total DESC, tag ASC
        ";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ getHistoricoConversa ] - Busca histórico de conversas do contato
     * 
     * @param int $id ID do contato
     * @param int $limite Limite de mensagens
     * @return array Histórico de conversas
     */
    public function getHistoricoConversa($id, $limite = 50)
    {
        $sql = "
            SELECT m.*, 
                   c.status as conversa_status, 
                   u.nome as atendente_nome,
                   CASE 
                       WHEN m.direcao = 'entrada' THEN 'recebida'
                       WHEN m.direcao = 'saida' THEN 'enviada'
                       ELSE 'sistema'
                   END as tipo,
                   m.tipo as tipo_conteudo
            FROM mensagens m
            JOIN conversas c ON m.conversa_id = c.id
            LEFT JOIN usuarios u ON c.atendente_id = u.id
            WHERE c.contato_id = :id
            ORDER BY m.criado_em DESC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }

    /**
     * [ buscarPorNumero ] - Busca contato por número (alias para lerContatoPorTelefone)
     * 
     * @param string $numero Número do contato
     * @return object|null Dados do contato
     */
    public function buscarPorNumero($numero)
    {
        return $this->lerContatoPorTelefone($numero);
    }

    /**
     * [ cadastrar ] - Cria um novo contato (alias para criarContato)
     * 
     * @param array $dados Dados do contato
     * @return int|false ID do contato criado ou false em caso de erro
     */
    public function cadastrar($dados)
    {
        return $this->criarContato($dados);
    }

    /**
     * [ verificarTelefone ] - Verifica se telefone já está cadastrado
     * 
     * @param string $telefone Telefone a verificar
     * @param int $excluirId ID para excluir da verificação
     * @return bool Telefone já existe
     */
    public function verificarTelefone($telefone, $excluirId = null)
    {
        // Limpar telefone (apenas números)
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        $sql = "
            SELECT COUNT(*) as total FROM contatos 
            WHERE (REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') = :telefone1
               OR REPLACE(REPLACE(REPLACE(REPLACE(numero, '(', ''), ')', ''), '-', ''), ' ', '') = :telefone2)
        ";
        
        $params = [
            'telefone1' => $telefone,
            'telefone2' => $telefone
        ];
        
        if ($excluirId) {
            $sql .= " AND id != :excluir_id";
            $params['excluir_id'] = $excluirId;
        }
        
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind(":$key", $value);
        }
        
        return $this->db->resultado()->total > 0;
    }
} 
