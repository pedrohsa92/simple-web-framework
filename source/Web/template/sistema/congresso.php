<!DOCTYPE html>
<html lang="<?= CONF_SITE_LANG ?>">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#000000">
  <meta name="author" content="IPR Contagem">
  <meta name="robots" content="noindex, nofollow">

  <?= $pagina->meta ?>
  <?= $pagina->structuredata ?>
  <?= $pagina->framework->css ?>
  <?= $pagina->assets->css ?>

  <?= $this->section("style"); ?>

  <?= $pagina->pwa ?>
</head>
<body>
  <?= $this->section("content"); ?>

  <?= $pagina->framework->js ?>
  <?= $pagina->assets->js ?>
  <?= $this->section("script"); ?>

  <script>
    const CONF_PATH = "<?= CONF_PATH ?>";
  </script>
</body>
</html>