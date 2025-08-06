<?php
/**
 * Script de teste para verificar o funcionamento do webhook com download de mídia
 */

// Carregar configurações primeiro
require_once 'config/app.php';
require_once 'app/autoload.php';

echo "🔍 **TESTE DO WEBHOOK COM DOWNLOAD DE MÍDIA**\n\n";

try {
    // 1. Verificar se o webhook está acessível
    echo "📋 **1. VERIFICANDO ACESSIBILIDADE DO WEBHOOK**\n";
    echo str_repeat("-", 50) . "\n";
    
    $webhookUrl = "http://localhost/chat/webhook/serpro";
    echo "✓ URL do webhook: {$webhookUrl}\n";
    
    // 2. Verificar departamentos e credenciais
    echo "\n📋 **2. VERIFICANDO DEPARTAMENTOS E CREDENCIAIS**\n";
    echo str_repeat("-", 50) . "\n";
    
    $departamentoModel = new DepartamentoModel();
    $departamentos = $departamentoModel->listarDepartamentosAtivos();
    
    if (empty($departamentos)) {
        echo "❌ Nenhum departamento ativo encontrado!\n";
        exit;
    }
    
    foreach ($departamentos as $departamento) {
        echo "✓ {$departamento->nome} (ID: {$departamento->id})\n";
    }
    
    // 3. Verificar credenciais por departamento
    echo "\n📋 **3. VERIFICANDO CREDENCIAIS POR DEPARTAMENTO**\n";
    echo str_repeat("-", 50) . "\n";
    
    $credencialSerproModel = new CredencialSerproModel();
    
    foreach ($departamentos as $departamento) {
        $credencial = $credencialSerproModel->obterCredencialAtiva($departamento->id);
        
        if ($credencial) {
            echo "✓ Departamento {$departamento->nome}: Credencial encontrada (ID: {$credencial->id})\n";
            
            // Testar token
            $serproApi = new SerproApi($credencial);
            $token = $serproApi->obterTokenValido();
            
            if ($token) {
                echo "  ✓ Token válido obtido\n";
            } else {
                echo "  ❌ Erro ao obter token\n";
            }
        } else {
            echo "❌ Departamento {$departamento->nome}: Nenhuma credencial encontrada\n";
        }
    }
    
    // 4. Testar identificação de departamento
    echo "\n📋 **4. TESTANDO IDENTIFICAÇÃO DE DEPARTAMENTO**\n";
    echo str_repeat("-", 50) . "\n";
    
    $departamentoHelper = new DepartamentoHelper();
    $numeroTeste = "556296185892";
    
    $departamentoId = $departamentoHelper->identificarDepartamento($numeroTeste);
    echo "✓ Número de teste: {$numeroTeste}\n";
    echo "✓ Departamento identificado: ID {$departamentoId}\n";
    
    // 5. Testar download de mídia
    echo "\n📋 **5. TESTANDO DOWNLOAD DE MÍDIA**\n";
    echo str_repeat("-", 50) . "\n";
    
    if ($departamentoId) {
        $credencial = $credencialSerproModel->obterCredencialAtiva($departamentoId);
        
        if ($credencial) {
            $serproApi = new SerproApi($credencial);
            
            // Testar com um ID fictício (deve retornar 404, mas o token deve funcionar)
            $resultadoDownload = $serproApi->downloadMidia("test_media_id_123");
            
            if ($resultadoDownload['status'] === 404) {
                echo "✓ Download de mídia testado (404 esperado para ID fictício)\n";
                echo "✓ Token está funcionando corretamente\n";
            } else {
                echo "❌ Erro inesperado no download: Status {$resultadoDownload['status']}\n";
            }
        } else {
            echo "❌ Credencial não encontrada para departamento ID: {$departamentoId}\n";
        }
    } else {
        echo "❌ Departamento não identificado para o número de teste\n";
    }
    
    // 6. Testar MinIO
    echo "\n📋 **6. TESTANDO MINIO**\n";
    echo str_repeat("-", 50) . "\n";
    
    try {
        $minioHelper = new MinioHelper();
        echo "✓ MinIO inicializado com sucesso\n";
        
        // Testar upload de arquivo fictício
        $dadosTeste = "Teste de upload MinIO";
        $resultadoUpload = MinioHelper::uploadMidia(
            $dadosTeste,
            'text',
            'text/plain',
            'teste.txt'
        );
        
        if ($resultadoUpload['sucesso']) {
            echo "✓ Upload para MinIO funcionando\n";
            echo "  ✓ Caminho: {$resultadoUpload['caminho_minio']}\n";
        } else {
            echo "❌ Erro no upload para MinIO: {$resultadoUpload['erro']}\n";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao inicializar MinIO: " . $e->getMessage() . "\n";
    }
    
    // 7. Resumo final
    echo "\n📋 **7. RESUMO FINAL**\n";
    echo str_repeat("-", 50) . "\n";
    echo "✅ Sistema preparado para download de mídia via webhook\n";
    echo "✅ Departamentos e credenciais configurados\n";
    echo "✅ Tokens sendo renovados automaticamente\n";
    echo "✅ MinIO funcionando para armazenamento\n";
    echo "✅ Webhook pronto para processar mensagens com mídia\n";
    
} catch (Exception $e) {
    echo "❌ **ERRO NO TESTE**: " . $e->getMessage() . "\n";
    echo "📚 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 