<nav id="simpleNavbar" class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <div id="simpleMenuLogo" class="simpleMenuLogo me-2">
      <img
        onclick="fgbAlternarSidebarClickLogo()"
        src="<?= Helpers::storage($contexto->empresa->emp_logo ?? $contexto->organizacao->org_logo ?? $usuarioLogado->empresa->emp_logo ?? $usuarioLogado->organizacao->org_logo ?? $projeto->prj_logo ?? 'storage/global/semimagem.jpg', 'storage/global/semimagem.jpg') ?>"
        alt="<?= $contexto->empresa->emp_nomefantasia ?? $contexto->empresa->emp_razaosocial ?? $usuarioLogado->empresa->emp_nomefantasia ?? $usuarioLogado->empresa->emp_razaosocial ?? $projeto->prj_titulo ?? 'Logo' ?>"
        class="img-fluid imgLogo emp_logo"
      >
    </div>

    <button
      type="button"
      id="btnHeaderContexto"
      class="btn btn-dark text-start d-none d-md-flex align-items-center me-auto px-2 py-1"
      title="<?= 
        ($contexto->organizacao->org_nomefantasia ?? $contexto->organizacao->org_razaosocial ?? $usuarioLogado->organizacao->org_nomefantasia ?? $usuarioLogado->organizacao->org_razaosocial ?? $projeto->prj_titulo ?? 'Organização')
        . ' / '
        . ($contexto->empresa->emp_nomefantasia ?? $contexto->empresa->emp_razaosocial ?? $usuarioLogado->empresa->emp_nomefantasia ?? $usuarioLogado->empresa->emp_razaosocial ?? 'Empresa')
      ?>"
      onclick="fgbAlternarSidebarClickLogo()"
    >
      <span class="d-block lh-sm">
        <span class="d-block fw-semibold text-truncate" style="max-width: 280px;">
          <?= Helpers::limitarString($contexto->organizacao->org_nomefantasia ?? $contexto->organizacao->org_razaosocial ?? $usuarioLogado->organizacao->org_nomefantasia ?? $usuarioLogado->organizacao->org_razaosocial ?? $projeto->prj_titulo ?? 'Organização', 40) ?>
        </span>

        <span class="d-block small opacity-75 text-truncate" style="max-width: 280px;">
          <?= Helpers::limitarString(
            ($contexto->empresa->emp_nomefantasia ?? $contexto->empresa->emp_razaosocial ?? $usuarioLogado->empresa->emp_nomefantasia ?? $usuarioLogado->empresa->emp_razaosocial ?? 'Empresa')
            . (
              !empty($contexto->empresa->emp_cnpjcpf ?? $usuarioLogado->empresa->emp_cnpjcpf ?? null)
                ? ' · ' . Helpers::adicionaMascaraCnpjCPf($contexto->empresa->emp_cnpjcpf ?? $usuarioLogado->empresa->emp_cnpjcpf)
                : ''
            ),
            48
          ) ?>
        </span>
      </span>
    </button>

    <button
      class="navbar-toggler border-0 p-0"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarContent"
      aria-controls="navbarContent"
      aria-expanded="false"
      aria-label="Toggle navigation"
      title="Abrir Menu"
      style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden;"
    >
      <img
        src="<?= Helpers::storage($usuarioLogado->usu_logo) ?>"
        alt="Perfil"
        class="img-fluid imgLogo usu_logo-<?=$usuarioLogado->usu_id?>"
      >
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav w-100 justify-content-lg-end">
        <li class="nav-item d-lg-none w-100">
          <button
            type="button"
            class="btn btn-dark text-start w-100"
            title="<?= 
              ($contexto->organizacao->org_nomefantasia ?? $contexto->organizacao->org_razaosocial ?? $usuarioLogado->organizacao->org_nomefantasia ?? $usuarioLogado->organizacao->org_razaosocial ?? $projeto->prj_titulo ?? 'Organização')
              . ' / '
              . ($contexto->empresa->emp_nomefantasia ?? $contexto->empresa->emp_razaosocial ?? $usuarioLogado->empresa->emp_nomefantasia ?? $usuarioLogado->empresa->emp_razaosocial ?? 'Empresa')
            ?>"
            onclick="fgbAlternarSidebarClickLogo()"
          >
            <i class="fa-solid fa-layer-group me-2 icon"></i>
            <span>
              <?= Helpers::limitarString($contexto->organizacao->org_nomefantasia ?? $contexto->organizacao->org_razaosocial ?? $usuarioLogado->organizacao->org_nomefantasia ?? $usuarioLogado->organizacao->org_razaosocial ?? $projeto->prj_titulo ?? 'Organização', 28) ?>
            </span>

            <div class="small opacity-75 ps-4">
              <?= Helpers::limitarString(
                ($contexto->empresa->emp_nomefantasia ?? $contexto->empresa->emp_razaosocial ?? $usuarioLogado->empresa->emp_nomefantasia ?? $usuarioLogado->empresa->emp_razaosocial ?? 'Empresa')
                . (
                  !empty($contexto->empresa->emp_cnpjcpf ?? $usuarioLogado->empresa->emp_cnpjcpf ?? null)
                    ? ' · ' . Helpers::adicionaMascaraCnpjCPf($contexto->empresa->emp_cnpjcpf ?? $usuarioLogado->empresa->emp_cnpjcpf)
                    : ''
                ),
                36
              ) ?>
            </div>
          </button>
        </li>

        <?php if (!empty($contexto) && Helpers::pode('contexto.trocar')): ?>
          <li class="nav-item d-lg-none w-100">
            <button
              type="button"
              class="btn btn-dark text-start w-100"
              onclick="fgbAbrirModalContexto()"
              title="Trocar organização de trabalho"
            >
              <i class="fa-solid fa-repeat me-2 icon"></i>Trocar organização
            </button>
          </li>
        <?php endif; ?>

        <li class="nav-item d-lg-none w-100">
          <a href="<?= Helpers::url('dashboard') ?>" type="button" class="btn btn-dark text-start w-100" title="Página inicial">
            <i class="fa-solid fa-house icon"></i> Dashboard
          </a>
        </li>

        <?php if (!is_null($usuarioLogado->permissoes ?? null)): ?>
          <li class="nav-item d-lg-none w-100 pt-3">
            <button onclick="fgbPesquisaGlobal()" class="btn btn-dark text-start w-100" title="Pesquisar">
              <i class="fas fa-search me-2 icon"></i> Pesquisar
            </button>
          </li>

          <li class="nav-item d-lg-none w-100">
            <button onclick="fgbVisualizarNotificacao()" class="btn btn-dark text-start w-100" title="Notificação">
              <i class="fas fa-bell me-2 icon"></i> Notificação
            </button>
          </li>

          <li class="nav-item d-lg-none w-100">
            <button onclick="fgbAlterarTema()" class="btn btn-dark text-start w-100" title="Tema">
              <i class="fas fa-moon me-2 icon"></i> Tema
            </button>
          </li>
        <?php endif; ?>

        <li class="nav-item d-lg-none w-100">
          <button
            class="btn btn-dark text-start w-100"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#userSubmenu"
            aria-expanded="false"
            aria-controls="userSubmenu"
            title="Usuário"
          >
            <i class="fas fa-user me-2 icon"></i>Usuário
          </button>

          <div class="collapse" id="userSubmenu">
            <ul class="list-unstyled ps-4">
              <li>
                <a class="btn btn-dark text-start w-100" title="Minha Conta" href="<?= Helpers::url('usuario/form/' . $usuarioLogado->usu_id) ?>">
                  <i class="fas fa-user me-2 icon"></i>Minha Conta
                </a>
              </li>

              <?php if (!is_null($usuarioLogado->permissoes ?? null)): ?>
                <li>
                  <a class="btn btn-dark text-start w-100" title="Mensagens" href="<?= Helpers::url('usuario/chat/' . $usuarioLogado->usu_id) ?>">
                    <i class="fas fa-envelope me-2 icon"></i>Mensagens
                  </a>
                </li>
              <?php endif; ?>

              <li>
                <a class="btn btn-dark text-start w-100" title="Bloquear" href="#" onclick="fgbBloquearAcesso(); return false;">
                  <i class="fas fa-lock me-2 icon"></i>Bloquear
                </a>
              </li>

              <li>
                <a class="btn btn-dark text-start w-100" title="Sair" href="#" onclick="fgbDesconectar(); return false;">
                  <i class="fas fa-sign-out-alt me-2 icon"></i>Sair
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item d-none d-lg-block">
          <a href="<?= Helpers::url('dashboard') ?>" type="button" class="btn btn-dark me-2" title="Página inicial">
            <i class="fa-solid fa-house icon"></i>
          </a>
        </li>

        <?php if (!is_null($usuarioLogado->permissoes ?? null)): ?>
          <li class="nav-item d-none d-lg-block">
            <button onclick="fgbPesquisaGlobal()" class="btn btn-dark me-2" title="Pesquisar">
              <i class="fas fa-search icon"></i>
            </button>
          </li>

          <li class="nav-item d-none d-lg-block">
            <button onclick="fgbVisualizarNotificacao()" class="btn btn-dark me-2" title="Notificação">
              <i class="fas fa-bell icon"></i>
            </button>
          </li>

          <li class="nav-item d-none d-lg-block">
            <button onclick="fgbAlterarTema()" class="btn btn-dark me-2" title="Tema">
              <i class="fas fa-moon icon"></i>
            </button>
          </li>
        <?php endif; ?>

        <li class="nav-item dropdown d-none d-lg-block">
          <button
            class="btn btn-dark dropdown-toggle p-0"
            id="profileMenu"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            title="<?= Helpers::limitarString($usuarioLogado->usu_nomefantasia ?? $usuarioLogado->usu_razaosocial ?? 'Usuário', 30) ?>"
            style="border-radius: 50%; overflow: hidden; width: 40px; height: 40px;"
          >
            <img
              src="<?= Helpers::storage($usuarioLogado->usu_logo) ?>"
              alt="<?= Helpers::limitarString($usuarioLogado->usu_nomefantasia ?? $usuarioLogado->usu_razaosocial ?? 'Usuário', 30) ?>"
              class="img-fluid imgLogo usu_logo-<?=$usuarioLogado->usu_id?>"
            >
          </button>

          <ul class="dropdown-menu bg-dark dropdown-menu-end" aria-labelledby="profileMenu">
            <li>
              <div class="dropdown-item text-center text-uppercase">
                <div class="w-100 p-0 m-0 gx-0">
                  <?= Helpers::limitarString($usuarioLogado->usu_nomefantasia ?? $usuarioLogado->usu_razaosocial ?? 'Usuário', 18) ?>
                </div>

                <div class="w-100 p-0 m-0 gx-0 small text-lowercase">
                  <?= Helpers::limitarString($usuarioLogado->usu_email ?? $usuarioLogado->usu_username ?? '', 28) ?>
                </div>
              </div>
            </li>

            <?php if (!empty($contexto) && Helpers::pode('contexto.trocar')): ?>
              <li>
                <button type="button" class="dropdown-item" onclick="fgbAbrirModalContexto()">
                  <i class="fa-solid fa-repeat me-2 icon"></i>Trocar organização
                </button>
              </li>
            <?php endif; ?>

            <li>
              <a class="dropdown-item" href="<?= Helpers::url('usuario/form/' . $usuarioLogado->usu_id) ?>">
                <i class="fas fa-user-cog me-2 icon"></i>Minha Conta
              </a>
            </li>

            <?php if (!is_null($usuarioLogado->permissoes ?? null)): ?>
              <li>
                <a class="dropdown-item" href="<?= Helpers::url('usuario/chat/' . $usuarioLogado->usu_id) ?>">
                  <i class="fas fa-envelope me-2 icon"></i>Mensagens
                </a>
              </li>
            <?php endif; ?>

            <li>
              <a class="dropdown-item" href="#" onclick="fgbBloquearAcesso(); return false;">
                <i class="fas fa-lock me-2 icon"></i>Bloquear
              </a>
            </li>

            <div class="dropdown-divider"></div>

            <li>
              <a class="dropdown-item" href="#" onclick="fgbDesconectar(); return false;">
                <i class="fas fa-sign-out-alt me-2 icon"></i>Sair
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>