<?php

/**
 * VERIFICAR STATUS DE ENTREGA - TEMPLATE SERPRO
 * 
 * Este arquivo permite verificar o status de entrega de um template
 * usando o ID retornado pela API Serpro.
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
    <title>Verificar Status - Template Serpro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .status-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificar Status do Template</h1>
        
        <div class="alert alert-info">
            <strong>📋 Status do seu último envio:</strong><br>
            ID: <strong>96cbd109-d61a-4a35-a9bf-983b060bcf64</strong><br>
            Template: <strong>central_intimacao_remota</strong><br>
            Número: <strong>5562996185892</strong>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="requisicao_id">ID da Requisição:</label>
                <input type="text" id="requisicao_id" name="requisicao_id" 
                       value="<?= $_POST['requisicao_id'] ?? '96cbd109-d61a-4a35-a9bf-983b060bcf64' ?>" 
                       placeholder="96cbd109-d61a-4a35-a9bf-983b060bcf64" required>
            </div>
            
            <button type="submit" name="verificar" class="btn">🔍 Verificar Status</button>
        </form>

        <?php
        if (isset($_POST['verificar'])) {
            $requisicaoId = $_POST['requisicao_id'];
            
            try {
                $serproApi = new SerproApi();
                $resultado = $serproApi->consultarStatus($requisicaoId);
                
                echo '<div class="status-box">';
                echo '<h3>📊 Status da Requisição</h3>';
                
                if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                    echo '<div class="alert alert-success">✅ Status consultado com sucesso!</div>';
                    
                    echo '<h4>📋 Detalhes:</h4>';
                    echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 5px;">';
                    echo json_encode($resultado['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    echo '</pre>';
                    
                    // Interpretar status comum
                    if (isset($resultado['response']['status'])) {
                        $status = $resultado['response']['status'];
                        switch (strtolower($status)) {
                            case 'sent':
                                echo '<div class="alert alert-success">📤 Mensagem enviada com sucesso!</div>';
                                break;
                            case 'delivered':
                                echo '<div class="alert alert-success">✅ Mensagem entregue ao WhatsApp!</div>';
                                break;
                            case 'read':
                                echo '<div class="alert alert-success">👁️ Mensagem lida pelo destinatário!</div>';
                                break;
                            case 'failed':
                                echo '<div class="alert alert-error">❌ Falha na entrega da mensagem</div>';
                                break;
                            default:
                                echo '<div class="alert alert-info">ℹ️ Status: ' . $status . '</div>';
                        }
                    }
                } else {
                    echo '<div class="alert alert-error">❌ Erro ao consultar status</div>';
                    echo '<p><strong>Status HTTP:</strong> ' . $resultado['status'] . '</p>';
                    echo '<p><strong>Erro:</strong> ' . ($resultado['error'] ?? 'Erro desconhecido') . '</p>';
                }
                
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="alert alert-error">❌ Exceção: ' . $e->getMessage() . '</div>';
            }
        }
        ?>

        <div class="status-box">
            <h3>📚 Status Possíveis</h3>
            <ul>
                <li><strong>sent</strong> - Mensagem enviada com sucesso</li>
                <li><strong>delivered</strong> - Mensagem entregue ao WhatsApp</li>
                <li><strong>read</strong> - Mensagem lida pelo destinatário</li>
                <li><strong>failed</strong> - Falha na entrega</li>
                <li><strong>pending</strong> - Pendente de processamento</li>
            </ul>
        </div>
    </div>
</body>
</html> 