<?php

/**
 * [ HANDLER ] - Sistema centralizado de tratamento de exceções
 * 
 * Esta classe gerencia todas as exceções do sistema, fornecendo
 * tratamento adequado para diferentes tipos de erro e ambientes.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class Handler
{
    /**
     * Registra o handler de exceções
     */
    public static function register()
    {
        set_exception_handler([self::class, 'handle']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Trata exceções não capturadas
     * 
     * @param Throwable $exception
     * @return void
     */
    public static function handle($exception)
    {
        // Log da exceção
        self::logException($exception);

        // Verifica se é uma requisição AJAX
        if (self::isAjaxRequest()) {
            self::renderAjaxError($exception);
            return;
        }

        // Renderiza página de erro baseada no ambiente
        if (APP_ENV === 'production') {
            self::renderProductionError($exception);
        } else {
            self::renderDevelopmentError($exception);
        }
    }

    /**
     * Trata erros PHP
     * 
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public static function handleError($level, $message, $file, $line)
    {
        if (error_reporting() === 0) {
            return false;
        }

        $exception = new ErrorException($message, 0, $level, $file, $line);
        self::handle($exception);

        return true;
    }

    /**
     * Trata erros fatais
     */
    public static function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::handle($exception);
        }
    }

    /**
     * Registra exceção no log
     * 
     * @param Throwable $exception
     */
    private static function logException($exception)
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
        ];

        $logMessage = json_encode($logData, JSON_PRETTY_PRINT);
        
        $logFile = __DIR__ . '/../../logs/exceptions.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage . PHP_EOL . str_repeat('-', 80) . PHP_EOL, FILE_APPEND);
    }

    /**
     * Verifica se é uma requisição AJAX
     * 
     * @return bool
     */
    private static function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Renderiza erro para requisições AJAX
     * 
     * @param Throwable $exception
     */
    private static function renderAjaxError($exception)
    {
        http_response_code(500);
        header('Content-Type: application/json');
        
        $response = [
            'error' => true,
            'message' => APP_ENV === 'production' ? 'Erro interno do servidor' : $exception->getMessage(),
            'code' => $exception->getCode()
        ];

        if (APP_ENV !== 'production') {
            $response['debug'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            ];
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Renderiza página de erro para produção
     * 
     * @param Throwable $exception
     */
    private static function renderProductionError($exception)
    {
        http_response_code(500);
        
        if (file_exists(__DIR__ . '/../Views/errors/500.php')) {
            require __DIR__ . '/../Views/errors/500.php';
        } else {
            echo '<h1>Erro Interno do Servidor</h1>';
            echo '<p>Ocorreu um erro inesperado. Tente novamente mais tarde.</p>';
        }
        exit;
    }

    /**
     * Renderiza página de erro para desenvolvimento
     * 
     * @param Throwable $exception
     */
    private static function renderDevelopmentError($exception)
    {
        http_response_code(500);
        
        if (file_exists(__DIR__ . '/../Views/errors/debug.php')) {
            require __DIR__ . '/../Views/errors/debug.php';
        } else {
            echo '<h1>Erro de Desenvolvimento</h1>';
            echo '<h2>' . get_class($exception) . '</h2>';
            echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
            echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>';
            echo '<p><strong>Linha:</strong> ' . $exception->getLine() . '</p>';
            echo '<h3>Stack Trace:</h3>';
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        }
        exit;
    }
} 