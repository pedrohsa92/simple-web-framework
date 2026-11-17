





<form id="formExtra" class="needs-validation p-3" novalidate>
  <input name="emp_id" value="<?= $empresa->emp_id ?? '' ?>" hidden>
  <input name="location" value="/cadastro/extra" hidden>

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Dados extras</h5>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 mb-3">
      <div class="form-floating">
        <input
          id="emp_googletag"
          name="emp_googletag"
          value="<?= $empresa->emp_googletag ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="Google Tag"
        >
        <label for="emp_googletag">Google Tag</label>
      </div>

      <small class="text-muted">
        Informe o identificador do Google Tag, quando aplicável.
      </small>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 mb-3">
      <label for="emp_descricao" class="form-label text-phsa">
        Descrição
      </label>

      <textarea
        id="emp_descricao"
        name="emp_descricao"
        class="form-control input-phsa-focus input-phsa-hover"
        placeholder="Descrição da empresa"
        style="height: 240px;"
      ><?= $empresa->emp_descricao ?? '' ?></textarea>

      <small class="text-muted">
        Utilize este campo para uma descrição detalhada da empresa.
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
      if (typeof tinymce !== 'undefined') {
        tinymce.init({
          selector: '#emp_descricao',
          toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist',
          menubar: false,
          branding: false,
          height: 300
        });
      }

      $('#formExtra').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const form = $(this);
        const buttons = form.find('button[type="submit"], input[type="submit"]');

        if (form.data('enviando') === true) {
          return;
        }

        if (typeof tinymce !== 'undefined') {
          tinymce.triggerSave();
        }

        if (!this.checkValidity()) {
          form.addClass('was-validated');
          return;
        }

        form.data('enviando', true);
        buttons.attr('disabled', true);

        swAguarde({
          title: 'Salvando dados extras...',
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

            swError('Não foi possível salvar os dados extras da empresa.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>