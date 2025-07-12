# Sistema de Confirmação Automática - ChatSerpro

## Visão Geral

O sistema de confirmação automática garante que todas as mensagens recebidas sejam automaticamente marcadas como entregues e lidas, simulando um comportamento humano natural no WhatsApp.

## Funcionamento

### 1. Quando uma mensagem é recebida:
- **Confirmação de Entrega**: Enviada imediatamente
- **Confirmação de Leitura**: Enviada após 2-5 segundos (delay aleatório)

### 2. Processamento Assíncrono:
- As confirmações de leitura são salvas em arquivo para processamento posterior
- Um script processa as confirmações pendentes a cada minuto
- Evita timeout no webhook principal

## Configuração

### 1. Webhook Automático
O webhook já está configurado para confirmar automaticamente. Não precisa de configuração adicional.

### 2. Script de Processamento
Configure o cron job para processar confirmações pendentes:

```bash
# Executar a cada minuto
* * * * * php /caminho/para/projeto/scripts/processar_confirmacoes.php

# Exemplo para XAMPP no Windows (via Task Scheduler)
# Comando: C:\xampp\php\php.exe
# Argumentos: C:\xampp\htdocs\chat\scripts\processar_confirmacoes.php
```

### 3. Configuração Manual (Opcional)
Para executar manualmente o processamento:

```bash
cd /caminho/para/projeto
php scripts/processar_confirmacoes.php
```

## Estrutura de Arquivos

### Logs de Confirmação
```
logs/
├── confirmacoes_pendentes.json          # Confirmações aguardando processamento
├── processamento_confirmacoes_YYYY-MM-DD.log  # Logs do processamento
└── webhook_YYYY-MM-DD.log               # Logs do webhook
```

### Scripts
```
scripts/
└── processar_confirmacoes.php           # Script de processamento
```

## Fluxo de Processamento

### 1. Recebimento da Mensagem
```
Webhook recebe mensagem
    ↓
Processa mensagem normalmente
    ↓
Confirma entrega imediatamente
    ↓
Agenda confirmação de leitura (2-5s)
    ↓
Salva em confirmacoes_pendentes.json
```

### 2. Processamento das Confirmações
```
Script executado a cada minuto
    ↓
Lê confirmacoes_pendentes.json
    ↓
Processa confirmações vencidas
    ↓
Envia confirmação via API Serpro
    ↓
Remove confirmações processadas
    ↓
Salva confirmações ainda pendentes
```

## Monitoramento

### Logs Disponíveis

#### 1. Logs do Webhook
```bash
tail -f logs/webhook_$(date +%Y-%m-%d).log
```

#### 2. Logs do Processamento
```bash
tail -f logs/processamento_confirmacoes_$(date +%Y-%m-%d).log
```

#### 3. Logs do PHP (erros)
```bash
tail -f /var/log/php/error.log
```

### Exemplos de Logs

#### Confirmação de Entrega (Imediata)
```json
{
  "message_id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIA...",
  "status": "delivered",
  "timestamp": "2025-01-27 10:30:00",
  "action": "auto_confirm_success",
  "response": {"success": true}
}
```

#### Confirmação de Leitura (Processada)
```json
{
  "message_id": "wamid.HBgMNTU2Mjk2MTg1ODkyFQIA...",
  "status": "read",
  "timestamp": "2025-01-27 10:30:05",
  "action": "auto_confirm_processed",
  "response": {"success": true}
}
```

## Configurações Avançadas

### 1. Delay de Leitura
Para alterar o delay entre entrega e leitura, edite o arquivo `app/Controllers/Webhook.php`:

```php
// Linha aproximada 260
$delay = rand(2, 5); // 2-5 segundos

// Alterar para:
$delay = rand(10, 30); // 10-30 segundos
```

### 2. Desabilitar Confirmação Automática
Para desabilitar temporariamente, comente a linha no webhook:

```php
// Linha aproximada 225
// $this->confirmarEntregaELeituraAutomatica($messageId, $numeroLimpo);
```

### 3. Configurar Horário de Funcionamento
Para confirmar apenas em horário comercial, edite o script:

```php
// Adicionar no início da função processarConfirmacoesPendentes()
$hora = date('H');
if ($hora < 8 || $hora > 18) {
    logProcessamento("Fora do horário comercial, pulando processamento");
    return;
}
```

## Troubleshooting

### Problema: Confirmações não são enviadas

