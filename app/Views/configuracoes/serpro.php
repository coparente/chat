<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NOME ?> - Configurações API Serpro</title>
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
                    <a href="<?= URL ?>/configuracoes" class="nav-link active">
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
                    <h1 class="topbar-title">Configurações API Serpro</h1>
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
                <?= Helper::mensagem('configuracao') ?>

                <!-- Header da página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-plug me-2"></i>Configurações API Serpro</h2>
                        <p class="text-muted">Configure as credenciais da API do WhatsApp Business do Serpro</p>
                    </div>
                    <div>
                        <a href="<?= URL ?>/configuracoes" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Voltar
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Formulário de Configurações -->
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-plug me-2"></i>
                                    Configurações da API Serpro
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <form id="formSerpro" method="POST" action="<?= URL ?>/configuracoes/serpro/salvar">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-key me-1"></i>
                                                Client ID *
                                            </label>
                                            <input type="text" class="form-control <?= $client_id_erro ? 'is-invalid' : '' ?>" 
                                                   id="client_id" name="client_id" 
                                                   value="<?= htmlspecialchars($client_id) ?>" 
                                                   placeholder="Seu Client ID da API Serpro" required>
                                            <?php if ($client_id_erro): ?>
                                                <div class="invalid-feedback">
                                                    <?= $client_id_erro ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-lock me-1"></i>
                                                Client Secret *
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control <?= $client_secret_erro ? 'is-invalid' : '' ?>" 
                                                       id="client_secret" name="client_secret" 
                                                       value="<?= htmlspecialchars($client_secret) ?>" 
                                                       placeholder="Seu Client Secret da API Serpro" required>
                                                <button class="btn btn-outline-secondary" type="button" id="toggleSecret">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($client_secret_erro): ?>
                                                    <div class="invalid-feedback">
                                                        <?= $client_secret_erro ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-globe me-1"></i>
                                            URL Base da API *
                                        </label>
                                        <input type="url" class="form-control <?= $base_url_erro ? 'is-invalid' : '' ?>" 
                                               id="base_url" name="base_url" 
                                               value="<?= htmlspecialchars($base_url) ?>" 
                                               placeholder="https://api.whatsapp.serpro.gov.br" required>
                                        <?php if ($base_url_erro): ?>
                                            <div class="invalid-feedback">
                                                <?= $base_url_erro ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-id-card me-1"></i>
                                                WABA ID *
                                            </label>
                                            <input type="text" class="form-control <?= $waba_id_erro ? 'is-invalid' : '' ?>" 
                                                   id="waba_id" name="waba_id" 
                                                   value="<?= htmlspecialchars($waba_id) ?>" 
                                                   placeholder="ID da conta WhatsApp Business" required>
                                            <?php if ($waba_id_erro): ?>
                                                <div class="invalid-feedback">
                                                    <?= $waba_id_erro ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-phone me-1"></i>
                                                Phone Number ID *
                                            </label>
                                            <input type="text" class="form-control <?= $phone_number_id_erro ? 'is-invalid' : '' ?>" 
                                                   id="phone_number_id" name="phone_number_id" 
                                                   value="<?= htmlspecialchars($phone_number_id) ?>" 
                                                   placeholder="ID do número de telefone" required>
                                            <?php if ($phone_number_id_erro): ?>
                                                <div class="invalid-feedback">
                                                    <?= $phone_number_id_erro ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Webhook Verify Token *
                                        </label>
                                        <input type="text" class="form-control <?= $webhook_verify_token_erro ? 'is-invalid' : '' ?>" 
                                               id="webhook_verify_token" name="webhook_verify_token" 
                                               value="<?= htmlspecialchars($webhook_verify_token) ?>" 
                                               placeholder="Token de verificação do webhook" required>
                                        <?php if ($webhook_verify_token_erro): ?>
                                            <div class="invalid-feedback">
                                                <?= $webhook_verify_token_erro ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (isset($erro)): ?>
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <?= $erro ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary" id="btnSalvar">
                                            <i class="fas fa-save me-2"></i>
                                            Salvar Configurações
                                        </button>
                                        
                                        <button type="button" class="btn btn-outline-success" id="btnTestar">
                                            <i class="fas fa-plug me-2"></i>
                                            Testar Conectividade
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações e Ajuda -->
                    <div class="col-lg-4">
                        <!-- Status do Token JWT -->
                        <div class="content-card mb-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-key me-2"></i>
                                    Status do Token JWT
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div id="tokenStatus" class="token-status">
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mt-2 mb-0">Verificando status...</p>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnRenovarToken">
                                        <i class="fas fa-refresh me-1"></i>
                                        Renovar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAtualizarStatus">
                                        <i class="fas fa-sync me-1"></i>
                                        Atualizar
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnLimparCache">
                                        <i class="fas fa-trash me-1"></i>
                                        Limpar
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informações da API
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-key text-primary"></i>
                                        Client ID
                                    </div>
                                    <div class="info-text">
                                        Identificador único da sua aplicação fornecido pelo Serpro.
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-lock text-warning"></i>
                                        Client Secret
                                    </div>
                                    <div class="info-text">
                                        Chave secreta da aplicação. <strong>Mantenha em segurança!</strong>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-globe text-info"></i>
                                        URL Base
                                    </div>
                                    <div class="info-text">
                                        Endereço base da API WhatsApp Business do Serpro.
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-id-card text-success"></i>
                                        WABA ID
                                    </div>
                                    <div class="info-text">
                                        Identificador da conta WhatsApp Business.
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-phone text-secondary"></i>
                                        Phone Number ID
                                    </div>
                                    <div class="info-text">
                                        Identificador do número de telefone WhatsApp.
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-shield-alt text-danger"></i>
                                        Webhook Token
                                    </div>
                                    <div class="info-text">
                                        Token para verificação de autenticidade dos webhooks.
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Importante:</strong> Os tokens JWT expiram em 10 minutos. O sistema renovará automaticamente quando necessário.
                                </div>
                            </div>
                        </div>
                        
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-question-circle me-2"></i>
                                    Precisa de Ajuda?
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <p class="text-muted">
                                    Para obter suas credenciais da API Serpro:
                                </p>
                                <ol class="help-list">
                                    <li>Acesse o portal do Serpro</li>
                                    <li>Solicite acesso à API WhatsApp Business</li>
                                    <li>Após aprovação, obtenha suas credenciais</li>
                                    <li>Configure aqui no sistema</li>
                                </ol>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Dica:</strong> Teste sempre a conectividade após salvar as configurações.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-3 mb-0">Testando conectividade com a API Serpro...</p>
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
        document.getElementById('toggleSecret').addEventListener('click', function() {
            const secretInput = document.getElementById('client_secret');
            const icon = this.querySelector('i');
            
            if (secretInput.type === 'password') {
                secretInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                secretInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Testar conectividade
        document.getElementById('btnTestar').addEventListener('click', function() {
            const dados = {
                client_id: document.getElementById('client_id').value,
                client_secret: document.getElementById('client_secret').value,
                base_url: document.getElementById('base_url').value,
                waba_id: document.getElementById('waba_id').value,
                phone_number_id: document.getElementById('phone_number_id').value,
                webhook_verify_token: document.getElementById('webhook_verify_token').value
            };
            
            // Validar campos obrigatórios
            const camposObrigatorios = ['client_id', 'client_secret', 'base_url', 'waba_id', 'phone_number_id'];
            let camposVazios = [];
            
            camposObrigatorios.forEach(campo => {
                if (!dados[campo]) {
                    camposVazios.push(campo);
                }
            });
            
            if (camposVazios.length > 0) {
                alert('Preencha todos os campos obrigatórios antes de testar a conectividade.');
                return;
            }
            
            // Mostrar loading
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();
            
            // Fazer requisição
            fetch('<?= URL ?>/configuracoes/serpro/testar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                loadingModal.hide();
                
                if (data.success) {
                    alert('✅ Conectividade testada com sucesso!\n\n' + data.message);
                    // Atualizar status do token após teste bem-sucedido
                    setTimeout(() => {
                        atualizarStatusToken();
                    }, 1000);
                } else {
                    alert('❌ Erro ao testar conectividade:\n\n' + data.message);
                }
            })
            .catch(error => {
                loadingModal.hide();
                alert('❌ Erro ao testar conectividade:\n\n' + error.message);
            });
        });
        
        // Gerenciamento de Token JWT
        
        // Atualizar status do token
        function atualizarStatusToken() {
            fetch('<?= URL ?>/configuracoes/token/status')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarStatusToken(data.dados);
                    } else {
                        mostrarErroToken('Erro ao verificar status do token');
                    }
                })
                .catch(error => {
                    mostrarErroToken('Erro de conexão: ' + error.message);
                });
        }
        
        // Mostrar status do token
        function mostrarStatusToken(dados) {
            const container = document.getElementById('tokenStatus');
            
            if (!dados.existe) {
                container.innerHTML = `
                    <div class="token-info">
                        <div class="token-badge badge bg-secondary">
                            <i class="fas fa-minus-circle me-1"></i>
                            Nenhum token
                        </div>
                        <p class="text-muted mt-2 mb-0">Nenhum token JWT encontrado no cache.</p>
                    </div>
                `;
                return;
            }
            
            const badgeClass = dados.valido ? 'bg-success' : 'bg-danger';
            const badgeIcon = dados.valido ? 'check-circle' : 'times-circle';
            const badgeText = dados.valido ? 'Válido' : 'Expirado';
            
            container.innerHTML = `
                <div class="token-info">
                    <div class="token-badge badge ${badgeClass}">
                        <i class="fas fa-${badgeIcon} me-1"></i>
                        ${badgeText}
                    </div>
                    <div class="token-details mt-2">
                        <div class="token-detail">
                            <small class="text-muted">Expira em:</small>
                            <div class="fw-bold">${dados.expira_em}</div>
                        </div>
                        <div class="token-detail mt-1">
                            <small class="text-muted">Tempo restante:</small>
                            <div class="fw-bold ${dados.valido ? 'text-success' : 'text-danger'}">
                                ${dados.tempo_restante_formatado}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Mostrar erro do token
        function mostrarErroToken(mensagem) {
            const container = document.getElementById('tokenStatus');
            container.innerHTML = `
                <div class="token-info">
                    <div class="token-badge badge bg-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Erro
                    </div>
                    <p class="text-muted mt-2 mb-0">${mensagem}</p>
                </div>
            `;
        }
        
        // Renovar token
        document.getElementById('btnRenovarToken').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            
            // Mostrar loading
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Renovando...';
            btn.disabled = true;
            
            fetch('<?= URL ?>/configuracoes/token/renovar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Token renovado com sucesso!\n\n' + data.message);
                    atualizarStatusToken();
                } else {
                    alert('❌ Erro ao renovar token:\n\n' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Erro ao renovar token:\n\n' + error.message);
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
        
        // Atualizar status manualmente
        document.getElementById('btnAtualizarStatus').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Atualizando...';
            btn.disabled = true;
            
            setTimeout(() => {
                atualizarStatusToken();
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 500);
        });
        
        // Limpar cache
        document.getElementById('btnLimparCache').addEventListener('click', function() {
            if (!confirm('Tem certeza que deseja limpar o cache do token?\n\nUm novo token será necessário na próxima requisição.')) {
                return;
            }
            
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Limpando...';
            btn.disabled = true;
            
            fetch('<?= URL ?>/configuracoes/token/limpar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Cache limpo com sucesso!');
                    atualizarStatusToken();
                } else {
                    alert('❌ Erro ao limpar cache:\n\n' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Erro ao limpar cache:\n\n' + error.message);
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
        
        // Atualizar status do token ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            atualizarStatusToken();
            
            // Atualizar automaticamente a cada 30 segundos
            setInterval(atualizarStatusToken, 30000);
        });
    </script>
    
    <style>
        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .info-text {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .help-list {
            font-size: 0.875rem;
            color: var(--text-muted);
            padding-left: 1rem;
        }
        
        .help-list li {
            margin-bottom: 0.5rem;
        }
        
        .input-group .btn {
            border-color: var(--border-color);
        }
        
        .content-card {
            border: 1px solid var(--border-color);
        }
        
        /* Estilos para o painel de Token JWT */
        .token-status {
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .token-info {
            text-align: center;
            width: 100%;
        }
        
        .token-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .token-details {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .token-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .token-detail small {
            color: var(--text-muted);
        }
        
        .token-detail .fw-bold {
            font-size: 0.875rem;
        }
        
        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Animações */
        .token-status {
            transition: all 0.3s ease;
        }
        
        .token-badge {
            transition: all 0.2s ease;
        }
        
        .token-badge:hover {
            transform: translateY(-1px);
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .token-details {
                padding: 0.75rem;
            }
            
            .token-detail {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            
            .d-flex.gap-2 {
                flex-wrap: wrap;
            }
            
            .btn-sm {
                font-size: 0.75rem;
                padding: 0.375rem 0.75rem;
            }
        }
    </style>
</body>
</html> 