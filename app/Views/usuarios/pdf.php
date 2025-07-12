<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Usuários</title>
    <style>
        * {
            font-family: "Helvetica", sans-serif;
            box-sizing: border-box;
        }
        body {
            font-size: 12px;
            line-height: 1.4;
            margin: 2px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if (isset($dados['logo_url'])): ?>
            <img src="<?= $dados['logo_url'] ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <div class="title">Relatório de Usuários</div>
        <div class="subtitle">Gerado em: <?= $dados['data_geracao'] ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados['usuarios'] as $usuario): ?>
                <tr>
                    <td><?= $usuario->id ?></td>
                    <td><?= $usuario->nome ?></td>
                    <td><?= $usuario->email ?></td>
                    <td><?= ucfirst($usuario->perfil) ?></td>
                    <td><?= ucfirst($usuario->status) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Total de usuários: <?= count($dados['usuarios']) ?></p>
        <p>Documento gerado automaticamente em <?= Helper::dataAtual() ?></p>
    </div>
</body>
</html> 