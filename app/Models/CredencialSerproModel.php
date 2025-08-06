<?php

/**
 * [ CREDENCIAISERPROMODEL ] - Model para gerenciar credenciais Serpro por departamento
 * 
 * Esta classe gerencia:
 * - CRUD de credenciais Serpro
 * - Tokens JWT por credencial
 * - Testes de conectividade
 * - Configurações específicas por departamento
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class CredencialSerproModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ listarPorDepartamento ] - Lista credenciais de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param bool $apenasAtivas Se true, retorna apenas credenciais ativas
     * @return array Lista de credenciais
     */
    public function listarPorDepartamento($departamentoId, $apenasAtivas = true)
    {
        $sql = "SELECT 
                    cs.*,
                    d.nome as departamento_nome,
                    d.cor as departamento_cor
                FROM credenciais_serpro_departamento cs
                JOIN departamentos d ON cs.departamento_id = d.id
                WHERE cs.departamento_id = :departamento_id";
        
        if ($apenasAtivas) {
            $sql .= " AND cs.status = 'ativo'";
        }
        
        $sql .= " ORDER BY cs.prioridade ASC, cs.nome ASC";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        
        return $this->db->resultados();
    }

    /**
     * [ buscarPorId ] - Busca credencial por ID
     * 
     * @param int $id ID da credencial
     * @return object|null Credencial encontrada
     */
    public function buscarPorId($id)
    {
        $sql = "SELECT 
                    cs.*,
                    d.nome as departamento_nome,
                    d.cor as departamento_cor
                FROM credenciais_serpro_departamento cs
                JOIN departamentos d ON cs.departamento_id = d.id
                WHERE cs.id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->resultado();
    }

    /**
     * [ obterCredencialAtiva ] - Obtém credencial ativa de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return object|null Credencial ativa
     */
    public function obterCredencialAtiva($departamentoId)
    {
        $sql = "SELECT 
                    cs.*,
                    d.nome as departamento_nome,
                    d.cor as departamento_cor
                FROM credenciais_serpro_departamento cs
                JOIN departamentos d ON cs.departamento_id = d.id
                WHERE cs.departamento_id = :departamento_id 
                AND cs.status = 'ativo'
                ORDER BY cs.prioridade ASC
                LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        
        return $this->db->resultado();
    }

    /**
     * [ obterCredencialPorPhoneNumberId ] - Obtém credencial por phone_number_id
     * 
     * @param string $phoneNumberId Phone Number ID da credencial
     * @return object|null Credencial encontrada
     */
    public function obterCredencialPorPhoneNumberId($phoneNumberId)
    {
        $sql = "SELECT 
                    cs.*,
                    d.nome as departamento_nome,
                    d.cor as departamento_cor
                FROM credenciais_serpro_departamento cs
                JOIN departamentos d ON cs.departamento_id = d.id
                WHERE cs.phone_number_id = :phone_number_id 
                AND cs.status = 'ativo'
                ORDER BY cs.prioridade ASC
                LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':phone_number_id', $phoneNumberId);
        
        return $this->db->resultado();
    }

    /**
     * [ criar ] - Cria nova credencial
     * 
     * @param array $dados Dados da credencial
     * @return bool Sucesso da operação
     */
    public function criar($dados)
    {
        $sql = "INSERT INTO credenciais_serpro_departamento 
                (departamento_id, nome, client_id, client_secret, base_url, waba_id, phone_number_id, webhook_verify_token, status, prioridade, configuracoes) 
                VALUES (:departamento_id, :nome, :client_id, :client_secret, :base_url, :waba_id, :phone_number_id, :webhook_verify_token, :status, :prioridade, :configuracoes)";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $dados['departamento_id']);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':client_id', $dados['client_id']);
        $this->db->bind(':client_secret', $dados['client_secret']);
        $this->db->bind(':base_url', $dados['base_url'] ?? 'https://api.whatsapp.serpro.gov.br');
        $this->db->bind(':waba_id', $dados['waba_id']);
        $this->db->bind(':phone_number_id', $dados['phone_number_id']);
        $this->db->bind(':webhook_verify_token', $dados['webhook_verify_token'] ?? null);
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        $this->db->bind(':prioridade', $dados['prioridade'] ?? 0);
        
        // Processar configurações como JSON válido
        $configuracoes = $dados['configuracoes'] ?? '{"timeout": 30, "retry_attempts": 3}';
        if (is_string($configuracoes)) {
            // Se já é uma string JSON válida, usar como está
            if (json_decode($configuracoes) !== null) {
                $this->db->bind(':configuracoes', $configuracoes);
            } else {
                // Se não é JSON válido, usar padrão
                $this->db->bind(':configuracoes', '{"timeout": 30, "retry_attempts": 3}');
            }
        } else {
            // Se é array, converter para JSON
            $this->db->bind(':configuracoes', json_encode($configuracoes));
        }
        
        return $this->db->executa();
    }

    /**
     * [ atualizar ] - Atualiza credencial
     * 
     * @param int $id ID da credencial
     * @param array $dados Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function atualizar($id, $dados)
    {
        $sql = "UPDATE credenciais_serpro_departamento SET 
                nome = :nome, 
                client_id = :client_id, 
                client_secret = :client_secret, 
                base_url = :base_url, 
                waba_id = :waba_id, 
                phone_number_id = :phone_number_id, 
                webhook_verify_token = :webhook_verify_token, 
                status = :status,
                prioridade = :prioridade, 
                configuracoes = :configuracoes
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':client_id', $dados['client_id']);
        $this->db->bind(':client_secret', $dados['client_secret']);
        $this->db->bind(':base_url', $dados['base_url'] ?? 'https://api.whatsapp.serpro.gov.br');
        $this->db->bind(':waba_id', $dados['waba_id']);
        $this->db->bind(':phone_number_id', $dados['phone_number_id']);
        $this->db->bind(':webhook_verify_token', $dados['webhook_verify_token'] ?? null);
        $this->db->bind(':status', $dados['status'] ?? 'ativo');
        $this->db->bind(':prioridade', $dados['prioridade'] ?? 0);
        
        // Processar configurações como JSON válido
        $configuracoes = $dados['configuracoes'] ?? '{"timeout": 30, "retry_attempts": 3}';
        if (is_string($configuracoes)) {
            // Se já é uma string JSON válida, usar como está
            if (json_decode($configuracoes) !== null) {
                $this->db->bind(':configuracoes', $configuracoes);
            } else {
                // Se não é JSON válido, usar padrão
                $this->db->bind(':configuracoes', '{"timeout": 30, "retry_attempts": 3}');
            }
        } else {
            // Se é array, converter para JSON
            $this->db->bind(':configuracoes', json_encode($configuracoes));
        }
        
        return $this->db->executa();
    }

    /**
     * [ excluir ] - Exclui credencial
     * 
     * @param int $id ID da credencial
     * @return bool Sucesso da operação
     */
    public function excluir($id)
    {
        $sql = "DELETE FROM credenciais_serpro_departamento WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * [ alterarStatus ] - Altera status da credencial
     * 
     * @param int $id ID da credencial
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function alterarStatus($id, $status)
    {
        $sql = "UPDATE credenciais_serpro_departamento SET status = :status WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        
        return $this->db->executa();
    }

    /**
     * [ salvarToken ] - Salva token JWT da credencial
     * 
     * @param int $id ID da credencial
     * @param string $token Token JWT
     * @param string $expiracao Data de expiração
     * @return bool Sucesso da operação
     */
    public function salvarToken($id, $token, $expiracao)
    {
        $sql = "UPDATE credenciais_serpro_departamento SET 
                token_cache = :token, 
                token_expiracao = :expiracao 
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':token', $token);
        $this->db->bind(':expiracao', $expiracao);
        
        return $this->db->executa();
    }

    /**
     * [ obterTokenValido ] - Obtém token válido, renovando automaticamente se necessário
     * 
     * @param int $id ID da credencial
     * @return string|null Token válido ou null se não conseguir renovar
     */
    public function obterTokenValido($id)
    {
        $sql = "SELECT token_cache, token_expiracao FROM credenciais_serpro_departamento WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        $resultado = $this->db->resultado();
        
        if (!$resultado || !$resultado->token_cache) {
            // Token não existe, tentar renovar
            error_log("🔄 Token não encontrado para credencial ID: {$id}, renovando automaticamente");
            return $this->renovarTokenAutomaticamente($id);
        }
        
        // Verificar se o token não expirou (com margem de segurança de 2 minutos)
        if ($resultado->token_expiracao) {
            $tempoExpiracao = strtotime($resultado->token_expiracao);
            $tempoAtual = time();
            $margemSeguranca = 120; // 2 minutos em segundos (renovar a cada 8 minutos)
            
            $tempoRestante = $tempoExpiracao - $tempoAtual;
            $minutosRestantes = round($tempoRestante / 60, 1);
            
            // Se faltam menos de 2 minutos para expirar, renovar automaticamente
            if ($tempoRestante < $margemSeguranca) {
                error_log("🔄 Renovando token automaticamente para credencial ID: {$id} (expira em {$minutosRestantes} minutos)");
                return $this->renovarTokenAutomaticamente($id);
            } else {
                error_log("✅ Token válido para credencial ID: {$id} (expira em {$minutosRestantes} minutos)");
            }
        }
        
        return $resultado->token_cache;
    }

    /**
     * [ renovarTokenAutomaticamente ] - Renova token automaticamente
     * 
     * @param int $id ID da credencial
     * @return string|null Token renovado ou null se falhar
     */
    private function renovarTokenAutomaticamente($id)
    {
        $credencial = $this->buscarPorId($id);
        if (!$credencial) {
            error_log("❌ Credencial ID: {$id} não encontrada para renovação automática");
            return null;
        }
        
        try {
            // Obter novo token da API Serpro
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
                error_log("❌ Erro cURL na renovação automática: " . $error);
                return null;
            }

            if ($httpCode !== 200) {
                error_log("❌ Erro HTTP {$httpCode} na renovação automática: " . $response);
                return null;
            }

            $responseData = json_decode($response, true);
            
            if (!isset($responseData['access_token'])) {
                error_log("❌ Token não encontrado na resposta da API: " . $response);
                return null;
            }

            $token = $responseData['access_token'];
            $expiracao = date('Y-m-d H:i:s', time() + 600); // 10 minutos
            
            // Salvar novo token
            $sql = "UPDATE credenciais_serpro_departamento SET 
                    token_cache = :token,
                    token_expiracao = :expiracao,
                    atualizado_em = NOW()
                    WHERE id = :id";
            
            $this->db->query($sql);
            $this->db->bind(':token', $token);
            $this->db->bind(':expiracao', $expiracao);
            $this->db->bind(':id', $id);
            
            if ($this->db->executa()) {
                error_log("✅ Token renovado automaticamente para credencial ID: {$id} - Departamento: {$credencial->departamento_nome}");
                return $token;
            } else {
                error_log("❌ Erro ao salvar token renovado para credencial ID: {$id}");
                return null;
            }
            
        } catch (Exception $e) {
            error_log("❌ Exceção na renovação automática: " . $e->getMessage());
            return null;
        }
    }

    /**
     * [ renovarToken ] - Força renovação do token
     * 
     * @param int $id ID da credencial
     * @return array Resultado da renovação
     */
    public function renovarToken($id)
    {
        $credencial = $this->buscarPorId($id);
        if (!$credencial) {
            return ['success' => false, 'message' => 'Credencial não encontrada'];
        }
        
        // Limpar token atual
        $sql = "UPDATE credenciais_serpro_departamento SET 
                token_cache = NULL, 
                token_expiracao = NULL 
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->executa();
        
        return ['success' => true, 'message' => 'Token renovado com sucesso'];
    }

    /**
     * [ testarConectividade ] - Testa conectividade da credencial
     * 
     * @param int $id ID da credencial
     * @return array Resultado do teste
     */
    public function testarConectividade($id)
    {
        $credencial = $this->buscarPorId($id);
        if (!$credencial) {
            return ['success' => false, 'message' => 'Credencial não encontrada'];
        }
        
        // Testar obtenção de token
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

        $resultado = [
            'success' => false,
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($error) {
            $resultado['message'] = "Erro cURL: " . $error;
        } elseif ($httpCode !== 200) {
            $resultado['message'] = "Erro HTTP: " . $httpCode;
        } else {
            $responseData = json_decode($response, true);
            if (isset($responseData['access_token'])) {
                $resultado['success'] = true;
                $resultado['message'] = 'Conexão bem-sucedida';
                
                // Salvar token obtido com expiração de 10 minutos
                $this->salvarToken($id, $responseData['access_token'], date('Y-m-d H:i:s', time() + 600));
            } else {
                $resultado['message'] = 'Token não encontrado na resposta';
            }
        }
        
        // Salvar resultado do teste
        $sql = "UPDATE credenciais_serpro_departamento SET 
                ultimo_teste = NOW(), 
                resultado_teste = :resultado 
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':resultado', json_encode($resultado));
        $this->db->executa();
        
        return $resultado;
    }

    /**
     * [ getStatusToken ] - Obtém status do token da credencial
     * 
     * @param int $id ID da credencial
     * @return array Status do token
     */
    public function getStatusToken($id)
    {
        $sql = "SELECT token_cache, token_expiracao, ultimo_teste, resultado_teste 
                FROM credenciais_serpro_departamento WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        $resultado = $this->db->resultado();
        
        if (!$resultado) {
            return [
                'tem_token' => false,
                'token_valido' => false,
                'expiracao' => null,
                'ultimo_teste' => null,
                'status_teste' => 'nao_testado'
            ];
        }
        
        $temToken = !empty($resultado->token_cache);
        $tokenValido = $temToken && $resultado->token_expiracao && strtotime($resultado->token_expiracao) > time();
        
        $statusTeste = 'nao_testado';
        if ($resultado->ultimo_teste) {
            $testeData = json_decode($resultado->resultado_teste, true);
            $statusTeste = $testeData['success'] ? 'sucesso' : 'falha';
        }
        
        return [
            'tem_token' => $temToken,
            'token_valido' => $tokenValido,
            'expiracao' => $resultado->token_expiracao,
            'ultimo_teste' => $resultado->ultimo_teste,
            'status_teste' => $statusTeste
        ];
    }

    /**
     * [ listarTodas ] - Lista todas as credenciais
     * 
     * @param bool $apenasAtivas Se true, retorna apenas credenciais ativas
     * @return array Lista de credenciais
     */
    public function listarTodas($apenasAtivas = true)
    {
        $sql = "SELECT 
                    cs.*,
                    d.nome as departamento_nome,
                    d.cor as departamento_cor
                FROM credenciais_serpro_departamento cs
                JOIN departamentos d ON cs.departamento_id = d.id";
        
        if ($apenasAtivas) {
            $sql .= " WHERE cs.status = 'ativo'";
        }
        
        $sql .= " ORDER BY d.prioridade ASC, cs.prioridade ASC, cs.nome ASC";
        
        $this->db->query($sql);
        
        return $this->db->resultados();
    }

    /**
     * [ getEstatisticas ] - Obtém estatísticas das credenciais
     * 
     * @return array Estatísticas
     */
    public function getEstatisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_credenciais,
                    SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as credenciais_ativas,
                    SUM(CASE WHEN status = 'inativo' THEN 1 ELSE 0 END) as credenciais_inativas,
                    SUM(CASE WHEN status = 'teste' THEN 1 ELSE 0 END) as credenciais_teste,
                    COUNT(DISTINCT departamento_id) as departamentos_com_credenciais
                FROM credenciais_serpro_departamento";
        
        $this->db->query($sql);
        
        return $this->db->resultado();
    }
} 