<?php include 'app/Views/include/head.php' ?>

<?php
// Preparar dados do usuário para o menu dinâmico
$usuario = [
    'id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['usuario_nome'],
    'email' => $_SESSION['usuario_email'],
    'perfil' => $_SESSION['usuario_perfil'],
    'status' => $_SESSION['usuario_status']
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
                    <h1 class="topbar-title">Dashboard</h1>
                </div>

                <div class="topbar-right">
                    <!-- Toggle Dark Mode -->
                    <button class="btn btn-outline-secondary btn-sm me-2" id="toggleTheme" title="Alternar tema">
                        <i class="fas fa-moon"></i>
                    </button>

                    <!-- Status do usuário -->
                    <div class="status-badge status-<?= $usuario['status'] === 'ativo' ? 'online' : ($usuario['status'] === 'ausente' ? 'away' : 'busy') ?>">
                        <span class="status-indicator"></span>
                        <?= ucfirst($usuario['status']) ?>
                    </div>

                    <!-- Menu do usuário -->
                    <div class="user-menu">
                        <div class="user-avatar" title="<?= $usuario['nome'] ?>">
                            <?= strtoupper(substr($usuario['nome'], 0, 2)) ?>
                        </div>
                    </div>

                    <!-- Logout -->
                    <a href="<?= URL ?>/sair" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Boas-vindas -->
                <div class="mb-4">
                    <h2 class="text-muted">Olá, <?= $usuario['nome'] ?>! 👋</h2>
                    <p class="text-muted">Aqui está o resumo das suas atividades hoje.</p>
                </div>

                <!-- Estatísticas -->
                <div class="stats-grid">
                    <?php if ($tipo_dashboard === 'admin'): ?>
                        <!-- Admin Stats -->
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Total de Usuários</span>
                                <div class="stat-card-icon" style="background: var(--info-color)">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $usuarios->total ?? 0 ?></div>
                            <div class="stat-card-description">
                                <?= $usuarios->ativos ?? 0 ?> ativos, <?= $usuarios->ausentes ?? 0 ?> ausentes
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Conversas (30 dias)</span>
                                <div class="stat-card-icon" style="background: var(--success-color)">
                                    <i class="fas fa-comments"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $conversas->total ?? 0 ?></div>
                            <div class="stat-card-description">
                                <?= $conversas->abertas ?? 0 ?> abertas, <?= $conversas->fechadas ?? 0 ?> fechadas
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Mensagens Hoje</span>
                                <div class="stat-card-icon" style="background: var(--warning-color)">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $mensagens_hoje->total ?? 0 ?></div>
                            <div class="stat-card-description">
                                <?= $mensagens_hoje->recebidas ?? 0 ?> recebidas, <?= $mensagens_hoje->enviadas ?? 0 ?> enviadas
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Departamentos</span>
                                <div class="stat-card-icon" style="background: var(--primary-color)">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $departamentos->total ?? 0 ?></div>
                            <div class="stat-card-description">
                                <?= $departamentos->ativos ?? 0 ?> ativos, <?= $departamentos->com_atendentes ?? 0 ?> com atendentes
                            </div>
                        </div>

                    <?php elseif ($tipo_dashboard === 'supervisor'): ?>
                        <!-- Supervisor Stats -->
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Conversas Ativas</span>
                                <div class="stat-card-icon" style="background: var(--primary-color)">
                                    <i class="fas fa-comments"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= count($conversas_ativas ?? []) ?></div>
                            <div class="stat-card-description">Em andamento</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Atendentes Online</span>
                                <div class="stat-card-icon" style="background: var(--success-color)">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= count($atendentes_online ?? []) ?></div>
                            <div class="stat-card-description">Disponíveis</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Performance Hoje</span>
                                <div class="stat-card-icon" style="background: var(--warning-color)">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $estatisticas_hoje->conversas_atendidas ?? 0 ?></div>
                            <div class="stat-card-description">Conversas finalizadas</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Mensagens Hoje</span>
                                <div class="stat-card-icon" style="background: var(--info-color)">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $estatisticas_hoje->total_mensagens ?? 0 ?></div>
                            <div class="stat-card-description">Total processadas</div>
                        </div>

                    <?php elseif ($tipo_dashboard === 'atendente'): ?>
                        <!-- Atendente Stats -->
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Minhas Conversas</span>
                                <div class="stat-card-icon" style="background: var(--primary-color)">
                                    <i class="fas fa-comments"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= count($minhas_conversas ?? []) ?></div>
                            <div class="stat-card-description">Conversas ativas</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Mensagens Não Lidas</span>
                                <div class="stat-card-icon" style="background: var(--danger-color)">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $mensagens_nao_lidas ?? 0 ?></div>
                            <div class="stat-card-description">Aguardando resposta</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Atendimentos Hoje</span>
                                <div class="stat-card-icon" style="background: var(--success-color)">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= $estatisticas_hoje->conversas_atendidas ?? 0 ?></div>
                            <div class="stat-card-description">Conversas finalizadas</div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <span class="stat-card-title">Pendentes</span>
                                <div class="stat-card-icon" style="background: var(--warning-color)">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="stat-card-value"><?= count($conversas_pendentes ?? []) ?></div>
                            <div class="stat-card-description">Aguardando atendimento</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Conteúdo adicional -->
                <div class="content-grid">
                    <?php if ($tipo_dashboard === 'admin' && isset($atendentes_online)): ?>
                        <!-- Atendentes Online -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h3 class="content-card-title">Atendentes Online</h3>
                                <span class="badge bg-success"><?= count($atendentes_online) ?></span>
                            </div>
                            <div class="content-card-body">
                                <?php if (empty($atendentes_online)): ?>
                                    <p class="text-muted text-center">Nenhum atendente online</p>
                                <?php else: ?>
                                    <ul class="item-list">
                                        <?php foreach ($atendentes_online as $atendente): ?>
                                            <li style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0;">
                                                <div class="item-info" style="display: flex; align-items: center; flex: 1;">
                                                    <div class="item-avatar">
                                                        <?php 
                                                        // Criar iniciais únicas baseadas no nome
                                                        $nomes = explode(' ', trim($atendente->nome));
                                                        if (count($nomes) >= 2) {
                                                            $iniciais = strtoupper(substr($nomes[0], 0, 1) . substr($nomes[count($nomes)-1], 0, 1));
                                                        } else {
                                                            $iniciais = strtoupper(substr($atendente->nome, 0, 2));
                                                        }
                                                        echo $iniciais;
                                                        ?>
                                                    </div>
                                                    <div class="item-details">
                                                        <h6><?= $atendente->nome ?></h6>
                                                        <small><?= ucfirst($atendente->status) ?></small>
                                                    </div>
                                                </div>
                                                <?php 
                                                // Determinar status baseado no último acesso
                                                $ultimoAcesso = strtotime($atendente->ultimo_acesso);
                                                $agora = time();
                                                $diferencaMinutos = ($agora - $ultimoAcesso) / 60;
                                                
                                                $statusClass = 'offline';
                                                $statusText = 'Offline';
                                                $statusColor = '#808080';
                                                
                                                if ($diferencaMinutos <= 5) {
                                                    // Online (últimos 5 minutos)
                                                    $statusClass = 'online';
                                                    $statusText = 'Online';
                                                    $statusColor = '#00ff00';
                                                } elseif ($diferencaMinutos <= 15) {
                                                    // Ausente (últimos 15 minutos)
                                                    $statusClass = 'away';
                                                    $statusText = 'Ausente';
                                                    $statusColor = '#ffff00';
                                                } else {
                                                    // Offline (mais de 15 minutos)
                                                    $statusClass = 'offline';
                                                    $statusText = 'Offline';
                                                    $statusColor = '#808080';
                                                }
                                                ?>
                                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                                                    <span class="status-indicator status-<?= $statusClass ?>" 
                                                          style="background-color: <?= $statusColor ?> !important; 
                                                                 background: <?= $statusColor ?> !important; 
                                                                 border-color: <?= $statusColor ?> !important;"></span>
                                                    <small style="color: #6c757d; font-size: 0.75rem; font-weight: 500;"><?= $statusText ?></small>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($tipo_dashboard === 'supervisor' && isset($atendentes_online)): ?>
                        <!-- Atendentes Online (Supervisor) -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h3 class="content-card-title">Atendentes Online</h3>
                                <span class="badge bg-success"><?= count($atendentes_online) ?></span>
                            </div>
                            <div class="content-card-body">
                                <?php if (empty($atendentes_online)): ?>
                                    <p class="text-muted text-center">Nenhum atendente online</p>
                                <?php else: ?>
                                    <ul class="item-list">
                                        <?php foreach ($atendentes_online as $atendente): ?>
                                            <li style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0;">
                                                <div class="item-info" style="display: flex; align-items: center; flex: 1;">
                                                    <div class="item-avatar">
                                                        <?php 
                                                        // Criar iniciais únicas baseadas no nome
                                                        $nomes = explode(' ', trim($atendente->nome));
                                                        if (count($nomes) >= 2) {
                                                            $iniciais = strtoupper(substr($nomes[0], 0, 1) . substr($nomes[count($nomes)-1], 0, 1));
                                                        } else {
                                                            $iniciais = strtoupper(substr($atendente->nome, 0, 2));
                                                        }
                                                        echo $iniciais;
                                                        ?>
                                                    </div>
                                                    <div class="item-details">
                                                        <h6><?= $atendente->nome ?></h6>
                                                        <small><?= ucfirst($atendente->status) ?></small>
                                                    </div>
                                                </div>
                                                <?php 
                                                // Determinar status baseado no último acesso
                                                $ultimoAcesso = strtotime($atendente->ultimo_acesso);
                                                $agora = time();
                                                $diferencaMinutos = ($agora - $ultimoAcesso) / 60;
                                                
                                                $statusClass = 'offline';
                                                $statusText = 'Offline';
                                                $statusColor = '#808080';
                                                
                                                if ($diferencaMinutos <= 5) {
                                                    // Online (últimos 5 minutos)
                                                    $statusClass = 'online';
                                                    $statusText = 'Online';
                                                    $statusColor = '#00ff00';
                                                } elseif ($diferencaMinutos <= 15) {
                                                    // Ausente (últimos 15 minutos)
                                                    $statusClass = 'away';
                                                    $statusText = 'Ausente';
                                                    $statusColor = '#ffff00';
                                                } else {
                                                    // Offline (mais de 15 minutos)
                                                    $statusClass = 'offline';
                                                    $statusText = 'Offline';
                                                    $statusColor = '#808080';
                                                }
                                                ?>
                                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                                                    <span class="status-indicator status-<?= $statusClass ?>" 
                                                          style="background-color: <?= $statusColor ?> !important; 
                                                                 background: <?= $statusColor ?> !important; 
                                                                 border-color: <?= $statusColor ?> !important;"></span>
                                                    <small style="color: #6c757d; font-size: 0.75rem; font-weight: 500;"><?= $statusText ?></small>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($tipo_dashboard === 'supervisor' && isset($conversas_ativas)): ?>
                        <!-- Conversas Ativas -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h3 class="content-card-title">Conversas Ativas</h3>
                                <a href="<?= URL ?>/chat" class="btn btn-sm btn-primary">Ver Todas</a>
                            </div>
                            <div class="content-card-body">
                                <?php if (empty($conversas_ativas)): ?>
                                    <p class="text-muted text-center">Nenhuma conversa ativa</p>
                                <?php else: ?>
                                    <ul class="item-list">
                                        <?php foreach (array_slice($conversas_ativas, 0, 5) as $conversa): ?>
                                            <li>
                                                <div class="item-info">
                                                    <div class="item-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="item-details">
                                                        <h6><?= $conversa->contato_nome ?: $conversa->numero ?></h6>
                                                        <small><?= $conversa->atendente_nome ?: 'Sem atendente' ?></small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-<?= $conversa->status === 'aberto' ? 'success' : 'warning' ?>"><?= ucfirst($conversa->status) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Conversas Fechadas -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h3 class="content-card-title">Conversas Finalizadas Hoje</h3>
                                <span class="badge bg-secondary"><?= $total_conversas_fechadas ?? 0 ?></span>
                            </div>
                            <div class="content-card-body">
                                <?php if (empty($conversas_fechadas)): ?>
                                    <p class="text-muted text-center">Nenhuma conversa finalizada hoje</p>
                                <?php else: ?>
                                    <ul class="item-list">
                                        <?php foreach (array_slice($conversas_fechadas, 0, 5) as $conversa): ?>
                                            <li>
                                                <div class="item-info">
                                                    <div class="item-avatar">
                                                        <i class="fas fa-user-check"></i>
                                                    </div>
                                                    <div class="item-details">
                                                        <h6><?= $conversa->contato_nome ?: $conversa->numero ?></h6>
                                                        <small><?= $conversa->atendente_nome ?: 'Sem atendente' ?> • <?= Helper::dataBr($conversa->atualizado_em) ?></small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-success">Finalizada</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($tipo_dashboard === 'atendente' && isset($minhas_conversas)): ?>
                        <!-- Minhas Conversas -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h3 class="content-card-title">Conversas Ativas</h3>
                                <a href="<?= URL ?>/chat" class="btn btn-sm btn-primary">Ver Todas</a>
                            </div>
                            <div class="content-card-body">
                                <?php if (empty($minhas_conversas)): ?>
                                    <p class="text-muted text-center">Nenhuma conversa ativa</p>
                                <?php else: ?>
                                    <ul class="item-list">
                                        <?php foreach (array_slice($minhas_conversas, 0, 5) as $conversa): ?>
                                            <li>
                                                <div class="item-info">
                                                    <div class="item-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="item-details">
                                                        <h6><?= $conversa->contato_nome ?: $conversa->numero ?></h6>
                                                        <small><?= Helper::dataBr($conversa->ultima_mensagem) ?></small>
                                                    </div>
                                                </div>
                                                <?php if ($conversa->mensagens_nao_lidas > 0): ?>
                                                    <span class="badge bg-danger"><?= $conversa->mensagens_nao_lidas ?></span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Conversas Pendentes -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h3 class="content-card-title">Aguardando Atendimento</h3>
                            </div>
                            <div class="content-card-body">
                                <?php if (empty($conversas_pendentes)): ?>
                                    <p class="text-muted text-center">Nenhuma conversa pendente</p>
                                <?php else: ?>
                                    <ul class="item-list">
                                        <?php foreach ($conversas_pendentes as $conversa): ?>
                                            <li>
                                                <div class="item-info">
                                                    <div class="item-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="item-details">
                                                        <h6><?= $conversa->contato_nome ?: $conversa->numero ?></h6>
                                                        <small><?= Helper::dataBr($conversa->criado_em) ?></small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm btn-outline-primary">Assumir</button>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $erro ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>

    <script>
        // Atualizar estatísticas em tempo real (opcional)
        function atualizarEstatisticas() {
            const baseUrl = window.location.origin + window.location.pathname.replace('/dashboard', '');

            fetch(baseUrl + '/dashboard/estatisticas')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar valores na tela
                        // console.log('Estatísticas atualizadas:', data.dados);
                    }
                })
                .catch(error => console.error('Erro ao atualizar estatísticas:', error));
        }

        // Atualizar a cada 30 segundos
        setInterval(atualizarEstatisticas, 30000);
    </script>
</body>