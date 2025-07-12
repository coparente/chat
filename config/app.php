<?php
// Carrega o Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad(); // safeLoad não gera erro se .env não existir

define('APP', dirname(__FILE__));
define('APPROOT', dirname(__FILE__));

/**
 * Configurações do Banco de Dados
 */
define('HOST', $_ENV['DEV_DB_HOST'] ?? getenv('PROD_DB_HOST') ?: 'localhost');
define('PORTA', intval($_ENV['DEV_DB_PORT'] ?? getenv('PROD_DB_PORT') ?: 3306));
define('BANCO', $_ENV['DEV_DB_NAME'] ?? getenv('PROD_DB_NAME') ?: 'copare52_chat');
define('USUARIO', $_ENV['DEV_DB_USERNAME'] ?? getenv('PROD_DB_USERNAME') ?: 'copare52_chat');
define('SENHA', $_ENV['DEV_DB_PASSWORD'] ?? getenv('PROD_DB_PASSWORD') ?: 'YiYDW*3vLLKk');

/**
 * Configurações da Aplicação
 */
define('APP_NOME', $_ENV['APP_NAME'] ?? 'meu-framework');
define('APP_VERSAO', $_ENV['APP_VERSION'] ?? '1.0');
define('URL', $_ENV['APP_URL'] ?? 'http://localhost/meu-framework');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');

/**
 * Configurações de Segurança
 */
define('HASH_COST', intval($_ENV['HASH_COST'] ?? 10));
define('TOKEN_EXPIRACAO', intval($_ENV['TOKEN_EXPIRATION'] ?? 86400)); // 24 horas
define('SESSAO_TEMPO_LIMITE', intval($_ENV['SESSION_TIMEOUT'] ?? 1800)); // 30 minutos
define('SESSAO_NOME', $_ENV['SESSION_NAME'] ?? 'meu_framework_session');

/**
 * Configurações da API do Google AI
 * IMPORTANTE: Mova sua chave real para o arquivo .env
 */
define('API_KEY', $_ENV['GOOGLE_API_KEY'] ?? '');

/**
 * Configurações da API do Serpro
 * IMPORTANTE: Mova suas credenciais reais para o arquivo .env
 */
define('SERPRO_CLIENT_ID', $_ENV['SERPRO_CLIENT_ID'] ?? '');
define('SERPRO_CLIENT_SECRET', $_ENV['SERPRO_CLIENT_SECRET'] ?? '');
define('SERPRO_BASE_URL', $_ENV['SERPRO_BASE_URL'] ?? 'https://api.whatsapp.serpro.gov.br');
define('SERPRO_WABA_ID', $_ENV['SERPRO_WABA_ID'] ?? '');
define('SERPRO_PHONE_NUMBER_ID', $_ENV['SERPRO_PHONE_NUMBER_ID'] ?? '');
define('WEBHOOK_VERIFY_TOKEN', $_ENV['WEBHOOK_VERIFY_TOKEN'] ?? '');

/**
 * Configurações da API do DataJud
 * IMPORTANTE: Mova sua chave real para o arquivo .env
 */
define('API_KEY_DATAJUD', $_ENV['DATAJUD_API_KEY'] ?? '');
define('URL_DATAJUD', $_ENV['DATAJUD_URL'] ?? 'https://api-publica.datajud.cnj.jus.br/api_publica_tjgo/_search');

/**
 * Rotas Padrão
 */
define('CONTROLLER', 'Login');
define('METODO', 'login');

/**
 * Configurações de Email
 */
define('EMAIL_ADMIN', $_ENV['EMAIL_ADMIN'] ?? 'admin@tjgo.jus.br');
define('EMAIL_SISTEMA', $_ENV['EMAIL_SISTEMA'] ?? 'sistema@tjgo.jus.br');

/**
 * Configurações de Upload
 */
define('UPLOAD_MAX_SIZE', intval($_ENV['UPLOAD_MAX_SIZE'] ?? 41943040)); // 40MB
define('UPLOAD_TIPOS_PERMITIDOS', ['txt']);
define('UPLOAD_DIR', $_ENV['UPLOAD_DIR'] ?? 'uploads/');

/**
 * Níveis de Acesso
 */
define('PERFIL_USUARIO', 'usuario');
define('PERFIL_ANALISTA', 'analista');
define('PERFIL_ADMIN', 'admin');

/**
 * Status de Usuário
 */
define('STATUS_ATIVO', 'ativo');
define('STATUS_INATIVO', 'inativo');

/**
 * Configurações de Paginação
 */
define('ITENS_POR_PAGINA', 20);
define('MAX_PAGINAS_NAVEGACAO', 5);

/**
 * Configurações de Timezone
 */
date_default_timezone_set('America/Sao_Paulo');

/**
 * Configurações de Segurança Avançadas
 */
// Headers de segurança
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // CSP básico - ajuste conforme necessário
    if (APP_ENV === 'production') {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;");
    }
}

/**
 * Inicialização do sistema de CSRF
 */
function iniciarCsrf() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Verificar token CSRF
 */
function verificarCsrf($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obter token CSRF
 */
function obterCsrf() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['csrf_token'] ?? '';
}

// Inicializar CSRF automaticamente
iniciarCsrf();
