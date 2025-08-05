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
                        <i class="fas fa-edit me-2"></i>
                        Editar Departamento: <?= htmlspecialchars($departamento->nome) ?>
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
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    Informações do Departamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Informações do Departamento -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>ID:</strong> <?= $departamento->id ?> | 
                                    <strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($departamento->criado_em)) ?>
                                    <?php if ($departamento->atualizado_em): ?>
                                        | <strong>Última atualização:</strong> <?= date('d/m/Y H:i', strtotime($departamento->atualizado_em)) ?>
                                    <?php endif; ?>
                                </div>

                                <form id="formEditarDepartamento" method="POST" action="<?= URL ?>/departamentos/atualizar/<?= $departamento->id ?>">
                                    <input type="hidden" name="departamento_id" value="<?= $departamento->id ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nome" class="form-label">
                                                    <i class="fas fa-building me-1"></i>
                                                    Nome do Departamento *
                                                </label>
                                                <input type="text" class="form-control" id="nome" name="nome" 
                                                       required maxlength="100" 
                                                       value="<?= htmlspecialchars($departamento->nome) ?>"
                                                       placeholder="Ex: Suporte Técnico">
                                                <div class="form-text">Nome único para identificar o departamento</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cor" class="form-label">
                                                    <i class="fas fa-palette me-1"></i>
                                                    Cor do Departamento
                                                </label>
                                                <input type="color" class="form-control form-control-color" id="cor" name="cor" 
                                                       value="<?= htmlspecialchars($departamento->cor ?? '#007bff') ?>" 
                                                       title="Escolha a cor do departamento">
                                                <div class="form-text">Cor para identificar o departamento</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="descricao" class="form-label">
                                            <i class="fas fa-align-left me-1"></i>
                                            Descrição
                                        </label>
                                        <textarea class="form-control" id="descricao" name="descricao" 
                                                  rows="3" maxlength="500" 
                                                  placeholder="Descreva as responsabilidades e características deste departamento"><?= htmlspecialchars($departamento->descricao ?? '') ?></textarea>
                                        <div class="form-text">Descrição detalhada do departamento</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="icone" class="form-label">
                                                    <i class="fas fa-icons me-1"></i>
                                                    Ícone do Departamento
                                                </label>
                                                <select class="form-select" id="icone" name="icone">
                                                    <option value="fas fa-building" <?= ($departamento->icone == 'fas fa-building') ? 'selected' : '' ?>>🏢 Edifício</option>
                                                    <option value="fas fa-headset" <?= ($departamento->icone == 'fas fa-headset') ? 'selected' : '' ?>>🎧 Suporte</option>
                                                    <option value="fas fa-chart-line" <?= ($departamento->icone == 'fas fa-chart-line') ? 'selected' : '' ?>>📈 Comercial</option>
                                                    <option value="fas fa-dollar-sign" <?= ($departamento->icone == 'fas fa-dollar-sign') ? 'selected' : '' ?>>💰 Financeiro</option>
                                                    <option value="fas fa-users" <?= ($departamento->icone == 'fas fa-users') ? 'selected' : '' ?>>👥 RH</option>
                                                    <option value="fas fa-gavel" <?= ($departamento->icone == 'fas fa-gavel') ? 'selected' : '' ?>>⚖️ Jurídico</option>
                                                    <option value="fas fa-cog" <?= ($departamento->icone == 'fas fa-cog') ? 'selected' : '' ?>>⚙️ Técnico</option>
                                                    <option value="fas fa-heart" <?= ($departamento->icone == 'fas fa-heart') ? 'selected' : '' ?>>❤️ Atendimento</option>
                                                </select>
                                                <div class="form-text">Ícone para representar o departamento</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="prioridade" class="form-label">
                                                    <i class="fas fa-sort-numeric-up me-1"></i>
                                                    Prioridade
                                                </label>
                                                <input type="number" class="form-control" id="prioridade" name="prioridade" 
                                                       min="0" max="100" 
                                                       value="<?= htmlspecialchars($departamento->prioridade ?? 0) ?>">
                                                <div class="form-text">Prioridade de atendimento (0 = menor, 100 = maior)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="horario_atendimento" class="form-label">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Horário de Atendimento
                                                </label>
                                                <input type="text" class="form-control" id="horario_atendimento" name="horario_atendimento" 
                                                       value="<?= htmlspecialchars($departamento->configuracoes->horario_atendimento ?? '08:00-18:00') ?>"
                                                       placeholder="Ex: 08:00-18:00">
                                                <div class="form-text">Horário padrão de funcionamento</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">
                                                    <i class="fas fa-toggle-on me-1"></i>
                                                    Status do Departamento
                                                </label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="ativo" <?= ($departamento->status == 'ativo') ? 'selected' : '' ?>>Ativo</option>
                                                    <option value="inativo" <?= ($departamento->status == 'inativo') ? 'selected' : '' ?>>Inativo</option>
                                                </select>
                                                <div class="form-text">Status atual do departamento</div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Estatísticas Rápidas -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="stats-card stats-card-info">
                                                <div class="stats-card-body">
                                                    <div class="stats-card-icon">
                                                        <i class="fas fa-key"></i>
                                                    </div>
                                                    <div class="stats-card-content">
                                                        <h3 class="stats-card-number"><?= $estatisticas->credenciais_count ?? 0 ?></h3>
                                                        <p class="stats-card-label">Credenciais Serpro</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stats-card stats-card-success">
                                                <div class="stats-card-body">
                                                    <div class="stats-card-icon">
                                                        <i class="fas fa-users"></i>
                                                    </div>
                                                    <div class="stats-card-content">
                                                        <h3 class="stats-card-number"><?= $estatisticas->atendentes_count ?? 0 ?></h3>
                                                        <p class="stats-card-label">Atendentes</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stats-card stats-card-warning">
                                                <div class="stats-card-body">
                                                    <div class="stats-card-icon">
                                                        <i class="fas fa-comments"></i>
                                                    </div>
                                                    <div class="stats-card-content">
                                                        <h3 class="stats-card-number"><?= $estatisticas->conversas_count ?? 0 ?></h3>
                                                        <p class="stats-card-label">Conversas</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stats-card stats-card-primary">
                                                <div class="stats-card-body">
                                                    <div class="stats-card-icon">
                                                        <i class="fas fa-envelope"></i>
                                                    </div>
                                                    <div class="stats-card-content">
                                                        <h3 class="stats-card-number"><?= $estatisticas->mensagens_count ?? 0 ?></h3>
                                                        <p class="stats-card-label">Mensagens</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="<?= URL ?>/departamentos" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Voltar
                                        </a>
                                        <div>
                                            <a href="<?= URL ?>/departamentos/credenciais/<?= $departamento->id ?>" 
                                               class="btn btn-outline-info me-2">
                                                <i class="fas fa-key me-2"></i>
                                                Credenciais Serpro
                                            </a>
                                            <a href="<?= URL ?>/departamentos/estatisticas/<?= $departamento->id ?>" 
                                               class="btn btn-outline-success me-2">
                                                <i class="fas fa-chart-bar me-2"></i>
                                                Estatísticas
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>
                                                Salvar Alterações
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include 'app/Views/include/linkjs.php' ?>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Validação do formulário
            $('#formEditarDepartamento').on('submit', function(e) {
                e.preventDefault();
                
                const nome = $('#nome').val().trim();
                if (!nome) {
                    Swal.fire('Erro!', 'O nome do departamento é obrigatório.', 'error');
                    $('#nome').focus();
                    return false;
                }

                // Enviar formulário
                enviarFormulario();
            });
        });

        function enviarFormulario() {
            const formData = new FormData($('#formEditarDepartamento')[0]);
            
            $.ajax({
                url: '<?= URL ?>/departamentos/atualizar/<?= $departamento->id ?>',
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
                            text: 'Departamento atualizado com sucesso!',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Ver Departamentos',
                            cancelButtonText: 'Continuar Editando'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= URL ?>/departamentos';
                            }
                        });
                    } else {
                        Swal.fire('Erro!', response.message || 'Erro ao atualizar departamento', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Erro AJAX:', xhr.responseText);
                    Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                }
            });
        }

        // Detectar mudanças no formulário
        let formOriginal = $('#formEditarDepartamento').serialize();
        
        $(document).on('change keyup', '#formEditarDepartamento input, #formEditarDepartamento textarea, #formEditarDepartamento select', function() {
            let formAtual = $('#formEditarDepartamento').serialize();
            if (formOriginal !== formAtual) {
                // Formulário foi alterado
                $(window).on('beforeunload', function() {
                    return 'Você tem alterações não salvas. Deseja realmente sair?';
                });
            } else {
                // Formulário não foi alterado
                $(window).off('beforeunload');
            }
        });

        // Remover aviso quando o formulário for enviado
        $('#formEditarDepartamento').on('submit', function() {
            $(window).off('beforeunload');
        });
    </script>
</body>
</html> 