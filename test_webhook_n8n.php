<?php
/**
 * Script de teste para webhooks do N8N
 * Simula diferentes tipos de mensagens enviadas pelo N8N
 */

// URL do webhook
$webhookUrl = 'https://coparente.top/chat/webhook/serpro';

// Exemplos de mensagens do N8N
$exemplos = [
    // Mensagem de texto
    'texto' => [
        [
            'body' => [
                'messaging_product' => 'whatsapp',
                'metadata' => [
                    'display_phone_number' => '556236114822',
                    'phone_number_id' => '749709211549367',
                    'webhook_object_id' => '6873f1e291c9565c68150fcf'
                ],
                'contacts' => [
                    [
                        'profile' => [
                            'name' => 'João Teste'
                        ],
                        'wa_id' => '556296185892'
                    ]
                ],
                'messages' => [
                    [
                        'from' => '556296185892',
                        'id' => 'wamid.TEST_TEXT_' . uniqid(),
                        'timestamp' => (string)time(),
                        'type' => 'text',
                        'text' => [
                            'body' => 'Esta é uma mensagem de teste do N8N'
                        ],
                        'document' => ['id' => '', 'filename' => '', 'mime_type' => ''],
                        'image' => ['id' => '', 'mime_type' => ''],
                        'button' => ['payload' => '', 'text' => ''],
                        'audio' => ['mime_type' => '', 'id' => '']
                    ]
                ]
            ]
        ]
    ],

    // Mensagem de áudio
    'audio' => [
        [
            'body' => [
                'messaging_product' => 'whatsapp',
                'metadata' => [
                    'display_phone_number' => '556236114822',
                    'phone_number_id' => '749709211549367',
                    'webhook_object_id' => '6873f1e291c9565c68150fcf'
                ],
                'contacts' => [],
                'messages' => [
                    [
                        'from' => '556296185892',
                        'id' => 'wamid.TEST_AUDIO_' . uniqid(),
                        'timestamp' => (string)time(),
                        'type' => 'audio',
                        'text' => ['body' => ''],
                        'document' => ['id' => '', 'filename' => '', 'mime_type' => ''],
                        'image' => ['id' => '', 'mime_type' => ''],
                        'button' => ['payload' => '', 'text' => ''],
                        'audio' => [
                            'mime_type' => 'audio/ogg; codecs=opus',
                            'id' => '973041891535956',
                            'text' => 'Transcrição: Testando áudio, teste de envio de áudio do N8N.'
                        ]
                    ]
                ]
            ]
        ]
    ],

    // Mensagem de imagem
    'imagem' => [
        [
            'body' => [
                'messaging_product' => 'whatsapp',
                'metadata' => [
                    'display_phone_number' => '556236114822',
                    'phone_number_id' => '749709211549367',
                    'webhook_object_id' => '6873f1e291c9565c68150fcf'
                ],
                'contacts' => [],
                'messages' => [
                    [
                        'from' => '556296185892',
                        'id' => 'wamid.TEST_IMAGE_' . uniqid(),
                        'timestamp' => (string)time(),
                        'type' => 'image',
                        'text' => ['body' => ''],
                        'document' => ['id' => '', 'filename' => '', 'mime_type' => ''],
                        'image' => [
                            'id' => '721450507411092',
                            'mime_type' => 'image/jpeg'
                        ],
                        'button' => ['payload' => '', 'text' => ''],
                        'audio' => ['mime_type' => '', 'id' => '']
                    ]
                ]
            ]
        ]
    ],

    // Mensagem de documento
    'documento' => [
        [
            'body' => [
                'messaging_product' => 'whatsapp',
                'metadata' => [
                    'display_phone_number' => '556236114822',
                    'phone_number_id' => '749709211549367',
                    'webhook_object_id' => '6873f1e291c9565c68150fcf'
                ],
                'contacts' => [],
                'messages' => [
                    [
                        'from' => '556296185892',
                        'id' => 'wamid.TEST_DOC_' . uniqid(),
                        'timestamp' => (string)time(),
                        'type' => 'document',
                        'text' => ['body' => ''],
                        'document' => [
                            'id' => '757387833416604',
                            'filename' => 'documento_teste_n8n.pdf',
                            'mime_type' => 'application/pdf'
                        ],
                        'image' => ['id' => '', 'mime_type' => ''],
                        'button' => ['payload' => '', 'text' => ''],
                        'audio' => ['mime_type' => '', 'id' => '']
                    ]
                ]
            ]
        ]
    ],

    // Mensagem de botão
    'botao' => [
        [
            'body' => [
                'messaging_product' => 'whatsapp',
                'metadata' => [
                    'display_phone_number' => '556236114822',
                    'phone_number_id' => '749709211549367',
                    'webhook_object_id' => '6873f1e291c9565c68150fcf'
                ],
                'contacts' => [],
                'messages' => [
                    [
                        'from' => '556296185892',
                        'id' => 'wamid.TEST_BUTTON_' . uniqid(),
                        'timestamp' => (string)time(),
                        'type' => 'button',
                        'text' => ['body' => ''],
                        'document' => ['id' => '', 'filename' => '', 'mime_type' => ''],
                        'image' => ['id' => '', 'mime_type' => ''],
                        'button' => [
                            'payload' => 'FALAR_COM_ATENDENTE',
                            'text' => 'FALAR COM ATENDENTE'
                        ],
                        'audio' => ['mime_type' => '', 'id' => '']
                    ]
                ]
            ]
        ]
    ]
];

