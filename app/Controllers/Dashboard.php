<?php

/**
 * [ DASHBOARD ] - Controlador para o painel principal do sistema de chat
 * 
 * Este controlador gerencia:
 * - Dashboard principal com estatísticas
 * - Visões diferentes por perfil (admin, supervisor, atendente)
 * - Estatísticas em tempo real
 * - Resumo de atividades
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class Dashboard extends Controllers
{
    private $usuarioModel;
    private $conversaModel;
    private $mensagemModel;
    private $sessaoWhatsappModel;

    public function __construct()
    {
        parent::__construct();
        
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }
        
        // Inicializar models
        $this->usuarioModel = $this->model('UsuarioModel');
        $this->conversaModel = $this->model('ConversaModel');
        $this->mensagemModel = $this->model('MensagemModel');
        $this->sessaoWhatsappModel = $this->model('SessaoWhatsappModel');
    }

    /**
     * [ inicial ] - Dashboard principal
     */
    public function inicial()
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'atendente';
        
        // Buscar dados baseados no perfil
        try {
            switch ($perfil) {
                case 'admin':
                    $dados = $this->getDashboardAdmin();
                    break;
                case 'supervisor':
                    $dados = $this->getDashboardSupervisor();
                    break;
                case 'atendente':
                    $dados = $this->getDashboardAtendente();
                    break;
                default:
                    $dados = $this->getDashboardAtendente();
            }
        } catch (Exception $e) {
            error_log("Erro no dashboard ({$perfil}): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Dados de fallback para evitar erro na view
            $dados = [
                'erro' => 'Erro ao carregar dados do dashboard',
                'tipo_dashboard' => $perfil,
                'minhas_conversas' => [],
                'conversas_pendentes' => [],
                'estatisticas_hoje' => (object)[
                    'conversas_atendidas' => 0,
                    'mensagens_enviadas' => 0,
                    'tempo_medio_resposta' => 0
                ],
                'mensagens_nao_lidas' => 0
            ];
        }

        // Dados comuns para todos os perfis
        $dados['usuario'] = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'perfil' => $_SESSION['usuario_perfil'],
            'status' => $_SESSION['usuario_status']
        ];

        $this->view('dashboard/inicial', $dados);
    }

    /**
     * [ getDashboardAdmin ] - Dados específicos para administrador
     */
    private function getDashboardAdmin()
    {
        $dados = [];

        // Estatísticas de usuários
        $dados['usuarios'] = $this->usuarioModel->getEstatisticasUsuarios();

        // Estatísticas de conversas (últimos 30 dias)
        $dados['conversas'] = $this->conversaModel->getEstatisticasConversas(30);

        // Mensagens de hoje
        $dados['mensagens_hoje'] = $this->mensagemModel->getEstatisticasMensagens();

        // Estatísticas de departamentos
        $departamentoModel = $this->model('DepartamentoModel');
        $dados['departamentos'] = $departamentoModel->getEstatisticasDepartamentos();

        // Atendentes online
        $dados['atendentes_online'] = $this->usuarioModel->getAtendentesOnline(5);

        $dados['tipo_dashboard'] = 'admin';
        return $dados;
    }

    /**
     * [ getDashboardSupervisor ] - Dados específicos para supervisor
     */
    private function getDashboardSupervisor()
    {
        $dados = [];

        // Conversas em andamento
        $dados['conversas_ativas'] = $this->conversaModel->getConversasAtivas(10);

        // Atendentes online
        $dados['atendentes_online'] = $this->usuarioModel->getAtendentesOnline(5);

        // Performance dos atendentes hoje
        $dados['performance_atendentes'] = $this->conversaModel->getPerformanceAtendentes();

        // Estatísticas do dia
        $dados['estatisticas_hoje'] = $this->conversaModel->getEstatisticasGerais();

        $dados['tipo_dashboard'] = 'supervisor';
        return $dados;
    }

    /**
     * [ getDashboardAtendente ] - Dados específicos para atendente
     */
    private function getDashboardAtendente()
    {
        $dados = [];
        $atendenteId = $_SESSION['usuario_id'];

        // Conversas do atendente
        $dados['minhas_conversas'] = $this->conversaModel->getConversasPorAtendente($atendenteId);

        // Conversas pendentes (sem atendente)
        $dados['conversas_pendentes'] = $this->conversaModel->getConversasPendentes(5);

        // Estatísticas do atendente hoje
        $dados['estatisticas_hoje'] = $this->conversaModel->getEstatisticasAtendente($atendenteId);

        // Mensagens não lidas
        $dados['mensagens_nao_lidas'] = $this->mensagemModel->contarMensagensNaoLidas($atendenteId);

        $dados['tipo_dashboard'] = 'atendente';
        return $dados;
    }

    /**
     * [ estatisticas ] - Endpoint AJAX para estatísticas em tempo real
     */
    public function estatisticas()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['error' => 'Sessão inválida']);
            return;
        }

        $perfil = $_SESSION['usuario_perfil'];
        $dados = [];

        try {
            switch ($perfil) {
                case 'admin':
                case 'supervisor':
                    // Conversas abertas
                    $dados['conversas_abertas'] = $this->conversaModel->contarConversasAbertas();

                    // Atendentes online
                    $dados['atendentes_online'] = $this->usuarioModel->contarAtendentesOnline();

                    // Mensagens de hoje
                    $dados['mensagens_hoje'] = $this->mensagemModel->contarMensagensHoje();
                    break;

                case 'atendente':
                    $atendenteId = $_SESSION['usuario_id'];
                    
                    // Minhas conversas abertas
                    $dados['minhas_conversas'] = $this->conversaModel->contarConversasPorAtendente($atendenteId);

                    // Mensagens não lidas
                    $dados['mensagens_nao_lidas'] = $this->mensagemModel->contarMensagensNaoLidas($atendenteId);
                    break;
            }

            echo json_encode(['success' => true, 'dados' => $dados]);

        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            echo json_encode(['error' => 'Erro ao buscar estatísticas']);
        }
    }

    /**
     * [ resumo ] - Endpoint para resumo geral do sistema (admin/supervisor)
     */
    public function resumo()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['error' => 'Sessão inválida']);
            return;
        }

        $perfil = $_SESSION['usuario_perfil'];

        if (!in_array($perfil, ['admin', 'supervisor'])) {
            echo json_encode(['error' => 'Acesso negado']);
            return;
        }

        try {
            $resumo = [
                'usuarios' => $this->usuarioModel->getEstatisticasUsuarios(),
                'conversas' => $this->conversaModel->getEstatisticasConversas(7), // Última semana
                'mensagens' => $this->mensagemModel->getEstatisticasMensagens(),
                'conexoes' => $this->sessaoWhatsappModel->getEstatisticasConexoes(),
                'timestamp' => time()
            ];

            echo json_encode(['success' => true, 'dados' => $resumo]);

        } catch (Exception $e) {
            error_log("Erro ao buscar resumo: " . $e->getMessage());
            echo json_encode(['error' => 'Erro ao buscar resumo']);
        }
    }

    /**
     * [ atividade ] - Endpoint para atividades recentes (futuro)
     */
    public function atividade()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['error' => 'Sessão inválida']);
            return;
        }

        // Futuro: implementar log de atividades
        $atividades = [
            ['tipo' => 'login', 'usuario' => $_SESSION['usuario_nome'], 'timestamp' => time()],
            ['tipo' => 'dashboard_acesso', 'usuario' => $_SESSION['usuario_nome'], 'timestamp' => time()]
        ];

        echo json_encode(['success' => true, 'atividades' => $atividades]);
    }
} 