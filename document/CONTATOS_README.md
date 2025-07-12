# Sistema de Gerenciamento de Contatos - ChatSerpro

## 📋 Visão Geral

O sistema de gerenciamento de contatos do ChatSerpro é uma solução completa para organizar, categorizar e acompanhar todos os contatos que interagem com seu sistema de atendimento via WhatsApp. Desenvolvido com foco na usabilidade e performance, oferece recursos avançados para equipes de atendimento de todos os tamanhos.

## ✨ Funcionalidades Principais

### 🔍 Listagem e Filtros Avançados
- **Listagem com paginação** (15 contatos por página)
- **Filtros múltiplos**: por nome, telefone, email, status, tags e período
- **Busca inteligente**: encontre contatos rapidamente
- **Ordenação**: por último contato, nome, empresa
- **Estatísticas em tempo real**: total de contatos, ativos, bloqueados

### 📝 Gestão Completa de Contatos
- **Cadastro detalhado**: nome, telefone, email, empresa, observações
- **Edição em linha**: atualize informações facilmente
- **Sistema de tags**: organize contatos por categorias personalizadas
- **Observações**: adicione notas importantes sobre cada contato
- **Histórico completo**: veja todas as interações registradas

### 🏷️ Sistema de Tags Inteligente
- **Tags populares**: sugestões baseadas em uso
- **Tags personalizadas**: crie suas próprias categorias
- **Filtros por tag**: encontre contatos por categoria
- **Gerenciamento dinâmico**: adicione/remova tags via AJAX

### 🔒 Controle de Acesso
- **Bloqueio de contatos**: impeça mensagens de contatos indesejados
- **Desbloqueio rápido**: reative contatos com um clique
- **Permissões por perfil**: controle quem pode excluir contatos
- **Auditoria**: rastreie todas as alterações

### 📊 Histórico e Estatísticas
- **Histórico completo**: todas as mensagens trocadas
- **Estatísticas detalhadas**: mensagens enviadas/recebidas, mídias
- **Timeline visual**: acompanhe a evolução do relacionamento
- **Relatórios de engajamento**: identifique contatos mais ativos

### 🌐 Integração WhatsApp
- **Botão direto**: inicie conversas com um clique
- **Sincronização automática**: contatos criados automaticamente
- **Status de entrega**: acompanhe se as mensagens foram lidas
- **Suporte a mídias**: imagens, áudios, documentos

## 🏗️ Estrutura Técnica

### Arquitetura MVC
```
app/
├── Controllers/
│   └── Contatos.php          # Controlador principal
├── Models/
│   └── ContatoModel.php      # Model com todas as operações
└── Views/
    └── contatos/
        ├── listar.php        # Listagem com filtros
        ├── cadastrar.php     # Formulário de cadastro
        ├── editar.php        # Formulário de edição
        └── perfil.php        # Perfil completo do contato
```

### Banco de Dados
```sql
-- Tabela principal
contatos (id, nome, telefone, email, empresa, observacoes, fonte, bloqueado, ultimo_contato)

-- Sistema de tags
contato_tags (id, contato_id, tag, criado_em)

-- Integração com conversas
conversas (id, contato_id, usuario_id, status, protocolo)

-- Histórico de mensagens
mensagens (id, conversa_id, tipo, conteudo, criado_em)
```

### Tecnologias Utilizadas
- **Backend**: PHP 8.0+ com padrão MVC
- **Frontend**: Bootstrap 5.3, jQuery 3.7, Font Awesome 6.4
- **Banco**: MySQL 8.0+ com InnoDB
- **AJAX**: Operações em tempo real
- **Responsivo**: Mobile-first design

## 📱 Interface do Usuário

### Design Moderno
- **Dark Mode**: tema escuro/claro automático
- **Responsivo**: funciona perfeitamente em mobile
- **Tooltips**: ajuda contextual em todos os elementos
- **Feedback visual**: notificações toast para todas as ações
- **Carregamento otimizado**: lazy loading para listas grandes

### Usabilidade
- **Navegação intuitiva**: menu lateral com indicadores visuais
- **Filtros rápidos**: aplicação automática via dropdown
- **Busca instantânea**: resultados em tempo real
- **Ações em lote**: seleção múltipla para operações
- **Atalhos de teclado**: produtividade aumentada

## 🔧 Configuração e Instalação

### Requisitos
```
- PHP 8.0 ou superior
- MySQL 8.0 ou superior
- Extensões PHP: PDO, mysqli, json, mbstring
- Servidor web: Apache/Nginx
```

### Instalação
1. **Execute o SQL de migração**:
```bash
mysql -u seu_usuario -p seu_banco < migration_contatos.sql
```

2. **Configure as permissões**:
```php
// No seu sistema, certifique-se de que as rotas estão ativas
// Verifique o arquivo routes/web.php
```

3. **Teste o sistema**:
```
- Acesse /contatos para ver a listagem
- Cadastre um contato de teste
- Verifique as funcionalidades AJAX
```

## 📋 Guia de Uso

### Para Administradores
```
✅ Acesso total a todos os contatos
✅ Pode cadastrar, editar e excluir contatos
✅ Gerencia tags e categorias
✅ Acesso a estatísticas completas
✅ Pode bloquear/desbloquear contatos
```

### Para Supervisores
```
✅ Pode ver todos os contatos
✅ Pode cadastrar e editar contatos
✅ Pode bloquear/desbloquear contatos
✅ Não pode excluir contatos
✅ Acesso a relatórios básicos
```

### Para Atendentes
```
✅ Pode ver todos os contatos
✅ Pode cadastrar novos contatos
✅ Pode editar contatos que criou
✅ Pode adicionar tags e observações
✅ Acesso ao histórico de conversas
```

