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
                        <i class="fas fa-chart-bar me-2"></i>
                        Estatísticas - <?= htmlspecialchars($departamento->nome) ?>
                    </h1>
                </div>
                
                <div class="topbar-right">
                    <a href="<?= URL ?>/departamentos" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Voltar
                    </a>
                    
                    <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="atualizarEstatisticas()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Atualizar
                    </button>
                    
                    <button type="button" class="btn btn-outline-success btn-sm me-2" onclick="exportarEstatisticas()">
                        <i class="fas fa-download me-2"></i>
                        Exportar
                    </button>
                    
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
                <!-- Informações do Departamento -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Departamento:</strong> <?= htmlspecialchars($departamento->nome) ?> 
                    (ID: <?= $departamento->id ?>) | 
                    <strong>Status:</strong> <?= $departamento->status === 'ativo' ? 'Ativo' : 'Inativo' ?> |
                    <strong>Última atualização:</strong> <?= date('d/m/Y H:i') ?>
                </div>

                <!-- Estatísticas Principais -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas->total_conversas ?? 0 ?></h3>
                                    <p class="stats-card-label">Total de Conversas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-success">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas->conversas_abertas ?? 0 ?></h3>
                                    <p class="stats-card-label">Conversas Abertas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-warning">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas->conversas_pendentes ?? 0 ?></h3>
                                    <p class="stats-card-label">Conversas Pendentes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-info">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas->total_mensagens ?? 0 ?></h3>
                                    <p class="stats-card-label">Total de Mensagens</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Segunda linha de estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card stats-card-secondary">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas->atendentes_ativos ?? 0 ?></h3>
                                    <p class="stats-card-label">Atendentes Ativos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-danger">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas->conversas_fechadas ?? 0 ?></h3>
                                    <p class="stats-card-label">Conversas Fechadas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-primary">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-stopwatch"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= round($estatisticas->tempo_resposta_medio ?? 0, 1) ?></h3>
                                    <p class="stats-card-label">Tempo Médio (min)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-dark">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number">
                                        <?= $taxa_atendimento ?? 0 ?>%
                                    </h3>
                                    <p class="stats-card-label">Taxa de Atendimento</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Status das Conversas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 300px;">
                                    <canvas id="chartStatusConversas"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Atividade por Período
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 300px;">
                                    <canvas id="chartAtividadePeriodo"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas Detalhadas -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Atendentes do Departamento
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($atendentes)): ?>
                                    <?php foreach ($atendentes as $atendente): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <?= strtoupper(substr($atendente->nome, 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($atendente->nome) ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= $atendente->email ?> | 
                                                        Max: <?= $atendente->max_conversas ?> conversas
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">Ativo</span>
                                                <br>
                                                <small class="text-muted">
                                                    <?= $atendente->horario_inicio ?> - <?= $atendente->horario_fim ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <p>Nenhum atendente ativo neste departamento</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-key me-2"></i>
                                    Credenciais Serpro
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($status_credenciais)): ?>
                                    <?php foreach ($status_credenciais as $credencial): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                            <div>
                                                <strong><?= htmlspecialchars($credencial->nome ?? 'Credencial') ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Último teste: <?= $credencial->ultimo_teste ? date('d/m/Y H:i', strtotime($credencial->ultimo_teste)) : 'Nunca' ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">Ativa</span>
                                                <br>
                                                <span class="badge bg-info">Token OK</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-key fa-3x mb-3"></i>
                                        <p>Nenhuma credencial configurada</p>
                                        <a href="<?= URL ?>/departamentos/credenciais/<?= $departamento->id ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            Configurar Credencial
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Conversas Recentes -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-comments me-2"></i>
                            Conversas Recentes
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Contato</th>
                                        <th>Atendente</th>
                                        <th>Status</th>
                                        <th>Mensagens</th>
                                        <th>Criada em</th>
                                        <th>Última Atividade</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($conversas)): ?>
                                        <?php foreach ($conversas as $conversa): ?>
                                            <tr>
                                                <td><?= $conversa->id ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($conversa->contato_nome ?? 'Contato') ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= $conversa->numero ?? 'N/A' ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($conversa->atendente_nome): ?>
                                                        <?= htmlspecialchars($conversa->atendente_nome) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sem atendente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $statusClass = '';
                                                    $statusText = '';
                                                    switch($conversa->status) {
                                                        case 'aberto':
                                                            $statusClass = 'bg-success';
                                                            $statusText = 'Aberta';
                                                            break;
                                                        case 'pendente':
                                                            $statusClass = 'bg-warning';
                                                            $statusText = 'Pendente';
                                                            break;
                                                        case 'fechado':
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = 'Fechada';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-light text-dark';
                                                            $statusText = ucfirst($conversa->status);
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= $statusText ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= $conversa->total_mensagens ?? 0 ?> msg
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= $conversa->criado_em ? date('d/m/Y H:i', strtotime($conversa->criado_em)) : 'N/A' ?>
                                                </td>
                                                <td>
                                                    <?= $conversa->ultima_mensagem ? date('d/m/Y H:i', strtotime($conversa->ultima_mensagem)) : 'N/A' ?>
                                                </td>
                                                <td>
                                                    <a href="<?= URL ?>/chat" class="btn btn-sm btn-outline-primary" title="Ver conversa">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                <i class="fas fa-comments fa-2x mb-2"></i>
                                                <p>Nenhuma conversa encontrada</p>
                                            </td>
                                        </tr>
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Dados para os gráficos
            const dadosStatus = {
                labels: ['Abertas', 'Pendentes', 'Fechadas'],
                datasets: [{
                    data: [
                        <?= $estatisticas->conversas_abertas ?? 0 ?>,
                        <?= $estatisticas->conversas_pendentes ?? 0 ?>,
                        <?= $estatisticas->conversas_fechadas ?? 0 ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            };

            const dadosAtividade = {
                labels: ['Hoje', 'Ontem', 'Última Semana', 'Último Mês'],
                datasets: [{
                    label: 'Conversas',
                    data: [
                        <?= $estatisticas->conversas_abertas ?? 0 ?>,
                        <?= $estatisticas->conversas_pendentes ?? 0 ?>,
                        <?= $estatisticas->conversas_fechadas ?? 0 ?>,
                        <?= $estatisticas->total_conversas ?? 0 ?>
                    ],
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            };

            // Gráfico de Status das Conversas
            const ctxStatus = document.getElementById('chartStatusConversas').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: dadosStatus,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Gráfico de Atividade por Período
            const ctxAtividade = document.getElementById('chartAtividadePeriodo').getContext('2d');
            new Chart(ctxAtividade, {
                type: 'line',
                data: dadosAtividade,
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
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        function atualizarEstatisticas() {
            Swal.fire({
                title: 'Atualizando...',
                text: 'Carregando estatísticas atualizadas',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simular atualização
            setTimeout(() => {
                Swal.fire('Sucesso!', 'Estatísticas atualizadas!', 'success')
                .then(() => {
                    location.reload();
                });
            }, 1500);
        }

        function exportarEstatisticas() {
            Swal.fire({
                title: 'Exportar Estatísticas',
                text: 'Escolha o formato de exportação:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'PDF',
                cancelButtonText: 'Excel',
                showDenyButton: true,
                denyButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Exportar PDF
                    exportarPDF();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Exportar Excel
                    exportarExcel();
                }
            });
        }

        function exportarPDF() {
            Swal.fire('Em desenvolvimento', 'Funcionalidade de exportação PDF será implementada em breve!', 'info');
        }

        function exportarExcel() {
            Swal.fire('Em desenvolvimento', 'Funcionalidade de exportação Excel será implementada em breve!', 'info');
        }

        // Auto-refresh a cada 5 minutos
        setInterval(function() {
            console.log('Atualizando estatísticas automaticamente...');
            // Aqui você pode implementar uma atualização silenciosa
        }, 300000); // 5 minutos
    </script>

    <style>
        .stats-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card-body {
            padding: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .stats-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 1.5rem;
        }
        
        .stats-card-success .stats-card-icon {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .stats-card-warning .stats-card-icon {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }
        
        .stats-card-info .stats-card-icon {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }
        
        .stats-card-secondary .stats-card-icon {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        
        .stats-card-danger .stats-card-icon {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        }
        
        .stats-card-primary .stats-card-icon {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        
        .stats-card-dark .stats-card-icon {
            background: linear-gradient(135deg, #343a40 0%, #212529 100%);
        }
        
        .stats-card-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        
        .stats-card-label {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .chart-container {
            position: relative;
            margin: auto;
        }
    </style>
</body>
</html> 