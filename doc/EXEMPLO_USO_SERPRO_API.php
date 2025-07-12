<?php

/**
 * ============================================================================
 * EXEMPLO DE USO DA CLASSE SERPROAPI
 * ============================================================================
 * 
 * Este arquivo demonstra como utilizar a classe SerproApi para integrar
 * com a API WhatsApp Business do Serpro em seus controllers e models.
 * 
 * A classe SerproApi gerencia automaticamente:
 * - Tokens JWT (obtenção, cache e renovação)
 * - Autenticação com a API
 * - Requisições HTTP
 * - Tratamento de erros
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */

// ============================================================================
// EXEMPLO 1: USO BÁSICO EM UM CONTROLLER
// ============================================================================

class ChatController extends Controllers
{
    private $serproApi;

    public function __construct()
    {
        parent::__construct();
        
        // Instanciar a API Serpro
        $this->serproApi = new SerproApi();
    }

    /**
     * Exemplo: Enviar template (primeira mensagem)
     */
    public function enviarTemplate()
    {
        // Verificar se a API está configurada
        if (!$this->serproApi->isConfigured()) {
            echo json_encode([
                'success' => false,
                'message' => 'API Serpro não configurada. Configure em /configuracoes/serpro'
            ]);
            return;
        }

        // Dados do template
        $numeroDestino = '5511999999999';
        $nomeTemplate = 'boas_vindas';
        $parametros = ['Nome do Cliente', 'Produto X'];
        
        // Enviar template
        $resultado = $this->serproApi->enviarTemplate($numeroDestino, $nomeTemplate, $parametros);
        
        if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
            echo json_encode([
                'success' => true,
                'message' => 'Template enviado com sucesso!',
                'dados' => $resultado['response']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao enviar template: ' . ($resultado['error'] ?? 'Erro desconhecido')
            ]);
        }
    }

    /**
     * Exemplo: Enviar mensagem de texto
     */
    public function enviarMensagemTexto()
    {
        // Verificar se a API está configurada
        if (!$this->serproApi->isConfigured()) {
            echo json_encode([
                'success' => false,
                'message' => 'API Serpro não configurada. Configure em /configuracoes/serpro'
            ]);
            return;
        }

        // Dados da mensagem
        $numeroDestino = '5511999999999';
        $mensagem = 'Olá! Como posso ajudá-lo hoje?';
        
        // Enviar mensagem de texto
        $resultado = $this->serproApi->enviarMensagemTexto($numeroDestino, $mensagem);
        
        if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
            echo json_encode([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso!',
                'dados' => $resultado['response']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao enviar mensagem: ' . ($resultado['error'] ?? 'Erro desconhecido')
            ]);
        }
    }

    /**
     * Exemplo: Enviar arquivo/documento
     */
    public function enviarDocumento()
    {
        // Verificar se a API está configurada
        if (!$this->serproApi->isConfigured()) {
            echo json_encode([
                'success' => false,
                'message' => 'API Serpro não configurada'
            ]);
            return;
        }

        // Primeiro, fazer upload do arquivo
        if (isset($_FILES['arquivo'])) {
            $arquivo = $_FILES['arquivo'];
            $tipoMidia = 'application/pdf'; // ou outro tipo MIME
            
            // Upload do arquivo
            $uploadResult = $this->serproApi->uploadMidia($arquivo, $tipoMidia);
            
            if ($uploadResult['status'] >= 200 && $uploadResult['status'] < 300) {
                $idMedia = $uploadResult['response']['id']; // ID retornado pelo upload
                
                // Enviar o documento
                $numeroDestino = '5511999999999';
                $resultado = $this->serproApi->enviarMidia(
                    $numeroDestino, 
                    'document', 
                    $idMedia, 
                    null, 
                    null, 
                    $arquivo['name'] // nome do arquivo
                );
                
                if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Documento enviado com sucesso!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erro ao enviar documento: ' . ($resultado['error'] ?? 'Erro desconhecido')
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro no upload: ' . ($uploadResult['error'] ?? 'Erro desconhecido')
                ]);
            }
        }
    }

    /**
     * Exemplo: Verificar status da conexão
     */
    public function verificarConexao()
    {
        $status = $this->serproApi->obterStatusConexao();
        
        echo json_encode($status);
    }

    /**
     * Exemplo: Consultar status de uma requisição
     */
    public function consultarStatusRequisicao($idRequisicao)
    {
        $status = $this->serproApi->consultarStatus($idRequisicao);
        
        echo json_encode($status);
    }
}

// ============================================================================
// EXEMPLO 2: USO EM UM MODEL
// ============================================================================

class ConversaModel
{
    private $db;
    private $serproApi;

