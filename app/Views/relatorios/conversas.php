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
                                                            <!-- Dropdown para alterar atendente -->
                                                            <div class="dropdown mt-1">
                                                                <button class="btn btn-sm btn-outline-info dropdown-toggle" 
                                                                        type="button" 
                                                                        data-bs-toggle="dropdown" 
                                                                        aria-expanded="false"
                                                                        title="Alterar atendente">
                                                                    <i class="fas fa-exchange-alt"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><h6 class="dropdown-header">Transferir para:</h6></li>
                                                                    <?php foreach ($atendentes as $atendente): ?>
                                                                        <?php if ($atendente->id != $conversa->atendente_id): ?>
                                                                            <li>
                                                                                <a class="dropdown-item" href="#" 
                                                                                   onclick="alterarAtendente(<?= $conversa->id ?>, <?= $atendente->id ?>, '<?= htmlspecialchars($atendente->nome) ?>')">
                                                                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                                                                    <?= $atendente->nome ?>
                                                                                    <?php if ($atendente->status !== 'ativo'): ?>
                                                                                        <span class="badge bg-warning ms-1">Inativo</span>
                                                                                    <?php endif; ?>
                                                                                </a>
                                                                            </li>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($atendentes) || count($atendentes) <= 1): ?>
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li><span class="dropdown-item-text text-muted">Nenhum outro atendente disponível</span></li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-user-slash me-1"></i>
                                                                Sem atendente
                                                            </span>
                                                            <!-- Dropdown para atribuir atendente -->
                                                            <div class="dropdown mt-1">
                                                                <button class="btn btn-sm btn-outline-success dropdown-toggle" 
                                                                        type="button" 
                                                                        data-bs-toggle="dropdown" 
                                                                        aria-expanded="false"
                                                                        title="Atribuir atendente">
                                                                    <i class="fas fa-user-plus"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><h6 class="dropdown-header">Atribuir para:</h6></li>
                                                                    <?php foreach ($atendentes as $atendente): ?>
                                                                        <li>
                                                                            <a class="dropdown-item" href="#" 
                                                                               onclick="alterarAtendente(<?= $conversa->id ?>, <?= $atendente->id ?>, '<?= htmlspecialchars($atendente->nome) ?>')">
                                                                                <i class="fas fa-user-tie text-primary me-2"></i>
                                                                                <?= $atendente->nome ?>
                                                                                <?php if ($atendente->status !== 'ativo'): ?>
                                                                                    <span class="badge bg-warning ms-1">Inativo</span>
                                                                                <?php endif; ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($atendentes)): ?>
                                                                        <li><span class="dropdown-item-text text-muted">Nenhum atendente disponível</span></li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
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
                                                            
                                                            <!-- Dropdown para alterar status -->
                                                            <div class="dropdown mt-1">
                                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                        type="button" 
                                                                        data-bs-toggle="dropdown" 
                                                                        aria-expanded="false"
                                                                        title="Alterar status">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a class="dropdown-item" href="#" 
                                                                           onclick="alterarStatus(<?= $conversa->id ?>, 'pendente')">
                                                                            <i class="fas fa-clock text-warning me-2"></i>
                                                                            Marcar como Pendente
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item" href="#" 
                                                                           onclick="alterarStatus(<?= $conversa->id ?>, 'aberto')">
                                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                                            Marcar como Aberto
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item text-danger" href="#" 
                                                                           onclick="alterarStatus(<?= $conversa->id ?>, 'fechado')">
                                                                            <i class="fas fa-times-circle me-2"></i>
                                                                            Fechar Conversa
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
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
                                                            <!-- <a href="<?= URL ?>/chat/conversa/<?= $conversa->id ?>" 
                                                               class="btn btn-primary btn-sm" 
                                                               title="Visualizar conversa">
                                                                <i class="fas fa-eye"></i>
                                                            </a> -->
                                                            <button class="btn btn-outline-success btn-sm" 
                                                                    onclick="visualizarConversa(<?= $conversa->id ?>, '<?= htmlspecialchars($conversa->contato_nome ?? 'Sem nome') ?>', '<?= $conversa->numero ?>', '<?= htmlspecialchars($conversa->atendente_nome ?? 'Sem atendente') ?>', '<?= $conversa->status ?>')"
                                                                    title="Visualizar conversa">
                                                                <i class="fas fa-comments"></i>
                                                            </button>
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

    <!-- Modal Visualizar Conversa -->
    <div class="modal fade" id="modalVisualizarConversa" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-comments me-2"></i>
                        Visualizar Conversa
                        <span id="conversaIdTitle" class="badge bg-primary ms-2"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Informações da conversa -->
                    <div class="conversation-info mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong><i class="fas fa-user me-2"></i>Contato:</strong>
                                    <span id="conversaContato"></span>
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-phone me-2"></i>Número:</strong>
                                    <span id="conversaNumero"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong><i class="fas fa-user-tie me-2"></i>Atendente:</strong>
                                    <span id="conversaAtendente"></span>
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-info-circle me-2"></i>Status:</strong>
                                    <span id="conversaStatus"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Área de mensagens -->
                    <div class="messages-container">
                        <div class="messages-header">
                            <h6><i class="fas fa-envelope me-2"></i>Mensagens</h6>
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshMessages()">
                                <i class="fas fa-sync-alt"></i>
                                Atualizar
                            </button>
                        </div>
                        <div class="messages-area" id="messagesArea">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                <p class="mt-2">Carregando mensagens...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Fechar
                    </button>
                    <!-- <a href="#" class="btn btn-primary" id="btnAbrirChat" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>
                        Abrir no Chat
                    </a> -->
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
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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

        function ucfirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
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

        function alterarStatus(conversaId, novoStatus) {
            // Confirmar ação
            let mensagemConfirmacao = '';
            let iconClass = '';
            let title = '';
            
            switch(novoStatus) {
                case 'pendente':
                    title = 'Marcar como Pendente';
                    mensagemConfirmacao = 'Deseja marcar esta conversa como pendente?';
                    iconClass = 'fas fa-clock text-warning';
                    break;
                case 'aberto':
                    title = 'Marcar como Aberto';
                    mensagemConfirmacao = 'Deseja marcar esta conversa como aberta?';
                    iconClass = 'fas fa-check-circle text-success';
                    break;
                case 'fechado':
                    title = 'Fechar Conversa';
                    mensagemConfirmacao = 'Deseja fechar esta conversa? Uma mensagem de encerramento será enviada automaticamente.';
                    iconClass = 'fas fa-times-circle text-danger';
                    break;
            }
            
            // Usar SweetAlert2 para confirmação
            Swal.fire({
                title: title,
                text: mensagemConfirmacao,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, alterar!',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    const loadingOverlay = $('#loadingOverlay');
                    loadingOverlay.fadeIn(200);
                    
                    // Fazer requisição
                    fetch('<?= URL ?>/relatorios/alterar-status-conversa', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            conversa_id: conversaId,
                            status: novoStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        loadingOverlay.fadeOut(200);
                        
                        if (data.success) {
                            // Mostrar mensagem de sucesso
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Alterado!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            
                            // Se foi fechada e enviou mensagem de encerramento
                            if (novoStatus === 'fechado' && data.mensagem_encerramento_enviada) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Mensagem de Encerramento',
                                    text: 'A mensagem de encerramento foi enviada automaticamente para o cliente.',
                                    confirmButtonText: 'OK'
                                });
                            }
                            
                            // Recarregar a página após um delay
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                            
                        } else {
                            // Mostrar erro
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.message || 'Erro ao alterar status da conversa',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        loadingOverlay.fadeOut(200);
                        console.error('Erro:', error);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro de conexão. Tente novamente.',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        function alterarAtendente(conversaId, novoAtendenteId, novoAtendenteNome) {
            // Confirmar ação
            Swal.fire({
                title: 'Transferir Conversa',
                text: `Deseja transferir esta conversa para ${novoAtendenteNome}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, transferir!',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    const loadingOverlay = $('#loadingOverlay');
                    loadingOverlay.fadeIn(200);
                    
                    // Fazer requisição
                    fetch('<?= URL ?>/relatorios/alterar-atendente-conversa', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            conversa_id: conversaId,
                            atendente_id: novoAtendenteId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        loadingOverlay.fadeOut(200);
                        
                        if (data.success) {
                            // Mostrar mensagem de sucesso
                            Swal.fire({
                                icon: 'success',
                                title: 'Conversa Transferida!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            
                            // Recarregar a página após um delay
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                            
                        } else {
                            // Mostrar erro
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.message || 'Erro ao transferir conversa',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        loadingOverlay.fadeOut(200);
                        console.error('Erro:', error);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro de conexão. Tente novamente.',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        function visualizarConversa(conversaId, contatoNome, numero, atendenteNome, status) {
            // Armazenar ID da conversa no modal
            $('#modalVisualizarConversa').data('conversa-id', conversaId);
            
            // Atualizar informações da conversa
            $('#conversaIdTitle').text(`#${conversaId}`);
            $('#conversaContato').text(contatoNome || 'Sem nome');
            $('#conversaNumero').text(numero || 'N/A');
            $('#conversaAtendente').text(atendenteNome || 'Sem atendente');
            $('#conversaStatus').text(status ? ucfirst(status) : 'N/A');
            
            // Atualizar link para abrir no chat
            $('#btnAbrirChat').attr('href', `<?= URL ?>/chat/conversa/${conversaId}`);

            // Limpar e mostrar mensagens anteriores
            $('#messagesArea').empty();
            $('#messagesArea').append(`
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando mensagens...</p>
                </div>
            `);

            // Mostrar modal
            $('#modalVisualizarConversa').modal('show');

            // Carregar mensagens da API
            fetch(`<?= URL ?>/relatorios/conversa/${conversaId}/mensagens`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#messagesArea').empty();
                        if (data.mensagens.length > 0) {
                            data.mensagens.forEach(mensagem => {
                                const messageDiv = document.createElement('div');
                                messageDiv.classList.add('message-item');
                                messageDiv.innerHTML = `
                                    <div class="message-bubble ${mensagem.tipo_mensagem === 'recebida' ? 'received' : 'sent'}">
                                        <div class="message-content">
                                            <p class="message-text">${mensagem.texto}</p>
                                            <div class="message-meta">
                                                <small class="message-time">${mensagem.criado_em}</small>
                                                ${mensagem.tipo_mensagem === 'enviada' ? `<small class="message-status">${mensagem.status}</small>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#messagesArea').append(messageDiv);
                            });
                        } else {
                            $('#messagesArea').html(`
                                <div class="text-center text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Nenhuma mensagem encontrada nesta conversa.</p>
                                </div>
                            `);
                        }
                        // Reaplicar tooltips após carregar mensagens
                        initTooltips();
                    } else {
                        $('#messagesArea').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                Erro ao carregar mensagens: ${data.message || 'Desconhecido'}
                            </div>
                        `);
                    }
                })
                .catch(error => {
                    $('#messagesArea').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i>
                            Erro de conexão: ${error.message}
                        </div>
                    `);
                });
        }

        function refreshMessages() {
            const conversaId = $('#modalVisualizarConversa').data('conversa-id');
            if (conversaId) {
                visualizarConversa(conversaId, $('#conversaContato').text(), $('#conversaNumero').text(), $('#conversaAtendente').text(), $('#conversaStatus').text());
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
            margin-bottom: 0.5rem;
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
        
        /* Estilo do dropdown de status */
        .status-container .dropdown {
            margin-top: 0.5rem;
        }
        
        .status-container .dropdown-toggle {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
        }
        
        .status-container .dropdown-menu {
            font-size: 0.8rem;
            min-width: 180px;
        }
        
        .status-container .dropdown-item {
            padding: 0.5rem 0.75rem;
        }
        
        .status-container .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .status-container .dropdown-item.text-danger:hover {
            background-color: #f8d7da;
        }
        
        /* Estilo do dropdown de atendente */
        .agent-badge {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .agent-badge .dropdown {
            margin-top: 0.5rem;
        }
        
        .agent-badge .dropdown-toggle {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
        }
        
        .agent-badge .dropdown-menu {
            font-size: 0.8rem;
            min-width: 200px;
        }
        
        .agent-badge .dropdown-item {
            padding: 0.5rem 0.75rem;
        }
        
        .agent-badge .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .agent-badge .dropdown-header {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            padding: 0.5rem 0.75rem;
        }
        
        /* Estilos para o modal de visualização de conversa */
        .conversation-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
        }
        
        .info-item {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .info-item strong {
            min-width: 100px;
            color: #495057;
        }
        
        .messages-container {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .messages-header {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .messages-header h6 {
            margin: 0;
            color: #495057;
        }
        
        .messages-area {
            height: 400px;
            overflow-y: auto;
            padding: 1rem;
            background: #fff;
        }
        
        .message-item {
            margin-bottom: 1rem;
        }
        
        .message-bubble {
            max-width: 80%;
            padding: 0.75rem;
            border-radius: 1rem;
            position: relative;
        }
        
        .message-bubble.received {
            background: #e3f2fd;
            color: #1565c0;
            margin-right: auto;
            border-bottom-left-radius: 0.25rem;
        }
        
        .message-bubble.sent {
            background: #e8f5e8;
            color: #2e7d32;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }
        
        .message-content {
            word-wrap: break-word;
        }
        
        .message-text {
            margin: 0 0 0.5rem 0;
            line-height: 1.4;
        }
        
        .message-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
        }
        
        .message-time {
            color: #6c757d;
        }
        
        .message-status {
            color: #28a745;
            font-weight: 500;
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
        
        /* Responsividade do modal */
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

            .modal-xl {
                max-width: 95%;
            }
            
            .messages-area {
                height: 300px;
            }
            
            .message-bubble {
                max-width: 90%;
            }
        }

    </style>
</body>
</html> 