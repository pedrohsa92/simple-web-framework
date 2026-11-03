<?php
  $this->layout("sistema::listBase", [
    'getInfo' => $getInfo,
    'titulo' => $titulo,
    'descricao' => $descricao,
    'urlCadastrar' => $urlCadastrar,
    'urlPesquisar' => $urlPesquisar,
    'labelPesquisaSimples' => $labelPesquisaSimples,
    'labelTotal' => $labelTotal,
    'nomeArquivo' => $nomeArquivo
  ]);
?>

<?php $this->start("listBase_pesquisaPersonalizada"); ?>
  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <select
          id="usu_status"
          name="usu_status"
          class="form-control input-phsa-focus input-phsa-hover"
        >
          <option value="">Todos</option>
          <option value="Ativo">Ativo</option>
          <option value="Inativo">Inativo</option>
        </select>
        <label for="usu_status">Status</label>
      </div>
    </div>

    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <select
          id="usu_online"
          name="usu_online"
          class="form-control input-phsa-focus input-phsa-hover"
        >
          <option value="">Todos</option>
          <option value="1">Online</option>
          <option value="0">Offline</option>
        </select>
        <label for="usu_online">Situação online</label>
      </div>
    </div>
  </div>
<?php $this->end(); ?>

<?php $this->start("listBase_camposOrdenacao"); ?>
  <option value="usu_id">Número de identificação</option>
  <option value="usu_razaosocial">Nome / razão social</option>
  <option value="usu_nomefantasia">Nome fantasia</option>
  <option value="usu_cnpjcpf">CPF/CNPJ</option>
  <option value="usu_email">E-mail de acesso</option>
  <option value="usu_username">Usuário</option>
  <option value="usu_status">Status</option>
  <option value="usu_online">Online</option>
<?php $this->end(); ?>