**Verificar:**
1. Cron job está configurado corretamente
2. Script tem permissões de execução
3. API Serpro está configurada
4. Logs de erro para detalhes

```bash
# Verificar cron jobs
crontab -l

# Testar script manualmente
php scripts/processar_confirmacoes.php

# Verificar permissões
ls -la scripts/processar_confirmacoes.php
```

### Problema: Muitas confirmações pendentes

**Possíveis causas:**
1. Cron job não está executando
2. Erro na API Serpro
3. Token expirado

**Solução:**
```bash
# Verificar arquivo de confirmações pendentes
cat logs/confirmacoes_pendentes.json

# Executar processamento manual
php scripts/processar_confirmacoes.php

# Verificar logs de erro
tail -20 logs/processamento_confirmacoes_$(date +%Y-%m-%d).log
```

### Problema: Erro de token

**Solução:**
1. Verificar configurações da API Serpro
2. Renovar token manualmente
3. Verificar conectividade

```bash
# Verificar status da API
curl -X GET http://localhost/chat/configuracoes/serpro
```

## Estatísticas

### Confirmações por Dia
```sql
SELECT 
    DATE(criado_em) as data,
    COUNT(*) as mensagens_recebidas
FROM mensagens 
WHERE direcao = 'entrada' 
GROUP BY DATE(criado_em)
ORDER BY data DESC;
```

### Taxa de Sucesso
```bash
# Contar confirmações bem-sucedidas
grep "auto_confirm_success" logs/processamento_confirmacoes_*.log | wc -l

# Contar confirmações com erro
grep "auto_confirm_error" logs/processamento_confirmacoes_*.log | wc -l
```

## Manutenção

### Limpeza de Logs
Os logs são limpos automaticamente após 30 dias. Para limpeza manual:

```bash
# Remover logs antigos
find logs/ -name "*.log" -mtime +30 -delete

# Limpar confirmações pendentes antigas (mais de 1 dia)
php -r "
$file = 'logs/confirmacoes_pendentes.json';
if (file_exists($file)) {
    $confirmacoes = json_decode(file_get_contents($file), true);
    $agora = time();
    $filtradas = array_filter($confirmacoes, function($c) use ($agora) {
        return ($agora - $c['scheduled_time']) < 86400; // 24 horas
    });
    file_put_contents($file, json_encode($filtradas, JSON_PRETTY_PRINT));
}
"
```

### Backup
```bash
# Backup dos logs importantes
tar -czf backup_logs_$(date +%Y%m%d).tar.gz logs/

# Backup do arquivo de confirmações
cp logs/confirmacoes_pendentes.json logs/confirmacoes_pendentes_backup.json
```

## Integração com Monitoramento

### Webhook de Monitoramento
Para receber alertas quando há muitas confirmações pendentes:

```php
// Adicionar no final de processarConfirmacoesPendentes()
$pendentes = count($confirmacoesPendentes);
if ($pendentes > 100) {
    // Enviar alerta
    file_get_contents('https://seu-webhook-monitoramento.com/alert?type=confirmacoes&count=' . $pendentes);
}
```

### Dashboard de Status
Criar endpoint para monitorar status:

```php
// Em app/Controllers/Api.php
public function statusConfirmacoes()
{
    $pendentes = 0;
    $arquivo = ROOT . '/logs/confirmacoes_pendentes.json';
    
    if (file_exists($arquivo)) {
        $confirmacoes = json_decode(file_get_contents($arquivo), true);
        $pendentes = count($confirmacoes ?: []);
    }
    
    echo json_encode([
        'confirmacoes_pendentes' => $pendentes,
        'ultimo_processamento' => filemtime($arquivo),
        'status' => $pendentes > 50 ? 'warning' : 'ok'
    ]);
}
```

## Considerações de Performance

### Otimizações
1. **Batch Processing**: Processa múltiplas confirmações em lote
2. **Cache de Token**: Reutiliza token da API para múltiplas confirmações
3. **Cleanup Automático**: Remove logs antigos automaticamente
4. **Retry Logic**: Reprocessa confirmações que falharam

### Limites
- Máximo 1000 confirmações pendentes por vez
- Timeout de 30 segundos por confirmação
- Reprocessamento até 3 tentativas por confirmação

## Conclusão

O sistema de confirmação automática garante que todas as mensagens recebidas sejam adequadamente confirmadas, mantendo um comportamento natural e profissional no WhatsApp Business. 