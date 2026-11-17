<header class="phsaScrollHeader">
  <nav class="phsaScrollNav" data-menu="tabConfiguracao">
    <ul class="phsaScrollList">
      <li class="phsaScrollItem active" data-target="ambiente">
        <i class="fa-solid fa-server"></i>&nbsp;Ambiente
      </li>

      <li class="phsaScrollItem" data-target="parametro">
        <i class="fa-solid fa-sliders"></i>&nbsp;Parâmetro
      </li>

      <li class="phsaScrollItem" data-target="preferencia">
        <i class="fa-solid fa-palette"></i>&nbsp;Preferência
      </li>
    </ul>
  </nav>
</header>

<div class="phsaTabContent">
  <div class="phsaTabItem active" id="ambiente">
    <?php include 'source/Web/sistema/organizacao/configuracaoAmbiente.php'; ?>
  </div>

  <div class="phsaTabItem" id="parametro">
    <?php include 'source/Web/sistema/organizacao/configuracaoParametro.php'; ?>
  </div>

  <div class="phsaTabItem" id="preferencia">
    <?php include 'source/Web/sistema/organizacao/configuracaoPreferencia.php'; ?>
  </div>
</div>
