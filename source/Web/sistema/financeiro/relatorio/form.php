<?php
  $e = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $moeda = static fn($valor): string => 'R$ ' . number_format((float) ($valor ?? 0), 2, ',', '.');
  $data = static function ($valor): string { $valor = trim((string) ($valor ?? '')); return $valor !== '' && strtotime($valor) ? date('d/m/Y', strtotime($valor)) : '—'; };
  $extrato = ($tipoRelatorio ?? '') === 'extratoMovimentacao';
  $selecionado = static fn($valor, array $itens): string => in_array((string) $valor, array_map('strval', $itens), true) ? 'selected' : '';
  $campoData = ['lan_dtbaixa' => 'Baixa do lançamento', 'lan_dtvencimento' => 'Vencimento do lançamento', 'lan_dtcad' => 'Cadastro do lançamento', 'tit_referencia' => 'Referência do título', 'tit_dtvencimento' => 'Vencimento do título'];
  $ordenacoes = ['ordem_extrato' => ['Ordem do extrato', 'Leitura cronológica do extrato.'], 'padrao' => ['Ordem padrão', 'Leitura padrão dos lançamentos financeiros.'], 'numero_identificacao' => ['Número de identificação', 'Ordenação pelo identificador do lançamento.'], 'mais_recentes' => ['Mais recentes primeiro', 'Movimentações mais recentes aparecem primeiro.'], 'maior_valor' => ['Maior valor primeiro', 'Movimentações de maior valor aparecem primeiro.'], 'menor_valor' => ['Menor valor primeiro', 'Movimentações de menor valor aparecem primeiro.'], 'conta_financeira' => ['Conta financeira', 'Ordenação alfabética pela conta financeira.'], 'plano_conta' => ['Plano de conta', 'Ordenação alfabética pelo plano de contas.'], 'centro_custo' => ['Centro de custo', 'Ordenação alfabética pelo centro de custo.'], 'natureza' => ['Natureza', 'Ordenação pela natureza da movimentação.'], 'status_lancamento' => ['Status do lançamento', 'Ordenação pelo status financeiro.'], 'titulo_financeiro' => ['Título financeiro', 'Ordenação alfabética pelo título financeiro.']];
  $ordenacaoAplicada = $ordenacoes[$filtros->op ?? 'ordem_extrato'] ?? $ordenacoes['ordem_extrato'];
?>
<?php $this->layout('sistema::sistema'); ?>

