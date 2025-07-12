# 📊 Sistema de Relatórios - ChatSerpro

## 🎯 Visão Geral

O sistema de relatórios do ChatSerpro oferece análises completas e detalhadas do desempenho do atendimento, permitindo aos gestores tomarem decisões baseadas em dados.

## 🚀 Funcionalidades Principais

### 📈 Dashboard Interativo
- **Gráficos em tempo real** com Chart.js
- **Filtros por período** personalizáveis
- **Indicadores visuais** de performance
- **Atualização automática** dos dados

### 📋 Relatórios Disponíveis

#### 1. **Relatório de Conversas**
- Lista detalhada de todas as conversas
- Filtros por data, status e atendente
- Estatísticas resumidas
- Exportação Excel/PDF

#### 2. **Performance de Atendentes**
- Produtividade individual
- Ranking por conversas atendidas
- Tempo médio de resposta
- Avaliações recebidas

#### 3. **Utilização de Templates**
- Templates mais utilizados
- Taxa de sucesso por template
- Análise de eficácia
- Conversas iniciadas por template

#### 4. **Volume de Mensagens**
- Mensagens por dia/hora
- Análise por tipo (texto, mídia, template)
- Distribuição entrada vs saída
- Picos de atividade

#### 5. **Tempo de Resposta**
- SLA de atendimento
- Tempos médios por atendente
- Evolução temporal
- Metas vs realizado

## 🔧 Configuração

### 1. **Executar Migration**
```sql
-- Execute o arquivo migration_relatorios.sql no seu banco
source migration_relatorios.sql;
```

### 2. **Verificar Permissões**
- Acesso restrito a **Admin** e **Supervisor**
- Configurado automaticamente no controller

### 3. **Configurar Índices (Opcional)**
```sql
-- Para melhor performance em grandes volumes
CREATE INDEX idx_mensagens_performance ON mensagens(criado_em, atendente_id, direcao);
CREATE INDEX idx_conversas_performance ON conversas(criado_em, status, atendente_id);
```

## 📊 Como Usar

### 1. **Acessar Relatórios**
```
http://seu-dominio/relatorios
```

### 2. **Filtrar Dados**
- Selecione período desejado
- Aplique filtros específicos
- Visualize resultados em tempo real

### 3. **Exportar Dados**
- **Excel**: Para análises detalhadas
- **PDF**: Para apresentações (em desenvolvimento)

## 🎨 Interface

### Dashboard Principal
```
┌─────────────────────────────────────┐
│ Período de Análise                  │
│ [Data Início] [Data Fim]            │
└─────────────────────────────────────┘

┌─────────────────┐ ┌─────────────────┐
│ Conversas/Dia   │ │ Status Conversas│
│ [Gráfico Linha] │ │ [Gráfico Pizza] │
└─────────────────┘ └─────────────────┘

┌─────────────────┐ ┌─────────────────┐
│ Top Atendentes  │ │ Templates Usados│
│ [Gráfico Barra] │ │ [Gráfico Barra] │
└─────────────────┘ └─────────────────┘
```

### Relatórios Específicos
```
┌─────────────────────────────────────┐
│ Filtros de Busca                    │
│ [Data] [Status] [Atendente] [Ações] │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Estatísticas Resumidas              │
│ [Cards com Números]                 │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Tabela de Dados                     │
│ [DataTable com Paginação]           │
└─────────────────────────────────────┘
```

## 📈 Métricas Calculadas

### Conversas
- **Total de conversas** no período
- **Taxa de fechamento** (fechadas/total)
- **Tempo médio de duração**
- **Contatos únicos** atendidos

### Atendentes
- **Conversas por atendente**
- **Tempo médio de resposta**
- **Avaliação média recebida**
- **Produtividade** (mensagens/hora)

### Templates
- **Utilização total** por template
- **Taxa de sucesso** (entregues/enviados)
- **Conversões** (respostas recebidas)

### Mensagens
- **Volume diário/horário**
- **Distribuição por tipo** (texto/mídia)
- **Fluxo** (entrada vs saída)

## 🔍 Consultas SQL Principais

### Performance de Atendentes
```sql
SELECT 
    u.nome,
    COUNT(DISTINCT c.id) as conversas,
    AVG(m.tempo_resposta) as tempo_medio,
    AVG(c.avaliacao) as avaliacao_media
FROM usuarios u
LEFT JOIN conversas c ON u.id = c.atendente_id
LEFT JOIN mensagens m ON u.id = m.atendente_id
WHERE u.perfil = 'atendente'
GROUP BY u.id;
```

### Volume de Mensagens
```sql
SELECT 
    DATE(criado_em) as data,
    COUNT(*) as total,
    SUM(CASE WHEN direcao = 'entrada' THEN 1 ELSE 0 END) as entrada,
    SUM(CASE WHEN direcao = 'saida' THEN 1 ELSE 0 END) as saida
FROM mensagens
GROUP BY DATE(criado_em);
```

## 🎯 KPIs Recomendados

### Para Gestores
1. **Taxa de Resolução**: Conversas fechadas / Total
2. **Tempo Médio de Resposta**: < 5 minutos
3. **Satisfação do Cliente**: Avaliação > 4.0
4. **Produtividade**: Conversas/atendente/dia

### Para Atendentes
1. **Conversas Atendidas**: Meta diária
2. **Tempo de Resposta**: Individual vs média
3. **Avaliação**: Feedback dos clientes
4. **Taxa de Resolução**: Problemas resolvidos

## 📱 Responsividade

- **Desktop**: Layout completo com todas as funcionalidades
- **Tablet**: Layout adaptado com navegação otimizada
- **Mobile**: Interface simplificada com foco nos dados essenciais

## 🔧 Troubleshooting

### Dados não aparecendo
1. Verificar se o período selecionado contém dados
2. Confirmar permissões do usuário logado
3. Verificar se as tabelas têm dados suficientes

### Performance lenta
1. Executar migration para criar índices
2. Limitar período de análise
3. Verificar se há muitos dados simultâneos

### Gráficos não carregando
1. Verificar conexão com CDN do Chart.js
2. Confirmar resposta da API dashboard
3. Verificar console do navegador para erros

## 🎉 Próximas Funcionalidades

- [ ] **Exportação PDF** melhorada
- [ ] **Relatórios agendados** por email
- [ ] **Alertas automáticos** baseados em KPIs
- [ ] **Comparativo entre períodos**
- [ ] **Análise de sentimento** das conversas
- [ ] **Relatório de ROI** do atendimento

## 🆘 Suporte

Para dúvidas ou problemas:
1. Verificar este README
2. Consultar logs do sistema
3. Contactar o administrador do sistema

---

**Sistema ChatSerpro - Relatórios** | **Versão 1.0.0** | **2025** 