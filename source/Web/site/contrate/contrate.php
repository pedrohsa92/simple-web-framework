<?php
  $this->layout('site::site');

  $planosContrataveis = array_values(array_filter((array) ($planos ?? []), fn($plano) => in_array($plano->pla_tipo ?? '', ['Teste', 'Comercial', 'Dedicado'], true)));
  $planoAtual = $planoSelecionado ?? ($planosContrataveis[0] ?? null);
  $etapaInicial = !empty($iniciarEtapaOrganizacao) ? 2 : 1;
  $cicloInicial = ($planoAtual->pla_tipo ?? '') === 'Teste' ? 'Teste' : (($cicloInicial ?? 'Mensal') === 'Anual' ? 'Anual' : 'Mensal');
  $textoCtaFinal = ($planoAtual->pla_tipo ?? '') === 'Comercial' ? 'Concluir contratação' : 'Ativar período de teste';
  $isConcluido = !empty($concluido) && !empty($resumo);
  $statusGatewayResumo = strtolower((string) ($resumo->plc_pagarme_status ?? ''));
  $pagamentoFalhou = $isConcluido && (($resumo->plc_statusfinanceiro ?? '') === 'Falhou' || $statusGatewayResumo === 'failed' || (is_numeric($statusGatewayResumo) && (int) $statusGatewayResumo >= 400));
  $pagamentoConfirmado = in_array(strtolower(trim((string) ($resumo->plc_statusfinanceiro ?? ''))), ['pago', 'isento'], true);
  $slugPaginaEmpresa = trim((string) ($resumo->emp_urlamigavel ?? ''));
  $urlPaginaEmpresa = $slugPaginaEmpresa !== '' && ($resumo->emp_webapp_status ?? '') === 'Publica'
    ? Helpers::url($slugPaginaEmpresa)
    : null;
  $preco = $planoAtual && ($planoAtual->pla_tipo ?? '') === 'Comercial' ? (float) ($planoAtual->pla_valormensal ?? 0) : 0;
  $beneficios = $planoAtual ? json_decode((string) ($planoAtual->pla_beneficios ?? '[]'), true) : [];
  $beneficios = is_array($beneficios) ? array_slice($beneficios, 0, 3) : [];
  $resumoPlano = static function ($descricao): string {
    $texto = trim(preg_replace('/\\s+/', ' ', strip_tags((string) $descricao)));
    return mb_strlen($texto) > 150 ? mb_substr($texto, 0, 147) . '...' : $texto;
  };
?>

