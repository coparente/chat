# Webhook da API Serpro - Sistema de Chat

## Visão Geral

O webhook da API Serpro é responsável por receber mensagens dos contatos em tempo real, permitindo que o sistema de chat processe automaticamente:

- Mensagens de texto recebidas dos contatos
- Status de entrega das mensagens enviadas
- Informações atualizadas de perfil dos contatos
- Diferentes tipos de mídia (imagem, áudio, vídeo, documento)

## Configuração

### 1. Endpoint do Webhook

```
POST /webhook/serpro
```

### 2. URL Completa

```
https://seudominio.com/webhook/serpro
```

### 3. Endpoint de Teste

```
GET /webhook/serpro/test
```

## Estrutura do Payload

### Mensagem Recebida

```json
{
  "data": [
    {
      "display_phone_number": "556232162929",
      "phone_number_id": "642958872237822",
      "webhook_object_id": "68472ebbef6eda2d4340f9f9",
      "contacts": [
        {
          "profile": {
            "name": "Cleyton Parente 😅"
          },
          "wa_id": "556296185892"
        }
      ],
      "messages": [
        {
          "from": "556296185892",
          "id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIAEhggNzkyN0YxODg4QjgxNzI2QkVFRENGNUY2MDBBRjI3MjAA",
          "timestamp": "1752361648",
          "text": {
            "body": "Teste"
          },
          "type": "text"
        }
      ],
      "errors": [],
      "statuses": [],
      "delay": 0
    }
  ],
  "webhookUrl": "https://webhook.helpersti.online/webhook-test/serpro-chat",
  "executionMode": "test"
}
```

### Status de Entrega

```json
{
  "data": [
    {
      "statuses": [
        {
          "id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIAEhggNzkyN0YxODg4QjgxNzI2QkVFRENGNUY2MDBBRjI3MjAA",
          "status": "delivered",
          "timestamp": "1752361648",
          "recipient_id": "556296185892"
        }
      ]
    }
  ]
}
```

## Tipos de Mensagem Suportados

### 1. Texto
```json
{
  "type": "text",
  "text": {
    "body": "Mensagem de texto"
  }
}
```

### 2. Imagem
```json
{
  "type": "image",
  "image": {
    "id": "media_id",
    "mime_type": "image/jpeg",
    "caption": "Legenda da imagem"
  }
}
```

### 3. Áudio
```json
{
  "type": "audio",
  "audio": {
    "id": "media_id",
    "mime_type": "audio/ogg"
  }
}
```

### 4. Vídeo
```json
{
  "type": "video",
  "video": {
    "id": "media_id",
    "mime_type": "video/mp4",
    "caption": "Legenda do vídeo"
  }
}
```

### 5. Documento
```json
{
  "type": "document",
  "document": {
    "id": "media_id",
    "mime_type": "application/pdf",
    "filename": "documento.pdf"
  }
}
```

### 6. Localização
```json
{
  "type": "location",
  "location": {
    "latitude": -23.5505,
    "longitude": -46.6333,
    "name": "São Paulo",
    "address": "São Paulo, SP, Brasil"
  }
}
```

## Processamento das Mensagens

### 1. Validação
- Verificação da estrutura do payload
- Validação dos campos obrigatórios
- Verificação do formato dos números de telefone

### 2. Processamento do Contato
- Busca contato existente por número
- Criação de novo contato se não existir
- Atualização do nome do contato se fornecido

### 3. Processamento da Conversa
- Busca conversa ativa para o contato
- Criação de nova conversa se não existir
- Reativação de conversa se estava fechada

### 4. Processamento da Mensagem
- Extração do conteúdo baseado no tipo
- Salvamento no banco de dados
- Atualização do timestamp da conversa
- Processamento de metadados

## Status de Entrega

### Mapeamento de Status

| Status API | Status Sistema | Descrição |
|------------|----------------|-----------|
| `sent` | `enviado` | Mensagem enviada |
| `delivered` | `entregue` | Mensagem entregue |
| `read` | `lido` | Mensagem lida |
| `failed` | `erro` | Falha no envio |

### Processamento
- Busca mensagem pelo ID do Serpro
- Atualização do status no banco
- Log do status para auditoria

## Logs e Debug

### Logs Automáticos
- Todas as requisições são logadas em `logs/webhook_YYYY-MM-DD.log`
- Logs incluem timestamp, IP, dados recebidos
- Apenas em ambiente de desenvolvimento

