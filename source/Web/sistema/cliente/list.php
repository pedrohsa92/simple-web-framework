<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-md mb-3 text-uppercase">
        <h4 class="m-0">Pesquisa de cliente</h4>
      </div>
      <div class="col-md mb-3 text-end">
        <a href="<?=Helpers::url('cliente/form')?>" class="bto bto-phsa-light" type="button">
          <i class="fa-solid fa-circle-plus"></i>&nbsp;Cadastrar
        </a>
        <button id="btnTipoPesquisa" onclick="alternarTipoPesquisa()" class="bto bto-phsa-light" type="button">
          <i class="fa-solid fa-magnifying-glass-minus"></i>&nbsp;Pesquisa simples
        </button>
      </div>
    </div>

    <form id="formList" class="needs-validation" novalidate>
      <div class="row pesquisaSimples">
        <div class="col-md-12 mb-3">
          <div class="form-floating">
            <input 
              type="text" 
              id="conteudo" 
              name="conteudo" 
              value="<?=$_GET['conteudo']?>" 
              class="form-control input-phsa-focus input-phsa-hover" 
              placeholder="Conteúdo">
            <label for="conteudo">Conteúdo</label>
          </div>
        </div>
      </div>

      <div class="row pesquisaPersonalizada">
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <select 
              id="status" 
              name="status" 
              class="form-control input-phsa-focus input-phsa-hover">
              <option value="">Sem preferência</option>
              <option value="1">Ativo</option>
              <option value="0">Inativo</option>
            </select>
            <label for="status">Status</label>
          </div>
        </div>
      </div>

      <div class="row pesquisaPersonalizada">
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <select 
              id="tipoFiltro" 
              name="tipoFiltro" 
              class="form-control input-phsa-focus input-phsa-hover">
              <option value="" selected>Sem preferência</option>
              <option value="Exatidao">Exatidão</option>
              <option value="Periodo">Período</option>
            </select>
            <label for="tipoFiltro">Tipo de Filtro</label>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <select 
              id="campoFiltro" 
              name="campoFiltro" 
              class="form-control input-phsa-focus input-phsa-hover"
              disabled>
              <option value="" selected>Sem preferência</option>
              <option value="cli_dtcad">Data de cadastro</option>
              <option value="cli_dtalter">Data de alteração</option>
            </select>
            <label for="campoFiltro">Campo de Filtro</label>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <input 
              type="date" 
              id="iniFiltro" 
              name="iniFiltro" 
              class="form-control input-phsa-focus input-phsa-hover" 
              disabled>
            <label for="iniFiltro">Início</label>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <input 
              type="date" 
              id="fimFiltro" 
              name="fimFiltro" 
              class="form-control input-phsa-focus input-phsa-hover" 
              disabled>
            <label for="fimFiltro">Fim</label>
          </div>
        </div>
      </div>

      <div class="row pesquisaPersonalizada">
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <select 
              id="campoOrder" 
              name="campoOrder" 
              class="form-control input-phsa-focus input-phsa-hover">
              <option value="">Sem preferência</option>
              <option value="cli_razaosocial">Nome Completo</option>
              <option value="cli_nomefantasia">Nome Social</option>
              <option value="cli_dtcad">Data de cadastro</option>
              <option value="cli_dtalter">Data de alteração</option>
            </select>
            <label for="campoOrder">Ordenar por</label>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <select 
              id="orderBy" 
              name="orderBy" 
              class="form-control input-phsa-focus input-phsa-hover"
              disabled>
              <option value="ASC">Crescente</option>
              <option value="DESC">Decrescente</option>
            </select>
            <label for="orderBy">Ordem</label>
          </div>
        </div>
      </div>

      <div class="row pesquisaPersonalizada">
        <div class="col-md-12 mb-3 text-end">
          <button id="btnPesquisar" class="bto bto-phsa" type="submit">
            <i class="fas fa-search"></i>&nbsp;Pesquisar
          </button>
        </div>
      </div>
    </form>

    <section id="resultadoList">
      <div class="row">
        <div class="col-sm-6 mb-2 text-muted text-uppercase small"><?=$getCliente->total?> Cliente(s) cadastrado(s)</div>
        <div id="totalList" class="col-sm-6 mb-2 text-muted text-uppercase small text-end"><?=$getCliente->total?> cliente(s) encontrado(s)</div>
      </div>

      <div class="row">
        <div class="col-md-12 mb-3 table-responsive">
          <table id="tableList" class="table table-striped table-hover">
            <thead>
              <tr>
                <th class="">ID</th>
                <th class="">Nome Completo</th>
                <th class="">Nome Social</th>
                <th class="">Documento</th>
                <th class="text-end hideOnPrint hideOnExcel">Ação</th>
              </tr>
            </thead>
            <tbody id="tbodyList">
              <?php if ($getCliente->total > 0): ?>
                <?php foreach ($getCliente->data as $cliente): ?>
                  <tr>
                    <td><?= $cliente->cli_id ?></td>
                    <td><?= $cliente->cli_razaosocial ?></td>
                    <td><?= $cliente->cli_nomefantasia ?? 'Não informado' ?></td>
                    <td>
                      <span 
                        class="border-phsa-hover-light"
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        title="Clique 2x para copiar"
                        ondblclick="fgbCopiarParaAreaDeTransferencia('<?=$cliente->cli_cnpjcpf?>', '<?=strlen($cliente->cli_cnpjcpf)==11?'CPF':(strlen($cliente->cli_cnpjcpf)==14?'CNPJ':'Documento')?>')"
                      >
                        <?= Helpers::adicionaMascaraCnpjCPf($cliente->cli_cnpjcpf) ?>
                      </span>
                    </td>
                    <td class="text-end hideOnPrint hideOnExcel">
                      <a href="<?= Helpers::url("cliente/form/{$cliente->cli_id}") ?>" class="bto bto-phsa-light">
                        <i class="fas fa-edit"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">
                    <i class="fas fa-exclamation-circle"></i>&nbsp;Nenhum cliente encontrado.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <div class="row">
      <div class="col-md-12 mb-3 text-end">
        <button onclick="fgbDatatable('tableList')" class="bto bto-phsa-light bto-phsa-hover" type="button">
          <i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar tabela
        </button>
        <button onclick="fgbConverterTabelaEmXlsx('tableList', 'pesquisaDecliente')" class="bto bto-phsa-light bto-phsa-hover" type="button">
          <i class="fas fa-file-excel"></i>&nbsp;Exportar para Excel
        </button>
        <button onclick="fgbGerarImpressao('resultadoList')" class="bto bto-phsa-light bto-phsa-hover" type="button">
          <i class="fas fa-print"></i>&nbsp;Imprimir Resultado
        </button>
      </div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      $('.pesquisaPersonalizada').hide();

      $('#tipoFiltro').change(function () {
        const tipo = $(this).val();
        const campoFiltro = $('#campoFiltro');
        const iniFiltro = $('#iniFiltro');
        const fimFiltro = $('#fimFiltro');

        if (tipo === '') {
          // Sem preferência
          campoFiltro.prop('disabled', true).val('');
          iniFiltro.prop('disabled', true).val('');
          fimFiltro.prop('disabled', true).val('');
        } else {
          // Exatidão ou Período
          campoFiltro.prop('disabled', false);
          const primeiraOpcao = campoFiltro.find('option:not([value=""])').first().val();

          if (campoFiltro.val() === '') {
            campoFiltro.val(primeiraOpcao);
          }

          campoFiltro.find('option[value=""]').prop('hidden', true);

          if (tipo === 'Exatidao') {
            iniFiltro.prop('disabled', false);
            fimFiltro.prop('disabled', true).val('');
          } else if (tipo === 'Periodo') {
            iniFiltro.prop('disabled', false);
            fimFiltro.prop('disabled', false);
          }
        }
      });

      $('#campoFiltro').change(function () {
        const campo = $(this).val();
        const iniFiltro = $('#iniFiltro');
        const fimFiltro = $('#fimFiltro');

        if (campo === '') {
          iniFiltro.prop('disabled', true).val('');
          fimFiltro.prop('disabled', true).val('');
        } else {
          iniFiltro.prop('disabled', false);
          if ($('#tipoFiltro').val() === 'Exatidao') {
            fimFiltro.prop('disabled', true).val('');
          } else if ($('#tipoFiltro').val() === 'Periodo') {
            fimFiltro.prop('disabled', false);
          }
        }
      });

      $('#campoOrder').change(function () {
        const campoOrder = $(this).val();
        const orderBy = $('#orderBy');

        if (campoOrder === '') {
          orderBy.prop('disabled', true).val('ASC');
        } else {
          orderBy.prop('disabled', false);
        }
      });

      $('#iniFiltro').change(function () {
        if ($('#campoFiltro').val() !== '') {
          $(this).prop('required', true);
        }
        $('#fimFiltro').attr('min', $(this).val());
      });

      $('#fimFiltro').change(function () {
        if ($('#tipoFiltro').val() === 'Periodo') {
          $(this).prop('required', true);
        }
      });

      $('#formList').submit(function (event) {
        event.preventDefault();
        if ($('.pesquisaPersonalizada').is(':visible')) {
          pesquisarcliente($(this).serialize());
        }
      });

      $('#conteudo').on('input', function () {
        if ( $('.pesquisaSimples').is(':visible') ) {
          pesquisarcliente({ conteudo: $(this).val() });
        }
      });
      $('#conteudo').trigger('input');
    });

    function alternarTipoPesquisa() {
      if ( $('.pesquisaSimples').is(':visible') ) {
        $('.pesquisaSimples').hide();
        $('.pesquisaPersonalizada').show();
        $('#conteudo').val('');
        $('#formList')[0].reset();
        $('#btnTipoPesquisa').html('<i class="fa-solid fa-magnifying-glass-plus"></i>&nbsp;Pesquisa personalizada');
        $('#status').focus();
      } else {
        $('.pesquisaPersonalizada').hide();
        $('.pesquisaSimples').show();
        $('#formList')[0].reset();
        $('#btnTipoPesquisa').html('<i class="fa-solid fa-magnifying-glass-minus"></i>&nbsp;Pesquisa simples');
        $('#conteudo').focus();
      }
    }

    function pesquisarcliente(data) {
      $.ajax({
        url: '<?= Helpers::url("cliente/pesquisarCliente") ?>',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function (response) {
          $('#totalList').html(`<span class="text-muted small">${response.total} cliente(s) encontrado(s)</span>`);
          gerarTbodyList(response);
        }
      });
    }

    function gerarTbodyList(data) {
      $('#tbodyList').empty();
      let html = '';

      if (data.status) {
        if (data.total > 0) {
          data.data.forEach((cliente) => {
            html += `
              <tr>
                <td>${cliente.cli_id}</td>
                <td>${cliente.cli_razaosocial}</td>
                <td>${cliente.cli_nomefantasia || 'Não informado'}</td>
                <td>${cliente.cli_cnpjcpf}</td>
                <td class="text-end hideOnPrint hideOnExcel">
                  <a href="<?= Helpers::url("cliente/form/") ?>${cliente.cli_id}" class="bto bto-phsa-light">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>`;
          });
        } else {
          html = `
            <tr class="table-warning">
              <td colspan="5" class="text-center">
                Nenhum cliente encontrado.
              </td>
            </tr>`;
        }
      } else {
        html = `
          <tr class="table-danger">
            <td colspan="5" class="text-center">
              ${data.text}<br>${data.errorInfo ? data.errorInfo[2] : ''}
            </td>
          </tr>`;
      }

      $('#tbodyList').html(html);
    }
  </script>
<?php $this->end(); ?>