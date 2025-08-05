<?php

/**
 * [ SERPROAPIDEPARTAMENTO ] - Classe para integração com API Serpro por departamento
 * 
 * Esta classe fornece métodos para:
 * - Usar credenciais específicas por departamento
 * - Gerenciar múltiplas credenciais
 * - Fallback automático entre credenciais
 * - Cache de tokens por credencial
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class SerproApiDepartamento
{
    private $credencialSerproModel;
    private $departamentoModel;
    private $lastError = '';
    private $credencialAtual = null;

    public function __construct()
    {
        $this->credencialSerproModel = new CredencialSerproModel();
        $this->departamentoModel = new DepartamentoModel();
    }

    /**
     * [ obterCredencialDepartamento ] - Obtém credencial para um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return object|null Credencial encontrada
     */
    public function obterCredencialDepartamento($departamentoId)
    {
        $credencial = $this->credencialSerproModel->obterCredencialAtiva($departamentoId);
        
        if (!$credencial) {
            $this->lastError = "Nenhuma credencial ativa encontrada para o departamento ID: {$departamentoId}";
            return null;
        }
        
        $this->credencialAtual = $credencial;
        return $credencial;
    }

    /**
     * [ obterCredencialPorNome ] - Obtém credencial por nome do departamento
     * 
     * @param string $nomeDepartamento Nome do departamento
     * @return object|null Credencial encontrada
     */
    public function obterCredencialPorNome($nomeDepartamento)
    {
        $departamento = $this->departamentoModel->buscarPorNome($nomeDepartamento);
        
        if (!$departamento) {
            $this->lastError = "Departamento não encontrado: {$nomeDepartamento}";
            return null;
        }
        
        return $this->obterCredencialDepartamento($departamento->id);
    }

    /**
     * [ obterTokenValido ] - Obtém token válido para uma credencial
     * 
     * @param int $credencialId ID da credencial
     * @return string|null Token válido
     */
    public function obterTokenValido($credencialId = null)
    {
        if ($credencialId) {
            return $this->credencialSerproModel->obterTokenValido($credencialId);
        }
        
        if (!$this->credencialAtual) {
            $this->lastError = "Nenhuma credencial selecionada";
            return null;
        }
        
        return $this->credencialSerproModel->obterTokenValido($this->credencialAtual->id);
    }

    /**
     * [ obterTokenDepartamento ] - Obtém token para um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return string|null Token válido
     */
    public function obterTokenDepartamento($departamentoId)
    {
        $credencial = $this->obterCredencialDepartamento($departamentoId);
        if (!$credencial) {
            return null;
        }
        
        return $this->obterTokenValido($credencial->id);
    }

    /**
     * [ getToken ] - Obtém token diretamente da API (sem cache)
     * 
     * @param int $credencialId ID da credencial
     * @return string|false Token de acesso ou false em caso de erro
     */
    public function getToken($credencialId = null)
    {
        if ($credencialId) {
            $credencial = $this->credencialSerproModel->buscarPorId($credencialId);
        } else {
            $credencial = $this->credencialAtual;
        }
        
        if (!$credencial) {
            $this->lastError = 'Credencial não encontrada';
            return false;
        }

        $url = rtrim($credencial->base_url, '/') . '/oauth2/token';
        
        $data = [
            'client_id' => $credencial->client_id,
            'client_secret' => $credencial->client_secret
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
            // Salvar token na credencial com expiração de 10 minutos
            $this->credencialSerproModel->salvarToken(
                $credencial->id, 
                $responseData['access_token'], 
                date('Y-m-d H:i:s', time() + 600) // 10 minutos = 600 segundos
            );
            
            return $responseData['access_token'];
        }

        $this->lastError = "Token não encontrado na resposta";
        return false;
    }

    /**
     * [ enviarTemplateDepartamento ] - Envia template para um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param string $destinatario Número do destinatário
     * @param string $nomeTemplate Nome do template
     * @param array $parametros Parâmetros do template
     * @param array $header Configurações do header
     * @return array Resultado da operação
     */
    public function enviarTemplateDepartamento($departamentoId, $destinatario, $nomeTemplate, $parametros = [], $header = null)
    {
        $credencial = $this->obterCredencialDepartamento($departamentoId);
        if (!$credencial) {
            return ['status' => 401, 'error' => 'Erro ao obter credencial: ' . $this->lastError];
        }
        
        return $this->enviarTemplateComCredencial($credencial, $destinatario, $nomeTemplate, $parametros, $header);
    }

    /**
     * [ enviarTemplateComCredencial ] - Envia template usando credencial específica
     * 
     * @param object $credencial Credencial a ser usada
     * @param string $destinatario Número do destinatário
     * @param string $nomeTemplate Nome do template
     * @param array $parametros Parâmetros do template
     * @param array $header Configurações do header
     * @return array Resultado da operação
     */
    public function enviarTemplateComCredencial($credencial, $destinatario, $nomeTemplate, $parametros = [], $header = null)
    {
        $token = $this->obterTokenValido($credencial->id);
        if (!$token) {
            // Tentar obter novo token
            $token = $this->getToken($credencial->id);
            if (!$token) {
                return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
            }
        }

        $url = rtrim($credencial->base_url, '/') . '/client/' . $credencial->phone_number_id . '/v2/requisicao/mensagem/template';
        
        $payload = [
            'nomeTemplate' => $nomeTemplate,
            'wabaId' => $credencial->waba_id,
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
                'filename' => "tjgo.png",
                'linkMedia' => "https://coparente.top/intranet/public/img/tjgo.png"
            ];
        }

        // Log para debug
        $this->logDebug('Enviando template', [
            'departamento' => $credencial->departamento_nome,
            'credencial' => $credencial->nome,
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
            'departamento' => $credencial->departamento_nome,
            'credencial' => $credencial->nome,
            'status' => $httpCode,
            'response' => $responseData,
            'raw_response' => $response
        ]);

        return [
            'status' => $httpCode,
            'response' => $responseData,
            'error' => $httpCode >= 400 ? $response : null,
            'departamento' => $credencial->departamento_nome,
            'credencial' => $credencial->nome
        ];
    }

    /**
     * [ enviarMensagemTextoDepartamento ] - Envia mensagem de texto para um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param string $destinatario Número do destinatário
     * @param string $mensagem Mensagem a ser enviada
     * @param string $messageId ID da mensagem (para resposta)
     * @return array Resultado da operação
     */
    public function enviarMensagemTextoDepartamento($departamentoId, $destinatario, $mensagem, $messageId = null)
    {
        $credencial = $this->obterCredencialDepartamento($departamentoId);
        if (!$credencial) {
            return ['status' => 401, 'error' => 'Erro ao obter credencial: ' . $this->lastError];
        }
        
        return $this->enviarMensagemTextoComCredencial($credencial, $destinatario, $mensagem, $messageId);
    }

    /**
     * [ enviarMensagemTextoComCredencial ] - Envia mensagem de texto usando credencial específica
     * 
     * @param object $credencial Credencial a ser usada
     * @param string $destinatario Número do destinatário
     * @param string $mensagem Mensagem a ser enviada
     * @param string $messageId ID da mensagem (para resposta)
     * @return array Resultado da operação
     */
    public function enviarMensagemTextoComCredencial($credencial, $destinatario, $mensagem, $messageId = null)
    {
        $token = $this->obterTokenValido($credencial->id);
        if (!$token) {
            $token = $this->getToken($credencial->id);
            if (!$token) {
                return ['status' => 401, 'error' => 'Erro ao obter token: ' . $this->lastError];
            }
        }

        $url = rtrim($credencial->base_url, '/') . '/client/' . $credencial->phone_number_id . '/v2/requisicao/mensagem/texto';
        
        $payload = [
            'destinatarios' => [$destinatario],
            'texto' => $mensagem
        ];

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
            'error' => $httpCode >= 400 ? $response : null,
            'departamento' => $credencial->departamento_nome,
            'credencial' => $credencial->nome
        ];
    }

    /**
     * [ testarConectividadeDepartamento ] - Testa conectividade de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return array Resultado do teste
     */
    public function testarConectividadeDepartamento($departamentoId)
    {
        $credenciais = $this->credencialSerproModel->listarPorDepartamento($departamentoId, false);
        
        $resultados = [];
        foreach ($credenciais as $credencial) {
            $resultado = $this->credencialSerproModel->testarConectividade($credencial->id);
            $resultados[] = [
                'credencial' => $credencial->nome,
                'resultado' => $resultado
            ];
        }
        
        return [
            'departamento_id' => $departamentoId,
            'total_credenciais' => count($credenciais),
            'resultados' => $resultados
        ];
    }

    /**
     * [ verificarStatusDepartamento ] - Verifica status de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return array Status do departamento
     */
    public function verificarStatusDepartamento($departamentoId)
    {
        $departamento = $this->departamentoModel->buscarPorId($departamentoId);
        if (!$departamento) {
            return [
                'departamento_encontrado' => false,
                'message' => 'Departamento não encontrado'
            ];
        }
        
        $credenciais = $this->credencialSerproModel->listarPorDepartamento($departamentoId, true);
        
        $statusCredenciais = [];
        foreach ($credenciais as $credencial) {
            $statusToken = $this->credencialSerproModel->getStatusToken($credencial->id);
            $statusCredenciais[] = [
                'credencial' => $credencial->nome,
                'status' => $credencial->status,
                'token_valido' => $statusToken['token_valido'],
                'ultimo_teste' => $statusToken['ultimo_teste'],
                'status_teste' => $statusToken['status_teste']
            ];
        }
        
        return [
            'departamento_encontrado' => true,
            'departamento' => $departamento->nome,
            'status_departamento' => $departamento->status,
            'total_credenciais' => count($credenciais),
            'credenciais' => $statusCredenciais
        ];
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
     * [ uploadMidiaDepartamento ] - Faz upload de mídia usando credenciais do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param array $arquivo Arquivo enviado
     * @param string $tipoMime Tipo MIME do arquivo
     * @return array Resultado do upload
     */
    public function uploadMidiaDepartamento($departamentoId, $arquivo, $tipoMime)
    {
        $credencial = $this->obterCredencialDepartamento($departamentoId);
        if (!$credencial) {
            return [
                'status' => 500,
                'error' => 'Credencial não encontrada para o departamento',
                'success' => false
            ];
        }
        
        return $this->uploadMidiaComCredencial($credencial, $arquivo, $tipoMime);
    }

    /**
     * [ uploadMidiaComCredencial ] - Faz upload de mídia usando credencial específica
     * 
     * @param object $credencial Credencial a ser usada
     * @param array $arquivo Arquivo enviado
     * @param string $tipoMime Tipo MIME do arquivo
     * @return array Resultado do upload
     */
    public function uploadMidiaComCredencial($credencial, $arquivo, $tipoMime)
    {
        $token = $this->obterTokenValido($credencial->id);
        if (!$token) {
            return [
                'status' => 500,
                'error' => 'Token não disponível',
                'success' => false
            ];
        }

        $url = rtrim($credencial->base_url, '/') . '/v17.0/' . $credencial->phone_number_id . '/media';
        
        $postData = [
            'messaging_product' => 'whatsapp',
            'file' => new CURLFile($arquivo['tmp_name'], $tipoMime, $arquivo['name'])
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: multipart/form-data'
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'status' => 500,
                'error' => 'Erro cURL: ' . $error,
                'success' => false
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'status' => $httpCode,
                'response' => $responseData,
                'success' => true
            ];
        } else {
            return [
                'status' => $httpCode,
                'error' => $responseData['error']['message'] ?? 'Erro desconhecido',
                'response' => $responseData,
                'success' => false
            ];
        }
    }

    /**
     * [ enviarMidiaDepartamento ] - Envia mídia usando credenciais do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param string $destinatario Número do destinatário
     * @param string $tipoMidia Tipo de mídia (image, audio, video, document)
     * @param string $idMedia ID da mídia no WhatsApp
     * @param string|null $caption Legenda da mídia
     * @param string|null $messageId ID da mensagem para resposta
     * @param string|null $nomeArquivo Nome do arquivo (para documentos)
     * @return array Resultado do envio
     */
    public function enviarMidiaDepartamento($departamentoId, $destinatario, $tipoMidia, $idMedia, $caption = null, $messageId = null, $nomeArquivo = null)
    {
        $credencial = $this->obterCredencialDepartamento($departamentoId);
        if (!$credencial) {
            return [
                'status' => 500,
                'error' => 'Credencial não encontrada para o departamento',
                'success' => false
            ];
        }
        
        return $this->enviarMidiaComCredencial($credencial, $destinatario, $tipoMidia, $idMedia, $caption, $messageId, $nomeArquivo);
    }

    /**
     * [ enviarMidiaComCredencial ] - Envia mídia usando credencial específica
     * 
     * @param object $credencial Credencial a ser usada
     * @param string $destinatario Número do destinatário
     * @param string $tipoMidia Tipo de mídia (image, audio, video, document)
     * @param string $idMedia ID da mídia no WhatsApp
     * @param string|null $caption Legenda da mídia
     * @param string|null $messageId ID da mensagem para resposta
     * @param string|null $nomeArquivo Nome do arquivo (para documentos)
     * @return array Resultado do envio
     */
    public function enviarMidiaComCredencial($credencial, $destinatario, $tipoMidia, $idMedia, $caption = null, $messageId = null, $nomeArquivo = null)
    {
        $token = $this->obterTokenValido($credencial->id);
        if (!$token) {
            return [
                'status' => 500,
                'error' => 'Token não disponível',
                'success' => false
            ];
        }

        $url = rtrim($credencial->base_url, '/') . '/v17.0/' . $credencial->phone_number_id . '/messages';
        
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $destinatario,
            'type' => $tipoMidia,
            $tipoMidia => [
                'id' => $idMedia
            ]
        ];

        if ($caption) {
            $data[$tipoMidia]['caption'] = $caption;
        }

        if ($messageId) {
            $data['context'] = [
                'message_id' => $messageId
            ];
        }

        if ($tipoMidia === 'document' && $nomeArquivo) {
            $data[$tipoMidia]['filename'] = $nomeArquivo;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'status' => 500,
                'error' => 'Erro cURL: ' . $error,
                'success' => false
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'status' => $httpCode,
                'response' => $responseData,
                'success' => true
            ];
        } else {
            return [
                'status' => $httpCode,
                'error' => $responseData['error']['message'] ?? 'Erro desconhecido',
                'response' => $responseData,
                'success' => false
            ];
        }
    }

    /**
     * [ logDebug ] - Registra informações de debug
     * 
     * @param string $acao Ação sendo executada
     * @param array $dados Dados para log
     */
    private function logDebug($acao, $dados)
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'acao' => $acao,
            'dados' => $dados
        ];
        
        $logFile = 'logs/serpro_departamento_debug.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
} 