<?php

/**
 * [ MIDDLEWARE ] - Classe para verificar permissões de acesso a módulos
 * 
 * Esta classe fornece métodos para verificar se um usuário tem permissão para acessar um módulo específico.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br> 
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access protected       
 */
class Middleware {

    private $moduloModel;

    public function __construct() {
        $this->moduloModel = new ModuloModel();
    }

    /**
     * Verifica se o usuário tem permissão para acessar um módulo específico
     * @param int $modulo_id ID do módulo a ser verificado
     */
    public static function verificarPermissao($modulo_id) {
        if (!isset($_SESSION['usuario_id'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Você precisa estar logado para acessar este recurso', 'alert alert-danger');
            Helper::redirecionar('/login/login');
            exit;
        }

        $moduloModel = new ModuloModel();
        
        // Admins têm acesso total
        if ($_SESSION['usuario_perfil'] === 'admin') {
            return true;
        }

        // Verifica permissão específica
        if (!$moduloModel->verificarPermissao($_SESSION['usuario_id'], $modulo_id)) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Você não tem permissão para acessar este recurso', 'alert alert-danger');
            Helper::redirecionar('dashboard/inicial');
            exit;
        }

        return true;
    }
} 