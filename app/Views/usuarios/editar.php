<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NOME ?> - Editar Usuário</title>
    <link rel="icon" href="<?= Helper::asset('img/logo.png') ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="<?= Helper::asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= Helper::asset('css/dashboard.css') ?>">
</head>
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
                    <a href="<?= URL ?>/contatos" class="nav-link">
                        <i class="fas fa-address-book"></i>
                        Contatos
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/relatorios" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Relatórios
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/usuarios" class="nav-link active">
                        <i class="fas fa-users"></i>
                        Usuários
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= URL ?>/configuracoes" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                </div>
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
                    <h1 class="topbar-title">Editar Usuário</h1>
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
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= URL ?>/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= URL ?>/usuarios">Usuários</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </nav>

                <!-- Header da página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-user-edit me-2"></i>Editar Usuário</h2>
                        <p class="text-muted">Altere as informações do usuário <?= htmlspecialchars($nome) ?></p>
                    </div>
                    
                    <div>
                        <a href="<?= URL ?>/usuarios" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Voltar
                        </a>
                    </div>
                </div>

                <!-- Formulário -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-user me-2"></i>Dados do Usuário
                                </h5>
                                <div class="user-avatar" style="width: 50px; height: 50px;">
                                    <?= strtoupper(substr($nome, 0, 2)) ?>
                                </div>
                            </div>
                            <div class="content-card-body">
                                <form method="POST" action="<?= URL ?>/usuarios/editar/<?= $id ?>">
                                    <div class="row">
                                        <!-- Nome -->
                                        <div class="col-md-6 mb-3">
                                            <label for="nome" class="form-label">
                                                <i class="fas fa-user me-1"></i>Nome Completo
                                            </label>
                                            <input type="text" 
                                                   class="form-control <?= !empty($nome_erro) ? 'is-invalid' : '' ?>" 
                                                   id="nome" 
                                                   name="nome" 
                                                   value="<?= htmlspecialchars($nome) ?>" 
                                                   placeholder="Digite o nome completo"
                                                   required>
                                            <?php if (!empty($nome_erro)): ?>
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-circle me-1"></i><?= $nome_erro ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- E-mail -->
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-1"></i>E-mail
                                            </label>
                                            <input type="email" 
                                                   class="form-control <?= !empty($email_erro) ? 'is-invalid' : '' ?>" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($email) ?>" 
                                                   placeholder="usuario@empresa.com"
                                                   required>
                                            <?php if (!empty($email_erro)): ?>
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-circle me-1"></i><?= $email_erro ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Nova Senha (opcional) -->
                                        <div class="col-md-6 mb-3">
                                            <label for="senha" class="form-label">
                                                <i class="fas fa-lock me-1"></i>Nova Senha (opcional)
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       class="form-control <?= !empty($senha_erro) ? 'is-invalid' : '' ?>" 
                                                       id="senha" 
                                                       name="senha" 
                                                       placeholder="Deixe em branco para manter a atual">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('senha')">
                                                    <i class="fas fa-eye" id="senhaIcon"></i>
                                                </button>
                                                <?php if (!empty($senha_erro)): ?>
                                                    <div class="invalid-feedback">
                                                        <i class="fas fa-exclamation-circle me-1"></i><?= $senha_erro ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-text">Deixe vazio para manter a senha atual</div>
                                        </div>

                                        <!-- Confirmar Nova Senha -->
                                        <div class="col-md-6 mb-3">
                                            <label for="confirma_senha" class="form-label">
                                                <i class="fas fa-lock me-1"></i>Confirmar Nova Senha
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       class="form-control <?= !empty($confirma_senha_erro) ? 'is-invalid' : '' ?>" 
                                                       id="confirma_senha" 
                                                       name="confirma_senha" 
                                                       placeholder="Confirme a nova senha">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirma_senha')">
                                                    <i class="fas fa-eye" id="confirma_senhaIcon"></i>
                                                </button>
                                                <?php if (!empty($confirma_senha_erro)): ?>
                                                    <div class="invalid-feedback">
                                                        <i class="fas fa-exclamation-circle me-1"></i><?= $confirma_senha_erro ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Perfil -->
                                        <div class="col-md-4 mb-3">
                                            <label for="perfil" class="form-label">
                                                <i class="fas fa-user-tag me-1"></i>Perfil
                                            </label>
                                            <select class="form-select" id="perfil" name="perfil" required 
                                                    <?= ($usuario_logado['perfil'] === 'supervisor') ? 'disabled' : '' ?>>
                                                <option value="atendente" <?= $perfil === 'atendente' ? 'selected' : '' ?>>Atendente</option>
                                                <option value="supervisor" <?= $perfil === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                                                <option value="admin" <?= $perfil === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                            </select>
                                            <?php if ($usuario_logado['perfil'] === 'supervisor'): ?>
                                                <input type="hidden" name="perfil" value="<?= $perfil ?>">
                                                <div class="form-text">Supervisores só podem editar atendentes</div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Status -->
                                        <div class="col-md-4 mb-3">
                                            <label for="status" class="form-label">
                                                <i class="fas fa-toggle-on me-1"></i>Status
                                            </label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                                <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                                <option value="ausente" <?= $status === 'ausente' ? 'selected' : '' ?>>Ausente</option>
                                                <option value="ocupado" <?= $status === 'ocupado' ? 'selected' : '' ?>>Ocupado</option>
                                            </select>
                                        </div>

                                        <!-- Max Chats -->
                                        <div class="col-md-4 mb-3">
                                            <label for="max_chats" class="form-label">
                                                <i class="fas fa-comments me-1"></i>Máximo de Chats
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="max_chats" 
                                                   name="max_chats" 
                                                   value="<?= $max_chats ?>" 
                                                   min="1" 
                                                   max="20"
                                                   placeholder="5">
                                            <div class="form-text">Conversas simultâneas permitidas</div>
                                        </div>
                                    </div>

                                    <!-- Botões -->
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= URL ?>/usuarios" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do usuário -->
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-info-circle me-2"></i>Informações do Usuário
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="user-avatar me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                        <?= strtoupper(substr($nome, 0, 2)) ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($nome) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($email) ?></small>
                                    </div>
                                </div>

                                <hr>

                                <h6><i class="fas fa-user-tag me-2"></i>Perfil Atual</h6>
                                <span class="badge bg-<?= $perfil === 'admin' ? 'danger' : ($perfil === 'supervisor' ? 'warning' : 'info') ?> mb-3">
                                    <?= ucfirst($perfil) ?>
                                </span>

                                <h6><i class="fas fa-toggle-on me-2"></i>Status Atual</h6>
                                <span class="badge bg-<?= $status === 'ativo' ? 'success' : 'secondary' ?> mb-3">
                                    <?= ucfirst($status) ?>
                                </span>

                                <h6><i class="fas fa-comments me-2"></i>Configuração Atual</h6>
                                <p class="text-muted small mb-0">
                                    Máximo de <strong><?= $max_chats ?> conversas</strong> simultâneas
                                </p>
                            </div>
                        </div>

                        <!-- Histórico -->
                        <div class="content-card mt-3">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-history me-2"></i>Histórico
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-user-plus text-primary me-2"></i>
                                        <small>Usuário criado</small>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-sign-in-alt text-success me-2"></i>
                                        <small>Último acesso: Hoje às 14:30</small>
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-edit text-warning me-2"></i>
                                        <small>Editando agora</small>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Ações adicionais (apenas admin) -->
                        <?php if ($usuario_logado['perfil'] === 'admin' && $id != $usuario_logado['id']): ?>
                        <div class="content-card mt-3">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-tools me-2"></i>Ações Avançadas
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="resetarSenha()">
                                        <i class="fas fa-key me-2"></i>Resetar Senha
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="confirmarExclusao(<?= $id ?>, '<?= htmlspecialchars($nome) ?>')">
                                        <i class="fas fa-trash me-2"></i>Excluir Usuário
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
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
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + 'Icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Validação em tempo real
        document.getElementById('confirma_senha').addEventListener('input', function() {
            const senha = document.getElementById('senha').value;
            const confirmaSenha = this.value;
            
            if (confirmaSenha && senha !== confirmaSenha) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Confirmar exclusão
        function confirmarExclusao(id, nome) {
            document.getElementById('nomeUsuario').textContent = nome;
            document.getElementById('btnConfirmarExclusao').href = '<?= URL ?>/usuarios/excluir/' + id;
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        }

        // Resetar senha
        function resetarSenha() {
            if (confirm('Deseja gerar uma nova senha temporária para este usuário?')) {
                // Implementar reset de senha
                alert('Funcionalidade em desenvolvimento');
            }
        }

        // Ajustar max_chats baseado no perfil
        document.getElementById('perfil').addEventListener('change', function() {
            const maxChatsField = document.getElementById('max_chats');
            
            switch(this.value) {
                case 'admin':
                case 'supervisor':
                    if (maxChatsField.value < 10) {
                        maxChatsField.value = 10;
                    }
                    break;
                case 'atendente':
                    if (maxChatsField.value > 10) {
                        maxChatsField.value = 5;
                    }
                    break;
            }
        });
    </script>
</body>
</html>
