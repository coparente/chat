<?php

/**
 * [ MENSAGENSAUTOMATICASHELPER ] - Helper para gerenciamento de mensagens automáticas por departamento
 * 
 * Este helper permite:
 * - Verificar horário de funcionamento por departamento
 * - Enviar mensagens automáticas baseadas em condições específicas do departamento
 * - Gerenciar respostas automáticas por departamento
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class MensagensAutomaticasHelper
{
    private $mensagemAutomaticaModel;
    private $serproApi;
    private $departamentoHelper;

    public function __construct()
    {
        try {
            // Verificar se APPROOT está definida
            if (!defined('APPROOT')) {
                // Definir APPROOT se não estiver definida
                define('APPROOT', dirname(__DIR__, 2));
            }
            
            // Carregar apenas as classes essenciais
            if (!class_exists('MensagemAutomaticaModel')) {
                require_once APPROOT . '/Models/MensagemAutomaticaModel.php';
            }
            
            if (!class_exists('SerproApi')) {
                require_once APPROOT . '/Libraries/SerproApi.php';
            }
            
            if (!class_exists('DepartamentoHelper')) {
                require_once APPROOT . '/Libraries/DepartamentoHelper.php';
            }
            
            $this->mensagemAutomaticaModel = new MensagemAutomaticaModel();
            $this->serproApi = new SerproApi();
            $this->departamentoHelper = new DepartamentoHelper();
            
        } catch (Exception $e) {
            error_log("Erro ao inicializar MensagensAutomaticasHelper: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * [ processarMensagemRecebida ] - Processa mensagem recebida e envia resposta automática por departamento
     * 
     * @param array $dadosMensagem Dados da mensagem recebida
     * @return array
     */
    public function processarMensagemRecebida($dadosMensagem)
    {
        $numero = $dadosMensagem['numero'] ?? null;
        $conversaId = $dadosMensagem['conversa_id'] ?? null;
        $conteudo = $dadosMensagem['conteudo'] ?? '';
        $departamentoId = $dadosMensagem['departamento_id'] ?? null;
        
        if (!$numero) {
            return ['success' => false, 'message' => 'Número não fornecido'];
        }

        // Se não foi fornecido departamento, identificar automaticamente
        if (!$departamentoId) {
            $departamentoId = $this->departamentoHelper->identificarDepartamento($numero, $conteudo);
            if (!$departamentoId) {
                error_log("⚠️ Não foi possível identificar departamento para número: {$numero}");
                return [
                    'success' => true,
                    'mensagem_enviada' => false,
                    'tipo_mensagem' => 'nenhuma',
                    'motivo' => 'Departamento não identificado'
                ];
            }
        }

        // Buscar mensagens automáticas do departamento
        $mensagemAutomatica = $this->obterMensagemAutomaticaPorDepartamento($departamentoId, $numero, $conversaId);
        
        if ($mensagemAutomatica) {
            $resultado = $this->enviarMensagemAutomatica($numero, $mensagemAutomatica['mensagem'], $conversaId);
            
            return [
                'success' => true,
                'mensagem_enviada' => true,
                'tipo_mensagem' => $mensagemAutomatica['tipo'],
                'conteudo_mensagem' => $mensagemAutomatica['mensagem'],
                'departamento_id' => $departamentoId,
                'resultado_envio' => $resultado
            ];
        }

        return [
            'success' => true,
            'mensagem_enviada' => false,
            'tipo_mensagem' => 'nenhuma',
            'motivo' => 'Nenhuma mensagem automática configurada para este departamento'
        ];
    }

    /**
     * [ obterMensagemAutomaticaPorDepartamento ] - Obtém mensagem automática específica do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @param string $numero Número do contato
     * @param int $conversaId ID da conversa
     * @return array|null Dados da mensagem automática
     */
    private function obterMensagemAutomaticaPorDepartamento($departamentoId, $numero, $conversaId = null)
    {
        try {
            // Verificar se há atendentes disponíveis no departamento
            $atendentesDisponiveis = $this->verificarAtendentesDisponiveisPorDepartamento($departamentoId);
            
            // Determinar tipo de mensagem baseado na situação
            $tipoMensagem = $this->determinarTipoMensagem($departamentoId, $atendentesDisponiveis, $conversaId);
            
            if (!$tipoMensagem) {
                return null;
            }

            // Buscar mensagem automática do departamento
            $mensagem = $this->mensagemAutomaticaModel->verificarMensagemAtiva($departamentoId, $tipoMensagem);
            
            if (!$mensagem) {
                error_log("ℹ️ Nenhuma mensagem automática encontrada para departamento {$departamentoId}, tipo: {$tipoMensagem}");
                return null;
            }

            // Verificar horário de funcionamento da mensagem
            if (!$this->mensagemAutomaticaModel->verificarHorarioFuncionamento($mensagem)) {
                error_log("ℹ️ Mensagem automática fora do horário de funcionamento: {$mensagem->titulo}");
                return null;
            }

            // Personalizar mensagem
            $mensagemPersonalizada = $this->personalizarMensagem($mensagem->mensagem, [
                'nome' => $this->obterNomeContato($numero),
                'departamento' => $this->obterNomeDepartamento($departamentoId),
                'numero' => $numero
            ]);

            return [
                'tipo' => $tipoMensagem,
                'mensagem' => $mensagemPersonalizada,
                'titulo' => $mensagem->titulo,
                'departamento_id' => $departamentoId
            ];

        } catch (Exception $e) {
            error_log("Erro ao obter mensagem automática por departamento: " . $e->getMessage());
            return null;
        }
    }

    /**
     * [ determinarTipoMensagem ] - Determina o tipo de mensagem automática baseado na situação
     * 
     * @param int $departamentoId ID do departamento
     * @param bool $atendentesDisponiveis Se há atendentes disponíveis
     * @param int $conversaId ID da conversa
     * @return string|null Tipo da mensagem
     */
    private function determinarTipoMensagem($departamentoId, $atendentesDisponiveis, $conversaId = null)
    {
        // Verificar se é primeira mensagem da conversa
        $primeiraMensagem = $this->verificarPrimeiraMensagem($conversaId);
        
        if ($primeiraMensagem) {
            return 'boas_vindas';
        }

        if (!$atendentesDisponiveis) {
            return 'ausencia';
        }

        // Verificar horário de funcionamento geral
        $horarioInfo = $this->verificarHorarioFuncionamento();
        if (!$horarioInfo['dentro_horario']) {
            return 'fora_horario';
        }

        return null; // Nenhuma mensagem automática necessária
    }

    /**
     * [ verificarPrimeiraMensagem ] - Verifica se é a primeira mensagem da conversa
     * 
     * @param int $conversaId ID da conversa
     * @return bool
     */
    private function verificarPrimeiraMensagem($conversaId)
    {
        if (!$conversaId) {
            return true; // Se não há conversa, assume primeira mensagem
        }

        try {
            // Buscar total de mensagens da conversa
            $sql = "SELECT COUNT(*) as total FROM mensagens WHERE conversa_id = :conversa_id";
            $db = new Database();
            $db->query($sql);
            $db->bind(':conversa_id', $conversaId);
            $resultado = $db->resultado();
            
            return $resultado && $resultado->total <= 1;
        } catch (Exception $e) {
            error_log("Erro ao verificar primeira mensagem: " . $e->getMessage());
            return true; // Em caso de erro, assume primeira mensagem
        }
    }

    /**
     * [ verificarAtendentesDisponiveisPorDepartamento ] - Verifica se há atendentes disponíveis no departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return bool
     */
    private function verificarAtendentesDisponiveisPorDepartamento($departamentoId)
    {
        try {
            $sql = "
                SELECT COUNT(*) as total
                FROM usuarios u
                JOIN atendentes_departamento ad ON u.id = ad.usuario_id
                WHERE ad.departamento_id = :departamento_id
                AND ad.status = 'ativo'
                AND u.status IN ('ativo', 'ausente', 'ocupado')
                AND u.ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ";
            
            $db = new Database();
            $db->query($sql);
            $db->bind(':departamento_id', $departamentoId);
            $resultado = $db->resultado();
            
            return $resultado && $resultado->total > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar atendentes por departamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * [ verificarHorarioFuncionamento ] - Verifica se está dentro do horário de funcionamento geral
     * 
     * @return array ['dentro_horario' => bool, 'mensagem' => string]
     */
    public function verificarHorarioFuncionamento()
    {
        $agora = new DateTime();
        $diaSemana = $agora->format('N'); // 1 (Segunda) a 7 (Domingo)
        $horaAtual = $agora->format('H:i:s');
        
        // Horário padrão de funcionamento: Segunda a Sexta, 08:00 às 18:00
        $horarioInicio = '08:00:00';
        $horarioFim = '18:00:00';
        
        // Verificar se é dia útil (Segunda a Sexta)
        if ($diaSemana >= 1 && $diaSemana <= 5) {
            $dentroHorario = $horaAtual >= $horarioInicio && $horaAtual <= $horarioFim;
            return [
                'dentro_horario' => $dentroHorario,
                'mensagem' => $dentroHorario ? 
                    'Dentro do horário de funcionamento' : 
                    "Fora do horário de funcionamento ({$horarioInicio} às {$horarioFim})"
            ];
        } else {
            return [
                'dentro_horario' => false,
                'mensagem' => 'Fim de semana - fora do horário de funcionamento'
            ];
        }
    }

    /**
     * [ personalizarMensagem ] - Personaliza a mensagem com dados específicos
     * 
     * @param string $mensagem Mensagem base
     * @param array $dados Dados para personalização
     * @return string
     */
    private function personalizarMensagem($mensagem, $dados)
    {
        // Substituir placeholders
        $substituicoes = [
            '{nome}' => $dados['nome'] ?? 'Cliente',
            '{departamento}' => $dados['departamento'] ?? 'nosso departamento',
            '{numero}' => $dados['numero'] ?? '',
            '{data}' => date('d/m/Y'),
            '{hora}' => date('H:i'),
            '{dia_semana}' => $this->obterDiaSemana(),
            '{empresa}' => 'ChatSerpro'
        ];

        foreach ($substituicoes as $placeholder => $valor) {
            $mensagem = str_replace($placeholder, $valor, $mensagem);
        }

        return $mensagem;
    }

    /**
     * [ obterNomeContato ] - Obtém nome do contato
     * 
     * @param string $numero Número do contato
     * @return string
     */
    private function obterNomeContato($numero)
    {
        try {
            $sql = "SELECT nome FROM contatos WHERE numero = :numero LIMIT 1";
            $db = new Database();
            $db->query($sql);
            $db->bind(':numero', $numero);
            $resultado = $db->resultado();
            
            return $resultado ? $resultado->nome : 'Cliente';
        } catch (Exception $e) {
            return 'Cliente';
        }
    }

    /**
     * [ obterNomeDepartamento ] - Obtém nome do departamento
     * 
     * @param int $departamentoId ID do departamento
     * @return string
     */
    private function obterNomeDepartamento($departamentoId)
    {
        try {
            $sql = "SELECT nome FROM departamentos WHERE id = :id LIMIT 1";
            $db = new Database();
            $db->query($sql);
            $db->bind(':id', $departamentoId);
            $resultado = $db->resultado();
            
            return $resultado ? $resultado->nome : 'nosso departamento';
        } catch (Exception $e) {
            return 'nosso departamento';
        }
    }

    /**
     * [ obterDiaSemana ] - Obtém o nome do dia da semana
     * 
     * @return string
     */
    private function obterDiaSemana()
    {
        $dias = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        return $dias[date('N')] ?? 'Hoje';
    }

    /**
     * [ enviarMensagemAutomatica ] - Envia mensagem automática via API Serpro
     * 
     * @param string $numero Número do destinatário
     * @param string $mensagem Mensagem a ser enviada
     * @param int $conversaId ID da conversa (opcional)
     * @return array
     */
    public function enviarMensagemAutomatica($numero, $mensagem, $conversaId = null)
    {
        try {
            // Enviar via API Serpro
            $resultado = $this->serproApi->enviarMensagem($numero, $mensagem);
            
            if ($resultado['success']) {
                // Salvar mensagem no banco se conversaId fornecido
                if ($conversaId) {
                    try {
                        $sql = "INSERT INTO mensagens (conversa_id, tipo, conteudo, direcao, status_entrega, criado_em) 
                                VALUES (:conversa_id, 'texto', :conteudo, 'saida', 'enviado', NOW())";
                        $db = new Database();
                        $db->query($sql);
                        $db->bind(':conversa_id', $conversaId);
                        $db->bind(':conteudo', $mensagem);
                        $db->executa();
                    } catch (Exception $e) {
                        error_log("Erro ao salvar mensagem automática no banco: " . $e->getMessage());
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Mensagem automática enviada com sucesso',
                    'message_id' => $resultado['message_id'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao enviar mensagem automática: ' . ($resultado['message'] ?? 'Erro desconhecido')
                ];
            }
        } catch (Exception $e) {
            error_log("Erro ao enviar mensagem automática: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao enviar mensagem automática: ' . $e->getMessage()
            ];
        }
    }
} 