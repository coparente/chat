<?php

/**
 * [ DEPARTAMENTOHELPER ] - Helper para lógica de identificação de departamento
 * 
 * Esta classe fornece métodos para:
 * - Identificar departamento baseado em número de telefone
 * - Identificar departamento baseado em palavras-chave
 * - Identificar departamento baseado em horário
 * - Identificar departamento baseado em disponibilidade de atendentes
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class DepartamentoHelper
{
    private $departamentoModel;
    private $credencialSerproModel;

    public function __construct()
    {
        $this->departamentoModel = new DepartamentoModel();
        $this->credencialSerproModel = new CredencialSerproModel();
    }

    /**
     * [ identificarDepartamento ] - Identifica departamento para uma conversa
     * 
     * @param string $numero Número do telefone
     * @param string $mensagem Mensagem inicial (opcional)
     * @param array $contexto Contexto adicional
     * @return int|null ID do departamento identificado
     */
    public function identificarDepartamento($numero, $mensagem = '', $contexto = [])
    {
        // 1. Tentar identificar por número de telefone
        $departamentoId = $this->identificarPorNumero($numero);
        if ($departamentoId) {
            return $departamentoId;
        }

        // 2. Tentar identificar por palavras-chave na mensagem
        $departamentoId = $this->identificarPorPalavrasChave($mensagem);
        if ($departamentoId) {
            return $departamentoId;
        }

        // 3. Tentar identificar por horário de atendimento
        $departamentoId = $this->identificarPorHorario();
        if ($departamentoId) {
            return $departamentoId;
        }

        // 4. Tentar identificar por disponibilidade de atendentes
        $departamentoId = $this->identificarPorDisponibilidade();
        if ($departamentoId) {
            return $departamentoId;
        }

        // 5. Retornar departamento padrão (Geral)
        return $this->obterDepartamentoPadrao();
    }

    /**
     * [ identificarPorNumero ] - Identifica departamento por número de telefone
     * 
     * @param string $numero Número do telefone
     * @return int|null ID do departamento
     */
    private function identificarPorNumero($numero)
    {
        // Implementar lógica baseada em prefixos de número
        // Exemplo: números que começam com 0800 vão para Suporte
        
        $numero = preg_replace('/[^0-9]/', '', $numero);
        
        // Regras de roteamento por número
        $regras = [
            '0800' => 2, // Suporte Técnico
            '400' => 3,  // Comercial
            '0300' => 4, // Financeiro
            '0500' => 5  // RH
        ];
        
        foreach ($regras as $prefixo => $departamentoId) {
            if (strpos($numero, (string)$prefixo) === 0) {
                // Verificar se o departamento existe e está ativo
                $departamento = $this->departamentoModel->buscarPorId($departamentoId);
                if ($departamento && $departamento->status === 'ativo') {
                    return $departamentoId;
                }
            }
        }
        
        return null;
    }

    /**
     * [ identificarPorPalavrasChave ] - Identifica departamento por palavras-chave
     * 
     * @param string $mensagem Mensagem para análise
     * @return int|null ID do departamento
     */
    private function identificarPorPalavrasChave($mensagem)
    {
        if (empty($mensagem)) {
            return null;
        }
        
        $mensagem = strtolower(trim($mensagem));
        
        // Palavras-chave por departamento
        $palavrasChave = [
            2 => [ // Suporte Técnico
                'problema', 'erro', 'bug', 'não funciona', 'defeito', 'técnico',
                'suporte', 'ajuda', 'assistência', 'manutenção', 'reparo'
            ],
            3 => [ // Comercial
                'compra', 'venda', 'preço', 'orçamento', 'proposta', 'comercial',
                'produto', 'serviço', 'negociação', 'promoção', 'desconto'
            ],
            4 => [ // Financeiro
                'pagamento', 'boleto', 'fatura', 'cobrança', 'financeiro',
                'dinheiro', 'valor', 'preço', 'conta', 'débito', 'crédito'
            ],
            5 => [ // RH
                'emprego', 'vaga', 'trabalho', 'curriculum', 'cv', 'rh',
                'recursos humanos', 'seleção', 'entrevista', 'salário'
            ]
        ];
        
        foreach ($palavrasChave as $departamentoId => $palavras) {
            foreach ($palavras as $palavra) {
                if (strpos($mensagem, $palavra) !== false) {
                    // Verificar se o departamento existe e está ativo
                    $departamento = $this->departamentoModel->buscarPorId($departamentoId);
                    if ($departamento && $departamento->status === 'ativo') {
                        return $departamentoId;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * [ identificarPorHorario ] - Identifica departamento por horário de atendimento
     * 
     * @return int|null ID do departamento
     */
    private function identificarPorHorario()
    {
        $horaAtual = (int) date('H');
        $diaSemana = (int) date('N'); // 1=Segunda, 7=Domingo
        
        // Buscar departamentos com horários específicos
        $departamentos = $this->departamentoModel->listarTodos(true);
        
        foreach ($departamentos as $departamento) {
            if (!empty($departamento->configuracoes)) {
                $config = json_decode($departamento->configuracoes, true);
                
                if (isset($config['horario_atendimento'])) {
                    $horarios = explode('-', $config['horario_atendimento']);
                    if (count($horarios) === 2) {
                        $inicio = (int) explode(':', $horarios[0])[0];
                        $fim = (int) explode(':', $horarios[1])[0];
                        
                        // Verificar se está no horário de atendimento
                        if ($horaAtual >= $inicio && $horaAtual <= $fim) {
                            // Verificar dias da semana
                            if (isset($config['dias_semana']) && is_array($config['dias_semana'])) {
                                if (in_array($diaSemana, $config['dias_semana'])) {
                                    return $departamento->id;
                                }
                            } else {
                                // Se não especificou dias, considerar todos
                                return $departamento->id;
                            }
                        }
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * [ identificarPorDisponibilidade ] - Identifica departamento por disponibilidade de atendentes
     * 
     * @return int|null ID do departamento ou null
     */
    private function identificarPorDisponibilidade()
    {
        $departamentos = $this->departamentoModel->listarTodos(true);
        
        foreach ($departamentos as $departamento) {
            $atendentes = $this->departamentoModel->getAtendentes($departamento->id, true);
            
            if (!empty($atendentes)) {
                // Verificar se há atendentes online
                foreach ($atendentes as $atendente) {
                    if ($atendente->status === 'ativo') {
                        // Verificar se o atendente está disponível
                        $conversasAtivas = $this->contarConversasAtendente($atendente->id);
                        
                        // Removida validação de limite - atendentes sempre disponíveis
                        return $departamento->id;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * [ obterDepartamentoPadrao ] - Obtém o departamento padrão (Geral)
     * 
     * @return int ID do departamento padrão
     */
    private function obterDepartamentoPadrao()
    {
        // Buscar departamento "Geral" ou o primeiro ativo
        $departamento = $this->departamentoModel->buscarPorNome('Geral');
        
        if ($departamento && $departamento->status === 'ativo') {
            return $departamento->id;
        }
        
        // Se não encontrar "Geral", buscar o primeiro departamento ativo
        $departamentos = $this->departamentoModel->listarTodos(true);
        if (!empty($departamentos)) {
            return $departamentos[0]->id;
        }
        
        // Fallback para ID 1
        return 1;
    }

    /**
     * [ contarConversasAtendente ] - Conta conversas ativas de um atendente
     * 
     * @param int $atendenteId ID do atendente
     * @return int Número de conversas ativas
     */
    private function contarConversasAtendente($atendenteId)
    {
        $db = new Database();
        $sql = "SELECT COUNT(*) as total FROM conversas 
                WHERE atendente_id = :atendente_id 
                AND status IN ('aberto', 'pendente')";
        
        $db->query($sql);
        $db->bind(':atendente_id', $atendenteId);
        $resultado = $db->resultado();
        
        return $resultado ? $resultado->total : 0;
    }

    /**
     * [ obterCredencialDepartamento ] - Obtém credencial ativa de um departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return object|null Credencial encontrada
     */
    public function obterCredencialDepartamento($departamentoId)
    {
        return $this->credencialSerproModel->obterCredencialAtiva($departamentoId);
    }

    /**
     * [ verificarDepartamentoAtivo ] - Verifica se um departamento está ativo
     * 
     * @param int $departamentoId ID do departamento
     * @return bool True se ativo
     */
    public function verificarDepartamentoAtivo($departamentoId)
    {
        $departamento = $this->departamentoModel->buscarPorId($departamentoId);
        return $departamento && $departamento->status === 'ativo';
    }

    /**
     * [ obterDepartamentosDisponiveis ] - Obtém lista de departamentos disponíveis
     * 
     * @return array Lista de departamentos
     */
    public function obterDepartamentosDisponiveis()
    {
        return $this->departamentoModel->listarTodos(true);
    }

    /**
     * [ logIdentificacao ] - Registra log da identificação de departamento
     * 
     * @param string $numero Número do telefone
     * @param string $mensagem Mensagem
     * @param int $departamentoId ID do departamento identificado
     * @param string $metodo Método usado para identificação
     */
    public function logIdentificacao($numero, $mensagem, $departamentoId, $metodo)
    {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'numero' => $numero,
            'mensagem' => substr($mensagem, 0, 100), // Limitar tamanho
            'departamento_id' => $departamentoId,
            'metodo' => $metodo
        ];
        
        error_log("DEPARTAMENTO_IDENTIFICADO: " . json_encode($log));
    }
} 