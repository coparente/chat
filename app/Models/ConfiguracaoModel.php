<?php

/**
 * [ CONFIGURACAOMODEL ] - Model para gerenciamento de configurações do ChatSerpro
 * 
 * Esta classe gerencia:
 * - Configurações da API Serpro
 * - Mensagens automáticas
 * - Configurações gerais do sistema
 * - Testes de conectividade
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class ConfiguracaoModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ salvarConfiguracaoSerpro ] - Salva configurações da API Serpro
     * 
     * @param array $configuracoes Configurações da API Serpro
     * @return bool Sucesso da operação
     */
    public function salvarConfiguracaoSerpro($configuracoes)
    {
        // Primeiro, verificar se já existe uma configuração
        $sql = "SELECT COUNT(*) as total FROM configuracoes WHERE chave = 'serpro_api'";
        $this->db->query($sql);
        $existe = $this->db->resultado()->total > 0;

        // Criptografar dados sensíveis
        $dadosCriptografados = [
            'client_id' => $this->criptografar($configuracoes['client_id']),
            'client_secret' => $this->criptografar($configuracoes['client_secret']),
            'base_url' => $configuracoes['base_url'],
            'waba_id' => $configuracoes['waba_id'],
            'phone_number_id' => $configuracoes['phone_number_id'],
            'webhook_verify_token' => $this->criptografar($configuracoes['webhook_verify_token'])
        ];

        if ($existe) {
            // Atualizar configuração existente
            $sql = "UPDATE configuracoes SET valor = :valor, atualizado_em = NOW() WHERE chave = 'serpro_api'";
        } else {
            // Criar nova configuração
            $sql = "INSERT INTO configuracoes (chave, valor, criado_em) VALUES ('serpro_api', :valor, NOW())";
        }

        $this->db->query($sql);
        $this->db->bind(':valor', json_encode($dadosCriptografados));
        
        return $this->db->executa();
    }

    /**
     * [ buscarConfiguracaoSerpro ] - Busca configurações da API Serpro
     * 
     * @return object|null Configurações da API Serpro
     */
    public function buscarConfiguracaoSerpro()
    {
        $sql = "SELECT valor FROM configuracoes WHERE chave = 'serpro_api'";
        $this->db->query($sql);
        
        $resultado = $this->db->resultado();
        
        if (!$resultado) {
            return null;
        }

        $configuracoes = json_decode($resultado->valor, true);
        
        if (!$configuracoes) {
            return null;
        }

        // Descriptografar dados sensíveis
        return (object) [
            'client_id' => $this->descriptografar($configuracoes['client_id']),
            'client_secret' => $this->descriptografar($configuracoes['client_secret']),
            'base_url' => $configuracoes['base_url'],
            'waba_id' => $configuracoes['waba_id'],
            'phone_number_id' => $configuracoes['phone_number_id'],
            'webhook_verify_token' => $this->descriptografar($configuracoes['webhook_verify_token'])
        ];
    }

    /**
     * [ testarConectividadeSerpro ] - Testa conectividade com a API Serpro
     * 
     * @param array $configuracoes Configurações para teste
     * @return array Resultado do teste
     */
    public function testarConectividadeSerpro($configuracoes)
    {
        try {
            // Usar endpoint de token como teste de conectividade
            $url = rtrim($configuracoes['base_url'], '/') . '/oauth2/token';
            
            // Dados para obter token (seguindo o padrão da classe auxiliar)
            $data = [
                'client_id' => $configuracoes['client_id'],
                'client_secret' => $configuracoes['client_secret']
            ];
            
            // Configurar cURL
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded'
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'Erro de conexão: ' . $error
                ];
            }

            if ($httpCode === 200) {
                $dados = json_decode($response, true);
                
                if (isset($dados['access_token'])) {
                    return [
                        'success' => true,
                        'message' => 'Conectividade testada com sucesso! Token obtido.',
                        'dados' => [
                            'token_type' => $dados['token_type'] ?? 'Bearer',
                            'expires_in' => $dados['expires_in'] ?? 3600,
                            'status' => 'Autenticação OK'
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Token não encontrado na resposta da API',
                        'erro' => $response
                    ];
                }
            } else {
                $errorData = json_decode($response, true);
                $errorMessage = $errorData['error_description'] ?? $errorData['message'] ?? 'Erro desconhecido';
                
                return [
                    'success' => false,
                    'message' => 'Erro HTTP ' . $httpCode . ': ' . $errorMessage,
                    'erro' => $response
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * [ obterTokenSerpro ] - Obtém token de acesso da API Serpro
     * 
     * @param array $configuracoes Configurações da API
     * @return string Token de acesso
     */
    private function obterTokenSerpro($configuracoes)
    {
        $url = rtrim($configuracoes['base_url'], '/') . '/oauth2/token';
        
        $data = [
            'client_id' => $configuracoes['client_id'],
            'client_secret' => $configuracoes['client_secret']
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return '';
        }

        if ($httpCode === 200) {
            $dados = json_decode($response, true);
            return $dados['access_token'] ?? '';
        }

        return '';
    }

    /**
     * [ obterTokenValido ] - Obtém token válido da API Serpro com cache
     * 
     * @param array $configuracoes Configurações da API
     * @return string Token de acesso válido
     */
    public function obterTokenValido($configuracoes = null)
    {
        // Se não foram passadas configurações, buscar do banco
        if (!$configuracoes) {
            $configuracoes = $this->buscarConfiguracaoSerpro();
            if (!$configuracoes) {
                return '';
            }
            // Converter objeto para array
            $configuracoes = [
                'client_id' => $configuracoes->client_id,
                'client_secret' => $configuracoes->client_secret,
                'base_url' => $configuracoes->base_url,
                'waba_id' => $configuracoes->waba_id,
                'phone_number_id' => $configuracoes->phone_number_id,
                'webhook_verify_token' => $configuracoes->webhook_verify_token
            ];
        }

        // Verificar se existe token válido em cache
        $tokenCache = $this->buscarTokenCache();
        
        if ($tokenCache && $this->tokenAindaValido($tokenCache)) {
            return $tokenCache['access_token'];
        }

        // Token expirado ou não existe, obter novo
        $novoToken = $this->obterNovoToken($configuracoes);
        
        if ($novoToken) {
            $this->salvarTokenCache($novoToken);
            return $novoToken['access_token'];
        }

        return '';
    }

    /**
     * [ obterNovoToken ] - Obtém novo token da API Serpro
     * 
     * @param array $configuracoes Configurações da API
     * @return array|null Dados do token
     */
    private function obterNovoToken($configuracoes)
    {
        $url = rtrim($configuracoes['base_url'], '/') . '/oauth2/token';
        
        $data = [
            'client_id' => $configuracoes['client_id'],
            'client_secret' => $configuracoes['client_secret']
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            return null;
        }

        $dados = json_decode($response, true);
        
        if (!isset($dados['access_token'])) {
            return null;
        }

        // Adicionar timestamp de expiração (10 minutos = 600 segundos)
        $dados['expires_at'] = time() + 600; // 10 minutos
        $dados['created_at'] = time();
        
        return $dados;
    }

    /**
     * [ buscarTokenCache ] - Busca token em cache
     * 
     * @return array|null Token em cache
     */
    private function buscarTokenCache()
    {
        $sql = "SELECT valor FROM configuracoes WHERE chave = 'serpro_token_cache'";
        $this->db->query($sql);
        
        $resultado = $this->db->resultado();
        
        if (!$resultado) {
            return null;
        }

        $token = json_decode($resultado->valor, true);
        
        return $token ?: null;
    }

    /**
     * [ salvarTokenCache ] - Salva token em cache
     * 
     * @param array $token Dados do token
     * @return bool Sucesso da operação
     */
    private function salvarTokenCache($token)
    {
        // Verificar se já existe cache
        $sql = "SELECT COUNT(*) as total FROM configuracoes WHERE chave = 'serpro_token_cache'";
        $this->db->query($sql);
        $existe = $this->db->resultado()->total > 0;

        if ($existe) {
            $sql = "UPDATE configuracoes SET valor = :valor, atualizado_em = NOW() WHERE chave = 'serpro_token_cache'";
        } else {
            $sql = "INSERT INTO configuracoes (chave, valor, descricao, criado_em) VALUES ('serpro_token_cache', :valor, 'Cache do token JWT da API Serpro', NOW())";
        }

        $this->db->query($sql);
        $this->db->bind(':valor', json_encode($token));
        
        return $this->db->executa();
    }

    /**
     * [ tokenAindaValido ] - Verifica se token ainda é válido
     * 
     * @param array $token Dados do token
     * @return bool Token é válido
     */
    private function tokenAindaValido($token)
    {
        if (!isset($token['expires_at']) || !isset($token['access_token'])) {
            return false;
        }

        // Verificar se ainda não expirou (com margem de segurança de 1 minuto)
        $agora = time();
        $expiraEm = $token['expires_at'] - 60; // 1 minuto de margem
        
        return $agora < $expiraEm;
    }

    /**
     * [ limparTokenCache ] - Limpa cache de token
     * 
     * @return bool Sucesso da operação
     */
    public function limparTokenCache()
    {
        $sql = "DELETE FROM configuracoes WHERE chave = 'serpro_token_cache'";
        $this->db->query($sql);
        
        return $this->db->executa();
    }

    /**
     * [ getStatusToken ] - Obtém status do token atual
     * 
     * @return array Status do token
     */
    public function getStatusToken()
    {
        $tokenCache = $this->buscarTokenCache();
        
        if (!$tokenCache) {
            return [
                'existe' => false,
                'valido' => false,
                'expira_em' => null,
                'tempo_restante' => null
            ];
        }

        $valido = $this->tokenAindaValido($tokenCache);
        $tempoRestante = $tokenCache['expires_at'] - time();
        
        return [
            'existe' => true,
            'valido' => $valido,
            'expira_em' => date('Y-m-d H:i:s', $tokenCache['expires_at']),
            'tempo_restante' => $tempoRestante > 0 ? $tempoRestante : 0,
            'tempo_restante_formatado' => $this->formatarTempo($tempoRestante)
        ];
    }

    /**
     * [ formatarTempo ] - Formata tempo em segundos
     * 
     * @param int $segundos Segundos
     * @return string Tempo formatado
     */
    private function formatarTempo($segundos)
    {
        if ($segundos <= 0) {
            return 'Expirado';
        }

        $minutos = floor($segundos / 60);
        $segundos = $segundos % 60;
        
        if ($minutos > 0) {
            return sprintf('%d min %d seg', $minutos, $segundos);
        } else {
            return sprintf('%d seg', $segundos);
        }
    }

    /**
     * [ renovarToken ] - Força renovação do token
     * 
     * @return array Resultado da renovação
     */
    public function renovarToken()
    {
        $configuracoes = $this->buscarConfiguracaoSerpro();
        
        if (!$configuracoes) {
            return [
                'success' => false,
                'message' => 'Configurações da API Serpro não encontradas'
            ];
        }

        // Limpar cache atual
        $this->limparTokenCache();

        // Obter novo token
        $configArray = [
            'client_id' => $configuracoes->client_id,
            'client_secret' => $configuracoes->client_secret,
            'base_url' => $configuracoes->base_url,
            'waba_id' => $configuracoes->waba_id,
            'phone_number_id' => $configuracoes->phone_number_id,
            'webhook_verify_token' => $configuracoes->webhook_verify_token
        ];

        $novoToken = $this->obterNovoToken($configArray);
        
        if ($novoToken) {
            $this->salvarTokenCache($novoToken);
            return [
                'success' => true,
                'message' => 'Token renovado com sucesso',
                'dados' => [
                    'token_type' => $novoToken['token_type'] ?? 'Bearer',
                    'expires_at' => date('Y-m-d H:i:s', $novoToken['expires_at']),
                    'tempo_restante' => $this->formatarTempo($novoToken['expires_at'] - time())
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao renovar token'
            ];
        }
    }

    /**
     * [ salvarMensagensAutomaticas ] - Salva mensagens automáticas
     * 
     * @param array $mensagens Mensagens automáticas
     * @return bool Sucesso da operação
     */
    public function salvarMensagensAutomaticas($mensagens)
    {
        // Verificar se já existe configuração
        $sql = "SELECT COUNT(*) as total FROM configuracoes WHERE chave = 'mensagens_automaticas'";
        $this->db->query($sql);
        $existe = $this->db->resultado()->total > 0;

        if ($existe) {
            $sql = "UPDATE configuracoes SET valor = :valor, atualizado_em = NOW() WHERE chave = 'mensagens_automaticas'";
        } else {
            $sql = "INSERT INTO configuracoes (chave, valor, criado_em) VALUES ('mensagens_automaticas', :valor, NOW())";
        }

        $this->db->query($sql);
        $this->db->bind(':valor', json_encode($mensagens));
        
        return $this->db->executa();
    }

    /**
     * [ buscarMensagensAutomaticas ] - Busca mensagens automáticas
     * 
     * @return object|null Mensagens automáticas
     */
    public function buscarMensagensAutomaticas()
    {
        $sql = "SELECT valor FROM configuracoes WHERE chave = 'mensagens_automaticas'";
        $this->db->query($sql);
        
        $resultado = $this->db->resultado();
        
        if (!$resultado) {
            return null;
        }

        $mensagens = json_decode($resultado->valor, true);
        
        return $mensagens ? (object) $mensagens : null;
    }

    /**
     * [ criarTabelaConfiguracoes ] - Cria tabela de configurações se não existir
     * 
     * @return bool Sucesso da operação
     */
    public function criarTabelaConfiguracoes()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS configuracoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                chave VARCHAR(100) NOT NULL UNIQUE,
                valor TEXT NOT NULL,
                descricao TEXT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_chave (chave)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $this->db->query($sql);
        return $this->db->executa();
    }

    /**
     * [ criptografar ] - Criptografa dados sensíveis
     * 
     * @param string $dados Dados para criptografar
     * @return string Dados criptografados
     */
    private function criptografar($dados)
    {
        if (empty($dados)) {
            return '';
        }

        $chave = $this->obterChaveCriptografia();
        $iv = openssl_random_pseudo_bytes(16);
        $dadosCriptografados = openssl_encrypt($dados, 'AES-256-CBC', $chave, 0, $iv);
        
        return base64_encode($iv . $dadosCriptografados);
    }

    /**
     * [ descriptografar ] - Descriptografa dados sensíveis
     * 
     * @param string $dados Dados criptografados
     * @return string Dados descriptografados
     */
    private function descriptografar($dados)
    {
        if (empty($dados)) {
            return '';
        }

        $chave = $this->obterChaveCriptografia();
        $dados = base64_decode($dados);
        $iv = substr($dados, 0, 16);
        $dadosCriptografados = substr($dados, 16);
        
        return openssl_decrypt($dadosCriptografados, 'AES-256-CBC', $chave, 0, $iv);
    }

    /**
     * [ obterChaveCriptografia ] - Obtém chave de criptografia
     * 
     * @return string Chave de criptografia
     */
    private function obterChaveCriptografia()
    {
        // Usar uma chave baseada na configuração do sistema
        $chave = defined('SERPRO_CLIENT_SECRET') ? SERPRO_CLIENT_SECRET : 'chave_padrao_sistema';
        return hash('sha256', $chave . 'chatserpro_encryption_key');
    }

    /**
     * [ buscarConfiguracao ] - Busca uma configuração específica
     * 
     * @param string $chave Chave da configuração
     * @return mixed Valor da configuração
     */
    public function buscarConfiguracao($chave)
    {
        $sql = "SELECT valor FROM configuracoes WHERE chave = :chave";
        $this->db->query($sql);
        $this->db->bind(':chave', $chave);
        
        $resultado = $this->db->resultado();
        
        if (!$resultado) {
            return null;
        }

        return $resultado->valor;
    }

    /**
     * [ salvarConfiguracao ] - Salva uma configuração específica
     * 
     * @param string $chave Chave da configuração
     * @param mixed $valor Valor da configuração
     * @param string $descricao Descrição da configuração
     * @return bool Sucesso da operação
     */
    public function salvarConfiguracao($chave, $valor, $descricao = null)
    {
        // Verificar se já existe
        $sql = "SELECT COUNT(*) as total FROM configuracoes WHERE chave = :chave";
        $this->db->query($sql);
        $this->db->bind(':chave', $chave);
        $existe = $this->db->resultado()->total > 0;

        $valorJson = is_array($valor) || is_object($valor) ? json_encode($valor) : $valor;

        if ($existe) {
            $sql = "UPDATE configuracoes SET valor = :valor, descricao = :descricao, atualizado_em = NOW() WHERE chave = :chave";
        } else {
            $sql = "INSERT INTO configuracoes (chave, valor, descricao, criado_em) VALUES (:chave, :valor, :descricao, NOW())";
        }

        $this->db->query($sql);
        $this->db->bind(':chave', $chave);
        $this->db->bind(':valor', $valorJson);
        $this->db->bind(':descricao', $descricao);
        
        return $this->db->executa();
    }

    /**
     * [ listarConfiguracoes ] - Lista todas as configurações
     * 
     * @return array Lista de configurações
     */
    public function listarConfiguracoes()
    {
        $sql = "SELECT chave, valor, descricao, criado_em, atualizado_em FROM configuracoes ORDER BY chave";
        $this->db->query($sql);
        
        return $this->db->resultados();
    }

    /**
     * [ excluirConfiguracao ] - Exclui uma configuração
     * 
     * @param string $chave Chave da configuração
     * @return bool Sucesso da operação
     */
    public function excluirConfiguracao($chave)
    {
        $sql = "DELETE FROM configuracoes WHERE chave = :chave";
        $this->db->query($sql);
        $this->db->bind(':chave', $chave);
        
        return $this->db->executa();
    }
} 