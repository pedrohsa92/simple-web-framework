<form id="formWebapp" class="needs-validation" novalidate autocomplete="off">
  <input type="hidden" name="emp_id" value="<?= (int) ($empresa->emp_id ?? 0) ?>">
  <?php
    $slugEmpresaSalvo = trim((string) ($empresa->emp_urlamigavel ?? ''));
    $urlBaseEmpresaPublica = rtrim(Helpers::url(), '/');
  ?>

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Página da empresa</h5>
      <p class="text-muted mb-3">Configure a página da empresa e a entrada principal do Webapp.</p>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <input type="text" id="emp_urlamigavel" name="emp_urlamigavel"
          value="<?= $escWebapp($empresa->emp_urlamigavel ?? '') ?>"
          class="form-control input-phsa-focus input-phsa-hover" placeholder="URL amigável"
          maxlength="50" pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
        <label for="emp_urlamigavel">URL amigável</label>
      </div>
      <div id="previewUrlEmpresa" class="form-text"></div>
    </div>

    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <?php $statusWebapp = (string) ($empresa->emp_webapp_status ?? 'Offline'); ?>
        <select id="emp_webapp_status" name="emp_webapp_status" class="form-select input-phsa-focus input-phsa-hover" required>
          <option value="Offline" <?= $statusWebapp === 'Offline' ? 'selected' : '' ?>>Offline</option>
          <option value="Publica" <?= $statusWebapp === 'Publica' ? 'selected' : '' ?>>Pública</option>
          <option value="Privada" <?= $statusWebapp === 'Privada' ? 'selected' : '' ?>>Privada</option>
        </select>
        <label for="emp_webapp_status">Disponibilidade da página</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <label for="emp_webapp_conteudo" class="form-label text-phsa">Conteúdo personalizado</label>
      <textarea id="emp_webapp_conteudo" name="emp_webapp_conteudo" class="form-control input-phsa-focus input-phsa-hover"
        placeholder="Conteúdo da página da empresa"><?= $escWebapp($empresa->emp_webapp_conteudo ?? '') ?></textarea>
      <small class="text-muted">Edite o conteúdo que será usado na página desta empresa.</small>
    </div>
  </div>

  <div class="d-flex justify-content-end">
    <button class="bto bto-phsa" type="submit"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar Webapp</button>
  </div>
</form>

<?php $this->push('script'); ?>
<script>
  $(function () {
    const slugEmpresaSalvo = <?= json_encode($slugEmpresaSalvo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const urlBaseEmpresaPublica = <?= json_encode($urlBaseEmpresaPublica, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function atualizarPreviewUrlEmpresa() {
      const slug = $.trim($('#emp_urlamigavel').val() || '');
      const $preview = $('#previewUrlEmpresa');
      $preview.removeClass('text-phsa text-muted text-secondary').empty();

      if (!slug) {
        $preview.addClass('text-muted').text('Informe uma URL amigável para gerar o endereço público da empresa.');
        return;
      }

      const rota = '/' + slug;
      if (slug === slugEmpresaSalvo) {
        const url = urlBaseEmpresaPublica + rota;
        const $link = $('<a>', {
          href: url,
          target: '_blank',
          rel: 'noopener',
          class: 'text-phsa text-decoration-none',
          title: 'Abrir página da empresa'
        }).text(rota + ' ');
        $link.append($('<i>', { class: 'fa-solid fa-arrow-up-right-from-square fa-xs' }));
        $preview.addClass('text-phsa').append($link);
        return;
      }

      $preview.addClass('text-muted').text(rota + ' — salve a página para ativar este endereço.');
    }

    $('#emp_urlamigavel').on('input', atualizarPreviewUrlEmpresa);
    atualizarPreviewUrlEmpresa();

    if (typeof tinymce !== 'undefined') {
      tinymce.init({
        selector: '#emp_webapp_conteudo',
        height: 350,
        menubar: false,
        plugins: 'lists emoticons code fullscreen advlist charmap',
        toolbar: 'undo redo | styles | bold italic underline strikethrough emoticons charmap | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | fullscreen | removeformat',
        branding: false,
        content_css: false,
        block_formats: 'Parágrafo=p; Título 1=h1; Título 2=h2; Título 3=h3; Título 4=h4; Título 5=h5; Título 6=h6'
      });
    }

    $('#formWebapp').on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();

      const form = $(this);
      const buttons = form.find('button[type="submit"]');
      if (form.data('enviando')) return;
      if (typeof tinymce !== 'undefined') tinymce.triggerSave();
      if (!this.checkValidity()) {
        form.addClass('was-validated');
        return;
      }

      form.data('enviando', true);
      buttons.prop('disabled', true);
      swAguarde({ title: 'Salvando Webapp...', text: 'Aguarde enquanto processamos os dados.', timer: null });

      $.ajax({
        url: '<?= Helpers::url("empresa/salvarEmpresa") ?>',
        type: 'POST',
        dataType: 'json',
        data: form.serialize(),
        success: function (response) {
          swResponse(response).then(function () {
            if (response.status && response.location) {
              window.location.href = response.location;
              return;
            }
            form.data('enviando', false);
            buttons.prop('disabled', false);
          });
        },
        error: function () {
          form.data('enviando', false);
          buttons.prop('disabled', false);
          swError('Não foi possível salvar a página da empresa.');
        }
      });
    });
  });
</script>
<?php $this->end(); ?>
