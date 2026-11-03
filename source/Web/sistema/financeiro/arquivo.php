<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="m-0 text-uppercase"><?= $titulo ?? 'Financeiro' ?></h4>
        <div class="text-muted small"><?= $descricao ?? 'Modulo financeiro do contexto ativo.' ?></div>
      </div>
    </div>

    <hr>

    <div class="text-muted">
      Estrutura inicial criada. As regras de negocio e os formularios serao implementados nas proximas etapas.
    </div>
  </div>
</div>
