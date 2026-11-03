





<form id="formDadosBasicos" class="needs-validation" novalidate>
  <input name="cli_id" value="<?=$cliente->cli_id?>" hidden>
  <input name="location" value="/cadastro/dadosbasicos" hidden>

  <div class="row">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input 
          id="cli_cnpjcpf" 
          name="cli_cnpjcpf" 
          value="<?=$cliente->cli_cnpjcpf?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Documento" 
          required 
        >
        <label for="cli_cnpjcpf">Documento</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>
    <div class="col-sm-8 mb-3">
      <div class="form-floating">
        <input 
          id="cli_razaosocial" 
          name="cli_razaosocial" 
          value="<?=$cliente->cli_razaosocial?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Razão Social" 
          required 
        >
        <label for="cli_razaosocial">Razão Social</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-8 mb-3">
      <div class="form-floating">
        <input 
          id="cli_nomefantasia" 
          name="cli_nomefantasia" 
          value="<?=$cliente->cli_nomefantasia?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Nome Fantasia"
        >
        <label for="cli_nomefantasia">Nome Fantasia</label>
      </div>
    </div>
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input 
          id="cli_dtnasc" 
          name="cli_dtnasc" 
          value="<?=$cliente->cli_dtnasc?>"
          type="date" 
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Data" 
        >
        <label for="cli_dtnasc">Data</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm mb-3">
      <div class="form-floating">
        <input 
          id="cli_im" 
          name="cli_im" 
          value="<?=$cliente->cli_im?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Inscrição Municipal" 
        >
        <label for="cli_im">Inscrição Municipal</label>
      </div>
    </div>
    <div class="col-sm mb-3">
      <div class="form-floating">
        <input 
          id="cli_ie" 
          name="cli_ie" 
          value="<?=$cliente->cli_ie?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Inscrição Estadual" 
        >
        <label for="cli_ie">Inscrição Estadual</label>
      </div>
    </div>
    <div class="col-sm mb-3">
      <div class="form-floating">
        <input 
          id="cli_rg" 
          name="cli_rg" 
          value="<?=$cliente->cli_rg?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="RG" 
        >
        <label for="cli_rg">RG</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 mb-3">
      <div class="form-floating">
        <textarea 
          id="cli_descricaoresumida" 
          name="cli_descricaoresumida" 
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Descrição Resumida" 
          style="height: 100px;" 
          maxlength="160" 
        ><?=$cliente->cli_descricaoresumida?></textarea>
        <label for="cli_descricaoresumida">Descrição Resumida</label>
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
      <?php if ( is_null($cliente->cli_id) ): ?>
        $('#cli_cnpjcpf').focus();
      <?php else: ?>
        setTimeout(function(){
          $('#cli_cnpjcpf').blur();
        }, 1000);
      <?php endif ?>

      $('#cli_cnpjcpf').on('change focus keypress', function () {
        $(this).unmask();
      });

      $('#cli_cnpjcpf').blur(function (event) {
        const length = $(this).val().replace(/\D/g, '').length;
        const today = new Date();
        const maxDate = new Date(today.setDate(today.getDate() - 1)).toISOString().split('T')[0];
        const minDate = new Date(today.setFullYear(today.getFullYear() - 150)).toISOString().split('T')[0];

        if (length === 11) {
          $(this).mask('999.999.999-99');
          $('label[for="cli_cnpjcpf"]').text('CPF');
          $('label[for="cli_razaosocial"]').text('Nome Completo');
          $('label[for="cli_nomefantasia"]').text('Nome Social');
          $('label[for="cli_dtnasc"]').text('Data de Nascimento');
          $('#cli_dtnasc').attr('min', minDate).attr('max', maxDate);
          $('#cli_im, #cli_ie').val('').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Jurídica/CNPJ');
          $('#cli_rg').val('').removeAttr('disabled');
        } else if (length === 14) {
          $(this).mask('99.999.999/9999-99');
          $('label[for="cli_cnpjcpf"]').text('CNPJ');
          $('label[for="cli_razaosocial"]').text('Razão Social');
          $('label[for="cli_nomefantasia"]').text('Nome Fantasia');
          $('label[for="cli_dtnasc"]').text('Data de Fundação');
          $('#cli_dtnasc').removeAttr('min').removeAttr('max');
          $('#cli_im, #cli_ie').val('').removeAttr('disabled').removeAttr('title');
          $('#cli_rg').val('').attr('disabled', true);
        } else {
          $(this).unmask();
          $('label[for="cli_cnpjcpf"]').text('Documento');
          $('label[for="cli_razaosocial"]').text('Nome Principal');
          $('label[for="cli_nomefantasia"]').text('Outra Identificação');
          $('label[for="cli_dtnasc"]').text('Data de Início');
          $('#cli_dtnasc').removeAttr('min').removeAttr('max');
          $('#cli_im, #cli_ie').val('').removeAttr('disabled').removeAttr('title');
        }
      });

      SimpleForm.bindForm('#formDadosBasicos', {
        urlDestino: '<?= Helpers::url("cliente/salvarCliente") ?>'
      });
    });
  </script>
<?php $this->end(); ?>