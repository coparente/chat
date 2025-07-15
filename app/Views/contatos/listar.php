
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
                    <a href="<?= URL ?>/contatos" class="nav-link active">
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
                    <a href="<?= URL ?>/configuracoes" class="nav-link">
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
                    <h1 class="topbar-title">Contatos</h1>
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
                <?= Helper::mensagem('contato') ?>

                <!-- Header da página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-address-book me-2"></i>Contatos</h2>
                        <p class="text-muted">Gerencie todos os seus contatos do ChatSerpro</p>
                    </div>
                    
                    <div>
                        <a href="<?= URL ?>/contatos/cadastrar" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Novo Contato
                        </a>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="stats-grid mb-4">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Total de Contatos</span>
                            <div class="stat-card-icon" style="background: var(--primary-color);">
                                <i class="fas fa-address-book"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $estatisticas->total ?? 0 ?></div>
                        <div class="stat-card-description">Todos os contatos cadastrados</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Contatos Ativos</span>
                            <div class="stat-card-icon" style="background: var(--success-color);">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $estatisticas->ativos ?? 0 ?></div>
                        <div class="stat-card-description">Não bloqueados</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Contatos Hoje</span>
                            <div class="stat-card-icon" style="background: var(--info-color);">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $estatisticas->hoje ?? 0 ?></div>
                        <div class="stat-card-description">Contatos recebidos hoje</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Esta Semana</span>
                            <div class="stat-card-icon" style="background: var(--warning-color);">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?= $estatisticas->semana ?? 0 ?></div>
                        <div class="stat-card-description">Últimos 7 dias</div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h5 class="content-card-title">
                            <i class="fas fa-filter me-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="content-card-body">
                        <form method="GET" action="<?= URL ?>/contatos/listar" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="busca" 
                                           placeholder="Nome, telefone ou e-mail..." value="<?= htmlspecialchars($filtros['busca']) ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="bloqueado" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    <option value="0" <?= $filtros['bloqueado'] === '0' ? 'selected' : '' ?>>Ativos</option>
                                    <option value="1" <?= $filtros['bloqueado'] === '1' ? 'selected' : '' ?>>Bloqueados</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Tag</label>
                                <select class="form-select" name="tag" onchange="this.form.submit()">
                                    <option value="">Todas</option>
                                    <?php foreach ($tags as $tag): ?>
                                    <option value="<?= htmlspecialchars($tag->tag) ?>" <?= $filtros['tag'] === $tag->tag ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tag->tag) ?> (<?= $tag->total ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Período</label>
                                <select class="form-select" name="periodo" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    <option value="hoje" <?= $filtros['periodo'] === 'hoje' ? 'selected' : '' ?>>Hoje</option>
                                    <option value="semana" <?= $filtros['periodo'] === 'semana' ? 'selected' : '' ?>>Esta semana</option>
                                    <option value="mes" <?= $filtros['periodo'] === 'mes' ? 'selected' : '' ?>>Este mês</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <?php if (array_filter($filtros)): ?>
                                    <a href="<?= URL ?>/contatos/listar" class="btn btn-outline-secondary d-block">
                                        <i class="fas fa-times me-1"></i>Limpar
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de contatos -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h5 class="content-card-title">
                            <i class="fas fa-list me-2"></i>Lista de Contatos
                        </h5>
                        <span class="badge bg-primary"><?= $total_contatos ?> contatos</span>
                    </div>
                    <div class="content-card-body p-0">
                        <?php if (empty($contatos)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-address-book text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">Nenhum contato encontrado</h5>
                                <p class="text-muted">Tente ajustar os filtros ou cadastre um novo contato</p>
                                <a href="<?= URL ?>/contatos/cadastrar" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Cadastrar Primeiro Contato
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 60px;">Avatar</th>
                                            <th>Nome</th>
                                            <th>Telefone</th>
                                            <th>E-mail</th>
                                            <th>Tags</th>
                                            <th style="width: 100px;">Status</th>
                                            <th style="width: 120px;">Conversas</th>
                                            <th style="width: 150px;">Último Contato</th>
                                            <th style="width: 200px;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contatos as $contato): ?>
                                        <tr>
                                            <td>
                                                <div class="user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--info-color);">
                                                    <?= strtoupper(substr($contato->nome, 0, 2)) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($contato->nome) ?></strong>
                                                    <?php if ($contato->empresa): ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($contato->empresa) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-monospace"><?= htmlspecialchars($contato->telefone) ?></span>
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contato->telefone) ?>" 
                                                   target="_blank" class="btn btn-sm btn-success ms-2" title="Abrir no WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($contato->email): ?>
                                                    <a href="mailto:<?= htmlspecialchars($contato->email) ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($contato->email) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($contato->tags): ?>
                                                    <?php foreach (explode(', ', $contato->tags) as $tag): ?>
                                                        <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($tag) ?></span>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($contato->bloqueado): ?>
                                                    <span class="badge bg-danger">Bloqueado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $contato->total_conversas ?? 0 ?></span>
                                            </td>
                                            <td>
                                                <?php if ($contato->ultimo_contato): ?>
                                                    <small class="text-muted" title="<?= Helper::dataBr($contato->ultimo_contato) ?>">
                                                        <?= date('d/m H:i', strtotime($contato->ultimo_contato)) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">Nunca</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= URL ?>/contatos/perfil/<?= $contato->id ?>" 
                                                       class="btn btn-outline-info" title="Ver Perfil">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <a href="<?= URL ?>/contatos/editar/<?= $contato->id ?>" 
                                                       class="btn btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if ($contato->bloqueado): ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="alterarBloqueio(<?= $contato->id ?>, false)"
                                                            title="Desbloquear">
                                                        <i class="fas fa-unlock"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button" class="btn btn-outline-warning" 
                                                            onclick="alterarBloqueio(<?= $contato->id ?>, true)"
                                                            title="Bloquear">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (in_array($usuario_logado['perfil'], ['admin', 'supervisor'])): ?>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="confirmarExclusao(<?= $contato->id ?>, '<?= htmlspecialchars($contato->nome) ?>')"
                                                            title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Paginação -->
                    <?php if ($total_paginas > 1): ?>
                    <div class="content-card-header border-top">
                        <nav aria-label="Paginação">
                            <ul class="pagination pagination-sm mb-0 justify-content-center">
                                <?php if ($pagina_atual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= URL ?>/contatos/listar/<?= $pagina_atual - 1 ?>?<?= http_build_query($filtros) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagina_atual - 2); $i <= min($total_paginas, $pagina_atual + 2); $i++): ?>
                                <li class="page-item <?= $i == $pagina_atual ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= URL ?>/contatos/listar/<?= $i ?>?<?= http_build_query($filtros) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($pagina_atual < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= URL ?>/contatos/listar/<?= $pagina_atual + 1 ?>?<?= http_build_query($filtros) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o contato <strong id="nomeContato"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Esta ação não pode ser desfeita!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
 
    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            document.getElementById('nomeContato').textContent = nome;
            document.getElementById('btnConfirmarExclusao').href = '<?= URL ?>/contatos/excluir/' + id;
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        }

        // Função para bloquear/desbloquear contato
        function alterarBloqueio(id, bloquear) {
            const acao = bloquear ? 'bloquear' : 'desbloquear';
            
            fetch(`<?= URL ?>/contatos/${acao}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Recarregar página após 1 segundo
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast('Erro: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro de conexão', 'error');
            });
        }

        // Função para mostrar toast de notificação
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
        }
    </script>
</body>
</html> 