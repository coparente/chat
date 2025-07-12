<?php

/**
 * [ SESSAOWHATSAPPMODEL ] - Model para gerenciamento de sessões WhatsApp
 * 
 * Esta classe gerencia todas as operações relacionadas às sessões/conexões WhatsApp:
 * - CRUD de sessões
 * - Estatísticas de conexões
 * - Controle de status das conexões
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class SessaoWhatsappModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ getEstatisticasConexoes ] - Estatísticas das conexões WhatsApp
     * 
     * @return object Estatísticas das conexões
     */
    public function getEstatisticasConexoes()
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'conectado' THEN 1 ELSE 0 END) as conectadas,
                SUM(CASE WHEN status = 'desconectado' THEN 1 ELSE 0 END) as desconectadas,
                SUM(CASE WHEN status = 'conectando' THEN 1 ELSE 0 END) as conectando,
                SUM(CASE WHEN status = 'erro' THEN 1 ELSE 0 END) as com_erro
            FROM sessoes_whatsapp
        ";
        
        $this->db->query($sql);
        return $this->db->resultado();
    }

    /**
     * [ listarSessoes ] - Lista todas as sessões
     * 
     * @return array Lista de sessões
     */
    public function listarSessoes()
    {
        $sql = "
            SELECT 
                id, nome, numero, status, ultima_conexao, criado_em
            FROM sessoes_whatsapp 
            ORDER BY criado_em DESC
        ";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ buscarSessaoPorId ] - Busca sessão por ID
     * 
     * @param int $id ID da sessão
     * @return object|null Dados da sessão
     */
    public function buscarSessaoPorId($id)
    {
        $sql = "SELECT * FROM sessoes_whatsapp WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->resultado();
    }

    /**
     * [ buscarSessaoPorNumero ] - Busca sessão pelo número
     * 
     * @param string $numero Número do WhatsApp
     * @return object|null Dados da sessão
     */
    public function buscarSessaoPorNumero($numero)
    {
        $sql = "SELECT * FROM sessoes_whatsapp WHERE numero = :numero";
        $this->db->query($sql);
        $this->db->bind(':numero', $numero);
        return $this->db->resultado();
    }

    /**
     * [ getSessoesConectadas ] - Busca sessões conectadas
     * 
     * @return array Lista de sessões conectadas
     */
    public function getSessoesConectadas()
    {
        $sql = "
            SELECT * FROM sessoes_whatsapp 
            WHERE status = 'conectado' 
            ORDER BY ultima_conexao DESC
        ";
        
        $this->db->query($sql);
        return $this->db->resultados();
    }

    /**
     * [ criarSessao ] - Cria uma nova sessão
     * 
     * @param array $dados Dados da sessão
     * @return bool Sucesso da operação
     */
    public function criarSessao($dados)
    {
        $sql = "
            INSERT INTO sessoes_whatsapp (
                nome, numero, serpro_session_id, serpro_waba_id, 
                serpro_phone_number_id, webhook_token, status, configuracoes, criado_em
            ) VALUES (
                :nome, :numero, :serpro_session_id, :serpro_waba_id,
                :serpro_phone_number_id, :webhook_token, :status, :configuracoes, NOW()
            )
        ";
        
        $this->db->query($sql);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':numero', $dados['numero']);
        $this->db->bind(':serpro_session_id', $dados['serpro_session_id'] ?? null);
        $this->db->bind(':serpro_waba_id', $dados['serpro_waba_id'] ?? null);
        $this->db->bind(':serpro_phone_number_id', $dados['serpro_phone_number_id'] ?? null);
        $this->db->bind(':webhook_token', $dados['webhook_token'] ?? null);
        $this->db->bind(':status', $dados['status'] ?? 'desconectado');
        $this->db->bind(':configuracoes', $dados['configuracoes'] ?? null);
        
        return $this->db->executa();
    }

    /**
     * [ atualizarSessao ] - Atualiza uma sessão
     * 
     * @param int $id ID da sessão
     * @param array $dados Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function atualizarSessao($id, $dados)
    {
        $campos = [];
        $valores = [];
        
        $camposPermitidos = [
            'nome', 'numero', 'serpro_session_id', 'serpro_waba_id', 
            'serpro_phone_number_id', 'webhook_token', 'status', 
            'qr_code', 'configuracoes'
        ];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($dados[$campo])) {
                $campos[] = "$campo = :$campo";
                $valores[$campo] = $dados[$campo];
            }
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $sql = "UPDATE sessoes_whatsapp SET " . implode(', ', $campos) . ", atualizado_em = NOW() WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        foreach ($valores as $campo => $valor) {
            $this->db->bind(":$campo", $valor);
        }
        
        return $this->db->executa();
    }

    /**
     * [ atualizarStatus ] - Atualiza apenas o status da sessão
     * 
     * @param int $id ID da sessão
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function atualizarStatus($id, $status)
    {
        $statusValidos = ['conectado', 'desconectado', 'conectando', 'erro'];
        
        if (!in_array($status, $statusValidos)) {
            return false;
        }
        
        $sql = "UPDATE sessoes_whatsapp SET status = :status";
        
        if ($status === 'conectado') {
            $sql .= ", ultima_conexao = NOW()";
        }
        
        $sql .= ", atualizado_em = NOW() WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * [ excluirSessao ] - Remove uma sessão
     * 
     * @param int $id ID da sessão
     * @return bool Sucesso da operação
     */
    public function excluirSessao($id)
    {
        $sql = "DELETE FROM sessoes_whatsapp WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }

    /**
     * [ contarConexoesAtivas ] - Conta conexões ativas
     * 
     * @return int Número de conexões ativas
     */
    public function contarConexoesAtivas()
    {
        $sql = "SELECT COUNT(*) as total FROM sessoes_whatsapp WHERE status = 'conectado'";
        $this->db->query($sql);
        return $this->db->resultado()->total;
    }

    /**
     * [ getSessaoPrincipal ] - Busca a sessão principal/padrão
     * 
     * @return object|null Sessão principal
     */
    public function getSessaoPrincipal()
    {
        $sql = "
            SELECT * FROM sessoes_whatsapp 
            WHERE status = 'conectado' 
            ORDER BY ultima_conexao DESC 
            LIMIT 1
        ";
        
        $this->db->query($sql);
        return $this->db->resultado();
    }

    /**
     * [ atualizarQrCode ] - Atualiza QR Code da sessão
     * 
     * @param int $id ID da sessão
     * @param string $qrCode QR Code
     * @return bool Sucesso da operação
     */
    public function atualizarQrCode($id, $qrCode)
    {
        $sql = "UPDATE sessoes_whatsapp SET qr_code = :qr_code, atualizado_em = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':qr_code', $qrCode);
        $this->db->bind(':id', $id);
        return $this->db->executa();
    }
} 