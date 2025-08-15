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
                                    <div class="chat-item" data-conversa-id="<?= $conversa->id ?>" data-status="<?= $conversa->status ?>" data-departamento-id="<?= $conversa->departamento_id ?? '' ?>" data-atendente-id="<?= $conversa->atendente_id ?? '' ?>" data-telefone="<?= $conversa->numero ?>">
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
                            
                            <!-- ✅ NOVO: Indicador de carregamento de mais conversas -->
                            <div class="loading-conversas" id="loadingConversas" style="display: none;">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <span class="text-muted">Carregando mais conversas...</span>
                                </div>
                            </div>
                            
                            <!-- ✅ NOVO: Indicador para carregar mais conversas -->
                            <div class="load-more-conversas" id="loadMoreConversas" style="display: none;">
                                <div class="text-center py-2">
                                    <button class="btn btn-outline-primary btn-sm" id="btnCarregarMaisConversas">
                                        <i class="fas fa-chevron-down me-2"></i>
                                        Carregar mais conversas
                                    </button>
                                </div>
                            </div>
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
                                        <div class="chat-phone" id="chatPhoneActive">
                                            <i class="fas fa-phone me-1"></i>
                                            <span class="text-muted">Telefone</span>
                                        </div>
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
                                <!-- ✅ NOVO: Indicador de carregamento de mensagens antigas -->
                                <div class="loading-messages-older" id="loadingMessagesOlder" style="display: none;">
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <span class="text-muted">Carregando mensagens antigas...</span>
                                    </div>
                                </div>
                                
                                <!-- ✅ NOVO: Indicador para carregar mais mensagens antigas -->
                                <div class="load-more-indicator" id="loadMoreIndicator" style="display: none;">
                                    <i class="fas fa-chevron-up me-2"></i>
                                    <span>Clique para carregar mais mensagens antigas</span>
                                </div>
                                
                                <!-- Mensagens serão carregadas aqui -->
                            </div>

                            <!-- Área de Digitação -->
                            <div class="chat-input-area" id="chatInputArea">
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" id="btnAnexo" title="Anexar arquivo">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="btnGravarAudio" title="Gravar áudio">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                    <input type="text" class="form-control" id="mensagem" placeholder="Digite sua mensagem..." maxlength="1000">
                                    <button type="button" class="btn btn-primary" id="btnEnviar">
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
                        <!-- <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Atenção:</strong> Se você for enviar mensagens para números das áreas 11 a 19, 21, 22, 24, 27 ou 28, nos estados de São Paulo, Rio de Janeiro ou Espírito Santo, não se esqueça de incluir o dígito 9 antes do número de celular.
                            Essa mudança é obrigatória para mensagens enviadas a celulares dessas regiões.
                        </div> -->

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

                        <!-- Mensagens Rápidas -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">
                                    <i class="fas fa-bolt me-1"></i>
                                    Mensagens Rápidas
                                </label>
                                <button type="button" class="btn btn-sm btn-secondary" id="toggleMensagensRapidas">
                                    <i class="fas fa-chevron-down me-1"></i>
                                    Mostrar
                                </button>
                            </div>
                            <div class="mensagens-rapidas" id="mensagensRapidasContainer" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="fas fa-gavel me-1"></i> Intimações</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mensagem-rapida" data-mensagem="Somos da Central de Intimação do Tribunal de Justiça do Estado de Goiás (TJGO) ⚖️. Informamos que existe um processo judicial em seu nome, de número XX, em andamento na Comarca de XX.">
                                                    <i class="fas fa-copy me-1"></i>
                                                    Somos da Central de Intimação do Tribunal de Justiça do Estado de Goiás (TJGO) ⚖️. Informamos que existe um processo judicial em seu nome, de número XX, em andamento na Comarca de XX.
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Informações</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mensagem-rapida" data-mensagem="Informações sobre processo judicial">
                                                    <i class="fas fa-copy me-1"></i>
                                                    Informações sobre processo judicial
                                                </div>
                                                <div class="mensagem-rapida" data-mensagem="Agendamento de audiência">
                                                    <i class="fas fa-copy me-1"></i>
                                                    Agendamento de audiência
                                                </div>
                                                <div class="mensagem-rapida" data-mensagem="Consulta de andamento processual">
                                                    <i class="fas fa-copy me-1"></i>
                                                    Consulta de andamento processual
                                                </div>
                                                <div class="mensagem-rapida" data-mensagem="Orientações sobre procedimento">
                                                    <i class="fas fa-copy me-1"></i>
                                                    Orientações sobre procedimento
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

    <!-- Modal de Upload -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paperclip me-2"></i>
                        Anexar Arquivo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm">
                        <div class="mb-3">
                            <label for="arquivo" class="form-label">Selecione o arquivo:</label>
                            <input type="file" class="form-control" id="arquivo" accept="image/*,video/*,audio/*,.pdf,.doc,.docx">
                        </div>
                        <div class="mb-3">
                            <label for="legenda" class="form-label">Legenda (opcional):</label>
                            <textarea class="form-control" id="legenda" rows="3" placeholder="Digite uma legenda para o arquivo..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnEnviarArquivo">
                        <i class="fas fa-paper-plane me-1"></i>
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Gravação de Áudio -->
    <div class="modal fade" id="audioModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-microphone me-2"></i>
                        Gravar Áudio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="audio-recorder">
                        <div class="recorder-status mb-3">
                            <i class="fas fa-microphone fa-2x text-primary" id="micIcon"></i>
                            <div class="recording-time mt-2" id="recordingTime">00:00</div>
                            <div class="recording-status" id="recordingStatus">Clique para começar</div>
                        </div>
                        
                        <div class="recorder-controls">
                            <button type="button" class="btn btn-danger btn-lg" id="btnIniciarGravacao">
                                <i class="fas fa-microphone"></i>
                                Iniciar
                            </button>
                            <button type="button" class="btn btn-success btn-lg d-none" id="btnPararGravacao">
                                <i class="fas fa-stop"></i>
                                Parar
                            </button>
                        </div>
                        
                        <div class="audio-preview mt-3 d-none" id="audioPreview">
                            <audio controls class="w-100">
                                <source id="audioSource" src="" type="audio/wav">
                                Seu navegador não suporta o elemento de áudio.
                            </audio>
                        </div>
                        
                        <div class="audio-actions mt-3 d-none" id="audioActions">
                            <div class="mb-3">
                                <label for="legendaAudio" class="form-label">Legenda (opcional):</label>
                                <input type="text" class="form-control" id="legendaAudio" placeholder="Digite uma legenda para o áudio...">
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRegravar">
                                    <i class="fas fa-redo"></i>
                                    Regravar
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="btnEnviarAudio">
                                    <i class="fas fa-paper-plane"></i>
                                    Enviar
                                </button>
                            </div>
                        </div>
                    </div>
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

        // ✅ NOVO: Variáveis para gravação de áudio
        let mediaRecorder = null;
        let audioChunks = [];
        let audioBlob = null;
        let recordingStartTime = null;
        let recordingTimer = null;
        let isRecording = false;

        // ✅ NOVO: Variáveis para carregamento sob demanda
        let carregandoMensagensAntigas = false;
        let offsetMensagensAntigas = 0;
        let temMaisMensagensAntigas = true;
        let primeiraMensagemId = null;
        let alturaScrollAntesCarregamento = 0;
        
        let carregandoConversas = false;
        let offsetConversas = 0;
        let temMaisConversas = true;
        let tipoConversasAtual = 'ativas';
        let conversasCarregadas = 0;
        
        let contatoJaRespondeuVerificado = false;
        let pollingInterval;

        // Inicialização
        $(document).ready(function() {
            initializeChat();
            setupEventListeners();
            setupMensagensRapidas();
            setupAudioRecording(); // ✅ NOVO: Configurar gravação de áudio
            
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
        
        // ✅ NOVO: Configurar mensagens rápidas
        function setupMensagensRapidas() {
            $(document).on('click', '.mensagem-rapida', function() {
                const mensagem = $(this).data('mensagem');
                const parametrosContainer = $('#parametrosContainer');
                const parametrosInputs = $('#parametrosInputs input');
                
                // Verificar se há inputs de parâmetros
                if (parametrosInputs.length > 0) {
                    // Se há inputs, colar no primeiro input disponível
                    const primeiroInput = parametrosInputs.first();
                    primeiroInput.val(mensagem);
                    primeiroInput.focus();
                    
                    // Mostrar feedback visual
                    mostrarFeedbackCopiado($(this));
                    
                    // Mostrar toast de confirmação
                    mostrarToast('Mensagem copiada para o primeiro parâmetro!', 'success');
                } else {
                    // Se não há inputs, copiar para área de transferência
                    copiarParaAreaTransferencia(mensagem);
                    
                    // Mostrar feedback visual
                    mostrarFeedbackCopiado($(this));
                    
                    // Mostrar toast de confirmação
                    mostrarToast('Mensagem copiada para área de transferência! Cole no campo de parâmetro.', 'success');
                }
            });
            
            // ✅ NOVO: Adicionar funcionalidade de colar em qualquer input de parâmetro
            $(document).on('click', '#parametrosInputs input', function() {
                // Mostrar dica de que pode colar mensagens rápidas
                if (!$(this).hasClass('dica-mostrada')) {
                    $(this).addClass('dica-mostrada');
                    mostrarToast('💡 Dica: Clique em uma mensagem rápida para copiar automaticamente!', 'info');
                }
            });
            
            // ✅ NOVO: Controlar botão de expandir/recolher mensagens rápidas
            $('#toggleMensagensRapidas').on('click', function() {
                const container = $('#mensagensRapidasContainer');
                const button = $(this);
                const icon = button.find('i');
                
                if (container.is(':visible')) {
                    container.slideUp(300);
                    button.html('<i class="fas fa-chevron-down me-1"></i>Mostrar');
                } else {
                    container.slideDown(300);
                    button.html('<i class="fas fa-chevron-up me-1"></i>Ocultar');
                }
            });
        }
        
        // ✅ NOVO: Mostrar feedback visual de cópia
        function mostrarFeedbackCopiado(elemento) {
            // Remover classe de outros elementos
            $('.mensagem-rapida').removeClass('copiada');
            
            // Adicionar classe ao elemento clicado
            elemento.addClass('copiada');
            
            // Remover classe após 2 segundos
            setTimeout(() => {
                elemento.removeClass('copiada');
            }, 2000);
        }
        
        // ✅ NOVO: Copiar para área de transferência
        function copiarParaAreaTransferencia(texto) {
            if (navigator.clipboard && window.isSecureContext) {
                // Usar Clipboard API moderna
                navigator.clipboard.writeText(texto).then(() => {
                    console.log('Texto copiado para área de transferência');
                }).catch(err => {
                    console.error('Erro ao copiar:', err);
                    fallbackCopyTextToClipboard(texto);
                });
            } else {
                // Fallback para navegadores mais antigos
                fallbackCopyTextToClipboard(texto);
            }
        }
        
        // ✅ NOVO: Fallback para copiar texto
        function fallbackCopyTextToClipboard(texto) {
            const textArea = document.createElement('textarea');
            textArea.value = texto;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                console.log('Texto copiado via fallback');
            } catch (err) {
                console.error('Erro no fallback:', err);
            }
            
            document.body.removeChild(textArea);
        }
        
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
            $('#mensagem').on('keypress', function(e) {
                if (e.which === 13) {
                    enviarMensagem();
                }
            });

            // Anexar arquivo
            $('#btnAnexo').on('click', function() {
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
                const filtroAtual = $('.btn-group .btn.active').data('filter');
                filtrarConversas(filtroAtual);
            });

            // Limpar busca ao pressionar Escape
            $('#searchConversas').on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $(this).val('');
                    const filtroAtual = $('.btn-group .btn.active').data('filter');
                    filtrarConversas(filtroAtual);
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
            let scrollTimeoutConversas = null;
            $('.chat-list').on('scroll', function() {
                const container = $(this);
                const scrollTop = container.scrollTop();
                const scrollHeight = container[0].scrollHeight;
                const containerHeight = container.height();

                // ✅ NOVO: Se está próximo do final, carregar mais conversas (com debounce)
                if (scrollTop + containerHeight >= scrollHeight - 50) {
                    // Usar debounce para evitar múltiplas chamadas
                    if (scrollTimeoutConversas) {
                        clearTimeout(scrollTimeoutConversas);
                    }
                    
                    scrollTimeoutConversas = setTimeout(() => {
                        verificarCarregamentoConversas();
                    }, 200); // Aguardar 200ms após parar de scrollar
                }
            });

            // Auto-resize do textarea da mensagem
            $('#mensagem').on('input', function() {
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
                
                // ✅ NOVO: Verificar se deve carregar mensagens antigas
                verificarCarregamentoMensagensAntigas();
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
            
            // ✅ NOVO: Event listener para carregar mais mensagens antigas
            $(document).on('click', '#loadMoreIndicator', function() {
                if (conversaAtiva && !carregandoMensagensAntigas && temMaisMensagensAntigas) {
                    carregarMensagensAntigas(conversaAtiva);
                }
            });
            
            // ✅ NOVO: Event listener para carregar mais conversas
            $(document).on('click', '#btnCarregarMaisConversas', function() {
                if (!carregandoConversas && temMaisConversas) {
                    carregarMaisConversas();
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
            
            // ✅ NOVO: Atualizar botões após abrir conversa
            setTimeout(atualizarBotoesConversa, 100);
            
            // ✅ NOVO: Resetar variáveis de carregamento de mensagens
            offsetMensagensAntigas = 0;
            temMaisMensagensAntigas = true;
            primeiraMensagemId = null;
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
            const telefone = item.data('telefone');
            const status = item.data('status');
            const tempo = item.find('.chat-time').text();

            // Atualizar header
            $('#chatNameActive').text(nome);
            $('#chatAvatarActive').text(nome.substr(0, 2).toUpperCase());
            $('#chatPhoneActive').html(`<i class="fas fa-phone me-1"></i><span class="text-muted fw-bold fs-6">${telefone}</span>`);

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
            const mensagem = $('#mensagem').val().trim();
            
            if (!mensagem || !conversaAtiva) {
                return;
            }
            
            const dados = {
                conversa_id: conversaAtiva,
                mensagem: mensagem
            };
            
            // Limpar input imediatamente para melhor UX
            $('#mensagem').val('').css('height', 'auto');
            
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
                        $('#mensagem').val(mensagem);
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
                    $('#mensagem').val(mensagem);
                    
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
            formData.append('1', caption);
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

        // Filtrar conversas (atualizada)
        function filtrarConversas(filtro) {
            // Remover mensagem de busca vazia
            removerMensagemNenhumaConversa();

            const termoBusca = $('#searchConversas').val().trim();
            const departamentoId = $('#filtroDepartamento').length > 0 ? $('#filtroDepartamento').val() : '';
            const atendenteId = $('#filtroAtendente').length > 0 ? $('#filtroAtendente').val() : '';

            $('.chat-item').each(function(index) {
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
                if (mostrar && departamentoId !== '' && departamentoId !== null) {
                    // Converter para string para comparação
                    const deptId = String(departamentoId);
                    const deptConversa = String(departamentoConversa || '');
                    
                    if (deptId !== deptConversa) {
                        mostrar = false;
                    }
                }

                // Aplicar filtro de atendente (se selecionado)
                if (mostrar && atendenteId !== '' && atendenteId !== null) {
                    // Converter para string para comparação
                    const atendId = String(atendenteId);
                    const atendConversa = String(atendenteConversa || '');
                    
                    if (atendId !== atendConversa) {
                        mostrar = false;
                    }
                }

                // Se há busca ativa, aplicar também o filtro de busca
                if (mostrar && termoBusca !== '') {
                    const termoLower = termoBusca.toLowerCase();
                    const nomeElement = $(this).find('.chat-name');
                    const nome = nomeElement.text().toLowerCase();
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
                            $('#mensagem').attr('placeholder', 'Aguarde o contato responder ao template antes de enviar mensagens...');
                            $('#mensagem').prop('disabled', true);
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
                            $('#mensagem').attr('placeholder', 'Digite sua mensagem...');
                            $('#mensagem').prop('disabled', false);
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
        // let contatoJaRespondeuVerificado = false; // Flag para controlar se já verificamos
        
        function verificarRespostaTemplate() {
            if (!conversaAtiva) {
                return;
            }

            // Se já verificamos que o contato respondeu e o input está habilitado, parar verificação
            if (contatoJaRespondeuVerificado && !$('#mensagem').prop('disabled')) {
                return;
            }

            $.ajax({
                url: `<?= URL ?>/chat/verificar-resposta-template/${conversaAtiva}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        if (response.contato_respondeu) {
                            // Se o contato respondeu, habilitar envio de mensagens
                            $('#mensagem').attr('placeholder', 'Digite sua mensagem...');
                            $('#mensagem').prop('disabled', false);
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
                            $('#mensagem').attr('placeholder', 'Aguarde o contato responder ao template antes de enviar mensagens...');
                            $('#mensagem').prop('disabled', true);
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
        // let pollingInterval;
        
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

        // ✅ NOVO: Configurar gravação de áudio
        function setupAudioRecording() {
            // Botão para abrir modal de gravação
            $('#btnGravarAudio').on('click', function() {
                if (!conversaAtiva) {
                    mostrarToast('Selecione uma conversa primeiro!', 'warning');
                    return;
                }
                
                // Verificar se o contato respondeu
                if ($('#mensagem').prop('disabled')) {
                    mostrarToast('Aguarde o contato responder ao template antes de enviar mensagens!', 'warning');
                    return;
                }
                
                $('#audioModal').modal('show');
                resetAudioRecorder();
            });
            
            // Iniciar gravação
            $('#btnIniciarGravacao').on('click', iniciarGravacao);
            
            // Parar gravação
            $('#btnPararGravacao').on('click', pararGravacao);
            
            // Regravar
            $('#btnRegravar').on('click', function() {
                resetAudioRecorder();
                $('#legendaAudio').val(''); // Limpar legenda
            });
            
            // Enviar áudio
            $('#btnEnviarAudio').on('click', enviarAudio);
            
            // Fechar modal
            $('#audioModal').on('hidden.bs.modal', function() {
                if (isRecording) {
                    pararGravacao();
                }
                resetAudioRecorder();
            });
            
            // ✅ NOVO: Parar gravação quando pressionar ESC
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && isRecording) {
                    pararGravacao();
                }
            });
        }
        
        // ✅ NOVO: Iniciar gravação
        async function iniciarGravacao() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                
                // Configurar MediaRecorder para OGG
                mediaRecorder = new MediaRecorder(stream, {
                    mimeType: 'audio/ogg;codecs=opus'
                });
                audioChunks = [];
                
                mediaRecorder.ondataavailable = (event) => {
                    audioChunks.push(event.data);
                };
                
                mediaRecorder.onstop = () => {
                    audioBlob = new Blob(audioChunks, { type: 'audio/ogg' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    
                    $('#audioSource').attr('src', audioUrl);
                    $('#audioPreview').removeClass('d-none');
                    $('#audioActions').removeClass('d-none');
                    
                    // Parar o stream
                    stream.getTracks().forEach(track => track.stop());
                };
                
                mediaRecorder.start();
                isRecording = true;
                recordingStartTime = Date.now();
                
                // Atualizar UI
                $('#btnIniciarGravacao').addClass('d-none');
                $('#btnPararGravacao').removeClass('d-none');
                $('#recordingStatus').text('Gravando...');
                $('.recorder-status').addClass('recording');
                
                // ✅ NOVO: Adicionar classe recording ao botão do chat
                $('#btnGravarAudio').addClass('recording');
                
                // Iniciar timer
                iniciarTimer();
                
            } catch (error) {
                console.error('Erro ao acessar microfone:', error);
                mostrarToast('Erro ao acessar microfone. Verifique as permissões!', 'error');
            }
        }
        
        // ✅ NOVO: Parar gravação
        function pararGravacao() {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
                isRecording = false;
                
                // Parar timer
                if (recordingTimer) {
                    clearInterval(recordingTimer);
                    recordingTimer = null;
                }
                
                // Atualizar UI
                $('#btnIniciarGravacao').removeClass('d-none');
                $('#btnPararGravacao').addClass('d-none');
                $('#recordingStatus').text('Gravação finalizada');
                $('.recorder-status').removeClass('recording');
            }
        }
        
        // ✅ NOVO: Iniciar timer
        function iniciarTimer() {
            recordingTimer = setInterval(() => {
                const elapsed = Date.now() - recordingStartTime;
                const seconds = Math.floor(elapsed / 1000);
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                
                const timeString = `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
                $('#recordingTime').text(timeString);
                
                // ✅ NOVO: Atualizar botão do chat com tempo
                $('#btnGravarAudio').html(`<i class="fas fa-microphone"></i> ${timeString}`);
                
                // Limite de 5 minutos
                if (seconds >= 300) {
                    pararGravacao();
                    mostrarToast('Tempo máximo de gravação atingido (5 minutos)', 'warning');
                }
            }, 1000);
        }
        
        // ✅ NOVO: Resetar gravador
        function resetAudioRecorder() {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
            }
            
            isRecording = false;
            audioChunks = [];
            audioBlob = null;
            
            if (recordingTimer) {
                clearInterval(recordingTimer);
                recordingTimer = null;
            }
            
            // Resetar UI
            $('#recordingTime').text('00:00');
            $('#recordingStatus').text('Clique para começar');
            $('.recorder-status').removeClass('recording');
            $('#btnIniciarGravacao').removeClass('d-none');
            $('#btnPararGravacao').addClass('d-none');
            $('#audioPreview').addClass('d-none');
            $('#audioActions').addClass('d-none');
            
            // ✅ NOVO: Restaurar botão do chat
            $('#btnGravarAudio').html('<i class="fas fa-microphone"></i>');
            $('#btnGravarAudio').removeClass('recording');
        }
        
        // ✅ NOVO: Enviar áudio
        function enviarAudio() {
            if (!audioBlob || !conversaAtiva) {
                mostrarToast('Nenhum áudio gravado!', 'warning');
                return;
            }
            
            const legenda = $('#legendaAudio').val().trim() || 'Áudio gravado';
            
            // ✅ NOVO: Converter Blob para base64
            const reader = new FileReader();
            reader.onload = function() {
                const audioData = reader.result; // Base64 data
                
                // Mostrar loading
                $('#btnEnviarAudio').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
                
                $.ajax({
                    url: 'chat/enviar-audio', // ✅ NOVO: Rota dedicada para áudio
                    type: 'POST',
                    data: {
                        conversa_id: conversaAtiva,
                        audio: audioData, // ✅ NOVO: Enviar base64
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            mostrarToast('Áudio enviado com sucesso!', 'success');
                            $('#audioModal').modal('hide');
                            
                            // Atualizar conversa
                            buscarMensagensConversa(conversaAtiva);
                        } else {
                            mostrarToast(response.message || 'Erro ao enviar áudio', 'error');
                        }
                    },
                    error: function(xhr) {
                        let mensagem = 'Erro ao enviar áudio';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensagem = xhr.responseJSON.message;
                        }
                        mostrarToast(mensagem, 'error');
                    },
                    complete: function() {
                        $('#btnEnviarAudio').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar');
                    }
                });
            };
            
            reader.onerror = function() {
                mostrarToast('Erro ao processar áudio', 'error');
            };
            
            reader.readAsDataURL(audioBlob);
        }

        // ✅ NOVO: Carregar mensagens antigas
        function carregarMensagensAntigas(conversaId) {
            if (carregandoMensagensAntigas || !temMaisMensagensAntigas) {
                return;
            }
            
            carregandoMensagensAntigas = true;
            
            // Salvar altura do scroll antes do carregamento
            const container = $('#chatMessages');
            alturaScrollAntesCarregamento = container.scrollTop();
            
            // Mostrar indicador de carregamento
            $('#loadingMessagesOlder').show();
            
            $.ajax({
                url: `<?= URL ?>/chat/carregar-mensagens-antigas/${conversaId}/${offsetMensagensAntigas}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Adicionar mensagens antigas no topo
                        adicionarMensagensAntigas(response.mensagens);
                        
                        // Atualizar offset e verificar se há mais mensagens
                        offsetMensagensAntigas = response.proximo_offset || 0;
                        temMaisMensagensAntigas = response.tem_mais;
                        
                        // ✅ NOVO: Mostrar/esconder indicador de carregar mais
                        if (temMaisMensagensAntigas) {
                            $('#loadMoreIndicator').show();
                        } else {
                            $('#loadMoreIndicator').hide();
                        }
                        
                        // Restaurar posição do scroll
                        setTimeout(() => {
                            restaurarPosicaoScroll();
                        }, 100);
                    }
                },
                error: function() {
                    console.log('Erro ao carregar mensagens antigas');
                },
                complete: function() {
                    carregandoMensagensAntigas = false;
                    $('#loadingMessagesOlder').hide();
                }
            });
        }

        // ✅ NOVO: Adicionar mensagens antigas no topo
        function adicionarMensagensAntigas(mensagens) {
            if (mensagens.length === 0) return;
            
            const container = $('#chatMessages');
            const mensagensExistentes = container.find('.message');
            
            // Adicionar mensagens antigas no topo (após o indicador de carregamento)
            mensagens.reverse().forEach(mensagem => {
                const isOutgoing = mensagem.direcao === 'saida';
                const messageClass = isOutgoing ? 'message-outgoing' : 'message-incoming';
                
                // Gerar ícone de status baseado no status_entrega
                let statusIcon = '';
                if (isOutgoing) {
                    statusIcon = gerarIconeStatus(mensagem.status_entrega || 'enviando');
                }
                
                // Gerar conteúdo da mensagem baseado no tipo
                let messageContent = '';
                
                // Determinar tipo real da mensagem
                let tipoReal = mensagem.tipo;
                if (!tipoReal && mensagem.midia_tipo) {
                    if (mensagem.midia_tipo.startsWith('image/')) {
                        tipoReal = 'imagem';
                    } else if (mensagem.midia_tipo.startsWith('audio/')) {
                        tipoReal = 'audio';
                    } else if (mensagem.midia_tipo.startsWith('video/')) {
                        tipoReal = 'video';
                    } else {
                        tipoReal = 'documento';
                    }
                }
                
                if (tipoReal === 'texto' || tipoReal === 'text') {
                    messageContent = `<div class="message-text">${mensagem.conteudo}</div>`;
                } else if ((tipoReal === 'imagem' || tipoReal === 'image') && mensagem.midia_url) {
                    let imageSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
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
                    let audioSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
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
                    let videoSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
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
                    let documentSrc = mensagem.midia_url;
                    if (mensagem.midia_url.startsWith('document/') || mensagem.midia_url.startsWith('image/') || 
                        mensagem.midia_url.startsWith('audio/') || mensagem.midia_url.startsWith('video/')) {
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
                
                // Inserir após o indicador de carregamento
                container.find('#loadingMessagesOlder').after(messageHtml);
            });
            
            // Atualizar primeira mensagem ID
            if (mensagens.length > 0) {
                primeiraMensagemId = mensagens[mensagens.length - 1].id;
            }
            
            // ✅ NOVO: Resetar variáveis de paginação
            offsetMensagensAntigas = 0;
            temMaisMensagensAntigas = true;
            
            // ✅ NOVO: Mostrar indicador de carregar mais mensagens antigas
            if (mensagens.length >= 20) { // Se há pelo menos 20 mensagens, provavelmente há mais
                $('#loadMoreIndicator').show();
            } else {
                $('#loadMoreIndicator').hide();
            }
        }

        // ✅ NOVO: Restaurar posição do scroll após carregar mensagens antigas
        function restaurarPosicaoScroll() {
            const container = $('#chatMessages');
            const novaAltura = container[0].scrollHeight;
            const diferencaAltura = novaAltura - alturaScrollAntesCarregamento;
            
            if (diferencaAltura > 0) {
                container.scrollTop(diferencaAltura);
            }
        }
        
        // ✅ NOVO: Verificar se deve carregar mensagens antigas
        function verificarCarregamentoMensagensAntigas() {
            const container = $('#chatMessages');
            const scrollTop = container.scrollTop();
            
            // Se está próximo do topo (primeiras mensagens), carregar mais
            if (scrollTop < 100 && !carregandoMensagensAntigas && temMaisMensagensAntigas) {
                carregarMensagensAntigas(conversaAtiva);
            }
        }
        
        // ✅ NOVO: Adicionar novas conversas na lista
        function adicionarConversas(conversas) {
            if (conversas.length === 0) return;
            
            const chatList = $('.chat-list');
            
            // Remover indicadores de carregamento temporariamente
            $('#loadingConversas, #loadMoreConversas').detach();
            
            // Adicionar cada conversa (evitando duplicatas)
            conversas.forEach(conversa => {
                // ✅ NOVO: Verificar se a conversa já existe
                const conversaExistente = chatList.find(`[data-conversa-id="${conversa.id}"]`);
                if (conversaExistente.length > 0) {
                    return; // Pular esta conversa
                }
                
                const conversaHtml = `
                    <div class="chat-item" data-conversa-id="${conversa.id}" data-status="${conversa.status}" data-departamento-id="${conversa.departamento_id || ''}" data-atendente-id="${conversa.atendente_id || ''}" data-telefone="${conversa.numero}">
                        <div class="chat-avatar">
                            <div class="avatar-circle">
                                ${(conversa.contato_nome || 'C').substring(0, 2).toUpperCase()}
                            </div>
                            ${conversa.mensagens_nao_lidas > 0 ? `<span class="badge bg-danger">${conversa.mensagens_nao_lidas}</span>` : ''}
                        </div>
                        <div class="chat-info">
                            <div class="chat-name">${conversa.contato_nome || 'Sem nome'}</div>
                            <div class="chat-last-message">
                                <i class="fas fa-phone me-1"></i>
                                ${conversa.numero}
                            </div>
                            ${conversa.departamento_nome ? `
                            <div class="chat-department">
                                <i class="fas fa-building me-1" style="color: ${conversa.departamento_cor || '#6c757d'}"></i>
                                <small class="text-muted">${conversa.departamento_nome}</small>
                            </div>
                            ` : ''}
                            ${conversa.atendente_nome ? `
                            <div class="chat-attendant">
                                <i class="fas fa-user me-1" style="color: var(--primary-color);"></i>
                                <small class="text-muted">${conversa.atendente_nome}</small>
                            </div>
                            ` : ''}
                            <div class="chat-time">
                                ${formatarTempo(conversa.ultima_mensagem || conversa.criado_em)}
                            </div>
                        </div>
                        <div class="chat-status">
                            <span class="badge bg-${conversa.status === 'aberto' ? 'success' : (conversa.status === 'pendente' ? 'warning' : 'secondary')}">
                                ${conversa.status.charAt(0).toUpperCase() + conversa.status.slice(1)}
                            </span>
                        </div>
                    </div>
                `;
                
                chatList.append(conversaHtml);
            });
            
            // Recolocar indicadores de carregamento
            chatList.append($('#loadingConversas, #loadMoreConversas'));
            
            // Adicionar event listeners para as novas conversas
            $('.chat-item').off('click').on('click', function() {
                const conversaId = $(this).data('conversa-id');
                if (conversaId) {
                    abrirConversa(conversaId);
                }
            });
        }
        
        // ✅ NOVO: Carregar mais conversas
        function carregarMaisConversas() {
            if (carregandoConversas || !temMaisConversas) {
                return;
            }
            
            carregandoConversas = true;
            
            // Mostrar indicador de carregamento
            $('#loadingConversas').show();
            $('#loadMoreConversas').hide();
            
            $.ajax({
                url: `<?= URL ?>/chat/carregar-mais-conversas/${tipoConversasAtual}/${offsetConversas}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // ✅ NOVO: Verificar se realmente há conversas novas
                        if (response.conversas && response.conversas.length > 0) {
                            // Adicionar novas conversas na lista
                            adicionarConversas(response.conversas);
                            
                            // Atualizar offset e verificar se há mais conversas
                            offsetConversas = response.proximo_offset || 0;
                            temMaisConversas = response.tem_mais;
                            conversasCarregadas += response.conversas.length;
                            
                            // console.log(`📊 Conversas carregadas: ${response.conversas.length}, Total: ${conversasCarregadas}, Tem mais: ${temMaisConversas}`);
                        } else {
                            temMaisConversas = false;
                        }
                        
                        // Mostrar/esconder indicador de carregar mais
                        if (temMaisConversas) {
                            $('#loadMoreConversas').show();
                        } else {
                            $('#loadMoreConversas').hide();
                            // ✅ NOVO: Mostrar mensagem de que não há mais conversas
                            mostrarMensagemNaoHaMaisConversas();
                        }
                        
                        // Atualizar contador de conversas
                        atualizarContadorConversas();
                    } else {
                        temMaisConversas = false;
                        $('#loadMoreConversas').hide();
                    }
                },
                error: function(xhr, status, error) {
                    temMaisConversas = false;
                    $('#loadMoreConversas').hide();
                },
                complete: function() {
                    carregandoConversas = false;
                    $('#loadingConversas').hide();
                }
            });
        }
        
        // ✅ NOVO: Verificar se deve carregar mais conversas
        function verificarCarregamentoConversas() {
            if (!carregandoConversas && temMaisConversas) {
                carregarMaisConversas();
            }
        }
        
        // ✅ NOVO: Resetar variáveis de carregamento de conversas
        function resetarCarregamentoConversas() {
            offsetConversas = 0;
            temMaisConversas = true;
            conversasCarregadas = 0;
            tipoConversasAtual = 'ativas';
            carregandoConversas = false;
            
            // Esconder indicadores e limpar mensagens
            $('#loadingConversas, #loadMoreConversas').hide();
            $('.no-more-conversas-message').remove();
        }
        
        // ✅ NOVO: Mostrar mensagem quando não há mais conversas
        function mostrarMensagemNaoHaMaisConversas() {
            // Remover mensagem anterior se existir
            $('.no-more-conversas-message').remove();
            
            const mensagem = `
                <div class="no-more-conversas-message text-center py-3">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="text-muted mb-0">Todas as conversas foram carregadas</p>
                    <small class="text-muted">Não há mais conversas para exibir</small>
                </div>
            `;
            
            // Inserir após o indicador de carregamento
            $('#loadingConversas').after(mensagem);
        }
    </script>

</body>

</html>
