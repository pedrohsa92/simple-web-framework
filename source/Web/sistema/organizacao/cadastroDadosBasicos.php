<form id="formDadosBasicosOrganizacao" class="needs-validation" novalidate>
  <input name="org_id" value="<?= $organizacao->org_id ?? '' ?>" hidden>
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
          id="org_cnpjcpf"
          name="org_cnpjcpf"
          value="<?= $organizacao->org_cnpjcpf ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Documento"
          required
        >
        <label for="org_cnpjcpf">Documento</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>

    <div class="col-sm-8 mb-3">
      <div class="form-floating">
        <input
          id="org_razaosocial"
          name="org_razaosocial"
          value="<?= $organizacao->org_razaosocial ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Nome / Razão Social"
          required
        >
        <label for="org_razaosocial">Nome / Razão Social</label>
        <div class="invalid-feedback">Campo obrigatório</div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select id="org_status" name="org_status" class="form-select input-phsa-focus input-phsa-hover" required>
          <option value="Ativo" <?= (($organizacao->org_status ?? 'Ativo') == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
          <option value="Inativo" <?= (($organizacao->org_status ?? '') == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
          <option value="Bloqueado" <?= (($organizacao->org_status ?? '') == 'Bloqueado') ? 'selected' : '' ?>>Bloqueado</option>
          <option value="Manutencao" <?= (($organizacao->org_status ?? '') == 'Manutencao') ? 'selected' : '' ?>>Manutenção</option>
        </select>
        <label for="org_status">Status</label>
      </div>
    </div>

    <div class="col-sm-5 mb-3">
      <div class="form-floating">
        <input
          id="org_nomefantasia"
          name="org_nomefantasia"
          value="<?= $organizacao->org_nomefantasia ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Nome Social / Fantasia"
        >
        <label for="org_nomefantasia">Nome Social / Fantasia</label>
      </div>
    </div>

    <div class="col-sm-3 mb-3">
      <div class="form-floating">
        <input
          id="org_dtnasc"
          name="org_dtnasc"
          value="<?= $organizacao->org_dtnasc ?? '' ?>"
          type="date"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Data"
        >
        <label for="org_dtnasc">Data</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 mb-3">
      <div class="form-floating">
        <textarea
          id="org_observacao"
          name="org_observacao"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Observação"
          style="height: 120px;"
        ><?= $organizacao->org_observacao ?? '' ?></textarea>
        <label for="org_observacao">Observação</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-juridica-area-organizacao">
    <div class="col-12 text-phsa">
      <h5>Dados de pessoa jurídica</h5>
    </div>

    <div class="col-md-4 mb-3">
      <div class="form-floating">
        <input id="org_im" name="org_im" value="<?= $organizacao->org_im ?? '' ?>" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica-organizacao" placeholder="Inscrição Municipal">
        <label for="org_im">Inscrição Municipal</label>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="form-floating">
        <input id="org_ie" name="org_ie" value="<?= $organizacao->org_ie ?? '' ?>" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica-organizacao" placeholder="Inscrição Estadual">
        <label for="org_ie">Inscrição Estadual</label>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="form-floating">
        <select id="org_idnaturezajuridica" name="org_idnaturezajuridica" class="form-select input-phsa-focus input-phsa-hover">
          <option value="" selected hidden>Selecione a natureza jurídica</option>
          <?php if ($getNaturezaJuridica->total > 0): ?>
            <?php foreach ($getNaturezaJuridica->data as $naturezaJuridica): ?>
              <option value="<?= $naturezaJuridica->id ?>" <?= (($organizacao->org_idnaturezajuridica ?? null) == $naturezaJuridica->id) ? 'selected' : '' ?>>
                <?= $naturezaJuridica->ntj_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="org_idnaturezajuridica">Natureza jurídica</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-fisica-area-organizacao">
    <div class="col-12 text-phsa">
      <h5>Dados de pessoa física</h5>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input id="org_rg" name="org_rg" value="<?= $organizacao->org_rg ?? '' ?>" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica-organizacao" placeholder="RG">
        <label for="org_rg">RG</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input id="org_nomemae" name="org_nomemae" value="<?= $organizacao->org_nomemae ?? '' ?>" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica-organizacao" placeholder="Nome da mãe">
        <label for="org_nomemae">Nome da mãe</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input id="org_nomepai" name="org_nomepai" value="<?= $organizacao->org_nomepai ?? '' ?>" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica-organizacao" placeholder="Nome do pai">
        <label for="org_nomepai">Nome do pai</label>
      </div>
    </div>
  </div>

  <div class="row campo-pessoa-fisica-area-organizacao">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select id="org_idsexo" name="org_idsexo" class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica-organizacao">
          <option value="" selected hidden>Selecione o sexo</option>
          <?php if ($getSexo->total > 0): ?>
            <?php foreach ($getSexo->data as $sexo): ?>
              <option value="<?= $sexo->id ?>" <?= (($organizacao->org_idsexo ?? null) == $sexo->id) ? 'selected' : '' ?>>
                <?= $sexo->sex_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="org_idsexo">Sexo</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select id="org_idestadocivil" name="org_idestadocivil" class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica-organizacao">
          <option value="" selected hidden>Selecione o estado civil</option>
          <?php if ($getEstadoCivil->total > 0): ?>
            <?php foreach ($getEstadoCivil->data as $estadoCivil): ?>
              <option value="<?= $estadoCivil->id ?>" <?= (($organizacao->org_idestadocivil ?? null) == $estadoCivil->id) ? 'selected' : '' ?>>
                <?= $estadoCivil->ecv_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="org_idestadocivil">Estado civil</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select id="org_idnaturalidade" name="org_idnaturalidade" class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica-organizacao">
          <option value="" selected hidden>Selecione a naturalidade</option>
          <?php if ($getNaturalidade->total > 0): ?>
            <?php foreach ($getNaturalidade->data as $naturalidade): ?>
              <option value="<?= $naturalidade->id ?>" <?= (($organizacao->org_idnaturalidade ?? null) == $naturalidade->id) ? 'selected' : '' ?>>
                <?= $naturalidade->nat_nome ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <label for="org_idnaturalidade">Naturalidade</label>
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
      function limparDesabilitarPessoaFisicaOrganizacao() {
          $('.campo-pessoa-fisica-organizacao').each(function () {
          $(this).val('').attr('disabled', true).attr('title', 'Disponível apenas para pessoa física/CPF');
        });

        $('.campo-pessoa-fisica-area-organizacao').hide();
      }

      function limparDesabilitarPessoaJuridicaOrganizacao() {
        $('.campo-pessoa-juridica-organizacao').each(function () {
          $(this).val('').attr('disabled', true).attr('title', 'Disponível apenas para pessoa jurídica/CNPJ');
        });

        $('.campo-pessoa-juridica-area-organizacao').hide();
      }

      function habilitarPessoaFisicaOrganizacao() {
        $('.campo-pessoa-fisica-area-organizacao').show();
        $('.campo-pessoa-fisica-organizacao').removeAttr('disabled').removeAttr('title');
      }

      function habilitarPessoaJuridicaOrganizacao() {
        $('.campo-pessoa-juridica-area-organizacao').show();
        $('.campo-pessoa-juridica-organizacao').removeAttr('disabled').removeAttr('title');
      }

      function ajustarDocumentoOrganizacao(limparCampos = true) {
        const documento = $('#org_cnpjcpf').val().replace(/\D/g, '');

        $('#org_cnpjcpf').unmask();

        if (documento.length === 11) {
          $('#org_cnpjcpf').mask('999.999.999-99');
          $('label[for="org_cnpjcpf"]').text('CPF');
          $('label[for="org_razaosocial"]').text('Nome completo');
          $('label[for="org_nomefantasia"]').text('Nome social');
          $('label[for="org_dtnasc"]').text('Data de nascimento');
          habilitarPessoaFisicaOrganizacao();

          if (limparCampos) {
            limparDesabilitarPessoaJuridicaOrganizacao();
          } else {
            $('.campo-pessoa-juridica-area-organizacao').hide();
            $('.campo-pessoa-juridica-organizacao').attr('disabled', true).attr('title', 'Disponível apenas para pessoa jurídica/CNPJ');
          }

          return;
        }

        if (documento.length === 14) {
          $('#org_cnpjcpf').mask('99.999.999/9999-99');
          $('label[for="org_cnpjcpf"]').text('CNPJ');
          $('label[for="org_razaosocial"]').text('Razão social');
          $('label[for="org_nomefantasia"]').text('Nome fantasia');
          $('label[for="org_dtnasc"]').text('Data de fundação');
          habilitarPessoaJuridicaOrganizacao();

          if (limparCampos) {
            limparDesabilitarPessoaFisicaOrganizacao();
          } else {
            $('.campo-pessoa-fisica-area-organizacao').hide();
            $('.campo-pessoa-fisica-organizacao').attr('disabled', true).attr('title', 'Disponível apenas para pessoa física/CPF');
          }

          return;
        }

        $('label[for="org_cnpjcpf"]').text('Documento');
        $('label[for="org_razaosocial"]').text('Nome / Razão Social');
        $('label[for="org_nomefantasia"]').text('Nome Social / Fantasia');
        $('label[for="org_dtnasc"]').text('Data');

        if (limparCampos) {
          limparDesabilitarPessoaFisicaOrganizacao();
          limparDesabilitarPessoaJuridicaOrganizacao();
        } else {
          $('.campo-pessoa-fisica-area-organizacao, .campo-pessoa-juridica-area-organizacao').hide();
          $('.campo-pessoa-fisica-organizacao').attr('disabled', true).attr('title', 'Disponível apenas para pessoa física/CPF');
          $('.campo-pessoa-juridica-organizacao').attr('disabled', true).attr('title', 'Disponível apenas para pessoa jurídica/CNPJ');
        }
      }

      <?php if (is_null($organizacao) || is_null($organizacao->org_id)): ?>
        $('#org_cnpjcpf').focus();
      <?php endif ?>

      ajustarDocumentoOrganizacao(false);

      $('#org_cnpjcpf').on('focus keypress', function () {
        $(this).unmask();
      });

      $('#org_cnpjcpf').on('input blur change', function () {
        ajustarDocumentoOrganizacao(true);
      });

      $('#formDadosBasicosOrganizacao').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const form = $(this);
        const buttons = form.find('button[type="submit"], input[type="submit"]');

        ajustarDocumentoOrganizacao(false);

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
          title: 'Salvando organização...',
          text: 'Aguarde enquanto processamos os dados.',
          timer: null
        });

        $.ajax({
          url: '<?= Helpers::url("organizacao/salvarOrganizacao") ?>',
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
            swError('Não foi possível salvar os dados básicos da organização.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>
