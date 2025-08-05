<?php

/**
 * [ HELPER ] - Classe auxiliar responsável por prover métodos estáticos para manipular e validar dados no sistema.
 * 
 * @author Cleyton Oliveira <coparente@tjgo.jus.br>
 * @copyright 2025-2025 TJGO
 * @version 1.0.1
 */

class Helper
{

    /**
     * Exibe uma mensagem de feedback no sistema
     * @param string $nome Nome da sessão para armazenar a mensagem
     * @param string $texto Texto da mensagem a ser exibida
     * @param string $classe Classe CSS para estilização da mensagem
     */
    public static function mensagem($nome, $texto = null, $classe = null)
    {

        if (!empty($nome)):
            if (!empty($texto) && empty($_SESSION[$nome])):
                if (!empty($_SESSION[$nome])):
                    unset($_SESSION[$nome]);
                endif;

                $_SESSION[$nome] = $texto;
                $_SESSION[$nome . 'classe'] = $classe;

            elseif (!empty($_SESSION[$nome]) && empty($texto)):
                $classe = !empty($_SESSION[$nome . 'classe']) ? $_SESSION[$nome . 'classe'] : 'alert alert-success';
                echo '<div class="' . $classe . '">' . $_SESSION[$nome] . '</div>';

                unset($_SESSION[$nome]);
                unset($_SESSION[$nome . 'classe']);
            endif;
        endif;
    }

    /**
     * Exibe uma mensagem de feedback no sistema usando SweetAlert
     * @param string $nome Nome da sessão para armazenar a mensagem
     * @param string $texto Texto da mensagem a ser exibida
     * @param string $tipo Tipo de mensagem (success, error, warning, info)
     */
    public static function mensagemSweetAlert($nome, $texto = null, $tipo = 'success')
    {
        if (!empty($nome)) {
            if (!empty($texto)) {
                $_SESSION[$nome . '_sweet'] = [
                    'texto' => $texto,
                    'tipo' => $tipo
                ];
            } elseif (isset($_SESSION[$nome . '_sweet'])) {
                $mensagem = $_SESSION[$nome . '_sweet'];
                unset($_SESSION[$nome . '_sweet']);

                echo "<script>
                    Swal.fire({
                        icon: '" . $mensagem['tipo'] . "',
                        title: 'Aviso',
                        html: '" . addslashes($mensagem['texto']) . "',
                        confirmButtonColor: '#3085d6'
                    });
                </script>";
            }
        }
    }

    /**
     * Verifica se o usuário está logado
     * @return bool True se o usuário está logado, false caso contrário
     */
    public static function estaLogado()
    {
        if (isset($_SESSION['usuario_id'])) :
            return true;
        else:
            return false;
        endif;
    }

