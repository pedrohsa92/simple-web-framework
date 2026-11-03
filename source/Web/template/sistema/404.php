<?php $this->layout("sistema::sistema"); ?>

<div class="card text-center" style="width: 100%; height: 100%; padding: 30px; font-size: 20px;">
  <div class="card-body d-flex flex-column align-items-center justify-content-center">
    <img class="mb-4" src="<?=Helpers::storage('storage/global/erro.gif')?>">
    <h1 class="card-title">Página Não Encontrada</h1>
    <p class="card-text">A página que você está tentando acessar não existe</p>
    <p class="card-text">Se você acha que isso é um erro, entre em contato com o suporte</p>
    <a href="<?=Helpers::url('dashboard')?>" class="bto bto-phsa-dark mt-3"><i class="fa-solid fa-house"></i> IR PARA DASHBOARD</a>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
  	$(document).ready(function() {
  	});
  </script>
<?php $this->end(); ?>

<?php $this->push("style"); ?>
<style type="text/css">
  .card-body img {
    max-width: 250px;
  }
</style>
<?php $this->end(); ?>
