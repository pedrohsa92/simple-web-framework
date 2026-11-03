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
  <main>
    <?= $this->insert("sistema::headerApp", $this->data) ?>

    <?= $this->section("content"); ?>

    <?= $this->insert("sistema::footerApp", $this->data) ?>
  </main>

  <?= $pagina->framework->js ?>
  <?= $pagina->assets->js ?>
  <?= $this->section("script"); ?>

  <script>
    const CONF_PATH = "<?= CONF_PATH ?>";
    const usuarioLogado = {
      usu_id: `<?= $usuarioLogado->usu_id ?>`,
      usu_sidebar: <?= $usuarioLogado->usu_sidebar ?? 1 ?>,
      usu_primarycolor: `<?= $usuarioLogado->usu_primarycolor ?? null ?>`,
      usu_tema: `<?= $usuarioLogado->usu_tema ?? null ?>`,
    }

    <?php if ( !is_null($usuarioLogado->usu_id) ): ?>
      fgbAlternarSidebar(usuarioLogado.usu_sidebar);
      fgbAlterarTema(usuarioLogado.usu_tema);
    <?php endif ?>

    const alturaParaExibirMenuApp = 150;

    $(window).on('scroll', function () {
      const scrollAtual = $(this).scrollTop();
      const $menu = $('#menuApp');

      if (scrollAtual >= alturaParaExibirMenuApp) {
        $menu.fadeIn(200).css('display', 'flex');
      } else {
        $menu.fadeOut(200);
      }
    });
  </script>
</body>
</html>