<?php
  $registroTitulo = $titulo ?? null;
  $isUpdate = is_object($registroTitulo) && (int) ($registroTitulo->tit_id ?? 0) > 0;
  $planos = is_array($planosContas ?? null) ? $planosContas : [];
  $origemDados = is_object($registroTitulo) ? ($registroTitulo->origem_dados ?? null) : null;
  $logs = $isUpdate ? json_decode((string) ($registroTitulo->tit_log ?? '[]'), true) : [];
  $logs = is_array($logs) ? $logs : [];
  $esc = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $dataHora = static function ($valor) use ($esc): string {
    $valor = trim((string) ($valor ?? ''));
    if ($valor === '') {
      return '-';
    }

    $timestamp = strtotime(str_replace('/', '-', $valor));
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $esc($valor);
  };
  $data = static function ($valor): string {
    $valor = (string) ($valor ?? '');
    return strlen($valor) >= 10 ? substr($valor, 0, 10) : $valor;
  };
  $statusFinanceiro = trim((string) ($registroTitulo->tit_statusfinanceiro ?? ''));
  if (!in_array($statusFinanceiro, ['Aguardando confirmação', 'Aguardando lançamento', 'Lançamento registrado', 'Parcial', 'Pago', 'Recebido', 'Cancelado'], true)) $statusFinanceiro = 'Aguardando confirmação';
  $statusFinanceiroClass = trim((string) ($registroTitulo->tit_statusfinanceiroclass ?? ''));
  if (!in_array($statusFinanceiroClass, ['secondary', 'primary', 'warning', 'success', 'danger'], true)) $statusFinanceiroClass = 'secondary';
  $statusTitulo = trim((string) ($registroTitulo->tit_status ?? ''));
  if (!in_array($statusTitulo, ['Cadastrado', 'Confirmado', 'Cancelado'], true)) $statusTitulo = 'Cadastrado';
  $statusTituloClass = trim((string) ($registroTitulo->tit_statusclass ?? ''));
  if (!in_array($statusTituloClass, ['secondary', 'success', 'danger'], true)) $statusTituloClass = 'secondary';
  $podeEditar = (bool) ($podeEditar ?? !$isUpdate);
  $podeCancelar = (bool) ($podeCancelar ?? false);
  $podeConfirmar = (bool) ($podeConfirmar ?? false);
  $possuiLancamentoBaixado = (bool) ($possuiLancamentoBaixado ?? false);
  $lancamentos = is_object($lancamentos ?? null) ? $lancamentos : (object) ['total' => 0, 'data' => []];
  $contasFinanceiras = is_array($contasFinanceiras ?? null) ? $contasFinanceiras : [];
  $centrosCusto = is_array($centrosCusto ?? null) ? $centrosCusto : [];
  $formasPagamento = is_array($formasPagamento ?? null) ? $formasPagamento : [];
  $condicoesPagamento = is_array($condicoesPagamento ?? null) ? $condicoesPagamento : [];
  $podeGerarLancamento = (bool) ($podeGerarLancamento ?? false);
  $podeBaixarLancamento = (bool) ($podeBaixarLancamento ?? false);
  $valor = static fn($valor): string => number_format((float) ($valor ?? 0), 2, ',', '.');
?>

<?php $this->layout('sistema::sistema'); ?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <?php if (!$isUpdate): ?>
          <h4 class="m-0 text-uppercase">Cadastro de novo título</h4>
          <div class="text-muted small">Registre um novo compromisso financeiro</div>
        <?php else: ?>
          <h4 class="m-0 text-uppercase"><?= $esc($registroTitulo->tit_nome ?? 'Título financeiro') ?></h4>
          <div class="text-muted small">
            TÍTULO Nº <?= (int) ($registroTitulo->tit_id ?? 0) ?>
            &bull; <?= $esc($registroTitulo->tit_natureza === 'Entrada' ? 'Entrada' : 'Saída') ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="d-flex align-items-center gap-2">
        <?php if ($isUpdate): ?>
          <span class="badge bg-<?= $esc($statusTituloClass) ?>"><i class="fa-solid fa-flag me-1"></i><?= $esc($statusTitulo) ?></span>
          <span class="badge bg-<?= $esc($statusFinanceiroClass) ?>"><i class="fa-solid fa-coins me-1"></i><?= $esc($statusFinanceiro) ?></span>
        <?php endif; ?>
        <?php if ($isUpdate): ?>
          <a href="<?= Helpers::url('titulo/form') ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-plus"></i>&nbsp;Novo título</a>
        <?php else: ?>
          <a href="<?= Helpers::url('financeiro/dashboard') ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($isUpdate && ($registroTitulo->tit_status ?? '') === 'Cancelado'): ?>
      <div class="alert alert-light border border-danger mb-4">
        <div class="text-danger fw-semibold text-uppercase mb-3">
          <i class="fa-solid fa-ban"></i>&nbsp;Título cancelado
        </div>
        <div class="row">
          <div class="col-12 mb-3">
            <div class="text-muted small text-uppercase">Motivo do cancelamento</div>
            <div class="fw-semibold text-break"><?= $esc($registroTitulo->tit_motivo_cancelamento ?? '-') ?></div>
          </div>
          <div class="col-12 col-md-6">
            <div class="text-muted small text-uppercase">Responsável</div>
            <div class="fw-semibold text-break"><?= $esc($registroTitulo->tit_usu_nome_cancelamento ?? '-') ?></div>
          </div>
          <div class="col-12 col-md-6">
            <div class="text-muted small text-uppercase">Data/hora</div>
            <div class="fw-semibold"><?= $dataHora($registroTitulo->tit_dtcancelamento ?? null) ?></div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <header class="phsaScrollHeader">
      <nav class="phsaScrollNav" data-menu="tabTitulo">
        <ul class="phsaScrollList">
          <li class="phsaScrollItem active" data-target="cadastroTitulo"><i class="fa-solid fa-file-lines"></i>&nbsp;Cadastro</li>
          <?php if ($isUpdate): ?>
            <li class="phsaScrollItem" data-target="financeiroTitulo"><i class="fa-solid fa-coins"></i>&nbsp;Financeiro</li>
            <li class="phsaScrollItem" data-target="anexoTitulo"><i class="fa-solid fa-paperclip"></i>&nbsp;Anexo</li>
            <li class="phsaScrollItem" data-target="historicoTitulo"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li>
          <?php endif; ?>
        </ul>
      </nav>
    </header>

    <div class="phsaTabContent phsaTabSubContent">
      <div class="phsaTabItem active p-3" id="cadastroTitulo">
        <form id="formTitulo" class="needs-validation" novalidate autocomplete="off">
          <?php if ($isUpdate && !$podeEditar): ?>
            <div class="alert alert-secondary d-flex align-items-center gap-2">
              <i class="fa-solid fa-lock"></i>
              <div>Este título não pode mais ser alterado porque seu status atual é <strong><?= $esc($registroTitulo->tit_status ?? 'indisponível') ?></strong>.</div>
            </div>
          <?php endif; ?>
          <input type="hidden" name="tit_id" value="<?= (int) ($registroTitulo->tit_id ?? 0) ?>">
          <input type="hidden" id="tit_idplanoconta" name="tit_idplanoconta" value="<?= (int) ($registroTitulo->tit_idplanoconta ?? 0) ?>">
          <input type="hidden" id="tit_origem" name="tit_origem" value="<?= $esc($registroTitulo->tit_origem ?? '') ?>">
          <input type="hidden" id="tit_idorigem" name="tit_idorigem" value="<?= (int) ($registroTitulo->tit_idorigem ?? 0) ?>">

          <div class="row">
            <div class="col-12 col-md-8 mb-3"><div class="form-floating"><input class="form-control input-phsa-focus input-phsa-hover" id="tit_nome" name="tit_nome" maxlength="100" value="<?= $esc($registroTitulo->tit_nome ?? '') ?>" placeholder="Título" <?= $podeEditar ? 'required' : 'disabled' ?>><label for="tit_nome">Título</label><div class="invalid-feedback">Informe o título.</div></div></div>
            <div class="col-12 col-md-4 mb-3"><div class="form-floating"><input class="form-control input-phsa-focus input-phsa-hover dinheiro text-end" id="tit_valortotal" name="tit_valortotal" inputmode="decimal" value="<?= $esc($registroTitulo->tit_valortotal ?? '') ?>" placeholder="Valor" <?= $podeEditar ? 'required' : 'disabled' ?>><label for="tit_valortotal">Valor</label><div class="invalid-feedback">Informe o valor.</div></div></div>
          </div>

          <div class="row">
            <div class="col-12 col-md-6 mb-3 position-relative">
              <div class="form-floating"><input class="form-control input-phsa-focus input-phsa-hover" id="tituloPlanoBusca" autocomplete="off" placeholder="Plano de contas" <?= $podeEditar ? 'required' : 'disabled' ?>><label for="tituloPlanoBusca">Plano de contas</label></div>
              <div id="tituloPlanoResultados" class="list-group position-absolute w-100 shadow bg-white" style="z-index:10;display:none;max-height:240px;overflow:auto"></div>
              <div class="invalid-feedback" id="tituloPlanoErro">Selecione um plano de contas.</div>
            </div>
            <div class="col-12 col-md-3 mb-3"><div class="form-floating"><input type="date" class="form-control input-phsa-focus input-phsa-hover text-center" id="tit_referencia" name="tit_referencia" value="<?= $esc($data($registroTitulo->tit_referencia ?? '')) ?>" placeholder="Referência" <?= $podeEditar ? 'required' : 'disabled' ?>><label for="tit_referencia">Referência *</label><div class="invalid-feedback">Informe a data de referência.</div></div></div>
            <div class="col-12 col-md-3 mb-3"><div class="form-floating"><input type="date" class="form-control input-phsa-focus input-phsa-hover text-center" id="tit_dtvencimento" name="tit_dtvencimento" value="<?= $esc($data(!$isUpdate ? ($registroTitulo->tit_referencia ?? '') : ($registroTitulo->tit_dtvencimento ?? ''))) ?>" placeholder="Vencimento" required <?= (!$podeEditar || !$isUpdate) ? 'disabled' : '' ?>><label for="tit_dtvencimento">Vencimento *</label><div class="invalid-feedback">Informe a data de vencimento.</div></div><?php if (!$isUpdate && $podeEditar): ?><div class="text-start mt-1" id="editarVencimentoLink"><button type="button" class="btn btn-link p-0 text-phsa text-decoration-none titulo-editar-vencimento-link" id="btnEditarVencimento"><i class="fa-solid fa-pen-to-square"></i>&nbsp;Editar vencimento</button></div><?php endif; ?></div>
          </div>

          <div class="row"><div class="col-12 mb-3"><div class="form-floating"><input class="form-control input-phsa-focus input-phsa-hover" id="tit_descricao" name="tit_descricao" maxlength="255" value="<?= $esc($registroTitulo->tit_descricao ?? '') ?>" placeholder="Descrição" <?= !$podeEditar ? 'disabled' : '' ?>><label for="tit_descricao">Descrição</label></div></div></div>

          <div id="tituloVinculoMensagem" class="alert alert-light border mb-3"><i class="fa-solid fa-user-slash"></i>&nbsp; Nenhum vínculo selecionado.</div>
          <div id="tituloVinculoCard" class="mb-3"></div>

          <?php if ($podeEditar || $podeCancelar): ?>
            <div class="row mt-3"><div class="col-12 d-flex flex-wrap justify-content-between gap-2">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($podeEditar): ?><button type="button" class="bto bto-phsa-light bto-phsa-hover" id="btnVincularTitulo"><i class="fa-solid fa-user-plus"></i>&nbsp;Vincular responsável</button><?php endif; ?>
                <?php if ($podeCancelar): ?><button type="button" class="bto text-danger" id="btnCancelarTitulo"><i class="fa-solid fa-ban"></i>&nbsp;Cancelar título</button><?php endif; ?>
              </div>
              <?php if ($podeEditar): ?>
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                  <button type="submit" class="bto bto-phsa"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar</button>
                  <?php if ($podeConfirmar): ?><button type="button" class="bto bto-phsa-dark" id="btnConfirmarTitulo"><i class="fa-solid fa-check-double"></i>&nbsp;Confirmar título</button><?php endif; ?>
                </div>
              <?php endif; ?>
            </div></div>
          <?php endif; ?>
        </form>
      </div>

      <?php if ($isUpdate): ?>
        <div class="phsaTabItem p-3" id="financeiroTitulo">
          <section>
            <div class="row">
              <div class="col-12 mb-3">
                <div id="cardResumoTitulo" class="row g-3 mb-3">
                  <div class="col-12 col-md-5"><div class="phsaFakeInput"><div class="phsaFakeInputLabel text-phsa">Título</div><div class="phsaFakeInputValue fw-semibold text-break"><?= $esc($registroTitulo->tit_nome ?? '-') ?></div></div></div>
                  <div class="col-12 col-md-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Plano de conta</div><div class="phsaFakeInputValue fw-semibold text-break"><?= $esc($registroTitulo->tit_pct_nome ?? '-') ?></div></div></div>
                  <div class="col-6 col-md-2"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Referência</div><div class="phsaFakeInputValue text-md-end"><?= $data($registroTitulo->tit_referencia ?? '') ? date('d/m/Y', strtotime($data($registroTitulo->tit_referencia ?? ''))) : '—' ?></div></div></div>
                  <div class="col-6 col-md-2"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Vencimento</div><div class="phsaFakeInputValue text-md-end"><?= $data($registroTitulo->tit_dtvencimento ?? '') ? date('d/m/Y', strtotime($data($registroTitulo->tit_dtvencimento ?? ''))) : '—' ?></div></div></div>
                  <div class="col-6 col-md-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Status do título</div><div class="phsaFakeInputValue"><span class="badge bg-<?= $esc($statusTituloClass) ?>"><i class="fa-solid fa-flag me-1"></i><?= $esc($statusTitulo) ?></span></div></div></div>
                  <div class="col-6 col-md-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Status financeiro</div><div class="phsaFakeInputValue"><span class="badge bg-<?= $esc($statusFinanceiroClass) ?>"><i class="fa-solid fa-coins me-1"></i><?= $esc($statusFinanceiro) ?></span></div></div></div>
                  <div class="col-6 col-md-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Natureza</div><div class="phsaFakeInputValue fw-bold fs-5 text-<?= ($registroTitulo->tit_natureza ?? '') === 'Saida' ? 'danger' : 'success' ?>"><?= ($registroTitulo->tit_natureza ?? '') === 'Saida' ? '<i class="fas fa-arrow-trend-down"></i> Saída' : '<i class="fas fa-arrow-trend-up"></i> Entrada' ?></div></div></div>
                  <div class="col-6 col-md-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Valor total</div><div class="phsaFakeInputValue fw-bold fs-5 text-end text-<?= ($registroTitulo->tit_natureza ?? '') === 'Saida' ? 'danger' : 'success' ?>">R$ <?= $valor($registroTitulo->tit_valortotal ?? 0) ?></div></div></div>
              </div>

              <?php if (($registroTitulo->tit_status ?? '') === 'Cadastrado'): ?>
                <div class="col-12 mb-3"><div class="alert alert-warning d-flex align-items-center gap-2 box-phsa-shadow-light border-phsa-light"><i class="fas fa-exclamation-triangle"></i><div>Este título ainda está como <strong>Cadastrado</strong>. Acesse a aba <strong>Cadastro</strong>, clique em <strong>Confirmar título</strong> e depois gere os lançamentos financeiros.</div></div></div>
              <?php elseif ($podeGerarLancamento): ?>
                <div class="col-12 mb-3"><div class="d-flex align-items-center justify-content-between flex-wrap gap-2"><h5 class="text-phsa mb-0">Ações financeiras</h5><button type="button" class="bto bto-phsa" id="btnGerarLancamentoTitulo"><i class="fa-solid <?= ($registroTitulo->tit_natureza ?? '') === 'Saida' ? 'fa-money-bill-wave' : 'fa-hand-holding-dollar' ?>"></i>&nbsp;Gerar lançamento</button></div></div>
              <?php endif; ?>

              <div class="col-12 mb-3">
                <div class="card border-phsa-light box-phsa-shadow-light">
                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"><div class="small text-uppercase text-muted">Lançamentos do título</div><div class="small text-muted"><?= (int) ($lancamentos->total ?? 0) ?> registro(s)</div></div>
                    <div class="table-responsive">
                      <table id="tableLancamentoTitulo" class="table table-sm table-striped table-hover align-middle mb-0 small">
                        <thead><tr><th>Descrição</th><th>Conta</th><th>Forma</th><th>Centro de custo</th><th class="text-center text-nowrap">Vencimento</th><th class="text-end text-nowrap">Valor informado</th><th class="text-end text-nowrap">Desconto</th><th class="text-end text-nowrap">Acréscimo</th><th class="text-end text-nowrap">Valor final</th><th class="text-center text-nowrap">Status</th><th class="text-center text-nowrap">Ação</th></tr></thead>
                        <tbody>
                          <?php if (($lancamentos->total ?? 0) > 0): ?>
                            <?php foreach (($lancamentos->data ?? []) as $lancamento): ?>
                              <?php $statusLancamento = trim((string) ($lancamento->lan_status ?? '')); if ($statusLancamento === '' && ($lancamento->lan_tipoacao ?? '') === 'Correcao') $statusLancamento = 'Corrigido'; $classeStatusLancamento = trim((string) ($lancamento->lan_statusclass ?? '')) ?: 'secondary'; $iconeLancamento = $statusLancamento === 'Corrigido' ? 'fa-pen-to-square' : (in_array($statusLancamento, ['A receber', 'Recebido'], true) ? 'fa-hand-holding-dollar' : 'fa-money-bill-wave'); $acaoLancamento = $statusLancamento === 'A receber' ? 'Receber' : 'Pagar'; ?>
                              <tr>
                                <td><button type="button" class="btn btn-link p-0 text-phsa text-decoration-none text-start fw-semibold js-detalhes-lancamento" data-id="<?= (int) $lancamento->lan_id ?>" title="Visualizar lançamento"><?= $esc($lancamento->lan_descricao ?? '—') ?></button><?php if ((int) ($lancamento->lan_totalparcela ?? 1) > 1): ?><div><span class="badge bg-light text-dark border">Parcela <?= (int) ($lancamento->lan_parcela ?? 1) ?>/<?= (int) ($lancamento->lan_totalparcela ?? 1) ?></span></div><?php endif; ?></td>
                                <td><?= $esc($lancamento->lan_ctf_nome ?? '—') ?></td>
                                <td><?= $esc($lancamento->lan_fpg_nome ?? '—') ?><div class="small text-muted"><?= $esc($lancamento->lan_cpg_nome ?? '—') ?></div></td>
                                <td><?= $esc($lancamento->lan_cdc_nome ?? '—') ?></td>
                                <td class="text-center text-nowrap"><?= !empty($lancamento->lan_dtvencimento) ? date('d/m/Y', strtotime($lancamento->lan_dtvencimento)) : '—' ?></td>
                                <td class="text-end text-nowrap fw-semibold">R$ <?= $valor($lancamento->lan_valorinformado ?? 0) ?></td>
                                <td class="text-end text-nowrap">R$ <?= $valor($lancamento->lan_desconto ?? 0) ?></td>
                                <td class="text-end text-nowrap">R$ <?= $valor($lancamento->lan_acrescimo ?? 0) ?></td>
                                <td class="text-end text-nowrap fw-bold">R$ <?= $valor($lancamento->lan_valor ?? 0) ?></td>
                                <td class="text-center text-nowrap"><?php if ($podeBaixarLancamento && in_array($statusLancamento, ['A pagar', 'A receber'], true)): ?><button type="button" class="badge titulo-lancamento-badge bg-<?= $esc($classeStatusLancamento) ?> border-0 js-baixar-lancamento" data-id="<?= (int) $lancamento->lan_id ?>" data-status="<?= $esc($statusLancamento) ?>" title="<?= $esc($acaoLancamento) ?>" aria-label="<?= $esc($acaoLancamento) ?>"><i class="fa-solid <?= $esc($iconeLancamento) ?> me-1"></i><?= $esc($statusLancamento) ?></button><?php else: ?><span class="badge titulo-lancamento-badge bg-<?= $esc($classeStatusLancamento) ?>"><i class="fa-solid <?= $esc($iconeLancamento) ?> me-1"></i><?= $esc($statusLancamento ?: '—') ?></span><?php endif; ?></td>
                                <td class="text-center text-nowrap"><div class="d-flex flex-column align-items-center gap-1"><button type="button" class="badge titulo-lancamento-badge bg-phsa-light text-phsa border-0 d-inline-flex align-items-center gap-1 js-detalhes-lancamento" data-id="<?= (int) $lancamento->lan_id ?>" data-descricao="<?= $esc($lancamento->lan_descricao ?? '') ?>" data-status="<?= $esc($lancamento->lan_status ?? '') ?>" data-valor="R$ <?= $valor($lancamento->lan_valor ?? 0) ?>" data-vencimento="<?= !empty($lancamento->lan_dtvencimento) ? date('d/m/Y', strtotime($lancamento->lan_dtvencimento)) : '—' ?>" title="Visualizar lançamento" aria-label="Visualizar lançamento"><i class="fa-solid fa-eye"></i><span>Visualizar</span></button><?php if ($podeBaixarLancamento && in_array($statusLancamento, ['A pagar', 'A receber'], true)): ?><button type="button" class="badge titulo-lancamento-badge bg-phsa text-white border-0 d-inline-flex align-items-center gap-1 js-baixar-lancamento" data-id="<?= (int) $lancamento->lan_id ?>" data-status="<?= $esc($statusLancamento) ?>"><i class="fa-solid <?= $esc($iconeLancamento) ?>"></i><span><?= $esc($acaoLancamento) ?></span></button><?php endif; ?></div></td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?><tr><td colspan="11" class="text-center text-muted"><i class="fa-solid fa-circle-info"></i>&nbsp;Nenhum lançamento carregado até o momento.</td></tr><?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
        <div class="phsaTabItem p-3" id="anexoTitulo"><div class="alert alert-info mb-0"><i class="fa-solid fa-circle-info"></i>&nbsp;A aba Anexo será implementada posteriormente.</div></div>
        <div class="phsaTabItem" id="historicoTitulo">
          <?php
            $historicoConfig = [
              'id' => $registroTitulo->tit_id ?? null,
              'status' => $statusTitulo,
              'statusClass' => $statusTituloClass,
              'createdAt' => $registroTitulo->tit_dtcad ?? null,
              'updatedAt' => $registroTitulo->tit_dtalter ?? null,
              'logs' => $logs,
              'emptyMessage' => 'Nenhum histórico registrado para este título.'
            ];
            include __DIR__ . '/../../../template/sistema/historicoPadrao.php';
          ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php $this->push('style'); ?>
