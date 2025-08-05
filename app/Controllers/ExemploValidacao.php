<?php

/**
 * [ EXEMPLOVALIDACAO ] - Controller de exemplo para demonstrar validação
 * 
 * Este controller demonstra como usar o sistema de validação centralizada
 * e tratamento de exceções no framework.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class ExemploValidacao extends Controllers
{
    /**
     * Exemplo de validação de formulário
     */
    public function validarUsuario()
    {
        try {
            // Dados do formulário (simulando $_POST)
            $dados = [
                'nome' => $_POST['nome'] ?? '',
                'email' => $_POST['email'] ?? '',
                'senha' => $_POST['senha'] ?? '',
                'senha_confirmation' => $_POST['senha_confirmation'] ?? '',
                'idade' => $_POST['idade'] ?? '',
                'telefone' => $_POST['telefone'] ?? ''
            ];

            // Regras de validação
            $regras = [
                'nome' => 'required|min:3|max:100|alpha',
                'email' => 'required|email',
                'senha' => 'required|min:6|confirmed',
                'idade' => 'required|integer|min:18',
                'telefone' => 'required|regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'
            ];

            // Mensagens customizadas
            $mensagens = [
                'nome.required' => 'O nome é obrigatório',
                'nome.min' => 'O nome deve ter pelo menos 3 caracteres',
                'nome.alpha' => 'O nome deve conter apenas letras',
                'email.email' => 'Digite um email válido',
                'senha.confirmed' => 'As senhas não conferem',
                'idade.min' => 'Você deve ter pelo menos 18 anos',
                'telefone.regex' => 'Digite um telefone válido no formato (11) 99999-9999'
            ];

            // Criar instância do validador
            $validator = new Validator($dados, $regras, $mensagens);

            // Validar dados
            $validator->validate();

            // Se chegou aqui, a validação passou
            Logger::info('Usuário validado com sucesso', [
                'email' => $dados['email'],
                'nome' => $dados['nome']
            ]);

            // Processar dados validados
            $this->processarUsuario($dados);

            // Retornar sucesso
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => true, 'message' => 'Usuário cadastrado com sucesso']);
            } else {
                Helper::mensagem('sucesso', 'Usuário cadastrado com sucesso!');
                Helper::redirecionar('usuarios');
            }

        } catch (ValidationException $e) {
            // Log do erro de validação
            Logger::warning('Erro de validação', [
                'errors' => $e->getErrors(),
                'data' => $dados
            ]);

            // Retornar erros de validação
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $e->getErrors()
                ], 422);
            } else {
                // Redirecionar de volta com erros
                $_SESSION['validation_errors'] = $e->getErrors();
                $_SESSION['old_input'] = $dados;
                Helper::redirecionar('usuarios/cadastrar');
            }
        } catch (Exception $e) {
            // Log do erro geral
            Logger::error('Erro ao processar usuário', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erro interno do servidor'
                ], 500);
            } else {
                Helper::mensagem('erro', 'Erro interno do servidor. Tente novamente.');
                Helper::redirecionar('usuarios/cadastrar');
            }
        }
    }

    /**
     * Exemplo de validação de API
     */
    public function apiValidarUsuario()
    {
        try {
            // Obter dados JSON
            $jsonData = json_decode(file_get_contents('php://input'), true);
            
            if (!$jsonData) {
                throw new ValidationException('Dados JSON inválidos');
            }

            // Regras de validação para API
            $regras = [
                'nome' => 'required|min:3|max:100',
                'email' => 'required|email',
                'perfil' => 'required|in:admin,usuario,analista'
            ];

            $validator = new Validator($jsonData, $regras);
            $validator->validate();

            // Processar dados
            $resultado = $this->processarUsuario($jsonData);

            Logger::api('Usuário criado via API', [
                'email' => $jsonData['email'],
                'perfil' => $jsonData['perfil']
            ]);

            $this->jsonResponse([
                'success' => true,
                'data' => $resultado,
                'message' => 'Usuário criado com sucesso'
            ]);

        } catch (ValidationException $e) {
            Logger::warning('Erro de validação na API', [
                'errors' => $e->getErrors()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->getErrors()
            ], 422);

        } catch (Exception $e) {
            Logger::error('Erro na API', [
                'message' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Processa dados do usuário
     * 
     * @param array $dados
     * @return array
     */
    private function processarUsuario($dados)
    {
        // Simular processamento
        $usuarioModel = $this->model('UsuarioModel');
        
        // Aqui você faria a inserção no banco
        // $resultado = $usuarioModel->criarUsuario($dados);
        
        Logger::info('Usuário processado', [
            'email' => $dados['email']
        ]);

        return [
            'id' => rand(1, 1000), // Simulado
            'nome' => $dados['nome'],
            'email' => $dados['email']
        ];
    }

    /**
     * Verifica se é uma requisição AJAX
     * 
     * @return bool
     */
    private function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Retorna resposta JSON
     * 
     * @param array $data
     * @param int $statusCode
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Exemplo de uso do Logger
     */
    public function exemploLogging()
    {
        // Logs de diferentes níveis
        Logger::info('Usuário acessou página de exemplo');
        Logger::debug('Dados de debug', ['user_id' => 123, 'action' => 'exemplo']);
        Logger::warning('Aviso importante', ['context' => 'exemplo']);
        Logger::error('Erro simulado', ['error_code' => 'TEST001']);
        
        // Logs específicos
        Logger::access('Acesso à página de exemplo');
        Logger::security('Tentativa de acesso', ['ip' => $_SERVER['REMOTE_ADDR']]);
        Logger::database('Consulta executada', ['query' => 'SELECT * FROM usuarios']);
        Logger::api('Chamada de API', ['endpoint' => '/api/usuarios']);

        echo "Logs registrados com sucesso!";
    }
} 