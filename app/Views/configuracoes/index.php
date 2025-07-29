
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
                    <a href="<?= URL ?>/configuracoes" class="nav-link active">
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
                    <h1 class="topbar-title">Configurações</h1>
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
                <!-- Alertas -->
                <?= Helper::mensagem('configuracao') ?>

                <!-- Header da página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-cog me-2"></i>Configurações</h2>
                        <p class="text-muted">Gerencie as configurações do ChatSerpro</p>
                    </div>
                </div>

                <!-- Grid de Configurações -->
                <div class="row g-4">
                    
                    <!-- API Serpro -->
                    <div class="col-md-6 col-lg-4">
                        <div class="content-card h-100">
                            <div class="content-card-body text-center">
                                <div class="config-icon mb-3">
                                    <i class="fas fa-plug fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">API Serpro</h5>
                                <p class="card-text text-muted">
                                    Configure as credenciais da API do WhatsApp Business do Serpro
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= URL ?>/configuracoes/serpro" class="btn btn-primary">
                                        <i class="fas fa-cog me-2"></i>
                                        Configurar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conexões WhatsApp -->
                    <div class="col-md-6 col-lg-4">
                        <div class="content-card h-100">
                            <div class="content-card-body text-center">
                                <div class="config-icon mb-3">
                                    <i class="fab fa-whatsapp fa-3x text-success"></i>
                                </div>
                                <h5 class="card-title">Conexões WhatsApp</h5>
                                <p class="card-text text-muted">
                                    Gerencie as conexões ativas do WhatsApp Business
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= URL ?>/configuracoes/conexoes" class="btn btn-success">
                                        <i class="fab fa-whatsapp me-2"></i>
                                        Gerenciar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagens Automáticas -->
                    <div class="col-md-6 col-lg-4">
                        <div class="content-card h-100">
                            <div class="content-card-body text-center">
                                <div class="config-icon mb-3">
                                    <i class="fas fa-robot fa-3x text-warning"></i>
                                </div>
                                <h5 class="card-title">Mensagens Automáticas</h5>
                                <p class="card-text text-muted">
                                    Configure respostas automáticas e mensagens padrão
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= URL ?>/configuracoes/mensagens" class="btn btn-warning">
                                        <i class="fas fa-robot me-2"></i>
                                        Configurar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações Gerais -->
                    <!-- <div class="col-md-6 col-lg-4">
                        <div class="content-card h-100">
                            <div class="content-card-body text-center">
                                <div class="config-icon mb-3">
                                    <i class="fas fa-sliders-h fa-3x text-info"></i>
                                </div>
                                <h5 class="card-title">Configurações Gerais</h5>
                                <p class="card-text text-muted">
                                    Configurações do sistema, timeouts e preferências
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= URL ?>/configuracoes/geral" class="btn btn-info">
                                        <i class="fas fa-sliders-h me-2"></i>
                                        Configurar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <!-- Backup & Restore -->
                    <!-- <div class="col-md-6 col-lg-4">
                        <div class="content-card h-100">
                            <div class="content-card-body text-center">
                                <div class="config-icon mb-3">
                                    <i class="fas fa-database fa-3x text-secondary"></i>
                                </div>
                                <h5 class="card-title">Backup & Restore</h5>
                                <p class="card-text text-muted">
                                    Backup das configurações e restauração do sistema
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= URL ?>/configuracoes/backup" class="btn btn-secondary">
                                        <i class="fas fa-database me-2"></i>
                                        Gerenciar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <!-- Logs do Sistema -->
                    <div class="col-md-6 col-lg-4">
                        <div class="content-card h-100">
                            <div class="content-card-body text-center">
                                <div class="config-icon mb-3">
                                    <i class="fas fa-file-alt fa-3x text-dark"></i>
                                </div>
                                <h5 class="card-title">Logs do Sistema</h5>
                                <p class="card-text text-muted">
                                    Visualize logs de erro, acesso e atividades do sistema
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= URL ?>/configuracoes/logs" class="btn btn-dark">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Informações do Sistema -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informações do Sistema
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="system-info">
                                            <div class="system-info-label">Sistema</div>
                                            <div class="system-info-value">ChatSerpro v1.0.0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="system-info">
                                            <div class="system-info-label">PHP</div>
                                            <div class="system-info-value"><?= phpversion() ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="system-info">
                                            <div class="system-info-label">Banco de Dados</div>
                                            <div class="system-info-value">MySQL</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="system-info">
                                            <div class="system-info-label">Servidor</div>
                                            <div class="system-info-value"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <style>
        .config-icon {
            transition: transform 0.3s ease;
        }
        
        .content-card:hover .config-icon {
            transform: scale(1.1);
        }
        
        .content-card {
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        
        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .system-info {
            padding: 1rem;
            background: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .system-info-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }
        
        .system-info-value {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .btn {
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
    </style>
</body>
</html> 