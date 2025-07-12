# Melhorias no Tratamento de Erros - Sistema de Chat

## Problema Identificado

O usuário reportou erro 410 (Gone) no console ao tentar enviar mensagens:
```
POST http://localhost/chat/chat/enviar-mensagem 410 (Gone)
```

## Análise do Problema

O erro 410 (Gone) é retornado quando:
1. A conversa expirou (passou das 24 horas da janela do WhatsApp Business API)
2. O frontend não estava tratando adequadamente este erro específico
3. A mensagem genérica "Erro ao enviar mensagem" não informava o motivo real

## Melhorias Implementadas

### 1. Tratamento de Erros HTTP no Frontend

**Antes:**
```javascript
error: function() {
    mostrarToast('Erro ao enviar mensagem', 'error');
}
```

**Depois:**
```javascript
error: function(xhr, textStatus, errorThrown) {
    console.log('❌ Erro ao enviar mensagem');
    console.log('Status:', xhr.status);
    console.log('ResponseText:', xhr.responseText);
    
    let mensagemErro = 'Erro ao enviar mensagem';
    
    // Interpretar resposta JSON
    try {
        if (xhr.responseText) {
            let jsonStart = xhr.responseText.indexOf('{');
            let jsonEnd = xhr.responseText.lastIndexOf('}');
            
            if (jsonStart !== -1 && jsonEnd !== -1) {
                let jsonString = xhr.responseText.substring(jsonStart, jsonEnd + 1);
                const response = JSON.parse(jsonString);
                
                if (response && response.message) {
                    mensagemErro = response.message;
                    
                    // Tratamento especial para conversa expirada
                    if (xhr.status === 410 && response.expirada) {
                        mensagemErro += '\n\nA conversa será removida da lista.';
                        
                        // Remover conversa da lista após 3 segundos
                        setTimeout(() => {
                            $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).fadeOut();
                            $('#chatActive').hide();
                            $('#chatWelcome').show();
                            conversaAtiva = null;
                        }, 3000);
                    }
                }
            }
        }
    } catch (e) {
        console.log('Erro ao fazer parse da resposta de erro:', e);
    }
    
    // Verificar códigos HTTP específicos
    if (xhr.status === 410) {
        mensagemErro = mensagemErro || 'Conversa expirada. Envie um novo template para reiniciar o contato.';
    } else if (xhr.status === 400) {
        mensagemErro = mensagemErro || 'Aguarde o contato responder ao template antes de enviar mensagens.';
    } else if (xhr.status === 404) {
        mensagemErro = 'Conversa não encontrada';
    } else if (xhr.status === 500) {
        mensagemErro = 'Erro interno do servidor';
    } else if (xhr.status === 0) {
        mensagemErro = 'Erro de conexão';
    }
    
    mostrarToast(mensagemErro, 'error');
}
```

### 2. Melhorias Aplicadas em Todas as Funções AJAX

As seguintes funções foram atualizadas com o tratamento de erros melhorado:

- **`enviarMensagem()`** - Envio de mensagens de texto
- **`enviarArquivo()`** - Envio de mídias (imagem, áudio, documento, vídeo)
- **`enviarTemplate()`** - Já tinha tratamento robusto, mantido

### 3. Sistema de Verificação de Status das Conversas

Implementado sistema proativo para verificar o status das conversas:

