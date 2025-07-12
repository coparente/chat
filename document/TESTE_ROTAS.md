# 🧪 Guia de Teste - Sistema de Rotas v2.0

## 📋 Como Testar o Sistema

### 1. URLs que DEVEM funcionar (cadastradas)
```
✅ http://localhost/meu-framework/                 → Login
✅ http://localhost/meu-framework/login            → Login
✅ http://localhost/meu-framework/dashboard        → Dashboard (após login)
✅ http://localhost/meu-framework/usuarios         → Lista usuários (após login)
✅ http://localhost/meu-framework/pagina/erro      → Página de erro
```

### 2. URLs que DEVEM dar erro 404 (não cadastradas)
```
❌ http://localhost/meu-framework/pagina-inexistente  → Erro 404
❌ http://localhost/meu-framework/teste               → Erro 404
❌ http://localhost/meu-framework/qualquer-coisa      → Erro 404
❌ http://localhost/meu-framework/usuarios/teste      → Erro 404 (rota não cadastrada)
```

### 3. Teste de Rotas com Parâmetros
Para testar rotas com parâmetros, descomente no arquivo `routes/web.php`:

```php
// Descomente esta linha no routes/web.php
Route::get('/usuario/{id}', function($id) {
    echo '<h1>Usuário ID: ' . $id . '</h1>';
    echo '<a href="' . URL . '">Voltar ao início</a>';
});
```

Depois teste:
```
✅ http://localhost/meu-framework/usuario/123  → Mostra ID: 123
✅ http://localhost/meu-framework/usuario/456  → Mostra ID: 456
```

### 4. Teste de Closures
Para testar closures, descomente no arquivo `routes/web.php`:

```php
// Descomente esta linha no routes/web.php
Route::get('/teste', function() {
    echo '<h1>Rota de teste funcionando!</h1>';
    echo '<p>Esta é uma rota de exemplo usando closure.</p>';
    echo '<a href="' . URL . '">Voltar ao início</a>';
});
```

Depois teste:
```
✅ http://localhost/meu-framework/teste  → Página de teste
```

## 🔧 Verificação do Sistema

### 1. Verificar se Apache está rodando
- Acesse: http://localhost/
- Deve mostrar a página do XAMPP

### 2. Verificar se o site carrega
- Acesse: http://localhost/meu-framework/
- Deve mostrar a página de login

### 3. Verificar erro 404
- Acesse: http://localhost/meu-framework/pagina-que-nao-existe
- Deve mostrar página de erro 404 personalizada

### 4. Verificar logs do Apache
Se ainda houver erro 500, verifique os logs:
```bash
# Windows
Get-Content C:\xampp\apache\logs\error.log | Select-String "meu-framework" | Select-Object -Last 5

# Linux/Mac
tail -f /var/log/apache2/error.log
```

## 🐛 Problemas Comuns

### Erro 500 Internal Server Error
1. **Verificar .htaccess**: Certifique-se que não há diretivas inválidas
2. **Verificar permissões**: Pastas devem ter permissão de leitura
3. **Verificar PHP**: Teste se `php -l index.php` não mostra erros
4. **Verificar banco**: Certifique-se que o banco está rodando

### Página em branco
1. **Verificar PHP errors**: Adicione no início do index.php:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Rota não funciona
1. **Verificar se está cadastrada** em `routes/web.php`
2. **Verificar sintaxe** da rota
3. **Verificar se controller existe**
4. **Verificar se método existe no controller**

## 🎯 Exemplos de Uso

### Adicionar nova rota simples
```php
// Em routes/web.php
Route::get('/minha-pagina', 'MeuController@minhaFuncao');
```

### Adicionar rota com parâmetro
```php
// Em routes/web.php
Route::get('/produto/{id}', 'ProdutoController@show');
```

### Adicionar rota com middleware
```php
// Em routes/web.php
Route::get('/admin', 'AdminController@index')->middleware(['auth', 'admin']);
```

### Adicionar grupo de rotas
```php
// Em routes/web.php
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function() {
    Route::get('/', 'AdminController@index');
    Route::get('/usuarios', 'AdminController@usuarios');
});
```

## ✅ Checklist Final

- [ ] Site carrega em http://localhost/meu-framework/
- [ ] Páginas não cadastradas mostram erro 404
- [ ] Página de login funciona
- [ ] Depois do login, dashboard carrega
- [ ] Rotas com parâmetros funcionam (se descomentadas)
- [ ] Closures funcionam (se descomentadas)
- [ ] Não há erros nos logs do Apache

## 📞 Precisa de ajuda?

Se encontrar problemas:
1. Verifique os logs do Apache
2. Teste com `php -l` os arquivos PHP
3. Verifique se todas as rotas estão cadastradas em `routes/web.php`
4. Certifique-se que controllers e métodos existem 