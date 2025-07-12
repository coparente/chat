# 🚀 Guia de Migração - Sistema de Rotas v2.0

Este guia vai te ajudar a migrar do sistema de roteamento antigo para o novo sistema moderno estilo Laravel.

## 📋 Checklist de Migração

### ✅ 1. Preparação do Ambiente

- [ ] Criar arquivo `.env` baseado no `.env.example`
- [ ] Mover credenciais sensíveis para o `.env`
- [ ] Verificar se o servidor suporta headers mod_headers
- [ ] Fazer backup do projeto atual

### ✅ 2. Configuração de Segurança

- [ ] Testar se os headers de segurança estão funcionando
- [ ] Verificar se arquivos sensíveis estão bloqueados
- [ ] Testar proteção CSRF em formulários

### ✅ 3. Migração das Rotas

- [ ] Identificar todas as rotas atuais
- [ ] Migrar rotas para `routes/web.php`
- [ ] Testar cada rota migrada
- [ ] Adicionar middlewares apropriados

---

## 🔄 Como Migrar suas Rotas

### Sistema Antigo ➡️ Sistema Novo

#### Rota Simples
```php
// ANTES (automático)
// URL: /usuarios/listar → Controllers/Usuarios.php → listar()

// DEPOIS (explícito em routes/web.php)
Route::get('/usuarios/listar', 'Usuarios@listar');
```

#### Rota com Parâmetros
```php
// ANTES
// URL: /usuarios/editar/123 → Controllers/Usuarios.php → editar(123)

// DEPOIS
Route::get('/usuarios/editar/{id}', 'Usuarios@editar');
```

#### Rotas POST com CSRF
```php
// ANTES (sem proteção CSRF)
// Formulário POST direto

// DEPOIS (com proteção CSRF)
Route::post('/usuarios/cadastrar', 'Usuarios@cadastrar')->middleware(['csrf']);

// No formulário:
<form method="POST">
    <?= Helper::csrfField() ?>
    <!-- campos do formulário -->
</form>
```

#### Rotas Protegidas
```php
// ANTES (verificação manual no controller)
if (!isset($_SESSION['usuario_id'])) {
    Helper::redirecionar('login');
}

// DEPOIS (middleware automático)
Route::group(['middleware' => ['auth']], function() {
    Route::get('/dashboard', 'Dashboard@inicial');
    Route::get('/usuarios', 'Usuarios@listar');
});
```

---

## 🛠️ Exemplos Práticos de Migração

### 1. Sistema de Usuários

#### Antes (Sistema Legado)
```
URLs automáticas:
/usuarios/listar
/usuarios/cadastrar
/usuarios/editar/123
/usuarios/excluir/123
```

#### Depois (routes/web.php)
```php
Route::group(['middleware' => ['auth']], function() {
    // Listar usuários
    Route::get('/usuarios', 'Usuarios@listar');
    Route::get('/usuarios/listar', 'Usuarios@listar');
    
    // Cadastrar usuário
    Route::get('/usuarios/cadastrar', 'Usuarios@cadastrar');
    Route::post('/usuarios/cadastrar', 'Usuarios@cadastrar')->middleware(['csrf']);
    
    // Editar usuário
    Route::get('/usuarios/editar/{id}', 'Usuarios@editar');
    Route::post('/usuarios/editar/{id}', 'Usuarios@editar')->middleware(['csrf']);
    
    // Excluir usuário
    Route::post('/usuarios/excluir/{id}', 'Usuarios@excluir')->middleware(['csrf']);
});
```

### 2. Sistema de Login

#### Antes
```php
// Controllers/Login.php handles everything automatically
```

#### Depois
```php
// Rotas públicas (sem middleware)
Route::get('/', 'Login@login');
Route::get('/login', 'Login@login');
Route::post('/login', 'Login@login')->middleware(['csrf']);
Route::get('/logout', 'Login@sair');
```

### 3. Recursos CRUD Completos

#### Antes (manual)
```php
// Múltiplas URLs manuais para cada ação
```

#### Depois (automático)
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

---

## 🔒 Implementando Segurança CSRF

