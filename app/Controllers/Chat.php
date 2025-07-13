<?php

/**
 * [ CHAT ] - Controlador para o sistema de chat multiatendimento
 * 
 * Este controlador gerencia:
 * - Painel principal do chat
 * - Envio de templates (primeira mensagem)
 * - Envio de mensagens de texto
 * - Envio de mídias (imagem, áudio, documento, vídeo)
 * - Controle de estado das conversas (24h timeout)
 * - Gerenciamento de atendentes
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class Chat extends Controllers
{
    private $conversaModel;
    private $mensagemModel;
    private $contatoModel;
    private $usuarioModel;
    private $serproApi;
    private $configuracaoModel;

    public function __construct()
    {
        parent::__construct();
        
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }
        
        // Inicializar models
        $this->conversaModel = $this->model('ConversaModel');
        $this->mensagemModel = $this->model('MensagemModel');
        $this->contatoModel = $this->model('ContatoModel');
        $this->usuarioModel = $this->model('UsuarioModel');
        $this->configuracaoModel = $this->model('ConfiguracaoModel');
        $this->serproApi = new SerproApi();
        
        // Atualizar último acesso
        $this->usuarioModel->atualizarUltimoAcesso($_SESSION['usuario_id']);
    }

    /**
     * [ index ] - Redireciona para o painel principal
     */
    public function index()
    {
        $this->painel();
    }

    /**
     * [ painel ] - Painel principal do chat
     */
    public function painel()
    {
        // Verificar se API Serpro está configurada
        if (!$this->serproApi->isConfigured()) {
            Helper::mensagem('chat', '<i class="fas fa-exclamation-triangle"></i> Configure a API Serpro antes de usar o chat', 'alert alert-warning');
            if ($_SESSION['usuario_perfil'] === 'admin') {
                Helper::redirecionar('configuracoes/serpro');
            } else {
                Helper::redirecionar('dashboard');
            }
            return;
        }

        $perfil = $_SESSION['usuario_perfil'];
        $usuarioId = $_SESSION['usuario_id'];
        
        // Buscar dados baseados no perfil
        $dados = [
            'usuario_logado' => [
                'id' => $usuarioId,
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $perfil,
                'status' => $_SESSION['usuario_status']
            ],
            'api_status' => $this->serproApi->obterStatusConexao(),
            'token_status' => $this->serproApi->obterStatusToken()
        ];

        // Dados específicos por perfil
        if ($perfil === 'atendente') {
            $dados['minhas_conversas'] = $this->conversaModel->getConversasPorAtendente($usuarioId);
            $dados['conversas_pendentes'] = $this->conversaModel->getConversasPendentes(5);
        } else {
            $dados['conversas_ativas'] = $this->conversaModel->getConversasAtivas(20);
            $dados['conversas_pendentes'] = $this->conversaModel->getConversasPendentes(10);
        }

        // Templates disponíveis
        $dados['templates'] = $this->getTemplatesDisponiveis();

        $this->view('chat/painel', $dados);
    }

    /**
     * [ iniciarConversa ] - Inicia nova conversa com template
     */
    public function iniciarConversa()
    {
        // Desabilitar exibição de erros em produção
        if (APP_ENV === 'production') {
            error_reporting(0);
        }
        
        // Limpar qualquer output buffer e definir headers antes de tudo
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método inválido']);
                exit;
            }

            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados || empty($dados['numero']) || empty($dados['template'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Número e template são obrigatórios']);
                exit;
            }

            $numero = $this->limparNumero($dados['numero']);
            $template = $dados['template'];
            $parametrosRaw = $dados['parametros'] ?? [];

            // Converter parâmetros para formato correto da API Serpro
            $parametros = [];
            foreach ($parametrosRaw as $parametro) {
                if (!empty($parametro)) {
                    $parametros[] = [
                        'tipo' => 'text',
                        'valor' => $parametro
                    ];
                }
            }

            // Verificar se já existe conversa ativa para este número
            $conversaExistente = $this->verificarConversaAtiva($numero);
            
            if ($conversaExistente) {
                http_response_code(409);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Já existe uma conversa ativa para este número. Aguarde 24 horas ou continue a conversa existente.'
                ]);
                exit;
            }

            // Criar/buscar contato
            $contato = $this->criarOuBuscarContato($numero, $dados['nome'] ?? null);
            
            if (!$contato) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao criar contato']);
                exit;
            }

            // Criar conversa
            $conversaId = $this->criarConversa($contato['id'], $_SESSION['usuario_id']);
            
            if (!$conversaId) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao criar conversa']);
                exit;
            }

            // Enviar template via API Serpro
            $resultado = $this->serproApi->enviarTemplate($numero, $template, $parametros);

            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                // Salvar mensagem no banco
                $this->salvarMensagemTemplate($conversaId, $contato['id'], $template, $parametros, $resultado);
                
                // Atualizar conversa
                $this->conversaModel->atualizarConversa($conversaId, [
                    'status' => 'aberto',
                    'ultima_mensagem' => date('Y-m-d H:i:s')
                ]);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Template enviado com sucesso!',
                    'conversa_id' => $conversaId,
                    'dados' => $resultado['response']
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao enviar template: ' . ($resultado['error'] ?? 'Erro desconhecido')
                ]);
            }
        } catch (Exception $e) {
            // Log do erro para debug mas não exibir
            error_log("Erro no Chat::iniciarConversa: " . $e->getMessage());
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
        
        exit;
    }

    /**
     * [ enviarMensagem ] - Envia mensagem de texto
     */
    public function enviarMensagem()
    {
        // Desabilitar exibição de erros em produção
        if (APP_ENV === 'production') {
            error_reporting(0);
        }
        
        // Limpar qualquer output buffer e definir headers antes de tudo
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método inválido']);
                exit;
            }

            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados || empty($dados['conversa_id']) || empty($dados['mensagem'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Conversa e mensagem são obrigatórias']);
                exit;
            }

            $conversaId = $dados['conversa_id'];
            $mensagem = $dados['mensagem'];

            // Verificar se a conversa existe e está ativa
            $conversa = $this->verificarConversa($conversaId);
            
            if (!$conversa) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
                exit;
            }

            // Verificar se conversa ainda está dentro do prazo (24h)
            if (!$this->conversaAindaAtiva($conversa)) {
                http_response_code(410);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Conversa expirada. Envie um novo template para reiniciar o contato.',
                    'expirada' => true
                ]);
                exit;
            }

            // Verificar se o contato já respondeu (necessário para enviar mensagem de texto)
            if (!$this->contatoJaRespondeu($conversaId)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Aguarde o contato responder ao template antes de enviar mensagens de texto.'
                ]);
                exit;
            }

            // Enviar mensagem via API Serpro
            $resultado = $this->serproApi->enviarMensagemTexto($conversa->numero, $mensagem);

            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                // Salvar mensagem no banco
                $this->salvarMensagemTexto($conversaId, $conversa->contato_id, $mensagem, $resultado);
                
                // Atualizar conversa
                $this->conversaModel->atualizarConversa($conversaId, [
                    'ultima_mensagem' => date('Y-m-d H:i:s')
                ]);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso!',
                    'dados' => $resultado['response']
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao enviar mensagem: ' . ($resultado['error'] ?? 'Erro desconhecido')
                ]);
            }
        } catch (Exception $e) {
            // Log do erro para debug mas não exibir
            error_log("Erro no Chat::enviarMensagem: " . $e->getMessage());
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
        
        exit;
    }

    /**
     * [ enviarMidia ] - Envia mídia (imagem, áudio, documento, vídeo)
     */
    public function enviarMidia()
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        $conversaId = $_POST['conversa_id'] ?? null;
        $caption = $_POST['caption'] ?? null;

        if (!$conversaId || !isset($_FILES['arquivo'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Conversa e arquivo são obrigatórios']);
            exit;
        }

        // Verificar se a conversa existe e está ativa
        $conversa = $this->verificarConversa($conversaId);
        
        if (!$conversa) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
            exit;
        }

        // Verificar se conversa ainda está dentro do prazo (24h)
        if (!$this->conversaAindaAtiva($conversa)) {
            http_response_code(410);
            echo json_encode([
                'success' => false, 
                'message' => 'Conversa expirada. Envie um novo template para reiniciar o contato.',
                'expirada' => true
            ]);
            exit;
        }

        // Verificar se o contato já respondeu
        if (!$this->contatoJaRespondeu($conversaId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Aguarde o contato responder ao template antes de enviar mídias.'
            ]);
            exit;
        }

        $arquivo = $_FILES['arquivo'];

        // Validar arquivo
        $validacao = $this->validarArquivo($arquivo);
        if (!$validacao['success']) {
            http_response_code(400);
            echo json_encode($validacao);
            exit;
        }

        // Fazer upload do arquivo
        $uploadResult = $this->serproApi->uploadMidia($arquivo, $arquivo['type']);

        if ($uploadResult['status'] >= 200 && $uploadResult['status'] < 300) {
            $idMedia = $uploadResult['response']['id'];
            $tipoMidia = $this->determinarTipoMidia($arquivo);

            // Enviar mídia
            $resultado = $this->serproApi->enviarMidia(
                $conversa->numero, 
                $tipoMidia, 
                $idMedia, 
                $caption, 
                null, 
                $tipoMidia === 'document' ? $arquivo['name'] : null
            );

            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                // Salvar mensagem no banco
                $this->salvarMensagemMidia($conversaId, $conversa->contato_id, $tipoMidia, $arquivo, $caption, $resultado);
                
                // Atualizar conversa
                $this->conversaModel->atualizarConversa($conversaId, [
                    'ultima_mensagem' => date('Y-m-d H:i:s')
                ]);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mídia enviada com sucesso!',
                    'dados' => $resultado['response']
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao enviar mídia: ' . ($resultado['error'] ?? 'Erro desconhecido')
                ]);
            }
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro no upload do arquivo: ' . ($uploadResult['error'] ?? 'Erro desconhecido')
            ]);
        }
        
        exit;
    }

    /**
     * [ buscarMensagens ] - Busca mensagens de uma conversa
     */
    public function buscarMensagens($conversaId)
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        if (!$conversaId || !is_numeric($conversaId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da conversa inválido']);
            exit;
        }

        $mensagens = $this->mensagemModel->getMensagensPorConversa($conversaId);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'mensagens' => $mensagens
        ]);
        
        exit;
    }

    /**
     * [ assumirConversa ] - Assume uma conversa pendente
     */
    public function assumirConversa($conversaId)
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        if (!$conversaId || !is_numeric($conversaId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da conversa inválido']);
            exit;
        }

        $resultado = $this->conversaModel->atualizarConversa($conversaId, [
            'atendente_id' => $_SESSION['usuario_id'],
            'status' => 'aberto'
        ]);

        if ($resultado) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Conversa assumida com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao assumir conversa']);
        }
        
        exit;
    }

    /**
     * [ fecharConversa ] - Fecha uma conversa
     */
    public function fecharConversa($conversaId)
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        if (!$conversaId || !is_numeric($conversaId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da conversa inválido']);
            exit;
        }

        $resultado = $this->conversaModel->atualizarConversa($conversaId, [
            'status' => 'fechado'
        ]);

        if ($resultado) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Conversa fechada com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao fechar conversa']);
        }
        
        exit;
    }

    /**
     * [ conversasAtivas ] - Lista conversas ativas
     */
    public function conversasAtivas()
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        $conversas = $this->conversaModel->getConversasAtivas(50);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'conversas' => $conversas
        ]);
        
        exit;
    }

    /**
     * [ conversasPendentes ] - Lista conversas pendentes
     */
    public function conversasPendentes()
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        $conversas = $this->conversaModel->getConversasPendentes(50);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'conversas' => $conversas
        ]);
        
        exit;
    }

    /**
     * [ verificarStatusMensagens ] - Verifica status atualizado das mensagens via API Serpro
     */
    public function verificarStatusMensagens($conversaId)
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        if (!$conversaId || !is_numeric($conversaId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da conversa inválido']);
            exit;
        }

        // Buscar mensagens com idRequisicao
        $mensagensComId = $this->mensagemModel->buscarMensagensComIdRequisicao($conversaId);
        
        if (empty($mensagensComId)) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'mensagens' => [],
                'message' => 'Nenhuma mensagem para verificar'
            ]);
            exit;
        }

        $mensagensStatus = [];
        $consultasRealizadas = 0;
        $consultasComSucesso = 0;

        // Consultar status de cada requisição via API Serpro
        foreach ($mensagensComId as $mensagem) {
            $idRequisicao = $mensagem['id_requisicao'];
            
            try {
                $consultasRealizadas++;
                $resultadoConsulta = $this->serproApi->consultarStatus($idRequisicao);
                
                if ($resultadoConsulta['status'] >= 200 && $resultadoConsulta['status'] < 300) {
                    $consultasComSucesso++;
                    $responseData = $resultadoConsulta['response'];
                    
                    // Mapear status das requisições de envio
                    $statusMapeados = [];
                    if (isset($responseData['requisicoesEnvio']) && is_array($responseData['requisicoesEnvio'])) {
                        foreach ($responseData['requisicoesEnvio'] as $requisicao) {
                            $destinatario = $requisicao['destinatario'];
                            $status = 'enviando'; // Status padrão
                            
                            // Mapear status baseado nos campos da API
                            if (!empty($requisicao['read'])) {
                                $status = 'lido';
                            } elseif (!empty($requisicao['delivered'])) {
                                $status = 'entregue';
                            } elseif (!empty($requisicao['sent'])) {
                                $status = 'enviado';
                            } elseif (!empty($requisicao['failed'])) {
                                $status = 'erro';
                            } elseif (!empty($requisicao['deleted'])) {
                                $status = 'erro';
                            }
                            
                            $statusMapeados[$destinatario] = $status;
                        }
                    }
                    
                    // Determinar o status da mensagem (usar o primeiro destinatário como referência)
                    $novoStatus = reset($statusMapeados) ?: $mensagem['status_entrega'];
                    
                    // Atualizar no banco se o status mudou
                    if ($novoStatus !== $mensagem['status_entrega']) {
                        if ($mensagem['serpro_message_id']) {
                            $this->mensagemModel->atualizarStatusPorSerproId($mensagem['serpro_message_id'], $novoStatus);
                        } else {
                            $this->mensagemModel->atualizarStatusEntrega($mensagem['id'], $novoStatus);
                        }
                    }
                    
                    $mensagensStatus[] = [
                        'id' => $mensagem['id'],
                        'status_entrega' => $novoStatus,
                        'serpro_message_id' => $mensagem['serpro_message_id'],
                        'status_anterior' => $mensagem['status_entrega'],
                        'atualizado' => $novoStatus !== $mensagem['status_entrega'],
                        'id_requisicao' => $idRequisicao,
                        'destinatarios_status' => $statusMapeados
                    ];
                    
                } else {
                    // Erro na consulta, manter status atual
                    $mensagensStatus[] = [
                        'id' => $mensagem['id'],
                        'status_entrega' => $mensagem['status_entrega'],
                        'serpro_message_id' => $mensagem['serpro_message_id'],
                        'status_anterior' => $mensagem['status_entrega'],
                        'atualizado' => false,
                        'id_requisicao' => $idRequisicao,
                        'erro_consulta' => $resultadoConsulta['error'] ?? 'Erro na consulta'
                    ];
                }
                
            } catch (Exception $e) {
                $mensagensStatus[] = [
                    'id' => $mensagem['id'],
                    'status_entrega' => $mensagem['status_entrega'],
                    'serpro_message_id' => $mensagem['serpro_message_id'],
                    'status_anterior' => $mensagem['status_entrega'],
                    'atualizado' => false,
                    'id_requisicao' => $idRequisicao,
                    'erro_exception' => $e->getMessage()
                ];
            }
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'mensagens' => $mensagensStatus,
            'consulta_api' => true,
            'total_mensagens' => count($mensagensComId),
            'consultas_realizadas' => $consultasRealizadas,
            'consultas_sucesso' => $consultasComSucesso,
            'taxa_sucesso' => $consultasRealizadas > 0 ? round(($consultasComSucesso / $consultasRealizadas) * 100, 2) : 0
        ]);
        
        exit;
    }
    
    /**
     * [ statusConversa ] - Verifica status da conversa (se pode enviar mensagens livres)
     */
    public function statusConversa($conversaId)
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        if (!$conversaId || !is_numeric($conversaId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da conversa inválido']);
            exit;
        }

        $conversa = $this->conversaModel->buscarPorId($conversaId);
        if (!$conversa) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
            exit;
        }

        $conversaAtiva = $this->conversaAindaAtiva($conversa);
        $contatoRespondeu = $this->contatoJaRespondeu($conversaId);
        $tempoRestante = $this->calcularTempoRestante($conversa);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'status' => [
                'conversa_ativa' => $conversaAtiva,
                'contato_respondeu' => $contatoRespondeu,
                'tempo_restante' => $tempoRestante
            ]
        ]);
        
        exit;
    }
    
    /**
     * [ atualizarStatusMensagem ] - Atualiza status de entrega de uma mensagem
     */
    public function atualizarStatusMensagem()
    {
        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        // Verificar se é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        // Pegar dados do JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['serpro_message_id']) || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $serproMessageId = $input['serpro_message_id'];
        $novoStatus = $input['status'];

        // Validar status
        $statusPermitidos = ['enviando', 'enviado', 'entregue', 'lido', 'erro'];
        if (!in_array($novoStatus, $statusPermitidos)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            exit;
        }

        // Atualizar mensagem no banco
        $resultado = $this->mensagemModel->atualizarStatusPorSerproId($serproMessageId, $novoStatus);
        
        if ($resultado) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Status atualizado com sucesso'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar status'
            ]);
        }
        
        exit;
    }

    /**
     * [ podeEnviarMensagemLivre ] - Verifica se pode enviar mensagem livre
     */
    private function podeEnviarMensagemLivre($conversaId, $conversa = null)
    {
        if (!$conversa) {
            $conversa = $this->verificarConversa($conversaId);
        }
        
        if (!$conversa) {
            return false;
        }

        return $this->contatoJaRespondeu($conversaId) && 
               $this->conversaAindaAtiva($conversa);
    }

    /**
     * [ calcularTempoRestante ] - Calcula tempo restante da janela de 24h
     */
    private function calcularTempoRestante($conversa)
    {
        $agora = time();
        $criadoEm = strtotime($conversa->criado_em);
        $ultimaMensagem = $conversa->ultima_mensagem ? strtotime($conversa->ultima_mensagem) : $criadoEm;
        
        $tempoLimite = max($criadoEm, $ultimaMensagem) + (24 * 60 * 60); // 24 horas
        $tempoRestante = $tempoLimite - $agora;
        
        return max(0, $tempoRestante); // Retorna 0 se já expirou
    }

    /**
     * [ verificarConversaAtiva ] - Verifica se existe conversa ativa para um número
     */
    private function verificarConversaAtiva($numero)
    {
        return $this->conversaModel->verificarConversaAtiva($numero);
    }

    /**
     * [ criarOuBuscarContato ] - Cria ou busca um contato
     */
    private function criarOuBuscarContato($numero, $nome = null)
    {
        // Buscar contato existente
        $contato = $this->contatoModel->buscarPorNumero($numero);
        
        if ($contato) {
            return (array) $contato;
        }

        // Criar novo contato
        $dadosContato = [
            'nome' => $nome ?: 'Contato ' . $numero,
            'numero' => $numero,
            'sessao_id' => 1 // Assumir sessão padrão
        ];

        if ($this->contatoModel->cadastrar($dadosContato)) {
            $novoContato = $this->contatoModel->buscarPorNumero($numero);
            return $novoContato ? (array) $novoContato : false;
        }

        return false;
    }

    /**
     * [ criarConversa ] - Cria uma nova conversa
     */
    private function criarConversa($contatoId, $atendenteId)
    {
        $dados = [
            'contato_id' => $contatoId,
            'atendente_id' => $atendenteId,
            'sessao_id' => 1,
            'status' => 'pendente'
        ];

        return $this->conversaModel->criarConversa($dados);
    }

    /**
     * [ salvarMensagemTemplate ] - Salva mensagem de template no banco
     */
    private function salvarMensagemTemplate($conversaId, $contatoId, $template, $parametros, $resultado)
    {
        $conteudo = "Template: {$template}";
        if (!empty($parametros)) {
            // Extrair apenas os valores dos parâmetros para o conteúdo
            $valoresParametros = [];
            foreach ($parametros as $param) {
                if (is_array($param) && isset($param['valor'])) {
                    $valoresParametros[] = $param['valor'];
                } elseif (is_string($param)) {
                    $valoresParametros[] = $param;
                }
            }
            if (!empty($valoresParametros)) {
                $conteudo .= " | Parâmetros: " . implode(', ', $valoresParametros);
            }
        }

        $dados = [
            'conversa_id' => $conversaId,
            'contato_id' => $contatoId,
            'atendente_id' => $_SESSION['usuario_id'],
            'serpro_message_id' => $resultado['response']['id'] ?? null,
            'tipo' => 'texto',
            'conteudo' => $conteudo,
            'direcao' => 'saida',
            'status_entrega' => 'enviado',
            'metadata' => json_encode([
                'tipo' => 'template',
                'template' => $template,
                'parametros' => $parametros,
                'serpro_response' => $resultado['response']
            ])
        ];

        return $this->mensagemModel->criarMensagem($dados);
    }

    /**
     * [ salvarMensagemTexto ] - Salva mensagem de texto no banco
     */
    private function salvarMensagemTexto($conversaId, $contatoId, $mensagem, $resultado)
    {
        $dados = [
            'conversa_id' => $conversaId,
            'contato_id' => $contatoId,
            'atendente_id' => $_SESSION['usuario_id'],
            'serpro_message_id' => $resultado['response']['id'] ?? null,
            'tipo' => 'texto',
            'conteudo' => $mensagem,
            'direcao' => 'saida',
            'status_entrega' => 'enviado',
            'metadata' => json_encode([
                'tipo' => 'texto',
                'serpro_response' => $resultado['response']
            ])
        ];

        return $this->mensagemModel->criarMensagem($dados);
    }

    /**
     * [ salvarMensagemMidia ] - Salva mensagem de mídia no banco
     */
    private function salvarMensagemMidia($conversaId, $contatoId, $tipoMidia, $arquivo, $caption, $resultado)
    {
        $conteudo = $caption ?: "Arquivo: {$arquivo['name']}";

        $dados = [
            'conversa_id' => $conversaId,
            'contato_id' => $contatoId,
            'atendente_id' => $_SESSION['usuario_id'],
            'serpro_message_id' => $resultado['response']['id'] ?? null,
            'tipo' => $tipoMidia,
            'conteudo' => $conteudo,
            'midia_nome' => $arquivo['name'],
            'midia_tipo' => $arquivo['type'],
            'direcao' => 'saida',
            'status_entrega' => 'enviado',
            'metadata' => json_encode([
                'tipo' => 'midia',
                'tipo_midia' => $tipoMidia,
                'arquivo_original' => $arquivo['name'],
                'tamanho' => $arquivo['size'],
                'serpro_response' => $resultado['response']
            ])
        ];

        return $this->mensagemModel->criarMensagem($dados);
    }

    /**
     * [ verificarConversa ] - Verifica se conversa existe e retorna dados
     */
    private function verificarConversa($conversaId)
    {
        return $this->conversaModel->verificarConversaPorId($conversaId);
    }

    /**
     * [ conversaAindaAtiva ] - Verifica se conversa ainda está dentro do prazo de 24h
     */
    private function conversaAindaAtiva($conversa)
    {
        return $this->conversaModel->conversaAindaAtiva($conversa);
    }

    /**
     * [ contatoJaRespondeu ] - Verifica se o contato já respondeu ao template
     */
    private function contatoJaRespondeu($conversaId)
    {
        return $this->mensagemModel->contatoJaRespondeu($conversaId);
    }

    /**
     * [ validarArquivo ] - Valida arquivo enviado
     */
    private function validarArquivo($arquivo)
    {
        $tamanhoMax = 16 * 1024 * 1024; // 16MB
        $tiposPermitidos = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/mp4',
            'video/mp4', 'video/avi', 'video/mov', 'video/wmv',
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'application/zip', 'application/x-rar-compressed'
        ];

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erro no upload do arquivo'];
        }

        if ($arquivo['size'] > $tamanhoMax) {
            return ['success' => false, 'message' => 'Arquivo muito grande. Máximo 16MB'];
        }

        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido'];
        }

        return ['success' => true];
    }

    /**
     * [ determinarTipoMidia ] - Determina o tipo de mídia baseado no arquivo
     */
    private function determinarTipoMidia($arquivo)
    {
        $tipo = $arquivo['type'];
        
        if (strpos($tipo, 'image/') === 0) {
            return 'image';
        } elseif (strpos($tipo, 'audio/') === 0) {
            return 'audio';
        } elseif (strpos($tipo, 'video/') === 0) {
            return 'video';
        } else {
            return 'document';
        }
    }

    /**
     * [ limparNumero ] - Limpa número de telefone
     */
    private function limparNumero($numero)
    {
        // Remove tudo que não for número
        $numero = preg_replace('/[^0-9]/', '', $numero);
        
        // Se começar com 0, remove
        if (substr($numero, 0, 1) === '0') {
            $numero = substr($numero, 1);
        }
        
        // Se não começar com 55, adiciona (código do Brasil)
        if (substr($numero, 0, 2) !== '55') {
            $numero = '55' . $numero;
        }
        
        return $numero;
    }

    /**
     * [ getTemplatesDisponiveis ] - Retorna templates disponíveis
     */
    private function getTemplatesDisponiveis()
    {
        // Templates baseados na API Serpro - devem estar aprovados na Meta
        return [
            [
                'nome' => 'central_intimacao_remota',
                'titulo' => 'Central de Intimação Remota',
                'descricao' => 'Template para intimações remotas do tribunal',
                'parametros' => ['mensagem']
            ]
            // ,
            // [
            //     'nome' => 'boas_vindas',
            //     'titulo' => 'Boas-vindas',
            //     'descricao' => 'Mensagem de boas-vindas personalizada',
            //     'parametros' => ['nome']
            // ],
            // [
            //     'nome' => 'promocao',
            //     'titulo' => 'Promoção',
            //     'descricao' => 'Oferta especial para clientes',
            //     'parametros' => ['nome', 'produto', 'desconto']
            // ],
            // [
            //     'nome' => 'lembrete',
            //     'titulo' => 'Lembrete',
            //     'descricao' => 'Lembrete de agendamento ou compromisso',
            //     'parametros' => ['nome', 'data', 'hora']
            // ],
            // [
            //     'nome' => 'suporte',
            //     'titulo' => 'Atendimento ao Cliente',
            //     'descricao' => 'Início de atendimento ao cliente',
            //     'parametros' => ['nome']
            // ],
            // [
            //     'nome' => 'notificacao',
            //     'titulo' => 'Notificação',
            //     'descricao' => 'Notificação importante para o cliente',
            //     'parametros' => ['nome', 'assunto', 'detalhes']
            // ]
        ];
    }
}
?> 