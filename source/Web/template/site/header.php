<?php
  $siteTitle = $projeto->prj_titulo ?? 'Simple Web';
?>

<header class="sw-header">
  <nav id="navbar-site" class="navbar navbar-expand-lg sw-navbar">
    <div class="container">
      <a class="navbar-brand sw-brand" href="<?= Helpers::url('#top') ?>" aria-label="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>">
        <img class="sw-brand-logo" src="<?= Helpers::storage('storage/global/logo.png') ?>" alt="Simple Web">
      </a>

      <button class="navbar-toggler sw-navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-site-responsive" aria-controls="navbar-site-responsive" aria-expanded="false" aria-label="Abrir menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="navbar-site-responsive" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#sobre') ?>">Sobre</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#proposito') ?>">Propósito</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#recursos') ?>">Recursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#planos') ?>">Planos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= Helpers::url('#faq') ?>">Dúvidas</a></li>
          <li class="nav-item sw-nav-divider d-none d-lg-block"></li>
          <li class="nav-item"><a class="nav-link sw-login-link" href="<?= Helpers::url('login') ?>">Entrar <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i></a></li>
          <li class="nav-item"><a class="btn sw-btn sw-btn-primary sw-nav-cta" href="<?= Helpers::url('#planos') ?>">Testar a base <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a></li>
        </ul>
      </div>
    </div>
  </nav>
</header>
