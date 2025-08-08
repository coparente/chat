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
     * [ conexoes ] - Página de conexões WhatsApp
     */
    public function conexoes()
    {
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }

        // Verificar se é admin
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('configuracao', '<i class="fas fa-ban"></i> Acesso negado - Apenas administradores podem acessar configurações', 'alert alert-danger');
            Helper::redirecionar('dashboard');
            return;
        }

        $dados = [
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
     * [ mensagens ] - Configurar mensagens automáticas por departamento
     */
    public function mensagens()
    {
        // Buscar departamentos
        $departamentoModel = $this->model('DepartamentoModel');
        $departamentos = $departamentoModel->listarTodos(true); // true = apenas ativos
        
        // Buscar mensagens automáticas por departamento
        $mensagemAutomaticaModel = $this->model('MensagemAutomaticaModel');
        $mensagensPorDepartamento = [];
        
        foreach ($departamentos as $departamento) {
            $mensagensPorDepartamento[$departamento->id] = $mensagemAutomaticaModel->buscarPorDepartamento($departamento->id);
        }
        
        $dados = [
            'departamentos' => $departamentos,
            'mensagens_por_departamento' => $mensagensPorDepartamento,
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ]
        ];

        $this->view('configuracoes/mensagens', $dados);
    }

    /**
     * [ salvarMensagens ] - Salva mensagens automáticas por departamento
     */
    public function salvarMensagens()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirecionar('configuracoes/mensagens');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        $mensagemAutomaticaModel = $this->model('MensagemAutomaticaModel');

        try {
            if (isset($input['acao'])) {
                switch ($input['acao']) {
                    case 'criar':
                        // Verificar se departamento_id existe
                        $departamentoModel = $this->model('DepartamentoModel');
                        $departamento = $departamentoModel->buscarPorId($input['dados']['departamento_id']);
                        
                        if (!$departamento) {
                            echo json_encode(['success' => false, 'message' => 'Departamento não encontrado']);
                            return;
                        }
                        
                        $resultado = $mensagemAutomaticaModel->criar($input['dados']);
                        $mensagem = $resultado ? 'Mensagem automática criada com sucesso!' : 'Erro ao criar mensagem automática';
                        break;

                    case 'atualizar':
                        $resultado = $mensagemAutomaticaModel->atualizar($input['id'], $input['dados']);
                        $mensagem = $resultado ? 'Mensagem automática atualizada com sucesso!' : 'Erro ao atualizar mensagem automática';
                        break;

                    case 'excluir':
                        $resultado = $mensagemAutomaticaModel->excluir($input['id']);
                        $mensagem = $resultado ? 'Mensagem automática excluída com sucesso!' : 'Erro ao excluir mensagem automática';
                        break;

                    case 'alterar_status':
                        $resultado = $mensagemAutomaticaModel->alterarStatus($input['id'], $input['ativo']);
                        $mensagem = $resultado ? 'Status alterado com sucesso!' : 'Erro ao alterar status';
                        break;

                    default:
                        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
                        return;
                }

                echo json_encode([
                    'success' => $resultado,
                    'message' => $mensagem
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ação não especificada']);
            }
        } catch (Exception $e) {
            error_log('Erro ao salvar mensagem: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    /**
     * [ buscarMensagem ] - Busca mensagem automática por ID
     */
    public function buscarMensagem($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $mensagemAutomaticaModel = $this->model('MensagemAutomaticaModel');
        $mensagem = $mensagemAutomaticaModel->buscarPorId($id);

        if ($mensagem) {
            echo json_encode([
                'success' => true,
                'mensagem' => $mensagem
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Mensagem não encontrada'
            ]);
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

    /**
     * [ logs ] - Página de logs do sistema
     */
    public function logs()
    {
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }

        // Verificar se é admin
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('configuracao', '<i class="fas fa-ban"></i> Acesso negado - Apenas administradores podem acessar logs', 'alert alert-danger');
            Helper::redirecionar('dashboard');
            return;
        }

        // Carregar modelo de logs
        $logModel = $this->model('LogModel');

        // Filtros
        $filtros = [
            'tipo' => $_GET['tipo'] ?? 'todos',
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-7 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
            'pagina' => $_GET['pagina'] ?? 1,
            'limite' => 50
        ];

        // Buscar dados
        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ],
            'filtros' => $filtros,
            'atividades' => $logModel->getAtividades($filtros),
            'acessos' => $logModel->getLogAcessos($filtros),
            'usuarios' => $this->model('UsuarioModel')->listarUsuarios(),
            'estatisticas' => $logModel->getEstatisticasLogs($filtros)
        ];

        $this->view('configuracoes/logs', $dados);
    }

    /**
     * [ limparLogs ] - Limpar logs antigos
     */
    public function limparLogs()
    {
        // header('Content-Type: application/json');
        // Limpar qualquer output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $dias = $input['dias'] ?? 30;

        try {
            $logModel = $this->model('LogModel');
            $resultado = $logModel->limparLogsAntigos($dias);

            echo json_encode([
                'success' => true,
                'message' => "Logs antigos (mais de {$dias} dias) foram removidos com sucesso",
                'logs_removidos' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao limpar logs: ' . $e->getMessage()
            ]);
        }
    }

  
} 