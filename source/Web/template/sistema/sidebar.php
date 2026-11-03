





<div id="simpleSidebar" class="simpleSidebar bg-dark nav-dark">
  <div class="sidebarContent">
    <ul class="menu list-unstyled">
      <?php if (Helpers::pode([
        'projeto.visualizar',
        'projeto.atualizar',
        'projeto.plano.visualizar',
        'projeto.plano.atualizar',
        'projeto.contratacao.visualizar',
        'projeto.contratacao.atualizar',
        'projeto.email.visualizar',
        'projeto.email.atualizar',
        'projeto.conhecimento.visualizar',
        'projeto.conhecimento.criar',
        'projeto.conhecimento.atualizar'
      ])): ?>
        <li>
          <button class="menu-item" title="Projeto">
            <i class="fa-solid fa-diagram-project icon"></i>
            <span>&nbsp;Projeto</span>
            <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
          </button>

          <ul class="submenu list-unstyled">
            <?php if (Helpers::pode(['projeto.visualizar', 'projeto.atualizar'])): ?>
              <li>
                <a href="<?= Helpers::url('projeto') ?>" type="button" class="submenu-item" title="Cadastro do projeto">
                  <i class="fas fa-id-card"></i>&nbsp;<span>Cadastro</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode(['projeto.plano.visualizar', 'projeto.plano.atualizar'])): ?>
              <li>
                <a href="<?= Helpers::url('projeto/plano') ?>" type="button" class="submenu-item" title="Planos do projeto">
                  <i class="fas fa-layer-group"></i>&nbsp;<span>Planos</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode(['projeto.contratacao.visualizar', 'projeto.contratacao.atualizar'])): ?>
              <li>
                <a href="<?= Helpers::url('projeto/contratacao') ?>" type="button" class="submenu-item" title="Contratações do projeto">
                  <i class="fas fa-file-contract"></i>&nbsp;<span>Contratações</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode(['projeto.email.visualizar', 'projeto.email.atualizar'])): ?>
              <li>
                <a href="<?= Helpers::url('projeto/email') ?>" type="button" class="submenu-item" title="Gestão de e-mails do projeto">
                  <i class="fa-solid fa-envelope"></i>&nbsp;<span>E-mails</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar'])): ?>
              <li>
                <a href="<?= Helpers::url('projeto/conhecimento/list') ?>" type="button" class="submenu-item" title="Gestão de conhecimento do projeto">
                  <i class="fa-solid fa-book-open-reader"></i>&nbsp;<span>Conhecimento</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <?php if (Helpers::pode(['organizacao.visualizar', 'organizacao.criar', 'organizacao.atualizar', 'organizacao.plano.contratar'])): ?>
        <li>
          <?php if (Helpers::pode('contexto.trocar')): ?>
            <button class="menu-item" title="Organização">
              <i class="fa-solid fa-sitemap icon"></i>
              <span>&nbsp;Organização</span>
              <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
            </button>

            <ul class="submenu list-unstyled">
              <li>
                <a href="<?= Helpers::url('organizacao') ?>" type="button" class="submenu-item" title="Acessar organizacao do contexto">
                  <i class="fas fa-id-card"></i>&nbsp;<span>Cadastro</span>
                </a>
              </li>

              <?php if (Helpers::pode('organizacao.plano.contratar')): ?>
                <li>
                  <a href="<?= Helpers::url('organizacao/plano') ?>" type="button" class="submenu-item" title="Planos contratados da organizaÃ§Ã£o">
                    <i class="fa-solid fa-file-contract"></i>&nbsp;<span>Plano</span>
                  </a>
                </li>
              <?php endif; ?>

              <li>
                <button onclick="fgbAbrirModalContexto()" class="submenu-item" title="Alterar contexto ativo">
                  <i class="fa-solid fa-repeat"></i>&nbsp;<span>Alterar contexto</span>
                </button>
              </li>
              <?php if (Helpers::pode('organizacao.visualizar')): ?>
                <li>
                  <a href="<?= Helpers::url('organizacao/basico') ?>" class="submenu-item" title="Gerenciar cadastros básicos da organização">
                    <i class="fa-solid fa-sliders"></i>&nbsp;<span>Básico</span>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          <?php else: ?>
            <?php if (Helpers::pode('organizacao.visualizar')): ?>
              <button class="menu-item" title="Organização"><i class="fa-solid fa-sitemap icon"></i><span>&nbsp;Organização</span><i class="fa-solid fa-angle-right submenu-icon ms-auto"></i></button>
              <ul class="submenu list-unstyled">
                <li><a href="<?= Helpers::url('organizacao') ?>" class="submenu-item" title="Acessar organização"><i class="fas fa-id-card"></i>&nbsp;<span>Cadastro</span></a></li>
                <li><a href="<?= Helpers::url('organizacao/basico') ?>" class="submenu-item" title="Gerenciar cadastros básicos da organização"><i class="fa-solid fa-sliders"></i>&nbsp;<span>Básico</span></a></li>
              </ul>
            <?php else: ?>
              <a href="<?= Helpers::url('organizacao') ?>" type="button" class="menu-item" title="Organização"><i class="fa-solid fa-sitemap icon"></i><span>&nbsp;Organização</span></a>
            <?php endif; ?>
          <?php endif; ?>
        </li>
      <?php endif; ?>

      <?php if (Helpers::pode(['empresa.visualizar', 'empresa.criar', 'empresa.atualizar'])): ?>
        <li>
          <button class="menu-item" title="Empresa">
            <i class="fas fa-building icon"></i>
            <span>&nbsp;Empresa</span>
            <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
          </button>

          <ul class="submenu list-unstyled">
            <?php if (Helpers::pode('empresa.criar')): ?>
              <li>
                <a href="<?= Helpers::url('empresa/form') ?>" type="button" class="submenu-item" title="Cadastrar empresa ou filial">
                  <i class="fas fa-plus-circle"></i>&nbsp;<span>Cadastrar</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode('empresa.visualizar')): ?>
              <li>
                <a href="<?= Helpers::url('empresa/list') ?>" type="button" class="submenu-item" title="Pesquisar empresas e filiais">
                  <i class="fas fa-search"></i>&nbsp;<span>Pesquisar</span>
                </a>
              </li>

              <li>
                <button onclick="fgbPesquisaRapida('empresa')" accesskey="e" class="submenu-item" title="Pesquisa rápida de empresa">
                  <i class="fas fa-bolt"></i>&nbsp;<span>Pesquisa rápida</span>
                </button>
              </li>
            <?php endif; ?>
            <?php if (Helpers::pode('empresa.visualizar')): ?>
              <li>
                <a href="<?= Helpers::url('empresa/basico') ?>" class="submenu-item" title="Gerenciar cadastros básicos da empresa">
                  <i class="fa-solid fa-sliders"></i>&nbsp;<span>Básico</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <?php if (Helpers::pode(['usuario.visualizar', 'usuario.criar', 'usuario.atualizar'])): ?>
        <li>
          <button class="menu-item" title="Usuário">
            <i class="fa-solid fa-user-shield icon"></i>
            <span>&nbsp;Usuário</span>
            <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
          </button>

          <ul class="submenu list-unstyled">
            <?php if (Helpers::pode('usuario.visualizar')): ?>
              <?php if (Helpers::pode('usuario.criar')): ?>
                <li>
                  <a href="<?= Helpers::url('usuario/form') ?>" type="button" class="submenu-item" title="Cadastrar usuário">
                    <i class="fas fa-plus-circle"></i>&nbsp;<span>Cadastrar</span>
                  </a>
                </li>
              <?php endif; ?>

              <li>
                <a href="<?= Helpers::url('usuario/list') ?>" type="button" class="submenu-item" title="Pesquisar usuários">
                  <i class="fas fa-search"></i>&nbsp;<span>Pesquisar</span>
                </a>
              </li>

              <li>
                <button onclick="fgbPesquisaRapida('usuario')" accesskey="u" class="submenu-item" title="Pesquisa rápida de usuário">
                  <i class="fas fa-bolt"></i>&nbsp;<span>Pesquisa rápida</span>
                </button>
              </li>
            <?php endif; ?>
            <?php if (Helpers::pode('usuario.visualizar')): ?>
              <li>
                <a href="<?= Helpers::url('usuario/basico') ?>" class="submenu-item" title="Gerenciar cadastros básicos de usuário">
                  <i class="fa-solid fa-sliders"></i>&nbsp;<span>Básico</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <?php if (Helpers::pode(['nivelacesso.visualizar', 'nivelacesso.criar', 'nivelacesso.atualizar'])): ?>
        <li>
          <button class="menu-item" title="Acesso">
            <i class="fas fa-lock icon"></i>
            <span>&nbsp;Nível de acesso</span>
            <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
          </button>

          <ul class="submenu list-unstyled">
            <?php if (Helpers::pode('nivelacesso.criar')): ?>
              <li>
                <a href="<?= Helpers::url('nivelacesso/form') ?>" type="button" class="submenu-item" title="Cadastrar nível de acesso">
                  <i class="fas fa-plus-circle"></i>&nbsp;<span>Cadastrar</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode('nivelacesso.visualizar')): ?>
              <li>
                <a href="<?= Helpers::url('nivelacesso/list') ?>" type="button" class="submenu-item" title="Pesquisar níveis de acesso">
                  <i class="fas fa-search"></i>&nbsp;<span>Pesquisar</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <?php if (Helpers::pode([
        'financeiro.visualizar',
        'financeiro.dashboard',
        'financeiro.relatorio',
        'financeiro.lancamento.visualizar',
        'financeiro.transferencia.visualizar',
        'financeiro.basico.visualizar'
      ])): ?>
        <li>
          <button class="menu-item" title="Financeiro">
            <i class="fa-solid fa-wallet icon"></i>
            <span>&nbsp;Financeiro</span>
            <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
          </button>

          <ul class="submenu list-unstyled">
            <?php if (Helpers::pode(['financeiro.visualizar', 'financeiro.dashboard'])): ?>
              <li>
                <a href="<?= Helpers::url('financeiro/dashboard') ?>" type="button" class="submenu-item" title="Dashboard financeiro">
                  <i class="fa-solid fa-chart-line"></i>&nbsp;<span>Dashboard</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode(['financeiro.visualizar', 'financeiro.relatorio'])): ?>
              <li>
                <a href="<?= Helpers::url('financeiro/relatorio') ?>" type="button" class="submenu-item" title="Relatórios financeiros">
                  <i class="fa-solid fa-file-lines"></i>&nbsp;<span>Relatórios</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode(['financeiro.visualizar', 'financeiro.transferencia.visualizar'])): ?>
              <li>
                <a href="<?= Helpers::url('financeiro/transferencia') ?>" type="button" class="submenu-item" title="Transferencia entre contas">
                  <i class="fa-solid fa-money-bill-transfer"></i>&nbsp;<span>Transferência</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode(['financeiro.visualizar', 'financeiro.basico.visualizar'])): ?>
              <li>
                <a href="<?= Helpers::url('financeiro/basico') ?>" type="button" class="submenu-item" title="Cadastros financeiros basicos">
                  <i class="fa-solid fa-sliders"></i>&nbsp;<span>Básico</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <?php if (Helpers::pode(['titulo.visualizar', 'titulo.criar', 'titulo.atualizar'])): ?>
        <li>
          <button class="menu-item" title="Titulo">
            <i class="fa-solid fa-file-invoice-dollar icon"></i>
            <span>&nbsp;Título</span>
            <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
          </button>

          <ul class="submenu list-unstyled">
            <?php if (Helpers::pode('titulo.criar')): ?>
              <li>
                <a href="<?= Helpers::url('titulo/form') ?>" type="button" class="submenu-item" title="Cadastrar titulo">
                  <i class="fas fa-plus-circle"></i>&nbsp;<span>Cadastrar</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode('titulo.visualizar')): ?>
              <li>
                <a href="<?= Helpers::url('titulo/list') ?>" type="button" class="submenu-item" title="Pesquisar titulos">
                  <i class="fas fa-search"></i>&nbsp;<span>Pesquisar</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode('titulo.visualizar')): ?>
              <li>
                <button onclick="fgbPesquisaRapida('titulo', 'título')" type="button" class="submenu-item" title="Pesquisa rápida de título">
                  <i class="fas fa-bolt"></i>&nbsp;<span>Pesquisa rápida</span>
                </button>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <?php if (Helpers::pode(['fornecedor.visualizar', 'fornecedor.criar', 'fornecedor.atualizar'])): ?>
        <li>
          <button class="menu-item" title="Fornecedor">
            <i class="fa-solid fa-truck-field icon"></i>
            <span>&nbsp;Fornecedor</span>
            <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
          </button>

          <ul class="submenu list-unstyled">
            <?php if (Helpers::pode('fornecedor.criar')): ?>
              <li>
                <a href="<?= Helpers::url('fornecedor/form') ?>" type="button" class="submenu-item" title="Cadastrar fornecedor">
                  <i class="fas fa-plus-circle"></i>&nbsp;<span>Cadastrar</span>
                </a>
              </li>
            <?php endif; ?>

            <?php if (Helpers::pode('fornecedor.visualizar')): ?>
              <li>
                <a href="<?= Helpers::url('fornecedor/list') ?>" type="button" class="submenu-item" title="Pesquisar fornecedores">
                  <i class="fas fa-search"></i>&nbsp;<span>Pesquisar</span>
                </a>
              </li>
              <li>
                <button onclick="fgbPesquisaRapida('fornecedor', 'fornecedor')" type="button" class="submenu-item" title="Pesquisa rápida de fornecedor">
                  <i class="fas fa-bolt"></i>&nbsp;<span>Pesquisa rápida</span>
                </button>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <li>
        <a href="<?= Helpers::url('ajuda') ?>" class="menu-item" title="Central de ajuda">
          <i class="fa-solid fa-circle-question icon"></i>
          <span>&nbsp;Ajuda</span>
        </a>
      </li>

      <li>
        <a target="_blank" href="<?= Helpers::url() ?>" class="menu-item" title="Site">
          <i class="fas fa-globe icon"></i>
          <span>&nbsp;Site</span>
        </a>
      </li>

      <li>
        <button onclick="fgbAlternarSidebar(3)" class="menu-item" id="hideSidebar" title="Esconder menu">
          <i class="fas fa-eye-slash icon"></i>
          <span>&nbsp;Esconder menu</span>
        </button>
      </li>
    </ul>
  </div>

  <ul class="list-unstyled sidebarFixedBottom">
    <li class="menu-item profile-item" id="btnProfileSidebar" title="<?= $usuarioLogado->usu_nomefantasia ?? $usuarioLogado->usu_razaosocial ?? 'Usuário' ?>">
      <img src="<?= Helpers::storage($usuarioLogado->usu_logo) ?>" alt="Perfil" class="icon profile-icon usu_logo-<?=$usuarioLogado->usu_id?>">

      <span>
        <div class="profile-name">
          <?= Helpers::limitarString($usuarioLogado->usu_nomefantasia ?? $usuarioLogado->usu_razaosocial ?? 'Usuário', 15) ?>
        </div>

        <div
          class="small lh-1 mt-1 opacity-75 overflow-hidden text-truncate"
          title="<?= $usuarioLogado->usu_email ?? $usuarioLogado->usu_username ?? '' ?>"
          style="max-width: 145px; font-size: .68rem;"
        >
          <?= Helpers::limitarString($usuarioLogado->usu_email ?? $usuarioLogado->usu_username ?? '', 22) ?>
        </div>
      </span>

      <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
    </li>

    <ul class="submenu list-unstyled profile-submenu">
      <?php if (!empty($contexto) && Helpers::pode('contexto.trocar')): ?>
        <li>
          <button
            type="button"
            class="submenu-item"
            id="btnSidebarContexto"
            onclick="fgbAbrirModalContexto()"
            title="Trocar organização de trabalho"
          >
            <i class="fa-solid fa-repeat icon"></i><span>&nbsp;Organização</span>
          </button>
        </li>
      <?php endif; ?>

      <li>
        <a href="<?= Helpers::url('usuario/form/' . $usuarioLogado->usu_id) ?>" class="submenu-item" id="btnSidebarMinhaConta" title="Acessar dados da minha conta">
          <i class="fa-solid fa-user-gear icon"></i><span>&nbsp;Minha Conta</span>
        </a>
      </li>

      <li>
        <a href="<?= Helpers::url('usuario/chat/' . $usuarioLogado->usu_id) ?>" class="submenu-item" id="btnSidebarMensagens" title="Acessar minhas mensagens">
          <i class="fa-solid fa-envelope icon"></i><span>&nbsp;Mensagens</span>
        </a>
      </li>

      <li>
        <a href="#" onclick="fgbBloquearAcesso(); return false;" class="submenu-item" id="btnSidebarBloquear" title="Bloquear a sessão atual">
          <i class="fa-solid fa-lock icon"></i><span>&nbsp;Bloquear</span>
        </a>
      </li>

      <li>
        <button
          type="button"
          class="submenu-item"
          id="btnSidebarMenuPreferencias"
          title="Configurar modo de exibição do menu lateral"
        >
          <i class="fa-solid fa-bars-staggered icon"></i><span>&nbsp;Menu</span>
          <i class="fa-solid fa-angle-right submenu-icon ms-auto"></i>
        </button>

        <ul class="sidebar-menu-options list-unstyled" style="display: none;">
          <li>
            <button onclick="fgbAlternarSidebar(1)" class="submenu-item ps-3" id="btnSidebarModoAberto" data-sidebar-mode="1" title="Mantém o menu lateral aberto com ícones e textos visíveis">
              <i class="fa-solid fa-table-columns"></i>&nbsp;<span>Aberto</span>
              <i class="fa-solid fa-check ms-auto <?= (int) ($usuarioLogado->usu_sidebar ?? 1) == 1 ? '' : 'opacity-0' ?>"></i>
            </button>
          </li>

          <li>
            <button onclick="fgbAlternarSidebar(2)" class="submenu-item ps-3" id="btnSidebarModoIcones" data-sidebar-mode="2" title="Exibe o menu lateral compacto, mantendo apenas os ícones visíveis">
              <i class="fa-solid fa-grip-vertical"></i>&nbsp;<span>Ícones</span>
              <i class="fa-solid fa-check ms-auto <?= (int) ($usuarioLogado->usu_sidebar ?? 1) == 2 ? '' : 'opacity-0' ?>"></i>
            </button>
          </li>

          <li>
            <button onclick="fgbAlternarSidebar(3)" class="submenu-item ps-3" id="btnSidebarModoOculto" data-sidebar-mode="3" title="Esconde completamente o menu lateral para maximizar a área de trabalho">
              <i class="fa-solid fa-eye-slash"></i>&nbsp;<span>Oculto</span>
              <i class="fa-solid fa-check ms-auto <?= (int) ($usuarioLogado->usu_sidebar ?? 1) == 3 ? '' : 'opacity-0' ?>"></i>
            </button>
          </li>

          <li class="d-none d-lg-block">
            <button onclick="fgbAlternarSidebar(4)" class="submenu-item ps-3" id="btnSidebarModoSobreposicao" data-sidebar-mode="4" title="Mantém o menu escondido e exibe temporariamente em sobreposição ao passar o mouse na logo">
              <i class="fa-solid fa-window-restore"></i>&nbsp;<span>Sobreposição</span>
              <i class="fa-solid fa-check ms-auto <?= (int) ($usuarioLogado->usu_sidebar ?? 1) == 4 ? '' : 'opacity-0' ?>"></i>
            </button>
          </li>
        </ul>
      </li>

      <li>
        <a href="#" onclick="fgbDesconectar(); return false;" class="submenu-item" id="btnSidebarSair" title="Sair do sistema">
          <i class="fa-solid fa-right-from-bracket icon"></i><span>&nbsp;Sair</span>
        </a>
      </li>
    </ul>
  </ul>
</div>
