<header class="phsaScrollHeader">
  <nav class="phsaScrollNav" data-menu="tabCadastro">
    <ul class="phsaScrollList">
      <li class="phsaScrollItem active" data-target="dadosbasicos">
        <i class="fa-solid fa-id-card"></i>&nbsp;Dados básicos
      </li>

      <?php if (!is_null($organizacao) && !is_null($organizacao->org_id)): ?>
        <li class="phsaScrollItem" data-target="endereco">
          <i class="fa-solid fa-map-marker-alt"></i>&nbsp;Endereço
        </li>

        <li class="phsaScrollItem" data-target="contato">
          <i class="fa-solid fa-phone"></i>&nbsp;Contato
        </li>

        <li class="phsaScrollItem" data-target="arquivo">
          <i class="fa-solid fa-file-circle-check"></i>&nbsp;Arquivo
        </li>

        <li class="phsaScrollItem" data-target="extra">
          <i class="fa-solid fa-layer-group"></i>&nbsp;Extra
        </li>
      <?php endif ?>
    </ul>
  </nav>
</header>

<div class="phsaTabContent">
  <div class="phsaTabItem active" id="dadosbasicos">
    <?php include 'source/Web/sistema/organizacao/cadastroDadosBasicos.php'; ?>
  </div>

  <?php if (!is_null($organizacao) && !is_null($organizacao->org_id)): ?>
    <div class="phsaTabItem" id="endereco">
      <?php include 'source/Web/sistema/endereco/form.php'; ?>
    </div>

    <div class="phsaTabItem" id="contato">
      <?php include 'source/Web/sistema/contato/form.php'; ?>
    </div>

    <div class="phsaTabItem" id="arquivo">
      <?php include 'source/Web/sistema/arquivo/arquivo.php'; ?>
    </div>

    <div class="phsaTabItem" id="extra">
      <?php include 'source/Web/sistema/organizacao/extra.php'; ?>
    </div>
  <?php endif ?>
</div>
