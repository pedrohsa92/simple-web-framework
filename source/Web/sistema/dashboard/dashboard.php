<?php
$this->layout('sistema::sistema');

$indicadores = $indicadores ?? [];
$cards = $indicadores['cards'] ?? [];
$modulos = $indicadores['modulos'] ?? [];
$acoes = $indicadores['acoes'] ?? [];
$esc = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
?>

<?php $this->start('style'); ?>
<style>
  .icv-dashboard { --icv-ink:#102a43; --icv-muted:#627d98; --icv-line:#d9e2ec; --icv-primary:var(--phsa-primary-bg,#2d8a68); color:var(--icv-ink); padding-bottom:1.5rem; }
  .icv-dashboard-hero { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin:0 0 1.25rem; }
  .icv-dashboard-eyebrow { color:var(--icv-primary); font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
  .icv-dashboard-title { margin:.2rem 0 .3rem; color:var(--icv-ink); font-size:clamp(1.45rem,2vw,2rem); font-weight:800; }
  .icv-dashboard-subtitle { margin:0; color:var(--icv-muted); }
  .icv-dashboard-context { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.45rem; }
  .icv-dashboard-context span { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .65rem; border:1px solid var(--icv-line); border-radius:999px; background:#fff; color:var(--icv-muted); font-size:.75rem; }
  .icv-dashboard-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(185px,1fr)); gap:.85rem; margin-bottom:1.15rem; }
  .icv-kpi-card { position:relative; display:flex; min-height:145px; flex-direction:column; overflow:hidden; padding:1rem; border:1px solid var(--icv-line); border-radius:.85rem; background:#fff; color:var(--icv-ink); text-decoration:none; box-shadow:0 .25rem .9rem rgba(16,42,67,.05); transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }
  .icv-kpi-card::after { position:absolute; right:-34px; bottom:-38px; width:92px; height:92px; border-radius:50%; background:var(--icv-tint,#eef4ff); content:""; }
  .icv-kpi-card:hover { transform:translateY(-3px); border-color:var(--icv-color,#2d8a68); color:var(--icv-ink); box-shadow:0 .7rem 1.35rem rgba(16,42,67,.12); }
  .icv-kpi-top { display:flex; align-items:center; justify-content:space-between; }
  .icv-kpi-icon { display:grid; width:2.3rem; height:2.3rem; place-items:center; border-radius:.65rem; background:var(--icv-color,#2d8a68); color:white; }
  .icv-kpi-arrow { color:#9fb3c8; font-size:.85rem; }
  .icv-kpi-label { margin-top:.8rem; color:var(--icv-muted); font-size:.78rem; font-weight:700; }
  .icv-kpi-value { position:relative; z-index:1; margin-top:.1rem; font-size:1.9rem; font-weight:800; line-height:1; }
  .icv-kpi-blue { --icv-color:#2f6fbd;--icv-tint:#edf4ff; } .icv-kpi-violet { --icv-color:#6754c7;--icv-tint:#f1efff; }
  .icv-kpi-teal { --icv-color:#168c8c;--icv-tint:#e9fbfb; } .icv-kpi-orange { --icv-color:#d47a24;--icv-tint:#fff5e8; }
  .icv-kpi-indigo { --icv-color:#405eb3;--icv-tint:#eef1ff; } .icv-kpi-slate { --icv-color:#52606d;--icv-tint:#f0f3f5; }
  .icv-kpi-rose { --icv-color:#bb5576;--icv-tint:#fff0f5; }
  .icv-dashboard-grid { display:grid; grid-template-columns:minmax(0,1.15fr) minmax(300px,.85fr); gap:1rem; }
  .icv-dashboard-panel { height:100%; padding:1.15rem; border:1px solid var(--icv-line); border-radius:.85rem; background:#fff; box-shadow:0 .25rem .9rem rgba(16,42,67,.04); }
  .icv-dashboard-panel-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
  .icv-dashboard-panel-title { display:flex; align-items:center; gap:.5rem; margin:0; color:var(--icv-ink); font-size:1rem; font-weight:800; }
  .icv-dashboard-panel-title i { color:var(--icv-primary); }
  .icv-dashboard-panel-subtitle { margin-top:.25rem; color:var(--icv-muted); font-size:.75rem; }
  .icv-module-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; }
  .icv-module-link { display:flex; align-items:center; gap:.6rem; min-width:0; padding:.7rem; border:1px solid #e6edf5; border-radius:.65rem; color:var(--icv-ink); text-decoration:none; transition:background .2s,border-color .2s; }
  .icv-module-link:hover { border-color:var(--icv-primary); background:var(--phsa-soft-bg,#e7eeec); color:var(--icv-primary); }
  .icv-module-link i { display:grid; width:1.8rem; height:1.8rem; flex:0 0 1.8rem; place-items:center; border-radius:.5rem; background:var(--phsa-soft-bg,#e7eeec); color:var(--icv-primary); }
  .icv-module-link span { overflow:hidden; font-size:.8rem; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
  .icv-action-list { display:grid; gap:.55rem; }
  .icv-action-link { display:flex; align-items:center; gap:.65rem; padding:.65rem .75rem; border:1px solid #e6edf5; border-radius:.65rem; color:var(--icv-ink); text-decoration:none; }
  .icv-action-link:hover { border-color:var(--icv-primary); background:var(--phsa-soft-hover-bg,#f2f7f5); color:var(--icv-primary); }
  .icv-action-link i { width:1.4rem; color:var(--icv-primary); text-align:center; }
  .icv-dashboard-empty { padding:1rem; border:1px dashed #cbd8e6; border-radius:.65rem; color:var(--icv-muted); font-size:.82rem; text-align:center; }
  .icv-dashboard-footnote { margin:.8rem 0 0; color:var(--icv-muted); font-size:.72rem; text-align:right; }
  @media(max-width:767.98px) { .icv-dashboard-hero { align-items:flex-start; flex-direction:column; } .icv-dashboard-context { justify-content:flex-start; } .icv-dashboard-grid { grid-template-columns:1fr; } .icv-module-list { grid-template-columns:1fr; } }
  @media(max-width:430px) { .icv-dashboard-cards { grid-template-columns:1fr; } }
</style>
<?php $this->end(); ?>

<div class="icv-dashboard">
  <header class="icv-dashboard-hero">
    <div>
      <div class="icv-dashboard-eyebrow"><i class="fa-solid fa-chart-line me-1"></i> Visão geral</div>
      <h1 class="icv-dashboard-title">Olá, <?= $esc($indicadores['usuario'] ?? ''); ?>!</h1>
      <p class="icv-dashboard-subtitle">Acompanhe os principais dados e acesse rapidamente os módulos do ICLIV.</p>
    </div>
    <div class="icv-dashboard-context" aria-label="Contexto ativo">
      <span><i class="fa-solid fa-sitemap"></i><?= $esc($indicadores['organizacao'] ?? 'Organização atual'); ?></span>
      <span><i class="fa-solid fa-building"></i><?= $esc($indicadores['empresa'] ?? 'Empresa atual'); ?></span>
    </div>
  </header>

  <?php if ($cards): ?>
    <section class="icv-dashboard-cards" aria-label="Indicadores principais">
      <?php foreach ($cards as $card): ?>
        <a class="icv-kpi-card icv-kpi-<?= $esc($card['tom'] ?? 'blue'); ?>" href="<?= $esc($card['href'] ?? '#'); ?>">
          <div class="icv-kpi-top">
            <span class="icv-kpi-icon"><i class="fa-solid <?= $esc($card['icone'] ?? 'fa-chart-simple'); ?>"></i></span>
            <i class="icv-kpi-arrow fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
          </div>
          <span class="icv-kpi-label"><?= $esc($card['titulo'] ?? ''); ?></span>
          <strong class="icv-kpi-value" data-counter="<?= (int) ($card['valor'] ?? 0); ?>">0</strong>
        </a>
      <?php endforeach; ?>
    </section>
  <?php else: ?>
    <div class="icv-dashboard-empty mb-3"><i class="fa-solid fa-lock me-1"></i> Não há indicadores disponíveis para o seu perfil.</div>
  <?php endif; ?>

  <div class="icv-dashboard-grid">
    <section class="icv-dashboard-panel">
      <div class="icv-dashboard-panel-header">
        <div>
          <h2 class="icv-dashboard-panel-title"><i class="fa-solid fa-compass"></i> Módulos ao seu alcance</h2>
          <div class="icv-dashboard-panel-subtitle">Acesso rápido conforme suas permissões</div>
        </div>
      </div>
      <?php if ($modulos): ?>
        <nav class="icv-module-list" aria-label="Módulos disponíveis">
          <?php foreach ($modulos as $modulo): ?>
            <a class="icv-module-link" href="<?= $esc($modulo['href'] ?? '#'); ?>">
              <i class="fa-solid <?= $esc($modulo['icone'] ?? 'fa-cube'); ?>"></i>
              <span><?= $esc($modulo['nome'] ?? ''); ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      <?php else: ?>
        <div class="icv-dashboard-empty">Nenhum módulo foi liberado para este perfil.</div>
      <?php endif; ?>
    </section>

    <section class="icv-dashboard-panel">
      <div class="icv-dashboard-panel-header">
        <div>
          <h2 class="icv-dashboard-panel-title"><i class="fa-solid fa-bolt"></i> Ações rápidas</h2>
          <div class="icv-dashboard-panel-subtitle">Comece uma tarefa sem navegar pelos menus</div>
        </div>
      </div>
      <?php if ($acoes): ?>
        <nav class="icv-action-list" aria-label="Ações rápidas">
          <?php foreach ($acoes as $acao): ?>
            <a class="icv-action-link" href="<?= $esc($acao['href'] ?? '#'); ?>">
              <i class="fa-solid <?= $esc($acao['icone'] ?? 'fa-arrow-right'); ?>"></i>
              <span><?= $esc($acao['nome'] ?? ''); ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      <?php else: ?>
        <div class="icv-dashboard-empty">Não há ações de cadastro disponíveis para o seu perfil.</div>
      <?php endif; ?>
      <p class="icv-dashboard-footnote">Indicadores atualizados em <?= $esc($indicadores['atualizado_em'] ?? ''); ?></p>
    </section>
  </div>
</div>

<?php $this->start('script'); ?>
<script>
  (function () {
    document.querySelectorAll('.icv-dashboard [data-counter]').forEach(function (elemento) {
      const alvo = Number(elemento.dataset.counter || 0);
      const inicio = performance.now();
      const duracao = 850;
      function atualizar(agora) {
        const progresso = Math.min((agora - inicio) / duracao, 1);
        const suavizado = 1 - Math.pow(1 - progresso, 3);
        elemento.textContent = Math.round(alvo * suavizado).toLocaleString('pt-BR');
        if (progresso < 1) requestAnimationFrame(atualizar);
      }
      requestAnimationFrame(atualizar);
    });
  })();
</script>
<?php $this->end(); ?>
