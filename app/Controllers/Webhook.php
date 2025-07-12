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
        parent::__construct();
        
        // Inicializar models
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
            
            if (isset($dados['data']) && is_array($dados['data'])) {
                foreach ($dados['data'] as $evento) {
                    $resultado = $this->processarEvento($evento);
                    $resultados[] = $resultado;
                }
            } else {
                // Formato direto (sem array 'data')
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
        
        // Processar mensagens recebidas
        if (isset($evento['messages']) && is_array($evento['messages'])) {
            foreach ($evento['messages'] as $mensagem) {
                $resultado = $this->processarMensagemRecebida($mensagem, $evento);
                $resultados[] = $resultado;
            }
        }
        
        // Processar status de entrega
        if (isset($evento['statuses']) && is_array($evento['statuses'])) {
            foreach ($evento['statuses'] as $status) {
                $resultado = $this->processarStatusEntrega($status, $evento);
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
            // Extrair informações da mensagem
            $numeroRemetente = $mensagem['from'] ?? null;
            $messageId = $mensagem['id'] ?? null;
            $timestamp = $mensagem['timestamp'] ?? time();
            $tipo = $mensagem['type'] ?? 'text';
            
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
            
            // Extrair conteúdo baseado no tipo
            $conteudo = $this->extrairConteudoMensagem($mensagem);
            
            // Salvar mensagem no banco
            $dadosMensagem = [
                'conversa_id' => $conversa['id'],
                'contato_id' => $contato['id'],
                'serpro_message_id' => $messageId,
                'tipo' => $this->mapearTipoMensagem($tipo),
                'conteudo' => $conteudo,
                'direcao' => 'entrada',
                'status_entrega' => 'entregue',
                'metadata' => json_encode([
                    'webhook_data' => $mensagem,
                    'timestamp_original' => $timestamp,
                    'tipo_original' => $tipo
                ])
            ];
            
            // Adicionar dados de mídia se necessário
            if (isset($mensagem[$tipo])) {
                $dadosMensagem = array_merge($dadosMensagem, $this->extrairDadosMedia($mensagem[$tipo], $tipo));
            }
            
            $mensagemId = $this->mensagemModel->criarMensagem($dadosMensagem);
            
            if ($mensagemId) {
                // Atualizar conversa
                $this->conversaModel->atualizarConversa($conversa['id'], [
                    'ultima_mensagem' => date('Y-m-d H:i:s'),
                    'status' => 'aberto' // Reativar conversa se estava fechada
                ]);
                
                // Atualizar último contato
                $this->contatoModel->atualizarUltimoContato($contato['id']);
                
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
     * [ processarStatusEntrega ] - Processa status de entrega das mensagens
     */
    private function processarStatusEntrega($status, $evento)
    {
        try {
            $messageId = $status['id'] ?? null;
            $statusEntrega = $status['status'] ?? null;
            $timestamp = $status['timestamp'] ?? time();
            
            if (!$messageId || !$statusEntrega) {
                return ['success' => false, 'message' => 'Dados de status incompletos'];
            }
            
            // Buscar mensagem pelo ID do Serpro
            $mensagem = $this->mensagemModel->buscarPorSerproId($messageId);
            
            if ($mensagem) {
                // Mapear status da API para nosso sistema
                $statusMapeado = $this->mapearStatusEntrega($statusEntrega);
                
                // Atualizar status da mensagem
                $this->mensagemModel->atualizarStatusEntrega($mensagem->id, $statusMapeado);
                
                return [
                    'success' => true,
                    'message' => 'Status atualizado com sucesso',
                    'mensagem_id' => $mensagem->id,
                    'status' => $statusMapeado
                ];
            } else {
                return ['success' => false, 'message' => 'Mensagem não encontrada'];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao processar status de entrega: " . $e->getMessage());
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
            return (array) $conversa;
        }
        
        // Criar nova conversa
        $dadosConversa = [
            'contato_id' => $contatoId,
            'sessao_id' => 1,
            'status' => 'pendente'
        ];
        
        $conversaId = $this->conversaModel->criarConversa($dadosConversa);
        
        if ($conversaId) {
            $novaConversa = $this->conversaModel->verificarConversaPorId($conversaId);
            return $novaConversa ? (array) $novaConversa : false;
        }
        
        return false;
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
                $caption = $mensagem['image']['caption'] ?? '';
                return $caption ?: 'Imagem enviada';
                
            case 'audio':
                return 'Áudio enviado';
                
            case 'video':
                $caption = $mensagem['video']['caption'] ?? '';
                return $caption ?: 'Vídeo enviado';
                
            case 'document':
                $filename = $mensagem['document']['filename'] ?? 'documento';
                return "Documento enviado: {$filename}";
                
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
            
            $logFile = ROOT . '/logs/webhook_' . date('Y-m-d') . '.log';
            
            // Criar diretório de logs se não existir
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND | LOCK_EX);
        }
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
}
?> 