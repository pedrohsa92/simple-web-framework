<?php
  $isUpdate = (int) ($id ?? 0) > 0;
  $esc = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $valor = static fn($numero): string => Helpers::formataValor((float) ($numero ?? 0));
  $data = static fn($valor): string => !empty($valor) ? date('d/m/Y', strtotime((string) $valor)) : '—';
  $urlList = Helpers::url('financeiro/transferencia/list');
  $urlSalvar = Helpers::url('financeiro/salvarTransferencia');
  $urlSaldoDisponivel = Helpers::url('financeiro/saldoDisponivelTransferencia');
  $urlEstornar = Helpers::url('financeiro/estornarTransferencia');
  $situacaoEstornada = ($transferencia->lan_status ?? '') === 'Estornado';
  $logs = json_decode((string) ($transferencia->lan_log ?? '[]'), true);
  $logs = is_array($logs) ? $logs : [];
  $dataHora = static function ($valor): string {
    $valor = trim((string) ($valor ?? ''));
    $timestamp = $valor !== '' ? strtotime(str_replace('/', '-', $valor)) : false;
    return $timestamp ? date('d/m/Y H:i', $timestamp) : '—';
  };
  $anexoTransferencia = $anexoTransferencia ?? null;
  $temAnexoTransferencia = $isUpdate && $anexoTransferencia && !empty($anexoTransferencia->arq_caminho);
  $nomeAnexoTransferencia = (string) ($anexoTransferencia->arq_nomeoriginal ?? $anexoTransferencia->arq_nome ?? 'Documento da transferência');
  $urlAnexoTransferencia = $temAnexoTransferencia ? Helpers::url((string) $anexoTransferencia->arq_caminho) : '';
  $dimensaoAnexoTransferencia = ((int) ($anexoTransferencia->arq_largura ?? 0) > 0 && (int) ($anexoTransferencia->arq_altura ?? 0) > 0)
    ? ((int) $anexoTransferencia->arq_largura . 'x' . (int) $anexoTransferencia->arq_altura)
    : '—';
?>
<?php $this->layout('sistema::sistema'); ?>

