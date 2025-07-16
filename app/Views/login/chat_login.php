<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NOME ?> - Login</title>
    <link rel="icon" href="<?= Helper::asset('img/logo.png') ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
     <link rel="stylesheet" href="<?= Helper::asset('css/login.css') ?>">


</head>
<body>
    <!-- Toggle de tema -->
    <button class="theme-toggle"  id="toggleTheme" data-toggle="tooltip" title="Alternar tema">
        <i class="fas fa-moon"></i>
    </button>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo e título -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h1 class="app-title"><?= APP_NOME ?></h1>
                <p class="app-subtitle">Sistema de Atendimento Multicanal</p>
            </div>

            <!-- Mensagens de erro/sucesso -->
            <?php if (!empty($dados['erro_geral'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $dados['erro_geral'] ?>
                </div>
            <?php endif; ?>

            <!-- Formulário de login -->
            <form action="<?= URL ?>/login" method="POST" id="loginForm">
                <?= Helper::csrfField() ?>
                
                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="position-relative">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            class="form-control <?= !empty($dados['email_erro']) ? 'is-invalid' : '' ?>"
                            name="email" 
                            id="email"
                            value="<?= $dados['email'] ?? '' ?>" 
                            placeholder="Digite seu email"
                            required
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Digite seu email"
                        >
                        <?php if (!empty($dados['email_erro'])): ?>
                            <div class="invalid-feedback"><?= $dados['email_erro'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Senha -->
                <div class="form-group">
                    <label class="form-label" for="senha">Senha</label>
                    <div class="position-relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            class="form-control <?= !empty($dados['senha_erro']) ? 'is-invalid' : '' ?>"
                            name="senha" 
                            id="senha"
                            placeholder="Digite sua senha"
                            required
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Digite sua senha"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if (!empty($dados['senha_erro'])): ?>
                            <div class="invalid-feedback"><?= $dados['senha_erro'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status inicial -->
                <div class="status-selector">
                    <label class="form-label">Status Inicial</label>
                    <div class="status-options">
                        <label class="status-option status-ativo active">
                            <input type="radio" name="status_inicial" value="ativo" checked>
                            <span class="status-indicator"></span>
                            Disponível
                        </label>
                        <label class="status-option status-ausente">
                            <input type="radio" name="status_inicial" value="ausente">
                            <span class="status-indicator"></span>
                            Ausente
                        </label>
                        <label class="status-option status-ocupado">
                            <input type="radio" name="status_inicial" value="ocupado">
                            <span class="status-indicator"></span>
                            Ocupado
                        </label>
                    </div>
                </div>

                <!-- Botão de login -->
                <button type="submit" class="btn btn-login mt-4">
                    <span class="btn-text">Entrar no Sistema</span>
                    <div class="loading"></div>
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= Helper::asset('js/login.js') ?>"></script>
    <script src="<?= Helper::asset('js/dashboard.js') ?>"></script>
</body>
</html> 