<div class="card shadow-sm"><div class="card-body p-3 p-md-4">
  <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4"><div><h4 class="m-0 text-uppercase"><?= $e($titulo) ?></h4><div class="text-muted small mt-1"><?= $e($descricao) ?></div></div><a href="<?= Helpers::url('financeiro/relatorio') ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a></div>
  <form method="get" class="needs-validation" novalidate>
    <div class="row g-3">
      <div class="col-12 col-md-3"><div class="form-floating"><select name="tipoFiltro" class="form-select input-phsa-focus input-phsa-hover"><option value="Periodo" <?= ($filtros->tipoFiltro ?? '') === 'Periodo' ? 'selected' : '' ?>>Período</option><option value="Exatidao" <?= ($filtros->tipoFiltro ?? '') === 'Exatidao' ? 'selected' : '' ?>>Exatidão</option></select><label>Tipo de filtro</label></div></div>
      <div class="col-12 col-md-3"><div class="form-floating"><select name="campoData" class="form-select input-phsa-focus input-phsa-hover"><?php foreach ($campoData as $valor => $label): ?><option value="<?= $e($valor) ?>" <?= ($filtros->campoData ?? '') === $valor ? 'selected' : '' ?>><?= $e($label) ?></option><?php endforeach; ?></select><label>Campo de data</label></div></div>
      <div class="col-12 col-md-3"><div class="form-floating"><input type="date" name="iniFiltro" value="<?= $e($filtros->inicio ?? '') ?>" class="form-control input-phsa-focus input-phsa-hover"><label>Início</label></div></div>
      <div class="col-12 col-md-3"><div class="form-floating"><input type="date" name="fimFiltro" value="<?= $e($filtros->fim ?? '') ?>" class="form-control input-phsa-focus input-phsa-hover"><label>Fim</label></div></div>
      <div id="opcoesAvancadas" class="col-12 <?= (!empty($filtros->busca) || !empty($filtros->idcentrocusto) || !empty($filtros->idplanoconta) || !empty($filtros->idcontafinanceira) || !empty($filtros->natureza) || !empty($filtros->status)) ? '' : 'd-none' ?>"><div class="row g-3"><div class="col-12"><div class="form-floating"><input name="busca" value="<?= $e($filtros->busca ?? '') ?>" class="form-control input-phsa-focus input-phsa-hover" placeholder="Descrição, observação ou título"><label>Descrição ou valor informado</label></div></div>
        <div class="col-12 col-md"><select id="idcentrocusto" name="idcentrocusto[]" class="js-relatorio-multiselect" multiple><?php foreach ($centrosCusto as $item): ?><option value="<?= (int) $item->cdc_id ?>" <?= $selecionado($item->cdc_id, $filtros->idcentrocusto ?? []) ?>><?= $e($item->cdc_nome) ?></option><?php endforeach; ?></select><div class="small text-muted">Centro de custo</div></div>
        <div class="col-12 col-md"><select id="idplanoconta" name="idplanoconta[]" class="js-relatorio-multiselect" multiple><?php foreach ($planosConta as $item): ?><option value="<?= (int) $item->pct_id ?>" <?= $selecionado($item->pct_id, $filtros->idplanoconta ?? []) ?>><?= $e($item->pct_nome) ?></option><?php endforeach; ?></select><div class="small text-muted">Plano de conta</div></div>
        <div class="col-12 col-md"><select id="idcontafinanceira" name="idcontafinanceira[]" class="js-relatorio-multiselect" multiple><?php foreach ($contasFinanceiras as $item): ?><option value="<?= (int) $item->ctf_id ?>" <?= $selecionado($item->ctf_id, $filtros->idcontafinanceira ?? []) ?>><?= $e($item->ctf_nome) ?></option><?php endforeach; ?></select><div class="small text-muted">Conta financeira</div></div>
        <div class="col-12 col-md"><select id="natureza" name="natureza[]" class="js-relatorio-multiselect" multiple><option value="Entrada" <?= $selecionado('Entrada', $filtros->natureza ?? []) ?>>Entrada</option><option value="Saida" <?= $selecionado('Saida', $filtros->natureza ?? []) ?>>Saída</option></select><div class="small text-muted">Natureza</div></div>
        <div class="col-12 col-md"><select id="status" name="status[]" class="js-relatorio-multiselect" multiple><?php foreach (['A receber', 'A pagar', 'Recebido', 'Pago', 'Estornado'] as $status): ?><option value="<?= $e($status) ?>" <?= $selecionado($status, $filtros->status ?? []) ?>><?= $e($status) ?></option><?php endforeach; ?></select><div class="small text-muted">Status do lançamento</div></div>
      </div></div>
      <div class="col-12 col-md-4"><div class="form-floating"><select name="op" id="op" class="form-select input-phsa-focus input-phsa-hover"><?php foreach (($extrato ? ['ordem_extrato' => 'Ordem do extrato'] : ['padrao' => 'Ordem padrão']) + ['numero_identificacao' => 'Número de identificação', 'mais_recentes' => 'Mais recentes primeiro', 'maior_valor' => 'Maior valor primeiro', 'menor_valor' => 'Menor valor primeiro', 'conta_financeira' => 'Conta financeira', 'plano_conta' => 'Plano de conta', 'centro_custo' => 'Centro de custo', 'natureza' => 'Natureza'] + (!$extrato ? ['status_lancamento' => 'Status do lançamento'] : []) + ['titulo_financeiro' => 'Título financeiro'] as $valor => $label): ?><option value="<?= $valor ?>" <?= ($filtros->op ?? '') === $valor ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select><label>Ordenar por</label></div></div>
      <div class="col-12 col-md-4"><div class="form-floating"><select name="df" id="df" class="form-select input-phsa-focus input-phsa-hover"><option value="asc" <?= ($filtros->df ?? '') === 'asc' ? 'selected' : '' ?>>Crescente</option><option value="desc" <?= ($filtros->df ?? '') === 'desc' ? 'selected' : '' ?>>Decrescente</option></select><label>De forma</label></div></div>
      <div class="col-12 col-md-4"><div class="form-floating"><select name="limit" id="limit" class="form-select input-phsa-focus input-phsa-hover"><?php foreach (['50' => '50 registros', '100' => '100 registros', '150' => '150 registros', '250' => '250 registros', '500' => '500 registros', '1000' => '1.000 registros', '1500' => '1.500 registros', '2000' => '2.000 registros', '2500' => '2.500 registros', '5000' => '5.000 registros', '10000' => '10.000 registros', 'sem_limite' => 'Sem limite'] as $valorLimite => $labelLimite): ?><option value="<?= $e($valorLimite) ?>" <?= (string) ($filtros->limit ?? '') === (string) $valorLimite ? 'selected' : '' ?>><?= $e($labelLimite) ?></option><?php endforeach; ?></select><label><?= $extrato ? 'Limite de exibição' : 'Limite de resultados' ?></label></div></div>
      <div id="alertOrdenacaoRelatorio" class="col-12 alert alert-info py-2 mb-0 d-none"><i class="fa-solid fa-circle-info me-1"></i><strong>Atenção sobre a ordenação do extrato.</strong> A ordenação altera a sequência cronológica; para uma leitura de saldo acumulado, utilize <strong>Ordem do extrato</strong>.</div>
      <div id="alertLimitSemLimite" class="col-12 alert alert-warning py-2 mb-0 d-none"><div class="d-flex gap-2"><i class="fa-solid fa-triangle-exclamation mt-1"></i><span class="small">A consulta sem limite pode ser lenta em períodos grandes. Use essa opção apenas quando necessário.</span></div></div>
      <div id="alertLimitDefinido" class="col-12 alert alert-info py-2 mb-0"><div class="d-flex gap-2"><i class="fa-solid fa-circle-info mt-1"></i><div><?php if ($extrato): ?><strong class="d-block">Tabela limitada</strong><span class="small">A tabela está limitada aos primeiros registros da ordenação selecionada. Os saldos e totais do relatório consideram todas as movimentações encontradas pelos filtros.</span><?php else: ?><span class="small">O limite afeta os lançamentos exibidos e o saldo acumulado da tabela. Para extrato completo, use Sem limite com filtros de período bem definidos.</span><?php endif; ?></div></div></div>
      <div class="col-12 d-flex justify-content-end align-items-end gap-2 flex-wrap"><button type="button" id="btnToggleOpcoes" class="bto bto-phsa-light"><i class="fa-solid fa-sliders"></i>&nbsp;<span>Exibir opções avançadas</span></button><button onclick="fgbDatatable('tableRelatorio')" class="bto bto-phsa-light" type="button"><i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar tabela</button><button onclick="fgbConverterTabelaEmXlsx('tableRelatorio', '<?= $e($tipoRelatorio ?? 'relatorio') ?>')" class="bto bto-phsa-light" type="button"><i class="fas fa-file-excel"></i>&nbsp;Exportar para Excel</button><button onclick="fgbGerarImpressao('resultadoRelatorio')" class="bto bto-phsa-light" type="button"><i class="fas fa-print"></i>&nbsp;Imprimir / PDF</button><button type="submit" class="bto bto-phsa"><i class="fa-solid fa-magnifying-glass"></i>&nbsp;Gerar relatório</button></div>
    </div>
  </form>
  <?php if (!$extrato): ?>
    <section class="relatorioAnaliticoResumo mt-4">
      <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-lg-3"><div class="relatorioResumoCard"><div class="relatorioResumoLabel">Lançamentos</div><div class="relatorioResumoValor text-dark"><?= number_format((int) ($resumoAnalitico->quantidade ?? 0), 0, ',', '.') ?></div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="relatorioResumoCard"><div class="relatorioResumoLabel">Total de entradas</div><div class="relatorioResumoValor text-success"><?= $moeda($resumoAnalitico->entradas ?? 0) ?></div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="relatorioResumoCard"><div class="relatorioResumoLabel">Total de saídas</div><div class="relatorioResumoValor text-danger"><?= $moeda($resumoAnalitico->saidas ?? 0) ?></div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="relatorioResumoCard"><div class="relatorioResumoLabel">Saldo do período</div><div class="relatorioResumoValor text-<?= ($resumoAnalitico->saldo ?? 0) < 0 ? 'danger' : 'success' ?>"><?= $moeda($resumoAnalitico->saldo ?? 0) ?></div></div></div>
      </div>
      <?php if (!empty($resumoPorConta)): ?>
        <div class="relatorioResumoPainel bg-body h-auto">
          <div class="relatorioResumoLabel">Resumo por conta</div>
          <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
              <thead><tr><th>Conta financeira</th><th class="text-center">Lançamentos</th><th class="text-end text-success">Entradas</th><th class="text-end text-danger">Saídas</th><th class="text-end">Saldo</th></tr></thead>
              <tbody><?php foreach ($resumoPorConta as $contaResumo): ?><tr><td class="fw-semibold"><?= $e($contaResumo->lan_ctf_nome ?? 'Sem conta') ?></td><td class="text-center"><?= number_format((int) ($contaResumo->qtd_lancamento ?? 0), 0, ',', '.') ?></td><td class="text-end text-success"><?= $moeda($contaResumo->entradas ?? 0) ?></td><td class="text-end text-danger"><?= $moeda($contaResumo->saidas ?? 0) ?></td><td class="text-end fw-bold text-<?= ($contaResumo->saldo ?? 0) < 0 ? 'danger' : 'success' ?>"><?= $moeda($contaResumo->saldo ?? 0) ?></td></tr><?php endforeach; ?></tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>
  <?php $this->insert("sistema/financeiro/relatorio/arquivos/{$tipoRelatorio}", $this->data + compact('e', 'moeda', 'data', 'campoData', 'ordenacaoAplicada')); ?>
