<?php

/**
 * TESTE DE ENVIO DE TEMPLATE - API SERPRO
 * 
 * Este arquivo demonstra como usar corretamente o sistema de templates
 * da API Serpro integrado ao ChatSerpro.
 * 
 * Para usar este teste:
 * 1. Configure as credenciais da API Serpro no sistema
 * 2. Certifique-se de que o template 'central_intimacao_remota' está aprovado na Meta
 * 3. Execute este arquivo via browser: http://localhost/meu-framework/test_template_serpro.php
 */

// Configuração
require_once 'config/app.php';
require_once 'app/libraries/Database.php';
require_once 'app/models/ConfiguracaoModel.php';
require_once 'app/libraries/SerproApi.php';

// Função para exibir resultado
function exibirResultado($titulo, $dados) {
    echo "<h3>$titulo</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Template Serpro - ChatSerpro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; 
        }
        .btn { 
            padding: 10px 20px; background: #007bff; color: white; 
            border: none; border-radius: 4px; cursor: pointer; 
        }
        .btn:hover { background: #0056b3; }
        .example-box { 
            background: #f8f9fa; border: 1px solid #dee2e6; 
            padding: 15px; margin: 15px 0; border-radius: 5px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Teste de Template - API Serpro</h1>
        
        <div class="alert alert-info">
            <strong>📋 Instruções:</strong><br>
            1. Configure as credenciais da API Serpro no sistema<br>
            2. Certifique-se de que o template está aprovado na Meta<br>
            3. Teste o envio preenchendo o formulário abaixo
        </div>

        <?php
        // Verificar se API está configurada
        try {
            $serproApi = new SerproApi();
            
            if (!$serproApi->isConfigured()) {
                echo '<div class="alert alert-error">❌ API Serpro não configurada. Acesse as configurações do sistema primeiro.</div>';
                exit;
            }
            
            // Verificar status do token
            $tokenStatus = $serproApi->obterStatusToken();
            if ($tokenStatus['valido']) {
                echo '<div class="alert alert-success">✅ Token JWT válido até: ' . $tokenStatus['expira_em'] . '</div>';
            } else {
                echo '<div class="alert alert-error">❌ Token JWT inválido ou expirado</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="alert alert-error">❌ Erro ao inicializar API: ' . $e->getMessage() . '</div>';
            exit;
        }
        ?>

        <!-- Formulário de Teste -->
        <div class="example-box">
            <h3>📱 Teste de Envio de Template</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="numero">Número do WhatsApp:</label>
                    <input type="text" id="numero" name="numero" placeholder="5511999999999" value="<?= $_POST['numero'] ?? '' ?>" required>
                    <small>Formato: 5511999999999 (código do país + DDD + número)</small>
                </div>
                
                <div class="form-group">
                    <label for="template">Template:</label>
                    <select id="template" name="template" required>
                        <option value="">Selecione um template</option>
                        <option value="central_intimacao_remota" <?= ($_POST['template'] ?? '') === 'central_intimacao_remota' ? 'selected' : '' ?>>
                            Central de Intimação Remota
                        </option>
                        <option value="boas_vindas" <?= ($_POST['template'] ?? '') === 'boas_vindas' ? 'selected' : '' ?>>
                            Boas-vindas
                        </option>
                        <option value="suporte" <?= ($_POST['template'] ?? '') === 'suporte' ? 'selected' : '' ?>>
                            Suporte
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="parametro1">Parâmetro 1:</label>
                    <input type="text" id="parametro1" name="parametro1" placeholder="Primeiro parâmetro do template" value="<?= $_POST['parametro1'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="parametro2">Parâmetro 2 (opcional):</label>
                    <input type="text" id="parametro2" name="parametro2" placeholder="Segundo parâmetro do template" value="<?= $_POST['parametro2'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="parametro3">Parâmetro 3 (opcional):</label>
                    <input type="text" id="parametro3" name="parametro3" placeholder="Terceiro parâmetro do template" value="<?= $_POST['parametro3'] ?? '' ?>">
                </div>
                
                <button type="submit" name="enviar" class="btn">🚀 Enviar Template</button>
            </form>
        </div>

        <?php
        // Processar envio
        if (isset($_POST['enviar'])) {
            $numero = $_POST['numero'];
            $template = $_POST['template'];
            $parametrosRaw = array_filter([
                $_POST['parametro1'] ?? '',
                $_POST['parametro2'] ?? '',
                $_POST['parametro3'] ?? ''
            ]);
            
            // Converter parâmetros para formato correto da API
            $parametros = [];
            foreach ($parametrosRaw as $parametro) {
                if (!empty($parametro)) {
                    $parametros[] = [
                        'tipo' => 'text',
                        'valor' => $parametro
                    ];
                }
            }
            
            echo '<div class="example-box">';
            echo '<h3>📤 Enviando Template</h3>';
            echo '<p><strong>Número:</strong> ' . htmlspecialchars($numero) . '</p>';
            echo '<p><strong>Template:</strong> ' . htmlspecialchars($template) . '</p>';
            echo '<p><strong>Parâmetros:</strong></p>';
            exibirResultado('Parâmetros Formatados', $parametros);
            
            // Enviar template
            try {
                $resultado = $serproApi->enviarTemplate($numero, $template, $parametros);
                
                if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                    echo '<div class="alert alert-success">✅ Template enviado com sucesso!</div>';
                    exibirResultado('Resposta da API', $resultado['response']);
                } else {
                    echo '<div class="alert alert-error">❌ Erro ao enviar template</div>';
                    exibirResultado('Erro', [
                        'status' => $resultado['status'],
                        'error' => $resultado['error'] ?? 'Erro desconhecido'
                    ]);
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-error">❌ Exceção: ' . $e->getMessage() . '</div>';
            }
            
            echo '</div>';
        }
        ?>

        <!-- Exemplos de Uso -->
        <div class="example-box">
            <h3>📚 Exemplos de Parâmetros por Template</h3>
            
            <h4>1. Central de Intimação Remota</h4>
            <p><strong>Parâmetro 1:</strong> Mensagem da intimação</p>
            <p><strong>Exemplo:</strong> "Você tem uma nova intimação judicial. Acesse o sistema para visualizar."</p>
            
            <h4>2. Boas-vindas</h4>
            <p><strong>Parâmetro 1:</strong> Nome da pessoa</p>
            <p><strong>Exemplo:</strong> "João Silva"</p>
            
            <h4>3. Suporte</h4>
            <p><strong>Parâmetro 1:</strong> Nome da pessoa</p>
            <p><strong>Exemplo:</strong> "Maria Santos"</p>
        </div>

        <!-- Estrutura da API -->
        <div class="example-box">
            <h3>🔧 Estrutura da Requisição</h3>
            <p>A API Serpro espera os parâmetros no seguinte formato:</p>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
{
    "nomeTemplate": "central_intimacao_remota",
    "wabaId": "SEU_WABA_ID",
    "destinatarios": ["5511999999999"],
    "body": {
        "parametros": [
            {
                "tipo": "text",
                "valor": "Sua mensagem aqui"
            }
        ]
    },
    "header": {
        "filename": "tjgo.png",
        "linkMedia": "https://coparente.top/intranet/public/img/tjgo.png"
    }
}
            </pre>
        </div>

        <!-- Debug Information -->
        <div class="example-box">
            <h3>🔍 Informações de Debug</h3>
            <p><strong>Status da API:</strong> <?= $serproApi->isConfigured() ? '✅ Configurada' : '❌ Não configurada' ?></p>
            <p><strong>Token Status:</strong> <?= $tokenStatus['valido'] ? '✅ Válido' : '❌ Inválido' ?></p>
            <?php if ($tokenStatus['valido']): ?>
                <p><strong>Expira em:</strong> <?= $tokenStatus['expira_em'] ?></p>
                <p><strong>Tempo restante:</strong> <?= $tokenStatus['tempo_restante_formatado'] ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 