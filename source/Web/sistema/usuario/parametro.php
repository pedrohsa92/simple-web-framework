





<div class="phsaTabContent">
  <form id="formParametro" class="needs-validation" novalidate>
    <input name="usu_id" value="<?= $usuario->usu_id ?>" hidden>
    <input name="location" value="/parametro" hidden>

    <section>
      <div class="row">
        <div class="col-12 text-phsa mb-3">
          <h5>Parâmetros do usuário</h5>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4 mb-3">
          <div class="card border-phsa-light box-phsa-shadow-light h-100">
            <div class="card-body">
              <h6 class="text-phsa mb-2">
                <i class="fa-solid fa-bars"></i>&nbsp;Menu lateral
              </h6>

              <p class="small text-muted mb-3">
                Defina o comportamento visual da barra lateral deste usuário.
              </p>

              <div class="d-grid gap-2">
                <button
                  type="button"
                  class="bto <?= ((int) $usuario->usu_sidebar === 1) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-parametro"
                  data-sidebar="1"
                >
                  <i class="fa-solid fa-table-columns"></i>&nbsp;Aberta
                </button>

                <button
                  type="button"
                  class="bto <?= ((int) $usuario->usu_sidebar === 2) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-parametro"
                  data-sidebar="2"
                >
                  <i class="fa-solid fa-grip-lines-vertical"></i>&nbsp;Recolhida
                </button>

                <button
                  type="button"
                  class="bto <?= ((int) $usuario->usu_sidebar === 3) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-parametro"
                  data-sidebar="3"
                >
                  <i class="fa-solid fa-eye-slash"></i>&nbsp;Oculta
                </button>

                <button
                  type="button"
                  class="bto <?= ((int) $usuario->usu_sidebar === 4) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-parametro"
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
                Escolha a página aberta após o login.
              </p>

              <div class="form-floating mb-3">
                <select
                  id="usu_home"
                  name="usu_home"
                  class="form-select input-phsa-focus input-phsa-hover"
                  required
                >
                  <option value="dashboard" <?= ($usuario->usu_home == 'dashboard') ? 'selected' : '' ?>>
                    Dashboard
                  </option>

                  <option value="usuario/list" <?= ($usuario->usu_home == 'usuario/list') ? 'selected' : '' ?>>
                    Pesquisa de usuário
                  </option>

                  <option value="usuario/form" <?= ($usuario->usu_home == 'usuario/form') ? 'selected' : '' ?>>
                    Cadastro de usuário
                  </option>
                </select>
                <label for="usu_home">Página inicial</label>
                <div class="invalid-feedback">Selecione a página inicial</div>
              </div>

					    <div class="row">
					      <div class="col-sm-12 mb-3 text-center">
					        <button class="bto bto-phsa w-100" type="submit">
					          <i class="fas fa-save"></i>&nbsp;Salvar página inicial
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
                <i class="fa-solid fa-circle-half-stroke"></i>&nbsp;Tema
              </h6>

              <p class="small text-muted mb-3">
                Alterne entre modo claro e escuro.
              </p>

              <button
                type="button"
                id="btnAlterarTemaUsuario"
                class="bto bto-phsa-light w-100 mb-3"
                data-tema="<?= $usuario->usu_tema ?>"
              >
                <?php if ($usuario->usu_tema == 'dark'): ?>
                  <i class="fa-solid fa-sun"></i>&nbsp;Usar tema claro
                <?php else: ?>
                  <i class="fa-solid fa-moon"></i>&nbsp;Usar tema escuro
                <?php endif ?>
              </button>

              <button
                type="button"
                id="btnAlterarPaletaUsuario"
                class="bto bto-phsa-light w-100"
              >
                <i class="fa-solid fa-palette"></i>&nbsp;Alterar paleta de cores
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </form>
</div>

<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      $('.btn-sidebar-parametro').on('click', function () {
        const sidebar = parseInt($(this).data('sidebar'));

        $('.btn-sidebar-parametro')
          .removeClass('bto-phsa')
          .addClass('bto-phsa-light');

        $(this)
          .removeClass('bto-phsa-light')
          .addClass('bto-phsa');

        fgbAlternarSidebar(sidebar, true);
      });

      $('#btnAlterarTemaUsuario').on('click', function () {
        const temaAtual = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const novoTema = temaAtual === 'dark' ? 'light' : 'dark';

        fgbAlterarTema(novoTema, true);

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

      $('#btnAlterarPaletaUsuario').on('click', function () {
        fgbAlterarPaletaDeCores(null, true);
      });

      $('#formParametro').on('submit', function (event) {
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
          title: 'Salvando parâmetros...',
          text: 'Aguarde enquanto processamos os dados.',
          timer: null
        });

        $.ajax({
          url: '<?= Helpers::url("usuario/salvarUsuario") ?>',
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

            swError('Não foi possível salvar os parâmetros do usuário.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>