### Estrutura do Log
```json
{
  "timestamp": "2025-01-27 10:30:00",
  "tipo": "serpro",
  "metodo": "POST",
  "ip": "192.168.1.100",
  "user_agent": "WhatsApp/2.21.0",
  "dados": "{...payload...}"
}
```

## Tratamento de Erros

### Erros Comuns
1. **Payload inválido**: Retorna HTTP 400
2. **Contato não encontrado**: Cria automaticamente
3. **Conversa não encontrada**: Cria automaticamente
4. **Erro no banco**: Retorna HTTP 500

### Resposta de Erro
```json
{
  "success": false,
  "message": "Descrição do erro"
}
```

### Resposta de Sucesso
```json
{
  "success": true,
  "message": "Webhook processado com sucesso",
  "resultados": [
    {
      "success": true,
      "message": "Mensagem processada com sucesso",
      "mensagem_id": 123,
      "conversa_id": 456
    }
  ]
}
```

## Segurança

### Validação de Origem
- Verificação do IP de origem (opcional)
- Validação do token de webhook (se configurado)
- Verificação da estrutura do payload

### Proteção contra Spam
- Limite de requisições por IP
- Validação de números de telefone
- Filtragem de mensagens duplicadas

## Integração com o Sistema

### Atualização em Tempo Real
- Mensagens aparecem automaticamente no chat
- Conversas são reativadas quando recebem mensagens
- Status de entrega é atualizado em tempo real

### Regras de Negócio
- Mensagens recebidas resetam o timer de 24h
- Conversas fechadas são reativadas automaticamente
- Contatos são criados automaticamente se não existirem

## Teste do Webhook

### Endpoint de Teste
```bash
curl -X GET https://seudominio.com/webhook/serpro/test
```

### Resposta do Teste
```json
{
  "success": true,
  "message": "Webhook está funcionando",
  "timestamp": "2025-01-27 10:30:00",
  "method": "GET",
  "data": null
}
```

### Teste com Payload
```bash
curl -X POST https://seudominio.com/webhook/serpro \
  -H "Content-Type: application/json" \
  -d '{
    "data": [
      {
        "messages": [
          {
            "from": "5511999999999",
            "id": "test_message_id",
            "timestamp": "1752361648",
            "text": {
              "body": "Teste de webhook"
            },
            "type": "text"
          }
        ],
        "contacts": [
          {
            "profile": {
              "name": "Teste"
            },
            "wa_id": "5511999999999"
          }
        ]
      }
    ]
  }'
```

## Configuração na API Serpro

### 1. Configurar URL do Webhook
No painel da API Serpro, configure:
- **URL**: `https://seudominio.com/webhook/serpro`
- **Método**: `POST`
- **Eventos**: `messages`, `message_status`

### 2. Verificar Conectividade
- Teste a URL do webhook
- Verifique se o servidor está acessível
- Confirme que o SSL está funcionando

### 3. Monitorar Logs
- Acompanhe os logs do webhook
- Verifique se as mensagens estão sendo recebidas
- Monitore erros e falhas

## Solução de Problemas

### Webhook não recebe mensagens
1. Verificar URL configurada na API Serpro
2. Confirmar que o servidor está acessível
3. Verificar logs de erro do servidor
4. Testar conectividade com o endpoint de teste

### Mensagens não aparecem no chat
1. Verificar se o webhook está processando corretamente
2. Confirmar que o contato/conversa está sendo criado
3. Verificar logs do banco de dados
4. Confirmar que o frontend está atualizando

### Erros de processamento
1. Verificar estrutura do payload recebido
2. Confirmar que todos os campos obrigatórios estão presentes
3. Verificar logs de erro detalhados
4. Testar com payload de exemplo

## Monitoramento

### Métricas Importantes
- Número de webhooks recebidos por hora
- Taxa de sucesso do processamento
- Tempo de resposta do webhook
- Número de mensagens processadas

### Alertas Recomendados
- Webhook indisponível por mais de 5 minutos
- Taxa de erro acima de 5%
- Tempo de resposta acima de 2 segundos
- Falha na criação de contatos/conversas

## Backup e Recuperação

### Logs de Webhook
- Manter logs por pelo menos 30 dias
- Backup automático dos logs
- Compressão de logs antigos

### Recuperação de Mensagens
- Possibilidade de reprocessar mensagens dos logs
- Verificação de integridade dos dados
- Recuperação de mensagens perdidas 