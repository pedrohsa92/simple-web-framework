<?php

$this->layout('sistema::sistema');

$empresaNome = trim((string) ($empresaNome ?? 'sua empresa'));
$cadastrosAssistente = $cadastrosAssistente ?? [];
$cadastrosFinanceiro = $cadastrosFinanceiro ?? [];
$gruposAssistente = [
  'gerais' => ['titulo' => 'Cadastros pessoais e natureza jurídica', 'itens' => ['sexo', 'estadocivil', 'naturalidade', 'naturezajuridica']]
];
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$conhecimentoPrimeiroAcesso = $conhecimentoPrimeiroAcesso ?? null;
$midiasPrimeiroAcesso = $midiasPrimeiroAcesso ?? [];
$escaparAjuda = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$conteudoSeguroAjuda = static function (string $html): string {
  if ($html === '' || !class_exists('DOMDocument')) {
    return nl2br(htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
  }

  $documento = new DOMDocument('1.0', 'UTF-8');
  $anterior = libxml_use_internal_errors(true);
  $documento->loadHTML('<?xml encoding="UTF-8"><div id="setup-help-content">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
  libxml_clear_errors();
  libxml_use_internal_errors($anterior);
  $container = $documento->getElementById('setup-help-content');
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

?>
<?php $this->push('style'); ?>
<style>
  .lh-setup { --lh-setup-blue: var(--phsa-primary-bg, #34896c); --lh-setup-ink: #102a43; --lh-setup-muted: #627d98; --lh-setup-line: #d9e2ec; --lh-setup-soft: #f5f8fc; width: 100%; max-width: none; margin: 0; padding: .25rem 0 1.5rem; color: var(--lh-setup-ink); }
  .lh-setup-shell { width: 100%; overflow: hidden; border: 1px solid var(--lh-setup-line); border-radius: 1rem; background: #fff; box-shadow: 0 .45rem 1.5rem rgba(16,42,67,.07); }
  .lh-setup-top { padding: 1.4rem 1.5rem 1.2rem; border-bottom: 1px solid #e8eef5; background: linear-gradient(135deg,#f8fbff 0%,#fff 62%); }
  .lh-setup-eyebrow { display: inline-flex; align-items: center; gap: .4rem; color: var(--lh-setup-blue); font-size: .7rem; font-weight: 800; letter-spacing: .11em; text-transform: uppercase; }
  .lh-setup-title { margin: .45rem 0 .35rem; color: var(--lh-setup-ink); font-size: clamp(1.45rem,2.4vw,2rem); font-weight: 800; }
  .lh-setup-subtitle { max-width: 700px; margin: 0; color: var(--lh-setup-muted); font-size: .9rem; line-height: 1.65; }
  .lh-setup-steps { display: flex; align-items: flex-start; gap: 0; margin: 1.3rem 0 0; }
  .lh-setup-step { position: relative; display: flex; flex: 1 1 0; align-items: center; gap: .5rem; color: #9aaabd; font-size: .74rem; font-weight: 700; }
  .lh-setup-step.is-clickable { cursor: pointer; }
  .lh-setup-step.is-clickable:hover { color: var(--lh-setup-blue); }
  .lh-setup-step.is-clickable:hover .lh-setup-step-bullet { border-color: var(--lh-setup-blue); box-shadow: 0 0 0 .2rem #eaf1ff; }
  .lh-setup-step:not(:last-child)::after { content: ''; flex: 1; height: 1px; margin: 0 .65rem; background: #d9e2ec; }
  .lh-setup-step-bullet { display: grid; flex: 0 0 1.65rem; width: 1.65rem; height: 1.65rem; place-items: center; border: 2px solid #d9e2ec; border-radius: 50%; color: #829ab1; background: #fff; font-size: .72rem; }
  .lh-setup-step.active, .lh-setup-step.is-complete { color: var(--lh-setup-blue); }
  .lh-setup-step.active .lh-setup-step-bullet, .lh-setup-step.is-complete .lh-setup-step-bullet { border-color: var(--lh-setup-blue); color: #fff; background: var(--lh-setup-blue); }
  .lh-setup-body { min-height: 340px; padding: 1.5rem; }
  .lh-setup-step-content[hidden] { display: none; }
  .lh-setup-welcome { display: grid; grid-template-columns: minmax(0,1.7fr) minmax(240px,.8fr); gap: 1.1rem; }
  .lh-setup-message { display: flex; flex-direction: column; justify-content: center; min-height: 260px; padding: 1.5rem; border: 1px solid #dce8f8; border-radius: .85rem; background: linear-gradient(145deg,#f5f9ff,#fff 72%); }
  .lh-setup-message-icon { display: grid; width: 3.5rem; height: 3.5rem; place-items: center; margin-bottom: 1rem; border-radius: 1rem; color: var(--lh-setup-blue); background: #e4f1eb; font-size: 1.45rem; }
  .lh-setup-message h2,.lh-setup-info h3 { margin: 0 0 .55rem; color: var(--lh-setup-ink); font-size: 1.25rem; font-weight: 800; }
  .lh-setup-message p { max-width: 620px; margin: 0; color: var(--lh-setup-muted); line-height: 1.7; }
  .lh-setup-context { margin-top: 1.2rem; padding: .8rem 1rem; border-left: 3px solid var(--lh-setup-blue); border-radius: .25rem .55rem .55rem .25rem; color: var(--lh-setup-ink); background: #eff8f3; font-size: .82rem; }
  .lh-setup-info { padding: 1.2rem; border: 1px solid #e4ebf3; border-radius: .85rem; background: var(--lh-setup-soft); }
  .lh-setup-info h3 { font-size: .95rem; }
  .lh-setup-info-item { display: flex; gap: .7rem; padding: .7rem 0; border-top: 1px solid #e3eaf2; color: var(--lh-setup-muted); font-size: .78rem; line-height: 1.5; }
  .lh-setup-info-item:first-of-type { border-top: 0; padding-top: 0; }
  .lh-setup-info-item i { width: 1.1rem; margin-top: .15rem; color: var(--lh-setup-blue); text-align: center; }
  .lh-setup-info-item strong { display: block; color: var(--lh-setup-ink); font-size: .8rem; }
  .lh-setup-help { margin-top: 1rem; overflow: hidden; border: 1px solid #dce8f8; border-radius: .85rem; background: #fff; }
  .lh-setup-help summary { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; color: var(--lh-setup-blue); cursor: pointer; list-style: none; }
  .lh-setup-help summary::-webkit-details-marker { display: none; }
  .lh-setup-help summary::after { content: '\f078'; margin-left: auto; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: .75rem; transition: transform .18s ease; }
  .lh-setup-help[open] summary::after { transform: rotate(180deg); }
  .lh-setup-help-icon { display: grid; flex: 0 0 2.25rem; width: 2.25rem; height: 2.25rem; place-items: center; border-radius: .7rem; background: #edf3ff; }
  .lh-setup-help-summary strong { display: block; color: var(--lh-setup-ink); font-size: .88rem; }
  .lh-setup-help-summary small { display: block; margin-top: .1rem; color: var(--lh-setup-muted); font-size: .75rem; }
  .lh-setup-help-content { padding: 1rem 1.15rem 1.2rem; border-top: 1px solid #e8eef5; color: #475569; line-height: 1.75; }
  .lh-setup-help-content h2, .lh-setup-help-content h3, .lh-setup-help-content h4 { margin: 1.15rem 0 .5rem; color: var(--lh-setup-ink); font-size: 1rem; font-weight: 750; }
  .lh-setup-help-content h2:first-child, .lh-setup-help-content h3:first-child, .lh-setup-help-content h4:first-child { margin-top: 0; }
  .lh-setup-help-content img { max-width: 100%; height: auto; border-radius: .5rem; }
  .lh-setup-help-content table { width: 100%; border-collapse: collapse; }
  .lh-setup-help-content th, .lh-setup-help-content td { padding: .45rem; border: 1px solid #d9e2ec; }
  .lh-setup-help-media { display: grid; gap: .75rem; margin-top: 1rem; }
  .lh-setup-help-media video { width: 100%; max-height: 30rem; border-radius: .65rem; background: #101828; }
  .lh-setup-stage-heading { margin-bottom: 1.2rem; }
  .lh-setup-stage-heading h2 { margin: .35rem 0 .3rem; color: var(--lh-setup-ink); font-size: 1.35rem; font-weight: 800; }
  .lh-setup-stage-heading p { max-width: 760px; margin: 0; color: var(--lh-setup-muted); font-size: .82rem; line-height: 1.6; }
  .lh-setup-catalog-layout { display: grid; grid-template-columns: 285px minmax(0,1fr); gap: 1.25rem; }
  .lh-setup-catalog-menu { align-self: start; padding: .75rem; border: 1px solid #e1e9f2; border-radius: .85rem; background: var(--lh-setup-soft); }
  .lh-setup-catalog-menu-title { padding: .35rem .5rem .7rem; color: var(--lh-setup-muted); font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
  .lh-setup-catalog-tab { display: flex; width: 100%; align-items: center; gap: .6rem; margin-top: .25rem; padding: .7rem .65rem; border: 1px solid transparent; border-radius: .6rem; color: var(--lh-setup-muted); background: transparent; font-size: .78rem; text-align: left; transition: background .2s ease,border-color .2s ease,color .2s ease; }
  .lh-setup-catalog-tab:hover,.lh-setup-catalog-tab.active { border-color: #cde4d8; color: var(--lh-setup-blue); background: #fff; }
  .lh-setup-catalog-tab.active { font-weight: 800; box-shadow: 0 .2rem .65rem rgba(16,42,67,.05); }
  .lh-setup-catalog-tab i { width: 1.2rem; text-align: center; }
  .lh-setup-catalog-tab > span:not(.lh-setup-catalog-tab-meta) { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .lh-setup-catalog-tab-meta { display: inline-flex; align-items: center; gap: .35rem; margin-left: auto; }
  .lh-setup-catalog-count { min-width: 1.15rem; color: var(--lh-setup-muted); font-size: .68rem; font-weight: 800; text-align: center; }
  .lh-setup-catalog-status.is-valid { color: #198754; } .lh-setup-catalog-status.is-invalid { color: #dc3545; }
  .lh-setup-catalog-panel { min-width: 0; padding: 1.25rem; border: 1px solid #dce8f8; border-radius: .85rem; background: linear-gradient(145deg,#f8fbff,#fff 65%); }
  .lh-setup-catalog-panel[hidden] { display: none; }
  .lh-setup-catalog-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
  .lh-setup-catalog-heading h3 { margin: 0 0 .3rem; color: var(--lh-setup-ink); font-size: 1.15rem; font-weight: 800; }
  .lh-setup-catalog-heading p { margin: 0; color: var(--lh-setup-muted); font-size: .8rem; line-height: 1.55; }
  .lh-setup-catalog-badge { border-radius: 999px; padding: .35rem .6rem; color: var(--lh-setup-blue); background: #e6f3ec; font-size: .68rem; font-weight: 800; white-space: nowrap; }
  .lh-setup-catalog-dependency-warning { display: flex; gap: .55rem; margin-bottom: .8rem; padding: .65rem .75rem; border: 1px solid #d9e2ec; border-radius: .5rem; color: var(--lh-setup-muted); background: #f5f8fc; font-size: .75rem; }
  .lh-setup-catalog-dependency-warning { align-items: flex-start; margin: 0 0 .85rem; padding: .65rem .75rem; border-color: #b6effb; border-radius: .55rem; color: #146c80; background: #e8faff; font-size: .76rem; }
  .lh-setup-catalog-dependency-warning i { margin-top: .12rem; }
  .lh-setup-plan-hierarchy-note { display: flex; align-items: flex-start; gap: .55rem; margin: 0 0 .85rem; padding: .7rem .8rem; border: 1px solid #c9dcff; border-radius: .6rem; color: #244b83; background: #f1f6ff; font-size: .76rem; line-height: 1.5; }
  .lh-setup-plan-hierarchy-note > i { margin-top: .15rem; }
  .lh-setup-plan-row.is-group { background: #f1f6ff; }
  .lh-setup-plan-row.is-group td { border-top: 1px solid #c9dcff; font-weight: 700; }
  .lh-setup-plan-row .lh-setup-plan-description { width: auto; text-align: left !important; }
  .lh-setup-plan-row.is-item .lh-setup-plan-description { padding-left: 1.5rem; color: #667085; }
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table th:first-child,
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table td:first-child { width: 3rem; text-align: center; }
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table th:nth-child(3),
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table td:nth-child(3) { width: 6rem; min-width: 6rem; text-align: center; white-space: nowrap; }
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table th:nth-last-child(2),
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table td:nth-last-child(2) { width: 6rem; text-align: center; }
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table th:last-child,
  .lh-setup-catalog-panel[data-finance-panel="plano-conta"] .lh-setup-catalog-table td:last-child { width: 5rem; min-width: 5rem; text-align: center; white-space: nowrap; }
  .lh-setup-plan-add-item { display: inline-flex; align-items: center; justify-content: center; gap: .2rem; min-width: max-content; padding: .25rem .45rem; font-size: .7rem; white-space: nowrap; }
  .lh-setup-catalog-balance-input { min-width: 7.5rem; }
  .lh-setup-catalog-number-input { min-width: 5.75rem; }
  .lh-setup-catalog-table tr.is-unavailable { opacity: .5; }
  .lh-setup-catalog-table-wrap { overflow-x: auto; border: 1px solid #e1e9f2; border-radius: .65rem; background: #fff; }
  .lh-setup-catalog-table { width: 100%; margin: 0; border-collapse: collapse; font-size: .8rem; }
  .lh-setup-catalog-table th { color: var(--lh-setup-muted); background: #f7f9fc; font-size: .68rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
  .lh-setup-catalog-table th,.lh-setup-catalog-table td { padding: .6rem .7rem; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
  .lh-setup-catalog-table tr:last-child td { border-bottom: 0; }
  .lh-setup-catalog-table td:first-child,.lh-setup-catalog-table th:first-child,.lh-setup-catalog-table td:nth-child(3),.lh-setup-catalog-table th:nth-child(3),.lh-setup-catalog-table td:last-child,.lh-setup-catalog-table th:last-child { width: 3.5rem; text-align: center; }
  .lh-setup-catalog-table input[type=checkbox],.lh-setup-catalog-table input[type=radio] { accent-color: var(--lh-setup-blue); }
  .lh-setup-catalog-table .form-control { min-width: 150px; font-size: .82rem; }
  .lh-setup-catalog-item-name { display: inline; padding: 0; border: 0; color: var(--lh-setup-blue); background: transparent; font: inherit; text-align: left; cursor: pointer; }
  .lh-setup-catalog-item-name:hover { color: #1f407f; text-decoration: none; }
  .lh-setup-catalog-item-name,.lh-setup-catalog-item-name:not(.is-default),.lh-setup-catalog-item-name:not(.is-default):hover { text-decoration: none; }
  .lh-setup-catalog-item-name.is-default { color: var(--lh-setup-ink); }
  .lh-setup-catalog-item-name.is-default:hover { color: var(--lh-setup-blue); text-decoration: none; }
  .lh-setup-catalog-table-unidades th:nth-child(3),.lh-setup-catalog-table-unidades td:nth-child(3) { width: 9rem; text-align: left; }
  .lh-setup-catalog-delete { display: inline-grid; width: 1.8rem; height: 1.8rem; place-items: center; padding: 0; border: 0; border-radius: .45rem; color: #dc3545; background: #fff0f2; }
  .lh-setup-catalog-delete:hover { color: #fff; background: #dc3545; }
  .lh-setup-catalog-actions { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: .9rem; }
  .lh-setup-catalog-actions-left,.lh-setup-catalog-actions-right { display: flex; flex-wrap: wrap; gap: .45rem; }
  .lh-setup-catalog-actions .bto { font-size: .72rem; }
  .lh-setup-catalog-note { margin: .85rem 0 0; color: var(--lh-setup-muted); font-size: .7rem; }
  .lh-setup-summary-overview { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .85rem; margin-bottom: 1rem; }
  .lh-setup-summary-highlight,.lh-setup-summary-card { padding: 1rem; border: 1px solid #dce8f8; border-radius: .75rem; background: #f8fbff; }
  .lh-setup-summary-highlight span { display: block; color: var(--lh-setup-muted); font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; }
  .lh-setup-summary-highlight strong { display: block; margin-top: .35rem; color: var(--lh-setup-ink); font-size: 1.15rem; }
  .lh-setup-summary-grid { display: grid; grid-template-columns: minmax(0,1fr); gap: 1rem; }
  .lh-setup-summary-card { min-width: 0; background: #fff; }
  .lh-setup-summary-card h3 { display: flex; align-items: center; gap: .45rem; margin: 0 0 .85rem; color: var(--lh-setup-ink); font-size: .9rem; }
  .lh-setup-summary-card h3 i { color: var(--lh-setup-blue); }
  .lh-setup-summary-item { padding: .65rem 0; border-top: 1px solid #edf2f7; }
  .lh-setup-summary-item:first-child { padding-top: 0; border-top: 0; }
  .lh-setup-summary-item-heading { display: flex; align-items: center; justify-content: space-between; gap: .75rem; color: var(--lh-setup-ink); font-size: .78rem; font-weight: 800; }
  .lh-setup-summary-item-count { color: var(--lh-setup-blue); font-size: .7rem; white-space: nowrap; }
  .lh-setup-summary-values { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }
  .lh-setup-summary-value { padding: .25rem .45rem; border-radius: .35rem; color: var(--lh-setup-muted); background: #f5f8fc; font-size: .68rem; }
  .lh-setup-summary-confirmation { display: flex; flex-wrap: wrap; align-items: flex-start; gap: .7rem; margin-top: 1rem; padding: .85rem; border: 1px solid #b9d0f5; border-radius: .55rem; color: var(--lh-setup-ink); background: #f4f8ff; }
  .lh-setup-summary-confirmation .form-check-input { flex: 0 0 auto; width: 1.15rem; height: 1.15rem; margin: .1rem 0 0; }
  .lh-setup-summary-confirmation label { display: flex; flex-direction: column; gap: .15rem; cursor: pointer; }
  .lh-setup-summary-confirmation label span { color: var(--lh-setup-muted); font-size: .85rem; }
  .lh-setup-summary-confirmation .invalid-feedback { flex-basis: 100%; margin-left: 1.85rem; }
  .lh-setup-summary-confirmation.is-invalid { border-color: #dc3545; }
  .lh-setup-summary-confirmation.is-invalid .invalid-feedback { display: block; }
  .lh-setup-actions [data-setup-confirm][hidden] { display: none; }
  .lh-setup-footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.5rem; border-top: 1px solid #e8eef5; background: #fbfcfe; }
  .lh-setup-progress { color: var(--lh-setup-muted); font-size: .76rem; } .lh-setup-progress strong { color: var(--lh-setup-ink); }
  .lh-setup-actions { display: flex; gap: .55rem; } .lh-setup-actions .bto { min-width: 110px; }
  @media(max-width:760px) { .lh-setup-top,.lh-setup-body { padding: 1.1rem; } .lh-setup-footer { align-items: stretch; flex-direction: column; padding: 1rem 1.1rem; } .lh-setup-actions { justify-content: flex-end; } .lh-setup-welcome,.lh-setup-catalog-layout,.lh-setup-summary-grid { grid-template-columns: 1fr; } .lh-setup-catalog-menu { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .25rem; } .lh-setup-catalog-menu-title { grid-column: 1/-1; } .lh-setup-catalog-actions { align-items: stretch; flex-direction: column; } .lh-setup-catalog-actions-left,.lh-setup-catalog-actions-right { justify-content: flex-start; } .lh-setup-summary-overview { grid-template-columns: 1fr; } .lh-setup-step { font-size: 0; } .lh-setup-step:not(:last-child)::after { margin: 0 .35rem; } }
</style>
<?php $this->end(); ?>

<div class="lh-setup">
  <section class="lh-setup-shell" aria-labelledby="tituloConfiguracaoInicial">
    <header class="lh-setup-top">
      <div class="lh-setup-eyebrow"><i class="fa-solid fa-wand-magic-sparkles"></i> Configuração inicial</div>
      <h1 class="lh-setup-title" id="tituloConfiguracaoInicial">Vamos preparar seu espaço</h1>
      <p class="lh-setup-subtitle">Antes de começar, vamos definir informações essenciais para que o ICLIV se adapte à rotina da sua organização e empresa.</p>
      <div class="lh-setup-steps" aria-label="Progresso da configuração inicial">
        <?php foreach (['Boas-vindas', 'Cadastros gerais', 'Financeiro', 'Resumo'] as $i => $rotulo): ?>
          <div class="lh-setup-step<?= $i === 0 ? ' active' : '' ?>" role="button" data-setup-step="<?= $i + 1 ?>" aria-current="<?= $i === 0 ? 'step' : 'false' ?>" aria-disabled="true" tabindex="-1">
            <span class="lh-setup-step-bullet"><?= $i + 1 ?></span><span><?= $escape($rotulo) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </header>

    <div class="lh-setup-body">
      <section class="lh-setup-step-content" id="lhSetupStep1">
        <div class="lh-setup-welcome">
          <div class="lh-setup-message">
            <div class="lh-setup-message-icon"><i class="fa-solid fa-compass"></i></div>
            <h2>Uma configuração rápida para começar bem</h2>
            <p>Este assistente é obrigatório no primeiro acesso após a contratação. Você poderá revisar as opções sugeridas, incluir ou remover itens e confirmar os cadastros básicos usados pelo ICLIV.</p>
            <div class="lh-setup-context"><i class="fa-solid fa-building me-1"></i> Configurando agora: <strong><?= $escape($empresaNome) ?></strong></div>
          </div>
          <aside class="lh-setup-info" aria-label="Informações sobre a configuração">
            <h3>O que acontecerá?</h3>
            <div class="lh-setup-info-item"><i class="fa-solid fa-list-check"></i><div><strong>Escolha os cadastros</strong>Adapte as listas auxiliares às necessidades da sua organização.</div></div>
            <div class="lh-setup-info-item"><i class="fa-solid fa-pen-to-square"></i><div><strong>Revise antes de confirmar</strong>Nada será gravado enquanto você avança pelas etapas.</div></div>
            <div class="lh-setup-info-item"><i class="fa-solid fa-shield-halved"></i><div><strong>Confirmação segura</strong>A gravação acontece somente depois da revisão final.</div></div>
          </aside>
        </div>
        <?php if ($conhecimentoPrimeiroAcesso): ?>
          <details class="lh-setup-help">
            <summary>
              <span class="lh-setup-help-icon"><i class="fa-regular fa-circle-question"></i></span>
              <span class="lh-setup-help-summary">
                <strong>Ficou com dúvida? Veja como fazer</strong>
                <small><?= $escaparAjuda($conhecimentoPrimeiroAcesso->con_titulo ?? 'Orientações para o primeiro acesso') ?></small>
              </span>
            </summary>
            <div class="lh-setup-help-content">
              <?php if (trim((string) ($conhecimentoPrimeiroAcesso->con_descricao_resumida ?? '')) !== ''): ?>
                <p class="text-muted"><?= $escaparAjuda($conhecimentoPrimeiroAcesso->con_descricao_resumida) ?></p>
              <?php endif; ?>
              <?php if (trim((string) ($conhecimentoPrimeiroAcesso->con_conteudo ?? '')) !== ''): ?>
                <div><?= $conteudoSeguroAjuda((string) $conhecimentoPrimeiroAcesso->con_conteudo) ?></div>
              <?php endif; ?>
              <?php if ($midiasPrimeiroAcesso): ?>
                <div class="lh-setup-help-media" aria-label="Vídeos e materiais de apoio">
                  <?php foreach ($midiasPrimeiroAcesso as $midia): ?>
                    <?php
                      $urlMidia = trim((string) ($midia->cmi_url_publica ?? $midia->cmi_url ?? ''));
                      $urlMidiaValida = preg_match('#^https?://#i', $urlMidia) === 1;
                    ?>
                    <?php if ($urlMidiaValida && ($midia->cmi_tipo ?? '') === 'Upload'): ?>
                      <div>
                        <strong class="d-block mb-2"><?= $escaparAjuda($midia->cmi_nome ?? 'Vídeo de orientação') ?></strong>
                        <video controls preload="none" playsinline src="<?= $escaparAjuda($urlMidia) ?>"></video>
                      </div>
                    <?php elseif ($urlMidiaValida): ?>
                      <a class="bto bto-phsa-light" href="<?= $escaparAjuda($urlMidia) ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>&nbsp;<?= $escaparAjuda($midia->cmi_nome ?? 'Abrir material de apoio') ?>
                      </a>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </details>
        <?php else: ?>
          <div class="lh-setup-help-hint mt-3 small text-muted">
            <i class="fa-regular fa-circle-question text-phsa me-1"></i>
            As orientações de primeiro acesso estarão disponíveis aqui assim que o conteúdo de ajuda for publicado.
          </div>
        <?php endif; ?>
      </section>

      <?php foreach ([2 => 'gerais'] as $etapa => $grupoId): ?>
        <section class="lh-setup-step-content" id="lhSetupStep<?= $etapa ?>" hidden>
          <div class="lh-setup-stage-heading">
            <div class="lh-setup-eyebrow"><i class="fa-solid fa-list-check"></i> Etapa <?= $etapa ?> de 4</div>
            <h2><?= $escape($gruposAssistente[$grupoId]['titulo']) ?></h2>
            <p>Revise as opções disponíveis. Desmarque as que não usará, edite os nomes, escolha o padrão ou acrescente novos registros.</p>
          </div>
          <div class="lh-setup-catalog-layout">
            <nav class="lh-setup-catalog-menu" aria-label="Cadastros desta etapa">
              <div class="lh-setup-catalog-menu-title">Cadastros disponíveis</div>
              <?php foreach ($gruposAssistente[$grupoId]['itens'] as $index => $chave): $cadastro = $cadastrosAssistente[$chave]; ?>
                <button type="button" class="lh-setup-catalog-tab<?= $index === 0 ? ' active' : '' ?>" data-catalog-tab="<?= $escape($chave) ?>">
                  <i class="fa-solid fa-list-check"></i><span><?= $escape($cadastro['titulo']) ?></span>
                  <span class="lh-setup-catalog-tab-meta"><span class="lh-setup-catalog-count" data-count>0</span><i class="fa-solid fa-circle-check lh-setup-catalog-status is-valid" data-status></i></span>
                </button>
              <?php endforeach; ?>
            </nav>

            <div>
              <?php foreach ($gruposAssistente[$grupoId]['itens'] as $index => $chave): $cadastro = $cadastrosAssistente[$chave]; ?>
                <section class="lh-setup-catalog-panel" data-catalog-panel="<?= $escape($chave) ?>"<?= $index === 0 ? '' : ' hidden' ?>>
                  <div class="lh-setup-catalog-heading">
                    <div><h3><?= $escape($cadastro['titulo']) ?></h3><p><?= $escape($cadastro['descricao']) ?></p></div>
                    <span class="lh-setup-catalog-badge" data-panel-count>0 selecionados</span>
                  </div>
                  <div class="lh-setup-catalog-table-wrap">
                    <table class="lh-setup-catalog-table">
                      <thead><tr><th><span class="visually-hidden">Usar</span></th><th>Nome</th><?php if ($chave === 'naturalidade'): ?><th>UF</th><?php endif; ?><th>Padrão</th><th>Ação</th></tr></thead>
                      <tbody data-catalog-body data-key="<?= $escape($chave) ?>" data-step-group="<?= $escape($grupoId) ?>">
                        <?php foreach ($cadastro['sugestoes'] as $i => $sugestao): $nome = is_array($sugestao) ? ($sugestao['nome'] ?? '') : $sugestao; ?>
                          <tr data-catalog-item>
                            <td><input type="checkbox" data-selected checked aria-label="Incluir <?= $escape($nome) ?>"></td>
                             <td><input type="text" class="form-control" data-name value="<?= $escape($nome) ?>" maxlength="255" required></td>
                            <?php if ($chave === 'naturalidade'): ?><td><input type="text" class="form-control" data-uf maxlength="2" aria-label="UF" placeholder="UF"></td><?php endif; ?>
                             <td><input type="radio" name="padrao-<?= $escape($chave) ?>" data-default<?= $i === 0 ? ' checked' : '' ?> aria-label="Definir <?= $escape($nome) ?> como padrão"></td>
                            <td><button type="button" class="lh-setup-catalog-delete" data-remove aria-label="Remover opção"><i class="fa-solid fa-trash"></i></button></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="lh-setup-catalog-actions">
                    <div class="d-flex flex-wrap gap-2">
                      <button type="button" class="bto bto-phsa-light" data-check-all><i class="fa-solid fa-check-double"></i>&nbsp;Marcar todas</button>
                      <button type="button" class="bto bto-phsa-light" data-uncheck-all><i class="fa-solid fa-square-minus"></i>&nbsp;Desmarcar todas</button>
                    </div>
                    <button type="button" class="bto bto-phsa-light" data-add><i class="fa-solid fa-plus"></i>&nbsp;Novo</button>
                  </div>
                  <p class="lh-setup-catalog-note">As alterações ainda não foram gravadas. Elas serão aplicadas após a confirmação do resumo.</p>
                </section>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endforeach; ?>

      <section class="lh-setup-step-content" id="lhSetupStep3" hidden>
        <div class="lh-setup-stage-heading">
          <div class="lh-setup-eyebrow"><i class="fa-solid fa-coins"></i> Etapa 3 de 4</div>
          <h2>Financeiro &gt; Básico</h2>
          <p>Prepare os cadastros essenciais do financeiro. As condições dependem das formas de pagamento selecionadas.</p>
        </div>
        <div class="lh-setup-catalog-layout">
          <nav class="lh-setup-catalog-menu" aria-label="Cadastros financeiros">
            <div class="lh-setup-catalog-menu-title">Cadastros desta etapa</div>
            <?php foreach ($cadastrosFinanceiro as $indice => $cadastro): ?>
              <button type="button" class="lh-setup-catalog-tab<?= $indice === 0 ? ' active' : '' ?>" data-finance-tab="<?= $escape($cadastro['id']) ?>">
                <i class="fa-solid <?= $cadastro['id'] === 'condicao-pgto' ? 'fa-link' : 'fa-coins' ?>"></i><span><?= $escape($cadastro['titulo']) ?></span>
                <span class="lh-setup-catalog-tab-meta"><span class="lh-setup-catalog-count" data-finance-count>0</span><i class="lh-setup-catalog-status is-invalid fa-solid fa-circle-xmark" data-finance-status title="Nenhuma opção selecionada"></i></span>
              </button>
            <?php endforeach; ?>
          </nav>
          <div>
            <?php foreach ($cadastrosFinanceiro as $indice => $cadastro): $id = $cadastro['id']; ?>
              <section class="lh-setup-catalog-panel" data-finance-panel="<?= $escape($id) ?>"<?= $indice === 0 ? '' : ' hidden' ?>>
                <div class="lh-setup-catalog-heading"><div><h3><?= $escape($cadastro['titulo']) ?></h3><p><?= $escape($cadastro['descricao']) ?></p></div><span class="lh-setup-catalog-badge">Sugestões iniciais</span></div>
                <?php if ($id === 'condicao-pgto'): ?><div class="lh-setup-catalog-dependency-warning" data-finance-warning hidden><i class="fa-solid fa-circle-info"></i><span>Selecione ao menos uma forma de pagamento para liberar este cadastro.</span></div><?php endif; ?>
                <?php if ($id === 'plano-conta'): ?><div class="lh-setup-plan-hierarchy-note"><i class="fa-solid fa-circle-info"></i><span><strong>Grupo</strong> organiza uma natureza financeira; <strong>Item</strong> é a classificação usada nos lançamentos. Todo grupo selecionado precisa ter pelo menos um item selecionado.</span></div><?php endif; ?>
                <div class="lh-setup-catalog-table-wrap"><table class="lh-setup-catalog-table"><thead><tr><th>Usar</th><?php if ($id === 'condicao-pgto'): ?><th>Forma de pagamento</th><?php endif; ?><th>Descrição</th><?php if ($id === 'conta-financeira'): ?><th>Tipo</th><th>Saldo inicial</th><?php elseif ($id === 'plano-conta'): ?><th>Natureza</th><?php elseif ($id === 'condicao-pgto'): ?><th>Quantidade de parcela</th><th>Dias entre parcelas</th><?php endif; ?><th>Padrão</th><th>Ação</th></tr></thead>
                  <tbody>
                    <?php
                      $itensFinanceiroPainel = [];
                      if ($id === 'plano-conta') {
                        foreach ($cadastro['grupos'] as $grupoPlano) {
                          $itensFinanceiroPainel[] = ['nome' => $grupoPlano['nome'], 'nivel' => 'Grupo', 'pai' => '', 'natureza' => $grupoPlano['natureza']];
                          foreach ($grupoPlano['itens'] as $itemPlano) $itensFinanceiroPainel[] = ['nome' => $itemPlano, 'nivel' => 'Item', 'pai' => $grupoPlano['nome'], 'natureza' => $grupoPlano['natureza']];
                        }
                      } else {
                        foreach ($cadastro['itens'] as $itemPlano) $itensFinanceiroPainel[] = is_array($itemPlano) ? $itemPlano : ['nome' => $itemPlano];
                      }
                    ?>
                    <?php foreach ($itensFinanceiroPainel as $itemIndice => $item): $key = $id . '-' . $itemIndice; $ehGrupoPlano = ($item['nivel'] ?? '') === 'Grupo'; $naturezaEntrada = ($item['natureza'] ?? 'Entrada') === 'Entrada'; ?>
                      <tr data-finance-item data-default-item="1" data-key="<?= $escape($key) ?>" data-catalog-nivel="<?= $escape($item['nivel'] ?? '') ?>" data-catalog-parent="<?= $escape($item['pai'] ?? '') ?>" data-catalog-natureza="<?= $escape($item['natureza'] ?? '') ?>" data-catalog-dependency-value="<?= $escape($item['dependencia'] ?? '') ?>" data-catalog-tipo="<?= $escape($item['tipo'] ?? 'Caixa') ?>" data-catalog-quantidade="<?= (int) ($item['quantidade'] ?? 1) ?>" data-catalog-intervalo="<?= (int) ($item['intervalo'] ?? 0) ?>" class="<?= $id === 'plano-conta' ? 'lh-setup-plan-row ' . ($ehGrupoPlano ? 'is-group' : 'is-item') : '' ?>" title="<?= $id === 'plano-conta' ? $escape(($ehGrupoPlano ? 'Grupo ' : 'Item ') . $item['nome']) : '' ?>">
                        <td><input type="checkbox" data-finance-selected checked aria-label="Incluir <?= $escape($item['nome']) ?>"></td>
                        <?php if ($id === 'condicao-pgto'): ?><td><span class="lh-setup-catalog-dependency-value" data-finance-dependency-label><?= $escape($cadastrosFinanceiro[3]['itens'][$itemIndice]['nome'] ?? '—') ?></span></td><?php endif; ?>
                        <td class="<?= $id === 'plano-conta' ? 'lh-setup-plan-description' : '' ?>"><button type="button" class="lh-setup-catalog-item-name is-default" data-finance-name><?= $escape($item['nome']) ?></button></td>
                        <?php if ($id === 'conta-financeira'): ?><td><span data-finance-type>Caixa</span></td><td><input type="text" class="form-control form-control-sm text-end dinheiro lh-setup-catalog-balance-input" data-finance-balance value="<?= $escape($item['saldoInicial'] ?? '0,00') ?>" inputmode="decimal" aria-label="Saldo inicial"></td>
                        <?php elseif ($id === 'plano-conta'): ?><td><span class="lh-setup-catalog-natureza text-<?= $naturezaEntrada ? 'success' : 'danger' ?>" data-finance-natureza><i class="fa-solid <?= $naturezaEntrada ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?> me-1"></i><?= $naturezaEntrada ? 'Entrada' : 'Saída' ?></span></td>
                        <?php elseif ($id === 'condicao-pgto'): ?><td><input type="number" min="1" step="1" class="form-control form-control-sm text-end lh-setup-catalog-number-input" data-finance-quantity value="<?= (int) ($item['quantidade'] ?? 1) ?>" aria-label="Quantidade de parcela" disabled></td><td><input type="number" min="0" step="1" class="form-control form-control-sm text-end lh-setup-catalog-number-input" data-finance-interval value="<?= (int) ($item['intervalo'] ?? 0) ?>" aria-label="Dias entre parcelas" disabled></td><?php endif; ?>
                        <td><input type="radio" name="padrao-<?= $escape($id) ?>" data-finance-default<?= $itemIndice === ($cadastro['padrao'] ?? -1) ? ' checked' : '' ?><?= $id === 'plano-conta' && $ehGrupoPlano ? ' disabled' : '' ?> aria-label="Definir padrão"></td>
                        <td><?php if ($id === 'plano-conta' && $ehGrupoPlano): ?><button type="button" class="bto bto-phsa-light btn-sm lh-setup-plan-add-item" data-finance-add-child title="Adicionar item ao grupo"><i class="fa-solid fa-plus"></i><span class="d-none d-lg-inline">&nbsp;Item</span></button><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody></table></div>
                <div class="lh-setup-catalog-actions"><div class="lh-setup-catalog-actions-left"><button type="button" class="bto bto-phsa-light" data-finance-check><i class="fa-solid fa-check-double"></i>&nbsp;Marcar todas</button><button type="button" class="bto bto-phsa-light" data-finance-uncheck><i class="fa-solid fa-square-minus"></i>&nbsp;Desmarcar todas</button></div><div class="lh-setup-catalog-actions-right">
                  <?php if ($id === 'plano-conta'): ?><button type="button" class="bto bto-phsa-light" data-finance-add data-new-level="Grupo"><i class="fa-solid fa-folder-plus"></i>&nbsp;Novo grupo</button><button type="button" class="bto bto-phsa-light" data-finance-add data-new-level="Item"><i class="fa-solid fa-file-circle-plus"></i>&nbsp;Novo item</button><?php else: ?><button type="button" class="bto bto-phsa-light" data-finance-add><i class="fa-solid fa-plus"></i>&nbsp;Novo</button><?php endif; ?></div></div>
                <p class="lh-setup-catalog-note">Nada será salvo enquanto a configuração não for confirmada no final.</p>
              </section>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="lh-setup-step-content" id="lhSetupStep4" hidden>
        <div class="lh-setup-stage-heading">
          <div class="lh-setup-eyebrow"><i class="fa-solid fa-clipboard-check"></i> Etapa 4 de 4</div>
          <h2>Resumo da configuração</h2>
          <p>Revise os cadastros selecionados. Os registros só serão criados depois que você confirmar esta etapa.</p>
        </div>
        <form id="lhSetupForm" method="post" action="<?= Helpers::url('dashboard/salvarAssistente') ?>" novalidate>
          <input type="hidden" name="configuracao_inicial_dados" data-setup-data>
          <div class="lh-setup-summary-overview">
            <div class="lh-setup-summary-highlight"><span>Total de registros selecionados</span><strong data-summary-total>0</strong></div>
            <div class="lh-setup-summary-highlight"><span>Etapas revisadas</span><strong>3</strong></div>
            <div class="lh-setup-summary-highlight"><span>Situação</span><strong class="text-success"><i class="fa-solid fa-circle-check"></i>&nbsp;Pronto para revisar</strong></div>
          </div>
          <div class="lh-setup-summary-grid">
            <?php foreach ($gruposAssistente as $grupoId => $grupo): ?>
              <article class="lh-setup-summary-card" data-summary-group="<?= $escape($grupoId) ?>">
                <h3><i class="fa-solid fa-list-check"></i> <?= $escape($grupo['titulo']) ?></h3>
                <div data-summary-list></div>
              </article>
            <?php endforeach; ?>
            <article class="lh-setup-summary-card" data-summary-group="financeiro"><h3><i class="fa-solid fa-coins"></i> Financeiro</h3><div data-summary-list></div></article>
          </div>
          <div class="lh-setup-summary-confirmation" data-acceptance-wrap>
            <input class="form-check-input" type="checkbox" id="iclivSetupAcceptance" name="configuracao_inicial_aceite" value="1" required aria-describedby="iclivSetupAcceptanceFeedback">
            <label for="iclivSetupAcceptance"><strong>Estou ciente e de acordo</strong><span>Revisei os dados e autorizo a criação dos cadastros básicos para esta empresa.</span></label>
            <div class="invalid-feedback" id="iclivSetupAcceptanceFeedback">Marque esta opção para confirmar a configuração.</div>
          </div>
        </form>
      </section>
    </div>

    <footer class="lh-setup-footer">
      <div class="lh-setup-progress" id="lhSetupProgress">Passo <strong>1</strong> de <strong>5</strong></div>
      <div class="lh-setup-actions">
        <button type="button" class="bto bto-phsa-light" id="lhSetupBack" disabled><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</button>
        <button type="button" class="bto bto-phsa-light" id="lhSetupNext">Próximo&nbsp;<i class="fa-solid fa-arrow-right"></i></button>
        <button type="submit" form="lhSetupForm" class="bto bto-phsa" data-setup-confirm hidden><i class="fa-solid fa-check"></i>&nbsp;Confirmar configuração</button>
      </div>
    </footer>
  </section>
</div>

<?php $this->push('script'); ?>
<script>
(function () {
   const total = 4;
  const etapas = Array.from({ length: total }, (_, i) => document.getElementById('lhSetupStep' + (i + 1)));
  const marcadores = Array.from(document.querySelectorAll('[data-setup-step]'));
  const back = document.getElementById('lhSetupBack');
  const next = document.getElementById('lhSetupNext');
  const confirmButton = document.querySelector('[data-setup-confirm]');
  const progress = document.getElementById('lhSetupProgress');
  let etapaAtual = 1;
  let maiorEtapaAlcancada = 1;
  if (!back || !next || etapas.some(item => !item) || !progress) return;

  function mostrarMensagem(icon, title, text) {
    if (window.Swal) {
      return Swal.fire({
        icon,
        title,
        text,
        showConfirmButton: false,
        timer: icon === 'warning' ? 4000 : 5000,
        timerProgressBar: true
      });
    }
    window.alert(text);
    return Promise.resolve();
  }

  function itensPainel(panel) { return Array.from(panel.querySelectorAll('[data-catalog-item]')); }
  function itensSelecionados(panel) { return itensPainel(panel).filter(row => row.querySelector('[data-selected]')?.checked); }
  function definirPadrao(panel) {
    if (!panel.querySelector('[data-default]')) return;
    const rows = itensSelecionados(panel);
    if (!rows.some(row => row.querySelector('[data-default]')?.checked) && rows.length) rows[0].querySelector('[data-default]').checked = true;
  }
  function atualizarPainel(panel) {
    const rows = itensPainel(panel), selected = itensSelecionados(panel);
    const tab = document.querySelector('[data-catalog-tab="' + panel.dataset.catalogPanel + '"]');
    const count = tab?.querySelector('[data-count]');
    if (count) count.textContent = String(selected.length);
    const badge = panel.querySelector('[data-panel-count]');
    if (badge) badge.textContent = selected.length + ' selecionados';
    const status = tab?.querySelector('[data-status]');
    if (status) {
      status.classList.toggle('is-valid', selected.length > 0);
      status.classList.toggle('is-invalid', selected.length === 0);
      status.className = 'fa-solid ' + (selected.length ? 'fa-circle-check is-valid' : 'fa-circle-exclamation is-invalid') + ' lh-setup-catalog-status';
    }
    rows.forEach(row => {
      const input = row.querySelector('[data-default]');
      if (input) input.disabled = !row.querySelector('[data-selected]').checked;
    });
    definirPadrao(panel);
  }
  function alternarPainel(chave, container) {
    container.querySelectorAll('[data-catalog-panel]').forEach(panel => { panel.hidden = panel.dataset.catalogPanel !== chave; });
    container.querySelectorAll('[data-catalog-tab]').forEach(tab => tab.classList.toggle('active', tab.dataset.catalogTab === chave));
  }
  function criarLinha(chave, nome, selecionado, padrao) {
    const naturalidade = chave === 'naturalidade';
    const possuiPadrao = true;
    const limiteNome = 255;
    const tr = document.createElement('tr'); tr.dataset.catalogItem = '';
    tr.innerHTML = '<td><input type="checkbox" data-selected aria-label="Incluir opção"></td>' +
      '<td><input type="text" class="form-control" data-name maxlength="' + limiteNome + '" required></td>' +
      (naturalidade ? '<td><input type="text" class="form-control" data-uf maxlength="2" aria-label="UF" placeholder="UF"></td>' : '') +
      (possuiPadrao ? '<td><input type="radio" data-default aria-label="Definir como padrão"></td>' : '') +
      '<td><button type="button" class="lh-setup-catalog-delete" data-remove aria-label="Remover opção"><i class="fa-solid fa-trash"></i></button></td>';
    tr.querySelector('[data-name]').value = nome || '';
    tr.querySelector('[data-selected]').checked = selecionado;
    if (possuiPadrao) {
      tr.querySelector('[data-default]').name = 'padrao-' + chave;
      tr.querySelector('[data-default]').checked = padrao;
    }
    tr.querySelector('[data-selected]').addEventListener('change', () => atualizarPainel(tr.closest('[data-catalog-panel]')));
    tr.querySelector('[data-default]')?.addEventListener('change', () => atualizarPainel(tr.closest('[data-catalog-panel]')));
    tr.querySelector('[data-remove]').addEventListener('click', () => { const panel = tr.closest('[data-catalog-panel]'); tr.remove(); atualizarPainel(panel); });
    return tr;
  }
  function atualizarResumo() {
    let totalSelecionado = 0;
    function adicionarResumo(lista, titulo, itens) {
      if (!lista || !itens.length) return;
      const item = document.createElement('div'); item.className = 'lh-setup-summary-item';
      const cabecalho = document.createElement('div'); cabecalho.className = 'lh-setup-summary-item-heading';
      const nome = document.createElement('span'); nome.textContent = titulo;
      const contador = document.createElement('span'); contador.className = 'lh-setup-summary-item-count';
      contador.textContent = itens.length + (itens.length === 1 ? ' selecionado' : ' selecionados');
      cabecalho.append(nome, contador); item.appendChild(cabecalho);
      const valores = document.createElement('div'); valores.className = 'lh-setup-summary-values';
      itens.forEach(dado => {
        const valor = document.createElement('span'); valor.className = 'lh-setup-summary-value';
        let texto = dado.nome || 'Sem descrição';
        if (dado.abreviacao) texto += ' (' + dado.abreviacao + ')';
        if (dado.natureza) texto += ' · ' + dado.natureza;
        if (dado.tipo) texto += ' · ' + dado.tipo;
        if (dado.formaPagamento) texto += ' · ' + dado.formaPagamento;
        if (dado.quantidade) texto += ' · ' + dado.quantidade + (Number(dado.quantidade) === 1 ? ' parcela' : ' parcelas');
        if (dado.intervalo !== undefined && dado.intervalo !== '') texto += ' · ' + dado.intervalo + ' dias';
        if (dado.saldo) texto += ' · Saldo: R$ ' + dado.saldo;
        valor.textContent = texto; valores.appendChild(valor);
      });
      item.appendChild(valores); lista.appendChild(item);
    }

    document.querySelectorAll('[data-summary-group]').forEach(group => {
      const box = group.querySelector('[data-summary-list]'); box.replaceChildren();
      const grupo = group.dataset.summaryGroup;
      document.querySelectorAll('[data-catalog-panel]').forEach(panel => {
        const body = panel.querySelector('[data-catalog-body]'), chave = body.dataset.key;
        if (body.dataset.stepGroup !== grupo) return;
        const rows = itensSelecionados(panel).map(row => ({nome: row.querySelector('[data-name]').value.trim(), abreviacao: row.querySelector('[data-abbreviation]')?.value.trim() || ''})).filter(item => item.nome);
        totalSelecionado += rows.length;
        adicionarResumo(box, panel.querySelector('h3').textContent.trim(), rows);
      });
    });
    document.querySelector('[data-summary-total]').textContent = String(totalSelecionado);
    const cadastros = Array.from(document.querySelectorAll('[data-catalog-panel]')).map(panel => {
      const body = panel.querySelector('[data-catalog-body]'), chave = body.dataset.key;
      return {painel: chave, itens: itensSelecionados(panel).map((row, ordem) => ({nome: row.querySelector('[data-name]').value.trim(), abreviacao: row.querySelector('[data-abbreviation]')?.value.trim() || '', ordem, padrao: row.querySelector('[data-default]')?.checked || false, uf: row.querySelector('[data-uf]')?.value.trim() || '', pais: 'Brasil'}))};
    });
    const cadastrosFinanceiros = Array.from(document.querySelectorAll('[data-finance-panel]')).map(panel => {
      const itens = Array.from(panel.querySelectorAll('[data-finance-item]')).filter(row => row.querySelector('[data-finance-selected]').checked).map((row, ordem) => ({
        chave: row.dataset.key || panel.dataset.financePanel + '-' + ordem,
        nome: row.querySelector('[data-finance-name]').textContent.trim(), ordem,
        padrao: row.querySelector('[data-finance-default]')?.checked || false,
        naturezaExibicao: row.querySelector('[data-finance-natureza]')?.textContent.trim() || '',
        tipo: panel.dataset.financePanel === 'conta-financeira' ? row.dataset.catalogTipo || 'Caixa' : '',
        saldoInicial: panel.dataset.financePanel === 'conta-financeira' ? row.querySelector('[data-finance-balance]')?.value || '0,00' : '',
        nivel: row.dataset.catalogNivel || '', pai: row.dataset.catalogParent || '',
        natureza: row.dataset.catalogNatureza || '',
        dependencia: row.dataset.catalogDependencyValue || '',
        formaPagamento: row.querySelector('[data-finance-dependency-label]')?.textContent.trim() || '',
        quantidade: Number(row.querySelector('[data-finance-quantity]')?.value || row.dataset.catalogQuantidade || 1),
        intervalo: Number(row.querySelector('[data-finance-interval]')?.value || row.dataset.catalogIntervalo || 0)
      }));
      totalSelecionado += itens.length;
      const nomeCadastro = panel.querySelector('h3').textContent.trim();
      const linhasResumo = itens.map(item => ({
        nome: item.nome,
        natureza: item.naturezaExibicao,
        tipo: item.tipo,
        formaPagamento: item.formaPagamento,
        quantidade: panel.dataset.financePanel === 'condicao-pgto' ? item.quantidade : '',
        intervalo: panel.dataset.financePanel === 'condicao-pgto' ? item.intervalo : undefined,
        saldo: item.saldoInicial
      }));
      adicionarResumo(document.querySelector('[data-summary-group="financeiro"] [data-summary-list]'), nomeCadastro, linhasResumo);
      return {painel: panel.dataset.financePanel, itens};
    });
    document.querySelector('[data-summary-total]').textContent = String(totalSelecionado);
    document.querySelector('[data-setup-data]').value = JSON.stringify({cadastros: cadastros.concat(cadastrosFinanceiros)});
  }
  function validarEtapa() {
    const current = etapas[etapaAtual - 1];
    if (etapaAtual === 2 || etapaAtual === 3) {
      let valido = true;
      current.querySelectorAll('[data-catalog-panel]').forEach(panel => {
        const rows = itensSelecionados(panel);
        if (!rows.length || rows.some(row => !row.querySelector('[data-name]').value.trim() || (row.querySelector('[data-abbreviation]') && !row.querySelector('[data-abbreviation]').value.trim()))) valido = false;
      });
      if (!valido) mostrarMensagem('warning', 'Revise os cadastros', 'Cada lista precisa ter ao menos uma opção selecionada, com nome e, quando aplicável, abreviação.');
      return valido;
    }
    if (etapaAtual === 4) {
      const valido = atualizarCadastrosFinanceiros();
      if (!valido) mostrarMensagem('warning', 'Revise os cadastros financeiros', 'Cada cadastro precisa ter ao menos uma opção válida. Confira também os vínculos do plano de contas e das condições de pagamento.');
      return valido;
    }
    return true;
  }
  function mostrarEtapa(numero) {
    etapaAtual = numero;
    etapas.forEach((el, i) => { el.hidden = i + 1 !== numero; });
    marcadores.forEach((el, i) => {
      const etapa = i + 1;
      const podeVoltar = etapa < numero && etapa <= maiorEtapaAlcancada;
      el.classList.toggle('active', etapa === numero);
      el.classList.toggle('is-complete', etapa < numero);
      el.classList.toggle('is-clickable', podeVoltar);
      el.setAttribute('aria-current', etapa === numero ? 'step' : 'false');
      el.setAttribute('aria-disabled', podeVoltar ? 'false' : 'true');
      el.tabIndex = podeVoltar ? 0 : -1;
    });
    progress.innerHTML = 'Passo <strong>' + numero + '</strong> de <strong>' + total + '</strong>';
    back.disabled = numero === 1;
    next.hidden = numero === total;
    confirmButton.hidden = numero !== total;
    if (numero === total) atualizarResumo();
    window.scrollTo({top: 0, behavior: 'smooth'});
  }
  document.querySelectorAll('[data-catalog-panel]').forEach(panel => {
    const chave = panel.dataset.catalogPanel;
    panel.querySelectorAll('[data-catalog-item]').forEach(row => {
      row.querySelector('[data-default]')?.setAttribute('name', 'padrao-' + chave);
      row.querySelector('[data-selected]').addEventListener('change', () => atualizarPainel(panel));
      row.querySelector('[data-default]')?.addEventListener('change', () => atualizarPainel(panel));
      row.querySelector('[data-remove]').addEventListener('click', () => { row.remove(); atualizarPainel(panel); });
    });
    panel.querySelector('[data-add]').addEventListener('click', () => { const row = criarLinha(chave, '', true, itensSelecionados(panel).length === 0); panel.querySelector('[data-catalog-body]').appendChild(row); row.querySelector('[data-name]').focus(); atualizarPainel(panel); });
    panel.querySelector('[data-check-all]').addEventListener('click', () => { itensPainel(panel).forEach(row => row.querySelector('[data-selected]').checked = true); atualizarPainel(panel); });
    panel.querySelector('[data-uncheck-all]').addEventListener('click', () => { itensPainel(panel).forEach(row => row.querySelector('[data-selected]').checked = false); atualizarPainel(panel); });
    atualizarPainel(panel);
  });
  function normalizarDescricao(valor) { return String(valor || '').trim().replace(/\s+/g, ' '); }
  function escaparHtml(valor) { return String(valor || '').replace(/[&<>\'"]/g, caractere => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[caractere])); }
  function descricaoFinanceiraExiste(panel, nome, atual) {
    const chave = normalizarDescricao(nome).toLocaleLowerCase('pt-BR');
    return Array.from(panel.querySelectorAll('[data-finance-item] [data-finance-name]')).some(label => label.closest('[data-finance-item]') !== atual && normalizarDescricao(label.textContent).toLocaleLowerCase('pt-BR') === chave);
  }
  function financeiroSelecionados(panel) { return Array.from(panel.querySelectorAll('[data-finance-item]')).filter(row => row.querySelector('[data-finance-selected]')?.checked); }
  function atualizarNaturezaFinanceira(row, natureza) {
    const entrada = natureza === 'Entrada', celula = row.querySelector('[data-finance-natureza]');
    row.dataset.catalogNatureza = natureza;
    if (celula) {
      celula.className = 'lh-setup-catalog-natureza text-' + (entrada ? 'success' : 'danger');
      celula.dataset.financeNatureza = '';
      celula.innerHTML = '<i class="fa-solid ' + (entrada ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down') + ' me-1"></i>' + (entrada ? 'Entrada' : 'Saída');
    }
  }
  function atualizarFinanceiro(panel) {
    const todos = Array.from(panel.querySelectorAll('[data-finance-item]'));
    const selecionados = financeiroSelecionados(panel);
    const tab = document.querySelector('[data-finance-tab="' + panel.dataset.financePanel + '"]');
    const painelId = panel.dataset.financePanel;
    let dependenciaValida = true, hierarquiaValida = true;
    if (painelId === 'condicao-pgto') {
      const formas = new Set(financeiroSelecionados(document.querySelector('[data-finance-panel="forma-pagamento"]')).map(row => row.dataset.key));
      todos.forEach(row => {
        const disponivel = formas.has(row.dataset.catalogDependencyValue);
        row.classList.toggle('is-unavailable', !disponivel);
        const checkbox = row.querySelector('[data-finance-selected]');
        if (!disponivel && checkbox.checked) checkbox.checked = false;
        checkbox.disabled = !disponivel;
      });
      dependenciaValida = formas.size > 0;
      const aviso = panel.querySelector('[data-finance-warning]');
      if (aviso) aviso.hidden = dependenciaValida;
    }
    if (painelId === 'plano-conta') {
      const grupos = selecionados.filter(row => row.dataset.catalogNivel === 'Grupo');
      const itens = selecionados.filter(row => row.dataset.catalogNivel === 'Item');
      const nomesGrupos = new Set(grupos.map(row => normalizarDescricao(row.querySelector('[data-finance-name]').textContent).toLocaleLowerCase('pt-BR')));
      hierarquiaValida = grupos.length > 0 && itens.length > 0
        && grupos.every(grupo => {
          const nome = normalizarDescricao(grupo.querySelector('[data-finance-name]').textContent).toLocaleLowerCase('pt-BR');
          return itens.some(item => normalizarDescricao(item.dataset.catalogParent).toLocaleLowerCase('pt-BR') === nome);
        })
        && itens.every(item => nomesGrupos.has(normalizarDescricao(item.dataset.catalogParent).toLocaleLowerCase('pt-BR')));
      panel.querySelectorAll('[data-finance-add-child]').forEach(button => { button.disabled = !button.closest('[data-finance-item]').querySelector('[data-finance-selected]').checked; });
      const addItem = panel.querySelector('[data-new-level="Item"]');
      if (addItem) addItem.disabled = grupos.length === 0;
    }
    todos.forEach(row => {
      const radio = row.querySelector('[data-finance-default]');
      if (radio) radio.disabled = !row.querySelector('[data-finance-selected]').checked || (painelId === 'plano-conta' && row.dataset.catalogNivel === 'Grupo');
    });
    const radioSelecionado = selecionados.some(row => row.querySelector('[data-finance-default]')?.checked && !row.querySelector('[data-finance-default]').disabled);
    if (!radioSelecionado) {
      const candidato = painelId === 'plano-conta'
        ? selecionados.find(row => row.dataset.catalogNivel === 'Item')
        : selecionados[0];
      if (candidato) candidato.querySelector('[data-finance-default]').checked = true;
    }
    const valido = selecionados.length > 0 && dependenciaValida && hierarquiaValida;
    if (tab) {
      tab.querySelector('[data-finance-count]').textContent = String(selecionados.length);
      const status = tab.querySelector('[data-finance-status]');
      status.classList.toggle('is-valid', valido); status.classList.toggle('is-invalid', !valido);
      status.classList.toggle('fa-circle-check', valido); status.classList.toggle('fa-circle-xmark', !valido);
      status.title = valido ? 'Pronto para cadastrar' : (painelId === 'plano-conta' && !hierarquiaValida ? 'Cada grupo selecionado precisa ter ao menos um item selecionado' : 'Nenhuma opção selecionada');
    }
    return valido;
  }
  function atualizarCadastrosFinanceiros() {
    let valido = true;
    document.querySelectorAll('[data-finance-panel]').forEach(panel => { if (!atualizarFinanceiro(panel)) valido = false; });
    return valido;
  }
  function atualizarDependenciasFinanceiras() {
    const formas = financeiroSelecionados(document.querySelector('[data-finance-panel="forma-pagamento"]'));
    const rotulos = new Map(formas.map(row => [row.dataset.key, normalizarDescricao(row.querySelector('[data-finance-name]').textContent)]));
    document.querySelectorAll('[data-finance-panel="condicao-pgto"] [data-finance-item]').forEach(row => {
      const rotulo = rotulos.get(row.dataset.catalogDependencyValue), label = row.querySelector('[data-finance-dependency-label]');
      if (rotulo && label) label.textContent = rotulo;
    });
    atualizarCadastrosFinanceiros();
  }
  function montarLinhaFinanceira(panel, nome, detalhes) {
    const id = panel.dataset.financePanel, dados = detalhes || {}, linha = document.createElement('tr');
    linha.dataset.financeItem = ''; linha.dataset.key = id + '-novo-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7); linha.dataset.customItem = '1';
    linha.dataset.catalogTipo = dados.tipo || 'Caixa'; linha.dataset.catalogNatureza = dados.natureza || 'Entrada';
    linha.dataset.catalogNivel = dados.nivel || ''; linha.dataset.catalogParent = dados.pai || '';
    linha.dataset.catalogDependencyValue = dados.dependencia?.valor || ''; linha.dataset.catalogDependencyLabel = dados.dependencia?.rotulo || '';
    linha.dataset.catalogQuantidade = String(dados.quantidade || 1); linha.dataset.catalogIntervalo = String(dados.intervalo || 0);
    if (id === 'plano-conta') {
      linha.className = 'lh-setup-plan-row ' + (dados.nivel === 'Grupo' ? 'is-group' : 'is-item');
      linha.title = (dados.nivel === 'Grupo' ? 'Grupo ' : 'Item ') + nome;
    }
    const tdUsar = document.createElement('td'), checkbox = document.createElement('input');
    checkbox.type = 'checkbox'; checkbox.dataset.financeSelected = ''; checkbox.checked = true; tdUsar.appendChild(checkbox); linha.appendChild(tdUsar);
    if (id === 'condicao-pgto') {
      const tdForma = document.createElement('td'), forma = document.createElement('span'); forma.className = 'lh-setup-catalog-dependency-value'; forma.dataset.financeDependencyLabel = ''; forma.textContent = dados.dependencia?.rotulo || '—'; tdForma.appendChild(forma); linha.appendChild(tdForma);
    }
    const tdNome = document.createElement('td'); if (id === 'plano-conta') tdNome.className = 'lh-setup-plan-description';
    const label = document.createElement('button'); label.type = 'button'; label.className = 'lh-setup-catalog-item-name'; label.dataset.financeName = ''; label.textContent = nome; tdNome.appendChild(label); linha.appendChild(tdNome);
    let saldoInput = null;
    if (id === 'conta-financeira') {
      const tdTipo = document.createElement('td'), tipo = document.createElement('span'); tipo.dataset.financeType = ''; tipo.textContent = dados.tipo || 'Caixa'; tdTipo.appendChild(tipo); linha.appendChild(tdTipo);
      const tdSaldo = document.createElement('td'); saldoInput = document.createElement('input'); saldoInput.type = 'text'; saldoInput.className = 'form-control form-control-sm text-end dinheiro lh-setup-catalog-balance-input'; saldoInput.dataset.financeBalance = ''; saldoInput.value = dados.saldo || '0,00'; saldoInput.inputMode = 'decimal'; saldoInput.setAttribute('aria-label', 'Saldo inicial'); tdSaldo.appendChild(saldoInput); linha.appendChild(tdSaldo);
    }
    if (id === 'plano-conta') {
      const tdNatureza = document.createElement('td'); tdNatureza.className = 'lh-setup-catalog-natureza'; tdNatureza.dataset.financeNatureza = ''; linha.appendChild(tdNatureza);
      atualizarNaturezaFinanceira(linha, dados.natureza || 'Entrada');
    }
    if (id === 'condicao-pgto') {
      [[ 'financeQuantity', dados.quantidade || 1, 1, 'Quantidade de parcela' ], [ 'financeInterval', dados.intervalo || 0, 0, 'Dias entre parcelas' ]].forEach(([key, value, min, labelText]) => {
        const td = document.createElement('td'), input = document.createElement('input'); input.type = 'number'; input.min = String(min); input.step = '1'; input.className = 'form-control form-control-sm text-end lh-setup-catalog-number-input'; input.dataset[key] = ''; input.value = String(value); input.setAttribute('aria-label', labelText); td.appendChild(input); linha.appendChild(td);
      });
    }
    const tdPadrao = document.createElement('td'), radio = document.createElement('input'); radio.type = 'radio'; radio.name = 'padrao-' + id; radio.dataset.financeDefault = ''; radio.disabled = id === 'plano-conta' && dados.nivel === 'Grupo'; tdPadrao.appendChild(radio); linha.appendChild(tdPadrao);
    const tdAcao = document.createElement('td');
    if (id === 'plano-conta' && dados.nivel === 'Grupo') { const add = document.createElement('button'); add.type = 'button'; add.className = 'bto bto-phsa-light btn-sm lh-setup-plan-add-item'; add.dataset.financeAddChild = ''; add.title = 'Adicionar item ao grupo'; add.innerHTML = '<i class="fa-solid fa-plus"></i><span class="d-none d-lg-inline">&nbsp;Item</span>'; tdAcao.appendChild(add); }
    const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'lh-setup-catalog-delete'; remove.dataset.financeDelete = ''; remove.title = 'Excluir opção'; remove.innerHTML = '<i class="fa-solid fa-trash"></i><span class="visually-hidden">Excluir</span>'; tdAcao.appendChild(remove); linha.appendChild(tdAcao);
    panel.querySelector('tbody').appendChild(linha);
    const padraoSelecionado = Array.from(panel.querySelectorAll('[data-finance-item]')).some(row => row.querySelector('[data-finance-selected]').checked && row.querySelector('[data-finance-default]').checked);
    if (!padraoSelecionado) radio.checked = true;
    if (window.jQuery?.fn?.mask && saldoInput) window.jQuery(linha).find('input.dinheiro').mask('#.##0,00', {reverse: true});
    atualizarDependenciasFinanceiras();
    return linha;
  }
  function abrirEditorFinanceiro(panel, linha, opcoes) {
    opcoes = opcoes || {}; const editando = Boolean(linha), id = panel.dataset.financePanel;
    const ehPlano = id === 'plano-conta', ehConta = id === 'conta-financeira', ehCondicao = id === 'condicao-pgto';
    const nomeAtual = editando ? normalizarDescricao(linha.querySelector('[data-finance-name]').textContent) : '';
    const nivel = ehPlano ? (editando ? linha.dataset.catalogNivel : opcoes.nivel || 'Grupo') : '';
    const paiAtual = ehPlano ? (editando ? linha.dataset.catalogParent : opcoes.pai || '') : '';
    const grupos = ehPlano ? Array.from(panel.querySelectorAll('[data-finance-item][data-catalog-nivel="Grupo"]')).filter(group => editando || group.querySelector('[data-finance-selected]').checked).map(group => ({nome: normalizarDescricao(group.querySelector('[data-finance-name]').textContent), natureza: group.dataset.catalogNatureza, selecionado: group.querySelector('[data-finance-selected]').checked})) : [];
    const formas = ehCondicao ? financeiroSelecionados(document.querySelector('[data-finance-panel="forma-pagamento"]')).map(row => ({valor: row.dataset.key, rotulo: normalizarDescricao(row.querySelector('[data-finance-name]').textContent)})) : [];
    if (ehPlano && !editando && nivel === 'Item' && !grupos.length) { mostrarMensagem('info', 'Grupo necessário', 'Selecione ou cadastre um grupo antes de adicionar um item.'); return; }
    if (ehCondicao && !formas.length) { mostrarMensagem('info', 'Forma de pagamento necessária', 'Cadastre ou selecione ao menos uma forma de pagamento antes de criar uma condição.'); return; }
    const naturezaAtual = editando ? linha.dataset.catalogNatureza : 'Entrada';
    const configuracao = {title: editando ? 'Editar opção' : 'Adicionar opção', showCancelButton: true, showDenyButton: editando, reverseButtons: true, focusConfirm: false, buttonsStyling: false, confirmButtonText: '<i class="fa-solid fa-check"></i>&nbsp;Salvar', denyButtonText: '<i class="fa-solid fa-trash"></i>&nbsp;Excluir', cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar', customClass: {confirmButton: 'bto bto-phsa', denyButton: 'bto bto-phsa-light text-danger', cancelButton: 'bto bto-phsa-light'}};
    configuracao.html = '<div class="text-start">' + (ehPlano ? '<div class="form-floating mb-3"><select id="iclivSetupLevel" class="form-select" ' + (editando ? 'disabled' : '') + '><option value="Grupo" ' + (nivel === 'Grupo' ? 'selected' : '') + '>Grupo</option><option value="Item" ' + (nivel === 'Item' ? 'selected' : '') + '>Item</option></select><label for="iclivSetupLevel">Nível</label></div>' : '') + '<div class="form-floating mb-3"><input id="iclivSetupName" class="form-control" value="' + escaparHtml(nomeAtual) + '" placeholder=" " autocomplete="off"><label for="iclivSetupName">Descrição</label></div>' + (ehPlano ? '<div id="iclivSetupParentWrap" class="mb-3" ' + (nivel === 'Item' ? '' : 'hidden') + '><div class="form-floating"><select id="iclivSetupParent" class="form-select">' + grupos.map(group => '<option value="' + escaparHtml(group.nome) + '" data-natureza="' + escaparHtml(group.natureza) + '" ' + (group.nome.toLocaleLowerCase('pt-BR') === paiAtual.toLocaleLowerCase('pt-BR') ? 'selected' : '') + '>' + escaparHtml(group.nome) + '</option>').join('') + '</select><label for="iclivSetupParent">Grupo</label></div><div class="form-text">O item ficará vinculado a este grupo.</div></div><div id="iclivSetupNatureWrap" class="form-floating mb-3" ' + (nivel === 'Grupo' ? '' : 'hidden') + '><select id="iclivSetupNature" class="form-select"><option value="Entrada" ' + (naturezaAtual === 'Entrada' ? 'selected' : '') + '>Entrada</option><option value="Saida" ' + (naturezaAtual === 'Saida' ? 'selected' : '') + '>Saída</option></select><label for="iclivSetupNature">Natureza</label></div>' : '') + (ehCondicao ? '<div class="form-floating mb-3"><select id="iclivSetupDependency" class="form-select">' + formas.map(form => '<option value="' + escaparHtml(form.valor) + '" ' + (form.valor === (editando ? linha.dataset.catalogDependencyValue : formas[0]?.valor) ? 'selected' : '') + '>' + escaparHtml(form.rotulo) + '</option>').join('') + '</select><label for="iclivSetupDependency">Forma de pagamento</label></div><div class="form-floating mb-3"><input id="iclivSetupQuantity" type="number" min="1" step="1" class="form-control" placeholder=" " value="' + (editando ? linha.dataset.catalogQuantidade : '1') + '"><label for="iclivSetupQuantity">Quantidade de parcela</label></div><div class="form-floating mb-3"><input id="iclivSetupInterval" type="number" min="0" step="1" class="form-control" placeholder=" " value="' + (editando ? linha.dataset.catalogIntervalo : '0') + '"><label for="iclivSetupInterval">Dias entre parcelas</label></div>' : '') + (ehConta ? '<div class="form-floating"><select id="iclivSetupAccountType" class="form-select">' + [['Caixa','Caixa'],['Conta corrente','Conta corrente'],['Conta poupanca','Conta poupança'],['Cartao','Cartão'],['Carteira digital','Carteira digital'],['Outra','Outra']].map(([value,label]) => '<option value="' + value + '" ' + (value === (editando ? linha.dataset.catalogTipo : 'Caixa') ? 'selected' : '') + '>' + label + '</option>').join('') + '</select><label for="iclivSetupAccountType">Tipo</label></div>' : '') + '</div>';
    if (ehPlano) configuracao.didOpen = () => { const level = document.getElementById('iclivSetupLevel'); level?.addEventListener('change', () => { const isItem = level.value === 'Item'; document.getElementById('iclivSetupParentWrap').hidden = !isItem; document.getElementById('iclivSetupNatureWrap').hidden = isItem; }); };
    configuracao.preConfirm = () => {
      const nome = normalizarDescricao(document.getElementById('iclivSetupName')?.value);
      if (!nome) { Swal.showValidationMessage('Informe a descrição.'); return false; }
      if (descricaoFinanceiraExiste(panel, nome, linha)) { Swal.showValidationMessage('Já existe uma opção com essa descrição.'); return false; }
      const result = {nome};
      if (ehPlano) {
        result.nivel = document.getElementById('iclivSetupLevel')?.value || nivel;
        if (result.nivel === 'Grupo') { result.pai = ''; result.natureza = document.getElementById('iclivSetupNature')?.value || 'Entrada'; }
        else { const selected = document.getElementById('iclivSetupParent')?.selectedOptions[0]; if (!selected?.value) { Swal.showValidationMessage('Selecione o grupo do item.'); return false; } result.pai = selected.value; result.natureza = selected.dataset.natureza || 'Entrada'; }
      }
      if (ehCondicao) { result.dependencia = document.getElementById('iclivSetupDependency').value; result.quantidade = Number(document.getElementById('iclivSetupQuantity').value); result.intervalo = Number(document.getElementById('iclivSetupInterval').value); if (!Number.isInteger(result.quantidade) || result.quantidade < 1) { Swal.showValidationMessage('Informe uma quantidade de parcelas válida.'); return false; } if (!Number.isInteger(result.intervalo) || result.intervalo < 0) { Swal.showValidationMessage('Informe um intervalo de dias válido.'); return false; } result.rotuloDependencia = document.getElementById('iclivSetupDependency').selectedOptions[0]?.textContent || ''; }
      if (ehConta) result.tipo = document.getElementById('iclivSetupAccountType').value;
      return result;
    };
    Swal.fire(configuracao).then(result => {
      if (result.isDenied && linha) { confirmarExclusaoFinanceira(panel, linha); return; }
      if (!result.isConfirmed) return;
      const data = result.value;
      if (editando) {
        const previousName = normalizarDescricao(linha.querySelector('[data-finance-name]').textContent), previousLevel = linha.dataset.catalogNivel;
        linha.querySelector('[data-finance-name]').textContent = data.nome;
        if (ehPlano) {
          linha.dataset.catalogNivel = data.nivel; linha.dataset.catalogParent = data.pai; atualizarNaturezaFinanceira(linha, data.natureza);
          linha.classList.toggle('is-group', data.nivel === 'Grupo'); linha.classList.toggle('is-item', data.nivel === 'Item'); linha.title = (data.nivel === 'Grupo' ? 'Grupo ' : 'Item ') + data.nome;
          if (previousLevel === 'Grupo' && data.nivel === 'Grupo') panel.querySelectorAll('[data-finance-item][data-catalog-nivel="Item"]').forEach(item => { if (normalizarDescricao(item.dataset.catalogParent).toLocaleLowerCase('pt-BR') === previousName.toLocaleLowerCase('pt-BR')) { item.dataset.catalogParent = data.nome; atualizarNaturezaFinanceira(item, data.natureza); } });
        }
        if (ehCondicao) { linha.dataset.catalogDependencyValue = data.dependencia; linha.dataset.catalogDependencyLabel = data.rotuloDependencia; linha.dataset.catalogQuantidade = String(data.quantidade); linha.dataset.catalogIntervalo = String(data.intervalo); linha.querySelector('[data-finance-dependency-label]').textContent = data.rotuloDependencia; linha.querySelector('[data-finance-quantity]').value = data.quantidade; linha.querySelector('[data-finance-interval]').value = data.intervalo; }
        if (ehConta) { linha.dataset.catalogTipo = data.tipo; linha.querySelector('[data-finance-type]').textContent = data.tipo; }
      } else {
        montarLinhaFinanceira(panel, data.nome, {nivel: data.nivel, pai: data.pai, natureza: data.natureza, tipo: data.tipo, dependencia: ehCondicao ? {valor: data.dependencia, rotulo: data.rotuloDependencia} : null, quantidade: data.quantidade, intervalo: data.intervalo});
      }
      atualizarDependenciasFinanceiras();
    });
  }
  function confirmarExclusaoFinanceira(panel, linha) {
    const nome = normalizarDescricao(linha.querySelector('[data-finance-name]').textContent);
    Swal.fire({title: 'Excluir opção?', text: 'A opção "' + nome + '" será removida deste assistente.', icon: 'warning', showCancelButton: true, reverseButtons: true, focusCancel: true, buttonsStyling: false, confirmButtonText: '<i class="fa-solid fa-trash"></i>&nbsp;Excluir', cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar', customClass: {confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light'}}).then(result => { if (!result.isConfirmed) return; linha.remove(); atualizarDependenciasFinanceiras(); });
  }
  document.querySelectorAll('[data-finance-panel]').forEach(panel => {
    const id = panel.dataset.financePanel;
    panel.addEventListener('change', event => {
      if (event.target.matches('[data-finance-selected]')) { if (id === 'forma-pagamento') atualizarDependenciasFinanceiras(); else atualizarFinanceiro(panel); }
      if (event.target.matches('[data-finance-default]')) atualizarFinanceiro(panel);
    });
    panel.addEventListener('click', event => {
      const tab = event.target.closest('[data-finance-tab]');
      if (tab) return;
      if (event.target.closest('[data-finance-check]')) { panel.querySelectorAll('[data-finance-selected]').forEach(input => { if (!input.disabled) input.checked = true; }); atualizarFinanceiro(panel); return; }
      if (event.target.closest('[data-finance-uncheck]')) { panel.querySelectorAll('[data-finance-selected]').forEach(input => { input.checked = false; }); atualizarFinanceiro(panel); return; }
      const child = event.target.closest('[data-finance-add-child]');
      if (child) { const group = child.closest('[data-finance-item]'); abrirEditorFinanceiro(panel, null, {nivel: 'Item', pai: group.querySelector('[data-finance-name]').textContent}); return; }
      const add = event.target.closest('[data-finance-add]');
      if (add) { abrirEditorFinanceiro(panel, null, {nivel: add.dataset.newLevel || 'Grupo'}); return; }
      const del = event.target.closest('[data-finance-delete]');
      if (del) { confirmarExclusaoFinanceira(panel, del.closest('[data-finance-item]')); return; }
      const name = event.target.closest('[data-finance-name]');
      if (name) abrirEditorFinanceiro(panel, name.closest('[data-finance-item]'));
    });
    panel.querySelectorAll('[data-finance-balance]').forEach(input => input.addEventListener('input', () => {}));
  });
  document.querySelectorAll('[data-finance-tab]').forEach(tab => tab.addEventListener('click', () => {
    const container = tab.closest('.lh-setup-catalog-layout'), key = tab.dataset.financeTab;
    container.querySelectorAll('[data-finance-panel]').forEach(panel => panel.hidden = panel.dataset.financePanel !== key);
    container.querySelectorAll('[data-finance-tab]').forEach(item => item.classList.toggle('active', item === tab));
  }));
  document.querySelectorAll('[data-finance-panel="condicao-pgto"] [data-finance-item]').forEach(row => { row.dataset.catalogDependencyValue = row.dataset.catalogDependencyValue || ''; });
  atualizarDependenciasFinanceiras();
  document.querySelectorAll('.lh-setup-step-content').forEach(step => {
    step.querySelectorAll('[data-catalog-tab]').forEach(tab => tab.addEventListener('click', () => alternarPainel(tab.dataset.catalogTab, step)));
  });
  next.addEventListener('click', () => {
    if (!validarEtapa()) return;
    maiorEtapaAlcancada = Math.max(maiorEtapaAlcancada, etapaAtual + 1);
    mostrarEtapa(Math.min(total, etapaAtual + 1));
  });
  back.addEventListener('click', () => mostrarEtapa(Math.max(1, etapaAtual - 1)));
  marcadores.forEach(marker => {
    const voltar = () => {
      const destino = Number(marker.dataset.setupStep);
      if (destino < etapaAtual && destino <= maiorEtapaAlcancada) mostrarEtapa(destino);
    };
    marker.addEventListener('click', voltar);
    marker.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); voltar(); }
    });
  });
  document.getElementById('lhSetupForm').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget, acceptance = form.querySelector('[name="configuracao_inicial_aceite"]');
    const acceptanceWrap = form.querySelector('[data-acceptance-wrap]');
    if (!acceptance.checked) {
      acceptanceWrap.classList.add('is-invalid');
      acceptance.classList.add('is-invalid');
      acceptance.focus();
      return;
    }
    acceptanceWrap.classList.remove('is-invalid');
    acceptance.classList.remove('is-invalid');
    const submit = document.querySelector('[data-setup-confirm]'); submit.disabled = true;
    try {
      const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'X-Requested-With': 'XMLHttpRequest'}});
      const result = await response.json();
      if (result.status) {
      const location = result.location || result.params?.location || window.location.href;
        window.location.assign(location);
        return;
      }
      const mensagem = result.text || result.message || 'Não foi possível concluir a configuração.';
      await mostrarMensagem('error', result.title || 'Erro', mensagem);
    } catch (error) {
      await mostrarMensagem('error', 'Erro', 'Falha de comunicação. Tente novamente.');
    } finally { submit.disabled = false; }
  });
  document.getElementById('iclivSetupAcceptance').addEventListener('change', event => {
    if (event.currentTarget.checked) {
      event.currentTarget.classList.remove('is-invalid');
      event.currentTarget.closest('[data-acceptance-wrap]')?.classList.remove('is-invalid');
    }
  });
  mostrarEtapa(1);
})();
</script>
<?php $this->end(); ?>
