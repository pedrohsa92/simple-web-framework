<?php
  $this->layout("sistema::login");

  $isBcd = ($projeto->prj_tipo ?? 'Compartilhado') === 'Dedicado'
    || (defined('CONF_ORG_ID') && trim((string) CONF_ORG_ID) !== '');
  $isBcc = !$isBcd;
  $loginMode = $isBcd ? 'bcd' : 'bcc';

  $contextoOrganizacao = $contexto->organizacao ?? null;
  $contextoEmpresa = $contexto->empresa ?? null;
  $contextoNome = $contextoEmpresa->emp_nomefantasia
    ?? $contextoEmpresa->emp_razaosocial
    ?? $contextoOrganizacao->org_nomefantasia
    ?? $contextoOrganizacao->org_razaosocial
    ?? 'icliv';
  $loginLocation = trim((string) ($_GET['location'] ?? 'dashboard'));
  $loginMessage = trim((string) ($_GET['message'] ?? ''));
?>

<main class="login-cover login-cover-<?= $loginMode ?>">
  <div class="container-fluid h-100">
    <div class="row h-100 align-items-center justify-content-center g-0">
      <div class="<?= $isBcc ? 'col-12 col-lg-11 col-xl-10 col-xxl-9' : 'col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4' ?>">
        <div class="login-shell login-shell-<?= $loginMode ?> card border-0 overflow-hidden">
          <div class="row g-0 h-100">
            <?php if ($isBcc): ?>
              <section class="col-lg-7 d-none d-lg-flex login-panel text-white">
                <div class="d-flex flex-column justify-content-between w-100 h-100 p-5">
                  <div>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill login-badge">
                      <i class="fa-solid fa-heart-pulse"></i>
                      <span class="small fw-semibold">Ecossistema de gestão para todos os animais</span>
                    </div>
                    <div class="login-animal-line mt-3"><span><i class="fa-solid fa-dove"></i> Aves</span><span><i class="fa-solid fa-horse"></i> Quadrúpedes</span><span><i class="fa-solid fa-cat"></i> Pets</span><span><i class="fa-solid fa-fish"></i> Aquáticos</span></div>

                    <div class="mt-5">
                      <h1 class="display-6 fw-bold mb-3">
                        Simplificando o cuidado com os animais.
                      </h1>

                      <p class="lead opacity-75 mb-4">
                        Gestão, presença digital e relacionamento para empresas que cuidam de aves, quadrúpedes, pets e animais aquáticos.
                      </p>

                      <div id="carouselModulosLogin" class="carousel slide mt-4" data-bs-ride="carousel" data-bs-interval="4500">
                        <div class="carousel-inner">
                          <div class="carousel-item active">
                            <div class="row g-3">
                              <div class="col-6">
                                <div class="login-feature rounded-4 p-3 h-100">
                                  <i class="fa-solid fa-people-group fa-lg mb-3"></i>
                                  <h6 class="fw-bold mb-1">Clientes e animais</h6>
                                  <p class="small opacity-75 mb-0">Organize clientes, animais e o histórico de cuidados em um só lugar.</p>
                                </div>
                              </div>

                              <div class="col-6">
                                <div class="login-feature rounded-4 p-3 h-100">
                                  <i class="fa-solid fa-calendar-days fa-lg mb-3"></i>
                                  <h6 class="fw-bold mb-1">Serviços e agenda</h6>
                                  <p class="small opacity-75 mb-0">Estruture serviços, horários e atendimentos com simplicidade.</p>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="carousel-item">
                            <div class="row g-3">
                              <div class="col-6">
                                <div class="login-feature rounded-4 p-3 h-100">
                                  <i class="fa-solid fa-people-arrows fa-lg mb-3"></i>
                                  <h6 class="fw-bold mb-1">Catálogo digital</h6>
                                  <p class="small opacity-75 mb-0">Apresente seus serviços e conteúdos em uma presença própria da empresa.</p>
                                </div>
                              </div>

                              <div class="col-6">
                                <div class="login-feature rounded-4 p-3 h-100">
                                  <i class="fa-solid fa-coins fa-lg mb-3"></i>
                                  <h6 class="fw-bold mb-1">Financeiro</h6>
                                  <p class="small opacity-75 mb-0">Acompanhe receitas, despesas, contas e movimentações.</p>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="carousel-item">
                            <div class="row g-3">
                              <div class="col-6">
                                <div class="login-feature rounded-4 p-3 h-100">
                                  <i class="fa-solid fa-wand-magic-sparkles fa-lg mb-3"></i>
                                  <h6 class="fw-bold mb-1">Conteúdo e relacionamento</h6>
                                  <p class="small opacity-75 mb-0">Compartilhe informações e fortaleça a relação com seus clientes.</p>
                                </div>
                              </div>

                              <div class="col-6">
                                <div class="login-feature rounded-4 p-3 h-100">
                                  <i class="fa-solid fa-mobile-screen-button fa-lg mb-3"></i>
                                  <h6 class="fw-bold mb-1">Conexão</h6>
                                  <p class="small opacity-75 mb-0">Aproxime empresas, profissionais, clientes e seus animais.</p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="carousel-indicators login-carousel-indicators">
                          <button type="button" data-bs-target="#carouselModulosLogin" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Módulos 1"></button>
                          <button type="button" data-bs-target="#carouselModulosLogin" data-bs-slide-to="1" aria-label="Módulos 2"></button>
                          <button type="button" data-bs-target="#carouselModulosLogin" data-bs-slide-to="2" aria-label="Módulos 3"></button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            <?php endif; ?>

            <section class="<?= $isBcc ? 'col-12 col-lg-5' : 'col-12' ?> bg-white">
              <div class="login-form-area d-flex flex-column align-items-center justify-content-center h-100 p-4 p-sm-5">
                <?php if ($isBcd): ?>
                  <div class="login-dedicated-note text-center mb-4">
                    <span class="login-dedicated-label">
                      <i class="fa-solid fa-shield-halved me-1"></i>
                      Ambiente dedicado
                    </span>
                    <strong><?= htmlspecialchars($contextoNome, ENT_QUOTES, 'UTF-8') ?></strong>
                    <small>Seu espaço exclusivo no icliv</small>
                  </div>
                <?php endif; ?>
                <div class="w-100 login-form-wrap">
                  <div class="text-center mb-4">
                    <?php
                      $loginLogo = $isBcd
                        ? ($contexto->empresa->emp_logo
                          ?? $contexto->organizacao->org_logo
                          ?? 'storage/global/icliv.png')
                        : 'storage/global/icliv.png';

                      $loginAlt = $contexto->empresa->emp_nomefantasia
                        ?? $contexto->organizacao->org_nomefantasia
                        ?? $projeto->prj_titulo
                        ?? $projeto->prj_nome
                        ?? 'icliv';
                    ?>

                    <div class="login-logo mx-auto mb-3">
                      <img src="<?= Helpers::storage($loginLogo, 'storage/global/icliv.png') ?>" alt="<?= htmlspecialchars($loginAlt, ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <h2 id="loginTitulo" class="fw-bold mb-1">
                      <?= $isBcd ? 'Acesso dedicado' : 'Bem-vindo ao icliv' ?>
                    </h2>

                    <p id="loginDescricao" class="text-muted mb-0">
                      <?= $isBcd ? 'Entre no ambiente exclusivo da sua empresa' : 'Conecte-se para cuidar melhor dos seus animais e clientes' ?>
                    </p>

                    <?php if ($loginMessage !== ''): ?>
                      <div class="alert alert-info text-start small mt-3 mb-0">
                        <i class="fa-solid fa-circle-info me-2"></i><?= htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8') ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div id="divLogin">
                    <form id="formLogin" class="needs-validation" novalidate autocomplete="new-password">
                      <input type="hidden" name="location" value="<?= htmlspecialchars($loginLocation !== '' ? $loginLocation : 'dashboard', ENT_QUOTES, 'UTF-8') ?>">

                      <div class="form-floating mb-3">
                        <input
                          type="text"
                          id="usu_login"
                          name="usu_login"
                          class="form-control input-phsa-focus input-phsa-hover"
                           placeholder="E-mail ou usuário"
                          required
                          autocomplete="new-password"
                        >
                        <label for="usu_login">E-mail ou usuário</label>
                        <div class="invalid-feedback">
                          Informe seu e-mail ou usuário.
                        </div>
                      </div>

                      <div class="form-floating mb-2 position-relative">
                        <input
                          type="password"
                          id="usu_senha"
                          name="usu_senha"
                          class="form-control input-phsa-focus input-phsa-hover pe-5"
                          placeholder="Senha"
                          required
                          autocomplete="new-password"
                        >
                        <label for="usu_senha">Senha</label>

                        <button
                          type="button"
                          id="btnToggleSenha"
                          class="btn-toggle-senha"
                          onclick="toggleSenhaLogin()"
                          tabindex="-1"
                          aria-label="Mostrar senha"
                        >
                          <i class="fa-solid fa-eye"></i>
                        </button>

                        <div class="invalid-feedback">
                          Informe sua senha.
                        </div>
                      </div>

                      <div class="d-flex justify-content-end mb-4">
                        <a href="#" onclick="modoRecuperacao(); return false;" class="small text-phsa text-decoration-none">
                          Esqueci minha senha
                        </a>
                      </div>

                      <button type="submit" class="bto bto-phsa w-100 py-2">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                        Entrar
                      </button>

                      <?php if ($isBcc): ?>
                        <div class="text-center mt-4 small">
                          <span class="text-muted">Ainda não tem conta?</span>
                          <a href="<?= Helpers::url('contrate') ?>" class="text-phsa fw-semibold text-decoration-none">
                            Cadastre-se
                          </a>
                        </div>
                      <?php endif; ?>
                    </form>
                  </div>

                  <div id="divRecuperarAcesso" class="d-none">
                    <form id="formRecuperarAcesso" class="needs-validation" novalidate autocomplete="new-password">
                      <div class="form-floating mb-3">
                        <select
                          id="tipo_recuperacao"
                          name="tipo_recuperacao"
                          class="form-select input-phsa-focus input-phsa-hover"
                          onchange="setTipoRecuperacao()"
                          required
                        >
                          <option value="email">E-mail</option>
                          <option value="whatsapp">WhatsApp</option>
                          <option value="sms">SMS</option>
                        </select>
                        <label for="tipo_recuperacao">Recuperar por</label>
                        <div class="invalid-feedback">
                          Selecione uma opção.
                        </div>
                      </div>

                      <div class="form-floating mb-4">
                        <input
                          type="email"
                          id="contato_recuperacao"
                          name="contato_recuperacao"
                          class="form-control input-phsa-focus input-phsa-hover"
                          placeholder="E-mail"
                          required
                          autocomplete="new-password"
                        >
                        <label id="label_contato_recuperacao" for="contato_recuperacao">E-mail</label>
                        <div class="invalid-feedback">
                          Informe os dados para recuperação.
                        </div>
                      </div>

                      <button type="submit" class="bto bto-phsa w-100 py-2">
                        <i class="fa-solid fa-envelope me-2"></i>
                        Recuperar senha
                      </button>

                      <div class="text-center mt-4 small">
                        <span class="text-muted">Lembrou a senha?</span>
                        <a href="#" onclick="modoLogin(); return false;" class="text-phsa fw-semibold text-decoration-none">
                          Fazer login
                        </a>
                      </div>
                    </form>
                  </div>

                  <div class="login-credit text-center mt-4">
                    <a href="<?= Helpers::url('/') ?>" class="small fw-semibold login-brand-name text-decoration-none" aria-label="Ir para o site icliv">icliv ®</a>
                    <div class="small text-muted">
                      Desenvolvido por
                      <a href="https://phsa.com.br" target="_blank" rel="noopener noreferrer">PHSA Tecnologia</a>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php $this->push("script"); ?>
  <script>
    const loginIsBcd = <?= $isBcd ? 'true' : 'false' ?>;

    $(document).ready(function () {
      $('#usu_login').focus();

      $('#formLogin').on('submit', function (e) {
        e.preventDefault();

        if (this.checkValidity() === false) {
          e.stopPropagation();
          this.classList.add('was-validated');
          return false;
        }

        this.classList.add('was-validated');
        enviarLogin();
      });

      $('#formRecuperarAcesso').on('submit', function (e) {
        e.preventDefault();

        if (this.checkValidity() === false) {
          e.stopPropagation();
          this.classList.add('was-validated');
          return false;
        }

        this.classList.add('was-validated');

        const form = $('#formRecuperarAcesso');

        bloquearFormulario(form, true);

        $.ajax({
          url: '<?= Helpers::url("usuario/recuperarAcesso") ?>',
          type: 'post',
          dataType: 'json',
          data: form.serialize(),
          success: function (response) {
            bloquearFormulario(form, false);
            swResponse(response);
          },
          error: function () {
            bloquearFormulario(form, false);
            swError('Não foi possível solicitar a recuperação de senha. Tente novamente.');
          }
        });
      });
    });

    function toggleSenhaLogin() {
      const input = $('#usu_senha');
      const button = $('#btnToggleSenha');

      if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        button.html('<i class="fa-solid fa-eye-slash"></i>');
        button.attr('aria-label', 'Ocultar senha');
        return;
      }

      input.attr('type', 'password');
      button.html('<i class="fa-solid fa-eye"></i>');
      button.attr('aria-label', 'Mostrar senha');
    }

    function modoLogin() {
      $('#loginTitulo').text(loginIsBcd ? 'Acesso dedicado' : 'Bem-vindo ao icliv');
      $('#loginDescricao').text(loginIsBcd
        ? 'Entre no ambiente exclusivo da sua empresa'
        : 'Conecte-se para cuidar melhor dos seus animais e clientes');
      $('#divRecuperarAcesso').addClass('d-none');
      $('#divLogin').removeClass('d-none');
      $('#usu_login').focus();
    }

    function modoRecuperacao() {
      $('#loginTitulo').text('Recuperar senha');
      $('#loginDescricao').text('Informe seu e-mail de acesso para receber as instruções');
      $('#divLogin').addClass('d-none');
      $('#divRecuperarAcesso').removeClass('d-none');
      setTipoRecuperacao();
      $('#contato_recuperacao').focus();
    }

    function setTipoRecuperacao() {
      const tipo = $('#tipo_recuperacao').val();
      const input = $('#contato_recuperacao');
      const label = $('#label_contato_recuperacao');
      const button = $('#formRecuperarAcesso button[type="submit"] i');

      input.val('').removeAttr('maxlength pattern');

      if (typeof input.unmask === 'function') {
        input.unmask();
      }

      if (tipo == 'email') {
        input.attr('type', 'email').attr('placeholder', 'E-mail');
        label.text('E-mail');
        button.attr('class', 'fa-solid fa-envelope me-2');
        return;
      }

      input.attr('type', 'text').attr('placeholder', tipo == 'whatsapp' ? 'WhatsApp' : 'Celular');
      label.text(tipo == 'whatsapp' ? 'WhatsApp' : 'Celular');
      button.attr('class', tipo == 'whatsapp' ? 'fa-brands fa-whatsapp me-2' : 'fa-solid fa-comment-sms me-2');

      if (typeof input.mask === 'function') {
        input.mask('(99) 9 9999-9999');
      }
    }

    function enviarLogin(usuIdEmpresa = null) {
      const form = $('#formLogin');
      const data = form.serializeArray();

      if (usuIdEmpresa) {
        data.push({
          name: 'usu_idempresa',
          value: usuIdEmpresa
        });
      }

      bloquearFormulario(form, true);

      $.ajax({
        url: '<?= Helpers::url("usuario/validarLogin") ?>',
        type: 'post',
        dataType: 'json',
        data: $.param(data),
        success: function (response) {
          bloquearFormulario(form, false);

          if (response.action === 'selecionar_empresa') {
            abrirSelecaoEmpresa(response.empresas || []);
            return;
          }

          if (response.status && response.location) {
            location.href = response.location;
            return;
          }

          if (response.status && response.url) {
            location.href = response.url;
            return;
          }

          swResponse(response);
        },
        error: function () {
          bloquearFormulario(form, false);
          swError('Não foi possível validar o acesso. Tente novamente.');
        }
      });
    }

    function abrirSelecaoEmpresa(empresas) {
      $('#modalSelecaoEmpresaLogin').remove();

      if (!empresas.length) {
        swWarning('Não foi possível identificar as empresas vinculadas a este acesso.');
        return;
      }

      let html = '';

      empresas.forEach(function (empresa) {
        html += `
          <button
            type="button"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3"
            onclick="selecionarEmpresaLogin('${empresa.emp_id}')"
          >
            <span>
              <strong>${empresa.emp_nomefantasia || empresa.emp_razaosocial || 'Empresa'}</strong>
              <span class="d-block small text-muted">Clique para continuar</span>
            </span>
            <i class="fa-solid fa-chevron-right text-phsa"></i>
          </button>
        `;
      });

      $('body').append(`
        <div class="modal fade" id="modalSelecaoEmpresaLogin" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
              <div class="modal-header bg-phsa text-white border-0">
                <h5 class="modal-title">
                  <i class="fa-solid fa-building me-2"></i>
                  Escolha a empresa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
              </div>

              <div class="modal-body p-4">
                <p class="text-muted mb-3">
                  Encontramos este acesso em mais de uma empresa. Selecione onde deseja entrar.
                </p>

                <div class="list-group border rounded-4 overflow-hidden">
                  ${html}
                </div>
              </div>
            </div>
          </div>
        </div>
      `);

      new bootstrap.Modal(document.getElementById('modalSelecaoEmpresaLogin')).show();
    }

    function selecionarEmpresaLogin(usuIdEmpresa) {
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalSelecaoEmpresaLogin'));

      if (modal) {
        modal.hide();
      }

      enviarLogin(usuIdEmpresa);
    }

    function bloquearFormulario(form, bloquear) {
      form.find('input, button, select, textarea').attr('disabled', bloquear);

      const button = form.find('button[type="submit"]');

      if (bloquear) {
        button.data('html-original', button.html());
        button.html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Validando...');
        return;
      }

      if (button.data('html-original')) {
        button.html(button.data('html-original'));
      }
    }
  </script>
