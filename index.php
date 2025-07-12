<?php
session_start();
include 'vendor/autoload.php';
include 'config/app.php';
include 'app/autoload.php';

// Inicializar banco de dados
$db = new Database;

// Carregar sistema de rotas
require_once 'app/Libraries/Route.php';

// Incluir head apenas se não for uma requisição AJAX
// if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
//     include 'app/Views/include/head.php';
// }

// Carregar rotas definidas
require_once 'routes/web.php';

// Processar rotas
Route::dispatch();

// Incluir scripts apenas se não for uma requisição AJAX
// if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    // include 'app/Views/include/linkjs.php';
// }
