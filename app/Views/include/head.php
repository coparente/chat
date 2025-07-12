<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  // Obtém a URL atual de forma segura
  $url = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI'], '/') : '';
  $segments = explode('/', $url);

  // Pega o terceiro segmento da URL (índice 2) se existir
  $titulo = isset($segments[2]) ? ucfirst($segments[2]) : APP_NOME;

  // Se não houver terceiro segmento, usa o nome da aplicação
  if (empty($titulo) || $titulo == '') {
    $titulo = APP_NOME;
  }
  ?>
  <title><?= APP_NOME ?> - <?= $titulo ?></title>

   <!-- CSS -->
   <link rel="stylesheet" href="<?= URL ?>/public/assets/componentes/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URL ?>/public/assets/componentes/font-awesome/css/all.min.css">
    <link href="<?= URL ?>/public/assets/componentes/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="<?= URL ?>/public/assets/dist/css/AdminTJGO.css" rel="stylesheet" type="text/css">
    <link href="<?= URL ?>/public/assets/dist/css/TJ_contraste-normal.css" rel="alternate stylesheet" type="text/css"
        title="contrasteNormal" disabled>
    <link href="<?= URL ?>/public/assets/dist/css/TJ_contraste-preto.css" rel="alternate stylesheet" type="text/css"
        title="contrastePreto" disabled>
      <link rel="shortcut icon" href="<?= URL ?>/public/assets/dist/img/favicon.ico">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= URL ?>/public/assets/componentes/select2/dist/css/select2.min.css">
    <!-- sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- chartjs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />

  <!-- Custom CSS -->
  <link href="<?= URL ?>/public/css/estilos.css" rel="stylesheet">

  <!-- Notificações -->
  <link href="<?= URL ?>/public/css/notificacoes.css" rel="stylesheet">

  <!-- DataTables -->
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <script>
    const URL = '<?= URL ?>';
</script>
</head>
<body class="skin-blue-light layout-top-nav">
    <div class="geral">