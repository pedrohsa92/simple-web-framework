<?php
  $conteudoAvisoPrivacidade = htmlspecialchars((string) ($projeto->prj_avisoprivacidade ?? ''), ENT_QUOTES, 'UTF-8');
  $versaoAvisoPrivacidade = (string) ($projeto->prj_avisoprivacidadeversao ?? 'Não publicada');
?>

<form id="formAvisoPrivacidadeProjeto" class="needs-validation p-3" novalidate>
  <input name="prj_id" value="<?= (int) ($projeto->prj_id ?? 1) ?>" hidden>
  <input name="prj_documento" value="avisoprivacidade" hidden>

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Aviso de privacidade</h5>
      <div class="small text-muted">
        Este conteúdo explica como os dados pessoais são tratados. Cada salvamento gera uma nova versão.
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-12 mb-3">
      <label for="prj_avisoprivacidade_editor" class="form-label text-phsa">Conteúdo do aviso</label>
      <textarea
        id="prj_avisoprivacidade_editor"
        name="prj_conteudo"
        class="form-control input-phsa-focus input-phsa-hover"
        placeholder="Informe o aviso de privacidade"
        style="height: 420px;"
        required
      ><?= $conteudoAvisoPrivacidade ?></textarea>
      <div class="invalid-feedback">Informe o conteúdo do aviso de privacidade.</div>
    </div>
  </div>

  <div class="small text-muted mb-3">
    Versão vigente: <span class="text-phsa fw-semibold"><?= htmlspecialchars($versaoAvisoPrivacidade, ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <div class="row">
    <div class="col-12 text-end">
      <button class="bto bto-phsa" type="submit">
        <i class="fas fa-save"></i>&nbsp;Salvar aviso de privacidade
      </button>
    </div>
  </div>
</form>

<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(function () {
      if (typeof tinymce !== 'undefined') {
        tinymce.init({
          selector: '#prj_avisoprivacidade_editor',
          toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link',
          menubar: false,
          branding: false,
          height: 420
        });
      }

      $('#formAvisoPrivacidadeProjeto').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const form = $(this);
        const buttons = form.find('button[type="submit"]');

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
        buttons.prop('disabled', true);
        swAguarde({
          title: 'Salvando aviso de privacidade...',
          text: 'Aguarde enquanto geramos a nova versão.',
          timer: null
        });

        $.ajax({
          url: '<?= Helpers::url('projeto/salvarDocumento') ?>',
          type: 'POST',
          dataType: 'json',
          data: form.serialize(),
          success: function (response) {
            swResponse(response).then(function () {
              if (response.status) {
                window.location.reload();
                return;
              }

              form.data('enviando', false);
              buttons.prop('disabled', false);
            });
          },
          error: function () {
            form.data('enviando', false);
            buttons.prop('disabled', false);
            swError('Não foi possível salvar o aviso de privacidade.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>
