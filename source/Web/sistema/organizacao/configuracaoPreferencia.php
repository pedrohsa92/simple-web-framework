<form id="formConfiguracaoPreferenciaOrganizacao" class="needs-validation" novalidate>
  <input name="org_id" value="<?= $organizacao->org_id ?>" hidden>
  <input name="location" value="/configuracao/preferencia" hidden>
  <input id="org_sidebar" name="org_sidebar" value="<?= (int) $organizacao->org_sidebar ?>" hidden>
  <input id="org_tema" name="org_tema" value="<?= $organizacao->org_tema ?>" hidden>
  <input
    id="org_paletacores"
    name="org_paletacores"
    value="<?= htmlspecialchars($organizacao->org_paletacores ?? '', ENT_QUOTES, 'UTF-8') ?>"
    hidden
  >

  <div class="row">
    <div class="col-12 text-phsa">
      <h5>Preferências da organização</h5>
    </div>

    <div class="col-12 mb-3">
      <div class="alert alert-light border mb-0">
        Configure preferências visuais e padrões que podem ser herdados por empresas e usuários desta organização.
      </div>
    </div>
  </div>

  <section>
    <div class="row">
      <div class="col-sm-4 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light h-100">
          <div class="card-body">
            <h6 class="text-phsa mb-2">
              <i class="fa-solid fa-bars"></i>&nbsp;Menu lateral
            </h6>

            <div class="d-grid gap-2">
              <button type="button" class="bto <?= ((int) $organizacao->org_sidebar === 1) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-organizacao" data-sidebar="1">
                <i class="fa-solid fa-table-columns"></i>&nbsp;Aberta
              </button>

              <button type="button" class="bto <?= ((int) $organizacao->org_sidebar === 2) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-organizacao" data-sidebar="2">
                <i class="fa-solid fa-grip-lines-vertical"></i>&nbsp;Recolhida
              </button>

              <button type="button" class="bto <?= ((int) $organizacao->org_sidebar === 3) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-organizacao" data-sidebar="3">
                <i class="fa-solid fa-eye-slash"></i>&nbsp;Oculta
              </button>

              <button type="button" class="bto <?= ((int) $organizacao->org_sidebar === 4) ? 'bto-phsa' : 'bto-phsa-light' ?> btn-sidebar-organizacao" data-sidebar="4">
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

            <div class="form-floating mb-3">
              <select id="org_home" name="org_home" class="form-select input-phsa-focus input-phsa-hover" required>
                <option value="dashboard" <?= ($organizacao->org_home == 'dashboard') ? 'selected' : '' ?>>Dashboard</option>

                <?php if (Helpers::pode('organizacao.visualizar')): ?>
                  <option value="organizacao" <?= ($organizacao->org_home == 'organizacao') ? 'selected' : '' ?>>
                    Cadastro da organização
                  </option>
                <?php endif ?>

                <?php if (Helpers::pode('empresa.visualizar')): ?>
                  <option value="empresa/list" <?= ($organizacao->org_home == 'empresa/list') ? 'selected' : '' ?>>
                    Pesquisa de empresa
                  </option>
                <?php endif ?>

                <?php if (Helpers::pode('usuario.visualizar')): ?>
                  <option value="usuario/list" <?= ($organizacao->org_home == 'usuario/list') ? 'selected' : '' ?>>
                    Pesquisa de usuario
                  </option>
                <?php endif ?>
              </select>

              <label for="org_home">Página inicial</label>
              <div class="invalid-feedback">Selecione a página inicial</div>
            </div>

            <button class="bto bto-phsa w-100" type="submit">
              <i class="fas fa-save"></i>&nbsp;Salvar preferências
            </button>
          </div>
        </div>
      </div>

      <div class="col-sm-4 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light h-100">
          <div class="card-body">
            <h6 class="text-phsa mb-2">
              <i class="fa-solid fa-circle-half-stroke"></i>&nbsp;Tema e paleta
            </h6>

            <button type="button" id="btnAlterarTemaOrganizacao" class="bto bto-phsa-light w-100 mb-3" data-tema="<?= $organizacao->org_tema ?>">
              <?php if ($organizacao->org_tema == 'dark'): ?>
                <i class="fa-solid fa-sun"></i>&nbsp;Usar tema claro
              <?php else: ?>
                <i class="fa-solid fa-moon"></i>&nbsp;Usar tema escuro
              <?php endif ?>
            </button>

            <button
              type="button"
              id="btnAlterarPaletaOrganizacao"
              onclick="fgbAlterarPaletaDeCores($('#org_paletacores').val(), true, {
                modulo: 'organizacao',
                titulo: '<i class=\'fa-solid fa-palette\'></i> Paleta de cores da organização',
                input: '#org_paletacores',
                salvarAutomatico: true,
                url: 'organizacao/salvarOrganizacao',
                campo: 'org_paletacores',
                dataExtra: {
                  org_id: <?= $organizacao->org_id ?>,
                  location: '/configuracao/preferencia'
                }
              })"
              class="bto bto-phsa-light w-100"
            >
              <i class="fa-solid fa-palette"></i>&nbsp;Alterar paleta de cores
            </button>
          </div>
        </div>
      </div>

      <div class="col-12 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-column flex-md-row">
              <div>
                <h6 class="text-phsa mb-2">
                  <i class="fa-solid fa-icons"></i>&nbsp;Favicon
                </h6>

                <div class="small text-muted">
                  Defina o ícone usado nas abas do navegador e metatags da organização.
                </div>
              </div>

              <div class="d-flex align-items-center gap-2">
                <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                  <img id="previewFaviconOrganizacao" src="<?= Helpers::storage($organizacao->org_favicon, 'storage/global/favicon.png') ?>" alt="Favicon" style="width: 32px; height: 32px; object-fit: contain;">
                </div>

                <button type="button" id="btnUploadFaviconOrganizacao" class="bto bto-phsa-light">
                  <i class="fa-solid fa-upload"></i>&nbsp;Anexar
                </button>

                <button type="button" id="btnSalvarFaviconOrganizacao" class="bto bto-phsa" style="display:none;">
                  <i class="fa-solid fa-save"></i>&nbsp;Salvar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</form>

