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
                        <p class="text-muted">Configure respostas automáticas por departamento</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="abrirModalNovaMensagem()">
                            <i class="fas fa-plus me-2"></i>
                            Nova Mensagem
                        </button>
                        <a href="<?= URL ?>/configuracoes" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-arrow-left me-2"></i>
                            Voltar
                        </a>
                    </div>
                </div>

                <!-- Departamentos -->
                <div class="row">
                    <?php foreach ($departamentos as $departamento): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h5 class="content-card-title">
                                        <i class="fas fa-building me-2" style="color: <?= $departamento->cor ?>"></i>
                                        <?= htmlspecialchars($departamento->nome) ?>
                                    </h5>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="abrirModalNovaMensagem(<?= $departamento->id ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="content-card-body">
                                    <?php 
                                    $mensagens = $mensagens_por_departamento[$departamento->id] ?? [];
                                    if (empty($mensagens)): 
                                    ?>
                                        <p class="text-muted text-center">Nenhuma mensagem automática configurada</p>
                                    <?php else: ?>
                                        <div class="mensagens-lista">
                                            <?php foreach ($mensagens as $mensagem): ?>
                                                <div class="mensagem-item" data-id="<?= $mensagem->id ?>">
                                                    <div class="mensagem-header">
                                                        <div class="mensagem-tipo">
                                                            <i class="<?= getTipoIcone($mensagem->tipo) ?>"></i>
                                                            <?= getTipoNome($mensagem->tipo) ?>
                                                        </div>
                                                        <div class="mensagem-acoes">
                                                            <button class="btn btn-sm btn-outline-secondary" 
                                                                    onclick="editarMensagem(<?= $mensagem->id ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    onclick="excluirMensagem(<?= $mensagem->id ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mensagem-conteudo">
                                                        <strong><?= htmlspecialchars($mensagem->titulo) ?></strong>
                                                        <p class="text-muted"><?= htmlspecialchars(substr($mensagem->mensagem, 0, 100)) ?>...</p>
                                                    </div>
                                                    <div class="mensagem-status">
                                                        <span class="badge bg-<?= $mensagem->ativo ? 'success' : 'secondary' ?>">
                                                            <?= $mensagem->ativo ? 'Ativo' : 'Inativo' ?>
                                                        </span>
                                                        <small class="text-muted">
                                                            <?= $mensagem->horario_inicio ?> - <?= $mensagem->horario_fim ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nova/Editar Mensagem -->
    <div class="modal fade" id="modalMensagem" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Nova Mensagem Automática</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formMensagem">
                        <input type="hidden" id="mensagemId" name="id">
                        <input type="hidden" id="departamentoId" name="departamento_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Departamento</label>
                                    <select class="form-select" id="selectDepartamento" name="departamento_id" required>
                                        <option value="">Selecione um departamento</option>
                                        <?php foreach ($departamentos as $dept): ?>
                                            <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->nome) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Mensagem</label>
                                    <select class="form-select" id="tipoMensagem" name="tipo" required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="boas_vindas">Boas-vindas</option>
                                        <option value="ausencia">Ausência de Atendentes</option>
                                        <option value="encerramento">Encerramento</option>
                                        <option value="fora_horario">Fora do Horário</option>
                                        <option value="aguardando">Aguardando Atendimento</option>
                                        <option value="transferencia">Transferência</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" class="form-control" id="tituloMensagem" name="titulo" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mensagem</label>
                            <textarea class="form-control" id="conteudoMensagem" name="mensagem" rows="4" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Horário de Início</label>
                                    <input type="time" class="form-control" id="horarioInicio" name="horario_inicio" value="08:00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Horário de Fim</label>
                                    <input type="time" class="form-control" id="horarioFim" name="horario_fim" value="18:00">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dias da Semana</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="segunda" name="dias_semana[]" checked>
                                <label class="form-check-label" for="segunda">Segunda-feira</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="2" id="terca" name="dias_semana[]" checked>
                                <label class="form-check-label" for="terca">Terça-feira</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="3" id="quarta" name="dias_semana[]" checked>
                                <label class="form-check-label" for="quarta">Quarta-feira</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="4" id="quinta" name="dias_semana[]" checked>
                                <label class="form-check-label" for="quinta">Quinta-feira</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="5" id="sexta" name="dias_semana[]" checked>
                                <label class="form-check-label" for="sexta">Sexta-feira</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="6" id="sabado" name="dias_semana[]">
                                <label class="form-check-label" for="sabado">Sábado</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="7" id="domingo" name="dias_semana[]">
                                <label class="form-check-label" for="domingo">Domingo</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ativoMensagem" name="ativo" checked>
                                <label class="form-check-label" for="ativoMensagem">Mensagem Ativa</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarMensagem()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'app/Views/include/linkjs.php' ?>
    
    <script>
        let modoEdicao = false;
        let mensagemEditando = null;

        // Debug: verificar departamentos carregados
        // console.log('Departamentos carregados:', <?= json_encode($departamentos) ?>);

        // Sincronizar campo de departamento quando select mudar
        document.addEventListener('DOMContentLoaded', function() {
            const selectDepartamento = document.getElementById('selectDepartamento');
            const inputDepartamento = document.getElementById('departamentoId');
            
            if (selectDepartamento && inputDepartamento) {
                selectDepartamento.addEventListener('change', function() {
                    inputDepartamento.value = this.value;
                });
            }
        });

        function abrirModalNovaMensagem(departamentoId = null) {
            modoEdicao = false;
            mensagemEditando = null;
            
            // Limpar formulário
            document.getElementById('formMensagem').reset();
            document.getElementById('mensagemId').value = '';
            document.getElementById('departamentoId').value = '';
            
            // Se um departamento foi especificado, selecioná-lo
            if (departamentoId) {
                document.getElementById('selectDepartamento').value = departamentoId;
                document.getElementById('departamentoId').value = departamentoId;
                document.getElementById('selectDepartamento').disabled = true;
            } else {
                document.getElementById('selectDepartamento').disabled = false;
                // Selecionar o primeiro departamento por padrão
                const select = document.getElementById('selectDepartamento');
                if (select.options.length > 1) {
                    select.selectedIndex = 1; // Primeira opção válida (pula o "Selecione um departamento")
                    document.getElementById('departamentoId').value = select.value;
                }
            }
            
            document.getElementById('modalTitulo').textContent = 'Nova Mensagem Automática';
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('modalMensagem')).show();
        }

        function editarMensagem(id) {
            // Buscar dados da mensagem via AJAX
            fetch('<?= URL ?>/configuracoes/mensagens/buscar/' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        preencherFormulario(data.mensagem);
                        modoEdicao = true;
                        mensagemEditando = id;
                        document.getElementById('modalTitulo').textContent = 'Editar Mensagem Automática';
                        new bootstrap.Modal(document.getElementById('modalMensagem')).show();
                    } else {
                        alert('Erro ao carregar mensagem: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar mensagem');
                });
        }

        function preencherFormulario(mensagem) {
            document.getElementById('mensagemId').value = mensagem.id;
            document.getElementById('departamentoId').value = mensagem.departamento_id;
            document.getElementById('selectDepartamento').value = mensagem.departamento_id;
            document.getElementById('tipoMensagem').value = mensagem.tipo;
            document.getElementById('tituloMensagem').value = mensagem.titulo;
            document.getElementById('conteudoMensagem').value = mensagem.mensagem;
            document.getElementById('horarioInicio').value = mensagem.horario_inicio;
            document.getElementById('horarioFim').value = mensagem.horario_fim;
            document.getElementById('ativoMensagem').checked = mensagem.ativo == 1;
            
            // Limpar checkboxes
            document.querySelectorAll('input[name="dias_semana[]"]').forEach(cb => cb.checked = false);
            
            // Marcar dias da semana
            if (mensagem.dias_semana) {
                mensagem.dias_semana.forEach(dia => {
                    const checkbox = document.querySelector(`input[name="dias_semana[]"][value="${dia}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
        }

        function salvarMensagem() {
            const formData = new FormData(document.getElementById('formMensagem'));
            const departamentoId = formData.get('departamento_id');
            
            // Validar se departamento foi selecionado
            if (!departamentoId || departamentoId === '') {
                alert('Por favor, selecione um departamento');
                return;
            }
            
            const dados = {
                departamento_id: departamentoId,
                tipo: formData.get('tipo'),
                titulo: formData.get('titulo'),
                mensagem: formData.get('mensagem'),
                horario_inicio: formData.get('horario_inicio'),
                horario_fim: formData.get('horario_fim'),
                ativo: document.getElementById('ativoMensagem').checked ? 1 : 0,
                dias_semana: Array.from(formData.getAll('dias_semana[]')).map(Number)
            };

            // Debug: verificar dados
            // console.log('Dados sendo enviados:', dados);

            const payload = {
                acao: modoEdicao ? 'atualizar' : 'criar',
                dados: dados
            };

            if (modoEdicao) {
                payload.id = mensagemEditando;
            }

            // console.log('Payload completo:', payload);

            fetch('<?= URL ?>/configuracoes/mensagens/salvar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                // console.log('Resposta do servidor:', data);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao salvar mensagem');
            });
        }

        function excluirMensagem(id) {
            if (confirm('Tem certeza que deseja excluir esta mensagem automática?')) {
                fetch('<?= URL ?>/configuracoes/mensagens/salvar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        acao: 'excluir',
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir mensagem');
                });
            }
        }
    </script>

    <style>
        .mensagem-item {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: var(--card-bg);
        }

        .mensagem-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .mensagem-tipo {
            font-weight: 600;
            color: var(--text-primary);
        }

        .mensagem-acoes {
            display: flex;
            gap: 0.5rem;
        }

        .mensagem-conteudo {
            margin-bottom: 0.5rem;
        }

        .mensagem-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mensagens-lista {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</body>
</html>

<?php
function getTipoIcone($tipo) {
    $icones = [
        'boas_vindas' => 'fas fa-handshake',
        'ausencia' => 'fas fa-user-clock',
        'encerramento' => 'fas fa-door-closed',
        'fora_horario' => 'fas fa-clock',
        'aguardando' => 'fas fa-hourglass-half',
        'transferencia' => 'fas fa-exchange-alt'
    ];
    return $icones[$tipo] ?? 'fas fa-comment';
}

function getTipoNome($tipo) {
    $nomes = [
        'boas_vindas' => 'Boas-vindas',
        'ausencia' => 'Ausência de Atendentes',
        'encerramento' => 'Encerramento',
        'fora_horario' => 'Fora do Horário',
        'aguardando' => 'Aguardando Atendimento',
        'transferencia' => 'Transferência'
    ];
    return $nomes[$tipo] ?? 'Mensagem';
}
?> 