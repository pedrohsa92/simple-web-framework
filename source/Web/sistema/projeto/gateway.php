<?php
  $gatewayConfig = json_decode($projeto->prj_gateway_configuracao ?? '{}', true);
  $gatewayConfig = is_array($gatewayConfig) ? $gatewayConfig : [];
  $gatewaySelecionado = $projeto->prj_gateway ?? 'Pagar.me';
  $pagarmeSelecionado = $gatewaySelecionado === 'Pagar.me';
  $podeAtualizarGateway = Helpers::pode('projeto.atualizar');
  $gatewayConfigJson = json_encode(
    $gatewayConfig,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
  );
?>

<div class="p-3">
  <form id="formGatewayProjeto" class="needs-validation" novalidate autocomplete="off">
    <input type="hidden" name="prj_id" value="<?= (int) ($projeto->prj_id ?? 1) ?>">

    <div class="row mb-4">
      <div class="col-12 mb-2">
        <h5 class="text-phsa mb-1">Configurações de pagamento (Gateway)</h5>
        <div class="small text-muted">Configure o gateway que receberá as contratações do projeto.</div>
      </div>

      <div class="col-12 col-lg-4 mb-3">
        <div class="form-floating">
          <select id="prj_gateway" name="prj_gateway" class="form-select input-phsa-focus input-phsa-hover" required <?= $podeAtualizarGateway ? '' : 'disabled' ?>>
            <option value="Pagar.me" <?= ($projeto->prj_gateway ?? 'Pagar.me') === 'Pagar.me' ? 'selected' : '' ?>>Pagar.me</option>
            <option value="MercadoPago" <?= ($projeto->prj_gateway ?? '') === 'MercadoPago' ? 'selected' : '' ?>>Mercado Pago</option>
            <option value="InfinitePay" <?= ($projeto->prj_gateway ?? '') === 'InfinitePay' ? 'selected' : '' ?>>Infinite Pay</option>
          </select>
          <label for="prj_gateway">Gateway</label>
        </div>
      </div>

      <div class="col-12 col-lg-4 mb-3 gatewayCampoPagarmeProjeto">
        <div class="form-floating">
          <input id="prj_gateway_pk" name="prj_gateway_pk" type="text" class="form-control input-phsa-focus input-phsa-hover" value="<?= $pagarmeSelecionado ? htmlspecialchars($projeto->prj_gateway_pk ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" <?= $pagarmeSelecionado ? 'required' : '' ?> <?= $podeAtualizarGateway && $pagarmeSelecionado ? '' : 'readonly' ?>>
          <label for="prj_gateway_pk">Public Key</label>
          <div class="invalid-feedback">Informe a Public Key.</div>
        </div>
      </div>

      <div class="col-12 col-lg-4 mb-3 gatewayCampoPagarmeProjeto">
        <div class="form-floating">
          <input id="prj_gateway_sk" name="prj_gateway_sk" type="text" class="form-control input-phsa-focus input-phsa-hover" value="<?= $pagarmeSelecionado ? htmlspecialchars($projeto->prj_gateway_sk ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" <?= $pagarmeSelecionado ? 'required' : '' ?> <?= $podeAtualizarGateway && $pagarmeSelecionado ? '' : 'readonly' ?>>
          <label for="prj_gateway_sk">Secret Key</label>
          <div class="invalid-feedback">Informe a Secret Key.</div>
        </div>
      </div>

      <div class="col-12 col-lg-5 mb-3 gatewayCampoPagarmeProjeto">
        <div class="form-floating">
          <input id="gatewayAccountIdProjeto" type="text" class="form-control input-phsa-focus input-phsa-hover" value="<?= $pagarmeSelecionado ? htmlspecialchars($gatewayConfig['account_id'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" <?= $podeAtualizarGateway && $pagarmeSelecionado ? '' : 'readonly' ?>>
          <label for="gatewayAccountIdProjeto">Account ID / conta recebedora</label>
        </div>
      </div>

      <div class="col-12 col-lg-4 mb-3 gatewayCampoPagarmeProjeto">
        <div class="form-floating">
          <select id="gatewayEnvironmentProjeto" class="form-select input-phsa-focus input-phsa-hover" <?= $podeAtualizarGateway ? '' : 'disabled' ?>>
            <option value="test" <?= ($gatewayConfig['environment'] ?? 'test') === 'test' ? 'selected' : '' ?>>Teste</option>
            <option value="live" <?= ($gatewayConfig['environment'] ?? '') === 'live' ? 'selected' : '' ?>>Produção</option>
          </select>
          <label for="gatewayEnvironmentProjeto">Ambiente</label>
        </div>
      </div>

      <div class="col-12 col-lg-3 mb-3 gatewayCampoPagarmeProjeto">
        <div class="form-floating">
          <select id="gatewayEnabledProjeto" class="form-select input-phsa-focus input-phsa-hover" <?= $podeAtualizarGateway ? '' : 'disabled' ?>>
            <option value="1" <?= !array_key_exists('enabled', $gatewayConfig) || !empty($gatewayConfig['enabled']) ? 'selected' : '' ?>>Sim</option>
            <option value="0" <?= array_key_exists('enabled', $gatewayConfig) && empty($gatewayConfig['enabled']) ? 'selected' : '' ?>>Não</option>
          </select>
          <label for="gatewayEnabledProjeto">Gateway habilitado</label>
        </div>
      </div>
    </div>

    <div id="gatewaySemConfiguracaoProjeto" class="alert alert-warning mb-4" role="alert" style="display: none;">
      <div class="fw-semibold">Gateway n&#227;o configurado</div>
      <div id="gatewaySemConfiguracaoMensagemProjeto" class="small"></div>
    </div>

    <div id="gatewaySplitPagarmeProjeto" class="card border-phsa-light box-phsa-shadow-light mb-4">
      <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <div>
            <h6 class="text-phsa mb-1">Regras de split</h6>
            <div class="small text-muted">Configure os recebedores e a divisão aplicada às cobranças.</div>
          </div>
          <?php if ($podeAtualizarGateway): ?>
            <button type="button" id="btnNovaRegraGatewayProjeto" class="bto bto-phsa"><i class="fa-solid fa-plus"></i>&nbsp;Nova regra</button>
          <?php endif; ?>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0" id="tabelaRegrasGatewayProjeto">
            <thead>
              <tr>
                <th>Recebedor</th>
                <th>Valor</th>
                <th>Taxa de processamento</th>
                <th>Recebe o restante</th>
                <th>Responsável por chargeback</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody id="tabelaRegrasGatewayProjetoBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <?php if ($podeAtualizarGateway): ?>
      <div class="d-flex justify-content-end gap-2">
        <button type="button" id="btnConsultarGatewayProjeto" class="bto bto-phsa-light"><i class="fa-solid fa-arrows-rotate"></i>&nbsp;Consultar</button>
        <button type="submit" class="bto bto-phsa"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar configuração do gateway</button>
      </div>
    <?php endif; ?>

    <div id="resultadoConsultaGatewayProjeto" class="card border-phsa-light box-phsa-shadow-light mt-4" style="display: none;">
      <div class="card-body">
        <h6 class="text-phsa mb-3">Resultado da consulta</h6>
        <pre id="conteudoConsultaGatewayProjeto" class="bg-light border rounded p-3 small mb-0" style="max-height: 420px; overflow: auto;"></pre>
      </div>
    </div>
  </form>
