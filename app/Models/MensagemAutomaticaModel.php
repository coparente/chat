<?php

/**
 * [ MENSAGEMAUTOMATICAMODEL ] - Model para gerenciar mensagens automáticas por departamento
 * 
 * Esta classe gerencia:
 * - CRUD de mensagens automáticas por departamento
 * - Configurações de horário e dias da semana
 * - Ativação/desativação de mensagens
 * - Busca de mensagens por tipo e departamento
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class MensagemAutomaticaModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * [ buscarPorDepartamento ] - Busca mensagens automáticas de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return array Lista de mensagens automáticas
     */
    public function buscarPorDepartamento($departamentoId)
    {
        $sql = "SELECT * FROM mensagens_automaticas_departamento 
                WHERE departamento_id = :departamento_id 
                ORDER BY tipo ASC, criado_em ASC";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        
        return $this->db->resultados();
    }

    /**
     * [ buscarPorTipo ] - Busca mensagens automáticas por tipo e departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param string $tipo Tipo da mensagem (boas_vindas, ausencia, encerramento, etc.)
     * @return object|null Mensagem automática encontrada
     */
    public function buscarPorTipo($departamentoId, $tipo)
    {
        $sql = "SELECT * FROM mensagens_automaticas_departamento 
                WHERE departamento_id = :departamento_id 
                AND tipo = :tipo 
                AND ativo = 1";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':tipo', $tipo);
        
        return $this->db->resultado();
    }

    /**
     * [ criar ] - Cria uma nova mensagem automática
     * 
     * @param array $dados Dados da mensagem automática
     * @return bool Sucesso da operação
     */
    public function criar($dados)
    {
        $sql = "INSERT INTO mensagens_automaticas_departamento 
                (departamento_id, tipo, titulo, mensagem, ativo, horario_inicio, horario_fim, dias_semana, configuracoes, criado_em) 
                VALUES 
                (:departamento_id, :tipo, :titulo, :mensagem, :ativo, :horario_inicio, :horario_fim, :dias_semana, :configuracoes, NOW())";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $dados['departamento_id']);
        $this->db->bind(':tipo', $dados['tipo']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':mensagem', $dados['mensagem']);
        $this->db->bind(':ativo', $dados['ativo'] ?? 1);
        $this->db->bind(':horario_inicio', $dados['horario_inicio'] ?? '08:00:00');
        $this->db->bind(':horario_fim', $dados['horario_fim'] ?? '18:00:00');
        $this->db->bind(':dias_semana', json_encode($dados['dias_semana'] ?? [1,2,3,4,5]));
        $this->db->bind(':configuracoes', json_encode($dados['configuracoes'] ?? []));
        
        return $this->db->executa();
    }

    /**
     * [ atualizar ] - Atualiza uma mensagem automática
     * 
     * @param int $id ID da mensagem automática
     * @param array $dados Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function atualizar($id, $dados)
    {
        $sql = "UPDATE mensagens_automaticas_departamento SET 
                tipo = :tipo,
                titulo = :titulo,
                mensagem = :mensagem,
                ativo = :ativo,
                horario_inicio = :horario_inicio,
                horario_fim = :horario_fim,
                dias_semana = :dias_semana,
                configuracoes = :configuracoes,
                atualizado_em = NOW()
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':tipo', $dados['tipo']);
        $this->db->bind(':titulo', $dados['titulo']);
        $this->db->bind(':mensagem', $dados['mensagem']);
        $this->db->bind(':ativo', $dados['ativo'] ?? 1);
        $this->db->bind(':horario_inicio', $dados['horario_inicio'] ?? '08:00:00');
        $this->db->bind(':horario_fim', $dados['horario_fim'] ?? '18:00:00');
        $this->db->bind(':dias_semana', json_encode($dados['dias_semana'] ?? [1,2,3,4,5]));
        $this->db->bind(':configuracoes', json_encode($dados['configuracoes'] ?? []));
        
        return $this->db->executa();
    }

    /**
     * [ excluir ] - Exclui uma mensagem automática
     * 
     * @param int $id ID da mensagem automática
     * @return bool Sucesso da operação
     */
    public function excluir($id)
    {
        $sql = "DELETE FROM mensagens_automaticas_departamento WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        return $this->db->executa();
    }

    /**
     * [ buscarPorId ] - Busca mensagem automática por ID
     * 
     * @param int $id ID da mensagem automática
     * @return object|null Mensagem automática encontrada
     */
    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM mensagens_automaticas_departamento WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        $resultado = $this->db->resultado();
        
        if ($resultado) {
            // Decodificar dados JSON
            $resultado->dias_semana = json_decode($resultado->dias_semana, true);
            $resultado->configuracoes = json_decode($resultado->configuracoes, true);
        }
        
        return $resultado;
    }

    /**
     * [ alterarStatus ] - Altera status de uma mensagem automática
     * 
     * @param int $id ID da mensagem automática
     * @param bool $ativo Novo status
     * @return bool Sucesso da operação
     */
    public function alterarStatus($id, $ativo)
    {
        $sql = "UPDATE mensagens_automaticas_departamento SET ativo = :ativo WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':ativo', $ativo ? 1 : 0);
        
        return $this->db->executa();
    }

    /**
     * [ getTiposDisponiveis ] - Retorna tipos de mensagens automáticas disponíveis
     * 
     * @return array Tipos disponíveis
     */
    public function getTiposDisponiveis()
    {
        return [
            'boas_vindas' => [
                'nome' => 'Boas-vindas',
                'descricao' => 'Enviada quando um novo contato inicia uma conversa',
                'icone' => 'fas fa-handshake'
            ],
            'ausencia' => [
                'nome' => 'Ausência de Atendentes',
                'descricao' => 'Enviada quando não há atendentes disponíveis',
                'icone' => 'fas fa-user-clock'
            ],
            'encerramento' => [
                'nome' => 'Encerramento',
                'descricao' => 'Enviada quando uma conversa é finalizada',
                'icone' => 'fas fa-door-closed'
            ],
            'fora_horario' => [
                'nome' => 'Fora do Horário',
                'descricao' => 'Enviada quando o contato escreve fora do horário de funcionamento',
                'icone' => 'fas fa-clock'
            ],
            'aguardando' => [
                'nome' => 'Aguardando Atendimento',
                'descricao' => 'Enviada quando o contato está na fila de espera',
                'icone' => 'fas fa-hourglass-half'
            ],
            'transferencia' => [
                'nome' => 'Transferência',
                'descricao' => 'Enviada quando uma conversa é transferida para outro departamento',
                'icone' => 'fas fa-exchange-alt'
            ]
        ];
    }

    /**
     * [ verificarMensagemAtiva ] - Verifica se há uma mensagem ativa para o tipo e departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param string $tipo Tipo da mensagem
     * @return object|null Mensagem ativa encontrada
     */
    public function verificarMensagemAtiva($departamentoId, $tipo)
    {
        $sql = "SELECT * FROM mensagens_automaticas_departamento 
                WHERE departamento_id = :departamento_id 
                AND tipo = :tipo 
                AND ativo = 1";
        
        $this->db->query($sql);
        $this->db->bind(':departamento_id', $departamentoId);
        $this->db->bind(':tipo', $tipo);
        
        $resultado = $this->db->resultado();
        
        if ($resultado) {
            $resultado->dias_semana = json_decode($resultado->dias_semana, true);
            $resultado->configuracoes = json_decode($resultado->configuracoes, true);
        }
        
        return $resultado;
    }

    /**
     * [ verificarHorarioFuncionamento ] - Verifica se está dentro do horário de funcionamento
     * 
     * @param object $mensagem Mensagem automática
     * @return bool Está dentro do horário
     */
    public function verificarHorarioFuncionamento($mensagem)
    {
        if (!$mensagem) {
            return false;
        }

        $agora = new DateTime();
        $diaSemana = $agora->format('N'); // 1 (Segunda) a 7 (Domingo)
        
        // Verificar se hoje é um dia de funcionamento
        if (!in_array($diaSemana, $mensagem->dias_semana)) {
            return false;
        }

        // Verificar horário
        $horaAtual = $agora->format('H:i:s');
        $horarioInicio = $mensagem->horario_inicio;
        $horarioFim = $mensagem->horario_fim;

        return $horaAtual >= $horarioInicio && $horaAtual <= $horarioFim;
    }
} 