<style>
  .titulo-editar-vencimento-link {
    font-size: 0.75rem;
  }

  .titulo-lancamento-badge {
    width: 88px;
    justify-content: center;
    text-align: center;
  }
</style>
<?php $this->end(); ?>

<?php $this->push('script'); ?>
<script>
  $(function () {
    const planos = <?= json_encode(array_map(static fn($plano) => ['id' => (int) ($plano->pct_id ?? 0), 'nome' => (string) ($plano->pct_nome ?? ''), 'codigo' => (string) ($plano->pct_codigo ?? ''), 'natureza' => (string) ($plano->pct_natureza ?? '')], $planos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const origemInicial = <?= json_encode($origemDados ?: null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const urlSalvar = '<?= Helpers::url('titulo/salvarTitulo') ?>';
    const urlPesquisa = '<?= Helpers::url('titulo/pesquisarVinculo') ?>';
    const urlConfirmar = '<?= Helpers::url('titulo/confirmarTitulo') ?>';
    const urlCancelar = '<?= Helpers::url('titulo/cancelarTitulo') ?>';
    const urlGerarLancamento = '<?= Helpers::url('titulo/gerarLancamentoTitulo') ?>';
    const urlBaixarLancamento = '<?= Helpers::url('titulo/pagarReceberLancamento') ?>';
    const urlDetalhesLancamento = '<?= Helpers::url('titulo/modalDetalhesLancamento') ?>';
    const urlModalCorrecaoLancamento = '<?= Helpers::url('titulo/modalCorrecaoLancamento') ?>';
    const urlCorrigirLancamento = '<?= Helpers::url('titulo/corrigirLancamento') ?>';
    const urlEstornarLancamento = '<?= Helpers::url('titulo/estornarLancamento') ?>';
    const urlSalvarArquivoLancamento = '<?= Helpers::url('arquivo/salvarArquivo') ?>';
    const tituloFinanceiroId = <?= (int) ($registroTitulo->tit_id ?? 0) ?>;
    const naturezaTitulo = <?= json_encode((string) ($registroTitulo->tit_natureza ?? 'Saida'), JSON_UNESCAPED_UNICODE) ?>;
    const valorTitulo = <?= json_encode((float) ($registroTitulo->tit_valortotal ?? 0)) ?>;
    const contasFinanceiras = <?= json_encode(array_map(static fn($item) => ['id' => (int) ($item->pct_id ?? $item->ctf_id ?? 0), 'nome' => (string) ($item->ctf_nome ?? '')], $contasFinanceiras), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const centrosCusto = <?= json_encode(array_map(static fn($item) => ['id' => (int) ($item->cdc_id ?? 0), 'nome' => (string) ($item->cdc_nome ?? '')], $centrosCusto), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const formasPagamento = <?= json_encode(array_map(static fn($item) => ['id' => (int) ($item->fpg_id ?? 0), 'nome' => (string) ($item->fpg_nome ?? '')], $formasPagamento), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const condicoesPagamento = <?= json_encode(array_map(static fn($item) => ['id' => (int) ($item->cpg_id ?? 0), 'formaId' => (int) ($item->cpg_idformapagamento ?? 0), 'nome' => (string) ($item->cpg_nome ?? ''), 'parcelas' => (int) ($item->cpg_qtdparcela ?? 1), 'intervalo' => (int) ($item->cpg_intervalodias ?? 30)], $condicoesPagamento), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const podeGerarLancamento = <?= $podeGerarLancamento ? 'true' : 'false' ?>;
    const podeBaixarLancamento = <?= $podeBaixarLancamento ? 'true' : 'false' ?>;
    const podeEditarTitulo = <?= $podeEditar ? 'true' : 'false' ?>;
    const podeCancelarTitulo = <?= $podeCancelar ? 'true' : 'false' ?>;
    const podeConfirmarTitulo = <?= $podeConfirmar ? 'true' : 'false' ?>;
    let snapshotInicialTitulo = null;
    function snapshotTitulo() {
      return JSON.stringify({
        nome: String($('#tit_nome').val() || '').trim(),
        valor: String($('#tit_valortotal').val() || '').trim(),
        plano: String($('#tit_idplanoconta').val() || '').trim(),
        referencia: String($('#tit_referencia').val() || '').trim(),
        vencimento: String($('#tit_dtvencimento').val() || '').trim(),
        descricao: String($('#tit_descricao').val() || '').trim(),
        origem: String($('#tit_origem').val() || '').trim(),
        origemId: String($('#tit_idorigem').val() || '').trim()
      });
    }
    function atualizarEstadoConfirmacaoTitulo() {
      if (!podeConfirmarTitulo || snapshotInicialTitulo === null) return;
      $('#btnConfirmarTitulo').prop('disabled', snapshotTitulo() !== snapshotInicialTitulo);
    }
    const $referenciaTitulo = $('#tit_referencia');
    const $vencimentoTitulo = $('#tit_dtvencimento');
    if ($('#btnEditarVencimento').length) {
      const sincronizarVencimento = function () {
        if ($vencimentoTitulo.prop('disabled')) $vencimentoTitulo.val($referenciaTitulo.val() || '');
        if ($('#formTitulo').hasClass('was-validated')) {
          $vencimentoTitulo.toggleClass('is-invalid', !$vencimentoTitulo.val());
        }
      };
      $referenciaTitulo.on('input change', sincronizarVencimento);
      $('#btnEditarVencimento').on('click', function () {
        $vencimentoTitulo.prop('disabled', false).focus();
        $('#editarVencimentoLink').remove();
      });
      sincronizarVencimento();
    }
    function escapar(valor) { return $('<div>').text(valor == null ? '' : valor).html(); }
    function rotuloPlano(plano) { return plano.nome + ' - ' + plano.natureza; }
    function renderPlanos(filtro = '') { const texto = String(filtro).toLowerCase(); const lista = planos.filter(plano => (rotuloPlano(plano) + ' ' + plano.id).toLowerCase().includes(texto)); $('#tituloPlanoResultados').html(lista.length ? lista.map(plano => `<button type="button" class="list-group-item list-group-item-action js-selecionar-plano text-start" data-id="${plano.id}"><span class="d-block fw-semibold">${escapar(plano.nome)}</span><span class="d-block small text-muted">${escapar(plano.natureza)} · código ${plano.id}</span></button>`).join('') : '<div class="list-group-item text-muted">Nenhum plano encontrado.</div>').show(); }
    function selecionarPlano(id) { const plano = planos.find(item => String(item.id) === String(id)); if (!plano) return; $('#tit_idplanoconta').val(plano.id); $('#tituloPlanoBusca').val(rotuloPlano(plano)).removeClass('is-invalid'); $('#tituloPlanoResultados').hide(); atualizarEstadoConfirmacaoTitulo(); }
    const planoAtual = planos.find(item => String(item.id) === String($('#tit_idplanoconta').val())); if (planoAtual) $('#tituloPlanoBusca').val(rotuloPlano(planoAtual));
    $('#tituloPlanoBusca').on('focus input', function () { renderPlanos($(this).val()); }); $(document).on('click', '.js-selecionar-plano', function () { selecionarPlano($(this).data('id')); }); $(document).on('click', function (event) { if (!$(event.target).closest('#tituloPlanoBusca, #tituloPlanoResultados').length) $('#tituloPlanoResultados').hide(); });
    let vinculoAtual = null;
    function renderVinculo(item) {
      vinculoAtual = item || null;
      if (!item) {
        $('#tit_origem, #tit_idorigem').val('');
        $('#tituloVinculoCard').empty();
        $('#tituloVinculoMensagem').show();
        return;
      }

      $('#tit_origem').val(item.origem);
      $('#tit_idorigem').val(item.id);
      $('#tituloVinculoMensagem').hide();

      const urlSemFoto = '<?= Helpers::url('storage/global/semimagem.jpg') ?>';
      const urlSemPerfil = '<?= Helpers::url('storage/global/semperfil.png') ?>';
      const logo = String(item.logo || '').trim();
      const foto = item.origem === 'fornecedor'
        ? urlSemFoto
        : (logo
          ? ((typeof CONF_URL !== 'undefined' ? String(CONF_URL).replace(/\/$/, '') : '') + '/' + logo.replace(/^\/+/, ''))
          : urlSemPerfil);
      const nome = item.nome || '-';
      const nomeSecundario = item.razaoSocial || '';
      const documento = String(item.documento || '').trim();
      const documentoMascara = String(item.documentoMascara || documento).trim();
      const celular = String(item.celular || '').trim();
      const celularMascara = String(item.celularMascara || celular).trim();
      const email = String(item.email || '').trim();
      const whatsapp = celular.replace(/\D/g, '');
      const whatsappHtml = whatsapp
        ? `<a href="https://wa.me/${whatsapp}" target="_blank" rel="noopener" class="text-success p-1" title="Abrir no WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>`
        : '';
      const copiarDocumento = documento
        ? `<button type="button" class="btn btn-default btn-sm text-phsa p-1 js-copiar-vinculo" data-valor="${escapar(documento)}" data-campo="Documento" title="Copiar documento"><i class="fa-regular fa-copy"></i></button>`
        : '';
      const copiarCelular = celular
        ? `<button type="button" class="btn btn-default btn-sm text-phsa p-1 js-copiar-vinculo" data-valor="${escapar(celular)}" data-campo="Celular" title="Copiar celular"><i class="fa-regular fa-copy"></i></button>`
        : '';
      const copiarEmail = email
        ? `<button type="button" class="btn btn-default btn-sm text-phsa p-1 js-copiar-vinculo" data-valor="${escapar(email)}" data-campo="E-mail" title="Copiar e-mail"><i class="fa-regular fa-copy"></i></button>`
        : '';
      const contatosVinculo = [];
      if (documento) contatosVinculo.push(`<span>Documento: ${escapar(documentoMascara)}</span>${copiarDocumento}`);
      if (celular) contatosVinculo.push(`<span>${escapar(celularMascara)}</span>${whatsappHtml}${copiarCelular}`);
      if (email) contatosVinculo.push(`<span>${escapar(email)}</span>${copiarEmail}`);
      const linhaContato = contatosVinculo.join('<span class="mx-1">|</span>');

      $('#tituloVinculoCard').html(`
        <div class="card border-phsa-light box-phsa-shadow-light mb-0">
          <div class="card-body">
          <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3">
              <img src="${escapar(foto)}" alt="Perfil" class="rounded-circle border border-phsa-light" width="52" height="52" style="object-fit:cover;">
              <div>
                <div class="fw-semibold text-uppercase text-phsa">${escapar(nome)}</div>
                ${nomeSecundario ? `<div class="small text-muted">${escapar(nomeSecundario)}</div>` : ''}
                <div class="small text-muted mb-1 fw-bold">${escapar(item.tipo || '')}</div>
                ${linhaContato ? `<div class="small text-muted d-flex flex-wrap align-items-center gap-2">${linhaContato}</div>` : ''}
              </div>
            </div>
            ${podeEditarTitulo ? `<div class="d-flex align-items-center gap-2"><button type="button" class="bto bto-phsa-light" id="btnEditarVinculo" title="Alterar vínculo"><i class="fa-solid fa-pen"></i></button><button type="button" class="bto bto-phsa-light" id="btnRemoverVinculo" title="Remover vínculo"><i class="fa-solid fa-xmark"></i></button></div>` : ''}
          </div>
          </div>
        </div>`);
    }

    renderVinculo(origemInicial);
    snapshotInicialTitulo = snapshotTitulo();
    atualizarEstadoConfirmacaoTitulo();
    // O modal de vínculo segue o mesmo fluxo do IPR: busca mínima de três
    // caracteres, resultado tabular e seleção explícita do fornecedor.
    $('#btnVincularTitulo').off('click').on('click', function () {
      const modalId = SimpleModal.mostrar({
        titulo: 'Vincular fornecedor',
        tamanho: 'modal-xl modal-dialog-scrollable',
        corpo: `
          <div class="row">
            <div class="col-12 col-md-4 mb-3">
              <div class="small text-muted mb-2">Vincular</div>
              <div class="border rounded px-3 py-2 bg-light-subtle"><i class="fa-solid fa-building me-1 text-phsa"></i>Fornecedor(es)</div>
            </div>
            <div class="col-12 col-md-8 mb-3 pt-md-4">
              <div class="form-floating">
                <input type="text" id="pesquisaVinculoTitulo" class="form-control input-phsa-focus input-phsa-hover" placeholder="Pesquisar fornecedor">
                <label id="labelPesquisaVinculoTitulo" for="pesquisaVinculoTitulo">Pesquisar fornecedor</label>
              </div>
            </div>
            <div class="col-12 mb-2"><div id="totalVinculoTitulo"></div></div>
            <div class="col-12">
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr>
                      <th width="70">Foto</th>
                      <th>Nome</th>
                      <th>Celular</th>
                      <th>Email</th>
                      <th>CPF/CNPJ</th>
                      <th width="120" class="text-end">Ação</th>
                    </tr>
                  </thead>
                  <tbody id="resultadoVinculoTitulo">
                    <tr class="table-light"><td colspan="99" class="text-center text-muted">Digite pelo menos 3 caracteres para pesquisar.</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>`,
        rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Fechar</button>'
      });

      const modal = SimpleModal.modais[modalId];
      if (vinculoAtual && vinculoAtual.origem) {
        modal.find(`[name="tipoVinculoTitulo"][value="${vinculoAtual.origem}"]`).prop('checked', true);
      }
      const itensPorChave = {};
      const fotoPadrao = '<?= Helpers::url('storage/global/semperfil.png') ?>';
      let timer = null;
      let requisicao = 0;

      function resetarResultados(texto = 'Digite pelo menos 3 caracteres para pesquisar.') {
        modal.find('#totalVinculoTitulo').empty();
        modal.find('#resultadoVinculoTitulo').html(`<tr class="table-light"><td colspan="99" class="text-center text-muted">${escapar(texto)}</td></tr>`);
      }

      function atualizarTipo() {
        modal.find('#labelPesquisaVinculoTitulo').text('Pesquisar fornecedor');
        modal.find('#pesquisaVinculoTitulo').attr('placeholder', 'Pesquisar fornecedor').val('').focus();
        resetarResultados();
      }

      function urlFoto(logo) {
        const valor = String(logo || '').trim();
        if (!valor) return fotoPadrao;
        if (valor.startsWith('/') || valor.startsWith('http://') || valor.startsWith('https://')) return valor;
        let base = typeof CONF_URL !== 'undefined' ? String(CONF_URL) : '';
        while (base.endsWith('/')) base = base.slice(0, -1);
        let caminho = valor;
        while (caminho.startsWith('/')) caminho = caminho.slice(1);
        return base ? base + '/' + caminho : caminho;
      }

      function renderizarResultados(itens, tipo) {
        Object.keys(itensPorChave).forEach(chave => delete itensPorChave[chave]);
        modal.find('#totalVinculoTitulo').html(`<span class="text-muted small">${itens.length} fornecedor(es) encontrado(s)</span>`);

        if (!itens.length) {
          resetarResultados('Nenhum registro encontrado.');
          return;
        }

        const html = itens.map(item => {
          const chave = `${item.origem}:${item.id}`;
          itensPorChave[chave] = item;
          const ministerios = Array.isArray(item.ministerios) && item.ministerios.length
            ? item.ministerios.join('<br>')
            : '-';
          return `<tr>
            <td><button type="button" class="border-0 bg-transparent p-0 js-vincular-item" data-chave="${escapar(chave)}" title="Vincular"><img src="${escapar(urlFoto(item.logo))}" alt="Perfil" class="rounded-circle me-3" width="50" height="50"></button></td>
            <td><button type="button" class="border-0 bg-transparent p-0 text-start js-vincular-item" data-chave="${escapar(chave)}" title="Vincular"><div class="fw-semibold text-phsa">${escapar(item.nome || '-')}</div><div class="small text-secondary">${escapar(item.razaoSocial || '')}</div></button></td>
            <td>${escapar(item.celular || '-')}</td>
            <td>${escapar(item.email || '-')}</td>
            <td>${escapar(item.documento || '-')}</td>
            <td class="text-end"><button type="button" class="bto bto-phsa js-vincular-item" data-chave="${escapar(chave)}"><i class="fa-solid fa-link"></i></button></td>
          </tr>`;
        }).join('');
        modal.find('#resultadoVinculoTitulo').html(html);
      }

      function pesquisar() {
        const pesquisa = String(modal.find('#pesquisaVinculoTitulo').val() || '').trim();
        const tipo = 'fornecedor';
        if (pesquisa.length < 3) {
          resetarResultados();
          return;
        }

        const numeroRequisicao = ++requisicao;
        modal.find('#totalVinculoTitulo').empty();
        modal.find('#resultadoVinculoTitulo').html('<tr><td colspan="99" class="text-center text-muted py-3"><i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Pesquisando...</td></tr>');
        $.ajax({
          url: urlPesquisa,
          method: 'POST',
          dataType: 'json',
          data: { tipo, pesquisa }
        }).done(function (response) {
          if (numeroRequisicao !== requisicao) return;
          if (!response || !response.status) {
            resetarResultados(response && response.text ? response.text : 'Não foi possível pesquisar os vínculos.');
            return;
          }
          renderizarResultados(Array.isArray(response.data) ? response.data : [], tipo);
        }).fail(function () {
          if (numeroRequisicao !== requisicao) return;
          resetarResultados('Não foi possível pesquisar os vínculos.');
        });
      }

      modal.find('[name="tipoVinculoTitulo"]').on('change', atualizarTipo);
      modal.find('#pesquisaVinculoTitulo').on('input', function () {
        clearTimeout(timer);
        timer = setTimeout(pesquisar, 400);
      });
      modal.on('click', '.js-vincular-item', function () {
        const item = itensPorChave[String($(this).data('chave'))];
        if (!item) return;
        renderVinculo(item);
        atualizarEstadoConfirmacaoTitulo();
        SimpleModal.fechar(modalId);
      });
      atualizarTipo();
    });

    $(document).on('click', '#btnEditarVinculo', function () { $('#btnVincularTitulo').trigger('click'); });
    $(document).on('click', '#btnRemoverVinculo', function () { renderVinculo(null); atualizarEstadoConfirmacaoTitulo(); });
    $(document).on('click', '.js-copiar-vinculo', function () {
      if (typeof fgbCopiarParaAreaDeTransferencia !== 'function') return;
      fgbCopiarParaAreaDeTransferencia($(this).attr('data-valor') || '', $(this).attr('data-campo') || 'Texto');
    });

    function escapeFinanceiro(valor) { return $('<div>').text(valor == null ? '' : valor).html(); }
    function valorFinanceiro(valor) {
      if (typeof valor === 'number') return valor;
      const texto = String(valor || '').replace(/R\$\s?/g, '').trim();
      if (texto.includes(',')) return Number(texto.replace(/\./g, '').replace(',', '.')) || 0;
      return Number(texto) || 0;
    }
    function dinheiroFinanceiro(valor) {
      return Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function dataParceladaFinanceiro(data, dias) {
      const objeto = new Date(`${data}T12:00:00`);
      objeto.setDate(objeto.getDate() + (Number(dias) || 0));
      return objeto.toISOString().slice(0, 10);
    }
    function opcoesFinanceiro(lista, placeholder) {
      return `<option value="">${escapeFinanceiro(placeholder)}</option>` + lista.map(item => `<option value="${item.id}">${escapeFinanceiro(item.nome)}</option>`).join('');
    }
    function abrirModalGerarLancamento() {
      if (!podeGerarLancamento) return;
      const modalId = SimpleModal.mostrar({
        tamanho: 'modal-xl modal-dialog-scrollable',
        titulo: `${naturezaTitulo === 'Entrada' ? 'Registrar recebimento' : 'Registrar pagamento'} do título #${tituloFinanceiroId}`,
        rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Fechar</button><button type="submit" form="formGerarLancamentoTitulo" class="bto bto-phsa" id="btnSalvarGerarLancamento"><i class="fa-solid fa-check"></i>&nbsp;Gerar lançamentos</button>',
        corpo: `
          <form id="formGerarLancamentoTitulo" class="needs-validation" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6"><div class="form-floating"><select class="form-select" id="lan_idcontafinanceira" required>${opcoesFinanceiro(contasFinanceiras, 'Selecione a conta financeira')}</select><label for="lan_idcontafinanceira">Conta financeira</label></div></div>
              <div class="col-12 col-md-6"><div class="form-floating"><select class="form-select" id="lan_idcentrocusto" required>${opcoesFinanceiro(centrosCusto, 'Selecione o centro de custo')}</select><label for="lan_idcentrocusto">Centro de custo</label></div></div>
              <div class="col-12 col-md-6"><div class="form-floating"><select class="form-select" id="lan_idformapagamento" required>${opcoesFinanceiro(formasPagamento, 'Selecione a forma de pagamento')}</select><label for="lan_idformapagamento">Forma de pagamento</label></div></div>
              <div class="col-12 col-md-6"><div class="form-floating"><select class="form-select" id="lan_idcondicaopgto" required><option value="">Selecione primeiro a forma</option></select><label for="lan_idcondicaopgto">Condição de pagamento</label></div></div>
              <div class="col-12 col-md-4"><div class="form-floating"><input type="date" class="form-control" id="lan_dtvencimento" value="<?= $esc($data($registroTitulo->tit_dtvencimento ?? date('Y-m-d'))) ?>" required><label for="lan_dtvencimento">Primeiro vencimento</label></div></div>
              <div class="col-12 col-md-4"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="lan_valorinformado" value="${dinheiroFinanceiro(valorTitulo)}" required><label for="lan_valorinformado">Valor informado por parcela</label></div></div>
              <div class="col-6 col-md-2"><div class="form-floating"><input type="number" class="form-control" id="lan_quantidade" min="1" max="120" value="1" required><label for="lan_quantidade">Parcelas</label></div></div>
              <div class="col-6 col-md-2"><div class="form-floating"><input type="number" class="form-control" id="lan_intervalo" min="1" max="3650" value="30" required><label for="lan_intervalo">Intervalo (dias)</label></div></div>
              <div class="col-12"><div class="form-floating"><input type="text" class="form-control" id="lan_descricao" value="${escapeFinanceiro('Lançamento do título #'+tituloFinanceiroId)}" maxlength="255" required><label for="lan_descricao">Descrição</label></div></div>
              <div class="col-12"><div class="border rounded p-3"><div class="small text-uppercase text-muted mb-2">Ajustes por parcela</div><div class="row g-3"><div class="col-6 col-md-3"><div class="form-floating"><select class="form-select" id="lan_tipodesconto"><option value="">Sem desconto</option><option value="Valor">Valor</option><option value="Percentual">Percentual</option></select><label for="lan_tipodesconto">Desconto</label></div></div><div class="col-6 col-md-3"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="lan_valordesconto" value="0,00"><label for="lan_valordesconto">Valor desconto</label></div></div><div class="col-6 col-md-3"><div class="form-floating"><select class="form-select" id="lan_tipoacrescimo"><option value="">Sem acréscimo</option><option value="Valor">Valor</option><option value="Percentual">Percentual</option></select><label for="lan_tipoacrescimo">Acréscimo</label></div></div><div class="col-6 col-md-3"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="lan_valoracrescimo" value="0,00"><label for="lan_valoracrescimo">Valor acréscimo</label></div></div></div></div></div>
            </div>
            <div id="previewGerarLancamentoTitulo" class="mt-3"></div>
          </form>`,
        aoMostrar: function (modal) {
          modal.find('.dinheiro').mask('#.##0,00', { reverse: true });
        }
      });
      const modal = SimpleModal.modais[modalId];
      modal.find('.dinheiro').mask('#.##0,00', { reverse: true });
      let parcelas = [];
      function atualizarCondicoes() {
        const formaId = Number(modal.find('#lan_idformapagamento').val());
        const lista = condicoesPagamento.filter(item => item.formaId === formaId);
        modal.find('#lan_idcondicaopgto').html(opcoesFinanceiro(lista, lista.length ? 'Selecione a condição' : 'Nenhuma condição disponível'));
        const condicao = lista[0];
        if (condicao) {
          modal.find('#lan_idcondicaopgto').val(condicao.id);
          modal.find('#lan_quantidade').val(condicao.parcelas || 1);
          modal.find('#lan_intervalo').val(condicao.intervalo || 30);
        }
      }
      function calcularParcelas() {
        const quantidade = Math.max(1, Math.min(120, Number(modal.find('#lan_quantidade').val()) || 1));
        const intervalo = Math.max(1, Number(modal.find('#lan_intervalo').val()) || 30);
        const data = modal.find('#lan_dtvencimento').val();
        const valorInformado = valorFinanceiro(modal.find('#lan_valorinformado').val());
        const tipoDesconto = modal.find('#lan_tipodesconto').val();
        const valorDesconto = valorFinanceiro(modal.find('#lan_valordesconto').val());
        const tipoAcrescimo = modal.find('#lan_tipoacrescimo').val();
        const valorAcrescimo = valorFinanceiro(modal.find('#lan_valoracrescimo').val());
        if (!data || valorInformado <= 0) { parcelas = []; modal.find('#previewGerarLancamentoTitulo').empty(); return; }
        const grupo = `TIT-${tituloFinanceiroId}-${Date.now()}`;
        parcelas = Array.from({ length: quantidade }, (_, indice) => {
          const desconto = tipoDesconto === 'Percentual' ? valorInformado * valorDesconto / 100 : (tipoDesconto === 'Valor' ? valorDesconto : 0);
          const acrescimo = tipoAcrescimo === 'Percentual' ? valorInformado * valorAcrescimo / 100 : (tipoAcrescimo === 'Valor' ? valorAcrescimo : 0);
          return {
            lan_idcontafinanceira: Number(modal.find('#lan_idcontafinanceira').val()),
            lan_idcentrocusto: Number(modal.find('#lan_idcentrocusto').val()),
            lan_idformapagamento: Number(modal.find('#lan_idformapagamento').val()),
            lan_idcondicaopgto: Number(modal.find('#lan_idcondicaopgto').val()),
            lan_dtvencimento: dataParceladaFinanceiro(data, indice * intervalo),
            lan_valorinformado: valorInformado,
            lan_tipodesconto: tipoDesconto,
            lan_valordesconto: valorDesconto,
            lan_desconto: desconto,
            lan_tipoacrescimo: tipoAcrescimo,
            lan_valoracrescimo: valorAcrescimo,
            lan_acrescimo: acrescimo,
            lan_valor: Math.max(0, valorInformado - desconto + acrescimo),
            lan_descricao: modal.find('#lan_descricao').val(),
            lan_grupo_parcelamento: quantidade > 1 ? grupo : '',
            lan_parcela: indice + 1,
            lan_totalparcela: quantidade
          };
        });
        modal.find('#previewGerarLancamentoTitulo').html(`<div class="small text-muted mb-2">${parcelas.length} lançamento(s) serão gerados</div><div class="table-responsive"><table class="table table-sm table-striped align-middle mb-0"><thead><tr><th>Parcela</th><th>Vencimento</th><th class="text-end">Valor final</th></tr></thead><tbody>${parcelas.map(item => `<tr><td>${item.lan_parcela}/${item.lan_totalparcela}</td><td>${new Date(`${item.lan_dtvencimento}T12:00:00`).toLocaleDateString('pt-BR')}</td><td class="text-end fw-semibold">R$ ${dinheiroFinanceiro(item.lan_valor)}</td></tr>`).join('')}</tbody></table></div>`);
      }
      modal.find('#lan_idformapagamento').on('change', atualizarCondicoes);
      modal.find('#formGerarLancamentoTitulo').on('input change', 'input, select', calcularParcelas);
      modal.find('#formGerarLancamentoTitulo').on('submit', function (event) {
        event.preventDefault();
        calcularParcelas();
        const form = $(this);
        if (!form[0].checkValidity() || !parcelas.length || parcelas.some(item => !item.lan_idcontafinanceira || !item.lan_idcentrocusto || !item.lan_idformapagamento || !item.lan_idcondicaopgto)) {
          form.addClass('was-validated');
          swWarning('Preencha todos os cadastros financeiros e os dados do lançamento.');
          return;
        }
        const botao = $('#btnSalvarGerarLancamento');
        botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Gerando');
        $.ajax({ url: urlGerarLancamento, method: 'POST', dataType: 'json', data: { tit_id: tituloFinanceiroId, lancamentos: JSON.stringify(parcelas) } }).done(function (response) {
          swResponse(response);
          if (response && response.status) setTimeout(() => window.location.reload(), 800);
          else botao.prop('disabled', false).html('<i class="fa-solid fa-check"></i>&nbsp;Gerar lançamentos');
        }).fail(function () { botao.prop('disabled', false).html('<i class="fa-solid fa-check"></i>&nbsp;Gerar lançamentos'); swError('Não foi possível gerar os lançamentos.'); });
      });
      atualizarCondicoes();
      calcularParcelas();
    }
    // Fluxo em etapas inspirado no IPR: primeiro centro de custo, depois planejamento.
    function abrirModalGerarLancamento() {
      if (!podeGerarLancamento) return;
      const resumoTitulo = $('#cardResumoTitulo').html();
      const dataVencimentoPadrao = '<?= $esc($data($registroTitulo->tit_dtvencimento ?? date('Y-m-d'))) ?>';
      const tituloAcao = naturezaTitulo === 'Entrada' ? 'recebimento' : 'pagamento';
      const planoTituloId = Number($('#tit_idplanoconta').val() || 0);
      const possuiCondicaoAtiva = condicoesPagamento.some(condicao => formasPagamento.some(forma => Number(forma.id) === Number(condicao.formaId)));
      const cadastrosAusentes = [];
      if (!planoTituloId || !planos.some(plano => Number(plano.id) === planoTituloId)) cadastrosAusentes.push('plano de contas');
      if (!contasFinanceiras.length) cadastrosAusentes.push('conta financeira');
      if (!centrosCusto.length) cadastrosAusentes.push('centro de custo');
      if (!formasPagamento.length) cadastrosAusentes.push('forma de pagamento');
      if (!possuiCondicaoAtiva) cadastrosAusentes.push('condição de pagamento');
      const modalId = SimpleModal.mostrar({
        tamanho: 'modal-xl modal-dialog-scrollable',
        titulo: `Registrar ${tituloAcao} do título #${tituloFinanceiroId}`,
        rodape: 'hide',
        corpo: `<div id="modalLancamentoTituloConteudo">
          <div class="row g-3 mb-3">${resumoTitulo}</div>
          <div id="stepCentroCustoLancamentoTitulo" class="card border-phsa-light box-phsa-shadow-light">
            <div class="card-body">
              <div class="position-relative"><div class="form-floating">
                <input type="text" class="form-control input-phsa-focus input-phsa-hover" id="buscaCentroCustoLancamentoTitulo" autocomplete="off" placeholder="Centro de custo">
                <label for="buscaCentroCustoLancamentoTitulo">Centro de custo</label>
              </div><div id="resultadosCentroCustoLancamentoTitulo" class="list-group position-absolute w-100 shadow bg-white" style="z-index:20;display:none;max-height:240px;overflow:auto"></div></div>
              <div id="mensagemCentroCustoLancamentoTitulo" class="small text-danger mt-2"></div>
              <div class="text-end mt-3"><button type="button" class="bto bto-phsa" id="btnSelecionarCentroCustoLancamentoTitulo" disabled><i class="fa-solid fa-check"></i>&nbsp;Selecionar Centro de Custo</button></div>
            </div>
          </div>
          <div id="stepPlanejamentoLancamentoTitulo" class="d-none"></div>
        </div>`
      });
      const modal = SimpleModal.modais[modalId];
      if (cadastrosAusentes.length) {
        modal.find('#modalLancamentoTituloConteudo').html(`<div class="row g-3 mb-3">${resumoTitulo}</div><div class="alert alert-warning border mb-0"><div class="fw-semibold mb-2"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;Cadastros financeiros pendentes</div><div>Para registrar o ${tituloAcao}, cadastre e mantenha ativo pelo menos um registro de:</div><ul class="mb-0 mt-2">${cadastrosAusentes.map(item => `<li>${escapeFinanceiro(item)}</li>`).join('')}</ul></div>`);
        return;
      }
      let centroCustoSelecionado = null;
      let lancamentos = [];
      let ajustesVisiveis = false;
      const intervaloAutomacao = 500;
      const totalLancado = () => lancamentos.reduce((total, item) => total + Number(item.lan_valor || 0), 0);
      const valorRestante = () => Math.max(0, valorTitulo - totalLancado());

      function renderizarCentrosCusto(filtro) {
        const texto = String(filtro || '').trim().toLowerCase();
        const lista = centrosCusto.filter(item => `${item.nome} ${item.id}`.toLowerCase().includes(texto));
        const html = lista.length ? lista.map(item => `<button type="button" class="list-group-item list-group-item-action text-start js-selecionar-centro-custo" data-id="${item.id}"><span class="d-block fw-semibold">${escapeFinanceiro(item.nome)}</span><span class="d-block small text-muted">Centro de custo · código ${item.id}</span></button>`).join('') : '<div class="list-group-item text-muted">Nenhum centro de custo encontrado.</div>';
        modal.find('#resultadosCentroCustoLancamentoTitulo').html(html).show();
      }
      function resumoCentroCusto() {
        return `<div class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body d-flex align-items-center justify-content-between gap-3"><div><div class="small text-uppercase text-muted">Centro de custo</div><div class="h5 text-phsa fw-bold mb-0">${escapeFinanceiro(centroCustoSelecionado.nome)}</div></div><div class="text-end"><div class="small text-uppercase text-muted">Valor restante</div><div class="h5 text-danger fw-bold mb-0" id="valorRestanteLancamentoTitulo">R$ ${dinheiroFinanceiro(valorRestante())}</div></div></div></div>`;
      }
      function renderizarAjusteTabela(valor, base, tipo) {
        const valorAjuste = Number(valor || 0);
        if (valorAjuste <= 0) return '<span class="text-muted">—</span>';
        const percentual = base > 0 ? (valorAjuste / base) * 100 : 0;
        return `<div>R$ ${dinheiroFinanceiro(valorAjuste)}</div><div class="small text-muted">${percentual.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%</div><span class="badge bg-light text-dark border">${escapeFinanceiro(tipo || 'Valor')}</span>`;
      }
      function renderizarPrevia() {
        const html = lancamentos.length ? `<div class="card border-phsa-light box-phsa-shadow-light mt-3"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-2"><span class="small text-uppercase text-muted">Lançamentos adicionados</span><span class="small text-muted">${lancamentos.length} registro(s)</span></div><div class="table-responsive"><table class="table table-sm small align-middle mb-0"><thead><tr><th>Conta</th><th>Forma/Condição</th><th>Vencimento</th><th class="text-end">Valor informado</th><th class="text-end">Desconto</th><th class="text-end">Acréscimo</th><th class="text-end">Valor final</th><th class="text-end">Ação</th></tr></thead><tbody>${lancamentos.map((item, indice) => { const conta = contasFinanceiras.find(opcao => String(opcao.id) === String(item.lan_idcontafinanceira)); const forma = formasPagamento.find(opcao => String(opcao.id) === String(item.lan_idformapagamento)); const condicao = condicoesPagamento.find(opcao => String(opcao.id) === String(item.lan_idcondicaopgto)); return `<tr><td>${escapeFinanceiro(conta ? conta.nome : '—')}</td><td>${escapeFinanceiro([forma && forma.nome, condicao && condicao.nome].filter(Boolean).join(' / ') || '—')}</td><td>${new Date(`${item.lan_dtvencimento}T12:00:00`).toLocaleDateString('pt-BR')}</td><td class="text-end">R$ ${dinheiroFinanceiro(item.lan_valorinformado)}</td><td class="text-end">R$ ${dinheiroFinanceiro(item.lan_desconto)}</td><td class="text-end">R$ ${dinheiroFinanceiro(item.lan_acrescimo)}</td><td class="text-end fw-semibold">R$ ${dinheiroFinanceiro(item.lan_valor)}</td><td class="text-end"><button type="button" class="bto bto-phsa-light btn-sm" data-indice="${indice}" title="Remover"><i class="fa-solid fa-xmark"></i></button></td></tr>`; }).join('')}</tbody></table></div><div class="text-end small text-muted mt-2">Valor restante: <strong>R$ ${dinheiroFinanceiro(valorRestante())}</strong></div></div></div>` : '<div class="alert alert-light border mt-3 mb-0"><i class="fa-solid fa-circle-info"></i>&nbsp;Nenhum lançamento adicionado até o momento.</div>';
        modal.find('#previewGerarLancamentoTitulo').html(html);
        modal.find('#previewGerarLancamentoTitulo tbody tr').each(function (indice) {
          const item = lancamentos[indice];
          const celulas = $(this).find('td');
          celulas.eq(4).html(renderizarAjusteTabela(item.lan_desconto, item.lan_valorinformado, item.lan_tipodesconto));
          celulas.eq(5).html(renderizarAjusteTabela(item.lan_acrescimo, item.lan_valorinformado, item.lan_tipoacrescimo));
        });
        modal.find('#valorRestanteLancamentoTitulo').text(`R$ ${dinheiroFinanceiro(valorRestante())}`);
        modal.find('#boxPlanejamentoLancamentoTitulo').toggle(valorRestante() > 0);
        modal.find('#btnConfirmarLancamentosTitulo').prop('disabled', !lancamentos.length);
      }
      function atualizarCondicoes() {
        const formaId = Number(modal.find('#lan_idformapagamento').val());
        const lista = condicoesPagamento.filter(item => Number(item.formaId) === formaId);
        modal.find('#lan_idcondicaopgto').html(opcoesFinanceiro(lista, lista.length ? 'Selecione a condição' : 'Nenhuma condição disponível'));
        if (lista[0]) modal.find('#lan_idcondicaopgto').val(lista[0].id);
      }
      function selecionarCentroCustoLancamentoTitulo(id) {
        centroCustoSelecionado = centrosCusto.find(item => String(item.id) === String(id)) || null;
        if (!centroCustoSelecionado) return false;
        modal.find('#buscaCentroCustoLancamentoTitulo').val(centroCustoSelecionado.nome);
        modal.find('#resultadosCentroCustoLancamentoTitulo').hide();
        modal.find('#mensagemCentroCustoLancamentoTitulo').empty();
        modal.find('#btnSelecionarCentroCustoLancamentoTitulo').prop('disabled', false);
        return true;
      }
      function focarProximoCadastroFinanceiro() {
        if (contasFinanceiras.length !== 1) {
          setTimeout(() => modal.find('#lan_idcontafinanceira').trigger('focus'), intervaloAutomacao);
          return;
        }
        modal.find('#lan_idcontafinanceira').val(contasFinanceiras[0].id);
        setTimeout(() => {
          if (formasPagamento.length !== 1) {
            modal.find('#lan_idformapagamento').trigger('focus');
            return;
          }
          modal.find('#lan_idformapagamento').val(formasPagamento[0].id);
          atualizarCondicoes();
          setTimeout(() => modal.find('#lan_valorinformado').trigger('focus'), intervaloAutomacao);
        }, intervaloAutomacao);
      }
      function valorAjustado() {
        const base = valorFinanceiro(modal.find('#lan_valorinformado').val());
        const tipoDesconto = modal.find('#lan_tipodesconto').val();
        const valorDesconto = valorFinanceiro(modal.find('#lan_valordesconto').val());
        const tipoAcrescimo = modal.find('#lan_tipoacrescimo').val();
        const valorAcrescimo = valorFinanceiro(modal.find('#lan_valoracrescimo').val());
        const desconto = tipoDesconto === 'Percentual' ? base * valorDesconto / 100 : (tipoDesconto === 'Valor' ? valorDesconto : 0);
        const acrescimo = tipoAcrescimo === 'Percentual' ? base * valorAcrescimo / 100 : (tipoAcrescimo === 'Valor' ? valorAcrescimo : 0);
        return { base, tipoDesconto, valorDesconto, desconto, tipoAcrescimo, valorAcrescimo, acrescimo, final: Math.max(0, base - desconto + acrescimo) };
      }
      function atualizarPreviewValorFinalLancamento() {
        const ajuste = valorAjustado();
        modal.find('#previewValorFinalLancamentoTitulo').text(`R$ ${dinheiroFinanceiro(ajuste.final)}`);
      }
      function renderizarPlanejamento() {
        modal.find('#stepCentroCustoLancamentoTitulo').addClass('d-none');
        modal.find('#stepPlanejamentoLancamentoTitulo').removeClass('d-none').html(`${resumoCentroCusto()}<form id="formPlanejamentoLancamentoTitulo" class="needs-validation" novalidate><div id="boxPlanejamentoLancamentoTitulo" class="card border-phsa-light box-phsa-shadow-light"><div class="card-body"><div class="small text-uppercase text-muted mb-3">Planejamento do ${tituloAcao}</div><div class="row g-3">
          <div class="col-12 col-md-4"><div class="form-floating"><select class="form-select" id="lan_idcontafinanceira" required>${opcoesFinanceiro(contasFinanceiras, 'Selecione')}</select><label for="lan_idcontafinanceira">Conta financeira</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
          <div class="col-12 col-md-4"><div class="form-floating"><select class="form-select" id="lan_idformapagamento" required>${opcoesFinanceiro(formasPagamento, 'Selecione')}</select><label for="lan_idformapagamento">Forma de pagamento</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
          <div class="col-12 col-md-4"><div class="form-floating"><select class="form-select" id="lan_idcondicaopgto" required><option value="">Selecione</option></select><label for="lan_idcondicaopgto">Condição de pagamento</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
          <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="preservarFormularioLancamentoTitulo"><label class="form-check-label" for="preservarFormularioLancamentoTitulo">Preservar formulário ao adicionar</label><div class="small text-muted">Mantém conta financeira, forma e condição após adicionar o lançamento.</div></div></div>
          <div class="col-12 col-md-4"><div class="form-floating"><input type="text" class="form-control" id="lan_observacao" maxlength="255"><label for="lan_observacao">Observação</label></div></div>
          <div class="col-12 col-md-4"><div class="form-floating"><input type="date" class="form-control text-center" id="lan_dtvencimento" value="${dataVencimentoPadrao}" required><label for="lan_dtvencimento">Data de vencimento</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
          <div class="col-12 col-md-4"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="lan_valorinformado" value="" required><label for="lan_valorinformado">Valor do lançamento</label><div class="invalid-feedback">Informe um valor maior que zero.</div></div></div>
          <div class="col-12 d-flex flex-wrap justify-content-end gap-2"><button type="button" class="bto bto-phsa-light" id="btnAplicarAcrescimoLancamentoTitulo"><i class="fa-solid fa-plus"></i>&nbsp;Aplicar acréscimo</button><button type="button" class="bto bto-phsa-light" id="btnAplicarDescontoLancamentoTitulo"><i class="fa-solid fa-percent"></i>&nbsp;Aplicar desconto</button></div>
          <div class="col-12 ${ajustesVisiveis ? '' : 'd-none'}" id="ajustesLancamentoTitulo"><div class="border rounded p-3"><div class="row g-3"><div class="col-6 col-md-3"><div class="form-floating"><select class="form-select" id="lan_tipodesconto"><option value="">Sem desconto</option><option value="Valor">Valor</option><option value="Percentual">Percentual</option></select><label for="lan_tipodesconto">Desconto</label></div></div><div class="col-6 col-md-3"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="lan_valordesconto" value="0,00"><label for="lan_valordesconto">Valor desconto</label></div></div><div class="col-6 col-md-3"><div class="form-floating"><select class="form-select" id="lan_tipoacrescimo"><option value="">Sem acréscimo</option><option value="Valor">Valor</option><option value="Percentual">Percentual</option></select><label for="lan_tipoacrescimo">Acréscimo</label></div></div><div class="col-6 col-md-3"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="lan_valoracrescimo" value="0,00"><label for="lan_valoracrescimo">Valor acréscimo</label></div></div></div></div></div>
          <div class="col-12 d-flex justify-content-between gap-2"><button type="button" class="bto bto-phsa-light" id="btnVoltarCentroCustoLancamentoTitulo"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</button><button type="button" class="bto bto-phsa" id="btnAdicionarLancamentoTitulo"><i class="fa-solid fa-plus"></i>&nbsp;Adicionar lançamento</button></div></div></div></div></form><div id="previewGerarLancamentoTitulo"></div><div class="text-end mt-3"><button type="button" class="bto bto-phsa" id="btnConfirmarLancamentosTitulo" disabled><i class="fa-solid fa-check"></i>&nbsp;Confirmar lançamentos</button></div>`);
        const botoesAjuste = modal.find('#btnAplicarAcrescimoLancamentoTitulo, #btnAplicarDescontoLancamentoTitulo');
        const origemBotoesAjuste = botoesAjuste.first().parent();
        const areaAcoes = modal.find('#btnAdicionarLancamentoTitulo').parent();
        const botaoVoltar = modal.find('#btnVoltarCentroCustoLancamentoTitulo').detach();
        const grupoAcoes = $('<div class="d-flex flex-wrap justify-content-end gap-2"></div>');
        grupoAcoes.append(botoesAjuste.detach()).append(modal.find('#btnAdicionarLancamentoTitulo').detach());
        areaAcoes.empty().append(botaoVoltar).append(grupoAcoes);
        origemBotoesAjuste.remove();

        const linhaAjustes = modal.find('#ajustesLancamentoTitulo .row').first();
        modal.find('#ajustesLancamentoTitulo > .border.rounded.p-3').removeClass('border rounded p-3');
        const camposAjuste = linhaAjustes.children().detach();
        const areaCamposAjuste = $('<div class="col-12 col-md-8"><div class="row g-3"></div></div>');
        camposAjuste.each(function () {
          const campo = $(this);
          const ehDesconto = campo.find('#lan_tipodesconto, #lan_valordesconto').length > 0;
          campo.removeClass('col-6 col-md-3').addClass('col-12 col-md-6').attr('data-ajuste-grupo', ehDesconto ? 'desconto' : 'acrescimo').addClass('d-none');
          areaCamposAjuste.find('.row').append(campo);
        });
        linhaAjustes.append(areaCamposAjuste).append('<div class="col-12 col-md-4 d-flex flex-column justify-content-center align-items-md-end text-md-end"><div class="small text-uppercase text-muted">Valor final</div><div class="h5 text-danger fw-bold mb-0" id="previewValorFinalLancamentoTitulo">R$ 0,00</div></div>');
        modal.find('.dinheiro').mask('#.##0,00', { reverse: true });
        atualizarPreviewValorFinalLancamento();
        setTimeout(() => {
          atualizarCondicoes();
          renderizarPrevia();
          focarProximoCadastroFinanceiro();
        }, intervaloAutomacao);
      }
      modal.find('#buscaCentroCustoLancamentoTitulo').on('focus input', function () { renderizarCentrosCusto($(this).val()); });
      modal.on('click', '.js-selecionar-centro-custo', function () { selecionarCentroCustoLancamentoTitulo($(this).data('id')); });
      modal.find('#btnSelecionarCentroCustoLancamentoTitulo').on('click', function () { if (centroCustoSelecionado) renderizarPlanejamento(); else modal.find('#mensagemCentroCustoLancamentoTitulo').text('Selecione um centro de custo.'); });
      modal.on('click', '#btnVoltarCentroCustoLancamentoTitulo', function () { ajustesVisiveis = false; modal.find('#stepPlanejamentoLancamentoTitulo').addClass('d-none').empty(); modal.find('#stepCentroCustoLancamentoTitulo').removeClass('d-none'); });
      modal.on('click', '#btnAplicarAcrescimoLancamentoTitulo, #btnAplicarDescontoLancamentoTitulo', function () {
        ajustesVisiveis = true;
        const ajustes = modal.find('#ajustesLancamentoTitulo').removeClass('d-none');
        const camposDesconto = ajustes.find('[data-ajuste-grupo="desconto"]');
        const camposAcrescimo = ajustes.find('[data-ajuste-grupo="acrescimo"]');
        if ($(this).is('#btnAplicarDescontoLancamentoTitulo')) camposDesconto.removeClass('d-none');
        else camposAcrescimo.removeClass('d-none');
        $(this).addClass('d-none');
      });
      modal.on('change', '#lan_idcontafinanceira', function () {
        setTimeout(() => {
          if (formasPagamento.length === 1) {
            modal.find('#lan_idformapagamento').val(formasPagamento[0].id);
            atualizarCondicoes();
            setTimeout(() => modal.find('#lan_valorinformado').trigger('focus'), intervaloAutomacao);
          } else {
            modal.find('#lan_idformapagamento').trigger('focus');
          }
        }, intervaloAutomacao);
      });
      modal.on('change', '#lan_idformapagamento', function () {
        setTimeout(() => {
          atualizarCondicoes();
          if (formasPagamento.length === 1) modal.find('#lan_valorinformado').trigger('focus');
        }, intervaloAutomacao);
      });
      modal.on('input', '#lan_valorinformado', function () { this.setCustomValidity(''); });
      modal.on('input change', '#lan_valorinformado, #lan_tipodesconto, #lan_valordesconto, #lan_tipoacrescimo, #lan_valoracrescimo', atualizarPreviewValorFinalLancamento);
      modal.on('click', '#btnAdicionarLancamentoTitulo', function () {
        const formulario = modal.find('#formPlanejamentoLancamentoTitulo')[0];
        const ajuste = valorAjustado();
        const valorInput = modal.find('#lan_valorinformado')[0];
        valorInput.setCustomValidity(ajuste.base > 0 ? '' : 'Informe um valor maior que zero.');
        if (!formulario.checkValidity() || !centroCustoSelecionado) { $(formulario).addClass('was-validated'); return; }
        const condicao = condicoesPagamento.find(item => String(item.id) === String(modal.find('#lan_idcondicaopgto').val()));
        const quantidade = Math.max(1, Math.min(120, Number(condicao && condicao.parcelas) || 1));
        const intervalo = Math.max(1, Number(condicao && condicao.intervalo) || 30);
        const grupo = quantidade > 1 ? `TIT-${tituloFinanceiroId}-${Date.now()}` : '';
        for (let indice = 0; indice < quantidade; indice += 1) lancamentos.push({ lan_idcontafinanceira: Number(modal.find('#lan_idcontafinanceira').val()), lan_idcentrocusto: Number(centroCustoSelecionado.id), lan_idformapagamento: Number(modal.find('#lan_idformapagamento').val()), lan_idcondicaopgto: Number(modal.find('#lan_idcondicaopgto').val()), lan_dtvencimento: dataParceladaFinanceiro(modal.find('#lan_dtvencimento').val(), indice * intervalo), lan_valorinformado: ajuste.base, lan_tipodesconto: ajuste.tipoDesconto, lan_valordesconto: ajuste.valorDesconto, lan_desconto: ajuste.desconto, lan_tipoacrescimo: ajuste.tipoAcrescimo, lan_valoracrescimo: ajuste.valorAcrescimo, lan_acrescimo: ajuste.acrescimo, lan_valor: ajuste.final, lan_observacao: modal.find('#lan_observacao').val(), lan_grupo_parcelamento: grupo, lan_parcela: indice + 1, lan_totalparcela: quantidade });
        renderizarPrevia();
        const preservar = modal.find('#preservarFormularioLancamentoTitulo').prop('checked');
        if (!preservar) {
          modal.find('#lan_idcontafinanceira, #lan_idformapagamento').val('');
          modal.find('#lan_idcondicaopgto').html('<option value="">Selecione</option>');
          modal.find('#lan_observacao').val('');
        }
        modal.find('#lan_valorinformado').val('');
      });
      modal.on('click', '[data-indice].bto-phsa-light', function () {
        const indice = Number($(this).data('indice'));
        Swal.fire({
          title: 'Remover lançamento?',
          text: 'O lançamento será removido da prévia atual.',
          icon: 'warning',
          showCancelButton: true,
          reverseButtons: true,
          cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar',
          confirmButtonText: '<i class="fa-solid fa-check"></i>&nbsp;Confirmar',
          customClass: { cancelButton: 'bto bto-phsa-light', confirmButton: 'bto bto-phsa' }
        }).then(function (resultado) {
          if (!resultado.isConfirmed) return;
          lancamentos.splice(indice, 1);
          renderizarPrevia();
        });
      });
      modal.on('click', '#btnConfirmarLancamentosTitulo', function () {
        const botao = $(this);
        Swal.fire({
          title: 'Registrar lançamentos?',
          text: 'Deseja registrar os lançamentos deste título?',
          icon: 'warning',
          showCancelButton: true,
          reverseButtons: true,
          cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar',
          confirmButtonText: '<i class="fa-solid fa-check-double"></i>&nbsp;Confirmar',
          customClass: { cancelButton: 'bto bto-phsa-light', confirmButton: 'bto bto-phsa' }
        }).then(function (resultado) {
          if (!resultado.isConfirmed) return;
          botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Confirmando');
          $.ajax({ url: urlGerarLancamento, method: 'POST', dataType: 'json', data: { tit_id: tituloFinanceiroId, lancamentos: JSON.stringify(lancamentos) } }).done(function (response) { swResponse(response); if (response && response.status) setTimeout(() => window.location.reload(), 800); else botao.prop('disabled', false).html('<i class="fa-solid fa-check"></i>&nbsp;Confirmar lançamentos'); }).fail(function () { botao.prop('disabled', false).html('<i class="fa-solid fa-check"></i>&nbsp;Confirmar lançamentos'); swError('Não foi possível gerar os lançamentos.'); });
        });
      });
      if (centrosCusto.length === 1 && selecionarCentroCustoLancamentoTitulo(centrosCusto[0].id)) {
        renderizarPlanejamento();
      }
    }
    $('#btnGerarLancamentoTitulo').on('click', abrirModalGerarLancamento);
    $(document).on('click', '.js-baixar-lancamento', function () {
      if (!podeBaixarLancamento) return;
      const id = Number($(this).data('id'));
      const status = String($(this).data('status') || '');
      const recebimento = status === 'A receber';
      const operacao = recebimento ? 'recebimento' : 'pagamento';
      const participio = recebimento ? 'recebido' : 'pago';
      const tituloOperacao = recebimento ? 'Receber lançamento' : 'Pagar lançamento';
      const dataHoje = '<?= date('Y-m-d') ?>';
      Swal.fire({
        icon: 'question',
        title: tituloOperacao,
        html: `<div class="text-start"><p class="mb-3">Confirme o ${operacao} deste lançamento informando a data em que o valor foi ${participio}.</p><label for="dataBaixaLancamento" class="form-label fw-semibold">Data do ${operacao}</label><input type="date" id="dataBaixaLancamento" class="form-control text-center" value="" max="${dataHoje}" required></div>`,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i>&nbsp;Confirmar',
        cancelButtonText: '<i class="fa-solid fa-arrow-left"></i>&nbsp;Sair',
        customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' },
        reverseButtons: true,
        allowOutsideClick: false,
        focusConfirm: false,
        didOpen: function () { $('#dataBaixaLancamento').trigger('focus'); },
        preConfirm: function () {
          const data = String($('#dataBaixaLancamento').val() || '').trim();
          if (!data) {
            Swal.showValidationMessage(`Informe a data do ${operacao}.`);
            return false;
          }
          if (data > dataHoje) {
            Swal.showValidationMessage('A data não pode ser maior que o dia atual.');
            return false;
          }
          return data;
        }
      }).then(function (resultado) {
        if (!resultado.isConfirmed) return;
        $.ajax({ url: urlBaixarLancamento, method: 'POST', dataType: 'json', data: { ids: [id], lan_dtbaixa: resultado.value } }).done(function (response) { swResponse(response); if (response && response.status) setTimeout(() => window.location.reload(), 800); }).fail(function () { swError('Não foi possível registrar a operação.'); });
      });
    });
    $(document).on('click', '.js-detalhes-lancamento', function () {
      const id = Number($(this).data('id') || 0);
      if (!id) return;
      $.ajax({ url: urlDetalhesLancamento, method: 'POST', data: { lan_id: id } })
        .done(function (html) {
          const conteudo = $('<div>').append($.parseHTML(html, document, true));
          const rodape = $('<div class="d-flex w-100 align-items-center justify-content-between gap-2 flex-wrap"></div>').append(conteudo.find('#footerDetalhesLancamento').contents()).prop('outerHTML');
          conteudo.find('#footerDetalhesLancamento').remove();
          SimpleModal.mostrar({
            titulo: `Detalhes do lançamento #${id}`,
            tamanho: 'modal-xl modal-dialog-scrollable',
            rodape,
            corpo: conteudo.html()
          });
        })
        .fail(function () { swError('Não foi possível carregar os detalhes do lançamento.'); });
    });

    function recarregarLancamentoAposAcao(response) {
      swResponse(response);
      if (response && response.status) setTimeout(() => window.location.reload(), 800);
    }

    $(document).on('click', '.js-imprimir-detalhes-lancamento', function () {
      if (typeof fgbGerarImpressao === 'function') {
        fgbGerarImpressao('detalhesLancamento');
      } else {
        window.print();
      }
    });

    $(document).on('change', '#arquivoLancamentoInput', function () {
      const arquivo = this.files && this.files[0];
      const idLancamento = Number($(this).data('id') || 0);
      if (!arquivo || !idLancamento) return;
      const leitor = new FileReader();
      leitor.onload = function (evento) {
        const base64 = String(evento.target.result || '');
        $.ajax({
          url: urlSalvarArquivoLancamento,
          method: 'POST',
          dataType: 'json',
          data: {
            arq_id: 0,
            arq_origem: 'lancamento',
            arq_idorigem: idLancamento,
            arq_nome: arquivo.name,
            arq_nomeoriginal: arquivo.name,
            arq_tipo: arquivo.type && arquivo.type.startsWith('image/') ? 'imagem' : 'arquivo',
            arq_base64: base64
          }
        }).done(function (response) {
          swResponse(response);
          if (response && response.status) setTimeout(() => window.location.reload(), 800);
        }).fail(function () {
          swError('Não foi possível anexar o arquivo.');
        });
      };
      leitor.readAsDataURL(arquivo);
      $(this).val('');
    });

    $(document).on('click', '.js-estornar-lancamento', function () {
      const id = Number($(this).data('id') || 0);
      const modalDetalhes = document.querySelector('.modal.show');
      Swal.fire({
        target: modalDetalhes || document.body,
        icon: 'warning',
        title: 'Estornar lançamento?',
        html: '<div class="text-start"><p class="mb-3">O lançamento atual será marcado como estornado e um novo lançamento pendente será criado para manter o título consistente.</p><label for="motivoEstornoLancamento" class="form-label fw-semibold">Motivo</label><textarea id="motivoEstornoLancamento" class="form-control" rows="3" maxlength="255" minlength="10" placeholder="Informe o motivo do estorno"></textarea></div>',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: '<i class="fa-solid fa-rotate-left"></i>&nbsp;Estornar',
        cancelButtonText: '<i class="fa-solid fa-arrow-left"></i>&nbsp;Sair',
        customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' },
        allowOutsideClick: false,
        focusConfirm: false,
        didOpen: function () {
          const campoMotivo = Swal.getPopup().querySelector('#motivoEstornoLancamento');
          if (campoMotivo) campoMotivo.focus();
        },
        preConfirm: function () {
          const motivo = String($('#motivoEstornoLancamento').val() || '').trim();
          if (motivo.length < 10) {
            Swal.showValidationMessage('Informe um motivo com no mínimo 10 caracteres.');
            return false;
          }
          return motivo;
        }
      }).then(function (resultado) {
        if (!resultado.isConfirmed) return;
        $.ajax({ url: urlEstornarLancamento, method: 'POST', dataType: 'json', data: { lan_id: id, motivo: resultado.value } })
          .done(recarregarLancamentoAposAcao)
          .fail(function () { swError('Não foi possível estornar o lançamento.'); });
      });
    });

    $(document).on('click', '.js-corrigir-lancamento', function () {
      const id = Number($(this).data('id') || 0);
      if (!id) return;
      $.ajax({ url: urlModalCorrecaoLancamento, method: 'POST', data: { lan_id: id } })
        .done(function (html) {
          const modalId = SimpleModal.mostrar({
            titulo: `Corrigir lançamento #${id}`,
            tamanho: 'modal-xl modal-dialog-scrollable',
            rodape: 'hide',
            corpo: html
          });
          const modal = SimpleModal.modais[modalId];
          let centroCustoSelecionado = null;
          let lancamentoPlanejado = null;
          let ajustesVisiveis = false;
          const tituloAcao = naturezaTitulo === 'Entrada' ? 'recebimento' : 'pagamento';
          const valorOriginal = Number(modal.find('#correcaoValorOriginal').val() || 0);

          function renderizarCentrosCusto(filtro) {
            const texto = String(filtro || '').trim().toLowerCase();
            const lista = centrosCusto.filter(item => `${item.nome} ${item.id}`.toLowerCase().includes(texto));
            const html = lista.length
              ? lista.map(item => `<button type="button" class="list-group-item list-group-item-action text-start js-selecionar-centro-custo-correcao" data-id="${item.id}"><span class="d-block fw-semibold">${escapeFinanceiro(item.nome)}</span><span class="d-block small text-muted">Centro de custo · código ${item.id}</span></button>`).join('')
              : '<div class="list-group-item text-muted">Nenhum centro de custo encontrado.</div>';
            modal.find('#resultadosCentroCustoCorrecao').html(html).show();
          }

          function selecionarCentroCusto(id) {
            centroCustoSelecionado = centrosCusto.find(item => String(item.id) === String(id)) || null;
            if (!centroCustoSelecionado) return false;
            modal.find('#buscaCentroCustoCorrecao').val(centroCustoSelecionado.nome);
            modal.find('#resultadosCentroCustoCorrecao').hide();
            modal.find('#mensagemCentroCustoCorrecao').empty();
            modal.find('#btnSelecionarCentroCustoCorrecao').prop('disabled', false);
            return true;
          }

          function resumoCentroCusto() {
            return `<div class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body d-flex align-items-center justify-content-between gap-3"><div><div class="small text-uppercase text-muted">Centro de custo</div><div class="h5 text-phsa fw-bold mb-0">${escapeFinanceiro(centroCustoSelecionado.nome)}</div></div><div class="text-end"><div class="small text-uppercase text-muted">Valor em correção</div><div class="h5 text-danger fw-bold mb-0">R$ ${dinheiroFinanceiro(valorOriginal)}</div></div></div></div>`;
          }

          function valorAjustado() {
            const base = valorFinanceiro(modal.find('#correcao_valorinformado').val());
            const tipoDesconto = modal.find('#correcao_tipodesconto').val();
            const valorDesconto = valorFinanceiro(modal.find('#correcao_valordesconto').val());
            const tipoAcrescimo = modal.find('#correcao_tipoacrescimo').val();
            const valorAcrescimo = valorFinanceiro(modal.find('#correcao_valoracrescimo').val());
            const desconto = tipoDesconto === 'Percentual' ? base * valorDesconto / 100 : (tipoDesconto === 'Valor' ? valorDesconto : 0);
            const acrescimo = tipoAcrescimo === 'Percentual' ? base * valorAcrescimo / 100 : (tipoAcrescimo === 'Valor' ? valorAcrescimo : 0);
            return { base, tipoDesconto, valorDesconto, desconto, tipoAcrescimo, valorAcrescimo, acrescimo, final: Math.max(0, base - desconto + acrescimo) };
          }

          function atualizarPreviewValorFinal() {
            const ajuste = valorAjustado();
            modal.find('#previewValorFinalCorrecao').text(`R$ ${dinheiroFinanceiro(ajuste.final)}`);
          }

          function renderizarPrevia() {
            if (!lancamentoPlanejado) {
              modal.find('#previewCorrecao').html('<div class="alert alert-light border mt-3 mb-0"><i class="fa-solid fa-circle-info"></i>&nbsp;Nenhum lançamento adicionado até o momento.</div>');
              modal.find('#btnConfirmarCorrecaoLancamento').prop('disabled', true);
              return;
            }
            const item = lancamentoPlanejado;
            const conta = contasFinanceiras.find(opcao => String(opcao.id) === String(item.lan_idcontafinanceira));
            const forma = formasPagamento.find(opcao => String(opcao.id) === String(item.lan_idformapagamento));
            const condicao = condicoesPagamento.find(opcao => String(opcao.id) === String(item.lan_idcondicaopgto));
            const data = item.lan_dtvencimento ? new Date(`${item.lan_dtvencimento}T12:00:00`).toLocaleDateString('pt-BR') : '—';
            const motivo = String(modal.find('[name="motivo"]').val() || '').trim();
            const motivoHtml = motivo ? `<tr><td colspan="8" class="small text-muted text-truncate" style="max-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="${escapeFinanceiro(motivo)}"><i class="fa-solid fa-comment-dots me-1"></i><strong>Motivo:</strong> ${escapeFinanceiro(motivo)}</td></tr>` : '';
            modal.find('#previewCorrecao').html(`<div class="card border-phsa-light box-phsa-shadow-light mt-3"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-2"><span class="small text-uppercase text-muted">Lançamento corrigido</span><span class="small text-muted">1 registro</span></div><div class="table-responsive"><table class="table table-sm small align-middle mb-0"><thead><tr><th>Conta</th><th>Forma/Condição</th><th>Vencimento</th><th class="text-end">Valor informado</th><th class="text-end">Desconto</th><th class="text-end">Acréscimo</th><th class="text-end">Valor final</th><th class="text-end">Ação</th></tr></thead><tbody><tr><td>${escapeFinanceiro(conta ? conta.nome : '—')}</td><td>${escapeFinanceiro([forma && forma.nome, condicao && condicao.nome].filter(Boolean).join(' / ') || '—')}</td><td>${data}</td><td class="text-end">R$ ${dinheiroFinanceiro(item.lan_valorinformado)}</td><td class="text-end">R$ ${dinheiroFinanceiro(item.lan_desconto)}</td><td class="text-end">R$ ${dinheiroFinanceiro(item.lan_acrescimo)}</td><td class="text-end fw-semibold">R$ ${dinheiroFinanceiro(item.lan_valor)}</td><td class="text-end"><button type="button" class="bto bto-phsa-light btn-sm" id="btnRemoverPlanejamentoCorrecao" title="Remover"><i class="fa-solid fa-xmark"></i></button></td></tr>${motivoHtml}</tbody></table></div></div></div>`);
            modal.find('#btnConfirmarCorrecaoLancamento').prop('disabled', false);
          }

          function atualizarCondicoes() {
            const formaId = Number(modal.find('#correcao_idformapagamento').val());
            const lista = condicoesPagamento.filter(item => Number(item.formaId) === formaId);
            modal.find('#correcao_idcondicaopgto').html(opcoesFinanceiro(lista, lista.length ? 'Selecione a condição' : 'Nenhuma condição disponível'));
            const inicial = Number(modal.find('#correcaoCondicaoInicial').val() || 0);
            if (lista.some(item => Number(item.id) === inicial)) modal.find('#correcao_idcondicaopgto').val(inicial);
            else if (lista[0]) modal.find('#correcao_idcondicaopgto').val(lista[0].id);
          }

          function renderizarPlanejamento() {
            modal.find('#stepCentroCustoCorrecao').addClass('d-none');
            modal.find('#stepPlanejamentoCorrecao').removeClass('d-none').html(`${resumoCentroCusto()}<form id="formCorrecaoLancamento" class="needs-validation" novalidate><input type="hidden" name="lan_id" value="${id}"><input type="hidden" name="lan_idcentrocusto" id="correcao_idcentrocusto" value="${centroCustoSelecionado.id}"><div id="boxPlanejamentoCorrecao" class="card border-phsa-light box-phsa-shadow-light"><div class="card-body"><div class="small text-uppercase text-muted mb-3">Planejamento do ${tituloAcao}</div><div class="row g-3">
              <div class="col-12 col-md-4"><div class="form-floating"><select class="form-select" id="correcao_idcontafinanceira" name="lan_idcontafinanceira" required>${opcoesFinanceiro(contasFinanceiras, 'Selecione')}</select><label for="correcao_idcontafinanceira">Conta financeira</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
              <div class="col-12 col-md-4"><div class="form-floating"><select class="form-select" id="correcao_idformapagamento" name="lan_idformapagamento" required>${opcoesFinanceiro(formasPagamento, 'Selecione')}</select><label for="correcao_idformapagamento">Forma de pagamento</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
              <div class="col-12 col-md-4"><div class="form-floating"><select class="form-select" id="correcao_idcondicaopgto" name="lan_idcondicaopgto" required><option value="">Selecione</option></select><label for="correcao_idcondicaopgto">Condição de pagamento</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
              <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="preservarFormularioCorrecao"><label class="form-check-label" for="preservarFormularioCorrecao">Preservar formulário ao adicionar</label><div class="small text-muted">Mantém conta financeira, forma e condição após adicionar o lançamento.</div></div></div>
              <div class="col-12 col-md-4"><div class="form-floating"><input type="text" class="form-control" id="correcao_observacao" name="lan_observacao" maxlength="255"><label for="correcao_observacao">Observação</label></div></div>
              <div class="col-12 col-md-4"><div class="form-floating"><input type="date" class="form-control text-center" id="correcao_dtvencimento" name="lan_dtvencimento" required><label for="correcao_dtvencimento">Data de vencimento</label><div class="invalid-feedback">Campo obrigatório.</div></div></div>
              <div class="col-12 col-md-4"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="correcao_valorinformado" name="lan_valorinformado" required><label for="correcao_valorinformado">Valor do lançamento</label><div class="invalid-feedback">Informe um valor maior que zero.</div></div></div>
              <div class="col-12 d-flex flex-wrap justify-content-end gap-2"><button type="button" class="bto bto-phsa-light" id="btnAplicarAcrescimoCorrecao"><i class="fa-solid fa-plus"></i>&nbsp;Aplicar acréscimo</button><button type="button" class="bto bto-phsa-light" id="btnAplicarDescontoCorrecao"><i class="fa-solid fa-percent"></i>&nbsp;Aplicar desconto</button></div>
              <div class="col-12 ${ajustesVisiveis ? '' : 'd-none'}" id="ajustesCorrecao"><div class="border rounded p-3"><div class="row g-3"><div class="col-6 col-md-3 d-none" data-ajuste-grupo="desconto"><div class="form-floating"><select class="form-select" id="correcao_tipodesconto" name="lan_tipodesconto"><option value="">Sem desconto</option><option value="Valor">Valor</option><option value="Percentual">Percentual</option></select><label for="correcao_tipodesconto">Desconto</label></div></div><div class="col-6 col-md-3 d-none" data-ajuste-grupo="desconto"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="correcao_valordesconto" name="lan_valordesconto" value="0,00"><label for="correcao_valordesconto">Valor desconto</label></div></div><div class="col-6 col-md-3 d-none" data-ajuste-grupo="acrescimo"><div class="form-floating"><select class="form-select" id="correcao_tipoacrescimo" name="lan_tipoacrescimo"><option value="">Sem acréscimo</option><option value="Valor">Valor</option><option value="Percentual">Percentual</option></select><label for="correcao_tipoacrescimo">Acréscimo</label></div></div><div class="col-6 col-md-3 d-none" data-ajuste-grupo="acrescimo"><div class="form-floating"><input type="text" class="form-control dinheiro text-end" id="correcao_valoracrescimo" name="lan_valoracrescimo" value="0,00"><label for="correcao_valoracrescimo">Valor acréscimo</label></div></div></div></div></div>
              <div class="col-12"><div class="form-floating"><textarea class="form-control" name="motivo" maxlength="255" minlength="10" style="height:100px" required placeholder="Motivo da correção"></textarea><label>Motivo da correção</label><div class="invalid-feedback">Informe um motivo com no mínimo 10 caracteres.</div></div></div>
              <div class="col-12 d-flex justify-content-between gap-2"><button type="button" class="bto bto-phsa-light" id="btnVoltarCentroCustoCorrecao"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</button><button type="button" class="bto bto-phsa" id="btnAdicionarCorrecao"><i class="fa-solid fa-plus"></i>&nbsp;Adicionar lançamento</button></div></div></div></div></form><div id="previewCorrecao"></div><div class="text-end mt-3"><button type="button" class="bto bto-phsa" id="btnConfirmarCorrecaoLancamento" disabled><i class="fa-solid fa-check"></i>&nbsp;Confirmar correção</button></div>`);
            const botoesAjuste = modal.find('#btnAplicarAcrescimoCorrecao, #btnAplicarDescontoCorrecao');
            const origemBotoesAjuste = botoesAjuste.first().parent();
            const areaAcoes = modal.find('#btnAdicionarCorrecao').parent();
            const botaoVoltar = modal.find('#btnVoltarCentroCustoCorrecao').detach();
            const grupoAcoes = $('<div class="d-flex flex-wrap justify-content-end gap-2"></div>');
            grupoAcoes.append(botoesAjuste.detach()).append(modal.find('#btnAdicionarCorrecao').detach());
            areaAcoes.empty().append(botaoVoltar).append(grupoAcoes);
            origemBotoesAjuste.remove();

            const linhaAjustes = modal.find('#ajustesCorrecao .row').first();
            modal.find('#ajustesCorrecao > .border.rounded.p-3').removeClass('border rounded p-3');
            const camposAjuste = linhaAjustes.children().detach();
            const areaCamposAjuste = $('<div class="col-12 col-md-8"><div class="row g-3"></div></div>');
            camposAjuste.each(function () {
              const campo = $(this);
              campo.removeClass('col-6 col-md-3').addClass('col-12 col-md-6');
              areaCamposAjuste.find('.row').append(campo);
            });
            linhaAjustes.append(areaCamposAjuste).append('<div class="col-12 col-md-4 d-flex flex-column justify-content-center align-items-md-end text-md-end"><div class="small text-uppercase text-muted">Valor final</div><div class="h5 text-danger fw-bold mb-0" id="previewValorFinalCorrecao">R$ 0,00</div></div>');
            modal.find('.dinheiro').mask('#.##0,00', { reverse: true });
            modal.find('#correcao_idcontafinanceira').val(modal.find('#correcaoContaInicial').val());
            modal.find('#correcao_idformapagamento').val(modal.find('#correcaoFormaInicial').val());
            modal.find('#correcao_dtvencimento').val(modal.find('#correcaoVencimentoInicial').val());
            modal.find('#correcao_valorinformado').val(modal.find('#correcaoValorInformadoInicial').val());
            modal.find('#correcao_observacao').val(modal.find('#correcaoObservacaoInicial').val());
            modal.find('#correcao_tipodesconto').val(modal.find('#correcaoTipoDescontoInicial').val());
            modal.find('#correcao_valordesconto').val(modal.find('#correcaoValorDescontoInicial').val());
            modal.find('#correcao_tipoacrescimo').val(modal.find('#correcaoTipoAcrescimoInicial').val());
            modal.find('#correcao_valoracrescimo').val(modal.find('#correcaoValorAcrescimoInicial').val());
            atualizarCondicoes();
            atualizarPreviewValorFinal();
            renderizarPrevia();
          }

          modal.find('#buscaCentroCustoCorrecao').on('focus input', function () { renderizarCentrosCusto($(this).val()); });
          modal.on('click', '.js-selecionar-centro-custo-correcao', function () { selecionarCentroCusto($(this).data('id')); });
          modal.on('click', '#btnSelecionarCentroCustoCorrecao', function () { if (centroCustoSelecionado) renderizarPlanejamento(); else modal.find('#mensagemCentroCustoCorrecao').text('Selecione um centro de custo.'); });
          modal.on('click', '#btnVoltarCentroCustoCorrecao', function () { lancamentoPlanejado = null; ajustesVisiveis = false; modal.find('#stepPlanejamentoCorrecao').addClass('d-none').empty(); modal.find('#stepCentroCustoCorrecao').removeClass('d-none'); });
          modal.on('click', '#btnAplicarAcrescimoCorrecao, #btnAplicarDescontoCorrecao', function () { ajustesVisiveis = true; modal.find('#ajustesCorrecao').removeClass('d-none'); const grupo = $(this).is('#btnAplicarDescontoCorrecao') ? 'desconto' : 'acrescimo'; modal.find(`#ajustesCorrecao [data-ajuste-grupo="${grupo}"]`).removeClass('d-none'); $(this).addClass('d-none'); });
          modal.on('change', '#correcao_idformapagamento', atualizarCondicoes);
          modal.on('input change', '#formCorrecaoLancamento input:not([type="hidden"]):not([name="motivo"]), #formCorrecaoLancamento select', function () { lancamentoPlanejado = null; atualizarPreviewValorFinal(); renderizarPrevia(); });
          modal.on('input', '#formCorrecaoLancamento textarea[name="motivo"]', renderizarPrevia);
          modal.on('click', '#btnAdicionarCorrecao', function () {
            const form = modal.find('#formCorrecaoLancamento')[0];
            const ajuste = valorAjustado();
            const valorInput = modal.find('#correcao_valorinformado')[0];
            valorInput.setCustomValidity(ajuste.base > 0 ? '' : 'Informe um valor maior que zero.');
            if (!form.checkValidity() || !centroCustoSelecionado) { $(form).addClass('was-validated'); return; }
            lancamentoPlanejado = { lan_idcontafinanceira: Number(modal.find('#correcao_idcontafinanceira').val()), lan_idcentrocusto: Number(centroCustoSelecionado.id), lan_idformapagamento: Number(modal.find('#correcao_idformapagamento').val()), lan_idcondicaopgto: Number(modal.find('#correcao_idcondicaopgto').val()), lan_dtvencimento: modal.find('#correcao_dtvencimento').val(), lan_valorinformado: ajuste.base, lan_tipodesconto: ajuste.tipoDesconto, lan_valordesconto: ajuste.valorDesconto, lan_desconto: ajuste.desconto, lan_tipoacrescimo: ajuste.tipoAcrescimo, lan_valoracrescimo: ajuste.valorAcrescimo, lan_acrescimo: ajuste.acrescimo, lan_valor: ajuste.final };
            renderizarPrevia();
          });
          modal.on('click', '#btnRemoverPlanejamentoCorrecao', function () { lancamentoPlanejado = null; renderizarPrevia(); });
          modal.on('click', '#btnConfirmarCorrecaoLancamento', function () {
            if (!lancamentoPlanejado) return;
            const form = modal.find('#formCorrecaoLancamento');
            if (!form[0].checkValidity()) { form.addClass('was-validated'); return; }
            const botao = $(this);
            botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando');
            $.ajax({ url: urlCorrigirLancamento, method: 'POST', dataType: 'json', data: form.serialize() })
              .done(function (response) { recarregarLancamentoAposAcao(response); })
              .fail(function () { botao.prop('disabled', false).html('<i class="fa-solid fa-check"></i>&nbsp;Confirmar correção'); swError('Não foi possível corrigir o lançamento.'); });
          });
          if (centrosCusto.length === 1 && selecionarCentroCusto(centrosCusto[0].id)) renderizarPlanejamento();
        })
        .fail(function () { swError('Não foi possível carregar o formulário de correção.'); });
    });
    $('#formTitulo').on('input change', 'input:not([type="hidden"]), textarea, select', atualizarEstadoConfirmacaoTitulo);
    $('#btnConfirmarTitulo').on('click', function () {
      if (!podeConfirmarTitulo) return;

      Swal.fire({
        icon: 'warning',
        title: 'Confirmar título?',
        text: 'Após confirmar, ele passará a valer no fluxo financeiro.',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check-double"></i>&nbsp;Confirmar',
        cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar',
        customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' },
        reverseButtons: true,
        allowOutsideClick: false
      }).then(function (resultado) {
        if (!resultado.isConfirmed) return;

        $.ajax({
          url: urlConfirmar,
          method: 'POST',
          dataType: 'json',
          data: { tit_id: $('input[name="tit_id"]').val() }
        }).done(function (response) {
          swResponse(response);
          if (response && response.status) setTimeout(() => window.location.reload(), 800);
        }).fail(function () {
          swError('Não foi possível confirmar o título.');
        });
      });
    });
    $('#btnCancelarTitulo').on('click', function () {
      if (!podeCancelarTitulo) return;

      Swal.fire({
        icon: 'warning',
        title: 'Cancelar título?',
        html: '<div class="text-start"><label class="form-label fw-semibold mb-1">Motivo do cancelamento</label><textarea id="motivoCancelamentoTitulo" class="form-control" rows="3" maxlength="255" placeholder="Descreva o motivo (mínimo 10 caracteres)"></textarea><div id="erroMotivoCancelamentoTitulo" class="text-danger small mt-1 d-none">Informe um motivo com no mínimo 10 caracteres.</div></div>',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-ban"></i>&nbsp;Cancelar título',
        cancelButtonText: '<i class="fa-solid fa-arrow-left"></i>&nbsp;Sair',
        customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' },
        reverseButtons: true,
        allowOutsideClick: false,
        preConfirm: function () {
          const motivo = String($('#motivoCancelamentoTitulo').val() || '').trim();
          if (motivo.length < 10) {
            $('#erroMotivoCancelamentoTitulo').removeClass('d-none');
            return false;
          }
          return motivo;
        }
      }).then(function (resultado) {
        if (!resultado.isConfirmed) return;

        $.ajax({
          url: urlCancelar,
          method: 'POST',
          dataType: 'json',
          data: { tit_id: $('input[name="tit_id"]').val(), motivo: resultado.value }
        }).done(function (response) {
          swResponse(response);
          if (response && response.status) setTimeout(() => window.location.reload(), 800);
        }).fail(function () {
          swError('Não foi possível cancelar o título.');
        });
      });
    });
    $('#formTitulo').on('submit', function (event) {
      event.preventDefault();
      const form = $(this);
      const planoId = Number($('#tit_idplanoconta').val());
      const planoSelecionado = planos.find(item => String(item.id) === String(planoId));
      const vencimentoDesabilitado = $vencimentoTitulo.prop('disabled');
      if (vencimentoDesabilitado && !$vencimentoTitulo.val()) $vencimentoTitulo.val($referenciaTitulo.val() || '');

      if (!form[0].checkValidity() || !$referenciaTitulo.val() || !$vencimentoTitulo.val() || !planoSelecionado) {
        form.addClass('was-validated');
        if (!planoSelecionado) $('#tituloPlanoBusca').addClass('is-invalid');
        $vencimentoTitulo.toggleClass('is-invalid', !$vencimentoTitulo.val());
        return;
      }

      $('#tituloPlanoBusca').removeClass('is-invalid');
      if (planoSelecionado.natureza === 'Saida' && !Number($('#tit_idorigem').val())) {
        swWarning('É obrigatório vincular um fornecedor para títulos de saída.');
        return;
      }

      if (vencimentoDesabilitado) $vencimentoTitulo.prop('disabled', false);
      const dadosFormulario = form.serialize();
      if (vencimentoDesabilitado) $vencimentoTitulo.prop('disabled', true);
      const botao = form.find('button[type="submit"]');
      botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando');
      $.ajax({ url: urlSalvar, method: 'POST', dataType: 'json', data: dadosFormulario })
        .done(function (response) {
          swResponse(response, { timer: null });
          if (response.status && response.location) {
            setTimeout(() => window.location.replace(response.location), 2000);
            return;
          }
          botao.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar');
        })
        .fail(function () {
          botao.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar');
          swError('Não foi possível salvar o título.');
        });
    });
  });
</script>
<?php $this->end(); ?>
