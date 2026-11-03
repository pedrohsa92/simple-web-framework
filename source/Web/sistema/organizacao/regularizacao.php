<?php
  $this->layout('sistema::sistema');

  $situacao = $situacao ?? (object) [];
  $contrato = $contrato ?? null;
  $administrador = !empty($administrador);
  $esc = static fn($valor) => htmlspecialchars((string) ($valor ?? '-'), ENT_QUOTES, 'UTF-8');
  $formatarData = static fn($valor) => empty($valor) ? '-' : date('d/m/Y H:i', strtotime((string) $valor));
  $formatarValor = static fn($valor) => 'R$ ' . number_format((float) ($valor ?? 0), 2, ',', '.');
  $tipoSituacao = (string) ($situacao->tipo ?? 'expirado');
  $temPagamento = $contrato && in_array($contrato->plc_statusfinanceiro ?? '', ['Em aberto', 'Falhou'], true);
  $urlContrato = $contrato ? Helpers::url('organizacao/plano/' . (int) $contrato->plc_id) : Helpers::url('organizacao/plano');
  $urlRenovacao = Helpers::url('organizacao/plano/renovar');
?>

<?php $this->push('style'); ?>
<style>
  .lh-regularizacao-page .lh-regularizacao-card { border: 1px solid var(--bs-border-color); border-radius: 1rem; background: var(--bs-body-bg); }
  .lh-regularizacao-page .lh-regularizacao-header { padding: 1.5rem; border-bottom: 1px solid var(--bs-border-color); }
  .lh-regularizacao-page .lh-regularizacao-body { padding: 1.5rem; }
  .lh-regularizacao-page .lh-regularizacao-icon { display: grid; width: 3.5rem; height: 3.5rem; place-items: center; border-radius: 50%; color: #fff; background: #d79a22; font-size: 1.35rem; }
  .lh-regularizacao-page .lh-regularizacao-field { padding: .85rem 1rem; border: 1px solid var(--bs-border-color); border-radius: .7rem; background: var(--bs-tertiary-bg); }
  .lh-regularizacao-page .lh-regularizacao-label { display: block; color: var(--bs-secondary-color); font-size: .7rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .lh-regularizacao-page .lh-regularizacao-value { margin-top: .2rem; color: var(--bs-body-color); }
</style>
<?php $this->end(); ?>

<main class="lh-regularizacao-page px-3 pb-4">
  <div class="lh-regularizacao-card shadow-sm">
    <div class="lh-regularizacao-header d-flex flex-wrap align-items-center gap-3">
      <div class="lh-regularizacao-icon"><i class="fa-solid fa-lock-open"></i></div>
      <div>
        <div class="small text-phsa fw-bold text-uppercase">Acesso da organização</div>
        <h2 class="m-0 mt-1">Regularize o acesso ao LevHub</h2>
        <div class="text-secondary mt-1">Seu login continua disponível para que você acompanhe e resolva esta situação.</div>
      </div>
    </div>

    <div class="lh-regularizacao-body">
      <?php if (!$administrador): ?>
        <div class="alert alert-warning mb-4">
          <strong><i class="fa-solid fa-circle-info me-1"></i>Acesso temporariamente limitado</strong>
          <div class="mt-1">A organização possui uma pendência de contratação. O administrador da organização precisa acessar esta área para concluir a regularização.</div>
        </div>
      <?php elseif ($tipoSituacao === 'pagamento'): ?>
        <div class="alert alert-warning mb-4">
          <strong><i class="fa-solid fa-credit-card me-1"></i>Pagamento necessário</strong>
          <div class="mt-1">A contratação foi registrada, mas o pagamento ainda não foi confirmado. Conclua o pagamento para liberar o uso completo do sistema.</div>
        </div>
      <?php elseif ($tipoSituacao === 'cancelado'): ?>
        <div class="alert alert-danger mb-4">
          <strong><i class="fa-solid fa-ban me-1"></i>Contratação cancelada</strong>
          <div class="mt-1">Escolha uma nova contratação para voltar a utilizar o LevHub.</div>
        </div>
      <?php else: ?>
        <div class="alert alert-danger mb-4">
          <strong><i class="fa-solid fa-calendar-xmark me-1"></i>Período encerrado</strong>
          <div class="mt-1">O período da contratação terminou. Renove o plano para liberar novamente o acesso completo ao sistema.</div>
        </div>
      <?php endif; ?>

      <?php if ($contrato): ?>
        <div class="row g-3 mb-4">
          <div class="col-12 col-md-3"><div class="lh-regularizacao-field"><span class="lh-regularizacao-label">Organização</span><div class="lh-regularizacao-value"><?= $esc($organizacao->org_nomefantasia ?? $organizacao->org_razaosocial ?? '-') ?></div></div></div>
          <div class="col-12 col-md-3"><div class="lh-regularizacao-field"><span class="lh-regularizacao-label">Plano</span><div class="lh-regularizacao-value text-phsa fw-semibold"><?= $esc($contrato->pla_nome ?? '-') ?></div></div></div>
          <div class="col-12 col-md-3"><div class="lh-regularizacao-field"><span class="lh-regularizacao-label">Situação financeira</span><div class="lh-regularizacao-value"><?= $esc($contrato->plc_statusfinanceiro ?? '-') ?></div></div></div>
          <div class="col-12 col-md-3"><div class="lh-regularizacao-field"><span class="lh-regularizacao-label">Valor</span><div class="lh-regularizacao-value"><?= $formatarValor($contrato->plc_valor ?? 0) ?> <small>/<?= $esc(strtolower($contrato->plc_ciclo ?? '')) ?></small></div></div></div>
          <div class="col-12 col-md-4"><div class="lh-regularizacao-field"><span class="lh-regularizacao-label">Cadastro</span><div class="lh-regularizacao-value"><?= $formatarData($contrato->plc_dtcad ?? null) ?></div></div></div>
          <div class="col-12 col-md-4"><div class="lh-regularizacao-field"><span class="lh-regularizacao-label">Início</span><div class="lh-regularizacao-value"><?= $formatarData($contrato->plc_dtinicio ?? null) ?></div></div></div>
          <div class="col-12 col-md-4"><div class="lh-regularizacao-field"><span class="lh-regularizacao-label">Fim</span><div class="lh-regularizacao-value"><?= $formatarData($contrato->plc_dtfim ?? null) ?></div></div></div>
        </div>
      <?php endif; ?>

      <?php if ($administrador): ?>
        <div class="d-flex flex-wrap justify-content-end gap-2">
          <?php if ($contrato): ?>
            <a href="<?= $esc($urlContrato) ?>" class="bto bto-phsa-light"><i class="fa-solid fa-file-invoice-dollar me-1"></i>Ver contratação</a>
          <?php endif; ?>
          <?php if ($temPagamento && !empty($contrato->plc_pagarme_url)): ?>
            <a href="<?= $esc($contrato->plc_pagarme_url) ?>" target="_blank" rel="noopener" class="bto bto-phsa"><i class="fa-solid fa-credit-card me-1"></i>Realizar pagamento</a>
          <?php elseif ($temPagamento): ?>
            <a href="<?= $esc($urlContrato) ?>" class="bto bto-phsa"><i class="fa-solid fa-credit-card me-1"></i>Realizar pagamento</a>
          <?php else: ?>
            <a href="<?= $esc($urlRenovacao) ?>" class="bto bto-phsa"><i class="fa-solid fa-rotate me-1"></i>Renovar plano</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>
