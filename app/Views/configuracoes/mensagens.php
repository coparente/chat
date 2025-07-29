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
                    <h1 class="topbar-title">Mensagens Automáticas</h1>
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
                        <h2><i class="fas fa-robot me-2"></i>Mensagens Automáticas</h2>
                        <p class="text-muted">Configure respostas automáticas e mensagens padrão do sistema</p>
                    </div>
                    <div>
                        <a href="<?= URL ?>/configuracoes" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Voltar
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Formulário de Mensagens -->
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-robot me-2"></i>
                                    Configurar Mensagens Automáticas
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <form method="POST" action="<?= URL ?>/configuracoes/mensagens/salvar">
                                    
                                    <!-- Mensagem de Boas-vindas -->
                                    <div class="mb-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="ativar_boas_vindas" 
                                                   name="ativar_boas_vindas" <?= $ativar_boas_vindas ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="ativar_boas_vindas">
                                                <i class="fas fa-handshake me-2"></i>
                                                <strong>Ativar Mensagem de Boas-vindas</strong>
                                            </label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mensagem de Boas-vindas</label>
                                            <textarea class="form-control" name="mensagem_boas_vindas" rows="3" 
                                                      placeholder="Digite a mensagem de boas-vindas..."><?= htmlspecialchars($mensagem_boas_vindas) ?></textarea>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Enviada automaticamente quando um novo contato inicia uma conversa.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mensagem de Ausência -->
                                    <div class="mb-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="ativar_ausencia" 
                                                   name="ativar_ausencia" <?= $ativar_ausencia ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="ativar_ausencia">
                                                <i class="fas fa-clock me-2"></i>
                                                <strong>Ativar Mensagem de Ausência</strong>
                                            </label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mensagem de Ausência</label>
                                            <textarea class="form-control" name="mensagem_ausencia" rows="3" 
                                                      placeholder="Digite a mensagem de ausência..."><?= htmlspecialchars($mensagem_ausencia) ?></textarea>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Enviada quando não há atendentes disponíveis.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mensagem de Encerramento -->
                                    <div class="mb-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="ativar_encerramento" 
                                                   name="ativar_encerramento" <?= $ativar_encerramento ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="ativar_encerramento">
                                                <i class="fas fa-handshake me-2"></i>
                                                <strong>Ativar Mensagem de Encerramento</strong>
                                            </label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mensagem de Encerramento</label>
                                            <textarea class="form-control" name="mensagem_encerramento" rows="3" 
                                                      placeholder="Digite a mensagem de encerramento..."><?= htmlspecialchars($mensagem_encerramento) ?></textarea>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Enviada quando o atendimento é finalizado.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Horário de Funcionamento -->
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-business-time me-2"></i>
                                            Horário de Funcionamento
                                        </label>
                                        <input type="text" class="form-control" name="horario_funcionamento" 
                                               value="<?= htmlspecialchars($horario_funcionamento) ?>" 
                                               placeholder="Ex: Segunda a Sexta: 08:00 às 18:00">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Formatos aceitos: "Segunda a Sexta: 08:00 às 18:00", "Segunda a Sexta das 08:00 às 18:00", "Segunda a Sexta: 08:00 às 18:00, Sábado: 09:00 às 12:00"
                                        </div>
                                    </div>

                                    <!-- Configurações Avançadas -->
                                    <div class="mb-4">
                                        <h6 class="form-label">
                                            <i class="fas fa-cogs me-2"></i>
                                            Configurações Avançadas
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="ativar_fora_horario" 
                                                           name="ativar_fora_horario" <?= ($ativar_fora_horario ?? true) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="ativar_fora_horario">
                                                        <i class="fas fa-clock text-warning me-1"></i>
                                                        Enviar mensagem fora do horário
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="ativar_sem_atendentes" 
                                                           name="ativar_sem_atendentes" <?= ($ativar_sem_atendentes ?? true) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="ativar_sem_atendentes">
                                                        <i class="fas fa-user-slash text-danger me-1"></i>
                                                        Enviar quando sem atendentes
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (isset($erro)): ?>
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <?= $erro ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            Salvar Configurações
                                        </button>
                                        
                                        <button type="button" class="btn btn-outline-info" id="btnPreview">
                                            <i class="fas fa-eye me-2"></i>
                                            Visualizar Mensagens
                                        </button>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Informações e Exemplos -->
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Exemplos de Mensagens
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="example-message">
                                    <div class="example-label">
                                        <i class="fas fa-handshake text-success"></i>
                                        Boas-vindas
                                    </div>
                                    <div class="example-text">
                                        "Olá! Seja bem-vindo(a) ao nosso atendimento. Em que posso ajudá-lo(a)?"
                                    </div>
                                </div>
                                
                                <div class="example-message">
                                    <div class="example-label">
                                        <i class="fas fa-clock text-warning"></i>
                                        Ausência
                                    </div>
                                    <div class="example-text">
                                        "No momento não há atendentes disponíveis. Deixe sua mensagem que retornaremos em breve."
                                    </div>
                                </div>
                                
                                <div class="example-message">
                                    <div class="example-label">
                                        <i class="fas fa-handshake text-info"></i>
                                        Encerramento
                                    </div>
                                    <div class="example-text">
                                        "Obrigado pelo contato! Se precisar de mais alguma coisa, estarei aqui para ajudar."
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h5 class="content-card-title">
                                    <i class="fas fa-cogs me-2"></i>
                                    Configurações Avançadas
                                </h5>
                            </div>
                            <div class="content-card-body">
                                <div class="config-item">
                                    <div class="config-label">
                                        <i class="fas fa-toggle-on text-success"></i>
                                        Ativação Individual
                                    </div>
                                    <div class="config-text">
                                        Cada tipo de mensagem pode ser ativado/desativado independentemente.
                                    </div>
                                </div>
                                
                                <div class="config-item">
                                    <div class="config-label">
                                        <i class="fas fa-clock text-primary"></i>
                                        Horário de Funcionamento
                                    </div>
                                    <div class="config-text">
                                        Define quando o atendimento está disponível para controle automático.
                                    </div>
                                </div>
                                
                                <div class="config-item">
                                    <div class="config-label">
                                        <i class="fas fa-user-robot text-info"></i>
                                        Automação
                                    </div>
                                    <div class="config-text">
                                        Mensagens são enviadas automaticamente baseadas em eventos do sistema.
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Dica:</strong> Use mensagens claras e amigáveis para melhorar a experiência do usuário.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>
                        Visualizar Mensagens
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="preview-container">
                        <div class="preview-message" id="previewBoasVindas">
                            <div class="preview-header">
                                <i class="fas fa-handshake text-success"></i>
                                Mensagem de Boas-vindas
                                <span class="preview-status" id="statusBoasVindas"></span>
                            </div>
                            <div class="preview-text" id="textBoasVindas"></div>
                        </div>
                        
                        <div class="preview-message" id="previewAusencia">
                            <div class="preview-header">
                                <i class="fas fa-clock text-warning"></i>
                                Mensagem de Ausência
                                <span class="preview-status" id="statusAusencia"></span>
                            </div>
                            <div class="preview-text" id="textAusencia"></div>
                        </div>
                        
                        <div class="preview-message" id="previewEncerramento">
                            <div class="preview-header">
                                <i class="fas fa-handshake text-info"></i>
                                Mensagem de Encerramento
                                <span class="preview-status" id="statusEncerramento"></span>
                            </div>
                            <div class="preview-text" id="textEncerramento"></div>
                        </div>
                        
                        <div class="preview-message">
                            <div class="preview-header">
                                <i class="fas fa-business-time text-primary"></i>
                                Horário de Funcionamento
                            </div>
                            <div class="preview-text" id="textHorario"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <script>
        // Preview das mensagens
        document.getElementById('btnPreview').addEventListener('click', function() {
            // Atualizar preview com valores atuais
            document.getElementById('textBoasVindas').textContent = 
                document.querySelector('[name="mensagem_boas_vindas"]').value || 'Nenhuma mensagem configurada';
            document.getElementById('textAusencia').textContent = 
                document.querySelector('[name="mensagem_ausencia"]').value || 'Nenhuma mensagem configurada';
            document.getElementById('textEncerramento').textContent = 
                document.querySelector('[name="mensagem_encerramento"]').value || 'Nenhuma mensagem configurada';
            document.getElementById('textHorario').textContent = 
                document.querySelector('[name="horario_funcionamento"]').value || 'Não configurado';
            
            // Status das mensagens
            document.getElementById('statusBoasVindas').innerHTML = 
                document.getElementById('ativar_boas_vindas').checked ? 
                '<span class="badge bg-success">Ativado</span>' : 
                '<span class="badge bg-secondary">Desativado</span>';
            
            document.getElementById('statusAusencia').innerHTML = 
                document.getElementById('ativar_ausencia').checked ? 
                '<span class="badge bg-success">Ativado</span>' : 
                '<span class="badge bg-secondary">Desativado</span>';
            
            document.getElementById('statusEncerramento').innerHTML = 
                document.getElementById('ativar_encerramento').checked ? 
                '<span class="badge bg-success">Ativado</span>' : 
                '<span class="badge bg-secondary">Desativado</span>';
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        });


    </script>
    
    <style>
        .example-message {
            margin-bottom: 1rem;
            padding: 1rem;
            background: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .example-message:last-child {
            margin-bottom: 0;
        }
        
        .example-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .example-text {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-style: italic;
        }
        
        .config-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .config-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .config-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .config-text {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .preview-container {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .preview-message {
            margin-bottom: 1rem;
            padding: 1rem;
            background: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .preview-message:last-child {
            margin-bottom: 0;
        }
        
        .preview-header {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .preview-text {
            font-size: 0.875rem;
            color: var(--text-muted);
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        
        .form-check-input:checked {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .content-card {
            border: 1px solid var(--border-color);
        }
    </style>
</body>
</html> 