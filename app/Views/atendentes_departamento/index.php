<?php include 'app/Views/include/head.php' ?>
<style>
    .atendente-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .atendente-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .atendente-status {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .status-ativo { background-color: #28a745; }
    .status-ausente { background-color: #ffc107; }
    .status-ocupado { background-color: #dc3545; }
    .status-inativo { background-color: #6c757d; }
    
    .configuracao-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .configuracao-item:last-child {
        border-bottom: none;
    }
    
    .btn-atendente {
        border-radius: 6px;
        font-size: 0.85rem;
        padding: 6px 12px;
    }
    
    .modal-configuracao .form-group {
        margin-bottom: 15px;
    }
    
    .horario-input {
        width: 120px;
    }
    
    .dias-semana {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .dia-checkbox {
        display: flex;
        align-items: center;
        gap: 5px;
    }
</style>
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
                        <i class="fas fa-users me-2"></i>
                        Atendentes - <?= htmlspecialchars($departamento->nome) ?>
                    </h1>
                </div>
                
                <div class="topbar-right">
                    <button class="btn btn-primary btn-sm me-2" onclick="abrirModalAdicionar()">
                        <i class="fas fa-plus me-1"></i>
                        Adicionar Atendente
                    </button>
                    
                    <a href="<?= URL ?>/departamentos" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Voltar
                    </a>
                    
                    <!-- Menu do usuário -->
                    <div class="user-menu">
                        <div class="user-avatar" title="<?= $usuario_logado['nome'] ?>">
                            <?= strtoupper(substr($usuario_logado['nome'], 0, 2)) ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="dashboard-content">
                <!-- Informações do Departamento -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informações do Departamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="configuracao-item">
                                            <span class="text-muted">Nome:</span>
                                            <strong><?= htmlspecialchars($departamento->nome) ?></strong>
                                        </div>
                                        <div class="configuracao-item">
                                            <span class="text-muted">Descrição:</span>
                                            <span><?= htmlspecialchars($departamento->descricao) ?: 'Sem descrição' ?></span>
                                        </div>
                                        <div class="configuracao-item">
                                            <span class="text-muted">Status:</span>
                                            <span class="badge bg-<?= $departamento->status === 'ativo' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($departamento->status) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="configuracao-item">
                                            <span class="text-muted">Total de Atendentes:</span>
                                            <strong><?= count($atendentes) ?></strong>
                                        </div>
                                        <div class="configuracao-item">
                                            <span class="text-muted">Atendentes Online:</span>
                                            <strong><?= count(array_filter($atendentes, function($a) { return $a->status === 'ativo'; })) ?></strong>
                                        </div>
                                        <div class="configuracao-item">
                                            <span class="text-muted">Criado em:</span>
                                            <span><?= date('d/m/Y H:i', strtotime($departamento->criado_em)) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Atendentes -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Atendentes do Departamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($atendentes)): ?>
                                    <div class="row">
                                        <?php foreach ($atendentes as $atendente): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="atendente-card">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <span class="atendente-status status-<?= $atendente->status ?>"></span>
                                                                <?= htmlspecialchars($atendente->nome) ?>
                                                            </h6>
                                                            <small class="text-muted"><?= htmlspecialchars($atendente->email) ?></small>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#" onclick="editarConfiguracao(<?= $atendente->id ?>)">
                                                                    <i class="fas fa-edit me-2"></i>Editar Configuração
                                                                </a></li>
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="removerAtendente(<?= $atendente->id ?>, '<?= htmlspecialchars($atendente->nome) ?>')">
                                                                    <i class="fas fa-trash me-2"></i>Remover
                                                                </a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                            <small class="text-muted d-block">Perfil</small>
                                            <strong><?= ucfirst($atendente->perfil_departamento) ?></strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Máx. Conversas</small>
                                            <strong><?= $atendente->max_conversas ?></strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Status</small>
                                            <span class="badge bg-<?= $atendente->status === 'ativo' ? 'success' : ($atendente->status === 'ausente' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($atendente->status) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= $atendente->horario_inicio ?> - <?= $atendente->horario_fim ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum atendente atribuído</h5>
                        <p class="text-muted">Adicione atendentes ao departamento para começar</p>
                        <button class="btn btn-primary" onclick="abrirModalAdicionar()">
                            <i class="fas fa-plus me-2"></i>
                            Adicionar Primeiro Atendente
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</main>
</div>

<!-- Modal Adicionar Atendente -->
<div class="modal fade" id="modalAdicionarAtendente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Atendente ao Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAdicionarAtendente">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuario_id">Selecionar Usuário</label>
                                <select class="form-control" id="usuario_id" name="usuario_id" required>
                                    <option value="">Selecione um usuário...</option>
                                    <?php foreach ($usuarios_disponiveis as $usuario): ?>
                                        <option value="<?= $usuario->id ?>">
                                            <?= htmlspecialchars($usuario->nome) ?> (<?= htmlspecialchars($usuario->email) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="perfil">Perfil no Departamento</label>
                                <select class="form-control" id="perfil" name="perfil" required>
                                    <option value="atendente">Atendente</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_conversas">Máximo de Conversas</label>
                                <input type="number" class="form-control" id="max_conversas" name="max_conversas" value="5" min="1" max="20" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horario_inicio">Horário de Início</label>
                                <input type="time" class="form-control" id="horario_inicio" name="horario_inicio" value="08:00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horario_fim">Horário de Fim</label>
                                <input type="time" class="form-control" id="horario_fim" name="horario_fim" value="18:00" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Dias da Semana</label>
                        <div class="dias-semana">
                            <div class="dia-checkbox">
                                <input type="checkbox" id="dia_1" name="dias_semana[]" value="1" checked>
                                <label for="dia_1">Segunda</label>
                            </div>
                            <div class="dia-checkbox">
                                <input type="checkbox" id="dia_2" name="dias_semana[]" value="2" checked>
                                <label for="dia_2">Terça</label>
                            </div>
                            <div class="dia-checkbox">
                                <input type="checkbox" id="dia_3" name="dias_semana[]" value="3" checked>
                                <label for="dia_3">Quarta</label>
                            </div>
                            <div class="dia-checkbox">
                                <input type="checkbox" id="dia_4" name="dias_semana[]" value="4" checked>
                                <label for="dia_4">Quinta</label>
                            </div>
                            <div class="dia-checkbox">
                                <input type="checkbox" id="dia_5" name="dias_semana[]" value="5" checked>
                                <label for="dia_5">Sexta</label>
                            </div>
                            <div class="dia-checkbox">
                                <input type="checkbox" id="dia_6" name="dias_semana[]" value="6">
                                <label for="dia_6">Sábado</label>
                            </div>
                            <div class="dia-checkbox">
                                <input type="checkbox" id="dia_7" name="dias_semana[]" value="7">
                                <label for="dia_7">Domingo</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="adicionarAtendente()">
                    <i class="fas fa-plus me-2"></i>
                    Adicionar Atendente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Configuração -->
<div class="modal fade" id="modalEditarConfiguracao" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Configuração do Atendente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarConfiguracao">
                    <input type="hidden" id="edit_usuario_id" name="usuario_id">
                    <input type="hidden" id="edit_departamento_id" name="departamento_id" value="<?= $departamento->id ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_perfil">Perfil no Departamento</label>
                                <select class="form-control" id="edit_perfil" name="perfil" required>
                                    <option value="atendente">Atendente</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_max_conversas">Máximo de Conversas</label>
                                <input type="number" class="form-control" id="edit_max_conversas" name="max_conversas" min="1" max="20" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_horario_inicio">Horário de Início</label>
                                <input type="time" class="form-control" id="edit_horario_inicio" name="horario_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_horario_fim">Horário de Fim</label>
                                <input type="time" class="form-control" id="edit_horario_fim" name="horario_fim" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Dias da Semana</label>
                        <div class="dias-semana" id="edit_dias_semana">
                            <!-- Será preenchido via JavaScript -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="atualizarConfiguracao()">
                    <i class="fas fa-save me-2"></i>
                    Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'app/Views/include/linkjs.php' ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function abrirModalAdicionar() {
        $('#modalAdicionarAtendente').modal('show');
    }

    function adicionarAtendente() {
        console.log('Iniciando adicionarAtendente...');
        
        const formData = new FormData(document.getElementById('formAdicionarAtendente'));
        const dados = {
            usuario_id: formData.get('usuario_id'),
            departamento_id: <?= $departamento->id ?>,
            perfil: formData.get('perfil'),
            max_conversas: formData.get('max_conversas'),
            horario_inicio: formData.get('horario_inicio'),
            horario_fim: formData.get('horario_fim'),
            dias_semana: Array.from(formData.getAll('dias_semana[]')).map(Number)
        };

        console.log('Dados a serem enviados:', dados);
        console.log('URL da requisição:', '<?= URL ?>/atendentes-departamento/adicionar-atendente');

        fetch('<?= URL ?>/atendentes-departamento/adicionar-atendente', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                Swal.fire('Sucesso!', data.message, 'success')
                .then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
        });
    }

    function removerAtendente(usuarioId, nomeAtendente) {
        Swal.fire({
            title: 'Confirmar Remoção',
            html: `Tem certeza que deseja remover <strong>${nomeAtendente}</strong> do departamento?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, remover!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const dados = {
                    usuario_id: usuarioId,
                    departamento_id: <?= $departamento->id ?>
                };

                fetch('<?= URL ?>/atendentes-departamento/remover-atendente', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dados)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Sucesso!', data.message, 'success')
                        .then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erro!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                });
            }
        });
    }

    function editarConfiguracao(usuarioId) {
        // Buscar dados do atendente via AJAX
        fetch(`<?= URL ?>/atendentes-departamento/buscar-configuracao/${usuarioId}/${<?= $departamento->id ?>}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const config = data.configuracao;
                
                // Preencher formulário
                document.getElementById('edit_usuario_id').value = usuarioId;
                document.getElementById('edit_perfil').value = config.perfil;
                document.getElementById('edit_max_conversas').value = config.max_conversas;
                document.getElementById('edit_horario_inicio').value = config.horario_inicio;
                document.getElementById('edit_horario_fim').value = config.horario_fim;
                
                // Preencher dias da semana
                const diasSemana = config.dias_semana || [1,2,3,4,5];
                const container = document.getElementById('edit_dias_semana');
                container.innerHTML = '';
                
                const dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
                dias.forEach((dia, index) => {
                    const diaNum = index + 1;
                    const checked = diasSemana.includes(diaNum) ? 'checked' : '';
                    
                    container.innerHTML += `
                        <div class="dia-checkbox">
                            <input type="checkbox" id="edit_dia_${diaNum}" name="dias_semana[]" value="${diaNum}" ${checked}>
                            <label for="edit_dia_${diaNum}">${dia}</label>
                        </div>
                    `;
                });
                
                $('#modalEditarConfiguracao').modal('show');
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
        });
    }

    function atualizarConfiguracao() {
        const formData = new FormData(document.getElementById('formEditarConfiguracao'));
        const dados = {
            usuario_id: formData.get('usuario_id'),
            departamento_id: formData.get('departamento_id'),
            perfil: formData.get('perfil'),
            max_conversas: formData.get('max_conversas'),
            horario_inicio: formData.get('horario_inicio'),
            horario_fim: formData.get('horario_fim'),
            dias_semana: Array.from(formData.getAll('dias_semana[]')).map(Number)
        };

        fetch('<?= URL ?>/atendentes-departamento/atualizar-configuracao', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Sucesso!', data.message, 'success')
                .then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
        });
    }
</script>
</body>
</html> 