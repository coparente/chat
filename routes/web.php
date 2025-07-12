<?php

/**
 * ============================================================================
 * SISTEMA DE ROTAS - CHATSERPRO MULTIATENDIMENTO
 * ============================================================================
 * 
 * Sistema de rotas estilo Laravel para o ChatSerpro
 * Todas as rotas devem ser definidas aqui para funcionar corretamente
 */

// ============================================================================
// ROTA PRINCIPAL - Redireciona para login do chat
// ============================================================================
Route::get('/', 'LoginChat@login');

// ============================================================================
// ROTAS DE AUTENTICAÇÃO - SISTEMA DE CHAT
// ============================================================================
Route::get('/login-chat', 'LoginChat@login');
Route::post('/login-chat', 'LoginChat@login');
Route::get('/login', 'LoginChat@login'); // Compatibilidade
Route::post('/login', 'LoginChat@login'); // Compatibilidade
Route::get('/logout', 'LoginChat@sair');
Route::get('/sair', 'LoginChat@sair');

// AJAX - Verificações de sessão
Route::post('/verificar-sessao', 'LoginChat@verificarSessao');
Route::post('/alterar-status', 'LoginChat@alterarStatus');

// ============================================================================
// ROTAS PROTEGIDAS - SISTEMA DE CHAT
// ============================================================================
Route::group(['middleware' => ['auth']], function() {
    
    // ========================================================================
    // DASHBOARD - Painel principal
    // ========================================================================
    Route::get('/dashboard', 'Dashboard@inicial');
    Route::get('/dashboard/inicial', 'Dashboard@inicial');
    Route::get('/dashboard/estatisticas', 'Dashboard@estatisticas');

    // ========================================================================
    // CHAT - Sistema de conversas
    // ========================================================================
    Route::group(['prefix' => 'chat'], function() {
        Route::get('/', 'Chat@painel');
        Route::get('/painel', 'Chat@painel');
        Route::get('/ativas', 'Chat@conversasAtivas');
        Route::get('/pendentes', 'Chat@conversasPendentes');
        Route::get('/fechadas', 'Chat@conversasFechadas');
        
        // Gerenciamento de conversas
        Route::post('/iniciar-conversa', 'Chat@iniciarConversa');
        Route::post('/assumir/{conversa_id}', 'Chat@assumirConversa');
        Route::post('/transferir/{conversa_id}', 'Chat@transferirConversa');
        Route::post('/fechar/{conversa_id}', 'Chat@fecharConversa');
        
        // Mensagens
        Route::post('/enviar-mensagem', 'Chat@enviarMensagem');
        Route::post('/enviar-midia', 'Chat@enviarMidia');
        Route::get('/buscar-mensagens/{conversa_id}', 'Chat@buscarMensagens');
        Route::post('/marcar-lida/{mensagem_id}', 'Chat@marcarComoLida');
        
        // Status das conversas
        Route::get('/status-conversa/{conversa_id}', 'Chat@statusConversa');
        
        // Templates
        Route::post('/iniciar/{contato_id}', 'Chat@iniciarConversa');
    });

    // ========================================================================
    // CONTATOS - Gerenciamento
    // ========================================================================
    Route::group(['prefix' => 'contatos'], function() {
        Route::get('/', 'Contatos@listar');
        Route::get('/listar', 'Contatos@listar');
        Route::get('/listar/{pagina}', 'Contatos@listar');
        
        // Cadastro e edição
        Route::get('/cadastrar', 'Contatos@cadastrar');
        Route::post('/cadastrar', 'Contatos@cadastrar');
        Route::get('/editar/{id}', 'Contatos@editar');
        Route::post('/editar/{id}', 'Contatos@editar');
        
        // Visualização e exclusão
        Route::get('/perfil/{id}', 'Contatos@perfil');
        Route::get('/excluir/{id}', 'Contatos@excluir');
        
        // Ações AJAX
        Route::post('/bloquear/{id}', 'Contatos@bloquear');
        Route::post('/desbloquear/{id}', 'Contatos@desbloquear');
        Route::post('/adicionar-tag/{id}', 'Contatos@adicionarTag');
        Route::post('/remover-tag/{id}', 'Contatos@removerTag');
        Route::post('/buscar-por-telefone', 'Contatos@buscarPorTelefone');
        Route::post('/atualizar/{id}', 'Contatos@atualizar');
    });

    // ========================================================================
    // RELATÓRIOS - Apenas para Admin e Supervisor
    // ========================================================================
    Route::group(['prefix' => 'relatorios', 'middleware' => ['supervisor_ou_admin']], function() {
        Route::get('/', 'Relatorios@index');
        Route::get('/atendimentos', 'Relatorios@atendimentos');
        Route::get('/conversas', 'Relatorios@conversas');
        Route::get('/performance', 'Relatorios@performance');
        Route::get('/mensagens', 'Relatorios@mensagens');
        Route::get('/exportar/{tipo}', 'Relatorios@exportar');
    });

    // ========================================================================
    // USUÁRIOS - Gerenciamento (Admin e Supervisor)
    // ========================================================================
    Route::group(['prefix' => 'usuarios', 'middleware' => ['supervisor_ou_admin']], function() {
        Route::get('/', 'Usuarios@listar');
        Route::get('/listar', 'Usuarios@listar');
        Route::get('/listar/{pagina}', 'Usuarios@listar');
        
        // Cadastro e edição (apenas admin para cadastro)
        Route::get('/cadastrar', 'Usuarios@cadastrar');
        Route::post('/cadastrar', 'Usuarios@cadastrar');
        Route::get('/editar/{id}', 'Usuarios@editar');
        Route::post('/editar/{id}', 'Usuarios@editar');
        
        // Ações específicas (apenas admin para exclusão)
        Route::get('/excluir/{id}', 'Usuarios@excluir');
        Route::post('/alterar-status', 'Usuarios@alterarStatus');
        
        // Permissões e configurações
        Route::get('/permissoes/{id}', 'Usuarios@permissoes');
        Route::post('/permissoes/{id}', 'Usuarios@salvarPermissoes');
        
        // AJAX - Ações rápidas
        Route::post('/ativar/{id}', 'Usuarios@ativar');
        Route::post('/desativar/{id}', 'Usuarios@desativar');
    });

    // ========================================================================
    // CONFIGURAÇÕES - Apenas Admin
    // ========================================================================
    Route::group(['prefix' => 'configuracoes', 'middleware' => ['admin']], function() {
        Route::get('/', 'Configuracoes@index');
        
        // Conexões WhatsApp
        Route::get('/conexoes', 'Configuracoes@conexoes');
        Route::post('/conexoes/nova', 'Configuracoes@novaConexao');
        Route::post('/conexoes/conectar/{id}', 'Configuracoes@conectar');
        Route::post('/conexoes/desconectar/{id}', 'Configuracoes@desconectar');
        Route::post('/conexoes/excluir/{id}', 'Configuracoes@excluirConexao');
        
        // API Serpro
        Route::get('/serpro', 'Configuracoes@serpro');
        Route::post('/serpro/salvar', 'Configuracoes@salvarSerpro');
        Route::post('/serpro/testar', 'Configuracoes@testarSerpro');
        
        // Gerenciamento de Token JWT
        Route::get('/token/status', 'Configuracoes@statusToken');
        Route::post('/token/renovar', 'Configuracoes@renovarToken');
        Route::post('/token/limpar', 'Configuracoes@limparTokenCache');
        
        // Mensagens Automáticas
        Route::get('/mensagens', 'Configuracoes@mensagens');
        Route::post('/mensagens/salvar', 'Configuracoes@salvarMensagens');
    });

    // ========================================================================
    // WEBHOOKS - Recebimento de mensagens do WhatsApp
    // ========================================================================
    Route::post('/webhook/whatsapp', 'Webhook@receberMensagem');
    Route::get('/webhook/whatsapp', 'Webhook@verificarWebhook');

    // ========================================================================
    // WEBHOOKS SERPRO - Recebimento de mensagens da API Serpro
    // ========================================================================
    Route::post('/webhook/serpro', 'Webhook@serpro');
    Route::get('/webhook/serpro/test', 'Webhook@test');

    // ========================================================================
    // API INTERNA - Para funcionalidades AJAX
    // ========================================================================
    Route::group(['prefix' => 'api'], function() {
        // Status em tempo real
        Route::get('/status-atendentes', 'Api@statusAtendentes');
        Route::get('/conversas-tempo-real', 'Api@conversasTempoReal');
        Route::get('/estatisticas-dash', 'Api@estatisticasDashboard');
        
        // Notificações
        Route::get('/notificacoes', 'Api@notificacoes');
        Route::post('/marcar-notificacao-lida/{id}', 'Api@marcarNotificacaoLida');
        
        // Upload de arquivos
        Route::post('/upload-arquivo', 'Api@uploadArquivo');
    });

    // ========================================================================
    // PÁGINAS ADMINISTRATIVAS
    // ========================================================================
    Route::get('/pagina/erro', 'Pagina@erro');
    Route::get('/sobre', 'Pagina@sobre');

});

// ============================================================================
// MIDDLEWARE CUSTOMIZADO PARA SUPERVISOR OU ADMIN
// ============================================================================
Route::middleware('supervisor_ou_admin', function($request) {
    if (!isset($_SESSION['usuario_perfil']) || 
        !in_array($_SESSION['usuario_perfil'], ['admin', 'supervisor'])) {
        Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
        Helper::redirecionar('dashboard');
        return false;
    }
    return true;
});

// ============================================================================
// MIDDLEWARE CUSTOMIZADO PARA ADMIN
// ============================================================================
Route::middleware('admin', function($request) {
    if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
        Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado - Apenas administradores', 'alert alert-danger');
        Helper::redirecionar('dashboard');
        return false;
    }
    return true;
});

// ============================================================================
// ROTAS DE FALLBACK (404)
// ============================================================================
// Qualquer rota não definida será redirecionada para erro 404
// O sistema não usa mais roteamento automático por segurança 