<div class="card shadow-sm">
  <div class="card-body p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <h4 class="m-0 text-uppercase"><?= $isUpdate ? 'Transferência #' . (int) $id : 'Nova transferência' ?></h4>
        <div class="text-muted small"><?= $isUpdate ? 'MOVIMENTAÇÃO INTERNA • TRANSFERÊNCIA ENTRE CONTAS' : 'Movimente saldo entre contas financeiras sem gerar receita, despesa ou título.' ?></div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if ($isUpdate): ?>
          <span class="badge bg-<?= $situacaoEstornada ? 'danger' : 'success' ?>"><i class="fa-solid <?= $situacaoEstornada ? 'fa-arrow-rotate-left' : 'fa-check' ?> me-1"></i><?= $situacaoEstornada ? 'Estornada' : 'Concluída' ?></span>
        <?php endif; ?>
        <a href="<?= $urlList ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar para transferências</a>
      </div>
    </div>

    <?php if ($isUpdate): ?>
      <header class="phsaScrollHeader"><nav class="phsaScrollNav" data-menu="tabTransferencia"><ul class="phsaScrollList"><li class="phsaScrollItem active" data-target="cadastroTransferencia"><i class="fa-solid fa-file-lines"></i>&nbsp;Cadastro</li><li class="phsaScrollItem" data-target="historicoTransferencia"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li></ul></nav></header>
      <div class="phsaTabContent phsaTabSubContent">
        <div class="phsaTabItem active p-3" id="cadastroTransferencia">
          <div class="alert alert-secondary d-flex align-items-center gap-2"><i class="fa-solid fa-lock"></i><div>Transferências são concluídas imediatamente e não podem ser alteradas. Caso necessário, realize o <strong>estorno</strong>.</div></div>
          <div class="row g-3">
            <div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Conta de origem</div><div class="phsaFakeInputValue fw-semibold text-danger"><i class="fa-solid fa-arrow-trend-down me-1"></i><?= $esc($transferencia->trf_conta_origem ?? '—') ?></div></div></div>
            <div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Conta de destino</div><div class="phsaFakeInputValue fw-semibold text-success"><i class="fa-solid fa-arrow-trend-up me-1"></i><?= $esc($transferencia->trf_conta_destino ?? '—') ?></div></div></div>
            <div class="col-6 col-md-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Data da transferência</div><div class="phsaFakeInputValue text-center"><?= $data($transferencia->lan_dtbaixa ?? null) ?></div></div></div>
            <div class="col-6 col-md-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Valor</div><div class="phsaFakeInputValue text-end fw-bold text-phsa">R$ <?= $valor($transferencia->lan_valor ?? 0) ?></div></div></div>
            <div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Situação</div><div class="phsaFakeInputValue"><span class="badge bg-<?= $situacaoEstornada ? 'danger' : 'success' ?>"><i class="fa-solid <?= $situacaoEstornada ? 'fa-arrow-rotate-left' : 'fa-check' ?> me-1"></i><?= $situacaoEstornada ? 'Estornada' : 'Concluída' ?></span></div></div></div>
            <div class="col-12"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Observação</div><div class="phsaFakeInputValue text-break"><?= $esc($transferencia->lan_observacao ?? 'Sem observação') ?></div></div></div>
            <div class="col-12"><div class="transferenciaAnexoBox border-phsa-light rounded-3 p-3 bg-phsa-hover-light"><div class="d-flex flex-wrap align-items-start justify-content-between gap-3"><div><div class="fw-semibold text-phsa"><i class="fa-solid fa-paperclip me-1"></i>Documento da transferência</div><div class="small text-muted">Comprovante opcional vinculado à movimentação.</div></div><?php if ($temAnexoTransferencia): ?><div class="d-flex flex-wrap justify-content-end gap-2"><a href="<?= $esc($urlAnexoTransferencia) ?>" target="_blank" rel="noopener" class="bto bto-phsa-light btn-sm" title="Visualizar documento"><i class="fa-solid fa-eye"></i>&nbsp;Visualizar</a><a href="<?= $esc($urlAnexoTransferencia) ?>" download="<?= $esc($nomeAnexoTransferencia) ?>" class="bto bto-phsa btn-sm" title="Baixar documento"><i class="fa-solid fa-download"></i>&nbsp;Baixar</a><?php if (($podeGerenciarAnexoTransferencia ?? false) && !$situacaoEstornada): ?><button type="button" class="bto bto-phsa-light btn-sm js-substituir-anexo-transferencia" data-arquivo-id="<?= (int) $anexoTransferencia->arq_id ?>" title="Trocar documento"><i class="fa-solid fa-rotate"></i>&nbsp;Trocar</button><?php endif; ?></div><?php elseif (($podeGerenciarAnexoTransferencia ?? false) && !$situacaoEstornada): ?><button type="button" class="bto bto-phsa-light btn-sm js-anexar-anexo-transferencia"><i class="fa-solid fa-paperclip"></i>&nbsp;Anexar documento</button><?php endif; ?></div><?php if ($temAnexoTransferencia): ?><div class="small text-muted mt-3"><i class="fa-solid fa-file me-1"></i><?= $esc($nomeAnexoTransferencia) ?> <span class="ms-2"><?= $esc(strtoupper((string) ($anexoTransferencia->arq_extensao ?? '—'))) ?> | <?= $esc(trim(($anexoTransferencia->arq_tamanho ?? '') . ' ' . ($anexoTransferencia->arq_medida ?? '')) ?: '—') ?><?php if ($dimensaoAnexoTransferencia !== '—'): ?> | <?= $esc($dimensaoAnexoTransferencia) ?><?php endif; ?> | <?= $dataHora($anexoTransferencia->arq_dtcad ?? null) ?></span></div><?php elseif ($situacaoEstornada): ?><div class="small text-muted mt-3"><i class="fa-solid fa-lock me-1"></i>Transferência estornada: novos documentos ou trocas não são permitidos.</div><?php endif; ?></div></div>
            <?php if ($situacaoEstornada): ?><div class="col-12"><div class="alert alert-danger mb-0"><div class="fw-semibold"><i class="fa-solid fa-arrow-rotate-left me-1"></i>Transferência estornada</div><div class="small mt-1">Motivo: <?= $esc($transferencia->lan_motivoacao ?? 'Não informado') ?></div></div></div><?php endif; ?>
          </div>
          <?php if ($podeEstornar): ?><div class="d-flex justify-content-end mt-4"><button type="button" id="btnEstornarTransferencia" class="bto text-danger"><i class="fa-solid fa-arrow-rotate-left"></i>&nbsp;Estornar transferência</button></div><?php endif; ?>
        </div>
        <div class="phsaTabItem" id="historicoTransferencia">
          <?php
            $historicoConfig = [
              'id' => $transferencia->trf_id ?? $transferencia->lan_id ?? $id,
              'status' => $situacaoEstornada ? 'Estornada' : 'Concluída',
              'statusClass' => $situacaoEstornada ? 'danger' : 'success',
              'createdAt' => $transferencia->lan_dtcad ?? null,
              'updatedAt' => $transferencia->lan_dtalter ?? null,
              'logs' => $logs,
              'emptyMessage' => 'Nenhum histórico registrado para esta transferência.'
            ];
            include __DIR__ . '/../../../template/sistema/historicoPadrao.php';
          ?>
        </div>
      </div>
    <?php elseif (count($contasAtivas) < 2): ?>
      <div class="alert alert-warning border mb-0"><div class="fw-semibold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>São necessárias ao menos duas contas ativas</div><div class="small">Cadastre ou ative outra conta financeira antes de registrar uma transferência.</div><a class="btn btn-sm bto bto-phsa-light mt-3" href="<?= Helpers::url('financeiro/basico/conta_financeira') ?>"><i class="fa-solid fa-building-columns"></i>&nbsp;Gerenciar contas financeiras</a></div>
    <?php else: ?>
      <form id="formTransferencia" class="needs-validation" novalidate>
        <section class="border-phsa-light rounded-3 p-3 p-md-4 mb-4 bg-phsa-hover-light">
          <div class="d-flex align-items-center gap-2 mb-3"><span class="bg-phsa-light text-phsa rounded-3 d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px"><i class="fa-solid fa-shield-halved"></i></span><div><div class="fw-semibold">Transferência com saldo protegido</div><div class="small text-muted">Entenda como a movimentação será registrada.</div></div></div>
          <div class="row g-3 small">
            <div class="col-12 col-md-4"><div class="h-100 p-3 bg-white rounded-3 border"><div class="text-success fw-semibold mb-1"><i class="fa-solid fa-wallet me-1"></i>Saldo suficiente</div><div class="text-muted">A origem precisa ter saldo disponível na data escolhida. Saldo zero ou negativo não permite transferência.</div></div></div>
            <div class="col-12 col-md-4"><div class="h-100 p-3 bg-white rounded-3 border"><div class="text-danger fw-semibold mb-1"><i class="fa-solid fa-credit-card me-1"></i>Cartão apenas no destino</div><div class="text-muted">Cartão representa limite ou dívida. Pagar a fatura é transferir de uma conta disponível para o cartão.</div></div></div>
            <div class="col-12 col-md-4"><div class="h-100 p-3 bg-white rounded-3 border"><div class="text-phsa fw-semibold mb-1"><i class="fa-solid fa-scale-balanced me-1"></i>Sem receita ou despesa</div><div class="text-muted">O sistema cria uma saída paga e uma entrada recebida vinculadas. O saldo geral não muda.</div></div></div>
          </div>
        </section>
        <div class="row g-3">
          <div class="col-12 col-md-6"><div class="form-floating"><input type="date" class="form-control input-phsa-focus input-phsa-hover text-center" id="trf_data" name="trf_data" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required placeholder="Data"><label for="trf_data">Data da transferência</label><div class="invalid-feedback">Informe uma data válida.</div></div></div>
          <div class="col-12 col-md-6"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover dinheiro text-end" id="trf_valor" name="trf_valor" inputmode="decimal" required placeholder="Valor"><label for="trf_valor">Valor</label><div class="invalid-feedback">Informe um valor maior que zero.</div></div></div>
          <div class="col-12 col-md-6"><div class="form-floating"><select class="form-select input-phsa-focus input-phsa-hover" id="trf_idcontaorigem" name="trf_idcontaorigem" required><option value="">Selecione</option><?php foreach ($contasAtivas as $conta): ?><?php $cartao = ($conta->ctf_tipo ?? '') === 'Cartao'; ?><option value="<?= (int) $conta->ctf_id ?>" <?= $cartao ? 'disabled' : '' ?>><?= $esc($conta->ctf_nome) ?><?= $cartao ? ' — cartão (apenas destino)' : '' ?></option><?php endforeach; ?></select><label for="trf_idcontaorigem">Conta de origem</label><div class="invalid-feedback">Selecione uma conta de origem disponível.</div></div><div id="trfSaldoOrigem" class="alert alert-light border mt-2 mb-0 small d-flex align-items-center gap-2"><i class="fa-solid fa-circle-info text-phsa"></i><span>Selecione a conta de origem e a data.</span></div></div>
          <div class="col-12 col-md-6"><div class="form-floating"><select class="form-select input-phsa-focus input-phsa-hover" id="trf_idcontadestino" name="trf_idcontadestino" required><option value="">Selecione</option><?php foreach ($contasAtivas as $conta): ?><option value="<?= (int) $conta->ctf_id ?>"><?= $esc($conta->ctf_nome) ?></option><?php endforeach; ?></select><label for="trf_idcontadestino">Conta de destino</label><div class="invalid-feedback">Selecione uma conta de destino diferente da origem.</div></div><div id="trfSaldoDestino" class="alert alert-light border mt-2 mb-0 small d-flex align-items-center gap-2"><i class="fa-solid fa-circle-info text-phsa"></i><span>Selecione a conta de destino e a data.</span></div></div>
          <div class="col-12"><div class="transferenciaAnexoBox border-phsa-light rounded-3 p-3 bg-phsa-hover-light"><div class="d-flex flex-wrap align-items-start justify-content-between gap-3"><div><div class="fw-semibold text-phsa"><i class="fa-solid fa-paperclip me-1"></i>Documento da transferência</div><div class="small text-muted">Opcional. Selecione um comprovante para vinculá-lo após o registro.</div></div><button type="button" id="btnAnexarTransferenciaCadastro" class="bto bto-phsa-light btn-sm"><i class="fa-solid fa-paperclip"></i>&nbsp;Anexar documento</button></div><div id="transferenciaAnexoPendente" class="d-none mt-3"></div></div></div>
          <div id="trfCampoObservacao" class="col-12 d-none"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover" id="trf_observacao" name="trf_observacao" maxlength="255" placeholder="Observação"><label for="trf_observacao">Observação <span class="text-muted">(opcional)</span></label></div></div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4"><button type="button" id="btnAdicionarObservacaoTransferencia" class="bto bto-phsa-light"><i class="fa-solid fa-align-left"></i>&nbsp;Adicionar observação</button><button type="submit" id="btnRegistrarTransferencia" class="bto bto-phsa"><i class="fa-solid fa-money-bill-transfer"></i>&nbsp;Registrar transferência</button></div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php $this->push('style'); ?>
  <style>
    .transferenciaAnexoBox {
      background-color: rgba(13, 110, 253, 0.035);
    }

    .transferenciaAnexoPendente {
      border: 1px dashed var(--bs-border-color);
      border-radius: 0.65rem;
      padding: 0.65rem 0.75rem;
      background: var(--bs-body-bg);
    }
  </style>
