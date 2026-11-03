<?php $this->layout("sistema::sistema"); ?>

<?php
  $prefixo = $prefixo ?? '';
  $listBaseDtcad = $prefixo !== '' ? "{$prefixo}_dtcad" : 'dtcad';
  $listBaseDtalter = $prefixo !== '' ? "{$prefixo}_dtalter" : 'dtalter';
?>

<div id="listBase_card" class="card shadow-sm">
  <div id="listBase_cardBody" class="card-body">
    <div id="alertDivBase" class="alert alert-warning d-none"></div>

    <?php if (empty($urlPesquisar ?? '')): ?>
      <div class="alert alert-danger">
        <strong>Erro de configuração:</strong> informe a variável <code>$urlPesquisar</code> para usar o listBase.
      </div>
    <?php endif; ?>

    <div id="listBase_header" class="row align-items-center">
      <div id="listBase_headerTitulo" class="col-12 col-md mb-3 text-uppercase">
        <h4 id="listBase_titulo" class="m-0"><?= $titulo ?? 'Pesquisa' ?></h4>

        <?php if (!empty($descricao ?? '')): ?>
          <div id="listBase_descricao" class="small text-muted mt-1">
            <?= $descricao ?>
          </div>
        <?php endif; ?>
      </div>

      <div id="listBase_headerAcoes" class="col-12 col-md-auto mb-3 text-end">
        <div id="listBase_headerAcoesGrupo" class="d-flex flex-column flex-md-row gap-2 justify-content-end">
          <?php if (!empty($urlBasico ?? '')): ?>
            <a id="listBase_btnBasico" href="<?= $urlBasico ?>" class="bto bto-phsa-light" type="button">
              <i class="fa-solid fa-sliders"></i>&nbsp;Básico
            </a>
          <?php endif; ?>
          <?php if (!empty($urlCadastrar ?? '') && empty($ocultarCadastrar ?? false)): ?>
            <a id="listBase_btnCadastrar" href="<?= $urlCadastrar ?>" class="bto bto-phsa-light" type="button">
              <i class="fa-solid fa-circle-plus"></i>&nbsp;Cadastrar
            </a>
          <?php endif; ?>

          <button id="listBase_btnTipoPesquisa" onclick="alternarTipoPesquisa()" class="bto bto-phsa-light" type="button">
            <i class="fa-solid fa-magnifying-glass-minus"></i>&nbsp;Pesquisa simples
          </button>

          <?= $this->section("listBase_botoesExtra"); ?>
        </div>
      </div>
    </div>

    <form id="listBase_formList" class="needs-validation" novalidate>
      <div id="listBase_pesquisaSimples" class="row pesquisaSimples">
        <div id="listBase_pesquisaSimplesCampo" class="col-12 mb-3">
          <div class="form-floating">
            <input
              type="text"
              id="lbLik"
              name="lbLik"
              value="<?= htmlspecialchars($_GET['lbLik'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              class="form-control input-phsa-focus input-phsa-hover"
              placeholder="Conteúdo"
            >
            <label id="listBase_lbLikLabel" for="lbLik"><?= $labelPesquisaSimples ?? 'Pesquisar por nome ou descrição' ?></label>
          </div>
        </div>
      </div>

      <div id="listBase_pesquisaPersonalizada" class="pesquisaPersonalizada">
        <?= $this->section("listBase_pesquisaPersonalizada"); ?>

        <div id="listBase_linhaFiltroData" class="row">
          <div id="listBase_colTipoFiltro" class="col-12 col-md-3 mb-3">
            <div class="form-floating">
              <select id="lbTpf" name="lbTpf" class="form-control input-phsa-focus input-phsa-hover">
                <option value="">Sem preferência</option>
                <option value="Exatidao">Exatidão</option>
                <option value="Periodo">Período</option>
              </select>
              <label id="listBase_lbTpfLabel" for="lbTpf">Tipo de filtro</label>
            </div>
          </div>

          <div id="listBase_colCampoFiltro" class="col-12 col-md-3 mb-3">
            <div class="form-floating">
              <select id="lbCmf" name="lbCmf" class="form-control input-phsa-focus input-phsa-hover" disabled>
                <option value="">Sem preferência</option>
                <option value="<?= $listBaseDtcad ?>">Data de cadastro</option>
                <option value="<?= $listBaseDtalter ?>">Data de alteração</option>
                <?= $this->section("listBase_camposFiltroData"); ?>
              </select>
              <label id="listBase_lbCmfLabel" for="lbCmf">Campo de filtro</label>
            </div>
          </div>

          <div id="listBase_colInicioFiltro" class="col-12 col-md-3 mb-3">
            <div class="form-floating">
              <input
                type="date"
                id="lbIni"
                name="lbIni"
                class="form-control input-phsa-focus input-phsa-hover text-center"
                disabled
              >
              <label id="listBase_lbIniLabel" for="lbIni">Início</label>
            </div>
          </div>

          <div id="listBase_colFimFiltro" class="col-12 col-md-3 mb-3">
            <div class="form-floating">
              <input
                type="date"
                id="lbFim"
                name="lbFim"
                class="form-control input-phsa-focus input-phsa-hover text-center"
                disabled
              >
              <label id="listBase_lbFimLabel" for="lbFim">Fim</label>
            </div>
          </div>
        </div>

        <div id="listBase_linhaOrdenacao" class="row">
          <div id="listBase_colCampoOrder" class="col-12 col-md-4 mb-3">
            <div class="form-floating">
              <select id="lbOrd" name="lbOrd" class="form-control input-phsa-focus input-phsa-hover">
                <option value="">Sem preferência</option>
                <option value="<?= $listBaseDtcad ?>">Data de cadastro</option>
                <option value="<?= $listBaseDtalter ?>">Data de alteração</option>
                <?= $this->section("listBase_camposOrdenacao"); ?>
              </select>
              <label id="listBase_lbOrdLabel" for="lbOrd">Ordenar por</label>
            </div>
          </div>

          <div id="listBase_colOrderBy" class="col-12 col-md-4 mb-3">
            <div class="form-floating">
              <select id="lbOby" name="lbOby" class="form-control input-phsa-focus input-phsa-hover" disabled>
                <option value="ASC">Crescente</option>
                <option value="DESC">Decrescente</option>
              </select>
              <label id="listBase_lbObyLabel" for="lbOby">Ordem</label>
            </div>
          </div>

          <div id="listBase_colLimit" class="col-12 col-md-4 mb-3">
            <div class="form-floating">
              <select id="lbLmt" name="lbLmt" class="form-control input-phsa-focus input-phsa-hover">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="300">300</option>
                <option value="400">400</option>
                <option value="500">500</option>
                <option value="1000" selected>1000</option>
                <option value="2000">2000</option>
                <option value="2500">2500</option>
                <option value="4000">4000</option>
                <option value="5000">5000</option>
                <option value="all">Ilimitado</option>
              </select>
              <label id="listBase_lbLmtLabel" for="lbLmt">Limite</label>
            </div>
          </div>
        </div>

        <div id="listBase_linhaBotaoPesquisar" class="row">
          <div id="listBase_colBotaoPesquisar" class="col-12 mb-3 text-end">
            <button id="listBase_btnPesquisar" class="bto bto-phsa" type="submit">
              <i class="fas fa-search"></i>&nbsp;Pesquisar
            </button>
          </div>
        </div>
      </div>
    </form>

    <div id="listBase_acoesResultado" class="<?= (int) ($getInfo->total ?? 0) > 0 ? '' : 'd-none' ?>">
      <div id="listBase_acoesResultadoLinha" class="row">
        <div id="listBase_acoesResultadoCol" class="col-12 mb-3 text-end">
          <div id="listBase_acoesResultadoGrupo" class="d-flex flex-column flex-md-row gap-2 justify-content-end">
            <button id="listBase_btnLimparPesquisa" onclick="limparPesquisa()" class="bto btn-sm bto-phsa-light bto-phsa-hover" type="button">
              <i class="fa-solid fa-eraser"></i>&nbsp;Limpar
            </button>

            <button id="listBase_btnAlternarVisualizacao" onclick="alternarVisualizacaoTabela()" class="bto btn-sm bto-phsa-light bto-phsa-hover" type="button">
              <i class="fa-solid fa-table-columns"></i>&nbsp;<span id="listBase_labelModoVisualizacao">Modo analítico</span>
            </button>

            <button id="listBase_btnDatatable" onclick="fgbDatatable('tableList')" class="bto btn-sm bto-phsa-light bto-phsa-hover" type="button">
              <i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar
            </button>

            <button id="listBase_btnExportar" onclick="fgbConverterTabelaEmXlsx('tableList', '<?= $nomeArquivo ?? 'pesquisa' ?>')" class="bto btn-sm bto-phsa-light bto-phsa-hover" type="button">
              <i class="fas fa-file-excel"></i>&nbsp;Exportar
            </button>

            <button id="listBase_btnImprimir" onclick="fgbGerarImpressao('resultadoList')" class="bto btn-sm bto-phsa-light bto-phsa-hover" type="button">
              <i class="fas fa-print"></i>&nbsp;Imprimir
            </button>
          </div>
        </div>
      </div>
    </div>

    <hr id="listBase_divisorResultado">

    <section id="resultadoList">
      <div id="listBase_resumoResultado" class="row">
        <div id="listBase_totalCadastrado" class="col-12 col-md-6 mb-2 text-muted small text-uppercase">
          <?= (int) ($getInfo->total ?? 0) ?> <?= $labelTotal ?? 'registro(s)' ?> cadastrado(s)
        </div>

        <div id="totalList" class="col-12 col-md-6 mb-2 text-muted small text-uppercase text-md-end">
          <?= (int) ($getInfo->total ?? 0) ?> <?= $labelTotal ?? 'registro(s)' ?> encontrado(s)
        </div>
      </div>

      <?php if (trim($this->section("listBase_resultado")) !== ''): ?>
        <?= $this->section("listBase_resultado"); ?>
      <?php else: ?>
        <div id="listBase_alertResultadoNaoInformado" class="alert alert-warning mt-3 mb-3">
          <strong>Atenção:</strong> não foi possível exibir os resultados.
          A seção <code>listBase_resultado</code> não foi encontrada ou está vazia.
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
    let listBaseModoVisualizacaoTabela = 'sintetico';
    let listBaseTimerPesquisa = null;
    let listBaseUrlPesquisar = '<?= $urlPesquisar ?? '' ?>';

    $(document).ready(function () {
      $('.pesquisaPersonalizada').hide();
      aplicarModoVisualizacaoTabela();

      if (validarEstruturaListBase()) {
        pesquisarListBase('inicial');
      }

      $('#lbLik').on('input', function () {
        clearTimeout(listBaseTimerPesquisa);

        const valor = String($(this).val() || '').trim();

        if (valor.length > 0 && valor.length < 4) {
          return;
        }

        listBaseTimerPesquisa = setTimeout(function () {
          pesquisarListBase('simples');
        }, 450);
      });

      $('#listBase_formList').on('submit', function (event) {
        event.preventDefault();

        if ($('.pesquisaSimples').is(':visible')) {
          pesquisarListBase('simples');
          return;
        }

        if (!this.checkValidity()) {
          event.stopPropagation();
          $(this).addClass('was-validated');
          return;
        }

        pesquisarListBase('personalizada');
      });

      $('#lbTpf').change(function () {
        const tipo = $(this).val();
        const campoFiltro = $('#lbCmf');
        const iniFiltro = $('#lbIni');
        const fimFiltro = $('#lbFim');

        if (tipo === '') {
          campoFiltro.prop('disabled', true).val('');
          iniFiltro.prop('disabled', true).val('');
          fimFiltro.prop('disabled', true).val('');
          return;
        }

        campoFiltro.prop('disabled', false);

        if (campoFiltro.val() === '') {
          campoFiltro.val(campoFiltro.find('option:not([value=""])').first().val());
        }

        iniFiltro.prop('disabled', false);
        fimFiltro.prop('disabled', tipo !== 'Periodo');

        if (tipo !== 'Periodo') {
          fimFiltro.val('');
        }
      });

      $('#lbCmf').change(function () {
        const campo = $(this).val();
        const tipo = $('#lbTpf').val();

        if (campo === '') {
          $('#lbIni').prop('disabled', true).val('');
          $('#lbFim').prop('disabled', true).val('');
          return;
        }

        $('#lbIni').prop('disabled', false);
        $('#lbFim').prop('disabled', tipo !== 'Periodo');

        if (tipo !== 'Periodo') {
          $('#lbFim').val('');
        }
      });

      $('#lbOrd').change(function () {
        const campoOrder = $(this).val();

        $('#lbOby').prop('disabled', campoOrder === '');

        if (campoOrder === '') {
          $('#lbOby').val('ASC');
        }
      });

      $('#lbIni').change(function () {
        $('#lbFim').attr('min', $(this).val());
      });
    });

    function validarEstruturaListBase() {
      limparAlertaListBase();

      if (listBaseUrlPesquisar === '') {
        alertarListBase('A variável urlPesquisar não foi informada no listBase.');
        return false;
      }

      if ($('#tbodyListBase').length <= 0) {
        alertarListBase('O arquivo de resultado não contém a tabela de resultados #tbodyListBase.');
        return false;
      }

      if (typeof renderizarLinhaListBase !== 'function') {
        alertarListBase('A função renderizarLinhaListBase(item, index) não foi definida na view filha.');
        return false;
      }

      return true;
    }

    function pesquisarListBase(tipoPesquisa) {
      if (!validarEstruturaListBase()) {
        return;
      }

      let dados = $('#listBase_formList').serializeArray();

      dados.push({
        name: 'lbTipoPesquisa',
        value: tipoPesquisa
      });

      $.ajax({
        url: listBaseUrlPesquisar,
        method: 'POST',
        data: dados,
        dataType: 'json',
        beforeSend: function () {
          $('#listBase_btnPesquisar')
            .prop('disabled', true)
            .html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Pesquisando');

          $('#tbodyListBase').html(`
            <tr>
              <td colspan="100" class="text-center text-muted py-4">
                <i class="fa-solid fa-spinner fa-spin"></i>
                Pesquisando...
              </td>
            </tr>
          `);
        },
        success: function (response) {
          if (!response.status) {
            renderizarResultadoListBase([]);
            alertarListBase(response.text || 'Erro ao pesquisar registros.');
            return;
          }

          renderizarResultadoListBase(response.data || []);
        },
        error: function () {
          renderizarResultadoListBase([]);
          alertarListBase('Erro ao executar a rota de pesquisa do listBase.');
        },
        complete: function () {
          $('#listBase_btnPesquisar')
            .prop('disabled', false)
            .html('<i class="fas fa-search"></i>&nbsp;Pesquisar');
        }
      });
    }

    function renderizarResultadoListBase(data) {
      if ($('#tbodyListBase').length <= 0) {
        alertarListBase('O arquivo de resultado não contém a tabela de resultados #tbodyListBase.');
        return;
      }

      if (!Array.isArray(data)) {
        data = [];
      }

      $('#tbodyListBase').html('');

      if (data.length <= 0) {
        $('#tbodyListBase').html(`
          <tr>
            <td colspan="100" class="text-center text-muted py-4">
              Nenhum registro encontrado.
            </td>
          </tr>
        `);

        atualizarTotalListBase(0);
        return;
      }

      data.forEach(function (item, index) {
        $('#tbodyListBase').append(renderizarLinhaListBase(item, index));
      });

      atualizarTotalListBase(data.length);
      aplicarModoVisualizacaoTabela();
      limparAlertaListBase();
    }

    function atualizarTotalListBase(total) {
      const label = '<?= $labelTotal ?? 'registro(s)' ?>';

      $('#totalList').text(total + ' ' + label + ' encontrado(s)');
      $('#listBase_acoesResultado').toggleClass('d-none', total <= 0);
    }

    function alertarListBase(texto) {
      $('#alertDivBase')
        .removeClass('d-none')
        .html('<strong>Atenção:</strong> ' + texto);
    }

    function limparAlertaListBase() {
      $('#alertDivBase')
        .addClass('d-none')
        .html('');
    }

    function alternarTipoPesquisa() {
      limparAlertaListBase();

      if ($('.pesquisaSimples').is(':visible')) {
        $('.pesquisaSimples').hide();
        $('.pesquisaPersonalizada').show();
        $('#lbLik').val('');
        $('#listBase_btnTipoPesquisa').html('<i class="fa-solid fa-magnifying-glass-plus"></i>&nbsp;Pesquisa personalizada');
        $('#lbTpf').focus();
        return;
      }

      $('.pesquisaPersonalizada').hide();
      $('.pesquisaSimples').show();
      $('#listBase_formList')[0].reset();
      $('#listBase_formList').removeClass('was-validated');
      $('#lbCmf').prop('disabled', true);
      $('#lbIni').prop('disabled', true);
      $('#lbFim').prop('disabled', true);
      $('#lbOby').prop('disabled', true);
      $('#listBase_btnTipoPesquisa').html('<i class="fa-solid fa-magnifying-glass-minus"></i>&nbsp;Pesquisa simples');
      $('#lbLik').focus();
    }

    function limparPesquisa() {
      $('#listBase_formList')[0].reset();
      $('#listBase_formList').removeClass('was-validated');
      $('#lbCmf').prop('disabled', true);
      $('#lbIni').prop('disabled', true);
      $('#lbFim').prop('disabled', true);
      $('#lbOby').prop('disabled', true);
      limparAlertaListBase();
      pesquisarListBase($('.pesquisaPersonalizada').is(':visible') ? 'personalizada' : 'simples');
      $('#lbLik').focus();
    }

    function aplicarModoVisualizacaoTabela() {
      if (listBaseModoVisualizacaoTabela === 'analitico') {
        $('.colSecundaria').show();
        $('#listBase_labelModoVisualizacao').text('Modo sintético');
        return;
      }

      $('.colSecundaria').hide();
      $('#listBase_labelModoVisualizacao').text('Modo analítico');
    }

    function alternarVisualizacaoTabela() {
      listBaseModoVisualizacaoTabela = listBaseModoVisualizacaoTabela === 'sintetico'
        ? 'analitico'
        : 'sintetico';

      aplicarModoVisualizacaoTabela();
    }
  </script>

  <?= $this->section("listBase_script"); ?>
<?php $this->end(); ?>
