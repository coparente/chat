<?php

/**
 * [ ROUTE ] - Sistema de roteamento moderno estilo Laravel
 * 
 * Esta classe fornece métodos para definir e gerenciar rotas HTTP.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 2.0.0
 * @access public       
 */
class Route
{
    private static $routes = [];
    private static $middlewares = [];
    private static $currentGroup = null;

    /**
     * Registra uma rota GET
     * @param string $uri URI da rota
     * @param string $action Controller@método ou closure
     * @return RouteRegistrar
     */
    public static function get($uri, $action)
    {
        return self::addRoute('GET', $uri, $action);
    }

    /**
     * Registra uma rota POST
     * @param string $uri URI da rota
     * @param string $action Controller@método ou closure
     * @return RouteRegistrar
     */
    public static function post($uri, $action)
    {
        return self::addRoute('POST', $uri, $action);
    }

    /**
     * Registra uma rota PUT
     * @param string $uri URI da rota
     * @param string $action Controller@método ou closure
     * @return RouteRegistrar
     */
    public static function put($uri, $action)
    {
        return self::addRoute('PUT', $uri, $action);
    }

    /**
     * Registra uma rota DELETE
     * @param string $uri URI da rota
     * @param string $action Controller@método ou closure
     * @return RouteRegistrar
     */
    public static function delete($uri, $action)
    {
        return self::addRoute('DELETE', $uri, $action);
    }

    /**
     * Registra uma rota para qualquer método HTTP
     * @param string $uri URI da rota
     * @param string $action Controller@método ou closure
     * @return RouteRegistrar
     */
    public static function any($uri, $action)
    {
        return self::addRoute(['GET', 'POST', 'PUT', 'DELETE'], $uri, $action);
    }

    /**
     * Registra rotas de recurso completo
     * @param string $name Nome do recurso
     * @param string $controller Nome do controller
     */
    public static function resource($name, $controller)
    {
        self::get($name, $controller . '@index');
        self::get($name . '/create', $controller . '@create');
        self::post($name, $controller . '@store');
        self::get($name . '/{id}', $controller . '@show');
        self::get($name . '/{id}/edit', $controller . '@edit');
        self::put($name . '/{id}', $controller . '@update');
        self::delete($name . '/{id}', $controller . '@destroy');
    }

    /**
     * Grupo de rotas com middleware
     * @param array $attributes Atributos do grupo (middleware, prefix)
     * @param callable $callback Callback com as rotas do grupo
     */
    public static function group($attributes, $callback)
    {
        $previousGroup = self::$currentGroup;
        self::$currentGroup = $attributes;
        
        call_user_func($callback);
        
        self::$currentGroup = $previousGroup;
    }

    /**
     * Adiciona middleware a uma rota
     * @param string|array $middleware Nome(s) do middleware
     * @return RouteRegistrar
     */
    public static function middleware($middleware)
    {
        return new RouteRegistrar($middleware);
    }

    /**
     * Adiciona uma rota ao sistema
     * @param string|array $methods Método(s) HTTP
     * @param string $uri URI da rota
     * @param string $action Controller@método
     * @return RouteRegistrar
     */
    private static function addRoute($methods, $uri, $action)
    {
        $methods = (array) $methods;
        
        // Aplicar prefixo do grupo se existir
        if (self::$currentGroup && isset(self::$currentGroup['prefix'])) {
            $uri = self::$currentGroup['prefix'] . '/' . ltrim($uri, '/');
        }
        
        // Limpar URI
        $uri = '/' . trim($uri, '/');
        if ($uri === '/') {
            $uri = '';
        }

        foreach ($methods as $method) {
            self::$routes[$method][$uri] = [
                'action' => $action,
                'middleware' => self::$currentGroup['middleware'] ?? [],
                'uri' => $uri
            ];
        }

        return new RouteRegistrar($action);
    }

    /**
     * Resolve e executa a rota atual
     */
    public static function dispatch()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $uri = self::getCurrentUri();

        // Procurar rota exata
        if (isset(self::$routes[$method][$uri])) {
            return self::executeRoute(self::$routes[$method][$uri], []);
        }

