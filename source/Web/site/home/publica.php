<?php
  $this->layout('site::site');

  $esc = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $empresa = is_object($empresaPublica ?? null) ? $empresaPublica : null;
  $paletaPadraoEmpresa = \Source\Service\PaletaCoresService::gerar('#2d8a68');
  $paletaEmpresa = $paletaPadraoEmpresa;
  $paletaInformada = $empresa->emp_paletacores ?? null;
  if (is_string($paletaInformada)) {
    $paletaInformada = json_decode($paletaInformada, true);
  } elseif (is_object($paletaInformada)) {
    $paletaInformada = (array) $paletaInformada;
  }
  if (is_array($paletaInformada)) {
    foreach ($paletaPadraoEmpresa as $chave => $corPadrao) {
      $cor = $paletaInformada[$chave] ?? null;
      if (is_string($cor) && preg_match('/^#[a-f0-9]{6}$/i', trim($cor))) {
        $paletaEmpresa[$chave] = strtolower(trim($cor));
      }
    }
  }
  $nomeEmpresa = $empresa
    ? (trim((string) ($empresa->emp_nomefantasia ?? '')) ?: trim((string) ($empresa->emp_razaosocial ?? 'Empresa')))
    : '';
  $logoEmpresa = trim((string) ($empresa->emp_logo ?? ''));
  $conteudoEmpresa = trim((string) ($empresa->emp_webapp_conteudo ?? ''));
  if ($conteudoEmpresa === '' && trim((string) ($empresa->emp_descricao ?? '')) !== '') {
    $conteudoEmpresa = '<p>' . $esc($empresa->emp_descricao) . '</p>';
  }
  if ($conteudoEmpresa === '' && $empresa) {
    $conteudoEmpresa = '<p>Bem-vindo à página oficial de ' . $esc($nomeEmpresa) . '.</p>';
  }

  $sanitizarConteudo = static function (string $html): string {
    if (!class_exists(DOMDocument::class)) {
      return nl2br(htmlspecialchars(trim(strip_tags($html)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    $documento = new DOMDocument('1.0', 'UTF-8');
    $errosLibxml = libxml_use_internal_errors(true);
    $documento->loadHTML('<?xml encoding="UTF-8"><div id="conteudo-publico">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($errosLibxml);

    $raiz = $documento->getElementById('conteudo-publico');
    if (!$raiz) {
      return '';
    }

    $permitidas = ['section', 'div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'br', 'hr', 'a', 'img'];
    $perigosas = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math'];
    $limparNos = static function (DOMNode $pai) use (&$limparNos, $permitidas, $perigosas): void {
      foreach (iterator_to_array($pai->childNodes) as $no) {
        if (!$no instanceof DOMElement) {
          continue;
        }

        $tag = strtolower($no->tagName);
        if (in_array($tag, $perigosas, true)) {
          $pai->removeChild($no);
          continue;
        }

        if (!in_array($tag, $permitidas, true)) {
          while ($no->firstChild) {
            $pai->insertBefore($no->firstChild, $no);
          }
          $pai->removeChild($no);
          continue;
        }

        $atributosPermitidos = match ($tag) {
          'a' => ['href', 'title', 'target', 'rel'],
          'img' => ['src', 'alt', 'title', 'width', 'height'],
          default => []
        };
        foreach (iterator_to_array($no->attributes) as $atributo) {
          if (!in_array(strtolower($atributo->name), $atributosPermitidos, true)) {
            $no->removeAttribute($atributo->name);
          }
        }

        $urlAttr = $tag === 'a' ? 'href' : ($tag === 'img' ? 'src' : null);
        if ($urlAttr && $no->hasAttribute($urlAttr)) {
          $url = trim($no->getAttribute($urlAttr));
          $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));
          $urlRelativaSegura = $url !== '' && !str_starts_with($url, '//') && !preg_match('/[\x00-\x20]/', $url);
          $esquemaPermitido = $esquema === '' || in_array($esquema, $tag === 'a' ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'], true);
          if (!$urlRelativaSegura || !$esquemaPermitido) {
            $no->removeAttribute($urlAttr);
          }
        }

        if ($tag === 'a' && strtolower($no->getAttribute('target')) === '_blank') {
          $no->setAttribute('rel', 'noopener noreferrer');
        } elseif ($tag === 'a' && $no->hasAttribute('target')) {
          $no->removeAttribute('target');
        }

        $limparNos($no);
      }
    };
    $limparNos($raiz);

    $conteudo = '';
    foreach ($raiz->childNodes as $no) {
      $conteudo .= $documento->saveHTML($no);
    }
    return $conteudo;
  };

  $conteudoSeguro = $empresa ? $sanitizarConteudo($conteudoEmpresa) : '';
  $urlLogo = $logoEmpresa !== '' ? Helpers::storage($logoEmpresa) : '';
  $slugEmpresa = trim((string) ($empresa->emp_urlamigavel ?? ''));
  $clienteLogado = is_object($clienteLogado ?? null) ? $clienteLogado : null;
  $endereco = is_object($enderecoPublico ?? null) ? $enderecoPublico : null;
  $pwa = is_object($pwaPublico ?? null) ? $pwaPublico : null;
  $linhasEndereco = [];
  if ($endereco) {
    $linhaLogradouro = trim(implode(', ', array_filter([
      trim((string) ($endereco->end_logradouro ?? '')),
      trim((string) ($endereco->end_numero ?? ''))
    ], static fn($parte) => $parte !== '')));
    $complemento = trim((string) ($endereco->end_complemento ?? ''));
    $bairro = trim((string) ($endereco->end_bairro ?? ''));
    $cidadeUf = trim(implode(' / ', array_filter([
      trim((string) ($endereco->end_cidade ?? '')),
      trim((string) ($endereco->end_uf ?? ''))
    ], static fn($parte) => $parte !== '')));
    $cep = trim((string) ($endereco->end_cep ?? ''));
    $linhasEndereco = array_values(array_filter([
      $linhaLogradouro,
      $complemento,
      $bairro,
      $cidadeUf,
      $cep !== '' ? 'CEP ' . $cep : ''
    ], static fn($parte) => $parte !== ''));
  }
  $urlMapa = '';
  if ($endereco) {
    $urlMapaInformada = trim((string) ($endereco->end_googlemaps ?? ''));
    $esquemaMapa = strtolower((string) parse_url($urlMapaInformada, PHP_URL_SCHEME));
    if (filter_var($urlMapaInformada, FILTER_VALIDATE_URL) && in_array($esquemaMapa, ['http', 'https'], true)) {
      $urlMapa = $urlMapaInformada;
    } elseif ($linhasEndereco) {
      $urlMapa = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(implode(', ', $linhasEndereco));
    }
  }
?>

<?php $this->push('style'); ?>
<style>
  body:has(.ic-company-page) {
    --company-primary:<?= $esc($paletaEmpresa['primaryBg']) ?>;
    --company-primary-dark:<?= $esc($paletaEmpresa['strongBg']) ?>;
    --company-primary-hover:<?= $esc($paletaEmpresa['primaryHoverBg']) ?>;
    --company-primary-text:<?= $esc($paletaEmpresa['primaryText']) ?>;
    --company-primary-soft:<?= $esc($paletaEmpresa['softBg']) ?>;
    --company-primary-soft-hover:<?= $esc($paletaEmpresa['softHoverBg']) ?>;
    --company-ink:#1d302c; --company-muted:#697d77; --company-border:#dce8e3;
    --ic-green:var(--company-primary); --ic-green-dark:var(--company-primary-dark);
    --ic-mint:var(--company-primary-soft); --ic-mint-light:var(--company-primary-soft-hover);
    --phsa-soft-bg:<?= $esc($paletaEmpresa['softBg']) ?>;
    --phsa-soft-text:<?= $esc($paletaEmpresa['softText']) ?>;
    --phsa-soft-hover-bg:<?= $esc($paletaEmpresa['softHoverBg']) ?>;
    --phsa-soft-hover-text:<?= $esc($paletaEmpresa['softHoverText']) ?>;
    --phsa-primary-bg:<?= $esc($paletaEmpresa['primaryBg']) ?>;
    --phsa-primary-text:<?= $esc($paletaEmpresa['primaryText']) ?>;
    --phsa-primary-hover-bg:<?= $esc($paletaEmpresa['primaryHoverBg']) ?>;
    --phsa-primary-hover-text:<?= $esc($paletaEmpresa['primaryHoverText']) ?>;
    --phsa-strong-bg:<?= $esc($paletaEmpresa['strongBg']) ?>;
    --phsa-strong-text:<?= $esc($paletaEmpresa['strongText']) ?>;
    --phsa-strong-hover-bg:<?= $esc($paletaEmpresa['strongHoverBg']) ?>;
    --phsa-strong-hover-text:<?= $esc($paletaEmpresa['strongHoverText']) ?>;
    --phsa-sidebar-text:<?= $esc($paletaEmpresa['sidebarText']) ?>;
    --phsa-sidebar-active-bg:<?= $esc($paletaEmpresa['sidebarActiveBg']) ?>;
    --phsa-sidebar-active-text:<?= $esc($paletaEmpresa['sidebarActiveText']) ?>;
    --phsa-sidebar-hover-bg:<?= $esc($paletaEmpresa['sidebarHoverBg']) ?>;
    --phsa-sidebar-hover-text:<?= $esc($paletaEmpresa['sidebarHoverText']) ?>;
    --phsa-sidebar-submenu-active-bg:<?= $esc($paletaEmpresa['sidebarSubmenuActiveBg']) ?>;
    --phsa-sidebar-submenu-active-text:<?= $esc($paletaEmpresa['sidebarSubmenuActiveText']) ?>;
    --phsa-sidebar-submenu-hover-bg:<?= $esc($paletaEmpresa['sidebarSubmenuHoverBg']) ?>;
    --phsa-sidebar-submenu-hover-text:<?= $esc($paletaEmpresa['sidebarSubmenuHoverText']) ?>;
    background:<?= $esc($paletaEmpresa['softHoverBg']) ?>;
  }
  body:has(.ic-company-page:not(.ic-company-page-not-found-layout)) .ic-header,
  body:has(.ic-company-page:not(.ic-company-page-not-found-layout)) #site-footer,
  body:has(.ic-company-page:not(.ic-company-page-not-found-layout)) #scrollToTop { display:none !important; }
  .ic-company-page { min-height:100vh; padding:1.5rem 0 4rem; color:var(--company-ink); background:#f4f8f6; }
  .ic-company-wrap { width:min(100% - 2rem,1120px); margin:0 auto; }
  .ic-company-brand { display:flex; align-items:center; gap:.85rem; padding:.25rem 0 1.25rem; }
  .ic-company-brand-logo { display:grid; width:54px; height:54px; flex:0 0 54px; place-items:center; overflow:hidden; border:1px solid var(--company-border); border-radius:17px; color:var(--company-primary); background:#fff; font-size:1.2rem; box-shadow:0 5px 18px rgba(23,67,53,.07); }
  .ic-company-brand-logo img { width:100%; height:100%; object-fit:cover; }
  .ic-company-brand-name { min-width:0; margin:0; font-size:1.05rem; font-weight:750; overflow-wrap:anywhere; }
  .ic-company-hero { position:relative; display:grid; grid-template-columns:1fr; gap:2rem; overflow:hidden; padding:clamp(1.5rem,5vw,4rem); border:1px solid #d7e7df; border-radius:30px; background:linear-gradient(125deg,#fff 0%,#f0f8f3 100%); box-shadow:0 18px 50px rgba(31,65,52,.07); }
  .ic-company-hero::after { position:absolute; top:-130px; right:-100px; width:350px; height:350px; border-radius:50%; background:radial-gradient(circle,rgba(85,174,132,.19),rgba(85,174,132,0)); content:""; pointer-events:none; }
  .ic-company-eyebrow { display:inline-flex; align-items:center; gap:.5rem; margin-bottom:1rem; color:var(--company-primary); font-size:.77rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
  .ic-company-hero h1 { max-width:720px; margin:0 0 1rem; color:#18372e; font-size:clamp(2.25rem,6vw,4.4rem); font-weight:800; letter-spacing:-.055em; line-height:1.04; overflow-wrap:anywhere; }
  .ic-company-hero p { max-width:650px; margin:0; color:#61736c; font-size:1.08rem; line-height:1.75; }
  .ic-company-hero-art { position:relative; z-index:1; display:grid; min-height:185px; place-items:center; border:1px solid rgba(33,107,87,.11); border-radius:24px; background:linear-gradient(145deg,rgba(255,255,255,.95),rgba(224,242,231,.85)); color:var(--company-primary); }
  .ic-company-hero-art i { font-size:clamp(3rem,10vw,5.2rem); filter:drop-shadow(0 14px 20px rgba(33,107,87,.17)); }
  .ic-company-actions { display:flex; flex-wrap:wrap; gap:.7rem; margin-top:1.6rem; }
  .ic-company-actions .ic-btn { min-height:46px; }
  .ic-company-section { padding-top:clamp(3rem,7vw,5.5rem); }
  .ic-company-section-head { max-width:720px; margin-bottom:1.5rem; }
  .ic-company-kicker { display:block; margin-bottom:.55rem; color:var(--company-primary); font-size:.76rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
  .ic-company-section-head h2 { margin:0 0 .65rem; color:#1c392f; font-size:clamp(1.65rem,4vw,2.5rem); font-weight:800; letter-spacing:-.04em; }
  .ic-company-section-head p { margin:0; color:var(--company-muted); line-height:1.7; }
  .ic-company-card { height:100%; padding:clamp(1.25rem,3vw,2rem); border:1px solid var(--company-border); border-radius:22px; background:#fff; box-shadow:0 8px 26px rgba(31,65,52,.045); }
  .ic-company-about { font-size:1.02rem; line-height:1.85; overflow-wrap:anywhere; }
  .ic-company-about > :first-child { margin-top:0; }
  .ic-company-about > :last-child { margin-bottom:0; }
  .ic-company-about img { max-width:100%; height:auto; border-radius:14px; }
  .ic-company-about a { color:var(--company-primary); }
  .ic-company-catalog-card { display:flex; flex-direction:column; gap:1rem; }
  .ic-company-catalog-icon,.ic-company-demo-icon { display:grid; width:54px; height:54px; flex:0 0 54px; place-items:center; border-radius:17px; color:var(--company-primary); background:#eaf5ee; font-size:1.35rem; }
  .ic-company-catalog-card h3,.ic-company-demo-card h3 { margin:0 0 .45rem; color:#233c33; font-size:1.18rem; font-weight:750; }
  .ic-company-catalog-card p,.ic-company-demo-card p { margin:0; color:var(--company-muted); line-height:1.65; }
  .ic-company-catalog-note { margin-top:auto; padding-top:.25rem; color:#88958f; font-size:.84rem; }
  .ic-company-feature { display:flex; min-height:100%; flex-direction:column; justify-content:space-between; gap:1.5rem; padding:clamp(1.5rem,4vw,2.5rem); border-radius:24px; color:#fff; background:linear-gradient(135deg,#1d6b53,#124838); box-shadow:0 18px 40px rgba(20,72,56,.18); }
  .ic-company-feature .ic-company-kicker,.ic-company-feature h2,.ic-company-feature p { color:#fff; }
  .ic-company-feature p { max-width:650px; opacity:.82; line-height:1.75; }
  .ic-company-feature .ic-company-demo-icon { color:#fff; background:rgba(255,255,255,.14); }
  .ic-company-install-disabled { display:inline-flex; min-height:46px; align-items:center; padding:.65rem 1rem; border:1px solid rgba(255,255,255,.38); border-radius:999px; color:#fff; background:rgba(255,255,255,.12); font-weight:700; }
  .ic-company-blog-art { display:grid; min-height:155px; place-items:center; margin:-.35rem -.35rem .2rem; border-radius:16px; color:#276a56; background:linear-gradient(140deg,#edf7f0,#d7eee0); font-size:2.5rem; }
  .ic-company-blog-card small { color:var(--company-primary); font-weight:750; letter-spacing:.08em; text-transform:uppercase; }
  .ic-company-address { display:flex; flex-direction:column; gap:1rem; }
  .ic-company-address-lines { color:#53675f; line-height:1.8; }
  .ic-company-address-lines span { display:block; }
  .ic-company-address-map { min-height:190px; display:grid; place-items:center; overflow:hidden; border-radius:17px; color:#2b6a55; background:radial-gradient(circle at 25% 35%,#fff 0 5px,transparent 6px),radial-gradient(circle at 70% 60%,#fff 0 4px,transparent 5px),linear-gradient(135deg,#e1f1e6,#cbe7d7); background-size:100% 100%; font-size:2.4rem; }
  .ic-company-address-map i { filter:drop-shadow(0 6px 8px rgba(32,89,67,.18)); }
  .ic-company-page-not-found-layout { min-height:62vh; padding:9.5rem 0 5rem; background:radial-gradient(circle at 12% 30%,color-mix(in srgb,var(--company-primary) 12%,transparent),transparent 24rem),linear-gradient(180deg,var(--company-primary-soft-hover) 0%,#fff 72%); }
  .ic-company-page-not-found { width:min(100% - 1.25rem,720px); margin:0 auto; }
  .ic-company-page-not-found-card { padding:clamp(2rem,5vw,4rem); border:1px solid var(--company-border); border-radius:2rem; background:rgba(255,255,255,.9); text-align:center; box-shadow:0 18px 50px rgba(31,48,72,.1); }
  .ic-company-page-not-found-icon { display:inline-grid; width:5rem; height:5rem; place-items:center; margin-bottom:1.5rem; border-radius:1.6rem; color:var(--company-primary); background:var(--company-primary-soft); font-size:2rem; }
  .ic-company-page-not-found-kicker { display:block; margin-bottom:.75rem; color:var(--company-primary); font-size:.74rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
  .ic-company-page-not-found-card h1 { margin-bottom:1rem; color:#182a49; font-size:clamp(2rem,5vw,3.5rem); letter-spacing:-.05em; }
  .ic-company-page-not-found-card p { max-width:34rem; margin:0 auto; color:#718096; font-size:1.05rem; line-height:1.7; }
  .ic-company-page-not-found-actions { display:flex; justify-content:center; flex-wrap:wrap; gap:.75rem; margin-top:2rem; }
  .ic-company-page-not-found-actions .ic-btn { min-height:46px; }
  @media (min-width:800px) { .ic-company-hero { grid-template-columns:minmax(0,1.5fr) minmax(250px,.8fr); align-items:center; }.ic-company-hero-art { min-height:250px; } }
  @media (min-width:640px) { .ic-company-page-not-found-layout { padding-top:9.5rem; } }
  @media (max-width:575.98px) { .ic-company-page-not-found-layout { padding-top:7.5rem; }.ic-company-page-not-found-card { border-radius:1.4rem; }.ic-company-page-not-found-actions .ic-btn { width:100%; } }
  body:has(.ic-company-page:not(.ic-company-page-not-found-layout)) { --company-ink:#172b4d; --company-muted:#66758d; --company-border:#dfe6f1; }
  body:has(.ic-company-page:not(.ic-company-page-not-found-layout)) { padding-bottom:104px; }
  body:has(.ic-company-page:not(.ic-company-page-not-found-layout)) .site-page { color:var(--company-ink); }
  .ic-company-page:not(.ic-company-page-not-found-layout) { padding:0 0 1.5rem; background:var(--company-primary-soft-hover); }
  .ic-public-header { position:sticky; z-index:1020; top:0; height:74px; border-bottom:1px solid rgba(220,228,239,.9); background:rgba(255,255,255,.93); backdrop-filter:blur(18px); }
  .ic-public-header-inner { width:min(100% - 2rem,1120px); height:100%; margin:auto; display:grid; grid-template-columns:1fr auto 1fr; align-items:center; }
  .ic-public-menu-trigger,.ic-public-account-trigger { display:inline-flex; min-height:42px; align-items:center; justify-content:center; gap:.55rem; padding:.55rem .95rem; border:1px solid var(--company-border); border-radius:999px; color:var(--company-ink); background:#fff; font-weight:700; }
  .ic-public-menu-trigger { justify-self:start; }
  .ic-public-account-trigger { justify-self:end; max-width:210px; }
  .ic-public-account-trigger span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .ic-public-brand { display:flex; align-items:center; justify-content:center; color:var(--company-ink); text-decoration:none; }
  .ic-public-brand-logo { display:block; width:42px; height:42px; object-fit:contain; }
  .ic-public-project-link { display:flex; width:100%; align-items:center; justify-content:flex-end; color:var(--company-ink); text-decoration:none; }
  .ic-public-project-link img { display:block; width:34px; height:34px; object-fit:contain; }
  .ic-company-wrap { width:min(100% - 2rem,1120px); margin:0 auto; }
  .ic-company-section { padding-top:2rem; scroll-margin-top:90px; }
  .ic-about-section { padding-top:1.4rem; }
  .ic-company-card { padding:clamp(1.1rem,3vw,1.8rem); border:1px solid var(--company-border); border-radius:22px; background:#fff; box-shadow:0 10px 30px rgba(31,48,72,.055); }
  .ic-company-about { font-size:1rem; line-height:1.75; }
  .ic-about-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:.8rem; }
  .ic-about-heading h1 { margin:0; font-size:clamp(1.35rem,3.5vw,1.9rem); letter-spacing:-.035em; }
  .ic-about-logo { width:56px; height:56px; flex:0 0 56px; display:grid; place-items:center; overflow:hidden; border:1px solid var(--company-border); border-radius:16px; color:var(--company-primary); background:#f8faff; }
  .ic-about-logo img { width:100%; height:100%; object-fit:cover; }
  .ic-company-rich-content > :first-child { margin-top:0; }.ic-company-rich-content > :last-child { margin-bottom:0; }
  .ic-company-rich-content img { max-width:100%; height:auto; border-radius:12px; }
  .ic-company-rich-content a { color:var(--company-primary); }
  .ic-company-cta-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; padding-top:1rem; }
  .ic-company-cta { min-height:88px; display:flex; align-items:center; gap:.9rem; padding:1.15rem 1.25rem; border:1px solid var(--company-border); border-radius:18px; color:var(--company-ink); background:#fff; box-shadow:0 8px 24px rgba(31,48,72,.045); font-weight:750; text-decoration:none; transition:transform .18s ease, box-shadow .18s ease; }
  .ic-company-cta:hover { transform:translateY(-2px); color:var(--company-primary); box-shadow:0 14px 30px rgba(31,48,72,.1); }
  .ic-company-cta > i:first-child { display:grid; width:48px; height:48px; flex:0 0 48px; place-items:center; border-radius:15px; color:var(--company-primary); background:var(--company-primary-soft); font-size:1.15rem; }
  .ic-company-cta > i:last-child { margin-left:auto; }
  .ic-company-cta-primary { color:#fff; border-color:var(--company-primary); background:var(--company-primary); }
  .ic-company-cta-primary:hover { color:#fff; background:var(--company-primary-dark); }
  .ic-company-cta-primary > i:first-child { color:#fff; background:rgba(255,255,255,.16); }
  .ic-company-section-head { display:flex; align-items:end; justify-content:space-between; gap:1rem; max-width:none; margin-bottom:1rem; }
  .ic-company-section-head h2 { margin:0; color:var(--company-ink); font-size:clamp(1.4rem,3.6vw,2rem); letter-spacing:-.035em; }
  .ic-company-section-head p { margin:.4rem 0 0; color:var(--company-muted); }
  .ic-company-kicker { color:var(--company-primary); font-size:.72rem; letter-spacing:.11em; }
  .ic-company-text-link { flex:0 0 auto; color:var(--company-primary); font-weight:700; text-decoration:none; }
  .ic-company-empty { min-height:130px; display:flex; align-items:center; justify-content:center; gap:.8rem; padding:1.5rem; border:1px dashed #ccd7e8; border-radius:18px; color:var(--company-muted); background:#fff; text-align:center; }
  .ic-product-carousel,.ic-news-carousel { position:relative; overflow:hidden; }
  .ic-product-track,.ic-news-track { display:flex; gap:1rem; overflow-x:auto; padding:3px 2px 14px; scroll-snap-type:x mandatory; scrollbar-width:none; scroll-behavior:smooth; }
  .ic-product-track::-webkit-scrollbar,.ic-news-track::-webkit-scrollbar { display:none; }
  .ic-product-card { flex:0 0 clamp(220px,28%,300px); overflow:hidden; border:1px solid var(--company-border); border-radius:19px; background:#fff; box-shadow:0 8px 22px rgba(31,48,72,.05); scroll-snap-align:start; }
  .ic-product-image { position:relative; height:185px; display:grid; place-items:center; overflow:hidden; color:var(--company-primary); background:linear-gradient(140deg,var(--company-primary-soft-hover),var(--company-primary-soft)); font-size:2.5rem; }
  .ic-product-image img { width:100%; height:100%; object-fit:cover; }
  .ic-product-image span { position:absolute; left:12px; bottom:12px; padding:.33rem .6rem; border-radius:999px; color:#203d72; background:rgba(255,255,255,.92); font-size:.72rem; font-weight:750; }
  .ic-product-copy { padding:1rem; }.ic-product-copy h3 { margin:0 0 .45rem; font-size:1.05rem; }.ic-product-copy p { display:-webkit-box; min-height:2.8em; margin:0 0 .65rem; overflow:hidden; color:var(--company-muted); line-height:1.45; -webkit-box-orient:vertical; -webkit-line-clamp:2; }.ic-product-copy strong { color:var(--company-primary); }
  .ic-carousel-control { position:absolute; z-index:1; top:50%; width:40px; height:40px; display:grid; place-items:center; transform:translateY(-50%); border:1px solid rgba(219,228,241,.9); border-radius:50%; color:var(--company-ink); background:rgba(255,255,255,.94); box-shadow:0 5px 15px rgba(28,45,72,.12); }.ic-carousel-control.is-prev { left:8px; }.ic-carousel-control.is-next { right:8px; }
  .ic-news-card { flex:0 0 min(82%,390px); display:grid; grid-template-columns:42% 1fr; overflow:hidden; border:1px solid var(--company-border); border-radius:19px; background:#fff; scroll-snap-align:start; }
  .ic-news-art { position:relative; min-height:235px; display:grid; place-items:center; color:#fff; background:linear-gradient(145deg,var(--company-primary-hover),var(--company-primary)); font-size:3rem; }.ic-news-art-1 { background:linear-gradient(145deg,#e5b875,#a66a2e); }.ic-news-art-2 { background:linear-gradient(145deg,#8ebca0,#347153); }.ic-news-art-3 { background:linear-gradient(145deg,#bb8fca,#744e8a); }.ic-news-art span { position:absolute; left:12px; bottom:12px; padding:.35rem .6rem; border-radius:999px; background:rgba(20,34,58,.28); font-size:.68rem; font-weight:750; letter-spacing:.08em; text-transform:uppercase; }
  .ic-news-copy { align-self:center; padding:1.15rem; }.ic-news-copy small { color:var(--company-primary); font-weight:750; }.ic-news-copy h3 { margin:.5rem 0; font-size:1.07rem; }.ic-news-copy p { color:var(--company-muted); font-size:.9rem; line-height:1.55; }.ic-news-copy a { color:var(--company-primary); font-weight:750; text-decoration:none; }
  .ic-company-lower-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }.ic-lower-card { display:flex; flex-direction:column; align-items:flex-start; gap:.75rem; }.ic-lower-card h2 { margin:0; color:var(--company-ink); font-size:1.35rem; }.ic-lower-card p { color:var(--company-muted); line-height:1.65; }.ic-lower-icon { display:grid; width:48px; height:48px; place-items:center; border-radius:15px; color:var(--company-primary); background:var(--company-primary-soft); font-size:1.15rem; }.ic-company-address-lines span { display:block; }.ic-company-install-disabled { min-height:42px; display:inline-flex; align-items:center; padding:.6rem .9rem; border:1px solid var(--company-border); border-radius:12px; color:var(--company-muted); background:#f8faff; font-weight:700; }
  .ic-public-footer { width:min(100% - 2rem,1120px); display:flex; justify-content:space-between; gap:1rem; margin:2rem auto 0; padding:1.25rem 0; border-top:1px solid var(--company-border); color:var(--company-muted); font-size:.85rem; }.ic-public-footer a { color:var(--company-primary); font-weight:750; text-decoration:none; }
  .ic-public-floating-nav { position:fixed; z-index:1030; left:50%; bottom:14px; width:min(560px,calc(100% - 24px)); min-height:70px; display:grid; grid-template-columns:repeat(4,1fr); gap:4px; padding:6px; transform:translateX(-50%); border:1px solid var(--company-border); border-radius:23px; background:rgba(255,255,255,.95); box-shadow:0 16px 42px rgba(23,39,65,.18); backdrop-filter:blur(16px); }
  .ic-public-floating-nav a,.ic-public-floating-nav button { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; border:0; border-radius:17px; color:#52647f; background:transparent; font-size:.72rem; font-weight:750; text-decoration:none; }.ic-public-floating-nav i { font-size:1.05rem; }.ic-public-floating-nav .is-active { color:var(--company-primary); background:var(--company-primary-soft); }
  .ic-public-scrim { position:fixed; z-index:1040; inset:0; background:rgba(12,19,31,.52); opacity:0; transition:opacity .22s ease; }.ic-public-scrim.is-visible { opacity:1; }.ic-public-scrim[hidden] { display:none; }
  .ic-public-drawer { position:fixed; z-index:1041; top:0; bottom:0; width:min(360px,88vw); padding:0 0 20px; overflow-y:auto; color:var(--company-ink); background:#fff; box-shadow:0 14px 42px rgba(15,28,48,.18); transition:transform .25s ease; }.ic-public-drawer-left { left:0; transform:translateX(-105%); }.ic-public-drawer-right { right:0; padding:0 14px 20px; transform:translateX(105%); }.ic-public-drawer.is-open { transform:translateX(0); }
  body.ic-drawer-open { overflow:hidden; }
  .ic-public-drawer-heading { min-height:88px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--company-border); }.ic-public-drawer-heading div { display:grid; gap:5px; }.ic-public-drawer-heading small { color:var(--company-primary); font-size:.68rem; font-weight:850; letter-spacing:.12em; }.ic-public-drawer-heading strong { font-size:1.1rem; }.ic-public-drawer-heading button { width:40px; height:40px; border:0; border-radius:50%; color:#66758d; background:transparent; font-size:1.2rem; }
  .ic-public-drawer-links { display:grid; gap:5px; padding-top:18px; }.ic-public-drawer-links > a { min-height:52px; display:flex; align-items:center; gap:13px; padding:0 13px; border-radius:14px; color:var(--company-ink); font-weight:700; text-decoration:none; }.ic-public-drawer-links > a:hover { color:var(--company-primary); background:var(--company-primary-soft); }.ic-public-drawer-links > a > i:first-child { width:20px; color:var(--company-ink); }.ic-public-drawer-links > a > i:last-child { margin-left:auto; color:#9aa7ba; font-size:.78rem; }.ic-public-drawer-links > a.is-active { color:var(--company-primary); background:var(--company-primary-soft); }.ic-public-drawer-links > a.is-active > i:first-child { color:var(--company-primary); }.ic-public-drawer-divider { height:1px; margin:10px 8px; background:var(--company-border); }.ic-public-about-icliv { min-height:68px !important; gap:12px !important; border:1px solid var(--company-border); background:#f7f9fd; }.ic-public-about-icliv > img { width:32px; height:32px; flex:0 0 32px; object-fit:contain; }.ic-public-about-icliv > span { display:grid; gap:3px; }.ic-public-about-icliv small { color:var(--company-muted); font-size:.76rem; font-weight:500; }.ic-public-account-profile { display:grid; justify-items:center; gap:8px; padding:24px 0 16px; }.ic-public-account-profile > span { width:68px; height:68px; display:grid; place-items:center; border-radius:50%; color:var(--company-primary); background:var(--company-primary-soft); font-size:1.4rem; }.ic-public-account-profile small { color:var(--company-muted); }
  /* Drawer de navegação seguindo as medidas e a hierarquia visual do LevHub. */
  .ic-public-drawer .ic-public-offcanvas-header { min-height:0; display:flex; align-items:center; justify-content:space-between; padding:28px 24px 18px; border-bottom:1px solid var(--company-border); }
  .ic-public-drawer .ic-public-offcanvas-title { margin:0; font-size:1.15rem; font-weight:600; }
  .ic-public-drawer-kicker { display:block; margin-bottom:5px; color:var(--company-primary); font-size:.68rem; font-weight:850; letter-spacing:.12em; text-transform:uppercase; }
  .ic-public-drawer .ic-public-offcanvas-body { padding:20px 16px; }
  .ic-public-drawer-menu { display:grid; width:100%; gap:6px; }
  .ic-public-drawer-link { width:100%; min-height:52px; display:flex; box-sizing:border-box; align-items:center; gap:13px; padding:12px 14px; border-radius:14px; color:var(--company-ink); font-weight:700; text-decoration:none; }
  .ic-public-drawer-link:hover,.ic-public-drawer-link.is-active { color:var(--company-primary); background:var(--company-primary-soft); }
  .ic-public-drawer-link > i:first-child { width:22px; color:var(--company-primary); text-align:center; }
  .ic-public-drawer-arrow { flex:0 0 auto; margin-left:auto; color:#9aa7b9 !important; font-size:.72rem; }
  .ic-public-drawer-divider { height:1px; margin:14px 8px 8px; background:var(--company-border); }
  .ic-public-project-card { min-height:68px; align-items:center; gap:11px; border:1px solid var(--company-border); background:#f7f9fd; }
  .ic-public-project-card:hover { background:var(--company-primary-soft); }
  .ic-public-project-logo { width:32px; height:32px; flex:0 0 32px; object-fit:contain; }
  .ic-public-project-copy { min-width:0; display:flex; flex:1; flex-direction:column; gap:2px; }
  .ic-public-project-copy strong { color:var(--company-ink); font-size:.9rem; }
  .ic-public-project-copy small { overflow:hidden; color:var(--company-muted); font-size:.72rem; font-weight:500; text-overflow:ellipsis; white-space:nowrap; }
  @media(max-width:700px) { .ic-company-cta-grid,.ic-company-lower-grid { grid-template-columns:1fr; }.ic-company-cta { min-height:76px; }.ic-product-card { flex-basis:74%; }.ic-news-card { flex-basis:92%; grid-template-columns:1fr; }.ic-news-art { min-height:150px; }.ic-public-header-inner { width:calc(100% - 1rem); }.ic-public-menu-trigger,.ic-public-account-trigger { min-height:40px; padding:.5rem .7rem; }.ic-public-account-trigger span { display:none; }.ic-public-brand-logo { width:38px; height:38px; }.ic-public-project-link img { width:31px; height:31px; }.ic-public-footer { flex-direction:column; }.ic-company-section { padding-top:1.6rem; } }
</style>
<?php $this->end(); ?>

<main class="ic-company-page<?= !$empresa ? ' ic-company-page-not-found-layout' : '' ?>">
  <?php if (!$empresa): ?>
    <div class="ic-company-page-not-found">
      <section class="ic-company-page-not-found-card" aria-labelledby="paginaNaoEncontrada">
        <div class="ic-company-page-not-found-icon" aria-hidden="true"><i class="fa-solid fa-compass"></i></div>
        <span class="ic-company-page-not-found-kicker">icliv</span>
        <h1 id="paginaNaoEncontrada">Página não encontrada</h1>
        <p>Confira o endereço ou volte para o site do icliv.</p>
        <div class="ic-company-page-not-found-actions">
          <a href="<?= $esc(Helpers::url()) ?>" class="ic-btn ic-btn-primary"><i class="fa-solid fa-house me-1" aria-hidden="true"></i>Ir para o site</a>
          <a href="<?= $esc(Helpers::url('login')) ?>" class="ic-btn ic-btn-outline"><i class="fa-solid fa-right-to-bracket me-1" aria-hidden="true"></i>Entrar</a>
        </div>
      </section>
    </div>
  <?php else: ?>
    <?php
      $artigosDemonstracao = [
        ['Bem-estar', 'Como criar uma rotina de cuidados que funciona para seu pet', 'Cuidados'],
        ['Alimentação', 'O que observar ao escolher a alimentação do seu companheiro', 'Nutrição'],
        ['Prevenção', 'Consultas preventivas: cuidado antes de aparecer um problema', 'Saúde'],
        ['Comportamento', 'Enriquecimento ambiental para uma vida mais feliz', 'Comportamento'],
        ['Adaptação', 'A chegada de um novo pet: primeiros passos para a família', 'Dicas'],
        ['Higiene', 'Pequenos hábitos de higiene que fazem diferença', 'Rotina'],
        ['Comunidade', 'Adotar é acolher: preparando a casa para um novo amigo', 'Comunidade']
      ];
    ?>
    <header class="ic-public-header">
      <div class="ic-public-header-inner">
        <button class="ic-public-menu-trigger" type="button" data-ic-open-drawer="icMenuPrincipal" aria-controls="icMenuPrincipal" aria-label="Abrir menu"><i class="fa-solid fa-bars"></i><span>Menu</span></button>
        <a class="ic-public-brand" href="#inicio" aria-label="Início — icliv">
          <img class="ic-public-brand-logo" src="<?= $esc(Helpers::storage('storage/global/icliv.png')) ?>" alt="icliv">
        </a>
        <div class="ic-public-header-account">
          <?php if ($clienteLogado): ?>
            <button class="ic-public-account-trigger" type="button" data-ic-open-drawer="icMenuConta" aria-controls="icMenuConta"><i class="fa-solid fa-circle-user"></i><span><?= $esc($clienteLogado->cli_nome ?? 'Minha conta') ?></span><i class="fa-solid fa-chevron-down"></i></button>
          <?php else: ?>
            <a class="ic-public-project-link" href="<?= $esc(Helpers::url()) ?>" target="_blank" rel="noopener noreferrer" aria-label="Conheça o icliv">
              <img src="<?= $esc(Helpers::storage('storage/global/icliv.png')) ?>" alt="icliv">
            </a>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <div class="ic-public-scrim" data-ic-close-drawer hidden></div>
    <aside class="ic-public-drawer ic-public-drawer-left" id="icMenuPrincipal" aria-hidden="true" tabindex="-1">
      <div class="ic-public-offcanvas-header">
        <div><span class="ic-public-drawer-kicker">Navegação</span><h2 class="ic-public-offcanvas-title"><?= $esc($nomeEmpresa) ?></h2></div>
        <button type="button" class="btn-close" data-ic-close-drawer aria-label="Fechar"></button>
      </div>
      <div class="ic-public-offcanvas-body">
        <nav class="ic-public-drawer-menu" aria-label="Menu principal">
          <?php if (!$clienteLogado): ?>
            <a href="#entrar" class="ic-public-drawer-link" data-ic-login><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i><span>Entrar</span><i class="fa-solid fa-chevron-right ic-public-drawer-arrow" aria-hidden="true"></i></a>
          <?php endif; ?>
          <a href="#inicio" class="ic-public-drawer-link is-active" aria-current="page" data-ic-nav><i class="fa-solid fa-house" aria-hidden="true"></i><span>Início</span><i class="fa-solid fa-chevron-right ic-public-drawer-arrow" aria-hidden="true"></i></a>
          <a href="#servicos" class="ic-public-drawer-link" data-ic-nav><i class="fa-solid fa-calendar-check" aria-hidden="true"></i><span>Serviços</span><i class="fa-solid fa-chevron-right ic-public-drawer-arrow" aria-hidden="true"></i></a>
          <div class="ic-public-drawer-divider" role="separator"></div>
          <a href="<?= $esc(Helpers::url()) ?>" target="_blank" rel="noopener noreferrer" class="ic-public-drawer-link ic-public-project-card">
            <img src="<?= $esc(Helpers::storage('storage/global/icliv.png')) ?>" alt="icliv" class="ic-public-project-logo">
            <span class="ic-public-project-copy"><strong>icliv</strong><small>Gestão para negócios que cuidam</small></span>
            <i class="fa-solid fa-arrow-up-right-from-square ic-public-drawer-arrow" aria-hidden="true"></i>
          </a>
        </nav>
      </div>
    </aside>

    <?php if ($clienteLogado): ?>
      <aside class="ic-public-drawer ic-public-drawer-right" id="icMenuConta" aria-hidden="true" tabindex="-1">
        <div class="ic-public-drawer-heading"><div><small>ÁREA DO CLIENTE</small><strong>Minha conta</strong></div><button type="button" data-ic-close-drawer aria-label="Fechar conta"><i class="fa-solid fa-xmark"></i></button></div>
        <div class="ic-public-account-profile"><span><i class="fa-solid fa-user"></i></span><strong><?= $esc($clienteLogado->cli_nome ?? '') ?></strong><small>Área exclusiva do cliente</small></div>
        <nav class="ic-public-drawer-links" aria-label="Menu da conta">
          <a href="#perfil"><i class="fa-solid fa-user"></i><span>Perfil</span><i class="fa-solid fa-chevron-right"></i></a>
          <a href="#pedidos"><i class="fa-solid fa-receipt"></i><span>Pedidos</span><i class="fa-solid fa-chevron-right"></i></a>
          <a href="#favoritos"><i class="fa-solid fa-heart"></i><span>Favoritos</span><i class="fa-solid fa-chevron-right"></i></a>
          <a href="#sair" data-ic-logout><i class="fa-solid fa-right-from-bracket"></i><span>Sair</span><i class="fa-solid fa-chevron-right"></i></a>
        </nav>
      </aside>
    <?php endif; ?>

    <div class="ic-company-wrap" id="inicio">
      <section class="ic-company-section ic-about-section" id="sobre">
        <article class="ic-company-card ic-company-about">
          <div class="ic-about-heading"><div><span class="ic-company-kicker">SOBRE A EMPRESA</span><h1>Conheça <?= $esc($nomeEmpresa) ?></h1></div><span class="ic-about-logo"><?php if ($urlLogo !== ''): ?><img src="<?= $esc($urlLogo) ?>" alt="Logo <?= $esc($nomeEmpresa) ?>"><?php else: ?><i class="fa-solid fa-paw"></i><?php endif; ?></span></div>
          <div class="ic-company-rich-content"><?= $conteudoSeguro ?></div>
        </article>
      </section>

      <nav class="ic-company-cta-grid" aria-label="Acessos da empresa">
        <a href="#servicos" id="servicos" class="ic-company-cta" data-ic-service><i class="fa-regular fa-calendar-check"></i><span>Agende um serviço</span><i class="fa-solid fa-arrow-right"></i></a>
      </nav>

      <section class="ic-company-section" id="novidades">
        <div class="ic-company-section-head"><div><span class="ic-company-kicker">CONTEÚDO E COMUNIDADE</span><h2>Últimas notícias</h2><p>Informações e novidades para quem cuida com carinho.</p></div><span class="ic-company-news-count">7 novidades</span></div>
        <div class="ic-news-carousel" data-ic-carousel>
          <div class="ic-news-track">
            <?php foreach ($artigosDemonstracao as $indice => [$categoriaArtigo, $tituloArtigo, $temaArtigo]): ?>
              <article class="ic-news-card"><div class="ic-news-art ic-news-art-<?= $indice % 4 ?>"><i class="<?= ['fa-solid fa-heart-pulse', 'fa-solid fa-bowl-food', 'fa-solid fa-shield-heart', 'fa-solid fa-paw'][$indice % 4] ?>"></i><span><?= $esc($temaArtigo) ?></span></div><div class="ic-news-copy"><small><?= $esc($categoriaArtigo) ?> · <?= date('d/m/Y', strtotime('-' . (7 - $indice) . ' days')) ?></small><h3><?= $esc($tituloArtigo) ?></h3><p>Dicas e conteúdos preparados para apoiar o cuidado e o bem-estar dos animais em todas as fases da vida.</p><a href="#novidades" aria-label="Leia <?= $esc($tituloArtigo) ?>">Leia mais <i class="fa-solid fa-arrow-right"></i></a></div></article>
            <?php endforeach; ?>
          </div>
          <button type="button" class="ic-carousel-control is-prev" data-ic-prev aria-label="Notícias anteriores"><i class="fa-solid fa-chevron-left"></i></button><button type="button" class="ic-carousel-control is-next" data-ic-next aria-label="Próximas notícias"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </section>

      <section class="ic-company-section ic-company-lower-grid" id="visite">
        <article class="ic-company-card ic-lower-card"><div class="ic-lower-icon"><i class="fa-solid fa-location-dot"></i></div><span class="ic-company-kicker">VISITE-NOS</span><h2>Estamos por aqui</h2><?php if ($linhasEndereco): ?><p class="ic-company-address-lines"><?php foreach ($linhasEndereco as $linhaEndereco): ?><span><?= $esc($linhaEndereco) ?></span><?php endforeach; ?></p><?php else: ?><p>Entre em contato para saber como nos encontrar.</p><?php endif; ?><?php if ($urlMapa !== ''): ?><a class="ic-btn ic-btn-primary mt-auto" href="<?= $esc($urlMapa) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-map-location-dot me-2"></i>Abrir no mapa</a><?php endif; ?></article>
        <article class="ic-company-card ic-lower-card" id="aplicativo"><div class="ic-lower-icon"><i class="fa-solid fa-mobile-screen-button"></i></div><span class="ic-company-kicker">SEMPRE POR PERTO</span><h2><?= $esc(trim((string) ($pwa->pwa_nome ?? '')) ?: 'Instale nosso app') ?></h2><p><?= $esc(trim((string) ($pwa->pwa_descricao ?? '')) ?: ($pwa ? 'Adicione a página da empresa à tela inicial do seu dispositivo.' : 'Acesse a página da empresa com praticidade direto da tela inicial do seu celular.')) ?></p><?php if ($pwa): ?><button id="instalarPwaEmpresa" class="ic-btn ic-btn-primary mt-auto" type="button"><i class="fa-solid fa-download me-2"></i>Instalar aplicativo</button><p id="instrucoesInstalacaoPwa" class="small mt-3 mb-0" hidden></p><?php else: ?><span class="ic-company-install-disabled mt-auto"><i class="fa-solid fa-circle-info me-2"></i>Instalação ainda não disponível</span><?php endif; ?></article>
      </section>
    </div>

    <nav class="ic-public-floating-nav" aria-label="Atalhos principais">
      <a class="is-active" href="#inicio" data-ic-nav><i class="fa-solid fa-house"></i><span>Início</span></a>
      <a href="#servicos" data-ic-nav><i class="fa-solid fa-calendar-check"></i><span>Serviço</span></a>
      <?php if ($clienteLogado): ?><button type="button" data-ic-open-drawer="icMenuConta"><i class="fa-solid fa-user"></i><span>Perfil</span></button><?php else: ?><button type="button" data-ic-login><i class="fa-solid fa-right-to-bracket"></i><span>Entrar</span></button><?php endif; ?>
    </nav>
    <footer class="ic-public-footer"><a href="<?= $esc(Helpers::url()) ?>"><i class="fa-solid fa-paw"></i> Conheça a icliv</a><span><?= $esc($nomeEmpresa) ?> · Todos os direitos reservados</span></footer>
  <?php endif; ?>
</main>

<?php if ($empresa): ?>
<?php $this->push('script'); ?>
<script>
  (function () {
    const scrim = document.querySelector('.ic-public-scrim');
    let drawerOpen = null;

    function closeDrawer() {
      if (!drawerOpen) return;
      drawerOpen.classList.remove('is-open');
      drawerOpen.setAttribute('aria-hidden', 'true');
      drawerOpen = null;
      scrim.classList.remove('is-visible');
      window.setTimeout(function () { scrim.hidden = true; }, 240);
      document.body.classList.remove('ic-drawer-open');
    }

    function openDrawer(id) {
      const drawer = document.getElementById(id);
      if (!drawer || !scrim) return;
      closeDrawer();
      drawerOpen = drawer;
      scrim.hidden = false;
      requestAnimationFrame(function () {
        scrim.classList.add('is-visible');
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
      });
      document.body.classList.add('ic-drawer-open');
    }

    document.addEventListener('click', function (event) {
      const open = event.target.closest('[data-ic-open-drawer]');
      if (open) { openDrawer(open.getAttribute('data-ic-open-drawer')); return; }
      if (event.target.closest('[data-ic-close-drawer]')) { closeDrawer(); return; }
      const nav = event.target.closest('[data-ic-nav]');
      if (nav) closeDrawer();
      const service = event.target.closest('[data-ic-service]');
      if (service) {
        event.preventDefault();
        if (window.SimpleModal && typeof window.SimpleModal.mostrar === 'function') {
          window.SimpleModal.mostrar({
            titulo: '<i class="fa-regular fa-calendar-check"></i>&nbsp;Agendamento de serviços',
            tamanho: 'modal-sm modal-dialog-centered',
            corpo: '<p class="mb-0 text-muted">O agendamento on-line estará disponível quando o módulo de serviços da empresa for implementado.</p>',
            rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Fechar</button>'
          });
        }
      }
      const login = event.target.closest('[data-ic-login]');
      if (login) {
        event.preventDefault();
        closeDrawer();
        if (!window.SimpleModal || typeof window.SimpleModal.mostrar !== 'function') return;
        window.SimpleModal.mostrar({
          titulo: '<i class="fa-solid fa-right-to-bracket"></i>&nbsp;Entrar como cliente',
          tamanho: 'modal-md modal-dialog-centered',
          corpo: '<div class="ic-client-login-intro"><span><i class="fa-solid fa-user"></i></span><div><strong>Acesse sua experiência</strong><p>Entre para acompanhar seus pedidos e favoritos.</p></div></div><div class="form-floating mb-3"><input type="email" class="form-control" id="icLoginEmail" placeholder="E-mail" autocomplete="username"><label for="icLoginEmail">E-mail</label></div><div class="form-floating"><input type="password" class="form-control" id="icLoginSenha" placeholder="Senha" autocomplete="current-password"><label for="icLoginSenha">Senha</label></div><p class="small text-muted mt-3 mb-0">A autenticação de clientes será conectada ao módulo de conta em uma próxima etapa.</p>',
          rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</button><button type="button" class="bto bto-phsa" data-ic-login-submit><i class="fa-solid fa-right-to-bracket"></i>&nbsp;Entrar</button>'
        });
      }
      if (event.target.closest('[data-ic-login-submit]')) {
        event.preventDefault();
        const campoEmail = document.getElementById('icLoginEmail');
        if (campoEmail) campoEmail.focus();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeDrawer();
    });
    if (scrim) scrim.addEventListener('click', closeDrawer);

    document.querySelectorAll('[data-ic-carousel]').forEach(function (carousel) {
      const track = carousel.querySelector(':scope > .ic-product-track, :scope > .ic-news-track');
      if (!track) return;
      carousel.querySelectorAll('[data-ic-prev], [data-ic-next]').forEach(function (button) {
        button.addEventListener('click', function () {
          const slide = track.querySelector(':scope > article');
          const distance = slide ? slide.getBoundingClientRect().width + 16 : track.clientWidth * .8;
          track.scrollBy({ left: button.hasAttribute('data-ic-next') ? distance : -distance, behavior: 'smooth' });
        });
      });
    });
  })();
</script>
<style>
  .ic-client-login-intro { display:flex; align-items:center; gap:14px; margin-bottom:22px; }.ic-client-login-intro > span { width:52px; height:52px; display:grid; flex:0 0 52px; place-items:center; border-radius:50%; color:var(--phsa-soft-text); background:var(--phsa-soft-bg); font-size:1.2rem; }.ic-client-login-intro strong { display:block; }.ic-client-login-intro p { margin:4px 0 0; color:#66758d; }
</style>
<?php $this->end(); ?>
<?php endif; ?>

<?php if ($empresa && $pwa): ?>
<?php $this->push('script'); ?>
<script>
  (function () {
    const manifestUrl = <?= json_encode(Helpers::url('manifest-' . (int) $empresa->emp_id . '.json'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const serviceWorkerUrl = <?= json_encode(Helpers::url('serviceworker-' . (int) $empresa->emp_id . '.js'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const manifest = document.createElement('link');
    manifest.rel = 'manifest';
    manifest.href = manifestUrl;
    document.head.appendChild(manifest);

    if ('serviceWorker' in navigator) navigator.serviceWorker.register(serviceWorkerUrl).catch(function () {});

    const button = document.getElementById('instalarPwaEmpresa');
    const instructions = document.getElementById('instrucoesInstalacaoPwa');
    let installPrompt = null;
    window.addEventListener('beforeinstallprompt', function (event) {
      event.preventDefault();
      installPrompt = event;
      button.hidden = false;
    });
    button.addEventListener('click', async function () {
      if (!installPrompt) {
        instructions.hidden = false;
        instructions.textContent = /iphone|ipad|ipod/i.test(navigator.userAgent)
          ? 'No Safari, toque em Compartilhar e depois em Adicionar à Tela de Início.'
          : 'Use a opção Instalar aplicativo ou Adicionar à tela inicial no menu do navegador.';
        return;
      }
      installPrompt.prompt();
      await installPrompt.userChoice;
      installPrompt = null;
      button.hidden = true;
    });
  })();
</script>
<?php $this->end(); ?>
<?php endif; ?>
