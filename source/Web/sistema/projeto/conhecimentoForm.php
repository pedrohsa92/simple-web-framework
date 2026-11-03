<?php
  $this->layout('sistema::sistema');
  $conhecimento = $conhecimento ?? (object) [];
  $categorias = $categorias ?? [];
  $midias = $midias ?? [];
  $podeAtualizar = (bool) ($podeAtualizar ?? false);
  $id = (int) ($conhecimento->con_id ?? 0);
  $esc = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $tags = json_decode((string) ($conhecimento->con_tags ?? '[]'), true);
  $tags = is_array($tags) ? array_values($tags) : [];
  $tags = array_slice($tags, 0, 3);
  $logsConhecimento = json_decode((string) ($conhecimento->con_log ?? '[]'), true);
  $logsConhecimento = is_array($logsConhecimento) ? $logsConhecimento : [];
  $formatarDataLog = static function ($data): string {
    $data = trim((string) ($data ?? ''));
    if ($data === '') return '-';
    $timestamp = strtotime(str_replace('/', '-', $data));
    return $timestamp ? date('d/m/Y H:i:s', $timestamp) : $data;
  };
?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <div class="text-uppercase small text-phsa fw-semibold">Projeto · Conhecimento</div>
        <h4 class="m-0"><?= $id > 0 ? 'Editando conhecimento: ' . $esc($conhecimento->con_titulo ?? 'Conhecimento') : 'Novo conhecimento' ?></h4>
        <div class="text-muted small mt-1">Organize orientações, tutoriais e conteúdos de apoio do ICLIV.</div>
      </div>
      <a href="<?= Helpers::url('projeto/conhecimento/list') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</a>
    </div>

    <header class="phsaScrollHeader">
      <nav class="phsaScrollNav" data-menu="tabConhecimento">
        <ul class="phsaScrollList">
          <li class="phsaScrollItem active" data-target="cadastroConhecimento"><i class="fa-solid fa-id-card"></i>&nbsp;Cadastro</li>
          <?php if ($id > 0): ?>
            <li class="phsaScrollItem" data-target="descricaoConhecimento"><i class="fa-solid fa-file-lines"></i>&nbsp;Descrição personalizada</li>
            <li class="phsaScrollItem" data-target="midiaConhecimento"><i class="fa-solid fa-photo-film"></i>&nbsp;Mídia</li>
            <li class="phsaScrollItem" data-target="historicoConhecimento"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li>
          <?php endif; ?>
        </ul>
      </nav>
    </header>

    <div class="phsaTabContent phsaTabSubContent">
      <div class="phsaTabItem active p-3" id="cadastroConhecimento">
        <form id="formCadastroConhecimento" class="needs-validation" novalidate autocomplete="off">
          <input type="hidden" name="con_id" value="<?= $id ?>">
          <div class="row g-3">
            <div class="col-12 col-lg-8">
              <div class="form-floating">
                <input id="con_titulo" name="con_titulo" class="form-control" maxlength="255" value="<?= $esc($conhecimento->con_titulo ?? '') ?>" required <?= !$podeAtualizar ? 'readonly' : '' ?>>
                <label for="con_titulo">Título *</label>
                <div class="invalid-feedback">Informe o título do conhecimento.</div>
              </div>
            </div>
            <div class="col-12 col-lg-4">
              <div class="form-floating">
                <select id="con_status" name="con_status" class="form-select" required <?= !$podeAtualizar ? 'disabled' : '' ?>>
                  <option value="Ativo" <?= ($conhecimento->con_status ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                  <option value="Inativo" <?= ($conhecimento->con_status ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
                <label for="con_status">Status *</label>
              </div>
            </div>
            <div class="col-12">
              <div class="form-floating">
                <textarea id="con_descricao_resumida" name="con_descricao_resumida" class="form-control" maxlength="500" style="height: 100px" <?= !$podeAtualizar ? 'readonly' : '' ?>><?= $esc($conhecimento->con_descricao_resumida ?? '') ?></textarea>
                <label for="con_descricao_resumida">Descrição resumida</label>
              </div>
            </div>
            <div class="col-12 col-lg-8">
              <div class="form-floating">
                <select id="con_idcategoria" name="con_idcategoria" class="form-select" required <?= !$podeAtualizar ? 'disabled' : '' ?>>
                  <option value="">Selecione uma categoria</option>
                  <?php foreach ($categorias as $categoria): ?>
                    <?php $categoriaId = (int) ($categoria->cct_id ?? 0); ?>
                    <option value="<?= $categoriaId ?>" <?= (int) ($conhecimento->con_idcategoria ?? 0) === $categoriaId ? 'selected' : '' ?>>
                      <?= $esc($categoria->cct_nome ?? '') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <label for="con_idcategoria">Categoria *</label>
                <div class="invalid-feedback">Selecione uma categoria.</div>
              </div>
              <?php if (!$categorias): ?><div class="small text-danger mt-2"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;Cadastre uma categoria antes de cadastrar o conhecimento.</div><?php endif; ?>
            </div>
            <div class="col-12 col-lg-4">
              <div class="form-floating">
                <input id="con_ordem" name="con_ordem" type="number" min="0" class="form-control" value="<?= (int) ($conhecimento->con_ordem ?? 0) ?>" <?= !$podeAtualizar ? 'readonly' : '' ?>>
                <label for="con_ordem">Ordem de exibição</label>
              </div>
            </div>
            <div class="col-12">
              <div class="conhecimento-tags-wrap">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                  <div class="fw-semibold text-phsa"><i class="fa-solid fa-tags"></i>&nbsp;Tags</div>
                  <span class="small text-muted">Até 3 tags</span>
                </div>
                <div id="tagsConhecimentoList" class="row g-2 conhecimento-tags-grid" aria-label="Tags do conhecimento"></div>
                <input id="con_tags" name="con_tags" type="hidden" value="<?= $esc(json_encode($tags, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
              </div>
            </div>
          </div>
          <?php if ($id <= 0): ?>
            <div class="mt-4">
              <label for="con_conteudo" class="form-label fw-semibold">Conteúdo do conhecimento</label>
              <textarea id="con_conteudo" name="con_conteudo" class="form-control"><?= $esc($conhecimento->con_conteudo ?? '') ?></textarea>
            </div>
          <?php endif; ?>
          <?php if ($podeAtualizar): ?>
            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="submit" id="btnSalvarConhecimento" class="bto bto-phsa"><i class="fa-solid fa-save"></i>&nbsp;Salvar conhecimento</button>
            </div>
          <?php endif; ?>
        </form>
      </div>

        <?php if ($id > 0): ?>
          <div class="phsaTabItem p-3" id="descricaoConhecimento">
            <form id="formDescricaoConhecimento" class="needs-validation" novalidate autocomplete="off">
              <input type="hidden" name="con_id" value="<?= $id ?>">
              <div class="alert alert-light border small"><i class="fa-solid fa-circle-info"></i>&nbsp;Use este espaço para explicar o procedimento com formatação, imagens e instruções detalhadas.</div>
              <textarea id="con_conteudo" name="con_conteudo" class="form-control" <?= !$podeAtualizar ? 'readonly' : '' ?>><?= $esc($conhecimento->con_conteudo ?? '') ?></textarea>
              <?php if ($podeAtualizar): ?>
                <div class="d-flex justify-content-end gap-2 mt-4">
                  <button type="submit" id="btnSalvarDescricaoConhecimento" class="bto bto-phsa"><i class="fa-solid fa-save"></i>&nbsp;Salvar descrição</button>
                </div>
              <?php endif; ?>
            </form>
          </div>

          <div class="phsaTabItem p-3" id="midiaConhecimento">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
              <div>
                <h5 class="text-phsa mb-1"><i class="fa-solid fa-photo-film"></i>&nbsp;Mídias do conhecimento</h5>
                <div class="small text-muted">Adicione vídeos enviados ao sistema ou links externos para complementar este conteúdo.</div>
              </div>
              <?php if ($podeAtualizar): ?>
                <button type="button" id="btnNovaMidiaConhecimento" class="bto bto-phsa"><i class="fa-solid fa-plus"></i>&nbsp;Adicionar mídia</button>
              <?php endif; ?>
            </div>

            <div id="listaMidiasConhecimento"></div>
          </div>

          <div class="phsaTabItem" id="historicoConhecimento">
            <?php
              $historicoConfig = [
                'id' => $id,
                'status' => $conhecimento->con_status ?? '—',
                'statusClass' => ($conhecimento->con_status ?? '') === 'Ativo' ? 'success' : 'danger',
                'createdAt' => $conhecimento->con_dtcad ?? null,
                'updatedAt' => $conhecimento->con_dtalter ?? null,
                'logs' => $logsConhecimento,
                'emptyMessage' => 'Nenhum histórico registrado para este conhecimento.'
              ];
              include __DIR__ . '/../../template/sistema/historicoPadrao.php';
            ?>
          </div>
        <?php endif; ?>
      </div>
  </div>
</div>

<?php $this->push('style'); ?>
<style>
  .conhecimento-tags-wrap { padding: .7rem .75rem .75rem; border: 1px solid var(--icliv-border, #dee2e6); border-radius: .375rem; background: var(--icliv-surface, #fff); }
  .conhecimento-tags-grid { --bs-gutter-x: .5rem; }
  .conhecimento-tag-button { display: flex; width: 100%; min-height: 2rem; align-items: center; justify-content: center; gap: .25rem; padding: .3rem .4rem; border-radius: 999px; font-size: .78rem; white-space: nowrap; }
  .conhecimento-tag { display: inline-flex; align-items: center; gap: .25rem; min-width: 0; }
  .conhecimento-tag-label { overflow: hidden; text-overflow: ellipsis; }
  .conhecimento-tag-remove { display: inline-grid; width: 1rem; height: 1rem; place-items: center; border-radius: 50%; color: inherit; font-size: .7rem; line-height: 1; }
  .conhecimento-tag-remove:hover { color: var(--bs-danger, #b02a37); background: rgba(176, 42, 55, .12); }
  .conhecimento-midia-card { border: 1px solid #dbe4ef; border-radius: .75rem; background: #fff; overflow: hidden; }
  .conhecimento-midia-preview { display: flex; min-height: 112px; align-items: center; justify-content: center; background: linear-gradient(135deg, #edf3fb, #f8fbff); color: #315ca8; }
  .conhecimento-midia-preview video, .conhecimento-midia-preview iframe { display: block; width: 100%; height: 112px; border: 0; object-fit: cover; }
  .conhecimento-midia-preview video { background: #111; }
  .conhecimento-midia-preview i { font-size: 2rem; }
  .conhecimento-midia-title { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .conhecimento-midia-progress { height: .6rem; }
  .conhecimento-midia-modal-preview { width: 100%; aspect-ratio: 16 / 9; min-height: 0; border: 1px dashed #c8d7e9; border-radius: .75rem; background: #f7faff; overflow: hidden; }
  .conhecimento-midia-modal-preview video, .conhecimento-midia-modal-preview iframe { display: block; width: 100%; height: 100%; min-height: 0; border: 0; border-radius: .7rem; background: #111; }
  .conhecimento-midia-info { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .35rem .75rem; }
  .conhecimento-midia-info div { min-width: 0; }
  @media (max-width: 575.98px) { .conhecimento-midia-info { grid-template-columns: 1fr; } }
</style>
<?php $this->end(); ?>

<?php $this->push('script'); ?>
<script>
  $(function () {
    const podeAtualizar = <?= $podeAtualizar ? 'true' : 'false' ?>;
    const $formCadastro = $('#formCadastroConhecimento');
    const $formDescricao = $('#formDescricaoConhecimento');

    if (typeof tinymce !== 'undefined' && $('#con_conteudo').length) {
      tinymce.init({
        selector: '#con_conteudo',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | removeformat code',
        menubar: false,
        branding: false,
        promotion: false,
        height: 520,
        readonly: <?= !$podeAtualizar ? 'true' : 'false' ?>,
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; color: #263957; padding: 12px; }'
      });
    }

    const escaparTag = function (valor) {
      return $('<div>').text(valor ?? '').html();
    };
    const normalizarTag = function (valor) {
      return String(valor ?? '').toLocaleLowerCase('pt-BR').trim();
    };
    const tagValida = function (tag) {
      return tag.length >= 3 && tag.length <= 20 && /^[\p{L}\p{N}]+$/u.test(tag);
    };
    const tagsSelecionadas = (() => {
      try {
        const tags = JSON.parse($('#con_tags').val() || '[]');
        return Array.isArray(tags) ? tags.slice(0, 3).map(normalizarTag).filter(tagValida) : [];
      } catch (error) {
        return [];
      }
    })();
    const renderizarTags = function () {
      const conteudo = Array.from({ length: 3 }, function (_, indice) {
        const tag = tagsSelecionadas[indice] || '';
        const label = tag ? `#${escaparTag(tag)}` : '#add';
        const remove = tag && podeAtualizar
          ? `<span class="conhecimento-tag-remove" data-remover-tag="${indice}" role="button" tabindex="0" aria-label="Excluir tag ${escaparTag(tag)}"><i class="fa-solid fa-xmark"></i></span>`
          : '';
        return `<div class="col-4"><button type="button" class="bto bto-phsa-light conhecimento-tag-button" data-adicionar-tag aria-label="Adicionar tag ${indice + 1}" ${podeAtualizar ? '' : 'disabled'}><span class="conhecimento-tag"><span class="conhecimento-tag-label">${label}</span>${remove}</span></button></div>`;
      }).join('');

      $('#tagsConhecimentoList').html(conteudo);
      $('#con_tags').val(JSON.stringify(tagsSelecionadas));
    };
    const abrirDialogoTag = function (indice = -1) {
      if (!podeAtualizar || (indice < 0 && tagsSelecionadas.length >= 3)) return;

      Swal.fire({
        title: indice >= 0 ? 'Editar tag' : 'Adicionar tag',
        input: 'text',
        inputValue: indice >= 0 ? tagsSelecionadas[indice] : '',
        inputPlaceholder: 'cadastro',
        inputAttributes: { minlength: '3', maxlength: '20', autocomplete: 'off' },
        showCancelButton: true,
        reverseButtons: true,
        focusCancel: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i>&nbsp;Confirmar',
        cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar',
        customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' },
        preConfirm: function (valor) {
          const tag = normalizarTag(valor);
          if (tag.length < 3 || tag.length > 20) {
            Swal.showValidationMessage('A tag deve ter entre 3 e 20 caracteres.');
            return false;
          }
          if (/\s/u.test(tag)) {
            Swal.showValidationMessage('A tag não pode conter espaços.');
            return false;
          }
          if (tag.startsWith('#')) {
            Swal.showValidationMessage('Não informe # no início da tag.');
            return false;
          }
          if (!tagValida(tag)) {
            Swal.showValidationMessage('Use somente letras e números na tag.');
            return false;
          }
          if (tagsSelecionadas.some((item, itemIndice) => itemIndice !== indice && item.toLowerCase() === tag.toLowerCase())) {
            Swal.showValidationMessage('Essa tag já foi adicionada.');
            return false;
          }
          return tag;
        }
      }).then(function (resultado) {
        if (!resultado.isConfirmed) return;
        if (indice >= 0) tagsSelecionadas[indice] = resultado.value;
        else tagsSelecionadas.push(resultado.value);
        renderizarTags();
      });
    };
    const confirmarExclusaoTag = function (indice) {
      const tag = tagsSelecionadas[indice];
      Swal.fire({
        title: 'Excluir tag?',
        text: `A tag #${tag} será removida.`,
        icon: 'question',
        showCancelButton: true,
        reverseButtons: true,
        focusCancel: true,
        confirmButtonText: '<i class="fa-solid fa-trash"></i>&nbsp;Excluir',
        cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar',
        customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' }
      }).then(function (resultado) {
        if (!resultado.isConfirmed) return;
        tagsSelecionadas.splice(indice, 1);
        renderizarTags();
      });
    };

    $('#tagsConhecimentoList').on('click', '[data-adicionar-tag]', function (event) {
      const $remove = $(event.target).closest('[data-remover-tag]');
      if ($remove.length) {
        event.preventDefault();
        event.stopPropagation();
        confirmarExclusaoTag(Number($remove.data('remover-tag')));
        return;
      }
      event.preventDefault();
      abrirDialogoTag();
    });
    renderizarTags();

    const conhecimentoId = <?= $id ?>;
    const urlSalvarMidia = '<?= Helpers::url('projeto/salvarConhecimentoMidia') ?>';
    const urlStatusMidia = '<?= Helpers::url('projeto/alterarStatusConhecimentoMidia') ?>';
    let midiasConhecimento = <?= json_encode(array_values($midias), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const escaparMidia = function (valor) {
      return $('<div>').text(valor ?? '').html();
    };
    const formatarTamanhoMidia = function (midia) {
      if (midia.tamanho && midia.medida) return `${midia.tamanho} ${midia.medida}`;
      if (!midia.size) return '-';
      const unidades = ['Bytes', 'KB', 'MB', 'GB'];
      let tamanho = Number(midia.size);
      let indice = 0;
      while (tamanho >= 1024 && indice < unidades.length - 1) { tamanho /= 1024; indice++; }
      return `${tamanho.toFixed(2)} ${unidades[indice]}`;
    };
    const formatarDuracaoMidia = function (segundos) {
      const valor = Number(segundos);
      if (!Number.isFinite(valor) || valor <= 0) return '-';
      const minutos = Math.floor(valor / 60);
      const resto = Math.floor(valor % 60).toString().padStart(2, '0');
      return `${minutos}:${resto}`;
    };
    const urlEmbedMidia = function (url) {
      try {
        const origem = new URL(url);
        const host = origem.hostname.toLowerCase().replace(/^www\./, '');
        if (host === 'youtu.be') {
          return `https://www.youtube.com/embed/${origem.pathname.replace(/^\//, '')}`;
        }
        if (host.includes('youtube.com')) {
          const videoId = origem.searchParams.get('v');
          if (videoId) return `https://www.youtube.com/embed/${videoId}`;
        }
        if (host.includes('vimeo.com')) {
          const videoId = origem.pathname.split('/').filter(Boolean).pop();
          if (videoId) return `https://player.vimeo.com/video/${videoId}`;
        }
        return url;
      } catch (error) {
        return url;
      }
    };
    const previewMidia = function (midia, modal = false) {
      const url = midia.cmi_url_publica || midia.cmi_url || '';
      const classe = modal ? 'class="rounded w-100"' : '';
      if (!url) return `<div class="text-center text-muted py-4"><i class="fa-solid fa-photo-film fa-2x"></i><div class="small mt-2">Sem endereço de mídia.</div></div>`;
      if (midia.cmi_tipo === 'Upload') {
        return `<video ${classe} src="${escaparMidia(url)}" controls playsinline preload="metadata"></video>`;
      }
      return `<iframe ${classe} src="${escaparMidia(urlEmbedMidia(url))}" title="${escaparMidia(midia.cmi_nome || 'Mídia')}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
    };
    const snapshotArquivo = function (arquivo) {
      const snapshot = { ...arquivo };
      delete snapshot.data;
      delete snapshot.arquivo;
      return snapshot;
    };
    const informacoesArquivo = function (arquivo) {
      const dimensao = arquivo.largura && arquivo.altura ? `${arquivo.largura} × ${arquivo.altura}` : '-';
      let alterado = '-';
      if (arquivo.lastModified) {
        const data = new Date(Number(arquivo.lastModified));
        if (!Number.isNaN(data.getTime())) alterado = data.toLocaleString('pt-BR');
      }
      return `<div class="conhecimento-midia-info small text-muted mt-3">
        <div><strong>Formato:</strong> ${escaparMidia(arquivo.type || '-')}</div>
        <div><strong>Tamanho:</strong> ${escaparMidia(formatarTamanhoMidia(arquivo))}</div>
        <div><strong>Resolução:</strong> ${escaparMidia(dimensao)}</div>
        <div><strong>Duração:</strong> ${escaparMidia(formatarDuracaoMidia(arquivo.duracao))}</div>
        <div class="text-break"><strong>Última alteração:</strong> ${escaparMidia(alterado)}</div>
      </div>`;
    };
    const renderizarMidias = function () {
      const $lista = $('#listaMidiasConhecimento');
      if (!$lista.length) return;
      if (!midiasConhecimento.length) {
        $lista.html('<div class="alert alert-light border mb-0"><i class="fa-solid fa-circle-info"></i>&nbsp;Nenhuma mídia cadastrada para este conhecimento.</div>');
        return;
      }

      const linhas = midiasConhecimento.map(function (midia) {
        const classeStatus = midia.cmi_statusclass === 'danger' ? 'danger' : 'success';
        const acaoStatus = midia.cmi_status === 'Ativo' ? 'Inativar' : 'Ativar';
        return `<tr>
          <td><div class="fw-semibold conhecimento-midia-title" title="${escaparMidia(midia.cmi_nome)}">${escaparMidia(midia.cmi_nome || 'Mídia sem nome')}</div><div class="small text-muted">${escaparMidia(midia.cmi_tipo)}</div></td>
          <td class="text-center">${escaparMidia(midia.cmi_ordem || 0)}</td>
          <td class="text-center"><button type="button" class="badge border-0 bg-${classeStatus} btnStatusMidiaConhecimento" data-cmi-id="${Number(midia.cmi_id)}" data-cmi-status="${escaparMidia(midia.cmi_status)}" title="${acaoStatus} mídia">${escaparMidia(midia.cmi_status)}</button></td>
          <td class="text-end"><button type="button" class="bto bto-phsa-light btnVisualizarMidiaConhecimento" data-cmi-id="${Number(midia.cmi_id)}" title="Visualizar mídia"><i class="fa-solid fa-eye"></i></button></td>
        </tr>`;
      }).join('');

      $lista.html(`<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Nome</th><th class="text-center">Ordem</th><th class="text-center">Status</th><th class="text-end">Ações</th></tr></thead><tbody>${linhas}</tbody></table></div>`);
    };
    renderizarMidias();

    const abrirVisualizacaoMidia = function (midia) {
      SimpleModal.mostrar({
        titulo: `<i class="fa-solid fa-photo-film"></i>&nbsp;${escaparMidia(midia.cmi_nome || 'Visualizar mídia')}`,
        tamanho: 'modal-lg modal-dialog-centered',
        corpo: `<div class="conhecimento-midia-modal-preview">${previewMidia(midia, true)}</div>`,
        rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Fechar</button>'
      });
    };

    const abrirCadastroMidia = function (tipo, arquivoInicial = null, lendoArquivo = false) {
      const upload = tipo === 'Upload';
      let arquivoSelecionado = null;
      const modalId = SimpleModal.mostrar({
        titulo: upload ? '<i class="fa-solid fa-upload"></i>&nbsp;Adicionar vídeo' : '<i class="fa-solid fa-link"></i>&nbsp;Adicionar link',
        tamanho: 'modal-lg modal-dialog-scrollable',
        corpo: upload ? `
          <div id="previewMidiaSelecionada" class="conhecimento-midia-modal-preview d-flex align-items-center justify-content-center text-muted mb-3"><div class="text-center"><i class="fa-solid fa-cloud-arrow-up fa-2x mb-2"></i><div>Selecione um vídeo para visualizar antes do envio.</div></div></div>
          <div class="form-floating mb-3"><input id="nomeMidiaSelecionada" class="form-control" maxlength="255" placeholder="Nome do vídeo" required><label for="nomeMidiaSelecionada">Nome do vídeo *</label><div class="invalid-feedback">Informe o nome do vídeo.</div></div>
          <div id="informacoesMidiaSelecionada"></div>
          <div id="progressoMidiaSelecionada" class="mt-3" hidden><div class="d-flex justify-content-between small text-muted mb-1"><span>Enviando vídeo...</span><span id="percentualMidiaSelecionada">0%</span></div><div class="progress conhecimento-midia-progress"><div id="barraMidiaSelecionada" class="progress-bar bg-phsa" role="progressbar" style="width:0%">&nbsp;</div></div></div>
        ` : `
          <div class="form-floating mb-3"><input id="urlLinkMidia" type="url" class="form-control" placeholder="Cole a URL da mídia" required><label for="urlLinkMidia">Link / URL *</label><div class="invalid-feedback">Informe uma URL válida.</div></div>
          <div class="form-floating mb-1"><input id="nomeLinkMidia" class="form-control" maxlength="255" minlength="5" placeholder="Título"><label for="nomeLinkMidia">Título</label><div class="invalid-feedback">Informe um título com pelo menos 5 caracteres.</div></div>
          <div id="statusTituloLinkMidia" class="small text-muted mb-3">Ao usar um link do YouTube, tentaremos preencher o título automaticamente.</div>
          <div id="previewLinkMidia" class="conhecimento-midia-modal-preview d-flex align-items-center justify-content-center text-muted"><div class="text-center"><i class="fa-solid fa-link fa-2x mb-2"></i><div>Informe um link para visualizar a mídia.</div></div></div>
        `,
        rodape: upload ? '<button type="button" id="btnCancelarCadastroMidia" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</button><button type="button" id="btnTrocarArquivoMidia" class="bto bto-phsa-light"><i class="fa-solid fa-rotate"></i>&nbsp;Trocar arquivo</button><button type="button" id="btnSalvarMidiaModal" class="bto bto-phsa" disabled><i class="fa-solid fa-upload"></i>&nbsp;Enviar vídeo</button>' : '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</button><button type="button" id="btnSalvarMidiaModal" class="bto bto-phsa"><i class="fa-solid fa-link"></i>&nbsp;Salvar link</button>'
      });
      const $modal = SimpleModal.modais[modalId];

      if (upload && lendoArquivo) {
        $modal.find('#previewMidiaSelecionada').html('<div class="text-center text-muted"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i><div>Lendo o vídeo selecionado...</div></div>');
      }

      const nomeArquivoSemExtensao = function (nome) {
        return String(nome || 'Vídeo').replace(/\.[^/.]+$/, '').trim() || 'Vídeo';
      };

      const preencherArquivo = function (arquivo) {
        if (!arquivo || !arquivo.status || !arquivo.data) {
          swError('Não foi possível ler o vídeo. Selecione um arquivo MP4 válido.');
          return false;
        }
        arquivoSelecionado = arquivo;
        const nome = nomeArquivoSemExtensao(arquivo.name);
        $modal.find('#nomeMidiaSelecionada').val(nome).removeClass('is-invalid');
        $modal.find('#previewMidiaSelecionada').removeClass('d-flex').html(previewMidia({ cmi_tipo: 'Upload', cmi_url_publica: arquivo.data, cmi_nome: nome }, true));
        $modal.find('#informacoesMidiaSelecionada').html(informacoesArquivo(arquivo));
        $modal.find('#btnSalvarMidiaModal').prop('disabled', false);
        return true;
      };

      const arquivoMp4Valido = function (arquivo) {
        const nome = String(arquivo?.name || '').toLowerCase();
        const tipoArquivo = String(arquivo?.type || '').toLowerCase();
        return tipoArquivo === 'video/mp4' || nome.endsWith('.mp4');
      };

      const selecionarArquivo = function () {
        if ($modal.data('enviando')) return;
        $modal.find('#previewMidiaSelecionada').html('<div class="text-center text-muted"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i><div>Lendo o vídeo selecionado...</div></div>');
        fgbSelecionarArquivo({
          customAccept: 'video/mp4,.mp4',
          replaceDefault: true,
          onFileReadStart: function (arquivo) {
            if (!arquivoMp4Valido(arquivo)) {
              swError('Formato inválido. Selecione somente um vídeo MP4.');
              $modal.find('#previewMidiaSelecionada').html('<div class="text-center text-muted"><i class="fa-solid fa-cloud-arrow-up fa-2x mb-2"></i><div>Selecione um vídeo MP4 válido.</div></div>');
              return false;
            }
          },
          onFileSelect: function (arquivo) {
            preencherArquivo(arquivo);
          }
        });
      };

      if (upload) {
        $modal.on('click', '#btnTrocarArquivoMidia', selecionarArquivo);
        $modal.on('click', '#btnSalvarMidiaModal', function () {
          const $botao = $(this);
          const nome = String($modal.find('#nomeMidiaSelecionada').val() || '').trim();
          if (!arquivoSelecionado || !nome) {
            $modal.find('#nomeMidiaSelecionada').addClass('is-invalid');
            return;
          }

          $modal.data('enviando', true);
          $modal.find('.btn-close, #btnCancelarCadastroMidia, #btnTrocarArquivoMidia').prop('disabled', true).hide();
          $botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Enviando');
          $modal.find('#progressoMidiaSelecionada').prop('hidden', false);

          const arquivo = arquivoSelecionado.arquivo;
          // O PHP do ambiente aceita arquivos multipart de até 2 MiB por requisição.
          // Mantemos cada parte em 1,5 MiB para deixar margem para o envelope multipart.
          const tamanhoParte = 1536 * 1024;
          const totalPartes = Math.ceil(arquivo.size / tamanhoParte);
          if (totalPartes > 512) {
            $modal.data('enviando', false);
            $modal.find('.btn-close, #btnCancelarCadastroMidia, #btnTrocarArquivoMidia').prop('disabled', false).show();
            $modal.find('#progressoMidiaSelecionada').prop('hidden', true);
            $botao.prop('disabled', false).html('<i class="fa-solid fa-upload"></i>&nbsp;Enviar vídeo');
            swError('O vídeo excede o limite de 768 MiB por envio.');
            return;
          }
          const idEnvio = (window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`)
            .replace(/[^a-f0-9]/gi, '').padEnd(32, '0').slice(0, 32).toLowerCase();

          const falhaEnvio = function (response = null) {
            $modal.data('enviando', false);
            $modal.find('.btn-close, #btnCancelarCadastroMidia, #btnTrocarArquivoMidia').prop('disabled', false).show();
            $botao.prop('disabled', false).html('<i class="fa-solid fa-upload"></i>&nbsp;Enviar vídeo');
            if (response) swResponse(response);
            else swError('Não foi possível enviar o vídeo.');
          };

          const enviarParte = function (indice) {
            const inicio = indice * tamanhoParte;
            const fim = Math.min(inicio + tamanhoParte, arquivo.size);
            const dados = new FormData();
            dados.append('cmi_idconhecimento', conhecimentoId);
            dados.append('cmi_tipo', 'Upload');
            dados.append('cmi_nome', nome);
            dados.append('cmi_nomearquivo', arquivo.name || 'video.mp4');
            dados.append('cmi_chunk_id', idEnvio);
            dados.append('cmi_chunk_index', indice);
            dados.append('cmi_chunk_total', totalPartes);
            if (indice === totalPartes - 1) {
              dados.append('cmi_json', JSON.stringify(snapshotArquivo(arquivoSelecionado)));
            }
            dados.append('cmi_arquivo', arquivo.slice(inicio, fim, arquivo.type || 'video/mp4'), arquivo.name || 'video.mp4');

            // O ID também vai na query string: se o PHP não interpretar o multipart,
            // ainda conseguimos associar a chamada ao conhecimento e diagnosticar o corpo.
            const urlParte = new URL(urlSalvarMidia, window.location.href);
            urlParte.searchParams.set('cmi_idconhecimento', String(conhecimentoId));

            $.ajax({
              url: urlParte.toString(),
              type: 'POST',
              dataType: 'json',
              data: dados,
              processData: false,
              contentType: false,
              xhr: function () {
                const xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                  xhr.upload.addEventListener('progress', function (event) {
                    if (!event.lengthComputable) return;
                    const bytesEnviados = inicio + Math.min(event.loaded, fim - inicio);
                    const percentual = Math.min(100, Math.round((bytesEnviados / arquivo.size) * 100));
                    $modal.find('#barraMidiaSelecionada').css('width', `${percentual}%`);
                    $modal.find('#percentualMidiaSelecionada').text(`${percentual}%`);
                  });
                }
                return xhr;
              }
            }).done(function (response) {
              if (!response.status) {
                falhaEnvio(response);
                return;
              }

              if (indice < totalPartes - 1) {
                enviarParte(indice + 1);
                return;
              }

              swResponse(response).then(function () {
                if (response.data && response.data[0]) midiasConhecimento.push(response.data[0]);
                renderizarMidias();
                SimpleModal.fechar(modalId);
              });
            }).fail(function () {
              falhaEnvio();
            });
          };

          enviarParte(0);
        });
        if (arquivoInicial) preencherArquivo(arquivoInicial);
      } else {
        let tituloPreenchidoAutomaticamente = false;
        let tentativaTitulo = 0;
        const $url = $modal.find('#urlLinkMidia');
        const $titulo = $modal.find('#nomeLinkMidia');
        const $statusTitulo = $modal.find('#statusTituloLinkMidia');

        const exigirTitulo = function (mensagem = 'Informe um título com pelo menos 5 caracteres.') {
          $titulo.prop('required', true);
          $statusTitulo.removeClass('text-success text-warning').addClass('text-muted').text(mensagem);
        };

        const buscarTituloYoutube = function (url) {
          const tentativaAtual = ++tentativaTitulo;
          const youtube = /(?:youtube\.com\/|youtu\.be\/)/i.test(url);
          if (!youtube) {
            tituloPreenchidoAutomaticamente = false;
            exigirTitulo('Informe um título com pelo menos 5 caracteres para este link.');
            return;
          }

          tituloPreenchidoAutomaticamente = false;
          $titulo.prop('required', true);
          $statusTitulo.removeClass('text-success text-muted').addClass('text-warning').html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Identificando o título do vídeo...');

          fetch(`https://www.youtube.com/oembed?url=${encodeURIComponent(url)}&format=json`)
            .then(function (resposta) {
              if (!resposta.ok) throw new Error('Não foi possível consultar o título.');
              return resposta.json();
            })
            .then(function (dados) {
              if (tentativaAtual !== tentativaTitulo) return;
              const titulo = String(dados.title || '').trim();
              if (titulo.length < 5) throw new Error('Título insuficiente.');
              $titulo.val(titulo).removeClass('is-invalid').prop('required', false);
              tituloPreenchidoAutomaticamente = true;
              $statusTitulo.removeClass('text-warning text-muted').addClass('text-success').html('<i class="fa-solid fa-check"></i>&nbsp;Título preenchido automaticamente pelo YouTube.');
            })
            .catch(function () {
              if (tentativaAtual !== tentativaTitulo) return;
              tituloPreenchidoAutomaticamente = false;
              exigirTitulo('Não foi possível identificar o título. Informe um título com pelo menos 5 caracteres.');
            });
        };

        $modal.on('input', '#urlLinkMidia', function () {
          const url = String($(this).val() || '').trim();
          tituloPreenchidoAutomaticamente = false;
          if ($titulo.val()) $titulo.val('');
          const preview = url ? previewMidia({ cmi_tipo: 'Link', cmi_url_publica: url, cmi_nome: $titulo.val() }) : '<div class="text-center text-muted py-4"><i class="fa-solid fa-link fa-2x"></i><div class="small mt-2">Informe um link para visualizar a mídia.</div></div>';
          $modal.find('#previewLinkMidia').removeClass('d-flex').html(preview);
          if (/^https?:\/\/[^\s]+$/i.test(url)) buscarTituloYoutube(url);
          else exigirTitulo('Informe um título com pelo menos 5 caracteres.');
        });
        $modal.on('input', '#nomeLinkMidia', function () {
          if (tituloPreenchidoAutomaticamente) tituloPreenchidoAutomaticamente = false;
          if (String($(this).val() || '').trim().length < 5) exigirTitulo();
          else $statusTitulo.removeClass('text-warning text-muted').addClass('text-success').html('<i class="fa-solid fa-check"></i>&nbsp;Título informado.');
        });
        $modal.on('click', '#btnSalvarMidiaModal', function () {
          const nome = String($titulo.val() || '').trim();
          const url = String($url.val() || '').trim();
          const urlValida = /^https?:\/\/[^\s]+$/i.test(url);
          const nomeValido = nome.length >= 5;
          $titulo.toggleClass('is-invalid', !nomeValido);
          $url.toggleClass('is-invalid', !urlValida);
          if (!urlValida || !nomeValido) return;
          const $botao = $(this);
          $botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando');
          $.ajax({ url: urlSalvarMidia, type: 'POST', dataType: 'json', data: { cmi_idconhecimento: conhecimentoId, cmi_tipo: 'Link', cmi_nome: nome, cmi_url: url } }).done(function (response) {
            swResponse(response).then(function () {
              if (!response.status) { $botao.prop('disabled', false).html('<i class="fa-solid fa-link"></i>&nbsp;Salvar link'); return; }
              if (response.data && response.data[0]) midiasConhecimento.push(response.data[0]);
              renderizarMidias();
              SimpleModal.fechar(modalId);
            });
          }).fail(function () { $botao.prop('disabled', false).html('<i class="fa-solid fa-link"></i>&nbsp;Salvar link'); swError('Não foi possível salvar o link.'); });
        });
      }

      return { modalId, preencherArquivo, selecionarArquivo };
    };

    const iniciarSelecaoVideo = function () {
      let controleModal = null;
      fgbSelecionarArquivo({
        customAccept: 'video/mp4,.mp4',
        replaceDefault: true,
        onFileReadStart: function (arquivo) {
          const nome = String(arquivo?.name || '').toLowerCase();
          const tipoArquivo = String(arquivo?.type || '').toLowerCase();
          if (tipoArquivo !== 'video/mp4' && !nome.endsWith('.mp4')) {
            swError('Formato inválido. Selecione somente um vídeo MP4.');
            return false;
          }
          controleModal = abrirCadastroMidia('Upload', null, true);
        },
        onFileSelect: function (arquivo) {
          if (!arquivo || !arquivo.status || !arquivo.data) {
            if (controleModal) SimpleModal.fechar(controleModal.modalId);
            swError('Não foi possível ler o vídeo. Selecione um arquivo MP4 válido.');
            return;
          }
          if (controleModal) controleModal.preencherArquivo(arquivo);
        }
      });
    };

    $('#btnNovaMidiaConhecimento').on('click', function () {
      Swal.fire({
        title: 'Adicionar mídia',
        text: 'Como deseja adicionar este recurso?',
        icon: 'question',
        showDenyButton: true,
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: '<i class="fa-solid fa-upload"></i>&nbsp;Enviar vídeo',
        denyButtonText: '<i class="fa-solid fa-link"></i>&nbsp;Usar link',
        cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar',
        customClass: { confirmButton: 'bto bto-phsa-light', denyButton: 'bto bto-phsa-light', cancelButton: 'bto bto-phsa-light' }
      }).then(function (resultado) {
        if (resultado.isConfirmed) iniciarSelecaoVideo();
        if (resultado.isDenied) abrirCadastroMidia('Link');
      });
    });

    $('#listaMidiasConhecimento').on('click', '.btnVisualizarMidiaConhecimento', function () {
      const midia = midiasConhecimento.find(item => Number(item.cmi_id) === Number($(this).data('cmi-id')));
      if (midia) abrirVisualizacaoMidia(midia);
    });

    $('#listaMidiasConhecimento').on('click', '.btnStatusMidiaConhecimento', function () {
      const $botao = $(this);
      const midia = midiasConhecimento.find(item => Number(item.cmi_id) === Number($botao.data('cmi-id')));
      if (!midia) return;
      const novoStatus = midia.cmi_status === 'Ativo' ? 'Inativo' : 'Ativo';
      Swal.fire({
        title: `${novoStatus === 'Ativo' ? 'Ativar' : 'Inativar'} mídia?`,
        text: `A mídia "${midia.cmi_nome || 'sem nome'}" será ${novoStatus === 'Ativo' ? 'ativada' : 'inativada'}.`,
        icon: 'question',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i>&nbsp;Confirmar',
        cancelButtonText: '<i class="fa-solid fa-xmark"></i>&nbsp;Cancelar',
        customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' }
      }).then(function (resultado) {
        if (!resultado.isConfirmed) return;
        $botao.prop('disabled', true);
        $.ajax({ url: urlStatusMidia, type: 'POST', dataType: 'json', data: { cmi_id: midia.cmi_id, cmi_idconhecimento: conhecimentoId, cmi_status: novoStatus } }).done(function (response) {
          swResponse(response).then(function () {
            $botao.prop('disabled', false);
            if (!response.status || !response.data || !response.data[0]) return;
            const indice = midiasConhecimento.findIndex(item => Number(item.cmi_id) === Number(midia.cmi_id));
            if (indice >= 0) midiasConhecimento[indice] = response.data[0];
            renderizarMidias();
          });
        }).fail(function () { $botao.prop('disabled', false); swError('Não foi possível alterar o status da mídia.'); });
      });
    });

    const salvarFormulario = function ($formAtual, $botao, tituloAguarde) {
      if (!podeAtualizar) return;

      if ($formAtual.find('#con_conteudo').length && typeof tinymce !== 'undefined') {
        tinymce.triggerSave();
      }

      if (!$formAtual[0].checkValidity()) {
        $formAtual.addClass('was-validated');
        return;
      }

      $botao.prop('disabled', true);
      swAguarde({ title: tituloAguarde, text: 'Aguarde enquanto processamos os dados.', timer: null });

      $.ajax({
        url: '<?= Helpers::url('projeto/salvarConhecimento') ?>',
        type: 'POST',
        dataType: 'json',
        data: $formAtual.serialize()
      }).done(function (response) {
        swResponse(response).then(function () {
          if (response.status && response.location) {
            window.location.href = response.location;
            return;
          }
          $botao.prop('disabled', false);
        });
      }).fail(function (xhr) {
        console.error(xhr.responseText);
        $botao.prop('disabled', false);
        swError('Não foi possível salvar o conhecimento.');
      });
    };

    $formCadastro.on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      salvarFormulario($(this), $('#btnSalvarConhecimento'), 'Salvando conhecimento...');
    });

    $formDescricao.on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      salvarFormulario($(this), $('#btnSalvarDescricaoConhecimento'), 'Salvando descrição...');
    });
  });
</script>
<?php $this->end(); ?>


