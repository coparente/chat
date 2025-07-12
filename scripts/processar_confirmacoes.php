<?php

/**
 * [ PROCESSAR CONFIRMAÇÕES ] - Script para processar confirmações de leitura pendentes
 * 
 * Este script deve ser executado periodicamente (a cada minuto) via cron job
 * para processar as confirmações de leitura que foram agendadas.
 * 
 * Cron job sugerido:
 * * * * * * php /caminho/para/projeto/scripts/processar_confirmacoes.php
 */

// Definir constantes do projeto
define('ROOT', dirname(__DIR__));

// Incluir configuração e autoload
require_once ROOT . '/config/app.php';
require_once ROOT . '/app/autoload.php';

/**
 * [ processarConfirmacoesPendentes ] - Processa confirmações pendentes
 */
function processarConfirmacoesPendentes()
{
    try {
        $confirmacaoFile = ROOT . '/logs/confirmacoes_pendentes.json';
        
        if (!file_exists($confirmacaoFile)) {
            return;
        }
        
        // Ler confirmações pendentes
        $confirmacoes = json_decode(file_get_contents($confirmacaoFile), true);
        
        if (!$confirmacoes || !is_array($confirmacoes)) {
            return;
        }
        
        $agora = time();
        $confirmacoesPendentes = [];
        $processadas = 0;
        
        foreach ($confirmacoes as $confirmacao) {
            if ($confirmacao['scheduled_time'] <= $agora) {
                // Processar confirmação
                if (confirmarLeituraMensagem($confirmacao['message_id'])) {
                    $processadas++;
                    logProcessamento("Confirmação de leitura processada: " . $confirmacao['message_id']);
                } else {
                    // Se falhou, reagendar para 1 minuto
                    $confirmacao['scheduled_time'] = $agora + 60;
                    $confirmacoesPendentes[] = $confirmacao;
                    logProcessamento("Falha ao processar confirmação, reagendada: " . $confirmacao['message_id']);
                }
            } else {
                // Ainda não chegou a hora
                $confirmacoesPendentes[] = $confirmacao;
            }
        }
        
        // Salvar confirmações ainda pendentes
        file_put_contents($confirmacaoFile, json_encode($confirmacoesPendentes, JSON_PRETTY_PRINT));
        
        if ($processadas > 0) {
            logProcessamento("Processadas {$processadas} confirmações de leitura");
        }
        
    } catch (Exception $e) {
        logProcessamento("Erro ao processar confirmações: " . $e->getMessage());
    }
}

/**
 * [ confirmarLeituraMensagem ] - Confirma leitura de uma mensagem específica
 */
function confirmarLeituraMensagem($messageId)
{
    try {
        // Instanciar API Serpro
        $serproApi = new SerproApi();
        
        // Verificar se API está configurada
        if (!$serproApi->isConfigured()) {
            logProcessamento("API Serpro não está configurada");
            return false;
        }
        
        // Confirmar leitura via API real
        $resultado = $serproApi->marcarComoLida($messageId);
        
        if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
            $logData = [
                'message_id' => $messageId,
                'status' => 'read',
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'auto_confirm_processed',
                'response' => $resultado['response']
            ];
            
            logProcessamento("Confirmação de leitura enviada com sucesso: " . json_encode($logData));
            return true;
        } else {
            $logData = [
                'message_id' => $messageId,
                'status' => 'read',
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'auto_confirm_error',
                'error' => $resultado['error']
            ];
            
            logProcessamento("Erro na confirmação de leitura: " . json_encode($logData));
            return false;
        }
        
    } catch (Exception $e) {
        logProcessamento("Erro ao confirmar leitura da mensagem {$messageId}: " . $e->getMessage());
        return false;
    }
}

/**
 * [ logProcessamento ] - Log das operações de processamento
 */
function logProcessamento($mensagem)
{
    $logFile = ROOT . '/logs/processamento_confirmacoes_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    
    file_put_contents($logFile, "[{$timestamp}] {$mensagem}\n", FILE_APPEND | LOCK_EX);
    
    // Também exibir no console se executado manualmente
    if (php_sapi_name() === 'cli') {
        echo "[{$timestamp}] {$mensagem}\n";
    }
}

/**
 * [ limpezaLogs ] - Limpa logs antigos (executar semanalmente)
 */
function limpezaLogs()
{
    try {
        $logsDir = ROOT . '/logs';
        $arquivos = glob($logsDir . '/processamento_confirmacoes_*.log');
        
        foreach ($arquivos as $arquivo) {
            $dataArquivo = filemtime($arquivo);
            $diasAtras = (time() - $dataArquivo) / (24 * 60 * 60);
            
            // Remover logs com mais de 30 dias
            if ($diasAtras > 30) {
                unlink($arquivo);
                logProcessamento("Log antigo removido: " . basename($arquivo));
            }
        }
        
    } catch (Exception $e) {
        logProcessamento("Erro na limpeza de logs: " . $e->getMessage());
    }
}

// Executar processamento
if (php_sapi_name() === 'cli') {
    logProcessamento("Iniciando processamento de confirmações...");
    processarConfirmacoesPendentes();
    
    // Limpeza de logs (executar apenas uma vez por dia)
    $ultimaLimpeza = ROOT . '/logs/.ultima_limpeza';
    if (!file_exists($ultimaLimpeza) || (time() - filemtime($ultimaLimpeza)) > 86400) {
        limpezaLogs();
        touch($ultimaLimpeza);
    }
    
    logProcessamento("Processamento concluído.");
} else {
    echo "Este script deve ser executado via linha de comando (CLI).\n";
}

?> 