### 1. Em Formulários HTML

#### Antes (sem proteção)
```html
<form method="POST" action="/usuarios/cadastrar">
    <input type="text" name="nome">
    <button type="submit">Salvar</button>
</form>
```

#### Depois (com proteção)
```html
<form method="POST" action="/usuarios/cadastrar">
    <?= Helper::csrfField() ?>
    <input type="text" name="nome" value="<?= Helper::old('nome') ?>">
    <button type="submit">Salvar</button>
</form>
```

### 2. Em Requisições AJAX

#### JavaScript/jQuery
```javascript
// Configurar token CSRF para todas as requisições
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Fetch API
fetch('/api/usuarios', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify(data)
});

// jQuery
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': token
    }
});
```

### 3. Atualizar Controller

#### Antes
```php
public function cadastrar() {
    $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    // processar dados...
}
```

#### Depois (mantém compatibilidade)
```php
public function cadastrar() {
    // O middleware CSRF já validou o token automaticamente
    $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    
    // Salvar dados antigos em caso de erro
    if ($erro) {
        Helper::flashInput($dados);
        Helper::redirecionar('usuarios/cadastrar');
    }
    
    // Limpar dados antigos em caso de sucesso
    Helper::clearOldInput();
    // processar dados...
}
```

---

## 🚦 Testando a Migração

### 1. Lista de Verificação

#### URLs Funcionando
- [ ] Página inicial (`/`)
- [ ] Login (`/login`)
- [ ] Dashboard (`/dashboard`)
- [ ] Listar usuários (`/usuarios`)
- [ ] Cadastrar usuário (`/usuarios/cadastrar`)

#### Segurança
- [ ] Headers de segurança presentes
- [ ] Arquivos `.env` bloqueados
- [ ] Pastas `app/`, `vendor/` bloqueadas
- [ ] CSRF funcionando em formulários

#### Funcionalidades
- [ ] Login/logout funcionando
- [ ] Cadastro de usuários funcionando
- [ ] Edição de usuários funcionando
- [ ] Permissões sendo respeitadas

### 2. Comandos de Teste

```bash
# Verificar headers de segurança
curl -I http://localhost/meu-framework

# Tentar acessar arquivo .env (deve dar 403)
curl http://localhost/meu-framework/.env

# Tentar acessar pasta app (deve dar 403)
curl http://localhost/meu-framework/app/
```

---

## 🛟 Solução de Problemas

### Problema: "Headers already sent"
```php
// Verificar se não há espaços/quebras antes do <?php
// Verificar se não há echo antes dos headers
```

### Problema: "CSRF Token Mismatch"
```php
// Verificar se o formulário tem <?= Helper::csrfField() ?>
// Verificar se a rota tem middleware(['csrf'])
// Verificar se a sessão está funcionando
```

### Problema: "Route not found"
```php
// Verificar se a rota está definida em routes/web.php
// Verificar se o controller existe
// Verificar se o método existe no controller
```

### Problema: "Permission denied"
```php
// Verificar middlewares de autenticação
// Verificar se o usuário está logado
// Verificar permissões do usuário
```

---

## 🎯 Próximos Passos

1. **Migrar gradualmente**: Não precisa migrar tudo de uma vez
2. **Testar cada rota**: Certifique-se que cada rota migrada funciona
3. **Documentar mudanças**: Mantenha registro do que foi alterado
4. **Treinar equipe**: Ensine como usar o novo sistema
5. **Monitorar logs**: Verifique se há erros após migração

---

## 📞 Precisa de Ajuda?

- Consulte o arquivo `CHANGELOG.md` para detalhes das mudanças
- Veja exemplos em `app/Views/exemplos/formulario_com_csrf.php`
- Teste com rotas simples primeiro antes de migrar o sistema completo

## ✅ Migração Completa!

Após seguir este guia, seu sistema terá:
- 🔒 Segurança aprimorada com CSRF
- 🚀 Sistema de rotas moderno
- 🛡️ Headers de segurança
- 📝 Melhor organização de código
- 🔄 Compatibilidade mantida 