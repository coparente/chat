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
                        <i class="fas fa-clipboard-list me-2"></i>
                        Logs do Sistema
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
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>
                                Logs do Sistema
                            </h2>
                            <p class="page-subtitle">
                                Monitoramento de atividades e acessos ao sistema
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#limparLogsModal">
                                <i class="fas fa-trash me-2"></i>
                                Limpar Logs Antigos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="stats-section mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?= $estatisticas->atividades->total_atividades ?? 0 ?></h3>
                                    <p>Atividades Registradas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-sign-in-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?= $estatisticas->acessos->total_acessos ?? 0 ?></h3>
                                    <p>Total de Acessos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?= $estatisticas->atividades->usuarios_ativos ?? 0 ?></h3>
                                    <p>Usuários Ativos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?= $estatisticas->acessos->ips_unicos ?? 0 ?></h3>
                                    <p>IPs Únicos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filters-section mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form id="filtrosForm" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Tipo de Log</label>
                                        <select class="form-select" name="tipo" id="tipoLog">
                                            <option value="todos" <?= $filtros['tipo'] === 'todos' ? 'selected' : '' ?>>Todos</option>
                                            <option value="atividades" <?= $filtros['tipo'] === 'atividades' ? 'selected' : '' ?>>Atividades</option>
                                            <option value="acessos" <?= $filtros['tipo'] === 'acessos' ? 'selected' : '' ?>>Acessos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Usuário</label>
                                        <select class="form-select" name="usuario_id" id="usuarioFiltro">
                                            <option value="">Todos</option>
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <option value="<?= $usuario->id ?>" <?= $filtros['usuario_id'] == $usuario->id ? 'selected' : '' ?>>
                                                    <?= $usuario->nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Data Início</label>
                                        <input type="date" class="form-control" name="data_inicio" value="<?= $filtros['data_inicio'] ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Data Fim</label>
                                        <input type="date" class="form-control" name="data_fim" value="<?= $filtros['data_fim'] ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status Acesso</label>
                                        <select class="form-select" name="status_acesso" id="statusAcesso">
                                            <option value="">Todos</option>
                                            <option value="1">Sucesso</option>
                                            <option value="0">Falha</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-2"></i>
                                                Filtrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="logs-tabs">
                    <ul class="nav nav-tabs" id="logsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="atividades-tab" data-bs-toggle="tab" data-bs-target="#atividades" type="button" role="tab">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Atividades (<?= count($atividades) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="acessos-tab" data-bs-toggle="tab" data-bs-target="#acessos" type="button" role="tab">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Acessos (<?= count($acessos) ?>)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="logsTabsContent">
                        <!-- Tab Atividades -->
                        <div class="tab-pane fade show active" id="atividades" role="tabpanel">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="tabelaAtividades">
                                            <thead>
                                                <tr>
                                                    <th>Data/Hora</th>
                                                    <th>Usuário</th>
                                                    <th>Ação</th>
                                                    <th>Descrição</th>
                                                    <th>Perfil</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($atividades)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            Nenhuma atividade encontrada no período selecionado
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($atividades as $atividade): ?>
                                                        <tr>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?= Helper::dataBr($atividade->data_hora, true) ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="user-avatar-sm me-2">
                                                                        <?= strtoupper(substr($atividade->usuario_nome ?? 'Sistema', 0, 2)) ?>
                                                                    </div>
                                                                    <div>
                                                                        <strong><?= $atividade->usuario_nome ?? 'Sistema' ?></strong>
                                                                        <br>
                                                                        <small class="text-muted"><?= $atividade->usuario_email ?? '' ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary"><?= ucfirst($atividade->acao) ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted"><?= $atividade->descricao ?? '-' ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary"><?= ucfirst($atividade->usuario_perfil ?? 'Sistema') ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Acessos -->
                        <div class="tab-pane fade" id="acessos" role="tabpanel">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="tabelaAcessos">
                                            <thead>
                                                <tr>
                                                    <th>Data/Hora</th>
                                                    <th>Usuário</th>
                                                    <th>IP</th>
                                                    <th>Status</th>
                                                    <th>User Agent</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($acessos)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            Nenhum acesso encontrado no período selecionado
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($acessos as $acesso): ?>
                                                        <tr>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?= Helper::dataBr($acesso->data_hora, true) ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <?php if ($acesso->usuario_id): ?>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="user-avatar-sm me-2">
                                                                            <?= strtoupper(substr($acesso->usuario_nome ?? 'N/A', 0, 2)) ?>
                                                                        </div>
                                                                        <div>
                                                                            <strong><?= $acesso->usuario_nome ?? 'N/A' ?></strong>
                                                                            <br>
                                                                            <small class="text-muted"><?= $acesso->email ?? '' ?></small>
                                                                        </div>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted"><?= $acesso->email ?? 'Acesso anônimo' ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <code><?= $acesso->ip ?></code>
                                                            </td>
                                                            <td>
                                                                <?php if ($acesso->sucesso): ?>
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-check me-1"></i>
                                                                        Sucesso
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning">
                                                                        <i class="fas fa-times me-1"></i>
                                                                        Falha
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted" title="<?= $acesso->user_agent ?>">
                                                                    <?= strlen($acesso->user_agent) > 50 ? substr($acesso->user_agent, 0, 50) . '...' : $acesso->user_agent ?>
                                                                </small>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Limpar Logs -->
    <div class="modal fade" id="limparLogsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2 text-danger"></i>
                        Limpar Logs Antigos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> Esta ação irá remover permanentemente os logs antigos do sistema.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Manter logs dos últimos:</label>
                        <select class="form-select" id="diasManter">
                            <option value="7">7 dias</option>
                            <option value="15">15 dias</option>
                            <option value="30" selected>30 dias</option>
                            <option value="60">60 dias</option>
                            <option value="90">90 dias</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Dica:</strong> Recomendamos manter pelo menos 30 dias de logs para auditoria.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnLimparLogs">
                        <i class="fas fa-trash me-1"></i>
                        Limpar Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#tabelaAtividades').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                }
            });

            $('#tabelaAcessos').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                }
            });

            // Limpar logs
            $('#btnLimparLogs').on('click', function() {
                const dias = $('#diasManter').val();
                
                if (!confirm(`Tem certeza que deseja remover os logs com mais de ${dias} dias? Esta ação não pode ser desfeita.`)) {
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processando...');

                $.ajax({
                    url: '<?= URL ?>/configuracoes/limpar-logs',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ dias: parseInt(dias) }),
                    success: function(response) {
                        if (response.success) {
                            // Verificar se Swal está disponível
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Logs Limpos!',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                // Fallback para alert padrão
                                alert('Logs limpos com sucesso!');
                                location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro!',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Erro: ' + response.message);
                            }
                        }
                    },
                    error: function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: 'Erro ao limpar logs. Tente novamente.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Erro ao limpar logs. Tente novamente.');
                        }
                    },
                    complete: function() {
                        $('#btnLimparLogs').prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Limpar Logs');
                    }
                });
            });

            // Filtros automáticos
            $('#tipoLog, #usuarioFiltro, #statusAcesso').on('change', function() {
                $('#filtrosForm').submit();
            });
        });
    </script>
    
    <style>
        .stats-section {
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .stat-content h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .stat-content p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .filters-section {
            margin-bottom: 2rem;
        }
        
        .logs-tabs {
            margin-bottom: 2rem;
        }
        
        .user-avatar-sm {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
        
        /* Dark mode */
        body.dark-mode .stat-card {
            background: var(--dark-bg);
            color: var(--dark-text);
        }
        
        body.dark-mode .table th {
            background-color: var(--dark-secondary);
            color: var(--dark-text);
        }
        
        body.dark-mode .table {
            color: var(--dark-text);
        }
    </style>
</body>
</html> 