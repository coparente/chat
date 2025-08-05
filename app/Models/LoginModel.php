<?php

/**
 * [ LOGINMODEL ] - Model responsável por gerenciar o login e autenticação dos usuários no sistema.
 * 
 * Esta classe lida com a autenticação de usuários, registro de tentativas de login e geração de tokens de recuperação.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025-2025 TJGO
 * @version 1.0.1
 * @access protected
 */
    
class LoginModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Verifica as credenciais do usuário
     * @param string $email Email do usuário
     * @param string $senha Senha do usuário
     * @return object|false Retorna o objeto usuário se autenticado, false caso contrário
     */
    public function checarLogin($email, $senha)
    {
        $this->db->query("SELECT * FROM usuarios WHERE email = :email ");
        $this->db->bind(":email", $email);
        // $this->db->bind(":status", STATUS_ATIVO);
        // AND status = :status
        $usuario = $this->db->resultado();

        if ($usuario && password_verify($senha, $usuario->senha)) {
            $this->registrarTentativaLogin($usuario->id, true);
            return $usuario;
        }

        $this->registrarTentativaLogin(null, false, $email);
        return false;
    }

    /**
     * Registra tentativas de login para fins de segurança
     * @param int|null $usuario_id ID do usuário (se autenticado)
     * @param bool $sucesso Se a tentativa foi bem sucedida
     * @param string|null $email Email usado na tentativa
     */
    private function registrarTentativaLogin($usuario_id = null, $sucesso = false, $email = null)
    {
        $this->db->query("INSERT INTO log_acessos (usuario_id, email, ip, sucesso, data_hora) 
                         VALUES (:usuario_id, :email, :ip, :sucesso, CURRENT_TIMESTAMP)");
        
        $this->db->bind(":usuario_id", $usuario_id);
        $this->db->bind(":email", $email);
        $this->db->bind(":ip", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $this->db->bind(":sucesso", $sucesso ? 1 : 0);
        
        $this->db->executa();
    }

    /**
     * Busca usuário por ID
     * @param int $id ID do usuário
     * @return object|false
     */
    public function lerUsuarioPorId($id)
    {
        $this->db->query("SELECT * FROM usuarios WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->resultado();
    }

    /**
     * Busca usuários administradores
     * @return array
     */
    public function lerAdmins()
    {
        $this->db->query("SELECT * FROM usuarios WHERE perfil = :perfil AND status = :status");
        $this->db->bind(":perfil", PERFIL_ADMIN);
        $this->db->bind(":status", STATUS_ATIVO);
        return $this->db->resultados();
    }

    /**
     * Verifica tentativas de login falhas recentes
     * @param string $email Email do usuário
     * @param int $minutos Período de tempo em minutos
     * @param int $maxTentativas Número máximo de tentativas permitidas
     * @return bool
     */
    public function verificarTentativasBloqueio($email, $minutos = 30, $maxTentativas = 6)
    {
        $this->db->query("SELECT COUNT(*) as total FROM log_acessos 
                         WHERE email = :email 
                         AND sucesso = 0 
                         AND data_hora > DATE_SUB(NOW(), INTERVAL :minutos MINUTE)");
        
        $this->db->bind(":email", $email);
        $this->db->bind(":minutos", $minutos);
        
        $resultado = $this->db->resultado();
        return $resultado->total >= $maxTentativas;
    }

    /**
     * Gera token para recuperação de senha
     * @param string $email Email do usuário
     * @return string|false
     */
    public function gerarTokenRecuperacao($email)
    {
        $token = bin2hex(random_bytes(32));
        $expiracao = date('Y-m-d H:i:s', time() + TOKEN_EXPIRACAO);

        $this->db->query("UPDATE usuarios SET 
                         token_recuperacao = :token,
                         token_expiracao = :expiracao 
                         WHERE email = :email AND status = :status");
        
        $this->db->bind(":token", $token);
        $this->db->bind(":expiracao", $expiracao);
        $this->db->bind(":email", $email);
        $this->db->bind(":status", STATUS_ATIVO);

        if ($this->db->executa()) {
            return $token;
        }

        return false;
    }

    /**
     * Verifica se um token de recuperação é válido
     * @param string $token Token de recuperação
     * @return object|false
     */
    public function verificarTokenRecuperacao($token)
    {
        $this->db->query("SELECT * FROM usuarios 
                         WHERE token_recuperacao = :token 
                         AND token_expiracao > NOW() 
                         AND status = :status");
        
        $this->db->bind(":token", $token);
        $this->db->bind(":status", STATUS_ATIVO);

        return $this->db->resultado();
    }

    /**
     * Limpa o token de recuperação após uso
     * @param int $id ID do usuário
     */
    public function limparTokenRecuperacao($id)
    {
        $this->db->query("UPDATE usuarios SET 
                         token_recuperacao = NULL,
                         token_expiracao = NULL 
                         WHERE id = :id");
        
        $this->db->bind(":id", $id);
        $this->db->executa();
    }
}
