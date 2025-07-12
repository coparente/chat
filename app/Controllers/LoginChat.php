<?php

/**
 * [ LOGINCHAT ] - Controlador para autenticação do sistema de chat multiatendimento
 * 
 * Este controlador gerencia:
 * - Login com status inicial do atendente
 * - Validação específica para perfis de chat (admin, supervisor, atendente)
 * - Controle de sessões ativas
 * - Verificação de limite de atendentes online
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class LoginChat extends Controllers
{
    protected $moduloModel;
    private $loginModel;
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->loginModel = $this->model('LoginModel');
        $this->usuarioModel = $this->model('UsuarioModel');

        // Inicia a sessão se não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSAO_NOME);
            session_start();
        }
    }

    /**
     * [ login ] - Exibe página de login e processa autenticação
     */
    public function login()
    {
        // Se já estiver logado, redireciona para o dashboard
        if (isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('dashboard');
            return;
        }

        // Verifica se é POST (envio do formulário)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarLogin();
            return;
        }

        // Exibe formulário de login
        $this->exibirFormularioLogin();
    }

    /**
     * [ processarLogin ] - Processa tentativa de login
     */
    private function processarLogin()
    {
        // Validação CSRF
        if (!verificarCsrf($_POST['_token'] ?? '')) {
            $dados = [
                'erro_geral' => 'Token de segurança inválido. Tente novamente.',
                'email' => '',
                'email_erro' => '',
                'senha_erro' => ''
            ];
            $this->view('login/chat_login', $dados);
            return;
        }

        // Sanitizar dados de entrada
        $dados = [
            'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'senha' => trim($_POST['senha'] ?? ''),
            'status_inicial' => in_array($_POST['status_inicial'] ?? '', ['ativo', 'ausente', 'ocupado']) 
                                ? $_POST['status_inicial'] : 'ativo',
            'email_erro' => '',
            'senha_erro' => '',
            'erro_geral' => ''
        ];

        // Validações
        if (empty($dados['email'])) {
            $dados['email_erro'] = 'Digite seu email';
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $dados['email_erro'] = 'Email inválido';
        }

        if (empty($dados['senha'])) {
            $dados['senha_erro'] = 'Digite sua senha';
        }

        // Se houver erros de validação
        if (!empty($dados['email_erro']) || !empty($dados['senha_erro'])) {
            $this->view('login/chat_login', $dados);
            return;
        }

        // Verificar bloqueio por tentativas excessivas
        if ($this->loginModel->verificarTentativasBloqueio($dados['email'])) {
            $dados['erro_geral'] = 'Muitas tentativas de login. Tente novamente em 30 minutos.';
            $this->view('login/chat_login', $dados);
            return;
        }

        // Tentar autenticar
        $usuario = $this->loginModel->checarLogin($dados['email'], $dados['senha']);

        if (!$usuario) {
            $dados['erro_geral'] = 'Email ou senha incorretos';
            $this->view('login/chat_login', $dados);
            return;
        }

        // Verificar se usuário está ativo
        if ($usuario->status === 'inativo') {
            $dados['erro_geral'] = 'Conta desativada. Entre em contato com o administrador.';
            $this->view('login/chat_login', $dados);
            return;
        }

        // Verificar se o perfil é válido para o sistema de chat
        if (!in_array($usuario->perfil, ['admin', 'supervisor', 'atendente'])) {
            $dados['erro_geral'] = 'Perfil não autorizado para o sistema de chat.';
            $this->view('login/chat_login', $dados);
            return;
        }

        // Criar sessão do usuário
        $this->criarSessaoUsuario($usuario, $dados['status_inicial']);
    }

    /**
     * [ criarSessaoUsuario ] - Cria sessão do usuário autenticado
     */
    private function criarSessaoUsuario($usuario, $statusInicial)
    {
        // Regenera ID da sessão por segurança
        session_regenerate_id(true);

        // Define variáveis de sessão
        $_SESSION['usuario_id'] = $usuario->id;
        $_SESSION['usuario_nome'] = $usuario->nome;
        $_SESSION['usuario_email'] = $usuario->email;
        $_SESSION['usuario_perfil'] = $usuario->perfil;
        $_SESSION['usuario_status'] = $statusInicial;
        $_SESSION['usuario_max_chats'] = $usuario->max_chats;
        $_SESSION['ultimo_acesso'] = time();
        $_SESSION['chats_ativos'] = [];

        // Atualizar status do usuário no banco
        $this->atualizarStatusUsuario($usuario->id, $statusInicial);

        // Buscar módulos permitidos
        if ($this->moduloModel !== null) {
            $modulos = $this->moduloModel->getModulosComSubmodulos($usuario->id);
            $_SESSION['modulos'] = $modulos;
        }

        // Atualizar último acesso
        $this->usuarioModel->atualizarUltimoAcesso($usuario->id);

        // Log de sucesso
        // Helper::registrarAtividade('Login Chat', "Usuário {$usuario->nome} entrou no sistema com status: {$statusInicial}");

        // Redirecionar baseado no perfil
        $this->redirecionarPorPerfil($usuario->perfil);
    }

    /**
     * [ atualizarStatusUsuario ] - Atualiza status do usuário no banco
     */
    private function atualizarStatusUsuario($usuarioId, $status)
    {
        try {
            $db = new Database();
            $sql = "UPDATE usuarios SET status = :status, ultimo_acesso = NOW() WHERE id = :id";
            $db->query($sql);
            $db->bind(':status', $status);
            $db->bind(':id', $usuarioId);
            $db->executa();
        } catch (Exception $e) {
            error_log("Erro ao atualizar status do usuário: " . $e->getMessage());
        }
    }

    /**
     * [ redirecionarPorPerfil ] - Redireciona usuário baseado no perfil
     */
    private function redirecionarPorPerfil($perfil)
    {
        switch ($perfil) {
            case 'admin':
                Helper::redirecionar('dashboard');
                break;
            case 'supervisor':
                Helper::redirecionar('dashboard');
                break;
            case 'atendente':
                Helper::redirecionar('chat');
                break;
            default:
                Helper::redirecionar('dashboard');
        }
    }

    /**
     * [ exibirFormularioLogin ] - Exibe formulário de login limpo
     */
    private function exibirFormularioLogin()
    {
        $dados = [
            'email' => '',
            'email_erro' => '',
            'senha_erro' => '',
            'erro_geral' => ''
        ];

        $this->view('login/chat_login', $dados);
    }

    /**
     * [ sair ] - Logout do sistema
     */
    public function sair()
    {
        // Atualizar status para inativo antes de sair
        // if (isset($_SESSION['usuario_id'])) {
        //     $this->atualizarStatusUsuario($_SESSION['usuario_id'], 'inativo');
            
        //     // Log de logout
        //     // Helper::registrarAtividade('Logout Chat', 'Usuário saiu do sistema');
        // }

        // Limpar todas as variáveis de sessão
        $_SESSION = array();

        // Destruir cookie da sessão se existir
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destruir sessão
        session_destroy();

        // Redirecionar para login
        Helper::redirecionar('login-chat');
    }

    /**
     * [ verificarSessao ] - Verifica se sessão está válida (AJAX)
     */
    public function verificarSessao()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['valid' => false, 'message' => 'Sessão expirada']);
            return;
        }

        // Verificar se usuário ainda está ativo no banco
        $usuario = $this->usuarioModel->lerUsuarioPorId($_SESSION['usuario_id']);
        
        if (!$usuario || $usuario->status === 'inativo') {
            echo json_encode(['valid' => false, 'message' => 'Usuário desativado']);
            return;
        }

        // Atualizar último acesso
        $_SESSION['ultimo_acesso'] = time();
        
        echo json_encode([
            'valid' => true,
            'usuario' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ]
        ]);
    }

    /**
     * [ alterarStatus ] - Altera status do usuário logado (AJAX)
     */
    public function alterarStatus()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sessão inválida']);
            return;
        }

        $novoStatus = $_POST['status'] ?? '';
        
        if (!in_array($novoStatus, ['ativo', 'ausente', 'ocupado'])) {
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            return;
        }

        // Atualizar status
        $this->atualizarStatusUsuario($_SESSION['usuario_id'], $novoStatus);
        $_SESSION['usuario_status'] = $novoStatus;

        // Log da alteração
        Helper::registrarAtividade('Alteração Status', "Status alterado para: {$novoStatus}");

        echo json_encode([
            'success' => true, 
            'message' => 'Status atualizado com sucesso',
            'status' => $novoStatus
        ]);
    }

    /**
     * [ recuperarSenha ] - Página de recuperação de senha
     */
    public function recuperarSenha()
    {
        // Implementar posteriormente se necessário
        $dados = [
            'titulo' => 'Recuperar Senha',
            'message' => 'Funcionalidade em desenvolvimento'
        ];
        
        $this->view('login/recuperar_senha', $dados);
    }
} 