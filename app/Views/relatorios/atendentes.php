
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
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= URL ?>/chat" class="nav-link">
                        <i class="fas fa-comments"></i> Chat
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= URL ?>/contatos" class="nav-link">
                        <i class="fas fa-address-book"></i> Contatos
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= URL ?>/relatorios" class="nav-link active">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?= URL ?>/usuarios" class="nav-link">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                </div>
                <?php if ($usuario_logado['perfil'] === 'admin'): ?>
                <div class="nav-item">
                    <a href="<?= URL ?>/configuracoes" class="nav-link">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>
        <!-- Conteúdo principal -->
        <main class="main-content" id="mainContent">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="topbar-title">
                        <i class="fas fa-user-tie me-2"></i>
                        Relatório de Atendentes
                    </h1>
                </div>
                <div class="topbar-right">
                <a href="<?= URL ?>/relatorios" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Voltar
                    </a>
                    <button class="btn btn-outline-secondary btn-sm me-2" id="toggleTheme" title="Alternar tema">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="status-badge status-online">
                        <span class="status-indicator"></span>
                        <?= ucfirst($usuario_logado['perfil']) ?>
                    </div>
                    <div class="user-menu">
                        <div class="user-avatar" title="<?= $usuario_logado['nome'] ?>">
                            <?= strtoupper(substr($usuario_logado['nome'], 0, 2)) ?>
                        </div>
                    </div>
                    <a href="<?= URL ?>/logout" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="page-header mb-4">
                    <h2 class="page-title">
                        <i class="fas fa-user-tie me-2 text-primary"></i>
                        Performance dos Atendentes
                    </h2>
                    <p class="page-subtitle">Acompanhe o desempenho individual dos atendentes</p>
                </div>
                <!-- Filtro de Período -->
                <div class="period-filter-card mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="get" class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Data Início</label>
                                    <input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Data Fim</label>
                                    <input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Atendente</label>
                                    <select class="form-select" name="atendente_id">
                                        <option value="">Todos</option>
                                        <?php foreach ($atendentes as $at): ?>
                                            <option value="<?= $at->id ?>" <?= $filtros['atendente_id'] == $at->id ? 'selected' : '' ?>><?= htmlspecialchars($at->nome) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Tabela de Performance -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-chart-line me-2"></i>Performance dos Atendentes
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Atendente</th>
                                        <th>Total Conversas</th>
                                        <th>Conversas Abertas</th>
                                        <th>Conversas Fechadas</th>
                                        <th>Total Mensagens</th>
                                        <th>Tempo Médio Resposta (min)</th>
                                        <th>Avaliação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($performance)): foreach ($performance as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item->nome) ?></td>
                                        <td><?= $item->total_conversas ?></td>
                                        <td><?= $item->conversas_abertas ?></td>
                                        <td><?= $item->conversas_fechadas ?></td>
                                        <td><?= $item->total_mensagens ?></td>
                                        <td><?= number_format($item->tempo_medio_resposta, 2) ?></td>
                                        <td><?= number_format($item->avaliacao_media, 1) ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="7">Nenhum dado encontrado.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Ranking dos Atendentes -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-trophy me-2"></i>Ranking dos Atendentes
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Posição</th>
                                        <th>Atendente</th>
                                        <th>Avaliação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ranking)): $pos=1; foreach ($ranking as $item): ?>
                                    <tr>
                                        <td><?= $pos++ ?></td>
                                        <td><?= htmlspecialchars($item->nome) ?></td>
                                        <td><?= number_format($item->avaliacao_media, 1) ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="3">Nenhum dado encontrado.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
        <?php include 'app/Views/include/linkjs.php' ?>
</body>
</html> 