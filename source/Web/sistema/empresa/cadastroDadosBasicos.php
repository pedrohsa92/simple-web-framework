





<form id="formDadosBasicos" class="needs-validation" novalidate>
  <input name="emp_id" value="<?= $empresa->emp_id ?? '' ?>" hidden>
  <input name="location" value="/cadastro/dadosbasicos" hidden>

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Dados básicos</h5>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="emp_cnpjcpf"
          name="emp_cnpjcpf"
          value="<?= $empresa->emp_cnpjcpf ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Documento"
          required
        >
        <label for="emp_cnpjcpf">Documento</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>

    <div class="col-sm-8 mb-3">
      <div class="form-floating">
        <input
          id="emp_razaosocial"
          name="emp_razaosocial"
          value="<?= $empresa->emp_razaosocial ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Nome / Razão Social"
          required
        >
        <label for="emp_razaosocial">Nome / Razão Social</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-8 mb-3">
      <div class="form-floating">
        <input
          id="emp_nomefantasia"
          name="emp_nomefantasia"
          value="<?= $empresa->emp_nomefantasia ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Nome Social / Fantasia"
        >
        <label for="emp_nomefantasia">Nome Social / Fantasia</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="emp_dtnasc"
          name="emp_dtnasc"
          value="<?= $empresa->emp_dtnasc ?? '' ?>"
          type="date"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Data"
        >
        <label for="emp_dtnasc">Data</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 mb-3">
      <div class="form-floating">
        <textarea
          id="emp_observacao"
          name="emp_observacao"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Observação"
          style="height: 120px;"
        ><?= $empresa->emp_observacao ?? '' ?></textarea>
        <label for="emp_observacao">Observação</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-juridica-area">
    <div class="col-12 text-phsa">
      <h5>Dados de pessoa jurídica</h5>
    </div>

    <div class="col-md-4 mb-3">
      <div class="form-floating">
        <input
          id="emp_im"
          name="emp_im"
          value="<?= $empresa->emp_im ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica"
          placeholder="Inscrição Municipal"
        >
        <label for="emp_im">Inscrição Municipal</label>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="form-floating">
        <input
          id="emp_ie"
          name="emp_ie"
          value="<?= $empresa->emp_ie ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica"
          placeholder="Inscrição Estadual"
        >
        <label for="emp_ie">Inscrição Estadual</label>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="form-floating">
        <select
          id="emp_idnaturezajuridica"
          name="emp_idnaturezajuridica"
          class="form-select input-phsa-focus input-phsa-hover"
        >
          <option value="" selected hidden>Selecione a natureza jurídica</option>
          <?php if ($getNaturezaJuridica->total > 0): ?>
            <?php foreach ($getNaturezaJuridica->data as $naturezaJuridica): ?>
              <option value="<?= $naturezaJuridica->id ?>" <?= (($empresa->emp_idnaturezajuridica ?? null) == $naturezaJuridica->id) ? 'selected' : '' ?>>
                <?= $naturezaJuridica->ntj_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="emp_idnaturezajuridica">Natureza jurídica</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-fisica-area">
    <div class="col-12 text-phsa">
      <h5>Dados de pessoa física</h5>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="emp_rg"
          name="emp_rg"
          value="<?= $empresa->emp_rg ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica"
          placeholder="RG"
        >
        <label for="emp_rg">RG</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="emp_nomemae"
          name="emp_nomemae"
          value="<?= $empresa->emp_nomemae ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica"
          placeholder="Nome da mãe"
        >
        <label for="emp_nomemae">Nome da mãe</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="emp_nomepai"
          name="emp_nomepai"
          value="<?= $empresa->emp_nomepai ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica"
          placeholder="Nome do pai"
        >
        <label for="emp_nomepai">Nome do pai</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-fisica-area">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select
          id="emp_idsexo"
          name="emp_idsexo"
          class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica"
        >
          <option value="" selected hidden>Selecione o sexo</option>
          <?php if ($getSexo->total > 0): ?>
            <?php foreach ($getSexo->data as $sexo): ?>
              <option value="<?= $sexo->id ?>" <?= (($empresa->emp_idsexo ?? null) == $sexo->id) ? 'selected' : '' ?>>
                <?= $sexo->sex_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="emp_idsexo">Sexo</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select
          id="emp_idestadocivil"
          name="emp_idestadocivil"
          class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica"
        >
          <option value="" selected hidden>Selecione o estado civil</option>
          <?php if ($getEstadoCivil->total > 0): ?>
            <?php foreach ($getEstadoCivil->data as $estadoCivil): ?>
              <option value="<?= $estadoCivil->id ?>" <?= (($empresa->emp_idestadocivil ?? null) == $estadoCivil->id) ? 'selected' : '' ?>>
                <?= $estadoCivil->ecv_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="emp_idestadocivil">Estado civil</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select
          id="emp_idnaturalidade"
          name="emp_idnaturalidade"
          class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica"
        >
          <option value="" selected hidden>Selecione a naturalidade</option>
          <?php if ($getNaturalidade->total > 0): ?>
            <?php foreach ($getNaturalidade->data as $naturalidade): ?>
              <option value="<?= $naturalidade->id ?>" <?= (($empresa->emp_idnaturalidade ?? null) == $naturalidade->id) ? 'selected' : '' ?>>
                <?= $naturalidade->nat_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="emp_idnaturalidade">Naturalidade</label>
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
      function limparDesabilitarPessoaFisica() {
        $('.campo-pessoa-fisica').each(function () {
          $(this).val('');
          $(this).attr('disabled', true);
          $(this).attr('title', 'Disponível apenas para Pessoa Física/CPF');
        });

        $('.campo-pessoa-fisica-area').hide();
      }

      function limparDesabilitarPessoaJuridica() {
        $('.campo-pessoa-juridica').each(function () {
          $(this).val('');
          $(this).attr('disabled', true);
          $(this).attr('title', 'Disponível apenas para Pessoa Jurídica/CNPJ');
        });

        $('.campo-pessoa-juridica-area').hide();
      }

      function habilitarPessoaFisica() {
        $('.campo-pessoa-fisica-area').show();

        $('.campo-pessoa-fisica').each(function () {
          $(this).removeAttr('disabled');
          $(this).removeAttr('title');
        });
      }

      function habilitarPessoaJuridica() {
        $('.campo-pessoa-juridica-area').show();

        $('.campo-pessoa-juridica').each(function () {
          $(this).removeAttr('disabled');
          $(this).removeAttr('title');
        });
      }

      function ajustarDocumentoEmpresa(limparCampos = true) {
        const documento = $('#emp_cnpjcpf').val().replace(/\D/g, '');
        const hoje = new Date();
        const dataMaxima = new Date(hoje);
        const dataMinima = new Date(hoje);

        dataMaxima.setDate(dataMaxima.getDate() - 1);
        dataMinima.setFullYear(dataMinima.getFullYear() - 150);

        const maxDate = dataMaxima.toISOString().split('T')[0];
        const minDate = dataMinima.toISOString().split('T')[0];

        $('#emp_cnpjcpf').unmask();

        if (documento.length === 11) {
          $('#emp_cnpjcpf').mask('999.999.999-99');

          $('label[for="emp_cnpjcpf"]').text('CPF');
          $('label[for="emp_razaosocial"]').text('Nome completo');
          $('label[for="emp_nomefantasia"]').text('Nome social');
          $('label[for="emp_dtnasc"]').text('Data de nascimento');

          $('#emp_dtnasc').attr('min', minDate).attr('max', maxDate);

          habilitarPessoaFisica();

          if (limparCampos) {
            limparDesabilitarPessoaJuridica();
          } else {
            $('.campo-pessoa-juridica-area').hide();
            $('.campo-pessoa-juridica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Jurídica/CNPJ');
          }

          return;
        }

        if (documento.length === 14) {
          $('#emp_cnpjcpf').mask('99.999.999/9999-99');

          $('label[for="emp_cnpjcpf"]').text('CNPJ');
          $('label[for="emp_razaosocial"]').text('Razão social');
          $('label[for="emp_nomefantasia"]').text('Nome fantasia');
          $('label[for="emp_dtnasc"]').text('Data de fundação');

          $('#emp_dtnasc').removeAttr('min').removeAttr('max');

          habilitarPessoaJuridica();

          if (limparCampos) {
            limparDesabilitarPessoaFisica();
          } else {
            $('.campo-pessoa-fisica-area').hide();
            $('.campo-pessoa-fisica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Física/CPF');
          }

          return;
        }

        $('label[for="emp_cnpjcpf"]').text('Documento');
        $('label[for="emp_razaosocial"]').text('Nome / Razão Social');
        $('label[for="emp_nomefantasia"]').text('Nome Social / Fantasia');
        $('label[for="emp_dtnasc"]').text('Data');

        $('#emp_dtnasc').removeAttr('min').removeAttr('max');

        if (limparCampos) {
          limparDesabilitarPessoaFisica();
          limparDesabilitarPessoaJuridica();
        } else {
          $('.campo-pessoa-fisica-area, .campo-pessoa-juridica-area').hide();
          $('.campo-pessoa-fisica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Física/CPF');
          $('.campo-pessoa-juridica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Jurídica/CNPJ');
        }
      }

      <?php if (is_null($empresa) || is_null($empresa->emp_id)): ?>
        $('#emp_cnpjcpf').focus();
      <?php endif ?>

      ajustarDocumentoEmpresa(false);

      $('#emp_cnpjcpf').on('focus keypress', function () {
        $(this).unmask();
      });

      $('#emp_cnpjcpf').on('input blur change', function () {
        ajustarDocumentoEmpresa(true);
      });

      $('#formDadosBasicos').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const form = $(this);
        const buttons = form.find('button[type="submit"], input[type="submit"]');

        ajustarDocumentoEmpresa(false);

        if (form.data('enviando') === true) {
          return;
        }

        if (!this.checkValidity()) {
          form.addClass('was-validated');
          return;
        }

        form.data('enviando', true);
        buttons.attr('disabled', true);

        swAguarde({
          title: 'Salvando empresa...',
          text: 'Aguarde enquanto processamos os dados.',
          timer: null
        });

        $.ajax({
          url: '<?= Helpers::url("empresa/salvarEmpresa") ?>',
          type: 'POST',
          dataType: 'json',
          data: form.serialize(),
          success: function (response) {
            swResponse(response).then(function () {
              setTimeout(function () {
                if (response.status && response.location) {
                  window.location.href = response.location;
                }
              }, 2000);

              form.data('enviando', false);
              buttons.attr('disabled', false);
            });
          },
          error: function () {
            form.data('enviando', false);
            buttons.attr('disabled', false);

            swError('Não foi possível salvar os dados básicos da empresa.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>
