<?php
  $this->layout('sistema::sistema');
  $esc = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <h4 class="m-0 text-uppercase"><?= $esc($tituloPagina) ?></h4>
        <div class="text-muted small mt-1">Gerencie os cadastros básicos usados nos dados de <?= $esc($rotuloModulo) ?>.</div>
      </div>
      <a href="<?= $esc($urlVoltar) ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar para <?= $esc($rotuloModulo) ?></a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Cadastro</th><th class="text-center">Registros</th><th class="text-end">Ação</th></tr></thead>
        <tbody>
          <?php foreach ($cadastros as $cadastro): $url = Helpers::url("{$modulo}/basico/{$cadastro->modulo}"); ?>
            <tr>
              <td>
                <a href="<?= $esc($url) ?>" class="text-phsa fw-semibold"><?= $esc($cadastro->titulo) ?></a>
                <div class="text-muted small"><?= $esc($cadastro->descricao) ?></div>
              </td>
              <td class="text-center"><span class="badge bg-phsa-light bg-phsa-hover-dark" title="Ativos / Inativos"><?= (int) $cadastro->ativos ?>/<?= (int) $cadastro->inativos ?></span></td>
              <td class="text-end"><a href="<?= $esc($url) ?>" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Abrir cadastro"><i class="fa-solid fa-arrow-right"></i></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
