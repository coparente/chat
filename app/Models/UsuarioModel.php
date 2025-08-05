<?php

/**
 * [ USUARIOMODEL ] - Model para gerenciamento de usuários
 * 
 * Esta classe gerencia todas as operações relacionadas aos usuários:
 * - CRUD de usuários
 * - Estatísticas de usuários
 * - Controle de status e permissões
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class UsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ getEstatisticasUsuarios ] - Estatísticas dos usuários do sistema
     * 
     * @return object Estatísticas dos usuários
     */
    public function getEstatisticasUsuarios()
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN status = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                SUM(CASE WHEN status = 'ocupado' THEN 1 ELSE 0 END) as ocupados,
                SUM(CASE WHEN status = 'inativo' THEN 1 ELSE 0 END) as inativos
            FROM usuarios 
            WHERE perfil IN ('admin', 'supervisor', 'atendente')
        ";
        
        $this->db->query($sql);
        return $this->db->resultado();
    }

    /**
     * [ getAtendentesOnline ] - Busca atendentes online (baseado no último acesso)
     * 
     * @param int $limite Limite de resultados
     * @return array Lista de atendentes online
     */
    public function getAtendentesOnline($limite = 5)
    {
        $sql = "
            SELECT 
                id,
                nome, 
                email,
                status, 
                ultimo_acesso,
                perfil
            FROM usuarios 
            WHERE perfil = 'atendente' 
            AND status = 'ativo' -- Apenas usuários ativos no sistema
            AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) -- Online nos últimos 15 minutos
            ORDER BY ultimo_acesso DESC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }

    /**
     * [ contarAtendentesOnline ] - Conta atendentes online
     * 
     * @return int Número de atendentes online
     */
    public function contarAtendentesOnline()
    {
        $sql = "
            SELECT COUNT(*) as total 
            FROM usuarios 
            WHERE perfil = 'atendente' 
            AND status = 'ativo' 
            AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ";
        $this->db->query($sql);
        return $this->db->resultado()->total;
    }

    /**
     * [ getAtendentesPorDepartamento ] - Busca atendentes de um departamento específico
     * 
     * @param int $departamentoId ID do departamento
     * @param bool $apenasAtivos Se true, retorna apenas atendentes ativos
     * @return array Lista de atendentes
     */
    public function getAtendentesPorDepartamento($departamentoId, $apenasAtivos = true)
    {
        $sql = "
            SELECT 
                u.id,
                u.nome,
                u.email,
                u.perfil,
                u.status,
                u.ultimo_acesso,
                ad.perfil as perfil_departamento,
                ad.max_conversas,
                ad.horario_inicio,
                ad.horario_fim,
                ad.dias_semana
            FROM usuarios u
            JOIN atendentes_departamento ad ON u.id = ad.usuario_id
            WHERE ad.departamento_id = :departamento_id
        ";
        
        if ($apenasAtivos) {
            $sql .= " AND ad.status = 'ativo' AND u.status = 'ativo'";
        }
        
        $sql .= " ORDER BY ad.perfil DESC, u.nome ASC";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        return $this->db->resultados();
    }

    /**
     * [ verificarAtendenteDepartamento ] - Verifica se um atendente pertence a um departamento
     * 
     * @param int $usuarioId ID do usuário
     * @param int $departamentoId ID do departamento
     * @return bool True se pertence ao departamento
     */
    public function verificarAtendenteDepartamento($usuarioId, $departamentoId)

    
    {

        $sql = "
            SELECT COUNT(*) as total
            FROM atendentes_departamento 
            WHERE usuario_id = :usuario_id 
            AND departamento_id = :departamento_id 
            AND status = 'ativo'
        ";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        $this->db->bind(':departamento_id', $departamentoId);
        $resultado = $this->db->resultado();
        
        return $resultado && $resultado->total > 0;
    }

    /**
     * [ getDepartamentosUsuario ] - Obtém departamentos de um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array Lista de departamentos
     */
    public function getDepartamentosUsuario($usuarioId)
    {
        $sql = "
            SELECT 
                d.id,
                d.nome,
                d.descricao,
                d.cor,
                d.icone,
                d.status,
                ad.perfil as perfil_departamento,
                ad.max_conversas,
                ad.horario_inicio,
                ad.horario_fim
            FROM departamentos d
            JOIN atendentes_departamento ad ON d.id = ad.departamento_id
            WHERE ad.usuario_id = :usuario_id 
            AND ad.status = 'ativo'
            AND d.status = 'ativo'
            ORDER BY d.prioridade ASC, d.nome ASC
        ";
        
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $usuarioId);
        return $this->db->resultados();
    }

    /**
     * [ getAtendentesDisponiveisPorDepartamento ] - Busca atendentes disponíveis de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return array Lista de atendentes disponíveis
     */
    public function getAtendentesDisponiveisPorDepartamento($departamentoId)
    {
        $sql = "
            SELECT 
                u.id,
                u.nome,
                u.email,
                u.status,
                ad.max_conversas,
                ad.horario_inicio,
                ad.horario_fim,
                (SELECT COUNT(*) FROM conversas WHERE atendente_id = u.id AND status IN ('aberto', 'pendente')) as conversas_ativas
            FROM usuarios u
            JOIN atendentes_departamento ad ON u.id = ad.usuario_id
            WHERE ad.departamento_id = :departamento_id
            AND ad.status = 'ativo'
            AND u.status = 'ativo'
            AND u.ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            HAVING conversas_ativas < ad.max_conversas
            ORDER BY conversas_ativas ASC, u.nome ASC
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        return $this->db->resultados();
    }

    /**
     * [ contarAtendentesPorDepartamento ] - Conta atendentes de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param bool $apenasAtivos Se true, conta apenas atendentes ativos
     * @return int Número de atendentes
     */
    public function contarAtendentesPorDepartamento($departamentoId, $apenasAtivos = true)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM atendentes_departamento ad
            JOIN usuarios u ON ad.usuario_id = u.id
            WHERE ad.departamento_id = :departamento_id
        ";
        
        if ($apenasAtivos) {
            $sql .= " AND ad.status = 'ativo' AND u.status = 'ativo'";
        }
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $resultado = $this->db->resultado();
        
        return $resultado ? $resultado->total : 0;
    }

    /**
     * [ getAtendentesOnlinePorDepartamento ] - Busca atendentes online de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param int $limite Limite de resultados
     * @return array Lista de atendentes online
     */
    public function getAtendentesOnlinePorDepartamento($departamentoId, $limite = 5)
    {
        $sql = "
            SELECT 
                u.nome, 
                u.status, 
                u.ultimo_acesso,
                ad.max_conversas,
                (SELECT COUNT(*) FROM conversas WHERE atendente_id = u.id AND status IN ('aberto', 'pendente')) as conversas_ativas
            FROM usuarios u
            JOIN atendentes_departamento ad ON u.id = ad.usuario_id
            WHERE ad.departamento_id = :departamento_id
            AND ad.status = 'ativo'
            AND u.status IN ('ativo', 'ausente', 'ocupado')
            AND u.ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ORDER BY conversas_ativas ASC, u.ultimo_acesso DESC
            LIMIT :limite
        ";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':limite', $limite);
        return $this->db->resultados();
    }

    /**
     * [ buscarAtendentesAtivos ] - Busca atendentes ativos para mensagens automáticas
     * 
     * @return array Lista de atendentes ativos
     */
    public function buscarAtendentesAtivos()
    {
        $sql = "
            SELECT id, nome, email, status, ultimo_acesso
            FROM usuarios 
            WHERE perfil IN ('atendente', 'supervisor', 'admin')
            AND status IN ('ativo', 'ausente', 'ocupado')
            AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ORDER BY status ASC, ultimo_acesso DESC
        ";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ lerUsuarioPorId ] - Busca usuário por ID
     * 
     * @param int $id ID do usuário
     * @return object|null Dados do usuário
     */
    public function lerUsuarioPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * [ lerUsuarioPorEmail ] - Busca usuário por email
     * 
     * @param string $email Email do usuário
     * @return object|null Dados do usuário
     */
    public function lerUsuarioPorEmail($email)
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $this->db->query($sql);
        $this->db->bind(':email', $email);
        return $this->db->resultado();
    }

    /**
     * [ atualizarUltimoAcesso ] - Atualiza último acesso do usuário
     * 
     * @param int $id ID do usuário
     * @return bool Sucesso da operação
     */
    public function atualizarUltimoAcesso($id)
    {
        $sql = "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ verificarUsuarioOnline ] - Verifica se um usuário está online
     * 
     * @param int $id ID do usuário
     * @param int $minutosLimite Minutos para considerar online (padrão: 15)
     * @return bool True se está online
     */
    public function verificarUsuarioOnline($id, $minutosLimite = 15)
    {
        $sql = "
            SELECT COUNT(*) as total 
            FROM usuarios 
            WHERE id = :id 
            AND status = 'ativo'
            AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)
        ";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':minutos', $minutosLimite);
        $resultado = $this->db->resultado();
        
        return $resultado && $resultado->total > 0;
    }

    /**
     * [ atualizarStatus ] - Atualiza status do usuário
     * 
     * @param int $id ID do usuário
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function atualizarStatus($id, $status)
    {
        $statusValidos = ['ativo', 'inativo', 'ausente', 'ocupado'];
        
        if (!in_array($status, $statusValidos)) {
            return false;
        }
        
        $sql = "UPDATE usuarios SET status = :status, atualizado_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ listarUsuarios ] - Lista todos os usuários
     * 
     * @param string $perfil Filtrar por perfil (opcional)
     * @return array Lista de usuários
     */
    public function listarUsuarios($perfil = null)
    {
        $sql = "SELECT id, nome, email, perfil, status, max_chats, ultimo_acesso, criado_em FROM usuarios";
        
        if ($perfil) {
            $sql .= " WHERE perfil = :perfil";
        }
        
        $sql .= " ORDER BY nome ASC";
        
        $this->db->query($sql);
        
        if ($perfil) {
            $this->db->bind(':perfil', $perfil);
        }
        
        return $this->db->resultados();
    }

    /**
     * [ listarPorPerfil ] - Lista usuários por perfil específico
     * 
     * @param string $perfil Perfil a buscar
     * @return array Lista de usuários
     */
    public function listarPorPerfil($perfil)
    {
        $sql = "
            SELECT id, nome, email, perfil, status, max_chats, ultimo_acesso, criado_em 
            FROM usuarios 
            WHERE perfil = :perfil 
            ORDER BY nome ASC
        ";
        
        $this->db->query($sql);
        $this->db->bind(':perfil', $perfil);
        
        return $this->db->resultados();
    }

    /**
     * [ criarUsuario ] - Cria um novo usuário
     * 
     * @param array $dados Dados do usuário
     * @return bool Sucesso da operação
     */
    public function criarUsuario($dados)
    {
        $sql = "
            INSERT INTO usuarios (nome, email, senha, perfil, status, max_chats, criado_em) 
            VALUES (:nome, :email, :senha, :perfil, :status, :max_chats, NOW())
        ";
        
        $this->db->query($sql);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':email', $dados['email']);
        $this->db->bind(':senha', password_hash($dados['senha'], PASSWORD_DEFAULT));
        $this->db->bind(':perfil', $dados['perfil'] ?? 'atendente');
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        $this->db->bind(':max_chats', $dados['max_chats'] ?? 5);
        
        return $this->db->executa();
    }

    /**
     * [ atualizarUsuario ] - Atualiza um usuário
     * 
     * @param int $id ID do usuário
     * @param array $dados Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function atualizarUsuario($id, $dados)
    {
        $campos = [];
        $valores = [];
        
        $camposPermitidos = ['nome', 'email', 'perfil', 'status', 'max_chats', 'avatar'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($dados[$campo])) {
                $campos[] = "$campo = :$campo";
                $valores[$campo] = $dados[$campo];
            }
        }
        
        // Se houver nova senha
        if (!empty($dados['senha'])) {
            $campos[] = "senha = :senha";
            $valores['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . ", atualizado_em = NOW() WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        foreach ($valores as $campo => $valor) {
            $this->db->bind(":$campo", $valor);
        }
        
        return $this->db->executa();
    }

    /**
     * [ excluirUsuario ] - Remove um usuário
     * 
     * @param int $id ID do usuário
     * @return bool Sucesso da operação
     */
    public function excluirUsuario($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ verificarEmail ] - Verifica se email já existe
     * 
     * @param string $email Email a verificar
     * @param int $excluirId ID para excluir da verificação (para edição)
     * @return bool True se email já existe
     */
    public function verificarEmail($email, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = :email";
        
        if ($excluirId) {
            $sql .= " AND id != :excluir_id";
        }
        
        $this->db->query($sql);
        $this->db->bind(':email', $email);
        
        if ($excluirId) {
            $this->db->bind(':excluir_id', $excluirId);
        }
        
        return $this->db->resultado()->total > 0;
    }

    /**
     * [ atualizarConfiguracoes ] - Atualiza configurações do usuário
     * 
     * @param int $id ID do usuário
     * @param array $configuracoes Configurações em formato array
     * @return bool Sucesso da operação
     */
    public function atualizarConfiguracoes($id, $configuracoes)
    {
        $sql = "UPDATE usuarios SET configuracoes = :configuracoes, atualizado_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':configuracoes', json_encode($configuracoes));
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ buscarPorTokenRecuperacao ] - Busca usuário por token de recuperação
     * 
     * @param string $token Token de recuperação
     * @return object|null Dados do usuário
     */
    public function buscarPorTokenRecuperacao($token)
    {
        $sql = "
            SELECT * FROM usuarios 
            WHERE token_recuperacao = :token 
            AND token_expiracao > NOW()
        ";
        
        $this->db->query($sql);
        $this->db->bind(':token', $token);
        return $this->db->resultado();
    }

    /**
     * [ definirTokenRecuperacao ] - Define token de recuperação de senha
     * 
     * @param int $id ID do usuário
     * @param string $token Token gerado
     * @param int $expiraEm Tempo de expiração em horas
     * @return bool Sucesso da operação
     */
    public function definirTokenRecuperacao($id, $token, $expiraEm = 24)
    {
        $sql = "
            UPDATE usuarios 
            SET token_recuperacao = :token, 
                token_expiracao = DATE_ADD(NOW(), INTERVAL :expira_em HOUR),
                atualizado_em = NOW()
            WHERE id = :id
        ";
        
        $this->db->query($sql);
        $this->db->bind(':token', $token);
        $this->db->bind(':expira_em', $expiraEm);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }
}
