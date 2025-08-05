<?php

/**
 * [ LOGGER ] - Sistema estruturado de logging
 * 
 * Esta classe fornece métodos para registrar logs de forma
 * estruturada e organizada por níveis de severidade.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class Logger
{
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    private static $instance = null;
    private $logPath;
    private $maxFiles = 30; // Manter apenas os últimos 30 arquivos
    private $maxSize = 10485760; // 10MB por arquivo

    private function __construct()
    {
        $this->logPath = __DIR__ . '/../../logs/';
        $this->ensureLogDirectory();
    }

    /**
     * Obtém instância singleton
     * 
     * @return Logger
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Garante que o diretório de logs existe
     */
    private function ensureLogDirectory()
    {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Registra log de emergência
     * 
     * @param string $message
     * @param array $context
     */
    public static function emergency($message, $context = [])
    {
        self::getInstance()->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Registra log de alerta
     * 
     * @param string $message
     * @param array $context
     */
    public static function alert($message, $context = [])
    {
        self::getInstance()->log(self::ALERT, $message, $context);
    }

    /**
     * Registra log crítico
     * 
     * @param string $message
     * @param array $context
     */
    public static function critical($message, $context = [])
    {
        self::getInstance()->log(self::CRITICAL, $message, $context);
    }

    /**
     * Registra log de erro
     * 
     * @param string $message
     * @param array $context
     */
    public static function error($message, $context = [])
    {
        self::getInstance()->log(self::ERROR, $message, $context);
    }

    /**
     * Registra log de aviso
     * 
     * @param string $message
     * @param array $context
     */
    public static function warning($message, $context = [])
    {
        self::getInstance()->log(self::WARNING, $message, $context);
    }

    /**
     * Registra log de notificação
     * 
     * @param string $message
     * @param array $context
     */
    public static function notice($message, $context = [])
    {
        self::getInstance()->log(self::NOTICE, $message, $context);
    }

    /**
     * Registra log informativo
     * 
     * @param string $message
     * @param array $context
     */
    public static function info($message, $context = [])
    {
        self::getInstance()->log(self::INFO, $message, $context);
    }

    /**
     * Registra log de debug
     * 
     * @param string $message
     * @param array $context
     */
    public static function debug($message, $context = [])
    {
        self::getInstance()->log(self::DEBUG, $message, $context);
    }

    /**
     * Registra log de acesso
     * 
     * @param string $message
     * @param array $context
     */
    public static function access($message, $context = [])
    {
        self::getInstance()->logToFile('access.log', $message, $context);
    }

    /**
     * Registra log de segurança
     * 
     * @param string $message
     * @param array $context
     */
    public static function security($message, $context = [])
    {
        self::getInstance()->logToFile('security.log', $message, $context);
    }

    /**
     * Registra log de banco de dados
     * 
     * @param string $message
     * @param array $context
     */
    public static function database($message, $context = [])
    {
        self::getInstance()->logToFile('database.log', $message, $context);
    }

    /**
     * Registra log de API
     * 
     * @param string $message
     * @param array $context
     */
    public static function api($message, $context = [])
    {
        self::getInstance()->logToFile('api.log', $message, $context);
    }

    /**
     * Registra log principal
     * 
     * @param int $level
     * @param string $message
     * @param array $context
     */
    private function log($level, $message, $context = [])
    {
        $logData = $this->formatLogEntry($level, $message, $context);
        $filename = $this->getLogFilename($level);
        
        $this->writeLog($filename, $logData);
        $this->rotateLogs($filename);
    }

    /**
     * Registra log em arquivo específico
     * 
     * @param string $filename
     * @param string $message
     * @param array $context
     */
    private function logToFile($filename, $message, $context = [])
    {
        $logData = $this->formatLogEntry(self::INFO, $message, $context);
        $this->writeLog($filename, $logData);
        $this->rotateLogs($filename);
    }

    /**
     * Formata entrada de log
     * 
     * @param int $level
     * @param string $message
     * @param array $context
     * @return string
     */
    private function formatLogEntry($level, $message, $context = [])
    {
        $levelName = $this->getLevelName($level);
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $levelName,
            'message' => $message,
            'context' => $context,
            'url' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'user_id' => $_SESSION['usuario_id'] ?? 'N/A',
            'session_id' => session_id() ?? 'N/A'
        ];

        return json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtém nome do nível de log
     * 
     * @param int $level
     * @return string
     */
    private function getLevelName($level)
    {
        $levels = [
            self::EMERGENCY => 'EMERGENCY',
            self::ALERT     => 'ALERT',
            self::CRITICAL  => 'CRITICAL',
            self::ERROR     => 'ERROR',
            self::WARNING   => 'WARNING',
            self::NOTICE    => 'NOTICE',
            self::INFO      => 'INFO',
            self::DEBUG     => 'DEBUG'
        ];

        return $levels[$level] ?? 'UNKNOWN';
    }

    /**
     * Obtém IP do cliente
     * 
     * @return string
     */
    private function getClientIp()
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'N/A';
    }

    /**
     * Obtém nome do arquivo de log
     * 
     * @param int $level
     * @return string
     */
    private function getLogFilename($level)
    {
        $levelName = strtolower($this->getLevelName($level));
        $date = date('Y-m-d');
        return "{$levelName}-{$date}.log";
    }

    /**
     * Escreve log no arquivo
     * 
     * @param string $filename
     * @param string $data
     */
    private function writeLog($filename, $data)
    {
        $filepath = $this->logPath . $filename;
        file_put_contents($filepath, $data . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotaciona arquivos de log
     * 
     * @param string $filename
     */
    private function rotateLogs($filename)
    {
        $filepath = $this->logPath . $filename;
        
        // Verifica se o arquivo existe e excede o tamanho máximo
        if (file_exists($filepath) && filesize($filepath) > $this->maxSize) {
            $this->rotateFile($filepath);
        }

        // Remove arquivos antigos
        $this->cleanOldLogs();
    }

    /**
     * Rotaciona arquivo de log
     * 
     * @param string $filepath
     */
    private function rotateFile($filepath)
    {
        $info = pathinfo($filepath);
        $rotatedFile = $info['dirname'] . '/' . $info['filename'] . '-' . date('Y-m-d-H-i-s') . '.' . $info['extension'];
        
        rename($filepath, $rotatedFile);
    }

    /**
     * Remove logs antigos
     */
    private function cleanOldLogs()
    {
        $files = glob($this->logPath . '*.log');
        
        if (count($files) > $this->maxFiles) {
            // Ordena por data de modificação (mais antigos primeiro)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Remove os arquivos mais antigos
            $filesToRemove = array_slice($files, 0, count($files) - $this->maxFiles);
            
            foreach ($filesToRemove as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Obtém logs recentes
     * 
     * @param string $level
     * @param int $limit
     * @return array
     */
    public static function getRecentLogs($level = null, $limit = 100)
    {
        $instance = self::getInstance();
        $logs = [];
        
        if ($level) {
            $filename = $instance->getLogFilename($level);
            $filepath = $instance->logPath . $filename;
            
            if (file_exists($filepath)) {
                $logs = $instance->readLogFile($filepath, $limit);
            }
        } else {
            $files = glob($instance->logPath . '*.log');
            foreach ($files as $file) {
                $logs = array_merge($logs, $instance->readLogFile($file, $limit));
            }
        }

        return $logs;
    }

    /**
     * Lê arquivo de log
     * 
     * @param string $filepath
     * @param int $limit
     * @return array
     */
    private function readLogFile($filepath, $limit)
    {
        $logs = [];
        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines) {
            $lines = array_slice($lines, -$limit);
            
            foreach ($lines as $line) {
                $log = json_decode($line, true);
                if ($log) {
                    $logs[] = $log;
                }
            }
        }

        return $logs;
    }

    /**
     * Limpa logs antigos por dias
     * 
     * @param int $days
     */
    public static function cleanOldLogsByDays($days = 30)
    {
        $instance = self::getInstance();
        $files = glob($instance->logPath . '*.log');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
} 