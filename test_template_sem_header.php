<?php

/**
 * TESTE RÁPIDO - TEMPLATE SEM HEADER
 * 
 * Teste para verificar se o template funciona sem imagem no header
 */

// Configuração
require_once 'config/app.php';
require_once 'app/libraries/Database.php';
require_once 'app/models/ConfiguracaoModel.php';
require_once 'app/libraries/SerproApi.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Template Sem Header</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Teste Template Sem Header</h1>
        
        <div class="alert alert-success">
            <strong>✅ Header desabilitado temporariamente</strong><br>
            O template será enviado sem imagem para evitar erro 403
        </div>

        <form method="POST" action="">
            <button type="submit" name="testar" class="btn">🚀 Testar Envio Sem Header</button>
        </form>

        <?php
        if (isset($_POST['testar'])) {
            try {
                $serproApi = new SerproApi();
                
                // Parâmetros do teste
                $parametros = [
                    [
                        'tipo' => 'text',
                        'valor' => 'Teste sem header - ' . date('H:i:s')
                    ]
                ];
                
                echo '<h3>📤 Enviando Template...</h3>';
                echo '<p><strong>Número:</strong> 5562996185892</p>';
                echo '<p><strong>Template:</strong> central_intimacao_remota</p>';
                echo '<p><strong>Header:</strong> Nenhum (removido)</p>';
                
                // Enviar template sem header
                $resultado = $serproApi->enviarTemplate(
                    '5562996185892',
                    'central_intimacao_remota',
                    $parametros,
                    null  // Header explicitamente null
                );
                
                if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                    echo '<div class="alert alert-success">✅ Template enviado com sucesso!</div>';
                    echo '<p><strong>ID da Requisição:</strong> ' . ($resultado['response']['id'] ?? 'N/A') . '</p>';
                    
                    echo '<h4>📋 Resposta Completa:</h4>';
                    echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 5px;">';
                    echo json_encode($resultado['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    echo '</pre>';
                    
                    if (isset($resultado['response']['id'])) {
                        echo '<div style="margin-top: 20px;">';
                        echo '<a href="verificar_status_template.php" class="btn">🔍 Verificar Status</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-error">❌ Erro ao enviar template</div>';
                    echo '<p><strong>Status:</strong> ' . $resultado['status'] . '</p>';
                    echo '<p><strong>Erro:</strong> ' . ($resultado['error'] ?? 'Erro desconhecido') . '</p>';
                }
                
            } catch (Exception $e) {
                echo '<div class="alert alert-error">❌ Exceção: ' . $e->getMessage() . '</div>';
            }
        }
        ?>

        <div style="background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h3>🔧 O que foi corrigido:</h3>
            <ul>
                <li>❌ <del>Header com URL inacessível causando erro 403</del></li>
                <li>✅ Template enviado sem header (apenas texto)</li>
                <li>✅ Evita erro de download de mídia</li>
            </ul>
        </div>
    </div>
</body>
</html> 