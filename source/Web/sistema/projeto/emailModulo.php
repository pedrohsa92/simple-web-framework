<?php
  $this->layout('sistema::sistema');
  $modulo = $modulo ?? (object) [];
  $esc = static fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>

<div class="card border shadow-sm">
  <div class="card-body text-center py-5">
    <div class="text-phsa mb-3"><i class="fa-solid <?= $esc($modulo->icone ?? 'fa-envelope') ?> fa-3x"></i></div>
    <h4 class="mb-2"><?= $esc($modulo->titulo ?? 'Módulo de e-mails') ?></h4>
    <p class="text-muted mb-2"><?= $esc($modulo->descricao ?? '') ?></p>
    <p class="text-muted small mx-auto" style="max-width: 680px;"><?= $esc($modulo->mensagem ?? '') ?></p>
    <a href="<?= Helpers::url('projeto/email') ?>" class="bto bto-phsa-light mt-3"><i class="fa-solid fa-arrow-left"></i>&nbsp;<?= $esc($modulo->acao ?? 'Voltar') ?></a>
  </div>
</div>
