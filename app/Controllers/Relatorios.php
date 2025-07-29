<?php

/**
 * [ RELATORIOS ] - Controlador para relatórios do ChatSerpro
 * 
 * Este controlador gerencia todos os relatórios do sistema:
 * - Relatórios de conversas
 * - Performance de atendentes
 * - Estatísticas de templates
 * - Volume de mensagens
 * - Tempo de resposta
 * - Análises por período
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class Relatorios extends Controllers
{
    private $relatorioModel;
    private $conversaModel;
    private $mensagemModel;
    private $usuarioModel;
    private $contatoModel;

    public function __construct()
    {
        parent::__construct();
        
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }
        
        // Verificar permissão (apenas admin e supervisor)
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'supervisor'])) {
            Helper::mensagem('acesso_negado', '<i class="fas fa-exclamation-triangle"></i> Acesso negado. Você não tem permissão para acessar esta área.', 'alert alert-danger');
            Helper::redirecionar('dashboard');
            return;
        }
        
        // Inicializar models
        $this->relatorioModel = $this->model('RelatorioModel');
        $this->conversaModel = $this->model('ConversaModel');
        $this->mensagemModel = $this->model('MensagemModel');
        $this->usuarioModel = $this->model('UsuarioModel');
        $this->contatoModel = $this->model('ContatoModel');
    }

    /**
     * [ index ] - Página principal de relatórios
     */
    public function index()
    {
        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'periodo_padrao' => [
                'inicio' => date('Y-m-d', strtotime('-30 days')),
                'fim' => date('Y-m-d')
            ]
        ];

        $this->view('relatorios/index', $dados);
    }

    /**
     * [ conversas ] - Relatório de conversas
     */
    public function conversas()
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
            'status' => $_GET['status'] ?? '',
            'atendente_id' => $_GET['atendente_id'] ?? '',
            'formato' => $_GET['formato'] ?? 'html'
        ];

        // Buscar dados do relatório
        $conversas = $this->relatorioModel->getRelatorioConversas($filtros);
        $estatisticas = $this->relatorioModel->getEstatisticasConversas($filtros);
        $atendentes = $this->usuarioModel->listarPorPerfil('atendente');

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'conversas' => $conversas,
            'estatisticas' => $estatisticas,
            'atendentes' => $atendentes,
            'filtros' => $filtros
        ];

        if ($filtros['formato'] === 'excel') {
            $this->exportarExcel('conversas', $conversas, $filtros);
        } elseif ($filtros['formato'] === 'pdf') {
            $this->exportarPDF('conversas', $dados);
        } else {
            $this->view('relatorios/conversas', $dados);
        }
    }

    /**
     * [ atendentes ] - Relatório de performance dos atendentes
     */
    public function atendentes()
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
            'atendente_id' => $_GET['atendente_id'] ?? '',
            'formato' => $_GET['formato'] ?? 'html'
        ];

        // Buscar dados do relatório
        $performance = $this->relatorioModel->getPerformanceAtendentes($filtros);
        $ranking = $this->relatorioModel->getRankingAtendentes($filtros);
        $atendentes = $this->usuarioModel->listarPorPerfil('atendente');

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'performance' => $performance,
            'ranking' => $ranking,
            'atendentes' => $atendentes,
            'filtros' => $filtros
        ];

        if ($filtros['formato'] === 'excel') {
            $this->exportarExcel('atendentes', $performance, $filtros);
        } elseif ($filtros['formato'] === 'pdf') {
            $this->exportarPDF('atendentes', $dados);
        } else {
            $this->view('relatorios/atendentes', $dados);
        }
    }

    /**
     * [ templates ] - Relatório de utilização de templates
     */
    public function templates()
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
            'template' => $_GET['template'] ?? '',
            'formato' => $_GET['formato'] ?? 'html'
        ];

        // Buscar dados do relatório
        $utilizacao = $this->relatorioModel->getUtilizacaoTemplates($filtros);
        $estatisticas = $this->relatorioModel->getEstatisticasTemplates($filtros);

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'utilizacao' => $utilizacao,
            'estatisticas' => $estatisticas,
            'filtros' => $filtros
        ];

        if ($filtros['formato'] === 'excel') {
            $this->exportarExcel('templates', $utilizacao, $filtros);
        } elseif ($filtros['formato'] === 'pdf') {
            $this->exportarPDF('templates', $dados);
        } else {
            $this->view('relatorios/templates', $dados);
        }
    }

    /**
     * [ mensagens ] - Relatório de volume de mensagens
     */
    public function mensagens()
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
            'tipo' => $_GET['tipo'] ?? '',
            'direcao' => $_GET['direcao'] ?? '',
            'formato' => $_GET['formato'] ?? 'html'
        ];

        // Buscar dados do relatório
        $volume = $this->relatorioModel->getVolumeMensagens($filtros);
        $por_dia = $this->relatorioModel->getMensagensPorDia($filtros);
        $por_hora = $this->relatorioModel->getMensagensPorHora($filtros);
        $estatisticas = $this->relatorioModel->getEstatisticasMensagens($filtros);

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'volume' => $volume,
            'por_dia' => $por_dia,
            'por_hora' => $por_hora,
            'estatisticas' => $estatisticas,
            'filtros' => $filtros
        ];

        if ($filtros['formato'] === 'excel') {
            $this->exportarExcel('mensagens', $volume, $filtros);
        } elseif ($filtros['formato'] === 'pdf') {
            $this->exportarPDF('mensagens', $dados);
        } else {
            $this->view('relatorios/mensagens', $dados);
        }
    }

    /**
     * [ tempo_resposta ] - Relatório de tempo de resposta
     */
    public function tempo_resposta()
    {
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d'),
            'atendente_id' => $_GET['atendente_id'] ?? '',
            'formato' => $_GET['formato'] ?? 'html'
        ];

        // Buscar dados do relatório
        $tempos = $this->relatorioModel->getTempoResposta($filtros);
        $media_por_atendente = $this->relatorioModel->getTempoRespostaPorAtendente($filtros);
        $evolucao = $this->relatorioModel->getEvolucaoTempoResposta($filtros);
        $atendentes = $this->usuarioModel->listarPorPerfil('atendente');

        $dados = [
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil']
            ],
            'tempos' => $tempos,
            'media_por_atendente' => $media_por_atendente,
            'evolucao' => $evolucao,
            'atendentes' => $atendentes,
            'filtros' => $filtros
        ];

        if ($filtros['formato'] === 'excel') {
            $this->exportarExcel('tempo_resposta', $tempos, $filtros);
        } elseif ($filtros['formato'] === 'pdf') {
            $this->exportarPDF('tempo_resposta', $dados);
        } else {
            $this->view('relatorios/tempo_resposta', $dados);
        }
    }

    /**
     * [ dashboard ] - Dashboard com gráficos para AJAX
     */
    public function dashboard()
    {
        header('Content-Type: application/json');

        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days')),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-d')
        ];

        try {
            $dados = [
                'conversas_por_dia' => $this->relatorioModel->getConversasPorDia($filtros),
                'mensagens_por_dia' => $this->relatorioModel->getMensagensPorDia($filtros),
                'conversas_por_status' => $this->relatorioModel->getConversasPorStatus($filtros),
                'top_atendentes' => $this->relatorioModel->getTopAtendentes($filtros),
                'templates_mais_usados' => $this->relatorioModel->getTemplatesMaisUsados($filtros),
                'tempo_resposta_medio' => $this->relatorioModel->getTempoRespostaGeral($filtros)
            ];

            echo json_encode([
                'success' => true,
                'dados' => $dados
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar dados do dashboard'
            ]);
        }
    }

    /**
     * [ alterarStatusConversa ] - Altera o status de uma conversa
     */
    public function alterarStatusConversa()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['conversa_id']) || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $conversaId = $input['conversa_id'];
        $novoStatus = $input['status'];

        // Validar status
        $statusValidos = ['pendente', 'aberto', 'fechado'];
        if (!in_array($novoStatus, $statusValidos)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            exit;
        }

        try {
            // Verificar se a conversa existe
            $conversa = $this->conversaModel->verificarConversaPorId($conversaId);
            if (!$conversa) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
                exit;
            }

            // Se estiver fechando a conversa, enviar mensagem de encerramento
            $mensagemEnviada = false;
            if ($novoStatus === 'fechado') {
                try {
                    // Carregar helper de mensagens automáticas
                    $mensagensHelper = new MensagensAutomaticasHelper();
                    
                    // Buscar informações do contato
                    $contato = $this->contatoModel->lerContatoPorId($conversa->contato_id);
                    
                    if ($contato) {
                        // Obter mensagem de encerramento
                        $mensagemEncerramento = $mensagensHelper->obterMensagemAutomatica('encerramento', [
                            'nome' => $contato->nome ?? 'Cliente'
                        ]);
                        
                        if ($mensagemEncerramento) {
                            // Enviar mensagem de encerramento
                            $resultadoEnvio = $mensagensHelper->enviarMensagemAutomatica(
                                $contato->numero,
                                $mensagemEncerramento,
                                $conversaId
                            );
                            
                            if ($resultadoEnvio['success']) {
                                $mensagemEnviada = true;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Log do erro, mas não falhar a alteração do status
                    error_log("Erro ao enviar mensagem de encerramento: " . $e->getMessage());
                }
            }

            // Atualizar status da conversa
            $resultado = $this->conversaModel->atualizarConversa($conversaId, [
                'status' => $novoStatus
            ]);

            if ($resultado) {
                // Log da ação
                error_log("🔄 Status da conversa {$conversaId} alterado para '{$novoStatus}' por {$_SESSION['usuario_nome']} (ID: {$_SESSION['usuario_id']})");
                
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Status da conversa alterado com sucesso',
                    'novo_status' => $novoStatus,
                    'mensagem_encerramento_enviada' => $mensagemEnviada
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao alterar status da conversa']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao alterar status da conversa: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
        
        exit;
    }

    /**
     * [ buscarMensagensConversa ] - Busca mensagens de uma conversa específica
     */
    public function buscarMensagensConversa($conversaId)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        try {
            // Verificar se a conversa existe
            $conversa = $this->conversaModel->verificarConversaPorId($conversaId);
            if (!$conversa) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
                exit;
            }

            // Buscar mensagens da conversa
            $mensagens = $this->mensagemModel->getMensagensPorConversa($conversaId);

            // Formatar mensagens para exibição
            $mensagensFormatadas = [];
            foreach ($mensagens as $mensagem) {
                $mensagensFormatadas[] = [
                    'id' => $mensagem->id,
                    'texto' => $mensagem->conteudo,
                    'tipo_mensagem' => $mensagem->direcao === 'entrada' ? 'recebida' : 'enviada',
                    'criado_em' => date('d/m/Y H:i', strtotime($mensagem->criado_em)),
                    'status' => $mensagem->status_entrega ?? 'enviada',
                    'tipo' => $mensagem->tipo ?? 'texto'
                ];
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'mensagens' => $mensagensFormatadas,
                'total_mensagens' => count($mensagensFormatadas)
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar mensagens da conversa: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
        
        exit;
    }

    /**
     * [ alterarAtendenteConversa ] - Altera o atendente de uma conversa
     */
    public function alterarAtendenteConversa()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['conversa_id']) || !isset($input['atendente_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $conversaId = $input['conversa_id'];
        $novoAtendenteId = $input['atendente_id'];

        try {
            // Verificar se a conversa existe
            $conversa = $this->conversaModel->verificarConversaPorId($conversaId);
            if (!$conversa) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
                exit;
            }

            // Verificar se o novo atendente existe e é um atendente
            $novoAtendente = $this->usuarioModel->lerUsuarioPorId($novoAtendenteId);
            if (!$novoAtendente || $novoAtendente->perfil !== 'atendente') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Atendente não encontrado ou inválido']);
                exit;
            }

            // Verificar se o atendente está ativo
            if ($novoAtendente->status !== 'ativo') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Atendente não está ativo']);
                exit;
            }

            // Atualizar atendente da conversa
            $resultado = $this->conversaModel->atualizarConversa($conversaId, [
                'atendente_id' => $novoAtendenteId
            ]);

            if ($resultado) {
                // Log da ação
                error_log("🔄 Atendente da conversa {$conversaId} alterado para '{$novoAtendente->nome}' (ID: {$novoAtendenteId}) por {$_SESSION['usuario_nome']} (ID: {$_SESSION['usuario_id']})");
                
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Atendente da conversa alterado com sucesso',
                    'novo_atendente' => [
                        'id' => $novoAtendente->id,
                        'nome' => $novoAtendente->nome,
                        'email' => $novoAtendente->email
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao alterar atendente da conversa']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao alterar atendente da conversa: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
        
        exit;
    }

    /**
     * [ exportarExcel ] - Exporta relatório para Excel
     */
    private function exportarExcel($tipo, $dados, $filtros)
    {
        $filename = "relatorio_{$tipo}_" . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fwrite($output, "\xEF\xBB\xBF");

        // Cabeçalhos baseados no tipo de relatório
        switch ($tipo) {
            case 'conversas':
                fputcsv($output, ['ID', 'Contato', 'Número', 'Atendente', 'Status', 'Criado em', 'Última mensagem', 'Total mensagens']);
                foreach ($dados as $item) {
                    fputcsv($output, [
                        $item->id,
                        $item->contato_nome,
                        $item->numero,
                        $item->atendente_nome ?: 'Sem atendente',
                        ucfirst($item->status),
                        date('d/m/Y H:i', strtotime($item->criado_em)),
                        $item->ultima_mensagem ? date('d/m/Y H:i', strtotime($item->ultima_mensagem)) : 'N/A',
                        $item->total_mensagens
                    ]);
                }
                break;

            case 'atendentes':
                fputcsv($output, ['Atendente', 'Total Conversas', 'Conversas Abertas', 'Conversas Fechadas', 'Total Mensagens', 'Tempo Médio Resposta (min)', 'Avaliação']);
                foreach ($dados as $item) {
                    fputcsv($output, [
                        $item->nome,
                        $item->total_conversas,
                        $item->conversas_abertas,
                        $item->conversas_fechadas,
                        $item->total_mensagens,
                        number_format($item->tempo_medio_resposta, 2),
                        number_format($item->avaliacao_media, 1)
                    ]);
                }
                break;

            case 'templates':
                fputcsv($output, ['Template', 'Total Utilizações', 'Sucesso', 'Falhas', 'Taxa Sucesso (%)', 'Última utilização']);
                foreach ($dados as $item) {
                    fputcsv($output, [
                        $item->template,
                        $item->total_utilizacoes,
                        $item->sucessos,
                        $item->falhas,
                        number_format($item->taxa_sucesso, 2),
                        $item->ultima_utilizacao ? date('d/m/Y H:i', strtotime($item->ultima_utilizacao)) : 'N/A'
                    ]);
                }
                break;

            case 'mensagens':
                fputcsv($output, ['Data', 'Entrada', 'Saída', 'Total', 'Texto', 'Mídia', 'Templates']);
                foreach ($dados as $item) {
                    fputcsv($output, [
                        date('d/m/Y', strtotime($item->data)),
                        $item->entrada,
                        $item->saida,
                        $item->total,
                        $item->texto,
                        $item->midia,
                        $item->templates
                    ]);
                }
                break;

            case 'tempo_resposta':
                fputcsv($output, ['Atendente', 'Conversas', 'Tempo Médio (min)', 'Tempo Mínimo (min)', 'Tempo Máximo (min)', 'Dentro SLA']);
                foreach ($dados as $item) {
                    fputcsv($output, [
                        $item->atendente_nome,
                        $item->total_conversas,
                        number_format($item->tempo_medio, 2),
                        number_format($item->tempo_minimo, 2),
                        number_format($item->tempo_maximo, 2),
                        $item->dentro_sla . '%'
                    ]);
                }
                break;
        }

        fclose($output);
        exit;
    }

    /**
     * [ exportarPDF ] - Exporta relatório para PDF
     */
    private function exportarPDF($tipo, $dados)
    {
        // Para implementação futura com biblioteca PDF
        Helper::mensagem('relatorio', '<i class="fas fa-info-circle"></i> Exportação para PDF em desenvolvimento. Use a exportação Excel por enquanto.', 'alert alert-info');
        Helper::redirecionar("relatorios/{$tipo}");
    }
} 