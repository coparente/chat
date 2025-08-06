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
    private $departamentoHelper; // Nova propriedade para departamentos

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
        $this->departamentoHelper = new DepartamentoHelper(); // Inicializar helper de departamentos
        
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
        $perfil = $_SESSION['usuario_perfil'];
        $usuarioId = $_SESSION['usuario_id'];
        
        // Buscar departamentos do usuário
        $departamentosUsuario = $this->usuarioModel->getDepartamentosUsuario($usuarioId);
        
        // Buscar dados baseados no perfil
        $dados = [
            'usuario_logado' => [
                'id' => $usuarioId,
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $perfil,
                'status' => $_SESSION['usuario_status']
            ],
            'departamentos_usuario' => $departamentosUsuario,
            'api_status' => $this->serproApi->obterStatusConexao(),
            'token_status' => $this->serproApi->obterStatusToken()
        ];

        // Dados específicos por perfil com suporte a departamentos
        if ($perfil === 'atendente') {
            // Filtrar conversas por departamentos do usuário
            $dados['minhas_conversas'] = $this->getConversasPorDepartamentosUsuario($usuarioId);
            $dados['conversas_pendentes'] = $this->getConversasPendentesPorDepartamentosUsuario($usuarioId);
        } else {
            // Admin/supervisor pode ver todas as conversas ou filtrar por departamento
            $departamentoFiltro = $_GET['departamento'] ?? null;
            if ($departamentoFiltro) {
                $dados['conversas_ativas'] = $this->conversaModel->getConversasAtivasPorDepartamento($departamentoFiltro, 20);
                $dados['conversas_pendentes'] = $this->conversaModel->getConversasPendentesPorDepartamento($departamentoFiltro, 10);
            } else {
                $dados['conversas_ativas'] = $this->conversaModel->getConversasAtivas(20);
                $dados['conversas_pendentes'] = $this->conversaModel->getConversasPendentes(10);
            }
        }

        // Templates disponíveis
        $dados['templates'] = $this->getTemplatesDisponiveis();

        // Carregar view
        $this->view('chat/painel', $dados);
    }

    /**
     * [ iniciarConversa ] - Inicia uma nova conversa
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
            $departamentoIdEnviado = $dados['departamento_id'] ?? null;

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
                error_log("❌ Erro ao criar/buscar contato para número: {$numero}");
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao criar contato']);
                exit;
            }

            // Determinar departamento para a conversa
            $departamentoId = null;
            
            if ($_SESSION['usuario_perfil'] === 'atendente') {
                // Para atendentes, usar o departamento enviado pelo frontend
                if ($departamentoIdEnviado) {
                    // Verificar se o usuário tem permissão para este departamento
                    $temPermissao = $this->usuarioModel->verificarAtendenteDepartamento($_SESSION['usuario_id'], $departamentoIdEnviado);
                    if ($temPermissao) {
                        $departamentoId = $departamentoIdEnviado;
                        error_log("✅ Usando departamento selecionado pelo usuário: ID {$departamentoId}");
                    } else {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para iniciar conversas neste departamento']);
                        exit;
                    }
                } else {
                    // Fallback: usar o primeiro departamento do usuário
                    $departamentosUsuario = $this->usuarioModel->getDepartamentosUsuario($_SESSION['usuario_id']);
                    if (!empty($departamentosUsuario)) {
                        $departamentoId = $departamentosUsuario[0]->id;
                        error_log("✅ Usando primeiro departamento do usuário: {$departamentosUsuario[0]->nome} (ID: {$departamentoId})");
                    } else {
                        // Fallback: identificar automaticamente
                        $departamentoId = $this->conversaModel->determinarDepartamentoConversa($numero, $dados['mensagem_inicial'] ?? '');
                        error_log("⚠️ Usuário não tem departamento associado, usando identificação automática: {$departamentoId}");
                    }
                }
            } else {
                // Para admin/supervisor, usar identificação automática
                $departamentoId = $this->conversaModel->determinarDepartamentoConversa($numero, $dados['mensagem_inicial'] ?? '');
                error_log("✅ Admin/Supervisor usando identificação automática: {$departamentoId}");
            }
            
            // Verificar se o usuário tem permissão para o departamento
            if ($_SESSION['usuario_perfil'] === 'atendente') {
                $temPermissao = $this->usuarioModel->verificarAtendenteDepartamento($_SESSION['usuario_id'], $departamentoId);
                if (!$temPermissao) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Você não tem permissão para iniciar conversas neste departamento']);
                    exit;
                }
            }

            // Criar conversa com departamento
            try {
                $conversaId = $this->criarConversaComDepartamento($contato['id'], $_SESSION['usuario_id'], $departamentoId);
                
                if (!$conversaId) {
                    error_log("❌ Erro ao criar conversa - conversaId retornou false");
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar conversa']);
                    exit;
                }
                
                error_log("✅ Conversa criada com sucesso: ID {$conversaId}, Departamento {$departamentoId}");
                
            } catch (Exception $e) {
                error_log("❌ Exceção ao criar conversa: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao criar conversa: ' . $e->getMessage()]);
                exit;
            }

            // ✅ NOVO: Obter credencial específica do departamento
            $credencial = $this->departamentoHelper->obterCredencialDepartamento($departamentoId);
            $resultado = null;
            $credencialUsada = null;
            
            if ($credencial) {
                // ✅ Usar SerproApi com credenciais específicas do departamento
                try {
                    $serproApiDepartamento = new SerproApi();
                    
                    // Configurar com credenciais específicas do departamento
                    $serproApiDepartamento->configurarComCredencial($credencial);
                    
                    $resultado = $serproApiDepartamento->enviarTemplate($numero, $template, $parametros);
                    $credencialUsada = [
                        'id' => $credencial->id,
                        'nome' => $credencial->nome,
                        'departamento_id' => $credencial->departamento_id,
                        'client_id' => $credencial->client_id
                    ];
                    
                    error_log("✅ Usando credencial específica do departamento: {$credencial->nome} (ID: {$credencial->id}) para departamento {$departamentoId}");
                    
                } catch (Exception $e) {
                    error_log("❌ Erro ao usar credencial específica do departamento: " . $e->getMessage());
                    // Fallback para API padrão
                    $resultado = $this->serproApi->enviarTemplate($numero, $template, $parametros);
                    $credencialUsada = ['fallback' => 'API padrão'];
                }
            } else {
                // ✅ Usar API padrão se não houver credencial específica
                $resultado = $this->serproApi->enviarTemplate($numero, $template, $parametros);
                $credencialUsada = ['fallback' => 'API padrão - sem credencial específica'];
                error_log("⚠️ Nenhuma credencial específica encontrada para departamento {$departamentoId}, usando API padrão");
            }

            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                // Salvar mensagem no banco
                $this->salvarMensagemTemplate($conversaId, $contato['id'], $template, $parametros, $resultado);
                
                // Atualizar conversa
                $this->conversaModel->atualizarConversa($conversaId, [
                    'status' => 'aberto',
                    'ultima_mensagem' => date('Y-m-d H:i:s')
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Conversa iniciada com sucesso!',
                    'conversa_id' => $conversaId,
                    'departamento_id' => $departamentoId,
                    'credencial_usada' => $credencialUsada
                ]);
            } else {
                // Se falhou o envio, deletar a conversa criada
                $this->conversaModel->atualizarConversa($conversaId, ['status' => 'fechado']);
                
                error_log("❌ Erro ao enviar template: " . ($resultado['message'] ?? 'Erro desconhecido'));
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erro ao enviar template: ' . ($resultado['message'] ?? 'Erro desconhecido'),
                    'credencial_usada' => $credencialUsada
                ]);
            }

        } catch (Exception $e) {
            error_log("❌ Erro em iniciarConversa: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            if (APP_ENV === 'development') {
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erro interno do servidor: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
            }
        }
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

            // Determinar qual API usar baseado no departamento da conversa
            $departamentoId = $conversa->departamento_id;
            $resultado = null;
            
            if ($departamentoId) {
                // Tentar usar credenciais específicas do departamento
                try {
                    $credencial = $this->departamentoHelper->obterCredencialDepartamento($departamentoId);
                    
                    if ($credencial) {
                        // ✅ NOVA ABORDAGEM: Usar SerproApi existente com credenciais do departamento
                        $serproApiDepartamento = new SerproApi();
                        
                        // Configurar temporariamente com as credenciais do departamento
                        $serproApiDepartamento->configurarComCredencial($credencial);
                        
                        $resultado = $serproApiDepartamento->enviarMensagemTexto($conversa->numero, $mensagem);
                        
                        if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                            error_log("✅ Mensagem enviada usando credenciais do departamento ID: {$departamentoId}");
                        } else {
                            error_log("⚠️ Falha com credenciais do departamento, tentando API padrão");
                            // Se falhar, tentar API padrão
                            $resultado = $this->serproApi->enviarMensagemTexto($conversa->numero, $mensagem);
                        }
                    } else {
                        // Se não há credencial específica, usar API padrão
                        $resultado = $this->serproApi->enviarMensagemTexto($conversa->numero, $mensagem);
                        error_log("⚠️ Nenhuma credencial específica encontrada para departamento {$departamentoId}, usando API padrão");
                    }
                } catch (Exception $e) {
                    error_log("❌ Erro com API do departamento: " . $e->getMessage() . " - Usando API padrão");
                    // Em caso de erro, usar API padrão
                    $resultado = $this->serproApi->enviarMensagemTexto($conversa->numero, $mensagem);
                }
            } else {
                // Se não há departamento, usar API padrão
                $resultado = $this->serproApi->enviarMensagemTexto($conversa->numero, $mensagem);
            }

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
        // Suprimir avisos de depreciação do AWS SDK
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('display_errors', 0);
        
        // Definir variável de ambiente para suprimir aviso do AWS SDK
        putenv('AWS_SUPPRESS_PHP_DEPRECATION_WARNING=true');
        
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

        // Determinar tipo de mídia
        $tipoMidia = $this->determinarTipoMidia($arquivo);

        // ✅ NOVO: Upload para MinIO
       
        $uploadMinio = MinioHelper::uploadMidia(
            file_get_contents($arquivo['tmp_name']),
            $tipoMidia,
            $arquivo['type'],
            $arquivo['name']
        );

        if (!$uploadMinio['sucesso']) {
            error_log("❌ Erro ao fazer upload para MinIO: " . $uploadMinio['erro']);
            // Continuar sem MinIO, mas logar o erro
            $caminhoMinio = null;
            $urlMinio = null;
        } else {
            $caminhoMinio = $uploadMinio['caminho_minio'];
            $urlMinio = $uploadMinio['url_minio'];
            error_log("✅ Mídia ENVIADA salva no MinIO: {$caminhoMinio}");
        }

        // Fazer upload do arquivo para API Serpro
        $departamentoId = $conversa->departamento_id;
        $uploadResult = null;
        
        if ($departamentoId) {
            // Tentar usar credenciais específicas do departamento
            try {
                $credencial = $this->departamentoHelper->obterCredencialDepartamento($departamentoId);
                
                if ($credencial) {
                    // ✅ NOVA ABORDAGEM: Usar SerproApi existente com credenciais do departamento
                    $serproApiDepartamento = new SerproApi();
                    
                    // Configurar temporariamente com as credenciais do departamento
                    $serproApiDepartamento->configurarComCredencial($credencial);
                    
                    $uploadResult = $serproApiDepartamento->uploadMidia($arquivo, $arquivo['type']);
                    
                    if ($uploadResult['status'] >= 200 && $uploadResult['status'] < 300) {
                        error_log("✅ Upload de mídia usando credenciais do departamento ID: {$departamentoId}");
                    } else {
                        error_log("⚠️ Falha no upload com credenciais do departamento, tentando API padrão");
                        // Se falhar, tentar API padrão
                        $uploadResult = $this->serproApi->uploadMidia($arquivo, $arquivo['type']);
                    }
                } else {
                    // Se não há credencial específica, usar API padrão
                    $uploadResult = $this->serproApi->uploadMidia($arquivo, $arquivo['type']);
                    error_log("⚠️ Nenhuma credencial específica encontrada para departamento {$departamentoId}, usando API padrão");
                }
            } catch (Exception $e) {
                error_log("❌ Erro com API do departamento: " . $e->getMessage() . " - Usando API padrão");
                // Em caso de erro, usar API padrão
                $uploadResult = $this->serproApi->uploadMidia($arquivo, $arquivo['type']);
            }
        } else {
            // Se não há departamento, usar API padrão
            $uploadResult = $this->serproApi->uploadMidia($arquivo, $arquivo['type']);
        }

        if ($uploadResult['status'] >= 200 && $uploadResult['status'] < 300) {
            $idMedia = $uploadResult['response']['id'];

            // Enviar mídia usando credenciais do departamento
            $resultado = null;
            
            if ($departamentoId) {
                try {
                    $credencial = $this->departamentoHelper->obterCredencialDepartamento($departamentoId);
                    
                    if ($credencial) {
                        // ✅ NOVA ABORDAGEM: Usar SerproApi existente com credenciais do departamento
                        $serproApiDepartamento = new SerproApi();
                        
                        // Configurar temporariamente com as credenciais do departamento
                        $serproApiDepartamento->configurarComCredencial($credencial);
                        
                        $resultado = $serproApiDepartamento->enviarMidia(
                            $conversa->numero, 
                            $tipoMidia, 
                            $idMedia, 
                            $caption, 
                            null, 
                            $tipoMidia === 'document' ? $arquivo['name'] : null
                        );
                        
                        if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                            error_log("✅ Mídia enviada usando credenciais do departamento ID: {$departamentoId}");
                        } else {
                            error_log("⚠️ Falha no envio com credenciais do departamento, tentando API padrão");
                            // Se falhar, tentar API padrão
                            $resultado = $this->serproApi->enviarMidia(
                                $conversa->numero, 
                                $tipoMidia, 
                                $idMedia, 
                                $caption, 
                                null, 
                                $tipoMidia === 'document' ? $arquivo['name'] : null
                            );
                        }
                    } else {
                        // Se não há credencial específica, usar API padrão
                        $resultado = $this->serproApi->enviarMidia(
                            $conversa->numero, 
                            $tipoMidia, 
                            $idMedia, 
                            $caption, 
                            null, 
                            $tipoMidia === 'document' ? $arquivo['name'] : null
                        );
                        error_log("⚠️ Nenhuma credencial específica encontrada para departamento {$departamentoId}, usando API padrão");
                    }
                } catch (Exception $e) {
                    error_log("❌ Erro com API do departamento: " . $e->getMessage() . " - Usando API padrão");
                    // Em caso de erro, usar API padrão
                    $resultado = $this->serproApi->enviarMidia(
                        $conversa->numero, 
                        $tipoMidia, 
                        $idMedia, 
                        $caption, 
                        null, 
                        $tipoMidia === 'document' ? $arquivo['name'] : null
                    );
                }
            } else {
                // Se não há departamento, usar API padrão
                $resultado = $this->serproApi->enviarMidia(
                    $conversa->numero, 
                    $tipoMidia, 
                    $idMedia, 
                    $caption, 
                    null, 
                    $tipoMidia === 'document' ? $arquivo['name'] : null
                );
            }

            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                // Salvar mensagem no banco com caminho do MinIO
                $this->salvarMensagemMidia($conversaId, $conversa->contato_id, $tipoMidia, $arquivo, $caption, $resultado, $caminhoMinio, $urlMinio);
                
                // Atualizar conversa
                $this->conversaModel->atualizarConversa($conversaId, [
                    'ultima_mensagem' => date('Y-m-d H:i:s')
                ]);

                // Limpar qualquer output antes de retornar JSON
                while (ob_get_level()) {
                    ob_end_clean();
                }

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mídia enviada com sucesso!',
                    'dados' => $resultado['response']
                ]);
            } else {
                // Limpar qualquer output antes de retornar JSON
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao enviar mídia: ' . ($resultado['error'] ?? 'Erro desconhecido')
                ]);
            }
        } else {
            // Limpar qualquer output antes de retornar JSON
            while (ob_get_level()) {
                ob_end_clean();
            }
            
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
            $mensagens = $this->mensagemModel->getMensagensPorConversa($conversaId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'mensagens' => $mensagens
            ]);
        } catch (Exception $e) {
            error_log("Erro no Chat::buscarMensagens: " . $e->getMessage());
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
        
        exit;
    }

    /**
     * [ servirMidia ] - Serve mídia do MinIO
     */
    public function servirMidia($caminhoMinio)
    {
        // Verificar se usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }

        // Decodificar caminho (caso venha encoded)
        $caminhoMinio = urldecode($caminhoMinio);

        // Carregar MinioHelper
        // require_once '/app/Libraries/MinioHelper.php';

        // Tentar baixar arquivo do MinIO
        $resultado = MinioHelper::baixarArquivo($caminhoMinio);

        if ($resultado['sucesso']) {
            // Definir headers apropriados
            header('Content-Type: ' . $resultado['content_type']);
            header('Content-Length: ' . $resultado['tamanho']);
            header('Cache-Control: public, max-age=3600'); // Cache por 1 hora
            
            // Evitar que seja interpretado como download
            if (strpos($resultado['content_type'], 'image/') === 0) {
                header('Content-Disposition: inline');
            } else {
                header('Content-Disposition: inline; filename="' . basename($caminhoMinio) . '"');
            }

            // Enviar dados
            echo $resultado['dados'];
        } else {
            // Erro ao baixar arquivo
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Arquivo não encontrado']);
        }

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

        try {
            // Buscar informações da conversa
            $conversa = $this->conversaModel->verificarConversaPorId($conversaId);
            if (!$conversa) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
                exit;
            }

            // Buscar informações do contato
            $contato = $this->contatoModel->lerContatoPorId($conversa->contato_id);
            if (!$contato) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Contato não encontrado']);
                exit;
            }

            // Enviar mensagem de encerramento
            $mensagemEnviada = false;
            $mensagemEncerramento = '';
            
            try {
                // Carregar helper de mensagens automáticas
                // require_once APPROOT . '/Libraries/MensagensAutomaticasHelper.php';
                $mensagensHelper = new MensagensAutomaticasHelper();
                
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
            } catch (Exception $e) {
                // Log do erro, mas não falhar o fechamento da conversa
                error_log("Erro ao enviar mensagem de encerramento: " . $e->getMessage());
            }

            // Fechar a conversa
            $resultado = $this->conversaModel->atualizarConversa($conversaId, [
                'status' => 'fechado'
            ]);

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Conversa fechada com sucesso',
                    'mensagem_encerramento_enviada' => $mensagemEnviada,
                    'conteudo_mensagem' => $mensagemEncerramento
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao fechar conversa']);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao fechar conversa: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
        
        exit;
    }

    /**
     * [ transferirConversa ] - Transfere conversa para outro atendente (Admin/Supervisor)
     */
    public function transferirConversa($conversaId)
    {
        // Verificar permissão (apenas admin e supervisor)
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'supervisor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores e supervisores podem transferir conversas.']);
            exit;
        }

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

        // Obter dados do JSON
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!$dados || !isset($dados['atendente_id']) || !is_numeric($dados['atendente_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID do atendente é obrigatório']);
            exit;
        }

        $atendenteId = $dados['atendente_id'];

        // Verificar se a conversa existe
        $conversa = $this->conversaModel->verificarConversaPorId($conversaId);
        if (!$conversa) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
            exit;
        }

        // Verificar se o atendente existe e é um atendente
        $atendente = $this->usuarioModel->lerUsuarioPorId($atendenteId);
        if (!$atendente || $atendente->perfil !== 'atendente') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Atendente não encontrado ou inválido']);
            exit;
        }

        // Verificar se o atendente está ativo
        if ($atendente->status !== 'ativo') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Atendente não está ativo']);
            exit;
        }

        // Transferir conversa
        $resultado = $this->conversaModel->atualizarConversa($conversaId, [
            'atendente_id' => $atendenteId,
            'status' => 'aberto'
        ]);

        if ($resultado) {
            // Log da transferência
            error_log("🔄 Conversa {$conversaId} transferida para atendente {$atendente->nome} (ID: {$atendenteId}) por {$_SESSION['usuario_nome']}");

            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'message' => "Conversa transferida com sucesso para {$atendente->nome}",
                'atendente' => [
                    'id' => $atendente->id,
                    'nome' => $atendente->nome,
                    'email' => $atendente->email
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao transferir conversa']);
        }
        
        exit;
    }

    /**
     * [ listarAtendentesDisponiveis ] - Lista atendentes disponíveis para transferência
     */
    public function listarAtendentesDisponiveis()
    {
        // Verificar permissão (apenas admin e supervisor)
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'supervisor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        // Limpar qualquer output buffer e definir headers antes de tudo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {
            $departamentoId = $_GET['departamento'] ?? null;
            
            if ($departamentoId) {
                // Buscar atendentes específicos do departamento
                $atendentes = $this->usuarioModel->getAtendentesDisponiveisPorDepartamento($departamentoId);
            } else {
                // Buscar todos os atendentes ativos
                $atendentes = $this->usuarioModel->listarPorPerfil('atendente');
                
                // Filtrar apenas atendentes ativos
                $atendentes = array_filter($atendentes, function($atendente) {
                    return $atendente->status === 'ativo';
                });
            }

            // Adicionar estatísticas de cada atendente
            $atendentesComStats = [];
            foreach ($atendentes as $atendente) {
                $conversasAtivas = $this->conversaModel->contarConversasPorAtendente($atendente->id);
                $maxConversas = $atendente->max_conversas ?? 5;
                
                $atendentesComStats[] = [
                    'id' => $atendente->id,
                    'nome' => $atendente->nome,
                    'email' => $atendente->email,
                    'status' => $atendente->status,
                    'conversas_ativas' => $conversasAtivas,
                    'max_conversas' => $maxConversas,
                    'disponivel' => $conversasAtivas < $maxConversas,
                    'departamento_id' => $departamentoId ?? null
                ];
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'atendentes' => $atendentesComStats,
                'departamento_id' => $departamentoId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao listar atendentes: ' . $e->getMessage()
            ]);
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

        // Buscar informações da conversa para obter o departamento
        $conversa = $this->verificarConversa($conversaId);
        if (!$conversa) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
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

        // ✅ NOVO: Determinar qual API usar baseado no departamento da conversa
        $departamentoId = $conversa->departamento_id;
        $serproApiDepartamento = null;
        
        if ($departamentoId) {
            // Tentar usar credenciais específicas do departamento
            try {
                $credencial = $this->departamentoHelper->obterCredencialDepartamento($departamentoId);
                
                if ($credencial) {
                    // ✅ Usar SerproApi com credenciais específicas do departamento
                    $serproApiDepartamento = new SerproApi();
                    $serproApiDepartamento->configurarComCredencial($credencial);
                    
                    error_log("✅ Verificando status usando credenciais do departamento ID: {$departamentoId}");
                } else {
                    error_log("⚠️ Nenhuma credencial específica encontrada para departamento {$departamentoId}, usando API padrão");
                }
            } catch (Exception $e) {
                error_log("❌ Erro ao configurar credenciais do departamento: " . $e->getMessage());
            }
        }
        
        // Se não conseguiu configurar credenciais específicas, usar API padrão
        if (!$serproApiDepartamento) {
            $serproApiDepartamento = $this->serproApi;
        }

        $mensagensStatus = [];
        $consultasRealizadas = 0;
        $consultasComSucesso = 0;

        // Consultar status de cada requisição via API Serpro
        foreach ($mensagensComId as $mensagem) {
            $idRequisicao = $mensagem['id_requisicao'];
            
            try {
                $consultasRealizadas++;
                $resultadoConsulta = $serproApiDepartamento->consultarStatus($idRequisicao);
                
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
            'taxa_sucesso' => $consultasRealizadas > 0 ? round(($consultasComSucesso / $consultasRealizadas) * 100, 2) : 0,
            'departamento_id' => $departamentoId
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

        $conversa = $this->conversaModel->verificarConversaPorId($conversaId);
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

        // ✅ NOVO: Buscar a mensagem para obter a conversa e departamento
        $mensagem = $this->mensagemModel->buscarPorSerproId($serproMessageId);
        $departamentoId = null;
        
        if ($mensagem) {
            // Buscar a conversa para obter o departamento
            $conversa = $this->verificarConversa($mensagem->conversa_id);
            if ($conversa) {
                $departamentoId = $conversa->departamento_id;
            }
        }

        // ✅ NOVO: Se necessário, confirmar status via API usando credenciais do departamento
        if (in_array($novoStatus, ['entregue', 'lido']) && $departamentoId) {
            try {
                $credencial = $this->departamentoHelper->obterCredencialDepartamento($departamentoId);
                
                if ($credencial) {
                    // ✅ Usar SerproApi com credenciais específicas do departamento
                    $serproApiDepartamento = new SerproApi();
                    $serproApiDepartamento->configurarComCredencial($credencial);
                    
                    // Confirmar status via API
                    $statusApi = ($novoStatus === 'lido') ? 'read' : 'delivered';
                    $resultadoApi = $serproApiDepartamento->confirmarStatusMensagem($serproMessageId, $statusApi);
                    
                    if ($resultadoApi['status'] >= 200 && $resultadoApi['status'] < 300) {
                        error_log("✅ Status confirmado via API usando credenciais do departamento ID: {$departamentoId}");
                    } else {
                        error_log("⚠️ Erro ao confirmar status via API: " . ($resultadoApi['error'] ?? 'Erro desconhecido'));
                    }
                } else {
                    error_log("⚠️ Nenhuma credencial específica encontrada para departamento {$departamentoId}");
                }
            } catch (Exception $e) {
                error_log("❌ Erro ao confirmar status via API: " . $e->getMessage());
            }
        }

        // Atualizar mensagem no banco
        $resultado = $this->mensagemModel->atualizarStatusPorSerproId($serproMessageId, $novoStatus);
        
        if ($resultado) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'departamento_id' => $departamentoId
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
     * [ criarConversaComDepartamento ] - Cria uma nova conversa com departamento
     * 
     * @param int $contatoId ID do contato
     * @param int $atendenteId ID do atendente
     * @param int $departamentoId ID do departamento
     * @return int|false ID da conversa criada ou false em caso de erro
     */
    private function criarConversaComDepartamento($contatoId, $atendenteId, $departamentoId)
    {
        $dados = [
            'contato_id' => $contatoId,
            'atendente_id' => $atendenteId,
            'sessao_id' => uniqid(),
            'status' => 'pendente',
            'prioridade' => 'normal'
        ];
        
        return $this->conversaModel->criarConversaComDepartamento($dados, $departamentoId);
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
    private function salvarMensagemMidia($conversaId, $contatoId, $tipoMidia, $arquivo, $caption, $resultado, $caminhoMinio = null, $urlMinio = null)
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
            'midia_url' => $caminhoMinio, // ✅ CORREÇÃO: Salvar apenas o caminho do MinIO, não a URL completa
            'direcao' => 'saida',
            'status_entrega' => 'enviado',
            'metadata' => json_encode([
                'tipo' => 'midia',
                'tipo_midia' => $tipoMidia,
                'arquivo_original' => $arquivo['name'],
                'tamanho' => $arquivo['size'],
                'serpro_response' => $resultado['response'],
                'minio_caminho' => $caminhoMinio, // Salvar caminho do MinIO nos metadados
                'minio_url' => $urlMinio // Salvar URL do MinIO nos metadados (para debug)
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

    /**
     * [ verificarConversaReativada ] - Verifica se uma conversa foi reativada recentemente
     */
    public function verificarConversaReativada($conversaId)
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

        $conversa = $this->verificarConversa($conversaId);
        if (!$conversa) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
            exit;
        }

        $foiReativada = $this->conversaModel->verificarConversaReativada($conversaId);
        $conversaAtiva = $this->conversaAindaAtiva($conversa);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'foi_reativada' => $foiReativada,
            'conversa_ativa' => $conversaAtiva
        ]);
        
        exit;
    }

    /**
     * [ verificarRespostaTemplate ] - Verifica se o contato respondeu ao template mais recente
     */
    public function verificarRespostaTemplate($conversaId)
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

        $conversa = $this->verificarConversa($conversaId);
        if (!$conversa) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
            exit;
        }

        $contatoRespondeu = $this->contatoJaRespondeu($conversaId);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'contato_respondeu' => $contatoRespondeu
        ]);
        
        exit;
    }

    /**
     * [ reenviarTemplate ] - Reenvia template para conversa expirada
     */
    public function reenviarTemplate()
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

            if (!$dados || empty($dados['conversa_id']) || empty($dados['template'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da conversa e template são obrigatórios']);
                exit;
            }

            $conversaId = $dados['conversa_id'];
            $template = $dados['template'];
            $parametrosRaw = $dados['parametros'] ?? [];

            // Verificar se a conversa existe
            $conversa = $this->verificarConversa($conversaId);
            
            if (!$conversa) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Conversa não encontrada']);
                exit;
            }

            // Verificar se a conversa realmente está expirada
            if ($this->conversaAindaAtiva($conversa)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Conversa ainda está ativa. Não é necessário reenviar template.']);
                exit;
            }

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

            // Enviar template via API Serpro
            $resultado = $this->serproApi->enviarTemplate($conversa->numero, $template, $parametros);

            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                // Salvar mensagem no banco
                $this->salvarMensagemTemplate($conversaId, $conversa->contato_id, $template, $parametros, $resultado);
                
                // Reativar a conversa
                $this->conversaModel->reativarConversa($conversaId);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Template reenviado com sucesso! A conversa foi reativada.',
                    'conversa_id' => $conversaId
                ]);
            } else {
                http_response_code($resultado['status']);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao reenviar template: ' . ($resultado['error'] ?? 'Erro desconhecido')
                ]);
            }

        } catch (Exception $e) {
            error_log('Erro ao reenviar template: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
        
        exit;
    }

    /**
     * [ getConversasPorDepartamentosUsuario ] - Obtém conversas do usuário por seus departamentos
     */
    private function getConversasPorDepartamentosUsuario($usuarioId)
    {
        $departamentosUsuario = $this->usuarioModel->getDepartamentosUsuario($usuarioId);
        $conversas = [];
        
        foreach ($departamentosUsuario as $departamento) {
            $conversasDepartamento = $this->conversaModel->getConversasPorDepartamento($departamento->id, ['aberto', 'pendente']);
            $conversas = array_merge($conversas, $conversasDepartamento);
        }
        
        return $conversas;
    }

    /**
     * [ getConversasPendentesPorDepartamentosUsuario ] - Obtém conversas pendentes do usuário por seus departamentos
     */
    private function getConversasPendentesPorDepartamentosUsuario($usuarioId)
    {
        $departamentosUsuario = $this->usuarioModel->getDepartamentosUsuario($usuarioId);
        $conversas = [];
        
        foreach ($departamentosUsuario as $departamento) {
            $conversasDepartamento = $this->conversaModel->getConversasPendentesPorDepartamento($departamento->id, 10);
            $conversas = array_merge($conversas, $conversasDepartamento);
        }
        
        return $conversas;
    }
}
?> 