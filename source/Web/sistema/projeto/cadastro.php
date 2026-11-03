<header class="phsaScrollHeader mb-3">
  <nav class="phsaScrollNav" data-menu="tabCadastroProjeto">
    <ul class="phsaScrollList">
      <li class="phsaScrollItem active" data-target="dadosBasicosProjeto">
        <i class="fa-solid fa-id-card"></i>&nbsp;Dados básicos
      </li>
      <li class="phsaScrollItem" data-target="imagensProjeto">
        <i class="fa-solid fa-images"></i>&nbsp;Imagens
      </li>
      <li class="phsaScrollItem" data-target="termosUsoProjeto">
        <i class="fa-solid fa-file-contract"></i>&nbsp;Termos de uso
      </li>
      <li class="phsaScrollItem" data-target="avisoPrivacidadeProjeto">
        <i class="fa-solid fa-user-shield"></i>&nbsp;Aviso de privacidade
      </li>
    </ul>
  </nav>
</header>

<div class="phsaTabContent phsaTabSubContent">
  <div class="phsaTabItem active" id="dadosBasicosProjeto">
    <?php include __DIR__ . '/dadosBasicos.php'; ?>
  </div>

  <div class="phsaTabItem" id="imagensProjeto">
    <?php include __DIR__ . '/imagens.php'; ?>
  </div>

  <div class="phsaTabItem" id="termosUsoProjeto">
    <?php include __DIR__ . '/termosUso.php'; ?>
  </div>

  <div class="phsaTabItem" id="avisoPrivacidadeProjeto">
    <?php include __DIR__ . '/avisoPrivacidade.php'; ?>
  </div>
</div>
