<?php include 'app/Views/include/head.php' ?>

<?php
// Preparar dados do usuário para o menu dinâmico
$usuario = [
    'id' => $usuario_logado['id'],
    'nome' => $usuario_logado['nome'],
    'email' => $usuario_logado['email'],
    'perfil' => $usuario_logado['perfil']
];
?>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <?php include 'app/Views/include/menu_sidebar.php' ?>
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
                    <!-- <div class="api-status me-3">
                        <div class="badge <?= $api_status['conectado'] ? 'bg-success' : 'bg-danger' ?>">
                            <i class="fas fa-circle me-1"></i>
                            <?= $api_status['conectado'] ? 'Conectado' : 'Desconectado' ?>
                        </div>
                        <small class="text-muted ms-2">
                            Token: <?= $token_status['tempo_restante_formatado'] ?? 'N/A' ?>
                        </small>
                    </div> -->

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
                            <div class="mt-2">
                                <small class="text-muted" id="contadorConversas">
                                    <?php
                                    $total_conversas = 0;
                                    if (isset($minhas_conversas) && !empty($minhas_conversas)) {
                                        $total_conversas = count($minhas_conversas);
                                    } elseif (isset($conversas_ativas) && !empty($conversas_ativas)) {
                                        $total_conversas = count($conversas_ativas);
                                    }
                                    ?>
                                    Mostrando <?= $total_conversas ?> de <?= $total_conversas ?> conversas
                                </small>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="chat-filters">
                            <div class="mb-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="searchConversas" placeholder="Buscar conversas...">
                                </div>
                            </div>
                            
                            <?php if (in_array($usuario_logado['perfil'], ['admin', 'supervisor'])): ?>
                            <div class="mb-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <select class="form-select" id="filtroDepartamento">
                                        <option value="">Todos os Departamentos</option>
                                        <?php 
                                        // Buscar todos os departamentos para o filtro
                                        $departamentosHelper = new DepartamentoHelper();
                                        $todosDepartamentos = $departamentosHelper->obterDepartamentosDisponiveis();
                                        foreach ($todosDepartamentos as $dept): 
                                        ?>
                                            <option value="<?= $dept->id ?>" style="color: <?= $dept->cor ?>">
                                                <?= htmlspecialchars($dept->nome) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <select class="form-select" id="filtroAtendente">
                                        <option value="">Todos os Atendentes</option>
                                        <?php 
                                        // Buscar todos os atendentes para o filtro
                                        $usuarioModel = new UsuarioModel();
                                        $todosAtendentes = $usuarioModel->listarUsuarios();
                                        foreach ($todosAtendentes as $atendente): 
                                            if ($atendente->perfil === 'atendente'):
                                        ?>
                                            <option value="<?= $atendente->id ?>">
                                                <?= htmlspecialchars($atendente->nome) ?>
                                            </option>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                            
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
                            // DEBUG: Verificar dados recebidos
                            if (isset($departamentos_usuario) && !empty($departamentos_usuario)) {
                                echo '<div class="alert alert-info mb-3">';
                                echo '<strong>Departamentos do usuário:</strong><br>';
                                foreach ($departamentos_usuario as $dept) {
                                    echo "- {$dept->nome} (ID: {$dept->id})<br>";
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-warning mb-3">';
                                echo '<strong>⚠️ Usuário não tem departamentos associados!</strong><br>';
                                echo 'Adicione o usuário a um departamento para ver conversas.';
                                echo '</div>';
                            }
                            
                            // Determinar qual lista de conversas usar baseado no perfil
                            $conversas_para_exibir = [];
                            if (isset($minhas_conversas) && !empty($minhas_conversas)) {
                                $conversas_para_exibir = $minhas_conversas;
                                echo '<div class="alert alert-success mb-3">';
                                echo '<strong>✅ Conversas filtradas por departamento:</strong> ' . count($minhas_conversas) . ' conversas encontradas';
                                echo '</div>';
                            } elseif (isset($conversas_ativas) && !empty($conversas_ativas)) {
                                $conversas_para_exibir = $conversas_ativas;
                                echo '<div class="alert alert-info mb-3">';
                                echo '<strong>ℹ️ Conversas gerais (admin/supervisor):</strong> ' . count($conversas_ativas) . ' conversas encontradas';
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-warning mb-3">';
                                echo '<strong>⚠️ Nenhuma conversa encontrada</strong><br>';
                                echo 'Verifique se há conversas nos departamentos do usuário.';
                                echo '</div>';
                            }
                            ?>

                            <?php if (!empty($conversas_para_exibir)): ?>
                                <?php foreach ($conversas_para_exibir as $conversa): ?>
                                    <div class="chat-item" data-conversa-id="<?= $conversa->id ?>" data-status="<?= $conversa->status ?>" data-departamento-id="<?= $conversa->departamento_id ?? '' ?>" data-atendente-id="<?= $conversa->atendente_id ?? '' ?>">
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
                                            <?php if (isset($conversa->departamento_nome)): ?>
                                            <div class="chat-department">
                                                <i class="fas fa-building me-1" style="color: <?= $conversa->departamento_cor ?? '#6c757d' ?>"></i>
                                                <small class="text-muted"><?= htmlspecialchars($conversa->departamento_nome) ?></small>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (isset($conversa->atendente_nome) && $conversa->atendente_nome): ?>
                                            <div class="chat-attendant">
                                                <i class="fas fa-user me-1" style="color: var(--primary-color);"></i>
                                                <small class="text-muted"><?= htmlspecialchars($conversa->atendente_nome) ?></small>
                                            </div>
                                            <?php endif; ?>
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
                                    <?php if (in_array($usuario_logado['perfil'], ['admin', 'supervisor'])): ?>
                                        <button class="btn btn-outline-warning btn-sm" id="btnTransferirConversaHeader" style="display: none;">
                                            <i class="fas fa-exchange-alt"></i> Transferir
                                        </button>
                                    <?php endif; ?>
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
                                    <input type="text" class="form-control" id="numeroContato" placeholder="556299999999" required>
                                    <div class="form-text">Digite o número com DDI + DDD + Celular sem o 9 digito</div>
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

                        <?php //if ($usuario_logado['perfil'] === 'atendente' && !empty($departamentos_usuario)): ?>
                        <div class="mb-3">
                            <label for="departamentoSelect" class="form-label">
                                <i class="fas fa-building me-1"></i>
                                Departamento *
                            </label>
                            <select class="form-select" id="departamentoSelect" required>
                                <option value="">Selecione um departamento</option>
                                <?php foreach ($departamentos_usuario as $departamento): ?>
                                    <option value="<?= $departamento->id ?>" style="color: <?= $departamento->cor ?>">
                                        <?= htmlspecialchars($departamento->nome) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Escolha o departamento para iniciar a conversa</div>
                        </div>
                        <?php //endif; ?>

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

    <!-- Modal Transferir Conversa -->
    <div class="modal fade" id="transferirModal" tabindex="-1" aria-labelledby="transferirModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferirModalLabel">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transferir Conversa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Transferir conversa:</strong> Selecione um atendente para receber esta conversa.
                    </div>

                    <div class="mb-3">
                        <label for="atendenteSelect" class="form-label">
                            <i class="fas fa-user me-1"></i>
                            Selecionar Atendente *
                        </label>
                        <select class="form-select" id="atendenteSelect" required>
                            <option value="">Carregando atendentes...</option>
                        </select>
                        <div class="form-text">Apenas atendentes ativos são exibidos</div>
                    </div>

                    <div class="atendentes-info" id="atendentesInfo" style="display: none;">
                        <h6 class="mb-2">Informações do Atendente</h6>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Nome:</strong> <span id="atendenteNome"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Email:</strong> <span id="atendenteEmail"></span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Conversas Ativas:</strong> <span id="atendenteConversas"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong> 
                                        <span id="atendenteStatus" class="badge"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnTransferirConversa">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transferir Conversa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>

    <script>
        // Variáveis globais
        let conversaAtiva = null;
        let intervaloBuscaMensagens = null;
        let isUserScrolling = false;
        let scrollTimeout = null;
        let lastScrollPosition = 0;
        let audioIsPlaying = false;

        // Inicialização
        $(document).ready(function() {
            initializeChat();
            setupEventListeners();
            
            // Detectar quando um áudio começa a tocar
            $(document).on('play', 'audio', function() {
                audioIsPlaying = true;
            });

            // Detectar quando o áudio é pausado ou termina
            $(document).on('pause ended', 'audio', function() {
                // Verifica se ainda tem algum áudio tocando
                audioIsPlaying = $('audio').toArray().some(a => !a.paused && !a.ended);
            });
            
            // Altere o setInterval para:
            setInterval(() => {
                if (conversaAtiva && !audioIsPlaying) {
                    buscarMensagensConversa(conversaAtiva);
                    verificarStatusMensagens();
                }
                //a cada 30 segundos, verificar se tem mensagens não lidas
            }, 30000); // 30 segundos
            
            // Garantir que o layout seja recalculado quando a janela for redimensionada
            $(window).on('resize', function() {
                adjustChatLayout();
            });
            
            // Inicializar layout
            adjustChatLayout();
        });
        
        // Verificar status das mensagens periodicamente
        function verificarStatusMensagens() {
            if (!conversaAtiva) {
                return;
            }
            
            // Buscar mensagens que ainda não foram lidas
            const mensagensNaoLidas = $('.message-outgoing').filter(function() {
                const messageId = $(this).data('message-id');
                const statusIcon = $(this).find('.message-time i');
                
                // Verificar se não é status de lido (não tem text-primary)
                return messageId && !statusIcon.hasClass('text-primary');
            });
            
            if (mensagensNaoLidas.length > 0) {
                // Fazer requisição para verificar status atualizado via API REAL
                $.ajax({
                    url: `<?= URL ?>/chat/verificar-status-mensagens/${conversaAtiva}`,
                    method: 'GET',
                    timeout: 20000, // 20 segundos para múltiplas consultas à API
                    success: function(response) {
                        if (response.success && response.mensagens) {
                            // Contar mensagens atualizadas (baseado na API REAL)
                            const mensagensAtualizadas = response.mensagens.filter(msg => msg.atualizado);
                            if (mensagensAtualizadas.length > 0) {
                                mensagensAtualizadas.forEach(msg => {
                                    atualizarStatusMensagemPorSerproId(msg.serpro_message_id, msg.status_entrega);
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        // Silencioso em caso de erro
                    }
                });
            }
        }
        
        // Atualizar status das mensagens na tela
        function atualizarStatusMensagensNaTela(mensagensStatus) {
            let atualizacoes = 0;
            
            mensagensStatus.forEach(mensagem => {
                const messageElement = $(`.message[data-message-id="${mensagem.id}"]`);
                if (messageElement.length > 0) {
                    const statusAtual = messageElement.attr('data-status');
                    
                    // Só atualizar se o status mudou
                    if (statusAtual !== mensagem.status_entrega) {
                        messageElement.attr('data-status', mensagem.status_entrega);
                        const statusIcon = gerarIconeStatus(mensagem.status_entrega);
                        messageElement.find('.message-time i').replaceWith(statusIcon);
                        atualizacoes++;
                        
                        console.log(`📱 Status da mensagem ${mensagem.id} atualizado na tela: ${statusAtual} → ${mensagem.status_entrega}`);
                    }
                }
            });
            
            if (atualizacoes > 0) {
                console.log(`✅ ${atualizacoes} mensagens atualizadas na tela`);
            }
        }
        
        // Processar atualizações de status do webhook (para uso futuro)
        function processarStatusWebhook(statusData) {
            if (statusData.message_id && statusData.status) {
                atualizarStatusMensagemPorSerproId(statusData.message_id, statusData.status);
            }
        }
        
        // Conectar com WebSocket para atualizações em tempo real (futuro)
        function conectarWebSocket() {
            // Implementar WebSocket para receber atualizações em tempo real
            // Por enquanto, usamos polling a cada 5 segundos
            console.log('WebSocket não implementado, usando polling');
        }

        // Ajustar layout do chat
        function adjustChatLayout() {
            const chatContainer = $('.chat-container');
            const windowHeight = $(window).height();
            const headerHeight = $('.topbar').outerHeight() || 60;
            const availableHeight = windowHeight - headerHeight - 20; // 20px margem

            chatContainer.css('height', `${availableHeight}px`);

            // Ajustar altura da lista de conversas
            const sidebarHeader = $('.chat-sidebar-header').outerHeight() || 60;
            const chatFilters = $('.chat-filters').outerHeight() || 100; // Aumentado para incluir o campo de busca
            const chatListHeight = availableHeight - sidebarHeader - chatFilters - 20;
            $('.chat-list').css('height', `${chatListHeight}px`);

            // Ajustar altura das mensagens se conversa estiver ativa
            if (conversaAtiva) {
                adjustMessagesHeight();
            }
        }

        // Ajustar altura da área de mensagens
        function adjustMessagesHeight() {
            const chatActive = $('.chat-active');
            if (!chatActive.is(':visible')) return;

            const chatHeader = $('.chat-header').outerHeight() || 80;
            const chatInputArea = $('.chat-input-area').outerHeight() || 120;
            const chatActiveHeight = chatActive.height();
            const messagesHeight = chatActiveHeight - chatHeader - chatInputArea - 20;

            $('.chat-messages').css('height', `${messagesHeight}px`);
        }

        // Scroll para a última mensagem
        function scrollToLastMessage() {
            const messagesContainer = $('#chatMessages');
            if (messagesContainer.length > 0) {
                const scrollHeight = messagesContainer[0].scrollHeight;
                messagesContainer.animate({
                    scrollTop: scrollHeight
                }, 300);
            }
        }

        // Configurar event listeners
        function setupEventListeners() {
            // Clique em item da lista de conversas
            $(document).on('click', '.chat-item', function() {
                const conversaId = $(this).data('conversa-id');
                
                // ✅ NOVO: Remover badge de mensagens não lidas ao abrir conversa
                const badge = $(this).find('.badge.bg-danger');
                if (badge.length > 0) {
                    badge.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
                
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

            // Busca de conversas
            $('#searchConversas').on('input', function() {
                const termo = $(this).val().toLowerCase();
                buscarConversas(termo);
            });

            // Limpar busca ao pressionar Escape
            $('#searchConversas').on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $(this).val('');
                    buscarConversas('');
                }
            });

            // Filtro de departamento (apenas para admin/supervisor)
            $('#filtroDepartamento').on('change', function() {
                const departamentoId = $(this).val();
                filtrarPorDepartamento(departamentoId);
            });

            // Filtro de atendente (apenas para admin/supervisor)
            $('#filtroAtendente').on('change', function() {
                const atendenteId = $(this).val();
                filtrarPorAtendente(atendenteId);
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

            // Scroll automático na lista de conversas quando há muitas
            $('.chat-list').on('scroll', function() {
                const container = $(this);
                const scrollTop = container.scrollTop();
                const scrollHeight = container[0].scrollHeight;
                const containerHeight = container.height();

                // Se está próximo do final, pode carregar mais conversas (futuro)
                if (scrollTop + containerHeight >= scrollHeight - 50) {
                    // Placeholder para carregamento de mais conversas
                    // console.log('Próximo do final da lista de conversas');
                }
            });

            // Auto-resize do textarea da mensagem
            $('#messageInput').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';

                // Limitar altura máxima
                if (this.scrollHeight > 100) {
                    this.style.height = '100px';
                    this.style.overflowY = 'auto';
                } else {
                    this.style.overflowY = 'hidden';
                }
            });
            
            // Event listener para scroll das mensagens - marcar como lidas quando visíveis
            $(document).on('scroll', '#chatMessages', function() {
                const currentScrollPosition = $(this).scrollTop();
                const isAtBottom = isScrollAtBottom($(this));
                
                if (!isAtBottom && currentScrollPosition !== lastScrollPosition) {
                    isUserScrolling = true;
                    if (scrollTimeout) clearTimeout(scrollTimeout);
                    // Após 2 minutos sem scroll manual, reativa o scroll automático
                    scrollTimeout = setTimeout(() => {
                        isUserScrolling = false;
                    }, 2 * 60 * 1000);
                }
                if (isAtBottom) {
                    isUserScrolling = false;
                    if (scrollTimeout) {
                        clearTimeout(scrollTimeout);
                        scrollTimeout = null;
                    }
                }
                lastScrollPosition = currentScrollPosition;
            });
            
            // Event listener para quando a página fica visível/invisível
            $(document).on('visibilitychange', function() {
                if (!document.hidden && conversaAtiva) {
                    // Status das mensagens é verificado automaticamente
                }
            });
            
            // Event listener para quando o usuário clica na área de mensagens
            $(document).on('click', '#chatMessages', function() {
                // Status das mensagens é verificado automaticamente
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

            // Inicializar contador
            atualizarContadorConversas();
            
            // ✅ NOVO: Iniciar polling de mensagens
            iniciarPollingMensagens();
        }

        // Abrir conversa
        function abrirConversa(conversaId) {
            conversaAtiva = conversaId;
            
            // Resetar contador de toast para nova conversa
            contatoJaRespondeuVerificado = false;

            // Marcar como ativa na lista
            $('.chat-item').removeClass('active');
            $(`.chat-item[data-conversa-id="${conversaId}"]`).addClass('active');

            // Esconder welcome e mostrar chat
            $('#chatWelcome').hide();
            $('#chatActive').show();

            // Ajustar layout após mostrar o chat
            setTimeout(() => {
                adjustMessagesHeight();
            }, 100);

            // Buscar dados da conversa
            buscarDadosConversa(conversaId);

            // Buscar mensagens
            buscarMensagensConversa(conversaId);

            // Scroll automático para a conversa ativa na lista
            scrollToActiveChat();
        }

        // Scroll para a conversa ativa na lista
        function scrollToActiveChat() {
            const activeChat = $('.chat-item.active');
            if (activeChat.length > 0) {
                const chatList = $('.chat-list');
                const listHeight = chatList.height();
                const itemTop = activeChat.position().top;
                const itemHeight = activeChat.outerHeight();

                // Se o item não estiver visível, fazer scroll
                if (itemTop < 0 || itemTop + itemHeight > listHeight) {
                    const scrollTop = chatList.scrollTop() + itemTop - (listHeight / 2) + (itemHeight / 2);
                    chatList.animate({
                        scrollTop: scrollTop
                    }, 300);
                }
            }
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
                        
                        // ✅ NOVO: Marcar mensagens como lidas quando abrir a conversa
                        marcarMensagensComoLidas(conversaId);
                        
                        // Verificar se o contato respondeu ao template
                        verificarStatusConversa();
                    }
                },
                error: function() {
                    console.log('Erro ao buscar mensagens');
                }
            });
        }

        // ✅ NOVO: Marcar mensagens como lidas
        function marcarMensagensComoLidas(conversaId) {
            $.ajax({
                url: `<?= URL ?>/chat/marcar-mensagens-lidas/${conversaId}`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        // console.log('Mensagens marcadas como lidas');
                    }
                },
                error: function() {
                    console.log('Erro ao marcar mensagens como lidas');
                }
            });
        }

        // Renderizar mensagens
        function renderizarMensagens(mensagens) {
            const container = $('#chatMessages');
            const isAtBottom = isScrollAtBottom(container);
            
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
                
                // Gerar ícone de status baseado no status_entrega
                let statusIcon = '';
                if (isOutgoing) {
                    statusIcon = gerarIconeStatus(mensagem.status_entrega || 'enviando');
                }
                
                // Gerar conteúdo da mensagem baseado no tipo
                let messageContent = '';
                
                // CORREÇÃO: Determinar tipo real da mensagem
                let tipoReal = mensagem.tipo;
                if (!tipoReal && mensagem.midia_tipo) {
                    // Se tipo estiver vazio, determinar baseado no midia_tipo
                    if (mensagem.midia_tipo.startsWith('image/')) {
                        tipoReal = 'imagem'; // Usar tipo salvo no banco
                    } else if (mensagem.midia_tipo.startsWith('audio/')) {
                        tipoReal = 'audio';
                    } else if (mensagem.midia_tipo.startsWith('video/')) {
                        tipoReal = 'video';
                    } else {
                        tipoReal = 'documento'; // Usar tipo salvo no banco
                    }
                }
                
                if (tipoReal === 'texto' || tipoReal === 'text') {
                    messageContent = `<div class="message-text">${mensagem.conteudo}</div>`;
                } else if ((tipoReal === 'imagem' || tipoReal === 'image') && mensagem.midia_url) {
                    // Verificar se é caminho do MinIO (começa com pasta do MinIO)
                    let imageSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
                        // É caminho do MinIO, usar media.php
                        imageSrc = `<?= URL ?>/media.php?file=${encodeURIComponent(mensagem.midia_url)}`;
                    }
                    
                    messageContent = `
                        <div class="message-media">
                            <img src="${imageSrc}" alt="Imagem" class="message-image" 
                                 onclick="visualizarMidia('${imageSrc}', 'image', '${mensagem.midia_nome || 'Imagem'}')"
                                 onerror="this.src='<?= Helper::asset('img/image-error.png') ?>'; this.onerror=null;">
                            ${mensagem.conteudo && mensagem.conteudo !== 'Arquivo: ' + mensagem.midia_nome ? 
                                `<div class="message-caption">${mensagem.conteudo}</div>` : ''}
                        </div>
                    `;
                } else if (tipoReal === 'audio' && mensagem.midia_url) {
                    // Verificar se é caminho do MinIO
                    let audioSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
                        // É caminho do MinIO, usar media.php
                        audioSrc = `<?= URL ?>/media.php?file=${encodeURIComponent(mensagem.midia_url)}`;
                    }
                    
                    messageContent = `
                        <div class="message-media">
                            <div class="audio-player">
                                <audio controls preload="metadata">
                                    <source src="${audioSrc}" type="${mensagem.midia_tipo || 'audio/mpeg'}">
                                    Seu navegador não suporta o elemento de áudio.
                                </audio>
                                <div class="audio-info">
                                    <i class="fas fa-music"></i>
                                    <span>${mensagem.midia_nome || 'Áudio'}</span>
                                </div>
                            </div>
                            ${mensagem.conteudo && mensagem.conteudo !== 'Arquivo: ' + mensagem.midia_nome ? 
                                `<div class="message-caption">${mensagem.conteudo}</div>` : ''}
                        </div>
                    `;
                } else if (tipoReal === 'video' && mensagem.midia_url) {
                    // Verificar se é caminho do MinIO
                    let videoSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
                        // É caminho do MinIO, usar media.php
                        videoSrc = `<?= URL ?>/media.php?file=${encodeURIComponent(mensagem.midia_url)}`;
                    }
                    
                    messageContent = `
                        <div class="message-media">
                            <video controls preload="metadata" class="message-video">
                                <source src="${videoSrc}" type="${mensagem.midia_tipo || 'video/mp4'}">
                                Seu navegador não suporta o elemento de vídeo.
                            </video>
                            ${mensagem.conteudo && mensagem.conteudo !== 'Arquivo: ' + mensagem.midia_nome ? 
                                `<div class="message-caption">${mensagem.conteudo}</div>` : ''}
                        </div>
                    `;
                } else if ((tipoReal === 'documento' || tipoReal === 'document') && mensagem.midia_url) {
                    // Verificar se é caminho do MinIO
                    let documentSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
                        // É caminho do MinIO, usar media.php
                        documentSrc = `<?= URL ?>/media.php?file=${encodeURIComponent(mensagem.midia_url)}`;
                    }
                    
                    const fileIcon = obterIconeArquivo(mensagem.midia_tipo || '', mensagem.midia_nome || '');
                    messageContent = `
                        <div class="message-media">
                            <div class="document-preview" onclick="window.open('${documentSrc}', '_blank')">
                                <div class="document-icon">
                                    <i class="fas ${fileIcon}"></i>
                                </div>
                                <div class="document-info">
                                    <div class="document-name">${mensagem.midia_nome || 'Documento'}</div>
                                    <div class="document-type">${obterTipoArquivo(mensagem.midia_tipo || '')}</div>
                                </div>
                                <div class="document-action">
                                    <i class="fas fa-download"></i>
                                </div>
                            </div>
                            ${mensagem.conteudo && mensagem.conteudo !== 'Arquivo: ' + mensagem.midia_nome ? 
                                `<div class="message-caption">${mensagem.conteudo}</div>` : ''}
                        </div>
                    `;
                } else {
                    // Fallback para mensagens sem mídia ou mídia não encontrada
                    messageContent = `<div class="message-text">${mensagem.conteudo}</div>`;
                }
                
                const messageHtml = `
                    <div class="message ${messageClass}" 
                         data-message-id="${mensagem.id}" 
                         data-serpro-id="${mensagem.serpro_message_id || ''}"
                         data-status="${mensagem.status_entrega || 'enviando'}"
                         data-tipo="${tipoReal}">
                        <div class="message-content">
                            ${messageContent}
                            <div class="message-time">
                                ${formatarTempo(mensagem.criado_em)}
                                ${statusIcon}
                            </div>
                        </div>
                    </div>
                `;
                
                container.append(messageHtml);
            });
            
            // Scroll para o fim apenas se já estava no final ou se é a primeira vez
            // REMOVIDO: Não fazer mais scroll automático para a última mensagem
            // if (isAtBottom || mensagens.length === container.find('.message').length) {
            //     setTimeout(() => {
            //         scrollToLastMessage();
            //     }, 100);
            // }
        }
        
        // Função auxiliar para obter ícone do arquivo
        function obterIconeArquivo(mimeType, fileName) {
            const extension = fileName.split('.').pop().toLowerCase();
            
            if (mimeType.includes('pdf') || extension === 'pdf') {
                return 'fa-file-pdf text-danger';
            } else if (mimeType.includes('word') || ['doc', 'docx'].includes(extension)) {
                return 'fa-file-word text-primary';
            } else if (mimeType.includes('excel') || ['xls', 'xlsx'].includes(extension)) {
                return 'fa-file-excel text-success';
            } else if (mimeType.includes('powerpoint') || ['ppt', 'pptx'].includes(extension)) {
                return 'fa-file-powerpoint text-warning';
            } else if (mimeType.includes('text') || extension === 'txt') {
                return 'fa-file-alt text-secondary';
            } else if (mimeType.includes('zip') || mimeType.includes('rar') || ['zip', 'rar'].includes(extension)) {
                return 'fa-file-archive text-info';
            } else {
                return 'fa-file text-muted';
            }
        }
        
        // Função auxiliar para obter tipo do arquivo
        function obterTipoArquivo(mimeType) {
            if (mimeType.includes('pdf')) return 'PDF';
            if (mimeType.includes('word')) return 'Word';
            if (mimeType.includes('excel')) return 'Excel';
            if (mimeType.includes('powerpoint')) return 'PowerPoint';
            if (mimeType.includes('text')) return 'Texto';
            if (mimeType.includes('zip')) return 'ZIP';
            if (mimeType.includes('rar')) return 'RAR';
            return 'Arquivo';
        }
        
        // Função para visualizar mídia em modal
        function visualizarMidia(url, tipo, nome) {
            const modalHtml = `
                <div class="modal fade" id="midiaModal" tabindex="-1" aria-labelledby="midiaModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="midiaModalLabel">
                                    <i class="fas fa-image me-2"></i>
                                    ${nome}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${url}" class="img-fluid" alt="${nome}" style="max-height: 70vh;">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>
                                    Fechar
                                </button>
                                <a href="${url}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt me-2"></i>
                                    Abrir em nova aba
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remover modal anterior se existir
            $('#midiaModal').remove();
            
            // Adicionar novo modal
            $('body').append(modalHtml);
            
            // Mostrar modal
            $('#midiaModal').modal('show');
            
            // Remover modal após fechar
            $('#midiaModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }

        // Gerar ícone de status da mensagem
        function gerarIconeStatus(status) {
            switch (status) {
                case 'enviando':
                    return '<i class="fas fa-clock text-muted" title="Enviando..."></i>';
                    
                case 'enviado':
                    return '<i class="fas fa-check text-muted" title="Enviado"></i>';
                    
                case 'entregue':
                    return '<i class="fas fa-check-double text-muted" title="Entregue"></i>';
                    
                case 'lido':
                    return '<i class="fas fa-check-double text-primary" title="Lido"></i>';
                    
                case 'erro':
                    return '<i class="fas fa-exclamation-triangle text-danger" title="Erro no envio"></i>';
                    
                default:
                    return '<i class="fas fa-check text-muted" title="Enviado"></i>';
            }
        }

        // Verificar se o scroll está no final
        function isScrollAtBottom(container) {
            if (container.length === 0) return true;

            const scrollTop = container.scrollTop();
            const scrollHeight = container[0].scrollHeight;
            const containerHeight = container.height();

            return scrollTop + containerHeight >= scrollHeight - 50;
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
            
            // Limpar input imediatamente para melhor UX
            $('#messageInput').val('').css('height', 'auto');
            
            $.ajax({
                url: '<?= URL ?>/chat/enviar-mensagem',
                method: 'POST',
                data: JSON.stringify(dados),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        // Buscar mensagens atualizada
                        buscarMensagensConversa(conversaAtiva);
                        mostrarToast('Mensagem enviada!', 'success');
                        
                        // Garantir que o scroll vá para o final
                        setTimeout(() => {
                            scrollToLastMessage();
                        }, 200);
                        
                        // Status será atualizado automaticamente via consulta da API
                        // console.log('📱 Mensagem enviada - aguardando status real da API');
                    } else {
                        // Restaurar mensagem se houve erro
                        $('#messageInput').val(mensagem);
                        mostrarToast(response.message, 'error');
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('❌ Erro ao enviar mensagem');
                    console.log('Status:', xhr.status);
                    console.log('TextStatus:', textStatus);
                    console.log('ErrorThrown:', errorThrown);
                    console.log('ResponseText:', xhr.responseText);
                    
                    // Restaurar mensagem em caso de erro
                    $('#messageInput').val(mensagem);
                    
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
                                            adjustChatLayout();
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
                        
                        // Mostrar opção de reenvio de template
                        mostrarOpcaoReenvioTemplate();
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
        
        // Simular progressão de status da mensagem
        function simularProgressaoStatus(serproMessageId) {
            // REMOVIDO: Não simular mais status automaticamente
            // Deixar apenas a consulta real da API determinar o status
            // console.log(`📱 Mensagem ${serproMessageId} enviada - aguardando status real da API`);
        }
        
        // REMOVIDO: Não marcar mais mensagens como lidas automaticamente
        // function marcarComoLidoSeConversaAtiva(serproMessageId) { ... }
        
        // Atualizar status de mensagem por Serpro ID (apenas para consulta real da API)
        function atualizarStatusMensagemPorSerproId(serproMessageId, novoStatus) {
            if (!serproMessageId) return;
            
            // Encontrar mensagem na tela pelo Serpro ID
            const messageElement = $(`.message[data-serpro-id="${serproMessageId}"]`);
            
            if (messageElement.length > 0) {
                // Atualizar data-status
                messageElement.attr('data-status', novoStatus);
                
                // Atualizar ícone
                const statusIcon = gerarIconeStatus(novoStatus);
                messageElement.find('.message-time i').replaceWith(statusIcon);
                
                // console.log(`Status da mensagem ${serproMessageId} atualizado para: ${novoStatus}`);
            }
        }

        // Enviar template
        function enviarTemplate() {
            const numero = $('#numeroContato').val().trim();
            const nome = $('#nomeContato').val().trim();
            const template = $('#templateSelect').val();
            const departamento = $('#departamentoSelect').val();

            if (!numero || !template) {
                mostrarToast('Número e template são obrigatórios', 'error');
                return;
            }

            // Para atendentes, verificar se departamento foi selecionado
            <?php if ($usuario_logado['perfil'] === 'atendente'): ?>
            if (!departamento) {
                mostrarToast('Selecione um departamento', 'error');
                return;
            }
            <?php endif; ?>

            // Coletar parâmetros
            const parametros = [];
            $('#parametrosInputs input').each(function() {
                parametros.push($(this).val().trim());
            });

            const dados = {
                numero: numero,
                nome: nome,
                template: template,
                parametros: parametros,
                departamento_id: departamento
            };

            // console.log('Enviando dados:', dados);

            $('#btnEnviarTemplate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enviando...');

            $.ajax({
                url: '<?= URL ?>/chat/iniciar-conversa',
                method: 'POST',
                data: JSON.stringify(dados),
                contentType: 'application/json',
                dataType: 'text', // Mudança para text para processar manualmente
                timeout: 30000,
                beforeSend: function(xhr) {
                    // console.log('Enviando requisição AJAX...');
                },
                success: function(responseText, textStatus, xhr) {
                    // console.log('Resposta recebida - Status HTTP:', xhr.status);
                    // console.log('Resposta bruta:', responseText);

                    // Tentar extrair JSON válido da resposta
                    let response = null;
                    try {
                        // Procurar pelo JSON na resposta (pode ter HTML/warnings antes)
                        let jsonStart = responseText.indexOf('{');
                        let jsonEnd = responseText.lastIndexOf('}');

                        if (jsonStart !== -1 && jsonEnd !== -1 && jsonEnd > jsonStart) {
                            let jsonString = responseText.substring(jsonStart, jsonEnd + 1);
                            response = JSON.parse(jsonString);
                            // console.log('JSON extraído:', response);
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
                            // console.log('✅ Sucesso detectado');
                            $('#novaConversaModal').modal('hide');
                            $('#formNovaConversa')[0].reset();
                            $('#parametrosContainer').hide();
                            mostrarToast('Template enviado com sucesso!', 'success');

                            // Recarregar lista de conversas
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            // console.log('❌ Falha na resposta:', response.message);
                            mostrarToast(response.message || 'Erro desconhecido', 'error');
                        }
                    } else {
                        // console.log('❌ Resposta inválida:', response);
                        mostrarToast('Resposta inválida do servidor', 'error');
                    }
                },
                error: function(xhr, textStatus, errorThrown) {
                    // console.log('❌ Erro AJAX');
                    // console.log('Status:', xhr.status);
                    // console.log('TextStatus:', textStatus);
                    // console.log('ErrorThrown:', errorThrown);
                    // console.log('ResponseText:', xhr.responseText);

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
                    // console.log('Requisição completada');
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
                        mostrarToast('Enviada com sucesso!', 'success');
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
                        
                        // Mostrar opção de reenvio de template
                        mostrarOpcaoReenvioTemplate();
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

        // Atualizar contador de conversas
        function atualizarContadorConversas() {
            const totalConversas = $('.chat-item').length;
            const conversasVisiveis = $('.chat-item:visible').length;

            $('#contadorConversas').text(`Mostrando ${conversasVisiveis} de ${totalConversas} conversas`);
        }

        // Função para buscar conversas
        function buscarConversas(termo) {
            if (termo.trim() === '') {
                // Se não há termo de busca, mostrar todas as conversas
                $('.chat-item').show();
                // Aplicar filtro ativo novamente
                const filtroAtivo = $('.chat-filters .btn.active').data('filter');
                filtrarConversas(filtroAtivo);
                return;
            }

            const termoLower = termo.toLowerCase();
            let encontrouAlguma = false;

            $('.chat-item').each(function() {
                const item = $(this);
                const nome = item.find('.chat-name').text().toLowerCase();
                const numero = item.find('.chat-last-message').text().toLowerCase();
                const status = item.find('.chat-status .badge').text().toLowerCase();

                // Buscar em nome, número ou status
                const encontrou = nome.includes(termoLower) ||
                    numero.includes(termoLower) ||
                    status.includes(termoLower);

                if (encontrou) {
                    item.show();
                    encontrouAlguma = true;
                } else {
                    item.hide();
                }
            });

            // Mostrar mensagem se não encontrou nada
            if (!encontrouAlguma) {
                mostrarMensagemNenhumaConversa();
            } else {
                removerMensagemNenhumaConversa();
            }

            // Atualizar contador
            atualizarContadorConversas();
        }

        // Mostrar mensagem quando não há conversas na busca
        function mostrarMensagemNenhumaConversa() {
            if ($('.no-results-message').length === 0) {
                const mensagem = `
                    <div class="no-results-message empty-state">
                        <i class="fas fa-search fa-3x text-muted"></i>
                        <p class="text-muted mt-3">Nenhuma conversa encontrada</p>
                        <small class="text-muted">
                            Tente buscar por nome, número ou status
                        </small>
                    </div>
                `;
                $('.chat-list').append(mensagem);
            }
        }

        // Remover mensagem de busca vazia
        function removerMensagemNenhumaConversa() {
            $('.no-results-message').remove();
        }

        // Filtrar conversas (atualizada)
        function filtrarConversas(filtro) {
            // Remover mensagem de busca vazia
            removerMensagemNenhumaConversa();

            const termoBusca = $('#searchConversas').val().trim();
            const departamentoId = $('#filtroDepartamento').val();
            const atendenteId = $('#filtroAtendente').val();

            $('.chat-item').each(function() {
                const status = $(this).data('status');
                const departamentoConversa = $(this).data('departamento-id');
                const atendenteConversa = $(this).data('atendente-id');
                let mostrar = true;

                // Aplicar filtro de status
                if (filtro === 'ativas' && status !== 'aberto') {
                    mostrar = false;
                } else if (filtro === 'pendentes' && status !== 'pendente') {
                    mostrar = false;
                }

                // Aplicar filtro de departamento (se selecionado)
                if (mostrar && departamentoId !== '' && departamentoConversa !== departamentoId) {
                    mostrar = false;
                }

                // Aplicar filtro de atendente (se selecionado)
                if (mostrar && atendenteId !== '' && atendenteConversa !== atendenteId) {
                    mostrar = false;
                }

                // Se há busca ativa, aplicar também o filtro de busca
                if (mostrar && termoBusca !== '') {
                    const termoLower = termoBusca.toLowerCase();
                    const nome = $(this).find('.chat-name').text().toLowerCase();
                    const numero = $(this).find('.chat-last-message').text().toLowerCase();
                    const statusTexto = $(this).find('.chat-status .badge').text().toLowerCase();
                    const departamentoTexto = $(this).find('.chat-department').text().toLowerCase();
                    const atendenteTexto = $(this).find('.chat-attendant').text().toLowerCase();

                    const encontrou = nome.includes(termoLower) ||
                        numero.includes(termoLower) ||
                        statusTexto.includes(termoLower) ||
                        departamentoTexto.includes(termoLower) ||
                        atendenteTexto.includes(termoLower);

                    if (!encontrou) {
                        mostrar = false;
                    }
                }

                $(this).toggle(mostrar);
            });

            // Verificar se há conversas visíveis
            const conversasVisiveis = $('.chat-item:visible').length;
            if (conversasVisiveis === 0) {
                mostrarMensagemNenhumaConversa();
            }

            // Atualizar contador
            atualizarContadorConversas();
        }

        // Filtrar por departamento
        function filtrarPorDepartamento(departamentoId) {
            const filtroAtual = $('.btn-group .btn.active').data('filter');
            filtrarConversas(filtroAtual);
        }

        // Filtrar por atendente
        function filtrarPorAtendente(atendenteId) {
            const filtroAtual = $('.btn-group .btn.active').data('filter');
            filtrarConversas(filtroAtual);
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
            return data.toLocaleTimeString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
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

                        // Se conversa não está mais ativa, mostrar opção de reenvio
                        if (!status.conversa_ativa) {
                            mostrarOpcaoReenvioTemplate();
                            return;
                        } else {
                            // Se conversa está ativa, esconder área de reenvio se estiver visível
                            if ($('#reenviarTemplateArea').is(':visible')) {
                                $('#reenviarTemplateArea').hide();
                                $('#chatInputArea').show();
                            }
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
                            $('#messageInput').attr('placeholder', 'Aguarde o contato responder ao template antes de enviar mensagens...');
                            $('#messageInput').prop('disabled', true);
                            $('#btnEnviar').prop('disabled', true);
                            
                            // Mostrar aviso visual
                            if ($('#avisoTemplate').length === 0) {
                                const avisoHtml = `
                                    <div class="alert alert-info mb-3" id="avisoTemplate">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <div>
                                                <strong>Atenção:</strong> Esta conversa foi iniciada com um template. 
                                                Você só poderá enviar mensagens após o contato responder ao template.
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#chatMessages').prepend(avisoHtml);
                            }
                        } else {
                            $('#messageInput').attr('placeholder', 'Digite sua mensagem...');
                            $('#messageInput').prop('disabled', false);
                            $('#btnEnviar').prop('disabled', false);
                            
                            // Remover aviso se existir
                            $('#avisoTemplate').remove();
                        }
                    }
                },
                error: function() {
                    // Ignorar erros na verificação de status
                }
            });
        }

        // Função para mostrar opção de reenvio de template
        function mostrarOpcaoReenvioTemplate() {
            // Esconder área de input normal
            $('#chatInputArea').hide();
            
            // Mostrar área de reenvio de template
            if ($('#reenviarTemplateArea').length === 0) {
                const reenviarArea = `
                    <div class="chat-input-area" id="reenviarTemplateArea">
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Conversa Expirada</h6>
                                    <p class="mb-2">Esta conversa expirou após 24 horas. Para continuar, você precisa reenviar um template.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="reenviar-template-form">
                            <div class="mb-3">
                                <label for="templateReenvio" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Selecionar Template *
                                </label>
                                <select class="form-select" id="templateReenvio" required>
                                    <option value="">Selecione um template</option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?= $template['nome'] ?>" data-parametros='<?= json_encode($template['parametros']) ?>'>
                                            <?= $template['titulo'] ?> - <?= $template['descricao'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="parametros-reenvio-container" id="parametrosReenvioContainer" style="display: none;">
                                <h6 class="mb-3">Parâmetros do Template</h6>
                                <div id="parametrosReenvioInputs">
                                    <!-- Inputs dos parâmetros serão adicionados aqui -->
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" id="btnReenviarTemplate">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Reenviar Template
                                </button>
                                </div>
                                </div>
                                </div>
                                `;
                                // <button type="button" class="btn btn-secondary" id="btnCancelarReenvio">
                                //     <i class="fas fa-times me-2"></i>
                                //     Cancelar
                                // </button>
                $('.chat-active').append(reenviarArea);
                
                // Event listeners para o formulário de reenvio
                setupReenvioTemplateEvents();
            }
            
            $('#reenviarTemplateArea').show();
        }

        // Configurar eventos para reenvio de template
        function setupReenvioTemplateEvents() {
            // Mudança de template
            $('#templateReenvio').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const parametros = selectedOption.data('parametros') || [];

                if (parametros.length > 0) {
                    $('#parametrosReenvioContainer').show();
                    gerarInputsParametrosReenvio(parametros);
                } else {
                    $('#parametrosReenvioContainer').hide();
                }
            });

            // Botão de reenviar template
            $('#btnReenviarTemplate').on('click', function() {
                reenviarTemplate();
            });

            // Botão de cancelar
            $('#btnCancelarReenvio').on('click', function() {
                $('#reenviarTemplateArea').hide();
                $('#chatInputArea').show();
            });
        }

        // Gerar inputs de parâmetros para reenvio
        function gerarInputsParametrosReenvio(parametros) {
            const container = $('#parametrosReenvioInputs');
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

        // Função para reenviar template
        function reenviarTemplate() {
            const template = $('#templateReenvio').val();
            
            if (!template) {
                mostrarToast('Selecione um template', 'error');
                return;
            }

            // Coletar parâmetros
            const parametros = [];
            $('#parametrosReenvioInputs input').each(function() {
                parametros.push($(this).val().trim());
            });

            const dados = {
                conversa_id: conversaAtiva,
                template: template,
                parametros: parametros
            };

            $('#btnReenviarTemplate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Reenviando...');

            $.ajax({
                url: '<?= URL ?>/chat/reenviar-template',
                method: 'POST',
                data: JSON.stringify(dados),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        mostrarToast(response.message, 'success');
                        
                        // Esconder área de reenvio e mostrar área normal
                        $('#reenviarTemplateArea').hide();
                        $('#chatInputArea').show();
                        
                        // Verificar status da conversa após reenvio
                        setTimeout(() => {
                            verificarStatusConversa();
                        }, 1000);
                        
                        // Recarregar mensagens da conversa
                        setTimeout(() => {
                            buscarMensagensConversa(conversaAtiva);
                        }, 2000);
                        
                        // Verificar se a conversa foi reativada imediatamente
                        setTimeout(() => {
                            $.ajax({
                                url: `<?= URL ?>/chat/verificar-conversa-reativada/${conversaAtiva}`,
                                method: 'GET',
                                success: function(statusResponse) {
                                    if (statusResponse.success && statusResponse.conversa_ativa) {
                                        // Se a conversa está ativa, esconder área de reenvio
                                        $('#reenviarTemplateArea').hide();
                                        $('#chatInputArea').show();
                                        
                                        // Resetar contador de toast para novo template
                                        contatoJaRespondeuVerificado = false;
                                        
                                        // Verificar se o contato respondeu ao novo template
                                        setTimeout(() => {
                                            verificarRespostaTemplate();
                                        }, 2000);
                                    }
                                }
                            });
                        }, 500);
                    } else {
                        mostrarToast(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let mensagem = 'Erro ao reenviar template';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            mensagem = response.message;
                        }
                    } catch (e) {
                        // Usar mensagem padrão
                    }
                    mostrarToast(mensagem, 'error');
                },
                complete: function() {
                    $('#btnReenviarTemplate').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Reenviar Template');
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

        // Funções para transferência de conversas
        function carregarAtendentesDisponiveis() {
            $.ajax({
                url: '<?= URL ?>/chat/atendentes-disponiveis',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const select = $('#atendenteSelect');
                        select.empty();
                        select.append('<option value="">Selecione um atendente</option>');
                        
                        response.atendentes.forEach(atendente => {
                            const disponivel = atendente.disponivel ? ' (Disponível)' : ' (Ocupado)';
                            const badge = atendente.disponivel ? 'success' : 'warning';
                            select.append(`<option value="${atendente.id}" data-atendente='${JSON.stringify(atendente)}'>
                                ${atendente.nome}${disponivel} <span class="badge bg-${badge}">${atendente.conversas_ativas} conversas</span>
                            </option>`);
                        });
                    } else {
                        mostrarToast('Erro ao carregar atendentes', 'error');
                    }
                },
                error: function() {
                    mostrarToast('Erro ao carregar atendentes', 'error');
                }
            });
        }

        function mostrarInfoAtendente(atendente) {
            $('#atendenteNome').text(atendente.nome);
            $('#atendenteEmail').text(atendente.email);
            $('#atendenteConversas').text(atendente.conversas_ativas);
            
            const statusClass = atendente.status === 'ativo' ? 'bg-success' : 'bg-secondary';
            $('#atendenteStatus').removeClass().addClass(`badge ${statusClass}`).text(atendente.status);
            
            $('#atendentesInfo').show();
        }

        function transferirConversa() {
            console.log('🔄 Iniciando transferência de conversa');
            
            const atendenteId = $('#atendenteSelect').val();
            console.log('👤 Atendente selecionado:', atendenteId);
            
            if (!atendenteId) {
                mostrarToast('Selecione um atendente', 'error');
                return;
            }

            if (!conversaAtiva) {
                mostrarToast('Nenhuma conversa ativa', 'error');
                return;
            }

            console.log('📞 Conversa ativa:', conversaAtiva);
            console.log('📤 Enviando requisição para transferir...');

            $('#btnTransferirConversa').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Transferindo...');

            $.ajax({
                url: `<?= URL ?>/chat/transferir/${conversaAtiva}`,
                method: 'POST',
                data: JSON.stringify({ atendente_id: atendenteId }),
                contentType: 'application/json',
                success: function(response) {
                    console.log('✅ Resposta de sucesso:', response);
                    if (response.success) {
                        mostrarToast(response.message, 'success');
                        $('#transferirModal').modal('hide');
                        
                        // Atualizar interface
                        $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).data('status', 'aberto');
                        $('#btnTransferirConversaHeader').hide();
                        $('#btnAssumirConversa').hide();
                        
                        // Recarregar conversa para mostrar novo atendente
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarToast(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.log('❌ Erro na transferência:', xhr);
                    let mensagem = 'Erro ao transferir conversa';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            mensagem = response.message;
                        }
                    } catch (e) {
                        // Usar mensagem padrão
                    }
                    mostrarToast(mensagem, 'error');
                },
                complete: function() {
                    $('#btnTransferirConversa').prop('disabled', false).html('<i class="fas fa-exchange-alt me-2"></i>Transferir Conversa');
                }
            });
        }

        // Event listeners para transferência
        $('#transferirModal').on('show.bs.modal', function() {
            carregarAtendentesDisponiveis();
        });

        $('#atendenteSelect').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                const atendente = selectedOption.data('atendente');
                mostrarInfoAtendente(atendente);
            } else {
                $('#atendentesInfo').hide();
            }
        });

        // Event listener para o botão de transferir no modal
        $(document).on('click', '#btnTransferirConversa', function() {
            console.log('🔘 Botão Transferir no modal clicado');
            transferirConversa();
        });

        // Event listener para abrir modal de transferência (botão no header)
        $(document).on('click', '#btnTransferirConversaHeader', function() {
            console.log('🔘 Botão Transferir no header clicado');
            $('#transferirModal').modal('show');
        });

        // Mostrar botão de transferir para admin/supervisor
        function atualizarBotoesConversa() {
            const perfil = '<?= $usuario_logado['perfil'] ?>';
            const status = $('.chat-item.active').data('status');
            
            if (perfil === 'admin' || perfil === 'supervisor') {
                if (status === 'pendente') {
                    $('#btnTransferirConversaHeader').show();
                    $('#btnAssumirConversa').show();
                } else if (status === 'aberto') {
                    $('#btnTransferirConversaHeader').show();
                    $('#btnAssumirConversa').hide();
                }
            } else {
                $('#btnTransferirConversaHeader').hide();
                if (status === 'pendente') {
                    $('#btnAssumirConversa').show();
                } else {
                    $('#btnAssumirConversa').hide();
                }
            }
        }

        // Atualizar botões quando abrir conversa
        const abrirConversaComBotoes = abrirConversa;
        abrirConversa = function(conversaId) {
            abrirConversaComBotoes(conversaId);
            setTimeout(atualizarBotoesConversa, 100);
        };

        // Verificar se o contato respondeu ao template periodicamente
        let contatoJaRespondeuVerificado = false; // Flag para controlar se já verificamos
        
        function verificarRespostaTemplate() {
            if (!conversaAtiva) {
                return;
            }

            // Se já verificamos que o contato respondeu e o input está habilitado, parar verificação
            if (contatoJaRespondeuVerificado && !$('#messageInput').prop('disabled')) {
                return;
            }

            $.ajax({
                url: `<?= URL ?>/chat/verificar-resposta-template/${conversaAtiva}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        if (response.contato_respondeu) {
                            // Se o contato respondeu, habilitar envio de mensagens
                            $('#messageInput').attr('placeholder', 'Digite sua mensagem...');
                            $('#messageInput').prop('disabled', false);
                            $('#btnEnviar').prop('disabled', false);
                            
                            // Remover aviso se existir
                            $('#avisoTemplate').remove();
                            
                            // Marcar como verificado
                            contatoJaRespondeuVerificado = true;
                            
                            // Mostrar toast de sucesso apenas algumas vezes
                            // if (contadorToastResposta < maxToastResposta) {
                            //     mostrarToast('Contato respondeu ao template! Agora você pode enviar mensagens.', 'success');
                            //     contadorToastResposta++;
                            // }
                        } else {
                            // Se o contato não respondeu, manter desabilitado
                            $('#messageInput').attr('placeholder', 'Aguarde o contato responder ao template antes de enviar mensagens...');
                            $('#messageInput').prop('disabled', true);
                            $('#btnEnviar').prop('disabled', true);
                            
                            // Resetar contador quando não há resposta
                            contatoJaRespondeuVerificado = false;
                        }
                    }
                },
                error: function() {
                    // Ignorar erros na verificação
                }
            });
        }

        // Verificar resposta ao template a cada 10 segundos
        setInterval(verificarRespostaTemplate, 10000);

        // ✅ NOVO: Sistema de polling para verificar novas mensagens
        let pollingInterval;
        
        function iniciarPollingMensagens() {
            // Verificar novas mensagens a cada 10 segundos
            pollingInterval = setInterval(verificarNovasMensagens, 10000);
        }
        
        function pararPollingMensagens() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        }
        
        function verificarNovasMensagens() {
            // Só verificar se o usuário estiver na página de chat
            if (window.location.pathname.includes('/chat')) {
                $.ajax({
                    url: `<?= URL ?>/chat/verificar-novas-mensagens`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.novas_mensagens) {
                            response.novas_mensagens.forEach(function(item) {
                                atualizarBadgeConversa(item.conversa_id, item.quantidade);
                            });
                        }
                    },
                    error: function() {
                        console.log('Erro ao verificar novas mensagens');
                    }
                });
            }
        }
        
        function atualizarBadgeConversa(conversaId, quantidade) {
            const chatItem = $(`.chat-item[data-conversa-id="${conversaId}"]`);
            if (chatItem.length > 0) {
                let badge = chatItem.find('.badge.bg-danger');
                
                if (quantidade > 0) {
                    if (badge.length > 0) {
                        // Atualizar badge existente
                        badge.text(quantidade);
                    } else {
                        // Criar novo badge
                        const avatar = chatItem.find('.chat-avatar');
                        avatar.append(`<span class="badge bg-danger">${quantidade}</span>`);
                    }
                } else {
                    // Remover badge se não há mensagens não lidas
                    badge.remove();
                }
            }
        }
    </script>

</body>

</html>