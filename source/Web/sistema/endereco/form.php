





<?php if (is_null($enderecoConfig) || !$enderecoConfig->status): ?>
  <div class="alert alert-danger" role="alert">
    <strong>Erro!</strong>
    <?= $enderecoConfig->text ?? 'Configuração de endereço não informada.' ?>
  </div>
<?php else: ?>
  <div id="<?= $enderecoConfig->componente_id ?>" class="phsaEnderecoComponente">
    <div class="row">
      <div class="col-md mb-3">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <h5 class="text-phsa mb-1">
              <?= $enderecoConfig->titulo ?>
            </h5>

            <div class="small text-muted">
              <?= $enderecoConfig->subtitulo ?>
            </div>

            <div class="small text-muted mt-1">
              <?= $enderecoConfig->total ?> endereço<?= $enderecoConfig->total == 1 ? '' : 's' ?> cadastrado<?= $enderecoConfig->total == 1 ? '' : 's' ?>

              <?php if ($enderecoConfig->limite > 0): ?>
                de <?= $enderecoConfig->limite ?> permitido<?= $enderecoConfig->limite == 1 ? '' : 's' ?>
              <?php else: ?>
                · limite ilimitado
              <?php endif; ?>
            </div>
          </div>

          <div>
            <button type="button" class="bto bto-phsa-light btn2CancelarEndereco">
              <i class="fas fa-times"></i>&nbsp;Cancelar
            </button>

            <?php if ($enderecoConfig->pode_criar): ?>
              <button type="button" class="bto bto-phsa-dark btnNovoEndereco">
                <i class="fa fa-plus"></i>&nbsp;Novo endereço
              </button>
            <?php elseif (!$enderecoConfig->permitir_criar): ?>
              <button type="button" class="bto bto-phsa-light" disabled title="Você não possui permissão para criar endereço">
                <i class="fa fa-lock"></i>&nbsp;Novo endereço
              </button>
            <?php else: ?>
              <button type="button" class="bto bto-phsa-light" disabled title="Limite de endereços atingido">
                <i class="fa fa-ban"></i>&nbsp;Limite atingido
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <?php if ($enderecoConfig->obrigatorio && $enderecoConfig->total == 0): ?>
      <div class="alert alert-warning">
        <strong>Atenção!</strong> Este cadastro exige pelo menos um endereço.
      </div>
    <?php endif; ?>

    <?php if ($enderecoConfig->atingiu_limite && $enderecoConfig->limite > 0): ?>
      <div class="alert alert-info">
        <strong>Limite atingido.</strong>
        O plano atual permite <?= $enderecoConfig->limite ?> endereço<?= $enderecoConfig->limite == 1 ? '' : 's' ?> para este cadastro.
      </div>
    <?php endif; ?>

    <div class="row listEndereco">
      <div class="col-md mb-3 table-responsive">
        <table class="table table-hover table-striped align-middle">
          <thead>
            <tr>
              <th>Nome</th>
              <th>CEP</th>
              <th>Logradouro</th>
              <th>Bairro</th>
              <th>Cidade</th>
              <th>UF</th>
              <th>Principal</th>
              <th>Status</th>
              <th class="text-end">Ação</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($enderecoConfig->total > 0): ?>
              <?php foreach ($enderecoConfig->data as $endereco): ?>
                <tr>
                  <td><?= empty($endereco->end_nome) ? 'Não informado' : $endereco->end_nome ?></td>
                  <td><?= empty($endereco->end_cep) ? '-' : Helpers::adicionaMascaraCEP($endereco->end_cep) ?></td>

                  <td>
                    <?= empty($endereco->end_logradouro) ? '-' : $endereco->end_logradouro ?>

                    <?php if (!empty($endereco->end_numero)): ?>
                      , <?= $endereco->end_numero ?>
                    <?php endif; ?>

                    <?php if (!empty($endereco->end_complemento)): ?>
                      <br>
                      <small class="text-secondary"><?= $endereco->end_complemento ?></small>
                    <?php endif; ?>

                    <?php if (!empty($endereco->end_referencia)): ?>
                      <br>
                      <small class="text-secondary"><?= $endereco->end_referencia ?></small>
                    <?php endif; ?>
                  </td>

                  <td><?= empty($endereco->end_bairro) ? '-' : $endereco->end_bairro ?></td>
                  <td><?= empty($endereco->end_cidade) ? '-' : $endereco->end_cidade ?></td>
                  <td title="<?= $endereco->end_estado ?>"><?= empty($endereco->end_uf) ? '-' : $endereco->end_uf ?></td>

                  <td><?= ((int) $endereco->end_principal === 1) ? 'Sim' : 'Não' ?></td>

                  <td>
                    <span class="text-<?= $endereco->end_statusclass ?>">
                      <?= $endereco->end_status ?>
                    </span>
                  </td>

                  <td class="text-end">
                    <?php if ($enderecoConfig->permitir_editar): ?>
                      <button type="button" class="bto btn-sm bto-phsa-light btnEditarEndereco" data-end-id="<?= $endereco->end_id ?>">
                        <i class="fa fa-edit"></i>
                      </button>
                    <?php else: ?>
                      <button type="button" class="bto btn-sm bto-phsa-light" disabled title="Você não possui permissão para editar endereço">
                        <i class="fa fa-lock"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center text-muted">
                  <i class="fa fa-exclamation-circle"></i>&nbsp;Nenhum endereço cadastrado.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row divEnderecoForm" style="display: none;">
      <div class="col-md mb-3">
        <form class="formEndereco needs-validation" novalidate autocomplete="off">
          <input class="end_id" name="end_id" hidden>
          <input name="end_origem" value="<?= $enderecoConfig->origem ?>" hidden>
          <input name="end_idorigem" value="<?= $enderecoConfig->idorigem ?>" hidden>
          <input name="end_idorganizacao" value="<?= $enderecoConfig->idorganizacao ?>" hidden>
          <input name="end_idempresa" value="<?= $enderecoConfig->idempresa ?>" hidden>

          <div class="row">
            <div class="col-md mb-3">
              <div class="form-floating">
                <input
                  name="end_nome"
                  class="form-control input-phsa-focus input-phsa-hover end_nome"
                  placeholder="Nome para identificação"
                >
                <label>Nome de identificação</label>
              </div>

              <div class="small mt-1">
                <a href="javascript:void(0)" class="nolink text-phsa btnLimparEndereco">
                  <i class="fa-solid fa-broom"></i>&nbsp;Limpar endereço
                </a>
              </div>
            </div>

            <div class="col-md mb-3">
              <div class="form-floating">
                <input
                  name="end_cep"
                  class="form-control input-phsa-focus input-phsa-hover end_cep"
                  placeholder="CEP"
                  required
                >
                <label>CEP</label>
                <div class="invalid-feedback">Campo obrigatório</div>
              </div>

              <div class="small text-muted mt-1">
                Digite somente números.
              </div>
            </div>

            <div class="col-md mb-3">
              <div class="form-floating">
                <select
                  name="end_status"
                  class="form-select input-phsa-focus input-phsa-hover end_status"
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
            <div class="col-md-9 mb-3">
              <div class="form-floating">
                <input
                  name="end_logradouro"
                  class="form-control input-phsa-focus input-phsa-hover inputViaCepReadOnly end_logradouro"
                  placeholder="Logradouro"
                  readonly
                >
                <label>Logradouro</label>
              </div>
            </div>

            <div class="col-md-3 mb-3">
              <div class="form-floating">
                <input
                  name="end_numero"
                  class="form-control input-phsa-focus input-phsa-hover end_numero"
                  placeholder="Número"
                >
                <label>Número</label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md mb-3">
              <div class="form-floating">
                <input
                  name="end_complemento"
                  class="form-control input-phsa-focus input-phsa-hover end_complemento"
                  placeholder="Complemento"
                >
                <label>Complemento</label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md mb-3">
              <div class="form-floating">
                <input
                  name="end_bairro"
                  class="form-control input-phsa-focus input-phsa-hover inputViaCepReadOnly end_bairro"
                  placeholder="Bairro"
                  readonly
                >
                <label>Bairro</label>
              </div>
            </div>

            <div class="col-md mb-3">
              <div class="form-floating">
                <input
                  name="end_cidade"
                  class="form-control input-phsa-focus input-phsa-hover inputViaCepReadOnly end_cidade"
                  placeholder="Cidade"
                  readonly
                >
                <label>Cidade</label>
              </div>
            </div>

            <div class="col-md mb-3">
              <div class="form-floating">
                <input
                  name="end_uf"
                  class="form-control input-phsa-focus input-phsa-hover inputViaCepReadOnly end_uf"
                  placeholder="UF"
                  maxlength="2"
                  readonly
                >
                <label>UF</label>
              </div>

              <div class="small text-muted mt-1">
                <span class="end_estado"></span>
              </div>
            </div>

            <input name="end_estado" class="end_estado_input inputViaCepReadOnly" hidden readonly>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="form-floating">
                <input
                  name="end_referencia"
                  class="form-control input-phsa-focus input-phsa-hover end_referencia"
                  placeholder="Referência"
                >
                <label>Referência</label>
              </div>
            </div>
          </div>

          <?php if ($enderecoConfig->permitir_principal): ?>
            <div class="row">
              <div class="col-md-12 mb-3">
                <div class="form-check form-switch">
                  <input
                    id="<?= $enderecoConfig->componente_id ?>_end_principal"
                    name="end_principal"
                    class="form-check-input end_principal"
                    type="checkbox"
                    value="1"
                  >
                  <label class="form-check-label" for="<?= $enderecoConfig->componente_id ?>_end_principal">
                    Tornar este o endereço principal para o <?= $enderecoConfig->nomeModulo ?>
                  </label>
                </div>
              </div>
            </div>
          <?php else: ?>
            <input name="end_principal" value="1" hidden>
          <?php endif; ?>

          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="divGooglemaps form-floating" hidden>
                <textarea
                  name="end_googlemaps"
                  class="form-control input-phsa-focus input-phsa-hover end_googlemaps"
                  placeholder="Google Maps"
                  style="height: 120px;"
                ></textarea>
                <label>Google Maps</label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <button type="button" class="bto bto-phsa-light btnCancelarEndereco">
                    <i class="fas fa-times"></i>&nbsp;Cancelar
                  </button>

                  <button type="button" class="bto bto-phsa-light btnVerGoogleMaps">
                    <i class="fas fa-map-marker-alt"></i>&nbsp;Google Maps
                  </button>

                  <?php if ($enderecoConfig->permitir_excluir): ?>
                    <button type="button" class="bto bto-phsa-light btnDeleteEnd" hidden>
                      <i class="fas fa-trash"></i>&nbsp;Excluir
                    </button>
                  <?php endif; ?>
                </div>

                <div>
                  <?php if ($enderecoConfig->permitir_criar || $enderecoConfig->permitir_editar): ?>
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

          <div class="row divPreviewGooglemaps mt-3" style="display: none;">
            <div class="col-md-12 mb-3">
              <div class="card border-phsa-light box-phsa-shadow-light">
                <div class="card-body">
                  <h6 class="text-phsa mb-3">
                    <i class="fa-solid fa-map-location-dot"></i>&nbsp;Pré-visualização do Google Maps
                  </h6>

                  <div class="previewGooglemaps ratio ratio-16x9"></div>
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
        $('.btn2CancelarEndereco').hide();

        const box = $('#<?= $enderecoConfig->componente_id ?>');
        const recarregarAoSalvar = <?= $enderecoConfig->recarregar_ao_salvar ? 'true' : 'false' ?>;

        function getFormEndereco() {
          return box.find('.formEndereco');
        }

        function limparCamposEndereco() {
          box.find('.end_id').val('');
          box.find('.end_nome').val('');
          box.find('.end_cep').val('');
          box.find('.end_status').val('Ativo');
          box.find('.end_logradouro').val('');
          box.find('.end_numero').val('');
          box.find('.end_complemento').val('');
          box.find('.end_bairro').val('');
          box.find('.end_cidade').val('');
          box.find('.end_uf').val('');
          box.find('.end_estado').text('');
          box.find('.end_estado_input').val('');
          box.find('.end_referencia').val('');
          box.find('.end_googlemaps').val('');
          box.find('.end_principal').prop('checked', false);
          box.find('.btnDeleteEnd').attr('hidden', true).removeData('end-id');
          box.find('.btnVerGoogleMaps').show();
          box.find('.divGooglemaps').attr('hidden', true);
          box.find('.divPreviewGooglemaps').hide();
          box.find('.previewGooglemaps').html('');
          box.find('.inputViaCepReadOnly').prop('readonly', true);
        }

        function limparFormEndereco() {
          const form = getFormEndereco();

          form[0].reset();
          form.removeClass('was-validated');
          limparCamposEndereco();
        }

        function alternarFormEndereco() {
          box.find('.btnNovoEndereco').toggle();
          box.find('.listEndereco').toggle();
          box.find('.divEnderecoForm').toggle();
          getFormEndereco().removeClass('was-validated');
        }

        function aplicarPreviewGooglemaps(valor) {
          const html = String(valor || '').trim();

          if (html === '') {
            box.find('.divPreviewGooglemaps').hide();
            box.find('.previewGooglemaps').html('');
            return;
          }

          box.find('.previewGooglemaps').html(html);
          box.find('.divPreviewGooglemaps').show();
        }

        box.find('.end_cep').mask('99999-999');

        box.on('blur', '.end_cep', function () {
          const cep = $(this).val().replace(/\D/g, '');

          if (cep.length !== 8) {
            return;
          }

          $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function (data) {
            if (!data.erro) {
              box.find('.end_logradouro').val(data.logradouro);
              box.find('.end_bairro').val(data.bairro);
              box.find('.end_cidade').val(data.localidade);
              box.find('.end_uf').val(data.uf);
              box.find('.end_estado').text(data.estado || '');
              box.find('.end_estado_input').val(data.estado || '');
              return;
            }

            swError('CEP não encontrado.');
          }).fail(function () {
            swError('Erro ao buscar o CEP. Tente novamente.');
          });
        });

        box.on('click', '.btnLimparEndereco', function () {
          limparCamposEndereco();
        });

        box.on('click', '.btnVerGoogleMaps', function () {
          $(this).hide();
          box.find('.divGooglemaps').removeAttr('hidden');
          box.find('.end_googlemaps').focus();
        });

        box.on('input', '.end_googlemaps', function () {
          aplicarPreviewGooglemaps($(this).val());
        });

        box.on('click', '.btnNovoEndereco', function () {
          limparFormEndereco();
          alternarFormEndereco();
          $('.btn2CancelarEndereco').show();
        });

        box.on('click', '.btnCancelarEndereco', function () {
          limparFormEndereco();
          alternarFormEndereco();
          $('.btn2CancelarEndereco').hide();
        });

        box.on('click', '.btn2CancelarEndereco', function () {
          limparFormEndereco();
          alternarFormEndereco();
          $('.btn2CancelarEndereco').hide();
        });

        box.on('click', '.btnEditarEndereco', function () {
          const endId = parseInt($(this).data('end-id'));

          limparFormEndereco();

          $('.btn2CancelarEndereco').show();

          $.ajax({
            url: '<?= Helpers::url("endereco/getEndereco") ?>/' + endId,
            method: 'POST',
            dataType: 'json',
            success: function (response) {
              if (!response || !response.end_id) {
                swError('Endereço não encontrado.');
                return;
              }

              alternarFormEndereco();

              box.find('.btnDeleteEnd')
                .removeAttr('hidden')
                .data('end-id', response.end_id);

              box.find('.end_id').val(response.end_id);
              box.find('.end_nome').val(response.end_nome);
              box.find('.end_cep').val(response.end_cep);
              box.find('.end_status').val(response.end_status || 'Ativo');
              box.find('.end_logradouro').val(response.end_logradouro);
              box.find('.end_numero').val(response.end_numero);
              box.find('.end_complemento').val(response.end_complemento);
              box.find('.end_bairro').val(response.end_bairro);
              box.find('.end_cidade').val(response.end_cidade);
              box.find('.end_uf').val(response.end_uf);
              box.find('.end_estado').text(response.end_estado || '');
              box.find('.end_estado_input').val(response.end_estado || '');
              box.find('.end_referencia').val(response.end_referencia);
              box.find('.end_googlemaps').val(response.end_googlemaps);
              box.find('.end_principal').prop('checked', parseInt(response.end_principal) === 1);

              box.find('.end_cep').unmask().mask('99999-999');

              if (response.end_googlemaps) {
                aplicarPreviewGooglemaps(response.end_googlemaps);
              }
            },
            error: function () {
              swError('Erro ao buscar endereço.');
            }
          });
        });

        box.on('submit', '.formEndereco', function (event) {
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
            title: 'Salvando endereço...',
            text: 'Aguarde enquanto processamos os dados.',
            timer: null
          });

          $.ajax({
            url: '<?= Helpers::url("endereco/salvarEndereco") ?>',
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

              swError('Não foi possível salvar o endereço.');
            }
          });
        });

        box.on('click', '.btnDeleteEnd', function () {
          const endId = parseInt($(this).data('end-id'));

          if (!endId) {
            swError('Endereço não identificado.');
            return;
          }

          Swal.fire({
            title: 'Atenção',
            text: 'Deseja excluir este endereço?',
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
              title: 'Excluindo endereço...',
              text: 'Aguarde enquanto processamos os dados.',
              timer: null
            });

            $.ajax({
              url: '<?= Helpers::url("endereco/deleteEndereco") ?>/' + endId,
              method: 'POST',
              dataType: 'json',
              data: {
                end_idorigem: '<?= $enderecoConfig->idorigem ?>',
                end_origem: '<?= $enderecoConfig->origem ?>',
                end_idorganizacao: '<?= $enderecoConfig->idorganizacao ?>',
                end_idempresa: '<?= $enderecoConfig->idempresa ?>'
              },
              success: function (response) {
                swResponse(response).then(function () {
                  if (response.status) {
                    location.reload();
                  }
                });
              },
              error: function () {
                swError('Não foi possível excluir o endereço.');
              }
            });
          });
        });
      });
    </script>
  <?php $this->end(); ?>
<?php endif; ?>
