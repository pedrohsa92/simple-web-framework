<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-md mb-3 text-uppercase">
        <h4 class="m-0">API WhatsApp</h4>
      </div>
      <div class="col-md mb-3 text-end">
        <button id="btnNovoApiWhatsapp" onclick="editarApiWhatsapp()" class="bto bto-phsa-light" type="button">
          <i class="fa fa-plus"></i>&nbsp;Nova Conexão
        </button>
      </div>
    </div>

		<div id="listApiWhatsapp" class="row">
		  <div class="col-md mb-3 table-responsive">
		    <table class="table table-hover table-striped">
		      <thead>
		        <tr>
		          <th>Nome</th>
		          <th>Dispositivo</th>
		          <th>Número</th>
		          <th>Situação</th>
		          <th>Status</th>
		          <th>Última Conexão</th>
		          <th class="text-end">Ação</th>
		        </tr>
		      </thead>
		      <tbody id="apiWhatsappLista">
		        <?php if ($getApiwhatsapp->total > 0): ?>
		          <?php foreach ($getApiwhatsapp->data as $api): ?>
		            <tr>
		              <td><?= $api->wap_nome ?></td>
		              <td><?= $api->wap_device ?></td>
		              <td><?= $api->wap_numero ?></td>
		              <td>
		                <span class="badge bg-secondary">
		                  <?= $api->wap_statusnome ?>
		                </span>
		              </td>
		              <td>
		              	<span class="badge bg-<?= $api->wap_statusclass ?>">
		                  <?= $api->wap_status?'Ativo':'Inativo' ?>
		                </span>
		              </td>
		              <td><?= date('d/m/Y H:i:s', strtotime($api->wap_dtonline)) ?></td>
		              <td class="text-end">
		                <button onclick="editarApiWhatsapp(<?= $api->wap_id ?>)" class="bto btn-sm bto-phsa-light">
		                  <i class="fa fa-edit"></i>
		                </button>
		              </td>
		            </tr>
		          <?php endforeach; ?>
		        <?php else: ?>
		          <tr>
		            <td colspan="20" class="text-center text-muted">
		              <i class="fa fa-exclamation-circle"></i>&nbsp;Nenhuma conexão cadastrada.
		            </td>
		          </tr>
		        <?php endif; ?>
		      </tbody>
		    </table>
		  </div>
		</div>

		<div id="divApiWhatsappForm" class="row" style="display: none;">
		  <div class="col-md mb-3">
		    <form id="formApiWhatsapp" class="needs-validation" novalidate>
		      <input id="wap_id" name="wap_id" hidden>

		      <div class="row">
		        <div class="col-md mb-3">
		          <div class="form-floating">
		            <input 
		              id="wap_nome" 
		              name="wap_nome" 
		              class="form-control input-phsa-focus input-phsa-hover" 
		              placeholder="Nome de Identificação" 
		              maxlength="10" 
		              required>
		            <label for="wap_nome">Nome de Identificação</label>
		            <div class="invalid-feedback">Campo obrigatório (máx. 10 caracteres)</div>
		          </div>
		        </div>
		        <div class="col-md mb-3">
		          <div class="form-floating">
		            <input 
		              id="wap_token" 
		              name="wap_token" 
		              class="form-control input-phsa-focus input-phsa-hover" 
		              placeholder="Token de Acesso" 
		              minlength="5" 
		              required>
		            <label for="wap_token">Token de Acesso</label>
		            <div class="invalid-feedback">Campo obrigatório (mín. 5 caracteres)</div>
		          </div>
		        </div>
						<div class="col-md mb-3">
						  <div class="form-floating">
						    <select id="wap_status" name="wap_status" class="form-select input-phsa-focus input-phsa-hover">
						      <option value="1">Ativo</option>
						      <option value="0">Inativo</option>
						    </select>
						    <label for="wap_status">Status</label>
						  </div>
						</div>
		      </div>

					<div id="wap_detalhes" class="alert alert-info" style="display: none;">
					  <strong>Dispositivo:</strong> <span id="info_wap_device"></span><br>
					  <strong>Número:</strong> <span id="info_wap_numero"></span><br>
					  <strong>Situação:</strong> <span id="info_wap_situacao"></span><br>
					  <strong>Status:</strong> <span id="info_wap_status"></span><br>
					  <strong>Data Cadastro:</strong> <span id="info_wap_dtcad"></span><br>
					  <strong>Data Alteração:</strong> <span id="info_wap_dtalter"></span><br>
					  <strong>Última Conexão:</strong> <span id="info_wap_dtonline"></span>
					</div>

		      <div class="row">
		        <div class="col-md-6 mb-3 text-start">
		          <button type="button" class="bto bto-phsa-light" onclick="editarApiWhatsapp()">
		            <i class="fas fa-times"></i>&nbsp;Cancelar
		          </button>
							<button id="btnTestarApi" class="bto bto-phsa-dark" type="button" style="display: none;" onclick="testarApiWhatsapp()">
							  <i class="fa-solid fa-paper-plane"></i>&nbsp;Testar api
							</button>
		        </div>
		        <div class="col-md-6 mb-3 text-end">
		          <button class="bto bto-phsa" type="submit">
		            <i class="fas fa-save"></i>&nbsp;Salvar
		          </button>
		        </div>
		      </div>
		    </form>
		  </div>
		</div>
  </div>
</div>

