<header class="phsaScrollHeader mb-3">
  <nav class="phsaScrollNav" data-menu="tabConfiguracaoProjeto">
    <ul class="phsaScrollList">
      <li class="phsaScrollItem active" data-target="parametro">
        <i class="fa-solid fa-sliders"></i>&nbsp;Parâmetro
      </li>
      <?php if (!empty($podeVisualizarPalavrasChaves)): ?>
      <li class="phsaScrollItem" data-target="palavrasChavesProjeto">
        <i class="fa-solid fa-tags"></i>&nbsp;Palavras-chave
      </li>
      <?php endif; ?>
      <li class="phsaScrollItem" data-target="gateway">
        <i class="fa-solid fa-credit-card"></i>&nbsp;Gateway
      </li>
    </ul>
  </nav>
</header>

<div class="phsaTabContent">
  <div class="phsaTabItem active" id="parametro">
    <div class="alert alert-light border mb-0">
      A aba <strong>Parâmetro</strong> está reservada para configurações técnicas e regras globais do projeto.
    </div>
  </div>

  <?php if (!empty($podeVisualizarPalavrasChaves)): ?>
  <div class="phsaTabItem" id="palavrasChavesProjeto">
    <?php include __DIR__ . '/palavrachave.php'; ?>
  </div>
  <?php endif; ?>

  <div class="phsaTabItem" id="gateway">
    <?php include __DIR__ . '/gateway.php'; ?>
  </div>
</div>