## 🎯 Casos de Uso Práticos

### 1. Cadastro de Novo Cliente
```
1. Clique em "Novo Contato"
2. Preencha as informações básicas
3. Adicione tags relevantes (cliente, vip, etc.)
4. Salve e inicie conversa via WhatsApp
```

### 2. Organização por Tags
```
1. Acesse a listagem de contatos
2. Use o filtro por tags
3. Adicione tags populares com um clique
4. Organize por: cliente, prospect, fornecedor, etc.
```

### 3. Atendimento Personalizado
```
1. Veja o perfil completo do contato
2. Consulte o histórico de conversas
3. Leia observações da equipe
4. Personalize o atendimento baseado no histórico
```

### 4. Controle de Acesso
```
1. Identifique contatos problemáticos
2. Bloqueie contatos indesejados
3. Monitore tentativas de contato
4. Desbloqueie quando necessário
```

## 📊 Métricas e Relatórios

### Estatísticas Disponíveis
- **Total de contatos** cadastrados
- **Contatos ativos** (não bloqueados)
- **Contatos do dia** (novos cadastros)
- **Contatos da semana** (últimos 7 dias)
- **Distribuição por tags**
- **Engajamento por contato**

### Relatórios Personalizados
```
# Contatos mais ativos
SELECT nome, COUNT(m.id) as mensagens 
FROM contatos c 
LEFT JOIN conversas cv ON c.id = cv.contato_id 
LEFT JOIN mensagens m ON cv.id = m.conversa_id 
GROUP BY c.id 
ORDER BY mensagens DESC;

# Tags mais usadas
SELECT tag, COUNT(*) as total 
FROM contato_tags 
GROUP BY tag 
ORDER BY total DESC;
```

## 🔄 Integração com WhatsApp

### Recursos Disponíveis
- **Link direto**: botão "Abrir WhatsApp" em cada contato
- **Criação automática**: contatos criados ao receber mensagens
- **Sincronização**: atualização automática do último contato
- **Histórico preservado**: todas as mensagens são salvas

### Fluxo de Integração
```
1. Mensagem recebida no WhatsApp
2. Sistema verifica se contato existe
3. Se não existe, cria automaticamente
4. Atualiza timestamp do último contato
5. Salva mensagem no histórico
```

## 🛠️ Personalização e Extensão

### Adicionando Novos Campos
```php
// No ContatoModel.php
private $camposPermitidos = [
    'nome', 'telefone', 'email', 
    'empresa', 'observacoes', 'novo_campo'
];
```

### Criando Novos Filtros
```php
// No Controller
if (!empty($filtros['novo_filtro'])) {
    $where[] = "c.campo = :filtro";
    $params['filtro'] = $filtros['novo_filtro'];
}
```

### Modificando a Interface
```html
<!-- Adicionar novo campo no formulário -->
<div class="form-group">
    <label>Novo Campo</label>
    <input type="text" name="novo_campo" class="form-control">
</div>
```

## 🔍 Solução de Problemas

### Problemas Comuns

**1. Contatos não aparecendo**
```
- Verifique se o banco foi criado corretamente
- Confirme as permissões do usuário
- Teste a conexão com o banco
```

**2. AJAX não funcionando**
```
- Verifique se jQuery está carregado
- Confirme as rotas em routes/web.php
- Teste as URLs diretamente no navegador
```

**3. Tags não salvando**
```
- Verifique se a tabela contato_tags existe
- Confirme os relacionamentos (Foreign Keys)
- Teste a função adicionarTag() isoladamente
```

**4. Histórico vazio**
```
- Verifique se as tabelas conversas e mensagens existem
- Confirme os relacionamentos entre tabelas
- Teste a função getHistoricoConversa()
```

## 🚀 Melhorias Futuras

### Roadmap de Desenvolvimento
- [ ] **Importação em massa** via CSV/Excel
- [ ] **Exportação de dados** para backup
- [ ] **API REST** para integrações externas
- [ ] **Notificações push** para novos contatos
- [ ] **Integração com CRM** externos
- [ ] **Análise de sentimento** nas mensagens
- [ ] **Chatbot inteligente** para triagem
- [ ] **Relatórios avançados** com gráficos
- [ ] **Backup automático** de contatos
- [ ] **Auditoria completa** de alterações

### Melhorias de Performance
- [ ] **Cache Redis** para consultas frequentes
- [ ] **Indexação avançada** no banco
- [ ] **Lazy loading** para listas grandes
- [ ] **Compressão de dados** para histórico
- [ ] **CDN** para assets estáticos

## 📞 Suporte e Contribuição

### Encontrou um Bug?
1. Verifique se já foi reportado
2. Crie um issue detalhado
3. Inclua steps para reproduzir
4. Adicione screenshots se necessário

### Quer Contribuir?
1. Fork o repositório
2. Crie uma branch para sua feature
3. Implemente com testes
4. Envie um pull request

### Comunidade
- **Documentação**: Sempre atualizada
- **Fórum**: Suporte da comunidade
- **Discord**: Chat em tempo real
- **YouTube**: Tutoriais em vídeo

---

## 📄 Licença

Este sistema é parte do **ChatSerpro** e segue a mesma licença do projeto principal.

## 🏆 Créditos

Desenvolvido com 💜 pela equipe ChatSerpro para otimizar o atendimento ao cliente via WhatsApp.

---

**Versão**: 1.0.0  
**Última atualização**: Janeiro 2025  
**Compatibilidade**: PHP 8.0+, MySQL 8.0+ 