    public function __construct()
    {
        $this->db = new Database();
        $this->serproApi = new SerproApi();
    }

    /**
     * Exemplo: Enviar mensagem automática de boas-vindas
     */
    public function enviarBoasVindas($numeroContato)
    {
        // Buscar mensagem de boas-vindas configurada
        $configuracaoModel = new ConfiguracaoModel();
        $mensagens = $configuracaoModel->buscarMensagensAutomaticas();
        
        if (!$mensagens || !$mensagens->ativar_boas_vindas) {
            return false;
        }

        $mensagem = $mensagens->mensagem_boas_vindas;
        
        // Enviar mensagem via API Serpro
        $resultado = $this->serproApi->enviarMensagemTexto($numeroContato, $mensagem);
        
        if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
            // Salvar mensagem no banco
            $this->salvarMensagemEnviada($numeroContato, $mensagem, 'boas_vindas');
            return true;
        }
        
        return false;
    }

    /**
     * Exemplo: Enviar template para novo cliente
     */
    public function enviarTemplateNovoCliente($numeroContato, $nomeCliente, $nomeProduto)
    {
        // Verificar se é primeira interação
        if (!$this->isPrimeiraMensagem($numeroContato)) {
            return false; // Só pode enviar template na primeira mensagem
        }

        $nomeTemplate = 'novo_cliente';
        $parametros = [$nomeCliente, $nomeProduto];
        
        // Enviar template via API Serpro
        $resultado = $this->serproApi->enviarTemplate($numeroContato, $nomeTemplate, $parametros);
        
        if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
            // Salvar no banco
            $this->salvarMensagemEnviada($numeroContato, "Template: $nomeTemplate", 'template');
            return true;
        }
        
        return false;
    }

    /**
     * Exemplo: Processar mensagem recebida
     */
    public function processarMensagemRecebida($numeroRemetente, $mensagem)
    {
        // Salvar mensagem no banco
        $this->salvarMensagemRecebida($numeroRemetente, $mensagem);
        
        // Verificar se é primeira mensagem do contato
        if ($this->isPrimeiraMensagem($numeroRemetente)) {
            // Enviar mensagem de boas-vindas
            $this->enviarBoasVindas($numeroRemetente);
        }
    }

    private function salvarMensagemEnviada($numero, $mensagem, $tipo)
    {
        $sql = "INSERT INTO mensagens (numero, mensagem, tipo, direcao, criado_em) VALUES (?, ?, ?, 'enviada', NOW())";
        $this->db->query($sql);
        $this->db->bind(1, $numero);
        $this->db->bind(2, $mensagem);
        $this->db->bind(3, $tipo);
        return $this->db->executa();
    }

    private function salvarMensagemRecebida($numero, $mensagem)
    {
        $sql = "INSERT INTO mensagens (numero, mensagem, tipo, direcao, criado_em) VALUES (?, ?, 'text', 'recebida', NOW())";
        $this->db->query($sql);
        $this->db->bind(1, $numero);
        $this->db->bind(2, $mensagem);
        return $this->db->executa();
    }

    private function isPrimeiraMensagem($numero)
    {
        $sql = "SELECT COUNT(*) as total FROM mensagens WHERE numero = ? AND direcao = 'recebida'";
        $this->db->query($sql);
        $this->db->bind(1, $numero);
        return $this->db->resultado()->total == 1;
    }
}

// ============================================================================
// EXEMPLO 3: USO EM UM SERVICE/HELPER
// ============================================================================

class WhatsAppService
{
    private $serproApi;

    public function __construct()
    {
        $this->serproApi = new SerproApi();
    }

    /**
     * Exemplo: Enviar mensagem com retry automático
     */
    public function enviarMensagemComRetry($numeroDestino, $mensagem, $maxTentativas = 3)
    {
        $tentativas = 0;
        
        while ($tentativas < $maxTentativas) {
            $resultado = $this->serproApi->enviarMensagemTexto($numeroDestino, $mensagem);
            
            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                return [
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso',
                    'dados' => $resultado['response']
                ];
            }
            
            // Se o token expirou, tentar renovar
            if ($resultado['status'] === 401) {
                $this->serproApi->renovarToken();
            }
            
            $tentativas++;
            sleep(1); // Aguardar 1 segundo entre tentativas
        }
        
