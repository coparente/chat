<?php

/**
 * [ DATABASEEXCEPTION ] - Exceção para erros de banco de dados
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class DatabaseException extends Exception
{
    protected $sql;
    protected $params;

    public function __construct($message = '', $sql = '', $params = [], $code = 500)
    {
        parent::__construct($message, $code);
        $this->sql = $sql;
        $this->params = $params;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getParams()
    {
        return $this->params;
    }
} 