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
<style>
    /* Estilos melhorados para os cards */
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        pointer-events: none;
    }
    
    .stats-card-body {
        display: flex;
        align-items: center;
        position: relative;
        z-index: 1;
    }
    
    .stats-card-icon {
        font-size: 3rem;
        margin-right: 20px;
        color: rgba(255,255,255,0.9);
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stats-card-content {
        flex: 1;
    }
    
    .stats-card-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stats-card-label {
        margin: 5px 0 0 0;
        color: rgba(255,255,255,0.9);
        font-weight: 500;
        font-size: 1.1rem;
    }
    
    .stats-card small {
        color: rgba(255,255,255,0.8);
        font-size: 0.85rem;
    }
    
    /* Variações de cores para os cards */
    .stats-card-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .stats-card-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stats-card-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    /* Cards de informação */
    .info-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    .info-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .info-card-header i {
        font-size: 1.5rem;
        color: #6c757d;
    }
    
    .info-card-header h5 {
        margin: 0;
        color: #495057;
        font-weight: 600;
    }
    
    .info-card-body {
        padding: 20px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #6c757d;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .info-value {
        font-weight: 600;
        font-size: 1rem;
    }
    
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .quick-actions .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .quick-actions .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    /* Melhorias na tabela */
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        border-radius: 12px 12px 0 0;
        padding: 20px;
    }
    
    .card-title {
        color: #495057;
        font-weight: 600;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .stats-card {
            margin-bottom: 15px;
            padding: 20px;
        }
        
        .stats-card-icon {
            font-size: 2.5rem;
            margin-right: 15px;
        }
        
        .stats-card-number {
            font-size: 2rem;
        }
        
        .info-card {
            margin-bottom: 15px;
        }
    }
    
    /* Ícone do departamento */
    .department-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* Melhorias nos badges */
    .badge {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
    }
    
    /* Melhorias nos botões */
    .btn-group .btn {
        border-radius: 6px;
        margin: 0 1px;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    /* Tooltips personalizados */
    .tooltip {
        font-size: 0.85rem;
    }
    
    .tooltip-inner {
        background-color: #495057;
        border-radius: 6px;
        padding: 8px 12px;
    }
    
    /* Animações suaves */
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0,123,255,0.05);
        transform: scale(1.01);
    }
    
    /* Melhorias no header da tabela */
    .header-actions .btn {
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .header-actions .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
</style>
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
                        <i class="fas fa-building me-2"></i>
                        Gerenciar Departamentos
                    </h1>
                </div>
                
                <div class="topbar-right">
                    <a href="<?= URL ?>/departamentos/criar" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-plus me-1"></i>
                        Novo Departamento
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
                <!-- Estatísticas Rápidas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas['total_departamentos'] ?? 0 ?></h3>
                                    <p class="stats-card-label">Total de Departamentos</p>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Todos os departamentos do sistema
                                    </small>
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
                                    <h3 class="stats-card-number"><?= $estatisticas['departamentos_ativos'] ?? 0 ?></h3>
                                    <p class="stats-card-label">Departamentos Ativos</p>
                                    <small class="text-muted">
                                        <i class="fas fa-play-circle me-1"></i>
                                        Em operação
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-warning">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas['credenciais']['total'] ?? 0 ?></h3>
                                    <p class="stats-card-label">Credenciais Serpro</p>
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Configuradas
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card stats-card-info">
                            <div class="stats-card-body">
                                <div class="stats-card-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-card-content">
                                    <h3 class="stats-card-number"><?= $estatisticas['total_atendentes'] ?? 0 ?></h3>
                                    <p class="stats-card-label">Atendentes</p>
                                    <small class="text-muted">
                                        <i class="fas fa-user-check me-1"></i>
                                        Disponíveis
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards Adicionais de Informação -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="fas fa-chart-line"></i>
                                <h5>Status Geral</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Departamentos Inativos:</span>
                                    <span class="info-value text-danger">
                                        <?= ($estatisticas['total_departamentos'] ?? 0) - ($estatisticas['departamentos_ativos'] ?? 0) ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Credenciais Ativas:</span>
                                    <span class="info-value text-success">
                                        <?= $estatisticas['credenciais']['ativas'] ?? 0 ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Credenciais Inativas:</span>
                                    <span class="info-value text-warning">
                                        <?= $estatisticas['credenciais']['inativas'] ?? 0 ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="fas fa-cog"></i>
                                <h5>Configurações</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Departamentos com Credenciais:</span>
                                    <span class="info-value text-info">
                                        <?= $estatisticas['credenciais']['departamentos_com_credenciais'] ?? 0 ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Departamentos sem Credenciais:</span>
                                    <span class="info-value text-warning">
                                        <?= ($estatisticas['total_departamentos'] ?? 0) - ($estatisticas['credenciais']['departamentos_com_credenciais'] ?? 0) ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Taxa de Cobertura:</span>
                                    <span class="info-value text-primary">
                                        <?php 
                                        $total = $estatisticas['total_departamentos'] ?? 0;
                                        $comCredenciais = $estatisticas['credenciais']['departamentos_com_credenciais'] ?? 0;
                                        $taxa = $total > 0 ? round(($comCredenciais / $total) * 100, 1) : 0;
                                        echo $taxa . '%';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="fas fa-tasks"></i>
                                <h5>Ações Rápidas</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="quick-actions">
                                    <a href="<?= URL ?>/departamentos/criar" class="btn btn-primary btn-sm mb-2 w-100">
                                        <i class="fas fa-plus me-2"></i>
                                        Novo Departamento
                                    </a>
                                    <a href="<?= URL ?>/departamentos/credenciais/1" class="btn btn-info btn-sm mb-2 w-100">
                                        <i class="fas fa-key me-2"></i>
                                        Gerenciar Credenciais
                                    </a>
                                    <button class="btn btn-success btn-sm mb-2 w-100" onclick="exportarEstatisticas()">
                                        <i class="fas fa-download me-2"></i>
                                        Exportar Dados
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Departamentos -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>
                                Lista de Departamentos
                            </h5>
                            <div class="header-actions">
                                <button class="btn btn-outline-secondary btn-sm me-2" onclick="refreshTable()" title="Atualizar tabela">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="exportarTabela()" title="Exportar tabela">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table">
                            <table id="tabelaDepartamentos" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th>Credenciais Serpro</th>
                                        <th>Atendentes</th>
                                        <th>Conversas</th>
                                        <th>Criado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($departamentos) && !empty($departamentos)): ?>
                                        <?php foreach ($departamentos as $departamento): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">#<?= $departamento->id ?></span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="department-icon me-2" style="background-color: <?= $departamento->cor ?? '#007bff' ?>">
                                                            <i class="<?= $departamento->icone ?? 'fas fa-building' ?>"></i>
                                                        </div>
                                                        <div>
                                                            <strong><?= htmlspecialchars($departamento->nome) ?></strong>
                                                            <?php if ($departamento->prioridade > 0): ?>
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-star text-warning"></i>
                                                                    Prioridade: <?= $departamento->prioridade ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($departamento->descricao)): ?>
                                                        <span data-toggle="tooltip" title="<?= htmlspecialchars($departamento->descricao) ?>">
                                                            <?= htmlspecialchars(substr($departamento->descricao, 0, 30)) ?>
                                                            <?= strlen($departamento->descricao) > 30 ? '...' : '' ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sem descrição</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($departamento->status === 'ativo'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Ativo
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-times-circle me-1"></i>
                                                            Inativo
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $credenciaisCount = $departamento->credenciais_count ?? 0;
                                                    if ($credenciaisCount > 0): ?>
                                                        <span class="badge bg-info" data-toggle="tooltip" title="<?= $credenciaisCount ?> credencial(is) configurada(s)">
                                                            <i class="fas fa-key me-1"></i>
                                                            <?= $credenciaisCount ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning" data-toggle="tooltip" title="Nenhuma credencial configurada">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            Sem credenciais
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $atendentesCount = $departamento->atendentes_count ?? 0;
                                                    if ($atendentesCount > 0): ?>
                                                        <span class="badge bg-primary" data-toggle="tooltip" title="<?= $atendentesCount ?> atendente(s) ativo(s)">
                                                            <i class="fas fa-users me-1"></i>
                                                            <?= $atendentesCount ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary" data-toggle="tooltip" title="Nenhum atendente associado">
                                                            <i class="fas fa-user-slash me-1"></i>
                                                            Sem atendentes
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $conversasCount = $departamento->conversas_count ?? 0;
                                                    if ($conversasCount > 0): ?>
                                                        <span class="badge bg-success" data-toggle="tooltip" title="<?= $conversasCount ?> conversa(s) ativa(s)">
                                                            <i class="fas fa-comments me-1"></i>
                                                            <?= $conversasCount ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark" data-toggle="tooltip" title="Nenhuma conversa ativa">
                                                            <i class="fas fa-comment-slash me-1"></i>
                                                            Sem conversas
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span data-toggle="tooltip" title="<?= date('d/m/Y H:i:s', strtotime($departamento->criado_em)) ?>">
                                                        <?= date('d/m/Y H:i', strtotime($departamento->criado_em)) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="visualizarDepartamento(<?= $departamento->id ?>)"
                                                                title="Visualizar detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="<?= URL ?>/departamentos/editar/<?= $departamento->id ?>" 
                                                           class="btn btn-sm btn-outline-warning"
                                                           title="Editar departamento">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= URL ?>/atendentes-departamento/<?= $departamento->id ?>" 
                                                           class="btn btn-sm btn-outline-secondary"
                                                           title="Gerenciar atendentes">
                                                            <i class="fas fa-users"></i>
                                                        </a>
                                                        <a href="<?= URL ?>/departamentos/credenciais/<?= $departamento->id ?>" 
                                                           class="btn btn-sm btn-outline-info"
                                                           title="Gerenciar credenciais Serpro">
                                                            <i class="fas fa-key"></i>
                                                        </a>
                                                        <a href="<?= URL ?>/departamentos/estatisticas/<?= $departamento->id ?>" 
                                                           class="btn btn-sm btn-outline-success"
                                                           title="Ver estatísticas">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="excluirDepartamento(<?= $departamento->id ?>, '<?= htmlspecialchars($departamento->nome) ?>')"
                                                                title="Excluir departamento">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Nenhum departamento encontrado
                                                <br>
                                                <small>Clique em "Novo Departamento" para criar o primeiro</small>
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

    <!-- Modal para Visualizar Departamento -->
    <div class="modal fade" id="modalVisualizarDepartamento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Departamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalVisualizarDepartamentoBody">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'app/Views/include/linkjs.php' ?>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#tabelaDepartamentos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true
            });
        });

        function visualizarDepartamento(departamentoId) {
            // Carregar detalhes do departamento via AJAX
            $.ajax({
                url: '<?= URL ?>/departamentos/api',
                method: 'POST',
                data: {
                    action: 'visualizar',
                    departamento_id: departamentoId
                },
                success: function(response) {
                    if (response.success) {
                        $('#modalVisualizarDepartamentoBody').html(response.html);
                        $('#modalVisualizarDepartamento').modal('show');
                    } else {
                        Swal.fire('Erro!', response.message || 'Erro ao carregar detalhes do departamento', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                }
            });
        }

        function excluirDepartamento(departamentoId, nomeDepartamento) {
            Swal.fire({
                title: 'Confirmar Exclusão',
                html: `Tem certeza que deseja excluir o departamento <strong>${nomeDepartamento}</strong>?<br><br>
                       <small class="text-warning">Esta ação não pode ser desfeita!</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= URL ?>/departamentos/api',
                        method: 'POST',
                        data: {
                            action: 'excluir',
                            departamento_id: departamentoId
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Sucesso!', 'Departamento excluído com sucesso!', 'success')
                                .then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erro!', response.message || 'Erro ao excluir departamento', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                        }
                    });
                }
            });
        }

        function alterarStatusDepartamento(departamentoId, novoStatus) {
            $.ajax({
                url: '<?= URL ?>/departamentos/api',
                method: 'POST',
                data: {
                    action: 'alterar_status',
                    departamento_id: departamentoId,
                    novo_status: novoStatus
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Sucesso!', 'Status do departamento alterado com sucesso!', 'success')
                        .then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erro!', response.message || 'Erro ao alterar status', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                }
            });
        }

        function exportarEstatisticas() {
            Swal.fire({
                title: 'Exportar Estatísticas',
                text: 'Escolha o formato de exportação:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'PDF',
                denyButtonText: 'Excel',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                denyButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Exportar PDF
                    exportarPDF();
                } else if (result.isDenied) {
                    // Exportar Excel
                    exportarExcel();
                }
            });
        }

        function exportarPDF() {
            Swal.fire({
                title: 'Gerando PDF...',
                text: 'Aguarde enquanto preparamos o relatório',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simular geração de PDF (implementar conforme necessário)
            setTimeout(() => {
                Swal.fire('Sucesso!', 'PDF gerado com sucesso!', 'success');
            }, 2000);
        }

        function exportarExcel() {
            Swal.fire({
                title: 'Gerando Excel...',
                text: 'Aguarde enquanto preparamos o relatório',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simular geração de Excel (implementar conforme necessário)
            setTimeout(() => {
                Swal.fire('Sucesso!', 'Excel gerado com sucesso!', 'success');
            }, 2000);
        }

        function refreshTable() {
            $('#tabelaDepartamentos').DataTable().ajax.reload();
        }

        function exportarTabela() {
            Swal.fire({
                title: 'Exportar Tabela',
                text: 'Escolha o formato de exportação para a tabela:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'PDF',
                denyButtonText: 'Excel',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                denyButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Exportar PDF
                    exportarTabelaPDF();
                } else if (result.isDenied) {
                    // Exportar Excel
                    exportarTabelaExcel();
                }
            });
        }

        function exportarTabelaPDF() {
            Swal.fire({
                title: 'Gerando PDF...',
                text: 'Aguarde enquanto preparamos o relatório',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simular geração de PDF (implementar conforme necessário)
            setTimeout(() => {
                Swal.fire('Sucesso!', 'PDF gerado com sucesso!', 'success');
            }, 2000);
        }

        function exportarTabelaExcel() {
            Swal.fire({
                title: 'Gerando Excel...',
                text: 'Aguarde enquanto preparamos o relatório',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simular geração de Excel (implementar conforme necessário)
            setTimeout(() => {
                Swal.fire('Sucesso!', 'Excel gerado com sucesso!', 'success');
            }, 2000);
        }

        // Adicionar animações aos cards
        $(document).ready(function() {
            // Animar cards ao carregar a página
            $('.stats-card').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                }).delay(index * 100).animate({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                }, 600);
            });

            // Animar cards de informação
            $('.info-card').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                }).delay((index + 4) * 100).animate({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                }, 600);
            });

            // Tooltips para informações adicionais
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html> 