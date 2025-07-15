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
                    <h1 class="topbar-title">Cadastrar Contato</h1>
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
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Header da página -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2><i class="fas fa-plus me-2"></i>Cadastrar Contato</h2>
                                <p class="text-muted">Adicione um novo contato ao sistema</p>
                            </div>
                            <div>
                                <a href="<?= URL ?>/contatos" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </a>
                            </div>
                        </div>

                        <!-- Formulário de cadastro -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-user-plus me-2"></i>Informações do Contato
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <form method="POST" action="<?= URL ?>/contatos/cadastrar" id="formCadastro">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Nome *</label>
                                                <input type="text" class="form-control <?= $nome_erro ? 'is-invalid' : '' ?>" 
                                                       name="nome" id="nome" placeholder="Nome completo" 
                                                       value="<?= htmlspecialchars($nome) ?>" required>
                                                <?php if ($nome_erro): ?>
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i><?= $nome_erro ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Telefone *</label>
                                                <input type="tel" class="form-control <?= $telefone_erro ? 'is-invalid' : '' ?>" 
                                                       name="telefone" id="telefone" placeholder="(11) 99999-9999" 
                                                       value="<?= htmlspecialchars($telefone) ?>" required>
                                                <?php if ($telefone_erro): ?>
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i><?= $telefone_erro ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">E-mail</label>
                                                <input type="email" class="form-control <?= $email_erro ? 'is-invalid' : '' ?>" 
                                                       name="email" id="email" placeholder="email@exemplo.com" 
                                                       value="<?= htmlspecialchars($email) ?>">
                                                <?php if ($email_erro): ?>
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i><?= $email_erro ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Empresa</label>
                                                <input type="text" class="form-control" name="empresa" id="empresa" 
                                                       placeholder="Nome da empresa" value="<?= htmlspecialchars($empresa) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Tags</label>
                                        <input type="text" class="form-control" name="tags" id="tags" 
                                               placeholder="Separar tags com vírgula (ex: cliente, vip, suporte)" 
                                               value="<?= htmlspecialchars($tags) ?>">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Use tags para organizar seus contatos (ex: cliente, prospect, fornecedor)
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4">
                                        <label class="form-label">Observações</label>
                                        <textarea class="form-control" name="observacoes" id="observacoes" 
                                                  rows="4" placeholder="Observações sobre o contato..."><?= htmlspecialchars($observacoes) ?></textarea>
                                    </div>
                                    
                                    <?php if (isset($erro)): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i><?= $erro ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="<?= URL ?>/contatos" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Cadastrar Contato
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar de ajuda -->
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-lightbulb me-2"></i>Dicas
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-phone text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6>Formato do Telefone</h6>
                                        <p class="text-muted mb-0">
                                            Digite o telefone com DDD. O sistema aceita vários formatos:
                                            (11) 99999-9999, 11999999999, +5511999999999
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-tags text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6>Usando Tags</h6>
                                        <p class="text-muted mb-0">
                                            As tags ajudam a organizar seus contatos. 
                                            Use palavras-chave como: cliente, prospect, fornecedor, vip
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <i class="fab fa-whatsapp text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6>Integração WhatsApp</h6>
                                        <p class="text-muted mb-0">
                                            Após cadastrar, você pode iniciar uma conversa diretamente 
                                            do WhatsApp Web através da lista de contatos
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-shield-alt text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6>Dados Seguros</h6>
                                        <p class="text-muted mb-0">
                                            Todas as informações são criptografadas e armazenadas 
                                            com segurança no sistema
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tags mais usadas -->
                        <?php if (!empty($tags_disponiveis)): ?>
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-star me-2"></i>Tags Populares
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <p class="text-muted small mb-3">Clique para adicionar rapidamente:</p>
                                <div class="tag-cloud">
                                    <?php foreach (array_slice($tags_disponiveis, 0, 10) as $tag): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2 tag-btn" 
                                            data-tag="<?= htmlspecialchars($tag->tag) ?>">
                                        <?= htmlspecialchars($tag->tag) ?>
                                        <span class="badge bg-secondary ms-1"><?= $tag->total ?></span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <script>
        $(document).ready(function() {
            // Máscara para telefone
            $('#telefone').on('input', function() {
                let value = this.value.replace(/\D/g, '');
                
                if (value.length <= 10) {
                    // Telefone fixo: (11) 1234-5678
                    value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
                } else {
                    // Celular: (11) 99999-9999
                    value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
                }
                
                this.value = value;
            });
            
            // Validação em tempo real
            $('#nome').on('blur', function() {
                validateField(this, this.value.trim().length >= 2, 'Nome deve ter pelo menos 2 caracteres');
            });
            
            $('#telefone').on('blur', function() {
                const telefone = this.value.replace(/\D/g, '');
                validateField(this, telefone.length >= 10, 'Telefone deve ter pelo menos 10 dígitos');
            });
            
            $('#email').on('blur', function() {
                if (this.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    validateField(this, emailRegex.test(this.value), 'E-mail inválido');
                }
            });
            
            // Adicionar tags populares
            $('.tag-btn').on('click', function() {
                const tag = $(this).data('tag');
                const tagsInput = $('#tags');
                const currentTags = tagsInput.val().split(',').map(t => t.trim()).filter(t => t);
                
                if (!currentTags.includes(tag)) {
                    currentTags.push(tag);
                    tagsInput.val(currentTags.join(', '));
                }
            });
            
            // Validação do formulário
            $('#formCadastro').on('submit', function(e) {
                const nome = $('#nome').val().trim();
                const telefone = $('#telefone').val().replace(/\D/g, '');
                const email = $('#email').val().trim();
                
                let valid = true;
                
                if (nome.length < 2) {
                    validateField($('#nome')[0], false, 'Nome deve ter pelo menos 2 caracteres');
                    valid = false;
                }
                
                if (telefone.length < 10) {
                    validateField($('#telefone')[0], false, 'Telefone deve ter pelo menos 10 dígitos');
                    valid = false;
                }
                
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    validateField($('#email')[0], false, 'E-mail inválido');
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                    showToast('Por favor, corrija os erros no formulário', 'error');
                }
            });
        });
        
        function validateField(field, isValid, message) {
            const $field = $(field);
            const $feedback = $field.next('.invalid-feedback');
            
            if (isValid) {
                $field.removeClass('is-invalid').addClass('is-valid');
                $feedback.hide();
            } else {
                $field.removeClass('is-valid').addClass('is-invalid');
                if ($feedback.length === 0) {
                    $field.after(`<div class="invalid-feedback"><i class="fas fa-exclamation-triangle me-1"></i>${message}</div>`);
                } else {
                    $feedback.html(`<i class="fas fa-exclamation-triangle me-1"></i>${message}`).show();
                }
            }
        }
        
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