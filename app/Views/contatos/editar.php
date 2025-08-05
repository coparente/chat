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
                    <h1 class="topbar-title">Editar Contato</h1>
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
                                <h2><i class="fas fa-edit me-2"></i>Editar Contato</h2>
                                <p class="text-muted">Atualize as informações do contato</p>
                            </div>
                            <div class="btn-group">
                                <a href="<?= URL ?>/contatos/perfil/<?= $id ?>" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>Ver Perfil
                                </a>
                                <a href="<?= URL ?>/contatos" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </a>
                            </div>
                        </div>

                        <!-- Formulário de edição -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-user-edit me-2"></i>Informações do Contato
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <form method="POST" action="<?= URL ?>/contatos/editar/<?= $id ?>" id="formEditar">
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
                                            <i class="fas fa-save me-2"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar de informações -->
                    <div class="col-lg-4">
                        <!-- Informações do contato -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-info-circle me-2"></i>Informações
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="text-center mb-3">
                                    <div class="user-avatar mx-auto" style="width: 60px; height: 60px; font-size: 1.5rem; background: var(--info-color);">
                                        <?= strtoupper(substr($nome, 0, 2)) ?>
                                    </div>
                                    <h6 class="mt-2 mb-0"><?= htmlspecialchars($nome) ?></h6>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small text-muted">ID do Contato</label>
                                    <div class="text-monospace">#<?= $id ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small text-muted">WhatsApp</label>
                                    <div>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $telefone) ?>" 
                                           target="_blank" class="btn btn-success btn-sm">
                                            <i class="fab fa-whatsapp me-1"></i>Abrir Conversa
                                        </a>
                                    </div>
                                </div>
                                
                                <?php if ($email): ?>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">E-mail</label>
                                    <div>
                                        <a href="mailto:<?= htmlspecialchars($email) ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-envelope me-1"></i>Enviar E-mail
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Ações rápidas -->
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-bolt me-2"></i>Ações Rápidas
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="d-grid gap-2">
                                    <a href="<?= URL ?>/contatos/perfil/<?= $id ?>" class="btn btn-outline-info">
                                        <i class="fas fa-eye me-2"></i>Ver Perfil Completo
                                    </a>
                                    
                                    <button type="button" class="btn btn-outline-warning" onclick="bloquearContato(<?= $id ?>)">
                                        <i class="fas fa-ban me-2"></i>Bloquear Contato
                                    </button>
                                    
                                    <?php if (in_array($usuario_logado['perfil'], ['admin', 'supervisor'])): ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmarExclusao(<?= $id ?>, '<?= htmlspecialchars($nome) ?>')">
                                        <i class="fas fa-trash me-2"></i>Excluir Contato
                                    </button>
                                    <?php endif; ?>
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
            $('#formEditar').on('submit', function(e) {
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
        
        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            document.getElementById('nomeContato').textContent = nome;
            document.getElementById('btnConfirmarExclusao').href = '<?= URL ?>/contatos/excluir/' + id;
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        }
        
        // Função para bloquear contato
        function bloquearContato(id) {
            if (confirm('Tem certeza que deseja bloquear este contato?')) {
                fetch(`<?= URL ?>/contatos/bloquear/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Contato bloqueado com sucesso!', 'success');
                        setTimeout(() => {
                            window.location.href = '<?= URL ?>/contatos';
                        }, 1500);
                    } else {
                        showToast('Erro: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showToast('Erro de conexão', 'error');
                });
            }
        }
        
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