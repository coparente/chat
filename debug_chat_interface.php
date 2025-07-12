<?php
// Configuração
require_once 'config/app.php';

// Simular sessão
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Admin';
$_SESSION['usuario_email'] = 'admin@test.com';
$_SESSION['usuario_perfil'] = 'admin';
$_SESSION['usuario_status'] = 'ativo';

// Templates disponíveis
$templates = [
    [
        'nome' => 'central_intimacao_remota',
        'titulo' => 'Central de Intimação Remota',
        'descricao' => 'Template para intimações remotas do tribunal',
        'parametros' => ['mensagem']
    ],
    [
        'nome' => 'boas_vindas',
        'titulo' => 'Boas-vindas',
        'descricao' => 'Mensagem de boas-vindas personalizada',
        'parametros' => ['nome']
    ],
    [
        'nome' => 'suporte',
        'titulo' => 'Atendimento ao Cliente',
        'descricao' => 'Início de atendimento ao cliente',
        'parametros' => ['nome']
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Debug Interface do Chat</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .debug-console {
            background: #1a1a1a;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            height: 300px;
            overflow-y: auto;
            margin-top: 20px;
        }
        .debug-console .timestamp {
            color: #888;
        }
        .debug-console .success {
            color: #00ff00;
        }
        .debug-console .error {
            color: #ff0000;
        }
        .debug-console .warning {
            color: #ffff00;
        }
        .debug-console .info {
            color: #00ffff;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">🔍 Debug Interface do Chat</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-plus-circle me-2"></i>
                            Nova Conversa - Debug
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Debug:</strong> Todas as ações serão registradas no console.
                        </div>
                        
                        <form id="formNovaConversa">
                            <div class="mb-3">
                                <label for="numeroContato" class="form-label">
                                    <i class="fas fa-phone me-1"></i>
                                    Número do WhatsApp *
                                </label>
                                <input type="text" class="form-control" id="numeroContato" 
                                       placeholder="Ex: 5562996185892" value="5562996185892" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nomeContato" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nome do Contato
                                </label>
                                <input type="text" class="form-control" id="nomeContato" 
                                       placeholder="Digite o nome" value="Debug Test">
                            </div>
                            
                            <div class="mb-3">
                                <label for="templateSelect" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Template *
                                </label>
                                <select class="form-select" id="templateSelect" required>
                                    <option value="">Selecione um template</option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?= $template['nome'] ?>" data-parametros='<?= json_encode($template['parametros']) ?>'>
                                            <?= $template['titulo'] ?> - <?= $template['descricao'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="parametros-container" id="parametrosContainer" style="display: none;">
                                <h6 class="mb-3">Parâmetros do Template</h6>
                                <div id="parametrosInputs">
                                    <!-- Inputs dos parâmetros serão adicionados aqui -->
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-primary" id="btnEnviarTemplate">
                                <i class="fas fa-paper-plane me-2"></i>
                                Enviar Template
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-terminal me-2"></i>
                            Console de Debug
                        </h5>
                        <button class="btn btn-sm btn-outline-secondary" id="btnLimparConsole">
                            <i class="fas fa-trash me-1"></i>
                            Limpar
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="debug-console" id="debugConsole">
                            <div class="timestamp">[<?= date('H:i:s') ?>]</div>
                            <div class="info">🔍 Debug Console Inicializado</div>
                            <div class="info">📋 Aguardando ações...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Função para log no console debug
        function debugLog(message, type = 'info') {
            const console = $('#debugConsole');
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = `
                <div class="timestamp">[${timestamp}]</div>
                <div class="${type}">${message}</div>
            `;
            console.append(logEntry);
            console.scrollTop(console[0].scrollHeight);
        }
        
        // Função para mostrar toast
        function mostrarToast(message, type = 'info') {
            const bgClass = type === 'success' ? 'bg-success' : 
                          type === 'error' ? 'bg-danger' : 'bg-info';
            
            const toast = `
                <div class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            // Adicionar ao body se não existe container
            if (!$('#toastContainer').length) {
                $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            }
            
            $('#toastContainer').append(toast);
            $('.toast').last().toast('show');
            
            debugLog(`Toast: ${message}`, type);
        }
        
        // Gerar inputs de parâmetros
        function gerarInputsParametros(parametros) {
            debugLog(`🔧 Gerando inputs para parâmetros: ${parametros.join(', ')}`, 'info');
            
            const container = $('#parametrosInputs');
            container.empty();
            
            parametros.forEach((param, index) => {
                const input = `
                    <div class="mb-3">
                        <label class="form-label">${param}</label>
                        <input type="text" class="form-control" placeholder="Digite o valor para ${param}" required>
                    </div>
                `;
                container.append(input);
            });
        }
        
        // Enviar template
        function enviarTemplate() {
            debugLog('🚀 Iniciando envio de template...', 'info');
            
            const numero = $('#numeroContato').val().trim();
            const nome = $('#nomeContato').val().trim();
            const template = $('#templateSelect').val();
            
            debugLog(`📋 Dados capturados:`, 'info');
            debugLog(`   - Número: ${numero}`, 'info');
            debugLog(`   - Nome: ${nome}`, 'info');
            debugLog(`   - Template: ${template}`, 'info');
            
            if (!numero || !template) {
                debugLog('❌ Validação falhou: Número e template são obrigatórios', 'error');
                mostrarToast('Número e template são obrigatórios', 'error');
                return;
            }
            
            // Coletar parâmetros
            const parametros = [];
            $('#parametrosInputs input').each(function() {
                const valor = $(this).val().trim();
                parametros.push(valor);
                debugLog(`   - Parâmetro: ${valor}`, 'info');
            });
            
            const dados = {
                numero: numero,
                nome: nome,
                template: template,
                parametros: parametros
            };
            
            debugLog('📤 Enviando dados via AJAX...', 'info');
            debugLog(`   URL: <?= URL ?>/chat/iniciar-conversa`, 'info');
            debugLog(`   Dados: ${JSON.stringify(dados)}`, 'info');
            
            $('#btnEnviarTemplate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enviando...');
            
            $.ajax({
                url: '<?= URL ?>/chat/iniciar-conversa',
                method: 'POST',
                data: JSON.stringify(dados),
                contentType: 'application/json',
                beforeSend: function() {
                    debugLog('🔄 Requisição enviada para o servidor...', 'info');
                },
                success: function(response) {
                    debugLog('✅ Resposta recebida do servidor:', 'success');
                    debugLog(`   Status: ${response.success ? 'Sucesso' : 'Erro'}`, response.success ? 'success' : 'error');
                    debugLog(`   Mensagem: ${response.message}`, response.success ? 'success' : 'error');
                    
                    if (response.dados) {
                        debugLog(`   Dados: ${JSON.stringify(response.dados)}`, 'info');
                    }
                    
                    if (response.success) {
                        $('#formNovaConversa')[0].reset();
                        $('#parametrosContainer').hide();
                        mostrarToast('Template enviado com sucesso!', 'success');
                        debugLog('🎉 Template enviado com sucesso!', 'success');
                    } else {
                        mostrarToast(response.message, 'error');
                        debugLog(`❌ Erro: ${response.message}`, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    debugLog('❌ Erro na requisição AJAX:', 'error');
                    debugLog(`   Status: ${status}`, 'error');
                    debugLog(`   Erro: ${error}`, 'error');
                    debugLog(`   Resposta: ${xhr.responseText}`, 'error');
                    mostrarToast('Erro ao enviar template', 'error');
                },
                complete: function() {
                    $('#btnEnviarTemplate').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Enviar Template');
                    debugLog('🔄 Requisição finalizada', 'info');
                }
            });
        }
        
        // Event listeners
        $(document).ready(function() {
            debugLog('🔧 Documento carregado, configurando eventos...', 'info');
            
            // Mudança de template
            $('#templateSelect').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const parametros = selectedOption.data('parametros') || [];
                
                debugLog(`🔄 Template selecionado: ${$(this).val()}`, 'info');
                debugLog(`   Parâmetros: ${parametros.join(', ')}`, 'info');
                
                if (parametros.length > 0) {
                    $('#parametrosContainer').show();
                    gerarInputsParametros(parametros);
                } else {
                    $('#parametrosContainer').hide();
                }
            });
            
            // Enviar template
            $('#btnEnviarTemplate').on('click', function() {
                debugLog('🖱️ Botão "Enviar Template" clicado', 'info');
                enviarTemplate();
            });
            
            // Limpar console
            $('#btnLimparConsole').on('click', function() {
                $('#debugConsole').empty();
                debugLog('🗑️ Console limpo', 'info');
            });
            
            debugLog('✅ Todos os eventos configurados', 'success');
        });
    </script>
</body>
</html> 