<?php $this->push('script'); ?>
<script type="text/javascript">
  $(document).ready(function () {
    SimpleForm.bindForm('#formApiWhatsapp', {
      urlDestino: '<?= Helpers::url("api_whatsapp/salvarApi") ?>'
    });
  });

	let objApiWpp = {};

	function editarApiWhatsapp(wap_id = null) {
	  limparForm();
	  if (wap_id) {
	    $.ajax({
	      url: '<?= Helpers::url("api_whatsapp/getApiwhatsapp") ?>/' + wap_id,
	      method: 'POST',
	      dataType: 'json',
	      success: function(response) {
	        if (response) {
	          objApiWpp = response;

	          exibirForm();
	          $('#wap_id').val(response.wap_id);
	          $('#wap_nome').val(response.wap_nome);
	          $('#wap_token').val(response.wap_token);
	          $('#info_wap_device').text(response.wap_device);
	          $('#info_wap_numero').text(response.wap_numero);
	          $('#info_wap_situacao').html(`<span class="badge bg-secondary">${response.wap_statusnome}</span>`);
	          $('#info_wap_status').html(`<span class="badge bg-${response.wap_statusclass}">${response.wap_status ? 'Ativo' : 'Inativo'}</span>`);
	          $('#info_wap_dtcad').text(response.wap_dtcad ? new Date(response.wap_dtcad).toLocaleString() : 'N/A');
	          $('#info_wap_dtalter').text(response.wap_dtalter ? new Date(response.wap_dtalter).toLocaleString() : 'N/A');
	          $('#info_wap_dtonline').text(response.wap_dtonline ? new Date(response.wap_dtonline).toLocaleString() : 'N/A');
	          $('#wap_detalhes').show();

	          $('#btnTestarApi')
	            .removeClass('bto-phsa-dark bto-phsa-light')
	            .addClass(response.wap_status == 1 ? 'bto-phsa-light' : 'bto-phsa-dark')
	            .show();
	        } else {
	          swToastError('Registro não encontrado!');
	          return false;
	        }
	      }
	    });
	  } else {
	    exibirForm();
	  }
	}

	function testarApiWhatsapp() {
	  if ( objApiWpp.wap_status==0 || objApiWpp.wap_id==undefined || objApiWpp.wap_id==null ) {
	    swError('Esta conexão está inativada!');
	    return;
	  }

	  setTimeout(function() {
	    $('#numberTest').focus();
	    $('.celular, .whatsapp').mask('(99) 9 9999-9999');
	  }, 1000);

	  Swal.fire({
	    title: "Testar API WhatsApp",
	    html: `
	      <div class="row">
	        <div class="col-sm-12 mb-3 text-start">
	          <label class="text-secondary mb-1" for="numberTest">Celular</label>
	          <input id="numberTest" type="text" class="form-control celular" placeholder="Celular">
	        </div>
	        <div class="col-sm-12 mb-3 text-start">
	          <label class="text-secondary mb-1" for="messageText">Mensagem</label>
	          <textarea id="messageText" class="form-control swal-content__textarea" placeholder="Escreva sua mensagem..." rows="5"></textarea>
	        </div>
	      </div>
	    `,
	    showCancelButton: true,
	    showConfirmButton: true,
	    confirmButtonText: `<i class="fa fa-paper-plane"></i> Enviar`,
	    cancelButtonText: `<i class="fa fa-times"></i> Cancelar`,
	    confirmButtonClass: "bto bto-phsa",
	    cancelButtonClass: "bto bto-phsa-light",
	    reverseButtons: true,
	    preConfirm: () => {
	      return new Promise((resolve, reject) => {
	        var text = $('#messageText').val();
	        var number = $('#numberTest').val();

	        if (text.trim() === '') {
	          swError('A mensagem não pode estar vazia!');
	          reject();
	        }

	        if (number.trim() === '' || number.length < 10) {
	          swError('O número de celular deve ter pelo menos 10 dígitos!');
	          reject();
	        }

	        swAguarde();

	        $.ajax({
	          url: `<?= Helpers::url('api_whatsapp/consumirApiWpp') ?>`,
	          type: 'post',
	          dataType: 'json',
	          data: { 
	            endPoint: 'sendText',
	            wap_id: objApiWpp.wap_id,
	            number: number,
	            text: text
	          },
	          success: function(response) {
	            // console.log(response);
	            swResponse(response);
	            resolve();
	          }
	        }).fail(() => {
	          swError('Erro ao enviar mensagem!');
	          reject();
	        });
	      });
	    }
	  });
	}

  function exibirForm() {
    $('#btnNovoApiWhatsapp').toggle();
    $('#listApiWhatsapp').toggle();
    $('#divApiWhatsappForm').toggle();
    $('#formApiWhatsapp')[0].reset();
    $('#formApiWhatsapp').removeClass('was-validated');
    $('.input-phsa-focus').removeClass('is-valid is-invalid');
    $('#wap_detalhes').hide();
  }

  function limparForm() {
    $('#wap_id').val("");
    $('#wap_nome').val("");
    $('#wap_token').val("");
    $('#wap_status').val("");
		$('#info_wap_device').text("");
		$('#info_wap_numero').text("");
		$('#info_wap_situacao').text("");
		$('#info_wap_status').text("");
		$('#info_wap_dtcad').text("");
		$('#info_wap_dtalter').text("");
		$('#info_wap_dtonline').text("");
		$('#wap_detalhes').hide();
  }
</script>
<?php $this->end(); ?>