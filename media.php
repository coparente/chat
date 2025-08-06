<?php
/**
 * 📁 ENDPOINT PARA SERVIR ARQUIVOS DO MINIO
 * 
 * URL: https://seu-dominio.com/media.php/document/2025/arquivo.pdf
 * ou: https://seu-dominio.com/media.php?file=document/2025/arquivo.pdf
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar erros para não corromper arquivos

require_once 'config/app.php';
require_once 'app/Libraries/MinioHelper.php';
require_once 'app/Libraries/Database.php';

/**
 * Função para verificar se usuário tem acesso ao arquivo
 */
function verificarAcesso($caminhoArquivo) {
    // Se não está logado, negar acesso
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    try {
        $db = new Database();
        
        // Admins têm acesso total
        if (isset($_SESSION['usuario_perfil']) && ($_SESSION['usuario_perfil'] === 'admin' || $_SESSION['usuario_perfil'] === 'supervisor' || $_SESSION['usuario_perfil'] === 'analista')) {
            return true;
        }
        
        // CORREÇÃO: Criar parâmetros distintos para cada uso do caminho
        // Verificar se o usuário tem acesso a alguma conversa que contenha este arquivo
        $sql = "SELECT DISTINCT c.atendente_id 
                FROM mensagens m 
                INNER JOIN conversas c ON m.conversa_id = c.id 
                WHERE (m.midia_url = :caminho1 OR m.conteudo = :caminho2)
                AND c.atendente_id = :usuario_id
                LIMIT 1";
        
        $db->query($sql);
        $db->bind(':caminho1', $caminhoArquivo);
        $db->bind(':caminho2', $caminhoArquivo);
        $db->bind(':usuario_id', $_SESSION['usuario_id']);
        
        return $db->resultado() !== false;
        
    } catch (Exception $e) {
        error_log("Erro ao verificar acesso: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para servir arquivo
 */
function servirArquivo($caminhoArquivo) {
    // Verificar acesso
    if (!verificarAcesso($caminhoArquivo)) {
        http_response_code(403);
        
        // Se não está logado, redirecionar para login
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . URL_BASE . '/login');
            exit;
        }
        
        echo "❌ Acesso negado";
        exit;
    }
    
    // Inicializar MinIO
    if (!MinioHelper::init()) {
        http_response_code(500);
        echo "❌ Erro interno do servidor";
        exit;
    }
    
    // Baixar arquivo do MinIO
    $resultado = MinioHelper::acessoDirecto($caminhoArquivo);
    
    if (!$resultado['sucesso']) {
        http_response_code(404);
        echo "❌ Arquivo não encontrado";
        exit;
    }
    
    // Limpar qualquer buffer de saída
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Headers apropriados
    header('Content-Type: ' . $resultado['content_type']);
    header('Content-Length: ' . $resultado['tamanho']);
    header('Cache-Control: private, max-age=3600');
    header('X-Content-Type-Options: nosniff');
    
    // Nome do arquivo
    $nomeArquivo = basename($caminhoArquivo);
    
    // Para documentos, forçar download; para mídia, permitir visualização
    if (strpos($resultado['content_type'], 'application/') === 0 || 
        strpos($resultado['content_type'], 'text/') === 0) {
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
    } else {
        header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
    }
    
    // Log de acesso
    error_log("📁 Arquivo servido via media.php: {$caminhoArquivo} (Usuário: {$_SESSION['usuario_id']})");
    
    // Servir arquivo
    echo $resultado['dados'];
    exit;
}

// =====================================================
// PROCESSAMENTO DA REQUISIÇÃO
// =====================================================

try {
    // Método 1: Via PATH_INFO (/media.php/document/2025/arquivo.pdf)
    if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
        $caminhoArquivo = ltrim($_SERVER['PATH_INFO'], '/');
        servirArquivo($caminhoArquivo);
    }
    
    // Método 2: Via parâmetro GET (?file=document/2025/arquivo.pdf)
    if (isset($_GET['file']) && !empty($_GET['file'])) {
        $caminhoArquivo = $_GET['file'];
        servirArquivo($caminhoArquivo);
    }
    
    // Método 3: Via URI parsing (para URLs como /media/document/2025/arquivo.pdf)
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    
    // Remover parâmetros GET da URI
    $requestUri = strtok($requestUri, '?');
    
    // Se a URL contém /media/, extrair o caminho
    if (strpos($requestUri, '/media/') !== false) {
        $parts = explode('/media/', $requestUri, 2);
        if (count($parts) === 2 && !empty($parts[1])) {
            $caminhoArquivo = $parts[1];
            servirArquivo($caminhoArquivo);
        }
    }
    
    // Se chegou até aqui, mostrar ajuda
    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Servidor de Mídia - MinIO</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .container { max-width: 600px; margin: 0 auto; }
            .alert { padding: 15px; border-radius: 5px; margin: 20px 0; }
            .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
            .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
            code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
            ul { line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>📁 Servidor de Mídia - MinIO</h1>
            
            <div class="alert alert-warning">
                <strong>⚠️ Uso Incorreto</strong><br>
                Este endpoint requer um caminho de arquivo válido.
            </div>
            
            <h3>💡 Formatos de URL Suportados:</h3>
            <ul>
                <li><code>https://seu-dominio.com/media.php/document/2025/arquivo.pdf</code></li>
                <li><code>https://seu-dominio.com/media.php?file=document/2025/arquivo.pdf</code></li>
                <li><code>https://seu-dominio.com/media/document/2025/arquivo.pdf</code> (via rewrite)</li>
            </ul>
            
            <div class="alert alert-info">
                <strong>🔐 Autenticação Necessária</strong><br>
                Você deve estar logado no sistema e ter permissão para acessar o arquivo solicitado.
            </div>
            
            <h3>🔗 URLs Alternativas do Sistema:</h3>
            <ul>
                <li><strong>Via Controller:</strong> <code>/chat/visualizarMidiaMinIO/caminho%2Fdo%2Farquivo</code></li>
                <li><strong>URL Fresca:</strong> <code>/chat/gerarUrlFresca/caminho%2Fdo%2Farquivo</code></li>
            </ul>
        </div>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    error_log("Erro no media.php: " . $e->getMessage());
    http_response_code(500);
    echo "❌ Erro interno do servidor";
}
?> 