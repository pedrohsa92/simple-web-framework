<?php $this->layout("sistema::sistema"); ?>

<div class="card text-center" style="width: 100%; height: 100%; padding: 30px; font-size: 20px;">
  <div class="card-body d-flex flex-column align-items-center justify-content-center">
    <img class="mb-4" src="<?=Helpers::storage('storage/global/erro.gif')?>">
    <h1 class="card-title"><?=$erro->title?></h1>
    <p class="card-text">
      <?=$erro->text?>
    </p>
    <p class="card-text text-secondary small">
      Se você acha que isso é um erro, por favor, entre em contato com o administrador
    </p>
  </div>
</div>