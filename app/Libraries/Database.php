<?php

/**
 * [ DATABASE ] - Classe para gerenciar a conexão com o banco de dados.
 * 
 * Esta classe fornece métodos para estabelecer conexões com o banco de dados,
 * executar consultas e manipular resultados.   
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2024-2025 TJGO
 * @version 1.0.0
 * @access protected       
 */
class Database
{

    private $host = HOST;
    private $usuario = USUARIO;
    private $senha = SENHA;
    private $banco = BANCO;
    private $porta = PORTA;
    private $dbh;
    private $stmt;

    public function __construct()
    {
        /**
         * Fonte de dados ou DSN contém as informações necessárias para conectar ao banco de dados 
         */
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->porta . ';dbname=' . $this->banco . ';charset=utf8mb4';

        $opcoes = [
            //armazena em cache a conexão para ser reutilizada, evita a sobrecarga de uma nova conexão, resultando em um aplicativo mais rapido.
            PDO::ATTR_PERSISTENT => true,
            //lança uma PDOException se ocorrer erro
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Garante que as colunas sejam retornadas com os nomes corretos
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            // Converte valores vazios em NULL
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            // Emula prepared statements para compatibilidade
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            /**
             * cria a instancia do PDO
             */
            $this->dbh = new PDO($dsn, $this->usuario, $this->senha, $opcoes);
        } catch (PDOException $e) {
            echo 'ERROR: ' . $e->getMessage() . '<br/>';
        }
    }

    /**
     * [ query ] - Prepara uma consulta SQL.
     * 
     * @param string $sql Consulta SQL a ser preparada
     * @return void
     */
    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * [ bind ] - Vincula um valor a um parâmetro.
     * 
     * @param string $parametro Parâmetro a ser vinculado
     * @param mixed $valor Valor a ser vinculado
     * @param string $tipo Tipo do valor a ser vinculado
     * @return void
     */
    public function bind($parametro, $valor, $tipo = null)
    {
        if (is_null($tipo)) :
            switch (true):
                case is_int($valor):
                    $tipo = PDO::PARAM_INT;
                    break;
                case is_bool($valor):
                    $tipo = PDO::PARAM_BOOL;
                    break;
                case is_null($valor):
                    $tipo = PDO::PARAM_NULL;
                    break;
                default:
                    $tipo = PDO::PARAM_STR;
            endswitch;
        endif;

        $this->stmt->bindValue($parametro, $valor, $tipo);
    }

    /**
     * [ executa ] - Executa a consulta preparada.
     * 
     * @return bool
     */
    public function executa()
    {
        return $this->stmt->execute();
    }

    /**
     * [ resultado ] - Retorna o primeiro resultado da consulta.
     * 
     * @return object
     */
    public function resultado()
    {
        $this->executa();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * [ resultados ] - Retorna todos os resultados da consulta.
     * 
     * @return array
     */
    public function resultados()
    {
        $this->executa();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * [ totalResultados ] - Retorna o número de resultados da consulta.
     * 
     * @return int
     */ 
    public function totalResultados()
    {
        return $this->stmt->rowCount();
    }

    /**
     * [ ultimoIdInserido ] - Retorna o último ID inserido.
     * 
     * @return int
     */
    public function ultimoIdInserido()
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * [ iniciarTransacao ] - Inicia uma transação no banco de dados
     * 
     * @return bool - True se a transação foi iniciada com sucesso, false caso contrário
     */
    public function iniciarTransacao() {
        return $this->dbh->beginTransaction();
    }

    /**
     * [ confirmarTransacao ] - Confirma uma transação no banco de dados
     * 
     * @return bool - True se a transação foi confirmada com sucesso, false caso contrário
     */
    public function confirmarTransacao() {
        return $this->dbh->commit();
    }

    /**
     * [ cancelarTransacao ] - Cancela uma transação no banco de dados
     * 
     * @return bool - True se a transação foi cancelada com sucesso, false caso contrário
     */
    public function cancelarTransacao() {
        return $this->dbh->rollBack();
    }
}
