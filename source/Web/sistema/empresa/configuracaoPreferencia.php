





<form id="formConfiguracaoPreferencia" class="needs-validation" novalidate>
  <input name="emp_id" value="<?= $empresa->emp_id ?>" hidden>
  <input name="location" value="/configuracao/preferencia" hidden>
  <input id="emp_sidebar" name="emp_sidebar" value="<?= (int) $empresa->emp_sidebar ?>" hidden>
  <input id="emp_tema" name="emp_tema" value="<?= $empresa->emp_tema ?>" hidden>
  <input
	  id="emp_paletacores"
	  name="emp_paletacores"
	  value="<?= htmlspecialchars($empresa->emp_paletacores ?? '', ENT_QUOTES, 'UTF-8') ?>"
	  hidden
	>

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Preferências da empresa</h5>
    </div>

    <div class="col-12 mb-3">
      <div class="alert alert-light border mb-0">
        Configure preferências visuais e padrões que podem ser herdados pelos usuários desta empresa.
      </div>
    </div>
  </div>

  <section>
    <div class="row">
      <div class="col-sm-4 mb-3">
        <div class="card box-phsa-hover-shadow-light h-100">
          <div class="card-body">
            <h6 class="text-phsa mb-2">
              <i class="fa-solid fa-bars"></i>&nbsp;Menu lateral
            </h6>

            <p class="small text-muted mb-3">
              Defina o comportamento padrão da barra lateral para novos usuários desta empresa.
            </p>

            <div class="d-grid gap-2">
              <button
                type="button"
                class="bto <?= ((int) $empresa->emp_sidebar === 1) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-empresa"
                data-sidebar="1"
              >
                <i class="fa-solid fa-table-columns"></i>&nbsp;Aberta
              </button>

              <button
                type="button"
                class="bto <?= ((int) $empresa->emp_sidebar === 2) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-empresa"
                data-sidebar="2"
              >
                <i class="fa-solid fa-grip-lines-vertical"></i>&nbsp;Recolhida
              </button>

              <button
                type="button"
                class="bto <?= ((int) $empresa->emp_sidebar === 3) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-empresa"
                data-sidebar="3"
              >
                <i class="fa-solid fa-eye-slash"></i>&nbsp;Oculta
              </button>

              <button
                type="button"
                class="bto <?= ((int) $empresa->emp_sidebar === 4) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-empresa"
                data-sidebar="4"
              >
                <i class="fa-solid fa-window-restore"></i>&nbsp;Overlay
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-4 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light h-100">
          <div class="card-body">
            <h6 class="text-phsa mb-2">
              <i class="fa-solid fa-house"></i>&nbsp;Página inicial
            </h6>

            <p class="small text-muted mb-3">
              Escolha a página inicial padrão para novos usuários desta empresa.
            </p>

            <div class="form-floating mb-3">
              <select
                id="emp_home"
                name="emp_home"
                class="form-select input-phsa-focus input-phsa-hover"
                required
              >
                <option value="dashboard" <?= ($empresa->emp_home == 'dashboard') ? 'selected' : '' ?>>
                  Dashboard
                </option>

                <?php if (Helpers::pode('organizacao.visualizar')): ?>
                  <option value="organizacao" <?= ($empresa->emp_home == 'organizacao') ? 'selected' : '' ?>>
                    Cadastro da organizacao
                  </option>
                <?php endif ?>

                <?php if (Helpers::pode('empresa.visualizar')): ?>
                  <option value="empresa/list" <?= ($empresa->emp_home == 'empresa/list') ? 'selected' : '' ?>>
                    Pesquisa de empresa
                  </option>
                <?php endif ?>

                <?php if (Helpers::pode('usuario.visualizar')): ?>
                  <option value="usuario/list" <?= ($empresa->emp_home == 'usuario/list') ? 'selected' : '' ?>>
                    Pesquisa de usuário
                  </option>
                <?php endif ?>
              </select>

              <label for="emp_home">Página inicial</label>
              <div class="invalid-feedback">Selecione a página inicial</div>
            </div>

            <div class="row">
              <div class="col-sm-12 mb-3 text-center">
                <button class="bto bto-phsa w-100" type="submit">
                  <i class="fas fa-save"></i>&nbsp;Salvar preferências
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-4 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light h-100">
          <div class="card-body">
            <h6 class="text-phsa mb-2">
              <i class="fa-solid fa-circle-half-stroke"></i>&nbsp;Tema e paleta
            </h6>

            <p class="small text-muted mb-3">
              Defina o tema e a paleta padrão para novos usuários desta empresa.
            </p>

            <button
              type="button"
              id="btnAlterarTemaEmpresa"
              class="bto bto-phsa-light w-100 mb-3"
              data-tema="<?= $empresa->emp_tema ?>"
            >
              <?php if ($empresa->emp_tema == 'dark'): ?>
                <i class="fa-solid fa-sun"></i>&nbsp;Usar tema claro
              <?php else: ?>
                <i class="fa-solid fa-moon"></i>&nbsp;Usar tema escuro
              <?php endif ?>
            </button>

						<button
						  type="button"
						  id="btnAlterarPaletaEmpresa"
						  onclick="fgbAlterarPaletaDeCores($('#emp_paletacores').val(), true, {
						    modulo: 'empresa',
						    titulo: '<i class=\'fa-solid fa-palette\'></i> Paleta de cores da empresa',
						    input: '#emp_paletacores',
						    salvarAutomatico: true,
						    url: 'empresa/salvarEmpresa',
						    campo: 'emp_paletacores',
						    dataExtra: {
						      emp_id: <?= $empresa->emp_id ?>,
						      location: '/configuracao/preferencia'
						    }
						  })"
						  class="bto bto-phsa-light w-100"
						>
						  <i class="fa-solid fa-palette"></i>&nbsp;Alterar paleta de cores
						</button>

            <small class="text-muted d-block mt-3">
              A alteração será gravada ao salvar as preferências.
            </small>
          </div>
        </div>
      </div>
    </div>
  </section>
