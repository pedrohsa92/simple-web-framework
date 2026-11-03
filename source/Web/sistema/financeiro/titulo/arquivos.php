<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h4 class="m-0 text-uppercase"><?= $titulo ?? 'Titulos' ?></h4>
        <div class="text-muted small"><?= $descricao ?? 'Titulos financeiros do contexto ativo.' ?></div>
      </div>

      <?php if (($modo ?? '') === 'list' && Helpers::pode('titulo.criar')): ?>
        <a href="<?= Helpers::url('titulo/form') ?>" class="bto bto-phsa">
          <i class="fa-solid fa-plus"></i>&nbsp;Novo
        </a>
      <?php endif; ?>

      <?php if (($modo ?? '') === 'form'): ?>
        <a href="<?= Helpers::url('titulo/list') ?>" class="bto bto-phsa-light bto-phsa-hover">
          <i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar
        </a>
      <?php endif; ?>
    </div>

    <hr>

    <?php if (($modo ?? '') === 'form'): ?>
      <div class="text-muted">
        Formulario inicial do titulo financeiro. ID atual: <?= (int) ($id ?? 0) ?>.
      </div>
    <?php else: ?>
      <div class="text-muted">
        Area reservada para pesquisa de titulos financeiros.
      </div>
    <?php endif; ?>
  </div>
</div>
