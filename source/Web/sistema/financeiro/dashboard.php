<?php $this->layout('sistema::sistema'); ?>

<?php
  $resumo = $dashboard->resumoGeral ?? (object) [];
  $contas = $dashboard->getSaldoPorConta ?? [];
  $central = $dashboard->centralDeContas ?? (object) ['data' => [], 'resumo' => (object) []];
  $contasCentral = $central->data ?? [];
  $esc = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $saldoAtual = (float) ($resumo->saldoAtual ?? 0);
  $saldoProjetado = (float) ($resumo->saldoProjetado ?? 0);
  $aReceber = (float) ($resumo->aReceber ?? 0);
  $aPagar = (float) ($resumo->aPagar ?? 0);
  $podeAbrirTitulo = Helpers::pode('titulo.visualizar');
?>

<div class="container-fluid px-0">
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="bg-phsa-gradient text-white">
      <div class="row g-0 align-items-center">
        <div class="col-12 col-lg-8">
          <div class="p-4">
            <h2 class="fw-bold mb-2 lh-1">Dashboard financeiro</h2>
            <div class="opacity-75">Visão resumida do financeiro geral</div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="p-3 p-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-4 d-flex align-items-center justify-content-center flex-shrink-0 bg-phsa-light text-phsa-dark" style="width:52px;height:52px;">
                    <i class="fa-solid fa-wallet fs-4"></i>
                  </div>
                  <div class="flex-grow-1 min-w-0">
                    <div class="small text-uppercase text-muted fw-semibold mb-1 text-end">Saldo atual</div>
                    <div class="text-end fw-bold fs-3 text-nowrap <?= Helpers::classSaldo($saldoAtual) ?>">R$ <?= Helpers::formataValor($saldoAtual) ?></div>
                    <div class="small text-muted text-end">Saldo consolidado das contas</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-4 mb-3">
      <div class="p-3 rounded-4 border-phsa-light box-phsa-hover-shadow-light bg-phsa-hover-light h-100">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="bg-success-subtle text-success rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;"><i class="fa-solid fa-arrow-trend-up"></i></div>
          <div class="small text-uppercase fw-semibold text-muted">À receber</div>
        </div>
        <div class="h3 fw-bold mb-1 text-nowrap text-success text-end">R$ <?= Helpers::formataValor($aReceber) ?></div>
        <div class="small text-muted">Entradas pendentes</div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 mb-3">
      <div class="p-3 rounded-4 border-phsa-light box-phsa-hover-shadow-light bg-phsa-hover-light h-100">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="bg-danger-subtle text-danger rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;"><i class="fa-solid fa-arrow-trend-down"></i></div>
          <div class="small text-uppercase fw-semibold text-muted">À pagar</div>
        </div>
        <div class="h3 fw-bold mb-1 text-nowrap text-danger text-end">R$ <?= Helpers::formataValor($aPagar) ?></div>
        <div class="small text-muted">Saídas pendentes</div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 mb-3">
      <div class="p-3 rounded-4 border-phsa-light box-phsa-hover-shadow-light bg-phsa-hover-light h-100">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="bg-phsa-light text-phsa-dark rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;"><i class="fa-solid fa-chart-line"></i></div>
          <div class="small text-uppercase fw-semibold text-muted">Saldo projetado</div>
        </div>
        <div class="h3 fw-bold mb-1 text-nowrap <?= Helpers::classSaldo($saldoProjetado) ?> text-end">R$ <?= Helpers::formataValor($saldoProjetado) ?></div>
        <div class="small text-muted">Saldo atual + à receber - à pagar</div>
      </div>
    </div>
  </div>

  <?php if (!empty($contas)): ?>
    <div class="card border-0 shadow-sm overflow-hidden rounded-4 mb-4">
      <div class="bg-phsa-light border-bottom px-3 py-3">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
          <div>
            <div class="small text-uppercase fw-semibold text-muted mb-1">Financeiro</div>
            <h5 class="mb-0 text-phsa-dark">Saldo por conta</h5>
          </div>
          <div class="d-flex flex-column align-items-lg-end gap-1">
            <div class="small text-muted"><?= count($contas) ?> conta(s) ativa(s)</div>
            <button type="button" onclick="alternarPrevisaoDashboard()" class="badge bg-phsa-light border-0 align-self-start align-self-lg-end">
              <i id="dashboardPrevisaoIcon" class="fa-solid fa-eye"></i>&nbsp;<span id="dashboardPrevisaoTexto">Ver previsão</span>
            </button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="border-bottom border-secondary">
            <tr class="small text-uppercase text-muted fw-semibold">
              <th class="px-3 py-2 border-0">Conta</th>
              <th class="px-3 py-2 border-0 text-end">Saldo inicial</th>
              <th class="px-3 py-2 border-0 text-end">Recebimentos</th>
              <th class="px-3 py-2 border-0 text-end">Saídas</th>
              <th class="px-3 py-2 border-0 text-end">Saldo atual</th>
              <th class="px-3 py-2 border-0 text-end colPrevisaoDashboard">À receber</th>
              <th class="px-3 py-2 border-0 text-end colPrevisaoDashboard">À pagar</th>
              <th class="px-3 py-2 border-0 text-end colPrevisaoDashboard">Projeção</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($contas as $conta): ?>
              <tr class="border-bottom">
                <td class="px-3 py-3" style="min-width:280px;max-width:320px;">
                  <div class="d-flex align-items-start gap-3">
                    <div class="bg-phsa-light text-phsa-dark rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:46px;height:46px;"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="min-w-0">
                      <div class="text-truncate fs-5 fw-bold lh-sm mb-1" title="<?= $esc($conta->ctf_nome ?? '') ?>"><?= $esc($conta->ctf_nome ?? 'Conta') ?></div>
                      <?php if (($conta->ctf_padrao ?? '') === 'Sim'): ?><span class="badge rounded-pill bg-phsa-light text-phsa-dark border border-phsa-light">Padrão</span><?php endif; ?>
                      <div class="small text-muted mt-1">Última transação: <?= Helpers::formataDataHora($conta->ultimaTransacao ?? null) ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-3 text-end"><span class="fw-semibold text-nowrap <?= Helpers::classSaldo($conta->saldoInicial ?? 0) ?>">R$ <?= Helpers::formataValor($conta->saldoInicial ?? 0) ?></span></td>
                <td class="px-3 py-3 text-end"><span class="fw-semibold text-nowrap text-success">R$ <?= Helpers::formataValor($conta->recebimentos ?? 0) ?></span></td>
                <td class="px-3 py-3 text-end"><span class="fw-semibold text-nowrap text-danger">R$ <?= Helpers::formataValor($conta->saidas ?? 0) ?></span></td>
                <td class="px-3 py-3 text-end"><span class="fw-bold text-nowrap <?= Helpers::classSaldo($conta->saldoAtual ?? 0) ?>">R$ <?= Helpers::formataValor($conta->saldoAtual ?? 0) ?></span></td>
                <td class="px-3 py-3 text-end colPrevisaoDashboard"><span class="fw-semibold text-nowrap text-success">R$ <?= Helpers::formataValor($conta->aReceber ?? 0) ?></span></td>
                <td class="px-3 py-3 text-end colPrevisaoDashboard"><span class="fw-semibold text-nowrap text-danger">R$ <?= Helpers::formataValor($conta->aPagar ?? 0) ?></span></td>
                <td class="px-3 py-3 text-end colPrevisaoDashboard"><span class="fw-bold text-nowrap <?= Helpers::classSaldo($conta->saldoProjetado ?? 0) ?>">R$ <?= Helpers::formataValor($conta->saldoProjetado ?? 0) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body text-center py-5">
        <div class="mb-3 text-phsa"><i class="fa-solid fa-building-columns fa-2x"></i></div>
        <h5 class="mb-1">Nenhuma conta financeira ativa encontrada</h5>
        <div class="text-muted">Cadastre contas financeiras para iniciar o acompanhamento.</div>
      </div>
    </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm overflow-hidden rounded-4 mb-5">
    <div class="bg-phsa-light border-bottom px-3 py-3">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
        <div>
          <div class="small text-uppercase fw-semibold text-muted mb-1">Financeiro</div>
          <h5 class="mb-0 text-phsa-dark">Central de contas</h5>
        </div>
        <div class="text-lg-end small text-muted d-flex flex-wrap gap-2 justify-content-lg-end">
          <span><strong><?= (int) ($central->resumo->qntEmAberto ?? 0) ?></strong> em aberto</span><span>|</span>
          <span><strong><?= (int) ($central->resumo->qntAReceber ?? 0) ?></strong> lanç. registrado(s)</span><span>|</span>
          <span><strong><?= (int) ($central->resumo->qntEmRecebimento ?? 0) ?></strong> parciais</span>
        </div>
      </div>
    </div>

    <?php if (!empty($contasCentral)): ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="border-bottom border-secondary">
            <tr class="small text-uppercase text-muted fw-semibold">
              <th class="px-3 py-2 border-0">Título</th>
              <th class="px-3 py-2 border-0">Plano de conta</th>
              <th class="px-3 py-2 border-0 text-center">Natureza</th>
              <th class="px-3 py-2 border-0">Conta</th>
              <th class="px-3 py-2 border-0 text-center">Vencimento</th>
              <th class="px-3 py-2 border-0 text-center">Lanç.</th>
              <th class="px-3 py-2 border-0 text-end">Movimentado</th>
              <th class="px-3 py-2 border-0 text-end">Restante</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($contasCentral as $row): ?>
              <?php
                $tituloUrl = Helpers::url('titulo/form/' . (int) ($row->tit_id ?? 0));
                $naturezaEntrada = ($row->tit_natureza ?? '') === 'Entrada';
              ?>
              <tr class="border-bottom">
                <td class="px-3 py-3" style="min-width:280px;max-width:320px;">
                  <?php if ($podeAbrirTitulo): ?><a href="<?= $tituloUrl ?>" class="text-phsa fw-semibold text-truncate d-block" title="<?= $esc($row->tit_nome ?? '') ?>"><?= $esc($row->tit_nome ?? 'Título') ?></a><?php else: ?><div class="fw-semibold"><?= $esc($row->tit_nome ?? 'Título') ?></div><?php endif; ?>
                  <?php if (!empty($row->tit_descricao)): ?><div class="small text-muted text-truncate mt-1" title="<?= $esc($row->tit_descricao) ?>"><?= $esc($row->tit_descricao) ?></div><?php endif; ?>
                  <div class="mt-2 d-flex flex-wrap gap-1"><span class="badge bg-<?= $esc($row->tit_statusclass ?? 'secondary') ?>"><i class="fa-solid fa-flag me-1"></i><?= $esc($row->tit_status ?? '—') ?></span><span class="badge bg-<?= $esc($row->tit_statusfinanceiroclass ?? 'secondary') ?>"><i class="fa-solid fa-coins me-1"></i><?= $esc($row->tit_statusfinanceiro ?? '—') ?></span></div>
                </td>
                <td class="px-3 py-3"><div class="fw-semibold text-truncate" style="max-width:220px"><?= $esc($row->tit_pct_nome ?: '—') ?></div></td>
                <td class="px-3 py-3 text-center"><span class="fw-semibold <?= $naturezaEntrada ? 'text-success' : 'text-danger' ?>"><i class="fas <?= $naturezaEntrada ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?> me-1"></i><?= $naturezaEntrada ? 'Entrada' : 'Saída' ?></span></td>
                <td class="px-3 py-3"><div class="text-truncate" style="max-width:180px"><?= $esc($row->tit_ctf_nome ?: '—') ?></div></td>
                <td class="px-3 py-3 text-center text-nowrap"><?= !empty($row->tit_dtvencimento) ? date('d/m/Y', strtotime($row->tit_dtvencimento)) : '—' ?></td>
                <td class="px-3 py-3 text-center text-nowrap"><?= (int) ($row->qnt_lancamento_baixado ?? 0) ?>/<?= (int) ($row->qnt_lancamento ?? 0) ?></td>
                <td class="px-3 py-3 text-end text-nowrap <?= Helpers::classSaldo($row->tit_valormovimentado ?? 0) ?>">R$ <?= Helpers::formataValor($row->tit_valormovimentado ?? 0) ?></td>
                <td class="px-3 py-3 text-end text-nowrap <?= Helpers::classSaldo($row->tit_valorrestante ?? 0) ?>">R$ <?= Helpers::formataValor($row->tit_valorrestante ?? 0) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <div class="mb-3 text-phsa"><i class="fa-solid fa-file-invoice-dollar fa-2x"></i></div>
        <h5 class="mb-1">Nenhuma conta encontrada</h5>
        <div class="text-muted">Não há títulos pendentes para exibir na central de contas.</div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php $this->push('style'); ?>
<style>
  .colPrevisaoDashboard { display: none; }
  .table tbody tr { transition: background-color .2s ease; }
  .table tbody tr:hover { background-color: var(--phsa-primary-light); }
</style>
<?php $this->end(); ?>

<?php $this->push('script'); ?>
<script>
  let dashboardPrevisaoVisivel = false;

  function alternarPrevisaoDashboard() {
    dashboardPrevisaoVisivel = !dashboardPrevisaoVisivel;
    $('.colPrevisaoDashboard').toggle(dashboardPrevisaoVisivel);
    $('#dashboardPrevisaoIcon').toggleClass('fa-eye', !dashboardPrevisaoVisivel).toggleClass('fa-eye-slash', dashboardPrevisaoVisivel);
    $('#dashboardPrevisaoTexto').text(dashboardPrevisaoVisivel ? 'Ocultar previsão' : 'Ver previsão');
  }
</script>
<?php $this->end(); ?>
