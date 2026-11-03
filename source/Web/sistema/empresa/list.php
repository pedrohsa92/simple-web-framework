<?php
  $this->layout("sistema::listBase", [
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
?>

<?php $this->start("listBase_pesquisaPersonalizada"); ?>
  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <select
          id="emp_status"
          name="emp_status"
          class="form-control input-phsa-focus input-phsa-hover"
        >
          <option value="">Todos</option>
          <option value="Ativo">Ativo</option>
          <option value="Inativo">Inativo</option>
        </select>
        <label for="emp_status">Status</label>
      </div>
    </div>

    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <select
          id="emp_tipo"
          name="emp_tipo"
          class="form-control input-phsa-focus input-phsa-hover"
        >
          <option value="">Todos</option>
          <option value="Matriz">Matriz</option>
          <option value="Filial">Filial</option>
        </select>
        <label for="emp_tipo">Tipo</label>
      </div>
    </div>
  </div>
<?php $this->end(); ?>

<?php $this->start("listBase_camposFiltroData"); ?>
  <option value="emp_dtnasc">Data de nascimento / fundação</option>
<?php $this->end(); ?>

<?php $this->start("listBase_camposOrdenacao"); ?>
  <option value="emp_id">Número de identificação</option>
  <option value="emp_razaosocial">Nome / razão social</option>
  <option value="emp_nomefantasia">Nome fantasia</option>
  <option value="emp_cnpjcpf">CPF/CNPJ</option>
  <option value="emp_tipo">Tipo</option>
  <option value="emp_status">Status</option>
  <option value="emp_dtnasc">Data de nascimento / fundação</option>
<?php $this->end(); ?>

<?php $this->start("listBase_resultado"); ?>
  <div class="table-responsive">
    <table id="tableList" class="table table-hover align-middle">
      <thead>
        <tr>
          <th class="colPrimaria">Empresa</th>
          <th class="colPrimaria">Documento</th>
          <th class="colPrimaria">Tipo</th>
          <th class="colPrimaria">Status</th>
          <th class="colSecundaria">Descrição</th>
          <th class="colSecundaria">Datas</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>

      <tbody id="tbodyListBase">
        <tr>
          <td colspan="100" class="text-center text-muted py-4">
            Aguardando pesquisa...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
<?php $this->end(); ?>

<?php $this->start("listBase_script"); ?>
  <script>
    const empresaPodeAtualizar = <?= !empty($usuarioLogado->permissoes->{'empresa.atualizar'}) ? 'true' : 'false' ?>;
    const empresaUrlForm = '<?= Helpers::url('empresa/form') ?>';

    function renderizarLinhaListBase(item, index) {
      let empId = parseInt(item.emp_id || 0);
      let razaoSocial = String(item.emp_razaosocial || '');
      let nomeFantasia = String(item.emp_nomefantasia || '');
      let documento = String(item.emp_cnpjcpf || '').replace(/\D/g, '');
      let documentoMascara = item.emp_cnpjcpf_mascara || item.emp_cnpjcpf || '';
      let tipo = String(item.emp_tipo || '');
      let status = String(item.emp_status || '');
      let statusClass = String(item.emp_statusclass || getStatusClassEmpresa(status));
      let descricao = String(item.emp_descricao || item.emp_observacao || '');
      let logo = getEmpresaLogoUrl(item.emp_logo);

      if (razaoSocial.length > 28) {
        razaoSocial = razaoSocial.substring(0, 28) + '...';
      }

      if (nomeFantasia.length > 28) {
        nomeFantasia = nomeFantasia.substring(0, 28) + '...';
      }

      return `
        <tr title="ID: ${empId}-${fgbEscapeHtml(item.emp_chave || '')}" class="text-align-top text-truncate">
          <td class="colPrimaria">
            <div class="d-flex align-items-center gap-2">
              <div
                class="rounded-circle overflow-hidden border bg-light flex-shrink-0"
                style="width: 42px; height: 42px;"
              >
                <img
                  src="${fgbEscapeHtml(logo)}"
                  alt="${fgbEscapeHtml(razaoSocial)}"
                  style="width: 100%; height: 100%; object-fit: cover;"
                >
              </div>

              <div class="min-w-0">
                <div class="fw-semibold text-truncate" style="max-width: 280px;">
                  ${
                    empresaPodeAtualizar
                      ? `<a href="${empresaUrlForm}/${empId}" class="text-phsa" title="Editar empresa">
                          ${fgbEscapeHtml(razaoSocial)}
                        </a>`
                      : `${fgbEscapeHtml(razaoSocial)}`
                  }
                </div>

                <div class="text-muted small text-truncate" style="max-width: 280px;">
                  ${nomeFantasia ? fgbEscapeHtml(nomeFantasia) : 'Sem nome fantasia'}
                </div>
              </div>
            </div>
          </td>

          <td class="colPrimaria">
            ${
              documento
                ? `<button type="button" class="btn p-0 text-phsa" title="Copiar documento" onclick="fgbCopiarParaAreaDeTransferencia('${documento}', 'Documento da empresa')">
                    ${fgbEscapeHtml(documentoMascara)}
                  </button>`
                : `<span class="text-muted">Sem documento</span>`
            }
          </td>

          <td class="colPrimaria">
            <span class="badge bg-secondary">
              ${fgbEscapeHtml(tipo || 'Não definido')}
            </span>
          </td>

          <td class="colPrimaria">
            ${
              empresaPodeAtualizar
                ? `<button type="button" class="badge bg-${fgbEscapeHtml(statusClass)} border-0 btnStatusEmpresa" data-emp-id="${empId}" data-status="${fgbEscapeHtml(status || 'Ativo')}" data-nome="${fgbEscapeHtml(razaoSocial)}" title="Alterar status">
                    ${fgbEscapeHtml(status || 'Não definido')}
                  </button>`
                : `<span class="badge bg-${fgbEscapeHtml(statusClass)}">
                    ${fgbEscapeHtml(status || 'Não definido')}
                  </span>`
            }
          </td>

          <td class="colSecundaria">
            <div class="text-muted small" style="max-width: 360px;">
              ${descricao ? fgbEscapeHtml(descricao) : 'Sem descrição'}
            </div>
          </td>

          <td class="colSecundaria">
            <div class="text-muted small">
              Cadastro: ${fgbEscapeHtml(fgbFormatarDataHoraUsaParaBr(item.emp_dtcad) || '')}<br>
              Alterado: ${fgbEscapeHtml(fgbFormatarDataHoraUsaParaBr(item.emp_dtalter) || '')}
            </div>
          </td>

          <td class="text-end">
            ${
              empresaPodeAtualizar
                ? `<a href="${empresaUrlForm}/${empId}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Editar empresa">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>`
                : `<span class="text-muted small">Sem ação</span>`
            }
          </td>
        </tr>
      `;
    }

    function getEmpresaLogoUrl(path) {
      path = String(path || '').trim();

      if (path === '') {
        path = 'storage/global/semimagem.jpg';
      }

      if (/^(https?:\/\/|data:)/i.test(path)) {
        return path;
      }

      return `${CONF_URL}/${path.replace(/^\/+/, '')}`;
    }

    function getStatusClassEmpresa(status) {
      switch (String(status || '')) {
        case 'Ativo':
          return 'success';

        case 'Inativo':
          return 'danger';

        case 'Bloqueado':
          return 'dark';

        case 'Manutencao':
          return 'warning';

        default:
          return 'secondary';
      }
    }

    $(document).off('click.statusEmpresa').on('click.statusEmpresa', '.btnStatusEmpresa', function () {
      abrirModalStatusEmpresa(
        parseInt($(this).data('emp-id') || 0),
        String($(this).data('status') || 'Ativo'),
        String($(this).data('nome') || '')
      );
    });

    function abrirModalStatusEmpresa(empId, statusAtual, nomeEmpresa) {
      const novoStatus = statusAtual === 'Ativo' ? 'Inativo' : 'Ativo';
      const modalId = SimpleModal.mostrar({
        titulo: '<i class="fa-solid fa-building"></i>&nbsp;Alterar status da igreja',
        corpo: `
          <form id="formStatusEmpresa" class="needs-validation" novalidate>
            <input type="hidden" name="emp_id" value="${empId}">
            <input type="hidden" name="emp_status" value="${fgbEscapeHtml(novoStatus)}">
            <div class="alert alert-light border small">
              <strong>${fgbEscapeHtml(nomeEmpresa || 'Igreja')}</strong><br>
              Status atual: <span class="badge bg-secondary">${fgbEscapeHtml(statusAtual)}</span><br>
              Novo status: <span class="badge bg-${novoStatus === 'Ativo' ? 'success' : 'danger'}">${fgbEscapeHtml(novoStatus)}</span>
            </div>
            <div class="form-floating">
              <textarea id="emp_status_motivo_modal" name="emp_status_motivo" class="form-control" placeholder="Motivo" style="height: 120px;" minlength="10" ${novoStatus === 'Inativo' ? 'required' : ''}></textarea>
              <label for="emp_status_motivo_modal">Motivo ${novoStatus === 'Inativo' ? '*' : '(opcional)'}</label>
              <div class="invalid-feedback">Informe pelo menos 10 caracteres para inativar.</div>
            </div>
          </form>
        `,
        rodape: `
          <button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</button>
          <button type="button" id="btnSalvarStatusEmpresa" class="bto bto-phsa"><i class="fa-solid fa-check"></i>&nbsp;Confirmar</button>
        `
      });

      $(document).off('click.salvarStatusEmpresa').on('click.salvarStatusEmpresa', '#btnSalvarStatusEmpresa', function () {
        const form = $('#formStatusEmpresa');

        if (!form[0].checkValidity()) {
          form.addClass('was-validated');
          return;
        }

        const botao = $(this);
        botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando');

        $.ajax({
          url: '<?= Helpers::url('empresa/salvarEmpresa') ?>',
          method: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function (response) {
            swResponse(response);

            if (!response.status) {
              botao.prop('disabled', false).html('<i class="fa-solid fa-check"></i>&nbsp;Confirmar');
              return;
            }

            SimpleModal.fechar(modalId);
            pesquisarListBase('personalizada');
          },
          error: function () {
            botao.prop('disabled', false).html('<i class="fa-solid fa-check"></i>&nbsp;Confirmar');
            swError('Não foi possível alterar o status da igreja.');
          }
        });
      });
    }
  </script>
<?php $this->end(); ?>
