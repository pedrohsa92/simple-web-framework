<?php
  $pwaConfig = $pwaConfig ?? null;
  $pwaImagens = is_array($pwaImagens ?? null) ? $pwaImagens : ['icones' => [], 'capturas' => []];
  $escPwa = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>

<header class="phsaScrollHeader">
  <nav class="phsaScrollNav" data-menu="tabWebappPwa">
    <ul class="phsaScrollList">
      <li class="phsaScrollItem active" data-target="configuracaoPwa"><i class="fa-solid fa-sliders"></i>&nbsp;Configuração</li>
      <li class="phsaScrollItem" data-target="imagensPwa"><i class="fa-solid fa-images"></i>&nbsp;Imagens</li>
      <li class="phsaScrollItem" data-target="historicoPwa"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li>
    </ul>
  </nav>
</header>

<div class="phsaTabContent phsaTabSubContent">
  <div class="phsaTabItem p-3 active" id="configuracaoPwa">
    <?php include 'source/Web/sistema/empresa/webappPwaConfiguracao.php'; ?>
  </div>
  <div class="phsaTabItem p-3" id="imagensPwa">
    <?php include 'source/Web/sistema/empresa/webappPwaImagens.php'; ?>
  </div>
  <div class="phsaTabItem p-3" id="historicoPwa">
    <?php include 'source/Web/sistema/empresa/webappPwaHistorico.php'; ?>
  </div>
</div>
