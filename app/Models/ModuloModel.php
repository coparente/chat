<?php

/**
 * [ MODULOMODEL ] - Model responsável por gerenciar os módulos do sistema
 * 
 * Esta classe lida com a persistência e recuperação de dados relacionados aos módulos,
 * incluindo permissões de usuário e estrutura hierárquica de módulos.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access protected
 */     
class ModuloModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ getModulosComSubmodulos ] - Busca todos os módulos com suas hierarquias
     * 
     * @param int|null $usuario_id ID do usuário (se null, retorna todos os módulos)
     * @return array Array contendo os módulos organizados hierarquicamente
     */
    public function getModulosComSubmodulos($usuario_id = null)
    {
        if ($usuario_id) {
            // Se usuário específico, busca apenas módulos permitidos
            $sql = "SELECT DISTINCT m.id, m.nome, m.rota, m.icone, m.pai_id, m.status
                    FROM modulos m
                    LEFT JOIN permissoes_usuario pu ON m.id = pu.modulo_id
                    WHERE m.status = 'ativo' 
                    AND (pu.usuario_id = :usuario_id OR :usuario_id IS NULL)
                    ORDER BY m.pai_id ASC, m.nome ASC";
            
            $this->db->query($sql);
            $this->db->bind(':usuario_id', $usuario_id);
        } else {
            // Se sem usuário, busca todos os módulos ativos
            $sql = "SELECT id, nome, rota, icone, pai_id, status
                    FROM modulos 
                    WHERE status = 'ativo'
                    ORDER BY pai_id ASC, nome ASC";
            
            $this->db->query($sql);
        }

        $modulos = $this->db->resultados();
        return $this->organizarHierarquia($modulos);
    }

    /**
     * [ organizarHierarquia ] - Organiza os módulos em estrutura hierárquica
     * 
     * @param array $modulos Lista de módulos
     * @return array Módulos organizados hierarquicamente
     */
    private function organizarHierarquia($modulos)
    {
        $organizados = [];
        $temp = [];

        // Primeiro, organizar por ID para facilitar a busca
        foreach ($modulos as $modulo) {
            $temp[$modulo->id] = $modulo;
            $temp[$modulo->id]->submodulos = [];
        }

        // Depois, organizar a hierarquia
        foreach ($temp as $modulo) {
            if ($modulo->pai_id == null) {
                // É um módulo principal
                $organizados[] = $modulo;
            } else {
                // É um submódulo, adicionar ao pai
                if (isset($temp[$modulo->pai_id])) {
                    $temp[$modulo->pai_id]->submodulos[] = $modulo;
                }
            }
        }

        return $organizados;
    }

    /**
     * [ verificarPermissao ] - Verifica se o usuário tem permissão para um módulo
     * 
     * @param int $usuario_id ID do usuário
     * @param int $modulo_id ID do módulo
     * @return bool True se tem permissão, false caso contrário
     */
    public function verificarPermissao($usuario_id, $modulo_id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM permissoes_usuario 
                WHERE usuario_id = :usuario_id AND modulo_id = :modulo_id";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':modulo_id', $modulo_id);
        
        $resultado = $this->db->resultado();
        return $resultado->total > 0;
    }

    /**
     * [ listarModulos ] - Lista todos os módulos do sistema
     * 
     * @return array Lista de módulos
     */
    public function listarModulos()
    {
        $sql = "SELECT * FROM modulos ORDER BY pai_id ASC, nome ASC";
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ cadastrarModulo ] - Cadastra um novo módulo
     * 
     * @param array $dados Dados do módulo
     * @return bool True se cadastrou com sucesso
     */
    public function cadastrarModulo($dados)
    {
        $sql = "INSERT INTO modulos (nome, descricao, rota, icone, pai_id, status) 
                VALUES (:nome, :descricao, :rota, :icone, :pai_id, :status)";
        
        $this->db->query($sql);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao'] ?? null);
        $this->db->bind(':rota', $dados['rota']);
        $this->db->bind(':icone', $dados['icone'] ?? null);
        $this->db->bind(':pai_id', $dados['pai_id'] ?? null);
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        
        return $this->db->executa();
    }

    /**
     * [ atualizarModulo ] - Atualiza um módulo existente
     * 
     * @param int $id ID do módulo
     * @param array $dados Dados do módulo
     * @return bool True se atualizou com sucesso
     */
    public function atualizarModulo($id, $dados)
    {
        $sql = "UPDATE modulos SET 
                nome = :nome,
                descricao = :descricao,
                rota = :rota,
                icone = :icone,
                pai_id = :pai_id,
                status = :status,
                atualizado_em = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao'] ?? null);
        $this->db->bind(':rota', $dados['rota']);
        $this->db->bind(':icone', $dados['icone'] ?? null);
        $this->db->bind(':pai_id', $dados['pai_id'] ?? null);
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        
        return $this->db->executa();
    }

    /**
     * [ excluirModulo ] - Exclui um módulo
     * 
     * @param int $id ID do módulo
     * @return bool True se excluiu com sucesso
     */
    public function excluirModulo($id)
    {
        // Primeiro remove as permissões relacionadas
        $this->db->query("DELETE FROM permissoes_usuario WHERE modulo_id = :modulo_id");
        $this->db->bind(':modulo_id', $id);
        $this->db->executa();
        
        // Depois remove o módulo
        $this->db->query("DELETE FROM modulos WHERE id = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * [ buscarModuloPorId ] - Busca um módulo pelo ID
     * 
     * @param int $id ID do módulo
     * @return object|null Dados do módulo
     */
    public function buscarModuloPorId($id)
    {
        $sql = "SELECT * FROM modulos WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->resultado();
    }

    /**
     * [ getModulosPrincipais ] - Busca apenas módulos principais (sem pai)
     * 
     * @return array Lista de módulos principais
     */
    public function getModulosPrincipais()
    {
        $sql = "SELECT * FROM modulos WHERE pai_id IS NULL AND status = 'ativo' ORDER BY nome ASC";
        $this->db->query($sql);
        return $this->db->resultados();
    }
} 