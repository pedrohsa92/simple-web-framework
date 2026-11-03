





<?php if ( $dadosComplementares->status ): ?>
  <div class="row">
    <div class="col-sm-12 mb-3">
      <div class="alert alert-warning">
        <ul>
          <?php foreach ($dadosComplementares->alert as $alerta): ?>
            <li><?= $alerta ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
<?php else: ?>
  <form id="formDadosComplementares" class="needs-validation" novalidate>
    <input name="cli_id" value="<?=$cliente->cli_id?>" hidden>
    <input name="location" value="/cadastro/dadoscomplementares" hidden>

    <div class="row mt-2">
      <div class="col-sm-6 mb-3">
        <div class="form-floating">
          <select 
            id="cli_idnaturezajuridica" 
            name="cli_idnaturezajuridica" 
            class="form-select input-phsa-focus input-phsa-hover"
            <?=($cliente->cli_cnpjcpf && strlen($cliente->cli_cnpjcpf) == 14) ? '' : 'disabled'?>
          >
            <option value="" selected hidden>Selecione a natureza jurídica</option>
            <?php if ($getNaturezaJuridica->total > 0): ?>
              <?php foreach ($getNaturezaJuridica->data as $naturezaJuridica): ?>
                <option value="<?=$naturezaJuridica->id?>" <?=($cliente->cli_idnaturezajuridica == $naturezaJuridica->id) ? 'selected' : ''?>>
                  <?=$naturezaJuridica->ntj_nome?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <label for="cli_idnaturezajuridica">Natureza Jurídica</label>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6 mb-3">
        <div class="form-floating">
          <select 
            id="cli_idestadocivil" 
            name="cli_idestadocivil" 
            class="form-select input-phsa-focus input-phsa-hover"
            <?=($cliente->cli_cnpjcpf && strlen($cliente->cli_cnpjcpf) == 11) ? '' : 'disabled'?>
          >
            <option value="" selected hidden>Selecione o estado civil</option>
            <?php if ($getEstadoCivil->total > 0): ?>
              <?php foreach ($getEstadoCivil->data as $estadoCivil): ?>
                <option value="<?=$estadoCivil->id?>" <?=($cliente->cli_idestadocivil == $estadoCivil->id) ? 'selected' : ''?>>
                  <?=$estadoCivil->ecv_nome?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <label for="cli_idestadocivil">Estado Civil</label>
        </div>
      </div>
      <div class="col-sm-6 mb-3">
        <div class="form-floating">
          <select 
            id="cli_idsexo" 
            name="cli_idsexo" 
            class="form-select input-phsa-focus input-phsa-hover"
            <?=($cliente->cli_cnpjcpf && strlen($cliente->cli_cnpjcpf) == 11) ? '' : 'disabled'?>
          >
            <option value="" selected hidden>Selecione o sexo</option>
            <?php if ($getSexo->total > 0): ?>
              <?php foreach ($getSexo->data as $sexo): ?>
                <option value="<?=$sexo->id?>" <?=($cliente->cli_idsexo == $sexo->id) ? 'selected' : ''?>>
                  <?=$sexo->sex_nome?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <label for="cli_idsexo">Sexo</label>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12 mb-3">
        <div class="form-floating">
          <textarea 
            id="cli_descricaopersonalizada" 
            name="cli_descricaopersonalizada" 
            class="form-control input-phsa-focus input-phsa-hover" 
            placeholder="Descrição Personalizada" 
            style="height: 200px;"
          ><?=$cliente->cli_descricaopersonalizada?></textarea>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12 mb-3 text-end">
        <button class="bto bto-phsa" type="submit">
          <i class="fas fa-save"></i>&nbsp;Salvar
        </button>
      </div>
    </div>
  </form>

  <?php $this->push('script'); ?>
  	<script type="text/javascript">
  	  $(document).ready(function () {
  	    const cnpjCpf = '<?=$cliente->cli_cnpjcpf?>';
  	    const length = cnpjCpf.replace(/\D/g, '').length;
  	    if ( length==11 ) {
  	      $('#cli_idestadocivil, #cli_idsexo').prop('disabled', false);
  	      $('#cli_idnaturezajuridica').prop('disabled', true).val('');
  	    } else if ( length==14 ) {
  	      $('#cli_idnaturezajuridica').prop('disabled', false);
  	      $('#cli_idestadocivil, #cli_idsexo').prop('disabled', true).val('');
  	    }

  	    tinymce.init({
  	      selector: '#cli_descricaopersonalizada',
  	      toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist'
  	    });

  	    SimpleForm.bindForm('#formDadosComplementares', {
  	      urlDestino: '<?= Helpers::url("empresa/salvarDadosComplementares") ?>'
  	    });
  	  });
  	</script>
  <?php $this->end(); ?>
<?php endif ?>
