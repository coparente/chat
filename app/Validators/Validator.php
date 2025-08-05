<?php

/**
 * [ VALIDATOR ] - Sistema centralizado de validação
 * 
 * Esta classe fornece métodos para validar dados de entrada
 * de forma consistente e reutilizável.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025 TJGO
 * @version 1.0.0
 */
class Validator
{
    private $errors = [];
    private $data = [];
    private $rules = [];
    private $messages = [];

    /**
     * Construtor
     * 
     * @param array $data Dados a serem validados
     * @param array $rules Regras de validação
     * @param array $messages Mensagens customizadas
     */
    public function __construct($data = [], $rules = [], $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * Valida os dados
     * 
     * @return bool
     * @throws ValidationException
     */
    public function validate()
    {
        foreach ($this->rules as $field => $rules) {
            $this->validateField($field, $rules);
        }

        if (!empty($this->errors)) {
            throw new ValidationException('Erro de validação', $this->errors);
        }

        return true;
    }

    /**
     * Valida um campo específico
     * 
     * @param string $field
     * @param string|array $rules
     */
    private function validateField($field, $rules)
    {
        $value = $this->getValue($field);
        $rules = is_string($rules) ? explode('|', $rules) : $rules;

        foreach ($rules as $rule) {
            $params = [];
            
            if (strpos($rule, ':') !== false) {
                list($rule, $param) = explode(':', $rule, 2);
                $params = explode(',', $param);
            }

            $method = 'validate' . ucfirst($rule);
            
            if (method_exists($this, $method)) {
                if (!$this->$method($field, $value, $params)) {
                    $this->addError($field, $rule, $params);
                    break; // Para na primeira falha
                }
            }
        }
    }

    /**
     * Obtém valor do campo
     * 
     * @param string $field
     * @return mixed
     */
    private function getValue($field)
    {
        $keys = explode('.', $field);
        $value = $this->data;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Adiciona erro de validação
     * 
     * @param string $field
     * @param string $rule
     * @param array $params
     */
    private function addError($field, $rule, $params = [])
    {
        $message = $this->getMessage($field, $rule, $params);
        $this->errors[$field][] = $message;
    }

    /**
     * Obtém mensagem de erro
     * 
     * @param string $field
     * @param string $rule
     * @param array $params
     * @return string
     */
    private function getMessage($field, $rule, $params = [])
    {
        $key = "{$field}.{$rule}";
        
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }

        // Mensagens padrão
        $messages = [
            'required' => 'O campo :field é obrigatório',
            'email' => 'O campo :field deve ser um email válido',
            'min' => 'O campo :field deve ter pelo menos :param caracteres',
            'max' => 'O campo :field deve ter no máximo :param caracteres',
            'numeric' => 'O campo :field deve ser numérico',
            'integer' => 'O campo :field deve ser um número inteiro',
            'string' => 'O campo :field deve ser uma string',
            'url' => 'O campo :field deve ser uma URL válida',
            'date' => 'O campo :field deve ser uma data válida',
            'unique' => 'O valor do campo :field já existe',
            'confirmed' => 'A confirmação do campo :field não confere',
            'different' => 'O campo :field deve ser diferente de :param',
            'same' => 'O campo :field deve ser igual a :param',
            'regex' => 'O formato do campo :field é inválido',
            'alpha' => 'O campo :field deve conter apenas letras',
            'alpha_num' => 'O campo :field deve conter apenas letras e números',
            'alpha_dash' => 'O campo :field deve conter apenas letras, números, hífens e underscores'
        ];

        $message = $messages[$rule] ?? "O campo :field falhou na validação {$rule}";
        
        return str_replace([':field', ':param'], [$field, implode(', ', $params)], $message);
    }

    // ============================================================================
    // REGRAS DE VALIDAÇÃO
    // ============================================================================

    /**
     * Valida campo obrigatório
     */
    private function validateRequired($field, $value)
    {
        return $value !== null && $value !== '';
    }

    /**
     * Valida email
     */
    private function validateEmail($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida tamanho mínimo
     */
    private function validateMin($field, $value, $params)
    {
        $min = (int) $params[0];
        return strlen($value) >= $min;
    }

    /**
     * Valida tamanho máximo
     */
    private function validateMax($field, $value, $params)
    {
        $max = (int) $params[0];
        return strlen($value) <= $max;
    }

    /**
     * Valida valor numérico
     */
    private function validateNumeric($field, $value)
    {
        return is_numeric($value);
    }

    /**
     * Valida valor inteiro
     */
    private function validateInteger($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Valida string
     */
    private function validateString($field, $value)
    {
        return is_string($value);
    }

    /**
     * Valida URL
     */
    private function validateUrl($field, $value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida data
     */
    private function validateDate($field, $value)
    {
        return strtotime($value) !== false;
    }

    /**
     * Valida regex
     */
    private function validateRegex($field, $value, $params)
    {
        $pattern = $params[0];
        return preg_match($pattern, $value);
    }

    /**
     * Valida apenas letras
     */
    private function validateAlpha($field, $value)
    {
        return preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $value);
    }

    /**
     * Valida letras e números
     */
    private function validateAlphaNum($field, $value)
    {
        return preg_match('/^[a-zA-Z0-9À-ÿ\s]+$/', $value);
    }

    /**
     * Valida letras, números, hífens e underscores
     */
    private function validateAlphaDash($field, $value)
    {
        return preg_match('/^[a-zA-Z0-9À-ÿ\s\-_]+$/', $value);
    }

    /**
     * Valida confirmação
     */
    private function validateConfirmed($field, $value)
    {
        $confirmationField = $field . '_confirmation';
        return isset($this->data[$confirmationField]) && $value === $this->data[$confirmationField];
    }

    /**
     * Valida valor diferente
     */
    private function validateDifferent($field, $value, $params)
    {
        $otherField = $params[0];
        $otherValue = $this->getValue($otherField);
        return $value !== $otherValue;
    }

    /**
     * Valida valor igual
     */
    private function validateSame($field, $value, $params)
    {
        $otherField = $params[0];
        $otherValue = $this->getValue($otherField);
        return $value === $otherValue;
    }

    /**
     * Obtém erros de validação
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Verifica se há erros
     * 
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Obtém primeiro erro de um campo
     * 
     * @param string $field
     * @return string|null
     */
    public function getFirstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Obtém todos os erros de um campo
     * 
     * @param string $field
     * @return array
     */
    public function getFieldErrors($field)
    {
        return $this->errors[$field] ?? [];
    }
} 