</div>

<?php $this->push('script'); ?>
<script>
  $(function () {
    const podeAtualizarGatewayProjeto = <?= $podeAtualizarGateway ? 'true' : 'false' ?>;
    let gatewayConfigProjeto = <?= $gatewayConfigJson ?: '{}' ?>;

    function gatewayEscapar(valor) {
      return $('<div>').text(String(valor ?? '')).html();
    }

    function gatewayNormalizar() {
      if (!gatewayConfigProjeto || typeof gatewayConfigProjeto !== 'object') gatewayConfigProjeto = {};
      gatewayConfigProjeto.enabled = typeof gatewayConfigProjeto.enabled === 'boolean' ? gatewayConfigProjeto.enabled : true;
      gatewayConfigProjeto.account_id = String(gatewayConfigProjeto.account_id || '').trim();
      gatewayConfigProjeto.environment = ['test', 'live'].includes(gatewayConfigProjeto.environment) ? gatewayConfigProjeto.environment : 'test';
      if (!Array.isArray(gatewayConfigProjeto.rules)) gatewayConfigProjeto.rules = [];

      // Os meios de pagamento são definidos pelo link do gateway e não fazem parte do escopo do projeto.
      delete gatewayConfigProjeto.payment_methods;
    }

    function gatewayAtualizarInterface(limparCampos = false) {
      const gateway = $('#prj_gateway').val();
      const pagarme = gateway === 'Pagar.me';
      const campos = $('.gatewayCampoPagarmeProjeto');

      if (limparCampos && !pagarme) {
        $('#prj_gateway_pk, #prj_gateway_sk, #gatewayAccountIdProjeto').val('');
        $('#gatewayEnvironmentProjeto').val('test');
        $('#gatewayEnabledProjeto').val('1');
        gatewayConfigProjeto.rules = [];
        $('#resultadoConsultaGatewayProjeto').hide();
      }

      campos.toggle(pagarme);
      campos.find('input').prop('disabled', !pagarme).prop('readonly', !pagarme || !podeAtualizarGatewayProjeto).prop('required', false);
      campos.find('select').prop('disabled', !pagarme || !podeAtualizarGatewayProjeto);

      if (pagarme) {
        $('#prj_gateway_pk, #prj_gateway_sk').prop('required', true).prop('disabled', !podeAtualizarGatewayProjeto);
        $('#gatewayAccountIdProjeto').prop('disabled', !podeAtualizarGatewayProjeto);
      }

      const nomes = {
        MercadoPago: 'Mercado Pago',
        InfinitePay: 'Infinite Pay'
      };

      $('#gatewaySemConfiguracaoProjeto').toggle(!pagarme);
      $('#gatewaySemConfiguracaoMensagemProjeto').text(
        !pagarme
          ? `O gateway ${nomes[gateway] || gateway} ainda n\u00e3o possui configura\u00e7\u00e3o implementada no painel.`
          : ''
      );
      $('#gatewaySplitPagarmeProjeto').toggle(pagarme);
      $('#btnConsultarGatewayProjeto').toggle(pagarme);

      if (!pagarme) {
        gatewayConfigProjeto.rules = [];
        gatewayRenderizarRegras();
      }
    }

    function gatewayTipoFixo() {
      const tipos = [...new Set(gatewayConfigProjeto.rules.map(regra => regra.type).filter(Boolean))];
      return tipos.length === 1 ? tipos[0] : '';
    }

    function gatewaySomaPercentual(indiceIgnorado = -1) {
      return Number(gatewayConfigProjeto.rules.reduce(function (total, regra, indice) {
        if (indice === indiceIgnorado || regra.type !== 'percentage') return total;
        return total + Number(regra.value || 0);
      }, 0).toFixed(2));
    }

    function gatewayFormatarValor(regra) {
      if (regra.type === 'flat') return (Number(regra.value || 0) / 100).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
      return `${Number(regra.value || 0).toFixed(2).replace('.', ',')} %`;
    }

    function gatewayBadge(condicao) {
      return condicao ? '<span class="badge bg-success">sim</span>' : '<span class="badge bg-secondary">não</span>';
    }

    function gatewayAtualizarVisibilidadeNovaRegra() {
      $('#btnNovaRegraGatewayProjeto').toggle(gatewaySomaPercentual() < 100);
    }

    function gatewayRenderizarRegras() {
      const corpo = $('#tabelaRegrasGatewayProjetoBody');
      corpo.empty();

      if (!gatewayConfigProjeto.rules.length) {
        corpo.append('<tr><td colspan="6" class="text-center text-muted py-3">Nenhuma regra cadastrada.</td></tr>');
        gatewayAtualizarVisibilidadeNovaRegra();
        return;
      }

      gatewayConfigProjeto.rules.forEach(function (regra, indice) {
        const opcoes = regra.options || {};
        corpo.append(`
          <tr>
            <td><div class="text-truncate" style="max-width: 240px;">${gatewayEscapar(regra.recipient_id || '-')}</div><div class="small text-secondary">${regra.type === 'flat' ? 'Valor fixo' : 'Percentual'}</div></td>
            <td>${gatewayFormatarValor(regra)}</td>
            <td>${gatewayBadge(Boolean(opcoes.charge_processing_fee))}</td>
            <td>${gatewayBadge(Boolean(opcoes.charge_remainder_fee))}</td>
            <td>${gatewayBadge(Boolean(opcoes.liable))}</td>
            <td class="text-end">
              ${podeAtualizarGatewayProjeto ? `<button type="button" class="bto btn-sm bto-phsa-light btnEditarRegraGatewayProjeto" data-indice="${indice}" title="Editar"><i class="fa-solid fa-pen"></i></button>` : ''}
              ${podeAtualizarGatewayProjeto ? `<button type="button" class="bto btn-sm bto-phsa-light btnExcluirRegraGatewayProjeto" data-indice="${indice}" title="Excluir"><i class="fa-solid fa-trash"></i></button>` : ''}
            </td>
          </tr>
        `);
      });

      const tipoFixo = gatewayTipoFixo();
      if (tipoFixo === 'percentage') {
        const total = gatewaySomaPercentual();
        corpo.append(`<tr><td colspan="6" class="text-end small text-secondary border-top">Total usado: <strong class="${total === 100 ? 'text-success' : 'text-danger'}">${total.toFixed(2).replace('.', ',')}%</strong></td></tr>`);
      }

      gatewayAtualizarVisibilidadeNovaRegra();
    }

    function gatewayNumero(valor) {
      let texto = String(valor || '').trim().replace(/\s/g, '');
      if (texto.includes(',')) texto = texto.replace(/\./g, '').replace(',', '.');
      const numero = Number(texto);
      return Number.isFinite(numero) ? numero : NaN;
    }

    function gatewayAbrirModalRegra(modo, indice = -1) {
      const editando = modo === 'editar';
      const regraAtual = editando ? gatewayConfigProjeto.rules[indice] : null;
      if (editando && !regraAtual) return;

      const tipoFixo = gatewayTipoFixo();
      const tipo = regraAtual?.type || tipoFixo || 'percentage';
      const valor = regraAtual ? (tipo === 'flat' ? Number(regraAtual.value || 0) / 100 : Number(regraAtual.value || 0)) : '';
      const opcoes = regraAtual?.options || {};
      const tipoDesabilitado = tipoFixo && !editando ? 'disabled' : '';
      const modalId = SimpleModal.mostrar({
        titulo: editando ? 'Editar regra de split' : 'Nova regra de split',
        tamanho: 'modal-lg modal-dialog-scrollable modal-fullscreen-sm-down',
        rodape: 'hide',
        corpo: `
          <form id="formModalRegraGatewayProjeto" class="needs-validation" novalidate>
            <div class="row g-3">
              <div class="col-12"><div class="form-floating"><input id="gatewayRegraRecebedor" type="text" class="form-control" value="${gatewayEscapar(regraAtual?.recipient_id || '')}" required><label for="gatewayRegraRecebedor">Id do recebedor</label><div class="invalid-feedback">Informe o id do recebedor.</div></div></div>
              <div class="col-12"><div class="form-floating"><select id="gatewayRegraTipo" class="form-select" required ${tipoDesabilitado}><option value="percentage" ${tipo === 'percentage' ? 'selected' : ''}>Percentual (%)</option><option value="flat" ${tipo === 'flat' ? 'selected' : ''}>Valor fixo (R$)</option></select><label for="gatewayRegraTipo">Tipo</label></div></div>
              <div class="col-12"><div class="form-floating"><input id="gatewayRegraValor" type="text" class="form-control" value="${valor === '' ? '' : String(valor).replace('.', ',')}" inputmode="decimal" required><label for="gatewayRegraValor">Valor</label><div class="form-text" id="gatewayRegraValorAjuda"></div><div class="invalid-feedback">Informe um valor válido.</div></div></div>
              <div class="col-12">
                <div class="form-check mb-2"><input id="gatewayRegraTaxa" class="form-check-input" type="checkbox" ${opcoes.charge_processing_fee ? 'checked' : ''}><label class="form-check-label" for="gatewayRegraTaxa">Cobrar taxas da transação deste recebedor</label></div>
                <div class="form-check mb-2"><input id="gatewayRegraRestante" class="form-check-input" type="checkbox" ${opcoes.charge_remainder_fee ? 'checked' : ''}><label class="form-check-label" for="gatewayRegraRestante">Receber o restante</label></div>
                <div class="form-check"><input id="gatewayRegraChargeback" class="form-check-input" type="checkbox" ${opcoes.liable ? 'checked' : ''}><label class="form-check-label" for="gatewayRegraChargeback">Responsável em caso de chargeback</label></div>
              </div>
              <div class="col-12 d-flex justify-content-end gap-2 mt-4"><button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</button><button type="submit" class="bto bto-phsa"><i class="fa-solid fa-check"></i>&nbsp;${editando ? 'Atualizar' : 'Adicionar'}</button></div>
            </div>
          </form>
        `
      });

      const modal = $('#' + modalId);
      const atualizarAjuda = function () {
        modal.find('#gatewayRegraValorAjuda').text(modal.find('#gatewayRegraTipo').val() === 'flat' ? 'Informe o valor em reais.' : 'Informe um valor entre 0 e 100.');
      };

      atualizarAjuda();
      modal.on('change', '#gatewayRegraTipo', atualizarAjuda);
      modal.on('submit', '#formModalRegraGatewayProjeto', function (evento) {
        evento.preventDefault();
        const form = $(this);
        if (!this.checkValidity()) {
          form.addClass('was-validated');
          return;
        }

        const tipoSelecionado = modal.find('#gatewayRegraTipo').val();
        const valorNumerico = gatewayNumero(modal.find('#gatewayRegraValor').val());
        if (!Number.isFinite(valorNumerico) || valorNumerico < 0 || (tipoSelecionado === 'percentage' && valorNumerico > 100)) {
          modal.find('#gatewayRegraValor').addClass('is-invalid');
          return;
        }

        const valorRegra = tipoSelecionado === 'flat' ? Math.round(valorNumerico * 100) : Number(valorNumerico.toFixed(2));
        if (tipoSelecionado === 'percentage' && gatewaySomaPercentual(editando ? indice : -1) + valorRegra > 100) {
          swToastError('A soma das regras percentuais não pode ultrapassar 100%.');
          return;
        }

        const novaRegra = {
          type: tipoSelecionado,
          value: valorRegra,
          options: {
            liable: modal.find('#gatewayRegraChargeback').is(':checked'),
            charge_remainder_fee: modal.find('#gatewayRegraRestante').is(':checked'),
            charge_processing_fee: modal.find('#gatewayRegraTaxa').is(':checked')
          },
          recipient_id: modal.find('#gatewayRegraRecebedor').val().trim()
        };

        if (editando) gatewayConfigProjeto.rules[indice] = novaRegra;
        else gatewayConfigProjeto.rules.push(novaRegra);
        gatewayRenderizarRegras();
        SimpleModal.fechar(modalId);
      });
    }

    gatewayNormalizar();
    gatewayRenderizarRegras();
    gatewayAtualizarInterface();

    $('#prj_gateway').on('change', function () { gatewayAtualizarInterface(true); });
    $('#btnNovaRegraGatewayProjeto').on('click', function () { gatewayAbrirModalRegra('adicionar'); });
    $(document).on('click', '.btnEditarRegraGatewayProjeto', function () { gatewayAbrirModalRegra('editar', Number($(this).data('indice'))); });
    $(document).on('click', '.btnExcluirRegraGatewayProjeto', function () {
      if (!window.confirm('Deseja excluir esta regra de split?')) return;
      gatewayConfigProjeto.rules.splice(Number($(this).data('indice')), 1);
      gatewayRenderizarRegras();
    });

    $('#formGatewayProjeto').on('submit', function (evento) {
      evento.preventDefault();
      evento.stopPropagation();
      const form = $(this);
      if (!this.checkValidity()) {
        form.addClass('was-validated');
        return;
      }

      gatewayConfigProjeto.enabled = $('#gatewayEnabledProjeto').val() === '1';
      gatewayConfigProjeto.account_id = $('#gatewayAccountIdProjeto').val().trim();
      gatewayConfigProjeto.environment = $('#gatewayEnvironmentProjeto').val();

      const dados = form.serializeArray();
      dados.push({name: 'prj_gateway_configuracao', value: JSON.stringify(gatewayConfigProjeto)});

      swAguarde({title: 'Salvando gateway...', text: 'Aguarde enquanto processamos a configuração.', timer: null});
      $.ajax({
        url: '<?= Helpers::url('projeto/salvarGateway') ?>',
        type: 'POST',
        dataType: 'json',
        data: dados,
        success: function (response) { swResponse(response); },
        error: function () { swError('Não foi possível salvar a configuração do gateway.'); }
      });
    });

    $('#btnConsultarGatewayProjeto').on('click', function () {
      swAguarde({title: 'Consultando gateway...', text: 'Aguarde enquanto consultamos o Pagar.me.', timer: null});

      $.ajax({
        url: '<?= Helpers::url('projeto/gateway/consultar') ?>',
        type: 'POST',
        dataType: 'json',
        data: {prj_id: $('#formGatewayProjeto input[name="prj_id"]').val()},
        success: function (response) {
          swFechar();
          $('#conteudoConsultaGatewayProjeto').text(JSON.stringify(response.data ?? response, null, 2));
          $('#resultadoConsultaGatewayProjeto').show();
          swResponse(response);
        },
        error: function () {
          swFechar();
          $('#resultadoConsultaGatewayProjeto').hide();
          swError('NÃ£o foi possÃ­vel consultar o gateway.');
        }
      });
    });
  });
</script>
<?php $this->end(); ?>
