





<?php if (is_null($contatoConfig) || !$contatoConfig->status): ?>
  <div class="alert alert-danger" role="alert">
    <strong>Erro!</strong>
    <?= $contatoConfig->text ?? 'Configuração de contato não informada.' ?>
  </div>
<?php else: ?>
  <div id="<?= $contatoConfig->componente_id ?>" class="phsaContatoComponente">
    <div class="row">
      <div class="col-md mb-3">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <h5 class="text-phsa mb-1">
              <?= $contatoConfig->titulo ?>
            </h5>

            <div class="small text-muted">
              <?= $contatoConfig->subtitulo ?>
            </div>

            <div class="small text-muted mt-1">
              <?= $contatoConfig->total ?> contato<?= $contatoConfig->total == 1 ? '' : 's' ?> cadastrado<?= $contatoConfig->total == 1 ? '' : 's' ?>

              <?php if ($contatoConfig->limite > 0): ?>
                de <?= $contatoConfig->limite ?> permitido<?= $contatoConfig->limite == 1 ? '' : 's' ?>
              <?php else: ?>
                · limite ilimitado
              <?php endif; ?>
            </div>
          </div>

          <div>
            <button type="button" class="bto bto-phsa-light btn2CancelarContato">
              <i class="fas fa-times"></i>&nbsp;Cancelar
            </button>

            <?php if ($contatoConfig->pode_criar): ?>
              <button type="button" class="bto bto-phsa-dark btnNovoContato">
                <i class="fa fa-plus"></i>&nbsp;Novo contato
              </button>
            <?php elseif (!$contatoConfig->permitir_criar): ?>
              <button type="button" class="bto bto-phsa-light" disabled title="Você não possui permissão para criar contato">
                <i class="fa fa-lock"></i>&nbsp;Novo contato
              </button>
            <?php else: ?>
              <button type="button" class="bto bto-phsa-light" disabled title="Limite de contatos atingido">
                <i class="fa fa-ban"></i>&nbsp;Limite atingido
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <?php if ($contatoConfig->obrigatorio && $contatoConfig->total == 0): ?>
      <div class="alert alert-warning">
        <strong>Atenção!</strong> Este cadastro exige pelo menos um contato.
      </div>
    <?php endif; ?>

    <?php if ($contatoConfig->atingiu_limite && $contatoConfig->limite > 0): ?>
      <div class="alert alert-info">
        <strong>Limite atingido.</strong>
        O plano atual permite <?= $contatoConfig->limite ?> contato<?= $contatoConfig->limite == 1 ? '' : 's' ?> para este cadastro.
      </div>
    <?php endif; ?>

    <div class="row listContato">
      <div class="col-md mb-3 table-responsive">
        <table class="table table-hover table-striped align-middle">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Identificação</th>
              <th>Conteúdo</th>
              <th>Observação</th>
              <th>Relatório</th>
              <th>Ordem</th>
              <th>Status</th>
              <th class="text-end">Ação</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($contatoConfig->total > 0): ?>
              <?php foreach ($contatoConfig->data as $contato): ?>
                <tr>
                  <td>
                    <?php
                      $iconeContato = [
                        'Celular' => '<i class="fa fa-mobile-alt"></i>',
                        'Telefone' => '<i class="fa fa-phone"></i>',
                        'Email' => '<i class="fa fa-envelope"></i>',
                        'WhatsApp' => '<i class="fa-brands fa-whatsapp"></i>',
                        'Instagram' => '<i class="fa-brands fa-instagram"></i>',
                        'Facebook' => '<i class="fa-brands fa-facebook"></i>',
                        'X' => '<i class="fa-brands fa-x-twitter"></i>',
                        'LinkedIn' => '<i class="fa-brands fa-linkedin"></i>',
                        'Site' => '<i class="fa fa-globe"></i>',
                        'Outro' => '<i class="fa fa-question-circle"></i>'
                      ];

                      echo $iconeContato[$contato->ctt_tipo] ?? '<i class="fa fa-question-circle"></i>';
                    ?>
                    &nbsp;<?= $contato->ctt_tipo ?>
                  </td>

                  <td><?= empty($contato->ctt_identificacao) ? '-' : $contato->ctt_identificacao ?></td>

                  <td>
                    <?php if (in_array($contato->ctt_tipo, ['Celular', 'Telefone', 'WhatsApp'], true)): ?>
                      <?= Helpers::adicionaMascaraContato($contato->ctt_conteudo ?? '') ?>
                    <?php elseif ($contato->ctt_tipo === 'Email'): ?>
                      <a href="mailto:<?= $contato->ctt_conteudo ?>" class="nolink text-phsa">
                        <?= $contato->ctt_conteudo ?>
                      </a>
                    <?php elseif ($contato->ctt_tipo === 'Site'): ?>
                      <a href="<?= $contato->ctt_conteudo ?>" target="_blank" class="nolink text-phsa">
                        <?= $contato->ctt_conteudo ?>
                      </a>
                    <?php else: ?>
                      <?= empty($contato->ctt_conteudo) ? '-' : $contato->ctt_conteudo ?>
                    <?php endif; ?>
                  </td>

                  <td><?= empty($contato->ctt_observacao) ? '-' : $contato->ctt_observacao ?></td>

                  <td><?= ((int) $contato->ctt_exibiremrelatorio === 1) ? 'Sim' : 'Não' ?></td>
                  <td><?= (int) ($contato->ctt_ordem ?? 0) ?></td>

                  <td>
                    <span class="text-<?= $contato->ctt_statusclass ?>">
                      <?= $contato->ctt_status ?>
                    </span>
                  </td>

                  <td class="text-end">
                    <?php if ($contatoConfig->permitir_editar): ?>
                      <button type="button" class="bto btn-sm bto-phsa-light btnEditarContato" data-ctt-id="<?= $contato->ctt_id ?>">
                        <i class="fa fa-edit"></i>
                      </button>
                    <?php else: ?>
                      <button type="button" class="bto btn-sm bto-phsa-light" disabled title="Você não possui permissão para editar contato">
                        <i class="fa fa-lock"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted">
                  <i class="fa fa-exclamation-circle"></i>&nbsp;Nenhum contato cadastrado.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row divContatoForm" style="display: none;">
      <div class="col-md mb-3">
        <form class="formContato needs-validation" novalidate autocomplete="off">
          <input class="ctt_id" name="ctt_id" hidden>
          <input name="ctt_origem" value="<?= $contatoConfig->origem ?>" hidden>
          <input name="ctt_idorigem" value="<?= $contatoConfig->idorigem ?>" hidden>
          <input name="ctt_idorganizacao" value="<?= $contatoConfig->idorganizacao ?>" hidden>
          <input name="ctt_idempresa" value="<?= $contatoConfig->idempresa ?>" hidden>

          <div class="row">
            <div class="col-md mb-3">
              <div class="form-floating">
                <select
                  name="ctt_tipo"
                  class="form-select input-phsa-focus input-phsa-hover ctt_tipo"
                  required
                >
                  <option value="Celular">Celular</option>
                  <option value="Telefone">Telefone</option>
                  <option value="Email">E-mail</option>
                  <option value="WhatsApp">WhatsApp</option>
                  <option value="Instagram">Instagram</option>
                  <option value="Facebook">Facebook</option>
                  <option value="X">X</option>
                  <option value="LinkedIn">LinkedIn</option>
                  <option value="Site">Site</option>
                  <option value="Outro">Outro</option>
                </select>
                <label>Tipo de contato</label>
                <div class="invalid-feedback">Campo obrigatório</div>
              </div>

              <div class="small mt-1">
                <a href="javascript:void(0)" class="nolink text-phsa btnLimparContato">
                  <i class="fa-solid fa-broom"></i>&nbsp;Limpar contato
                </a>
              </div>
            </div>

            <div class="col-md mb-3">
              <div class="form-floating">
                <input
                  name="ctt_identificacao"
                  class="form-control input-phsa-focus input-phsa-hover ctt_identificacao"
                  placeholder="Identificação"
                >
                <label>Identificação</label>
              </div>

              <div class="small text-muted mt-1">
                Ex: Financeiro, Comercial, Suporte, Responsável, Portaria.
              </div>
            </div>

            <div class="col-md mb-3">
              <div class="form-floating">
                <select
                  name="ctt_status"
                  class="form-select input-phsa-focus input-phsa-hover ctt_status"
                  required
                >
                  <option value="Ativo">Ativo</option>
                  <option value="Inativo">Inativo</option>
                </select>
                <label>Status</label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8 mb-3">
              <div class="form-floating">
                <input
                  name="ctt_conteudo"
                  class="form-control input-phsa-focus input-phsa-hover ctt_conteudo"
                  placeholder="Conteúdo"
                  required
                >
                <label class="labelCttConteudo">Conteúdo do contato</label>
                <div class="invalid-feedback">Campo obrigatório</div>
              </div>

              <div class="small text-muted mt-1 textoAjudaCttConteudo">
                Informe o conteúdo do contato.
              </div>
            </div>

            <div class="col-md-4 mb-3">
              <div class="form-floating">
                <input
                  name="ctt_ordem"
                  type="number"
                  class="form-control input-phsa-focus input-phsa-hover ctt_ordem"
                  placeholder="Ordem"
                  value="0"
                >
                <label>Ordem</label>
              </div>

              <div class="small text-muted mt-1">
                Menores números aparecem primeiro.
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="form-floating">
                <textarea
                  name="ctt_observacao"
                  class="form-control input-phsa-focus input-phsa-hover ctt_observacao"
                  placeholder="Observação"
                  style="height: 100px;"
                ></textarea>
                <label>Observação</label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="form-check form-switch">
                <input
                  id="<?= $contatoConfig->componente_id ?>_ctt_exibiremrelatorio"
                  name="ctt_exibiremrelatorio"
                  class="form-check-input ctt_exibiremrelatorio"
                  type="checkbox"
                  value="1"
                >
                <label class="form-check-label" for="<?= $contatoConfig->componente_id ?>_ctt_exibiremrelatorio">
                  Exibir este contato em relatórios do <?= $contatoConfig->nomeModulo ?>
                </label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <button type="button" class="bto bto-phsa-light btnCancelarContato">
                    <i class="fas fa-times"></i>&nbsp;Cancelar
                  </button>

                  <?php if ($contatoConfig->permitir_excluir): ?>
                    <button type="button" class="bto bto-phsa-light btnDeleteCtt" hidden>
                      <i class="fas fa-trash"></i>&nbsp;Excluir
                    </button>
                  <?php endif; ?>
                </div>

                <div>
                  <?php if ($contatoConfig->permitir_criar || $contatoConfig->permitir_editar): ?>
                    <button class="bto bto-phsa" type="submit">
                      <i class="fas fa-save"></i>&nbsp;Salvar
                    </button>
                  <?php else: ?>
                    <button class="bto bto-phsa-light" type="button" disabled>
                      <i class="fa fa-lock"></i>&nbsp;Sem permissão
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php $this->push('script'); ?>
    <script type="text/javascript">
      $(document).ready(function () {
        $('.btn2CancelarContato').hide();

        const box = $('#<?= $contatoConfig->componente_id ?>');
        const recarregarAoSalvar = <?= $contatoConfig->recarregar_ao_salvar ? 'true' : 'false' ?>;

        function getFormContato() {
          return box.find('.formContato');
        }

        function getMascaraContato(tipo) {
          if (tipo === 'Celular' || tipo === 'WhatsApp') {
            return '(99) 9 9999-9999';
          }

          if (tipo === 'Telefone') {
            return '(99) 9999-9999';
          }

          return null;
        }

        function aplicarComportamentoTipoContato() {
          const tipo = box.find('.ctt_tipo').val();
          const input = box.find('.ctt_conteudo');

          input.unmask();
          input.attr('type', 'text');
          input.attr('placeholder', 'Conteúdo');

          box.find('.labelCttConteudo').text('Conteúdo do contato');
          box.find('.textoAjudaCttConteudo').text('Informe o conteúdo do contato.');

          if (tipo === 'Celular') {
            input.mask(getMascaraContato(tipo));
            input.attr('placeholder', 'Celular');
            box.find('.labelCttConteudo').text('Celular');
            box.find('.textoAjudaCttConteudo').text('Digite o celular com DDD.');
            return;
          }

          if (tipo === 'WhatsApp') {
            input.mask(getMascaraContato(tipo));
            input.attr('placeholder', 'WhatsApp');
            box.find('.labelCttConteudo').text('WhatsApp');
            box.find('.textoAjudaCttConteudo').text('Digite o WhatsApp com DDD.');
            return;
          }

          if (tipo === 'Telefone') {
            input.mask(getMascaraContato(tipo));
            input.attr('placeholder', 'Telefone');
            box.find('.labelCttConteudo').text('Telefone');
            box.find('.textoAjudaCttConteudo').text('Digite o telefone com DDD.');
            return;
          }

          if (tipo === 'Email') {
            input.attr('type', 'email');
            input.attr('placeholder', 'E-mail');
            box.find('.labelCttConteudo').text('E-mail');
            box.find('.textoAjudaCttConteudo').text('Informe um e-mail válido.');
            return;
          }

          if (tipo === 'Site') {
            input.attr('type', 'url');
            input.attr('placeholder', 'URL');
            box.find('.labelCttConteudo').text('URL');
            box.find('.textoAjudaCttConteudo').text('Informe uma URL completa, incluindo https://.');
            return;
          }

          if (['Instagram', 'Facebook', 'X', 'LinkedIn'].includes(tipo)) {
            input.attr('placeholder', 'Perfil ou URL');
            box.find('.labelCttConteudo').text('Perfil ou URL');
            box.find('.textoAjudaCttConteudo').text('Informe o perfil, usuário ou URL da rede social.');
          }
        }

        function limparCamposContato() {
          box.find('.ctt_id').val('');
          box.find('.ctt_tipo').val('Celular');
          box.find('.ctt_identificacao').val('');
          box.find('.ctt_conteudo').val('');
          box.find('.ctt_status').val('Ativo');
          box.find('.ctt_observacao').val('');
          box.find('.ctt_exibiremrelatorio').prop('checked', false);
          box.find('.ctt_ordem').val('0');
          box.find('.btnDeleteCtt').attr('hidden', true).removeData('ctt-id');
          aplicarComportamentoTipoContato();
        }

        function limparFormContato() {
          const form = getFormContato();

          form[0].reset();
          form.removeClass('was-validated');
          limparCamposContato();
        }

        function alternarFormContato() {
          box.find('.btnNovoContato').toggle();
          box.find('.listContato').toggle();
          box.find('.divContatoForm').toggle();
          getFormContato().removeClass('was-validated');
        }

        box.on('change', '.ctt_tipo', function () {
          aplicarComportamentoTipoContato();
        });

        box.on('click', '.btnLimparContato', function () {
          limparCamposContato();
        });

        box.on('click', '.btnNovoContato', function () {
          limparFormContato();
          alternarFormContato();
          $('.btn2CancelarContato').show();
        });

        box.on('click', '.btnCancelarContato', function () {
          limparFormContato();
          alternarFormContato();
          $('.btn2CancelarContato').hide();
        });

        box.on('click', '.btn2CancelarContato', function () {
          limparFormContato();
          alternarFormContato();
          $('.btn2CancelarContato').hide();
        });

        box.on('click', '.btnEditarContato', function () {
          const cttId = parseInt($(this).data('ctt-id'));

          limparFormContato();

          $('.btn2CancelarContato').show();

          $.ajax({
            url: '<?= Helpers::url("contato/getContato") ?>/' + cttId,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
              if (!response || !response.ctt_id) {
                swError('Contato não encontrado.');
                return;
              }

              alternarFormContato();

              box.find('.btnDeleteCtt')
                .removeAttr('hidden')
                .data('ctt-id', response.ctt_id);

              box.find('.ctt_id').val(response.ctt_id);
              box.find('.ctt_tipo').val(response.ctt_tipo || 'Outro');
              box.find('.ctt_identificacao').val(response.ctt_identificacao);
              box.find('.ctt_status').val(response.ctt_status || 'Ativo');
              box.find('.ctt_conteudo').val(response.ctt_conteudo);
              box.find('.ctt_observacao').val(response.ctt_observacao);
              box.find('.ctt_exibiremrelatorio').prop('checked', parseInt(response.ctt_exibiremrelatorio) === 1);
              box.find('.ctt_ordem').val(response.ctt_ordem || 0);

              aplicarComportamentoTipoContato();
            },
            error: function () {
              swError('Erro ao buscar contato.');
            }
          });
        });

        box.on('submit', '.formContato', function (event) {
          event.preventDefault();
          event.stopPropagation();

          const form = $(this);
          const buttons = form.find('button[type="submit"], input[type="submit"]');

          if (form.data('enviando') === true) {
            return;
          }

          if (!this.checkValidity()) {
            form.addClass('was-validated');
            return;
          }

          form.data('enviando', true);
          buttons.attr('disabled', true);

          swAguarde({
            title: 'Salvando contato...',
            text: 'Aguarde enquanto processamos os dados.',
            timer: null
          });

          $.ajax({
            url: '<?= Helpers::url("contato/salvarContato") ?>',
            type: 'POST',
            dataType: 'json',
            data: form.serialize(),
            success: function (response) {
              swResponse(response).then(function () {
                if (response.status && recarregarAoSalvar) {
                  setTimeout(function(){
                    location.reload();
                    return;
                  }, 2000)
                }

                form.data('enviando', false);
                buttons.attr('disabled', false);
              });
            },
            error: function () {
              form.data('enviando', false);
              buttons.attr('disabled', false);

              swError('Não foi possível salvar o contato.');
            }
          });
        });

        box.on('click', '.btnDeleteCtt', function () {
          const cttId = parseInt($(this).data('ctt-id'));

          if (!cttId) {
            swError('Contato não identificado.');
            return;
          }

          Swal.fire({
            title: 'Atenção',
            text: 'Deseja excluir este contato?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Excluir',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            customClass: {
              confirmButton: 'bto bto-phsa',
              cancelButton: 'bto bto-phsa-light'
            },
            reverseButtons: true,
            allowOutsideClick: false,
          }).then((result) => {
            if (!result.isConfirmed) {
              return;
            }

            swAguarde({
              title: 'Excluindo contato...',
              text: 'Aguarde enquanto processamos os dados.',
              timer: null
            });

            $.ajax({
              url: '<?= Helpers::url("contato/deleteContato") ?>/' + cttId,
              method: 'POST',
              dataType: 'json',
              data: {
                ctt_idorigem: '<?= $contatoConfig->idorigem ?>',
                ctt_origem: '<?= $contatoConfig->origem ?>',
                ctt_idorganizacao: '<?= $contatoConfig->idorganizacao ?>',
                ctt_idempresa: '<?= $contatoConfig->idempresa ?>'
              },
              success: function (response) {
                swResponse(response).then(function () {
                  if (response.status) {
                    location.reload();
                  }
                });
              },
              error: function () {
                swError('Não foi possível excluir o contato.');
              }
            });
          });
        });

        aplicarComportamentoTipoContato();
      });
    </script>
  <?php $this->end(); ?>
<?php endif; ?>
