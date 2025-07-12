<?php

/**
 * CONFIGURAR HEADER - TEMPLATE SERPRO
 * 
 * Este arquivo ajuda a configurar o header corretamente para evitar erro 403
 */

// Configuração
require_once 'config/app.php';
require_once 'app/libraries/Database.php';
require_once 'app/models/ConfiguracaoModel.php';
require_once 'app/libraries/SerproApi.php';

// URLs de teste (imagens públicas)
$urlsTeste = [
    'Logo WhatsApp' => 'https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg',
    'Logo GitHub' => 'https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png',
    'Imagem Teste' => 'https://via.placeholder.com/150x150/0075FF/FFFFFF?text=TESTE',
    'Logo Serpro' => 'https://www.serpro.gov.br/theme/serpro_portal_theme/images/logo.png'
];

function testarUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Header - Template Serpro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn:hover { opacity: 0.8; }
        .url-test { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configurar Header do Template</h1>
        
        <div class="alert alert-warning">
            <strong>⚠️ Problema Identificado:</strong><br>
            O erro 403 ocorre porque a URL da imagem no header não está acessível.<br>
            Vamos testar URLs válidas e configurar corretamente.
        </div>

        <!-- Testar URLs -->
        <div class="url-test">
            <h3>🧪 Teste de URLs Disponíveis</h3>
            <?php foreach ($urlsTeste as $nome => $url): ?>
                <?php $status = testarUrl($url); ?>
                <p>
                    <strong><?= $nome ?>:</strong> 
                    <span class="<?= $status == 200 ? 'status-ok' : 'status-error' ?>">
                        HTTP <?= $status ?> <?= $status == 200 ? '✅' : '❌' ?>
                    </span>
                    <br>
                    <small style="color: #666;"><?= $url ?></small>
                </p>
            <?php endforeach; ?>
        </div>

        <!-- Formulário de teste -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="url_imagem">URL da Imagem para Header:</label>
                <select name="url_imagem" id="url_imagem" class="form-group input" style="width: 100%; padding: 8px;">
                    <option value="">Selecione uma URL testada</option>
                    <?php foreach ($urlsTeste as $nome => $url): ?>
                        <?php if (testarUrl($url) == 200): ?>
                            <option value="<?= $url ?>" <?= ($_POST['url_imagem'] ?? '') === $url ? 'selected' : '' ?>>
                                <?= $nome ?> ✅
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="filename">Nome do Arquivo:</label>
                <input type="text" id="filename" name="filename" 
                       value="<?= $_POST['filename'] ?? 'logo.png' ?>" 
                       placeholder="logo.png" required>
            </div>
            
            <button type="submit" name="testar_header" class="btn">🧪 Testar Header</button>
            <button type="submit" name="aplicar_configuracao" class="btn btn-success">✅ Aplicar Configuração</button>
        </form>

        <?php
        if (isset($_POST['testar_header']) || isset($_POST['aplicar_configuracao'])) {
            $urlImagem = $_POST['url_imagem'];
            $filename = $_POST['filename'];
            
            if (empty($urlImagem)) {
                echo '<div class="alert alert-error">❌ Selecione uma URL válida</div>';
            } else {
                echo '<div class="url-test">';
                echo '<h3>🧪 Teste do Header Personalizado</h3>';
                echo '<p><strong>URL:</strong> ' . htmlspecialchars($urlImagem) . '</p>';
                echo '<p><strong>Filename:</strong> ' . htmlspecialchars($filename) . '</p>';
                
                // Testar status da URL
                $statusUrl = testarUrl($urlImagem);
                if ($statusUrl == 200) {
                    echo '<div class="alert alert-success">✅ URL acessível (HTTP 200)</div>';
                    
                    if (isset($_POST['testar_header'])) {
                        // Enviar template de teste
                        try {
                            $serproApi = new SerproApi();
                            
                            $parametros = [
                                [
                                    'tipo' => 'text',
                                    'valor' => 'Teste com header personalizado - ' . date('H:i:s')
                                ]
                            ];
                            
                            $header = [
                                'filename' => $filename,
                                'linkMedia' => $urlImagem
                            ];
                            
                            $resultado = $serproApi->enviarTemplate(
                                '5562996185892',
                                'central_intimacao_remota',
                                $parametros,
                                $header
                            );
                            
                            if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
                                echo '<div class="alert alert-success">✅ Template com header enviado com sucesso!</div>';
                                echo '<p><strong>ID:</strong> ' . ($resultado['response']['id'] ?? 'N/A') . '</p>';
                            } else {
                                echo '<div class="alert alert-error">❌ Erro ao enviar template</div>';
                                echo '<p>' . ($resultado['error'] ?? 'Erro desconhecido') . '</p>';
                            }
                            
                        } catch (Exception $e) {
                            echo '<div class="alert alert-error">❌ Exceção: ' . $e->getMessage() . '</div>';
                        }
                    }
                    
                    if (isset($_POST['aplicar_configuracao'])) {
                        // Aplicar configuração permanente
                        echo '<div class="alert alert-success">✅ Configuração aplicada!</div>';
                        echo '<p>Agora edite o arquivo <code>app/Libraries/SerproApi.php</code> linha ~165:</p>';
                        echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 5px;">';
                        echo htmlspecialchars('$payload[\'header\'] = [
    \'filename\' => "' . $filename . '",
    \'linkMedia\' => "' . $urlImagem . '"
];');
                        echo '</pre>';
                    }
                } else {
                    echo '<div class="alert alert-error">❌ URL não acessível (HTTP ' . $statusUrl . ')</div>';
                }
                
                echo '</div>';
            }
        }
        ?>

        <!-- Opções de configuração -->
        <div class="url-test">
            <h3>⚙️ Opções de Configuração</h3>
            
            <h4>1. 🚀 Solução Rápida (Sem Imagem)</h4>
            <p>Templates enviados apenas com texto, sem header de imagem.</p>
            <a href="test_template_sem_header.php" class="btn">Testar Sem Header</a>
            
            <h4>2. 🎨 Solução Completa (Com Imagem)</h4>
            <p>Use uma das URLs testadas acima que retornou HTTP 200.</p>
            
            <h4>3. 🏠 Solução Local</h4>
            <p>Faça upload de uma imagem para seu servidor e use a URL local:</p>
            <ul>
                <li>Crie a pasta: <code>public/img/</code></li>
                <li>Faça upload de: <code>logo.png</code></li>
                <li>URL final: <code><?= URL ?>/public/img/logo.png</code></li>
            </ul>
        </div>
    </div>
</body>
</html> 