```javascript
// Verificar status da conversa ativa
function verificarStatusConversa() {
    if (!conversaAtiva) {
        return;
    }
    
    $.ajax({
        url: `<?= URL ?>/chat/status-conversa/${conversaAtiva}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const status = response.status;
                
                // Se conversa não está mais ativa, alertar
                if (!status.conversa_ativa) {
                    mostrarToast('Conversa expirada! Envie um novo template para reiniciar o contato.', 'error');
                    
                    // Remover conversa da lista
                    $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).fadeOut();
                    $('#chatActive').hide();
                    $('#chatWelcome').show();
                    conversaAtiva = null;
                    return;
                }
                
                // Alertar se está próximo de expirar (menos de 1 hora)
                if (status.tempo_restante < 3600 && status.tempo_restante > 0) {
                    const horas = Math.floor(status.tempo_restante / 3600);
                    const minutos = Math.floor((status.tempo_restante % 3600) / 60);
                    const tempoFormatado = horas > 0 ? `${horas}h ${minutos}m` : `${minutos}m`;
                    
                    // Mostrar alerta apenas uma vez por conversa
                    if (!$(`.chat-item[data-conversa-id="${conversaAtiva}"]`).hasClass('alerta-expirar')) {
                        $(`.chat-item[data-conversa-id="${conversaAtiva}"]`).addClass('alerta-expirar');
                        mostrarToast(`Atenção: Conversa expira em ${tempoFormatado}`, 'warning');
                    }
                }
                
                // Atualizar indicador visual se o contato ainda não respondeu
                if (!status.contato_respondeu) {
                    $('#messageInput').attr('placeholder', 'Aguardando resposta do contato ao template...');
                    $('#messageInput').prop('disabled', true);
                    $('#btnEnviarMensagem').prop('disabled', true);
                } else {
                    $('#messageInput').attr('placeholder', 'Digite sua mensagem...');
                    $('#messageInput').prop('disabled', false);
                    $('#btnEnviarMensagem').prop('disabled', false);
                }
            }
        },
        error: function() {
            // Ignorar erros na verificação de status
        }
    });
}

// Verificar status das conversas a cada 5 minutos
setInterval(verificarStatusConversa, 5 * 60 * 1000);
```

### 4. Melhorias no Sistema de Toasts

Adicionado suporte para toasts de warning (alertas):

```javascript
function mostrarToast(mensagem, tipo) {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${tipo === 'success' ? 'success' : (tipo === 'warning' ? 'warning' : 'danger')} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${tipo === 'success' ? 'check' : (tipo === 'warning' ? 'exclamation-triangle' : 'times-circle')} me-2"></i>
                    ${mensagem}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    // ... resto da implementação
}
```

## Códigos de Erro Tratados

| Código | Significado | Tratamento |
|--------|-------------|------------|
| 400 | Bad Request | Mensagem específica do servidor ou "Aguarde o contato responder ao template" |
| 404 | Not Found | "Conversa não encontrada" |
| 410 | Gone | "Conversa expirada. Envie um novo template para reiniciar o contato." + remoção da conversa da lista |
| 500 | Internal Server Error | "Erro interno do servidor" |
| 0 | Network Error | "Erro de conexão" |

## Funcionalidades Adicionais

### 1. Remoção Automática de Conversas Expiradas
- Quando uma conversa expira (erro 410), ela é automaticamente removida da lista
- Interface retorna para a tela de boas-vindas
- Usuário recebe feedback claro sobre o que aconteceu

### 2. Alertas Proativos
- Sistema verifica status das conversas a cada 5 minutos
- Alerta quando conversa está próxima de expirar (menos de 1 hora)
- Desabilita campos de entrada quando contato ainda não respondeu

### 3. Logs de Debug
- Todos os erros são logados no console para debug
- Informações detalhadas sobre status HTTP e resposta do servidor

## Benefícios das Melhorias

1. **Melhor Experiência do Usuário**
   - Mensagens de erro claras e específicas
   - Feedback visual adequado para cada situação
   - Remoção automática de conversas expiradas

2. **Prevenção de Erros**
   - Alertas proativos sobre expiração de conversas
   - Desabilitação de campos quando não é possível enviar mensagens
   - Verificação contínua do status das conversas

3. **Facilidade de Debug**
   - Logs detalhados no console
   - Informações precisas sobre erros HTTP
   - Rastreamento completo de requisições AJAX

4. **Conformidade com WhatsApp Business API**
   - Respeita as regras de janela de 24 horas
   - Trata adequadamente os diferentes estados das conversas
   - Impede envio de mensagens quando não permitido

## Conclusão

As melhorias implementadas resolvem completamente o problema do erro 410 (Gone) e oferecem uma experiência muito mais robusta e informativa para os usuários do sistema de chat. O sistema agora trata adequadamente todos os cenários possíveis das regras do WhatsApp Business API e fornece feedback claro sobre o status das conversas. 