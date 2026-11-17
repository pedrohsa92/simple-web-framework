





<form id="formDadosBasicos" class="needs-validation" novalidate>
  <input name="usu_id" value="<?= $usuario->usu_id ?? '' ?>" hidden>
  <input name="location" value="/cadastro/dadosbasicos" hidden>

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Dados pessoais</h5>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="usu_cnpjcpf"
          name="usu_cnpjcpf"
          value="<?= $usuario->usu_cnpjcpf ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Documento"
          required
        >
        <label for="usu_cnpjcpf">Documento</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>

    <div class="col-sm-8 mb-3">
      <div class="form-floating">
        <input
          id="usu_razaosocial"
          name="usu_razaosocial"
          value="<?= $usuario->usu_razaosocial ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Nome / Razão Social"
          required
        >
        <label for="usu_razaosocial">Nome / Razão Social</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-8 mb-3">
      <div class="form-floating">
        <input
          id="usu_nomefantasia"
          name="usu_nomefantasia"
          value="<?= $usuario->usu_nomefantasia ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Nome Social / Fantasia"
        >
        <label for="usu_nomefantasia">Nome Social / Fantasia</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="usu_dtnasc"
          name="usu_dtnasc"
          value="<?= $usuario->usu_dtnasc ?? '' ?>"
          type="date"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Data"
        >
        <label for="usu_dtnasc">Data</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-juridica-area">
    <div class="col-12 text-phsa">
      <h5>Dados de pessoa jurídica</h5>
    </div>

    <div class="col-sm-6 mb-3">
      <div class="form-floating">
        <input
          id="usu_im"
          name="usu_im"
          value="<?= $usuario->usu_im ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica"
          placeholder="Inscrição Municipal"
        >
        <label for="usu_im">Inscrição Municipal</label>
      </div>
    </div>

    <div class="col-sm-6 mb-3">
      <div class="form-floating">
        <input
          id="usu_ie"
          name="usu_ie"
          value="<?= $usuario->usu_ie ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica"
          placeholder="Inscrição Estadual"
        >
        <label for="usu_ie">Inscrição Estadual</label>
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
          id="usu_rg"
          name="usu_rg"
          value="<?= $usuario->usu_rg ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica"
          placeholder="RG"
        >
        <label for="usu_rg">RG</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="usu_nomemae"
          name="usu_nomemae"
          value="<?= $usuario->usu_nomemae ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica"
          placeholder="Nome da mãe"
        >
        <label for="usu_nomemae">Nome da mãe</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input
          id="usu_nomepai"
          name="usu_nomepai"
          value="<?= $usuario->usu_nomepai ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica"
          placeholder="Nome do pai"
        >
        <label for="usu_nomepai">Nome do pai</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-fisica-area">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select
          id="usu_idestadocivil"
          name="usu_idestadocivil"
          class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica"
        >
          <option value="" selected hidden>Selecione o estado civil</option>
          <?php if ($getEstadoCivil->total > 0): ?>
            <?php foreach ($getEstadoCivil->data as $estadoCivil): ?>
              <option value="<?= $estadoCivil->id ?>" <?= (($usuario->usu_idestadocivil ?? null) == $estadoCivil->id) ? 'selected' : '' ?>>
                <?= $estadoCivil->ecv_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="usu_idestadocivil">Estado civil</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select
          id="usu_idsexo"
          name="usu_idsexo"
          class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica"
        >
          <option value="" selected hidden>Selecione o sexo</option>
          <?php if ($getSexo->total > 0): ?>
            <?php foreach ($getSexo->data as $sexo): ?>
              <option value="<?= $sexo->id ?>" <?= (($usuario->usu_idsexo ?? null) == $sexo->id) ? 'selected' : '' ?>>
                <?= $sexo->sex_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="usu_idsexo">Sexo</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select
          id="usu_idnaturalidade"
          name="usu_idnaturalidade"
          class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica"
        >
          <option value="" selected hidden>Selecione a naturalidade</option>
          <?php if ($getNaturalidade->total > 0): ?>
            <?php foreach ($getNaturalidade->data as $naturalidade): ?>
              <option value="<?= $naturalidade->id ?>" <?= (($usuario->usu_idnaturalidade ?? null) == $naturalidade->id) ? 'selected' : '' ?>>
                <?= $naturalidade->nat_nome ?><?= empty($naturalidade->nat_uf) ? '' : ' / ' . strtoupper($naturalidade->nat_uf) ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="usu_idnaturalidade">Naturalidade</label>
      </div>
    </div>
  </div>

  <?php if (is_null($usuario->usu_id)): ?>
    <section>
      <div class="row">
        <div class="col-12 text-phsa">
          <h5>Dados de acesso</h5>
        </div>
      </div>

      <?php include 'source/Web/sistema/usuario/dadosDeAcesso.php'; ?>
    </section>
  <?php endif ?>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="form-floating">
        <textarea
          id="usu_observacao"
          name="usu_observacao"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Observação"
          style="height: 120px;"
        ><?= $usuario->usu_observacao ?? '' ?></textarea>
        <label for="usu_observacao">Observação</label>
      </div>

      <small class="text-muted">
        Utilize este campo para observações internas sobre o usuário.
      </small>
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

      function ajustarDocumentoUsuario(limparCampos = true) {
        const documento = $('#usu_cnpjcpf').val().replace(/\D/g, '');
        const hoje = new Date();
        const dataMaxima = new Date(hoje);
        const dataMinima = new Date(hoje);

        dataMaxima.setDate(dataMaxima.getDate() - 1);
        dataMinima.setFullYear(dataMinima.getFullYear() - 150);

        const maxDate = dataMaxima.toISOString().split('T')[0];
        const minDate = dataMinima.toISOString().split('T')[0];

        $('#usu_cnpjcpf').unmask();

        if (documento.length === 11) {
          $('#usu_cnpjcpf').mask('999.999.999-99');

          $('label[for="usu_cnpjcpf"]').text('CPF');
          $('label[for="usu_razaosocial"]').text('Nome completo');
          $('label[for="usu_nomefantasia"]').text('Nome social');
          $('label[for="usu_dtnasc"]').text('Data de nascimento');

          $('#usu_dtnasc').attr('min', minDate).attr('max', maxDate);

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
          $('#usu_cnpjcpf').mask('99.999.999/9999-99');

          $('label[for="usu_cnpjcpf"]').text('CNPJ');
          $('label[for="usu_razaosocial"]').text('Razão social');
          $('label[for="usu_nomefantasia"]').text('Nome fantasia');
          $('label[for="usu_dtnasc"]').text('Data de fundação');

          $('#usu_dtnasc').removeAttr('min').removeAttr('max');

          habilitarPessoaJuridica();

          if (limparCampos) {
            limparDesabilitarPessoaFisica();
          } else {
            $('.campo-pessoa-fisica-area').hide();
            $('.campo-pessoa-fisica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Física/CPF');
          }

          return;
        }

        $('label[for="usu_cnpjcpf"]').text('Documento');
        $('label[for="usu_razaosocial"]').text('Nome / Razão Social');
        $('label[for="usu_nomefantasia"]').text('Nome Social / Fantasia');
        $('label[for="usu_dtnasc"]').text('Data');

        $('#usu_dtnasc').removeAttr('min').removeAttr('max');

        if (limparCampos) {
          limparDesabilitarPessoaFisica();
          limparDesabilitarPessoaJuridica();
        } else {
          $('.campo-pessoa-fisica-area, .campo-pessoa-juridica-area').hide();
          $('.campo-pessoa-fisica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Física/CPF');
          $('.campo-pessoa-juridica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Jurídica/CNPJ');
        }
      }

      <?php if (is_null($usuario->usu_id)): ?>
        $('#usu_cnpjcpf').focus();
      <?php endif ?>

      ajustarDocumentoUsuario(false);

      $('#usu_cnpjcpf').on('focus keypress', function () {
        $(this).unmask();
      });

      $('#usu_cnpjcpf').on('input blur change', function () {
        ajustarDocumentoUsuario(true);
      });

      $('#formDadosBasicos').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const form = $(this);
        const buttons = form.find('button[type="submit"], input[type="submit"]');

        ajustarDocumentoUsuario(false);

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
          title: 'Salvando usuário...',
          text: 'Aguarde enquanto processamos os dados.',
          timer: null
        });

        $.ajax({
          url: '<?= Helpers::url("usuario/salvarUsuario") ?>',
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

            swError('Não foi possível salvar os dados básicos do usuário.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>