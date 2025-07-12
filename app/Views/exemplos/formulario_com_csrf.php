<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemplo de Formulário com CSRF</title>
    
    <!-- Meta tag para token CSRF (uso em JavaScript) -->
    <?= Helper::csrfMeta() ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Exemplo de Formulário com Proteção CSRF</h4>
                        <small class="text-muted">Demonstração do novo sistema de rotas e segurança</small>
                    </div>
                    <div class="card-body">
                        
                        <!-- EXEMPLO 1: Formulário com CSRF Protection -->
                        <h5>1. Formulário com Proteção CSRF</h5>
                        <form action="/usuarios/cadastrar" method="POST" class="mb-4">
                            <!-- Token CSRF obrigatório para formulários POST -->
                            <?= Helper::csrfField() ?>
                            
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome:</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nome" 
                                       name="nome" 
                                       value="<?= Helper::old('nome') ?>" 
                                       required>
                                <small class="text-muted">Usando Helper::old() para manter valor após erro</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail:</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= Helper::old('email') ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha:</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="senha" 
                                       name="senha" 
                                       required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Cadastrar com CSRF</button>
                        </form>
                        
                        <hr>
                        
                        <!-- EXEMPLO 2: AJAX com CSRF -->
                        <h5>2. Requisição AJAX com CSRF</h5>
                        <button id="ajax-btn" class="btn btn-info mb-4">Fazer Requisição AJAX</button>
                        
                        <hr>
                        
                        <!-- EXEMPLO 3: Demonstração de helpers -->
                        <h5>3. Helpers Disponíveis</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Helper::csrfToken():</strong><br>
                                <code><?= Helper::csrfToken() ?></code>
                            </div>
                            <div class="col-md-6">
                                <strong>Helper::asset():</strong><br>
                                <code><?= Helper::asset('css/style.css') ?></code>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- EXEMPLO 4: Demonstração de debug -->
                        <h5>4. Helpers de Debug</h5>
                        <button onclick="demonstrarDump()" class="btn btn-secondary">Testar Helper::dump()</button>
                        
                    </div>
                </div>
                
                <!-- Informações sobre o novo sistema -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>🚀 Como usar o novo sistema de rotas</h5>
                    </div>
                    <div class="card-body">
                        <h6>1. Definindo rotas em routes/web.php:</h6>
                        <pre><code>// Rota GET simples
Route::get('/usuarios', 'Usuarios@listar');

// Rota POST com middleware CSRF
Route::post('/usuarios/cadastrar', 'Usuarios@cadastrar')->middleware(['csrf']);

// Grupo de rotas com middleware
Route::group(['middleware' => ['auth']], function() {
    Route::get('/dashboard', 'Dashboard@inicial');
    Route::post('/logout', 'Login@sair');
});

// Rotas com parâmetros
Route::get('/usuarios/{id}', 'Usuarios@show');
Route::put('/usuarios/{id}', 'Usuarios@update');

// Rotas de recurso completo (CRUD)
Route::resource('posts', 'PostController');</code></pre>
                        
                        <h6 class="mt-3">2. Middlewares disponíveis:</h6>
                        <ul>
                            <li><code>auth</code> - Verifica se usuário está logado</li>
                            <li><code>admin</code> - Verifica se usuário é admin</li>
                            <li><code>csrf</code> - Verifica token CSRF</li>
                        </ul>
                        
                        <h6 class="mt-3">3. Helpers de segurança:</h6>
                        <ul>
                            <li><code>Helper::csrfField()</code> - Campo hidden com token</li>
                            <li><code>Helper::csrfToken()</code> - Obtém token atual</li>
                            <li><code>Helper::csrfMeta()</code> - Meta tag para JavaScript</li>
                            <li><code>Helper::old('campo')</code> - Valor antigo após erro</li>
                        </ul>
                        
                        <h6 class="mt-3">4. Compatibilidade:</h6>
                        <p class="text-muted">O sistema mantém compatibilidade com o roteador antigo como fallback.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exemplo de AJAX com CSRF
        document.getElementById('ajax-btn').addEventListener('click', function() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/api/usuarios', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    teste: 'dados'
                })
            })
            .then(response => response.json())
            .then(data => {
                alert('Requisição AJAX realizada com sucesso!');
                console.log(data);
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro na requisição: ' + error);
            });
        });
        
        // Demonstração do Helper::dump()
        function demonstrarDump() {
            // Esta função seria executada no PHP
            alert('Verifique o Helper::dump() no código PHP');
        }
        
        // Configurar CSRF para todas as requisições AJAX
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            // Configurar para jQuery se estiver usando
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token.getAttribute('content')
                    }
                });
            }
        }
    </script>
</body>
</html> 