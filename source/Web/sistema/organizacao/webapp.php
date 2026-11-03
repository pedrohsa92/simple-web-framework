<form id="formWebappOrganizacao" class="needs-validation p-3" novalidate autocomplete="off">
  <input type="hidden" name="org_id" value="<?= (int) ($organizacao->org_id ?? 0) ?>">

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Webapp da organização</h5>
      <p class="text-muted mb-3">Configure a página pública ou privada da organização.</p>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <input
          type="text"
          id="org_urlamigavel"
          name="org_urlamigavel"
          value="<?= htmlspecialchars((string) ($organizacao->org_urlamigavel ?? ''), ENT_QUOTES, 'UTF-8') ?>"
          class="form-control input-phsa-focus input-phsa-hover"
          placeholder="URL amigável"
          maxlength="50"
          pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
        >
        <label for="org_urlamigavel">URL amigável</label>
      </div>
      <div id="previewUrlOrganizacao" class="form-text" aria-live="polite"></div>
    </div>

    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <?php $statusWebappOrganizacao = (string) ($organizacao->org_webapp_status ?? 'Offline'); ?>
        <select id="org_webapp_status" name="org_webapp_status" class="form-select input-phsa-focus input-phsa-hover" required>
          <option value="Offline" <?= $statusWebappOrganizacao === 'Offline' ? 'selected' : '' ?>>Offline</option>
          <option value="Publica" <?= $statusWebappOrganizacao === 'Publica' ? 'selected' : '' ?>>Pública</option>
          <option value="Privada" <?= $statusWebappOrganizacao === 'Privada' ? 'selected' : '' ?>>Privada</option>
        </select>
        <label for="org_webapp_status">Disponibilidade da página</label>
      </div>
      <small class="text-muted">Defina se a página da organização estará disponível para visitantes.</small>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <label for="org_webapp_conteudo" class="form-label text-phsa">Conteúdo personalizado</label>
      <textarea
        id="org_webapp_conteudo"
        name="org_webapp_conteudo"
        class="form-control input-phsa-focus input-phsa-hover"
        placeholder="Conteúdo da página da organização"
      ><?= htmlspecialchars((string) ($organizacao->org_webapp_conteudo ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
      <small class="text-muted">Esse conteúdo será exibido na página institucional da organização.</small>
    </div>
  </div>

  <div class="row">
    <div class="col-12 text-end">
      <button class="bto bto-phsa" type="submit">
        <i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar Webapp
      </button>
    </div>
  </div>
</form>

<?php $this->push('script'); ?>
<script>
  $(function () {
    const slugOrganizacaoSalvo = <?= json_encode(strtolower(trim((string) ($organizacao->org_urlamigavel ?? ''))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const statusWebappSalvo = <?= json_encode((string) ($organizacao->org_webapp_status ?? 'Offline'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const urlBaseWebapp = <?= json_encode(rtrim(Helpers::url(), '/'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function atualizarPreviewUrlOrganizacao() {
      const slug = $.trim($('#org_urlamigavel').val() || '').toLowerCase();
      const $preview = $('#previewUrlOrganizacao').removeClass('text-phsa text-muted');

      if (!slug) {
        $preview.addClass('text-muted').text('Informe uma URL amigável para visualizar o endereço da página.');
        return;
      }

      const rota = '/' + slug;
      if (slug === slugOrganizacaoSalvo && statusWebappSalvo === 'Publica' && $('#org_webapp_status').val() === statusWebappSalvo) {
        const url = urlBaseWebapp + '/' + encodeURIComponent(slug);
        const $link = $('<a>', { href: url, target: '_blank', rel: 'noopener noreferrer', class: 'text-phsa text-decoration-none', title: 'Abrir página pública da organização' });
        $link.text(rota + ' ');
        $link.append($('<i>', { class: 'fa-solid fa-arrow-up-right-from-square fa-xs', 'aria-hidden': 'true' }));
        $preview.addClass('text-phsa').empty().append($link);
        return;
      }

      if (slug === slugOrganizacaoSalvo) {
        $preview.addClass('text-muted').text(rota + (statusWebappSalvo === 'Publica' ? ' — salve para atualizar o endereço.' : ' — a página precisa estar pública para ser acessada.'));
        return;
      }

      $preview.addClass('text-muted').text(rota + ' — salve para ativar este endereço.');
    }

    $('#org_urlamigavel').on('input', atualizarPreviewUrlOrganizacao);
    atualizarPreviewUrlOrganizacao();

    if (typeof tinymce !== 'undefined') {
      tinymce.init({
        selector: '#org_webapp_conteudo',
        height: 350,
        menubar: false,
        plugins: 'lists emoticons code fullscreen advlist charmap',
        toolbar: 'undo redo | styles | bold italic underline strikethrough emoticons charmap | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | fullscreen | removeformat',
        branding: false,
        content_css: false,
        language: 'pt_BR',
        language_url: 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/langs/pt_BR.js',
        block_formats: 'Parágrafo=p; Título 1=h1; Título 2=h2; Título 3=h3; Título 4=h4; Título 5=h5; Título 6=h6'
      });
    }

    $('#formWebappOrganizacao').on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();

      const form = $(this);
      const buttons = form.find('button[type="submit"]');
      if (form.data('enviando') === true) return;
      if (typeof tinymce !== 'undefined') tinymce.triggerSave();
      if (!this.checkValidity()) {
        form.addClass('was-validated');
        return;
      }

      form.data('enviando', true);
      buttons.prop('disabled', true);
      swAguarde({ title: 'Salvando Webapp...', text: 'Aguarde enquanto processamos os dados.', timer: null });

      $.ajax({
        url: '<?= Helpers::url("organizacao/salvarOrganizacao") ?>',
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
          swError('Não foi possível salvar o Webapp da organização.');
        }
      });
    });
  });
</script>
<?php $this->end(); ?>