<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      let objOrgFavicon = {};

      $('.btn-sidebar-organizacao').on('click', function () {
        const sidebar = parseInt($(this).data('sidebar'));

        $('.btn-sidebar-organizacao').removeClass('bto-phsa').addClass('bto-phsa-light');
        $(this).removeClass('bto-phsa-light').addClass('bto-phsa');

        $('#org_sidebar').val(sidebar);

        if (typeof fgbAlternarSidebar === 'function') {
          fgbAlternarSidebar(sidebar, false);
        }
      });

      $('#btnAlterarTemaOrganizacao').on('click', function () {
        const temaAtual = $('#org_tema').val() || 'light';
        const novoTema = temaAtual === 'dark' ? 'light' : 'dark';

        $('#org_tema').val(novoTema);

        if (typeof fgbAlterarTema === 'function') {
          fgbAlterarTema(novoTema, false);
        }

        $(this).html(
          novoTema === 'dark'
            ? '<i class="fa-solid fa-sun"></i>&nbsp;Usar tema claro'
            : '<i class="fa-solid fa-moon"></i>&nbsp;Usar tema escuro'
        );
      });

      $('#btnUploadFaviconOrganizacao').on('click', function () {
        fgbSelecionarImagem({
          onFileSelect: (response) => {
            objOrgFavicon = response;

            if (!objOrgFavicon.status) {
              swResponse(objOrgFavicon);
              return;
            }

            $('#previewFaviconOrganizacao').attr('src', objOrgFavicon.data);
            $('#btnSalvarFaviconOrganizacao').show(200);
          }
        });
      });

      $('#btnSalvarFaviconOrganizacao').on('click', function () {
        if (!objOrgFavicon || !objOrgFavicon.data) {
          swToastError('Nenhuma imagem para salvar!');
          return;
        }

        swAguarde();

        $.ajax({
          url: '<?= Helpers::url("organizacao/salvarOrganizacao") ?>',
          method: 'POST',
          dataType: 'json',
          data: {
            org_id: <?= $organizacao->org_id ?>,
            org_favicon: objOrgFavicon.data,
            location: '/configuracao/preferencia'
          },
          success: function (response) {
            swResponse(response);
            $('#btnSalvarFaviconOrganizacao').hide(200);
            objOrgFavicon = {};
          }
        });
      });

      $('#formConfiguracaoPreferenciaOrganizacao').on('submit', function (event) {
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
          url: '<?= Helpers::url("organizacao/salvarOrganizacao") ?>',
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
            swError('Não foi possível salvar as preferências da organização.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>
