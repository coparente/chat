<?php

/**
 * [ CONTROLLERS ] - Classe base para controladores do sistema.
 * 
 * Esta classe fornece métodos comuns para todos os controladores do sistema.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected       
 */
class Controllers{

    protected $moduloModel;

    public function __construct()
    {
        // Inicializa o ModuloModel apenas se não estivermos na página de login
        if ($this->naoEhPaginaLogin()) {
            $this->moduloModel = $this->model('ModuloModel');
        }
    }

    /**
     * [ model ] - Carrega um modelo do sistema.
     * 
     * @param string $model Nome do modelo a ser carregado
     * @return object Instância do modelo carregado
     */
    public function model($model){
        require_once './app/Models/'.$model.'.php';
        return new $model;
    }

    /**
     * [ view ] - Carrega uma view do sistema.
     * 
     * @param string $view Nome da view a ser carregada
     * @param array $dados Dados a serem passados para a view
     */
    public function view($view, $dados = [])
    {
        // Verifica se é uma view de login antes de adicionar os módulos
        $isLoginView = (strpos($view, 'login/') === 0);
        
        // Adiciona os módulos aos dados apenas se não for uma view de login
        if (!$isLoginView && $this->moduloModel !== null) {
            if (!isset($dados['modulos'])) {
                $dados['modulos'] = $this->moduloModel->getModulosComSubmodulos();
            }
        }

        // Extrai as variáveis do array $dados para que fiquem disponíveis na view
        if (!empty($dados)) {
            extract($dados);
        }

        $arquivo = ('./app/Views/'.$view.'.php');
        if(file_exists($arquivo)):
            require_once $arquivo;
        else:
            die('O arquivo de View não existe!');
        endif;
    }

    /**
     * [ naoEhPaginaLogin ] - Verifica se a página atual não é a página de login
     * 
     * @return bool
     */
    private function naoEhPaginaLogin()
    {
        // Verifica se não estamos em uma rota relacionada ao login
        $rotas_login = ['', 'login', 'login/login', 'login/recuperar'];
        $url_atual = isset($_GET['url']) ? $_GET['url'] : '';
        return !in_array($url_atual, $rotas_login);
    }
}