    /**
     * Verifica se um nome é válido
     * @param string $nome Nome a ser verificado
     * @return bool True se o nome é válido, false caso contrário
     */
    public static function checarNome($nome)
    {
        if (!preg_match('/^([áÁàÀãÃâÂéÉèÈêÊíÍìÌóÓòÒõÕôÔúÚùÙçÇaA-zZ]+)+((\s[áÁàÀãÃâÂéÉèÈêÊíÍìÌóÓòÒõÕôÔúÚùÙçÇaA-zZ]+)+)?$/', $nome)):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * Verifica se um email é válido
     * @param string $email Email a ser verificado
     * @return bool True se o email é válido, false caso contrário
     */
    public static function checarEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * Formata uma data no formato brasileiro
     * @param string $data Data a ser formatada
     * @return string Data formatada ou false se a data não for válida
     */
    public static function dataBr($data)
    {
        if (isset($data)):
            return date('d/m/Y H:i', strtotime($data));
        else:
            return false;
        endif;
    }

    //redireciona para uma url com header location

    // public static function redirecionar($url)
    // {
    //     header("Location:" . URL . DIRECTORY_SEPARATOR . $url);
    // }

    public static function redirecionar($url)
    {
        // Garantir que as mensagens sejam processadas antes do redirecionamento
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Redirecionar para a URL especificada
        header("Location:" . URL . DIRECTORY_SEPARATOR . $url);
        exit();
    }

    /**
     * Transforma uma string no formato de URL amigável e retorna a string convertida!
     * @param string $string Uma string qualquer
     * @return string Uma URL amigável válida 
     */
    public static function urlAmigavel($string)
    {
        $mapa = [];
        $mapa['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        $mapa['b'] = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
        $url = strtr(utf8_decode($string), utf8_decode($mapa['a']), $mapa['b']);
        $url = strip_tags(trim($url));
        $url = str_replace(' ', '-', $url);
        $url = str_replace(['-----', '----', '---', '--'], '-', $url);

        return strtolower(utf8_decode($url));
    }

    /**
     * Resume Texto: Limita a quantidade de palavras de um texto a serem exibidas!
     * @param string $texto Um texto qualquer
     * @param int $limite Limite de palavras
     * @param string $continue (opcional) Se não for informado recebe "..." 
     * @return string Texto Resumido
     */
    public static function resumirTexto($texto, $limite, $continue = null)
    {

        $textoLimpo = strip_tags(trim($texto));
        $limiteTexto = (int) $limite;

        $array = explode(' ', $textoLimpo);
        $totalPalavras = count($array);
        $textoResumido = implode(' ', array_slice($array, 0, $limiteTexto));

        $lerMais = (empty($continue) ? ' ...' : ' ' . $continue);
        $resultado = ($limite < $totalPalavras ? $textoResumido . $lerMais : $texto);

        return $resultado;
    }

    /**
     * Data Atual: Retorna a data atual no formato sábado, 12 de setembro de 2020 
     * @return $dataFormatada string Data formatada
     */
    public static function dataAtual()
    {
        $diaMes = date('d');
        $diaSemana = date('w');
        $mes = date('n') - 1;
        $ano = date('Y');

        $nomeSemana = ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];

        $nomeMes = ["janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"];

        $dataFormatada = $nomeSemana[$diaSemana] . ', ' . $diaMes . ' de ' . $nomeMes[$mes] . ' de ' . $ano;

        return $dataFormatada;
    }

    /**
     * Registra uma atividade no sistema
     * @param string $acao Ação realizada
     * @param string $descricao Descrição detalhada da atividade
     * @return bool True se a atividade foi registrada com sucesso, false caso contrário
     */
    public static function registrarAtividade($acao, $descricao)
    {
        if (isset($_SESSION['usuario_id'])) {
            $atividadeModel = new AtividadeModel();
            return $atividadeModel->registrarAtividade(
                $_SESSION['usuario_id'],
                $acao,
                $descricao
            );
        }
        return false;
    }

    public static function formatarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    public static function formatarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }


    public static function formatarTelefone($telefone)
    {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
    }

    /**
     * Envia uma mensagem para um número de telefone
     * @param string $numero Número de telefone
     * @param string $mensagem Mensagem a ser enviada
     * @return array Retorna o status e a resposta da mensagem
     */
    public static function enviarMensagem($numero, $mensagem)
    {
        $url = "https://unoapidj.helpersti.online/v1.25.2/556232162569/messages";
        $token = "any"; // Substitua pelo seu token real

        $data = [
            "to" => $numero,
            "type" => "text",
            "text" => [
                "body" => $mensagem
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            "status" => $httpCode,
            "response" => json_decode($response, true)
        ];
    }

    /**
     * Envia uma mensagem para um número de telefone com mídia
     * @param string $numero Número de telefone
     * @param string $mensagem Mensagem a ser enviada
     * @param string $tipo Tipo de mídia (image, audio, video, document)
     * @param string $arquivo URL do arquivo
     * @return array Retorna o status e a resposta da mensagem
     */
    public static function enviarMensagemComMedia($numero, $mensagem, $tipo = 'text', $arquivo = null)
    {
        $url = "https://unoapidj.helpersti.online/v1.25.2/556232162569/messages";
        $token = "any";

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $numero,
            "type" => $tipo
        ];

        // var_dump($arquivo);
        // die();
        if ($tipo === 'text') {
            $data["text"] = ["body" => $mensagem];
        } else if ($tipo === 'document') {
            // // Lê o arquivo e converte para base64
            // $file_data = file_get_contents($arquivo['tmp_name']);
            // var_dump($file_data);
            // die();

            $data["document"] = [
                "link" => $arquivo['tmp_name'],
                "caption" => $mensagem,
                "filename" => $arquivo['name']
            ];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                "status" => 500,
                "response" => ["error" => $error]
            ];
        }

