<?php
  $caminhoFaviconSistema = (string) ($contexto->organizacao->org_favicon ?? $usuarioLogado->organizacao->org_favicon ?? 'storage/global/favicon.png');
  $faviconSistema = Helpers::storage($caminhoFaviconSistema, 'storage/global/favicon.png');
  $arquivoFaviconSistema = dirname(__DIR__, 4) . '/' . ltrim($caminhoFaviconSistema, '/\\');
  if (is_file($arquivoFaviconSistema)) {
    $faviconSistema .= '?v=' . filemtime($arquivoFaviconSistema);
  }
?>
<!DOCTYPE html>
<html lang="<?= $projeto->prj_lang ?? 'pt-BR' ?>">
<head>
  <?php if (!empty($usuarioLogado->empresa->emp_googletag ?? $usuarioLogado->organizacao->org_googletag ?? $projeto->prj_googletag ?? null)): ?>
    <?php $googleTag = $usuarioLogado->empresa->emp_googletag ?? $usuarioLogado->organizacao->org_googletag ?? $projeto->prj_googletag; ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $googleTag ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }

      gtag('js', new Date());
      gtag('config', '<?= $googleTag ?>');
    </script>
  <?php endif; ?>

  <meta charset="utf-8">
  <meta name="msapplication-tap-highlight" content="no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, shrink-to-fit=no">
  <meta name="author" content="<?= $projeto->prj_author ?? 'Pedro HSA - https://phsa.com.br' ?>">
  <meta name="robots" content="<?= $projeto->prj_robots ?? 'index, follow, noarchive' ?>">

  <?= $pagina->meta ?? '' ?>
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($faviconSistema, ENT_QUOTES, 'UTF-8') ?>">
  <?= $pagina->structuredata ?? '' ?>
  <?= $pagina->framework->css ?? '' ?>
  <?= $pagina->assets->css ?? '' ?>

  <?= $this->section("style"); ?>

  <?= $pagina->pwa ?? '' ?>