</form>

<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      $('.btn-sidebar-empresa').on('click', function () {
        const sidebar = parseInt($(this).data('sidebar'));

        $('.btn-sidebar-empresa')
          .removeClass('bto-phsa')
          .addClass('bto-phsa-light');

        $(this)
          .removeClass('bto-phsa-light')
          .addClass('bto-phsa');

        $('#emp_sidebar').val(sidebar);

        if (typeof fgbAlternarSidebar === 'function') {
          fgbAlternarSidebar(sidebar, false);
        }
      });

      $('#btnAlterarTemaEmpresa').on('click', function () {
        const temaAtual = $('#emp_tema').val() || 'light';
        const novoTema = temaAtual === 'dark' ? 'light' : 'dark';

        $('#emp_tema').val(novoTema);

        if (typeof fgbAlterarTema === 'function') {
          fgbAlterarTema(novoTema, false);
        }

        if (novoTema === 'dark') {
          $(this)
            .data('tema', 'dark')
            .html('<i class="fa-solid fa-sun"></i>&nbsp;Usar tema claro');

          return;
        }

        $(this)
          .data('tema', 'light')
          .html('<i class="fa-solid fa-moon"></i>&nbsp;Usar tema escuro');
      });

      $('#formConfiguracaoPreferencia').on('submit', function (event) {
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
          title: 'Salvando preferências...',
          text: 'Aguarde enquanto processamos os dados.',
          timer: null
        });

        $.ajax({
          url: '<?= Helpers::url("empresa/salvarEmpresa") ?>',
          type: 'POST',
          dataType: 'json',
          data: form.serialize(),
          success: function (response) {
            swResponse(response).then(function () {
              if (response.status && response.location) {
                window.location.href = response.location;
                return;
              }

              form.data('enviando', false);
              buttons.attr('disabled', false);
            });
          },
          error: function () {
            form.data('enviando', false);
            buttons.attr('disabled', false);

            swError('Não foi possível salvar as preferências da empresa.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>
