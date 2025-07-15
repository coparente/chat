<?php include 'app/Views/include/head.php' ?>
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
                    <a href="<?= URL ?>/chat" class="nav-link">
                        <i class="fas fa-comments"></i>
                        Chat
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/contatos" class="nav-link active">
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
                    <h1 class="topbar-title">Perfil do Contato</h1>
                </div>
                
                <div class="topbar-right">
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
            <div class="dashboard-content">
                <div class="row">
                    <!-- Perfil do contato -->
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-user me-2"></i>Perfil
                                </h5>
                            </div>
                            <div class="content-card-body text-center">
                                <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; background: var(--info-color);">
                                    <?= strtoupper(substr($contato->nome, 0, 2)) ?>
                                </div>
                                
                                <h4 class="mb-2"><?= htmlspecialchars($contato->nome) ?></h4>
                                
                                <?php if ($contato->empresa): ?>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-building me-1"></i>
                                    <?= htmlspecialchars($contato->empresa) ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <?php if ($contato->bloqueado): ?>
                                        <span class="badge bg-danger">Bloqueado</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contato->telefone) ?>" 
                                       target="_blank" class="btn btn-success">
                                        <i class="fab fa-whatsapp me-2"></i>Abrir WhatsApp
                                    </a>
                                    
                                    <a href="<?= URL ?>/contatos/editar/<?= $contato->id ?>" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i>Editar Contato
                                    </a>
                                    
                                    <a href="<?= URL ?>/contatos" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Voltar
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações de contato -->
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-info-circle me-2"></i>Informações
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="mb-3">
                                    <label class="form-label small text-muted">ID</label>
                                    <div class="text-monospace">#<?= $contato->id ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Telefone</label>
                                    <div class="text-monospace"><?= htmlspecialchars($contato->telefone) ?></div>
                                </div>
                                
                                <?php if ($contato->email): ?>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">E-mail</label>
                                    <div>
                                        <a href="mailto:<?= htmlspecialchars($contato->email) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($contato->email) ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($contato->tags): ?>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Tags</label>
                                    <div>
                                        <?php foreach (explode(', ', $contato->tags) as $tag): ?>
                                            <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Fonte</label>
                                    <div class="text-capitalize"><?= htmlspecialchars($contato->fonte) ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Cadastrado em</label>
                                    <div><?= date('d/m/Y H:i', strtotime($contato->criado_em)) ?></div>
                                </div>
                                
                                <?php if ($contato->ultimo_contato): ?>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Último contato</label>
                                    <div><?= date('d/m/Y H:i', strtotime($contato->ultimo_contato)) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Observações -->
                        <?php if ($contato->observacoes): ?>
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-sticky-note me-2"></i>Observações
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($contato->observacoes)) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Histórico de conversas -->
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-comments me-2"></i>Histórico de Conversas
                                </h5>
                                <span class="badge bg-primary"><?= count($historico) ?> mensagens</span>
                            </div>
                            <div class="content-card-body">
                                <?php if (empty($historico)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-comments text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="text-muted mt-3">Nenhuma conversa ainda</h5>
                                        <p class="text-muted">
                                            Este contato ainda não iniciou nenhuma conversa no sistema.
                                        </p>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contato->telefone) ?>" 
                                           target="_blank" class="btn btn-success">
                                            <i class="fab fa-whatsapp me-2"></i>Iniciar Conversa
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="chat-history" style="max-height: 600px; overflow-y: auto;">
                                        <?php foreach (array_reverse($historico) as $mensagem): ?>
                                        <div class="message-item d-flex mb-3 <?= $mensagem->tipo === 'recebida' ? 'flex-row' : 'flex-row-reverse' ?>">
                                            <div class="message-avatar me-3">
                                                <?php if ($mensagem->tipo === 'recebida'): ?>
                                                    <div class="user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--info-color);">
                                                        <?= strtoupper(substr($contato->nome, 0, 2)) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--primary-color);">
                                                        <?= strtoupper(substr($mensagem->atendente_nome ?? 'S', 0, 2)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="message-content flex-grow-1">
                                                <div class="message-bubble p-3 rounded-3 <?= $mensagem->tipo === 'recebida' ? 'bg-light text-dark' : 'bg-primary text-white' ?>" 
                                                     style="max-width: 70%; <?= $mensagem->tipo === 'recebida' ? '' : 'margin-left: auto;' ?>">
                                                    
                                                    <?php if ($mensagem->tipo_conteudo === 'texto'): ?>
                                                        <p class="mb-0"><?= nl2br(htmlspecialchars($mensagem->conteudo)) ?></p>
                                                    <?php elseif ($mensagem->tipo_conteudo === 'imagem'): ?>
                                                        <div class="mb-2">
                                                            <i class="fas fa-image me-2"></i>Imagem
                                                        </div>
                                                        <p class="mb-0 small"><?= htmlspecialchars($mensagem->conteudo) ?></p>
                                                    <?php elseif ($mensagem->tipo_conteudo === 'audio'): ?>
                                                        <div class="mb-2">
                                                            <i class="fas fa-microphone me-2"></i>Áudio
                                                        </div>
                                                        <p class="mb-0 small"><?= htmlspecialchars($mensagem->conteudo) ?></p>
                                                    <?php elseif ($mensagem->tipo_conteudo === 'documento'): ?>
                                                        <div class="mb-2">
                                                            <i class="fas fa-file me-2"></i>Documento
                                                        </div>
                                                        <p class="mb-0 small"><?= htmlspecialchars($mensagem->conteudo) ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <div class="message-time mt-2">
                                                        <small class="<?= $mensagem->tipo === 'recebida' ? 'text-muted' : 'text-white-50' ?>">
                                                            <?= date('d/m/Y H:i', strtotime($mensagem->criado_em)) ?>
                                                            <?php if ($mensagem->atendente_nome && $mensagem->tipo === 'enviada'): ?>
                                                                • <?= htmlspecialchars($mensagem->atendente_nome) ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mt-4 text-center">
                                        <p class="text-muted small">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Exibindo as últimas 50 mensagens
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Estatísticas da conversa -->
                        <?php if (!empty($historico)): ?>
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-chart-bar me-2"></i>Estatísticas
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="stat-item text-center">
                                            <div class="stat-number text-primary">
                                                <?= count($historico) ?>
                                            </div>
                                            <div class="stat-label text-muted">Total de mensagens</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="stat-item text-center">
                                            <div class="stat-number text-success">
                                                <?= count(array_filter($historico, fn($m) => $m->tipo === 'recebida')) ?>
                                            </div>
                                            <div class="stat-label text-muted">Recebidas</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="stat-item text-center">
                                            <div class="stat-number text-info">
                                                <?= count(array_filter($historico, fn($m) => $m->tipo === 'enviada')) ?>
                                            </div>
                                            <div class="stat-label text-muted">Enviadas</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="stat-item text-center">
                                            <div class="stat-number text-warning">
                                                <?= count(array_filter($historico, fn($m) => $m->tipo_conteudo !== 'texto')) ?>
                                            </div>
                                            <div class="stat-label text-muted">Mídias</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <script>
        $(document).ready(function() {
            // Auto-scroll para a última mensagem
            const chatHistory = $('.chat-history');
            if (chatHistory.length > 0) {
                chatHistory.scrollTop(chatHistory[0].scrollHeight);
            }
        });
    </script>
</body>
</html> 