</div></div>

<?php $this->push('style'); ?><style>.phsaFakeInput{min-height:58px;border:1px solid #dee2e6;border-radius:.85rem;background:var(--bs-body-bg);padding:.55rem .75rem .45rem}.phsaFakeInputLabel,.relatorioResumoLabel{font-size:.72rem;line-height:1.2;text-transform:uppercase;color:#59697d;margin-bottom:.45rem}.phsaFakeInputValue{font-size:.875rem;line-height:1.35;min-height:1.2rem}.relatorioResumoCard{height:100%;min-height:120px;background:var(--bs-body-bg);border:1px solid #cddbf1;border-radius:.45rem;box-shadow:0 .25rem .5rem rgba(26,63,120,.13);padding:1.25rem}.relatorioResumoValor{font-size:1.45rem;font-weight:700;line-height:1.1}.relatorioResumoPainel{background:#dce7fa;border:1px solid #aec5e8;border-radius:.35rem;padding:1.25rem;color:#163d78}@media print{.simpleNavbar,.simpleSidebar,.simpleFooter,.breadcrumb,.bto,form{display:none!important}.card{border:0!important;box-shadow:none!important}}</style><?php $this->end(); ?>

<?php $this->push('script'); ?><script>
  $(function () {
    const avancadas = $('#opcoesAvancadas');
    const botaoAvancadas = $('#btnToggleOpcoes');
    const atualizarBotaoAvancadas = () => {
      const exibindo = !avancadas.hasClass('d-none');
      botaoAvancadas.find('span').text(exibindo ? 'Ocultar opções avançadas' : 'Exibir opções avançadas');
      botaoAvancadas.find('i').attr('class', exibindo ? 'fa-solid fa-sliders' : 'fa-solid fa-sliders');
    };
    botaoAvancadas.on('click', () => { avancadas.toggleClass('d-none'); atualizarBotaoAvancadas(); });
    atualizarBotaoAvancadas();

    if (typeof $.fn.multiselect === 'function') {
      $('.js-relatorio-multiselect').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        nonSelectedText: 'None selected',
        nSelectedText: 'selecionados',
        allSelectedText: 'Todos selecionados',
        selectAllText: 'Selecionar todos',
        filterPlaceholder: 'Pesquisar',
        buttonWidth: '100%',
        maxHeight: 250,
        templates: { button: '<button type="button" class="multiselect dropdown-toggle bto bto-phsa-light w-100 text-start" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span></button>' }
      });
    }

    const extratoMovimentacao = <?= $extrato ? 'true' : 'false' ?>;
    const atualizarAlertas = () => {
      $('#alertOrdenacaoRelatorio').toggleClass('d-none', !extratoMovimentacao || $('#op').val() === 'ordem_extrato');
      const semLimite = $('#limit').val() === 'sem_limite';
      $('#alertLimitSemLimite').toggleClass('d-none', !semLimite);
      $('#alertLimitDefinido').toggleClass('d-none', semLimite || Number($('#limit').val() || 0) >= 10000);
    };
    $('#op, #limit').on('change', atualizarAlertas);
    atualizarAlertas();
  });
</script><?php $this->end(); ?>
