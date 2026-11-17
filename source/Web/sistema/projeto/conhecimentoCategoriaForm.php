<?php
  $this->layout('sistema::sistema');
  $categoria = $categoria ?? (object) [];
  $id = (int) ($categoria->cct_id ?? 0);
  $editando = $id > 0;
  $podeAtualizar = (bool) ($podeAtualizar ?? false);
  $podeCriar = (bool) ($podeCriar ?? false);
  $podeSalvar = $editando ? $podeAtualizar : $podeCriar;
  $urlLista = $urlLista ?? Helpers::url('projeto/conhecimento/categoria');
  $esc = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $logsCategoria = json_decode((string) ($categoria->cct_log ?? '[]'), true);
  $logsCategoria = is_array($logsCategoria) ? $logsCategoria : [];
?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <div class="text-uppercase small text-phsa fw-semibold">Projeto · Conhecimento</div>
        <h4 class="m-0"><?= $editando ? 'Editando categoria: ' . $esc($categoria->cct_nome ?? 'Categoria') : 'Nova categoria' ?></h4>
        <div class="text-muted small mt-1"><?= $editando ? 'Atualize os dados desta categoria.' : 'Preencha os dados para cadastrar uma categoria de conhecimento.' ?></div>
      </div>
      <a href="<?= $esc($urlLista) ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Cancelar</a>
    </div>

    <header class="phsaScrollHeader">
      <nav class="phsaScrollNav" data-menu="tabConhecimentoCategoria">
        <ul class="phsaScrollList">
          <li class="phsaScrollItem active" data-target="cadastroCategoriaConhecimento"><i class="fa-solid fa-id-card"></i>&nbsp;Cadastro</li>
          <?php if ($editando): ?><li class="phsaScrollItem" data-target="historicoCategoriaConhecimento"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li><?php endif; ?>
        </ul>
      </nav>
    </header>

    <div class="phsaTabContent phsaTabSubContent">
      <div class="phsaTabItem active p-3" id="cadastroCategoriaConhecimento">
        <form id="formCategoriaConhecimento" class="needs-validation" novalidate autocomplete="off">
          <input type="hidden" name="cct_id" value="<?= $id ?>">
          <div class="row g-3">
            <div class="col-12 col-lg-8">
              <div class="form-floating">
                <input id="cct_nome" name="cct_nome" type="text" maxlength="150" class="form-control" value="<?= $esc($categoria->cct_nome ?? '') ?>" required <?= !$podeSalvar ? 'readonly' : '' ?>>
                <label for="cct_nome">Nome da categoria *</label>
                <div class="invalid-feedback">Informe o nome da categoria.</div>
              </div>
            </div>
            <div class="col-12 col-lg-4">
              <div class="form-floating">
                <select id="cct_status" name="cct_status" class="form-select" required <?= !$podeSalvar ? 'disabled' : '' ?>>
                  <option value="Ativo" <?= ($categoria->cct_status ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                  <option value="Inativo" <?= ($categoria->cct_status ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
                <label for="cct_status">Status *</label>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="form-floating">
                <input id="cct_ordem" name="cct_ordem" type="number" min="0" step="1" class="form-control" value="<?= (int) ($categoria->cct_ordem ?? 0) ?>" <?= !$podeSalvar ? 'readonly' : '' ?>>
                <label for="cct_ordem">Ordem de exibição</label>
              </div>
            </div>
          </div>
          <div class="alert alert-light border small mt-3 mb-0"><i class="fa-solid fa-circle-info"></i>&nbsp;Cada conhecimento é vinculado diretamente a uma categoria; não há subcategorias.</div>
          <?php if ($podeSalvar): ?>
            <div class="d-flex justify-content-end mt-4">
              <button type="submit" id="btnSalvarCategoriaConhecimento" class="bto bto-phsa"><i class="fa-solid fa-floppy-disk"></i>&nbsp;<?= $editando ? 'Salvar categoria' : 'Cadastrar categoria' ?></button>
            </div>
          <?php endif; ?>
        </form>
      </div>

      <?php if ($editando): ?>
        <div class="phsaTabItem p-3" id="historicoCategoriaConhecimento">
          <?php
            $historicoRegistro = $categoria;
            $historicoPrefixo = 'cct_';
            $historicoId = $id;
            $historicoStatus = (string) ($categoria->cct_status ?? '');
            $historicoStatusClass = (string) ($categoria->cct_statusclass ?? 'secondary');
            $historicoLogs = $logsCategoria;
            include __DIR__ . '/../historicoPadrao.php';
          ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php $this->push('script'); ?>
<script>
  $(function () {
    const form = document.getElementById('formCategoriaConhecimento');
    if (!form) return;
    const $form = $(form);
    const $botao = $('#btnSalvarCategoriaConhecimento');

    $form.on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      if (!form.checkValidity()) {
        $form.addClass('was-validated');
        form.querySelector(':invalid')?.focus();
        return;
      }

      $botao.prop('disabled', true);
      swAguarde({ title: 'Salvando categoria...', text: 'Aguarde enquanto processamos os dados.', timer: null });
      $.ajax({
        url: '<?= Helpers::url('projeto/salvarConhecimentoCategoria') ?>',
        type: 'POST',
        dataType: 'json',
        data: $form.serialize()
      }).done(response => {
        swResponse(response, { timer: 2500 });
        if (response.status) {
          const urlDestino = response.location || <?= json_encode($urlLista, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
          setTimeout(() => window.location.replace(urlDestino), 1500);
          return;
        }
        $botao.prop('disabled', false);
      }).fail(xhr => {
        console.error(xhr.responseText);
        $botao.prop('disabled', false);
        swError('Não foi possível salvar a categoria.');
      });
    });
  });
</script>
<?php $this->end(); ?>
