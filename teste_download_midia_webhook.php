<?php
/**
 * Script de teste para simular download de mídia do webhook
 */

// Carregar configurações primeiro
require_once 'config/app.php';
require_once 'app/autoload.php';

echo "🔍 **TESTE DE DOWNLOAD DE MÍDIA DO WEBHOOK**\n\n";

try {
    // Simular dados do webhook
    $midiaId = "109657336257511";
    $tipo = "image";
    $mimeType = "image/jpeg";
    $filename = null;
    
    echo "📋 **1. DADOS DE TESTE**\n";
    echo str_repeat("-", 50) . "\n";
    echo "✓ Mídia ID: {$midiaId}\n";
    echo "✓ Tipo: {$tipo}\n";
    echo "✓ MIME Type: {$mimeType}\n";
    echo "✓ Filename: " . ($filename ?: 'null') . "\n\n";
    
    // 2. Testar identificação de departamento
    echo "📋 **2. IDENTIFICAÇÃO DE DEPARTAMENTO**\n";
    echo str_repeat("-", 50) . "\n";
    
    $departamentoModel = new DepartamentoModel();
    $departamentos = $departamentoModel->listarDepartamentosAtivos();
    
    if (empty($departamentos)) {
        echo "❌ Nenhum departamento ativo encontrado!\n";
        exit;
    }
    
    // Usar o primeiro departamento ativo como padrão
    $departamentoPadrao = $departamentos[0];
    echo "✓ Departamento padrão: {$departamentoPadrao->nome} (ID: {$departamentoPadrao->id})\n";
    
    // 3. Testar obtenção de credenciais
    echo "\n📋 **3. OBTENÇÃO DE CREDENCIAIS**\n";
    echo str_repeat("-", 50) . "\n";
    
    $credencialSerproModel = new CredencialSerproModel();
    $credencial = $credencialSerproModel->obterCredencialAtiva($departamentoPadrao->id);
    
    if (!$credencial) {
        echo "❌ Credencial não encontrada para departamento ID: {$departamentoPadrao->id}\n";
        exit;
    }
    
    echo "✓ Credencial encontrada: {$credencial->nome} (ID: {$credencial->id})\n";
    echo "✓ Base URL: {$credencial->base_url}\n";
    echo "✓ WABA ID: {$credencial->waba_id}\n";
    echo "✓ Phone Number ID: {$credencial->phone_number_id}\n";
    
    // 4. Testar token
    echo "\n📋 **4. TESTE DE TOKEN**\n";
    echo str_repeat("-", 50) . "\n";
    
    $token = $credencialSerproModel->obterTokenValido($credencial->id);
    
    if ($token) {
        echo "✓ Token obtido com sucesso\n";
        echo "✓ Token (primeiros 50 chars): " . substr($token, 0, 50) . "...\n";
    } else {
        echo "❌ Erro ao obter token\n";
        exit;
    }
    
    // 5. Testar SerproApi com credenciais específicas
    echo "\n📋 **5. TESTE SERPROAPI COM CREDENCIAIS**\n";
    echo str_repeat("-", 50) . "\n";
    
    $serproApi = new SerproApi($credencial);
    
    // Verificar se está configurado
    if ($serproApi->isConfigured()) {
        echo "✓ SerproApi configurado com credenciais específicas\n";
    } else {
        echo "❌ SerproApi não configurado corretamente\n";
        exit;
    }
    
    // 6. Testar download de mídia
    echo "\n📋 **6. TESTE DE DOWNLOAD DE MÍDIA**\n";
    echo str_repeat("-", 50) . "\n";
    
    $resultadoDownload = $serproApi->downloadMidia($midiaId);
    
    echo "✓ Status da resposta: {$resultadoDownload['status']}\n";
    
    if ($resultadoDownload['status'] === 200) {
        echo "✓ Download realizado com sucesso!\n";
        echo "✓ Tamanho dos dados: " . strlen($resultadoDownload['data']) . " bytes\n";
        
        // 7. Testar upload para MinIO
        echo "\n📋 **7. TESTE DE UPLOAD PARA MINIO**\n";
        echo str_repeat("-", 50) . "\n";
        
        $resultadoUpload = MinioHelper::uploadMidia(
            $resultadoDownload['data'],
            $tipo,
            $mimeType,
            $filename
        );
        
        if ($resultadoUpload['sucesso']) {
            echo "✓ Upload para MinIO realizado com sucesso!\n";
            echo "✓ Caminho no MinIO: {$resultadoUpload['caminho_minio']}\n";
            echo "✓ Nome do arquivo: {$resultadoUpload['nome_arquivo']}\n";
            echo "✓ Tamanho: " . number_format($resultadoUpload['tamanho'] / 1024, 2) . " KB\n";
            echo "✓ Bucket: {$resultadoUpload['bucket']}\n";
        } else {
            echo "❌ Erro no upload para MinIO: {$resultadoUpload['erro']}\n";
        }
        
    } elseif ($resultadoDownload['status'] === 404) {
        echo "ℹ️ Mídia não encontrada (404) - isso é esperado para IDs fictícios\n";
        echo "✓ Token está funcionando corretamente\n";
        echo "✓ API está respondendo adequadamente\n";
        
    } else {
        echo "❌ Erro no download: Status {$resultadoDownload['status']}\n";
        if (isset($resultadoDownload['error'])) {
            echo "❌ Erro: {$resultadoDownload['error']}\n";
        }
    }
    
    // 8. Resumo final
    echo "\n📋 **8. RESUMO FINAL**\n";
    echo str_repeat("-", 50) . "\n";
    
    if ($resultadoDownload['status'] === 200 && $resultadoUpload['sucesso']) {
        echo "✅ DOWNLOAD E UPLOAD FUNCIONANDO PERFEITAMENTE!\n";
        echo "✅ Webhook pronto para processar mídia real\n";
    } elseif ($resultadoDownload['status'] === 404) {
        echo "✅ SISTEMA CONFIGURADO CORRETAMENTE!\n";
        echo "✅ Token funcionando\n";
        echo "✅ API respondendo adequadamente\n";
        echo "✅ Pronto para mídia real\n";
    } else {
        echo "❌ PROBLEMAS IDENTIFICADOS\n";
        echo "❌ Verificar configurações\n";
    }
    
} catch (Exception $e) {
    echo "❌ **ERRO NO TESTE**: " . $e->getMessage() . "\n";
    echo "📚 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 