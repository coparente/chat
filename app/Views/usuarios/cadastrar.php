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
                    <h1 class="topbar-title">Cadastrar Usuário</h1>
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
                        <li class="breadcrumb-item active">Cadastrar</li>
                    </ol>
                </nav>

                <!-- Header da página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-user-plus me-2"></i>Cadastrar Novo Usuário</h2>
                        <p class="text-muted">Adicione um novo usuário ao sistema ChatSerpro</p>
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
                            </div>
                            <div class="content-card-body">
                                <form method="POST" action="<?= URL ?>/usuarios/cadastrar">
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
                                        <!-- Senha -->
                                        <div class="col-md-6 mb-3">
                                            <label for="senha" class="form-label">
                                                <i class="fas fa-lock me-1"></i>Senha
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       class="form-control <?= !empty($senha_erro) ? 'is-invalid' : '' ?>" 
                                                       id="senha" 
                                                       name="senha" 
                                                       placeholder="Mínimo 6 caracteres"
                                                       required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('senha')">
                                                    <i class="fas fa-eye" id="senhaIcon"></i>
                                                </button>
                                                <?php if (!empty($senha_erro)): ?>
                                                    <div class="invalid-feedback">
                                                        <i class="fas fa-exclamation-circle me-1"></i><?= $senha_erro ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Confirmar Senha -->
                                        <div class="col-md-6 mb-3">
                                            <label for="confirma_senha" class="form-label">
                                                <i class="fas fa-lock me-1"></i>Confirmar Senha
                                            </label>
                                            <div class="input-group">
                                                <input type="password" 
                                                       class="form-control <?= !empty($confirma_senha_erro) ? 'is-invalid' : '' ?>" 
                                                       id="confirma_senha" 
                                                       name="confirma_senha" 
                                                       placeholder="Confirme a senha"
                                                       required>
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
                                            <select class="form-select" id="perfil" name="perfil" required>
                                                <option value="atendente" <?= $perfil === 'atendente' ? 'selected' : '' ?>>Atendente</option>
                                                <option value="supervisor" <?= $perfil === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                                                <option value="admin" <?= $perfil === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                            </select>
                                        </div>

                                        <!-- Status -->
                                        <div class="col-md-4 mb-3">
                                            <label for="status" class="form-label">
                                                <i class="fas fa-toggle-on me-1"></i>Status
                                            </label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                                <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
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
                                            <div class="form-text">Número máximo de conversas simultâneas</div>
                                        </div>
                                    </div>

                                    <!-- Botões -->
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= URL ?>/usuarios" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Cadastrar Usuário
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Informações adicionais -->
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-info-circle me-2"></i>Informações
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <h6><i class="fas fa-users me-2"></i>Perfis de Usuário</h6>
                                <ul class="list-unstyled mb-4">
                                    <li class="mb-2">
                                        <span class="badge bg-danger me-2">Admin</span>
                                        Acesso total ao sistema, pode gerenciar usuários e configurações
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-warning me-2">Supervisor</span>
                                        Pode gerenciar atendentes e visualizar relatórios
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-info me-2">Atendente</span>
                                        Atende clientes via chat e gerencia contatos
                                    </li>
                                </ul>

                                <h6><i class="fas fa-toggle-on me-2"></i>Status do Usuário</h6>
                                <ul class="list-unstyled mb-4">
                                    <li class="mb-1"><strong>Ativo:</strong> Pode fazer login no sistema</li>
                                    <li class="mb-1"><strong>Inativo:</strong> Login bloqueado</li>
                                </ul>

                                <h6><i class="fas fa-comments me-2"></i>Configurações de Chat</h6>
                                <p class="text-muted small mb-0">
                                    O número máximo de chats define quantas conversas simultâneas 
                                    um atendente pode gerenciar.
                                </p>
                            </div>
                        </div>

                        <!-- Dicas de segurança -->
                        <div class="content-card mt-3">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-shield-alt me-2"></i>Dicas de Segurança
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Use senhas com pelo menos 6 caracteres
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Combine letras, números e símbolos
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Evite dados pessoais na senha
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Oriente o usuário a trocar a senha no primeiro acesso
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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

        // Ajustar max_chats baseado no perfil
        document.getElementById('perfil').addEventListener('change', function() {
            const maxChatsField = document.getElementById('max_chats');
            
            switch(this.value) {
                case 'admin':
                case 'supervisor':
                    maxChatsField.value = 10;
                    break;
                case 'atendente':
                    maxChatsField.value = 5;
                    break;
            }
        });
    </script>
</body>
</html>