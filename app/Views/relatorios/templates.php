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
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="topbar-title">
                        <i class="fas fa-file-alt me-2"></i>
                        Relatório de Templates
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
                        <i class="fas fa-file-alt me-2 text-primary"></i>
                        Utilização de Templates
                    </h2>
                    <p class="page-subtitle">Acompanhe o uso e desempenho dos templates de mensagem</p>
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
                                    <label class="form-label">Template</label>
                                    <input type="text" class="form-control" name="template" value="<?= htmlspecialchars($filtros['template']) ?>" placeholder="Nome do template">
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
                <!-- Tabela de Utilização -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-file-alt me-2"></i>Utilização de Templates
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Template</th>
                                        <th>Total Utilizações</th>
                                        <th>Sucesso</th>
                                        <th>Falhas</th>
                                        <th>Taxa Sucesso (%)</th>
                                        <th>Última utilização</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($utilizacao)): foreach ($utilizacao as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item->template) ?></td>
                                        <td><?= $item->total_utilizacoes ?></td>
                                        <td><?= $item->sucessos ?></td>
                                        <td><?= $item->falhas ?></td>
                                        <td><?= number_format($item->taxa_sucesso, 2) ?></td>
                                        <td><?= $item->ultima_utilizacao ? date('d/m/Y H:i', strtotime($item->ultima_utilizacao)) : 'N/A' ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="6">Nenhum dado encontrado.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Estatísticas Gerais -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-chart-pie me-2"></i>Estatísticas Gerais
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <?php if (!empty($estatisticas)): foreach ($estatisticas as $chave => $valor): ?>
                                <li><strong><?= htmlspecialchars($chave) ?>:</strong> <?= htmlspecialchars($valor) ?></li>
                            <?php endforeach; else: ?>
                                <li>Nenhuma estatística disponível.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include 'app/Views/include/linkjs.php' ?>
</body>
</html> 