<?php include 'app/Views/include/head.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="display-1 text-danger"><?= $dados['codigoErro'] ?? '404' ?></h1>
                <h2 class="mb-4"><?= $dados['tituloPagina'] ?? 'Página Não Encontrada' ?></h2>
                <p class="lead text-muted mb-4">
                    <?= $dados['mensagemErro'] ?? 'A página que você está procurando não existe ou foi movida.' ?>
                </p>
                <div class="mb-4">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Esta rota não está cadastrada no sistema de rotas.
                    </p>
                </div>
                <div class="mt-2">
                    <a href="<?= URL ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>
                        Voltar para a Página Inicial
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>
                        Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'app/Views/include/linkjs.php'; ?>
<style>
.error-page {
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.error-page h1 {
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.error-page .btn {
    transition: all 0.3s ease;
}

.error-page .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>


