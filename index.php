<?php
ob_start();
session_start();
include 'vendor/autoload.php';
include 'config/app.php';
include 'app/autoload.php';

// Inicializar banco de dados
$db = new Database;

// Carregar sistema de rotas
require_once 'app/Libraries/Route.php';

// Carregar rotas definidas
require_once 'routes/web.php';

// Processar rotas
Route::dispatch();

ob_end_flush();
