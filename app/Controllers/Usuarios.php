<?php

/**
 * [ USUARIOS ] - Controlador para gerenciamento de usuários do ChatSerpro
 * 
 * Este controlador permite:
 * - Listar, cadastrar, editar e excluir usuários
 * - Gerenciar perfis: admin, supervisor, atendente
 * - Controlar status: ativo, inativo, ausente, ocupado
 * - Configurações específicas para chat (max_chats)
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 2.0.0
 */
class Usuarios extends Controllers
{
    private $usuariosPorPagina = 10;
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        
        // Carrega o model de usuário
        $this->usuarioModel = $this->model('UsuarioModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }

        // Verifica se é admin ou supervisor
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'supervisor'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado: Apenas administradores e supervisores podem gerenciar usuários', 'alert alert-danger');
            Helper::redirecionar('dashboard');
            return;
        }

        // Atualiza último acesso
        $this->usuarioModel->atualizarUltimoAcesso($_SESSION['usuario_id']);
    }

    /**
     * [ index ] - Redireciona para listagem
     */
    public function index()
    {
        $this->listar();
    }

    /**
     * [ listar ] - Lista usuários com filtros e paginação
     */
    public function listar($pagina = 1)
    {
        // Sanitizar parâmetros
        $filtro = filter_input(INPUT_GET, 'filtro', FILTER_SANITIZE_STRING) ?: '';
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) ?: '';
        $perfil = filter_input(INPUT_GET, 'perfil', FILTER_SANITIZE_STRING) ?: '';

        // Buscar usuários
        $usuarios = $this->usuarioModel->listarUsuarios($perfil);
        
        // Aplicar filtros
        if ($filtro) {
            $usuarios = array_filter($usuarios, function($usuario) use ($filtro) {
                return stripos($usuario->nome, $filtro) !== false || 
                       stripos($usuario->email, $filtro) !== false;
            });
        }

        if ($status) {
            $usuarios = array_filter($usuarios, function($usuario) use ($status) {
                return $usuario->status === $status;
            });
        }

        // Calcular paginação
        $totalUsuarios = count($usuarios);
        $totalPaginas = ceil($totalUsuarios / $this->usuariosPorPagina);
        $offset = ($pagina - 1) * $this->usuariosPorPagina;
        $usuariosPaginados = array_slice($usuarios, $offset, $this->usuariosPorPagina);

        // Dados para a view
        $dados = [
            'usuarios' => $usuariosPaginados,
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas,
            'total_usuarios' => $totalUsuarios,
            'filtro' => $filtro,
            'status' => $status,
            'perfil' => $perfil,
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ]
        ];

        $this->view('usuarios/listar', $dados);
    }

    /**
     * [ cadastrar ] - Cadastra novo usuário
     */
    public function cadastrar()
    {
        // Apenas admin pode cadastrar
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Apenas administradores podem cadastrar usuários', 'alert alert-danger');
            Helper::redirecionar('usuarios');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        if ($formulario) {
            $dados = [
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'senha' => trim($formulario['senha']),
                'confirma_senha' => trim($formulario['confirma_senha']),
                'perfil' => trim($formulario['perfil']),
                'status' => trim($formulario['status']),
                'max_chats' => (int) trim($formulario['max_chats']),
                'nome_erro' => '',
                'email_erro' => '',
                'senha_erro' => '',
                'confirma_senha_erro' => ''
            ];

            // Validações
            if (empty($dados['nome'])) {
                $dados['nome_erro'] = 'Preencha o campo nome';
            }

            if (empty($dados['email'])) {
                $dados['email_erro'] = 'Preencha o campo e-mail';
            } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $dados['email_erro'] = 'E-mail inválido';
            } elseif ($this->usuarioModel->verificarEmail($dados['email'])) {
                $dados['email_erro'] = 'E-mail já cadastrado';
            }

            if (empty($dados['senha'])) {
                $dados['senha_erro'] = 'Preencha o campo senha';
            } elseif (strlen($dados['senha']) < 6) {
                $dados['senha_erro'] = 'A senha deve ter no mínimo 6 caracteres';
            }

            if (empty($dados['confirma_senha'])) {
                $dados['confirma_senha_erro'] = 'Confirme a senha';
            } elseif ($dados['senha'] !== $dados['confirma_senha']) {
                $dados['confirma_senha_erro'] = 'As senhas são diferentes';
            }

            // Se não há erros, cadastrar
            if (empty($dados['nome_erro']) && empty($dados['email_erro']) && 
                empty($dados['senha_erro']) && empty($dados['confirma_senha_erro'])) {
                
                $dadosUsuario = [
                    'nome' => $dados['nome'],
                    'email' => $dados['email'],
                    'senha' => $dados['senha'],
                    'perfil' => $dados['perfil'],
                    'status' => $dados['status'],
                    'max_chats' => $dados['max_chats']
                ];

                if ($this->usuarioModel->criarUsuario($dadosUsuario)) {
                    // ✅ NOVO: Registrar log de criação de usuário
                    LogHelper::criarUsuario($dados['nome'], $dados['perfil']);
                    
                    Helper::mensagem('usuario', '<i class="fas fa-check"></i> Usuário cadastrado com sucesso!', 'alert alert-success');
                    Helper::redirecionar('usuarios');
                } else {
                    $dados['erro'] = 'Erro ao cadastrar usuário';
                }
            }
        } else {
            $dados = [
                'nome' => '',
                'email' => '',
                'senha' => '',
                'confirma_senha' => '',
                'perfil' => 'atendente',
                'status' => 'ativo',
                'max_chats' => 5,
                'nome_erro' => '',
                'email_erro' => '',
                'senha_erro' => '',
                'confirma_senha_erro' => ''
            ];
        }

        $dados['usuario_logado'] = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'perfil' => $_SESSION['usuario_perfil'],
            'status' => $_SESSION['usuario_status']
        ];

        $this->view('usuarios/cadastrar', $dados);
    }

    /**
     * [ editar ] - Edita usuário existente
     */
    public function editar($id)
    {
        if (!$id || !is_numeric($id)) {
            Helper::mensagem('usuario', '<i class="fas fa-exclamation-triangle"></i> ID inválido', 'alert alert-warning');
            Helper::redirecionar('usuarios');
            return;
        }

        $usuario = $this->usuarioModel->lerUsuarioPorId($id);
        
        if (!$usuario) {
            Helper::mensagem('usuario', '<i class="fas fa-exclamation-triangle"></i> Usuário não encontrado', 'alert alert-warning');
            Helper::redirecionar('usuarios');
            return;
        }

        // Verificações de permissão
        if ($_SESSION['usuario_perfil'] === 'supervisor') {
            // Supervisor pode editar apenas atendentes
            if ($usuario->perfil !== 'atendente') {
                Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Supervisores só podem editar atendentes', 'alert alert-danger');
                Helper::redirecionar('usuarios');
                return;
            }
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        if ($formulario) {
            $dados = [
                'id' => $id,
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'perfil' => trim($formulario['perfil']),
                'status' => trim($formulario['status']),
                'max_chats' => (int) trim($formulario['max_chats']),
                'nome_erro' => '',
                'email_erro' => ''
            ];

            // Validações
            if (empty($dados['nome'])) {
                $dados['nome_erro'] = 'Preencha o campo nome';
            }

            if (empty($dados['email'])) {
                $dados['email_erro'] = 'Preencha o campo e-mail';
            } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $dados['email_erro'] = 'E-mail inválido';
            } elseif ($this->usuarioModel->verificarEmail($dados['email'], $id)) {
                $dados['email_erro'] = 'E-mail já cadastrado';
            }

            // Verificar se supervisor está tentando alterar perfil
            if ($_SESSION['usuario_perfil'] === 'supervisor' && $dados['perfil'] !== 'atendente') {
                $dados['perfil'] = 'atendente'; // Força atendente
            }

            // Se não há erros, atualizar
            if (empty($dados['nome_erro']) && empty($dados['email_erro'])) {
                $dadosAtualizacao = [
                    'nome' => $dados['nome'],
                    'email' => $dados['email'],
                    'perfil' => $dados['perfil'],
                    'status' => $dados['status'],
                    'max_chats' => $dados['max_chats']
                ];

                // Processar nova senha se fornecida
                if (!empty($formulario['senha'])) {
                    if (strlen($formulario['senha']) >= 6) {
                        if ($formulario['senha'] === $formulario['confirma_senha']) {
                            $dadosAtualizacao['senha'] = $formulario['senha'];
                        } else {
                            $dados['senha_erro'] = 'As senhas são diferentes';
                        }
                    } else {
                        $dados['senha_erro'] = 'A senha deve ter no mínimo 6 caracteres';
                    }
                }

                if (empty($dados['senha_erro']) && $this->usuarioModel->atualizarUsuario($id, $dadosAtualizacao)) {
                    // ✅ NOVO: Registrar log de edição de usuário
                    LogHelper::editarUsuario($id, $dados['nome']);
                    
                    Helper::mensagem('usuario', '<i class="fas fa-check"></i> Usuário atualizado com sucesso!', 'alert alert-success');
                    Helper::redirecionar('usuarios');
                } else {
                    $dados['erro'] = 'Erro ao atualizar usuário';
                }
            }
        } else {
            $dados = [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'perfil' => $usuario->perfil,
                'status' => $usuario->status,
                'max_chats' => $usuario->max_chats ?? 5,
                'nome_erro' => '',
                'email_erro' => ''
            ];
        }

        $dados['usuario_logado'] = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'perfil' => $_SESSION['usuario_perfil'],
            'status' => $_SESSION['usuario_status']
        ];

        $this->view('usuarios/editar', $dados);
    }

    /**
     * [ excluir ] - Exclui usuário (apenas admin)
     */
    public function excluir($id)
    {
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Apenas administradores podem excluir usuários', 'alert alert-danger');
            Helper::redirecionar('usuarios');
            return;
        }

        if (!$id || !is_numeric($id)) {
            Helper::mensagem('usuario', '<i class="fas fa-exclamation-triangle"></i> ID inválido', 'alert alert-warning');
            Helper::redirecionar('usuarios');
            return;
        }

        // Não permitir excluir a si mesmo
        if ($id == $_SESSION['usuario_id']) {
            Helper::mensagem('usuario', '<i class="fas fa-ban"></i> Você não pode excluir sua própria conta', 'alert alert-danger');
            Helper::redirecionar('usuarios');
            return;
        }

        $usuario = $this->usuarioModel->lerUsuarioPorId($id);
        
        if (!$usuario) {
            Helper::mensagem('usuario', '<i class="fas fa-exclamation-triangle"></i> Usuário não encontrado', 'alert alert-warning');
            Helper::redirecionar('usuarios');
            return;
        }

        if ($this->usuarioModel->excluirUsuario($id)) {
            // ✅ NOVO: Registrar log de exclusão de usuário
            LogHelper::excluirUsuario($id, $usuario->nome);
            
            Helper::mensagem('usuario', '<i class="fas fa-check"></i> Usuário excluído com sucesso!', 'alert alert-success');
        } else {
            Helper::mensagem('usuario', '<i class="fas fa-exclamation-triangle"></i> Erro ao excluir usuário', 'alert alert-danger');
        }

        Helper::redirecionar('usuarios');
    }

    /**
     * [ alterarStatus ] - Altera status do usuário via AJAX
     */
    public function alterarStatus()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        // Debug - log da requisição
        error_log('AJAX alterarStatus - Recebendo requisição...');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Debug - log dos dados recebidos
        error_log('AJAX alterarStatus - Dados: ' . print_r($input, true));
        
        $id = $input['id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$id || !$status) {
            error_log('AJAX alterarStatus - Dados inválidos: ID=' . $id . ', Status=' . $status);
            echo json_encode(['success' => false, 'message' => 'Dados inválidos - ID ou status em branco']);
            return;
        }

        $statusValidos = ['ativo', 'inativo', 'ausente', 'ocupado'];
        
        if (!in_array($status, $statusValidos)) {
            error_log('AJAX alterarStatus - Status inválido: ' . $status);
            echo json_encode(['success' => false, 'message' => 'Status inválido. Use: ' . implode(', ', $statusValidos)]);
            return;
        }

        // Verificar se o usuário existe
        $usuario = $this->usuarioModel->lerUsuarioPorId($id);
        if (!$usuario) {
            error_log('AJAX alterarStatus - Usuário não encontrado: ID=' . $id);
            echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
            return;
        }

        // Verificar permissões
        if ($_SESSION['usuario_perfil'] !== 'admin' && $usuario->perfil !== 'atendente') {
            error_log('AJAX alterarStatus - Sem permissão. Perfil logado: ' . $_SESSION['usuario_perfil'] . ', Perfil do usuário: ' . $usuario->perfil);
            echo json_encode(['success' => false, 'message' => 'Sem permissão para alterar este usuário']);
            return;
        }

        // Tentar atualizar o status
        if ($this->usuarioModel->atualizarStatus($id, $status)) {
            error_log('AJAX alterarStatus - Sucesso! ID=' . $id . ', Novo status=' . $status);
            echo json_encode([
                'success' => true, 
                'message' => 'Status atualizado com sucesso',
                'user_id' => $id,
                'new_status' => $status
            ]);
        } else {
            error_log('AJAX alterarStatus - Falha na atualização do banco. ID=' . $id . ', Status=' . $status);
            echo json_encode(['success' => false, 'message' => 'Erro interno - falha na atualização do banco de dados']);
        }
    }
}
