<?php

/**
 * [ LOGHELPER ] - Helper para registro de logs de atividades
 * 
 * Esta classe facilita o registro de atividades dos usuários
 * no sistema, fornecendo métodos simples e padronizados.
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class LogHelper
{
    private static $logModel = null;

    /**
     * [ registrarAtividade ] - Registra uma atividade do usuário
     * 
     * @param string $acao Ação realizada
     * @param string $descricao Descrição da atividade
     * @param int|null $usuarioId ID do usuário (opcional, usa sessão atual)
     * @return bool Sucesso da operação
     */
    public static function registrarAtividade($acao, $descricao = null, $usuarioId = null)
    {
        if (self::$logModel === null) {
            self::$logModel = new LogModel();
        }

        // Se não foi passado usuário, usar da sessão
        if ($usuarioId === null && isset($_SESSION['usuario_id'])) {
            $usuarioId = $_SESSION['usuario_id'];
        }

        return self::$logModel->registrarAtividade($usuarioId, $acao, $descricao);
    }

    /**
     * [ registrarAcesso ] - Registra um acesso ao sistema
     * 
     * @param int|null $usuarioId ID do usuário (opcional)
     * @param string|null $email Email do usuário (opcional)
     * @param bool $sucesso Se o acesso foi bem-sucedido
     * @return bool Sucesso da operação
     */
    public static function registrarAcesso($usuarioId = null, $email = null, $sucesso = false)
    {
        if (self::$logModel === null) {
            self::$logModel = new LogModel();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return self::$logModel->registrarAcesso($usuarioId, $email, $ip, $userAgent, $sucesso);
    }

    /**
     * [ login ] - Registra tentativa de login
     * 
     * @param string $email Email do usuário
     * @param bool $sucesso Se o login foi bem-sucedido
     * @return bool Sucesso da operação
     */
    public static function login($email, $sucesso = false)
    {
        return self::registrarAcesso(null, $email, $sucesso);
    }

    /**
     * [ logout ] - Registra logout do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return bool Sucesso da operação
     */
    public static function logout($usuarioId)
    {
        return self::registrarAtividade('logout', 'Usuário fez logout do sistema', $usuarioId);
    }

    /**
     * [ criarConversa ] - Registra criação de conversa
     * 
     * @param int $conversaId ID da conversa
     * @param string $numero Número do contato
     * @return bool Sucesso da operação
     */
    public static function criarConversa($conversaId, $numero)
    {
        return self::registrarAtividade('criar_conversa', "Conversa #{$conversaId} criada para {$numero}");
    }

    /**
     * [ assumirConversa ] - Registra assunção de conversa
     * 
     * @param int $conversaId ID da conversa
     * @param string $numero Número do contato
     * @return bool Sucesso da operação
     */
    public static function assumirConversa($conversaId, $numero)
    {
        return self::registrarAtividade('assumir_conversa', "Conversa #{$conversaId} assumida ({$numero})");
    }

    /**
     * [ fecharConversa ] - Registra fechamento de conversa
     * 
     * @param int $conversaId ID da conversa
     * @param string $numero Número do contato
     * @return bool Sucesso da operação
     */
    public static function fecharConversa($conversaId, $numero)
    {
        return self::registrarAtividade('fechar_conversa', "Conversa #{$conversaId} fechada ({$numero})");
    }

    /**
     * [ enviarMensagem ] - Registra envio de mensagem
     * 
     * @param int $conversaId ID da conversa
     * @param string $tipo Tipo da mensagem (texto, imagem, etc.)
     * @return bool Sucesso da operação
     */
    public static function enviarMensagem($conversaId, $tipo = 'texto')
    {
        return self::registrarAtividade('enviar_mensagem', "Mensagem {$tipo} enviada na conversa #{$conversaId}");
    }

    /**
     * [ criarUsuario ] - Registra criação de usuário
     * 
     * @param string $nome Nome do usuário criado
     * @param string $perfil Perfil do usuário
     * @return bool Sucesso da operação
     */
    public static function criarUsuario($nome, $perfil)
    {
        return self::registrarAtividade('criar_usuario', "Usuário '{$nome}' criado com perfil {$perfil}");
    }

    /**
     * [ editarUsuario ] - Registra edição de usuário
     * 
     * @param int $usuarioId ID do usuário editado
     * @param string $nome Nome do usuário
     * @return bool Sucesso da operação
     */
    public static function editarUsuario($usuarioId, $nome)
    {
        return self::registrarAtividade('editar_usuario', "Usuário #{$usuarioId} ({$nome}) editado");
    }

    /**
     * [ excluirUsuario ] - Registra exclusão de usuário
     * 
     * @param int $usuarioId ID do usuário excluído
     * @param string $nome Nome do usuário
     * @return bool Sucesso da operação
     */
    public static function excluirUsuario($usuarioId, $nome)
    {
        return self::registrarAtividade('excluir_usuario', "Usuário #{$usuarioId} ({$nome}) excluído");
    }

    /**
     * [ alterarPermissoes ] - Registra alteração de permissões
     * 
     * @param int $usuarioId ID do usuário
     * @param string $nome Nome do usuário
     * @return bool Sucesso da operação
     */
    public static function alterarPermissoes($usuarioId, $nome)
    {
        return self::registrarAtividade('alterar_permissoes', "Permissões do usuário #{$usuarioId} ({$nome}) alteradas");
    }

    /**
     * [ configurarSistema ] - Registra alteração de configuração
     * 
     * @param string $configuracao Nome da configuração
     * @param string $valor Valor da configuração
     * @return bool Sucesso da operação
     */
    public static function configurarSistema($configuracao, $valor)
    {
        return self::registrarAtividade('configurar_sistema', "Configuração '{$configuracao}' alterada para '{$valor}'");
    }

    /**
     * [ exportarRelatorio ] - Registra exportação de relatório
     * 
     * @param string $tipo Tipo do relatório
     * @param string $formato Formato do arquivo
     * @return bool Sucesso da operação
     */
    public static function exportarRelatorio($tipo, $formato)
    {
        return self::registrarAtividade('exportar_relatorio', "Relatório '{$tipo}' exportado em {$formato}");
    }

    /**
     * [ limparLogs ] - Registra limpeza de logs
     * 
     * @param int $dias Número de dias mantidos
     * @param int $quantidade Quantidade de logs removidos
     * @return bool Sucesso da operação
     */
    public static function limparLogs($dias, $quantidade)
    {
        return self::registrarAtividade('limpar_logs', "Logs com mais de {$dias} dias removidos ({$quantidade} registros)");
    }

    /**
     * [ erro ] - Registra erro do sistema
     * 
     * @param string $erro Descrição do erro
     * @param string $arquivo Arquivo onde ocorreu o erro
     * @param int $linha Linha do erro
     * @return bool Sucesso da operação
     */
    public static function erro($erro, $arquivo = '', $linha = 0)
    {
        $descricao = "Erro: {$erro}";
        if ($arquivo) {
            $descricao .= " em {$arquivo}";
        }
        if ($linha) {
            $descricao .= " linha {$linha}";
        }

        return self::registrarAtividade('erro_sistema', $descricao);
    }

    /**
     * [ acessarPagina ] - Registra acesso a página
     * 
     * @param string $pagina Nome da página
     * @return bool Sucesso da operação
     */
    public static function acessarPagina($pagina)
    {
        return self::registrarAtividade('acessar_pagina', "Página '{$pagina}' acessada");
    }

    /**
     * [ downloadArquivo ] - Registra download de arquivo
     * 
     * @param string $arquivo Nome do arquivo
     * @param string $tipo Tipo do arquivo
     * @return bool Sucesso da operação
     */
    public static function downloadArquivo($arquivo, $tipo)
    {
        return self::registrarAtividade('download_arquivo', "Arquivo '{$arquivo}' ({$tipo}) baixado");
    }
} 