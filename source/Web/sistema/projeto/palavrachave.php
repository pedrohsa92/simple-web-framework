<?php
  $palavrasChaves = $palavrasChaves ?? [];
  $catalogoPalavrasChaves = $catalogoPalavrasChaves ?? [];
  $podeAtualizarPalavrasChaves = !empty($podeAtualizarPalavrasChaves);
  $catalogoJson = json_encode($catalogoPalavrasChaves, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
  $palavrasChavesJson = json_encode($palavrasChaves, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<div class="card border shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <h5 class="m-0 text-phsa">Palavras-chave</h5>
        <div class="small text-muted mt-1">Cadastre os campos que poderão ser usados na montagem de mensagens do sistema.</div>
      </div>

      <?php if ($podeAtualizarPalavrasChaves): ?>
        <button type="button" id="btnNovaPalavraChave" class="bto bto-phsa">
          <i class="fa-solid fa-plus"></i>&nbsp;Nova palavra-chave
        </button>
      <?php endif; ?>
    </div>

    <form id="formPesquisaPalavraChave" class="mb-4" autocomplete="off">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-10">
          <div class="form-floating">
            <input id="pesquisaPalavraChave" name="pesquisa" type="search" class="form-control input-phsa-focus input-phsa-hover" placeholder="Pesquisar">
            <label for="pesquisaPalavraChave">Pesquisar grupo, palavra, campo ou descrição</label>
          </div>
        </div>
        <div class="col-12 col-lg-2 d-grid">
          <button type="submit" class="bto bto-phsa-light">
            <i class="fa-solid fa-magnifying-glass"></i>&nbsp;Pesquisar
          </button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tabelaPalavrasChavesProjeto">
        <thead>
          <tr>
            <th>Grupo</th>
            <th>Palavra-chave</th>
            <th>Campo</th>
            <th>Descrição</th>
            <th>Status</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody id="tbodyPalavrasChavesProjeto"></tbody>
      </table>
    </div>
  </div>
</div>

<?php $this->push('script'); ?>
  <script>
    $(function () {
      const catalogoPalavrasChaves = <?= $catalogoJson ?: '{}' ?>;
      let palavrasChavesProjeto = <?= $palavrasChavesJson ?: '[]' ?>;
      const podeAtualizarPalavrasChaves = <?= $podeAtualizarPalavrasChaves ? 'true' : 'false' ?>;

      function escapar(valor) {
        return $('<div>').text(valor ?? '').html();
      }

      function grupoLabel(grupo) {
        return catalogoPalavrasChaves[grupo]?.label || grupo || '-';
      }

      function campoConfig(grupo, chave) {
        return catalogoPalavrasChaves[grupo]?.colunas?.[chave] || null;
      }

      function renderizarTabela() {
        const tbody = $('#tbodyPalavrasChavesProjeto');

        if (!palavrasChavesProjeto.length) {
          tbody.html('<tr><td colspan="6" class="text-center text-muted py-4">Nenhuma palavra-chave encontrada.</td></tr>');
          return;
        }

        tbody.html(palavrasChavesProjeto.map(function (item) {
          const statusClass = item.pch_statusclass || (item.pch_status === 'Ativo' ? 'success' : 'danger');
          const acoes = podeAtualizarPalavrasChaves
            ? `<button type="button" class="bto btn-sm bto-phsa-light btnEditarPalavraChave me-1" data-id="${Number(item.pch_id)}" title="Editar"><i class="fa-solid fa-pen-to-square"></i></button>
               <button type="button" class="bto btn-sm bto-phsa-light btnExcluirPalavraChave" data-id="${Number(item.pch_id)}" title="Excluir"><i class="fa-solid fa-trash"></i></button>`
            : '<span class="text-muted">-</span>';

          return `<tr>
            <td>${escapar(grupoLabel(item.pch_grupo))}</td>
            <td><span class="text-phsa fw-semibold">{${escapar(item.pch_palavra)}}</span></td>
            <td><code>${escapar(item.pch_chave)}</code></td>
            <td>${escapar(item.pch_descricao || '-')}</td>
            <td><span class="badge bg-${escapar(statusClass)}">${escapar(item.pch_status || '-')}</span></td>
            <td class="text-end text-nowrap">${acoes}</td>
          </tr>`;
        }).join(''));
      }

      function abrirModalPalavraChave(item = null) {
        if (!podeAtualizarPalavrasChaves || typeof SimpleModal === 'undefined') return;

        const grupoAtual = item?.pch_grupo || Object.keys(catalogoPalavrasChaves)[0] || '';
        const chaveAtual = item?.pch_chave || '';
        const modalId = SimpleModal.mostrar({
          titulo: item ? 'Editar palavra-chave' : 'Nova palavra-chave',
          tamanho: 'modal-lg',
          corpo: `<form id="formPalavraChaveProjeto" class="needs-validation" novalidate>
            <input type="hidden" name="pch_id" value="${Number(item?.pch_id || 0)}">
            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <div class="form-floating">
                  <select id="pch_grupo" name="pch_grupo" class="form-select" required></select>
                  <label for="pch_grupo">Grupo</label>
                  <div class="invalid-feedback">Selecione o grupo.</div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="form-floating">
                  <select id="pch_chave" name="pch_chave" class="form-select" required></select>
                  <label for="pch_chave">Campo</label>
                  <div class="invalid-feedback">Selecione o campo.</div>
                </div>
              </div>
              <div class="col-12">
                <div class="form-floating">
                  <input id="pch_palavra" type="text" class="form-control" readonly>
                  <label for="pch_palavra">Palavra-chave</label>
                </div>
              </div>
              <div class="col-12">
                <div class="form-floating">
                  <input id="pch_descricao" name="pch_descricao" type="text" class="form-control" maxlength="255" value="${escapar(item?.pch_descricao || '')}" required>
                  <label for="pch_descricao">Descrição</label>
                  <div class="invalid-feedback">Informe a descrição.</div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="form-floating">
                  <select id="pch_status" name="pch_status" class="form-select" required>
                    <option value="Ativo" ${item?.pch_status !== 'Inativo' ? 'selected' : ''}>Ativo</option>
                    <option value="Inativo" ${item?.pch_status === 'Inativo' ? 'selected' : ''}>Inativo</option>
                  </select>
                  <label for="pch_status">Status</label>
                </div>
              </div>
            </div>
          </form>`,
          rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-times"></i>&nbsp;Cancelar</button><button type="submit" form="formPalavraChaveProjeto" class="bto bto-phsa"><i class="fa-solid fa-save"></i>&nbsp;Salvar</button>'
        });

        const modal = SimpleModal.modais[modalId];
        const form = modal.find('#formPalavraChaveProjeto');

        function carregarCampos(grupo, chaveSelecionada = '') {
          const colunas = catalogoPalavrasChaves[grupo]?.colunas || {};
          const select = modal.find('#pch_chave');
          select.html(Object.entries(colunas).map(function ([chave, config]) {
            return `<option value="${escapar(chave)}" ${chave === chaveSelecionada ? 'selected' : ''}>${escapar(config.descricao)} (${escapar(chave)})</option>`;
          }).join(''));
          atualizarPalavra();
        }

        function atualizarPalavra() {
          const grupo = modal.find('#pch_grupo').val();
          const chave = modal.find('#pch_chave').val();
          const config = campoConfig(grupo, chave);
          modal.find('#pch_palavra').val(config?.palavra || '');

          if (!modal.find('#pch_descricao').val() && config?.descricao) {
            modal.find('#pch_descricao').val(config.descricao);
          }
        }

        modal.find('#pch_grupo').html(Object.entries(catalogoPalavrasChaves).map(function ([grupo, config]) {
          return `<option value="${escapar(grupo)}" ${grupo === grupoAtual ? 'selected' : ''}>${escapar(config.label)}</option>`;
        }).join(''));
        carregarCampos(grupoAtual, chaveAtual);
        modal.on('change', '#pch_grupo', function () { carregarCampos($(this).val()); });
        modal.on('change', '#pch_chave', atualizarPalavra);
        modal.on('submit', '#formPalavraChaveProjeto', function (event) {
          event.preventDefault();
          event.stopPropagation();

          if (!this.checkValidity()) {
            form.addClass('was-validated');
            return;
          }

          const buttons = form.closest('.modal-content').find('button[type="submit"]');
          buttons.prop('disabled', true);
          swAguarde({ title: 'Salvando palavra-chave...', text: 'Aguarde enquanto processamos os dados.', timer: null });

          $.ajax({
            url: '<?= Helpers::url('projeto/salvarPalavraChave') ?>',
            type: 'POST',
            dataType: 'json',
            data: form.serialize(),
            success: function (response) {
              swResponse(response).then(function () {
                if (response.status) {
                  window.location.reload();
                  return;
                }
                buttons.prop('disabled', false);
              });
            },
            error: function () {
              buttons.prop('disabled', false);
              swError('Não foi possível salvar a palavra-chave.');
            }
          });
        });
      }

      $('#btnNovaPalavraChave').on('click', function () { abrirModalPalavraChave(); });
      $('#tbodyPalavrasChavesProjeto').on('click', '.btnEditarPalavraChave', function () {
        const id = Number($(this).data('id'));
        const item = palavrasChavesProjeto.find((registro) => Number(registro.pch_id) === id);
        if (item) abrirModalPalavraChave(item);
      });

      $('#tbodyPalavrasChavesProjeto').on('click', '.btnExcluirPalavraChave', function () {
        const id = Number($(this).data('id'));
        if (!id) return;

        Swal.fire({
          title: 'Atenção',
          text: 'Deseja excluir esta palavra-chave?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-trash"></i> Excluir',
          cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
          customClass: { confirmButton: 'bto bto-phsa', cancelButton: 'bto bto-phsa-light' },
          reverseButtons: true,
          allowOutsideClick: false
        }).then(function (result) {
          if (!result.isConfirmed) return;

          swAguarde({ title: 'Excluindo palavra-chave...', text: 'Aguarde enquanto processamos os dados.', timer: null });
          $.ajax({
            url: '<?= Helpers::url('projeto/excluirPalavraChave') ?>',
            type: 'POST',
            dataType: 'json',
            data: { pch_id: id },
            success: function (response) {
              swResponse(response).then(function () {
                if (response.status) window.location.reload();
              });
            },
            error: function () { swError('Não foi possível excluir a palavra-chave.'); }
          });
        });
      });

      $('#formPesquisaPalavraChave').on('submit', function (event) {
        event.preventDefault();
        const form = $(this);
        const button = form.find('button[type="submit"]');
        button.prop('disabled', true);

        $.ajax({
          url: '<?= Helpers::url('projeto/pesquisarPalavrasChaves') ?>',
          type: 'POST',
          dataType: 'json',
          data: form.serialize(),
          success: function (response) {
            if (response.status) {
              palavrasChavesProjeto = response.data || [];
              renderizarTabela();
              return;
            }
            swResponse(response);
          },
          error: function () { swError('Não foi possível pesquisar as palavras-chave.'); },
          complete: function () { button.prop('disabled', false); }
        });
      });

      renderizarTabela();
    });
  </script>
<?php $this->end(); ?>