<?php $this->end(); ?>

<?php $this->push("style"); ?>
  <style>
    html,
    body {
      height: 100%;
      overflow: hidden;
      background: #f8f9fa;
    }

    .login-cover {
      min-height: 100vh;
      height: 100vh;
      overflow: hidden;
      background:
        radial-gradient(circle at top left, var(--phsa-soft-bg, #d6def2), transparent 34%),
        radial-gradient(circle at bottom right, var(--phsa-soft-hover-bg, #ecf1fb), transparent 34%),
        #f8f9fa;
    }

    .login-shell {
      min-height: 680px;
      max-height: calc(100vh - 3rem);
      border-radius: 2rem;
      background: #ffffff;
      box-shadow: 0 2rem 5rem rgba(15, 23, 42, 0.18);
    }

    .login-panel {
      min-height: 680px;
      position: relative;
      overflow: hidden;
      background:
        linear-gradient(135deg, rgba(37, 68, 127, 0.95), rgba(50, 90, 168, 0.86)),
        var(--phsa-primary-bg, #325aa8);
    }

    .login-panel::before,
    .login-panel::after {
      content: "";
      position: absolute;
      width: 22rem;
      height: 22rem;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.09);
      pointer-events: none;
    }

    .login-panel::before {
      top: -8rem;
      right: -6rem;
    }

    .login-panel::after {
      bottom: -10rem;
      left: -7rem;
    }

    .login-panel > div {
      position: relative;
      z-index: 1;
    }

    .login-badge,
    .login-feature {
      background: rgba(255, 255, 255, 0.13);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.08);
    }

    .login-feature {
      min-height: 160px;
      transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .login-feature:hover {
      transform: translateY(-3px);
      background: rgba(255, 255, 255, 0.18);
    }

    .login-carousel-indicators {
      position: static;
      margin: 1.5rem 0 0 0;
    }

    .login-carousel-indicators [data-bs-target] {
      width: 0.55rem;
      height: 0.55rem;
      border-radius: 50%;
      border: 0;
      opacity: 0.45;
      background-color: #ffffff;
    }

    .login-carousel-indicators .active {
      opacity: 1;
    }

    .login-form-area {
      min-height: 680px;
      background: #ffffff;
    }

    .login-form-wrap {
      max-width: 390px;
    }

    .login-logo {
      width: 112px;
      height: 112px;
      border-radius: 1.75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--phsa-soft-bg, #d6def2);
      border: 1px solid rgba(37, 68, 127, 0.08);
      box-shadow: 0 1rem 2.5rem rgba(37, 68, 127, 0.12);
      overflow: hidden;
    }

    .login-logo img {
      max-width: 78%;
      max-height: 78%;
      object-fit: contain;
    }

    .btn-toggle-senha {
      position: absolute;
      top: 50%;
      right: 0.85rem;
      transform: translateY(-50%);
      z-index: 5;
      border: 0;
      background: transparent;
      color: #6c757d;
      padding: 0.35rem;
      line-height: 1;
      transition: color 0.15s ease, transform 0.15s ease;
    }

    .btn-toggle-senha:hover {
      color: var(--phsa-primary-bg, #325aa8);
      transform: translateY(-50%) scale(1.08);
    }

    .btn-toggle-senha:focus {
      outline: none;
      box-shadow: none;
    }

    .form-floating > .form-control,
    .form-floating > .form-select {
      border-color: #dee2e6;
      border-radius: 0.85rem;
    }

    .form-floating > .form-control:focus,
    .form-floating > .form-select:focus {
      border-color: var(--phsa-primary-bg, #325aa8);
      box-shadow: 0 0 0 0.2rem rgba(50, 90, 168, 0.15);
    }

    .text-phsa {
      color: var(--phsa-primary-bg, #325aa8) !important;
    }

    .text-phsa:hover {
      color: var(--phsa-primary-hover-bg, #25447f) !important;
    }

    .bto.bto-phsa {
      border-radius: 0.85rem;
      font-weight: 600;
      box-shadow: 0 0.9rem 1.8rem rgba(50, 90, 168, 0.22);
    }

    .bto.bto-phsa:hover {
      transform: translateY(-1px);
      box-shadow: 0 1rem 2rem rgba(37, 68, 127, 0.26);
    }

    #modalSelecaoEmpresaLogin .modal-content {
      border: 0;
      border-radius: 1.25rem;
      box-shadow: 0 2rem 5rem rgba(15, 23, 42, 0.18);
    }

    #modalSelecaoEmpresaLogin .list-group-item {
      border-color: rgba(37, 68, 127, 0.08);
      transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    #modalSelecaoEmpresaLogin .list-group-item:hover,
    #modalSelecaoEmpresaLogin .list-group-item.active {
      background-color: var(--phsa-primary-bg, #325aa8);
      border-color: var(--phsa-primary-bg, #325aa8);
      color: var(--phsa-primary-text, #ffffff);
    }

    @media (max-width: 991.98px) {
      .login-shell {
        min-height: auto;
        max-height: calc(100vh - 2rem);
        border-radius: 1.5rem;
      }

      .login-form-area {
        min-height: auto;
        height: calc(100vh - 2rem);
      }
    }

    @media (max-width: 575.98px) {
      .login-cover {
        background: #ffffff;
      }

      .login-shell {
        height: 100vh;
        max-height: 100vh;
        border-radius: 0;
        box-shadow: none;
      }

      .login-form-area {
        height: 100vh;
        padding: 1.5rem !important;
      }

      .login-logo {
        width: 96px;
        height: 96px;
      }
    }

    @media (max-height: 720px) {
      .login-shell,
      .login-panel,
      .login-form-area {
        min-height: calc(100vh - 2rem);
      }

      .login-logo {
        width: 86px;
        height: 86px;
      }
    }

    /* Identidade visual do icliv no BCC: aberta, humana e comercial. */
    .login-cover-bcc {
      background:
        radial-gradient(circle at 8% 12%, rgba(50, 90, 168, 0.18), transparent 28%),
        radial-gradient(circle at 92% 88%, rgba(37, 68, 127, 0.14), transparent 32%),
        #f8f9fa;
    }

    .login-shell-bcc {
      border-radius: 2.25rem;
      box-shadow: 0 2.5rem 6rem rgba(37, 68, 127, 0.18);
    }

    .login-cover-bcc .login-panel {
      background:
        radial-gradient(circle at 82% 14%, rgba(255, 255, 255, 0.12), transparent 20%),
        linear-gradient(145deg, rgba(37, 68, 127, 0.98), rgba(50, 90, 168, 0.92));
    }

    .login-cover-bcc .login-panel::before,
    .login-cover-bcc .login-panel::after {
      background: rgba(255, 255, 255, 0.1);
    }

    .login-cover-bcc .login-badge,
    .login-cover-bcc .login-feature {
      background: rgba(255, 255, 255, 0.1);
      border-color: rgba(255, 255, 255, 0.18);
    }

    .login-cover-bcc .login-feature:hover {
      background: rgba(255, 255, 255, 0.17);
    }

    .login-cover-bcc .login-form-area {
      background: #ffffff;
    }

    .login-cover-bcc .login-logo {
      background: var(--phsa-soft-bg, #d6def2);
      border-color: rgba(37, 68, 127, 0.08);
      box-shadow: 0 1rem 2.5rem rgba(37, 68, 127, 0.12);
    }

    .login-cover-bcc .login-brand-name {
      color: var(--phsa-primary-bg, #325aa8);
    }

    .login-cover-bcc .text-phsa {
      color: var(--phsa-primary-bg, #325aa8) !important;
    }

    .login-cover-bcc .text-phsa:hover {
      color: var(--phsa-primary-hover-bg, #25447f) !important;
    }

    .login-cover-bcc .bto.bto-phsa {
      background: linear-gradient(135deg, #25447f, #325aa8);
      border-color: transparent;
      box-shadow: 0 0.9rem 1.8rem rgba(50, 90, 168, 0.22);
    }

    /* Identidade visual do BCD: exclusiva, segura e orientada ao ambiente. */
    .login-cover-bcd {
      background:
        radial-gradient(circle at 14% 16%, rgba(214, 222, 242, 0.85), transparent 30%),
        radial-gradient(circle at 86% 84%, rgba(236, 241, 251, 0.95), transparent 30%),
        #f8f9fa;
    }

    .login-shell-bcd {
      border: 1px solid rgba(37, 68, 127, 0.12) !important;
      border-radius: 1.75rem;
      background: #ffffff;
      box-shadow: 0 2rem 5rem rgba(37, 68, 127, 0.18);
    }

    .login-cover-bcd .login-form-area {
      min-height: 680px;
      background: #ffffff;
    }

    .login-cover-bcd .login-form-wrap {
      max-width: 410px;
    }

    .login-cover-bcd .login-logo {
      width: 104px;
      height: 104px;
      border-radius: 50%;
      background: var(--phsa-soft-bg, #d6def2);
      border-color: rgba(37, 68, 127, 0.14);
      box-shadow: 0 1rem 2.5rem rgba(37, 68, 127, 0.14);
    }

    .login-dedicated-note {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      color: var(--phsa-primary-hover-bg, #25447f);
    }

    .login-dedicated-note strong {
      font-size: 1.05rem;
      letter-spacing: -0.01em;
    }

    .login-dedicated-note small {
      color: #6c757d;
    }

    .login-dedicated-label {
      display: inline-flex;
      align-items: center;
      padding: 0.35rem 0.7rem;
      border-radius: 999px;
      background: var(--phsa-soft-bg, #d6def2);
      color: var(--phsa-primary-hover-bg, #25447f);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .login-cover-bcd .form-floating > .form-control,
    .login-cover-bcd .form-floating > .form-select {
      background: #ffffff;
      border-color: #dee2e6;
    }

    .login-cover-bcd .form-floating > .form-control:focus,
    .login-cover-bcd .form-floating > .form-select:focus {
      border-color: var(--phsa-primary-bg, #325aa8);
      box-shadow: 0 0 0 0.2rem rgba(50, 90, 168, 0.15);
    }

    .login-cover-bcd .text-phsa {
      color: var(--phsa-primary-bg, #325aa8) !important;
    }

    .login-cover-bcd .text-phsa:hover {
      color: var(--phsa-primary-hover-bg, #25447f) !important;
    }

    .login-cover-bcd .bto.bto-phsa {
      background: linear-gradient(135deg, #25447f, #325aa8);
      border-color: transparent;
      box-shadow: 0 0.9rem 1.8rem rgba(50, 90, 168, 0.22);
    }

    .login-credit a {
      color: inherit;
      font-weight: 600;
      text-decoration: none;
    }

    .login-credit a:hover {
      text-decoration: underline;
    }

    @media (max-width: 575.98px) {
      .login-cover-bcd .login-form-area {
        min-height: 100vh;
      }

      .login-dedicated-note {
        margin-top: 0.5rem;
      }
    }

    /* Identidade icliv: tecnologia para o cuidado de todas as espécies. */
    .login-cover-bcc,
    .login-cover-bcd {
      --ic-green: #2d8a68;
      --ic-green-dark: #17634c;
      --ic-mint: #dff3e8;
      --ic-ink: #19352d;
      background:
        radial-gradient(circle at 8% 12%, rgba(190, 232, 207, .8), transparent 30%),
        radial-gradient(circle at 92% 88%, rgba(244, 201, 107, .16), transparent 30%),
        #f3fbf6;
    }

    .login-cover-bcc .login-panel {
      background:
        radial-gradient(circle at 84% 12%, rgba(255, 255, 255, .14), transparent 22%),
        linear-gradient(145deg, #17634c, #2d8a68 62%, #43a77c);
    }

    .login-cover-bcc .login-panel::before,
    .login-cover-bcc .login-panel::after {
      background: rgba(255, 255, 255, .11);
    }

    .login-animal-line {
      display: flex;
      flex-wrap: wrap;
      gap: .45rem;
      color: rgba(255, 255, 255, .78);
      font-size: .66rem;
      font-weight: 700;
    }

    .login-animal-line span {
      padding: .35rem .55rem;
      border: 1px solid rgba(255, 255, 255, .18);
      border-radius: 999px;
      background: rgba(255, 255, 255, .08);
    }

    .login-animal-line i { margin-right: .2rem; color: #b9efd0; }
    .login-cover-bcc .login-badge,
    .login-cover-bcc .login-feature { background: rgba(255, 255, 255, .11); border-color: rgba(255, 255, 255, .19); }
    .login-cover-bcc .login-feature:hover { background: rgba(255, 255, 255, .18); }
    .login-cover-bcc .login-form-area,
    .login-cover-bcd .login-form-area { background: rgba(255, 255, 255, .98); }
    .login-logo { width: 176px; height: 86px; border: 0; border-radius: 1.25rem; background: transparent; box-shadow: none; }
    .login-logo img { width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: contain; }
    .login-cover-bcc .login-brand-name,
    .login-cover-bcd .login-brand-name,
    .login-cover-bcc .text-phsa,
    .login-cover-bcd .text-phsa { color: var(--ic-green) !important; }
    .login-cover-bcc .login-brand-name:hover,
    .login-cover-bcd .login-brand-name:hover,
    .login-cover-bcc .text-phsa:hover,
    .login-cover-bcd .text-phsa:hover { color: var(--ic-green-dark) !important; }
    .login-cover-bcc .bto.bto-phsa,
    .login-cover-bcd .bto.bto-phsa { background: linear-gradient(135deg, var(--ic-green-dark), var(--ic-green)); border-color: transparent; box-shadow: 0 .9rem 1.8rem rgba(45, 138, 104, .22); }
    .login-cover-bcc .bto.bto-phsa:hover,
    .login-cover-bcd .bto.bto-phsa:hover { background: var(--ic-green-dark); }
    .login-cover-bcc .form-floating > .form-control:focus,
    .login-cover-bcc .form-floating > .form-select:focus,
    .login-cover-bcd .form-floating > .form-control:focus,
    .login-cover-bcd .form-floating > .form-select:focus { border-color: var(--ic-green); box-shadow: 0 0 0 .2rem rgba(45, 138, 104, .14); }
    .login-dedicated-label { background: var(--ic-mint); color: var(--ic-green-dark); }
    .login-dedicated-note { color: var(--ic-green-dark); }

    @media (max-width: 575.98px) { .login-logo { width: 154px; height: 76px; } }
  </style>
<?php $this->end(); ?>
