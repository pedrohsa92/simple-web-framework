





<form id="formRelatorio" class="needs-validation" novalidate>
  <div class="row">
    <div class="col-md-8 mb-3">
      <div class="form-floating">
        <input 
          type="text" 
          id="conteudo" 
          name="conteudo" 
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Conteúdo">
        <label for="conteudo">Conteúdo</label>
      </div>
    </div>
    <div class="col-md-4 mb-3">
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

	<!-- Filtros padrão -->
  <div class="row">
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
          <option value="emp_dtcad">Data de cadastro</option>
          <option value="emp_dtalter">Data de alteração</option>
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

  <!-- Filtros padrão -->
  <div class="row">
    <div class="col-md mb-3">
      <div class="form-floating">
        <select 
          id="campoOrder" 
          name="campoOrder" 
          class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Sem preferência</option>
          <option value="emp_razaosocial">Razão social</option>
          <option value="emp_nomefantasia">Nome Fantasia</option>
          <option value="emp_dtcad">Data de cadastro</option>
          <option value="emp_dtalter">Data de alteração</option>
        </select>
        <label for="campoOrder">Ordenar por</label>
      </div>
    </div>
    <div class="col-md mb-3">
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
    <div class="col-md mb-3">
      <div class="form-floating">
        <select 
          id="limit" 
          name="limit" 
          class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Sem limite</option>
					<option value="10">10</option>
					<option value="25">25</option>
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="250">250</option>
					<option value="500">500</option>
					<option value="1000">1000</option>
					<option value="1500">1500</option>
					<option value="2000">2000</option>
					<option value="2500">2500</option>
					<option value="3000">3000</option>
					<option value="3500">3500</option>
					<option value="4000">4000</option>
					<option value="4500">4500</option>
					<option value="5000">5000</option>
					<option value="personalizado">Personalizado</option>
        </select>
        <label for="limit">Limite</label>
      </div>
      <div id="divLimit" class="w-100 d-none">
      	<button onclick="alternarLimit()" type="button" class="btn p-0 m-0 small text-phsa">
      		<small>Selecionar limite na lista</small>
      	</button>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12 mb-3 text-end">
      <button id="btnPesquisar" class="bto bto-phsa" type="submit">
        <i class="fas fa-search"></i>&nbsp;Pesquisar
      </button>
    </div>
  </div>
</form>

<hr>

