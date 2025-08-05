<?php

/**
 * [ DEPARTAMENTOS ] - Controlador para gerenciar departamentos
 * 
 * Este controlador gerencia:
 * - CRUD de departamentos
 * - Associação de atendentes
 * - Configurações de credenciais Serpro
 * - Estatísticas por departamento
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class Departamentos extends Controllers
{
    private $departamentoModel;
    private $credencialSerproModel;
    private $usuarioModel;
    private $serproApiDepartamento;

    public function __construct()
    {
        parent::__construct();
        
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login');
            return;
        }
        
        // Verificar permissão (apenas admin)
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('acesso_negado', '<i class="fas fa-exclamation-triangle"></i> Acesso negado. Apenas administradores podem gerenciar departamentos.', 'alert alert-danger');
            Helper::redirecionar('dashboard');
            return;
        }
        
        // Inicializar models
        try {
            $this->departamentoModel = $this->model('DepartamentoModel');
            $this->credencialSerproModel = $this->model('CredencialSerproModel');
            $this->usuarioModel = $this->model('UsuarioModel');
            // $this->serproApiDepartamento = new SerproApiDepartamento();
            
            error_log("Models inicializados com sucesso");
        } catch (Exception $e) {
            error_log("Erro ao inicializar models: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * [ index ] - Lista todos os departamentos
     */
    public function index()
    {
        // $departamentos = $this->departamentoModel->listarTodos(false);
        $departamentos = $this->departamentoModel->listarTodosComContagens(false);
        $estatisticas = $this->getEstatisticasGerais();

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'departamentos' => $departamentos,
            'estatisticas' => $estatisticas
        ];

        $this->view('departamentos/index', $dados);
    }

    /**
     * [ criar ] - Formulário para criar departamento
     */
    public function criar()
    {
        $usuarios = $this->usuarioModel->listarUsuarios();

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'usuarios' => $usuarios
        ];

        $this->view('departamentos/criar', $dados);
    }

    /**
     * [ salvar ] - Salva novo departamento
     */
    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Método não permitido']);
                exit;
            }
            Helper::redirecionar('departamentos');
            return;
        }

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'cor' => $_POST['cor'] ?? '#007bff',
            'icone' => $_POST['icone'] ?? 'fas fa-building',
            'prioridade' => intval($_POST['prioridade'] ?? 0),
            'status' => $_POST['status'] ?? 'ativo',
            'configuracoes' => [
                'horario_atendimento' => $_POST['horario_atendimento'] ?? '08:00-18:00',
                'dias_semana' => $_POST['dias_semana'] ?? [1,2,3,4,5]
            ]
        ];

        // Validar dados
        if (empty($dados['nome'])) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nome do departamento é obrigatório']);
                exit;
            }
            Helper::mensagem('erro', 'Nome do departamento é obrigatório', 'alert alert-danger');
            Helper::redirecionar('departamentos/criar');
            return;
        }

        // Verificar se nome já existe
        $departamentoExistente = $this->departamentoModel->buscarPorNome($dados['nome']);
        if ($departamentoExistente) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Já existe um departamento com este nome']);
                exit;
            }
            Helper::mensagem('erro', 'Já existe um departamento com este nome', 'alert alert-danger');
            Helper::redirecionar('departamentos/criar');
            return;
        }

        // Criar departamento
        $resultado = $this->departamentoModel->criar($dados);
        
        if ($resultado) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Departamento criado com sucesso!']);
                exit;
            }
            Helper::mensagem('sucesso', 'Departamento criado com sucesso!', 'alert alert-success');
            Helper::redirecionar('departamentos');
        } else {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro ao criar departamento']);
                exit;
            }
            Helper::mensagem('erro', 'Erro ao criar departamento', 'alert alert-danger');
            Helper::redirecionar('departamentos/criar');
        }
    }

    /**
     * [ editar ] - Formulário para editar departamento
     */
    public function editar($id)
    {
        $departamento = $this->departamentoModel->buscarPorId($id);
        if (!$departamento) {
            Helper::mensagem('erro', 'Departamento não encontrado', 'alert alert-danger');
            Helper::redirecionar('departamentos');
            return;
        }

        $usuarios = $this->usuarioModel->listarUsuarios();
        $atendentes = $this->departamentoModel->getAtendentes($id, false);

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'departamento' => $departamento,
            'usuarios' => $usuarios,
            'atendentes' => $atendentes
        ];

        $this->view('departamentos/editar', $dados);
    }

    /**
     * [ atualizar ] - Atualiza departamento
     */
    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Método não permitido']);
                exit;
            }
            Helper::redirecionar('departamentos');
            return;
        }

        $departamento = $this->departamentoModel->buscarPorId($id);
        if (!$departamento) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Departamento não encontrado']);
                exit;
            }
            Helper::mensagem('erro', 'Departamento não encontrado', 'alert alert-danger');
            Helper::redirecionar('departamentos');
            return;
        }

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'cor' => $_POST['cor'] ?? '#007bff',
            'icone' => $_POST['icone'] ?? 'fas fa-building',
            'prioridade' => intval($_POST['prioridade'] ?? 0),
            'status' => $_POST['status'] ?? 'ativo',
            'configuracoes' => [
                'horario_atendimento' => $_POST['horario_atendimento'] ?? '08:00-18:00',
                'dias_semana' => $_POST['dias_semana'] ?? [1,2,3,4,5]
            ]
        ];

        // Validar dados
        if (empty($dados['nome'])) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nome do departamento é obrigatório']);
                exit;
            }
            Helper::mensagem('erro', 'Nome do departamento é obrigatório', 'alert alert-danger');
            Helper::redirecionar("departamentos/editar/{$id}");
            return;
        }

        // Verificar se nome já existe (exceto o próprio)
        $departamentoExistente = $this->departamentoModel->buscarPorNome($dados['nome']);
        if ($departamentoExistente && $departamentoExistente->id != $id) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Já existe um departamento com este nome']);
                exit;
            }
            Helper::mensagem('erro', 'Já existe um departamento com este nome', 'alert alert-danger');
            Helper::redirecionar("departamentos/editar/{$id}");
            return;
        }

        // Atualizar departamento
        $resultado = $this->departamentoModel->atualizar($id, $dados);
        
        if ($resultado) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Departamento atualizado com sucesso!']);
                exit;
            }
            Helper::mensagem('sucesso', 'Departamento atualizado com sucesso!', 'alert alert-success');
            Helper::redirecionar('departamentos');
        } else {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar departamento']);
                exit;
            }
            Helper::mensagem('erro', 'Erro ao atualizar departamento', 'alert alert-danger');
            Helper::redirecionar("departamentos/editar/{$id}");
        }
    }

    /**
     * [ credenciais ] - Gerencia credenciais Serpro do departamento
     */
    public function credenciais($departamentoId)
    {
        $departamento = $this->departamentoModel->buscarPorId($departamentoId);
        if (!$departamento) {
            Helper::mensagem('erro', 'Departamento não encontrado', 'alert alert-danger');
            Helper::redirecionar('departamentos');
            return;
        }

        $credenciais = $this->credencialSerproModel->listarPorDepartamento($departamentoId, false);

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'departamento' => $departamento,
            'credenciais' => $credenciais
        ];

        $this->view('departamentos/credenciais', $dados);
    }

    /**
     * [ salvarCredencial ] - Salva credencial Serpro
     */
    public function salvarCredencial()
    {
        // Log de debug
        error_log("=== DEBUG: salvarCredencial iniciado ===");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Erro: Método não permitido");
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Método não permitido']);
                exit;
            }
            Helper::redirecionar('departamentos');
            return;
        }

        try {
            $dados = [
                'departamento_id' => intval($_POST['departamento_id']),
                'nome' => $_POST['nome'] ?? '',
                'client_id' => $_POST['client_id'] ?? '',
                'client_secret' => $_POST['client_secret'] ?? '',
                'base_url' => $_POST['base_url'] ?? 'https://api.whatsapp.serpro.gov.br',
                'waba_id' => $_POST['waba_id'] ?? '',
                'phone_number_id' => $_POST['phone_number_id'] ?? '',
                'webhook_verify_token' => $_POST['webhook_verify_token'] ?? '',
                'status' => $_POST['status'] ?? 'ativo',
                'prioridade' => intval($_POST['prioridade'] ?? 0),
                'configuracoes' => $this->processarConfiguracoes($_POST['configuracoes'] ?? '')
            ];

            error_log("Dados processados: " . print_r($dados, true));

            // Validar dados obrigatórios
            $camposObrigatorios = ['nome', 'client_id', 'client_secret', 'waba_id', 'phone_number_id'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    error_log("Erro: Campo obrigatório '{$campo}' está vazio");
                    if ($this->isAjaxRequest()) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => "Campo {$campo} é obrigatório"]);
                        exit;
                    }
                    Helper::mensagem('erro', "Campo {$campo} é obrigatório", 'alert alert-danger');
                    Helper::redirecionar("departamentos/credenciais/{$dados['departamento_id']}");
                    return;
                }
            }

            error_log("Validação passou, verificando se é edição ou criação");

            // Verificar se é uma edição ou criação
            $credencialId = $_POST['credencial_id'] ?? null;
            
            if ($credencialId) {
                error_log("Editando credencial ID: {$credencialId}");
                // Edição
                $resultado = $this->credencialSerproModel->atualizar($credencialId, $dados);
                $mensagem = 'Credencial atualizada com sucesso!';
            } else {
                error_log("Criando nova credencial");
                // Criação
                $resultado = $this->credencialSerproModel->criar($dados);
                $mensagem = 'Credencial criada com sucesso!';
            }
            
            error_log("Resultado da operação: " . ($resultado ? 'true' : 'false'));
            
            if ($resultado) {
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => $mensagem,
                        'credencial_id' => $credencialId ?: $resultado
                    ]);
                    exit;
                }
                Helper::mensagem('sucesso', $mensagem, 'alert alert-success');
            } else {
                error_log("Erro ao salvar credencial");
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Erro ao salvar credencial']);
                    exit;
                }
                Helper::mensagem('erro', 'Erro ao salvar credencial', 'alert alert-danger');
            }
            
            Helper::redirecionar("departamentos/credenciais/{$dados['departamento_id']}");
            
        } catch (Exception $e) {
            error_log("EXCEÇÃO em salvarCredencial: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
                exit;
            }
            Helper::mensagem('erro', 'Erro interno: ' . $e->getMessage(), 'alert alert-danger');
            Helper::redirecionar('departamentos');
        }
    }

    /**
     * [ testarCredencial ] - Testa conectividade da credencial
     */
    public function testarCredencial($credencialId)
    {
        header('Content-Type: application/json');

        $resultado = $this->credencialSerproModel->testarConectividade($credencialId);
        
        echo json_encode($resultado);
        exit;
    }

    /**
     * [ renovarToken ] - Renova token da credencial
     */
    public function renovarToken($credencialId)
    {
        header('Content-Type: application/json');

        $resultado = $this->credencialSerproModel->renovarToken($credencialId);
        
        echo json_encode($resultado);
        exit;
    }

    /**
     * [ estatisticas ] - Exibe estatísticas do departamento
     */
    public function estatisticas($departamentoId)
    {
        $departamento = $this->departamentoModel->buscarPorId($departamentoId);
        if (!$departamento) {
            Helper::mensagem('erro', 'Departamento não encontrado', 'alert alert-danger');
            Helper::redirecionar('departamentos');
            return;
        }

        // Obter estatísticas completas do modelo
        $estatisticasCompletas = $this->departamentoModel->getEstatisticasCompletas($departamentoId);
        
        // Obter atendentes do departamento
        $atendentes = $this->departamentoModel->getAtendentes($departamentoId, true);
        
        // Obter conversas recentes do departamento
        $conversas = $this->departamentoModel->getConversas($departamentoId, ['limit' => 10]);
        
        // Obter credenciais do departamento
        $credenciais = $this->credencialSerproModel->listarPorDepartamento($departamentoId, true);

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'departamento' => $departamento,
            'estatisticas' => $estatisticasCompletas->basicas,
            'atendentes' => $atendentes,
            'conversas' => $conversas,
            'status_credenciais' => $credenciais,
            'estatisticas_adicionais' => $estatisticasCompletas->adicionais,
            'taxa_atendimento' => $estatisticasCompletas->taxa_atendimento
        ];

        $this->view('departamentos/estatisticas', $dados);
    }

    /**
     * [ getEstatisticasGerais ] - Obtém estatísticas gerais
     */
    private function getEstatisticasGerais()
    {
        $departamentos = $this->departamentoModel->listarTodos(false);
        $estatisticasCredenciais = $this->credencialSerproModel->getEstatisticas();

        $totalDepartamentos = count($departamentos);
        $departamentosAtivos = 0;
        $totalAtendentes = 0;

        foreach ($departamentos as $departamento) {
            if ($departamento->status === 'ativo') {
                $departamentosAtivos++;
            }
            
            $atendentes = $this->departamentoModel->getAtendentes($departamento->id, true);
            $totalAtendentes += count($atendentes);
        }

        // Converter objeto de estatísticas em array
        $credenciaisArray = [
            'total' => $estatisticasCredenciais->total_credenciais ?? 0,
            'ativas' => $estatisticasCredenciais->credenciais_ativas ?? 0,
            'inativas' => $estatisticasCredenciais->credenciais_inativas ?? 0,
            'teste' => $estatisticasCredenciais->credenciais_teste ?? 0,
            'departamentos_com_credenciais' => $estatisticasCredenciais->departamentos_com_credenciais ?? 0
        ];

        return [
            'total_departamentos' => $totalDepartamentos,
            'departamentos_ativos' => $departamentosAtivos,
            'total_atendentes' => $totalAtendentes,
            'credenciais' => $credenciaisArray
        ];
    }

    /**
     * [ processarConfiguracoes ] - Processa configurações para JSON válido
     * 
     * @param string $configuracoes Configurações em string
     * @return string JSON válido
     */
    private function processarConfiguracoes($configuracoes)
    {
        // Se está vazio, usar padrão
        if (empty(trim($configuracoes))) {
            return '{"timeout": 30, "retry_attempts": 3}';
        }
        
        // Se já é JSON válido, retornar como está
        if (json_decode($configuracoes) !== null) {
            return $configuracoes;
        }
        
        // Se não é JSON válido, tentar criar um JSON básico
        try {
            // Tentar interpretar como configurações básicas
            $configArray = [
                'timeout' => 30,
                'retry_attempts' => 3
            ];
            
            // Se parece ser um formato de configuração, tentar extrair valores
            if (preg_match('/timeout.*?(\d+)/i', $configuracoes, $matches)) {
                $configArray['timeout'] = intval($matches[1]);
            }
            
            if (preg_match('/retry.*?(\d+)/i', $configuracoes, $matches)) {
                $configArray['retry_attempts'] = intval($matches[1]);
            }
            
            return json_encode($configArray);
        } catch (Exception $e) {
            // Se tudo falhar, usar padrão
            return '{"timeout": 30, "retry_attempts": 3}';
        }
    }

    /**
     * [ api ] - Endpoints AJAX para departamentos
     */
    public function api()
    {
        header('Content-Type: application/json');

        if (!isset($_POST['action']) && !isset($_POST['acao'])) {
            echo json_encode(['success' => false, 'message' => 'Ação não especificada']);
            exit;
        }

        $acao = $_POST['action'] ?? $_POST['acao'];

        switch ($acao) {
            case 'excluir':
                $this->apiExcluirDepartamento();
                break;
            case 'buscar_credencial':
                $this->apiBuscarCredencial();
                break;
            case 'alterar_status':
                $this->apiAlterarStatus();
                break;
            case 'adicionar_atendente':
                $this->apiAdicionarAtendente();
                break;
            case 'remover_atendente':
                $this->apiRemoverAtendente();
                break;
            case 'testar_conectividade':
                $this->apiTestarConectividade();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
        }
        
        exit;
    }

    /**
     * [ apiBuscarCredencial ] - Busca credencial via AJAX
     */
    private function apiBuscarCredencial()
    {
        $credencialId = intval($_POST['credencial_id'] ?? 0);

        if (!$credencialId) {
            echo json_encode(['success' => false, 'message' => 'ID da credencial inválido']);
            exit;
        }

        $credencial = $this->credencialSerproModel->buscarPorId($credencialId);
        
        if ($credencial) {
            echo json_encode(['success' => true, 'credencial' => $credencial]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Credencial não encontrada']);
        }
    }

    /**
     * [ apiExcluirDepartamento ] - Exclui departamento via AJAX
     */
    private function apiExcluirDepartamento()
    {
        $departamentoId = intval($_POST['departamento_id'] ?? 0);

        if (!$departamentoId) {
            echo json_encode(['success' => false, 'message' => 'ID do departamento inválido']);
            exit;
        }

        $departamento = $this->departamentoModel->buscarPorId($departamentoId);
        if (!$departamento) {
            echo json_encode(['success' => false, 'message' => 'Departamento não encontrado']);
            exit;
        }

        $resultado = $this->departamentoModel->excluir($departamentoId);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Departamento excluído com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Não é possível excluir um departamento que possui conversas associadas']);
        }
    }

    /**
     * [ apiAlterarStatus ] - Altera status do departamento via AJAX
     */
    private function apiAlterarStatus()
    {
        $departamentoId = intval($_POST['departamento_id'] ?? 0);
        $novoStatus = $_POST['status'] ?? '';

        if (!$departamentoId || !in_array($novoStatus, ['ativo', 'inativo'])) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $resultado = $this->departamentoModel->alterarStatus($departamentoId, $novoStatus);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar status']);
        }
    }

    /**
     * [ apiAdicionarAtendente ] - Adiciona atendente ao departamento via AJAX
     */
    private function apiAdicionarAtendente()
    {
        $departamentoId = intval($_POST['departamento_id'] ?? 0);
        $usuarioId = intval($_POST['usuario_id'] ?? 0);
        $configuracoes = [
            'perfil' => $_POST['perfil'] ?? 'atendente',
            'max_conversas' => intval($_POST['max_conversas'] ?? 5),
            'horario_inicio' => $_POST['horario_inicio'] ?? '08:00:00',
            'horario_fim' => $_POST['horario_fim'] ?? '18:00:00',
            'dias_semana' => json_decode($_POST['dias_semana'] ?? '[1,2,3,4,5]', true)
        ];

        if (!$departamentoId || !$usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $resultado = $this->departamentoModel->adicionarAtendente($departamentoId, $usuarioId, $configuracoes);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Atendente adicionado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar atendente']);
        }
    }

    /**
     * [ apiRemoverAtendente ] - Remove atendente do departamento via AJAX
     */
    private function apiRemoverAtendente()
    {
        $departamentoId = intval($_POST['departamento_id'] ?? 0);
        $usuarioId = intval($_POST['usuario_id'] ?? 0);

        if (!$departamentoId || !$usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $resultado = $this->departamentoModel->removerAtendente($departamentoId, $usuarioId);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Atendente removido com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover atendente']);
        }
    }

    /**
     * [ apiTestarConectividade ] - Testa conectividade do departamento via AJAX
     */
    private function apiTestarConectividade()
    {
        $departamentoId = intval($_POST['departamento_id'] ?? 0);

        if (!$departamentoId) {
            echo json_encode(['success' => false, 'message' => 'ID do departamento inválido']);
            exit;
        }

        // Temporariamente desabilitado
        echo json_encode([
            'success' => false, 
            'message' => 'Funcionalidade temporariamente indisponível'
        ]);
        
        // $resultado = $this->serproApiDepartamento->testarConectividadeDepartamento($departamentoId);
        // echo json_encode($resultado);
    }

    /**
     * [ isAjaxRequest ] - Verifica se é uma requisição AJAX
     */
    private function isAjaxRequest()
    {
        // Verificar header X-Requested-With
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        
        // Verificar se é uma requisição via jQuery AJAX
        if (isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }
        
        // Verificar se é uma requisição via fetch
        if (isset($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], '*/*') !== false) {
            return true;
        }
        
        return false;
    }
} 