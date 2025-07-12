<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NOME ?> - Chat</title>
    <link rel="icon" href="<?= Helper::asset('img/logo.png') ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="<?= Helper::asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= Helper::asset('css/dashboard.css') ?>">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fab fa-whatsapp"></i>
                    <?= APP_NOME ?>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="<?= URL ?>/dashboard" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/chat" class="nav-link active">
                        <i class="fas fa-comments"></i>
                        Chat
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/contatos" class="nav-link">
                        <i class="fas fa-address-book"></i>
                        Contatos
                    </a>
                </div>
                
                <?php if (in_array($usuario_logado['perfil'], ['admin', 'supervisor'])): ?>
                <div class="nav-item">
                    <a href="<?= URL ?>/relatorios" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Relatórios
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/usuarios" class="nav-link">
                        <i class="fas fa-users"></i>
                        Usuários
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if ($usuario_logado['perfil'] === 'admin'): ?>
                <div class="nav-item">
                    <a href="<?= URL ?>/configuracoes" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Conteúdo principal -->
        <main class="main-content" id="mainContent">
            <!-- Header -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="topbar-title">
                        <i class="fas fa-comments me-2"></i>
                        Chat WhatsApp
                    </h1>
                </div>
                
                <div class="topbar-right">
                    <!-- Status da API -->
                    <div class="api-status me-3">
                        <div class="badge <?= $api_status['conectado'] ? 'bg-success' : 'bg-danger' ?>">
                            <i class="fas fa-circle me-1"></i>
                            <?= $api_status['conectado'] ? 'Conectado' : 'Desconectado' ?>
                        </div>
                        <small class="text-muted ms-2">
                            Token: <?= $token_status['tempo_restante_formatado'] ?? 'N/A' ?>
                        </small>
                    </div>
                    
                    <!-- Toggle Dark Mode -->
                    <button class="btn btn-outline-secondary btn-sm me-2" id="toggleTheme" title="Alternar tema">
                        <i class="fas fa-moon"></i>
                    </button>
                    
                    <!-- Status do usuário -->
                    <div class="status-badge status-<?= $usuario_logado['status'] === 'ativo' ? 'online' : ($usuario_logado['status'] === 'ausente' ? 'away' : 'busy') ?>">
                        <span class="status-indicator"></span>
                        <?= ucfirst($usuario_logado['status']) ?>
                    </div>
                    
                    <!-- Menu do usuário -->
                    <div class="user-menu">
                        <div class="user-avatar" title="<?= $usuario_logado['nome'] ?>">
                            <?= strtoupper(substr($usuario_logado['nome'], 0, 2)) ?>
                        </div>
                    </div>
                    
                    <!-- Logout -->
                    <a href="<?= URL ?>/logout" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="chat-container">
                <div class="row h-100">
                    <!-- Lista de Conversas -->
                    <div class="col-md-4 col-lg-3 chat-sidebar">
                        <div class="chat-sidebar-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-comments me-2"></i>
                                    Conversas
                                </h5>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#novaConversaModal">
                                    <i class="fas fa-plus"></i>
                                    Nova
                                </button>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="chat-filters">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary active" data-filter="todas">
                                    Todas
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-filter="ativas">
                                    Ativas
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-filter="pendentes">
                                    Pendentes
                                </button>
                            </div>
                        </div>
                        
                        <!-- Lista de Conversas -->
                        <div class="chat-list" id="chatList">
                            <?php 
                            // Determinar qual lista de conversas usar baseado no perfil
                            $conversas_para_exibir = [];
                            if (isset($minhas_conversas) && !empty($minhas_conversas)) {
                                $conversas_para_exibir = $minhas_conversas;
                            } elseif (isset($conversas_ativas) && !empty($conversas_ativas)) {
                                $conversas_para_exibir = $conversas_ativas;
                            }
                            ?>
                            
                            <?php if (!empty($conversas_para_exibir)): ?>
                                <?php foreach ($conversas_para_exibir as $conversa): ?>
                                    <div class="chat-item" data-conversa-id="<?= $conversa->id ?>" data-status="<?= $conversa->status ?>">
                                        <div class="chat-avatar">
                                            <div class="avatar-circle">
                                                <?= strtoupper(substr($conversa->contato_nome ?? 'C', 0, 2)) ?>
                                            </div>
                                            <?php if (isset($conversa->mensagens_nao_lidas) && $conversa->mensagens_nao_lidas > 0): ?>
                                                <span class="badge bg-danger"><?= $conversa->mensagens_nao_lidas ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="chat-info">
                                            <div class="chat-name"><?= $conversa->contato_nome ?? 'Sem nome' ?></div>
                                            <div class="chat-last-message">
                                                <i class="fas fa-phone me-1"></i>
                                                <?= $conversa->numero ?>
                                            </div>
                                            <div class="chat-time">
                                                <?= date('d/m H:i', strtotime($conversa->ultima_mensagem ?? $conversa->criado_em)) ?>
                                            </div>
                                        </div>
                                        <div class="chat-status">
                                            <span class="badge bg-<?= $conversa->status === 'aberto' ? 'success' : ($conversa->status === 'pendente' ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($conversa->status) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-comments fa-3x text-muted"></i>
                                    <p class="text-muted mt-3">Nenhuma conversa encontrada</p>
                                    <small class="text-muted">
                                        Inicie uma nova conversa enviando um template
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Área de Chat -->
                    <div class="col-md-8 col-lg-9 chat-main">
                        <div class="chat-welcome" id="chatWelcome">
                            <div class="welcome-content">
                                <div class="welcome-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <h3>Bem-vindo ao Chat WhatsApp</h3>
                                <p class="text-muted">
                                    Selecione uma conversa para começar ou inicie uma nova conversa enviando um template.
                                </p>
                                <div class="welcome-stats">
                                    <div class="stat-item">
                                        <div class="stat-number">
                                            <?php 
                                            $total_conversas = 0;
                                            if (isset($minhas_conversas)) {
                                                $total_conversas = count($minhas_conversas);
                                            } elseif (isset($conversas_ativas)) {
                                                $total_conversas = count($conversas_ativas);
                                            }
                                            echo $total_conversas;
                                            ?>
                                        </div>
                                        <div class="stat-label">Conversas</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number"><?= count($conversas_pendentes ?? []) ?></div>
                                        <div class="stat-label">Pendentes</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chat Ativo -->
                        <div class="chat-active" id="chatActive" style="display: none;">
                            <!-- Header da Conversa -->
                            <div class="chat-header">
                                <div class="chat-header-info">
                                    <div class="chat-avatar">
                                        <div class="avatar-circle" id="chatAvatarActive">
                                            C
                                        </div>
                                    </div>
                                    <div class="chat-details">
                                        <h6 class="chat-name" id="chatNameActive">Nome do Contato</h6>
                                        <div class="chat-status-info">
                                            <span class="badge" id="chatStatusActive">Status</span>
                                            <span class="text-muted ms-2" id="chatTimeActive">Tempo</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-actions">
                                    <button class="btn btn-outline-success btn-sm" id="btnAssumirConversa" style="display: none;">
                                        <i class="fas fa-hand-paper"></i> Assumir
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" id="btnFecharConversa">
                                        <i class="fas fa-times"></i> Fechar
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Mensagens -->
                            <div class="chat-messages" id="chatMessages">
                                <!-- Mensagens serão carregadas aqui -->
                            </div>
                            
                            <!-- Área de Digitação -->
                            <div class="chat-input-area" id="chatInputArea">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" id="btnAnexar">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <input type="text" class="form-control" id="messageInput" placeholder="Digite sua mensagem...">
                                    <button class="btn btn-primary" type="button" id="btnEnviar">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <div class="input-info">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Aguarde o contato responder ao template antes de enviar mensagens.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nova Conversa -->
    <div class="modal fade" id="novaConversaModal" tabindex="-1" aria-labelledby="novaConversaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="novaConversaModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nova Conversa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNovaConversa">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> A primeira mensagem sempre deve ser um template. Você só poderá enviar mensagens de texto após o contato responder.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="numeroContato" class="form-label">
                                        <i class="fas fa-phone me-1"></i>
                                        Número do WhatsApp *
                                    </label>
                                    <input type="text" class="form-control" id="numeroContato" placeholder="(11) 99999-9999" required>
                                    <div class="form-text">Digite o número com DDD</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nomeContato" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Nome do Contato
                                    </label>
                                    <input type="text" class="form-control" id="nomeContato" placeholder="Nome do contato">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="templateSelect" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>
                                Template *
                            </label>
                            <select class="form-select" id="templateSelect" required>
                                <option value="">Selecione um template</option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= $template['nome'] ?>" data-parametros='<?= json_encode($template['parametros']) ?>'>
                                        <?= $template['titulo'] ?> - <?= $template['descricao'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="parametros-container" id="parametrosContainer" style="display: none;">
                            <h6 class="mb-3">Parâmetros do Template</h6>
                            <div id="parametrosInputs">
                                <!-- Inputs dos parâmetros serão adicionados aqui -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnEnviarTemplate">
                        <i class="fas fa-paper-plane me-2"></i>
                        Enviar Template
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload de Arquivo -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="fas fa-cloud-upload-alt me-2"></i>
                        Enviar Arquivo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formUpload" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="arquivo" class="form-label">
                                <i class="fas fa-file me-1"></i>
                                Selecionar Arquivo
                            </label>
                            <input type="file" class="form-control" id="arquivo" name="arquivo" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar" required>
                            <div class="form-text">
                                Tipos suportados: Imagem, Áudio, Vídeo, PDF, Word, Texto, ZIP, RAR (máx. 16MB)
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="caption" class="form-label">
                                <i class="fas fa-comment me-1"></i>
                                Legenda (opcional)
                            </label>
                            <textarea class="form-control" id="caption" name="caption" rows="3" placeholder="Digite uma legenda para o arquivo..."></textarea>
                        </div>
                        
                        <div class="upload-preview" id="uploadPreview" style="display: none;">
                            <div class="preview-content">
                                <div class="preview-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="preview-info">
                                    <div class="preview-name"></div>
                                    <div class="preview-size"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnEnviarArquivo">
                        <i class="fas fa-cloud-upload-alt me-2"></i>
                        Enviar Arquivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= Helper::asset('js/app.js') ?>"></script>
    <script src="<?= Helper::asset('js/dashboard.js') ?>"></script>
    
    <script>
        // Variáveis globais
        let conversaAtiva = null;
        let intervaloBuscaMensagens = null;
        
        // Inicialização
        $(document).ready(function() {
            initializeChat();
            setupEventListeners();
            
            // Buscar mensagens a cada 5 segundos se há conversa ativa
            setInterval(() => {
                if (conversaAtiva) {
                    buscarMensagensConversa(conversaAtiva);
                }
            }, 5000);
        });
        
        // Configurar event listeners
        function setupEventListeners() {
            // Clique em item da lista de conversas
            $(document).on('click', '.chat-item', function() {
                const conversaId = $(this).data('conversa-id');
                abrirConversa(conversaId);
            });
            
            // Enviar mensagem
            $('#btnEnviar').on('click', enviarMensagem);
            $('#messageInput').on('keypress', function(e) {
                if (e.which === 13) {
                    enviarMensagem();
                }
            });
            
            // Anexar arquivo
            $('#btnAnexar').on('click', function() {
                $('#uploadModal').modal('show');
            });
            
            // Enviar template
            $('#btnEnviarTemplate').on('click', enviarTemplate);
            
            // Enviar arquivo
            $('#btnEnviarArquivo').on('click', enviarArquivo);
            
            // Mudança de template
            $('#templateSelect').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const parametros = selectedOption.data('parametros') || [];
                
                if (parametros.length > 0) {
                    $('#parametrosContainer').show();
                    gerarInputsParametros(parametros);
                } else {
                    $('#parametrosContainer').hide();
                }
            });
            
            // Preview de arquivo
            $('#arquivo').on('change', function() {
                const file = this.files[0];
                if (file) {
                    mostrarPreviewArquivo(file);
                }
            });
            
            // Filtros de conversa
            $('.chat-filters .btn').on('click', function() {
                $('.chat-filters .btn').removeClass('active');
                $(this).addClass('active');
                
                const filtro = $(this).data('filter');
                filtrarConversas(filtro);
            });
            
            // Assumir conversa
            $('#btnAssumirConversa').on('click', function() {
                if (conversaAtiva) {
                    assumirConversa(conversaAtiva);
                }
            });
            
            // Fechar conversa
            $('#btnFecharConversa').on('click', function() {
                if (conversaAtiva) {
                    fecharConversa(conversaAtiva);
                }
            });
        }
        
        // Inicializar chat
        function initializeChat() {
            // Verificar se há conversas
            if ($('.chat-item').length > 0) {
                // Abrir primeira conversa automaticamente
                const primeiraConversa = $('.chat-item').first().data('conversa-id');
                abrirConversa(primeiraConversa);
            }
        }
        
        // Abrir conversa
        function abrirConversa(conversaId) {
            conversaAtiva = conversaId;
            
            // Marcar como ativa na lista
            $('.chat-item').removeClass('active');
            $(`.chat-item[data-conversa-id="${conversaId}"]`).addClass('active');
            
            // Esconder welcome e mostrar chat
            $('#chatWelcome').hide();
            $('#chatActive').show();
            
            // Buscar dados da conversa
            buscarDadosConversa(conversaId);
            
            // Buscar mensagens
            buscarMensagensConversa(conversaId);
        }
        
        // Buscar dados da conversa
        function buscarDadosConversa(conversaId) {
            // Pegar dados do item da lista
            const item = $(`.chat-item[data-conversa-id="${conversaId}"]`);
            const nome = item.find('.chat-name').text();
            const status = item.data('status');
            const tempo = item.find('.chat-time').text();
            
            // Atualizar header
            $('#chatNameActive').text(nome);
            $('#chatAvatarActive').text(nome.substr(0, 2).toUpperCase());
            
            const statusClass = status === 'aberto' ? 'bg-success' : (status === 'pendente' ? 'bg-warning' : 'bg-secondary');
            $('#chatStatusActive').removeClass().addClass(`badge ${statusClass}`).text(status.toUpperCase());
            $('#chatTimeActive').text(tempo);
            
            // Mostrar/esconder botões
            if (status === 'pendente') {
                $('#btnAssumirConversa').show();
            } else {
                $('#btnAssumirConversa').hide();
            }
        }
        
        // Buscar mensagens da conversa
        function buscarMensagensConversa(conversaId) {
            $.ajax({
                url: `<?= URL ?>/chat/buscar-mensagens/${conversaId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderizarMensagens(response.mensagens);
                    }
                },
                error: function() {
                    console.log('Erro ao buscar mensagens');
                }
            });
        }
        
        // Renderizar mensagens
        function renderizarMensagens(mensagens) {
            const container = $('#chatMessages');
            container.empty();
            
            if (mensagens.length === 0) {
                container.html(`
                    <div class="empty-messages">
                        <i class="fas fa-comments fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Nenhuma mensagem ainda</p>
                    </div>
                `);
                return;
            }
            
            mensagens.forEach(mensagem => {
                const isOutgoing = mensagem.direcao === 'saida';
                const messageClass = isOutgoing ? 'message-outgoing' : 'message-incoming';
                
                const messageHtml = `
                    <div class="message ${messageClass}">
                        <div class="message-content">
                            <div class="message-text">${mensagem.conteudo}</div>
                            <div class="message-time">
                                ${formatarTempo(mensagem.criado_em)}
                                ${isOutgoing ? '<i class="fas fa-check text-success"></i>' : ''}
                            </div>
                        </div>
                    </div>
                `;
                
                container.append(messageHtml);
            });
            
            // Scroll para o fim
            container.scrollTop(container[0].scrollHeight);
        }
        
        // Enviar mensagem
        function enviarMensagem() {
            const mensagem = $('#messageInput').val().trim();
            
            if (!mensagem || !conversaAtiva) {
                return;
            }
            
            const dados = {
                conversa_id: conversaAtiva,
                mensagem: mensagem
            };
            
            $.ajax({
                url: '<?= URL ?>/chat/enviar-mensagem',
                method: 'POST',
                data: JSON.stringify(dados),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        $('#messageInput').val('');
                        buscarMensagensConversa(conversaAtiva);
                        mostrarToast('Mensagem enviada!', 'success');
                    } else {
                        mostrarToast(response.message, 'error');
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('❌ Erro ao enviar mensagem');
                    console.log('Status:', xhr.status);
                    console.log('TextStatus:', textStatus);
                    console.log('ErrorThrown:', errorThrown);
                    console.log('ResponseText:', xhr.responseText);
                    
                    let mensagemErro = 'Erro ao enviar mensagem';
                    
                    // Tentar interpretar a resposta JSON mesmo em caso de erro
                    try {
                        if (xhr.responseText) {
                            // Procurar JSON na resposta
                            let jsonStart = xhr.responseText.indexOf('{');
                            let jsonEnd = xhr.responseText.lastIndexOf('}');
                            
                            if (jsonStart !== -1 && jsonEnd !== -1) {
                                let jsonString = xhr.responseText.substring(jsonStart, jsonEnd + 1);
                                const response = JSON.parse(jsonString);
                                
                                if (response && response.message) {
                                    mensagemErro = response.message;
                                    
                                    // Tratamento especial para conversa expirada
                                    if (xhr.status === 410 && response.expirada) {
                                        mensagemErro += '\n\nA conversa será removida da lista.';
                                        
                                        // Remover conversa da lista após 3 segundos
                                        setTimeout(() => {
                                            $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).fadeOut();
                                            $('#chatActive').hide();
                                            $('#chatWelcome').show();
                                            conversaAtiva = null;
                                        }, 3000);
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        console.log('Erro ao fazer parse da resposta de erro:', e);
                    }
                    
                    // Verificar códigos HTTP específicos
                    if (xhr.status === 410) {
                        mensagemErro = mensagemErro || 'Conversa expirada. Envie um novo template para reiniciar o contato.';
                    } else if (xhr.status === 400) {
                        mensagemErro = mensagemErro || 'Aguarde o contato responder ao template antes de enviar mensagens.';
                    } else if (xhr.status === 404) {
                        mensagemErro = 'Conversa não encontrada';
                    } else if (xhr.status === 500) {
                        mensagemErro = 'Erro interno do servidor';
                    } else if (xhr.status === 0) {
                        mensagemErro = 'Erro de conexão';
                    }
                    
                    mostrarToast(mensagemErro, 'error');
                }
            });
        }
        
        // Enviar template
        function enviarTemplate() {
            const numero = $('#numeroContato').val().trim();
            const nome = $('#nomeContato').val().trim();
            const template = $('#templateSelect').val();
            
            if (!numero || !template) {
                mostrarToast('Número e template são obrigatórios', 'error');
                return;
            }
            
            // Coletar parâmetros
            const parametros = [];
            $('#parametrosInputs input').each(function() {
                parametros.push($(this).val().trim());
            });
            
            const dados = {
                numero: numero,
                nome: nome,
                template: template,
                parametros: parametros
            };
            
            console.log('Enviando dados:', dados);
            
            $('#btnEnviarTemplate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enviando...');
            
            $.ajax({
                url: '<?= URL ?>/chat/iniciar-conversa',
                method: 'POST',
                data: JSON.stringify(dados),
                contentType: 'application/json',
                dataType: 'text', // Mudança para text para processar manualmente
                timeout: 30000,
                beforeSend: function(xhr) {
                    console.log('Enviando requisição AJAX...');
                },
                success: function(responseText, textStatus, xhr) {
                    console.log('Resposta recebida - Status HTTP:', xhr.status);
                    console.log('Resposta bruta:', responseText);
                    
                    // Tentar extrair JSON válido da resposta
                    let response = null;
                    try {
                        // Procurar pelo JSON na resposta (pode ter HTML/warnings antes)
                        let jsonStart = responseText.indexOf('{');
                        let jsonEnd = responseText.lastIndexOf('}');
                        
                        if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
                            let jsonString = responseText.substring(jsonStart, jsonEnd + 1);
                            response = JSON.parse(jsonString);
                            console.log('JSON extraído:', response);
                        } else {
                            throw new Error('JSON não encontrado na resposta');
                        }
                    } catch (e) {
                        console.log('Erro ao extrair JSON:', e);
                        
                        // Se não conseguir extrair JSON, tratar como erro
                        mostrarToast('Resposta inválida do servidor', 'error');
                        return;
                    }
                    
                    // Verificar se a resposta é um objeto válido
                    if (typeof response === 'object' && response !== null) {
                        if (response.success === true) {
                            console.log('✅ Sucesso detectado');
                            $('#novaConversaModal').modal('hide');
                            $('#formNovaConversa')[0].reset();
                            $('#parametrosContainer').hide();
                            mostrarToast('Template enviado com sucesso!', 'success');
                            
                            // Recarregar lista de conversas
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            console.log('❌ Falha na resposta:', response.message);
                            mostrarToast(response.message || 'Erro desconhecido', 'error');
                        }
                    } else {
                        console.log('❌ Resposta inválida:', response);
                        mostrarToast('Resposta inválida do servidor', 'error');
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('❌ Erro AJAX');
                    console.log('Status:', xhr.status);
                    console.log('TextStatus:', textStatus);
                    console.log('ErrorThrown:', errorThrown);
                    console.log('ResponseText:', xhr.responseText);
                    
                    let mensagemErro = 'Erro ao enviar template';
                    
                    // Tentar interpretar a resposta mesmo em caso de erro
                    try {
                        // Procurar JSON mesmo em respostas de erro
                        let jsonStart = xhr.responseText.indexOf('{');
                        let jsonEnd = xhr.responseText.lastIndexOf('}');
                        
                        if (jsonStart !== -1 && jsonEnd !== -1) {
                            let jsonString = xhr.responseText.substring(jsonStart, jsonEnd + 1);
                            const response = JSON.parse(jsonString);
                            
                            if (response && response.message) {
                                mensagemErro = response.message;
                            }
                        }
                    } catch (e) {
                        console.log('Erro ao fazer parse da resposta de erro:', e);
                    }
                    
                    // Verificar se foi erro HTTP específico
                    if (xhr.status === 500) {
                        mensagemErro = 'Erro interno do servidor';
                    } else if (xhr.status === 400) {
                        mensagemErro = 'Dados inválidos';
                    } else if (xhr.status === 0) {
                        mensagemErro = 'Erro de conexão';
                    }
                    
                    mostrarToast(mensagemErro, 'error');
                },
                complete: function() {
                    console.log('Requisição completada');
                    $('#btnEnviarTemplate').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Enviar Template');
                }
            });
        }
        
        // Enviar arquivo
        function enviarArquivo() {
            const formData = new FormData();
            const arquivo = $('#arquivo')[0].files[0];
            const caption = $('#caption').val().trim();
            
            if (!arquivo || !conversaAtiva) {
                return;
            }
            
            formData.append('arquivo', arquivo);
            formData.append('caption', caption);
            formData.append('conversa_id', conversaAtiva);
            
            $('#btnEnviarArquivo').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enviando...');
            
            $.ajax({
                url: '<?= URL ?>/chat/enviar-midia',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#uploadModal').modal('hide');
                        $('#formUpload')[0].reset();
                        $('#uploadPreview').hide();
                        buscarMensagensConversa(conversaAtiva);
                        mostrarToast('Arquivo enviado!', 'success');
                    } else {
                        mostrarToast(response.message, 'error');
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('❌ Erro ao enviar arquivo');
                    console.log('Status:', xhr.status);
                    console.log('TextStatus:', textStatus);
                    console.log('ErrorThrown:', errorThrown);
                    console.log('ResponseText:', xhr.responseText);
                    
                    let mensagemErro = 'Erro ao enviar arquivo';
                    
                    // Tentar interpretar a resposta JSON mesmo em caso de erro
                    try {
                        if (xhr.responseText) {
                            // Procurar JSON na resposta
                            let jsonStart = xhr.responseText.indexOf('{');
                            let jsonEnd = xhr.responseText.lastIndexOf('}');
                            
                            if (jsonStart !== -1 && jsonEnd !== -1) {
                                let jsonString = xhr.responseText.substring(jsonStart, jsonEnd + 1);
                                const response = JSON.parse(jsonString);
                                
                                if (response && response.message) {
                                    mensagemErro = response.message;
                                    
                                    // Tratamento especial para conversa expirada
                                    if (xhr.status === 410 && response.expirada) {
                                        mensagemErro += '\n\nA conversa será removida da lista.';
                                        
                                        // Remover conversa da lista após 3 segundos
                                        setTimeout(() => {
                                            $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).fadeOut();
                                            $('#chatActive').hide();
                                            $('#chatWelcome').show();
                                            conversaAtiva = null;
                                        }, 3000);
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        console.log('Erro ao fazer parse da resposta de erro:', e);
                    }
                    
                    // Verificar códigos HTTP específicos
                    if (xhr.status === 410) {
                        mensagemErro = mensagemErro || 'Conversa expirada. Envie um novo template para reiniciar o contato.';
                    } else if (xhr.status === 400) {
                        mensagemErro = mensagemErro || 'Aguarde o contato responder ao template antes de enviar arquivos.';
                    } else if (xhr.status === 404) {
                        mensagemErro = 'Conversa não encontrada';
                    } else if (xhr.status === 500) {
                        mensagemErro = 'Erro interno do servidor';
                    } else if (xhr.status === 0) {
                        mensagemErro = 'Erro de conexão';
                    }
                    
                    mostrarToast(mensagemErro, 'error');
                },
                complete: function() {
                    $('#btnEnviarArquivo').prop('disabled', false).html('<i class="fas fa-cloud-upload-alt me-2"></i>Enviar Arquivo');
                }
            });
        }
        
        // Gerar inputs de parâmetros
        function gerarInputsParametros(parametros) {
            const container = $('#parametrosInputs');
            container.empty();
            
            parametros.forEach((param, index) => {
                const input = `
                    <div class="mb-3">
                        <label class="form-label">${param}</label>
                        <input type="text" class="form-control" placeholder="Digite o valor para ${param}" required>
                    </div>
                `;
                container.append(input);
            });
        }
        
        // Mostrar preview do arquivo
        function mostrarPreviewArquivo(file) {
            const preview = $('#uploadPreview');
            const name = preview.find('.preview-name');
            const size = preview.find('.preview-size');
            
            name.text(file.name);
            size.text(formatarTamanho(file.size));
            
            // Ícone baseado no tipo
            let icon = 'fas fa-file';
            if (file.type.startsWith('image/')) {
                icon = 'fas fa-image';
            } else if (file.type.startsWith('audio/')) {
                icon = 'fas fa-music';
            } else if (file.type.startsWith('video/')) {
                icon = 'fas fa-video';
            } else if (file.type === 'application/pdf') {
                icon = 'fas fa-file-pdf';
            }
            
            preview.find('.preview-icon i').removeClass().addClass(icon);
            preview.show();
        }
        
        // Filtrar conversas
        function filtrarConversas(filtro) {
            $('.chat-item').each(function() {
                const status = $(this).data('status');
                let mostrar = true;
                
                if (filtro === 'ativas' && status !== 'aberto') {
                    mostrar = false;
                } else if (filtro === 'pendentes' && status !== 'pendente') {
                    mostrar = false;
                }
                
                $(this).toggle(mostrar);
            });
        }
        
        // Assumir conversa
        function assumirConversa(conversaId) {
            $.ajax({
                url: `<?= URL ?>/chat/assumir/${conversaId}`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        mostrarToast('Conversa assumida!', 'success');
                        // Atualizar status
                        $(`.chat-item[data-conversa-id="${conversaId}"]`).data('status', 'aberto');
                        $('#btnAssumirConversa').hide();
                        $('#chatStatusActive').removeClass().addClass('badge bg-success').text('ABERTO');
                    } else {
                        mostrarToast(response.message, 'error');
                    }
                },
                error: function() {
                    mostrarToast('Erro ao assumir conversa', 'error');
                }
            });
        }
        
        // Fechar conversa
        function fecharConversa(conversaId) {
            if (!confirm('Tem certeza que deseja fechar esta conversa?')) {
                return;
            }
            
            $.ajax({
                url: `<?= URL ?>/chat/fechar/${conversaId}`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        mostrarToast('Conversa fechada!', 'success');
                        // Remover da lista ou atualizar status
                        $(`.chat-item[data-conversa-id="${conversaId}"]`).fadeOut();
                        $('#chatActive').hide();
                        $('#chatWelcome').show();
                        conversaAtiva = null;
                    } else {
                        mostrarToast(response.message, 'error');
                    }
                },
                error: function() {
                    mostrarToast('Erro ao fechar conversa', 'error');
                }
            });
        }
        
        // Funções auxiliares
        function formatarTempo(timestamp) {
            const data = new Date(timestamp);
            return data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }
        
        function formatarTamanho(bytes) {
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            if (bytes === 0) return '0 Bytes';
            const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }
        
        function mostrarToast(mensagem, tipo) {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${tipo === 'success' ? 'success' : (tipo === 'warning' ? 'warning' : 'danger')} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${tipo === 'success' ? 'check' : (tipo === 'warning' ? 'exclamation-triangle' : 'times-circle')} me-2"></i>
                            ${mensagem}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            const toastContainer = $('#toastContainer');
            if (toastContainer.length === 0) {
                $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            }
            
            const toast = $(toastHtml);
            $('#toastContainer').append(toast);
            
            const bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
            
            // Remover após 5 segundos
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        // Verificar status da conversa ativa
        function verificarStatusConversa() {
            if (!conversaAtiva) {
                return;
            }
            
            $.ajax({
                url: `<?= URL ?>/chat/status-conversa/${conversaAtiva}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const status = response.status;
                        
                        // Se conversa não está mais ativa, alertar
                        if (!status.conversa_ativa) {
                            mostrarToast('Conversa expirada! Envie um novo template para reiniciar o contato.', 'error');
                            
                            // Remover conversa da lista
                            $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).fadeOut();
                            $('#chatActive').hide();
                            $('#chatWelcome').show();
                            conversaAtiva = null;
                            return;
                        }
                        
                        // Alertar se está próximo de expirar (menos de 1 hora)
                        if (status.tempo_restante < 3600 && status.tempo_restante > 0) {
                            const horas = Math.floor(status.tempo_restante / 3600);
                            const minutos = Math.floor((status.tempo_restante % 3600) / 60);
                            const tempoFormatado = horas > 0 ? `${horas}h ${minutos}m` : `${minutos}m`;
                            
                            // Mostrar alerta apenas uma vez por conversa
                            if (!$(`.chat-item[data-conversa-id="${conversaAtiva}"]`).hasClass('alerta-expirar')) {
                                $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).addClass('alerta-expirar');
                                mostrarToast(`Atenção: Conversa expira em ${tempoFormatado}`, 'warning');
                            }
                        }
                        
                        // Atualizar indicador visual se o contato ainda não respondeu
                        if (!status.contato_respondeu) {
                            $('#messageInput').attr('placeholder', 'Aguardando resposta do contato ao template...');
                            $('#messageInput').prop('disabled', true);
                            $('#btnEnviarMensagem').prop('disabled', true);
                        } else {
                            $('#messageInput').attr('placeholder', 'Digite sua mensagem...');
                            $('#messageInput').prop('disabled', false);
                            $('#btnEnviarMensagem').prop('disabled', false);
                        }
                    }
                },
                error: function() {
                    // Ignorar erros na verificação de status
                }
            });
        }
        
        // Verificar status das conversas a cada 5 minutos
        setInterval(verificarStatusConversa, 5 * 60 * 1000);
        
        // Modificar a função abrirConversa existente para incluir verificação de status
        const abrirConversaOriginal = abrirConversa;
        abrirConversa = function(conversaId) {
            // Chamar função original
            abrirConversaOriginal(conversaId);
            
            // Verificar status da conversa após abrir
            setTimeout(verificarStatusConversa, 1000);
        };
    </script>
    
    <style>
        .chat-container {
            height: calc(100vh - 120px);
            overflow: hidden;
        }
        
        .chat-sidebar {
            border-right: 1px solid var(--border-color);
            height: 100%;
            overflow-y: auto;
        }
        
        .chat-sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--card-bg);
        }
        
        .chat-filters {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .chat-list {
            height: calc(100% - 140px);
            overflow-y: auto;
        }
        
        .chat-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .chat-item:hover {
            background: var(--hover-bg);
        }
        
        .chat-item.active {
            background: var(--primary-color);
            color: white;
        }
        
        .chat-avatar {
            position: relative;
            margin-right: 1rem;
        }
        
        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .chat-item.active .avatar-circle {
            background: white;
            color: var(--primary-color);
        }
        
        .chat-info {
            flex: 1;
        }
        
        .chat-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .chat-last-message {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .chat-item.active .chat-last-message {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .chat-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        .chat-item.active .chat-time {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .chat-main {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .chat-welcome {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .welcome-content {
            text-align: center;
        }
        
        .welcome-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .welcome-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .chat-active {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--card-bg);
        }
        
        .chat-header-info {
            display: flex;
            align-items: center;
        }
        
        .chat-header .chat-avatar {
            margin-right: 1rem;
        }
        
        .chat-header .avatar-circle {
            width: 40px;
            height: 40px;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: var(--chat-bg);
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
        }
        
        .message-outgoing {
            justify-content: flex-end;
        }
        
        .message-incoming {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            position: relative;
        }
        
        .message-outgoing .message-content {
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 0.25rem;
        }
        
        .message-incoming .message-content {
            background: white;
            color: var(--text-primary);
            border-bottom-left-radius: 0.25rem;
            border: 1px solid var(--border-color);
        }
        
        .message-text {
            margin-bottom: 0.25rem;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            text-align: right;
        }
        
        .chat-input-area {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            background: var(--card-bg);
        }
        
        .input-info {
            margin-top: 0.5rem;
            text-align: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-messages {
            text-align: center;
            padding: 2rem;
        }
        
        .api-status {
            display: flex;
            align-items: center;
        }
        
        .parametros-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .upload-preview {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .preview-content {
            display: flex;
            align-items: center;
        }
        
        .preview-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-right: 1rem;
        }
        
        .preview-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .preview-size {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .chat-container {
                height: calc(100vh - 100px);
            }
            
            .chat-sidebar {
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            
            .welcome-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .message-content {
                max-width: 85%;
            }
        }
    </style>
</body>
</html> 