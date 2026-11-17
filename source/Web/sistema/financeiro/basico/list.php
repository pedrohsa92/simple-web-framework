<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="m-0 text-uppercase"><?= $titulo ?? 'Básico financeiro' ?></h4>
        <div class="text-muted small"><?= $descricao ?? 'Cadastros financeiros básicos.' ?></div>
      </div>
    </div>

    <hr>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Cadastro</th>
            <th>Finalidade</th>
            <th class="text-center">Registros</th>
            <th class="text-end">Ação</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach (($cadastros ?? []) as $cadastro): ?>
            <tr>
              <td>
                <a
                  href="<?= Helpers::url('financeiro/basico/' . $cadastro->modulo) ?>"
                  class="text-phsa fw-semibold"
                  title="<?= $cadastro->titulo ?>"
                >
                  <?= $cadastro->titulo ?>
                </a>
              </td>

              <td class="text-muted"><?= $cadastro->descricao ?></td>

              <td class="text-center">
                <span class="badge bg-phsa-light bg-phsa-hover-dark" title="Ativos / Inativos">
                  <?= (int) ($cadastro->totalAtivos ?? 0) ?>/<?= (int) ($cadastro->totalInativos ?? 0) ?>
                </span>
              </td>

              <td class="text-end">
                <a
                  href="<?= Helpers::url('financeiro/basico/' . $cadastro->modulo) ?>"
                  class="bto btn-sm bto-phsa-light bto-phsa-hover"
                  title="Abrir cadastro"
                >
                  <i class="fa-regular fa-circle-right"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
