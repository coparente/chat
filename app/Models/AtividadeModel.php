<?php

/**
 * [ ATIVIDADEMODEL ] - Model responsável por gerenciar as atividades dos usuários no sistema.
 * 
 * Esta classe lida com a persistência e recuperação de dados relacionados às atividades realizadas pelos usuários,
 * incluindo a inserção de novas atividades e a consulta dessas atividades.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025-2025 TJGO
 * @version 1.0.1
 * @access protected
 */ 

class AtividadeModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Registra uma atividade no sistema
     * @param int $usuario_id ID do usuário que realizou a ação
     * @param string $acao Ação realizada
     * @param string $descricao Descrição detalhada da atividade
     * @return bool True se a atividade foi registrada com sucesso, false caso contrário
     */
    public function registrarAtividade($usuario_id, $acao, $descricao) {

        $this->db->query('INSERT INTO atividades (usuario_id, acao, descricao) 
                          VALUES (:usuario_id, :acao, :descricao)');
        
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':acao', $acao);
        $this->db->bind(':descricao', $descricao);

        return $this->db->executa();
    }

    /**
     * Lista as atividades do sistema
     * @param int $limite Limite de resultados a serem retornados
     * @return array Array de atividades
     */
    public function listarAtividades($limite = 100) {
        $this->db->query('SELECT a.*, u.nome as usuario_nome, u.perfil as usuario_perfil 
                            FROM atividades a 
                            JOIN usuarios u ON a.usuario_id = u.id 
                            ORDER BY a.data_hora DESC 
                            LIMIT :limite');
        
        $this->db->bind(':limite', $limite);
        
        return $this->db->resultados();
    }

    /**
     * Lista as atividades do sistema com paginação para DataTables
     * @param array $params Parâmetros do DataTables
     * @return array Array com dados e informações de paginação
     */
    public function listarAtividadesDataTable($params) {
        try {
            // Query base
            $sql = "SELECT a.*, u.nome as usuario_nome, u.perfil as usuario_perfil 
                    FROM atividades a 
                    JOIN usuarios u ON a.usuario_id = u.id";
            
            // Debug da query
            // error_log('SQL Base: ' . $sql);

            // Total de registros sem filtro
            $this->db->query("SELECT COUNT(*) as total FROM atividades");
            $recordsTotal = $this->db->resultado()->total;
            // error_log('Total de registros: ' . $recordsTotal);

            // Busca
            $where = [];
            if (!empty($params['search']['value'])) {
                $where[] = "(u.nome LIKE :search OR a.acao LIKE :search OR a.descricao LIKE :search)";
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            // Total de registros com filtro
            $sqlCount = str_replace("a.*, u.nome as usuario_nome, u.perfil as usuario_perfil", "COUNT(*) as total", $sql);
            $this->db->query($sqlCount);
            if (!empty($params['search']['value'])) {
                $this->db->bind(':search', "%{$params['search']['value']}%");
            }
            $recordsFiltered = $this->db->resultado()->total;

            // Ordenação
            $colunas = ['data_hora', 'usuario_nome', 'usuario_perfil', 'acao', 'descricao'];
            $coluna = $colunas[$params['order'][0]['column']] ?? 'data_hora';
            $direcao = $params['order'][0]['dir'] ?? 'DESC';
            $sql .= " ORDER BY {$coluna} {$direcao}";

            // Paginação
            $sql .= " LIMIT :limit OFFSET :offset";
            
            $this->db->query($sql);
            
            if (!empty($params['search']['value'])) {
                $this->db->bind(':search', "%{$params['search']['value']}%");
            }
            
            $this->db->bind(':limit', (int)$params['length'], PDO::PARAM_INT);
            $this->db->bind(':offset', (int)$params['start'], PDO::PARAM_INT);

            $resultados = $this->db->resultados();
            // error_log('Resultados encontrados: ' . count($resultados));

            return [
                'draw' => isset($params['draw']) ? intval($params['draw']) : 0,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $resultados
            ];
        } catch (Exception $e) {
            // error_log('Erro no DataTable: ' . $e->getMessage());
            throw $e;
        }
    }
} 