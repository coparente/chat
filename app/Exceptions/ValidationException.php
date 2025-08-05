<?php

/**
 * [ VALIDATIONEXCEPTION ] - Exceção para erros de validação
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class ValidationException extends Exception
{
    protected $errors;

    public function __construct($message = '', $errors = [], $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
} 