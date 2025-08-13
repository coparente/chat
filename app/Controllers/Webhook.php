<?php

/**
 * [ WEBHOOK ] - Controlador para webhooks da API Serpro
 * 
 * Este controlador gerencia:
 * - Recebimento de mensagens via webhook
 * - Processamento de status de entrega
 * - Atualização de conversas
 * - Criação automática de contatos/conversas
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class Webhook extends Controllers
{
    private $conversaModel;
    private $mensagemModel;
    private $contatoModel;
    private $configuracaoModel;

    public function __construct()
    {
        // NÃO chamar parent::__construct() para evitar verificações de autenticação
        // Webhooks são endpoints públicos que não precisam de autenticação

        // Inicializar models diretamente
        $this->conversaModel = $this->model('ConversaModel');
        $this->mensagemModel = $this->model('MensagemModel');
        $this->contatoModel = $this->model('ContatoModel');
        $this->configuracaoModel = $this->model('ConfiguracaoModel');
    }

    /**
     * [ serpro ] - Endpoint principal para webhooks da API Serpro
     */
    public function serpro()
    {
        // Limpar qualquer output buffer e definir headers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {
            // Log da requisição para debug
            $this->logWebhook('serpro', $_SERVER['REQUEST_METHOD'], file_get_contents('php://input'));

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método não permitido']);
                exit;
            }

            // Obter dados do webhook
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                exit;
            }

            // Processar cada evento no webhook
            $resultados = [];

            // Verificar se é um array de objetos com 'body' (novo formato)
            if (is_array($dados) && isset($dados[0]['body'])) {
                foreach ($dados as $item) {
                    if (isset($item['body'])) {
                        $evento = $item['body'];
                        $resultado = $this->processarEvento($evento);
                        $resultados[] = $resultado;
                    }
                }
            }
            // Formato antigo com 'data'
            elseif (isset($dados['data']) && is_array($dados['data'])) {
                foreach ($dados['data'] as $evento) {
                    $resultado = $this->processarEvento($evento);
                    $resultados[] = $resultado;
                }
            }
            // Formato direto (sem array 'data' ou 'body')
            else {
                $resultado = $this->processarEvento($dados);
                $resultados[] = $resultado;
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Webhook processado com sucesso',
                'resultados' => $resultados
            ]);
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro no webhook Serpro: " . $e->getMessage());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }

        exit;
    }

    /**
     * [ processarEvento ] - Processa um evento individual do webhook
     */
    private function processarEvento($evento)
    {
        $resultados = [];

        // ✅ DEBUG: Log do evento completo
 
        // ✅ PRIORIDADE: Processar status de entrega PRIMEIRO
        // Isso evita que status updates sejam interpretados como mensagens
        if (isset($evento['statuses']) && is_array($evento['statuses'])) {
            foreach ($evento['statuses'] as $status) {
                $resultado = $this->processarStatusEntrega($status, $evento);
                $resultados[] = $resultado;
            }
        }

        // Processar mensagens recebidas (APÓS processar status)
        if (isset($evento['messages']) && is_array($evento['messages'])) {
            foreach ($evento['messages'] as $mensagem) {
                // ✅ VALIDAÇÃO EXTRA: Verificar se não é um objeto de status
                // Verificar se tem os campos obrigatórios E se não são valores inválidos
                $from = $mensagem['from'] ?? null;
                $id = $mensagem['id'] ?? null;
                $type = $mensagem['type'] ?? null;
                
                // ✅ CORREÇÃO: Detectar valores inválidos como "[undefined]" ou vazios
                if (!$from || !$id || !$type || 
                    $from === '[undefined]' || $id === '[undefined]' || $type === '[undefined]' ||
                    empty(trim($from)) || empty(trim($id)) || empty(trim($type))) {
                    continue; // Pular esta "mensagem"
                }

                // ✅ VALIDAÇÃO EXTRA: Verificar se não tem campos típicos de status
                if (isset($mensagem['status']) && !isset($mensagem['text']) && !isset($mensagem['image']) && !isset($mensagem['audio']) && !isset($mensagem['video']) && !isset($mensagem['document'])) {
                    continue; // Pular este status disfarçado
                }

                $resultado = $this->processarMensagemRecebida($mensagem, $evento);
                $resultados[] = $resultado;
            }
        }

        // Processar contatos (informações de perfil)
        if (isset($evento['contacts']) && is_array($evento['contacts'])) {
            foreach ($evento['contacts'] as $contato) {
                $resultado = $this->processarInformacaoContato($contato, $evento);
                $resultados[] = $resultado;
            }
        }

        return $resultados;
    }

    /**
     * [ processarMensagemRecebida ] - Processa mensagem recebida do contato
     */
    private function processarMensagemRecebida($mensagem, $evento)
    {
        try {
            // Carregar o helper do MinIO
            // require_once APPROOT . '/Libraries/MinioHelper.php';
            
            // Extrair informações da mensagem
            $numeroRemetente = $mensagem['from'] ?? null;
            $messageId = $mensagem['id'] ?? null;
            $timestamp = $mensagem['timestamp'] ?? time();
            $tipo = $mensagem['type'] ?? 'text';

            // ✅ DEBUG: Verificar se não é um evento de status sendo processado incorretamente
            // ✅ CORREÇÃO: Detectar valores inválidos mais rigorosamente
            if (empty($numeroRemetente) || empty($messageId) || empty($tipo) ||
                $numeroRemetente === '[undefined]' || $messageId === '[undefined]' || $tipo === '[undefined]' ||
                trim($numeroRemetente) === '' || trim($messageId) === '' || trim($tipo) === '') {

            
                return ['success' => false, 'message' => 'Dados da mensagem incompletos ou inválidos - possível evento de status'];
            }

            if (!$numeroRemetente || !$messageId) {
                return ['success' => false, 'message' => 'Dados da mensagem incompletos'];
            }

            // Limpar e formatar número
            $numeroLimpo = $this->limparNumero($numeroRemetente);

            // Buscar ou criar contato
            $contato = $this->buscarOuCriarContato($numeroLimpo, $evento);

            if (!$contato) {
                return ['success' => false, 'message' => 'Erro ao criar/buscar contato'];
            }

            // Buscar ou criar conversa
            $conversa = $this->buscarOuCriarConversa($contato['id']);

            if (!$conversa) {
                return ['success' => false, 'message' => 'Erro ao criar/buscar conversa'];
            }

            // Verificar se a mensagem já existe (evitar duplicatas)
            // $mensagemExistente = $this->verificarMensagemExistente($messageId);
            
            // if ($mensagemExistente) {
            //     return ['success' => true, 'message' => 'Mensagem já processada (duplicata ignorada)'];
            // }

            // Extrair conteúdo e informações de mídia baseado no tipo
            $conteudo = '';
            $midiaId = null;
            $midiaTipo = null;
            $midiaFilename = null;
            $midiaUrl = null;
            
            switch ($tipo) {
                case 'text':
                    $conteudo = $mensagem['text']['body'] ?? '';
                    break;
                    
                case 'image':
                    $midiaId = $mensagem['image']['id'] ?? '';
                    $midiaTipo = $mensagem['image']['mime_type'] ?? 'image/jpeg';
                    $conteudo = $mensagem['image']['caption'] ?? '';
                    break;
                    
                case 'audio':
                    $midiaId = $mensagem['audio']['id'] ?? '';
                    $midiaTipo = $mensagem['audio']['mime_type'] ?? 'audio/ogg';
                    $conteudo = $mensagem['audio']['text'] ?? '';
                    break;
                    
                case 'video':
                    $midiaId = $mensagem['video']['id'] ?? '';
                    $midiaTipo = $mensagem['video']['mime_type'] ?? 'video/mp4';
                    $conteudo = $mensagem['video']['caption'] ?? '';
                    break;
                    
                case 'document':
                    $midiaId = $mensagem['document']['id'] ?? '';
                    $midiaTipo = $mensagem['document']['mime_type'] ?? 'application/octet-stream';
                    $midiaFilename = $mensagem['document']['filename'] ?? 'documento';
                    $conteudo = $mensagem['document']['caption'] ?? '';
                    break;
                    
                case 'button':
                    $conteudo = $mensagem['button']['text'] ?? '';
                    break;
                    
                default:
                    $conteudo = $this->extrairConteudoMensagem($mensagem);
            }

            // Se há mídia, fazer download da API SERPRO e upload para MinIO
            if ($midiaId && in_array($tipo, ['image', 'audio', 'video', 'document'])) {
                $resultadoDownload = $this->baixarESalvarMidiaMinIO($midiaId, $tipo, $midiaTipo, $midiaFilename);
                
                if ($resultadoDownload['sucesso']) {
                    // Salvar apenas o caminho no banco, não a URL assinada
                    $caminhoMinio = $resultadoDownload['caminho_minio'];
                    $midiaFilename = $resultadoDownload['nome_arquivo'];
                    $midiaUrl = $caminhoMinio; // Salvar caminho no campo midia_url
                    
                    error_log("✅ Mídia baixada e salva no MinIO: {$caminhoMinio}");
                } else {
                    error_log("❌ Erro ao baixar/salvar mídia: " . $resultadoDownload['erro']);
                    // Continua salvando com o ID da mídia mesmo se o download falhar
                }
            }

            // Salvar mensagem no banco
            $dadosMensagem = [
                'conversa_id' => $conversa['id'],
                'contato_id' => $contato['id'],
                'serpro_message_id' => $messageId,
                'tipo' => $this->mapearTipoMensagem($tipo),
                'conteudo' => $conteudo,
                'midia_url' => $midiaUrl,
                'midia_nome' => $midiaFilename,
                'midia_tipo' => $midiaTipo,
                'direcao' => 'entrada',
                'status_entrega' => 'entregue',
                'metadata' => json_encode([
                    'webhook_data' => $mensagem,
                    'timestamp_original' => $timestamp,
                    'tipo_original' => $tipo
                ])
            ];

            $mensagemId = $this->mensagemModel->criarMensagem($dadosMensagem);

            if ($mensagemId) {
                // Atualizar conversa - apenas última mensagem, NÃO alterar status
                $this->conversaModel->atualizarConversa($conversa['id'], [
                    'ultima_mensagem' => date('Y-m-d H:i:s')
                    // NÃO alterar status aqui - manter status atual da conversa
                ]);

                // Atualizar último contato
                $this->contatoModel->atualizarUltimoContato($contato['id']);

                // CONFIRMAÇÃO AUTOMÁTICA DE ENTREGA E LEITURA
                $this->confirmarEntregaELeituraAutomatica($messageId, $numeroLimpo);

                // PROCESSAR MENSAGENS AUTOMÁTICAS
                $this->processarMensagensAutomaticas($numeroLimpo, $conversa['id'], $contato);

                // Log de sucesso
                $tipoLog = $midiaId ? "mídia ($tipo)" : "texto";
                error_log("✅ Mensagem $tipoLog salva com sucesso: ID={$messageId}, Conversa={$conversa['id']}");
                
                // Log específico para mídia
                if ($midiaId && $midiaUrl) {
                    error_log("📁 Caminho salvo no banco: {$midiaUrl}");
                }

                return [
                    'success' => true,
                    'message' => 'Mensagem processada com sucesso',
                    'mensagem_id' => $mensagemId,
                    'conversa_id' => $conversa['id']
                ];
            } else {
                return ['success' => false, 'message' => 'Erro ao salvar mensagem'];
            }
        } catch (Exception $e) {
            error_log("Erro ao processar mensagem recebida: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao processar mensagem: ' . $e->getMessage()];
        }
    }

    /**
     * [ processarMensagensAutomaticas ] - Processa e envia mensagens automáticas
     * 
     * @param string $numero Número do remetente
     * @param int $conversaId ID da conversa
     * @param array $contato Dados do contato
     */
    private function processarMensagensAutomaticas($numero, $conversaId, $contato)
    {
        try {
            // Carregar helper de mensagens automáticas
            // require_once APPROOT . '/Libraries/MensagensAutomaticasHelper.php';
            $mensagensHelper = new MensagensAutomaticasHelper();
            
            // Buscar departamento da conversa
            $departamentoId = null;
            if ($conversaId) {
                $conversa = $this->conversaModel->verificarConversaPorId($conversaId);
                $departamentoId = $conversa ? $conversa->departamento_id : null;
            }
            
            // Dados para processamento
            $dadosMensagem = [
                'numero' => $numero,
                'conversa_id' => $conversaId,
                'departamento_id' => $departamentoId,
                'nome_contato' => $contato['nome'] ?? 'Cliente',
                'conteudo' => '' // Não é necessário para mensagens automáticas
            ];
            
            // Processar mensagem recebida
            $resultado = $mensagensHelper->processarMensagemRecebida($dadosMensagem);
            
            if ($resultado['success'] && $resultado['mensagem_enviada']) {
                error_log("🤖 Mensagem automática enviada: {$resultado['tipo_mensagem']} - Departamento: {$resultado['departamento_id']} - {$resultado['conteudo_mensagem']}");
                
                // Log detalhado do resultado
                if (isset($resultado['resultado_envio'])) {
                    $envio = $resultado['resultado_envio'];
                    if ($envio['success']) {
                        error_log("✅ Mensagem automática enviada com sucesso via API Serpro");
                    } else {
                        error_log("❌ Erro ao enviar mensagem automática: " . $envio['message']);
                    }
                }
            } else {
                error_log("ℹ️ Nenhuma mensagem automática necessária: " . ($resultado['motivo'] ?? 'Motivo não especificado'));
            }
            
        } catch (Exception $e) {
            error_log("❌ Erro ao processar mensagens automáticas: " . $e->getMessage());
        }
    }

    /**
     * [ processarStatusEntrega ] - Processa status de entrega das mensagens
     */
    private function processarStatusEntrega($status, $evento)
    {
        try {
            $messageId = $status['id'] ?? null;
            $statusEntrega = $status['status'] ?? null;
            $timestamp = $status['timestamp'] ?? time();
            $numeroRemetente = $status['from'] ?? null;
 
            // Log detalhado do status recebido para monitoramento
            $this->logStatusProcessamento($status, $evento);
 

            if (!$messageId || !$statusEntrega) {
                error_log("❌ Status incompleto recebido via webhook: messageId={$messageId}, status={$statusEntrega}");
                return ['success' => false, 'message' => 'Dados de status incompletos'];
            }

            // Buscar mensagem pelo serpro_message_id (mensagens de SAÍDA do sistema)
            error_log("🔍 Buscando mensagem no banco com serpro_message_id: {$messageId}");
            $mensagem = $this->mensagemModel->buscarPorSerproId($messageId);

            if ($mensagem) {
                error_log("✅ Mensagem encontrada no banco: ID={$mensagem->id}, Status atual={$mensagem->status_entrega}");
 
                // Mapear status da API para nosso sistema
                $statusMapeado = $this->mapearStatusEntrega($statusEntrega);
                $statusAnterior = $mensagem->status_entrega;
 
                error_log("🔄 Mapeamento de status: {$statusEntrega} → {$statusMapeado}");

                // Atualizar status APENAS se mudou (otimização)
                if ($statusMapeado !== $statusAnterior) {
                    $resultadoUpdate = $this->mensagemModel->atualizarStatusEntrega($mensagem->id, $statusMapeado);
                    
                    if ($resultadoUpdate) {
                        error_log("✅ Status atualizado via webhook: messageId={$messageId}, {$statusAnterior} → {$statusMapeado}");
                    } else {
                        error_log("❌ Erro ao atualizar status no banco: messageId={$messageId}");
                    }

                    return [
                        'success' => true,
                        'message' => 'Status atualizado com sucesso',
                        'mensagem_id' => $mensagem->id,
                        'status_anterior' => $statusAnterior,
                        'status_novo' => $statusMapeado,
                        'atualizado' => true
                    ];
                } else {
                    error_log("ℹ️ Status já está atualizado: messageId={$messageId}, status={$statusMapeado}");
                    return [
                        'success' => true,
                        'message' => 'Status já está atualizado',
                        'mensagem_id' => $mensagem->id,
                        'status' => $statusMapeado,
                        'atualizado' => false
                    ];
                }
            } else {
               
                // ✅ CORREÇÃO: Retornar success=true para não gerar erro no webhook
                // Mensagens não encontradas são normais (mensagens de teste, externas, etc.)
                return [
                    'success' => true,
                    'message' => 'Mensagem não encontrada - ignorando status (normal para mensagens externas)',
                    'ignorado' => true,
                    'message_id' => $messageId,
                    'status' => $statusEntrega
                ];
            }
        } catch (Exception $e) {
            error_log("❌ Erro ao processar status de entrega via webhook: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Erro ao processar status: ' . $e->getMessage()];
        }
    }

    /**
     * [ processarInformacaoContato ] - Processa informações de contato
     */
    private function processarInformacaoContato($contato, $evento)
    {
        try {
            $waId = $contato['wa_id'] ?? null;
            $nome = $contato['profile']['name'] ?? null;

            if (!$waId) {
                return ['success' => false, 'message' => 'ID do WhatsApp não fornecido'];
            }

            // Limpar número
            $numeroLimpo = $this->limparNumero($waId);

            // Buscar contato existente
            $contatoExistente = $this->contatoModel->buscarPorNumero($numeroLimpo);

            if ($contatoExistente) {
                // Atualizar nome se fornecido e diferente
                if ($nome && $nome !== $contatoExistente->nome) {
                    $this->contatoModel->atualizarContato($contatoExistente->id, [
                        'nome' => $nome
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Contato atualizado com sucesso',
                        'contato_id' => $contatoExistente->id,
                        'nome_atualizado' => $nome
                    ];
                }
            }

            return ['success' => true, 'message' => 'Informação de contato processada'];
        } catch (Exception $e) {
            error_log("Erro ao processar informação de contato: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao processar contato: ' . $e->getMessage()];
        }
    }

    /**
     * [ buscarOuCriarContato ] - Busca ou cria um contato
     */
    private function buscarOuCriarContato($numero, $evento)
    {
        // Buscar contato existente
        $contato = $this->contatoModel->buscarPorNumero($numero);

        if ($contato) {
            return (array) $contato;
        }

        // Tentar extrair nome do evento
        $nome = null;
        if (isset($evento['contacts']) && is_array($evento['contacts'])) {
            foreach ($evento['contacts'] as $contatoInfo) {
                if (isset($contatoInfo['wa_id']) && $this->limparNumero($contatoInfo['wa_id']) === $numero) {
                    $nome = $contatoInfo['profile']['name'] ?? null;
                    break;
                }
            }
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
     * [ buscarOuCriarConversa ] - Busca ou cria uma conversa
     */
    private function buscarOuCriarConversa($contatoId)
    {
        // Buscar conversa ativa para o contato
        $conversa = $this->conversaModel->buscarConversaAtivaContato($contatoId);

        if ($conversa) {
            // Se já existe conversa ativa, retornar sem alterar status
            // O status será mantido (aberto ou pendente)
            return (array) $conversa;
        }

        // Se não existe conversa ativa, identificar departamento pelo phone_number_id do webhook
        $departamentoId = $this->identificarDepartamentoPeloWebhook();

        // Criar nova conversa como PENDENTE
        $dadosConversa = [
            'contato_id' => $contatoId,
            'sessao_id' => 1,
            'status' => 'pendente' // Número novo = pendente
        ];
        
        // Adicionar departamento_id se foi identificado
        if ($departamentoId) {
            $dadosConversa['departamento_id'] = $departamentoId;
            error_log("✅ Departamento identificado pelo webhook: {$departamentoId}");
        }

        $conversaId = $this->conversaModel->criarConversa($dadosConversa);

        if ($conversaId) {
            $novaConversa = $this->conversaModel->verificarConversaPorId($conversaId);
            return $novaConversa ? (array) $novaConversa : false;
        }

        return false;
    }
    
    /**
     * [ identificarDepartamentoPeloWebhook ] - Identifica departamento pelo phone_number_id do webhook
     * 
     * @return int|null ID do departamento
     */
    private function identificarDepartamentoPeloWebhook()
    {
        try {
            // Obter dados do webhook
            $dadosWebhook = json_decode(file_get_contents('php://input'), true);
            
            if (!$dadosWebhook) {
                error_log("❌ Dados do webhook não encontrados");
                return null;
            }
            
            // Extrair phone_number_id do webhook
            $phoneNumberId = null;
            
            // Verificar formato do webhook
            if (is_array($dadosWebhook) && isset($dadosWebhook[0]['body']['metadata']['phone_number_id'])) {
                $phoneNumberId = $dadosWebhook[0]['body']['metadata']['phone_number_id'];
            } elseif (isset($dadosWebhook['metadata']['phone_number_id'])) {
                $phoneNumberId = $dadosWebhook['metadata']['phone_number_id'];
            }
            
            if (!$phoneNumberId) {
                error_log("❌ Phone Number ID não encontrado no webhook");
                return null;
            }
            
            error_log("✅ Phone Number ID extraído do webhook: {$phoneNumberId}");
            
            // Buscar credencial por phone_number_id
            $credencialSerproModel = new CredencialSerproModel();
            $credencial = $credencialSerproModel->obterCredencialPorPhoneNumberId($phoneNumberId);
            
            if ($credencial) {
                error_log("✅ Credencial encontrada: {$credencial->nome} -> Departamento: {$credencial->departamento_id}");
                return $credencial->departamento_id;
            } else {
                error_log("❌ Nenhuma credencial encontrada para phone_number_id: {$phoneNumberId}");
                return null;
            }
            
        } catch (Exception $e) {
            error_log("❌ Erro ao identificar departamento pelo webhook: " . $e->getMessage());
            return null;
        }
    }

    /**
     * [ extrairConteudoMensagem ] - Extrai conteúdo baseado no tipo da mensagem
     */
    private function extrairConteudoMensagem($mensagem)
    {
        $tipo = $mensagem['type'] ?? 'text';

        switch ($tipo) {
            case 'text':
                return $mensagem['text']['body'] ?? '';

            case 'image':
                $id = $mensagem['image']['id'] ?? '';
                $mimeType = $mensagem['image']['mime_type'] ?? 'image/jpeg';
                $caption = $mensagem['image']['caption'] ?? '';
                return $caption ?: 'Imagem enviada';

            case 'audio':
                $id = $mensagem['audio']['id'] ?? '';
                $mimeType = $mensagem['audio']['mime_type'] ?? 'audio/ogg';
                $text = $mensagem['audio']['text'] ?? '';
                return $text ?? 'Áudio enviado';


            case 'document':
                $id = $mensagem['document']['id'] ?? '';
                $mimeType = $mensagem['document']['mime_type'] ?? 'application/pdf';
                $filename = $mensagem['document']['filename'] ?? 'documento';
                $caption = $mensagem['document']['caption'] ?? '';
                return "Documento enviado: {$filename}";

            case 'button':
                $text = $mensagem['button']['text'] ?? '';
                return $text ?: 'Botão enviado';

            case 'video':
                $caption = $mensagem['video']['caption'] ?? '';
                return $caption ?: 'Vídeo enviado';

            case 'location':
                $latitude = $mensagem['location']['latitude'] ?? '';
                $longitude = $mensagem['location']['longitude'] ?? '';
                return "Localização enviada: {$latitude}, {$longitude}";

            case 'contacts':
                return 'Contato enviado';

            default:
                return 'Mensagem não suportada';
        }
    }

    /**
     * [ extrairDadosMedia ] - Extrai dados de mídia da mensagem
     */
    private function extrairDadosMedia($dadosMedia, $tipo)
    {
        $dados = [];

        if (isset($dadosMedia['id'])) {
            $dados['midia_url'] = $dadosMedia['id']; // ID da mídia na API
        }

        if (isset($dadosMedia['mime_type'])) {
            $dados['midia_tipo'] = $dadosMedia['mime_type'];
        }

        if (isset($dadosMedia['filename'])) {
            $dados['midia_nome'] = $dadosMedia['filename'];
        }

        return $dados;
    }

    /**
     * [ mapearTipoMensagem ] - Mapeia tipo da API para nosso sistema
     */
    private function mapearTipoMensagem($tipo)
    {
        $mapeamento = [
            'text' => 'texto',
            'image' => 'imagem',
            'audio' => 'audio',
            'video' => 'video',
            'document' => 'documento',
            'location' => 'localizacao',
            'contacts' => 'contato'
        ];

        return $mapeamento[$tipo] ?? 'texto';
    }

    /**
     * [ mapearStatusEntrega ] - Mapeia status da API para nosso sistema
     */
    private function mapearStatusEntrega($status)
    {
        $mapeamento = [
            'sent' => 'enviado',
            'delivered' => 'entregue',
            'read' => 'lido',
            'failed' => 'erro'
        ];

        return $mapeamento[$status] ?? 'enviado';
    }

    /**
     * [ limparNumero ] - Limpa e formata número de telefone
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
     * [ logWebhook ] - Log das requisições de webhook para debug
     */
    private function logWebhook($tipo, $metodo, $dados)
    {
        if (APP_ENV === 'development') {
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'tipo' => $tipo,
                'metodo' => $metodo,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'dados' => $dados
            ];

            // Usar caminho relativo ao diretório atual do projeto
            $logFile = dirname(__DIR__, 2) . '/logs/webhook_' . date('Y-m-d') . '.log';

            // Criar diretório de logs se não existir
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * [ confirmarEntregaELeituraAutomatica ] - Confirma automaticamente entrega e leitura
     */
    private function confirmarEntregaELeituraAutomatica($messageId, $numero)
    {
        try {
            // Instanciar API Serpro
            $serproApi = new SerproApi();

            // Confirmar entrega imediatamente
            $this->confirmarStatusMensagem($messageId, 'delivered', $serproApi);

            // Confirmar leitura após 2-5 segundos (delay aleatório para parecer mais natural)
            $delay = rand(2, 5);

            // Para simular leitura automática, vamos usar um approach diferente
            // Podemos fazer isso de forma assíncrona ou programar para depois
            $this->programarConfirmacaoLeitura($messageId, $delay, $serproApi);
        } catch (Exception $e) {
            error_log("Erro na confirmação automática: " . $e->getMessage());
        }
    }

    /**
     * [ confirmarStatusMensagem ] - Confirma status da mensagem via API
     */
    private function confirmarStatusMensagem($messageId, $status, $serproApi)
    {
        try {
            // Usar a API real do Serpro para confirmar status
            $resultado = $serproApi->confirmarStatusMensagem($messageId, $status);

            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                $logData = [
                    'message_id' => $messageId,
                    'status' => $status,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'action' => 'auto_confirm_success',
                    'response' => $resultado['response']
                ];

                error_log("Confirmação automática enviada com sucesso: " . json_encode($logData));
                return true;
            } else {
                $logData = [
                    'message_id' => $messageId,
                    'status' => $status,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'action' => 'auto_confirm_error',
                    'error' => $resultado['error']
                ];

                error_log("Erro na confirmação automática: " . json_encode($logData));
                return false;
            }
        } catch (Exception $e) {
            error_log("Erro ao confirmar status da mensagem: " . $e->getMessage());
            return false;
        }
    }

    /**
     * [ programarConfirmacaoLeitura ] - Programa confirmação de leitura com delay
     */
    private function programarConfirmacaoLeitura($messageId, $delay, $serproApi)
    {
        try {
            // Para implementação simples, vamos usar sleep (não recomendado para produção)
            // Em produção, use um sistema de filas como Redis ou banco de dados

            // Método 1: Sleep simples (pode causar timeout)
            // sleep($delay);
            // $this->confirmarStatusMensagem($messageId, 'read', $serproApi);

            // Método 2: Salvar para processamento posterior
            $this->salvarConfirmacaoPendente($messageId, $delay);
        } catch (Exception $e) {
            error_log("Erro ao programar confirmação de leitura: " . $e->getMessage());
        }
    }

    /**
     * [ salvarConfirmacaoPendente ] - Salva confirmação pendente para processamento posterior
     */
    private function salvarConfirmacaoPendente($messageId, $delay)
    {
        try {
            // Criar arquivo de confirmação pendente
            $confirmacaoData = [
                'message_id' => $messageId,
                'status' => 'read',
                'scheduled_time' => time() + $delay,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $confirmacaoFile = dirname(__DIR__, 2) . '/logs/confirmacoes_pendentes.json';

            // Ler confirmações existentes
            $confirmacoes = [];
            if (file_exists($confirmacaoFile)) {
                $confirmacoes = json_decode(file_get_contents($confirmacaoFile), true) ?: [];
            }

            // Adicionar nova confirmação
            $confirmacoes[] = $confirmacaoData;

            // Salvar de volta
            file_put_contents($confirmacaoFile, json_encode($confirmacoes, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            error_log("Erro ao salvar confirmação pendente: " . $e->getMessage());
        }
    }

    /**
     * [ baixarESalvarMidiaMinIO ] - Baixa mídia da API SERPRO e salva no MinIO
     */
    private function baixarESalvarMidiaMinIO($midiaId, $tipo, $mimeType, $filename = null)
    {
        try {
            // Passo 1: Identificar departamento para obter credenciais corretas
            $departamentoId = $this->identificarDepartamentoParaMidia();
            
            if (!$departamentoId) {
                error_log("❌ Não foi possível identificar departamento para download de mídia");
                return [
                    'sucesso' => false,
                    'erro' => 'Departamento não identificado para download de mídia'
                ];
            }
            
            // Passo 2: Obter credenciais do departamento
            $credencialSerproModel = new CredencialSerproModel();
            $credencial = $credencialSerproModel->obterCredencialAtiva($departamentoId);
            
            if (!$credencial) {
                error_log("❌ Credencial não encontrada para departamento ID: {$departamentoId}");
                return [
                    'sucesso' => false,
                    'erro' => 'Credencial não encontrada para o departamento'
                ];
            }
            
            // Passo 3: Configurar SerproApi com credenciais específicas
            $serproApi = new SerproApi($credencial);
            $resultadoDownload = $serproApi->downloadMidia($midiaId);
            
            if ($resultadoDownload['status'] !== 200) {
                error_log("❌ Erro ao baixar mídia da API SERPRO: " . ($resultadoDownload['error'] ?? 'Status ' . $resultadoDownload['status']));
                return [
                    'sucesso' => false,
                    'erro' => 'Erro ao baixar mídia da API SERPRO: ' . ($resultadoDownload['error'] ?? 'Status ' . $resultadoDownload['status'])
                ];
            }
            
            // Passo 4: Upload para MinIO
            $resultadoUpload = MinioHelper::uploadMidia(
                $resultadoDownload['data'], 
                $tipo, 
                $mimeType, 
                $filename
            );
            
            if (!$resultadoUpload['sucesso']) {
                return [
                    'sucesso' => false,
                    'erro' => 'Erro ao fazer upload para MinIO: ' . $resultadoUpload['erro']
                ];
            }
            
            // Log de sucesso
            error_log("📁 Mídia {$midiaId} salva no MinIO: {$resultadoUpload['caminho_minio']} (Tamanho: " . 
                     number_format($resultadoUpload['tamanho'] / 1024, 2) . " KB)");
            
            return [
                'sucesso' => true,
                'caminho_minio' => $resultadoUpload['caminho_minio'],
                'url_minio' => $resultadoUpload['url_minio'],
                'nome_arquivo' => $resultadoUpload['nome_arquivo'],
                'tamanho' => $resultadoUpload['tamanho'],
                'mime_type' => $mimeType,
                'bucket' => $resultadoUpload['bucket']
            ];
            
        } catch (Exception $e) {
            error_log("❌ Erro ao baixar/salvar mídia {$midiaId}: " . $e->getMessage());
            return [
                'sucesso' => false,
                'erro' => 'Exceção: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * [ identificarDepartamentoParaMidia ] - Identifica departamento para download de mídia
     * 
     * @return int|null ID do departamento
     */
    private function identificarDepartamentoParaMidia()
    {
        try {
            // Tentar obter departamento da conversa atual
            $conversaAtual = $this->obterConversaAtual();
            if ($conversaAtual && $conversaAtual->departamento_id) {
                return $conversaAtual->departamento_id;
            }
            
            // Se não há conversa atual, usar departamento padrão
            $departamentoModel = new DepartamentoModel();
            $departamentoPadrao = $departamentoModel->buscarDepartamentoPadrao();
            
            if ($departamentoPadrao) {
                return $departamentoPadrao->id;
            }
            
            // Se não há departamento padrão, usar o primeiro ativo
            $departamentos = $departamentoModel->listarDepartamentosAtivos();
            if (!empty($departamentos)) {
                return $departamentos[0]->id;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("❌ Erro ao identificar departamento para mídia: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * [ obterConversaAtual ] - Obtém conversa atual do contexto
     * 
     * @return object|null Conversa atual
     */
    private function obterConversaAtual()
    {
        // Esta é uma implementação simplificada
        // Em um sistema real, você pode armazenar o contexto da conversa
        // ou usar outras estratégias para identificar a conversa atual
        
        // Por enquanto, retornar null para usar departamento padrão
        return null;
    }

    /**
     * [ verificarMensagemExistente ] - Verifica se mensagem já existe
     */
    private function verificarMensagemExistente($messageId)
    {
        $mensagem = $this->mensagemModel->buscarPorSerproId($messageId);
        return $mensagem !== null;
    }

    /**
     * [ debug ] - Endpoint para debug dos dados recebidos
     */
    public function debug()
    {
        // Limpar qualquer output buffer e definir headers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        // Coletar todas as informações possíveis
        $debugData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'headers' => getallheaders(),
            'raw_body' => file_get_contents('php://input'),
            'parsed_body' => json_decode(file_get_contents('php://input'), true),
            'get_params' => $_GET,
            'post_params' => $_POST,
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null
        ];

        // Salvar log detalhado
        $logFile = dirname(__DIR__, 2) . '/logs/webhook_debug_' . date('Y-m-d') . '.log';

        // Criar diretório de logs se não existir
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, json_encode($debugData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND | LOCK_EX);

        // Responder com os dados recebidos
        echo json_encode([
            'success' => true,
            'message' => 'Debug webhook - dados capturados',
            'data_received' => $debugData
        ], JSON_PRETTY_PRINT);

        exit;
    }

    /**
     * [ test ] - Endpoint para testar webhook
     */
    public function test()
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => true,
            'message' => 'Webhook está funcionando',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'data' => json_decode(file_get_contents('php://input'), true)
        ]);

        exit;
    }

    /**
     * [ debugN8n ] - Endpoint específico para debug do n8n
     */
    public function debugN8n()
    {
        // Limpar qualquer output buffer e definir headers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Tratar OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // Coletar TODOS os dados possíveis
        $debugData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'full_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'headers' => [],
            'raw_body' => file_get_contents('php://input'),
            'parsed_body' => json_decode(file_get_contents('php://input'), true),
            'get_params' => $_GET,
            'post_params' => $_POST,
            'files' => $_FILES,
            'cookies' => $_COOKIE,
            'session' => $_SESSION ?? [],
            'server_vars' => [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
                'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
                'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'HTTP_ACCEPT' => $_SERVER['HTTP_ACCEPT'] ?? null,
                'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
                'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? null,
                'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? null,
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
                'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
                'HTTP_X_REAL_IP' => $_SERVER['HTTP_X_REAL_IP'] ?? null,
                'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? null,
                'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
                'PATH_INFO' => $_SERVER['PATH_INFO'] ?? null,
                'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
                'REQUEST_TIME' => $_SERVER['REQUEST_TIME'] ?? null,
                'REQUEST_TIME_FLOAT' => $_SERVER['REQUEST_TIME_FLOAT'] ?? null,
            ]
        ];

        // Capturar todos os headers
        if (function_exists('getallheaders')) {
            $debugData['headers'] = getallheaders();
        } else {
            // Fallback para quando getallheaders não está disponível
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $debugData['headers'][$header] = $value;
                }
            }
        }

        // Salvar log super detalhado
        $logFile = dirname(__DIR__, 2) . '/logs/webhook_n8n_debug_' . date('Y-m-d_H-i-s') . '.log';

        // Criar diretório de logs se não existir
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, json_encode($debugData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);

        // Responder com dados super detalhados
        $response = [
            'success' => true,
            'message' => 'Debug N8N - dados capturados com sucesso',
            'debug_info' => [
                'endpoint' => 'debugN8n',
                'working' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => phpversion(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
            ],
            'data_received' => $debugData,
            'recommendations' => [
                'check_url' => 'Verifique se a URL está correta: https://coparente.top/chat/webhook/serpro',
                'check_method' => 'Use método POST',
                'check_content_type' => 'Use Content-Type: application/json',
                'check_body' => 'Envie dados no formato JSON válido'
            ]
        ];

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * [ logStatusProcessamento ] - Log detalhado do processamento de status
     * 
     * @param array $status Dados do status recebido
     * @param array $evento Dados completos do evento do webhook
     */
    private function logStatusProcessamento($status, $evento)
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tipo' => 'status_webhook',
            'message_id' => $status['id'] ?? 'N/A',
            'status' => $status['status'] ?? 'N/A',
            'from' => $status['from'] ?? 'N/A',
            'timestamp_original' => $status['timestamp'] ?? 'N/A',
            'metadata' => $evento['metadata'] ?? [],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
 
        // Log sempre, não apenas em development (para monitoramento)
        $logFile = dirname(__DIR__, 2) . '/logs/webhook_status_' . date('Y-m-d') . '.log';
 
        // Criar diretório de logs se não existir
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
 
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
}