</head>
<body style="overflow: hidden!important;">
  <div id="preloader">
    <div class="spinner"></div>
  </div>

  <?= $this->insert("sistema::header", $this->data) ?>

  <main id="simpleMain" class="simpleMain">
    <?= $this->insert("sistema::sidebar", $this->data) ?>

    <div id="simpleConteudo" class="simpleConteudo">
      <?= $this->insert("sistema::breadcrumb", $this->data) ?>

      <?= $this->section("content"); ?>
      <?= $this->insert("sistema::footer", $this->data) ?>
    </div>
  </main>

  <framework>
    <?= $pagina->framework->js ?? '' ?>
  </framework>

  <?= $pagina->assets->js ?? '' ?>

  <?= $this->section("script"); ?>

  <script>
    const CONF_URL = <?= json_encode(Helpers::url(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    const projetoAtual = <?= json_encode([
      'prj_id' => (int) ($projeto->prj_id ?? 0),
      'prj_nome' => $projeto->prj_nome ?? '',
      'prj_titulo' => $projeto->prj_titulo ?? '',
      'prj_tipo' => $projeto->prj_tipo ?? '',
      'prj_dominio' => $projeto->prj_dominio ?? ''
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    const usuarioLogado = <?= json_encode([
      'usu_id' => (int) ($usuarioLogado->usu_id ?? 0),
      'usu_nomefantasia' => $usuarioLogado->usu_nomefantasia ?? '',
      'usu_razaosocial' => $usuarioLogado->usu_razaosocial ?? '',
      'usu_sidebar' => (int) ($usuarioLogado->usu_sidebar ?? 1),
      'usu_paletacores' => $usuarioLogado->usu_paletacores ?? '',
      'usu_tema' => $usuarioLogado->usu_tema ?? 'light',
      'organizacao' => [
        'org_id' => (int) ($usuarioLogado->organizacao->org_id ?? 0),
        'org_nomefantasia' => $usuarioLogado->organizacao->org_nomefantasia ?? '',
        'org_ambiente' => $usuarioLogado->organizacao->org_ambiente ?? ''
      ],
      'empresa' => [
        'emp_id' => (int) ($usuarioLogado->empresa->emp_id ?? 0),
        'emp_nomefantasia' => $usuarioLogado->empresa->emp_nomefantasia ?? '',
        'emp_tipo' => $usuarioLogado->empresa->emp_tipo ?? '',
        'emp_paletacores' => $usuarioLogado->empresa->emp_paletacores ?? ''
      ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    fgbAlternarSidebar(usuarioLogado.usu_sidebar, false);
    fgbAlterarTema(usuarioLogado.usu_tema, false);

    if (usuarioLogado.usu_paletacores) {
      fgbAlterarPaletaDeCores(usuarioLogado.usu_paletacores);
    } else if (usuarioLogado.empresa && usuarioLogado.empresa.emp_paletacores) {
      fgbAlterarPaletaDeCores(usuarioLogado.empresa.emp_paletacores);
    }

    $(document).ready(function () {
      function getPhsaTabContext($item) {
        const $nav = $item.closest('.phsaScrollNav');
        const $header = $nav.closest('.phsaScrollHeader');
        const $content = $header.next('.phsaTabContent');

        return {
          nav: $nav,
          header: $header,
          content: $content
        };
      }

      function ativarPhsaTab($item) {
        const target = $item.data('target');

        if (!target) {
          return false;
        }

        const ctx = getPhsaTabContext($item);

        if (!ctx.nav.length || !ctx.header.length || !ctx.content.length) {
          return false;
        }

        const $target = ctx.content.children('.phsaTabItem#' + target);

        if (!$target.length) {
          return false;
        }

        ctx.nav.find('> .phsaScrollList > .phsaScrollItem').removeClass('active');
        $item.addClass('active');

        ctx.content.children('.phsaTabItem').removeClass('active');
        $target.addClass('active');

        ativarPrimeiraSubTab($target);

        return true;
      }

      function ativarPrimeiraSubTab($tabItem) {
        const $subHeader = $tabItem.children('.phsaScrollHeader').first();

        if (!$subHeader.length) {
          return;
        }

        const $subNav = $subHeader.find('.phsaScrollNav').first();
        const $activeSubItem = $subNav.find('> .phsaScrollList > .phsaScrollItem.active').first();

        if ($activeSubItem.length) {
          ativarPhsaTab($activeSubItem);
          return;
        }

        const $firstSubItem = $subNav.find('> .phsaScrollList > .phsaScrollItem').first();

        if ($firstSubItem.length) {
          ativarPhsaTab($firstSubItem);
        }
      }

      function inicializarPhsaTabs() {
        $('.phsaScrollHeader').each(function () {
          const $header = $(this);
          const $nav = $header.find('.phsaScrollNav').first();
          const $content = $header.next('.phsaTabContent');

          if (!$nav.length || !$content.length) {
            return;
          }

          const $items = $nav.find('> .phsaScrollList > .phsaScrollItem');

          if (!$items.length) {
            return;
          }

          let $activeItem = $items.filter('.active').first();

          if (!$activeItem.length) {
            $activeItem = $items.first();
          }

          ativarPhsaTab($activeItem);
        });
      }

      function ativarTabPorLink(tabLink) {
        if (!tabLink) {
          return;
        }

        const partes = tabLink.split('/').filter(Boolean);

        partes.forEach(function (target) {
          const $item = $('.phsaScrollItem[data-target="' + target + '"]').first();

          if ($item.length) {
            ativarPhsaTab($item);
          }
        });
      }

      $(document).on('click', '.phsaScrollItem', function (event) {
        event.preventDefault();
        event.stopPropagation();

        ativarPhsaTab($(this));
      });

      inicializarPhsaTabs();

      const tabLink = <?= json_encode($tabLink ?? '', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

      ativarTabPorLink(tabLink);
    });
  </script>

  <?= $pagina->serviceworker ?? '' ?>

  <a href="#" id="scrollToTop" class="scroll-to-top">
    <i class="bx bx-up-arrow-alt"></i>
  </a>
</body>
</html>