function enviarWebhook($url, $dados, $tipo) {
    echo "\n🔄 Enviando webhook de $tipo...\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($dados),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'User-Agent: N8N-Test/1.0'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Erro cURL: $error\n";
        return false;
    }
    
    echo "📊 Status HTTP: $httpCode\n";
    
    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        if ($responseData && $responseData['success']) {
            echo "✅ Sucesso: {$responseData['message']}\n";
            if (isset($responseData['resultados'])) {
                foreach ($responseData['resultados'] as $i => $resultado) {
                    if (is_array($resultado)) {
                        foreach ($resultado as $subResultado) {
                            if (isset($subResultado['message'])) {
                                echo "   📝 Resultado: {$subResultado['message']}\n";
                            }
                        }
                    } else {
                        echo "   📝 Resultado $i: " . print_r($resultado, true) . "\n";
                    }
                }
            }
        } else {
            echo "❌ Falha: " . ($responseData['message'] ?? 'Erro desconhecido') . "\n";
        }
    } else {
        echo "❌ Erro HTTP $httpCode: $response\n";
    }
    
    echo "----------------------------------------\n";
    return $httpCode === 200;
}

// Executar testes
echo "🚀 Iniciando testes do webhook N8N\n";
echo "📡 URL: $webhookUrl\n";
echo "========================================\n";

$sucessos = 0;
$total = 0;

foreach ($exemplos as $tipo => $dados) {
    $total++;
    if (enviarWebhook($webhookUrl, $dados, $tipo)) {
        $sucessos++;
    }
    sleep(1); // Pausa entre requisições
}

echo "\n📊 RESUMO DOS TESTES\n";
echo "========================================\n";
echo "✅ Sucessos: $sucessos\n";
echo "❌ Falhas: " . ($total - $sucessos) . "\n";
echo "📈 Taxa de sucesso: " . round(($sucessos / $total) * 100, 2) . "%\n";

if ($sucessos === $total) {
    echo "\n🎉 Todos os testes passaram! Webhook N8N está funcionando corretamente.\n";
} else {
    echo "\n⚠️  Alguns testes falharam. Verifique os logs para mais detalhes.\n";
}

echo "\n💡 Para verificar se as mensagens foram salvas:\n";
echo "   - Acesse o painel do chat em: https://coparente.top/chat\n";
echo "   - Verifique os logs em: logs/webhook_" . date('Y-m-d') . ".log\n";
echo "   - Verifique se as mídias foram salvas no MinIO\n";
?> 