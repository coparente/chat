
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
                    <h1 class="topbar-title">Gerenciar Usuários</h1>
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
                <?= Helper::mensagem('usuario') ?>

                <!-- Header da página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-users me-2"></i>Usuários do Sistema</h2>
                        <p class="text-muted">Gerencie os usuários do ChatSerpro</p>
                    </div>
                    
                    <?php if ($usuario_logado['perfil'] === 'admin' || $usuario_logado['perfil'] === 'supervisor'): ?>
                    <div>
                        <a href="<?= URL ?>/usuarios/cadastrar" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Novo Usuário
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Filtros -->
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h5 class="content-card-title">
                            <i class="fas fa-filter me-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="content-card-body">
                        <form method="GET" action="<?= URL ?>/usuarios/listar" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">Todos os Status</option>
                                    <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                    <option value="ausente" <?= $status === 'ausente' ? 'selected' : '' ?>>Ausente</option>
                                    <option value="ocupado" <?= $status === 'ocupado' ? 'selected' : '' ?>>Ocupado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Perfil</label>
                                <select class="form-select" name="perfil" onchange="this.form.submit()">
                                    <option value="">Todos os Perfis</option>
                                    <option value="admin" <?= $perfil === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                    <option value="supervisor" <?= $perfil === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                                    <option value="atendente" <?= $perfil === 'atendente' ? 'selected' : '' ?>>Atendente</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Buscar</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="filtro" 
                                           placeholder="Nome ou e-mail..." value="<?= htmlspecialchars($filtro) ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if ($filtro || $status || $perfil): ?>
                                    <a href="<?= URL ?>/usuarios/listar" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de usuários -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h5 class="content-card-title">
                            <i class="fas fa-list me-2"></i>Lista de Usuários
                        </h5>
                        <span class="badge bg-primary"><?= $total_usuarios ?> usuários</span>
                    </div>
                    <div class="content-card-body p-0">
                        <?php if (empty($usuarios)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">Nenhum usuário encontrado</h5>
                                <p class="text-muted">Tente ajustar os filtros ou cadastre um novo usuário</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 60px;">Avatar</th>
                                            <th>Nome</th>
                                            <th>E-mail</th>
                                            <th style="width: 120px;">Perfil</th>
                                            <th style="width: 120px;">Status</th>
                                            <th style="width: 150px;">Último Acesso</th>
                                            <th style="width: 200px;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td>
                                                <div class="user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr($usuario->nome, 0, 2)) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($usuario->nome) ?></strong>
                                                    <?php if ($usuario->id == $usuario_logado['id']): ?>
                                                        <span class="badge bg-info ms-1">Você</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($usuario->email) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $usuario->perfil === 'admin' ? 'danger' : ($usuario->perfil === 'supervisor' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($usuario->perfil) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm status-select" 
                                                        data-user-id="<?= $usuario->id ?>"
                                                        <?= ($usuario_logado['perfil'] !== 'admin' && $usuario->perfil !== 'atendente') ? 'disabled' : '' ?>>
                                                    <option value="ativo" <?= $usuario->status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                                    <option value="inativo" <?= $usuario->status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                                    <option value="ausente" <?= $usuario->status === 'ausente' ? 'selected' : '' ?>>Ausente</option>
                                                    <option value="ocupado" <?= $usuario->status === 'ocupado' ? 'selected' : '' ?>>Ocupado</option>
                                                </select>
                                            </td>
                                            <td>
                                                <?php if ($usuario->ultimo_acesso): ?>
                                                    <small class="text-muted" title="<?= Helper::dataBr($usuario->ultimo_acesso) ?>">
                                                        <?= date('d/m H:i', strtotime($usuario->ultimo_acesso)) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">Nunca</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($usuario_logado['perfil'] === 'admin' || ($usuario_logado['perfil'] === 'supervisor' && $usuario->perfil === 'atendente')): ?>
                                                    <a href="<?= URL ?>/usuarios/editar/<?= $usuario->id ?>" 
                                                       class="btn btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($usuario_logado['perfil'] === 'admin' && $usuario->id != $usuario_logado['id']): ?>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="confirmarExclusao(<?= $usuario->id ?>, '<?= htmlspecialchars($usuario->nome) ?>')"
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
                                    <a class="page-link" href="<?= URL ?>/usuarios/listar/<?= $pagina_atual - 1 ?>?filtro=<?= urlencode($filtro) ?>&status=<?= urlencode($status) ?>&perfil=<?= urlencode($perfil) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagina_atual - 2); $i <= min($total_paginas, $pagina_atual + 2); $i++): ?>
                                <li class="page-item <?= $i == $pagina_atual ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= URL ?>/usuarios/listar/<?= $i ?>?filtro=<?= urlencode($filtro) ?>&status=<?= urlencode($status) ?>&perfil=<?= urlencode($perfil) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($pagina_atual < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= URL ?>/usuarios/listar/<?= $pagina_atual + 1 ?>?filtro=<?= urlencode($filtro) ?>&status=<?= urlencode($status) ?>&perfil=<?= urlencode($perfil) ?>">
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
                    <p>Tem certeza que deseja excluir o usuário <strong id="nomeUsuario"></strong>?</p>
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= Helper::asset('js/app.js') ?>"></script>
    <script src="<?= Helper::asset('js/dashboard.js') ?>"></script>
    
    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            document.getElementById('nomeUsuario').textContent = nome;
            document.getElementById('btnConfirmarExclusao').href = '<?= URL ?>/usuarios/excluir/' + id;
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        }

        // Alterar status via AJAX
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const userId = this.dataset.userId;
                const newStatus = this.value;
                const originalStatus = this.dataset.originalValue || this.value;
                
                fetch('<?= URL ?>/usuarios/alterar-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: userId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.dataset.originalValue = newStatus;
                        // Mostrar notificação de sucesso
                        showToast('Status atualizado com sucesso!', 'success');
                    } else {
                        // Reverter para status original
                        this.value = originalStatus;
                        showToast('Erro ao alterar status: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    // Reverter para status original
                    this.value = originalStatus;
                    showToast('Erro de conexão ao alterar status', 'error');
                });
            });
            
            // Guardar valor original
            select.dataset.originalValue = select.value;
        });

        // Função para mostrar toast de notificação
        function showToast(message, type = 'info') {
            // Criar elemento do toast
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            // Adicionar ao body
            document.body.appendChild(toast);

            // Remover automaticamente após 3 segundos
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
        }
    </script>
</body>
</html>