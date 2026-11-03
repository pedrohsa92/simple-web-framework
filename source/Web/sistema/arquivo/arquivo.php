





<?php if (is_null($arquivoConfig) || !$arquivoConfig->status): ?>
  <div class="alert alert-danger" role="alert">
    <strong>Erro!</strong>
    <?= $arquivoConfig->text ?? 'Configuração de arquivo não informada.' ?>
  </div>
<?php else: ?>
  <div id="<?= $arquivoConfig->componente_id ?>" class="phsaArquivoComponente">
    <div class="row">
      <div class="col-md mb-3">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <h5 class="text-phsa mb-1">
              <?= $arquivoConfig->titulo ?>
            </h5>

            <div class="small text-muted">
              <?= $arquivoConfig->subtitulo ?>
            </div>

            <div class="small text-muted mt-1">
              <?= $arquivoConfig->total ?> arquivo<?= $arquivoConfig->total == 1 ? '' : 's' ?> cadastrado<?= $arquivoConfig->total == 1 ? '' : 's' ?>

              <?php if ($arquivoConfig->limite > 0): ?>
                de <?= $arquivoConfig->limite ?> permitido<?= $arquivoConfig->limite == 1 ? '' : 's' ?>
              <?php else: ?>
                · limite ilimitado
              <?php endif; ?>
            </div>
          </div>

          <?php if ($arquivoConfig->pode_criar): ?>
            <button type="button" class="bto bto-phsa-dark btnNovoArquivo">
              <i class="fa-solid fa-upload"></i>&nbsp;Enviar arquivo
            </button>
          <?php elseif (!$arquivoConfig->permitir_criar): ?>
            <button type="button" class="bto bto-phsa-light" disabled title="Você não possui permissão para enviar arquivo">
              <i class="fa fa-lock"></i>&nbsp;Enviar arquivo
            </button>
          <?php else: ?>
            <button type="button" class="bto bto-phsa-light" disabled title="Limite de arquivos atingido">
              <i class="fa fa-ban"></i>&nbsp;Limite atingido
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($arquivoConfig->obrigatorio && $arquivoConfig->total == 0): ?>
      <div class="alert alert-warning">
        <strong>Atenção!</strong> Este cadastro exige pelo menos um arquivo.
      </div>
    <?php endif; ?>

    <?php if ($arquivoConfig->atingiu_limite && $arquivoConfig->limite > 0): ?>
      <div class="alert alert-info">
        <strong>Limite atingido.</strong>
        O plano atual permite <?= $arquivoConfig->limite ?> arquivo<?= $arquivoConfig->limite == 1 ? '' : 's' ?> para este cadastro.
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-12 mb-3">
        <form class="formArquivo" hidden>
          <input type="hidden" name="arq_id" class="arq_id">
          <input type="hidden" name="arq_origem" value="<?= $arquivoConfig->origem ?>">
          <input type="hidden" name="arq_idorigem" value="<?= $arquivoConfig->idorigem ?>">
          <input type="hidden" name="arq_idorganizacao" value="<?= $arquivoConfig->idorganizacao ?>">
          <input type="hidden" name="arq_idempresa" value="<?= $arquivoConfig->idempresa ?>">
          <input type="hidden" name="arq_status" value="Ativo">
          <input type="hidden" name="arq_nome" class="arq_nome">
          <input type="hidden" name="arq_nomeoriginal" class="arq_nomeoriginal">
          <input type="hidden" name="arq_descricao" class="arq_descricao">
          <input type="hidden" name="arq_tipo" class="arq_tipo">
          <textarea name="arq_base64" class="arq_base64"></textarea>
        </form>

        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle mb-0 small">
            <thead>
              <tr>
                <th style="width: 48px;"></th>
                <th>Arquivo</th>
                <th>Tipo</th>
                <th>Tamanho</th>
                <th>Status</th>
                <th style="width: 150px;">Cadastro</th>
                <th class="text-end" style="width: 165px;">Ação</th>
              </tr>
            </thead>

            <tbody>
              <?php if ($arquivoConfig->total > 0): ?>
                <?php foreach ($arquivoConfig->data as $arquivo): ?>
                  <tr>
                    <td>
                      <?php if (str_starts_with((string) $arquivo->arq_mime, 'image/')): ?>
                        <img
                          src="<?= Helpers::url($arquivo->arq_caminho) ?>"
                          alt="<?= $arquivo->arq_nome ?>"
                          class="rounded"
                          style="width: 38px; height: 38px; object-fit: cover;"
                        >
                      <?php elseif (strtolower((string) $arquivo->arq_extensao) == 'pdf'): ?>
                        <img
                          src="<?= Helpers::url('storage/global/pdf.png') ?>"
                          alt="PDF"
                          class="rounded"
                          style="width: 38px; height: 38px; object-fit: contain;"
                        >
                      <?php elseif (in_array(strtolower((string) $arquivo->arq_extensao), ['xls', 'xlsx'])): ?>
                        <img
                          src="<?= Helpers::url('storage/global/xls.png') ?>"
                          alt="Planilha"
                          class="rounded"
                          style="width: 38px; height: 38px; object-fit: contain;"
                        >
                      <?php else: ?>
                        <span class="d-inline-flex align-items-center justify-content-center rounded border" style="width: 38px; height: 38px;">
                          <i class="fa-regular fa-file"></i>
                        </span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <div>
                        <a
                          href="javascript:void(0)"
                          class="text-phsa text-decoration-none btnVisualizarArquivo"
                          title="Abrir visualização"
                          data-arq-id="<?= $arquivo->arq_id ?>"
                          data-arq-nome="<?= htmlspecialchars($arquivo->arq_nome ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-nomeoriginal="<?= htmlspecialchars($arquivo->arq_nomeoriginal ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-descricao="<?= htmlspecialchars($arquivo->arq_descricao ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-status="<?= htmlspecialchars($arquivo->arq_status ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-statusclass="<?= htmlspecialchars($arquivo->arq_statusclass ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-mime="<?= htmlspecialchars($arquivo->arq_mime ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-extensao="<?= htmlspecialchars($arquivo->arq_extensao ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-tamanho="<?= htmlspecialchars(($arquivo->arq_tamanho ?? '') . ' ' . ($arquivo->arq_medida ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-caminho="<?= htmlspecialchars(Helpers::url($arquivo->arq_caminho), ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-nomearquivo="<?= htmlspecialchars($arquivo->arq_nomearquivo ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-diretorio="<?= htmlspecialchars($arquivo->arq_diretorio ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-hash="<?= htmlspecialchars($arquivo->arq_hash ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-dtcad="<?= empty($arquivo->arq_dtcad) ? '-' : date('d/m/Y H:i', strtotime($arquivo->arq_dtcad)) ?>"
                          data-arq-dtalter="<?= empty($arquivo->arq_dtalter) ? '-' : date('d/m/Y H:i', strtotime($arquivo->arq_dtalter)) ?>"
                          data-arq-log="<?= htmlspecialchars($arquivo->arq_log ?? '[]', ENT_QUOTES, 'UTF-8') ?>"
                        >
                          <?= empty($arquivo->arq_nome) ? $arquivo->arq_nomeoriginal : $arquivo->arq_nome ?>
                        </a>
                      </div>

                      <?php if (!empty($arquivo->arq_descricao)): ?>
                        <div class="small text-muted">
                          <?= $arquivo->arq_descricao ?>
                        </div>
                      <?php endif; ?>

                      <div class="small text-muted">
                        <?= $arquivo->arq_nomearquivo ?>
                      </div>
                    </td>

                    <td>
                      <div><?= empty($arquivo->arq_extensao) ? '-' : strtoupper($arquivo->arq_extensao) ?></div>
                      <div class="small text-muted"><?= empty($arquivo->arq_mime) ? '-' : $arquivo->arq_mime ?></div>
                    </td>

                    <td>
                      <?= $arquivo->arq_tamanho ?> <?= $arquivo->arq_medida ?>
                    </td>

                    <td>
                      <span class="text-<?= $arquivo->arq_statusclass ?>">
                        <?= $arquivo->arq_status ?>
                      </span>
                    </td>

                    <td>
                      <?= empty($arquivo->arq_dtcad) ? '-' : date('d/m/Y H:i', strtotime($arquivo->arq_dtcad)) ?>
                    </td>

                    <td class="text-end">
                      <div class="d-inline-flex gap-1">
                        <?php if ($arquivoConfig->permitir_excluir): ?>
                          <button
                            type="button"
                            class="bto bto-phsa-light btnDeleteArquivo btnArquivoAcao"
                            data-arq-id="<?= $arquivo->arq_id ?>"
                            title="Excluir arquivo"
                          >
                            <i class="fa fa-trash"></i>
                          </button>
                        <?php endif; ?>

                        <a
                          href="<?= Helpers::url($arquivo->arq_caminho) ?>"
                          target="_blank"
                          class="bto bto-phsa-light btnArquivoAcao"
                          title="Abrir em nova aba"
                        >
                          <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>

                        <button
                          type="button"
                          class="bto bto-phsa-light btnVisualizarArquivo btnArquivoAcao"
                          title="Abrir visualização"
                          data-arq-id="<?= $arquivo->arq_id ?>"
                          data-arq-nome="<?= htmlspecialchars($arquivo->arq_nome ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-nomeoriginal="<?= htmlspecialchars($arquivo->arq_nomeoriginal ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-descricao="<?= htmlspecialchars($arquivo->arq_descricao ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-status="<?= htmlspecialchars($arquivo->arq_status ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-statusclass="<?= htmlspecialchars($arquivo->arq_statusclass ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-mime="<?= htmlspecialchars($arquivo->arq_mime ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-extensao="<?= htmlspecialchars($arquivo->arq_extensao ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-tamanho="<?= htmlspecialchars(($arquivo->arq_tamanho ?? '') . ' ' . ($arquivo->arq_medida ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-caminho="<?= htmlspecialchars(Helpers::url($arquivo->arq_caminho), ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-nomearquivo="<?= htmlspecialchars($arquivo->arq_nomearquivo ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-diretorio="<?= htmlspecialchars($arquivo->arq_diretorio ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-hash="<?= htmlspecialchars($arquivo->arq_hash ?? '', ENT_QUOTES, 'UTF-8') ?>"
                          data-arq-dtcad="<?= empty($arquivo->arq_dtcad) ? '-' : date('d/m/Y H:i', strtotime($arquivo->arq_dtcad)) ?>"
                          data-arq-dtalter="<?= empty($arquivo->arq_dtalter) ? '-' : date('d/m/Y H:i', strtotime($arquivo->arq_dtalter)) ?>"
                          data-arq-log="<?= htmlspecialchars($arquivo->arq_log ?? '[]', ENT_QUOTES, 'UTF-8') ?>"
                        >
                          <i class="fa-solid fa-eye"></i>
                        </button>

                        <?php if ($arquivoConfig->permitir_editar): ?>
                          <button
                            type="button"
                            class="bto bto-phsa-light btnEditarArquivo btnArquivoAcao"
                            data-arq-id="<?= $arquivo->arq_id ?>"
                            data-arq-nome="<?= htmlspecialchars($arquivo->arq_nome ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-arq-descricao="<?= htmlspecialchars($arquivo->arq_descricao ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-arq-status="<?= $arquivo->arq_status ?>"
                            title="Editar informações"
                          >
                            <i class="fa fa-edit"></i>
                          </button>
                        <?php endif; ?>

                        <a
                          href="<?= Helpers::url($arquivo->arq_caminho) ?>"
                          download="<?= htmlspecialchars($arquivo->arq_nomeoriginal ?? $arquivo->arq_nomearquivo ?? 'arquivo', ENT_QUOTES, 'UTF-8') ?>"
                          class="bto bto-phsa-light btnArquivoAcao"
                          title="Baixar arquivo"
                        >
                          <i class="fa-solid fa-download"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted">
                    <i class="fa fa-exclamation-circle"></i>&nbsp;Nenhum arquivo cadastrado.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php $this->push('script'); ?>
    <script type="text/javascript">
      $(document).ready(function () {
        const box = $('#<?= $arquivoConfig->componente_id ?>');
        const limite = <?= (int) $arquivoConfig->limite ?>;
        const totalAtual = <?= (int) $arquivoConfig->total ?>;
        const recarregarAoSalvar = <?= $arquivoConfig->recarregar_ao_salvar ? 'true' : 'false' ?>;
        const iconPdf = '<?= Helpers::url("storage/global/pdf.png") ?>';
        const iconXls = '<?= Helpers::url("storage/global/xls.png") ?>';

        function getFormArquivo() {
          return box.find('.formArquivo');
        }

        function limparFormArquivo() {
          const form = getFormArquivo();

          form[0].reset();
          form.find('.arq_id').val('');
          form.find('.arq_nome').val('');
          form.find('.arq_nomeoriginal').val('');
          form.find('.arq_descricao').val('');
          form.find('.arq_tipo').val('');
          form.find('.arq_base64').val('');
          form.find('[name="arq_status"]').val('Ativo');
        }

        function getExtensaoArquivo(nome) {
          const partes = String(nome || '').split('.');
          return partes.length > 1 ? partes.pop().toLowerCase() : '';
        }

        function getMimeBase64(base64) {
          const match = String(base64 || '').match(/^data:([^;]+);base64,/);
          return match ? match[1].toLowerCase() : '';
        }

        function getTamanhoArquivo(arquivo) {
          if (arquivo.sizeText) {
            return arquivo.sizeText;
          }

          if (arquivo.sizeFormatted) {
            return arquivo.sizeFormatted;
          }

          if (arquivo.size) {
            const unidades = ['Bytes', 'KB', 'MB', 'GB'];
            let tamanho = parseFloat(arquivo.size);
            let indice = 0;

            while (tamanho >= 1024 && indice < unidades.length - 1) {
              tamanho = tamanho / 1024;
              indice++;
            }

            return tamanho.toFixed(2) + ' ' + unidades[indice];
          }

          if (arquivo.data) {
            const base64 = String(arquivo.data).split(',')[1] || '';
            const bytes = Math.round((base64.length * 3) / 4);
            const unidades = ['Bytes', 'KB', 'MB', 'GB'];
            let tamanho = bytes;
            let indice = 0;

            while (tamanho >= 1024 && indice < unidades.length - 1) {
              tamanho = tamanho / 1024;
              indice++;
            }

            return tamanho.toFixed(2) + ' ' + unidades[indice];
          }

          return '-';
        }

        function getPreviewArquivo(arquivo) {
          const nome = arquivo.name || arquivo.nome || '';
          const extensao = getExtensaoArquivo(nome);
          const mime = arquivo.mime || arquivo.type || getMimeBase64(arquivo.data);
          const isImage = arquivo.isImage || mime.startsWith('image/');

          if (isImage) {
            return `
              <img
                src="${arquivo.data}"
                class="w-100 h-100"
                style="object-fit: contain;"
                alt="${nome}"
              >
            `;
          }

          if (extensao === 'pdf' || mime === 'application/pdf') {
            return `
              <img
                src="${iconPdf}"
                class="w-100 h-100"
                style="object-fit: contain;"
                alt="PDF"
              >
            `;
          }

          if (['xls', 'xlsx'].includes(extensao)) {
            return `
              <img
                src="${iconXls}"
                class="w-100 h-100"
                style="object-fit: contain;"
                alt="Planilha"
              >
            `;
          }

          return `
            <div class="d-flex align-items-center justify-content-center w-100 h-100 text-muted">
              <i class="fa-regular fa-file fa-3x"></i>
            </div>
          `;
        }

        function getPreviewExistente(arquivo) {
          const extensao = String(arquivo.extensao || '').toLowerCase();
          const mime = String(arquivo.mime || '').toLowerCase();

          if (mime.startsWith('image/')) {
            return `
              <img
                src="${arquivo.caminho}"
                class="w-100 h-100 rounded"
                style="object-fit: contain; max-height: 70vh;"
                alt="${arquivo.nome}"
              >
            `;
          }

          if (extensao === 'pdf' || mime === 'application/pdf') {
            return `
              <iframe
                src="${arquivo.caminho}"
                class="w-100 rounded border"
                style="height: 70vh;"
              ></iframe>
            `;
          }

          if (['xls', 'xlsx'].includes(extensao)) {
            return `
              <div class="text-center py-5">
                <img src="${iconXls}" style="width: 90px; height: 90px; object-fit: contain;">
                <div class="mt-3 text-muted">Pré-visualização interna indisponível para planilhas.</div>
              </div>
            `;
          }

          return `
            <div class="text-center py-5">
              <i class="fa-regular fa-file fa-4x text-muted"></i>
              <div class="mt-3 text-muted">Pré-visualização indisponível para este tipo de arquivo.</div>
            </div>
          `;
        }

        function abrirArquivoBase64(arquivo) {
          const janela = window.open('', '_blank');

          if (!janela) {
            swWarning('Não foi possível abrir nova aba. Verifique o bloqueador de pop-up.');
            return;
          }

          const nome = arquivo.name || 'arquivo';
          const mime = arquivo.mime || arquivo.type || getMimeBase64(arquivo.data);
          const extensao = getExtensaoArquivo(nome);

          if (mime.startsWith('image/')) {
            janela.document.write(`<title>${nome}</title><img src="${arquivo.data}" style="max-width:100%;height:auto;">`);
            return;
          }

          if (mime === 'application/pdf' || extensao === 'pdf') {
            janela.document.write(`<title>${nome}</title><iframe src="${arquivo.data}" style="width:100%;height:100vh;border:0;"></iframe>`);
            return;
          }

          janela.location.href = arquivo.data;
        }

        function montarPayloadArquivo(arquivo) {
          const form = getFormArquivo();
          const nome = arquivo.name || arquivo.nome || 'arquivo';

          limparFormArquivo();

          form.find('.arq_nome').val(nome);
          form.find('.arq_nomeoriginal').val(nome);
          form.find('.arq_tipo').val((arquivo.isImage || getMimeBase64(arquivo.data).startsWith('image/')) ? 'imagem' : 'arquivo');
          form.find('.arq_base64').val(arquivo.data);

          return form.serialize();
        }

        function salvarArquivo(payload, callback) {
          $.ajax({
            url: '<?= Helpers::url("arquivo/salvarArquivo") ?>',
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function (response) {
              if (typeof callback === 'function') {
                callback(response);
                return;
              }

              swResponse(response);

              if (response.status && recarregarAoSalvar) {
                location.reload();
              }
            },
            error: function () {
              swError('Não foi possível salvar o arquivo.');
            }
          });
        }

        function renderArquivosSelecionados(arquivos, modalId) {
          const boxArquivos = $('#listaArquivosSelecionados');
          const btnSalvar = $('#btnSalvarArquivosSelecionados');

          if (arquivos.length <= 0) {
            boxArquivos.html(`
              <div class="alert alert-warning mb-0">
                Nenhum arquivo selecionado.
              </div>
            `);

            btnSalvar.prop('disabled', true);
            return;
          }

          btnSalvar.prop('disabled', false);

          let html = '<div class="row">';

          arquivos.forEach(function (arquivo, index) {
            const nome = arquivo.name || arquivo.nome || 'Arquivo';
            const extensao = getExtensaoArquivo(nome);
            const mime = arquivo.mime || arquivo.type || getMimeBase64(arquivo.data);
            const dimensao = arquivo.width && arquivo.height ? `${arquivo.width} x ${arquivo.height}` : '';

            html += `
              <div class="col-12 col-md-6 col-xl-4 mb-3" data-index="${index}">
                <div class="card h-100 border-phsa-light box-phsa-shadow-light">
                  <div class="card-body">
                    <div class="rounded bg-light border d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                      ${getPreviewArquivo(arquivo)}
                    </div>

                    <div class="small text-truncate fw-semibold" title="${nome}">
                      ${nome}
                    </div>

                    <div class="small text-muted">
                      ${extensao ? extensao.toUpperCase() : '-'} · ${getTamanhoArquivo(arquivo)}
                    </div>

                    ${dimensao ? `<div class="small text-muted">${dimensao}</div>` : ''}
                    ${mime ? `<div class="small text-muted text-truncate" title="${mime}">${mime}</div>` : ''}

                    <div class="d-flex gap-2 mt-3">
                      <button type="button" class="bto btn-sm bto-phsa-light btnAbrirTentativaArquivo" data-index="${index}" title="Abrir em nova aba">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                      </button>

                      <button type="button" class="bto btn-sm bto-phsa-light btnRemoverTentativaArquivo" data-index="${index}" title="Remover da tentativa de upload">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            `;
          });

          html += '</div>';

          boxArquivos.html(html);
        }

        function abrirModalUploadArquivos(arquivos) {
          const modalId = SimpleModal.mostrar({
            titulo: '<i class="fa-solid fa-file-circle-check"></i> Conferir arquivos',
            tamanho: 'modal-xl modal-dialog-scrollable',
            corpo: `
              <div id="listaArquivosSelecionados"></div>
            `,
            rodape: `
              <button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal">
                <i class="fa-solid fa-times"></i>&nbsp;Cancelar
              </button>

              <button type="button" id="btnSalvarArquivosSelecionados" class="bto bto-phsa">
                <i class="fa-solid fa-save"></i>&nbsp;Salvar
              </button>
            `
          });

          renderArquivosSelecionados(arquivos, modalId);

          $(document).off('click.abrirTentativaArquivo').on('click.abrirTentativaArquivo', '.btnAbrirTentativaArquivo', function () {
            const index = parseInt($(this).data('index'));

            if (!arquivos[index]) {
              swError('Arquivo não encontrado.');
              return;
            }

            abrirArquivoBase64(arquivos[index]);
          });

          $(document).off('click.removerTentativaArquivo').on('click.removerTentativaArquivo', '.btnRemoverTentativaArquivo', function () {
            const index = parseInt($(this).data('index'));

            arquivos.splice(index, 1);
            renderArquivosSelecionados(arquivos, modalId);
          });

          $(document).off('click.salvarTentativaArquivo').on('click.salvarTentativaArquivo', '#btnSalvarArquivosSelecionados', function () {
            if (arquivos.length <= 0) {
              swWarning('Nenhum arquivo selecionado para salvar.');
              return;
            }

            const btn = $(this);
            let index = 0;

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>&nbsp;Salvando');

            swAguarde({
              title: 'Salvando arquivos...',
              text: 'Aguarde enquanto processamos os dados.',
              timer: null
            });

            function enviarProximo() {
              if (index >= arquivos.length) {
                SimpleModal.fechar(modalId);

                if (recarregarAoSalvar) {
                  location.reload();
                }

                return;
              }

              const arquivo = arquivos[index];

              salvarArquivo(montarPayloadArquivo(arquivo), function (response) {
                if (!response.status) {
                  btn.prop('disabled', false).html('<i class="fa-solid fa-save"></i>&nbsp;Salvar');
                  swResponse(response);
                  return;
                }

                index++;
                enviarProximo();
              });
            }

            enviarProximo();
          });

          $('#' + modalId).on('hidden.bs.modal', function () {
            $(document).off('click.abrirTentativaArquivo');
            $(document).off('click.removerTentativaArquivo');
            $(document).off('click.salvarTentativaArquivo');
          });
        }

        box.on('click', '.btnNovoArquivo', function () {
          fgbSelecionarArquivo({
            customAccept: '<?= $arquivoConfig->accept ?>',
            replaceDefault: true,
            multiple: <?= $arquivoConfig->multiple ? 'true' : 'false' ?>,
            onFileSelect: function (arquivos) {
              if (!Array.isArray(arquivos)) {
                arquivos = [arquivos];
              }

              arquivos = arquivos.filter(function (arquivo) {
                if (!arquivo.status) {
                  swError(arquivo.text || 'Arquivo inválido.');
                  return false;
                }

                return true;
              });

              if (arquivos.length <= 0) {
                return;
              }

              if (limite > 0 && (totalAtual + arquivos.length) > limite) {
                swWarning('A quantidade selecionada ultrapassa o limite permitido pelo plano.');
                return;
              }

              abrirModalUploadArquivos(arquivos);
            }
          });
        });

        function renderLogArquivo(log) {
          let logs = [];

          if (Array.isArray(log)) {
            logs = log;
          } else if (typeof log === 'object' && log !== null) {
            logs = [log];
          } else {
            try {
              logs = JSON.parse(log || '[]');
            } catch (e) {
              logs = [];
            }
          }

          if (!Array.isArray(logs) || logs.length <= 0) {
            return `
              <div class="small text-muted">
                Nenhum log registrado.
              </div>
            `;
          }

          let html = `
            <div class="table-responsive">
              <table class="table table-hover align-middle small mb-0">
                <thead>
                  <tr>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Data</th>
                  </tr>
                </thead>
                <tbody>
          `;

          logs.forEach(function (item) {
            html += `
              <tr>
                <td>${item.usu_razaosocial || '-'}</td>
                <td>${item.acao || '-'}</td>
                <td>${item.data || '-'}</td>
              </tr>
            `;
          });

          html += `
                </tbody>
              </table>
            </div>
          `;

          return html;
        }

        box.on('click', '.btnVisualizarArquivo', function () {
          const arquivo = {
            id: $(this).data('arq-id'),
            nome: String($(this).data('arq-nome') || ''),
            nomeoriginal: String($(this).data('arq-nomeoriginal') || ''),
            descricao: String($(this).data('arq-descricao') || ''),
            status: String($(this).data('arq-status') || ''),
            statusclass: String($(this).data('arq-statusclass') || ''),
            mime: String($(this).data('arq-mime') || ''),
            extensao: String($(this).data('arq-extensao') || ''),
            tamanho: String($(this).data('arq-tamanho') || ''),
            caminho: String($(this).data('arq-caminho') || ''),
            nomearquivo: String($(this).data('arq-nomearquivo') || ''),
            diretorio: String($(this).data('arq-diretorio') || ''),
            hash: String($(this).data('arq-hash') || ''),
            dtcad: String($(this).data('arq-dtcad') || '-'),
            dtalter: String($(this).data('arq-dtalter') || '-'),
            log: $(this).data('arq-log') || []
          };

          SimpleModal.mostrar({
            titulo: '<i class="fa-solid fa-eye"></i> Abrir visualização',
            tamanho: 'modal-xl modal-dialog-scrollable',
            corpo: `
              <div class="row">
                <div class="col-12 col-xl-7 mb-3">
                  <div class="border rounded p-2 bg-light">
                    ${getPreviewExistente(arquivo)}
                  </div>
                </div>

                <div class="col-12 col-xl-5 mb-3">
                  <div class="card border-phsa-light box-phsa-shadow-light">
                    <div class="card-body">
                      <h6 class="text-phsa mb-3">Informações do arquivo</h6>

                      <div class="small mb-2">
                        <div class="text-muted">Nome</div>
                        <div>${arquivo.nome || arquivo.nomeoriginal || '-'}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Nome original</div>
                        <div>${arquivo.nomeoriginal || '-'}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Arquivo físico</div>
                        <div class="text-break">${arquivo.nomearquivo || '-'}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Tipo</div>
                        <div>${arquivo.extensao ? arquivo.extensao.toUpperCase() : '-'} · ${arquivo.mime || '-'}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Tamanho</div>
                        <div>${arquivo.tamanho || '-'}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Status</div>
                        <div><span class="text-${arquivo.statusclass}">${arquivo.status || '-'}</span></div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Cadastro</div>
                        <div>${arquivo.dtcad}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Última alteração</div>
                        <div>${arquivo.dtalter}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Diretório</div>
                        <div class="text-break">${arquivo.caminho || '-'}</div>
                      </div>

                      <div class="small mb-2">
                        <div class="text-muted">Hash</div>
                        <div class="text-break">${arquivo.hash || '-'}</div>
                      </div>

                      ${arquivo.descricao ? `
                        <div class="small mb-2">
                          <div class="text-muted">Descrição</div>
                          <div>${arquivo.descricao}</div>
                        </div>
                      ` : ''}

                      <div class="small mt-3">
                        <div class="text-muted mb-1">Log</div>
                        <div class="border rounded bg-light p-2" style="max-height: 220px; overflow:auto;">
                          ${renderLogArquivo(arquivo.log)}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `,
            rodape: `
              <a href="${arquivo.caminho}" target="_blank" class="bto bto-phsa-light">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>&nbsp;Abrir em nova aba
              </a>

              <button type="button" class="bto bto-phsa" data-bs-dismiss="modal">
                <i class="fa-solid fa-check"></i>&nbsp;Ok
              </button>
            `
          });
        });

        box.on('click', '.btnEditarArquivo', function () {
          const arqId = parseInt($(this).data('arq-id'));
          const arqNome = String($(this).data('arq-nome') || '');
          const arqDescricao = String($(this).data('arq-descricao') || '');
          const arqStatus = String($(this).data('arq-status') || 'Ativo');

          const modalId = SimpleModal.mostrar({
            titulo: '<i class="fa-solid fa-file-pen"></i> Editar arquivo',
            tamanho: 'modal-lg',
            corpo: `
              <form id="formEditarArquivo" class="needs-validation" novalidate>
                <input name="arq_id" value="${arqId}" hidden>
                <input name="arq_origem" value="<?= $arquivoConfig->origem ?>" hidden>
                <input name="arq_idorigem" value="<?= $arquivoConfig->idorigem ?>" hidden>
                <input name="arq_idorganizacao" value="<?= $arquivoConfig->idorganizacao ?>" hidden>
                <input name="arq_idempresa" value="<?= $arquivoConfig->idempresa ?>" hidden>

                <div class="row">
                  <div class="col-md-8 mb-3">
                    <div class="form-floating">
                      <input
                        id="arq_nome_modal"
                        name="arq_nome"
                        class="form-control input-phsa-focus input-phsa-hover"
                        placeholder="Nome"
                        value="${arqNome}"
                        required
                      >
                      <label for="arq_nome_modal">Nome</label>
                      <div class="invalid-feedback">Campo obrigatório</div>
                    </div>
                  </div>

                  <div class="col-md-4 mb-3">
                    <div class="form-floating">
                      <select
                        id="arq_status_modal"
                        name="arq_status"
                        class="form-select input-phsa-focus input-phsa-hover"
                        required
                      >
                        <option value="Ativo" ${arqStatus === 'Ativo' ? 'selected' : ''}>Ativo</option>
                        <option value="Inativo" ${arqStatus === 'Inativo' ? 'selected' : ''}>Inativo</option>
                      </select>
                      <label for="arq_status_modal">Status</label>
                    </div>
                  </div>

                  <div class="col-12 mb-3">
                    <div class="form-floating">
                      <textarea
                        id="arq_descricao_modal"
                        name="arq_descricao"
                        class="form-control input-phsa-focus input-phsa-hover"
                        placeholder="Descrição"
                        style="height: 100px;"
                      >${arqDescricao}</textarea>
                      <label for="arq_descricao_modal">Descrição</label>
                    </div>
                  </div>
                </div>
              </form>
            `,
            rodape: `
              <button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal">
                <i class="fa-solid fa-times"></i>&nbsp;Cancelar
              </button>

              <button type="button" id="btnSalvarEditarArquivo" class="bto bto-phsa">
                <i class="fa-solid fa-save"></i>&nbsp;Salvar
              </button>
            `
          });

          $(document).off('click.salvarEditarArquivo').on('click.salvarEditarArquivo', '#btnSalvarEditarArquivo', function () {
            const form = $('#formEditarArquivo');

            if (!form[0].checkValidity()) {
              form.addClass('was-validated');
              return;
            }

            $('#btnSalvarEditarArquivo')
              .prop('disabled', true)
              .html('<i class="fas fa-spinner fa-spin"></i>&nbsp;Salvando');

            salvarArquivo(form.serialize(), function (response) {
              swResponse(response);

              if (!response.status) {
                $('#btnSalvarEditarArquivo')
                  .prop('disabled', false)
                  .html('<i class="fa-solid fa-save"></i>&nbsp;Salvar');

                return;
              }

              SimpleModal.fechar(modalId);

              if (recarregarAoSalvar) {
                location.reload();
              }
            });
          });

          $('#' + modalId).on('hidden.bs.modal', function () {
            $(document).off('click.salvarEditarArquivo');
          });
        });

        box.on('click', '.btnDeleteArquivo', function () {
          const arqId = parseInt($(this).data('arq-id'));

          if (!arqId) {
            swError('Arquivo não identificado.');
            return;
          }

          Swal.fire({
            title: 'Atenção',
            text: 'Deseja excluir este arquivo?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não',
            customClass: {
              confirmButton: 'bto bto-phsa',
              cancelButton: 'bto bto-phsa-hover'
            }
          }).then((result) => {
            if (!result.isConfirmed) {
              return;
            }

            swAguarde({
              title: 'Excluindo arquivo...',
              text: 'Aguarde enquanto processamos os dados.',
              timer: null
            });

            $.ajax({
              url: '<?= Helpers::url("arquivo/deleteArquivo") ?>/' + arqId,
              method: 'POST',
              dataType: 'json',
              data: {
                arq_idorigem: '<?= $arquivoConfig->idorigem ?>',
                arq_origem: '<?= $arquivoConfig->origem ?>',
                arq_idorganizacao: '<?= $arquivoConfig->idorganizacao ?>',
                arq_idempresa: '<?= $arquivoConfig->idempresa ?>'
              },
              success: function (response) {
                swResponse(response).then(function () {
                  if (response.status) {
                    location.reload();
                  }
                });
              },
              error: function () {
                swError('Não foi possível excluir o arquivo.');
              }
            });
          });
        });
      });
    </script>
  <?php $this->end(); ?>
<?php endif; ?>

<style>
  .btnArquivoAcao {
    width: 28px;
    height: 28px;
    padding: 0;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
</style>