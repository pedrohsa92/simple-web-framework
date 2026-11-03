<?php
  $siteProject = $projeto ?? (object) [];
  $siteLanguage = $siteProject->prj_lang ?? 'pt-BR';
  $siteGoogleTag = $siteProject->prj_googletag ?? null;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) $siteLanguage, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <?php if (!empty($siteGoogleTag)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($siteGoogleTag, ENT_QUOTES, 'UTF-8') ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag() { dataLayer.push(arguments); }
      gtag('js', new Date());
      gtag('config', <?= json_encode($siteGoogleTag, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
    </script>
  <?php endif; ?>

  <meta charset="utf-8">
  <meta name="msapplication-tap-highlight" content="no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, shrink-to-fit=no">
  <meta name="author" content="<?= htmlspecialchars($siteProject->prj_author ?? 'icliv - https://icliv.com.br', ENT_QUOTES, 'UTF-8') ?>">
  <meta name="robots" content="<?= htmlspecialchars($siteProject->prj_robots ?? 'index, follow, noarchive', ENT_QUOTES, 'UTF-8') ?>">

  <?= $pagina->meta ?? '' ?>
  <?= $pagina->webapp ?? '' ?>
  <?= $pagina->structuredata ?? '' ?>
  <?= $pagina->framework->css ?? '' ?>
  <?= $pagina->assets->css ?? '' ?>

  <?= $this->section('style'); ?>
  <style>@import url("<?= Helpers::url('source/Web/assets/css/site.css?v=20261007-icliv') ?>");</style>
</head>
<body class="site-page">
  <div id="preloader"><div class="spinner"></div></div>

  <?= $this->insert('site::header', $this->data) ?>

  <main>
    <?= $this->section('content'); ?>
  </main>

  <?= $this->insert('site::footer', $this->data) ?>

  <?= $pagina->framework->js ?? '' ?>
  <?= $pagina->assets->js ?? '' ?>
  <?= $this->section('script'); ?>

  <script>
    const CONF_PATH = <?= json_encode(Helpers::url(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  </script>

  <a href="#top" id="scrollToTop" class="scroll-to-top" aria-label="Voltar ao topo"><i class="bx bx-up-arrow-alt"></i></a>
</body>
</html>
