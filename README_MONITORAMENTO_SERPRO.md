# 📊 Monitoramento de Requisições da API Serpro

Este sistema permite monitorar e analisar o volume de requisições feitas à API Serpro em tempo real.

## 🚀 Configuração Inicial

### 1. Adicionar Logs aos Métodos
```bash
php gerenciar_logs_serpro.php adicionar-logs
```

### 2. Verificar Status
```bash
php gerenciar_logs_serpro.php status
```

## 📋 Comandos Disponíveis

### 🔍 Monitor - Análise Completa
Analisa todos os logs existentes e gera relatórios detalhados:
```bash
php gerenciar_logs_serpro.php monitor
```

**Informações geradas:**
- 📈 Requisições por método (enviarTemplate, enviarMensagemTexto, enviarMidia, etc.)
- 👥 Requisições por usuário
- 📱 Top 10 destinatários
- ⏰ Requisições por hora do dia
- 📅 Requisições por dia
- ❌ Erros encontrados

### 🔄 Tempo Real - Monitoramento Ativo
Monitora requisições conforme elas acontecem:
```bash
php gerenciar_logs_serpro.php tempo-real
```

**Funcionalidades:**
- Exibe cada requisição em tempo real
- Mostra estatísticas a cada 30 segundos
- Calcula taxa de requisições por minuto
- Identifica erros instantaneamente

### 🧹 Limpar - Manutenção
Remove logs antigos para economizar espaço:
```bash
php gerenciar_logs_serpro.php limpar
```

**Critérios de limpeza:**
- Remove logs com mais de 7 dias
- Reduz arquivos maiores que 50MB
- Mantém apenas as últimas 1000 linhas
- Remove relatórios de análise antigos

### 📊 Status - Verificação Rápida
Verifica o status atual dos logs:
```bash
php gerenciar_logs_serpro.php status
```

## 📁 Estrutura de Arquivos

```
logs/
├── serpro_requests.log          # Log principal de requisições
├── serpro_analise_YYYY-MM-DD_HH-MM-SS.txt  # Relatórios de análise
└── last_check.txt              # Controle para monitoramento em tempo real
```

## 📊 Métodos Monitorados

O sistema monitora automaticamente os seguintes métodos da API Serpro:

1. **enviarTemplate** - Envio de templates (primeira mensagem)
2. **enviarMensagemTexto** - Envio de mensagens de texto
3. **enviarMidia** - Envio de mídias (imagem, áudio, documento, vídeo)
4. **uploadMidia** - Upload de arquivos para a API
5. **consultarStatus** - Consulta de status de mensagens

## 🔍 Informações Capturadas

Para cada requisição, o sistema registra:

- **Timestamp** - Data e hora exata
- **Usuário** - Nome do usuário que fez a requisição
- **Perfil** - Perfil do usuário (admin, supervisor, atendente)
- **Método** - Método da API chamado
- **Destinatário** - Número de telefone de destino
- **Parâmetros** - Detalhes específicos da requisição
- **IP** - Endereço IP do usuário
- **User Agent** - Navegador/dispositivo usado

## 📈 Análise de Volume

### Identificação de Picos
- **Por hora**: Identifica horários de maior atividade
- **Por dia**: Mostra padrões semanais
- **Por usuário**: Identifica usuários mais ativos
- **Por método**: Mostra quais funcionalidades são mais usadas

### Detecção de Problemas
- **Erros frequentes**: Identifica problemas recorrentes
- **Taxa de erro**: Calcula porcentagem de falhas
- **Tempo de resposta**: Monitora performance da API

## 🛠️ Uso Prático

### 1. Monitoramento Diário
```bash
# Verificar status atual
php gerenciar_logs_serpro.php status

# Analisar logs do dia
php gerenciar_logs_serpro.php monitor
```

### 2. Monitoramento em Produção
```bash
# Iniciar monitoramento em tempo real
php gerenciar_logs_serpro.php tempo-real
```

### 3. Manutenção Semanal
```bash
# Limpar logs antigos
php gerenciar_logs_serpro.php limpar
```

## 📊 Exemplos de Relatórios

### Relatório de Volume
```
📈 REQUISIÇÕES POR MÉTODO
-------------------------
enviarMensagemTexto    :  156 ( 45.2%)
enviarTemplate         :   89 ( 25.8%)
enviarMidia            :   67 ( 19.4%)
uploadMidia            :   23 (  6.7%)
consultarStatus        :   10 (  2.9%)
```

### Relatório de Usuários
```
👥 REQUISIÇÕES POR USUÁRIO
---------------------------
João Silva            :  89 ( 25.8%)
Maria Santos          :  67 ( 19.4%)
Admin Sistema         :  45 ( 13.0%)
```

### Relatório de Horários
```
⏰ REQUISIÇÕES POR HORA
----------------------
09:00 - 09:59:   45
10:00 - 10:59:   67
11:00 - 11:59:   89
14:00 - 14:59:   78
15:00 - 15:59:   56
```

## 🔧 Configuração Avançada

### Limites Configuráveis
- **Tamanho máximo do log**: 50MB
- **Idade máxima**: 7 dias
- **Linhas mantidas**: 1000
- **Intervalo de estatísticas**: 30 segundos

### Personalização
Edite os arquivos de configuração para ajustar:
- `limpar_logs_serpro.php` - Limites de limpeza
- `monitor_tempo_real.php` - Intervalo de verificação
- `monitor_serpro_requests.php` - Critérios de análise

## 🚨 Alertas e Recomendações

### Volume Alto
- **> 100 req/min**: Verificar se há spam ou loop
- **> 1000 req/hora**: Considerar otimizações
- **> 10000 req/dia**: Revisar arquitetura

### Erros Frequentes
- **> 10% de erro**: Investigar problemas na API
- **Erros 401**: Verificar tokens
- **Erros 500**: Verificar configuração da API

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique o status dos logs: `php gerenciar_logs_serpro.php status`
2. Analise os erros: `php gerenciar_logs_serpro.php monitor`
3. Verifique se os logs estão sendo gerados após usar o sistema

---

**🎯 Objetivo**: Monitorar e otimizar o uso da API Serpro para identificar padrões de uso e prevenir problemas de performance. 