<section id="resultadoList">
  <div class="row">
    <div id="totalList" class="col-sm-12 mb-2 text-muted text-uppercase small text-center">
    	<!-- x empresa(s) encontrada(s) -->
    </div>
  </div>

  <div class="row">
    <div class="col-md-12 mb-3 table-responsive">
      <table id="tableList" class="table table-striped table-hover">
        <thead>
          <tr>
            <th class="">ID</th>
            <th class="">Identificação</th>
            <th class="">Documento</th>
          </tr>
        </thead>
        <tbody id="tbodyList">
          <tr>
            <td class="table-warning text-center h5 text-phsa" colspan="10">Selecione os filtros e clique em pesquisar</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<div id="btnOpcoes" class="row">
  <div class="col-md-12 mb-3 text-end">
		<button id="btnCompartilhar" class="bto bto-phsa-light bto-phsa-hover" type="button">
		  <i class="fa-solid fa-share"></i>&nbsp;Compartilhar relatório
		</button>
    <button onclick="fgbDatatable('tableList')" class="bto bto-phsa-light bto-phsa-hover" type="button">
      <i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar tabela
    </button>
    <button onclick="fgbConverterTabelaEmXlsx('tableList', $relatorioFile)" class="bto bto-phsa-light bto-phsa-hover" type="button">
      <i class="fas fa-file-excel"></i>&nbsp;Exportar para Excel
    </button>
    <button onclick="fgbGerarImpressao('resultadoList')" class="bto bto-phsa-light bto-phsa-hover" type="button">
      <i class="fas fa-print"></i>&nbsp;Imprimir Resultado
    </button>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
  	let urlParams = <?= json_encode($urlParams) ?>;

		$(document).ready(function () {
		  if (Object.keys(urlParams).length > 0) {
		    Object.keys(urlParams).forEach(key => {
		      let input = $(`[name="${key}"]`);
		      if (input.length) {
		        input.val(urlParams[key]);
		      }
		    });

		    setTimeout(function(){
		    	$("#formRelatorio").submit();
		    }, 1500);
		  }

    	$('#btnOpcoes').hide();

      $('#tipoFiltro').change(function () {
        const tipo = $(this).val();
        const campoFiltro = $('#campoFiltro');
        const iniFiltro = $('#iniFiltro');
        const fimFiltro = $('#fimFiltro');

        if (tipo=='') {
          // Sem preferência
          campoFiltro.prop('disabled', true).val('');
          iniFiltro.prop('disabled', true).val('');
          fimFiltro.prop('disabled', true).val('');
        } else {
          // Exatidão ou Período
          campoFiltro.prop('disabled', false);
          const primeiraOpcao = campoFiltro.find('option:not([value=""])').first().val();

          if (campoFiltro.val()=='') {
            campoFiltro.val(primeiraOpcao);
          }

          campoFiltro.find('option[value=""]').prop('hidden', true);

          if (tipo=='Exatidao') {
            iniFiltro.prop('disabled', false);
            fimFiltro.prop('disabled', true).val('');
          } else if (tipo=='Periodo') {
            iniFiltro.prop('disabled', false);
            fimFiltro.prop('disabled', false);
          }
        }
      });

      $('#campoFiltro').change(function () {
        const campo = $(this).val();
        const iniFiltro = $('#iniFiltro');
        const fimFiltro = $('#fimFiltro');

        if (campo=='') {
          iniFiltro.prop('disabled', true).val('');
          fimFiltro.prop('disabled', true).val('');
        } else {
          iniFiltro.prop('disabled', false);
          if ($('#tipoFiltro').val()=='Exatidao') {
            fimFiltro.prop('disabled', true).val('');
          } else if ($('#tipoFiltro').val()=='Periodo') {
            fimFiltro.prop('disabled', false);
          }
        }
      });

      $('#campoOrder').change(function () {
        const campoOrder = $(this).val();
        const orderBy = $('#orderBy');

        if (campoOrder=='') {
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
        if ($('#tipoFiltro').val()=='Periodo') {
          $(this).prop('required', true);
        }
      });

		  $(document).on('change', '#limit', function () {
		    if ($(this).val()=='personalizado') {
		      alternarLimit();
		    }
		  });

      $("#formRelatorio").submit(function(event) {
        if ( this.checkValidity()===false ) {
          event.preventDefault();
          event.stopPropagation();
          swToastError('Confira os campos obrigatórios!');
          this.classList.add('was-validated');
          return false;
        }
        event.preventDefault();
        this.classList.add('was-validated');

        $('#btnPesquisar').attr('disabled', true);

        $.ajax({
          url: '<?= Helpers::url('relatorio/execute/'.$relatorioFile)?>',
          type: 'post',
          dataType: 'json',
          data: $('#formRelatorio').serialize(),
          beforeSend: function() {
          	swAguarde();
          },
          success: function (response) {
          	setTimeout(function(){
          		swFechar();
	          	$('#btnPesquisar').attr('disabled', false);
	          	atualizarResultado(response);
          	}, 1500);
          }
        });
      });
    });

		function atualizarResultado(response) {
		  if ( !response.status ) {
		    $("#totalList").text("Nenhum resultado encontrado");
		    $("#tbodyList").html(`
		      <tr>
		        <td class="table-danger text-center h5 text-phsa" colspan="10">
		        	${response.text}
		        </td>
		      </tr>
		    `);
		    $("#btnOpcoes,#btnCompartilhar").hide();
		    return;
		  }

		  if ( response.total==0 ) {
		    $("#totalList").text("Nenhum resultado encontrado");
		    $("#tbodyList").html(`
		      <tr>
		        <td class="table-warning text-center h5 text-phsa" colspan="10">
		          Nenhum resultado encontrado
		        </td>
		      </tr>
		    `);
		    $("#btnOpcoes,#btnCompartilhar").hide();
		    return;
		  }

		  $("#totalList").text(`${response.total} resultado(s) encontrado(s)`);
		  $("#tbodyList").empty();

		  response.data.forEach(item => {
		    $("#tbodyList").append(`
		      <tr>
		        <td>
		        	${item.emp_id}
		        </td>
		        <td>
		    			${item.emp_razaosocial}<br>
		    			<span class="small text-secondary">${item.emp_nomefantasia}</span>
		        </td>
		        <td>
		    			${item.emp_cnpjcpf}
		        </td>
		      </tr>
		    `);
		  });

  		$("#btnCompartilhar").attr("onclick", `fgbCopiarParaAreaDeTransferencia('${response.urlString}', 'URL do relatório')`).fadeIn(500);
		  $("#btnOpcoes").fadeIn(500);
		}

    function alternarLimit() {
		  let limitContainer = $('#limit').parent();
		  let divLimit = $('#divLimit');

		  limitContainer.html(`
		    <input 
		      id="limit" 
		      name="limit" 
		      type="number" 
		      inputmode="numeric" 
		      class="form-control input-phsa-focus input-phsa-hover" 
		      placeholder="Limite"
		      min="1">
		    <label for="limit">Limite</label>
		  `);

		  divLimit.removeClass('d-none').html(`
		    <button onclick="restaurarLimit()" type="button" class="btn p-0 m-0 small text-phsa">
		      <small>Selecionar limite na lista</small>
		    </button>
		  `);

		  $('#limit').focus();
		}

		function restaurarLimit() {
		  let limitContainer = $('#limit').parent();
		  let divLimit = $('#divLimit');

		  limitContainer.html(`
		    <select 
		      id="limit" 
		      name="limit" 
		      class="form-control input-phsa-focus input-phsa-hover">
		      <option value="">Sem limite</option>
		      <option value="10">10</option>
		      <option value="25">25</option>
		      <option value="50">50</option>
		      <option value="100">100</option>
		      <option value="250">250</option>
		      <option value="500">500</option>
		      <option value="1000">1000</option>
		      <option value="1500">1500</option>
		      <option value="2000">2000</option>
		      <option value="2500">2500</option>
		      <option value="3000">3000</option>
		      <option value="3500">3500</option>
		      <option value="4000">4000</option>
		      <option value="4500">4500</option>
		      <option value="5000">5000</option>
		      <option value="personalizado">Personalizado</option>
		    </select>
		    <label for="limit">Limite</label>
		  `);

		  divLimit.addClass('d-none');

		  $('#limit').focus();
		}
  </script>
<?php $this->end(); ?>