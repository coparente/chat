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
                    <h1 class="topbar-title">
                        <i class="fas fa-key me-2"></i>
                        Credenciais Serpro - <?= htmlspecialchars($departamento->nome) ?>
                    </h1>
                </div>
                
                <div class="topbar-right">
                    <a href="<?= URL ?>/departamentos" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Voltar
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
                <!-- Informações do Departamento -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Departamento:</strong> <?= htmlspecialchars($departamento->nome) ?> 
                    (ID: <?= $departamento->id ?>) | 
                    <strong>Total de Credenciais:</strong> <?= count($credenciais ?? []) ?>
                </div>

                <!-- Ações -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Ações
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="testarTodasCredenciais()">
                                <i class="fas fa-play me-2"></i>
                                Testar Todas
                            </button>
                            <button type="button" class="btn btn-primary" onclick="abrirModalNovaCredencial()">
                                <i class="fas fa-plus me-2"></i>
                                Nova Credencial
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de Credenciais -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-key me-2"></i>
                            Credenciais Configuradas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="listaCredenciais">
                            <?php if (isset($credenciais) && is_array($credenciais) && count($credenciais) > 0): ?>
                                <?php foreach ($credenciais as $credencial): ?>
                                    <div class="card mb-3 <?= $credencial->status === 'ativo' ? 'border-success' : 'border-danger' ?>" 
                                         id="credencial-<?= $credencial->id ?>">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <h6 class="mb-1">
                                                        <i class="fas fa-key me-2"></i>
                                                        <?= htmlspecialchars($credencial->nome) ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        ID: <?= $credencial->id ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="d-flex flex-column">
                                                        <span class="badge <?= $credencial->status === 'ativo' ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= ucfirst($credencial->status) ?>
                                                        </span>
                                                        <small class="text-muted mt-1">
                                                            Prioridade: <?= $credencial->prioridade ?? 0 ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="font-monospace">
                                                        <strong>Token:</strong> 
                                                        <?php if ($credencial->token_cache): ?>
                                                            <span class="text-success">
                                                                <i class="fas fa-check-circle"></i> Válido
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                Expira: <?= $credencial->token_expiracao ? date('d/m/Y H:i', strtotime($credencial->token_expiracao)) : 'N/A' ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <span class="text-warning">
                                                                <i class="fas fa-exclamation-triangle"></i> Não configurado
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="d-flex flex-column">
                                                        <small class="text-muted">
                                                            <strong>Último teste:</strong>
                                                        </small>
                                                        <span id="ultimo-teste-<?= $credencial->id ?>">
                                                            <?php if ($credencial->ultimo_teste): ?>
                                                                <?= date('d/m/Y H:i', strtotime($credencial->ultimo_teste)) ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">Nunca testado</span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="testarCredencial(<?= $credencial->id ?>)"
                                                                title="Testar">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="renovarToken(<?= $credencial->id ?>)"
                                                                title="Renovar Token">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="editarCredencial(<?= $credencial->id ?>)"
                                                                title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="excluirCredencial(<?= $credencial->id ?>, '<?= htmlspecialchars($credencial->nome) ?>')"
                                                                title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Resultado do teste -->
                                            <div id="resultado-teste-<?= $credencial->id ?>" class="mt-3" style="display: none;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Nenhuma credencial configurada!</strong><br>
                                    Clique em "Nova Credencial" para adicionar a primeira credencial Serpro para este departamento.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para Nova/Editar Credencial -->
    <div class="modal fade" id="modalCredencial" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCredencialTitle">Nova Credencial Serpro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCredencial">
                        <input type="hidden" id="credencial_id" name="credencial_id" value="">
                        <input type="hidden" name="departamento_id" value="<?= $departamento->id ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">
                                        <i class="fas fa-tag me-1"></i>
                                        Nome da Credencial *
                                    </label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           required maxlength="100" placeholder="Ex: Credencial Principal">
                                    <div class="form-text">Nome para identificar esta credencial</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>
                                        Status
                                    </label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="ativo" selected>Ativo</option>
                                        <option value="inativo">Inativo</option>
                                        <option value="teste">Teste</option>
                                    </select>
                                    <div class="form-text">Status da credencial</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Client ID *
                                    </label>
                                    <input type="text" class="form-control" id="client_id" name="client_id" 
                                           required maxlength="255" placeholder="Seu Client ID">
                                    <div class="form-text">Client ID fornecido pela Serpro</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="client_secret" class="form-label">
                                        <i class="fas fa-lock me-1"></i>
                                        Client Secret *
                                    </label>
                                    <input type="password" class="form-control" id="client_secret" name="client_secret" 
                                           required maxlength="255" placeholder="Seu Client Secret">
                                    <div class="form-text">Client Secret fornecido pela Serpro</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="base_url" class="form-label">
                                        <i class="fas fa-link me-1"></i>
                                        URL Base *
                                    </label>
                                    <input type="url" class="form-control" id="base_url" name="base_url" 
                                           required maxlength="255" value="https://api.whatsapp.serpro.gov.br" 
                                           placeholder="https://api.whatsapp.serpro.gov.br">
                                    <div class="form-text">URL base da API Serpro</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prioridade" class="form-label">
                                        <i class="fas fa-sort-numeric-up me-1"></i>
                                        Prioridade
                                    </label>
                                    <input type="number" class="form-control" id="prioridade" name="prioridade" 
                                           min="0" max="100" value="0">
                                    <div class="form-text">Ordem de uso (0 = principal)</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="waba_id" class="form-label">
                                        <i class="fas fa-id-card me-1"></i>
                                        WABA ID *
                                    </label>
                                    <input type="text" class="form-control" id="waba_id" name="waba_id" 
                                           required maxlength="255" placeholder="Seu WABA ID">
                                    <div class="form-text">WABA ID fornecido pela Serpro</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone_number_id" class="form-label">
                                        <i class="fas fa-phone me-1"></i>
                                        Phone Number ID *
                                    </label>
                                    <input type="text" class="form-control" id="phone_number_id" name="phone_number_id" 
                                           required maxlength="255" placeholder="Seu Phone Number ID">
                                    <div class="form-text">Phone Number ID fornecido pela Serpro</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="webhook_verify_token" class="form-label">
                                <i class="fas fa-shield-alt me-1"></i>
                                Webhook Verify Token
                            </label>
                            <input type="text" class="form-control" id="webhook_verify_token" name="webhook_verify_token" 
                                   maxlength="255" placeholder="Token de verificação do webhook">
                            <div class="form-text">Token para verificar webhooks (opcional)</div>
                        </div>

                        <div class="mb-3">
                            <label for="configuracoes" class="form-label">
                                <i class="fas fa-cogs me-1"></i>
                                Configurações Adicionais
                            </label>
                            <textarea class="form-control" id="configuracoes" name="configuracoes" 
                                      rows="3" placeholder='{"timeout": 30, "retry_attempts": 3}'>
                            </textarea>
                            <div class="form-text">Configurações em formato JSON (opcional)</div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Campos obrigatórios:</strong> Nome, Client ID, Client Secret, URL Base, WABA ID, Phone Number ID
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarCredencial()">
                        <i class="fas fa-save me-2"></i>
                        Salvar Credencial
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'app/Views/include/linkjs.php' ?>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar modal
            const modalCredencial = new bootstrap.Modal(document.getElementById('modalCredencial'));
        });

        function abrirModalNovaCredencial() {
            $('#modalCredencialTitle').text('Nova Credencial Serpro');
            $('#formCredencial')[0].reset();
            $('#credencial_id').val('');
            $('#modalCredencial').modal('show');
        }

        function editarCredencial(credencialId) {
            $.ajax({
                url: '<?= URL ?>/departamentos/api',
                method: 'POST',
                data: {
                    action: 'buscar_credencial',
                    credencial_id: credencialId
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        const credencial = response.credencial;
                        $('#modalCredencialTitle').text('Editar Credencial Serpro');
                        $('#credencial_id').val(credencial.id);
                        $('#nome').val(credencial.nome);
                        $('#status').val(credencial.status || 'ativo');
                        $('#client_id').val(credencial.client_id);
                        $('#client_secret').val(credencial.client_secret);
                        $('#base_url').val(credencial.base_url || 'https://api.whatsapp.serpro.gov.br');
                        $('#prioridade').val(credencial.prioridade || 0);
                        $('#waba_id').val(credencial.waba_id);
                        $('#phone_number_id').val(credencial.phone_number_id);
                        $('#webhook_verify_token').val(credencial.webhook_verify_token || '');
                        $('#configuracoes').val(credencial.configuracoes || '{"timeout": 30, "retry_attempts": 3}');
                        $('#modalCredencial').modal('show');
                    } else {
                        Swal.fire('Erro!', response.message || 'Erro ao carregar credencial', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Erro AJAX:', xhr.responseText);
                    Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                }
            });
        }

        function salvarCredencial() {
            const formData = new FormData($('#formCredencial')[0]);
            const credencialId = $('#credencial_id').val();
            
            $.ajax({
                url: '<?= URL ?>/departamentos/salvar-credencial',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Sucesso!',
                            text: 'Credencial salva com sucesso!',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Testar Agora',
                            cancelButtonText: 'Fechar'
                        }).then((result) => {
                            $('#modalCredencial').modal('hide');
                            if (result.isConfirmed && response.credencial_id) {
                                testarCredencial(response.credencial_id);
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Erro!', response.message || 'Erro ao salvar credencial', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Erro AJAX:', xhr.responseText);
                    Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                }
            });
        }

        function testarCredencial(credencialId) {
            const resultadoDiv = $(`#resultado-teste-${credencialId}`);
            resultadoDiv.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testando...</div>').show();
            
            $.ajax({
                url: '<?= URL ?>/departamentos/testar-credencial/' + credencialId,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        resultadoDiv.html(`
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Teste bem-sucedido!</strong><br>
                                ${response.message}
                            </div>
                        `);
                        $(`#ultimo-teste-${credencialId}`).text(new Date().toLocaleString('pt-BR'));
                    } else {
                        resultadoDiv.html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong>Falha no teste!</strong><br>
                                ${response.message}
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Erro AJAX:', xhr.responseText);
                    resultadoDiv.html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Erro de conexão!</strong><br>
                            Não foi possível conectar ao servidor.
                        </div>
                    `);
                }
            });
        }

        function renovarToken(credencialId) {
            Swal.fire({
                title: 'Renovar Token',
                text: 'Deseja renovar o token desta credencial?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, renovar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= URL ?>/departamentos/renovar-token/' + credencialId,
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Sucesso!', 'Token renovado com sucesso!', 'success')
                                .then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erro!', response.message || 'Erro ao renovar token', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Erro AJAX:', xhr.responseText);
                            Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                        }
                    });
                }
            });
        }

        function excluirCredencial(credencialId, nomeCredencial) {
            Swal.fire({
                title: 'Confirmar Exclusão',
                html: `Tem certeza que deseja excluir a credencial <strong>${nomeCredencial}</strong>?<br><br>
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
                            action: 'excluir_credencial',
                            credencial_id: credencialId
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Sucesso!', 'Credencial excluída com sucesso!', 'success')
                                .then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erro!', response.message || 'Erro ao excluir credencial', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                        }
                    });
                }
            });
        }

        function testarTodasCredenciais() {
            Swal.fire({
                title: 'Testar Todas as Credenciais',
                text: 'Deseja testar todas as credenciais ativas deste departamento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, testar todas',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= URL ?>/departamentos/api',
                        method: 'POST',
                        data: {
                            action: 'testar_todas_credenciais',
                            departamento_id: <?= $departamento->id ?>
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Sucesso!', 'Teste de todas as credenciais concluído!', 'success')
                                .then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erro!', response.message || 'Erro ao testar credenciais', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html> 