<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-md mb-3 text-uppercase">
        <h4 class="m-0">Consulta de Envios - API WhatsApp</h4>
      </div>
      <div class="col-md mb-3 text-end">
        <button id="btnTipoPesquisa" onclick="alternarTipoPesquisa()" class="bto bto-phsa-light" type="button">
          <i class="fa-solid fa-magnifying-glass-minus"></i>&nbsp;Pesquisa simples
        </button>
      </div>
    </div>

    <form id="formList" class="needs-validation" novalidate>
      <div class="row pesquisaSimples">
        <div class="col-md-12 mb-3">
          <div class="form-floating">
            <input 
              type="text" 
              id="conteudo" 
              name="conteudo" 
              value="<?=$_GET['conteudo']?>" 
              class="form-control input-phsa-focus input-phsa-hover" 
              placeholder="Conteúdo">
            <label for="conteudo">Conteúdo</label>
          </div>
        </div>
      </div>

      <div class="row pesquisaPersonalizada">
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <select 
              id="status" 
              name="status" 
              class="form-control input-phsa-focus input-phsa-hover">
              <option value="">Sem preferência</option>
              <option value="1">Sucesso</option>
              <option value="0">Erro</option>
            </select>
            <label for="status">Status do Envio</label>
          </div>
        </div>
      </div>

      <div class="row pesquisaPersonalizada">
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <select 
              id="campoFiltro" 
              name="campoFiltro" 
              class="form-control input-phsa-focus input-phsa-hover">
              <option value="">Sem preferência</option>
              <option value="wpi_dtcad">Data de Envio</option>
              <option value="wpi_dtalter">Última Atualização</option>
            </select>
            <label for="campoFiltro">Filtrar por</label>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <input 
              type="date" 
              id="iniFiltro" 
              name="iniFiltro" 
              class="form-control input-phsa-focus input-phsa-hover">
            <label for="iniFiltro">Início</label>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="form-floating">
            <input 
              type="date" 
              id="fimFiltro" 
              name="fimFiltro" 
              class="form-control input-phsa-focus input-phsa-hover">
            <label for="fimFiltro">Fim</label>
          </div>
        </div>
      </div>

      <div class="row pesquisaPersonalizada">
        <div class="col-md-12 mb-3 text-end">
          <button id="btnPesquisar" class="bto bto-phsa" type="submit">
            <i class="fas fa-search"></i>&nbsp;Pesquisar
          </button>
        </div>
      </div>
    </form>

    <section id="resultadoList">
      <div class="row">
        <div class="col-sm-6 mb-2 text-muted text-uppercase small"><?=$getApiwhatsappInfo->total?> Envio(s) registrado(s)</div>
        <div id="totalList" class="col-sm-6 mb-2 text-muted text-uppercase small text-end"><?=$getApiwhatsappInfo->total?> envio(s) encontrado(s)</div>
      </div>

      <div class="row">
        <div class="col-md-12 mb-3 table-responsive">
          <table id="tableList" class="table table-striped table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Mensagem</th>
                <th>Cadastrado</th>
                <th>Atualizado</th>
                <th>Status</th>
                <th class="text-end">Ação</th>
              </tr>
            </thead>
            <tbody id="tbodyList">
              <?php if ($getApiwhatsappInfo->total > 0): ?>
                <?php foreach ($getApiwhatsappInfo->data as $envio): ?>
                  <tr>
                    <td><?= $envio->wpi_id ?></td>
                    <td><?= $envio->wpi_number ?></td>
                    <td><?= mb_strimwidth($envio->wpi_message, 0, 50, "...") ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($envio->wpi_dtcad)) ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($envio->wpi_dtalter)) ?></td>
                    <td>
                      <span class="badge bg-<?= $envio->wpi_status ? 'success' : 'danger' ?>">
                        <?= $envio->wpi_status ? 'Sucesso' : 'Erro' ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <?php if ($envio->wpi_status == 0): ?>
                        <button 
                          onclick="reenviarMensagem(
                            <?= $envio->wpi_id ?>, 
                            '<?= Helpers::adicionaMascaraContato($envio->wpi_number) ?>', 
                            `<?= addslashes($envio->wpi_message) ?>`
                          )" 
                          class="bto bto-phsa-light">
                          <i class="fa-solid fa-rotate"></i>
                        </button>
                      <?php endif; ?>

                      <button 
                        onclick="visualizarLog(
                          <?= $envio->wpi_id ?>, 
                          '<?= Helpers::adicionaMascaraContato($envio->wpi_number) ?>', 
                          `<?= addslashes($envio->wpi_message) ?>`, 
                          `<?= addslashes($envio->wpi_log) ?>`
                        )" 
                        class="bto bto-phsa-light">
                        <i class="fa-solid fa-eye"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="10" class="text-center text-muted">
                    <i class="fas fa-exclamation-circle"></i>&nbsp;Nenhum envio encontrado.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <div class="row">
      <div class="col-md-12 mb-3 text-end">
        <button onclick="fgbDatatable('tableList')" class="bto bto-phsa-light bto-phsa-hover" type="button">
          <i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar tabela
        </button>
        <button onclick="fgbConverterTabelaEmXlsx('tableList', 'pesquisaEnviosAPI')" class="bto bto-phsa-light bto-phsa-hover" type="button">
          <i class="fas fa-file-excel"></i>&nbsp;Exportar para Excel
        </button>
        <button onclick="fgbGerarImpressao('resultadoList')" class="bto bto-phsa-light bto-phsa-hover" type="button">
          <i class="fas fa-print"></i>&nbsp;Imprimir Resultado
        </button>
      </div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      $('.pesquisaPersonalizada').hide();

      $('#tipoFiltro').change(function () {
        const tipo = $(this).val();
        const campoFiltro = $('#campoFiltro');
        const iniFiltro = $('#iniFiltro');
        const fimFiltro = $('#fimFiltro');

        if (tipo === '') {
          campoFiltro.prop('disabled', true).val('');
          iniFiltro.prop('disabled', true).val('');
          fimFiltro.prop('disabled', true).val('');
        } else {
          campoFiltro.prop('disabled', false);
          const primeiraOpcao = campoFiltro.find('option:not([value=""])').first().val();
          if (campoFiltro.val() === '') {
            campoFiltro.val(primeiraOpcao);
          }
          campoFiltro.find('option[value=""]').prop('hidden', true);

          if (tipo === 'Exatidao') {
            iniFiltro.prop('disabled', false);
            fimFiltro.prop('disabled', true).val('');
          } else if (tipo === 'Periodo') {
            iniFiltro.prop('disabled', false);
            fimFiltro.prop('disabled', false);
          }
        }
      });

      $('#campoFiltro').change(function () {
        const campo = $(this).val();
        const iniFiltro = $('#iniFiltro');
        const fimFiltro = $('#fimFiltro');

        if (campo === '') {
          iniFiltro.prop('disabled', true).val('');
          fimFiltro.prop('disabled', true).val('');
        } else {
          iniFiltro.prop('disabled', false);
          if ($('#tipoFiltro').val() === 'Exatidao') {
            fimFiltro.prop('disabled', true).val('');
          } else if ($('#tipoFiltro').val() === 'Periodo') {
            fimFiltro.prop('disabled', false);
          }
        }
      });

      $('#campoOrder').change(function () {
        const campoOrder = $(this).val();
        const orderBy = $('#orderBy');

        if (campoOrder === '') {
          orderBy.prop('disabled', true).val('ASC');
        } else {
          orderBy.prop('disabled', false);
        }
      });

      $('#iniFiltro').change(function () {
        if ($('#campoFiltro').val() !== '') {
          $(this).prop('required', true);
        }
        $('#fimFiltro').attr('min', $(this).val());
      });

      $('#fimFiltro').change(function () {
        if ($('#tipoFiltro').val() === 'Periodo') {
          $(this).prop('required', true);
        }
      });

      $('#formList').submit(function (event) {
        event.preventDefault();
        if ($('.pesquisaPersonalizada').is(':visible')) {
          pesquisarApiWhatsapp($(this).serialize());
        }
      });

      $('#conteudo').on('input', function () {
        if ( $('.pesquisaSimples').is(':visible') ) {
          pesquisarApiWhatsapp({ conteudo: $(this).val() });
        }
      });

      $('#conteudo').trigger('input');
    });

    function alternarTipoPesquisa() {
      if ( $('.pesquisaSimples').is(':visible') ) {
        $('.pesquisaSimples').hide();
        $('.pesquisaPersonalizada').show();
        $('#conteudo').val('');
        $('#formList')[0].reset();
        $('#btnTipoPesquisa').html('<i class="fa-solid fa-magnifying-glass-plus"></i>&nbsp;Pesquisa personalizada');
        $('#status').focus();
      } else {
        $('.pesquisaPersonalizada').hide();
        $('.pesquisaSimples').show();
        $('#formList')[0].reset();
        $('#btnTipoPesquisa').html('<i class="fa-solid fa-magnifying-glass-minus"></i>&nbsp;Pesquisa simples');
        $('#conteudo').focus();
      }
    }

    function pesquisarApiWhatsapp(data) {
      $.ajax({
        url: '<?= Helpers::url("api_whatsapp/pesquisarEnvio") ?>',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function (response) {
          $('#totalList').html(`<span class="text-muted small">${response.total} envio(s) encontrado(s)</span>`);
          gerarTbodyList(response);
        }
      });
    }

    function gerarTbodyList(data) {
      $('#tbodyList').empty();
      let html = '';

      if (data.status) {
        if (data.total > 0) {
          data.data.forEach((envio) => {
          html += `
            <tr>
              <td>${envio.wpi_id}</td>
              <td>
                ${envio.wpi_number_mask}
                <button onclick="window.open('https://wa.me/${envio.wpi_number}', '_blank')" class="btn btn-sm text-phsa border-phsa-hover-light">
                  <i class="fa-brands fa-whatsapp"></i>
                </button>
                <button onclick="fgbCopiarParaAreaDeTransferencia('${envio.wpi_number}', 'Número de WhatsApp')" class="btn btn-sm text-phsa border-phsa-hover-light">
                  <i class="fa-solid fa-copy"></i>
                </button>
              </td>
              <td>${envio.wpi_message.substring(0, 50)}...</td>
              <td>${envio.wpi_dtcad_br}</td>
              <td>${envio.wpi_dtalter_br}</td>
              <td>
                <span class="badge bg-${envio.wpi_status ? 'success' : 'danger'}">
                  ${envio.wpi_status ? 'Sucesso' : 'Erro'}
                </span>
              </td>
              <td class="text-end">
                ${envio.wpi_status == 0 
                  ? `<button onclick="reenviarMensagem(
                        ${envio.wpi_id}, 
                        '${envio.wpi_number_mask}', 
                        \`${envio.wpi_message.replace(/`/g, "\\`")}\`
                      )" class="bto bto-phsa-light">
                       <i class="fa-solid fa-rotate"></i>
                     </button>`
                  : ''
                }

                <button onclick="visualizarLog(
                      ${envio.wpi_id}, 
                      '${envio.wpi_number_mask}', 
                      \`${envio.wpi_message.replace(/`/g, "\\`")}\`, 
                      \`${envio.wpi_log.replace(/`/g, "\\`")}\`
                    )" class="bto bto-phsa-light">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </td>
            </tr>`;
          });
        } else {
          html = `
            <tr class="table-warning">
              <td colspan="7" class="text-center">
                Nenhum envio encontrado.
              </td>
            </tr>`;
        }
      } else {
        html = `
          <tr class="table-danger">
            <td colspan="7" class="text-center">
              ${data.text}<br>${data.errorInfo ? data.errorInfo[2] : ''}
            </td>
          </tr>`;
      }

      $('#tbodyList').html(html);
    }

    function reenviarMensagem(wpi_id, wpi_number_mask, wpi_message) {
      Swal.fire({
        html: `
          <h5>Deseja reenviar esta mensagem<br>para <strong>${wpi_number_mask}</strong>?</h5>
          <div class="alert alert-secondary text-start">
            <i class="fa-solid fa-comment-dots"></i> <strong>Mensagem:</strong>
            <blockquote class="mt-2 mb-0" style="font-style: italic; border-left: 4px solid #0d6efd; padding-left: 10px;">
              ${wpi_message}
            </blockquote>
          </div>
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Sim',
        cancelButtonText: '<i class="fas fa-times"></i> Não',
        customClass: {
          confirmButton: "bto bto-phsa",
          cancelButton: "bto bto-phsa-light"
        },
        reverseButtons: true,
        allowOutsideClick: false
      }).then((result) => {
        if (result.isConfirmed) {
          swAguarde({ title: "Reenviando...", html: "<b>Por favor, aguarde...</b>" });

          $.ajax({
            url: '<?= Helpers::url("api_whatsapp/reenviarMensagem") ?>/' + wpi_id,
            method: 'POST',
            dataType: 'json',
            success: function(response) {
              swResponse(response);
            },
            error: function(xhr, status, error) {
              console.error("Erro ao reenviar mensagem:", error);
              swError("Erro ao tentar reenviar a mensagem. Por favor, tente novamente.");
            }
          });
        }
      });
    }

    function visualizarLog(wpi_id, wpi_number, wpi_message, wpi_log) {
      Swal.fire({
        title: `Registro #${wpi_id}`,
        html: `
          <div class="text-start">
            <strong>Número:</strong> ${wpi_number} <br><br>
            <strong>Mensagem:</strong>
            <blockquote class="mt-2 mb-2" style="font-style: italic; border-left: 4px solid #0d6efd; padding-left: 10px;">
              ${wpi_message}
            </blockquote>
            <strong>Log:</strong>
            <pre class="mt-2 p-2 border rounded bg-light" style="white-space: pre-wrap; max-height: 200px; overflow-y: auto;">
              ${wpi_log}
            </pre>
          </div>
        `,
        icon: "info",
        showConfirmButton: true,
        confirmButtonText: '<i class="fa-solid fa-times"></i> Fechar',
        customClass: {
          confirmButton: "bto bto-phsa"
        }
      });
    }
  </script>
<?php $this->end(); ?>