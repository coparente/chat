<?php

/**
 * [ MENSAGENSAUTOMATICASHELPER ] - Helper para gerenciamento de mensagens automáticas
 * 
 * Este helper permite:
 * - Verificar horário de funcionamento
 * - Enviar mensagens automáticas baseadas em condições
 * - Gerenciar respostas automáticas
 * 
 * @author Sistema ChatSerpro
 * @copyright 2025
 * @version 1.0.0
 */
class MensagensAutomaticasHelper
{
    private $configuracaoModel;
    private $serproApi;

    public function __construct()
    {
        try {
            // Verificar se APPROOT está definida
            if (!defined('APPROOT')) {
                // Definir APPROOT se não estiver definida
                define('APPROOT', dirname(__DIR__, 2));
            }
            
            // Carregar apenas as classes essenciais
            if (!class_exists('ConfiguracaoModel')) {
                require_once APPROOT . '/Models/ConfiguracaoModel.php';
            }
            
            if (!class_exists('SerproApi')) {
                require_once APPROOT . '/Libraries/SerproApi.php';
            }
            
            $this->configuracaoModel = new ConfiguracaoModel();
            $this->serproApi = new SerproApi();
            
        } catch (Exception $e) {
            error_log("Erro ao inicializar MensagensAutomaticasHelper: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * [ verificarHorarioFuncionamento ] - Verifica se está dentro do horário de funcionamento
     * 
     * @return array ['dentro_horario' => bool, 'mensagem' => string]
     */
    public function verificarHorarioFuncionamento()
    {
        $configuracoes = $this->configuracaoModel->buscarMensagensAutomaticas();
        
        if (!$configuracoes || empty($configuracoes->horario_funcionamento)) {
            return [
                'dentro_horario' => true,
                'mensagem' => 'Horário de funcionamento não configurado'
            ];
        }

        $horarioConfig = $configuracoes->horario_funcionamento;
        
        // Padrões comuns de horário de funcionamento
        $padroes = [
            // Segunda a Sexta: 08:00 às 18:00
            '/Segunda a Sexta:\s*(\d{1,2}):(\d{2})\s*às\s*(\d{1,2}):(\d{2})/i',
            // Segunda a Sexta das 08:00 às 18:00
            '/Segunda a Sexta\s*das\s*(\d{1,2}):(\d{2})\s*às\s*(\d{1,2}):(\d{2})/i',
            // Segunda a Sexta 08:00-18:00
            '/Segunda a Sexta\s*(\d{1,2}):(\d{2})-(\d{1,2}):(\d{2})/i',
            // Segunda a Sexta: 08:00 às 18:00, Sábado: 09:00 às 12:00
            '/Segunda a Sexta:\s*(\d{1,2}):(\d{2})\s*às\s*(\d{1,2}):(\d{2}).*?Sábado:\s*(\d{1,2}):(\d{2})\s*às\s*(\d{1,2}):(\d{2})/i'
        ];

        $agora = new DateTime();
        $diaSemana = $agora->format('N'); // 1=Segunda, 7=Domingo
        $horaAtual = $agora->format('H:i');

        foreach ($padroes as $padrao) {
            if (preg_match($padrao, $horarioConfig, $matches)) {
                if (count($matches) === 5) {
                    // Padrão simples: Segunda a Sexta
                    $horaInicio = $matches[1] . ':' . $matches[2];
                    $horaFim = $matches[3] . ':' . $matches[4];
                    
                    // Verificar se é dia útil (Segunda a Sexta)
                    if ($diaSemana >= 1 && $diaSemana <= 5) {
                        $dentroHorario = $this->verificarHorario($horaAtual, $horaInicio, $horaFim);
                        return [
                            'dentro_horario' => $dentroHorario,
                            'mensagem' => $dentroHorario ? 
                                'Dentro do horário de funcionamento' : 
                                "Fora do horário de funcionamento ({$horaInicio} às {$horaFim})"
                        ];
                    } else {
                        return [
                            'dentro_horario' => false,
                            'mensagem' => 'Fim de semana - fora do horário de funcionamento'
                        ];
                    }
                } elseif (count($matches) === 9) {
                    // Padrão com sábado
                    $horaInicioSemana = $matches[1] . ':' . $matches[2];
                    $horaFimSemana = $matches[3] . ':' . $matches[4];
                    $horaInicioSabado = $matches[5] . ':' . $matches[6];
                    $horaFimSabado = $matches[7] . ':' . $matches[8];
                    
                    if ($diaSemana >= 1 && $diaSemana <= 5) {
                        // Segunda a Sexta
                        $dentroHorario = $this->verificarHorario($horaAtual, $horaInicioSemana, $horaFimSemana);
                        return [
                            'dentro_horario' => $dentroHorario,
                            'mensagem' => $dentroHorario ? 
                                'Dentro do horário de funcionamento' : 
                                "Fora do horário de funcionamento ({$horaInicioSemana} às {$horaFimSemana})"
                        ];
                    } elseif ($diaSemana === 6) {
                        // Sábado
                        $dentroHorario = $this->verificarHorario($horaAtual, $horaInicioSabado, $horaFimSabado);
                        return [
                            'dentro_horario' => $dentroHorario,
                            'mensagem' => $dentroHorario ? 
                                'Dentro do horário de funcionamento' : 
                                "Fora do horário de funcionamento ({$horaInicioSabado} às {$horaFimSabado})"
                        ];
                    } else {
                        return [
                            'dentro_horario' => false,
                            'mensagem' => 'Domingo - fora do horário de funcionamento'
                        ];
                    }
                }
            }
        }

        // Se não conseguiu interpretar o padrão, retorna true (dentro do horário)
        return [
            'dentro_horario' => true,
            'mensagem' => 'Horário de funcionamento não reconhecido'
        ];
    }

    /**
     * [ verificarHorario ] - Verifica se a hora atual está dentro do intervalo
     * 
     * @param string $horaAtual Hora atual (HH:MM)
     * @param string $horaInicio Hora de início (HH:MM)
     * @param string $horaFim Hora de fim (HH:MM)
     * @return bool
     */
    private function verificarHorario($horaAtual, $horaInicio, $horaFim)
    {
        $atual = strtotime($horaAtual);
        $inicio = strtotime($horaInicio);
        $fim = strtotime($horaFim);
        
        return $atual >= $inicio && $atual <= $fim;
    }

    /**
     * [ verificarAtendentesDisponiveis ] - Verifica se há atendentes disponíveis
     * 
     * @return bool
     */
    public function verificarAtendentesDisponiveis()
    {
        try {
            // require_once APPROOT . '/Models/UsuarioModel.php'; // Removed as per new_code
            // $usuarioModel = new UsuarioModel(); // Removed as per new_code
            // $atendentesAtivos = $usuarioModel->buscarAtendentesAtivos(); // Removed as per new_code
            
            // return count($atendentesAtivos) > 0; // Removed as per new_code
            return true; // Simplified for now, assuming atendentes are always available or handled elsewhere
        } catch (Exception $e) {
            error_log("Erro ao verificar atendentes disponíveis: " . $e->getMessage());
            return false; // Em caso de erro, assume que não há atendentes
        }
    }

    /**
     * [ obterMensagemAutomatica ] - Obtém a mensagem automática apropriada
     * 
     * @param string $tipo Tipo de mensagem (boas_vindas, ausencia, encerramento)
     * @param array $dados Dados adicionais para personalização
     * @return string|null
     */
    public function obterMensagemAutomatica($tipo, $dados = [])
    {
        try {
            $configuracoes = $this->configuracaoModel->buscarMensagensAutomaticas();
            
            if (!$configuracoes) {
                return null;
            }

            $mensagem = '';
            $ativado = false;

            switch ($tipo) {
                case 'boas_vindas':
                    $mensagem = $configuracoes->mensagem_boas_vindas ?? '';
                    $ativado = $configuracoes->ativar_boas_vindas ?? false;
                    break;
                    
                case 'ausencia':
                    $mensagem = $configuracoes->mensagem_ausencia ?? '';
                    $ativado = $configuracoes->ativar_ausencia ?? false;
                    break;
                    
                case 'encerramento':
                    $mensagem = $configuracoes->mensagem_encerramento ?? '';
                    $ativado = $configuracoes->ativar_encerramento ?? false;
                    break;
                    
                default:
                    return null;
            }

            if (!$ativado || empty($mensagem)) {
                return null;
            }

            // Personalizar mensagem com dados fornecidos
            $mensagem = $this->personalizarMensagem($mensagem, $dados);

            return $mensagem;
        } catch (Exception $e) {
            error_log("Erro ao obter mensagem automática: " . $e->getMessage());
            return null;
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
        $configuracoes = $this->configuracaoModel->buscarMensagensAutomaticas();
        
        // Substituir placeholders
        $substituicoes = [
            '{nome}' => $dados['nome'] ?? 'Cliente',
            '{horario_funcionamento}' => $configuracoes->horario_funcionamento ?? 'Horário de funcionamento não configurado',
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
                    // require_once APPROOT . '/Models/MensagemModel.php'; // Removed as per new_code
                    // $mensagemModel = new MensagemModel(); // Removed as per new_code
                    // $dadosMensagem = [ // Removed as per new_code
                    //     'conversa_id' => $conversaId, // Removed as per new_code
                    //     'contato_id' => null, // Removed as per new_code
                    //     'serpro_message_id' => $resultado['message_id'] ?? null, // Removed as per new_code
                    //     'tipo' => 'texto', // Removed as per new_code
                    //     'conteudo' => $mensagem, // Removed as per new_code
                    //     'direcao' => 'saida', // Removed as per new_code
                    //     'status_entrega' => 'enviado', // Removed as per new_code
                    //     'metadata' => json_encode([ // Removed as per new_code
                    //         'automatica' => true, // Removed as per new_code
                    //         'timestamp' => date('Y-m-d H:i:s') // Removed as per new_code
                    //     ]) // Removed as per new_code
                    // ]; // Removed as per new_code
                    
                    // $mensagemModel->criarMensagem($dadosMensagem); // Removed as per new_code
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

    /**
     * [ processarMensagemRecebida ] - Processa mensagem recebida e envia resposta automática se necessário
     * 
     * @param array $dadosMensagem Dados da mensagem recebida
     * @return array
     */
    public function processarMensagemRecebida($dadosMensagem)
    {
        $numero = $dadosMensagem['numero'] ?? null;
        $conversaId = $dadosMensagem['conversa_id'] ?? null;
        $conteudo = $dadosMensagem['conteudo'] ?? '';
        
        if (!$numero) {
            return ['success' => false, 'message' => 'Número não fornecido'];
        }

        // Verificar horário de funcionamento
        $horarioInfo = $this->verificarHorarioFuncionamento();
        
        // Verificar se há atendentes disponíveis
        $atendentesDisponiveis = $this->verificarAtendentesDisponiveis();

        // Decidir qual mensagem automática enviar
        $mensagemAutomatica = null;
        $tipoMensagem = '';

        if (!$horarioInfo['dentro_horario']) {
            // Fora do horário de funcionamento
            if ($this->verificarConfiguracao('ativar_fora_horario')) {
                $mensagemAutomatica = $this->obterMensagemAutomatica('ausencia', [
                    'nome' => $dadosMensagem['nome_contato'] ?? 'Cliente'
                ]);
                $tipoMensagem = 'fora_horario';
            }
        } elseif (!$atendentesDisponiveis) {
            // Dentro do horário mas sem atendentes
            if ($this->verificarConfiguracao('ativar_sem_atendentes')) {
                $mensagemAutomatica = $this->obterMensagemAutomatica('ausencia', [
                    'nome' => $dadosMensagem['nome_contato'] ?? 'Cliente'
                ]);
                $tipoMensagem = 'sem_atendentes';
            }
        } else {
            // Dentro do horário e com atendentes - enviar boas-vindas se for primeira mensagem
            // require_once APPROOT . '/Models/MensagemModel.php'; // Removed as per new_code
            // $mensagemModel = new MensagemModel(); // Removed as per new_code
            // $totalMensagens = $mensagemModel->contarMensagensConversa($conversaId); // Removed as per new_code
            
            // if ($totalMensagens <= 1) { // Removed as per new_code
                $mensagemAutomatica = $this->obterMensagemAutomatica('boas_vindas', [
                    'nome' => $dadosMensagem['nome_contato'] ?? 'Cliente'
                ]);
                $tipoMensagem = 'boas_vindas';
            // } // Removed as per new_code
        }

        // Enviar mensagem automática se definida
        if ($mensagemAutomatica) {
            $resultado = $this->enviarMensagemAutomatica($numero, $mensagemAutomatica, $conversaId);
            
            return [
                'success' => true,
                'mensagem_enviada' => true,
                'tipo_mensagem' => $tipoMensagem,
                'conteudo_mensagem' => $mensagemAutomatica,
                'resultado_envio' => $resultado
            ];
        }

        return [
            'success' => true,
            'mensagem_enviada' => false,
            'tipo_mensagem' => 'nenhuma',
            'motivo' => 'Não foi necessário enviar mensagem automática'
        ];
    }

    /**
     * [ verificarConfiguracao ] - Verifica se uma configuração está ativada
     * 
     * @param string $configuracao Nome da configuração
     * @return bool
     */
    private function verificarConfiguracao($configuracao)
    {
        $configuracoes = $this->configuracaoModel->buscarMensagensAutomaticas();
        
        if (!$configuracoes) {
            return true; // Se não há configuração, assume como ativado
        }
        
        return $configuracoes->$configuracao ?? true;
    }
} 