<?php

/**
 * [ MIDDLEWARE ] - Classe para verificar permissões de acesso
 * 
 * Esta classe fornece métodos para verificar se um usuário tem permissão para acessar recursos específicos.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br> 
 * @copyright 2025 TJGO
 * @version 1.0.0
 * @access protected       
 */
class Middleware {

    public function __construct() {
        // Construtor vazio - sem referências a módulos
    }

    /**
     * Verifica se o usuário tem permissão para acessar um recurso específico
     * @param string $recurso Nome do recurso a ser verificado
     */
    public static function verificarPermissao($recurso) {
        if (!isset($_SESSION['usuario_id'])) {
            Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Você precisa estar logado para acessar este recurso', 'alert alert-danger');
            Helper::redirecionar('/login/login');
            exit;
        }

        // Admins têm acesso total
        if ($_SESSION['usuario_perfil'] === 'admin') {
            return true;
        }

        // Supervisores têm acesso a recursos de gestão
        if ($_SESSION['usuario_perfil'] === 'supervisor') {
            $recursosPermitidos = ['chat', 'contatos', 'dashboard', 'relatorios'];
            if (in_array($recurso, $recursosPermitidos)) {
                return true;
            }
        }

        // Atendentes têm acesso limitado
        if ($_SESSION['usuario_perfil'] === 'atendente') {
            $recursosPermitidos = ['chat', 'dashboard'];
            if (in_array($recurso, $recursosPermitidos)) {
                return true;
            }
        }

        // Se chegou aqui, não tem permissão
        Helper::mensagem('dashboard', '<i class="fas fa-ban"></i> Você não tem permissão para acessar este recurso', 'alert alert-danger');
        Helper::redirecionar('dashboard/inicial');
        exit;
    }
} 