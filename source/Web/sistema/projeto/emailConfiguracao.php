<?php
  $this->layout('sistema::sistema');
  $esc = static fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
  $podeAtualizar = (bool) ($podeAtualizar ?? false);
?>

<div class="card border shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <div class="text-uppercase small text-phsa fw-semibold">Projeto · E-mails</div>
        <h4 class="m-0">Identidade dos e-mails</h4>
        <div class="text-muted small mt-1">Defina o cabeçalho e o rodapé aplicados automaticamente às mensagens do projeto.</div>
      </div>
      <a href="<?= Helpers::url('projeto/email') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Hub de e-mails</a>
    </div>

    <div class="alert alert-light border d-flex gap-2 align-items-start mb-4">
      <i class="fa-solid fa-circle-info text-phsa mt-1"></i>
      <div class="small text-muted">Os campos aceitam HTML e palavras-chave do projeto. O conteúdo específico de cada template será inserido entre o cabeçalho e o rodapé.</div>
    </div>

    <form id="formConfiguracaoEmailProjeto" method="post" novalidate>
      <input type="hidden" name="prj_id" value="<?= (int) ($projeto->prj_id ?? 1) ?>">

      <div class="row">
        <div class="col-12 mb-3">
          <div class="card border">
            <div class="card-header bg-transparent">
              <div class="fw-semibold text-phsa"><i class="fa-solid fa-arrow-up"></i>&nbsp; Cabeçalho</div>
              <div class="small text-muted mt-1">Área superior da mensagem, normalmente usada para logo e identificação.</div>
            </div>
            <div class="card-body">
              <textarea name="prj_email_header" id="prj_email_header" class="form-control" rows="16" <?= !$podeAtualizar ? 'readonly' : '' ?>><?= $esc($projeto->prj_email_header ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="col-12 mb-3">
          <div class="card border">
            <div class="card-header bg-transparent">
              <div class="fw-semibold text-phsa"><i class="fa-solid fa-arrow-down"></i>&nbsp; Rodapé</div>
              <div class="small text-muted mt-1">Área inferior da mensagem, usada para créditos, contatos e avisos.</div>
            </div>
            <div class="card-body">
              <textarea name="prj_email_footer" id="prj_email_footer" class="form-control" rows="16" <?= !$podeAtualizar ? 'readonly' : '' ?>><?= $esc($projeto->prj_email_footer ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
        <a href="<?= Helpers::url('projeto/email') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-times"></i>&nbsp;Cancelar</a>
        <button type="button" id="btnVisualizarIdentidadeEmail" class="bto bto-phsa-light"><i class="fa-solid fa-eye"></i>&nbsp;Visualizar estrutura</button>
        <?php if ($podeAtualizar): ?>
          <button type="submit" class="bto bto-phsa"><i class="fa-solid fa-save"></i>&nbsp;Salvar configuração</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php $this->push('script'); ?><script>
  $(function () {
    const $form = $('#formConfiguracaoEmailProjeto');
    const podeAtualizar = <?= $podeAtualizar ? 'true' : 'false' ?>;

    if (typeof tinymce !== 'undefined') {
      tinymce.init({
        selector: '#prj_email_header, #prj_email_footer',
        plugins: 'link lists table code',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link table | removeformat code',
        menubar: false,
        branding: false,
        promotion: false,
        height: 360,
        readonly: !podeAtualizar,
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; color: #263957; padding: 12px; }'
      });
    }

    $('#btnVisualizarIdentidadeEmail').on('click', function () {
      if (typeof tinymce !== 'undefined') tinymce.triggerSave();

      const substituir = function (html) {
        return String(html || '')
          .replaceAll('{projeto.logo}', '<?= $esc(Helpers::url('storage/global/logo.png')) ?>')
          .replaceAll('{projeto.titulo}', '<?= $esc($projeto->prj_titulo ?? 'LevHub') ?>')
          .replaceAll('{projeto.url}', '<?= $esc(rtrim((string) ($projeto->prj_url_central ?? Helpers::url('')), '/')) ?>');
      };
      const header = substituir($('#prj_email_header').val());
      const footer = substituir($('#prj_email_footer').val());
      const corpo = '<div style="font-family:Arial,Helvetica,sans-serif;color:#24344d;"><p><strong>Ol&aacute;, Pedro!</strong></p><p>Este &eacute; um exemplo da &aacute;rea em que o conte&uacute;do de cada template ser&aacute; inserido.</p></div>';

      SimpleModal.mostrar({
        titulo: 'Visualizar identidade dos e-mails',
        tamanho: 'modal-xl',
        corpo: '<div style="background:#f4f7fb;padding:24px;">' + header + '<div style="max-width:640px;margin:0 auto;background:#ffffff;padding:32px;">' + corpo + '</div>' + footer + '</div>',
        rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-times"></i>&nbsp;Fechar</button>'
      });
    });

    $form.on('submit', function (event) {
      event.preventDefault();
      if (!podeAtualizar) return;
      if (typeof tinymce !== 'undefined') tinymce.triggerSave();

      const $button = $form.find('button[type="submit"]');
      $button.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando...');

      $.ajax({
        url: '<?= Helpers::url('projeto/salvarEmailConfiguracao') ?>',
        type: 'POST',
        dataType: 'json',
        data: $form.serialize()
      }).done(function (response) {
        swResponse(response).then(function () {
          $button.prop('disabled', false).html('<i class="fa-solid fa-save"></i>&nbsp;Salvar configuração');
        });
      }).fail(function () {
        $button.prop('disabled', false).html('<i class="fa-solid fa-save"></i>&nbsp;Salvar configuração');
        swError('Não foi possível salvar a identidade dos e-mails.');
      });
    });
  });
</script><?php $this->end(); ?>
