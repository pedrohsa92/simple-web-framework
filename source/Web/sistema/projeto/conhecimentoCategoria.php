<?php
  $this->layout('sistema::sistema');
  $categorias = $categorias ?? [];
  $podeAtualizar = (bool) ($podeAtualizar ?? false);
  $podeCriar = (bool) ($podeCriar ?? false);
  $podeAcessarLista = (bool) ($podeAcessarLista ?? false);
  $esc = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $categoriasJson = json_encode($categorias, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <div class="text-uppercase small text-phsa fw-semibold">Projeto · Conhecimento</div>
        <h4 class="m-0">Categorias de conhecimento</h4>
        <div class="text-muted small mt-1">Organize os assuntos que classificam os conhecimentos da central.</div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= Helpers::url($podeAcessarLista ? 'projeto/conhecimento/list' : 'projeto/conhecimento/form') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;<?= $podeAcessarLista ? 'Conhecimentos' : 'Novo conhecimento' ?></a>
        <?php if ($podeCriar): ?><a href="<?= Helpers::url('projeto/conhecimento/categoria/novo') ?>" class="bto bto-phsa"><i class="fa-solid fa-plus"></i>&nbsp;Nova categoria</a><?php endif; ?>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Categoria</th><th>Status</th><th>Conhecimentos</th><th>Ordem</th><th class="text-end">Ação</th></tr></thead>
        <tbody id="tbodyCategoriasConhecimento">
          <?php foreach ($categorias as $indice => $categoria): $id = (int) ($categoria->cct_id ?? 0); $ordem = (int) ($categoria->cct_ordem ?? 0); ?>
            <tr>
              <td><?php if ($podeAtualizar): ?><a href="<?= Helpers::url('projeto/conhecimento/categoria/' . $id) ?>" class="text-phsa text-decoration-none fw-semibold"><?= $esc($categoria->cct_nome ?? '-') ?></a><?php else: ?><?= $esc($categoria->cct_nome ?? '-') ?><?php endif; ?></td>
              <td><span class="badge bg-<?= ($categoria->cct_status ?? 'Ativo') === 'Ativo' ? 'success' : 'danger' ?>"><?= $esc($categoria->cct_status ?? 'Ativo') ?></span></td>
              <td><?= (int) ($categoria->cct_totalconhecimentos ?? 0) ?></td>
              <td class="text-center"><?php if ($podeAtualizar): ?><div class="d-inline-flex align-items-center justify-content-center gap-1"><button type="button" class="bto btn-sm bto-phsa-light btnOrdenarCategoria" data-id="<?= $id ?>" data-direcao="cima" title="Mover para cima" <?= $indice > 0 ? '' : 'disabled' ?>><i class="fa-solid fa-chevron-up"></i></button><span class="px-1 fw-semibold"><?= $ordem ?></span><button type="button" class="bto btn-sm bto-phsa-light btnOrdenarCategoria" data-id="<?= $id ?>" data-direcao="baixo" title="Mover para baixo" <?= $indice < count($categorias) - 1 ? '' : 'disabled' ?>><i class="fa-solid fa-chevron-down"></i></button></div><?php else: ?><?= $ordem ?><?php endif; ?></td>
              <td class="text-end"><?php if ($podeAtualizar): ?><a href="<?= Helpers::url('projeto/conhecimento/categoria/' . $id) ?>" class="bto btn-sm bto-phsa-light" title="Editar categoria"><i class="fa-solid fa-pen-to-square"></i></a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$categorias): ?><tr><td colspan="5" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php $this->push('script'); ?>
<script>
  $(function () {
    let categorias = <?= $categoriasJson ?: '[]' ?>;
    const podeAtualizar = <?= $podeAtualizar ? 'true' : 'false' ?>;
    const escaparHtml = valor => $('<div>').text(valor ?? '').html();

    function renderizar() {
      const $tbody = $('#tbodyCategoriasConhecimento');
      if (!categorias.length) { $tbody.html('<tr><td colspan="5" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td></tr>'); return; }
      $tbody.html(categorias.map((categoria, indice) => {
        const id = Number(categoria.cct_id || 0), ordem = Number(categoria.cct_ordem || 0);
        const status = String(categoria.cct_status || 'Ativo'), statusClass = status === 'Ativo' ? 'success' : 'danger';
        const urlEdicao = '<?= Helpers::url('projeto/conhecimento/categoria') ?>/' + id;
        const acoes = podeAtualizar ? `<a href="${urlEdicao}" class="bto btn-sm bto-phsa-light" title="Editar categoria"><i class="fa-solid fa-pen-to-square"></i></a>` : '<span class="text-muted">-</span>';
        const nome = podeAtualizar ? `<a href="${urlEdicao}" class="text-phsa text-decoration-none fw-semibold">${escaparHtml(categoria.cct_nome || '-')}</a>` : escaparHtml(categoria.cct_nome || '-');
        const ordemHtml = podeAtualizar ? `<div class="d-inline-flex align-items-center justify-content-center gap-1"><button type="button" class="bto btn-sm bto-phsa-light btnOrdenarCategoria" data-id="${id}" data-direcao="cima" ${indice > 0 ? '' : 'disabled'}><i class="fa-solid fa-chevron-up"></i></button><span class="px-1 fw-semibold">${ordem}</span><button type="button" class="bto btn-sm bto-phsa-light btnOrdenarCategoria" data-id="${id}" data-direcao="baixo" ${indice < categorias.length - 1 ? '' : 'disabled'}><i class="fa-solid fa-chevron-down"></i></button></div>` : ordem;
        return `<tr><td>${nome}</td><td><span class="badge bg-${statusClass}">${escaparHtml(status)}</span></td><td>${Number(categoria.cct_totalconhecimentos || 0)}</td><td class="text-center">${ordemHtml}</td><td class="text-end">${acoes}</td></tr>`;
      }).join(''));
    }
    function atualizar() {
      $.ajax({ url: '<?= Helpers::url('projeto/pesquisarConhecimentoCategorias') ?>', type: 'POST', dataType: 'json' })
        .done(response => { if (!response.status) { swResponse(response, { timer: 2500 }); return; } categorias = response.data || []; renderizar(); })
        .fail(xhr => console.error(xhr.responseText));
    }
    $(document).on('click', '.btnOrdenarCategoria', function () {
      if (!podeAtualizar) return;
      const $button = $(this).prop('disabled', true);
      $.ajax({ url: '<?= Helpers::url('projeto/reordenarConhecimentoCategoria') ?>', type: 'POST', dataType: 'json', data: { cct_id: Number($button.data('id') || 0), direcao: String($button.data('direcao') || '') } })
        .done(response => { if (response.status) { atualizar(); return; } $button.prop('disabled', false); swResponse(response, { timer: 2500 }); })
        .fail(xhr => { console.error(xhr.responseText); $button.prop('disabled', false); swError('Não foi possível alterar a ordem da categoria.'); });
    });
  });
</script>
<?php $this->end(); ?>


