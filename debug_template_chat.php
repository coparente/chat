<?php

/**
 * DEBUG - Template via Chat Interface
 * 
 * Simula o envio de template exatamente como o painel de chat faz
 */

// Configuração
require_once 'config/app.php';
require_once 'app/libraries/Database.php';
require_once 'app/models/ConfiguracaoModel.php';
require_once 'app/models/ConversaModel.php';
require_once 'app/models/MensagemModel.php';
require_once 'app/models/ContatoModel.php';
require_once 'app/libraries/SerproApi.php';

// Simular sessão de usuário
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Admin';
$_SESSION['usuario_email'] = 'admin@test.com';
$_SESSION['usuario_perfil'] = 'admin';
$_SESSION['usuario_status'] = 'ativo';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐛 Debug Template Chat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { opacity: 0.8; }
        .debug-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .debug-info pre { background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐛 Debug Template Chat Interface</h1>
        
        <div class="alert alert-warning">
            <strong>⚠️ Modo Debug:</strong><br>
            Este arquivo simula exatamente o que o painel de chat faz ao enviar um template.
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="numero">Número do WhatsApp:</label>
                <input type="text" id="numero" name="numero" value="5562996185892" required>
            </div>
            
            <div class="form-group">
                <label for="nome">Nome do Contato:</label>
                <input type="text" id="nome" name="nome" value="Teste Debug">
            </div>
            
            <div class="form-group">
                <label for="template">Template:</label>
                <select id="template" name="template" required>
                    <option value="">Selecione um template</option>
                    <option value="central_intimacao_remota">Central de Intimação Remota</option>
                    <option value="boas_vindas">Boas-vindas</option>
                    <option value="suporte">Suporte</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="parametros">Parâmetros (um por linha):</label>
                <textarea id="parametros" name="parametros" rows="3" placeholder="Teste debug - hora atual">Teste debug - <?= date('H:i:s') ?></textarea>
            </div>
            
            <button type="submit" name="simular_envio" class="btn">🧪 Simular Envio do Chat</button>
        </form>

        <?php
        if (isset($_POST['simular_envio'])) {
            try {
                echo '<div class="debug-info"><h3>🔍 Debug Step by Step</h3>';
                
                // Simular dados do POST exatamente como o chat faz
                $numero = $_POST['numero'];
                $nome = $_POST['nome'];
                $template = $_POST['template'];
                $parametrosText = $_POST['parametros'];
                
                // Converter parâmetros
                $parametrosArray = array_filter(explode("\n", $parametrosText));
                
                echo '<h4>1. Dados recebidos:</h4>';
                echo '<pre>';
                echo "Número: $numero\n";
                echo "Nome: $nome\n";
                echo "Template: $template\n";
                echo "Parâmetros: " . print_r($parametrosArray, true);
                echo '</pre>';
                
                // Simular o que o Chat.php faz
                echo '<h4>2. Processamento (como Chat.php):</h4>';
                
                // Limpar número (método do Chat.php)
                function limparNumero($numero) {
                    return preg_replace('/[^0-9]/', '', $numero);
                }
                
                $numeroLimpo = limparNumero($numero);
                echo '<p>Número limpo: ' . $numeroLimpo . '</p>';
                
                // Converter parâmetros para formato API
                $parametros = [];
                foreach ($parametrosArray as $parametro) {
                    if (!empty(trim($parametro))) {
                        $parametros[] = [
                            'tipo' => 'text',
                            'valor' => trim($parametro)
                        ];
                    }
                }
                
                echo '<p>Parâmetros convertidos:</p>';
                echo '<pre>' . json_encode($parametros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                
                // Verificar conexão com API
                echo '<h4>3. Verificação da API:</h4>';
                $serproApi = new SerproApi();
                
                if (!$serproApi->isConfigured()) {
                    echo '<div class="alert alert-error">❌ API não configurada!</div>';
                    echo '</div>';
                    return;
                }
                
                echo '<p>✅ API configurada</p>';
                
                // Verificar token
                $token = $serproApi->obterTokenValido();
                if (empty($token)) {
                    echo '<div class="alert alert-error">❌ Token inválido!</div>';
                    echo '</div>';
                    return;
                }
                
                echo '<p>✅ Token válido (tamanho: ' . strlen($token) . ' caracteres)</p>';
                
                // Enviar template
                echo '<h4>4. Enviando template:</h4>';
                $resultado = $serproApi->enviarTemplate($numeroLimpo, $template, $parametros);
                
                echo '<p><strong>Status HTTP:</strong> ' . $resultado['status'] . '</p>';
                
                if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                    echo '<div class="alert alert-success">✅ Template enviado com sucesso!</div>';
                    echo '<p><strong>ID da Requisição:</strong> ' . ($resultado['response']['id'] ?? 'N/A') . '</p>';
                    
                    echo '<h4>5. Resposta da API:</h4>';
                    echo '<pre>' . json_encode($resultado['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                } else {
                    echo '<div class="alert alert-error">❌ Erro ao enviar template</div>';
                    echo '<p><strong>Erro:</strong> ' . ($resultado['error'] ?? 'Erro desconhecido') . '</p>';
                    
                    if (isset($resultado['response'])) {
                        echo '<h4>5. Resposta de erro:</h4>';
                        echo '<pre>' . json_encode($resultado['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                    }
                }
                
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="alert alert-error">❌ Exceção: ' . $e->getMessage() . '</div>';
                echo '<p><strong>Arquivo:</strong> ' . $e->getFile() . '</p>';
                echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
                echo '<p><strong>Stack trace:</strong></p>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
            }
        }
        ?>

        <div class="debug-info">
            <h3>📋 Informações do Sistema</h3>
            <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
            <p><strong>Sessão Ativa:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? 'Sim' : 'Não' ?></p>
            <p><strong>Usuário Logado:</strong> <?= $_SESSION['usuario_nome'] ?? 'Não logado' ?></p>
            <p><strong>URL Base:</strong> <?= URL ?></p>
            <p><strong>Diretório Atual:</strong> <?= __DIR__ ?></p>
        </div>
    </div>
</body>
</html> 