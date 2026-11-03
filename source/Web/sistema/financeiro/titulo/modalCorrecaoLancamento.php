<?php
  $lan = $lancamento ?? null;
  $titulo = $titulo ?? null;
  $esc = static fn($v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
  $dt = static function ($v, string $fmt = 'd/m/Y'): string {
    $v = trim((string) ($v ?? ''));
    return $v !== '' && strtotime($v) ? date($fmt, strtotime($v)) : '—';
  };
  $money = static fn($v): string => 'R$ ' . number_format((float) ($v ?? 0), 2, ',', '.');
  $value = static fn($v): string => number_format((float) ($v ?? 0), 2, ',', '.');
  $entrada = ($lan->lan_natureza ?? '') === 'Entrada';
  $opcoes = static function (array $itens, string $idCampo, string $nomeCampo, $selecionado = 0) use ($esc): string {
    $html = '<option value="">Selecione</option>';
    foreach ($itens as $item) {
      $id = (int) ($item->{$idCampo} ?? $item->id ?? 0);
      $nome = (string) ($item->{$nomeCampo} ?? $item->nome ?? '');
      $html .= '<option value="' . $id . '"' . ($id === (int) $selecionado ? ' selected' : '') . '>' . $esc($nome) . '</option>';
    }
    return $html;
  };
  $info = static function (string $label, string $value) use ($esc): string {
    return '<div class="phsaFakeInput"><div class="phsaFakeInputLabel">' . $esc($label) . '</div><div class="phsaFakeInputValue text-break">' . $value . '</div></div>';
  };
?>

<div id="modalCorrecaoLancamento">
  <input type="hidden" id="correcaoValorOriginal" value="<?= number_format((float) ($lan->lan_valor ?? 0), 2, '.', '') ?>">
  <input type="hidden" id="correcaoContaInicial" value="<?= (int) ($lan->lan_idcontafinanceira ?? 0) ?>">
  <input type="hidden" id="correcaoFormaInicial" value="<?= (int) ($lan->lan_idformapagamento ?? 0) ?>">
  <input type="hidden" id="correcaoCondicaoInicial" value="<?= (int) ($lan->lan_idcondicaopgto ?? 0) ?>">
  <input type="hidden" id="correcaoVencimentoInicial" value="<?= $esc(substr((string) ($lan->lan_dtvencimento ?? ''), 0, 10)) ?>">
  <input type="hidden" id="correcaoValorInformadoInicial" value="<?= $esc($value($lan->lan_valorinformado ?? 0)) ?>">
  <input type="hidden" id="correcaoObservacaoInicial" value="<?= $esc($lan->lan_observacao ?? '') ?>">
  <input type="hidden" id="correcaoTipoDescontoInicial" value="<?= $esc($lan->lan_tipodesconto ?? '') ?>">
  <input type="hidden" id="correcaoValorDescontoInicial" value="<?= $esc($value($lan->lan_valordesconto ?? 0)) ?>">
  <input type="hidden" id="correcaoTipoAcrescimoInicial" value="<?= $esc($lan->lan_tipoacrescimo ?? '') ?>">
  <input type="hidden" id="correcaoValorAcrescimoInicial" value="<?= $esc($value($lan->lan_valoracrescimo ?? 0)) ?>">

  <section class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
      <div><span class="badge bg-primary mb-2"><i class="fa-solid fa-pen-to-square me-1"></i>Corrigir lançamento</span><div class="h5 text-phsa mb-1">Lançamento #<?= (int) ($lan->lan_id ?? 0) ?></div><div class="small text-muted">A correção substitui este lançamento por um novo lançamento corrigido, mantendo o fluxo como <?= $entrada ? 'recebido' : 'pago' ?>.</div></div>
      <div class="text-end"><div class="small text-uppercase text-muted">Valor em correção</div><div class="fw-bold fs-4 text-<?= $entrada ? 'success' : 'danger' ?>"><?= $money($lan->lan_valor ?? 0) ?></div></div>
    </div>
  </div></section>

  <?php if ($titulo): ?>
    <section class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
        <div><div class="small text-uppercase text-muted">Título #<?= (int) ($titulo->tit_id ?? 0) ?></div><div class="h5 text-phsa fw-bold mb-0"><?= $esc($titulo->tit_nome ?? '—') ?></div></div>
        <div class="d-flex gap-2 flex-wrap"><span class="badge bg-<?= $esc($titulo->tit_statusclass ?? 'secondary') ?>"><i class="fa-solid fa-flag me-1"></i><?= $esc($titulo->tit_status ?? '—') ?></span><span class="badge bg-<?= $esc($titulo->tit_statusfinanceiroclass ?? 'secondary') ?>"><i class="fa-solid fa-coins me-1"></i><?= $esc($titulo->tit_statusfinanceiro ?? '—') ?></span></div>
      </div>
      <div class="row g-2 small">
        <div class="col-12 col-md-4"><?= $info('Plano de conta', $esc($titulo->tit_pct_nome ?? '—')) ?></div>
        <div class="col-6 col-md-2"><?= $info('Referência', $dt($titulo->tit_referencia ?? null)) ?></div>
        <div class="col-6 col-md-2"><?= $info('Vencimento', $dt($titulo->tit_dtvencimento ?? null)) ?></div>
        <div class="col-6 col-md-2"><?= $info('Valor título', '<strong class="text-' . ($entrada ? 'success' : 'danger') . '">' . $money($titulo->tit_valortotal ?? 0) . '</strong>') ?></div>
        <div class="col-6 col-md-2"><?= $info('Movimentado', '<strong class="text-phsa">' . $money($titulo->tit_valormovimentado ?? 0) . '</strong>') ?></div>
      </div>
    </div></section>
  <?php endif; ?>

  <section class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
      <div><div class="small text-uppercase text-muted">Lançamento original</div><div class="h5 text-phsa fw-bold mb-0"><?= $esc($lan->lan_descricao ?? '—') ?></div></div>
      <div class="d-flex gap-2 flex-wrap"><span class="badge bg-<?= $esc($lan->lan_statusclass ?? 'secondary') ?>"><?= $esc($lan->lan_status ?? '—') ?></span><span class="badge bg-<?= $entrada ? 'success' : 'danger' ?>"><i class="fa-solid <?= $entrada ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?> me-1"></i><?= $entrada ? 'Entrada' : 'Saída' ?></span></div>
    </div>
    <div class="row g-2 small">
      <div class="col-12 col-md-3"><?= $info('Centro de custo atual', $esc($lan->lan_cdc_nome ?? '—')) ?></div>
      <div class="col-12 col-md-3"><?= $info('Conta atual', $esc($lan->lan_ctf_nome ?? '—')) ?></div>
      <div class="col-12 col-md-3"><?= $info('Forma / Condição', $esc(($lan->lan_fpg_nome ?? '—') . ' / ' . ($lan->lan_cpg_nome ?? '—'))) ?></div>
      <div class="col-12 col-md-3"><?= $info('Vencimento', $dt($lan->lan_dtvencimento ?? null)) ?></div>
      <div class="col-12 col-md-4"><?= $info('Baixa', $dt($lan->lan_dtbaixa ?? null)) ?></div>
      <div class="col-12 col-md-4"><?= $info('Baixado em', $dt($lan->lan_dtbaixa ?? null, 'd/m/Y H:i')) ?></div>
      <div class="col-12 col-md-4"><?= $info('Baixado por', $esc($lan->lan_usu_nome_baixa ?? '—')) ?></div>
    </div>
  </div></section>

  <section id="stepCentroCustoCorrecao" class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
    <div class="position-relative"><div class="form-floating">
      <input type="text" class="form-control input-phsa-focus input-phsa-hover" id="buscaCentroCustoCorrecao" autocomplete="off" placeholder="Centro de custo">
      <label for="buscaCentroCustoCorrecao">Centro de custo</label>
    </div><div id="resultadosCentroCustoCorrecao" class="list-group position-absolute w-100 shadow bg-white" style="z-index:20;display:none;max-height:240px;overflow:auto"></div></div>
    <div id="mensagemCentroCustoCorrecao" class="small text-danger mt-2"></div>
    <div class="text-end mt-3"><button type="button" class="bto bto-phsa" id="btnSelecionarCentroCustoCorrecao" disabled><i class="fa-solid fa-check"></i>&nbsp;Selecionar Centro de Custo</button></div>
  </div></section>

  <section id="stepPlanejamentoCorrecao" class="d-none"></section>
</div>