        return [
            'success' => false,
            'message' => 'Falha após ' . $maxTentativas . ' tentativas'
        ];
    }

    /**
     * Exemplo: Enviar mensagem para múltiplos contatos
     */
    public function enviarMensagemBroadcast($numeros, $mensagem)
    {
        $resultados = [];
        
        foreach ($numeros as $numero) {
            $resultado = $this->serproApi->enviarMensagemTexto($numero, $mensagem);
            
            $resultados[] = [
                'numero' => $numero,
                'sucesso' => $resultado['status'] >= 200 && $resultado['status'] < 300,
                'message' => $resultado['status'] >= 200 && $resultado['status'] < 300 ? 
                            'Enviado' : 
                            'Erro: ' . ($resultado['error'] ?? 'Erro desconhecido')
            ];
            
            // Aguardar 1 segundo entre envios para não sobrecarregar a API
            sleep(1);
        }
        
        return $resultados;
    }

    /**
     * Exemplo: Verificar saúde da API
     */
    public function verificarSaudeAPI()
    {
        $status = $this->serproApi->obterStatusConexao();
        $statusToken = $this->serproApi->obterStatusToken();
        
        return [
            'api_configurada' => $this->serproApi->isConfigured(),
            'api_online' => $this->serproApi->verificarStatusAPI(),
            'conexao_ativa' => $status['conectado'] ?? false,
            'token_valido' => $statusToken['valido'] ?? false,
            'tempo_restante_token' => $statusToken['tempo_restante_formatado'] ?? 'N/A',
            'detalhes' => [
                'status_conexao' => $status,
                'status_token' => $statusToken
            ]
        ];
    }
}

// ============================================================================
// EXEMPLO 4: USO EM UM WEBHOOK
// ============================================================================

class WebhookController extends Controllers
{
    private $serproApi;

    public function __construct()
    {
        parent::__construct();
        $this->serproApi = new SerproApi();
    }

    /**
     * Exemplo: Processar webhook do WhatsApp
     */
    public function receberMensagem()
    {
        $input = file_get_contents('php://input');
        $dados = json_decode($input, true);
        
        if (!$dados) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }
        
        // Processar mensagens recebidas
        if (isset($dados['entry'])) {
            foreach ($dados['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        if (isset($change['value']['messages'])) {
                            foreach ($change['value']['messages'] as $mensagem) {
                                $this->processarMensagem($mensagem);
                            }
                        }
                    }
                }
            }
        }
        
        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    }

    private function processarMensagem($mensagem)
    {
        $numeroRemetente = $mensagem['from'];
        $textoMensagem = $mensagem['text']['body'] ?? '';
        $tipoMensagem = $mensagem['type'];
        
        // Salvar mensagem no banco
        $conversaModel = new ConversaModel();
        $conversaModel->processarMensagemRecebida($numeroRemetente, $textoMensagem);
        
        // Resposta automática baseada no conteúdo
        if (strtolower($textoMensagem) === 'oi' || strtolower($textoMensagem) === 'olá') {
            $resposta = 'Olá! Como posso ajudá-lo hoje?';
            $this->serproApi->enviarMensagemTexto($numeroRemetente, $resposta);
        }
    }
}

// ============================================================================
// EXEMPLO 5: UPLOAD E ENVIO DE MÍDIAS
// ============================================================================

class MidiaService
{
    private $serproApi;

    public function __construct()
    {
        $this->serproApi = new SerproApi();
    }

