<?php

/**
 * [ SERPROAPI ] - Classe utilitária para integração com a API Serpro
 * 
 * Esta classe fornece métodos para:
 * - Obter tokens JWT válidos automaticamente
 * - Fazer requisições autenticadas à API
 * - Gerenciar cache de tokens
 * - Enviar mensagens WhatsApp
 * - Enviar templates e mídias
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class SerproApi
{
    private $configuracaoModel;
    private $configuracoes;
    private $baseUrl;
    private $wabaId;
    private $phoneNumberId;
    private $clientId;
    private $clientSecret;
    private $lastError = '';

    public function __construct()
    {
        $this->configuracaoModel = new ConfiguracaoModel();
        $this->carregarConfiguracoes();
    }

    /**
     * [ carregarConfiguracoes ] - Carrega configurações da API Serpro
     * 
     * @return void
     */
    private function carregarConfiguracoes()
    {
        $this->configuracoes = $this->configuracaoModel->buscarConfiguracaoSerpro();
        
        if ($this->configuracoes) {
            $this->baseUrl = rtrim($this->configuracoes->base_url, '/');
            $this->wabaId = $this->configuracoes->waba_id;
            $this->phoneNumberId = $this->configuracoes->phone_number_id;
            $this->clientId = $this->configuracoes->client_id;
            $this->clientSecret = $this->configuracoes->client_secret;
        }
    }

    /**
     * [ isConfigured ] - Verifica se a API está configurada
     * 
     * @return bool API está configurada
     */
    public function isConfigured()
    {
        return $this->configuracoes !== null;
    }

    /**
     * [ obterTokenValido ] - Obtém token JWT válido com cache
     * 
     * @return string Token JWT válido
     */
    public function obterTokenValido()
    {
        if (!$this->isConfigured()) {
            return '';
        }

        return $this->configuracaoModel->obterTokenValido();
    }

    /**
     * [ getToken ] - Obtém token diretamente da API (sem cache)
     * 
     * @return string|false Token de acesso ou false em caso de erro
     */
    public function getToken()
    {
        if (!$this->isConfigured()) {
            $this->lastError = 'API não configurada';
            return false;
        }

        $url = $this->baseUrl . '/oauth2/token';
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = "Erro cURL: " . $error;
            return false;
        }

        if ($httpCode !== 200) {
            $this->lastError = "Erro HTTP: " . $httpCode . " - " . $response;
            return false;
        }

        $responseData = json_decode($response, true);
        
        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        }

        $this->lastError = "Token não encontrado na resposta";
        return false;
    }

    /**
     * [ enviarTemplate ] - Envia template (primeira mensagem)
     * 
     * @param string $destinatario Número do destinatário
     * @param string $nomeTemplate Nome do template
     * @param array $parametros Parâmetros do template
     * @param array $header Configurações do header (opcional)
     * @return array Resultado da operação
     */
    public function enviarTemplate($destinatario, $nomeTemplate, $parametros = [], $header = null)
    {
        $token = $this->obterTokenValido();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
        }

        $url = $this->baseUrl . '/client/' . $this->phoneNumberId . '/v2/requisicao/mensagem/template';
        
        $payload = [
            'nomeTemplate' => $nomeTemplate,
            'wabaId' => $this->wabaId,
            'destinatarios' => [$destinatario]
        ];

        // Adiciona parâmetros se fornecidos
        if (!empty($parametros)) {
            $payload['body'] = [
                'parametros' => $parametros
            ];
        }

        // Adiciona header se fornecido ou usa padrão
        if ($header !== null) {
            $payload['header'] = $header;
        } elseif (!empty($parametros)) {
            // Header padrão quando há parâmetros
            $payload['header'] = [
                // 'filename' => "logo.png",
                // 'linkMedia' => URL . "/public/img/logo.png"
                'filename' =>  "tjgo.png",
                'linkMedia' => "https://coparente.top/intranet/public/img/tjgo.png"
            ];
        }

        // Log para debug
        $this->logDebug('Enviando template', [
            'destinatario' => $destinatario,
            'template' => $nomeTemplate,
            'parametros_count' => count($parametros),
            'has_header' => isset($payload['header']),
            'payload' => $payload
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = "Erro cURL: " . $error;
            $this->logDebug('Erro cURL', ['error' => $error]);
            return ['status' => 500, 'error' => $this->lastError];
        }

        $responseData = json_decode($response, true);

        // Log da resposta
        $this->logDebug('Resposta da API', [
            'status' => $httpCode,
            'response' => $responseData,
            'raw_response' => $response
        ]);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * [ enviarMensagemTexto ] - Envia mensagem de texto (conversa já iniciada)
     * 
     * @param string $destinatario Número do destinatário
     * @param string $mensagem Mensagem a ser enviada
     * @param string $messageId ID da mensagem (para resposta)
     * @return array Resultado da operação
     */
    public function enviarMensagemTexto($destinatario, $mensagem, $messageId = null)
    {
        $token = $this->obterTokenValido();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
        }

        $url = $this->baseUrl . '/client/' . $this->phoneNumberId . '/v2/requisicao/mensagem/texto';
        
        $payload = [
            'destinatario' => $destinatario,
            'body' => $mensagem,
            'preview_url' => false
        ];

        // Adiciona contexto se fornecido (resposta a uma mensagem)
        if ($messageId) {
            $payload['message_id'] = $messageId;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => $this->lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * [ enviarMensagem ] - Método de compatibilidade (usa enviarMensagemTexto)
     * 
     * @param string $numeroDestino Número do destinatário
     * @param string $mensagem Mensagem a ser enviada
     * @param string $tipo Tipo da mensagem (não usado na nova API)
     * @return array Resultado da operação
     */
    public function enviarMensagem($numeroDestino, $mensagem, $tipo = 'text')
    {
        $resultado = $this->enviarMensagemTexto($numeroDestino, $mensagem);
        
        // Converter para formato compatível com a API antiga
        return [
            'success' => $resultado['status'] >= 200 && $resultado['status'] < 300,
            'message' => $resultado['status'] >= 200 && $resultado['status'] < 300 ? 
                        'Mensagem enviada com sucesso' : 
                        'Erro ao enviar mensagem: ' . ($resultado['error'] ?? 'Erro desconhecido'),
            'dados' => $resultado['response'] ?? null,
            'http_code' => $resultado['status']
        ];
    }

    /**
     * [ enviarMidia ] - Envia mídia (imagem, documento, etc.)
     * 
     * @param string $destinatario Número do destinatário
     * @param string $tipoMidia Tipo da mídia (image, document, video, audio)
     * @param string $idMedia ID da mídia (obtido via upload)
     * @param string $caption Legenda (opcional)
     * @param string $messageId ID da mensagem (para resposta)
     * @param string $filename Nome do arquivo (para documentos)
     * @return array Resultado da operação
     */
    public function enviarMidia($destinatario, $tipoMidia, $idMedia, $caption = null, $messageId = null, $filename = null)
    {
        $token = $this->obterTokenValido();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
        }

        $url = $this->baseUrl . '/client/' . $this->phoneNumberId . '/v2/requisicao/mensagem/media';
        
        // Para documentos, tentar diferentes variações de campo filename
        if ($tipoMidia === 'document' && $filename) {
            $variationsToTry = [
                ['filename' => $filename],
                ['fileName' => $filename], 
                ['name' => $filename],
                ['nomeArquivo' => $filename]
            ];
            
            foreach ($variationsToTry as $index => $variation) {
                $payload = [
                    'destinatario' => $destinatario,
                    'tipoMedia' => $tipoMidia,
                    'idMedia' => $idMedia
                ];
                
                // Adicionar a variação atual
                $payload = array_merge($payload, $variation);
                
                // Adicionar message_id se fornecido
                if ($messageId) {
                    $payload['message_id'] = $messageId;
                }
                
                $resultado = $this->executarRequisicaoMidia($url, $token, $payload);
                
                if ($resultado['status'] === 200 || $resultado['status'] === 201) {
                    return $resultado;
                }
            }
            
            return $resultado; // Retorna o último resultado se nenhuma variação funcionou
        }
        
        // Para outros tipos de mídia (imagem, vídeo, áudio)
        $payload = [
            'destinatario' => $destinatario,
            'tipoMedia' => $tipoMidia,
            'idMedia' => $idMedia
        ];

        // Adicionar caption se fornecido
        if ($caption && in_array($tipoMidia, ['image', 'video', 'audio'])) {
            $payload['caption'] = $caption;
        }

        if ($messageId) {
            $payload['message_id'] = $messageId;
        }
        
        return $this->executarRequisicaoMidia($url, $token, $payload);
    }

    /**
     * [ executarRequisicaoMidia ] - Executa a requisição de mídia
     * 
     * @param string $url URL da requisição
     * @param string $token Token de acesso
     * @param array $payload Dados da requisição
     * @return array Resultado da operação
     */
    private function executarRequisicaoMidia($url, $token, $payload)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => $this->lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * [ uploadMidia ] - Faz upload de mídia para a Meta
     * 
     * @param array $arquivo Dados do arquivo ($_FILES)
     * @param string $tipoMidia Tipo da mídia
     * @return array Resultado da operação
     */
    public function uploadMidia($arquivo, $tipoMidia)
    {
        $token = $this->obterTokenValido();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
        }

        $url = $this->baseUrl . '/client/' . $this->phoneNumberId . '/v2/media';
        
        // Verificar se o arquivo existe
        if (!file_exists($arquivo['tmp_name'])) {
            return ['status' => 400, 'error' => 'Arquivo temporário não encontrado'];
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'mediaType' => $tipoMidia,
            'file' => new CURLFile($arquivo['tmp_name'], $tipoMidia, $arquivo['name'])
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => $this->lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * [ consultarStatus ] - Consulta status de uma requisição
     * 
     * @param string $idRequisicao ID da requisição
     * @return array Resultado da consulta
     */
    public function consultarStatus($idRequisicao)
    {
        $token = $this->obterTokenValido();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
        }

        $url = $this->baseUrl . '/client/' . $this->phoneNumberId . '/v2/requisicao/' . $idRequisicao;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => $this->lastError];
        }

        $responseData = json_decode($response, true);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * [ buscarMensagens ] - Método de compatibilidade (não implementado na nova API)
     * 
     * @param string $numeroRemetente Número do remetente (opcional)
     * @return array Lista de mensagens
     */
    public function buscarMensagens($numeroRemetente = null)
    {
        // A nova API do Serpro não tem endpoint para buscar mensagens
        // As mensagens são recebidas via webhook
        return [
            'success' => false,
            'message' => 'Busca de mensagens não disponível. Use webhooks para receber mensagens.'
        ];
    }

    /**
     * [ obterPerfilContato ] - Método de compatibilidade (não implementado na nova API)
     * 
     * @param string $numeroContato Número do contato
     * @return array Dados do perfil
     */
    public function obterPerfilContato($numeroContato)
    {
        // A nova API do Serpro não tem endpoint específico para perfil de contato
        return [
            'success' => false,
            'message' => 'Obtenção de perfil de contato não disponível na API Serpro.'
        ];
    }

    /**
     * [ confirmarStatusMensagem ] - Confirma status de uma mensagem (entrega/leitura)
     * 
     * @param string $messageId ID da mensagem
     * @param string $status Status a confirmar ('delivered' ou 'read')
     * @return array Resultado da operação
     */
    public function confirmarStatusMensagem($messageId, $status)
    {
        $token = $this->obterTokenValido();
        if (!$token) {
            return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
        }

        // Endpoint para confirmar status da mensagem
        $url = $this->baseUrl . '/client/' . $this->phoneNumberId . '/v2/requisicao/mensagem/status';
        
        $payload = [
            'message_id' => $messageId,
            'status' => $status
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->lastError = "Erro cURL: " . $error;
            return ['status' => 500, 'error' => $this->lastError];
        }

        $responseData = json_decode($response, true);

        // Log da confirmação
        $this->logDebug('Confirmação de status', [
            'message_id' => $messageId,
            'status' => $status,
            'http_code' => $httpCode,
            'response' => $responseData
        ]);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null
        ];
    }

    /**
     * [ marcarComoLida ] - Marca mensagem como lida (método atualizado)
     * 
     * @param string $messageId ID da mensagem
     * @return array Resultado da operação
     */
    public function marcarComoLida($messageId)
    {
        return $this->confirmarStatusMensagem($messageId, 'read');
    }

    /**
     * [ marcarComoEntregue ] - Marca mensagem como entregue
     * 
     * @param string $messageId ID da mensagem
     * @return array Resultado da operação
     */
    public function marcarComoEntregue($messageId)
    {
        return $this->confirmarStatusMensagem($messageId, 'delivered');
    }

    /**
     * [ verificarStatusAPI ] - Verifica se a API está online
     * 
     * @return bool API está online
     */
    public function verificarStatusAPI()
    {
        $token = $this->getToken();
        return $token !== false;
    }

    /**
     * [ obterStatusConexao ] - Obtém status da conexão com a API
     * 
     * @return array Status da conexão
     */
    public function obterStatusConexao()
    {
        if (!$this->isConfigured()) {
            return [
                'conectado' => false,
                'message' => 'API não configurada'
            ];
        }

        $token = $this->obterTokenValido();
        
        if (empty($token)) {
            return [
                'conectado' => false,
                'message' => 'Token inválido ou expirado'
            ];
        }

        return [
            'conectado' => true,
            'message' => 'Conexão ativa',
            'token_valido' => true,
            'configuracoes' => [
                'base_url' => $this->baseUrl,
                'waba_id' => $this->wabaId,
                'phone_number_id' => $this->phoneNumberId
            ]
        ];
    }

    /**
     * [ renovarToken ] - Força renovação do token
     * 
     * @return array Resultado da renovação
     */
    public function renovarToken()
    {
        return $this->configuracaoModel->renovarToken();
    }

    /**
     * [ obterStatusToken ] - Obtém status do token atual
     * 
     * @return array Status do token
     */
    public function obterStatusToken()
    {
        return $this->configuracaoModel->getStatusToken();
    }

    /**
     * [ getLastError ] - Obtém o último erro ocorrido
     * 
     * @return string Último erro
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * [ logDebug ] - Registra informações de debug
     * 
     * @param string $titulo Título do log
     * @param array $dados Dados para logging
     */
    private function logDebug($titulo, $dados)
    {
        if (defined('DEBUG') && DEBUG) {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'titulo' => $titulo,
                'dados' => $dados
            ];
            
            $logFile = 'logs/serpro_debug.log';
            $logDir = dirname($logFile);
            
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * [ enviarTemplatePersonalizado ] - Envia template com configurações personalizadas
     * 
     * @param string $destinatario Número do destinatário
     * @param string $nomeTemplate Nome do template
     * @param array $parametros Parâmetros do template
     * @param array $opcoes Opções adicionais (header, etc.)
     * @return array Resultado da operação
     */
    public function enviarTemplatePersonalizado($destinatario, $nomeTemplate, $parametros = [], $opcoes = [])
    {
        $header = null;
        
        // Configurar header se especificado
        if (isset($opcoes['header'])) {
            $header = $opcoes['header'];
        } elseif (isset($opcoes['imagem'])) {
            $header = [
                'filename' => $opcoes['imagem']['filename'] ?? 'imagem.png',
                'linkMedia' => $opcoes['imagem']['url']
            ];
        }
        
        return $this->enviarTemplate($destinatario, $nomeTemplate, $parametros, $header);
    }
}
?> 