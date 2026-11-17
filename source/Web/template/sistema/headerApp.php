





<nav id="menuApp" class="navbar navbar-dark bg-dark fixed-top shadow-sm" style="display: none;">
  <div class="container-fluid d-flex justify-content-between align-items-center px-3">
    <a href="<?= Helpers::url('app') ?>" id="simpleMenuLogo" class="simpleMenuLogo me-3">
      <img src="<?=Helpers::storage($empresaPrincipal->emp_logo)?>" alt="Logo" class="img-fluid imgLogo emp_logo">
    </a>

    <!-- Perfil -->
    <?php if (!is_null($usuarioLogado->usu_id)): ?>
			<div class="dropdown">
			  <button class="btn btn-dark p-0 border-0" type="button" id="menuPerfil" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; overflow: hidden; width: 36px; height: 36px;">
			    <img src="<?= Helpers::storage($usuarioLogado->usu_logo) ?>" class="img-fluid" alt="Perfil">
			  </button>

			  <ul class="dropdown-menu dropdown-menu-end bg-dark text-white small" aria-labelledby="menuPerfil">
			    <?php if (!is_null($usuarioLogado->usu_idnivelacesso)): ?>
			      <li><a class="dropdown-item text-white" href="<?= Helpers::url('dashboard') ?>"><i class="fas fa-home me-2"></i> Dashboard</a></li>
			    <?php endif; ?>

			    <li><a class="dropdown-item text-white" href="<?= Helpers::url('app/perfil/' . $usuarioLogado->usu_id) ?>"><i class="fas fa-user me-2"></i> Meu Perfil</a></li>
			    <li><a class="dropdown-item text-white" href="<?= Helpers::url('app/feed') ?>"><i class="fas fa-book-bible me-2"></i> Devocionais</a></li>
			    <li><a class="dropdown-item text-white" href="<?= Helpers::url('app/escala') ?>"><i class="fas fa-calendar-check me-2"></i> Consultar Escala</a></li>
			    <li><a class="dropdown-item text-white" href="<?= Helpers::url('app/minhaagenda') ?>"><i class="fas fa-calendar-day me-2"></i> Minha Agenda</a></li>

			    <?php if (!is_null($usuarioLogado->permissoes)): ?>
			      <li><a class="dropdown-item text-white" href="<?= Helpers::url('usuario/chat/' . $usuarioLogado->usu_id) ?>"><i class="fas fa-envelope me-2"></i> Mensagens</a></li>
			      <li><a class="dropdown-item text-white" href="#" onclick="fgbAlterarTema()"><i class="fas fa-moon me-2"></i> Alterar Tema</a></li>
			    <?php endif; ?>

			    <li><a class="dropdown-item text-white" href="#" onclick="fgbBloquearAcesso()"><i class="fas fa-lock me-2"></i> Bloquear</a></li>

			    <li><hr class="dropdown-divider bg-secondary"></li>

			    <li><a class="dropdown-item text-white" href="#" onclick="fgbDesconectar()"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
			  </ul>
			</div>
    <?php else: ?>
      <div class="dropdown">
        <button class="btn btn-dark p-0 border-0" type="button" id="menuPerfilDeslogado" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; overflow: hidden; width: 36px; height: 36px;">
          <img src="<?= Helpers::url('storage/global/desconectado.jpg') ?>" class="img-fluid" alt="Desconectado">
        </button>
        <ul class="dropdown-menu dropdown-menu-end bg-dark text-white" aria-labelledby="menuPerfilDeslogado">
          <li><a class="dropdown-item text-white" href="<?= Helpers::url('login') ?>"><i class="fas fa-sign-in-alt me-2"></i> Conectar</a></li>
          <li><a class="dropdown-item text-white" href="<?= Helpers::url('/') ?>"><i class="fas fa-globe me-2"></i> Ir para o site</a></li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</nav>