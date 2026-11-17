<?php $this->layout("sistema::sistema"); ?>

<?php
  $recursoTitulo = ucfirst((string) ($limite->recurso_titulo ?? 'recurso'));
  $planoNome = (string) ($limite->plano ?? 'plano atual');
  $total = (int) ($limite->total ?? 0);
  $limiteValor = (int) ($limite->limite ?? 0);
  $urlVoltar = $limite->url_voltar ?? Helpers::url('dashboard');
?>

<div class="card border-phsa-light box-phsa-shadow-light">
  <div class="card-body text-center py-5 px-3 px-md-5">
    <div class="mb-4 text-warning">
      <i class="fa-solid fa-chart-simple fa-4x"></i>
    </div>

    <span class="badge bg-warning text-dark mb-3">Limite do plano</span>
    <h1 class="h3 text-phsa mb-3">Limite de <?= htmlspecialchars($recursoTitulo, ENT_QUOTES, 'UTF-8') ?> atingido</h1>
    <p class="lead mb-4">
      O plano <strong><?= htmlspecialchars($planoNome, ENT_QUOTES, 'UTF-8') ?></strong> permite até
      <strong><?= number_format($limiteValor, 0, ',', '.') ?></strong> <?= htmlspecialchars(strtolower($recursoTitulo), ENT_QUOTES, 'UTF-8') ?>.
    </p>

    <div class="alert alert-light border text-start mx-auto mb-4" style="max-width: 620px;">
      <i class="fa-solid fa-circle-info text-phsa me-2"></i>
      Sua organização já utiliza <strong><?= number_format($total, 0, ',', '.') ?></strong> de
      <strong><?= number_format($limiteValor, 0, ',', '.') ?></strong> <?= htmlspecialchars(strtolower($recursoTitulo), ENT_QUOTES, 'UTF-8') ?> disponíveis.
    </div>

    <p class="text-secondary mb-4">Para cadastrar mais <?= htmlspecialchars(strtolower($recursoTitulo), ENT_QUOTES, 'UTF-8') ?>, será necessário utilizar um plano com limite maior.</p>

    <a href="<?= htmlspecialchars($urlVoltar, ENT_QUOTES, 'UTF-8') ?>" class="bto bto-phsa-light">
      <i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar para <?= htmlspecialchars(strtolower($recursoTitulo), ENT_QUOTES, 'UTF-8') ?>
    </a>
  </div>
</div>