        return [
            "status" => $httpCode,
            "response" => json_decode($response, true)
        ];
    }

    /**
     * [ formatarValorParaBD ] - Formata valor monetário para o formato do banco de dados
     * 
     * @param string $valor - Valor monetário no formato brasileiro (ex: 1.234,56)
     * @return float - Valor formatado para o banco de dados (ex: 1234.56)
     */
    public static function formatarValorParaBD($valor)
    {
        // Remove pontos de milhar e substitui vírgula por ponto
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return (float) $valor;
    }

    /**
     * Conta o número de mensagens não lidas para um usuário
     * 
     * @return int Número de mensagens não lidas
     */
    public static function contarMensagensNaoLidas()
    {
        if (!isset($_SESSION['usuario_id'])) {
            return 0;
        }

        require_once APPROOT . '/Models/MensagemModel.php';
        $mensagemModel = new MensagemModel();
        return $mensagemModel->contarMensagensNaoLidas($_SESSION['usuario_id']);
    }

    /**
     * Retorna o badge com o contador de mensagens não lidas
     * 
     * @return string HTML do badge com o contador
     */
    public static function badgeMensagensNaoLidas()
    {
        $total = self::contarMensagensNaoLidas();

        if ($total > 0) {
            return '<span class="badge bg-danger float-end">' . $total . '</span>';
        }

        return '';
    }

    /**
     * [ verificarNovasMensagens ] - Verifica se há novas mensagens e retorna o script para exibir o SweetAlert
     * 
     * @return string Script JS para exibir o SweetAlert se houver novas mensagens
     */
    public static function verificarNovasMensagens()
    {
        if (!isset($_SESSION['usuario_id'])) {
            return '';
        }

        $db = new Database();
        $sql = "SELECT COUNT(*) as total FROM cuc_mensagens_destinatarios md
            JOIN cuc_mensagens_sistema ms ON md.mensagem_id = ms.id
            WHERE md.usuario_id = :usuario_id 
            AND md.lida = false 
            AND ms.ativa = true
            AND (ms.data_expiracao IS NULL OR ms.data_expiracao >= CURRENT_DATE)";

        $db->query($sql);
        $db->bind(':usuario_id', $_SESSION['usuario_id']);
        $resultado = $db->resultado();

        if ($resultado && $resultado->total > 0) {
            // Verifica se já exibimos o alerta nesta sessão
            if (!isset($_SESSION['alerta_mensagens_exibido']) || !$_SESSION['alerta_mensagens_exibido']) {
                $_SESSION['alerta_mensagens_exibido'] = true;

                return "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Novas Mensagens!',
                        text: 'Você tem {$resultado->total} " . ($resultado->total > 1 ? 'novas mensagens' : 'nova mensagem') . " não " . ($resultado->total > 1 ? 'lidas' : 'lida') . ".',
                        icon: 'info',
                        confirmButtonText: 'Ver Mensagens',
                        showCancelButton: true,
                        cancelButtonText: 'Mais Tarde'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '" . URL . "/mensagens/index';
                        }
                    });
                });
            </script>";
            }
        }

        return '';
    }

    /**
     * Função para validar o cpf ou cnpj validarCPFCNPJ
     * @param mixed $str
     * @return bool
     */
    public static function validarCPFCNPJ($str)
    {
        // Remove caracteres não numéricos
        $str = preg_replace('/[^0-9]/', '', $str);

        // CPF
        if (strlen($str) === 11) {
            $cpf = $str;
            // Verifica se todos os dígitos são iguais
            if (preg_match('/^(\d)\1+$/', $cpf)) return false;

            // Validação do CPF
            $soma = 0;
            for ($i = 0; $i < 9; $i++) {
                $soma += intval($cpf[$i]) * (10 - $i);
            }
            $resto = 11 - ($soma % 11);
            if ($resto >= 10) $resto = 0;
            if ($resto != intval($cpf[9])) return false;

            $soma = 0;
            for ($i = 0; $i < 10; $i++) {
                $soma += intval($cpf[$i]) * (11 - $i);
            }
            $resto = 11 - ($soma % 11);
            if ($resto >= 10) $resto = 0;
            if ($resto != intval($cpf[10])) return false;

            return true;
        }
        // CNPJ
        elseif (strlen($str) === 14) {
            $cnpj = $str;
            // Validação do CNPJ
            $soma = 0;
            $multiplicador = 2;
            for ($i = 11; $i >= 0; $i--) {
                $soma += intval($cnpj[$i]) * $multiplicador;
                $multiplicador = ($multiplicador == 9) ? 2 : $multiplicador + 1;
            }
            $resto = $soma % 11;
            if ($resto < 2) {
                $digito_verificador1 = 0;
            } else {
                $digito_verificador1 = 11 - $resto;
            }
            if ($digito_verificador1 != intval($cnpj[12])) return false;

            $soma = 0;
            $multiplicador = 2;
            for ($i = 12; $i >= 0; $i--) {
                $soma += intval($cnpj[$i]) * $multiplicador;
                $multiplicador = ($multiplicador == 9) ? 2 : $multiplicador + 1;
            }
            $resto = $soma % 11;
            if ($resto < 2) {
                $digito_verificador2 = 0;
            } else {
                $digito_verificador2 = 11 - $resto;
            }
            if ($digito_verificador2 != intval($cnpj[13])) return false;

            return true;
        }
        return false;
    }

    /**
     * [ montarQueryString ] - Monta a query string para paginação com filtros
     * 
     * @param array $params Parâmetros atuais da URL
     * @param array $novosParams Novos parâmetros a serem adicionados/substituídos
     * @return string Query string formatada
     */
    public static function montarQueryString($params, $novosParams = [])
    {
        // Mesclar parâmetros atuais com novos parâmetros
        $queryParams = array_merge($params, $novosParams);

        // Remover parâmetros vazios
        foreach ($queryParams as $key => $value) {
            if ($value === '' || $value === null) {
                unset($queryParams[$key]);
            }
        }

        // Se não houver parâmetros, retornar string vazia
        if (empty($queryParams)) {
            return '';
        }

        // Construir a query string
        return '?' . http_build_query($queryParams);
    }

    /**
     * [ csrfField ] - Gera um campo hidden com o token CSRF para formulários
     * 
     * @return string HTML do campo hidden com token CSRF
     */
    public static function csrfField()
    {
        $token = obterCsrf();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * [ csrfToken ] - Obtém o token CSRF atual
     * 
     * @return string Token CSRF
     */
    public static function csrfToken()
    {
        return obterCsrf();
    }

    /**
     * [ csrfMeta ] - Gera meta tag com token CSRF para uso em JavaScript
     * 
     * @return string HTML da meta tag com token CSRF
     */
    public static function csrfMeta()
    {
        $token = obterCsrf();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * [ old ] - Recupera valores antigos de formulário após erro de validação
     * 
     * @param string $key Chave do campo
     * @param mixed $default Valor padrão se não houver valor antigo
     * @return mixed Valor antigo ou padrão
     */
    public static function old($key, $default = '')
    {
        if (isset($_SESSION['old_input'][$key])) {
            $value = $_SESSION['old_input'][$key];
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
    }

    /**
     * [ flashInput ] - Armazena dados do formulário para recuperação após erro
     * 
     * @param array $input Dados do formulário
     */
    public static function flashInput($input)
    {
        $_SESSION['old_input'] = $input;
    }

    /**
     * [ clearOldInput ] - Limpa dados antigos do formulário
     */
    public static function clearOldInput()
    {
        unset($_SESSION['old_input']);
    }

    /**
     * [ route ] - Gera URL para uma rota nomeada (funcionalidade futura)
     * 
     * @param string $name Nome da rota
     * @param array $parameters Parâmetros da rota
     * @return string URL da rota
     */
    public static function route($name, $parameters = [])
    {
        // Esta função pode ser implementada futuramente quando tivermos rotas nomeadas
        return URL . '/' . ltrim($name, '/');
    }

    /**
     * [ asset ] - Gera URL para um asset público
     * 
     * @param string $path Caminho do asset
     * @return string URL completa do asset
     */
    public static function asset($path)
    {
        return URL . '/public/' . ltrim($path, '/');
    }

    /**
     * [ dd ] - Debug and die - Exibe variável formatada e para execução
     * 
     * @param mixed $var Variável a ser exibida
     */
    public static function dd($var)
    {
        echo '<pre style="background: #f4f4f4; padding: 15px; border: 1px solid #ddd; margin: 10px; border-radius: 5px;">';
        var_dump($var);
        echo '</pre>';
        die();
    }

    /**
     * [ dump ] - Exibe variável formatada sem parar execução
     * 
     * @param mixed $var Variável a ser exibida
     */
    public static function dump($var)
    {
        echo '<pre style="background: #f4f4f4; padding: 15px; border: 1px solid #ddd; margin: 10px; border-radius: 5px;">';
        var_dump($var);
        echo '</pre>';
    }
}