<?php $this->start("listBase_resultado"); ?>
  <div class="table-responsive">
    <table id="tableList" class="table table-hover align-middle">
      <thead>
        <tr>
          <th class="colPrimaria">Usuário</th>
          <th class="colPrimaria">Contato</th>
          <th class="colPrimaria">Documento</th>
          <th class="colPrimaria">Empresa principal</th>
          <th class="colPrimaria">Nível</th>
          <th class="colPrimaria">Status</th>
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
    const usuarioPodeAtualizar = <?= !empty($usuarioLogado->permissoes->{'usuario.atualizar'}) ? 'true' : 'false' ?>;
    const usuarioUrlForm = '<?= Helpers::url('usuario/form') ?>';

    function renderizarLinhaListBase(item, index) {
      let usu_razaosocial = String(item.usu_razaosocial || '');
      let usu_nomefantasia = String(item.usu_nomefantasia || '');
      let empresa = item.emp_nomefantasia || item.emp_razaosocial || '';
      let email = String(item.usu_email || '');
      let contatoNumero = String(item.usu_whatsapp || item.usu_celular || '').replace(/\D/g, '');
      let contatoMascara = item.usu_whatsapp_mascara || item.usu_celular_mascara || item.usu_whatsapp || item.usu_celular || '';
      let usuDocumento = String(item.usu_cnpjcpf || '').replace(/\D/g, '');
      let empDocumento = String(item.emp_cnpjcpf || '').replace(/\D/g, '');
      let usuDocumentoMascara = item.usu_cnpjcpf_mascara || item.usu_cnpjcpf || '';
      let empDocumentoMascara = item.emp_cnpjcpf_mascara || item.emp_cnpjcpf || '';
      let niveis = '';

      if (usu_razaosocial.length > 20) {
        usu_razaosocial = usu_razaosocial.substring(0, 20) + '...';
      }

      if (usu_nomefantasia.length > 20) {
        usu_nomefantasia = usu_nomefantasia.substring(0, 20) + '...';
      }

      try {
        let arrNiveis = item.usu_niveisacesso ? JSON.parse(item.usu_niveisacesso) : [];

        if (Array.isArray(arrNiveis)) {
          niveis = arrNiveis.map(nivel => {
            if (typeof nivel === 'object' && nivel !== null) {
              return nivel.nva_nome || nivel.nva_id || '';
            }

            return nivel;
          }).filter(Boolean).join(', ');
        }
      } catch (e) {
        niveis = '';
      }

      return `
        <tr title="ID: ${parseInt(item.usu_id || 0)}-${fgbEscapeHtml(item.usu_chave || '')}" class="text-align-top text-truncate">
          <td class="colPrimaria">
            <div class="d-flex align-items-center gap-2">
              <div
                class="rounded-circle overflow-hidden border bg-light flex-shrink-0"
                style="width: 42px; height: 42px;"
              >
                <img
                  src="${CONF_URL}/${item.usu_logo || 'storage/global/semperfil.png'}"
                  alt="${fgbEscapeHtml(usu_razaosocial)}"
                  style="width: 100%; height: 100%; object-fit: cover;"
                >
              </div>

              <div class="min-w-0">
                <div class="fw-semibold text-truncate" style="max-width: 260px;">
                  ${
                    usuarioPodeAtualizar
                      ? `<a href="${usuarioUrlForm}/${parseInt(item.usu_id || 0)}" class="text-phsa" title="Editar usuário">
                          ${fgbEscapeHtml(usu_razaosocial)}
                        </a>`
                      : `${fgbEscapeHtml(usu_razaosocial)}`
                  }
                </div>

                <div class="text-muted small text-truncate" style="max-width: 260px;">
                  ${fgbEscapeHtml(usu_nomefantasia)}
                </div>
              </div>
            </div>
          </td>

          <td class="colPrimaria">
            <div class="d-flex align-items-center gap-1">
              ${
                email
                  ? `<button type="button" class="btn p-0 text-phsa" title="Copiar e-mail" onclick="fgbCopiarParaAreaDeTransferencia('${fgbEscapeHtml(email)}', 'E-mail')">
                      ${fgbEscapeHtml(email)}
                    </button>`
                  : `<span class="text-muted">Sem e-mail</span>`
              }
            </div>

            <div class="text-muted small mt-1 d-flex align-items-center gap-2">
              ${
                contatoNumero
                  ? `<button type="button" class="btn p-0 text-muted small" title="Copiar contato" onclick="fgbCopiarParaAreaDeTransferencia('${contatoNumero}', 'Contato')">
                      ${fgbEscapeHtml(contatoMascara)}
                    </button>

                    <a href="https://wa.me/55${contatoNumero}" target="_blank" class="text-success" title="Abrir WhatsApp">
                      <i class="fa-brands fa-whatsapp"></i>
                    </a>`
                  : `<span>Sem contato</span>`
              }
            </div>
          </td>

          <td class="colPrimaria">
            ${
              usuDocumento
                ? `<button type="button" class="btn p-0 text-phsa" title="Copiar documento" onclick="fgbCopiarParaAreaDeTransferencia('${usuDocumento}', 'Documento do usuário')">
                    ${fgbEscapeHtml(usuDocumentoMascara)}
                  </button>`
                : `<span class="text-muted">Sem documento</span>`
            }
          </td>

          <td class="colPrimaria">
            <div>
              ${fgbEscapeHtml(empresa)}
            </div>

            <div class="text-muted small mt-1">
              ${
                empDocumento
                  ? `<button type="button" class="btn p-0 text-muted small" title="Copiar documento da empresa" onclick="fgbCopiarParaAreaDeTransferencia('${empDocumento}', 'Documento da empresa')">
                      ${fgbEscapeHtml(empDocumentoMascara)}
                    </button>`
                  : `<span>Sem documento</span>`
              }
            </div>
          </td>

          <td class="colPrimaria">
            ${niveis ? fgbEscapeHtml(niveis) : '<span class="text-muted">Sem nível</span>'}
          </td>

          <td class="colPrimaria">
            ${
              usuarioPodeAtualizar
                ? `<button type="button" class="badge bg-${fgbEscapeHtml(item.usu_statusclass || 'secondary')} border-0 btnStatusUsuario" data-usu-id="${parseInt(item.usu_id || 0)}" data-status="${fgbEscapeHtml(item.usu_status || 'Ativo')}" data-nome="${fgbEscapeHtml(item.usu_razaosocial || '')}" title="Alterar status">
                    ${fgbEscapeHtml(item.usu_status || '')}
                  </button>`
                : `<span class="badge bg-${fgbEscapeHtml(item.usu_statusclass || 'secondary')}">
                    ${fgbEscapeHtml(item.usu_status || '')}
                  </span>`
            }
          </td>

          <td class="text-end">
            ${
              usuarioPodeAtualizar
                ? `<a href="${usuarioUrlForm}/${parseInt(item.usu_id || 0)}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Editar usuário">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>`
                : `<span class="text-muted small">Sem ação</span>`
            }
          </td>
        </tr>
      `;
    }

    $(document).off('click.statusUsuario').on('click.statusUsuario', '.btnStatusUsuario', function () {
      abrirModalStatusUsuario(
        parseInt($(this).data('usu-id') || 0),
        String($(this).data('status') || 'Ativo'),
        String($(this).data('nome') || '')
      );
    });

    function abrirModalStatusUsuario(usuId, statusAtual, nomeUsuario) {
      const novoStatus = statusAtual === 'Ativo' ? 'Inativo' : 'Ativo';
      const modalId = SimpleModal.mostrar({
        titulo: '<i class="fa-solid fa-user-pen"></i>&nbsp;Alterar status do usuário',
        corpo: `
          <form id="formStatusUsuario" class="needs-validation" novalidate>
            <input type="hidden" name="usu_id" value="${usuId}">
            <input type="hidden" name="usu_status" value="${fgbEscapeHtml(novoStatus)}">
            <div class="alert alert-light border small">
              <strong>${fgbEscapeHtml(nomeUsuario || 'Usuário')}</strong><br>
              Status atual: <span class="badge bg-secondary">${fgbEscapeHtml(statusAtual)}</span><br>
              Novo status: <span class="badge bg-${novoStatus === 'Ativo' ? 'success' : 'danger'}">${fgbEscapeHtml(novoStatus)}</span>
            </div>
            <div class="form-floating">
              <textarea id="usu_status_motivo_modal" name="usu_status_motivo" class="form-control" placeholder="Motivo" style="height: 120px;" minlength="10" ${novoStatus === 'Inativo' ? 'required' : ''}></textarea>
              <label for="usu_status_motivo_modal">Motivo ${novoStatus === 'Inativo' ? '*' : '(opcional)'}</label>
              <div class="invalid-feedback">Informe pelo menos 10 caracteres para inativar.</div>
            </div>
          </form>
        `,
        rodape: `
          <button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</button>
          <button type="button" id="btnSalvarStatusUsuario" class="bto bto-phsa"><i class="fa-solid fa-check"></i>&nbsp;Confirmar</button>
        `
      });

      $(document).off('click.salvarStatusUsuario').on('click.salvarStatusUsuario', '#btnSalvarStatusUsuario', function () {
        const form = $('#formStatusUsuario');

        if (!form[0].checkValidity()) {
          form.addClass('was-validated');
          return;
        }

        const botao = $(this);
        botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando');

        $.ajax({
          url: '<?= Helpers::url('usuario/salvarUsuario') ?>',
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
            swError('Não foi possível alterar o status do usuário.');
          }
        });
      });
    }
  </script>
<?php $this->end(); ?>
