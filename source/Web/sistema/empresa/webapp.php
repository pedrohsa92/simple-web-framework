<?php $escWebapp = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'); ?>

<header class="phsaScrollHeader">
  <nav class="phsaScrollNav" data-menu="tabWebapp">
    <ul class="phsaScrollList">
      <li class="phsaScrollItem active" data-target="paginaWebapp"><i class="fa-solid fa-file-lines"></i>&nbsp;Página</li>
      <li class="phsaScrollItem" data-target="pwaWebapp"><i class="fa-solid fa-mobile-screen-button"></i>&nbsp;PWA</li>
    </ul>
  </nav>
</header>

<div class="phsaTabContent phsaTabSubContent">
  <div class="phsaTabItem p-3 active" id="paginaWebapp">
    <?php include 'source/Web/sistema/empresa/webappPagina.php'; ?>
  </div>
  <div class="phsaTabItem" id="pwaWebapp">
    <?php include 'source/Web/sistema/empresa/webappPwa.php'; ?>
  </div>
</div>
