





<form id="formConfiguracaoParametro" class="needs-validation" novalidate>
  <input name="emp_id" value="<?= $empresa->emp_id ?>" hidden>
  <input name="location" value="/configuracao/parametro" hidden>
  <input id="emp_parametro" name="emp_parametro" value="" hidden>

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Parâmetros comportamentais</h5>
    </div>

    <div class="col-12 mb-3">
      <div class="alert alert-light border mb-0">
        Configure regras comportamentais próprias desta empresa.
      </div>
    </div>
  </div>

  <?php foreach ($parametrosData as $secao => $parametro): ?>
    <div class="row">
      <div class="col-12 mb-3">
        <div class="card box-phsa-hover-shadow-light">
          <div class="card-body">
            <h6 class="text-phsa mb-1">
              <i class="<?= $parametro->icone ?>"></i>&nbsp;<?= $parametro->titulo ?>
            </h6>

            <p class="text-muted small mb-3">
              <?= $parametro->descricao ?>
            </p>

            <div class="row">
              <?php foreach ($parametro->booleanos as $campo => $label): ?>
                <div class="col-sm-12 mb-3">
                  <div class="form-check form-switch">
                    <input
                      id="<?= $secao ?>_<?= $campo ?>"
                      class="form-check-input parametro-boolean"
                      type="checkbox"
                      data-secao="<?= $secao ?>"
                      data-campo="<?= $campo ?>"
                      <?= !is_null($empresa->emp_parametro) && $empresa->emp_parametro->{$secao}->{$campo} === true ? 'checked' : '' ?>
                    >

                    <label class="form-check-label" for="<?= $secao ?>_<?= $campo ?>">
                      <?= $label ?>
                    </label>
                  </div>
                </div>
              <?php endforeach ?>
            </div>

            <hr>

            <div class="row">
              <div class="col-12 mb-2">
                <h6 class="text-phsa mb-0">
                  Campos obrigatórios no cadastro
                </h6>

                <small class="text-muted">
                  Defina quais campos serão obrigatórios ao criar um novo <?= strtolower($parametro->titulo) ?>.
                </small>
              </div>

              <?php foreach ($parametro->camposObrigatoriosCadastro as $campo => $label): ?>
                <div class="col-sm-4 mb-2">
                  <div class="form-check">
                    <input
                      id="<?= $secao ?>_cadastro_<?= $campo ?>"
                      class="form-check-input parametro-array"
                      type="checkbox"
                      value="<?= $campo ?>"
                      data-secao="<?= $secao ?>"
                      data-campo="camposObrigatoriosCadastro"
                      <?= !is_null($empresa->emp_parametro) && in_array($campo, $empresa->emp_parametro->{$secao}->camposObrigatoriosCadastro, true) ? 'checked' : '' ?>
                    >

                    <label class="form-check-label" for="<?= $secao ?>_cadastro_<?= $campo ?>">
                      <?= $label ?>
                    </label>
                  </div>
                </div>
              <?php endforeach ?>
            </div>

            <hr>

            <div class="row">
              <div class="col-12 mb-2">
                <h6 class="text-phsa mb-0">
                  Campos obrigatórios na edição
                </h6>

                <small class="text-muted">
                  Defina quais campos serão obrigatórios ao editar um <?= strtolower($parametro->titulo) ?> existente.
                </small>
              </div>

              <?php foreach ($parametro->camposObrigatoriosEdicao as $campo => $label): ?>
                <div class="col-sm-4 mb-2">
                  <div class="form-check">
                    <input
                      id="<?= $secao ?>_edicao_<?= $campo ?>"
                      class="form-check-input parametro-array"
                      type="checkbox"
                      value="<?= $campo ?>"
                      data-secao="<?= $secao ?>"
                      data-campo="camposObrigatoriosEdicao"
                      <?= !is_null($empresa->emp_parametro) && in_array($campo, $empresa->emp_parametro->{$secao}->camposObrigatoriosEdicao, true) ? 'checked' : '' ?>
                    >

                    <label class="form-check-label" for="<?= $secao ?>_edicao_<?= $campo ?>">
                      <?= $label ?>
                    </label>
                  </div>
                </div>
              <?php endforeach ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach ?>

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
      function montarParametroEmpresa() {
        let parametro = {};

        $('.parametro-boolean').each(function () {
          const secao = $(this).data('secao');
          const campo = $(this).data('campo');

          if (!parametro[secao]) {
            parametro[secao] = {};
          }

          parametro[secao][campo] = $(this).is(':checked');
        });

        $('.parametro-array').each(function () {
          const secao = $(this).data('secao');
          const campo = $(this).data('campo');

          if (!parametro[secao]) {
            parametro[secao] = {};
          }

          if (!parametro[secao][campo]) {
            parametro[secao][campo] = [];
          }

          if ($(this).is(':checked')) {
            parametro[secao][campo].push($(this).val());
          }
        });

        $('#emp_parametro').val(JSON.stringify(parametro));
      }

      montarParametroEmpresa();

      $('.parametro-boolean, .parametro-array').on('change', function () {
        montarParametroEmpresa();
      });

      $('#formConfiguracaoParametro').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const form = $(this);
        const buttons = form.find('button[type="submit"], input[type="submit"]');

        montarParametroEmpresa();

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
          title: 'Salvando parâmetros...',
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

            swError('Não foi possível salvar os parâmetros da empresa.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>