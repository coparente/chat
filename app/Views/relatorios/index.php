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
                        <i class="fas fa-chart-bar me-2"></i>
                        Centro de Relatórios
                    </h1>
                </div>
                
                <div class="topbar-right">
                    <!-- Toggle Dark Mode -->
                    <button class="btn btn-outline-secondary btn-sm me-2" id="toggleTheme" title="Alternar tema">
                        <i class="fas fa-moon"></i>
                    </button>
                    
                    <!-- Status do usuário -->
                    <div class="status-badge status-online">
                        <span class="status-indicator"></span>
                        <?= ucfirst($usuario_logado['perfil']) ?>
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
                <!-- Header da página -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="page-title">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>
                                Análise de Dados e Relatórios
                            </h2>
                            <p class="page-subtitle">
                                Insights detalhados sobre o desempenho do atendimento
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="fas fa-download me-2"></i>
                                Exportar Relatório
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtro de Período -->
                <div class="period-filter-card">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="period-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-1">Período de Análise</h5>
                                    <p class="text-muted mb-0">Selecione o período para visualizar os dados</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label">Data Início</label>
                                            <input type="date" class="form-control" id="dataInicio" 
                                                   value="<?= $periodo_padrao['inicio'] ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Data Fim</label>
                                            <input type="date" class="form-control" id="dataFim" 
                                                   value="<?= $periodo_padrao['fim'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Charts -->
                <div class="charts-section">
                    <div class="row g-4">
                        <!-- Gráfico Principal -->
                        <div class="col-lg-8">
                            <div class="chart-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-chart-line me-2 text-primary"></i>
                                                    Evolução de Conversas e Mensagens
                                                </h5>
                                                <small class="text-muted">Visualização temporal do volume de atendimentos</small>
                                            </div>
                                            <div class="chart-actions">
                                                <button class="btn btn-sm btn-outline-primary" onclick="atualizarGrafico('linha')">
                                                    <i class="fas fa-chart-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="atualizarGrafico('area')">
                                                    <i class="fas fa-chart-area"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="chartPrincipal" height="300"></canvas>
                                        </div>
                                        <div class="chart-legend">
                                            <div class="legend-item">
                                                <span class="legend-color" style="background: #25D366;"></span>
                                                <span class="legend-label">Conversas</span>
                                            </div>
                                            <div class="legend-item">
                                                <span class="legend-color" style="background: #FF6B6B;"></span>
                                                <span class="legend-label">Mensagens</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status das Conversas -->
                        <div class="col-lg-4">
                            <div class="chart-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-transparent">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-pie me-2 text-success"></i>
                                            Status das Conversas
                                        </h5>
                                        <small class="text-muted">Distribuição por estado</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="chartStatus" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos Secundários -->
                    <div class="row g-4 mt-2">
                        <div class="col-lg-6">
                            <div class="chart-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-transparent">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-trophy me-2 text-warning"></i>
                                            Top Atendentes
                                        </h5>
                                        <small class="text-muted">Ranking de produtividade</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="chartAtendentes" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="chart-card">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-transparent">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-alt me-2 text-info"></i>
                                            Templates Mais Usados
                                        </h5>
                                        <small class="text-muted">Utilização de templates</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="chartTemplates" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Relatórios Detalhados -->
                <div class="reports-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-folder-open me-2"></i>
                            Relatórios Detalhados
                        </h3>
                        <p class="section-subtitle">Acesse relatórios específicos para análises aprofundadas</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="report-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="report-icon mb-3">
                                            <i class="fas fa-comments"></i>
                                        </div>
                                        <h5 class="card-title">Relatório de Conversas</h5>
                                        <p class="card-text">Análise detalhada das conversas por período, status e atendente</p>
                                        <div class="report-stats">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Última atualização: agora
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="<?= URL ?>/relatorios/conversas" class="btn btn-primary w-100">
                                            <i class="fas fa-eye me-2"></i>
                                            Visualizar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="report-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="report-icon mb-3">
                                            <i class="fas fa-user-chart"></i>
                                        </div>
                                        <h5 class="card-title">Performance de Atendentes</h5>
                                        <p class="card-text">Análise de produtividade e eficiência dos atendentes</p>
                                        <div class="report-stats">
                                            <small class="text-muted">
                                                <i class="fas fa-users me-1"></i>
                                                Atendentes ativos
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="<?= URL ?>/relatorios/atendentes" class="btn btn-success w-100">
                                            <i class="fas fa-eye me-2"></i>
                                            Visualizar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="report-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="report-icon mb-3">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <h5 class="card-title">Utilização de Templates</h5>
                                        <p class="card-text">Estatísticas de uso e eficácia dos templates</p>
                                        <div class="report-stats">
                                            <small class="text-muted">
                                                <i class="fas fa-percentage me-1"></i>
                                                Taxa de sucesso
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="<?= URL ?>/relatorios/templates" class="btn btn-info w-100">
                                            <i class="fas fa-eye me-2"></i>
                                            Visualizar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="report-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="report-icon mb-3">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <h5 class="card-title">Volume de Mensagens</h5>
                                        <p class="card-text">Análise do volume e tipos de mensagens trocadas</p>
                                        <div class="report-stats">
                                            <small class="text-muted">
                                                <i class="fas fa-chart-bar me-1"></i>
                                                Tendência de crescimento
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="<?= URL ?>/relatorios/mensagens" class="btn btn-warning w-100">
                                            <i class="fas fa-eye me-2"></i>
                                            Visualizar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="report-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="report-icon mb-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <h5 class="card-title">Tempo de Resposta</h5>
                                        <p class="card-text">Análise dos tempos de resposta e SLA de atendimento</p>
                                        <div class="report-stats">
                                            <small class="text-muted">
                                                <i class="fas fa-tachometer-alt me-1"></i>
                                                Metas de SLA
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="<?= URL ?>/relatorios/tempo-resposta" class="btn btn-danger w-100">
                                            <i class="fas fa-eye me-2"></i>
                                            Visualizar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="report-card">
                                <div class="card border-0 shadow-sm h-100 border-primary">
                                    <div class="card-body text-center">
                                        <div class="report-icon mb-3 special">
                                            <i class="fas fa-download"></i>
                                        </div>
                                        <h5 class="card-title">Exportação Personalizada</h5>
                                        <p class="card-text">Gere relatórios customizados em Excel ou PDF</p>
                                        <div class="report-stats">
                                            <small class="text-muted">
                                                <i class="fas fa-cog me-1"></i>
                                                Configurações avançadas
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="fas fa-download me-2"></i>
                                            Exportar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Exportação -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-download me-2"></i>
                        Exportar Relatório Personalizado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formExport">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Tipo de Relatório
                                    </label>
                                    <select class="form-select" id="tipoRelatorio" required>
                                        <option value="">Selecione...</option>
                                        <option value="conversas">📞 Conversas</option>
                                        <option value="atendentes">👥 Atendentes</option>
                                        <option value="templates">📋 Templates</option>
                                        <option value="mensagens">💬 Mensagens</option>
                                        <option value="tempo_resposta">⏱️ Tempo de Resposta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-file-export me-1"></i>
                                        Formato
                                    </label>
                                    <select class="form-select" id="formatoExport">
                                        <option value="excel">📊 Excel (.xlsx)</option>
                                        <option value="pdf">📄 PDF</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Data Início
                                    </label>
                                    <input type="date" class="form-control" id="exportDataInicio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Data Fim
                                    </label>
                                    <input type="date" class="form-control" id="exportDataFim" required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Dica:</strong> Relatórios com muitos dados podem demorar alguns segundos para serem gerados.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnExportExcel">
                        <i class="fas fa-download me-1"></i>
                        Gerar Relatório
                    </button>
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
            <p class="mt-2">Carregando dados...</p>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <script>
        // Variáveis dos gráficos
        let chartPrincipal, chartStatus, chartAtendentes, chartTemplates;

        $(document).ready(function() {
            // Configurar datas padrão no modal
            $('#exportDataInicio').val($('#dataInicio').val());
            $('#exportDataFim').val($('#dataFim').val());

            // Carregar dados iniciais
            carregarDashboard();

            // Event listeners
            $('#dataInicio, #dataFim').on('change', function() {
                carregarDashboard();
                // Atualizar datas do modal
                $('#exportDataInicio').val($('#dataInicio').val());
                $('#exportDataFim').val($('#dataFim').val());
            });
            
            $('#btnExportExcel').on('click', function() {
                const tipo = $('#tipoRelatorio').val();
                const formato = $('#formatoExport').val();
                const dataInicio = $('#exportDataInicio').val();
                const dataFim = $('#exportDataFim').val();
                
                if (!tipo || !dataInicio || !dataFim) {
                    alert('Preencha todos os campos obrigatórios');
                    return;
                }
                
                const url = `<?= URL ?>/relatorios/${tipo}?formato=${formato}&data_inicio=${dataInicio}&data_fim=${dataFim}`;
                window.open(url, '_blank');
                
                $('#exportModal').modal('hide');
            });
        });

        function carregarDashboard() {
            const dataInicio = $('#dataInicio').val();
            const dataFim = $('#dataFim').val();
            
            mostrarLoading();
            
            $.ajax({
                url: '<?= URL ?>/relatorios/dashboard',
                data: { data_inicio: dataInicio, data_fim: dataFim },
                success: function(response) {
                    if (response.success) {
                        atualizarGraficos(response.dados);
                    } else {
                        mostrarErro('Erro ao carregar dados');
                    }
                },
                error: function() {
                    mostrarErro('Erro de conexão');
                },
                complete: function() {
                    ocultarLoading();
                }
            });
        }

        function atualizarGraficos(dados) {
            // Gráfico Principal - Conversas e Mensagens
            const ctxPrincipal = document.getElementById('chartPrincipal').getContext('2d');
            if (chartPrincipal) chartPrincipal.destroy();
            
            chartPrincipal = new Chart(ctxPrincipal, {
                type: 'line',
                data: {
                    labels: dados.conversas_por_dia.map(item => formatarData(item.data)),
                    datasets: [
                        {
                            label: 'Conversas',
                            data: dados.conversas_por_dia.map(item => item.total),
                            borderColor: '#25D366',
                            backgroundColor: 'rgba(37, 211, 102, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#25D366',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        },
                        {
                            label: 'Mensagens',
                            data: dados.mensagens_por_dia.map(item => item.total),
                            borderColor: '#FF6B6B',
                            backgroundColor: 'rgba(255, 107, 107, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#FF6B6B',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            cornerRadius: 8,
                            displayColors: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: '#6c757d'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6c757d'
                            }
                        }
                    }
                }
            });

            // Gráfico de Status
            const ctxStatus = document.getElementById('chartStatus').getContext('2d');
            if (chartStatus) chartStatus.destroy();
            
            chartStatus = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: dados.conversas_por_status.map(item => capitalizar(item.status)),
                    datasets: [{
                        data: dados.conversas_por_status.map(item => item.total),
                        backgroundColor: [
                            '#28a745', // Aberto
                            '#ffc107', // Pendente
                            '#6c757d', // Fechado
                            '#dc3545'  // Outros
                        ],
                        borderWidth: 0,
                        cutout: '60%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                color: '#6c757d'
                            }
                        }
                    }
                }
            });

            // Top Atendentes
            const ctxAtendentes = document.getElementById('chartAtendentes').getContext('2d');
            if (chartAtendentes) chartAtendentes.destroy();
            
            chartAtendentes = new Chart(ctxAtendentes, {
                type: 'bar',
                data: {
                    labels: dados.top_atendentes.map(item => item.nome),
                    datasets: [{
                        label: 'Conversas',
                        data: dados.top_atendentes.map(item => item.conversas),
                        backgroundColor: 'rgba(255, 193, 7, 0.8)',
                        borderColor: '#ffc107',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: '#6c757d'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6c757d'
                            }
                        }
                    }
                }
            });

            // Templates Mais Usados
            const ctxTemplates = document.getElementById('chartTemplates').getContext('2d');
            if (chartTemplates) chartTemplates.destroy();
            
            chartTemplates = new Chart(ctxTemplates, {
                type: 'bar',
                data: {
                    labels: dados.templates_mais_usados.map(item => item.template),
                    datasets: [{
                        label: 'Utilizações',
                        data: dados.templates_mais_usados.map(item => item.utilizacoes),
                        backgroundColor: 'rgba(23, 162, 184, 0.8)',
                        borderColor: '#17a2b8',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: '#6c757d'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6c757d'
                            }
                        }
                    }
                }
            });
        }

        function mostrarLoading() {
            $('#loadingOverlay').fadeIn(200);
        }

        function ocultarLoading() {
            $('#loadingOverlay').fadeOut(200);
        }

        function mostrarErro(mensagem) {
            // Implementar toast ou alert
            console.error(mensagem);
        }

        function formatarData(data) {
            return new Date(data).toLocaleDateString('pt-BR');
        }

        function capitalizar(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        function atualizarGrafico(tipo) {
            // Função para alterar tipo de gráfico
            console.log('Alterando para:', tipo);
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
        
        .period-filter-card {
            margin-bottom: 2rem;
        }
        
        .period-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .charts-section {
            margin-bottom: 3rem;
        }
        
        .chart-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }
        
        .legend-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .chart-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .reports-section {
            margin-bottom: 2rem;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .section-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .report-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }
        
        .report-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto;
        }
        
        .report-icon.special {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .report-stats {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
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
            
            .chart-legend {
                flex-direction: column;
                gap: 1rem;
            }
            
            .period-filter-card .row {
                text-align: center;
            }
            
            .period-filter-card .col-md-2,
            .period-filter-card .col-md-6 {
                margin-bottom: 1rem;
            }
        }
        
        /* Dark mode */
        body.dark-mode .report-icon {
            background: linear-gradient(135deg, var(--primary-dark), #0a5d2c);
        }
        
        body.dark-mode .period-icon {
            background: linear-gradient(135deg, var(--primary-dark), #0a5d2c);
        }
        
        body.dark-mode .loading-overlay {
            background: rgba(0, 0, 0, 0.8);
        }
        
        body.dark-mode .loading-content {
            color: #ffffff;
        }
    </style>
</body>
</html> 