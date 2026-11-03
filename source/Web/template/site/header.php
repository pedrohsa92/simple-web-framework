<?php
  $siteTitle = $projeto->prj_titulo ?? 'icliv';
?>

<header class="ic-header">
  <nav id="navbar-site" class="navbar navbar-expand-lg ic-navbar">
    <div class="container">
      <a class="navbar-brand ic-brand" href="<?= Helpers::url('#top') ?>" aria-label="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>">
        <img class="ic-brand-logo" src="<?= Helpers::storage('storage/global/icliv.png') ?>" alt="icliv — gestão que cuida">
      </a>

      <button class="navbar-toggler ic-navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-site-responsive" aria-controls="navbar-site-responsive" aria-expanded="false" aria-label="Abrir menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="navbar-site-responsive" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#recursos') ?>">Recursos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#como-funciona') ?>">Como funciona</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#planos') ?>">Planos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#faq') ?>">Dúvidas</a></li>
          <li class="nav-item ic-nav-divider d-none d-lg-block"></li>
          <li class="nav-item"><a class="nav-link ic-login-link" href="<?= Helpers::url('login') ?>">Entrar <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i></a></li>
          <li class="nav-item"><a class="btn ic-btn ic-btn-primary ic-nav-cta" href="<?= Helpers::url('#planos') ?>">Começar agora <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a></li>
        </ul>
      </div>
    </div>
  </nav>
</header>
