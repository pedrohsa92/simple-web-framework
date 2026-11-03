<?php
  $this->layout('sistema::sistema');
  $conteudos = $conteudos ?? [];
  $categorias = $categorias ?? [];
  $registro = $registro ?? null;
  $midias = $midias ?? [];
  $pesquisa = trim((string) ($pesquisa ?? ''));
  $categoriaSelecionada = (int) ($categoriaSelecionada ?? 0);
  $esc = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $urlAjuda = static fn (array $params = []): string => Helpers::url('ajuda' . ($params ? '?' . http_build_query($params) : ''));
  $tagsDoConhecimento = static function ($json): array {
    $tags = json_decode((string) $json, true);
    return is_array($tags) ? array_slice(array_filter(array_map(static fn ($tag): string => trim((string) $tag), $tags)), 0, 3) : [];
  };
  $conteudoSeguro = static function (string $html): string {
    if ($html === '' || !class_exists('DOMDocument')) return nl2br(htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    $documento = new DOMDocument('1.0', 'UTF-8');
    $anterior = libxml_use_internal_errors(true);
    $documento->loadHTML('<?xml encoding="UTF-8"><div id="help-content">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($anterior);
    $container = $documento->getElementById('help-content');
    if (!$container) return '';
    $permitidas = ['p','br','strong','b','em','i','u','s','ul','ol','li','h2','h3','h4','blockquote','a','img','table','thead','tbody','tr','th','td','hr','pre','code','span','div'];
    $percorrer = static function ($no) use (&$percorrer, $permitidas): void {
      for ($filho = $no->firstChild; $filho;) {
        $seguinte = $filho->nextSibling;
        if ($filho instanceof DOMElement) {
          $tag = strtolower($filho->tagName);
          if (!in_array($tag, $permitidas, true)) {
            while ($filho->firstChild) $filho->parentNode->insertBefore($filho->firstChild, $filho);
            $filho->parentNode->removeChild($filho);
          } else {
            $permitirAtributos = $tag === 'a' ? ['href','title','target','rel'] : ($tag === 'img' ? ['src','alt','title','width','height'] : []);
            foreach (iterator_to_array($filho->attributes) as $atributo) {
              if (!in_array(strtolower($atributo->name), $permitirAtributos, true)) $filho->removeAttribute($atributo->name);
            }
            foreach (['href','src'] as $atributoUrl) {
              if ($filho->hasAttribute($atributoUrl) && !preg_match('#^(https?://|/|data:image/(png|jpeg|gif|webp);base64,)#i', trim($filho->getAttribute($atributoUrl)))) {
                $filho->removeAttribute($atributoUrl);
              }
            }
            $percorrer($filho);
          }
        }
        $filho = $seguinte;
      }
    };
    $percorrer($container);
    $saida = '';
    foreach ($container->childNodes as $filho) $saida .= $documento->saveHTML($filho);
    return $saida;
  };
  $embedDaMidia = static function (object $midia) use ($esc): string {
    $url = trim((string) ($midia->cmi_url_publica ?? $midia->cmi_url ?? ''));
    if ($url === '') return '';
    if (($midia->cmi_tipo ?? '') === 'Upload') {
      return '<video class="w-100 rounded" controls preload="metadata" playsinline src="' . $esc($url) . '"></video>';
    }
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $path = (string) parse_url($url, PHP_URL_PATH);
    $videoId = '';
    if (str_contains($host, 'youtu.be')) $videoId = trim($path, '/');
    elseif (str_contains($host, 'youtube.com')) {
      parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
      $videoId = (string) ($query['v'] ?? '');
      if ($videoId === '' && preg_match('#/(embed|shorts)/([^/?]+)#', $path, $matches)) $videoId = $matches[2];
    }
    if ($videoId !== '' && preg_match('/^[A-Za-z0-9_-]+$/', $videoId)) {
      return '<div class="ratio ratio-16x9"><iframe src="https://www.youtube-nocookie.com/embed/' . $esc($videoId) . '" title="' . $esc($midia->cmi_nome ?? 'Vídeo') . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe></div>';
    }
    if (str_contains($host, 'vimeo.com') && preg_match('#/(\d+)#', $path, $matches)) {
      return '<div class="ratio ratio-16x9"><iframe src="https://player.vimeo.com/video/' . $esc($matches[1]) . '" title="' . $esc($midia->cmi_nome ?? 'Vídeo') . '" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>';
    }
    return '<a class="bto bto-phsa-light" href="' . $esc($url) . '" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-arrow-up-right-from-square"></i>&nbsp;Abrir mídia</a>';
  };
  $categoriasPorId = [];
  foreach ($categorias as $categoria) $categoriasPorId[(int) $categoria->cct_id] = $categoria;
  $categoriaSelecionadaObj = null;
  foreach ($categorias as $categoria) {
    if ((int) ($categoria->cct_id ?? 0) === $categoriaSelecionada) {
      $categoriaSelecionadaObj = $categoria;
      break;
    }
  }
?>

<style>
  .central-ajuda { --ajuda-azul: var(--phsa-primary, #3159a8); --ajuda-borda: #e1e7f0; }
  .central-ajuda-heading { position: relative; isolation: isolate; overflow: hidden; margin-bottom: 1rem; padding: .85rem 1rem 1rem; border: 1px solid rgba(49, 89, 168, .16); border-radius: .8rem; }
  .central-ajuda-heading.is-reader { padding: .65rem .8rem; }
  .central-ajuda-heading::before, .central-ajuda-heading::after { content: ''; position: absolute; z-index: -1; border-radius: 50%; pointer-events: none; }
  .central-ajuda-heading::before { width: 7rem; height: 7rem; right: 3.2rem; top: -5.1rem; background: rgba(255, 255, 255, .42); }
  .central-ajuda-heading::after { width: 8rem; height: 8rem; right: -3rem; bottom: -5.2rem; background: rgba(49, 89, 168, .06); }
  .central-ajuda-heading h1 { position: relative; font-size: 1.45rem; font-weight: 650; color: inherit; }
  .central-ajuda-heading > p { position: relative; }
  .central-ajuda-heading form { position: relative; }
  .central-ajuda-search { width: 100%; }
  .central-ajuda-search .form-control { min-height: 3.15rem; border-left: 0; }
  .central-ajuda-search .input-group-text { padding-inline: 1rem; border-right: 0; background: #fff; }
  .central-ajuda-layout { margin-top: 1.25rem; }
  .central-ajuda-tree { position: sticky; top: 1rem; border-right: 1px solid var(--ajuda-borda); padding-right: 1rem; }
  .central-ajuda-tree-title { color: #7a8799; font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: .25rem .65rem .6rem; }
  .central-ajuda-tree-all, .central-ajuda-tree-category { display: flex; align-items: center; gap: .55rem; min-height: 2.2rem; border-radius: .42rem; padding: .42rem .65rem; color: #43516a; text-decoration: none; line-height: 1.3; }
  .central-ajuda-tree-all { margin-bottom: .55rem; font-weight: 600; }
  .central-ajuda-tree-category { margin-top: .35rem; font-size: .9rem; font-weight: 650; }
  .central-ajuda-tree-all:hover, .central-ajuda-tree-category:hover, .central-ajuda-tree-item:hover,
  .central-ajuda-tree-all.active, .central-ajuda-tree-category.active, .central-ajuda-tree-item.active { color: var(--ajuda-azul); background: #edf2fc; }
  .central-ajuda-tree-count { margin-left: auto; color: #8994a5; font-size: .75rem; font-weight: 400; }
  .central-ajuda-main { min-width: 0; }
  .central-ajuda-section-title { font-size: 1.05rem; font-weight: 650; }
  .central-ajuda-results { border-top: 1px solid var(--ajuda-borda); }
  .central-ajuda-result { display: flex; align-items: flex-start; gap: .8rem; padding: .95rem .35rem; border-bottom: 1px solid var(--ajuda-borda); color: inherit; text-decoration: none; }
  .central-ajuda-result:hover .central-ajuda-result-title { color: var(--ajuda-azul); }
  .central-ajuda-result-icon { display: grid; flex: 0 0 2rem; width: 2rem; height: 2rem; place-items: center; margin-top: .1rem; border-radius: .5rem; background: #f1f4f9; color: var(--ajuda-azul); }
  .central-ajuda-result-title { font-weight: 600; transition: color .15s ease; }
  .central-ajuda-result-arrow { margin: .4rem .2rem 0 auto; color: #8b96a7; }
  .central-ajuda-article { max-width: 1050px; }
  .central-ajuda-content { max-width: 900px; line-height: 1.8; overflow-wrap: anywhere; }
  .central-ajuda-content h2, .central-ajuda-content h3, .central-ajuda-content h4 { margin-top: 1.8rem; margin-bottom: .7rem; color: #1f2f49; }
  .central-ajuda-content p, .central-ajuda-content ul, .central-ajuda-content ol { color: #475569; }
  .central-ajuda-content img { max-width: 100%; height: auto; border-radius: .5rem; }
  .central-ajuda-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
  .central-ajuda-content th, .central-ajuda-content td { border: 1px solid #dee2e6; padding: .5rem; }
  .central-ajuda-topic-nav { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin: 0 0 .75rem; }
  .central-ajuda-topic-nav a, .central-ajuda-topic-nav span { display: inline-flex; align-items: center; gap: .45rem; color: var(--ajuda-azul); font-size: .85rem; text-decoration: none; }
  .central-ajuda-topic-nav a:hover { text-decoration: underline; }
  .central-ajuda-topic-nav .is-unavailable { color: #9aa4b2; }
  .central-ajuda-reader { --reader-bg: #fff; --reader-color: #26364c; --reader-muted: #68778b; --reader-border: #dce3ed; position: relative; display: flex; width: 100%; min-height: 480px; flex-direction: column; overflow: hidden; border: 1px solid var(--reader-border); border-radius: .85rem; background: var(--reader-bg); color: var(--reader-color); box-shadow: 0 .3rem 1rem rgba(20, 45, 90, .06); transition: background .18s ease, color .18s ease; }
  .central-ajuda-reader[data-theme="dark"] { --reader-bg: #171b24; --reader-color: #e7eaf0; --reader-muted: #aab4c3; --reader-border: #343b49; color-scheme: dark; }
  .central-ajuda-reader[data-reading-mode="sepia"] .central-ajuda-reader-scroll { background: #f2e8d5; color: #473b2b; }
  .central-ajuda-reader[data-reading-mode="paper-light"] .central-ajuda-reader-scroll { background: #fffaf0; color: #3d3b36; }
  .central-ajuda-reader[data-reading-mode="soft-yellow"] .central-ajuda-reader-scroll { background: #fff8d8; color: #403c2c; }
  .central-ajuda-reader[data-reading-mode="rest-green"] .central-ajuda-reader-scroll { background: #eaf5eb; color: #30483b; }
  .central-ajuda-reader[data-reading-mode="rest-blue"] .central-ajuda-reader-scroll { background: #eaf3fa; color: #304458; }
  .central-ajuda-reader[data-reading-mode="soft-gray"] .central-ajuda-reader-scroll { background: #eef0f2; color: #353c45; }
  .central-ajuda-reader[data-theme="dark"] .central-ajuda-reader-scroll { background: #202631; color: #e7eaf0; }
  .central-ajuda-reader-toolbar { display: flex; align-items: center; justify-content: flex-end; gap: .4rem; padding: .55rem .75rem; border-bottom: 1px solid var(--reader-border); background: var(--reader-bg); }
  .central-ajuda-reader-toolbar-label { margin-right: auto; color: var(--reader-muted); font-size: .78rem; }
  .central-ajuda-reader-toolbar-label a { color: inherit; text-decoration: none; }
  .central-ajuda-reader-toolbar-label a:hover { color: var(--ajuda-azul); text-decoration: underline; }
  .central-ajuda-tool { display: grid; width: 2.25rem; height: 2.25rem; place-items: center; border: 1px solid transparent; border-radius: .55rem; color: var(--reader-muted); background: transparent; }
  .central-ajuda-tool:hover, .central-ajuda-tool.is-active { border-color: var(--reader-border); color: var(--ajuda-azul); background: rgba(49, 89, 168, .08); }
  .central-ajuda-reader-scroll { flex: 1 1 auto; min-height: 0; overflow: auto; padding: clamp(1.2rem, 4vw, 2.8rem); background: var(--reader-bg); color: var(--reader-color); transition: background .18s ease, color .18s ease; }
  .central-ajuda-reader-head { max-width: 900px; margin: 0 auto 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--reader-border); }
  .central-ajuda-reader-title { margin: 0; color: var(--ajuda-azul); font-size: clamp(1.55rem, 3vw, 2.15rem); line-height: 1.25; }
  .central-ajuda-reader-summary { margin: .8rem 0 0; color: var(--reader-muted); font-size: 1.02rem; }
  .central-ajuda-reader-content { max-width: 900px; margin: 0 auto; font-size: var(--reader-font-size, 17px); font-family: var(--reader-font-family, system-ui, sans-serif); line-height: 1.8; overflow-wrap: anywhere; }
  .central-ajuda-reader.is-reading-mode .central-ajuda-reader-scroll { padding-top: clamp(2rem, 6vw, 4rem); padding-bottom: clamp(2rem, 6vw, 4rem); }
  .central-ajuda-reader.is-reading-mode .central-ajuda-reader-content { max-width: 740px; line-height: 2; }
  .central-ajuda-reader-content h2, .central-ajuda-reader-content h3, .central-ajuda-reader-content h4 { color: inherit; font-family: inherit; }
  .central-ajuda-reader-content p, .central-ajuda-reader-content li { color: inherit; }
  .central-ajuda-reader-content a { color: var(--ajuda-azul); }
  .central-ajuda-reader-media { margin-top: 2rem; padding-top: 1.2rem; border-top: 1px solid var(--reader-border); }
  .central-ajuda-reader-media h2 { margin: 0 0 .9rem; font-size: 1rem; }
  .central-ajuda-reader-media-item { margin-bottom: 1.25rem; }
  .central-ajuda-reader-media-item:last-child { margin-bottom: 0; }
  .central-ajuda-reader-media-item h3 { margin: 0 0 .65rem; font-size: .9rem; }
  .central-ajuda-reader-meta { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); align-items: center; gap: .55rem 1rem; padding: .8rem 1rem; border-top: 1px solid var(--reader-border); color: var(--reader-muted); background: var(--reader-bg); font-size: .78rem; }
  .central-ajuda-reader-tags { display: inline-flex; flex-wrap: wrap; gap: .5rem; color: var(--ajuda-azul); }
  .central-ajuda-reader-tags a { color: inherit; text-decoration: none; }
  .central-ajuda-reader-tags a:hover { text-decoration: underline; }
  .central-ajuda-reader-meta-item { display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap; }
  .central-ajuda-reader-views { justify-self: center; }
  .central-ajuda-reader-updated { justify-self: end; }
  .central-ajuda-reader-expanded { position: fixed; z-index: 1080; inset: 0; width: 100vw; height: 100dvh; max-width: none; min-height: 0; border: 0; border-radius: 0; }
  .central-ajuda-reader-expanded .central-ajuda-reader-scroll { padding: clamp(1rem, 5vw, 4rem); }
  body.central-ajuda-reader-open { overflow: hidden; }
  .central-ajuda-settings-backdrop { position: fixed; z-index: 1090; inset: 0; visibility: hidden; background: rgba(13, 20, 32, .35); opacity: 0; transition: opacity .2s ease, visibility .2s ease; }
  .central-ajuda-settings-backdrop.is-open { visibility: visible; opacity: 1; }
  .central-ajuda-settings { position: fixed; z-index: 1100; top: 0; right: 0; bottom: 0; width: min(390px, 92vw); padding: 1.1rem; transform: translateX(102%); border-left: 1px solid #dce3ed; background: #fff; box-shadow: 0 0 2rem rgba(0, 0, 0, .16); transition: transform .22s ease; }
  .central-ajuda-settings.is-open { transform: translateX(0); }
  .central-ajuda-settings-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
  .central-ajuda-settings-head h2 { margin: 0; font-size: 1.05rem; font-weight: 700; }
  .central-ajuda-settings-close { display: grid; width: 2.25rem; height: 2.25rem; place-items: center; border: 1px solid #dce3ed; border-radius: 50%; color: #526176; background: #f7f9fc; }
  .central-ajuda-setting { margin-bottom: 1.1rem; }
  .central-ajuda-setting label { display: block; margin-bottom: .4rem; color: #68778b; font-size: .78rem; font-weight: 700; }
  .central-ajuda-setting .form-select { min-height: 2.65rem; }
  .central-ajuda-font-size { display: grid; grid-template-columns: 2.75rem 1fr 2.75rem; min-height: 2.65rem; overflow: hidden; border: 1px solid #ced4da; border-radius: .375rem; }
  .central-ajuda-font-size button { border: 0; color: #334155; background: #f5f7fa; font-size: 1.1rem; }
  .central-ajuda-font-size output { display: grid; place-items: center; border-right: 1px solid #ced4da; border-left: 1px solid #ced4da; font-size: .9rem; font-weight: 700; }
  @media (max-width: 575.98px) { .central-ajuda-topic-nav a, .central-ajuda-topic-nav span { font-size: .75rem; } .central-ajuda-reader { min-height: 400px; } .central-ajuda-reader-meta { grid-template-columns: 1fr 1fr; gap: .55rem .75rem; } .central-ajuda-reader-tags { grid-column: 1 / -1; } .central-ajuda-reader-views { justify-self: start; } .central-ajuda-reader-updated { justify-self: end; white-space: normal; text-align: right; } }
  @media (max-width: 991.98px) { .central-ajuda-tree { position: static; border-right: 0; border-bottom: 1px solid var(--ajuda-borda); padding: 0 0 1rem; } }
</style>

<div class="container-fluid px-0 central-ajuda">
  <header class="central-ajuda-heading bg-phsa-light <?= $registro ? 'is-reader' : '' ?>">
    <?php if (!$registro): ?><h1 class="mb-1">Central de ajuda</h1><p class="text-muted mb-3">Encontre orientações para usar o ICLIV.</p><?php endif; ?>
    <form class="central-ajuda-search" method="get" action="<?= $esc(Helpers::url('ajuda')) ?>">
      <div class="input-group shadow-sm">
        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-phsa"></i></span>
        <input class="form-control" type="search" name="pesquisa" value="<?= $esc($pesquisa) ?>" placeholder="Pesquisar na central de ajuda" aria-label="Pesquisar conteúdos de ajuda">
        <button class="bto bto-phsa px-4" type="submit">Buscar</button>
      </div>
    </form>
  </header>

  <div class="row g-3 central-ajuda-layout">
    <aside class="col-12 col-lg-3 col-xl-2">
      <nav class="central-ajuda-tree" aria-label="Navegação da Central de Ajuda">
        <div class="central-ajuda-tree-title">Navegar por assunto</div>
        <a class="central-ajuda-tree-all <?= !$pesquisa && !$categoriaSelecionada ? 'active' : '' ?>" href="<?= $esc($urlAjuda(['categoria' => 0])) ?>"><i class="fa-solid fa-layer-group"></i><span>Todos os assuntos</span></a>
        <?php foreach ($categoriasPorId as $categoriaId => $categoria): ?>
          <a class="central-ajuda-tree-category <?= $categoriaSelecionada === $categoriaId ? 'active' : '' ?>" href="<?= $esc($urlAjuda(array_filter(['pesquisa' => $pesquisa ?: null, 'categoria' => $categoriaId]))) ?>"><i class="fa-solid fa-layer-group"></i><span><?= $esc($categoria->cct_nome ?? '') ?></span><span class="central-ajuda-tree-count"><?= (int) ($categoria->cct_totalconhecimentos ?? 0) ?></span></a>
        <?php endforeach; ?>
      </nav>
    </aside>

    <section class="col-12 col-lg-9 col-xl-10 central-ajuda-main">
      <?php if (!empty($conteudoNaoEncontrado)): ?>
        <div class="py-5 text-center"><i class="fa-solid fa-circle-exclamation fa-2x text-warning mb-3"></i><h2 class="h5">Conteúdo não encontrado</h2><p class="text-muted">Este conteúdo não está disponível ou foi removido.</p><a class="bto bto-phsa-light" href="<?= $esc(Helpers::url('ajuda')) ?>"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar à Central de ajuda</a></div>
      <?php elseif ($registro): ?>
        <?php
          $tags = $tagsDoConhecimento($registro->con_tags ?? '[]');
          $contagemLeitura = (int) ($registro->con_visualizacoes ?? 0);
          $timestampAtualizacao = strtotime((string) ($registro->con_dtalter ?? ''));
          $dataAtualizacao = $timestampAtualizacao ? date('d/m/Y H:i', $timestampAtualizacao) : '—';
          $categoriaItemId = (int) ($registro->con_idcategoria ?? 0);
          $urlCategoria = $urlAjuda(['categoria' => $categoriaItemId]);
          $urlAnterior = $topicoAnterior ? Helpers::url('ajuda/' . (int) $topicoAnterior->con_id . '?categoria=' . (int) $topicoAnterior->con_idcategoria) : '';
          $urlProximo = $proximoTopico ? Helpers::url('ajuda/' . (int) $proximoTopico->con_id . '?categoria=' . (int) $proximoTopico->con_idcategoria) : '';
        ?>
        <?php if ($topicoAnterior || $proximoTopico): ?><nav class="central-ajuda-topic-nav" aria-label="Navegação entre conhecimentos">
          <?php if ($topicoAnterior): ?><a href="<?= $esc($urlAnterior) ?>" title="<?= $esc('Ler tópico anterior: ' . $topicoAnterior->con_titulo) ?>"><i class="fa-solid fa-arrow-left"></i><span>Ler tópico anterior</span></a><?php endif; ?>
          <?php if ($proximoTopico): ?><a href="<?= $esc($urlProximo) ?>" title="<?= $esc('Ler próximo tópico: ' . $proximoTopico->con_titulo) ?>"><span>Ler próximo tópico</span><i class="fa-solid fa-arrow-right"></i></a><?php endif; ?>
        </nav><?php endif; ?>

        <article class="central-ajuda-reader" id="centralAjudaReader" data-help-id="<?= (int) $registro->con_id ?>" data-count-url="<?= $esc(Helpers::url('ajuda/registrarLeitura')) ?>" data-theme="light" data-reading-mode="standard">
          <div class="central-ajuda-reader-toolbar">
            <span class="central-ajuda-reader-toolbar-label"><i class="fa-solid fa-book-open me-1"></i><a href="<?= $esc($urlCategoria) ?>"><?= $esc($registro->con_categoria_nome ?? '') ?></a></span>
            <button type="button" class="central-ajuda-tool" data-help-toggle-theme aria-label="Alternar tema claro/escuro" title="Alternar tema"><i class="fa-solid fa-circle-half-stroke"></i></button>
            <button type="button" class="central-ajuda-tool" data-help-toggle-mode aria-label="Ativar modo de leitura" title="Alternar modo de leitura"><i class="fa-solid fa-glasses"></i></button>
            <button type="button" class="central-ajuda-tool" data-help-toggle-expand aria-label="Maximizar área de leitura" title="Maximizar leitura"><i class="fa-solid fa-up-right-and-down-left-from-center"></i></button>
            <button type="button" class="central-ajuda-tool" data-help-open-settings aria-label="Configurações de leitura" title="Configurações"><i class="fa-solid fa-gear"></i></button>
          </div>
          <div class="central-ajuda-reader-scroll">
            <header class="central-ajuda-reader-head">
              <h1 class="central-ajuda-reader-title"><?= $esc($registro->con_titulo ?? 'Tópico') ?></h1>
              <?php if (trim((string) ($registro->con_descricao_resumida ?? '')) !== ''): ?><p class="central-ajuda-reader-summary"><?= $esc($registro->con_descricao_resumida) ?></p><?php endif; ?>
            </header>
            <?php if (trim((string) ($registro->con_conteudo ?? '')) !== ''): ?><div class="central-ajuda-reader-content"><?= $conteudoSeguro((string) $registro->con_conteudo) ?></div><?php endif; ?>
            <?php if ($midias): ?><section class="central-ajuda-reader-media"><h2><i class="fa-solid fa-photo-film text-phsa me-1"></i>Vídeos e materiais</h2><?php foreach ($midias as $midia): ?><div class="central-ajuda-reader-media-item"><h3><?= $esc($midia->cmi_nome ?? 'Mídia') ?></h3><?= $embedDaMidia($midia) ?></div><?php endforeach; ?></section><?php endif; ?>
          </div>
          <footer class="central-ajuda-reader-meta">
            <span class="central-ajuda-reader-tags"><?php foreach ($tags as $indice => $tag): ?><?= $indice > 0 ? ' · ' : '' ?><a href="<?= $esc($urlAjuda(['pesquisa' => $tag])) ?>" data-help-search-tag="<?= $esc($tag) ?>">#<?= $esc($tag) ?></a><?php endforeach; ?></span>
            <span class="central-ajuda-reader-meta-item central-ajuda-reader-views" aria-live="polite"><i class="fa-regular fa-eye"></i><strong data-help-read-count><?= number_format($contagemLeitura, 0, ',', '.') ?></strong><span data-help-read-label><?= $contagemLeitura === 1 ? 'leitura' : 'leituras' ?></span></span>
            <span class="central-ajuda-reader-meta-item central-ajuda-reader-updated"><i class="fa-regular fa-clock"></i><span>Atualizado em: <?= $esc($dataAtualizacao) ?></span></span>
          </footer>
        </article>

        <?php if ($topicoAnterior || $proximoTopico): ?><nav class="central-ajuda-topic-nav mt-3" aria-label="Navegação entre conhecimentos, final do artigo">
          <?php if ($topicoAnterior): ?><a href="<?= $esc($urlAnterior) ?>" title="<?= $esc('Ler tópico anterior: ' . $topicoAnterior->con_titulo) ?>"><i class="fa-solid fa-arrow-left"></i><span>Ler tópico anterior</span></a><?php endif; ?>
          <?php if ($proximoTopico): ?><a href="<?= $esc($urlProximo) ?>" title="<?= $esc('Ler próximo tópico: ' . $proximoTopico->con_titulo) ?>"><span>Ler próximo tópico</span><i class="fa-solid fa-arrow-right"></i></a><?php endif; ?>
        </nav><?php endif; ?>

        <div class="central-ajuda-settings-backdrop" data-help-settings-close></div>
        <aside class="central-ajuda-settings" data-help-settings role="dialog" aria-modal="true" aria-labelledby="centralAjudaSettingsTitle" aria-hidden="true">
          <div class="central-ajuda-settings-head"><h2 id="centralAjudaSettingsTitle">Configurações de leitura</h2><button type="button" class="central-ajuda-settings-close" data-help-settings-close aria-label="Fechar configurações"><i class="fa-solid fa-xmark"></i></button></div>
          <div class="central-ajuda-setting"><label>Tema</label><button type="button" class="bto bto-phsa-light w-100" data-help-toggle-theme><i class="fa-solid fa-circle-half-stroke me-1"></i>Alternar claro/escuro</button></div>
          <div class="central-ajuda-setting"><label for="centralAjudaVisualMode">Modo visual</label><select id="centralAjudaVisualMode" class="form-select" data-help-visual-mode><option value="standard">Padrão</option><option value="paper-light">Papel claro</option><option value="sepia">Sépia</option><option value="soft-yellow">Amarelo suave</option><option value="rest-green">Verde descanso</option><option value="rest-blue">Azul descanso</option><option value="soft-gray">Cinza suave</option></select></div>
          <div class="central-ajuda-setting"><label for="centralAjudaFontFamily">Fonte</label><select id="centralAjudaFontFamily" class="form-select" data-help-font-family><option value="system">Padrão do sistema</option><option value="serif">Serifada</option><option value="georgia">Georgia</option><option value="arial">Arial</option></select></div>
          <div class="central-ajuda-setting"><label>Tamanho da fonte</label><div class="central-ajuda-font-size"><button type="button" data-help-font-down aria-label="Diminuir fonte">A−</button><output data-help-font-value>17px</output><button type="button" data-help-font-up aria-label="Aumentar fonte">A+</button></div></div>
        </aside>
      <?php elseif ($pesquisa !== '' || $categoriaSelecionada > 0): ?>
        <?php $categoriaNomeSelecionada = ''; foreach ($categorias as $categoria) if ((int) $categoria->cct_id === $categoriaSelecionada) { $categoriaNomeSelecionada = (string) $categoria->cct_nome; break; } ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h2 class="central-ajuda-section-title mb-0"><?= $pesquisa !== '' ? 'Resultados da pesquisa' : $esc($categoriaNomeSelecionada ?: 'Assuntos selecionados') ?></h2><span class="small text-muted"><?= count($conteudos) ?> <?= count($conteudos) === 1 ? 'guia' : 'guias' ?></span></div>
        <?php if (!empty($erroConsulta)): ?><div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;Não foi possível carregar os conteúdos agora. Tente novamente em instantes.</div>
        <?php elseif (!$conteudos): ?><div class="py-5 text-center"><i class="fa-solid fa-magnifying-glass fa-2x text-muted mb-3"></i><h3 class="h5">Nenhum guia encontrado</h3><p class="text-muted mb-0"><?= $pesquisa !== '' ? 'Tente outros termos ou navegue pelos assuntos ao lado.' : 'Ainda não há conteúdo publicado nesta área.' ?></p></div>
        <?php else: ?><div class="central-ajuda-results"><?php foreach ($conteudos as $item): $tags = $tagsDoConhecimento($item->con_tags ?? '[]'); ?>
          <a class="central-ajuda-result" href="<?= $esc(Helpers::url('ajuda/' . (int) $item->con_id . '?' . http_build_query(array_filter(['pesquisa' => $pesquisa ?: null, 'categoria' => $categoriaSelecionada ?: null])))) ?>">
            <span class="central-ajuda-result-icon"><i class="fa-regular fa-file-lines"></i></span><span class="flex-grow-1 min-w-0"><span class="central-ajuda-result-title d-block"><?= $esc($item->con_titulo ?? 'Orientação') ?></span><?php if (!empty($item->con_descricao_resumida)): ?><span class="d-block small text-muted mt-1"><?= $esc($item->con_descricao_resumida) ?></span><?php endif; ?><?php if ($tags): ?><span class="d-block small text-phsa mt-1" aria-label="Tags"><?php foreach ($tags as $indice => $tag): ?><?= $indice > 0 ? ' · ' : '' ?>#<?= $esc($tag) ?><?php endforeach; ?></span><?php endif; ?></span><i class="fa-solid fa-chevron-right central-ajuda-result-arrow small"></i>
          </a>
        <?php endforeach; ?></div><?php endif; ?>
      <?php elseif (!empty($erroConsulta)): ?>
        <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;Não foi possível carregar os conteúdos agora. Tente novamente em instantes.</div>
      <?php else: ?>
        <div class="py-5 text-center text-muted"><i class="fa-regular fa-compass fa-2x mb-3 text-phsa"></i><p class="mb-1 fw-semibold text-body">Escolha um assunto no menu ao lado</p><p class="small mb-0">Ou pesquise acima para encontrar um guia.</p></div>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php if ($registro): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const $ = window.jQuery;
    if (!$) return;

    const reader = document.getElementById('centralAjudaReader');
    if (!reader) return;

    const $reader = $(reader);
    const $settings = $('[data-help-settings]');
    const $settingsBackdrop = $('.central-ajuda-settings-backdrop');
    const preferenceKey = 'iclivCentralAjudaPreferencias';
    const historyKey = 'iclivCentralAjudaLeituras';
    const readingModes = ['standard', 'paper-light', 'sepia', 'soft-yellow', 'rest-green', 'rest-blue', 'soft-gray'];
    const fontFamilies = {
      system: 'system-ui, -apple-system, "Segoe UI", sans-serif',
      serif: 'serif',
      georgia: 'Georgia, "Times New Roman", serif',
      arial: 'Arial, Helvetica, sans-serif'
    };
    const preferences = { theme: 'light', mode: 'standard', font: 'system', size: 17 };

    $('[data-help-search-tag]').on('click', function (event) {
      const form = document.querySelector('.central-ajuda-search');
      const input = form && form.querySelector('[name="pesquisa"]');
      if (!form || !input) return;
      event.preventDefault();
      input.value = $(this).attr('data-help-search-tag');
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.submit();
    });

    try {
      Object.assign(preferences, JSON.parse(localStorage.getItem(preferenceKey) || '{}'));
    } catch (error) {}

    function savePreferences() {
      try { localStorage.setItem(preferenceKey, JSON.stringify(preferences)); } catch (error) {}
    }

    function applyPreferences() {
      preferences.theme = preferences.theme === 'dark' ? 'dark' : 'light';
      preferences.mode = readingModes.includes(preferences.mode) ? preferences.mode : 'standard';
      preferences.font = Object.prototype.hasOwnProperty.call(fontFamilies, preferences.font) ? preferences.font : 'system';
      preferences.size = Math.min(28, Math.max(14, parseInt(preferences.size, 10) || 17));
      $reader.attr('data-theme', preferences.theme).attr('data-reading-mode', preferences.mode);
      $reader.toggleClass('is-reading-mode', preferences.mode !== 'standard');
      reader.style.setProperty('--reader-font-family', fontFamilies[preferences.font]);
      reader.style.setProperty('--reader-font-size', preferences.size + 'px');
      $('[data-help-font-value]').text(preferences.size + 'px');
      $('[data-help-font-family]').val(preferences.font);
      $('[data-help-visual-mode]').val(preferences.mode);
      $('[data-help-toggle-mode]')
        .toggleClass('is-active', preferences.mode !== 'standard')
        .attr('aria-label', preferences.mode === 'standard' ? 'Ativar modo de leitura' : 'Desativar modo de leitura');
      $('[data-help-toggle-theme]').toggleClass('is-active', preferences.theme === 'dark');
    }

    function toggleTheme() {
      preferences.theme = preferences.theme === 'dark' ? 'light' : 'dark';
      applyPreferences();
      savePreferences();
    }

    function setReadingMode(mode) {
      preferences.mode = readingModes.includes(mode) ? mode : 'standard';
      applyPreferences();
      savePreferences();
    }

    function closeSettings() {
      $settings.removeClass('is-open').attr('aria-hidden', 'true');
      $settingsBackdrop.removeClass('is-open');
    }

    applyPreferences();
    $('[data-help-toggle-theme]').on('click', toggleTheme);
    $('[data-help-toggle-mode]').on('click', function () {
      setReadingMode(preferences.mode === 'standard' ? 'sepia' : 'standard');
    });
    $('[data-help-visual-mode]').on('change', function () { setReadingMode($(this).val()); });
    $('[data-help-font-family]').on('change', function () {
      preferences.font = $(this).val();
      applyPreferences();
      savePreferences();
    });
    $('[data-help-font-down]').on('click', function () {
      preferences.size = Math.max(14, preferences.size - 1);
      applyPreferences();
      savePreferences();
    });
    $('[data-help-font-up]').on('click', function () {
      preferences.size = Math.min(28, preferences.size + 1);
      applyPreferences();
      savePreferences();
    });

    $('[data-help-open-settings]').on('click', function () {
      $settings.addClass('is-open').attr('aria-hidden', 'false');
      $settingsBackdrop.addClass('is-open');
      $settings.find('select').first().trigger('focus');
    });
    $('[data-help-settings-close]').on('click', closeSettings);

    $('[data-help-toggle-expand]').on('click', function () {
      const expanded = !$reader.hasClass('central-ajuda-reader-expanded');
      $reader.toggleClass('central-ajuda-reader-expanded', expanded);
      $('body').toggleClass('central-ajuda-reader-open', expanded);
      $(this).attr('aria-label', expanded ? 'Minimizar área de leitura' : 'Maximizar área de leitura')
        .attr('title', expanded ? 'Minimizar leitura' : 'Maximizar leitura')
        .find('i').toggleClass('fa-up-right-and-down-left-from-center', !expanded)
        .toggleClass('fa-down-left-and-up-right-to-center', expanded);
    });

    $(document).on('keydown.centralAjudaReader', function (event) {
      if (event.key === 'Escape') {
        closeSettings();
        if ($reader.hasClass('central-ajuda-reader-expanded')) {
          $reader.removeClass('central-ajuda-reader-expanded');
          $('body').removeClass('central-ajuda-reader-open');
          $('[data-help-toggle-expand]').attr('aria-label', 'Maximizar área de leitura')
            .attr('title', 'Maximizar leitura').find('i')
            .removeClass('fa-down-left-and-up-right-to-center')
            .addClass('fa-up-right-and-down-left-from-center');
        }
      }
    });

    const conhecimentoId = parseInt($reader.attr('data-help-id'), 10);
    const cooldown = 30 * 60 * 1000;
    let historico = {};
    try {
      const salvo = JSON.parse(localStorage.getItem(historyKey) || '{}');
      if (salvo && typeof salvo === 'object' && !Array.isArray(salvo)) historico = salvo;
    } catch (error) {}

    const agora = Date.now();
    const ultimaLocal = Number(historico[conhecimentoId] || 0);
    if (conhecimentoId > 0 && !(ultimaLocal > 0 && agora - ultimaLocal < cooldown)) {
      $.ajax({
        url: $reader.attr('data-count-url'),
        method: 'POST',
        dataType: 'json',
        data: { id: conhecimentoId }
      }).done(function (resposta) {
        if (!resposta || !resposta.status || !resposta.data) return;
        const contagem = Number(resposta.data.visualizacoes || 0);
        $('[data-help-read-count]').text(new Intl.NumberFormat('pt-BR').format(contagem));
        $('[data-help-read-label]').text(contagem === 1 ? 'leitura' : 'leituras');
        const ultimaServidor = Number(resposta.data.ultimaLeitura || 0) * 1000;
        historico[conhecimentoId] = resposta.data.contada ? Date.now() : (ultimaServidor || Date.now());
        try { localStorage.setItem(historyKey, JSON.stringify(historico)); } catch (error) {}
      });
    }
  });
</script>
<?php endif; ?>


