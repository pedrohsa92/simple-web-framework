<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php if (!empty($empresaSelecionada->emp_googletag ?? null)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?=$empresaSelecionada->emp_googletag?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }

      gtag('js', new Date());
      gtag('config', '<?=$empresaSelecionada->emp_googletag?>');
    </script>
  <?php endif ?>

  <meta charset="utf-8"/>
  <meta name="msapplication-tap-highlight" content="no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, shrink-to-fit=no">
  <meta name="author" content="Pedro HSA - https://phsa.com.br">
  <meta name="robots" content="index, follow, noarchive">

  <?=$pagina->meta ?? ''?>
  <?=$pagina->webapp ?? ''?>
  <?=$pagina->pwa ?? ''?>
  <?=$pagina->structuredata ?? ''?>
  <?=$pagina->framework->css ?? ''?>
  <?=$pagina->assets->css ?? ''?>

  <?=$this->section("style")?>
</head>
<body>
  <div id="preloader">
    <div class="spinner"></div>
  </div>

  <main>
    <?=$this->section("content")?>
  </main>

  <?=$pagina->framework->js ?? ''?>
  <?=$pagina->assets->js ?? ''?>

  <?=$this->section("script")?>

  <script>
    const CONF_URL = "<?=Helpers::url()?>";
  </script>

  <?=$pagina->serviceworker ?? ''?>

  <a href="#" id="scrollToTop" class="scroll-to-top">
    <i class="bx bx-up-arrow-alt"></i>
  </a>
</body>
</html>