    /**
     * Exemplo: Upload e envio de imagem
     */
    public function enviarImagem($numeroDestino, $arquivo, $caption = null)
    {
        // Fazer upload da imagem
        $uploadResult = $this->serproApi->uploadMidia($arquivo, $arquivo['type']);
        
        if ($uploadResult['status'] >= 200 && $uploadResult['status'] < 300) {
            $idMedia = $uploadResult['response']['id'];
            
            // Enviar a imagem
            $resultado = $this->serproApi->enviarMidia($numeroDestino, 'image', $idMedia, $caption);
            
            return [
                'success' => $resultado['status'] >= 200 && $resultado['status'] < 300,
                'message' => $resultado['status'] >= 200 && $resultado['status'] < 300 ? 
                            'Imagem enviada com sucesso' : 
                            'Erro ao enviar imagem',
                'dados' => $resultado['response'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro no upload da imagem',
                'erro' => $uploadResult['error'] ?? 'Erro desconhecido'
            ];
        }
    }

    /**
     * Exemplo: Enviar diferentes tipos de mídia
     */
    public function enviarMidiaPorTipo($numeroDestino, $arquivo, $tipo)
    {
        $tiposMidia = [
            'jpg' => 'image',
            'jpeg' => 'image',
            'png' => 'image',
            'gif' => 'image',
            'pdf' => 'document',
            'doc' => 'document',
            'docx' => 'document',
            'mp4' => 'video',
            'mp3' => 'audio',
            'wav' => 'audio'
        ];
        
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $tipoMidia = $tiposMidia[$extensao] ?? 'document';
        
        // Upload
        $uploadResult = $this->serproApi->uploadMidia($arquivo, $arquivo['type']);
        
        if ($uploadResult['status'] >= 200 && $uploadResult['status'] < 300) {
            $idMedia = $uploadResult['response']['id'];
            
            // Enviar mídia
            $resultado = $this->serproApi->enviarMidia(
                $numeroDestino, 
                $tipoMidia, 
                $idMedia, 
                null, 
                null, 
                $tipoMidia === 'document' ? $arquivo['name'] : null
            );
            
            return [
                'success' => $resultado['status'] >= 200 && $resultado['status'] < 300,
                'message' => $resultado['status'] >= 200 && $resultado['status'] < 300 ? 
                            'Mídia enviada com sucesso' : 
                            'Erro ao enviar mídia',
                'tipo' => $tipoMidia,
                'dados' => $resultado['response'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro no upload da mídia',
                'erro' => $uploadResult['error'] ?? 'Erro desconhecido'
            ];
        }
    }
}

// ============================================================================
// EXEMPLO 6: MONITORAMENTO E LOGS
// ============================================================================

class ApiMonitorService
{
    private $serproApi;

    public function __construct()
    {
        $this->serproApi = new SerproApi();
    }

    /**
     * Exemplo: Monitorar status da API e renovar token se necessário
     */
    public function monitorarAPI()
    {
        $statusToken = $this->serproApi->obterStatusToken();
        
        // Se o token vai expirar em menos de 2 minutos, renovar
        if ($statusToken['tempo_restante'] < 120) {
            $renovacao = $this->serproApi->renovarToken();
            
            if ($renovacao['success']) {
                $this->log('Token renovado automaticamente');
            } else {
                $this->log('Erro ao renovar token: ' . $renovacao['message'], 'error');
            }
        }
        
        return $statusToken;
    }

    private function log($mensagem, $nivel = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$nivel}] {$mensagem}" . PHP_EOL;
        
        file_put_contents('logs/serpro_api.log', $logMessage, FILE_APPEND);
    }
}

// ============================================================================
// RESUMO DAS FUNCIONALIDADES DISPONÍVEIS
// ============================================================================

/*
MÉTODOS PRINCIPAIS DA CLASSE SERPROAPI:

1. isConfigured() - Verifica se a API está configurada
2. obterTokenValido() - Obtém token JWT válido (com cache automático)
3. getToken() - Obtém token diretamente da API (sem cache)
4. enviarTemplate($destinatario, $nomeTemplate, $parametros) - Envia template
5. enviarMensagemTexto($destinatario, $mensagem, $messageId) - Envia mensagem de texto
6. enviarMensagem($numeroDestino, $mensagem, $tipo) - Método de compatibilidade
7. enviarMidia($destinatario, $tipoMidia, $idMedia, $caption, $messageId, $filename) - Envia mídia
8. uploadMidia($arquivo, $tipoMidia) - Faz upload de mídia
9. consultarStatus($idRequisicao) - Consulta status de uma requisição
10. verificarStatusAPI() - Verifica se a API está online
11. obterStatusConexao() - Verifica status da conexão
12. renovarToken() - Força renovação do token
13. obterStatusToken() - Obtém informações do token atual
14. getLastError() - Obtém último erro ocorrido

ENDPOINTS UTILIZADOS:
- /oauth2/token - Obter token JWT
- /client/{phoneNumberId}/v2/requisicao/mensagem/template - Enviar template
- /client/{phoneNumberId}/v2/requisicao/mensagem/texto - Enviar texto
- /client/{phoneNumberId}/v2/requisicao/mensagem/media - Enviar mídia
- /client/{phoneNumberId}/v2/media - Upload de mídia
- /client/{phoneNumberId}/v2/requisicao/{idRequisicao} - Consultar status

TIPOS DE MÍDIA SUPORTADOS:
- image (jpg, jpeg, png, gif)
- document (pdf, doc, docx, txt, etc.)
- video (mp4, avi, etc.)
- audio (mp3, wav, etc.)

IMPORTANTE:
- Templates devem ser enviados apenas na primeira mensagem
- Mensagens de texto podem ser enviadas em conversas já iniciadas
- Mídias precisam ser enviadas primeiro via upload, depois via envio
- Tokens expiram em 10 minutos e são renovados automaticamente
- O sistema gerencia cache e renovação de tokens transparentemente

CONFIGURAÇÃO:
- Configure as credenciais em /configuracoes/serpro
- O sistema gerencia automaticamente tokens e cache
- Logs são salvos automaticamente quando configurado
- Monitoramento em tempo real disponível na interface
*/

?> 