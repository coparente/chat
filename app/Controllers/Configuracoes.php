<?php

/**
 * [ CONFIGURACOES ] - Controlador para gerenciamento de configurações do ChatSerpro
 * 
 * Este controlador permite:
 * - Configurar credenciais da API Serpro
 * - Gerenciar sessões WhatsApp
 * - Testar conectividade com a API
 * - Configurar mensagens automáticas
 * - Gerenciar configurações gerais do sistema
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class Configuracoes extends Controllers
{
    private $configuracaoModel;
    private $sessaoWhatsappModel;

    public function __construct()
    {
        parent::__construct();
        
        // Carrega os models necessários
        $this->configuracaoModel = $this->model('ConfiguracaoModel');
        $this->sessaoWhatsappModel = $this->model('SessaoWhatsappModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }

        // Verifica se é admin
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores podem acessar as configurações', 'alert alert-danger');
            Helper::redirecionar('dashboard');
            return;
        }

        // Atualiza último acesso
        $usuarioModel = $this->model('UsuarioModel');
        $usuarioModel->atualizarUltimoAcesso($_SESSION['usuario_id']);
    }

    /**
     * [ index ] - Página inicial das configurações
     */
    public function index()
    {
        $dados = [
            'pagina_titulo' => 'Configurações',
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ]
        ];

        $this->view('configuracoes/index', $dados);
    }

    /**
     * [ serpro ] - Configurações da API Serpro
     */
    public function serpro()
    {
        // Carregar configurações existentes
        $configuracoes = $this->configuracaoModel->buscarConfiguracaoSerpro();
        
        $dados = [
            'client_id' => $configuracoes->client_id ?? '',
            'client_secret' => $configuracoes->client_secret ?? '',
            'base_url' => $configuracoes->base_url ?? 'https://api.whatsapp.serpro.gov.br',
            'waba_id' => $configuracoes->waba_id ?? '',
            'phone_number_id' => $configuracoes->phone_number_id ?? '',
            'webhook_verify_token' => $configuracoes->webhook_verify_token ?? '',
            'client_id_erro' => '',
            'client_secret_erro' => '',
            'base_url_erro' => '',
            'waba_id_erro' => '',
            'phone_number_id_erro' => '',
            'webhook_verify_token_erro' => ''
        ];

        $dados['usuario_logado'] = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'perfil' => $_SESSION['usuario_perfil'],
            'status' => $_SESSION['usuario_status']
        ];

        $this->view('configuracoes/serpro', $dados);
    }

    /**
     * [ salvarSerpro ] - Salva configurações da API Serpro
     */
    public function salvarSerpro()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('configuracoes/serpro');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        if (!$formulario) {
            Helper::mensagem('configuracao', '<i class="fas fa-exclamation-triangle"></i> Dados inválidos', 'alert alert-danger');
            Helper::redirecionar('configuracoes/serpro');
            return;
        }

        // Processar formulário
        $dados = [
            'client_id' => trim($formulario['client_id']),
            'client_secret' => trim($formulario['client_secret']),
            'base_url' => trim($formulario['base_url']),
            'waba_id' => trim($formulario['waba_id']),
            'phone_number_id' => trim($formulario['phone_number_id']),
            'webhook_verify_token' => trim($formulario['webhook_verify_token']),
            'client_id_erro' => '',
            'client_secret_erro' => '',
            'base_url_erro' => '',
            'waba_id_erro' => '',
            'phone_number_id_erro' => '',
            'webhook_verify_token_erro' => ''
        ];

        // Validações
        if (empty($dados['client_id'])) {
            $dados['client_id_erro'] = 'Client ID é obrigatório';
        }

        if (empty($dados['client_secret'])) {
            $dados['client_secret_erro'] = 'Client Secret é obrigatório';
        }

        if (empty($dados['base_url'])) {
            $dados['base_url_erro'] = 'URL Base é obrigatória';
        } elseif (!filter_var($dados['base_url'], FILTER_VALIDATE_URL)) {
            $dados['base_url_erro'] = 'URL Base deve ser uma URL válida';
        }

        if (empty($dados['waba_id'])) {
            $dados['waba_id_erro'] = 'WABA ID é obrigatório';
        }

        if (empty($dados['phone_number_id'])) {
            $dados['phone_number_id_erro'] = 'Phone Number ID é obrigatório';
        }

        if (empty($dados['webhook_verify_token'])) {
            $dados['webhook_verify_token_erro'] = 'Webhook Verify Token é obrigatório';
        }

        // Se há erros, retornar para o formulário
        if (!empty($dados['client_id_erro']) || 
            !empty($dados['client_secret_erro']) || 
            !empty($dados['base_url_erro']) || 
            !empty($dados['waba_id_erro']) || 
            !empty($dados['phone_number_id_erro']) || 
            !empty($dados['webhook_verify_token_erro'])) {
            
            $dados['usuario_logado'] = [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ];

            $this->view('configuracoes/serpro', $dados);
            return;
        }

        // Se não há erros, salvar configurações
        $configuracoesSerpro = [
            'client_id' => $dados['client_id'],
            'client_secret' => $dados['client_secret'],
            'base_url' => $dados['base_url'],
            'waba_id' => $dados['waba_id'],
            'phone_number_id' => $dados['phone_number_id'],
            'webhook_verify_token' => $dados['webhook_verify_token']
        ];

        if ($this->configuracaoModel->salvarConfiguracaoSerpro($configuracoesSerpro)) {
            Helper::mensagem('configuracao', '<i class="fas fa-check"></i> Configurações da API Serpro salvas com sucesso!', 'alert alert-success');
            Helper::redirecionar('configuracoes/serpro');
        } else {
            Helper::mensagem('configuracao', '<i class="fas fa-exclamation-triangle"></i> Erro ao salvar configurações da API Serpro', 'alert alert-danger');
            Helper::redirecionar('configuracoes/serpro');
        }
    }

    /**
     * [ testarSerpro ] - Testa a conectividade com a API Serpro
     */
    public function testarSerpro()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        // Validar campos obrigatórios
        $camposObrigatorios = ['client_id', 'client_secret', 'base_url', 'waba_id', 'phone_number_id'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($input[$campo])) {
                echo json_encode(['success' => false, 'message' => "Campo {$campo} é obrigatório"]);
                return;
            }
        }

        // Testar conectividade
        $resultado = $this->configuracaoModel->testarConectividadeSerpro($input);

        if ($resultado['success']) {
            echo json_encode([
                'success' => true, 
                'message' => 'Conectividade com a API Serpro testada com sucesso!',
                'dados' => $resultado['dados']
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => $resultado['message'],
                'erro' => $resultado['erro'] ?? null
            ]);
        }
    }

    /**
     * [ conexoes ] - Gerenciar conexões WhatsApp
     */
    public function conexoes()
    {
        // Buscar todas as sessões
        $sessoes = $this->sessaoWhatsappModel->listarSessoes();

        $dados = [
            'pagina_titulo' => 'Conexões WhatsApp',
            'sessoes' => $sessoes,
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ]
        ];

        $this->view('configuracoes/conexoes', $dados);
    }

    /**
     * [ novaConexao ] - Criar nova conexão WhatsApp
     */
    public function novaConexao()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['nome']) || empty($input['numero'])) {
            echo json_encode(['success' => false, 'message' => 'Nome e número são obrigatórios']);
            return;
        }

        // Buscar configurações da API Serpro
        $configuracoes = $this->configuracaoModel->buscarConfiguracaoSerpro();
        
        if (!$configuracoes) {
            echo json_encode(['success' => false, 'message' => 'Configure primeiro a API Serpro']);
            return;
        }

        $dadosSessao = [
            'nome' => $input['nome'],
            'numero' => $input['numero'],
            'serpro_waba_id' => $configuracoes->waba_id,
            'serpro_phone_number_id' => $configuracoes->phone_number_id,
            'webhook_token' => $configuracoes->webhook_verify_token,
            'status' => 'desconectado'
        ];

        if ($this->sessaoWhatsappModel->criarSessao($dadosSessao)) {
            echo json_encode(['success' => true, 'message' => 'Conexão WhatsApp criada com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar conexão WhatsApp']);
        }
    }

    /**
     * [ conectar ] - Conectar uma sessão WhatsApp
     */
    public function conectar($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $resultado = $this->sessaoWhatsappModel->conectarSessao($id);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Conectando à sessão WhatsApp...']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao conectar sessão WhatsApp']);
        }
    }

    /**
     * [ desconectar ] - Desconectar uma sessão WhatsApp
     */
    public function desconectar($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $resultado = $this->sessaoWhatsappModel->desconectarSessao($id);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Sessão WhatsApp desconectada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao desconectar sessão WhatsApp']);
        }
    }

    /**
     * [ excluirConexao ] - Excluir uma conexão WhatsApp
     */
    public function excluirConexao($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $resultado = $this->sessaoWhatsappModel->excluirSessao($id);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Conexão WhatsApp excluída']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir conexão WhatsApp']);
        }
    }

    /**
     * [ mensagens ] - Configurar mensagens automáticas
     */
    public function mensagens()
    {
        // Carregar configurações existentes
        $mensagens = $this->configuracaoModel->buscarMensagensAutomaticas();
        
        $dados = [
            'mensagem_boas_vindas' => $mensagens->mensagem_boas_vindas ?? 'Olá! Seja bem-vindo(a) ao nosso atendimento. Em que posso ajudá-lo(a)?',
            'mensagem_ausencia' => $mensagens->mensagem_ausencia ?? 'No momento não há atendentes disponíveis. Deixe sua mensagem que retornaremos em breve.',
            'mensagem_encerramento' => $mensagens->mensagem_encerramento ?? 'Obrigado pelo contato! Se precisar de mais alguma coisa, estarei aqui para ajudar.',
            'horario_funcionamento' => $mensagens->horario_funcionamento ?? 'Segunda a Sexta: 08:00 às 18:00',
            'ativar_boas_vindas' => $mensagens->ativar_boas_vindas ?? true,
            'ativar_ausencia' => $mensagens->ativar_ausencia ?? true,
            'ativar_encerramento' => $mensagens->ativar_encerramento ?? true,
            'ativar_fora_horario' => $mensagens->ativar_fora_horario ?? true,
            'ativar_sem_atendentes' => $mensagens->ativar_sem_atendentes ?? true
        ];

        $dados['usuario_logado'] = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'perfil' => $_SESSION['usuario_perfil'],
            'status' => $_SESSION['usuario_status']
        ];

        $this->view('configuracoes/mensagens', $dados);
    }

    /**
     * [ salvarMensagens ] - Salva mensagens automáticas
     */
    public function salvarMensagens()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('configuracoes/mensagens');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        if (!$formulario) {
            Helper::mensagem('configuracao', '<i class="fas fa-exclamation-triangle"></i> Dados inválidos', 'alert alert-danger');
            Helper::redirecionar('configuracoes/mensagens');
            return;
        }

        $dados = [
            'mensagem_boas_vindas' => trim($formulario['mensagem_boas_vindas']),
            'mensagem_ausencia' => trim($formulario['mensagem_ausencia']),
            'mensagem_encerramento' => trim($formulario['mensagem_encerramento']),
            'horario_funcionamento' => trim($formulario['horario_funcionamento']),
            'ativar_boas_vindas' => isset($formulario['ativar_boas_vindas']),
            'ativar_ausencia' => isset($formulario['ativar_ausencia']),
            'ativar_encerramento' => isset($formulario['ativar_encerramento']),
            'ativar_fora_horario' => isset($formulario['ativar_fora_horario']),
            'ativar_sem_atendentes' => isset($formulario['ativar_sem_atendentes'])
        ];

        if ($this->configuracaoModel->salvarMensagensAutomaticas($dados)) {
            Helper::mensagem('configuracao', '<i class="fas fa-check"></i> Mensagens automáticas salvas com sucesso!', 'alert alert-success');
            Helper::redirecionar('configuracoes/mensagens');
        } else {
            Helper::mensagem('configuracao', '<i class="fas fa-exclamation-triangle"></i> Erro ao salvar mensagens automáticas', 'alert alert-danger');
            Helper::redirecionar('configuracoes/mensagens');
        }
    }

    /**
     * [ statusToken ] - Verifica status do token JWT
     */
    public function statusToken()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $statusToken = $this->configuracaoModel->getStatusToken();

        echo json_encode([
            'success' => true,
            'dados' => $statusToken
        ]);
    }

    /**
     * [ renovarToken ] - Renova token JWT manualmente
     */
    public function renovarToken()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $resultado = $this->configuracaoModel->renovarToken();

        echo json_encode($resultado);
    }

    /**
     * [ limparTokenCache ] - Limpa cache do token JWT
     */
    public function limparTokenCache()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        if ($this->configuracaoModel->limparTokenCache()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cache do token limpo com sucesso'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao limpar cache do token'
            ]);
        }
    }

  
} 