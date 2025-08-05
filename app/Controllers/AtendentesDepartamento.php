<?php

/**
 * [ ATENDENTESDEPARTAMENTO ] - Controlador para gerenciar atendentes por departamento
 * 
 * Este controlador gerencia:
 * - Adicionar/remover atendentes de departamentos
 * - Configurar permissões por departamento
 * - Definir horários e limites por departamento
 * - Visualizar estatísticas por departamento
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class AtendentesDepartamento extends Controllers
{
    private $usuarioModel;
    private $departamentoModel;

    public function __construct()
    {
        parent::__construct();
        
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login');
            return;
        }
        
        // Verificar permissão (apenas admin e supervisor)
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'supervisor'])) {
            Helper::redirecionar('dashboard');
            return;
        }
        
        // Inicializar models
        $this->usuarioModel = $this->model('UsuarioModel');
        $this->departamentoModel = $this->model('DepartamentoModel');
    }

    /**
     * [ index ] - Lista atendentes de um departamento
     */
    public function index($departamentoId = null)
    {
        if (!$departamentoId) {
            Helper::redirecionar('departamentos');
            return;
        }

        // Buscar dados do departamento
        $departamento = $this->departamentoModel->buscarPorId($departamentoId);
        if (!$departamento) {
            Helper::mensagem('departamentos', 'Departamento não encontrado', 'alert alert-danger');
            Helper::redirecionar('departamentos');
            return;
        }

        // Buscar atendentes do departamento
        $atendentes = $this->usuarioModel->getAtendentesPorDepartamento($departamentoId);
        
        // Buscar todos os usuários disponíveis (não atribuídos ao departamento)
        $todosUsuarios = $this->usuarioModel->listarPorPerfil('atendente');
        $usuariosDisponiveis = [];
        
        foreach ($todosUsuarios as $usuario) {
            $jaAtribuido = false;
            foreach ($atendentes as $atendente) {
                if ($atendente->id === $usuario->id) {
                    $jaAtribuido = true;
                    break;
                }
            }
            if (!$jaAtribuido) {
                $usuariosDisponiveis[] = $usuario;
            }
        }

        $dados = [
            'departamento' => $departamento,
            'atendentes' => $atendentes,
            'usuarios_disponiveis' => $usuariosDisponiveis,
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ]
        ];

        $this->view('atendentes_departamento/index', $dados);
    }

    /**
     * [ adicionarAtendente ] - Adiciona um atendente ao departamento
     */
    public function adicionarAtendente()
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $dados = json_decode(file_get_contents('php://input'), true);

        if (empty($dados['usuario_id']) || empty($dados['departamento_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
            return;
        }

        try {
            // Verificar se usuário já está no departamento
            $jaAtribuido = $this->usuarioModel->verificarAtendenteDepartamento(
                $dados['usuario_id'], 
                $dados['departamento_id']
            );

            if ($jaAtribuido) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Usuário já está atribuído a este departamento']);
                return;
            }

            // Adicionar usuário ao departamento
            $resultado = $this->departamentoModel->adicionarAtendente(
                $dados['departamento_id'],
                $dados['usuario_id'],
                $dados['perfil'] ?? 'atendente',
                $dados['max_conversas'] ?? 5,
                $dados['horario_inicio'] ?? '08:00:00',
                $dados['horario_fim'] ?? '18:00:00',
                $dados['dias_semana'] ?? [1,2,3,4,5] // Segunda a sexta
            );

            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Atendente adicionado ao departamento com sucesso!'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao adicionar atendente']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * [ removerAtendente ] - Remove um atendente do departamento
     */
    public function removerAtendente()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $dados = json_decode(file_get_contents('php://input'), true);

        if (empty($dados['usuario_id']) || empty($dados['departamento_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
            return;
        }

        try {
            // Remover usuário do departamento
            $resultado = $this->departamentoModel->removerAtendente(
                $dados['departamento_id'],
                $dados['usuario_id']
            );

            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Atendente removido do departamento com sucesso!'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao remover atendente']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * [ atualizarConfiguracao ] - Atualiza configuração do atendente no departamento
     */
    public function atualizarConfiguracao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $dados = json_decode(file_get_contents('php://input'), true);

        if (empty($dados['usuario_id']) || empty($dados['departamento_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
            return;
        }

        try {
            // Atualizar configuração
            $resultado = $this->departamentoModel->atualizarConfiguracaoAtendente(
                $dados['departamento_id'],
                $dados['usuario_id'],
                $dados['perfil'] ?? 'atendente',
                $dados['max_conversas'] ?? 5,
                $dados['horario_inicio'] ?? '08:00:00',
                $dados['horario_fim'] ?? '18:00:00',
                $dados['dias_semana'] ?? [1,2,3,4,5],
                $dados['status'] ?? 'ativo'
            );

            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Configuração atualizada com sucesso!'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar configuração']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * [ buscarConfiguracao ] - Busca configuração de um atendente no departamento
     */
    public function buscarConfiguracao($usuarioId, $departamentoId)
    {
        try {
            // Buscar configuração do atendente no departamento
            $configuracao = $this->departamentoModel->buscarConfiguracaoAtendente($departamentoId, $usuarioId);
            
            if ($configuracao) {
                echo json_encode([
                    'success' => true,
                    'configuracao' => $configuracao
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * [ estatisticas ] - Estatísticas do departamento
     */
    public function estatisticas($departamentoId)
    {
        // Buscar dados do departamento
        $departamento = $this->departamentoModel->buscarPorId($departamentoId);
        if (!$departamento) {
            Helper::redirecionar('departamentos');
            return;
        }

        // Buscar estatísticas
        $atendentes = $this->usuarioModel->getAtendentesPorDepartamento($departamentoId);
        $atendentesOnline = $this->usuarioModel->getAtendentesOnlinePorDepartamento($departamentoId);
        $totalAtendentes = $this->usuarioModel->contarAtendentesPorDepartamento($departamentoId);

        $dados = [
            'departamento' => $departamento,
            'atendentes' => $atendentes,
            'atendentes_online' => $atendentesOnline,
            'total_atendentes' => $totalAtendentes,
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ]
        ];

        $this->view('atendentes_departamento/estatisticas', $dados);
    }
} 