<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro de Desenvolvimento</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .debug-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .debug-header {
            background: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .debug-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .debug-content {
            padding: 20px;
        }
        .error-section {
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            overflow: hidden;
        }
        .error-section h3 {
            background: #f8f9fa;
            margin: 0;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            font-size: 16px;
            color: #495057;
        }
        .error-section-content {
            padding: 15px;
        }
        .error-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .error-info strong {
            color: #856404;
        }
        .stack-trace {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            overflow-x: auto;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .request-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .request-info strong {
            color: #0056b3;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error-type {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="debug-header">
            <h1>🐛 Erro de Desenvolvimento</h1>
            <p>Este erro é exibido apenas em ambiente de desenvolvimento</p>
        </div>
        
        <div class="debug-content">
            <div class="error-section">
                <h3>📋 Informações do Erro</h3>
                <div class="error-section-content">
                    <div class="error-info">
                        <strong>Tipo:</strong> <span class="error-type"><?php echo htmlspecialchars(get_class($exception)); ?></span><br>
                        <strong>Mensagem:</strong> <?php echo htmlspecialchars($exception->getMessage()); ?><br>
                        <strong>Arquivo:</strong> <?php echo htmlspecialchars($exception->getFile()); ?><br>
                        <strong>Linha:</strong> <?php echo $exception->getLine(); ?><br>
                        <strong>Código:</strong> <?php echo $exception->getCode(); ?>
                    </div>
                </div>
            </div>

            <div class="error-section">
                <h3>🌐 Informações da Requisição</h3>
                <div class="error-section-content">
                    <div class="request-info">
                        <strong>URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A'); ?><br>
                        <strong>Método:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A'); ?><br>
                        <strong>IP:</strong> <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A'); ?><br>
                        <strong>User Agent:</strong> <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A'); ?><br>
                        <strong>Data/Hora:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                    </div>
                </div>
            </div>

            <div class="error-section">
                <h3>📚 Stack Trace</h3>
                <div class="error-section-content">
                    <div class="stack-trace"><?php echo htmlspecialchars($exception->getTraceAsString()); ?></div>
                </div>
            </div>

            <a href="<?= URL ?>" class="btn">🏠 Voltar ao Início</a>
        </div>
    </div>
</body>
</html> 