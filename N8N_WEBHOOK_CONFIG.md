# Configuração do Webhook no n8n

## 🔍 Diagnóstico do Problema

### Status dos Endpoints
- ✅ **Webhook Principal**: `https://coparente.top/chat/webhook/serpro` - **FUNCIONANDO**
- ✅ **Debug Geral**: `https://coparente.top/chat/webhook/serpro/debug` - **FUNCIONANDO**
- ✅ **Debug N8N**: `https://coparente.top/chat/webhook/serpro/debug-n8n` - **FUNCIONANDO**
- ✅ **Teste**: `https://coparente.top/chat/webhook/serpro/test` - **FUNCIONANDO**

### Testes Realizados
1. **Thunder Client** ✅ - Webhook funciona perfeitamente
2. **PowerShell direto** ✅ - Webhook funciona perfeitamente
3. **Curl direto** ✅ - Webhook funciona perfeitamente
4. **n8n** ❌ - Retorna HTML da página de login

### Conclusão
O problema **NÃO** está no webhook do servidor, mas sim na configuração do n8n que está enviando a requisição de forma incorreta.

## 🚨 Problema Identificado

O n8n está enviando templates não processados (`{{ $json.body.messaging_product }}`) em vez dos valores reais, E além disso, pode estar enviando a requisição para uma URL incorreta ou com headers inadequados.

## 🔧 URLs de Debug

Para testar e debugar o webhook, use estas URLs:

- **Debug N8N**: `https://coparente.top/chat/webhook/serpro/debug-n8n` (NOVO!)
- **Debug Geral**: `https://coparente.top/chat/webhook/serpro/debug`
- **Webhook Principal**: `https://coparente.top/chat/webhook/serpro`

## 📋 Passos para Resolver

### 1. Primeiro, teste o endpoint de debug do n8n
Configure o n8n para enviar para: `https://coparente.top/chat/webhook/serpro/debug-n8n`

### 2. Verifique os logs gerados
Os logs serão salvos em: `logs/webhook_n8n_debug_YYYY-MM-DD_HH-MM-SS.log`

### 3. Analise os dados capturados
O endpoint de debug captura:
- Headers completos
- URL exata acessada
- Método HTTP
- Body da requisição
- Todos os parâmetros

### 4. Corrija a configuração baseada no debug

## Configuração Correta no n8n

### 1. Estrutura de Dados Esperada

O webhook espera dados no formato:

```json
[
  {
    "body": {
      "messaging_product": "whatsapp",
      "metadata": {
        "display_phone_number": "15551234567",
        "phone_number_id": "123456789",
        "webhook_object_id": "abc123"
      },
      "contacts": [
        {
          "profile": {
            "name": "Nome do Contato"
          },
          "wa_id": "5511999999999"
        }
      ],
      "messages": [
        {
          "from": "5511999999999",
          "id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIA",
          "timestamp": "1752361648",
          "text": {
            "body": "Mensagem de teste"
          },
          "type": "text"
        }
      ],
      "errors": [],
      "statuses": [
        {
          "id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIA",
          "status": "delivered",
          "timestamp": "1752361648",
          "recipient_id": "5511999999999"
        }
      ]
    }
  }
]
```

### 2. Configuração no n8n

#### Método 1: Usando Set Node (Recomendado)

1. **Adicione um Set Node** antes do HTTP Request
2. **Configure os campos** com valores fixos ou dinâmicos:

```javascript
// No Set Node, configure assim:
{
  "body": {
    "messaging_product": "whatsapp",
    "metadata": {
      "display_phone_number": "{{ $json.body.metadata.display_phone_number }}",
      "phone_number_id": "{{ $json.body.metadata.phone_number_id }}",
      "webhook_object_id": "{{ $json.body.metadata.webhook_object_id }}"
    },
    "contacts": [
      {
        "profile": {
          "name": "{{ $json.body.contacts[0].profile.name }}"
        },
        "wa_id": "{{ $json.body.contacts[0].wa_id }}"
      }
    ],
    "messages": [
      {
        "from": "{{ $json.body.messages[0].from }}",
        "id": "{{ $json.body.messages[0].id }}",
        "timestamp": "{{ $json.body.messages[0].timestamp }}",
        "text": {
          "body": "{{ $json.body.messages[0].text.body }}"
        },
        "type": "{{ $json.body.messages[0].type }}"
      }
    ],
    "errors": [],
    "statuses": "{{ $json.body.statuses || [] }}"
  }
}
```

