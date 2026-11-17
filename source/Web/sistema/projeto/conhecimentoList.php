<?php
  $this->layout('sistema::listBase', [
    'getInfo' => $getInfo,
    'titulo' => $titulo,
    'descricao' => $descricao,
    'urlCadastrar' => $urlCadastrar,
    'urlPesquisar' => $urlPesquisar,
    'ocultarCadastrar' => $ocultarCadastrar,
    'labelPesquisaSimples' => $labelPesquisaSimples,
    'labelTotal' => $labelTotal,
    'nomeArquivo' => $nomeArquivo,
    'prefixo' => $prefixo
  ]);
  $podeAtualizar = (bool) ($podeAtualizar ?? false);
  $podeVisualizarRegistros = (bool) ($podeVisualizarRegistros ?? false);
  $podeAbrirConhecimento = $podeAtualizar || $podeVisualizarRegistros;
  $alertaFlashJson = json_encode($alertaFlash ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<?php $this->start('listBase_botoesExtra'); ?>
  <?php if ($podeVisualizarRegistros): ?>
    <a href="<?= Helpers::url('projeto/conhecimento/categoria') ?>" class="bto bto-phsa-light" type="button">
      <i class="fa-solid fa-folder-tree"></i>&nbsp;Categorias
    </a>
  <?php endif; ?>
<?php $this->end(); ?>

<?php $this->start('listBase_pesquisaPersonalizada'); ?>
  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <select id="con_status" name="con_status" class="form-select input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="Ativo">Ativo</option>
          <option value="Inativo">Inativo</option>
        </select>
        <label for="con_status">Status</label>
      </div>
    </div>
  </div>
<?php $this->end(); ?>

<?php $this->start('listBase_camposOrdenacao'); ?>
  <option value="con_id">Número de identificação</option>
  <option value="con_titulo">Título</option>
  <option value="cct_nome">Categoria</option>
  <option value="con_status">Status</option>
  <option value="con_ordem">Ordem</option>
<?php $this->end(); ?>

<?php $this->start('listBase_resultado'); ?>
  <?php if (!$podeVisualizarRegistros): ?>
    <div class="alert alert-info mb-3"><i class="fa-solid fa-circle-info"></i>&nbsp;Você pode cadastrar conhecimentos. Para consultar registros existentes, é necessária a permissão de visualização.</div>
  <?php endif; ?>
  <div class="table-responsive">
    <table id="tableList" class="table table-hover align-middle">
      <thead>
        <tr>
          <th class="colPrimaria">Conhecimento</th>
          <th class="colPrimaria">Categoria</th>
          <th class="colPrimaria">Status</th>
          <th class="colSecundaria">Tags</th>
          <th class="colSecundaria">Ordem</th>
          <th class="colSecundaria">Cadastro</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody id="tbodyListBase">
        <tr><td colspan="100" class="text-center text-muted py-4">Aguardando pesquisa...</td></tr>
      </tbody>
    </table>
  </div>
<?php $this->end(); ?>

<?php $this->start('listBase_script'); ?>
  <script>
    const conhecimentoPodeAtualizar = <?= $podeAtualizar ? 'true' : 'false' ?>;
    const conhecimentoPodeAbrir = <?= $podeAbrirConhecimento ? 'true' : 'false' ?>;
    const conhecimentoUrlForm = '<?= Helpers::url('projeto/conhecimento/form') ?>';

    function renderizarLinhaListBase(item) {
      const id = parseInt(item.con_id || 0, 10);
      const titulo = String(item.con_titulo || 'Conhecimento sem título');
      const resumo = String(item.con_descricao_resumida || '');
      const categoria = String(item.con_categoria_nome || 'Categoria não definida');
      const status = String(item.con_status || '');
      const statusClass = String(item.con_statusclass || (status === 'Ativo' ? 'success' : 'danger'));
      let tags = [];
      try {
        const parsed = JSON.parse(item.con_tags || '[]');
        tags = Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        tags = [];
      }
      const url = `${conhecimentoUrlForm}/${id}`;
      const tituloHtml = conhecimentoPodeAbrir
        ? `<a href="${url}" class="text-phsa fw-semibold">${fgbEscapeHtml(titulo)}</a>`
        : `<span class="fw-semibold">${fgbEscapeHtml(titulo)}</span>`;

      return `
        <tr title="ID: ${id}">
          <td class="colPrimaria">
            ${tituloHtml}
            ${resumo ? `<div class="small text-muted text-truncate" style="max-width: 460px">${fgbEscapeHtml(resumo)}</div>` : ''}
          </td>
          <td class="colPrimaria">${fgbEscapeHtml(categoria)}</td>
          <td class="colPrimaria"><span class="badge bg-${fgbEscapeHtml(statusClass)}">${fgbEscapeHtml(status || 'Não definido')}</span></td>
          <td class="colSecundaria">${tags.length ? tags.map(tag => `#${fgbEscapeHtml(String(tag))}`).join(' · ') : '<span class="text-muted">Sem tags</span>'}</td>
          <td class="colSecundaria">${parseInt(item.con_ordem || 0, 10)}</td>
          <td class="colSecundaria">${item.con_dtcad ? fgbEscapeHtml(String(item.con_dtcad)) : '-'}</td>
          <td class="text-end text-nowrap">
            ${conhecimentoPodeAtualizar
              ? `<a href="${url}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Editar conhecimento"><i class="fa-solid fa-pen-to-square"></i>&nbsp;Editar</a>`
              : conhecimentoPodeAbrir
                ? `<a href="${url}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Visualizar conhecimento"><i class="fa-solid fa-eye"></i>&nbsp;Visualizar</a>`
              : '<span class="text-muted small">Sem ação</span>'}
          </td>
        </tr>
      `;
    }

    const alertaFlashConhecimento = <?= $alertaFlashJson ?: 'null' ?>;
    if (alertaFlashConhecimento && alertaFlashConhecimento.mensagem) {
      Swal.fire({
        title: alertaFlashConhecimento.titulo || 'Conhecimento não encontrado',
        text: alertaFlashConhecimento.mensagem,
        icon: 'error',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      });
    }
  </script>
<?php $this->end(); ?>


