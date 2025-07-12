# 📋 Guia Completo de Templates - API Serpro

## 🎯 Introdução

Este guia explica como usar corretamente os templates da API Serpro no sistema ChatSerpro, baseado no exemplo fornecido pelo usuário.

## 🔧 Estrutura da API

A API Serpro espera os templates no seguinte formato:

```json
{
    "nomeTemplate": "central_intimacao_remota",
    "wabaId": "SEU_WABA_ID",
    "destinatarios": ["5511999999999"],
    "body": {
        "parametros": [
            {
                "tipo": "text",
                "valor": "Sua mensagem aqui"
            }
        ]
    },
    "header": {
        "filename": "logo.png",
        "linkMedia": "https://seusite.com/img/logo.png"
    }
}
```

## 📝 Templates Disponíveis

### 1. Central de Intimação Remota
- **Nome:** `central_intimacao_remota`
- **Descrição:** Template para intimações remotas do tribunal
- **Parâmetros:** 
  - `mensagem`: Texto da intimação

**Exemplo de uso:**
```php
$parametros = [
    [
        'tipo' => 'text',
        'valor' => 'Você tem uma nova intimação judicial. Acesse o sistema para visualizar.'
    ]
];
```

### 2. Boas-vindas
- **Nome:** `boas_vindas`
- **Descrição:** Mensagem de boas-vindas personalizada
- **Parâmetros:**
  - `nome`: Nome da pessoa

**Exemplo de uso:**
```php
$parametros = [
    [
        'tipo' => 'text',
        'valor' => 'João Silva'
    ]
];
```

### 3. Suporte
- **Nome:** `suporte`
- **Descrição:** Início de atendimento ao cliente
- **Parâmetros:**
  - `nome`: Nome da pessoa

### 4. Promoção
- **Nome:** `promocao`
- **Descrição:** Oferta especial para clientes
- **Parâmetros:**
  - `nome`: Nome da pessoa
  - `produto`: Nome do produto
  - `desconto`: Valor do desconto

**Exemplo de uso:**
```php
$parametros = [
    [
        'tipo' => 'text',
        'valor' => 'Maria Santos'
    ],
    [
        'tipo' => 'text',
        'valor' => 'Smartphone XYZ'
    ],
    [
        'tipo' => 'text',
        'valor' => '20%'
    ]
];
```

## 🚀 Como Usar no Sistema

### 1. Via Interface (Painel de Chat)

1. Acesse o painel de chat
2. Clique em "Nova Conversa"
3. Preencha:
   - Número do WhatsApp
   - Selecione o template
   - Preencha os parâmetros
4. Clique em "Enviar Template"

### 2. Via Código PHP

```php
<?php
// Instanciar a classe SerproApi
$serproApi = new SerproApi();

// Preparar parâmetros
$parametros = [
    [
        'tipo' => 'text',
        'valor' => 'João Silva'
    ]
];

// Enviar template
$resultado = $serproApi->enviarTemplate(
    '5511999999999',        // Número do destinatário
    'boas_vindas',          // Nome do template
    $parametros             // Parâmetros
);

// Verificar resultado
if ($resultado['status'] >= 200 && $resultado['status'] < 300) {
    echo "Template enviado com sucesso!";
} else {
    echo "Erro: " . $resultado['error'];
}
?>
```

### 3. Via AJAX (Frontend)

```javascript
// Dados do template
const dados = {
    numero: '5511999999999',
    nome: 'João Silva',
    template: 'boas_vindas',
    parametros: ['João Silva']
};

// Enviar via AJAX
$.ajax({
    url: '/chat/iniciar-conversa',
    method: 'POST',
    data: JSON.stringify(dados),
    contentType: 'application/json',
    success: function(response) {
        if (response.success) {
            alert('Template enviado com sucesso!');
        } else {
            alert('Erro: ' + response.message);
        }
    }
});
```

## 🔄 Fluxo de Conversão de Parâmetros

O sistema converte automaticamente os parâmetros do formato simples para o formato da API:

**Entrada (do frontend):**
```json
{
    "parametros": ["João Silva", "Produto XYZ", "20%"]
}
```

**Conversão (no backend):**
```php
$parametros = [];
foreach ($parametrosRaw as $parametro) {
    if (!empty($parametro)) {
        $parametros[] = [
            'tipo' => 'text',
            'valor' => $parametro
        ];
    }
}
```