#### Método 2: Usando Code Node

1. **Adicione um Code Node** antes do HTTP Request
2. **Use este código JavaScript**:

```javascript
// Processar dados recebidos
const inputData = $input.all();

const processedData = inputData.map(item => {
  const originalData = item.json;
  
  return {
    json: {
      body: {
        messaging_product: originalData.body?.messaging_product || "whatsapp",
        metadata: {
          display_phone_number: originalData.body?.metadata?.display_phone_number || "",
          phone_number_id: originalData.body?.metadata?.phone_number_id || "",
          webhook_object_id: originalData.body?.metadata?.webhook_object_id || ""
        },
        contacts: originalData.body?.contacts || [],
        messages: originalData.body?.messages || [],
        errors: originalData.body?.errors || [],
        statuses: originalData.body?.statuses || []
      }
    }
  };
});

return processedData;
```

### 3. Configuração do HTTP Request Node

- **URL**: `https://coparente.top/chat/webhook/serpro`
- **Method**: POST
- **Content-Type**: application/json
- **Body**: Use o resultado do Set Node ou Code Node

### 4. Teste e Debug

#### Passo 1: Testar com Debug
1. Mude temporariamente a URL para: `https://coparente.top/chat/webhook/serpro/debug`
2. Execute o workflow
3. Verifique os logs em `logs/webhook_debug_YYYY-MM-DD.log`

#### Passo 2: Verificar Dados
O debug mostrará exatamente o que está sendo recebido:
- Headers
- Body raw
- Body parsed
- Todos os parâmetros

#### Passo 3: Corrigir e Testar
1. Ajuste a configuração baseada no debug
2. Teste novamente com debug
3. Quando estiver correto, mude para a URL principal

### 5. Exemplo de Payload Correto

```json
[
  {
    "body": {
      "messaging_product": "whatsapp",
      "metadata": {
        "display_phone_number": "15551234567",
        "phone_number_id": "123456789",
        "webhook_object_id": "abc123"
      },
      "contacts": [
        {
          "profile": {
            "name": "João Silva"
          },
          "wa_id": "5511999999999"
        }
      ],
      "messages": [
        {
          "from": "5511999999999",
          "id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIA",
          "timestamp": "1752361648",
          "text": {
            "body": "Olá, preciso de ajuda"
          },
          "type": "text"
        }
      ],
      "errors": [],
      "statuses": []
    }
  }
]
```

### 6. Troubleshooting

#### Problema: Templates não processados
- **Causa**: n8n não está processando as expressões
- **Solução**: Use Set Node ou Code Node para processar os dados

#### Problema: Dados vazios
- **Causa**: Estrutura de dados incorreta
- **Solução**: Verifique a estrutura no debug

#### Problema: Erro 400 (Bad Request)
- **Causa**: JSON inválido ou estrutura incorreta
- **Solução**: Valide o JSON e estrutura

### 7. Monitoramento

Após configurar, monitore:
- Logs do webhook: `logs/webhook_YYYY-MM-DD.log`
- Confirmações: `logs/confirmacoes_pendentes.json`
- Processamento: `logs/processamento_confirmacoes_YYYY-MM-DD.log`

### 8. Próximos Passos

1. Configure o n8n usando um dos métodos acima
2. Teste com a URL de debug primeiro
3. Verifique os logs para confirmar dados corretos
4. Mude para a URL principal quando estiver funcionando
5. Configure o cron job para processamento de confirmações

## Suporte

Se continuar com problemas, envie:
1. Screenshot da configuração do n8n
2. Logs do debug
3. Dados que estão sendo enviados

O webhook está funcionando corretamente no servidor, o problema está na configuração do n8n. 