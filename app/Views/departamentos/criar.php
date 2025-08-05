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
                        <i class="fas fa-plus me-2"></i>
                        Criar Novo Departamento
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
                                    <i class="fas fa-plus me-2"></i>
                                    Informações do Departamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="formCriarDepartamento" method="POST" action="<?= URL ?>/departamentos/salvar">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nome" class="form-label">
                                                    <i class="fas fa-building me-1"></i>
                                                    Nome do Departamento *
                                                </label>
                                                <input type="text" class="form-control" id="nome" name="nome" 
                                                       required maxlength="100" placeholder="Ex: Suporte Técnico">
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
                                                       value="#007bff" title="Escolha a cor do departamento">
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
                                                  placeholder="Descreva as responsabilidades e características deste departamento"></textarea>
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
                                                    <option value="fas fa-building" selected>🏢 Edifício</option>
                                                    <option value="fas fa-headset">🎧 Suporte</option>
                                                    <option value="fas fa-chart-line">📈 Comercial</option>
                                                    <option value="fas fa-dollar-sign">💰 Financeiro</option>
                                                    <option value="fas fa-users">👥 RH</option>
                                                    <option value="fas fa-gavel">⚖️ Jurídico</option>
                                                    <option value="fas fa-cog">⚙️ Técnico</option>
                                                    <option value="fas fa-heart">❤️ Atendimento</option>
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
                                                       min="0" max="100" value="0">
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
                                                       value="08:00-18:00" placeholder="Ex: 08:00-18:00">
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
                                                    <option value="ativo" selected>Ativo</option>
                                                    <option value="inativo">Inativo</option>
                                                </select>
                                                <div class="form-text">Status inicial do departamento</div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Próximos Passos:</strong> Após criar o departamento, você poderá:
                                        <ul class="mb-0 mt-2">
                                            <li>Configurar credenciais da API Serpro específicas para este departamento</li>
                                            <li>Adicionar atendentes ao departamento</li>
                                            <li>Configurar mensagens automáticas específicas</li>
                                            <li>Definir templates de mensagem personalizados</li>
                                        </ul>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="<?= URL ?>/departamentos" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Voltar
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-outline-secondary me-2" onclick="limparFormulario()">
                                                <i class="fas fa-eraser me-2"></i>
                                                Limpar
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>
                                                Criar Departamento
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
            $('#formCriarDepartamento').on('submit', function(e) {
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
            const formData = new FormData($('#formCriarDepartamento')[0]);
            
            $.ajax({
                url: '<?= URL ?>/departamentos/salvar',
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
                            text: 'Departamento criado com sucesso!',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Ver Departamentos',
                            cancelButtonText: 'Criar Outro'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= URL ?>/departamentos';
                            } else {
                                limparFormulario();
                            }
                        });
                    } else {
                        Swal.fire('Erro!', response.message || 'Erro ao criar departamento', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Erro AJAX:', xhr.responseText);
                    Swal.fire('Erro!', 'Erro de conexão. Tente novamente.', 'error');
                }
            });
        }

        function limparFormulario() {
            $('#formCriarDepartamento')[0].reset();
            $('#nome').focus();
            
            Swal.fire({
                title: 'Formulário Limpo',
                text: 'Todos os campos foram limpos.',
                icon: 'info',
                timer: 1500,
                showConfirmButton: false
            });
        }

        // Auto-save do formulário (salvar no localStorage)
        function salvarRascunho() {
            const dados = {
                nome: $('#nome').val(),
                descricao: $('#descricao').val(),
                cor: $('#cor').val(),
                icone: $('#icone').val(),
                prioridade: $('#prioridade').val(),
                horario_atendimento: $('#horario_atendimento').val(),
                status: $('#status').val()
            };
            localStorage.setItem('rascunho_departamento', JSON.stringify(dados));
        }

        function carregarRascunho() {
            const rascunho = localStorage.getItem('rascunho_departamento');
            if (rascunho) {
                const dados = JSON.parse(rascunho);
                $('#nome').val(dados.nome || '');
                $('#descricao').val(dados.descricao || '');
                $('#cor').val(dados.cor || '#007bff');
                $('#icone').val(dados.icone || 'fas fa-building');
                $('#prioridade').val(dados.prioridade || '0');
                $('#horario_atendimento').val(dados.horario_atendimento || '08:00-18:00');
                $('#status').val(dados.status || 'ativo');
            }
        }

        // Salvar rascunho a cada 5 segundos
        setInterval(salvarRascunho, 5000);

        // Carregar rascunho ao carregar a página
        $(document).ready(function() {
            carregarRascunho();
        });

        // Limpar rascunho quando o formulário for enviado com sucesso
        function limparRascunho() {
            localStorage.removeItem('rascunho_departamento');
        }
    </script>
</body>
</html> 