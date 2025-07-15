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
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/chat" class="nav-link">
                        <i class="fas fa-comments"></i>
                        <span>Chat</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/contatos" class="nav-link">
                        <i class="fas fa-address-book"></i>
                        <span>Contatos</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/relatorios" class="nav-link active">
                        <i class="fas fa-chart-bar"></i>
                        <span>Relatórios</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/usuarios" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Usuários</span>
                    </a>
                </div>
                
                <?php if ($usuario_logado['perfil'] === 'admin'): ?>
                <div class="nav-item">
                    <a href="<?= URL ?>/configuracoes" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Configurações</span>
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
                        Relatório de Conversas
                    </h1>
                </div>
                
                <div class="topbar-right">
                    <a href="<?= URL ?>/relatorios" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Voltar
                    </a>
                    
                    <!-- Toggle Dark Mode -->
                    <button class="btn btn-outline-secondary btn-sm me-2" id="toggleTheme" title="Alternar tema">
                        <i class="fas fa-moon"></i>
                    </button>
                    
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
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="page-title">
                                <i class="fas fa-comments me-2 text-primary"></i>
                                Relatório de Conversas
                            </h2>
                            <p class="page-subtitle">
                                Análise detalhada das conversas e interações com clientes
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <button class="btn btn-success" onclick="exportarRelatorio('excel')">
                                    <i class="fas fa-file-excel me-2"></i>
                                    Excel
                                </button>
                                <button class="btn btn-danger" onclick="exportarRelatorio('pdf')">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filter-section">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-filter me-2"></i>
                                    Filtros de Busca
                                </h5>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="collapse show" id="filtrosCollapse">
                            <div class="card-body">
                                <form method="GET" id="formFiltros">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Data Início
                                            </label>
                                            <input type="date" class="form-control" name="data_inicio" 
                                                   value="<?= $filtros['data_inicio'] ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Data Fim
                                            </label>
                                            <input type="date" class="form-control" name="data_fim" 
                                                   value="<?= $filtros['data_fim'] ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Status
                                            </label>
                                            <select class="form-select" name="status">
                                                <option value="">Todos os Status</option>
                                                <option value="pendente" <?= $filtros['status'] === 'pendente' ? 'selected' : '' ?>>
                                                    🟡 Pendente
                                                </option>
                                                <option value="aberto" <?= $filtros['status'] === 'aberto' ? 'selected' : '' ?>>
                                                    🟢 Aberto
                                                </option>
                                                <option value="fechado" <?= $filtros['status'] === 'fechado' ? 'selected' : '' ?>>
                                                    ⚫ Fechado
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">
                                                <i class="fas fa-user me-1"></i>
                                                Atendente
                                            </label>
                                            <select class="form-select" name="atendente_id">
                                                <option value="">Todos os Atendentes</option>
                                                <?php foreach ($atendentes as $atendente): ?>
                                                    <option value="<?= $atendente->id ?>" 
                                                            <?= $filtros['atendente_id'] == $atendente->id ? 'selected' : '' ?>>
                                                        👤 <?= $atendente->nome ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="filter-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>
                                            Aplicar Filtros
                                        </button>
                                        <a href="<?= URL ?>/relatorios/conversas" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>
                                            Limpar Filtros
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas Resumidas -->
                <div class="stats-section">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon primary">
                                                <i class="fas fa-comments"></i>
                                            </div>
                                            <div class="stat-content">
                                                <h3 class="stat-number"><?= number_format($estatisticas->total_conversas ?? 0) ?></h3>
                                                <p class="stat-label">Total de Conversas</p>
                                                <div class="stat-trend">
                                                    <small class="text-muted">
                                                        <i class="fas fa-chart-line me-1"></i>
                                                        Período selecionado
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon success">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="stat-content">
                                                <h3 class="stat-number"><?= number_format($estatisticas->fechadas ?? 0) ?></h3>
                                                <p class="stat-label">Conversas Fechadas</p>
                                                <div class="stat-trend">
                                                    <small class="text-success">
                                                        <i class="fas fa-arrow-up me-1"></i>
                                                        <?= round(($estatisticas->fechadas ?? 0) / max(($estatisticas->total_conversas ?? 1), 1) * 100, 1) ?>% do total
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon warning">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="stat-content">
                                                <h3 class="stat-number"><?= number_format($estatisticas->abertas ?? 0) ?></h3>
                                                <p class="stat-label">Conversas Abertas</p>
                                                <div class="stat-trend">
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Necessitam atenção
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon info">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="stat-content">
                                                <h3 class="stat-number"><?= number_format($estatisticas->contatos_unicos ?? 0) ?></h3>
                                                <p class="stat-label">Contatos Únicos</p>
                                                <div class="stat-trend">
                                                    <small class="text-info">
                                                        <i class="fas fa-address-book me-1"></i>
                                                        Clientes distintos
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Conversas -->
                <div class="conversations-table">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-table me-2"></i>
                                    Lista de Conversas
                                    <span class="badge bg-primary ms-2"><?= count($conversas) ?> registros</span>
                                </h5>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                                        <i class="fas fa-sync-alt"></i>
                                        Atualizar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($conversas)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tabelaConversas">
                                        <thead>
                                            <tr>
                                                <th width="80">#ID</th>
                                                <th>Contato</th>
                                                <th width="130">Número</th>
                                                <th width="120">Atendente</th>
                                                <th width="100">Status</th>
                                                <th width="120">Mensagens</th>
                                                <th width="110">Criado em</th>
                                                <th width="110">Última msg</th>
                                                <th width="100">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($conversas as $conversa): ?>
                                                <tr>
                                                    <td>
                                                        <div class="conversation-id">
                                                            <strong>#<?= $conversa->id ?></strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="contact-info">
                                                            <div class="contact-avatar">
                                                                <?= strtoupper(substr($conversa->contato_nome ?? 'C', 0, 2)) ?>
                                                            </div>
                                                            <div class="contact-details">
                                                                <div class="contact-name">
                                                                    <?= $conversa->contato_nome ?? 'Sem nome' ?>
                                                                </div>
                                                                <?php if ($conversa->departamento): ?>
                                                                    <div class="contact-department">
                                                                        <i class="fas fa-building me-1"></i>
                                                                        <?= $conversa->departamento ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="phone-number">
                                                            <i class="fab fa-whatsapp me-1"></i>
                                                            <code><?= $conversa->numero ?></code>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($conversa->atendente_nome): ?>
                                                            <div class="agent-badge">
                                                                <i class="fas fa-user-tie me-1"></i>
                                                                <?= $conversa->atendente_nome ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-user-slash me-1"></i>
                                                                Sem atendente
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="status-container">
                                                            <?php
                                                            $statusClass = 'secondary';
                                                            $statusIcon = 'fas fa-circle';
                                                            
                                                            if ($conversa->status === 'aberto') {
                                                                $statusClass = 'success';
                                                                $statusIcon = 'fas fa-check-circle';
                                                            } elseif ($conversa->status === 'pendente') {
                                                                $statusClass = 'warning';
                                                                $statusIcon = 'fas fa-clock';
                                                            } elseif ($conversa->status === 'fechado') {
                                                                $statusClass = 'secondary';
                                                                $statusIcon = 'fas fa-times-circle';
                                                            }
                                                            ?>
                                                            <span class="badge bg-<?= $statusClass ?> status-badge">
                                                                <i class="<?= $statusIcon ?> me-1"></i>
                                                                <?= ucfirst($conversa->status) ?>
                                                            </span>
                                                            <?php if ($conversa->prioridade === 'alta'): ?>
                                                                <div class="priority-indicator">
                                                                    <i class="fas fa-exclamation-triangle text-danger" title="Prioridade Alta"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="message-stats">
                                                            <div class="total-messages">
                                                                <strong><?= $conversa->total_mensagens ?></strong>
                                                            </div>
                                                            <div class="message-breakdown">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-arrow-down text-primary me-1"></i><?= $conversa->mensagens_recebidas ?? 0 ?>
                                                                    <i class="fas fa-arrow-up text-success ms-1 me-1"></i><?= $conversa->mensagens_enviadas ?? 0 ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="date-info">
                                                            <div class="date-primary">
                                                                <?= date('d/m/Y', strtotime($conversa->criado_em)) ?>
                                                            </div>
                                                            <div class="date-secondary">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-clock me-1"></i>
                                                                    <?= date('H:i', strtotime($conversa->criado_em)) ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($conversa->ultima_mensagem): ?>
                                                            <div class="date-info">
                                                                <div class="date-primary">
                                                                    <?= date('d/m/Y', strtotime($conversa->ultima_mensagem)) ?>
                                                                </div>
                                                                <div class="date-secondary">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-clock me-1"></i>
                                                                        <?= date('H:i', strtotime($conversa->ultima_mensagem)) ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">
                                                                <i class="fas fa-minus"></i>
                                                                N/A
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" 
                                                               class="btn btn-primary btn-sm" 
                                                               title="Visualizar conversa">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <button class="btn btn-outline-info btn-sm" 
                                                                    onclick="verDetalhes(<?= $conversa->id ?>)"
                                                                    title="Ver detalhes">
                                                                <i class="fas fa-info-circle"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <h5 class="empty-title">Nenhuma conversa encontrada</h5>
                                    <p class="empty-text">Tente ajustar os filtros de busca ou verifique se existem dados para o período selecionado.</p>
                                    <a href="<?= URL ?>/relatorios/conversas" class="btn btn-primary">
                                        <i class="fas fa-refresh me-2"></i>
                                        Limpar Filtros
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Detalhes da Conversa -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Detalhes da Conversa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalDetalhesContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2">Carregando detalhes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Processando dados...</p>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <script>
        let dataTable = null;

        $(document).ready(function() {
            // Inicializar DataTable
            if ($('#tabelaConversas').length) {
                dataTable = $('#tabelaConversas').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                    },
                    order: [[0, 'desc']],
                    pageLength: 25,
                    responsive: true,
                    columnDefs: [
                        { targets: [-1], orderable: false, searchable: false },
                        { targets: [0], className: 'text-center' },
                        { targets: [4], className: 'text-center' },
                        { targets: [5], className: 'text-center' },
                        { targets: [8], className: 'text-center' }
                    ],
                    dom: '<"table-controls"<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>>' +
                         '<"table-wrapper"t>' +
                         '<"table-footer"<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>>',
                    drawCallback: function() {
                        // Reaplicar tooltips após redraw
                        initTooltips();
                    }
                });
            }

            
            // Inicializar tooltips
            initTooltips();

            // Auto-submit do formulário ao trocar filtros
            $('#formFiltros select, #formFiltros input').on('change', function() {
                showAutoSubmitMessage();
            });
        });

        function initTooltips() {
            // Inicializar tooltips do Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        function showAutoSubmitMessage() {
            // Mostrar mensagem de auto-submit
            const submitBtn = $('#formFiltros button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Aplicando...');
            
            // Auto-submit após 1 segundo
            setTimeout(function() {
                $('#formFiltros').submit();
            }, 1000);
        }

        function verDetalhes(conversaId) {
            $('#modalDetalhesContent').html(`
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando detalhes...</p>
                </div>
            `);
            
            $('#modalDetalhes').modal('show');
            
            // Simulação de carregamento de detalhes
            setTimeout(function() {
                $('#modalDetalhesContent').html(`
                    <div class="conversation-details">
                        <div class="detail-item">
                            <strong>ID da Conversa:</strong> #${conversaId}
                        </div>
                        <div class="detail-item">
                            <strong>Status:</strong> <span class="badge bg-success">Ativo</span>
                        </div>
                        <div class="detail-item">
                            <strong>Criado em:</strong> ${new Date().toLocaleDateString('pt-BR')}
                        </div>
                        <div class="detail-item">
                            <strong>Última atividade:</strong> ${new Date().toLocaleString('pt-BR')}
                        </div>
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Funcionalidade em desenvolvimento:</strong> Em breve você poderá ver todos os detalhes da conversa aqui.
                        </div>
                    </div>
                `);
            }, 1000);
        }

        function exportarRelatorio(formato) {
            // Coletar dados do formulário atual
            const formData = new FormData($('#formFiltros')[0]);
            const params = new URLSearchParams();
            
            // Adicionar parâmetros do form
            for (let pair of formData.entries()) {
                if (pair[1]) {
                    params.append(pair[0], pair[1]);
                }
            }
            
            // Adicionar formato
            params.append('formato', formato);
            
            // Mostrar loading
            $('#loadingOverlay').fadeIn(200);
            
            // Simular delay e abrir em nova janela
            setTimeout(function() {
                const url = `<?= URL ?>/relatorios/conversas?${params.toString()}`;
                window.open(url, '_blank');
                $('#loadingOverlay').fadeOut(200);
            }, 1000);
        }

        function refreshTable() {
            if (dataTable) {
                dataTable.ajax.reload();
            } else {
                location.reload();
            }
        }
    </script>
    
    <style>
        /* Estilos personalizados */
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        
        .filter-section {
            margin-bottom: 2rem;
        }
        
        .filter-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .stats-section {
            margin-bottom: 2rem;
        }
        
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-right: 1rem;
        }
        
        .stat-icon.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
        .stat-icon.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .stat-icon.warning { background: linear-gradient(135deg, #ffc107, #d39e00); }
        .stat-icon.info { background: linear-gradient(135deg, #17a2b8, #138496); }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .stat-trend {
            font-size: 0.8rem;
        }
        
        .conversations-table {
            margin-bottom: 2rem;
        }
        
        .conversation-id {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .contact-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .contact-name {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .contact-department {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .phone-number {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        .agent-badge {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-container {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
        
        .priority-indicator {
            text-align: center;
        }
        
        .message-stats {
            text-align: center;
        }
        
        .total-messages {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .message-breakdown {
            margin-top: 0.25rem;
        }
        
        .date-info {
            text-align: center;
        }
        
        .date-primary {
            font-weight: 500;
        }
        
        .date-secondary {
            margin-top: 0.25rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-icon {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        .empty-title {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .empty-text {
            color: #6c757d;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .table-controls {
            margin-bottom: 1rem;
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        .table-footer {
            margin-top: 1rem;
        }
        
        .conversation-details .detail-item {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .conversation-details .detail-item:last-child {
            border-bottom: none;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            z-index: 9999;
        }
        
        .loading-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .page-header .row {
                text-align: center;
            }
            
            .page-header .col-md-4 {
                margin-top: 1rem;
            }
            
            .stat-card .d-flex {
                flex-direction: column;
                text-align: center;
            }
            
            .stat-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .contact-info {
                flex-direction: column;
                text-align: center;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }

    </style>
</body>
</html> 