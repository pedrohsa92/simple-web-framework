<?php $this->layout("sistema::sistema"); ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form id="formNivelAcesso" class="needs-validation" novalidate>
      <input
        type="hidden"
        id="nva_id"
        name="nva_id"
        value="<?= (int) ($nivelAcesso->nva_id ?? 0) ?>"
      >

      <input
        type="hidden"
        id="nva_statusclass"
        name="nva_statusclass"
        value="<?= $nivelAcesso->nva_statusclass ?? 'success' ?>"
      >

      <div class="row align-items-start mb-4">
        <div class="col-12">
          <h4 class="m-0">
            <?= !empty($nivelAcesso->nva_id) ? 'Editar nível de acesso' : 'Novo nível de acesso' ?>
          </h4>

          <div class="text-muted small mt-1">
            <i class="fa-solid fa-building"></i>
            <?= Helpers::limitarString($contexto->organizacao->org_nomefantasia, 50) ?>
            <span class="mx-1">/</span>
            <i class="fa-solid fa-store"></i>
            <?= Helpers::limitarString($contexto->empresa->emp_nomefantasia, 50) ?>
          </div>
        </div>
      </div>

      <?php if (!empty($nivelAcesso->nva_imutavel)): ?>
        <div class="alert alert-warning">
          <i class="fa-solid fa-lock"></i>
          Este nível de acesso é imutável e não pode ser alterado.
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12 col-lg-8 mb-3">
          <div class="form-floating">
            <input
              type="text"
              id="nva_nome"
              name="nva_nome"
              value="<?= htmlspecialchars($nivelAcesso->nva_nome ?? '', ENT_QUOTES, 'UTF-8') ?>"
              class="form-control input-phsa-focus input-phsa-hover"
              placeholder="Nome"
              maxlength="50"
              required
              <?= !empty($nivelAcesso->nva_imutavel) ? 'disabled' : '' ?>
            >
            <label for="nva_nome">Nome</label>
            <div class="invalid-feedback">Informe o nome do nível de acesso</div>
          </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
          <div class="form-floating">
            <select
              id="nva_status"
              name="nva_status"
              class="form-control input-phsa-focus input-phsa-hover"
              required
              <?= !empty($nivelAcesso->nva_imutavel) ? 'disabled' : '' ?>
            >
              <option value="Ativo" <?= ($nivelAcesso->nva_status ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
              <option value="Inativo" <?= ($nivelAcesso->nva_status ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
            <label for="nva_status">Status</label>
          </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
          <div class="form-floating">
            <select
              id="nva_escopo"
              name="nva_escopo"
              class="form-control input-phsa-focus input-phsa-hover"
              required
              <?= !empty($nivelAcesso->nva_id) || !empty($nivelAcesso->nva_imutavel) ? 'disabled' : '' ?>
            >
              <?php foreach ($escoposPermitidos as $escopo): ?>
                <option
                  value="<?= htmlspecialchars($escopo->value, ENT_QUOTES, 'UTF-8') ?>"
                  <?= (($nivelAcesso->nva_escopo ?? $escopoPadrao) === $escopo->value) ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($escopo->label, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>

            <?php if (!empty($nivelAcesso->nva_id) || !empty($nivelAcesso->nva_imutavel)): ?>
              <input type="hidden" name="nva_escopo" value="<?= htmlspecialchars($nivelAcesso->nva_escopo, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>

            <label for="nva_escopo">Escopo</label>
          </div>
        </div>

        <div class="col-12 col-lg-8 mb-3">
          <div class="form-floating">
            <input
              type="text"
              id="nva_descricao"
              name="nva_descricao"
              value="<?= htmlspecialchars($nivelAcesso->nva_descricao ?? '', ENT_QUOTES, 'UTF-8') ?>"
              class="form-control input-phsa-focus input-phsa-hover"
              placeholder="Descrição não obrigatória"
              maxlength="255"
              <?= !empty($nivelAcesso->nva_imutavel) ? 'disabled' : '' ?>
            >
            <label for="nva_descricao">Descrição não obrigatória</label>
          </div>
        </div>

        <input
          type="hidden"
          name="nva_padrao_inicial"
          value="0"
        >

        <?php if (($usuarioLogado->escopo_maximo ?? '') === 'Projeto'): ?>
          <div id="boxPadraoInicial" class="col-12 mb-3">
            <div class="border rounded p-3 h-100">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  value="1"
                  id="nva_padrao_inicial"
                  name="nva_padrao_inicial"
                  <?= (int) ($nivelAcesso->nva_padrao_inicial ?? 0) === 1 ? 'checked' : '' ?>
                  <?= !empty($nivelAcesso->nva_imutavel) ? 'disabled' : '' ?>
                >

                <label class="form-check-label fw-semibold" for="nva_padrao_inicial">
                  Nível inicial da organização
                </label>
              </div>

              <div class="text-muted small mt-1">
                Usado automaticamente no primeiro usuário criado pelo autocadastro.
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <hr>

      <div class="row align-items-center mb-3">
        <div class="col-12 col-lg mb-3 mb-lg-0">
          <h5 class="m-0">
            Permissões
          </h5>

          <div class="text-muted small">
            <span id="textoResumoPermissoes">
              Este nível de acesso possui <b><?= count($permissoesNivel) ?></b> permissões selecionadas.
            </span>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <div class="input-group">
            <span class="input-group-text">
              <i class="fa-solid fa-magnifying-glass"></i>
            </span>

            <input
              type="text"
              id="pesquisarPermissao"
              class="form-control input-phsa-focus input-phsa-hover"
              placeholder="Pesquisar permissão"
            >

            <button
              type="button"
              id="btnAlternarCollapsePermissoesInput"
              class="btn btn-outline-secondary"
              title="Abrir todos os módulos"
            >
              <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
            </button>
          </div>
        </div>
      </div>

      <div id="boxPermissoes" class="accordion">
        <?php foreach ($permissoesSistema as $modulo): ?>
          <div
            class="accordion-item mb-2 border rounded overflow-hidden itemModuloPermissao"
            data-modulo="<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
          >
            <h2 class="accordion-header" id="headingPermissao<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>">
              <button
                class="accordion-button collapsed fw-bold"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapsePermissao<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
                aria-expanded="false"
                aria-controls="collapsePermissao<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
              >
                <?= htmlspecialchars($modulo->titulo, ENT_QUOTES, 'UTF-8') ?>
              </button>
            </h2>

            <div
              id="collapsePermissao<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
              class="accordion-collapse collapse collapseModuloPermissao"
              aria-labelledby="headingPermissao<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
            >
              <div class="accordion-body">
                <?php if (!empty($modulo->selecionarTodos)): ?>
                  <div class="row align-items-center border rounded p-2 mb-2 box-phsa-shadow-light boxSelecionarTodosModulo">
                    <div class="col">
                      <label class="fw-bold text-uppercase small mb-0" for="selecionarTodos<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>">
                        Selecionar todos deste módulo
                      </label>
                    </div>

                    <div class="col-auto">
                      <div class="form-check form-switch mb-0">
                        <input
                          id="selecionarTodos<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
                          class="form-check-input selecionarTodosModulo"
                          data-modulo="<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
                          type="checkbox"
                          role="switch"
                          <?= !empty($nivelAcesso->nva_imutavel) ? 'disabled' : '' ?>
                        >
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <?php foreach ($modulo->permissoes as $permissao): ?>
                  <div
                    class="row align-items-center border border-phsa-hover rounded p-2 mb-2 itemPermissao"
                    data-permissao="<?= htmlspecialchars($permissao->per_nome, ENT_QUOTES, 'UTF-8') ?>"
                    data-descricao="<?= htmlspecialchars($permissao->per_descricao, ENT_QUOTES, 'UTF-8') ?>"
                  >
                    <div class="col">
                      <label class="mb-0" for="permissao<?= md5($permissao->per_nome) ?>">
                        <span class="d-block fw-semibold">
                          <?= htmlspecialchars($permissao->per_descricao, ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <span class="d-block text-muted small">
                          <?= htmlspecialchars($permissao->per_nome, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                      </label>
                    </div>

                    <div class="col-auto">
                      <div class="form-check form-switch mb-0">
                        <input
                          id="permissao<?= md5($permissao->per_nome) ?>"
                          name="permissoes[]"
                          value="<?= htmlspecialchars($permissao->per_nome, ENT_QUOTES, 'UTF-8') ?>"
                          class="form-check-input checkModulo check-<?= htmlspecialchars($modulo->modulo, ENT_QUOTES, 'UTF-8') ?>"
                          type="checkbox"
                          role="switch"
                          <?= !empty($permissoesNivel[$permissao->per_nome]) ? 'checked' : '' ?>
                          <?= !empty($nivelAcesso->nva_imutavel) ? 'disabled' : '' ?>
                        >
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="alertPermissaoVazia" class="alert alert-info d-none">
        Nenhuma permissão encontrada para a pesquisa informada.
      </div>

      <div id="alertSemPermissaoSelecionada" class="alert alert-warning d-none">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Selecione pelo menos uma permissão para salvar este nível de acesso.
      </div>

      <div id="alertPermissaoIncompativel" class="alert alert-danger d-none">
        <i class="fa-solid fa-circle-exclamation"></i>
        Existem permissões incompatíveis com o escopo selecionado.
      </div>

      <div class="row mt-4">
        <div class="col-12">
          <div class="float-end d-flex flex-column flex-md-row gap-2">
            <a href="<?= Helpers::url('nivelacesso/list') ?>" class="bto bto-phsa-light">
              <i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar
            </a>

            <button
              type="button"
              id="btnAlternarCollapsePermissoesFinal"
              class="bto bto-phsa-light"
            >
              <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
              <span>&nbsp;Abrir todos</span>
            </button>

            <?php if (empty($nivelAcesso->nva_imutavel)): ?>
              <button
                type="button"
                id="btnAlternarTodasPermissoes"
                class="bto bto-phsa-light"
              >
                <i class="fa-solid fa-square-check"></i>
                <span>&nbsp;Marcar todas</span>
              </button>

              <button id="btnSalvarNivelAcesso" class="bto bto-phsa" type="submit">
                <i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?php $this->push('script'); ?>
  <script>
    const mapaPermissoesEscopo = <?= json_encode($mapaPermissoesEscopo ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    $(document).ready(function () {
      atualizarStatusClassNivelAcesso();
      atualizarTelaPorEscopo(true);
      atualizarSelecionarTodos();
      atualizarResumoPermissoes();
      atualizarBotoesCollapsePermissoes();
      atualizarBotaoTodasPermissoes();

      $('#nva_status').on('change', function () {
        atualizarStatusClassNivelAcesso();
      });

      $('#nva_escopo').on('change', function () {
        atualizarTelaPorEscopo(false);
      });

      $('#pesquisarPermissao').on('input', function () {
        pesquisarPermissaoNivelAcesso();
      });

      $(document).on('change', '.selecionarTodosModulo', function () {
        getChecksModuloDisponiveis($(this).data('modulo')).prop('checked', $(this).is(':checked'));

        atualizarSelecionarTodos();
        atualizarResumoPermissoes();
        atualizarBotaoTodasPermissoes();
      });

      $(document).on('change', '.checkModulo', function () {
        if (!permissaoCompativelComEscopo($(this).val(), $('#nva_escopo').val())) {
          $(this).prop('checked', false);
          $('#alertPermissaoIncompativel').removeClass('d-none');

          if (typeof swToastError === 'function') {
            swToastError('Permissão incompatível com o escopo selecionado.');
          }
        }

        atualizarSelecionarTodos();
        atualizarResumoPermissoes();
        atualizarBotaoTodasPermissoes();
      });

      $(document).on('shown.bs.collapse hidden.bs.collapse', '.collapseModuloPermissao', function () {
        atualizarBotoesCollapsePermissoes();
      });

      $('#btnAlternarCollapsePermissoesInput, #btnAlternarCollapsePermissoesFinal').on('click', function () {
        alternarCollapsePermissoes();
      });

      $('#btnAlternarTodasPermissoes').on('click', function () {
        alternarTodasPermissoes();
      });

      $('#formNivelAcesso').on('submit', function (event) {
        event.preventDefault();

        if (!this.checkValidity()) {
          event.stopPropagation();
          $(this).addClass('was-validated');
          return;
        }

        if (!validarPermissoesSelecionadasPorEscopo()) {
          $('#alertPermissaoIncompativel').removeClass('d-none');

          if (typeof swToastError === 'function') {
            swToastError('Permissões incompatíveis com o escopo do nível de acesso.');
          }

          return;
        }

        if ($('.checkModulo:checked').length <= 0) {
          $('#alertSemPermissaoSelecionada').removeClass('d-none');

          if (typeof swToastError === 'function') {
            swToastError('Selecione pelo menos uma permissão.');
          }

          return;
        }

        $('#alertSemPermissaoSelecionada').addClass('d-none');
        $('#alertPermissaoIncompativel').addClass('d-none');

        $.ajax({
          url: '<?= Helpers::url('nivelacesso/salvarNivelAcesso') ?>',
          method: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          beforeSend: function () {
            $('#btnSalvarNivelAcesso')
              .prop('disabled', true)
              .html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando');
          },
          success: function (response) {
            swResponse(response);

            if (response.status) {
              if (response.data && response.data.nva_id) {
                window.location.href = '<?= Helpers::url('nivelacesso/form') ?>/' + response.data.nva_id;
                return;
              }

              window.location.href = '<?= Helpers::url('nivelacesso/list') ?>';
            }
          },
          complete: function () {
            $('#btnSalvarNivelAcesso')
              .prop('disabled', false)
              .html('<i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar');
          },
          error: function () {
            swToastError('Erro ao salvar nível de acesso.');
          }
        });
      });
    });

    function atualizarStatusClassNivelAcesso() {
      $('#nva_statusclass').val($('#nva_status').val() === 'Ativo' ? 'success' : 'danger');
    }

    function atualizarTelaPorEscopo(manterSelecionadas) {
      atualizarPadraoInicialNivelAcesso();
      atualizarPermissoesPorEscopo(manterSelecionadas);
      pesquisarPermissaoNivelAcesso();
      atualizarSelecionarTodos();
      atualizarResumoPermissoes();
      atualizarBotaoTodasPermissoes();
      atualizarBotoesCollapsePermissoes();
    }

    function atualizarPadraoInicialNivelAcesso() {
      if ($('#boxPadraoInicial').length <= 0) {
        return;
      }

      if ($('#nva_escopo').val() === 'Organizacao') {
        $('#boxPadraoInicial').removeClass('d-none');
        $('#nva_padrao_inicial').prop('disabled', false);
        return;
      }

      $('#nva_padrao_inicial').prop('checked', false).prop('disabled', true);
      $('#boxPadraoInicial').addClass('d-none');
    }

    function atualizarPermissoesPorEscopo(manterSelecionadas) {
      const escopo = $('#nva_escopo').val();

      $('.itemModuloPermissao').each(function () {
        let totalCompativel = 0;

        $(this).find('.itemPermissao').each(function () {
          const check = $(this).find('.checkModulo');
          const compativel = permissaoCompativelComEscopo(check.val(), escopo);

          if (!compativel) {
            check.prop('checked', false);
            $(this).addClass('d-none').attr('data-incompativel', '1');
          } else {
            totalCompativel++;
            $(this).removeClass('d-none').attr('data-incompativel', '0');
          }
        });

        if (totalCompativel <= 0) {
          $(this).addClass('d-none').attr('data-incompativel', '1');
        } else {
          $(this).removeClass('d-none').attr('data-incompativel', '0');
        }
      });

      if (!manterSelecionadas) {
        $('.checkModulo').each(function () {
          if (!permissaoCompativelComEscopo($(this).val(), escopo)) {
            $(this).prop('checked', false);
          }
        });
      }

      $('#alertPermissaoIncompativel').toggleClass('d-none', validarPermissoesSelecionadasPorEscopo());
    }

    function permissaoCompativelComEscopo(permissao, escopo) {
      const recurso = String(permissao || '').split('.')[0];
      return (mapaPermissoesEscopo[escopo] || []).includes(recurso);
    }

    function validarPermissoesSelecionadasPorEscopo() {
      let valido = true;
      const escopo = $('#nva_escopo').val();

      $('.checkModulo:checked').each(function () {
        if (!permissaoCompativelComEscopo($(this).val(), escopo)) {
          valido = false;
        }
      });

      return valido;
    }

    function pesquisarPermissaoNivelAcesso() {
      const pesquisa = String($('#pesquisarPermissao').val() || '').toLowerCase();
      let totalVisivel = 0;

      $('.itemModuloPermissao').each(function () {
        if ($(this).attr('data-incompativel') === '1') {
          return;
        }

        let moduloVisivel = false;

        $(this).find('.itemPermissao').each(function () {
          if ($(this).attr('data-incompativel') === '1') {
            return;
          }

          const permissao = String($(this).data('permissao') || '').toLowerCase();
          const descricao = String($(this).data('descricao') || '').toLowerCase();

          if (pesquisa === '' || permissao.includes(pesquisa) || descricao.includes(pesquisa)) {
            $(this).removeClass('d-none');
            moduloVisivel = true;
            totalVisivel++;
          } else {
            $(this).addClass('d-none');
          }
        });

        $(this).toggleClass('d-none', !moduloVisivel);
      });

      $('#alertPermissaoVazia').toggleClass('d-none', totalVisivel > 0);

      atualizarSelecionarTodos();
      atualizarBotoesCollapsePermissoes();
      atualizarBotaoTodasPermissoes();
    }

    function getModulosDisponiveis() {
      return $('.itemModuloPermissao:not(.d-none) .collapseModuloPermissao');
    }

    function getChecksDisponiveis() {
      return $('.itemModuloPermissao:not(.d-none) .itemPermissao:not(.d-none) .checkModulo:not(:disabled)');
    }

    function getChecksModuloDisponiveis(modulo) {
      return $('.itemModuloPermissao:not(.d-none)').filter(function () {
        return String($(this).data('modulo')) === String(modulo);
      }).find('.itemPermissao:not(.d-none) .checkModulo:not(:disabled)');
    }

    function atualizarSelecionarTodos() {
      $('.selecionarTodosModulo').each(function () {
        const checks = getChecksModuloDisponiveis($(this).data('modulo'));
        const total = checks.length;
        const totalMarcado = checks.filter(':checked').length;

        $(this).prop('checked', total > 0 && total === totalMarcado);
      });
    }

    function atualizarResumoPermissoes() {
      const total = $('.checkModulo:checked').length;

      $('#textoResumoPermissoes').html(
        total === 1
          ? 'Este nível de acesso possui <b>1</b> permissão selecionada.'
          : `Este nível de acesso possui <b>${total}</b> permissões selecionadas.`
      );

      $('#alertSemPermissaoSelecionada').toggleClass('d-none', total > 0);
    }

    function todosModulosAbertos() {
      const modulos = getModulosDisponiveis();

      return modulos.length > 0 && modulos.filter('.show').length === modulos.length;
    }

    function alternarCollapsePermissoes() {
      const abrir = !todosModulosAbertos();

      getModulosDisponiveis().each(function () {
        bootstrap.Collapse.getOrCreateInstance(this, {
          toggle: false
        })[abrir ? 'show' : 'hide']();
      });

      setTimeout(function () {
        atualizarBotoesCollapsePermissoes();
      }, 250);
    }

    function atualizarBotoesCollapsePermissoes() {
      if (todosModulosAbertos()) {
        $('#btnAlternarCollapsePermissoesInput')
          .attr('title', 'Fechar todos os módulos')
          .html('<i class="fa-solid fa-down-left-and-up-right-to-center"></i>');

        $('#btnAlternarCollapsePermissoesFinal')
          .html('<i class="fa-solid fa-down-left-and-up-right-to-center"></i><span>&nbsp;Fechar todos</span>');

        return;
      }

      $('#btnAlternarCollapsePermissoesInput')
        .attr('title', 'Abrir todos os módulos')
        .html('<i class="fa-solid fa-up-right-and-down-left-from-center"></i>');

      $('#btnAlternarCollapsePermissoesFinal')
        .html('<i class="fa-solid fa-up-right-and-down-left-from-center"></i><span>&nbsp;Abrir todos</span>');
    }

    function todasPermissoesMarcadas() {
      const checks = getChecksDisponiveis();

      return checks.length > 0 && checks.filter(':checked').length === checks.length;
    }

    function alternarTodasPermissoes() {
      getChecksDisponiveis().prop('checked', !todasPermissoesMarcadas());

      atualizarSelecionarTodos();
      atualizarResumoPermissoes();
      atualizarBotaoTodasPermissoes();
    }

    function atualizarBotaoTodasPermissoes() {
      if (todasPermissoesMarcadas()) {
        $('#btnAlternarTodasPermissoes')
          .html('<i class="fa-regular fa-square"></i><span>&nbsp;Desmarcar todas</span>');

        return;
      }

      $('#btnAlternarTodasPermissoes')
        .html('<i class="fa-solid fa-square-check"></i><span>&nbsp;Marcar todas</span>');
    }
  </script>
<?php $this->end(); ?>
