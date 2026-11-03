<?php
  $nomeEmpresaPwa = trim((string) ($empresa->emp_nomefantasia ?? $empresa->emp_razaosocial ?? ''));
  $nomePwa = (string) ($pwaConfig->pwa_nome ?? ($nomeEmpresaPwa !== '' ? $nomeEmpresaPwa : 'Aplicativo da empresa'));
  $nomeCurtoPwa = (string) ($pwaConfig->pwa_nomecurto ?? mb_substr($nomePwa, 0, 50));
  $descricaoPwa = (string) ($pwaConfig->pwa_descricao ?? 'Aplicativo Web Progressivo');
  $statusPwa = (string) ($pwaConfig->pwa_status ?? 'Inativo');
  $corTemaPwa = (string) ($pwaConfig->pwa_cor_tema ?? '#0D6EFD');
  $corFundoPwa = (string) ($pwaConfig->pwa_cor_fundo ?? '#FFFFFF');
?>

<form id="formPwaConfig" class="needs-validation" novalidate autocomplete="off">
  <input type="hidden" name="pwa_idempresa" value="<?= (int) $empresa->emp_id ?>">
  <div class="row">
    <div class="col-12 col-md-4 mb-3">
      <div class="form-floating">
        <select id="pwa_status" name="pwa_status" class="form-select input-phsa-focus input-phsa-hover" required>
          <option value="Ativo" <?= $statusPwa === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
          <option value="Inativo" <?= $statusPwa !== 'Ativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <label for="pwa_status">Status do PWA</label>
      </div>
      <div class="form-text">Quando ativo, o manifesto e os recursos do aplicativo são gerados.</div>
    </div>
    <div class="col-12 col-md-4 mb-3">
      <div class="form-floating">
        <input id="pwa_nome" name="pwa_nome" value="<?= $escPwa($nomePwa) ?>" maxlength="100" class="form-control input-phsa-focus input-phsa-hover" placeholder="Nome do PWA" required>
        <label for="pwa_nome">Nome do PWA</label>
      </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
      <div class="form-floating">
        <input id="pwa_nomecurto" name="pwa_nomecurto" value="<?= $escPwa($nomeCurtoPwa) ?>" maxlength="50" class="form-control input-phsa-focus input-phsa-hover" placeholder="Nome curto" required>
        <label for="pwa_nomecurto">Nome curto</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <input type="color" id="pwa_cor_tema" name="pwa_cor_tema" value="<?= $escPwa($corTemaPwa) ?>" class="form-control form-control-color input-phsa-focus input-phsa-hover w-100" title="Cor do tema">
        <label for="pwa_cor_tema">Cor do tema</label>
      </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <input type="color" id="pwa_cor_fundo" name="pwa_cor_fundo" value="<?= $escPwa($corFundoPwa) ?>" class="form-control form-control-color input-phsa-focus input-phsa-hover w-100" title="Cor de fundo">
        <label for="pwa_cor_fundo">Cor de fundo</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="form-floating">
        <textarea id="pwa_descricao" name="pwa_descricao" maxlength="255" class="form-control input-phsa-focus input-phsa-hover" placeholder="Descrição do PWA" style="height: 120px;"><?= $escPwa($descricaoPwa) ?></textarea>
        <label for="pwa_descricao">Descrição</label>
      </div>
    </div>
  </div>
  <div class="d-flex justify-content-end">
    <button class="bto bto-phsa" type="submit"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar configuração</button>
  </div>
</form>

<?php $this->push('script'); ?>
<script>
  $(function () {
    $('#formPwaConfig').on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      const form = $(this);
      const buttons = form.find('button[type="submit"]');
      if (form.data('enviando')) return;
      if (!this.checkValidity()) {
        form.addClass('was-validated');
        return;
      }
      form.data('enviando', true);
      buttons.prop('disabled', true);
      swAguarde({ title: 'Salvando configuração do PWA...', text: 'Gerando os recursos do aplicativo.', timer: null });
      $.ajax({
        url: '<?= Helpers::url("empresa/salvarPwa") ?>',
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
          swError('Não foi possível salvar a configuração do PWA.');
        }
      });
    });
  });
</script>
<?php $this->end(); ?>
