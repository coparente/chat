<?php

/**
 * [ PERMISSAOMODEL ] - Model para gerenciamento de permissões
 * 
 * Esta classe gerencia todas as operações relacionadas às permissões:
 * - Verificação de permissões
 * - Gerenciamento de recursos
 * - Controle de acesso por usuário
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class PermissaoModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ verificarPermissao ] - Verifica se um usuário tem permissão para um recurso
     * 
     * @param int $usuarioId ID do usuário
     * @param string $recursoNome Nome do recurso
     * @param string $permissaoMinima Permissão mínima necessária (ler, escrever, excluir, admin)
     * @return bool True se tem permissão
     */
    public function verificarPermissao($usuarioId, $recursoNome, $permissaoMinima = 'ler')
    {
        $sql = "
            SELECT p.permissao
            FROM permissoes_usuario_nova p
            JOIN recursos r ON p.recurso_id = r.id
            WHERE p.usuario_id = :usuario_id 
            AND r.nome = :recurso_nome
            AND r.status = 'ativo'
        ";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':recurso_nome', $recursoNome);
        $resultado = $this->db->resultado();
        
        if (!$resultado) {
            return false;
        }
        
        // Hierarquia de permissões
        $hierarquia = [
            'ler' => 1,
            'escrever' => 2,
            'excluir' => 3,
            'admin' => 4
        ];
        
        $permissaoUsuario = $hierarquia[$resultado->permissao] ?? 0;
        $permissaoNecessaria = $hierarquia[$permissaoMinima] ?? 0;
        
        return $permissaoUsuario >= $permissaoNecessaria;
    }

    /**
     * [ verificarPermissaoModulo ] - Verifica permissão por módulo e ação
     * 
     * @param int $usuarioId ID do usuário
     * @param string $modulo Nome do módulo
     * @param string $acao Nome da ação
     * @param string $permissaoMinima Permissão mínima necessária
     * @return bool True se tem permissão
     */
    public function verificarPermissaoModulo($usuarioId, $modulo, $acao, $permissaoMinima = 'ler')
    {
        $sql = "
            SELECT p.permissao
            FROM permissoes_usuario_nova p
            JOIN recursos r ON p.recurso_id = r.id
            WHERE p.usuario_id = :usuario_id 
            AND r.modulo = :modulo
            AND r.acao = :acao
            AND r.status = 'ativo'
        ";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':modulo', $modulo);
        $this->db->bind(':acao', $acao);
        $resultado = $this->db->resultado();
        
        if (!$resultado) {
            return false;
        }
        
        // Hierarquia de permissões
        $hierarquia = [
            'ler' => 1,
            'escrever' => 2,
            'excluir' => 3,
            'admin' => 4
        ];
        
        $permissaoUsuario = $hierarquia[$resultado->permissao] ?? 0;
        $permissaoNecessaria = $hierarquia[$permissaoMinima] ?? 0;
        
        return $permissaoUsuario >= $permissaoNecessaria;
    }

    /**
     * [ getPermissoesUsuario ] - Obtém todas as permissões de um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array Lista de permissões
     */
    public function getPermissoesUsuario($usuarioId)
    {
        $sql = "
            SELECT 
                r.nome as recurso,
                r.descricao,
                r.modulo,
                r.acao,
                r.tipo,
                p.permissao
            FROM permissoes_usuario_nova p
            JOIN recursos r ON p.recurso_id = r.id
            WHERE p.usuario_id = :usuario_id
            AND r.status = 'ativo'
            ORDER BY r.modulo, r.acao
        ";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        return $this->db->resultados();
    }

    /**
     * [ getRecursosDisponiveis ] - Obtém todos os recursos disponíveis
     * 
     * @return array Lista de recursos
     */
    public function getRecursosDisponiveis()
    {
        $sql = "
            SELECT 
                id,
                nome,
                descricao,
                modulo,
                acao,
                tipo,
                status
            FROM recursos 
            WHERE status = 'ativo'
            ORDER BY modulo, acao
        ";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ getRecursosPorModulo ] - Obtém recursos agrupados por módulo
     * 
     * @return array Recursos agrupados por módulo
     */
    public function getRecursosPorModulo()
    {
        $sql = "
            SELECT 
                id,
                nome,
                descricao,
                modulo,
                acao,
                tipo,
                status
            FROM recursos 
            WHERE status = 'ativo'
            ORDER BY modulo, acao
        ";
        
        $this->db->query($sql);
        $resultados = $this->db->resultados();
        
        $modulos = [];
        foreach ($resultados as $recurso) {
            if (!isset($modulos[$recurso->modulo])) {
                $modulos[$recurso->modulo] = [];
            }
            
            $modulos[$recurso->modulo][] = [
                'id' => $recurso->id,
                'nome' => $recurso->nome,
                'descricao' => $recurso->descricao,
                'acao' => $recurso->acao,
                'tipo' => $recurso->tipo,
                'status' => $recurso->status
            ];
        }
        
        return $modulos;
    }

    /**
     * [ adicionarPermissao ] - Adiciona uma permissão para um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @param int $recursoId ID do recurso
     * @param string $permissao Tipo de permissão
     * @return bool Sucesso da operação
     */
    public function adicionarPermissao($usuarioId, $recursoId, $permissao = 'ler')
    {
        $sql = "
            INSERT INTO permissoes_usuario_nova (usuario_id, recurso_id, permissao) 
            VALUES (:usuario_id, :recurso_id, :permissao)
            ON DUPLICATE KEY UPDATE permissao = :permissao
        ";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':recurso_id', $recursoId);
        $this->db->bind(':permissao', $permissao);
        
        return $this->db->executa();
    }

    /**
     * [ removerPermissao ] - Remove uma permissão de um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @param int $recursoId ID do recurso
     * @return bool Sucesso da operação
     */
    public function removerPermissao($usuarioId, $recursoId)
    {
        $sql = "DELETE FROM permissoes_usuario_nova WHERE usuario_id = :usuario_id AND recurso_id = :recurso_id";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':recurso_id', $recursoId);
        
        return $this->db->executa();
    }

    /**
     * [ atualizarPermissoesUsuario ] - Atualiza todas as permissões de um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @param array $permissoes Array de permissões [recurso_id => permissao]
     * @return bool Sucesso da operação
     */
    public function atualizarPermissoesUsuario($usuarioId, $permissoes)
    {
        // Remover permissões existentes
        $sql = "DELETE FROM permissoes_usuario_nova WHERE usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->executa();
        
        // Adicionar novas permissões
        $sql = "INSERT INTO permissoes_usuario_nova (usuario_id, recurso_id, permissao) VALUES (:usuario_id, :recurso_id, :permissao)";
        $this->db->query($sql);
        
        foreach ($permissoes as $recursoId => $permissao) {
            if ($permissao) { // Só adiciona se a permissão não for vazia
                $this->db->bind(':usuario_id', $usuarioId);
                $this->db->bind(':recurso_id', $recursoId);
                $this->db->bind(':permissao', $permissao);
                $this->db->executa();
            }
        }
        
        return true;
    }

    /**
     * [ getUsuariosComPermissao ] - Obtém usuários que têm uma permissão específica
     * 
     * @param string $recursoNome Nome do recurso
     * @param string $permissaoMinima Permissão mínima
     * @return array Lista de usuários
     */
    public function getUsuariosComPermissao($recursoNome, $permissaoMinima = 'ler')
    {
        $sql = "
            SELECT 
                u.id,
                u.nome,
                u.email,
                u.perfil,
                p.permissao
            FROM usuarios u
            JOIN permissoes_usuario_nova p ON u.id = p.usuario_id
            JOIN recursos r ON p.recurso_id = r.id
            WHERE r.nome = :recurso_nome
            AND r.status = 'ativo'
            AND p.permissao >= :permissao_minima
            ORDER BY u.nome
        ";
        
        $this->db->query($sql);
        $this->db->bind(':recurso_nome', $recursoNome);
        $this->db->bind(':permissao_minima', $permissaoMinima);
        
        return $this->db->resultados();
    }

    /**
     * [ criarRecurso ] - Cria um novo recurso no sistema
     * 
     * @param array $dados Dados do recurso
     * @return bool Sucesso da operação
     */
    public function criarRecurso($dados)
    {
        $sql = "
            INSERT INTO recursos (nome, descricao, modulo, acao, tipo, status) 
            VALUES (:nome, :descricao, :modulo, :acao, :tipo, :status)
        ";
        
        $this->db->query($sql);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':modulo', $dados['modulo']);
        $this->db->bind(':acao', $dados['acao']);
        $this->db->bind(':tipo', $dados['tipo']);
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        
        return $this->db->executa();
    }

    /**
     * [ atualizarRecurso ] - Atualiza um recurso existente
     * 
     * @param int $id ID do recurso
     * @param array $dados Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function atualizarRecurso($id, $dados)
    {
        $sql = "
            UPDATE recursos 
            SET nome = :nome, descricao = :descricao, modulo = :modulo, 
                acao = :acao, tipo = :tipo, status = :status, atualizado_em = NOW()
            WHERE id = :id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':descricao', $dados['descricao']);
        $this->db->bind(':modulo', $dados['modulo']);
        $this->db->bind(':acao', $dados['acao']);
        $this->db->bind(':tipo', $dados['tipo']);
        $this->db->bind(':status', $dados['status']);
        
        return $this->db->executa();
    }

    /**
     * [ excluirRecurso ] - Remove um recurso do sistema
     * 
     * @param int $id ID do recurso
     * @return bool Sucesso da operação
     */
    public function excluirRecurso($id)
    {
        $sql = "DELETE FROM recursos WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }
}
?> 