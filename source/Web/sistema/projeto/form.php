<?php
  $this->layout('sistema::sistema');
  $totalOrganizacoes = (int) ($totalOrganizacoes ?? 0);
?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <h4 class="m-0">Projeto</h4>
        <div class="text-muted small mt-1">Administre a base central do LevHub e acompanhe sua operação.</div>
      </div>

      <div class="text-end">
        <span class="badge bg-<?= htmlspecialchars($projeto->prj_statusclass ?? 'success', ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($projeto->prj_status ?? '-', ENT_QUOTES, 'UTF-8') ?>
        </span>
        <div class="small text-muted mt-1">ID #<?= (int) ($projeto->prj_id ?? 1) ?></div>
      </div>
    </div>

    <header class="phsaScrollHeader">
      <nav class="phsaScrollNav" data-menu="tabProjeto">
        <ul class="phsaScrollList">
          <li class="phsaScrollItem active" data-target="cadastro">
            <i class="fa-solid fa-id-card"></i>&nbsp;Cadastro
          </li>
          <li class="phsaScrollItem" data-target="configuracao">
            <i class="fa-solid fa-gear"></i>&nbsp;Configuração
          </li>
          <li class="phsaScrollItem" data-target="organizacoes">
            <i class="fa-solid fa-sitemap"></i>&nbsp;Organizações
            <span class="badge bg-phsa-light bg-phsa-hover-dark ms-1"><?= $totalOrganizacoes ?></span>
          </li>
          <li class="phsaScrollItem" data-target="historico">
            <i class="fa-solid fa-clock"></i>&nbsp;Histórico
          </li>
        </ul>
      </nav>
    </header>

    <div class="phsaTabContent phsaTabSubContent">
      <div class="phsaTabItem active" id="cadastro">
        <?php include __DIR__ . '/cadastro.php'; ?>
      </div>

      <div class="phsaTabItem" id="configuracao">
        <?php include __DIR__ . '/configuracao.php'; ?>
      </div>

      <div class="phsaTabItem" id="organizacoes">
        <?php include __DIR__ . '/organizacoes.php'; ?>
      </div>

      <div class="phsaTabItem" id="historico">
        <?php include __DIR__ . '/historico.php'; ?>
      </div>
    </div>
  </div>
</div>
