<!DOCTYPE html>
<html lang="<?=CONF_SITE_LANG?>">
<head>
  <meta charset="utf-8"/>
  <meta name="msapplication-tap-highlight" content="no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, shrink-to-fit=no">
  <meta name="author" content="Pedro HSA - Https://phsa.com.br">
  <meta name="robots" content="index, follow, noarchive">

  <?=$pagina->meta?>
  <?=$pagina->framework->css?>
  <?=$pagina->assets->css?>

  <?= $this->section("style"); ?>
</head>
<body>
  <main>
    <?= $this->section("content"); ?>
  </main>

  <?=$pagina->framework->js?>
  <?=$pagina->assets->js?>

  <?= $this->section("script"); ?>

  <!-- Constantes JS -->
  <script>
    const CONF_PATH = "<?= CONF_PATH ?>";
  </script>
</body>
</html>