**Saída (para API):**
```json
{
    "parametros": [
        {"tipo": "text", "valor": "João Silva"},
        {"tipo": "text", "valor": "Produto XYZ"},
        {"tipo": "text", "valor": "20%"}
    ]
}
```

## 🖼️ Configuração de Header (Imagem)

### Header Padrão
O sistema adiciona automaticamente um header padrão quando há parâmetros:

```php
$payload['header'] = [
    'filename' => "logo.png",
    'linkMedia' => URL . "/public/img/logo.png"
];
```

### Header Personalizado
Para usar um header personalizado:

```php
$header = [
    'filename' => 'minha_imagem.png',
    'linkMedia' => 'https://meusite.com/img/minha_imagem.png'
];

$resultado = $serproApi->enviarTemplate(
    '5511999999999',
    'meu_template',
    $parametros,
    $header
);
```

### Sem Header
Para enviar template sem header:

```php
$resultado = $serproApi->enviarTemplate(
    '5511999999999',
    'meu_template',
    $parametros,
    []  // Header vazio
);
```

## 🔍 Debug e Logs

### Habilitando Debug
No arquivo `config/config.php`:

```php
define('DEBUG', true);
```

### Logs de Debug
Os logs são salvos em `logs/serpro_debug.log`:

```json
{
    "timestamp": "2025-01-03 10:30:00",
    "titulo": "Enviando template",
    "dados": {
        "destinatario": "5511999999999",
        "template": "boas_vindas",
        "parametros_count": 1,
        "has_header": true,
        "payload": {...}
    }
}
```

## 📊 Teste de Templates

### Arquivo de Teste
Use o arquivo `test_template_serpro.php` para testar templates:

```bash
http://localhost/meu-framework/test_template_serpro.php
```

### Verificações Importantes

1. **Template Aprovado**: Certifique-se de que o template está aprovado na Meta
2. **Parâmetros Corretos**: Verifique se os parâmetros estão no formato correto
3. **Token Válido**: Confirme se o token JWT está válido
4. **Número Válido**: Use formato correto: `5511999999999`

## ❌ Erros Comuns

### 1. Template Não Aprovado
```json
{
    "error": "Template não encontrado ou não aprovado"
}
```
**Solução:** Aprove o template na Meta Business

### 2. Parâmetros Incorretos
```json
{
    "error": "Parâmetros inválidos"
}
```
**Solução:** Verifique o formato dos parâmetros

### 3. Token Expirado
```json
{
    "error": "Token JWT expirado"
}
```
**Solução:** O sistema renova automaticamente, aguarde alguns minutos

### 4. Número Inválido
```json
{
    "error": "Número de telefone inválido"
}
```
**Solução:** Use formato: `5511999999999`

## 🔄 Manutenção de Templates

### Adicionando Novos Templates

1. **No Controller Chat.php:**
```php
private function getTemplatesDisponiveis()
{
    return [
        // ... templates existentes ...
        [
            'nome' => 'novo_template',
            'titulo' => 'Novo Template',
            'descricao' => 'Descrição do template',
            'parametros' => ['param1', 'param2']
        ]
    ];
}
```

2. **Aprovação na Meta:**
   - Acesse Meta Business
   - Vá para WhatsApp > Gerenciar
   - Adicione/aprove o template

### Modificando Templates Existentes

1. Edite o template na Meta Business
2. Aguarde aprovação
3. Teste no sistema

## 🔒 Segurança

### Validação de Parâmetros
```php
private function validarParametros($parametros)
{
    foreach ($parametros as $parametro) {
        if (empty($parametro['valor'])) {
            return false;
        }
        // Adicionar mais validações conforme necessário
    }
    return true;
}
```

### Sanitização
```php
private function sanitizarParametro($valor)
{
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}
```

## 📈 Monitoramento

### Métricas Importantes
- Taxa de entrega de templates
- Tempo de resposta da API
- Erros por tipo de template
- Volume de envios por período

### Alertas
- Token próximo ao vencimento
- Falhas consecutivas na API
- Templates rejeitados

## 🎯 Conclusão

O sistema está configurado para funcionar corretamente com a API Serpro, seguindo o padrão fornecido. Use este guia como referência para implementar novos templates e resolver problemas comuns.

**Pontos importantes:**
- Sempre teste em ambiente de desenvolvimento
- Mantenha os templates atualizados na Meta
- Monitore os logs para identificar problemas
- Siga as melhores práticas de segurança 