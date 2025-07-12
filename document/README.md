# Meu-Framework v2.0 🚀

> Framework PHP moderno com sistema de rotas estilo Laravel, segurança aprimorada e arquitetura MVC robusta.

[![Versão](https://img.shields.io/badge/versão-2.0.0-blue.svg)](https://github.com/seu-usuario/meu-framework)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net)
[![Segurança](https://img.shields.io/badge/segurança-9%2F10-brightgreen.svg)](#-segurança)
[![Licença](https://img.shields.io/badge/licença-MIT-yellow.svg)](LICENSE)

## 🆕 Novidades da v2.0

### 🚀 Sistema de Rotas Moderno
```php
// routes/web.php - Estilo Laravel
Route::get('/', 'HomeController@index');
Route::post('/users/login', 'UserController@login');
Route::group(['middleware' => ['auth']], function() {
    Route::resource('posts', 'PostController');
});
```

### 🔒 Segurança Aprimorada
- **Proteção CSRF** automática
- **Headers de segurança** completos
- **Middleware** de autenticação e autorização
- **Variáveis de ambiente** para credenciais

### 🛠️ Helpers Modernos
```php
Helper::csrfField()    // Token CSRF para formulários
Helper::old('nome')    // Manter valores após erro
Helper::asset('css')   // URLs para assets
Helper::dd($var)       // Debug and die
```

---

## 🚀 Funcionalidades Principais

### Módulos e Submódulos
- Cadastro de módulos principais e submódulos
- Gerenciamento de status (ativo/inativo)
- Controle hierárquico de módulos
- Personalização de ícones (FontAwesome)
- Definição de rotas personalizadas

### Sistema de Usuários
- Cadastro e gerenciamento de usuários
- Múltiplos níveis de acesso (admin/analista/usuário)
- Sistema de autenticação seguro com CSRF
- Gerenciamento de permissões granular por módulo
- Perfis de acesso personalizáveis

### Sistema de Rotas v2.0
- **Sintaxe Laravel**: `Route::get()`, `Route::post()`, etc.
- **Rotas com parâmetros**: `/users/{id}`
- **Grupos de rotas**: Middleware e prefixos
- **Rotas de recurso**: CRUD automático com `Route::resource()`
- **Middleware**: `auth`, `admin`, `csrf`
- **Compatibilidade**: Fallback para sistema legado

### Permissões Avançadas
- Atribuição granular de permissões
- Herança de permissões entre módulos e submódulos
- Interface intuitiva para gerenciamento
- Validação em tempo real
- Proteção contra acessos não autorizados

---

## 🛠️ Tecnologias Utilizadas

- **PHP 7.4+** - Linguagem principal
- **MySQL/MariaDB** - Banco de dados
- **PDO** - Prepared statements para segurança
- **Bootstrap 5** - Framework CSS
- **FontAwesome 5** - Ícones
- **JavaScript/jQuery** - Interatividade
- **Composer** - Gerenciamento de dependências

---

## 🔧 Instalação Rápida

### 1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/meu-framework.git
cd meu-framework
```

### 2. Instale dependências
```bash
composer install
```

### 3. Configure ambiente
```bash
# Crie o arquivo .env (você precisa criar este arquivo manualmente)
cp .env.example .env

# Edite .env com suas configurações
nano .env
```

### 4. Configure banco de dados
```bash
# Importe a estrutura do banco
mysql -u root -p < db_meu_framework.sql
```

### 5. Configure servidor web
```apache
# Apache Virtual Host
<VirtualHost *:80>
    DocumentRoot "/caminho/para/meu-framework"
    ServerName meu-framework.local
    DirectoryIndex index.php
</VirtualHost>
```

### 6. Acesse o sistema
```
http://localhost/meu-framework
```

---

## 👥 Usuários Padrão

| Tipo | Login | Senha | Permissões |
|------|-------|-------|------------|
| **Admin** | admin@tjgo.jus.br | 123456 | Acesso total |
| **Analista** | analista@tjgo.jus.br | 123456 | Gestão limitada |
| **Usuário** | usuario@tjgo.jus.br | 123456 | Acesso básico |

---

## 📁 Estrutura do Projeto

```
meu-framework/
├── app/
│   ├── Controllers/          # Controladores MVC
│   ├── Models/              # Modelos de dados
│   ├── Views/               # Templates e views
│   ├── Libraries/           # Classes core (Route, Database, etc.)
│   ├── configuracao.php     # Configurações do sistema
│   └── autoload.php         # Autoload customizado
├── routes/
│   └── web.php              # Definição de rotas (NOVO!)
├── public/
│   ├── css/                 # Folhas de estilo
│   ├── js/                  # Scripts JavaScript
│   ├── img/                 # Imagens
│   └── assets/              # Assets externos
├── vendor/                  # Dependências do Composer
├── uploads/                 # Upload de arquivos
├── index.php                # Ponto de entrada
├── .htaccess                # Configurações Apache
├── composer.json            # Dependências
└── README.md                # Este arquivo
```

---

## 🔐 Segurança

### Headers de Segurança Implementados
```apache
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: (em produção)
```

### Proteções Implementadas
- ✅ **SQL Injection**: Prepared statements PDO
- ✅ **CSRF**: Tokens automáticos em formulários
- ✅ **XSS**: Sanitização de entrada e escape de saída
- ✅ **Clickjacking**: X-Frame-Options
- ✅ **File Upload**: Validação e sanitização
- ✅ **Directory Traversal**: Bloqueio de arquivos sensíveis
- ✅ **Session Hijacking**: Configurações seguras

### Score de Segurança: 9/10 ⭐

---

## 🚀 Como Usar o Sistema de Rotas v2.0

### 1. Definindo Rotas Básicas
```php
// routes/web.php

// Rota GET simples
Route::get('/home', 'HomeController@index');

// Rota POST com dados
Route::post('/users', 'UserController@store');

// Rota com parâmetros
Route::get('/users/{id}', 'UserController@show');
Route::get('/posts/{id}/comments/{comment}', 'CommentController@show');
```

### 2. Grupos de Rotas com Middleware
```php
// Rotas que requerem autenticação
Route::group(['middleware' => ['auth']], function() {
    Route::get('/dashboard', 'DashboardController@index');
    Route::get('/profile', 'ProfileController@show');
});

// Rotas apenas para administradores
Route::group(['middleware' => ['auth', 'admin']], function() {
    Route::resource('users', 'UserController');
    Route::get('/admin/settings', 'AdminController@settings');
});

// Rotas com prefixo
Route::group(['prefix' => 'api', 'middleware' => ['auth']], function() {
    Route::get('/users', 'ApiController@users');
    Route::post('/upload', 'ApiController@upload');
});
```

### 3. Rotas de Recurso (CRUD Automático)
```php
// Uma linha cria 7 rotas automaticamente
Route::resource('posts', 'PostController');

// Equivale a:
// GET    /posts           -> PostController@index
// GET    /posts/create    -> PostController@create
// POST   /posts           -> PostController@store
// GET    /posts/{id}      -> PostController@show
// GET    /posts/{id}/edit -> PostController@edit
// PUT    /posts/{id}      -> PostController@update
// DELETE /posts/{id}      -> PostController@destroy
```

### 4. Middleware Disponíveis
- **`auth`** - Verifica se usuário está logado
- **`admin`** - Verifica se usuário é administrador
- **`csrf`** - Proteção CSRF para formulários

### 5. Formulários com Proteção CSRF
```php
<form method="POST" action="/users/create">
    <?= Helper::csrfField() ?>
    
    <input type="text" name="nome" value="<?= Helper::old('nome') ?>">
    <input type="email" name="email" value="<?= Helper::old('email') ?>">
    
    <button type="submit">Cadastrar</button>
</form>
```

---

## 🛠️ Helpers Úteis

### Segurança
```php
Helper::csrfField()              // Campo CSRF para formulários
Helper::csrfToken()              // Token CSRF atual
Helper::csrfMeta()               // Meta tag para AJAX
```

### Formulários
```php
Helper::old('campo', 'padrão')   // Valor antigo após erro
Helper::flashInput($dados)       // Salvar dados para próxima request
Helper::clearOldInput()          // Limpar dados salvos
```

### URLs e Assets
```php
Helper::asset('css/style.css')   // URL completa para asset
Helper::route('users.show', $id) // URL para rota nomeada (futuro)
```

### Debug
```php
Helper::dd($variavel)            // Debug and die
Helper::dump($variavel)          // Debug sem parar execução
```

---

## 📚 Documentação Adicional

- 📋 **[CHANGELOG.md](CHANGELOG.md)** - Histórico de mudanças
- 🚀 **[MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)** - Guia de migração
- 🧪 **[app/Views/exemplos/](app/Views/exemplos/)** - Exemplos práticos

---

## 🔄 Compatibilidade

### ✅ Migração Suave
- **100% compatível** com código existente
- Sistema legado mantido como **fallback automático**
- Migração **gradual** possível
- **Zero breaking changes**

### 🎯 Como Migrar
1. Definir novas rotas em `routes/web.php`
2. Testar funcionamento
3. Manter rotas antigas até migração completa
4. Remover sistema legado quando conveniente

---

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## 🆘 Suporte

Encontrou um bug ou tem uma sugestão?

- 📧 **Email**: coparente@tjgo.jus.br
- 🐛 **Issues**: [GitHub Issues](https://github.com/seu-usuario/meu-framework/issues)
- 📖 **Wiki**: [GitHub Wiki](https://github.com/seu-usuario/meu-framework/wiki)

---

## 🏆 Changelog Resumido

### v2.0.0 (2025-01-XX)
- 🚀 Sistema de rotas moderno estilo Laravel
- 🔒 Proteção CSRF completa
- 🛡️ Headers de segurança implementados
- 🔧 Helpers modernos adicionados
- 📁 Estrutura de pastas otimizada

### v1.0.0 (2024-XX-XX)
- 🎯 Sistema MVC básico
- 👤 Autenticação de usuários
- 🔐 Sistema de permissões
- 📊 CRUD completo

---

<div align="center">

**Desenvolvido com ❤️ para ser a base perfeita dos seus projetos PHP**

⭐ Se este projeto te ajudou, deixe uma estrela no GitHub!

</div>