<?php $this->push('style'); ?>
<style>
  .lh-contract-page { background: #f7f9fc; min-height: 72vh; padding: 7rem 0 5rem; overflow-x: hidden; }
  .site-page .lh-header { background: #fff; box-shadow: 0 .5rem 1.5rem rgba(16, 33, 63, .08); backdrop-filter: none; }
  .lh-contract-shell { max-width: 1120px; margin: 0 auto; }
  .lh-contract-card { background: #fff; border: 1px solid #e3eaf4; border-radius: 1.25rem; box-shadow: 0 18px 50px rgba(22, 58, 105, .08); }
  .lh-contract-header { background: linear-gradient(135deg, #123d83, #2c68bc); color: #fff; border-radius: 1.25rem 1.25rem 0 0; padding: 2.25rem 2.5rem; }
  .lh-contract-step { display: none; }
  .lh-contract-step.active { display: block; }
  .lh-contract-progress { display: grid; grid-template-columns: repeat(4, 1fr); gap: .55rem; margin: 1.5rem 0 2rem; padding: .55rem; border: 1px solid #e2eaf4; border-radius: 1rem; background: #f8fbff; }
  .lh-contract-progress-item { display: flex; align-items: center; justify-content: center; gap: .45rem; min-height: 2.65rem; padding: .45rem .65rem; border: 1px solid transparent; border-radius: .7rem; color: #91a0b3; font-size: .75rem; font-weight: 700; line-height: 1.2; text-align: center; }
  .lh-contract-progress-item i { display: grid; place-items: center; width: 1.55rem; height: 1.55rem; flex: 0 0 1.55rem; border-radius: 50%; background: #eaf0f7; color: #91a0b3; font-size: .7rem; }
  .lh-contract-progress-item.active { color: #2f5fa8; }
  .lh-contract-progress-item.active { border-color: #cbdcf3; background: #fff; box-shadow: 0 .3rem .8rem rgba(47, 100, 180, .08); }
  .lh-contract-progress-item.active i { background: #2f64b4; color: #fff; }
  .lh-contract-plan { height: 100%; border: 1px solid #dfe7f2; border-radius: 1rem; padding: 1.25rem; cursor: default; transition: .2s ease; }
  .lh-contract-plan:hover, .lh-contract-plan.selected { border-color: #2f64b4; box-shadow: 0 8px 24px rgba(47, 100, 180, .12); }
  .lh-contract-plan.selected { background: #f8fbff; }
  .lh-contract-plan-actions { display: grid; grid-template-columns: 1fr; gap: .5rem; width: min(100%, 24rem); flex: 0 1 24rem; margin-top: 1rem; }
  .lh-contract-plan-actions .bto { width: 100%; }
  .lh-contract-plan-actions .text-phsa { border: 0; background: transparent; }
  .lh-contract-plan-actions .lh-cycle-action { display: grid; grid-template-columns: 1.85rem minmax(0, 1fr) auto; align-items: center; gap: .65rem; width: 100%; min-width: 0; padding: .65rem .8rem; border: 1px solid var(--phsa-soft-bg); border-radius: .75rem; color: var(--phsa-soft-text); background: var(--phsa-soft-bg); text-align: left; box-shadow: none; }
  .lh-contract-plan-actions .lh-cycle-action:hover { border-color: var(--phsa-soft-hover-bg); color: var(--phsa-soft-hover-text); background: var(--phsa-soft-hover-bg); box-shadow: 0 .35rem .8rem rgba(38, 76, 133, .08); transform: translateY(-1px); }
  .lh-cycle-action > i { display: grid; width: 1.85rem; height: 1.85rem; place-items: center; border-radius: .55rem; color: #2f64b4; background: #edf3fb; font-size: .8rem; }
  .lh-cycle-action > span { font-size: .84rem; font-weight: 700; white-space: nowrap; }
  .lh-cycle-action > strong { color: #183e78; font-size: .86rem; font-weight: 800; white-space: nowrap; }
  .lh-cycle-action > strong small { color: #71829c; font-size: .72rem; font-weight: 600; }
  .lh-contract-details-action { width: auto; padding: 0; margin-bottom: 1rem; border: 0; background: transparent; }
  .lh-contract-plan-details { flex: 1 1 auto; }
  .lh-contract-plan-details h3 { margin-bottom: .35rem; }
  .lh-contract-plan-details > ul { display: none; }
  .lh-contract-logo-preview { width: 5.5rem; height: 5.5rem; object-fit: contain; border: 1px solid #dce5f0; border-radius: .85rem; background: #fff; padding: .45rem; }
  .lh-contract-logo-upload { padding: 1rem; border: 1px solid #dce5f0; border-radius: 1rem; background: #f8fbff; }
  .lh-contract-logo-preview-box { display: grid; min-height: 9rem; place-items: center; padding: .75rem; border: 1px dashed #c9d8eb; border-radius: .85rem; background: #fff; }
  .lh-contract-logo-preview-box .lh-contract-logo-preview { width: 100%; max-width: 12rem; height: 8rem; }
  .lh-contract-logo-upload .bto { width: auto; }
  #dadosLogoOrganizacao { min-height: 1.25rem; word-break: break-word; }
  #grupoCiclo { display: inline-flex !important; align-items: center; gap: .2rem; padding: .25rem; border: 1px solid #d8e4f3; border-radius: 999px; background: #f4f8fd; }
  #grupoCiclo::before { content: none; }
  #grupoCiclo button { min-width: 6rem; padding: .45rem .75rem; border: 0; border-radius: 999px; color: #71829c; background: transparent; font-size: .78rem; font-weight: 700; }
  #grupoCiclo button:hover, #grupoCiclo button.active { color: #214d8f; background: #fff; box-shadow: 0 .2rem .55rem rgba(47, 100, 180, .12); }
  .lh-contract-plan .lh-plan-price { color: #1d3f76; font-size: 1.7rem; font-weight: 800; }
  .lh-contract-plan .lh-plan-price small { font-size: .8rem; font-weight: 500; color: #71829c; }
  .lh-contract-summary { background: #f8fbff; border: 1px solid #dce8f7; border-radius: 1rem; padding: 1rem 1.25rem; }
  .lh-summary-section { height: 100%; padding: 1rem; border: 1px solid #e1eaf5; border-radius: .85rem; background: #fff; }
  .lh-summary-section-title { display: flex; align-items: center; gap: .45rem; margin-bottom: .75rem; color: #214d8f; font-size: .82rem; font-weight: 800; }
  .lh-summary-field { margin-bottom: .55rem; }
  .lh-summary-field:last-child { margin-bottom: 0; }
  .lh-summary-field small { display: block; margin-bottom: .1rem; color: #71829c; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
  .lh-summary-logo { display: block; width: 7rem; height: 7rem; object-fit: contain; padding: .5rem; border: 1px solid #dce5f0; border-radius: .8rem; background: #fff; }
  .lh-summary-cycle { display: inline-flex; gap: .2rem; padding: .2rem; border: 1px solid #d8e4f3; border-radius: 999px; background: #f4f8fd; }
  .lh-summary-cycle button { padding: .35rem .7rem; border: 0; border-radius: 999px; color: #71829c; background: transparent; font-size: .75rem; font-weight: 700; }
  .lh-summary-cycle button.active, .lh-summary-cycle button:hover { color: #214d8f; background: #fff; box-shadow: 0 .2rem .55rem rgba(47, 100, 180, .12); }
  .lh-contract-instructions { border-color: #dce8f7; background: #f8fbff; color: #536780; }
  .lh-contract-label { color: #5d6e85; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
  .lh-contract-step[data-step="2"] .col-md-8,
  .lh-contract-step[data-step="2"] .col-md-4 { flex: 0 0 auto; width: 100%; }
  .lh-contract-success { width: 100%; margin: 0 auto; }
  .lh-contract-success-icon { width: 4rem; height: 4rem; display: grid; place-items: center; border-radius: 50%; color: #fff; background: #2d9665; font-size: 1.8rem; margin: 0 auto 1rem; }
  .lh-contract-page .lh-contract-step.active > div:first-child { gap: 1rem; }
  .lh-contract-page .lh-contract-step.active > div:first-child > div:first-child { min-width: 0; }
  @media (max-width: 991px) {
    .lh-contract-plan { align-items: stretch !important; }
    .lh-contract-plan-actions { width: 100%; max-width: none; flex-basis: auto; }
  }
  @media (max-width: 767px) {
    .lh-contract-page { padding: 5rem .25rem 3rem; }
    .lh-contract-shell { width: 100%; }
    .lh-contract-card { border-radius: .9rem; }
    .lh-contract-header { padding: 1.4rem 1.1rem; }
    .lh-contract-header h1 { font-size: 1.65rem; }
    .lh-contract-header .btn { width: 100%; }
    .lh-contract-progress { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .2rem; margin: 1rem 0 1.5rem; padding: .3rem; }
    .lh-contract-progress-item { min-height: 2.35rem; padding: .3rem .15rem; gap: .2rem; font-size: .6rem; }
    .lh-contract-progress-item i { width: 1.25rem; height: 1.25rem; flex-basis: 1.25rem; font-size: .6rem; }
    .lh-contract-page .lh-contract-step.active > div:first-child { flex-direction: column; align-items: stretch !important; gap: .7rem !important; }
    #grupoCiclo { align-self: flex-start; }
    #grupoCiclo button { min-width: 5.5rem; padding: .4rem .65rem; }
    .lh-contract-plan { padding: 1rem; gap: 1rem !important; }
    .lh-contract-plan-actions { margin-top: .25rem; }
    .lh-contract-plan-actions .lh-cycle-action { grid-template-columns: 1.5rem minmax(0, 1fr) auto; gap: .4rem; padding: .55rem .6rem; }
    .lh-cycle-action > i { width: 1.5rem; height: 1.5rem; border-radius: .45rem; font-size: .7rem; }
    .lh-cycle-action > span { font-size: .75rem; }
    .lh-cycle-action > strong { font-size: .78rem; }
    .lh-cycle-action > strong small { font-size: .66rem; }
    .lh-contract-logo-upload { padding: .75rem; }
    .lh-contract-logo-preview-box { min-height: 7rem; }
    .lh-contract-logo-preview-box .lh-contract-logo-preview { height: 6rem; }
    .lh-contract-step .row > [class*="col-"] { min-width: 0; }
  }
  @media (max-width: 400px) {
    .lh-contract-progress-item { font-size: .55rem; }
    .lh-contract-progress-item i { display: none; }
    #grupoCiclo button { min-width: 5.1rem; }
    .lh-cycle-action > span { font-size: .7rem; }
    .lh-cycle-action > strong { font-size: .72rem; }
  }
  .lh-legal-consent { padding: 1rem 1.15rem; border: 1px solid #dce8f7; border-radius: .85rem; background: #fff; }
  .lh-legal-consent .form-check { margin-bottom: .75rem; }
  .lh-legal-consent .form-check:last-child { margin-bottom: 0; }
  .lh-legal-consent .form-check-label { color: #536780; line-height: 1.55; }
  .lh-legal-link { border: 0; padding: 0; color: var(--phsa-color, #2f64b4); background: transparent; font-weight: 700; text-decoration: none !important; }
  .lh-legal-link:hover, .lh-legal-link:focus { color: var(--phsa-color, #214d8f); text-decoration: none !important; }

  /* Identidade icliv: clareza para a empresa e acolhimento para quem começa. */
  .site-page .ic-header { background: rgba(255, 255, 255, .96); box-shadow: 0 .55rem 1.5rem rgba(25, 53, 45, .08); backdrop-filter: blur(16px); }
  .lh-contract-page { background: linear-gradient(135deg, #f3fbf6, #fffdf8); color: #19352d; }
  .lh-contract-card { border-color: #dcebe2; border-radius: 1.5rem; box-shadow: 0 1.5rem 4rem rgba(25, 53, 45, .1); }
  .lh-contract-header { position: relative; overflow: hidden; border-radius: 1.5rem 1.5rem 0 0; background: linear-gradient(125deg, #17634c, #2d8a68); }
  .lh-contract-header::after { content: "\f1b0"; position: absolute; right: 2rem; bottom: -3.7rem; color: rgba(255, 255, 255, .08); font: 900 12rem/1 "Font Awesome 6 Free"; transform: rotate(-16deg); }
  .lh-contract-header > div { position: relative; z-index: 1; }
  .ic-contract-brand { display: inline-flex; align-items: center; gap: .75rem; margin-bottom: 1.1rem; color: #fff; font-size: 1.15rem; font-weight: 900; letter-spacing: -.04em; }
  .ic-contract-brand img { width: 104px; height: 42px; object-fit: contain; object-position: left center; filter: brightness(0) invert(1); }
  .lh-contract-progress { border-color: #dcebe2; background: #f3fbf6; }
  .lh-contract-progress-item.active { color: #17634c; border-color: #b9ddc8; }
  .lh-contract-progress-item.active i { background: #2d8a68; }
  .lh-contract-plan { border-color: #dcebe2; }
  .lh-contract-plan:hover, .lh-contract-plan.selected { border-color: #2d8a68; box-shadow: 0 .7rem 1.8rem rgba(45, 138, 104, .14); }
  .lh-contract-plan.selected { background: #f3fbf6; }
  .lh-contract-plan .text-phsa, .lh-contract-page .text-phsa { color: #17634c !important; }
  .lh-contract-plan .text-phsa:hover, .lh-contract-page .text-phsa:hover { color: #2d8a68 !important; }
  .lh-contract-plan .badge.text-primary { color: #17634c !important; background: #dff3e8 !important; }
  .lh-contract-plan .lh-plan-price { color: #17634c; }
  .lh-contract-plan .lh-cycle-action .lh-cycle-value { color: #17634c; }
  .lh-contract-plan .lh-cycle-action:hover .lh-cycle-value,
  .lh-contract-plan .lh-cycle-action:focus-visible .lh-cycle-value { color: #fff; }
  .lh-contract-plan .lh-cycle-action:hover .lh-cycle-value small,
  .lh-contract-plan .lh-cycle-action:focus-visible .lh-cycle-value small { color: #fff; }
  .lh-contract-page .form-control:focus, .lh-contract-page .form-select:focus { border-color: #2d8a68; box-shadow: 0 0 0 .2rem rgba(45, 138, 104, .14); }
  .lh-contract-page .bto.bto-phsa:visited,
  .lh-contract-page .bto.bto-phsa { border-color: transparent; color: #fff !important; background-color: var(--phsa-primary-bg, #2d8a68); background-image: none !important; box-shadow: 0 .7rem 1.4rem rgba(45, 138, 104, .2); transition: color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease; }
  .lh-contract-page .bto.bto-phsa:hover,
  .lh-contract-page .bto.bto-phsa:focus-visible { color: #fff !important; background-color: var(--phsa-primary-hover-bg, #17634c); background-image: none !important; box-shadow: 0 .8rem 1.6rem rgba(45, 138, 104, .24); transform: translateY(-1px); }
  .lh-contract-page .bto.bto-phsa-light { border-color: #b9ddc8; color: #17634c; background: #f3fbf6; }
  .lh-contract-page .bto.bto-phsa-light:hover { border-color: #2d8a68; color: #fff; background: #2d8a68; }
  .lh-contract-summary, .lh-contract-instructions { border-color: #dcebe2; background: #f3fbf6; }
  .lh-contract-palette { padding: 1rem; border: 1px solid #dcebe2; border-radius: 1rem; background: #f3fbf6; }
  .lh-contract-palette-picker { display: flex; align-items: center; gap: .85rem; padding: .8rem; border: 1px solid #dce5f0; border-radius: .8rem; background: #fff; }
  .lh-contract-palette-picker input[type="color"] { width: 3.1rem; height: 2.6rem; padding: .2rem; border: 1px solid #b9ddc8; border-radius: .55rem; background: #fff; cursor: pointer; }
  .lh-contract-palette-code { color: #17634c; font-weight: 800; letter-spacing: .04em; }
  .lh-contract-palette-preview { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .6rem; margin-top: .85rem; }
  .lh-contract-palette-swatch { min-height: 4rem; display: flex; flex-direction: column; justify-content: center; padding: .75rem; border-radius: .7rem; text-align: center; font-size: .8rem; font-weight: 800; }
  .lh-contract-cnpj-field, .lh-contract-validation-field { position: relative; }
  .lh-contract-cnpj-field .form-control, .lh-contract-validation-field .form-control { padding-right: 2.75rem; background-image: none; }
  .lh-contract-cnpj-status { position: absolute; z-index: 4; top: 50%; right: .95rem; display: grid; place-items: center; width: 1.25rem; height: 1.25rem; transform: translateY(-50%); pointer-events: none; font-size: 1.05rem; }
  .lh-contract-cnpj-status.valid { color: #198754; }
  .lh-contract-cnpj-status.invalid { color: #dc3545; }
  .lh-contract-cnpj-status.pending { color: #6c757d; }
  .lh-contract-logo-recommendation { display: flex; align-items: flex-start; gap: .55rem; padding: .75rem; border: 1px solid #b9ddc8; border-radius: .75rem; color: #17634c; background: #f3fbf6; }
  @media (max-width: 575px) { .lh-contract-palette-preview { grid-template-columns: 1fr; } }
  .lh-summary-section-title { color: #17634c; }
  .lh-summary-logo { border-color: #dcebe2; background: #fff; }
  .lh-contract-success-icon { background: #2d8a68; }
  .lh-contract-logo-upload { border-color: #dcebe2; background: #f3fbf6; }
  .lh-contract-logo-preview-box { border-color: #b9ddc8; }
  .lh-legal-link { color: #2d8a68; }
  .lh-legal-link:hover, .lh-legal-link:focus { color: #17634c; }
  @media (max-width: 575.98px) { .ic-contract-brand img { width: 92px; } }
</style>
<?php $this->end(); ?>

<main class="lh-contract-page">
  <div class="container lh-contract-shell">
    <?php if ($isConcluido): ?>
      <div class="lh-contract-card lh-contract-success overflow-hidden">
        <div class="p-4 p-md-5">
          <div class="text-center">
            <div class="lh-contract-success-icon <?= $pagamentoFalhou ? 'bg-warning text-dark' : '' ?>"><i class="fa-solid <?= $pagamentoFalhou ? 'fa-triangle-exclamation' : 'fa-check' ?>"></i></div>
            <span class="badge <?= $pagamentoFalhou ? 'text-bg-warning' : 'text-bg-success' ?> mb-3"><?= $pagamentoFalhou ? 'Cadastro preservado' : 'Cadastro concluído' ?></span>
            <h1 class="h3 text-phsa mb-2"><?= $pagamentoFalhou ? 'Sua empresa foi criada.' : 'Sua empresa já está preparada.' ?></h1>
            <p class="text-muted mb-4">A contratação do plano <strong><?= htmlspecialchars($resumo->pla_nome ?? '', ENT_QUOTES, 'UTF-8') ?></strong> foi registrada com sucesso.</p>
          </div>

          <div class="lh-contract-summary text-start mb-4">
            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <div class="lh-summary-section h-100">
                  <div class="lh-summary-section-title"><i class="fa-solid fa-building"></i>Empresa</div>
                  <div class="d-flex align-items-start gap-3">
                    <img class="lh-summary-logo" src="<?= Helpers::storage($resumo->org_logo ?? null, 'storage/global/icliv.png') ?>" alt="Logo da empresa">
                    <div class="flex-grow-1">
                      <div class="lh-summary-field"><small>CNPJ</small><?= htmlspecialchars($resumo->org_cnpjcpf ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="lh-summary-field"><small>Razão social</small><?= htmlspecialchars($resumo->org_razaosocial ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="lh-summary-section h-100">
                  <div class="lh-summary-section-title"><i class="fa-solid fa-building"></i>Primeira empresa</div>
                  <div class="lh-summary-field"><small>CNPJ</small><?= htmlspecialchars($resumo->emp_cnpjcpf ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="lh-summary-field"><small>Razão social</small><?= htmlspecialchars($resumo->emp_razaosocial ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="lh-summary-section h-100">
                  <div class="lh-summary-section-title"><i class="fa-solid fa-user-shield"></i>Administrador</div>
                  <div class="lh-summary-field"><small>CPF</small><?= htmlspecialchars($resumo->usu_cnpjcpf ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="lh-summary-field"><small>Nome completo</small><?= htmlspecialchars($resumo->usu_razaosocial ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="lh-summary-field"><small>E-mail</small><?= htmlspecialchars($resumo->usu_email ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="lh-summary-field"><small>Celular</small><?= htmlspecialchars($resumo->usu_celular ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="lh-summary-section h-100">
                  <div class="lh-summary-section-title"><i class="fa-solid fa-layer-group"></i>Contratação</div>
                  <div class="lh-summary-field"><small>Plano</small><strong><?= htmlspecialchars($resumo->pla_nome ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></strong></div>
                  <div class="lh-summary-field"><small>Periodicidade</small><?= htmlspecialchars($resumo->plc_ciclo ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="lh-summary-field"><small>Valor</small><strong>R$ <?= number_format((float) ($resumo->plc_valor ?? 0), 2, ',', '.') ?></strong></div>
                  <div class="lh-summary-field"><small>Situação financeira</small><span class="badge text-bg-<?= $pagamentoFalhou ? 'danger' : (($resumo->plc_statusfinanceiro ?? '') === 'Pago' ? 'success' : 'secondary') ?>"><?= htmlspecialchars($resumo->plc_statusfinanceiro ?? 'Em aberto', ENT_QUOTES, 'UTF-8') ?></span></div>
                </div>
              </div>
            </div>
          </div>

          <?php if ($pagamentoFalhou): ?>
            <div class="alert alert-warning text-start"><i class="fa-solid fa-circle-exclamation me-2"></i><strong>O link de pagamento não foi gerado.</strong> Seu cadastro foi preservado, mas o pagamento ainda não foi concluído. Use o botão abaixo para solicitar uma nova tentativa.</div>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <button type="button" class="bto bto-phsa" id="btnTentarPagamento" data-plc-id="<?= (int) $resumo->plc_id ?>" data-token="<?= htmlspecialchars($tokenConfirmacao, ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-rotate me-2"></i>Tentar gerar link novamente</button>
              <a class="bto bto-phsa" href="<?= Helpers::url('login') ?>"><i class="fa-solid fa-right-to-bracket me-2"></i>Ir para o login</a>
            </div>
          <?php elseif (!empty($resumo->plc_pagarme_url)): ?>
            <div class="alert lh-contract-instructions text-start"><i class="fa-solid fa-credit-card me-2 text-phsa"></i><strong>Próxima etapa:</strong> acesse o link seguro, escolha a forma de pagamento e conclua o pagamento. Depois, entre no icliv com o e-mail e a senha cadastrados para acompanhar o acesso da sua empresa.</div>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <a class="bto bto-phsa px-4" href="<?= htmlspecialchars($resumo->plc_pagarme_url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Pagar agora</a>
              <a class="bto bto-phsa px-4" href="<?= Helpers::url('login') ?>"><i class="fa-solid fa-right-to-bracket me-2"></i>Ir para o login</a>
              <?php if ($pagamentoConfirmado && $urlPaginaEmpresa): ?>
                <a class="bto bto-phsa px-4" href="<?= htmlspecialchars($urlPaginaEmpresa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Visitar minha página</a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="alert lh-contract-instructions text-start"><i class="fa-solid fa-flask me-2 text-success"></i><strong>Próxima etapa:</strong> seu período de teste está pronto para começar. Entre no icliv com o e-mail e a senha cadastrados para acessar sua empresa.</div>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <a class="bto bto-phsa px-4" href="<?= Helpers::url('login') ?>"><i class="fa-solid fa-right-to-bracket me-2"></i>Entrar no icliv</a>
              <?php if ($pagamentoConfirmado && $urlPaginaEmpresa): ?>
                <a class="bto bto-phsa px-4" href="<?= htmlspecialchars($urlPaginaEmpresa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Visitar minha página</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="mt-4 text-center"><a href="<?= Helpers::url('/') ?>" class="text-phsa text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Voltar ao site</a></div>
        </div>
      </div>
    <?php elseif (!empty($planoNaoDisponivel)): ?>
      <div class="lh-contract-card p-4 p-md-5 text-center"><div class="lh-contract-success-icon bg-primary"><i class="fa-solid fa-comments"></i></div><h1 class="h3 text-phsa">Vamos conversar sobre o ambiente dedicado</h1><p class="text-muted mb-4">Esse plano é personalizado para a realidade da sua empresa. Nossa equipe comercial vai entender sua necessidade e apresentar a melhor configuração.</p><a class="btn bto bto-phsa" href="mailto:comercial@icliv.com.br"><i class="fa-solid fa-envelope me-2"></i>Falar com a equipe comercial</a><div class="mt-4"><a href="<?= Helpers::url('/') ?>#planos" class="text-phsa text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Voltar aos planos</a></div></div>
    <?php elseif (!$planoAtual): ?>
      <div class="lh-contract-card p-5 text-center"><h1 class="h3 text-phsa">Nenhum plano disponível</h1><p class="text-muted">No momento não há plano aberto para contratação.</p></div>
    <?php else: ?>
      <div class="lh-contract-card overflow-hidden">
        <div class="lh-contract-header">
          <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div><div class="ic-contract-brand"><img src="<?= Helpers::storage('storage/global/icliv.png') ?>" alt="icliv"></div><div class="text-uppercase small fw-bold opacity-75 mb-2">Comece sua jornada</div><h1 class="h2 mb-2">Contrate o icliv</h1><p class="mb-0 opacity-75">Um cadastro simples para sua empresa começar com clareza e segurança.</p></div>
            <a href="<?= Helpers::url('/') ?>#planos" class="btn btn-outline-light"><i class="fa-solid fa-arrow-left me-2"></i>Voltar aos planos</a>
          </div>
        </div>

        <div class="p-3 p-md-5">
            <div class="lh-contract-progress" id="contrateProgresso">
            <div class="lh-contract-progress-item <?= $etapaInicial >= 1 ? 'active' : '' ?>"><i class="fa-solid fa-layer-group"></i>Plano</div><div class="lh-contract-progress-item <?= $etapaInicial >= 2 ? 'active' : '' ?>"><i class="fa-solid fa-building"></i>Empresa</div><div class="lh-contract-progress-item <?= $etapaInicial >= 3 ? 'active' : '' ?>"><i class="fa-solid fa-user-shield"></i>Usuário principal</div><div class="lh-contract-progress-item <?= $etapaInicial >= 4 ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-check"></i>Resumo</div>
          </div>

          <form id="formContratacao" method="post" action="<?= Helpers::url('contrate/salvar') ?>" enctype="multipart/form-data" autocomplete="off" novalidate>
            <input type="hidden" name="pla_id" id="pla_id" value="<?= (int) ($planoAtual->pla_id ?? 0) ?>">
            <input type="hidden" name="plc_ciclo" id="plc_ciclo" value="<?= htmlspecialchars($cicloInicial, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="org_logo" id="org_logo">
            <input type="hidden" id="org_nomefantasia">
            <input type="hidden" id="usu_nomefantasia">

            <section class="lh-contract-step <?= $etapaInicial === 1 ? 'active' : '' ?>" data-step="1">
              <div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h4 text-phsa mb-1">Escolha seu plano</h2><p class="text-muted mb-0">Você poderá revisar tudo antes de concluir.</p></div><div class="btn-group" role="group" id="grupoCiclo"><button type="button" class="btn btn-outline-primary active" data-ciclo="Mensal">Mensal</button><button type="button" class="btn btn-outline-primary" data-ciclo="Anual">Anual</button></div></div>
              <div class="row g-3" id="listaPlanosContrate">
                <?php foreach ($planosContrataveis as $plano): $selecionado = (int) $plano->pla_id === (int) $planoAtual->pla_id; $beneficiosPlano = json_decode((string) ($plano->pla_beneficios ?? '[]'), true); ?>
                  <div class="col-12">
                    <div class="lh-contract-plan d-flex flex-column flex-lg-row align-items-lg-center gap-3 <?= $selecionado ? 'selected' : '' ?>" data-plano-id="<?= (int) $plano->pla_id ?>" data-tipo="<?= htmlspecialchars($plano->pla_tipo, ENT_QUOTES, 'UTF-8') ?>" data-mensal="<?= (float) ($plano->pla_valormensal ?? 0) ?>" data-anual="<?= (float) ($plano->pla_valoranual ?? 0) ?>" data-dias="<?= (int) ($plano->pla_dias_teste ?? 0) ?>" data-descricao="<?= htmlspecialchars((string) ($plano->pla_descricao ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-beneficios="<?= htmlspecialchars(json_encode(is_array($beneficiosPlano) ? $beneficiosPlano : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>">
                      <div class="lh-contract-plan-details">
                      <span class="badge rounded-pill text-bg-light text-primary mb-3"><?= htmlspecialchars($plano->pla_rotulo ?: $plano->pla_tipo, ENT_QUOTES, 'UTF-8') ?></span>
                      <h3 class="h5 text-phsa mb-2"><?= htmlspecialchars($plano->pla_nome, ENT_QUOTES, 'UTF-8') ?></h3>
                      <p class="small text-muted mb-2"><?= htmlspecialchars($plano->pla_descricaoresumida ?? $resumoPlano($plano->pla_descricao ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                      <button type="button" class="bto text-phsa js-detalhes-plano lh-contract-details-action"><i class="fa-solid fa-circle-info"></i>&nbsp;Saiba mais</button>
                      <?php if ($plano->pla_tipo !== 'Comercial'): ?><div class="lh-plan-price" data-price><?= $plano->pla_tipo === 'Teste' ? ((int) ($plano->pla_dias_teste ?? 30) . ' dias grátis') : 'Exclusivo <small>ambiente dedicado</small>' ?></div><?php endif; ?>
                      <?php if (is_array($beneficiosPlano)): ?><ul class="list-unstyled small text-muted mt-3 mb-0"><?php foreach (array_slice($beneficiosPlano, 0, 3) as $beneficio): ?><li class="mb-1"><i class="fa-solid fa-check text-success me-1"></i><?= htmlspecialchars((string) $beneficio, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><?php endif; ?>
                      </div>
                      <div class="lh-contract-plan-actions">
                        <?php if ($plano->pla_tipo === 'Comercial'): ?>
                          <button type="button" class="bto bto-phsa-light lh-cycle-action js-selecionar-plano" data-ciclo="Mensal"><i class="fa-solid fa-arrow-right"></i><span>Escolher plano</span><strong class="lh-cycle-value">R$ <?= number_format((float) ($plano->pla_valormensal ?? 0), 2, ',', '.') ?><small>/mês</small></strong></button>
                        <?php else: ?>
                          <?php if ($plano->pla_tipo === 'Dedicado'): ?>
                            <a class="bto bto-phsa-light" href="<?= Helpers::url('contrate/' . (int) $plano->pla_id) ?>"><i class="fa-solid fa-comments"></i>&nbsp;<?= htmlspecialchars($plano->pla_textobotao ?: 'Falar com a equipe', ENT_QUOTES, 'UTF-8') ?></a>
                          <?php else: ?>
                            <button type="button" class="bto bto-phsa-light js-selecionar-plano" data-ciclo="Teste"><i class="fa-solid fa-rocket"></i>&nbsp;<?= htmlspecialchars($plano->pla_textobotao ?: 'Começar teste', ENT_QUOTES, 'UTF-8') ?></button>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>

            <section class="lh-contract-step <?= $etapaInicial === 2 ? 'active' : '' ?>" data-step="2">
              <h2 class="h4 text-phsa mb-1">Sobre sua organização</h2><p class="text-muted mb-4">A primeira empresa será criada com estes mesmos dados. Depois você poderá adicionar outras.</p>
              <div class="row g-3">
                <div class="col-md-6"><div class="form-floating lh-contract-cnpj-field"><input class="form-control" id="org_cnpjcpf" name="org_cnpjcpf" placeholder="CNPJ" inputmode="numeric" required aria-describedby="feedbackCnpj"><label for="org_cnpjcpf">CNPJ *</label><span class="lh-contract-cnpj-status" id="statusCnpj" aria-live="polite" hidden></span><div class="invalid-feedback" id="feedbackCnpj">Informe um CNPJ válido.</div><div class="valid-feedback">CNPJ disponível para cadastro.</div></div></div>
                <div class="col-md-6"><div class="form-floating"><input class="form-control" id="org_razaosocial" name="org_razaosocial" placeholder="Razão social" required><label for="org_razaosocial">Razão social *</label><div class="invalid-feedback">Informe a razão social.</div></div></div>
                <div class="col-12">
                  <div class="lh-contract-palette">
                    <label class="form-label small fw-semibold mb-2" for="org_cor_principal">Cor principal da organização *</label>
                    <div class="lh-contract-palette-picker">
                      <input type="color" id="org_cor_principal" name="org_cor_principal" value="#2d8a68" required aria-describedby="orientacaoCorPrincipal">
                      <div><div class="lh-contract-palette-code" id="org_cor_principal_codigo">#2D8A68</div><div class="small text-muted" id="orientacaoCorPrincipal">Escolha uma cor marcante. Evite branco e tons muito claros para garantir contraste e boa leitura.</div></div>
                    </div>
                    <div class="lh-contract-palette-preview" aria-label="Pré-visualização da paleta">
                      <div class="lh-contract-palette-swatch" id="previewCorSuave"><span>Suave</span><small>Plano de fundo e texto</small></div>
                      <div class="lh-contract-palette-swatch" id="previewCorPrincipal"><span>Principal</span><small>Plano de fundo e texto</small></div>
                      <div class="lh-contract-palette-swatch" id="previewCorForte"><span>Forte</span><small>Plano de fundo e texto</small></div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label small fw-semibold">Logo da organização *</label>
                  <div class="lh-contract-logo-upload row g-3 align-items-center">
                    <div class="col-sm-4">
                      <div class="lh-contract-logo-preview-box"><img id="previewLogoOrganizacao" class="lh-contract-logo-preview" src="<?= Helpers::storage(null, 'storage/global/icliv.png') ?>" alt="Pré-visualização do logo"></div>
                    </div>
                    <div class="col-sm-8">
                      <div class="lh-contract-logo-recommendation small mb-3"><i class="fa-solid fa-lightbulb"></i><div><strong>Recomendação:</strong> envie uma imagem quadrada, na proporção <strong>4:4</strong>, com até <strong>2 MB</strong>. Assim, o logo terá melhor encaixe nas telas do sistema.</div></div>
                      <p class="small text-muted mb-2">Envie uma imagem que represente sua empresa. Recomendamos PNG, JPG ou WEBP, preferencialmente com fundo transparente.</p>
                      <button type="button" class="bto bto-phsa-light" id="btnSelecionarLogoOrganizacao"><i class="fa-solid fa-image me-2"></i>Selecionar logo</button>
                      <div class="small text-muted mt-2" id="dadosLogoOrganizacao">Nenhum arquivo selecionado.</div>
                    </div>
                  </div>
                  <input class="d-none" type="file" id="org_logo_file" accept="image/png,image/jpeg,image/webp" required>
                  <div class="invalid-feedback">Selecione a logo da organização.</div>
                </div>
              </div>
              <div class="d-flex justify-content-between mt-4"><button type="button" class="bto bto-phsa-light btn-anterior"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</button><button type="button" class="btn bto bto-phsa btn-proximo">Continuar <i class="fa-solid fa-arrow-right ms-2"></i></button></div>
            </section>

            <section class="lh-contract-step" data-step="3">
              <h2 class="h4 text-phsa mb-1">Crie o acesso principal</h2><p class="text-muted mb-4">Este usuário será o primeiro administrador da empresa.</p>
              <div class="row g-3">
                <div class="col-md-6 mb-3"><div class="form-floating lh-contract-validation-field"><input class="form-control" id="usu_cnpjcpf" name="usu_cnpjcpf" placeholder="CPF" inputmode="numeric" maxlength="14" required aria-describedby="feedbackCpf"><label for="usu_cnpjcpf">CPF *</label><span class="lh-contract-cnpj-status" id="statusCpf" aria-live="polite" hidden></span><div class="invalid-feedback" id="feedbackCpf">Informe um CPF válido.</div><div class="valid-feedback">CPF disponível para cadastro.</div></div></div>
                <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control" id="usu_razaosocial" name="usu_razaosocial" placeholder="Nome completo" required><label for="usu_razaosocial">Nome completo *</label><div class="invalid-feedback">Informe o nome completo.</div></div></div>
                <div class="col-md-6 mb-3"><div class="form-floating lh-contract-validation-field"><input class="form-control" type="email" id="usu_email" name="usu_email" placeholder="E-mail" required aria-describedby="feedbackEmail"><label for="usu_email">E-mail *</label><span class="lh-contract-cnpj-status" id="statusEmail" aria-live="polite" hidden></span><div class="invalid-feedback" id="feedbackEmail">Informe um e-mail válido.</div><div class="valid-feedback">E-mail disponível para cadastro.</div></div></div>
                <div class="col-md-6 mb-3"><div class="form-floating lh-contract-validation-field"><input class="form-control" id="usu_celular" name="usu_celular" placeholder="Celular" inputmode="tel" required aria-describedby="feedbackCelular"><label for="usu_celular">Celular *</label><span class="lh-contract-cnpj-status" id="statusCelular" aria-live="polite" hidden></span><div class="invalid-feedback" id="feedbackCelular">Informe um celular válido.</div><div class="valid-feedback">Celular disponível para cadastro.</div></div></div>
                <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control" type="password" id="usu_senha" name="usu_senha" placeholder="Senha" minlength="8" required><label for="usu_senha">Senha *</label><div class="invalid-feedback">Informe uma senha com pelo menos 8 caracteres.</div></div></div>
                <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control" type="password" id="usu_senhaconfirmacao" name="usu_senhaconfirmacao" placeholder="Confirmar senha" minlength="8" required><label for="usu_senhaconfirmacao">Confirmar senha *</label><div class="invalid-feedback">As senhas precisam ser iguais.</div></div></div>
              </div>
              <div class="form-text mt-3"><i class="fa-solid fa-shield-halved me-1 text-success"></i>A senha precisa ter 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial.</div>
              <div class="d-flex justify-content-between mt-4"><button type="button" class="bto bto-phsa-light btn-anterior"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</button><button type="button" class="btn bto bto-phsa btn-proximo">Continuar <i class="fa-solid fa-arrow-right ms-2"></i></button></div>
            </section>

            <section class="lh-contract-step" data-step="4">
              <h2 class="h4 text-phsa mb-1">Revise e confirme</h2><p class="text-muted mb-4">Confira os dados antes de criar sua empresa.</p>
              <div class="lh-contract-summary mb-3" id="resumoContratacao"></div>
              <div class="lh-legal-consent mb-3" aria-label="Consentimentos da contratação">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="plc_aceite" name="plc_aceite" value="1" required>
                  <label class="form-check-label" for="plc_aceite">
                    Li e aceito os
                    <button type="button" class="lh-legal-link js-abrirDocumentoLegal" data-documento="termosuso">Termos de Uso</button>
                    e o
                    <button type="button" class="lh-legal-link js-abrirDocumentoLegal" data-documento="avisoprivacidade">Aviso de Privacidade do icliv</button>.
                  </label>
                  <div class="invalid-feedback">Leia e aceite os Termos de Uso e o Aviso de Privacidade.</div>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="usu_newsletter" name="usu_newsletter" value="1">
                  <label class="form-check-label" for="usu_newsletter">Aceito receber novidades e comunicações do icliv.</label>
                </div>
              </div>
              <div class="alert lh-contract-instructions small" id="orientacoesContratacao"><i class="fa-solid fa-lock me-2 text-success"></i>Seus dados serão usados somente para criar e liberar o acesso à sua conta icliv.</div>
              <div class="d-flex justify-content-between mt-4"><button type="button" class="bto bto-phsa-light btn-anterior"><i class="fa-solid fa-arrow-left me-2"></i>Editar dados</button><button type="submit" class="btn bto bto-phsa" id="btnFinalizar"><span id="btnFinalizarTexto"><?= htmlspecialchars($textoCtaFinal, ENT_QUOTES, 'UTF-8') ?></span> <i class="fa-solid fa-check ms-2"></i></button></div>
            </section>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php $this->push('script'); ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formContratacao');
    const campoCnpjOrganizacao = document.getElementById('org_cnpjcpf');
    const previewSlug = document.getElementById('previsualizacaoSlug');
    if (campoCnpjOrganizacao && previewSlug) {
      const basePublica = <?= json_encode(rtrim(Helpers::url(''), '/'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      const atualizarPreviewSlug = function () {
        const cnpj = campoCnpjOrganizacao.value.replace(/\D/g, '').slice(0, 50);
        previewSlug.textContent = cnpj
          ? 'Endereço inicial da página: ' + basePublica + '/' + cnpj + ' · você poderá personalizá-lo depois.'
          : 'O endereço público da empresa começará com o CNPJ, que poderá ser personalizado depois.';
      };
      campoCnpjOrganizacao.addEventListener('input', atualizarPreviewSlug);
      atualizarPreviewSlug();
    }
    const corOrganizacao = document.getElementById('org_cor_principal');
    const normalizarHex = function (valor) { return /^#[a-f\d]{6}$/i.test(valor || '') ? valor.toLowerCase() : '#2d8a68'; };
    const hexRgb = function (hex) { return [1, 3, 5].map(indice => parseInt(hex.slice(indice, indice + 2), 16)); };
    const rgbHex = function (rgb) { return '#' + rgb.map(valor => Math.max(0, Math.min(255, Math.round(valor))).toString(16).padStart(2, '0')).join(''); };
    const rgbHsl = function (rgb) {
      const [r, g, b] = rgb.map(value => value / 255);
      const max = Math.max(r, g, b), min = Math.min(r, g, b), delta = max - min;
      const l = (max + min) / 2;
      let h = 0, s = 0;
      if (delta > 0) {
        s = delta / (1 - Math.abs(2 * l - 1));
        if (max === r) h = 60 * (((g - b) / delta) % 6);
        else if (max === g) h = 60 * ((b - r) / delta + 2);
        else h = 60 * ((r - g) / delta + 4);
        if (h < 0) h += 360;
      }
      return {h, s, l};
    };
    const hslRgb = function ({h, s, l}) {
      const c = (1 - Math.abs(2 * l - 1)) * s;
      const x = c * (1 - Math.abs((h / 60) % 2 - 1));
      const m = l - c / 2;
      let rgb;
      if (h < 60) rgb = [c, x, 0]; else if (h < 120) rgb = [x, c, 0]; else if (h < 180) rgb = [0, c, x]; else if (h < 240) rgb = [0, x, c]; else if (h < 300) rgb = [x, 0, c]; else rgb = [c, 0, x];
      return rgb.map(value => (value + m) * 255);
    };
    const textoContraste = function (rgb) {
      const lum = rgb.map(valor => { valor /= 255; return valor <= .03928 ? valor / 12.92 : Math.pow((valor + .055) / 1.055, 2.4); });
      return .2126 * lum[0] + .7152 * lum[1] + .0722 * lum[2] > .45 ? '#212529' : '#fff';
    };
    const atualizarPreviewPaleta = function () {
      if (!corOrganizacao) return;
      const principal = normalizarHex(corOrganizacao.value);
      const rgb = hexRgb(principal);
      const hsl = rgbHsl(rgb);
      const suave = hslRgb({h: hsl.h, s: Math.min(.45, Math.max(.18, hsl.s * .35)), l: .92});
      const forte = hslRgb({h: hsl.h, s: hsl.s, l: Math.max(0, hsl.l - .105)});
      const codigo = document.getElementById('org_cor_principal_codigo');
      if (codigo) codigo.textContent = principal.toUpperCase();
      [
        ['previewCorSuave', suave],
        ['previewCorPrincipal', rgb],
        ['previewCorForte', forte]
      ].forEach(function ([id, cor]) {
        const amostra = document.getElementById(id);
        if (!amostra) return;
        amostra.style.backgroundColor = rgbHex(cor);
        amostra.style.color = textoContraste(cor);
      });
    };
    if (corOrganizacao) {
      corOrganizacao.addEventListener('input', atualizarPreviewPaleta);
      atualizarPreviewPaleta();
    }
    const statusCnpj = document.getElementById('statusCnpj');
    let cnpjValidado = '';
    let cnpjRequisicao = null;
    const cnpjValidoLocalmente = function (valor) {
      const documento = String(valor || '').replace(/\D/g, '');
      if (documento.length !== 14 || /^(\d)\1{13}$/.test(documento)) return false;
      let soma = 0;
      let peso = 5;
      for (let i = 0; i < 12; i++) { soma += Number(documento[i]) * peso; peso = peso === 2 ? 9 : peso - 1; }
      let digito = soma % 11 < 2 ? 0 : 11 - (soma % 11);
      if (digito !== Number(documento[12])) return false;
      soma = 0; peso = 6;
      for (let i = 0; i < 13; i++) { soma += Number(documento[i]) * peso; peso = peso === 2 ? 9 : peso - 1; }
      digito = soma % 11 < 2 ? 0 : 11 - (soma % 11);
      return digito === Number(documento[13]);
    };
    const validarDisponibilidadeCnpj = async function () {
      if (!campoCnpjOrganizacao) return false;
      const documento = campoCnpjOrganizacao.value.replace(/\D/g, '');
      if (!cnpjValidoLocalmente(documento)) {
        campoCnpjOrganizacao.setCustomValidity('Informe um CNPJ válido.');
        campoCnpjOrganizacao.classList.add('is-invalid');
        campoCnpjOrganizacao.classList.remove('is-valid');
        const feedback = document.getElementById('feedbackCnpj');
        if (feedback) feedback.textContent = 'Informe um CNPJ válido.';
        if (statusCnpj) { statusCnpj.hidden = false; statusCnpj.className = 'lh-contract-cnpj-status invalid'; statusCnpj.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>'; }
        return false;
      }
      if (cnpjValidado === documento) return true;
      if (cnpjRequisicao && cnpjRequisicao.documento === documento) return cnpjRequisicao.promise;
      campoCnpjOrganizacao.setCustomValidity('');
      if (statusCnpj) { statusCnpj.hidden = false; statusCnpj.className = 'lh-contract-cnpj-status pending'; statusCnpj.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }
      const dados = new FormData();
      dados.append('org_cnpjcpf', documento);
      const promise = fetch('<?= Helpers::url('contrate/validarCnpj') ?>', {method: 'POST', body: dados, headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(response => response.json())
        .then(response => {
          if (campoCnpjOrganizacao.value.replace(/\D/g, '') !== documento) return false;
          const feedback = document.getElementById('feedbackCnpj');
          campoCnpjOrganizacao.setCustomValidity(response.status ? '' : (response.text || 'Este CNPJ já está cadastrado.'));
          campoCnpjOrganizacao.classList.toggle('is-valid', !!response.status);
          campoCnpjOrganizacao.classList.toggle('is-invalid', !response.status);
          if (feedback && !response.status) feedback.textContent = response.text || 'Este CNPJ já está cadastrado.';
          if (statusCnpj) { statusCnpj.hidden = false; statusCnpj.className = 'lh-contract-cnpj-status ' + (response.status ? 'valid' : 'invalid'); statusCnpj.innerHTML = response.status ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-circle-exclamation"></i>'; }
          cnpjValidado = response.status ? documento : '';
          return !!response.status;
        })
        .catch(() => {
          campoCnpjOrganizacao.setCustomValidity('Não foi possível validar o CNPJ agora. Tente novamente.');
          campoCnpjOrganizacao.classList.add('is-invalid');
          const feedback = document.getElementById('feedbackCnpj');
          if (feedback) feedback.textContent = 'Não foi possível validar o CNPJ agora. Tente novamente.';
          return false;
        })
        .finally(() => { cnpjRequisicao = null; });
      cnpjRequisicao = {documento, promise};
      return promise;
    };
    if (campoCnpjOrganizacao) {
      campoCnpjOrganizacao.addEventListener('blur', validarDisponibilidadeCnpj);
      campoCnpjOrganizacao.addEventListener('input', function () {
        cnpjValidado = '';
        campoCnpjOrganizacao.setCustomValidity('');
        campoCnpjOrganizacao.classList.remove('is-valid', 'is-invalid');
        if (statusCnpj) statusCnpj.hidden = true;
      });
    }
    const digitos = valor => String(valor || '').replace(/\D/g, '');
    const cpfValidoLocalmente = function (valor) {
      const cpf = digitos(valor);
      if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
      let soma = 0;
      for (let i = 0; i < 9; i++) soma += Number(cpf[i]) * (10 - i);
      let resto = soma % 11;
      let digito = resto < 2 ? 0 : 11 - resto;
      if (digito !== Number(cpf[9])) return false;
      soma = 0;
      for (let i = 0; i < 10; i++) soma += Number(cpf[i]) * (11 - i);
      resto = soma % 11;
      digito = resto < 2 ? 0 : 11 - resto;
      return digito === Number(cpf[10]);
    };
    const validadorRemoto = function (id, endpoint, parametro, normalizar, validarLocal, feedbackId, statusId, mensagem) {
      const campo = document.getElementById(id);
      const feedback = document.getElementById(feedbackId);
      const status = document.getElementById(statusId);
      let validado = '';
      let pendente = null;
      const definir = function (tipo, texto) {
        campo.classList.toggle('is-valid', tipo === 'valid');
        campo.classList.toggle('is-invalid', tipo === 'invalid');
        campo.setCustomValidity(tipo === 'invalid' ? (texto || mensagem.invalido) : '');
        if (feedback && texto) feedback.textContent = texto;
        if (status) {
          status.hidden = tipo === 'empty';
          status.className = 'lh-contract-cnpj-status ' + tipo;
          status.innerHTML = tipo === 'pending' ? '<i class="fa-solid fa-spinner fa-spin"></i>' : tipo === 'valid' ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-circle-exclamation"></i>';
        }
      };
      const validar = async function () {
        if (!campo) return false;
        const valor = campo.value.trim();
        const chave = normalizar(valor);
        if (!validarLocal(valor)) { validado = ''; definir('invalid', mensagem.invalido); return false; }
        if (validado === chave) return true;
        if (pendente && pendente.chave === chave) return pendente.promise;
        definir('pending');
        const dados = new FormData();
        dados.append(parametro, chave);
        const promise = fetch(endpoint, {method: 'POST', body: dados, headers: {'X-Requested-With': 'XMLHttpRequest'}})
          .then(response => response.json())
          .then(response => {
            if (normalizar(campo.value) !== chave) return false;
            definir(response.status ? 'valid' : 'invalid', response.status ? mensagem.valido : (response.text || mensagem.invalido));
            validado = response.status ? chave : '';
            return !!response.status;
          })
          .catch(() => { definir('invalid', mensagem.indisponivel); return false; })
          .finally(() => { pendente = null; });
        pendente = {chave, promise};
        return promise;
      };
      if (campo) {
        campo.addEventListener('blur', validar);
        campo.addEventListener('input', () => { validado = ''; definir('empty'); });
      }
      return validar;
    };
    const validarCpfDisponibilidade = validadorRemoto('usu_cnpjcpf', '<?= Helpers::url('contrate/validarCpf') ?>', 'usu_cnpjcpf', digitos, cpfValidoLocalmente, 'feedbackCpf', 'statusCpf', {invalido: 'Informe um CPF válido.', valido: 'CPF disponível para cadastro.', indisponivel: 'Não foi possível validar o CPF agora. Tente novamente.'});
    const validarEmailDisponibilidade = validadorRemoto('usu_email', '<?= Helpers::url('contrate/validarEmail') ?>', 'usu_email', valor => String(valor || '').trim().toLowerCase(), valor => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor), 'feedbackEmail', 'statusEmail', {invalido: 'Informe um e-mail válido.', valido: 'E-mail disponível para cadastro.', indisponivel: 'Não foi possível validar o e-mail agora. Tente novamente.'});
    const validarCelularDisponibilidade = validadorRemoto('usu_celular', '<?= Helpers::url('contrate/validarCelular') ?>', 'usu_celular', digitos, valor => [10, 11].includes(digitos(valor).length), 'feedbackCelular', 'statusCelular', {invalido: 'Informe um celular válido.', valido: 'Celular disponível para cadastro.', indisponivel: 'Não foi possível validar o celular agora. Tente novamente.'});
    const documentosLegais = <?= json_encode([
      'termosuso' => [
        'titulo' => 'Termos de Uso',
        'versao' => (string) ($termosUsoVersao ?? ''),
        'conteudo' => (string) ($termosUso ?? '')
      ],
      'avisoprivacidade' => [
        'titulo' => 'Aviso de Privacidade do icliv',
        'versao' => (string) ($avisoPrivacidadeVersao ?? ''),
        'conteudo' => (string) ($avisoPrivacidade ?? '')
      ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    document.querySelectorAll('.js-abrirDocumentoLegal').forEach(function (botao) {
      botao.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const documento = documentosLegais[botao.dataset.documento];
        if (!documento) return;

        SimpleModal.mostrar({
          titulo: '<i class="fa-solid fa-file-shield"></i>&nbsp;' + documento.titulo,
          tamanho: 'modal-lg',
          corpo: '<div class="lh-plan-details-content">' + (documento.conteudo || '<p>Documento não disponível.</p>') + '</div><div class="small text-muted border-top pt-3 mt-4">Versão: ' + documento.versao + '</div>',
          rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Fechar</button>'
        });
      });
    });

    const btnTentarPagamento = document.getElementById('btnTentarPagamento');
    if (btnTentarPagamento) {
      btnTentarPagamento.addEventListener('click', function () {
        btnTentarPagamento.disabled = true;
        btnTentarPagamento.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Gerando link...';
        const dados = new FormData();
        dados.append('plc_id', btnTentarPagamento.dataset.plcId || '');
        dados.append('token', btnTentarPagamento.dataset.token || '');
        fetch('<?= Helpers::url('contrate/tentarPagamento') ?>', {method: 'POST', body: dados, headers: {'X-Requested-With': 'XMLHttpRequest'}})
          .then(response => response.json())
          .then(response => {
            if (response.status && response.location) {
              window.location.href = response.location;
              return;
            }
            if (typeof Swal !== 'undefined') {
              Swal.fire({icon: response.icon || 'warning', title: response.title || 'Atenção', text: response.text || 'Não foi possível gerar o link.', confirmButtonText: '<i class="fa-solid fa-check"></i> OK', buttonsStyling: false, customClass: {confirmButton: 'bto bto-phsa'}});
            } else {
              alert(response.text || 'Não foi possível gerar o link.');
            }
            btnTentarPagamento.disabled = false;
            btnTentarPagamento.innerHTML = '<i class="fa-solid fa-rotate me-2"></i>Tentar gerar link novamente';
          })
          .catch(() => {
            btnTentarPagamento.disabled = false;
            btnTentarPagamento.innerHTML = '<i class="fa-solid fa-rotate me-2"></i>Tentar gerar link novamente';
            alert('Não foi possível gerar o link agora. Tente novamente.');
          });
      });
    }
    if (!form) return;
    const steps = Array.from(form.querySelectorAll('.lh-contract-step'));
    const progress = Array.from(document.querySelectorAll('#contrateProgresso .lh-contract-progress-item'));
    let step = <?= (int) $etapaInicial ?>;
    form.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' && step !== 4 && event.target.tagName !== 'TEXTAREA') {
        event.preventDefault();
        event.stopPropagation();
      }
    });

    function planoSelecionado() { return document.querySelector('.lh-contract-plan.selected'); }
    function atualizarCiclo(ciclo) {
      document.getElementById('plc_ciclo').value = ciclo;
      document.querySelectorAll('#grupoCiclo [data-ciclo]').forEach(function (botao) { botao.classList.toggle('active', botao.dataset.ciclo === ciclo); });
      document.querySelectorAll('.lh-contract-plan[data-tipo="Comercial"]').forEach(function (plano) {
        const botao = plano.querySelector('.js-selecionar-plano');
        const valor = ciclo === 'Anual' ? plano.dataset.anual : plano.dataset.mensal;
        if (!botao) return;
        botao.dataset.ciclo = ciclo;
        botao.querySelector('.lh-cycle-value').innerHTML = 'R$ ' + Number(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2}) + '<small>/' + (ciclo === 'Anual' ? 'ano' : 'mês') + '</small>';
      });
    }
    function atualizarPlano(plano) {
      if (!plano) return;
      document.getElementById('pla_id').value = plano.dataset.planoId;
      const tipo = plano.dataset.tipo;
      const ciclo = tipo === 'Teste' ? 'Teste' : (document.getElementById('plc_ciclo').value === 'Anual' ? 'Anual' : 'Mensal');
      document.getElementById('plc_ciclo').value = ciclo;
      if (tipo === 'Comercial') atualizarCiclo(ciclo);
      else document.getElementById('grupoCiclo').style.visibility = 'hidden';
      if (tipo === 'Comercial') document.getElementById('grupoCiclo').style.visibility = 'visible';
      document.querySelectorAll('[data-price]').forEach(function (price) {
        if (price.closest('.lh-contract-plan') !== plano) return;
        if (tipo === 'Teste') price.innerHTML = (plano.dataset.dias || '30') + ' dias grátis';
        else price.innerHTML = 'R$ ' + Number(ciclo === 'Anual' ? plano.dataset.anual : plano.dataset.mensal).toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' <small>/' + (ciclo === 'Anual' ? 'ano' : 'mês') + '</small>';
      });
    }
    function renderResumo() {
      const plano = planoSelecionado();
      const valor = plano.dataset.tipo === 'Teste' ? (plano.dataset.dias || '30') + ' dias grátis' : 'R$ ' + Number(document.getElementById('plc_ciclo').value === 'Anual' ? plano.dataset.anual : plano.dataset.mensal).toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' / ' + (document.getElementById('plc_ciclo').value === 'Anual' ? 'ano' : 'mês');
      document.getElementById('resumoContratacao').innerHTML = '<div class="row g-3"><div class="col-md-6"><div class="lh-contract-label">Plano</div><strong>' + plano.querySelector('h3').textContent + '</strong></div><div class="col-md-6"><div class="lh-contract-label">Valor</div><strong>' + valor + '</strong></div><div class="col-md-6"><div class="lh-contract-label">Organização</div>' + document.getElementById('org_razaosocial').value + '</div><div class="col-md-6"><div class="lh-contract-label">Administrador</div>' + document.getElementById('usu_razaosocial').value + '</div><div class="col-12"><div class="lh-contract-label">E-mail de acesso</div>' + document.getElementById('usu_email').value + '</div></div>';
    }
    function escaparResumo(valor) { const elemento = document.createElement('div'); elemento.textContent = valor == null || valor === '' ? 'Não informado' : String(valor); return elemento.innerHTML; }
    function renderResumoCompleto() {
      const plano = planoSelecionado();
      if (!plano) return;
      const tipo = plano.dataset.tipo;
      const ciclo = document.getElementById('plc_ciclo').value;
      const comercial = tipo === 'Comercial';
      const valor = tipo === 'Teste'
        ? (plano.dataset.dias || '30') + ' dias grátis'
        : 'R$ ' + Number(ciclo === 'Anual' ? plano.dataset.anual : plano.dataset.mensal).toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' / ' + (ciclo === 'Anual' ? 'ano' : 'mês');
      const logo = document.getElementById('org_logo').value;
      const cicloResumo = comercial ? `<div class="lh-summary-field"><small>Periodicidade</small><div class="lh-summary-cycle" id="resumoCiclo"><button type="button" class="${ciclo === 'Mensal' ? 'active' : ''}" data-ciclo="Mensal">Mensal</button><button type="button" class="${ciclo === 'Anual' ? 'active' : ''}" data-ciclo="Anual">Anual</button></div></div>` : '';
      document.getElementById('resumoContratacao').innerHTML = `<div class="row g-3"><div class="col-12 col-lg-6"><div class="lh-summary-section"><div class="lh-summary-section-title"><i class="fa-solid fa-building"></i>Organização</div><div class="row g-3 align-items-center"><div class="col-auto"><img class="lh-summary-logo" src="${logo}" alt="Logo da organização"></div><div class="col"><div class="lh-summary-field"><small>CNPJ</small>${escaparResumo(document.getElementById('org_cnpjcpf').value)}</div><div class="lh-summary-field"><small>Razão social</small>${escaparResumo(document.getElementById('org_razaosocial').value)}</div><div class="lh-summary-field"><small>Nome fantasia</small>${escaparResumo(document.getElementById('org_nomefantasia').value)}</div></div></div></div></div><div class="col-12 col-lg-6"><div class="lh-summary-section"><div class="lh-summary-section-title"><i class="fa-solid fa-user-shield"></i>Administrador da empresa</div><div class="lh-summary-field"><small>CPF</small>${escaparResumo(document.getElementById('usu_cnpjcpf').value)}</div><div class="lh-summary-field"><small>Nome completo</small>${escaparResumo(document.getElementById('usu_razaosocial').value)}</div><div class="lh-summary-field"><small>Nome social</small>${escaparResumo(document.getElementById('usu_nomefantasia').value)}</div><div class="lh-summary-field"><small>E-mail</small>${escaparResumo(document.getElementById('usu_email').value)}</div><div class="lh-summary-field"><small>Celular</small>${escaparResumo(document.getElementById('usu_celular').value)}</div></div></div><div class="col-12"><div class="lh-summary-section"><div class="lh-summary-section-title"><i class="fa-solid fa-layer-group"></i>Contratação</div><div class="row g-3"><div class="col-sm-4"><div class="lh-summary-field"><small>Plano</small><strong>${escaparResumo(plano.querySelector('h3')?.textContent)}</strong></div></div><div class="col-sm-4"><div class="lh-summary-field"><small>Valor</small><strong>${valor}</strong></div></div><div class="col-sm-4">${cicloResumo}</div></div></div></div></div>`;
      document.querySelectorAll('#resumoContratacao .lh-summary-field').forEach(function (campo) {
        const rotulo = campo.querySelector('small')?.textContent;
        if (rotulo === 'Nome fantasia' || rotulo === 'Nome social') campo.remove();
      });
      document.getElementById('orientacoesContratacao').innerHTML = tipo === 'Teste'
        ? '<i class="fa-solid fa-flask me-2 text-success"></i><strong>Plano de teste:</strong> ao concluir, sua empresa poderá iniciar o período de teste sem cobrança. Você será direcionado para o login e poderá acessar o ambiente com o usuário administrador criado.'
        : '<i class="fa-solid fa-credit-card me-2 text-phsa"></i><strong>Próxima etapa:</strong> ao concluir, você receberá as orientações para pagamento. Após a confirmação, sua empresa poderá começar a usar o icliv.';
      const cta = document.getElementById('btnFinalizarTexto');
      if (cta) cta.textContent = comercial ? 'Concluir contratação' : 'Ativar período de teste';
    }
    function mostrarStep(n) { step = n; steps.forEach(s => s.classList.toggle('active', Number(s.dataset.step) === step)); progress.forEach((p, i) => p.classList.toggle('active', i < step)); if (step === 4) renderResumoCompleto(); window.scrollTo({top: document.querySelector('.lh-contract-card').offsetTop - 80, behavior: 'smooth'}); }
    const resumoContratacao = document.getElementById('resumoContratacao');
    if (resumoContratacao) {
      resumoContratacao.addEventListener('click', function (event) { const botao = event.target.closest('[data-ciclo]'); if (!botao || !botao.closest('#resumoCiclo')) return; const plano = planoSelecionado(); if (!plano || plano.dataset.tipo !== 'Comercial') return; atualizarCiclo(botao.dataset.ciclo); renderResumoCompleto(); });
    }
    document.querySelectorAll('.lh-contract-plan').forEach(function (plano) { plano.addEventListener('click', function () { document.querySelectorAll('.lh-contract-plan').forEach(p => p.classList.remove('selected')); plano.classList.add('selected'); atualizarPlano(plano); }); });
    document.querySelectorAll('.js-selecionar-plano').forEach(function (botao) {
      botao.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const plano = botao.closest('.lh-contract-plan');
        document.querySelectorAll('.lh-contract-plan').forEach(p => p.classList.remove('selected'));
        plano.classList.add('selected');
        document.getElementById('plc_ciclo').value = botao.dataset.ciclo;
        atualizarPlano(plano);
        mostrarStep(2);
      });
    });
    document.querySelectorAll('.js-detalhes-plano').forEach(function (botao) {
      botao.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const plano = botao.closest('.lh-contract-plan');
        let beneficios = [];
        try { beneficios = JSON.parse(plano.dataset.beneficios || '[]'); } catch (erro) { beneficios = []; }
        const escapar = function (valor) { const elemento = document.createElement('div'); elemento.textContent = valor || ''; return elemento.innerHTML; };
        const lista = beneficios.length ? `<div class="mt-4"><h6 class="text-phsa">Benefícios</h6><ul class="list-unstyled mb-0">${beneficios.map(b => `<li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>${escapar(b)}</li>`).join('')}</ul></div>` : '';
        const modalId = SimpleModal.mostrar({
          titulo: '<i class="fa-solid fa-circle-info"></i> ' + escapar(plano.querySelector('h3')?.textContent || 'Detalhes do plano'),
          tamanho: 'modal-lg',
          corpo: `<div class="mb-3"><span class="badge rounded-pill text-bg-light text-primary">${escapar(plano.querySelector('.badge')?.textContent || '')}</span></div><div class="lh-plan-details-content">${plano.dataset.descricao || '<p>Mais informações estarão disponíveis em breve.</p>'}</div>${lista}`,
          rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Fechar</button>'
        });
        const modal = SimpleModal.modais[modalId];
        if (modal) modal.attr('id', 'modalDetalhesDoPlano');
      });
    });
    document.querySelectorAll('#grupoCiclo [data-ciclo]').forEach(function (botao) { botao.addEventListener('click', function () { atualizarCiclo(botao.dataset.ciclo); }); });
    document.querySelectorAll('.btn-proximo').forEach(function (botao) {
      botao.addEventListener('click', async function () {
        const current = steps.find(s => Number(s.dataset.step) === step);
        const currentValid = step === 1 || Array.from(current.querySelectorAll('input,select,textarea')).every(field => field.checkValidity());
        if (!currentValid) { form.classList.add('was-validated'); return; }
        if (step === 2 && !(await validarDisponibilidadeCnpj())) { form.classList.add('was-validated'); return; }
        if (step === 3 && !(await Promise.all([validarCpfDisponibilidade(), validarEmailDisponibilidade(), validarCelularDisponibilidade()])).every(Boolean)) { form.classList.add('was-validated'); return; }
        if (step === 3 && document.getElementById('usu_senha').value !== document.getElementById('usu_senhaconfirmacao').value) { document.getElementById('usu_senhaconfirmacao').setCustomValidity('As senhas não conferem.'); form.classList.add('was-validated'); return; }
        document.getElementById('usu_senhaconfirmacao').setCustomValidity('');
        mostrarStep(Math.min(4, step + 1));
      });
    });
    document.querySelectorAll('.btn-anterior').forEach(function (botao) {
      botao.classList.remove('btn', 'btn-outline-secondary');
      botao.classList.add('bto', 'bto-phsa-light');
      botao.addEventListener('click', () => mostrarStep(Math.max(1, step - 1)));
    });
    ['org_cnpjcpf', 'usu_cnpjcpf'].forEach(function (id) { const campo = document.getElementById(id); if (campo) { campo.classList.add(id === 'org_cnpjcpf' ? 'cnpj' : 'cpf'); campo.setAttribute('inputmode', 'numeric'); campo.setAttribute('autocomplete', 'off'); } });
    const celular = document.getElementById('usu_celular');
    if (celular) { celular.classList.add('celular'); celular.setAttribute('autocomplete', 'off'); }
    ['org_razaosocial', 'usu_razaosocial', 'usu_email'].forEach(function (id) { const campo = document.getElementById(id); if (campo) campo.setAttribute('autocomplete', 'off'); });
    const atualizarNomesCompatibilidade = function () {
      const fantasia = document.getElementById('org_nomefantasia');
      const social = document.getElementById('usu_nomefantasia');
      if (fantasia) fantasia.value = document.getElementById('org_razaosocial')?.value || '';
      if (social) social.value = document.getElementById('usu_razaosocial')?.value || '';
    };
    ['org_razaosocial', 'usu_razaosocial'].forEach(id => document.getElementById(id)?.addEventListener('input', atualizarNomesCompatibilidade));
    atualizarNomesCompatibilidade();
    ['usu_senha', 'usu_senhaconfirmacao'].forEach(function (id) {
      const campo = document.getElementById(id);
      if (!campo) return;
      campo.classList.add('controleDeSenha');
      campo.setAttribute('autocomplete', 'new-password');
      campo.parentElement.classList.add('position-relative');
    });
    const senha = document.getElementById('usu_senha');
    const confirmacaoSenha = document.getElementById('usu_senhaconfirmacao');
    const validarConfirmacaoSenha = function () {
      if (!senha || !confirmacaoSenha) return;
      confirmacaoSenha.setCustomValidity(confirmacaoSenha.value && senha.value !== confirmacaoSenha.value ? 'As senhas não conferem.' : '');
    };
    if (senha && confirmacaoSenha) {
      senha.addEventListener('input', validarConfirmacaoSenha);
      confirmacaoSenha.addEventListener('input', validarConfirmacaoSenha);
    }
    const logoInput = document.getElementById('org_logo_file');
    const logoButton = document.getElementById('btnSelecionarLogoOrganizacao');
    const logoPreview = document.getElementById('previewLogoOrganizacao');
    const logoData = document.getElementById('org_logo');
    const logoInfo = document.getElementById('dadosLogoOrganizacao');
    const aplicarLogo = function (response) {
      if (!response || !response.status) {
        if (response && typeof swResponse === 'function') swResponse(response);
        return;
      }
      const tamanhoBytes = Number(response.size) || Math.ceil(String(response.data || '').length * 0.75);
      if (tamanhoBytes > 2 * 1024 * 1024) {
        if (typeof Swal !== 'undefined') Swal.fire({icon: 'warning', title: 'Imagem muito grande', text: 'A logo deve ter no máximo 2 MB.'});
        else alert('A logo deve ter no máximo 2 MB.');
        if (logoInput) logoInput.required = true;
        return;
      }
      if (logoPreview) logoPreview.src = response.data;
      if (logoData) logoData.value = response.data;
      if (logoInfo) logoInfo.innerHTML = '<i class="fa-solid fa-circle-check text-success me-1"></i>' + response.name + ' · ' + response.tamanho + ' ' + response.medida;
      if (logoInput) logoInput.required = false;
    };
    if (logoInput) {
      logoInput.required = true;
      logoInput.setAttribute('autocomplete', 'off');
      logoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => aplicarLogo({status: true, data: e.target.result, name: file.name, size: file.size, tamanho: (file.size / 1024).toFixed(1), medida: 'KB'});
        reader.readAsDataURL(file);
      });
    }
    if (logoButton && typeof fgbSelecionarImagem === 'function') {
      logoButton.addEventListener('click', function () {
        fgbSelecionarImagem({
          replaceDefault: true,
          customAccept: 'image/png,image/jpeg,image/webp',
          onFileSelect: aplicarLogo
        });
      });
    }
    function mostrarAlertaContratacao(titulo, texto, icon) {
      if (typeof Swal !== 'undefined') {
        return Swal.fire({
          icon: icon || 'warning',
          title: titulo || 'Atenção',
          text: texto || 'Não foi possível concluir.',
          confirmButtonText: '<i class="fa-solid fa-check"></i> OK',
          buttonsStyling: false,
          customClass: { confirmButton: 'bto bto-phsa' }
        });
      }

      alert(texto || 'Não foi possível concluir.');
    }
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (step !== 4) return;
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }
      const btn = document.getElementById('btnFinalizar');
      const btnHtmlInicial = btn ? btn.innerHTML : '';
      const restaurarBotaoFinalizar = function () {
        if (!btn) return;
        btn.disabled = false;
        btn.innerHTML = btnHtmlInicial;
      };
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Processando...';
      fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(r => r.json())
        .then(response => {
          if (response.status && response.location) {
            window.location.href = response.location;
            return;
          }
          mostrarAlertaContratacao(response.title, response.text, response.icon);
          restaurarBotaoFinalizar();
          renderResumoCompleto();
        })
        .catch(() => {
          mostrarAlertaContratacao('Atenção', 'Não foi possível concluir agora. Tente novamente.', 'error');
          restaurarBotaoFinalizar();
          renderResumoCompleto();
        });
    });
    atualizarPlano(planoSelecionado());
  });
</script>
<?php $this->end(); ?>
