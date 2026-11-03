<?php $this->layout("sistema::sistema"); ?>

<div class="card shadow-sm"><div class="card-body p-3 p-md-4">
  <h4 class="m-0 text-uppercase"><?= $titulo ?? 'Relatórios financeiros' ?></h4>
  <div class="text-muted small mb-4"><?= $descricao ?? 'Relatórios financeiros do contexto ativo.' ?></div>
  <div class="row g-3">
    <div class="col-12"><a href="<?= Helpers::url('financeiro/relatorio/extratoMovimentacao') ?>" class="card h-100 border-phsa-light box-phsa-shadow-light text-decoration-none text-body"><div class="card-body d-flex gap-3"><span class="bg-phsa-light text-phsa rounded-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:46px;height:46px"><i class="fa-solid fa-building-columns fa-lg"></i></span><div><div class="fw-semibold text-phsa">Extrato movimentação</div><div class="small text-muted mt-1">Acompanhe saldo anterior, entradas, saídas e saldo final por período.</div></div></div></a></div>
    <div class="col-12"><a href="<?= Helpers::url('financeiro/relatorio/lancamentosAnalitico') ?>" class="card h-100 border-phsa-light box-phsa-shadow-light text-decoration-none text-body"><div class="card-body d-flex gap-3"><span class="bg-phsa-light text-phsa rounded-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:46px;height:46px"><i class="fa-solid fa-list-check fa-lg"></i></span><div><div class="fw-semibold text-phsa">Lançamento analítico</div><div class="small text-muted mt-1">Consulte cada lançamento com conta, plano, centro de custo e status.</div></div></div></a></div>
  </div>
</div></div>
