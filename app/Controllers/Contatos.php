<?php

/**
 * [ CONTATOS ] - Controlador para gerenciamento de contatos do ChatSerpro
 * 
 * Este controlador permite:
 * - Listar, cadastrar, editar e excluir contatos
 * - Sistema de tags para organização
 * - Bloquear/desbloquear contatos
 * - Visualizar histórico de conversas
 * - Integração com WhatsApp
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class Contatos extends Controllers
{
    private $contatosPorPagina = 15;
    private $contatoModel;

    public function __construct()
    {
        parent::__construct();
        
        // Carrega o model de contato
        $this->contatoModel = $this->model('ContatoModel');

        // Verifica se o usuário está logado
        if (!isset($_SESSION['usuario_id'])) {
            Helper::redirecionar('login-chat');
            return;
        }

        // Atualiza último acesso
        $usuarioModel = $this->model('UsuarioModel');
        $usuarioModel->atualizarUltimoAcesso($_SESSION['usuario_id']);
    }

    /**
     * [ index ] - Redireciona para listagem
     */
    public function index()
    {
        $this->listar();
    }

    /**
     * [ listar ] - Lista contatos com filtros e paginação
     */
    public function listar($pagina = 1)
    {
        // Sanitizar parâmetros
        $filtros = [
            'busca' => filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_STRING) ?: '',
            'bloqueado' => filter_input(INPUT_GET, 'bloqueado', FILTER_SANITIZE_STRING) ?: '',
            'tag' => filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_STRING) ?: '',
            'periodo' => filter_input(INPUT_GET, 'periodo', FILTER_SANITIZE_STRING) ?: ''
        ];

        // Buscar contatos
        $contatos = $this->contatoModel->listarContatos($filtros, $pagina, $this->contatosPorPagina);
        $totalContatos = $this->contatoModel->contarContatos($filtros);
        $totalPaginas = ceil($totalContatos / $this->contatosPorPagina);

        // Buscar tags disponíveis
        $tags = $this->contatoModel->listarTags();

        // Estatísticas
        $estatisticas = $this->contatoModel->getEstatisticasContatos();

        // Dados para a view
        $dados = [
            'contatos' => $contatos,
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas,
            'total_contatos' => $totalContatos,
            'filtros' => $filtros,
            'tags' => $tags,
            'estatisticas' => $estatisticas,
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ]
        ];

        $this->view('contatos/listar', $dados);
    }

    /**
     * [ cadastrar ] - Cadastra novo contato
     */
    public function cadastrar()
    {
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        if ($formulario) {
            $dados = [
                'nome' => trim($formulario['nome']),
                'telefone' => trim($formulario['telefone']),
                'email' => trim($formulario['email']),
                'empresa' => trim($formulario['empresa']),
                'observacoes' => trim($formulario['observacoes']),
                'tags' => trim($formulario['tags']),
                'nome_erro' => '',
                'telefone_erro' => '',
                'email_erro' => ''
            ];

            // Validações
            if (empty($dados['nome'])) {
                $dados['nome_erro'] = 'Preencha o campo nome';
            }

            if (empty($dados['telefone'])) {
                $dados['telefone_erro'] = 'Preencha o campo telefone';
            } elseif ($this->contatoModel->verificarTelefone($dados['telefone'])) {
                $dados['telefone_erro'] = 'Este telefone já está cadastrado';
            }

            if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $dados['email_erro'] = 'E-mail inválido';
            }

            // Se não há erros, cadastrar
            if (empty($dados['nome_erro']) && empty($dados['telefone_erro']) && empty($dados['email_erro'])) {
                $dadosContato = [
                    'nome' => $dados['nome'],
                    'telefone' => $dados['telefone'],
                    'email' => $dados['email'] ?: null,
                    'empresa' => $dados['empresa'] ?: null,
                    'observacoes' => $dados['observacoes'] ?: null,
                    'fonte' => 'manual'
                ];

                $contatoId = $this->contatoModel->criarContato($dadosContato);
                
                if ($contatoId) {
                    // Adicionar tags se fornecidas
                    if (!empty($dados['tags'])) {
                        $tags = explode(',', $dados['tags']);
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            if (!empty($tag)) {
                                $this->contatoModel->adicionarTag($contatoId, $tag);
                            }
                        }
                    }

                    Helper::mensagem('contato', '<i class="fas fa-check"></i> Contato cadastrado com sucesso!', 'alert alert-success');
                    Helper::redirecionar('contatos');
                } else {
                    $dados['erro'] = 'Erro ao cadastrar contato';
                }
            }
        } else {
            $dados = [
                'nome' => '',
                'telefone' => '',
                'email' => '',
                'empresa' => '',
                'observacoes' => '',
                'tags' => '',
                'nome_erro' => '',
                'telefone_erro' => '',
                'email_erro' => ''
            ];
        }

        // Buscar tags disponíveis
        $dados['tags_disponiveis'] = $this->contatoModel->listarTags();
        $dados['usuario_logado'] = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'perfil' => $_SESSION['usuario_perfil'],
            'status' => $_SESSION['usuario_status']
        ];

        $this->view('contatos/cadastrar', $dados);
    }

    /**
     * [ editar ] - Edita contato existente
     */
    public function editar($id)
    {
        if (!$id || !is_numeric($id)) {
            Helper::mensagem('contato', '<i class="fas fa-exclamation-triangle"></i> ID inválido', 'alert alert-warning');
            Helper::redirecionar('contatos');
            return;
        }

        $contato = $this->contatoModel->lerContatoPorId($id);
        
        if (!$contato) {
            Helper::mensagem('contato', '<i class="fas fa-exclamation-triangle"></i> Contato não encontrado', 'alert alert-warning');
            Helper::redirecionar('contatos');
            return;
        }

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        if ($formulario) {
            $dados = [
                'id' => $id,
                'nome' => trim($formulario['nome']),
                'telefone' => trim($formulario['telefone']),
                'email' => trim($formulario['email']),
                'empresa' => trim($formulario['empresa']),
                'observacoes' => trim($formulario['observacoes']),
                'tags' => trim($formulario['tags']),
                'nome_erro' => '',
                'telefone_erro' => '',
                'email_erro' => ''
            ];

            // Validações
            if (empty($dados['nome'])) {
                $dados['nome_erro'] = 'Preencha o campo nome';
            }

            if (empty($dados['telefone'])) {
                $dados['telefone_erro'] = 'Preencha o campo telefone';
            } elseif ($this->contatoModel->verificarTelefone($dados['telefone'], $id)) {
                $dados['telefone_erro'] = 'Este telefone já está cadastrado';
            }

            if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                $dados['email_erro'] = 'E-mail inválido';
            }

            // Se não há erros, atualizar
            if (empty($dados['nome_erro']) && empty($dados['telefone_erro']) && empty($dados['email_erro'])) {
                $dadosAtualizacao = [
                    'nome' => $dados['nome'],
                    'telefone' => $dados['telefone'],
                    'email' => $dados['email'] ?: null,
                    'empresa' => $dados['empresa'] ?: null,
                    'observacoes' => $dados['observacoes'] ?: null
                ];

                if ($this->contatoModel->atualizarContato($id, $dadosAtualizacao)) {
                    // Atualizar tags
                    $this->contatoModel->removerTodasTags($id);
                    if (!empty($dados['tags'])) {
                        $tags = explode(',', $dados['tags']);
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            if (!empty($tag)) {
                                $this->contatoModel->adicionarTag($id, $tag);
                            }
                        }
                    }

                    Helper::mensagem('contato', '<i class="fas fa-check"></i> Contato atualizado com sucesso!', 'alert alert-success');
                    Helper::redirecionar('contatos');
                } else {
                    $dados['erro'] = 'Erro ao atualizar contato';
                }
            }
        } else {
            $dados = [
                'id' => $contato->id,
                'nome' => $contato->nome,
                'telefone' => $contato->telefone,
                'email' => $contato->email,
                'empresa' => $contato->empresa,
                'observacoes' => $contato->observacoes,
                'tags' => $contato->tags,
                'nome_erro' => '',
                'telefone_erro' => '',
                'email_erro' => ''
            ];
        }

        $dados['tags_disponiveis'] = $this->contatoModel->listarTags();
        $dados['usuario_logado'] = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'perfil' => $_SESSION['usuario_perfil'],
            'status' => $_SESSION['usuario_status']
        ];

        $this->view('contatos/editar', $dados);
    }

    /**
     * [ perfil ] - Visualiza perfil completo do contato
     */
    public function perfil($id)
    {
        if (!$id || !is_numeric($id)) {
            Helper::mensagem('contato', '<i class="fas fa-exclamation-triangle"></i> ID inválido', 'alert alert-warning');
            Helper::redirecionar('contatos');
            return;
        }

        $contato = $this->contatoModel->lerContatoPorId($id);
        
        if (!$contato) {
            Helper::mensagem('contato', '<i class="fas fa-exclamation-triangle"></i> Contato não encontrado', 'alert alert-warning');
            Helper::redirecionar('contatos');
            return;
        }

        // Buscar histórico de conversas
        $historico = $this->contatoModel->getHistoricoConversa($id);

        $dados = [
            'contato' => $contato,
            'historico' => $historico,
            'usuario_logado' => [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'perfil' => $_SESSION['usuario_perfil'],
                'status' => $_SESSION['usuario_status']
            ]
        ];

        $this->view('contatos/perfil', $dados);
    }

    /**
     * [ excluir ] - Exclui contato
     */
    public function excluir($id)
    {
        // Apenas admin e supervisor podem excluir
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'supervisor'])) {
            Helper::mensagem('contato', '<i class="fas fa-ban"></i> Apenas administradores e supervisores podem excluir contatos', 'alert alert-danger');
            Helper::redirecionar('contatos');
            return;
        }

        if (!$id || !is_numeric($id)) {
            Helper::mensagem('contato', '<i class="fas fa-exclamation-triangle"></i> ID inválido', 'alert alert-warning');
            Helper::redirecionar('contatos');
            return;
        }

        $contato = $this->contatoModel->lerContatoPorId($id);
        
        if (!$contato) {
            Helper::mensagem('contato', '<i class="fas fa-exclamation-triangle"></i> Contato não encontrado', 'alert alert-warning');
            Helper::redirecionar('contatos');
            return;
        }

        if ($this->contatoModel->excluirContato($id)) {
            Helper::mensagem('contato', '<i class="fas fa-check"></i> Contato excluído com sucesso!', 'alert alert-success');
        } else {
            Helper::mensagem('contato', '<i class="fas fa-exclamation-triangle"></i> Erro ao excluir contato', 'alert alert-danger');
        }

        Helper::redirecionar('contatos');
    }

    /**
     * [ bloquear ] - Bloqueia contato via AJAX
     */
    public function bloquear($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        if ($this->contatoModel->bloquearContato($id)) {
            echo json_encode(['success' => true, 'message' => 'Contato bloqueado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao bloquear contato']);
        }
    }

    /**
     * [ desbloquear ] - Desbloqueia contato via AJAX
     */
    public function desbloquear($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        if ($this->contatoModel->desbloquearContato($id)) {
            echo json_encode(['success' => true, 'message' => 'Contato desbloqueado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao desbloquear contato']);
        }
    }

    /**
     * [ adicionarTag ] - Adiciona tag ao contato via AJAX
     */
    public function adicionarTag($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $tag = $input['tag'] ?? null;

        if (!$id || !is_numeric($id) || empty($tag)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        if ($this->contatoModel->adicionarTag($id, trim($tag))) {
            echo json_encode(['success' => true, 'message' => 'Tag adicionada com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tag']);
        }
    }

    /**
     * [ removerTag ] - Remove tag do contato via AJAX
     */
    public function removerTag($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $tag = $input['tag'] ?? null;

        if (!$id || !is_numeric($id) || empty($tag)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        if ($this->contatoModel->removerTag($id, trim($tag))) {
            echo json_encode(['success' => true, 'message' => 'Tag removida com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover tag']);
        }
    }

    /**
     * [ buscarPorTelefone ] - Busca contato por telefone via AJAX
     */
    public function buscarPorTelefone()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $telefone = $input['telefone'] ?? null;

        if (empty($telefone)) {
            echo json_encode(['success' => false, 'message' => 'Telefone não informado']);
            return;
        }

        $contato = $this->contatoModel->lerContatoPorTelefone($telefone);

        if ($contato) {
            echo json_encode([
                'success' => true, 
                'contato' => $contato,
                'message' => 'Contato encontrado'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Contato não encontrado'
            ]);
        }
    }
} 