        // Procurar rota com parâmetros
        foreach (self::$routes[$method] ?? [] as $route => $routeData) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove o match completo
                return self::executeRoute($routeData, $matches);
            }
        }

        // Rota não encontrada - tentar sistema legado
        return self::fallbackToLegacyRouter();
    }

    /**
     * Executa uma rota encontrada
     * @param array $routeData Dados da rota
     * @param array $parameters Parâmetros da URL
     */
    private static function executeRoute($routeData, $parameters = [])
    {
        // Executar middlewares
        if (!empty($routeData['middleware'])) {
            foreach ($routeData['middleware'] as $middleware) {
                if (!self::executeMiddleware($middleware)) {
                    return false;
                }
            }
        }

        $action = $routeData['action'];

        // Se é uma closure
        if (is_callable($action)) {
            return call_user_func_array($action, $parameters);
        }

        // Se é Controller@método
        if (is_string($action) && strpos($action, '@') !== false) {
            list($controller, $method) = explode('@', $action);
            
            $controllerPath = './app/Controllers/' . $controller . '.php';
            
            if (!file_exists($controllerPath)) {
                return self::handleNotFound();
            }

            require_once $controllerPath;
            
            if (!class_exists($controller)) {
                return self::handleNotFound();
            }

            $controllerInstance = new $controller;
            
            if (!method_exists($controllerInstance, $method)) {
                return self::handleNotFound();
            }

            return call_user_func_array([$controllerInstance, $method], $parameters);
        }

        return self::handleNotFound();
    }

    /**
     * Executa middleware
     * @param string $middleware Nome do middleware
     * @return bool
     */
    private static function executeMiddleware($middleware)
    {
        // Middleware auth
        if ($middleware === 'auth') {
            if (!isset($_SESSION['usuario_id'])) {
                Helper::redirecionar('login/login');
                return false;
            }
        }

        // Middleware admin
        if ($middleware === 'admin') {
            if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'admin') {
                Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Acesso negado', 'alert alert-danger');
                Helper::redirecionar('dashboard/inicial');
                return false;
            }
        }

        // Middleware csrf (para rotas POST/PUT/DELETE)
        if ($middleware === 'csrf') {
            $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
                $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
                if (!verificarCsrf($token)) {
                    http_response_code(403);
                    die('Token CSRF inválido');
                }
            }
        }

        return true;
    }

    /**
     * Obtém a URI atual
     * @return string
     */
    private static function getCurrentUri()
    {
        $uri = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL) ?? '';
        $uri = '/' . trim($uri, '/');
        if ($uri === '/') {
            $uri = '';
        }
        return $uri;
    }

    /**
     * Fallback para o roteador legado
     */
    private static function fallbackToLegacyRouter()
    {
        // Ao invés de usar o sistema legado, redirecionar para página de erro
        return self::handleNotFound();
    }

    /**
     * Trata rota não encontrada
     */
    private static function handleNotFound()
    {
        // Verificar se existe o controller de página de erro
        $errorControllerPath = './app/Controllers/Pagina.php';
        
        if (file_exists($errorControllerPath)) {
            require_once $errorControllerPath;
            
            if (class_exists('Pagina')) {
                $controller = new Pagina();
                
                if (method_exists($controller, 'erro')) {
                    http_response_code(404);
                    return $controller->erro();
                }
            }
        }
        
        // Se não conseguir carregar a página de erro, mostrar erro básico
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - ' . APP_NOME . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center">
                    <h1 class="display-1">404</h1>
                    <h2 class="mb-4">Página não encontrada</h2>
                    <p class="lead">A página que você está procurando não foi encontrada.</p>
                    <p class="text-muted">Esta rota não está cadastrada no sistema.</p>
                    <a href="' . URL . '" class="btn btn-primary">Voltar ao início</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
        die();
    }

    /**
     * Obtém todas as rotas registradas
     * @return array
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Limpa todas as rotas (útil para testes)
     */
    public static function clearRoutes()
    {
        self::$routes = [];
    }
}

/**
 * Classe auxiliar para registro de rotas com middleware
 */
class RouteRegistrar
{
    private $middleware = [];
    private $action;

    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * Adiciona middleware à rota
     * @param string|array $middleware
     * @return self
     */
    public function middleware($middleware)
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);
        return $this;
    }

    /**
     * Define nome para a rota
     * @param string $name
     * @return self
     */
    public function name($name)
    {
        // Implementar se necessário
        return $this;
    }
} 