<?php $this->end(); ?>

<?php $this->push('script'); ?>
  <script>
    const transferenciaSalvarUrl = '<?= $urlSalvar ?>';
    const transferenciaSaldoDisponivelUrl = '<?= $urlSaldoDisponivel ?>';
    const transferenciaEstornarUrl = '<?= $urlEstornar ?>';
    const transferenciaAnexoSalvarUrl = '<?= Helpers::url('arquivo/salvarArquivo') ?>';
    const transferenciaAnexoSubstituirUrl = '<?= Helpers::url('arquivo/substituirArquivo') ?>';
    const transferenciaId = <?= (int) $id ?>;
    const transferenciaPodeGerenciarAnexo = <?= ($podeGerenciarAnexoTransferencia ?? false) ? 'true' : 'false' ?>;
    const transferenciaAceitos = '.pdf,.txt,.doc,.docx,.xls,.xlsx,.json,.xml,.jpg,.jpeg,.png,.webp';
    let transferenciaAnexoPendente = null;
    let saldoOrigemAtual = null;
    let saldoDestinoAtual = null;
    let contaOrigemAtual = '';
    let contaDestinoAtual = '';

    function transferenciaEscape(valor) {
      return $('<div>').text(valor == null ? '' : valor).html();
    }

    function transferenciaValorNumerico(valor) {
      return Number(String(valor || '').replace(/\./g, '').replace(',', '.')) || 0;
    }

    function transferenciaDinheiro(valor) {
      return Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function transferenciaRenderAnexoPendente(arquivo) {
      const area = $('#transferenciaAnexoPendente');
      if (!arquivo) {
        area.addClass('d-none').empty();
        return;
      }
      area.removeClass('d-none').html(`<div class="transferenciaAnexoPendente d-flex flex-wrap align-items-center justify-content-between gap-2"><div class="small"><i class="fa-solid fa-file me-1 text-phsa"></i><strong>${transferenciaEscape(arquivo.name || 'Documento selecionado')}</strong><div class="text-muted">O documento será vinculado após registrar a transferência.</div></div><button type="button" class="bto bto-phsa-light btn-sm" id="btnRemoverAnexoTransferencia"><i class="fa-solid fa-xmark"></i>&nbsp;Remover</button></div>`);
    }

    function transferenciaEnviarAnexo(arquivo, idOrigem, idArquivo = 0) {
      if (!arquivo || !arquivo.status || !idOrigem) {
        return $.Deferred().reject().promise();
      }
      const dados = {
        arq_id: 0,
        arq_origem: 'transferencia',
        arq_idorigem: Number(idOrigem),
        arq_nome: arquivo.name,
        arq_nomeoriginal: arquivo.name,
        arq_tipo: arquivo.isImage ? 'imagem' : 'arquivo',
        arq_base64: arquivo.data
      };
      const url = idArquivo ? `${transferenciaAnexoSubstituirUrl}/${Number(idArquivo)}` : transferenciaAnexoSalvarUrl;
      return $.ajax({ url, method: 'POST', dataType: 'json', data: dados });
    }

    function transferenciaSelecionarAnexo(idArquivo = 0) {
      if (!transferenciaPodeGerenciarAnexo) return;
      fgbSelecionarArquivo({
        customAccept: transferenciaAceitos,
        replaceDefault: true,
        onFileSelect: function (arquivo) {
          if (!arquivo || !arquivo.status) {
            swError((arquivo && arquivo.text) || 'Arquivo inválido.');
            return;
          }
          if (!transferenciaId) {
            transferenciaAnexoPendente = arquivo;
            transferenciaRenderAnexoPendente(arquivo);
            return;
          }
          const botao = idArquivo
            ? $(`.js-substituir-anexo-transferencia[data-arquivo-id="${Number(idArquivo)}"]`)
            : $('.js-anexar-anexo-transferencia');
          botao.prop('disabled', true);
          transferenciaEnviarAnexo(arquivo, transferenciaId, idArquivo)
            .done(function (response) {
              swResponse(response);
              if (response && response.status) setTimeout(() => window.location.reload(), 700);
              else botao.prop('disabled', false);
            })
            .fail(function () {
              botao.prop('disabled', false);
              swError('Não foi possível salvar o documento da transferência.');
            });
        }
      });
    }

    $('#btnAnexarTransferenciaCadastro').on('click', () => transferenciaSelecionarAnexo());
    $(document).on('click', '#btnRemoverAnexoTransferencia', function () {
      transferenciaAnexoPendente = null;
      transferenciaRenderAnexoPendente(null);
    });
    $(document).on('click', '.js-anexar-anexo-transferencia', () => transferenciaSelecionarAnexo());
    $(document).on('click', '.js-substituir-anexo-transferencia', function () {
      transferenciaSelecionarAnexo($(this).data('arquivo-id'));
    });

    function atualizarOpcoesContaDestino() {
      const origem = String($('#trf_idcontaorigem').val() || '');
      const destino = $('#trf_idcontadestino');
      destino.find('option').prop('disabled', false);
      if (origem) destino.find(`option[value="${origem}"]`).prop('disabled', true);
      if (String(destino.val() || '') === origem) destino.val('');
    }

    function atualizarResumoSaldo(tipo) {
      const origem = tipo === 'origem';
      const saldoAntes = origem ? saldoOrigemAtual : saldoDestinoAtual;
      const conta = origem ? contaOrigemAtual : contaDestinoAtual;
      const alerta = origem ? $('#trfSaldoOrigem') : $('#trfSaldoDestino');
      const valorTransferencia = transferenciaValorNumerico($('#trf_valor').val());
      if (saldoAntes === null || !conta) return;

      const saldoDepois = origem ? saldoAntes - valorTransferencia : saldoAntes + valorTransferencia;
      const saldoInsuficiente = origem && valorTransferencia > saldoAntes;
      const classe = saldoInsuficiente ? 'warning' : (origem ? 'success' : 'primary');
      const icone = saldoInsuficiente ? 'fa-triangle-exclamation' : (origem ? 'fa-arrow-trend-down' : 'fa-arrow-trend-up');
      const movimento = origem ? 'Saída' : 'Entrada';
      alerta.attr('class', `alert alert-${classe} border mt-2 mb-0 small`).html(`<div class="d-flex align-items-start gap-2"><i class="fa-solid ${icone} mt-1"></i><div><div>Conta <strong>${transferenciaEscape(conta)}</strong> tinha <strong>R$ ${transferenciaDinheiro(saldoAntes)}</strong>.</div><div>${movimento}: <strong>R$ ${transferenciaDinheiro(valorTransferencia)}</strong>. Ficará com <strong>R$ ${transferenciaDinheiro(saldoDepois)}</strong>.</div>${saldoInsuficiente ? '<div class="mt-1 fw-semibold">O valor ultrapassa o saldo disponível.</div>' : ''}</div></div>`);
    }

    function consultarSaldoDisponivel(tipo) {
      const origem = tipo === 'origem';
      const conta = String($(`#trf_idconta${origem ? 'origem' : 'destino'}`).val() || '');
      const data = String($('#trf_data').val() || '');
      const saldo = origem ? $('#trfSaldoOrigem') : $('#trfSaldoDestino');
      if (!conta || !data) {
        if (origem) { saldoOrigemAtual = null; contaOrigemAtual = ''; } else { saldoDestinoAtual = null; contaDestinoAtual = ''; }
        saldo.attr('class', 'alert alert-light border mt-2 mb-0 small d-flex align-items-center gap-2').html(`<i class="fa-solid fa-circle-info text-phsa"></i><span>Selecione a conta de ${origem ? 'origem' : 'destino'} e a data.</span>`);
        return;
      }
      saldo.attr('class', 'alert alert-light border mt-2 mb-0 small d-flex align-items-center gap-2').html('<i class="fa-solid fa-spinner fa-spin text-phsa"></i><span>Consultando saldo...</span>');
      $.ajax({ url: transferenciaSaldoDisponivelUrl, method: 'POST', dataType: 'json', data: { trf_idconta: conta, trf_data: data, trf_tipoConta: tipo } })
        .done((response) => {
          if (!response || !response.status) {
            if (origem) { saldoOrigemAtual = null; contaOrigemAtual = ''; } else { saldoDestinoAtual = null; contaDestinoAtual = ''; }
            saldo.attr('class', 'alert alert-warning border mt-2 mb-0 small d-flex align-items-center gap-2').html(`<i class="fa-solid fa-triangle-exclamation"></i><span>${transferenciaEscape(response && response.text || 'Não foi possível consultar o saldo.')}</span>`);
            return;
          }
          const valorSaldo = Number(response.data && response.data.saldo || 0);
          if (origem) { saldoOrigemAtual = valorSaldo; contaOrigemAtual = String(response.data && response.data.conta || 'conta selecionada'); } else { saldoDestinoAtual = valorSaldo; contaDestinoAtual = String(response.data && response.data.conta || 'conta selecionada'); }
          atualizarResumoSaldo(tipo);
        })
        .fail(() => { if (origem) saldoOrigemAtual = null; else saldoDestinoAtual = null; saldo.attr('class', 'alert alert-warning border mt-2 mb-0 small d-flex align-items-center gap-2').html('<i class="fa-solid fa-triangle-exclamation"></i><span>Não foi possível consultar o saldo.</span>'); });
    }

    $('#trf_idcontaorigem').on('change', function () { atualizarOpcoesContaDestino(); consultarSaldoDisponivel('origem'); consultarSaldoDisponivel('destino'); });
    $('#trf_idcontadestino').on('change', () => consultarSaldoDisponivel('destino'));
    $('#trf_data').on('change', () => { consultarSaldoDisponivel('origem'); consultarSaldoDisponivel('destino'); });
    $('#trf_valor').on('input', function () {
      const valor = transferenciaValorNumerico(this.value);
      this.setCustomValidity(saldoOrigemAtual !== null && valor > saldoOrigemAtual ? 'O valor informado ultrapassa o saldo disponível.' : '');
      atualizarResumoSaldo('origem');
      atualizarResumoSaldo('destino');
    });

    $('#btnAdicionarObservacaoTransferencia').on('click', function () {
      $(this).addClass('d-none');
      $('#trfCampoObservacao').removeClass('d-none');
      $('#trf_observacao').trigger('focus');
    });

    $('#formTransferencia').on('submit', function (event) {
      event.preventDefault();
      const formulario = this;
      const origem = String($('#trf_idcontaorigem').val() || '');
      const destino = String($('#trf_idcontadestino').val() || '');
      const campoDestino = $('#trf_idcontadestino').get(0);
      const campoValor = $('#trf_valor').get(0);
      campoDestino.setCustomValidity(origem && destino && origem === destino ? 'A conta de destino deve ser diferente da origem.' : '');
      const valorTransferencia = transferenciaValorNumerico(campoValor.value);
      campoValor.setCustomValidity(valorTransferencia <= 0
        ? 'Informe um valor maior que zero.'
        : (saldoOrigemAtual !== null && valorTransferencia > saldoOrigemAtual ? 'O valor informado ultrapassa o saldo disponível.' : ''));
      if (!formulario.checkValidity()) { $(formulario).addClass('was-validated'); return; }

      Swal.fire({
        icon: 'question',
        title: 'Registrar transferência?',
        text: 'A saída e a entrada vinculadas serão registradas imediatamente.',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: '<i class="fa-solid fa-check"></i>&nbsp;Registrar',
        customClass: { cancelButton: 'bto bto-phsa-light', confirmButton: 'bto bto-phsa' }
      }).then((resultado) => {
        if (!resultado.isConfirmed) return;
        const botao = $('#btnRegistrarTransferencia').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Registrando');
        $.ajax({ url: transferenciaSalvarUrl, method: 'POST', dataType: 'json', data: $(formulario).serialize() })
          .done((response) => {
            swResponse(response);
            if (response && response.status && response.location) {
              const location = response.location;
              if (transferenciaAnexoPendente && response.transferencia_id) {
                botao.html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Vinculando documento');
                transferenciaEnviarAnexo(transferenciaAnexoPendente, response.transferencia_id)
                  .done((anexoResponse) => { swResponse(anexoResponse); setTimeout(() => window.location.href = location, 700); })
                  .fail(() => { swError('A transferência foi registrada, mas não foi possível vincular o documento.'); setTimeout(() => window.location.href = location, 1200); });
              } else {
                setTimeout(() => window.location.href = location, 700);
              }
            } else {
              botao.prop('disabled', false).html('<i class="fa-solid fa-money-bill-transfer"></i>&nbsp;Registrar transferência');
            }
          })
          .fail(() => { botao.prop('disabled', false).html('<i class="fa-solid fa-money-bill-transfer"></i>&nbsp;Registrar transferência'); swError('Não foi possível registrar a transferência.'); });
      });
    });

    $('#btnEstornarTransferencia').on('click', function () {
      Swal.fire({
        icon: 'warning',
        title: 'Estornar transferência?',
        text: 'Os dois lançamentos vinculados serão estornados e os saldos voltarão ao estado anterior.',
        input: 'text',
        inputLabel: 'Motivo do estorno',
        inputPlaceholder: 'Descreva o motivo',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: '<i class="fa-solid fa-arrow-rotate-left"></i>&nbsp;Estornar',
        customClass: { cancelButton: 'bto bto-phsa-light', confirmButton: 'bto bto-danger' },
        inputValidator: (valor) => !String(valor || '').trim() ? 'Informe o motivo do estorno.' : undefined
      }).then((resultado) => {
        if (!resultado.isConfirmed) return;
        const botao = $('#btnEstornarTransferencia').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Estornando');
        $.ajax({ url: transferenciaEstornarUrl, method: 'POST', dataType: 'json', data: { trf_id: transferenciaId, trf_motivo: resultado.value } })
          .done((response) => { swResponse(response); if (response && response.status) setTimeout(() => window.location.reload(), 700); else botao.prop('disabled', false).html('<i class="fa-solid fa-arrow-rotate-left"></i>&nbsp;Estornar transferência'); })
          .fail(() => { botao.prop('disabled', false).html('<i class="fa-solid fa-arrow-rotate-left"></i>&nbsp;Estornar transferência'); swError('Não foi possível estornar a transferência.'); });
      });
    });
  </script>
<?php $this->end(); ?>
