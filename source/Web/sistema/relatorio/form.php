<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-12 mb-3 text-uppercase">
        <h4 class="m-0">
          <?= htmlspecialchars($relatorioNome) ?>
          <a href="<?= Helpers::url('relatorio/list') ?>" class="bto bto-phsa-light float-end">
            <i class="fa fa-arrow-left"></i>&nbsp;Voltar
          </a>
        </h4>
      </div>
    </div>

    <div id="relatorioConteudo" class="row">
      <div class="col-md-12">
		    <?php
		      if ( file_exists($relatorioPath) ) {
		        include $relatorioPath;
		      } else {
		        echo "<p class='text-danger'><i class='fa fa-exclamation-circle'></i> Erro: O relatório não pôde ser carregado.</p>";
		      }
		    ?>
      </div>
    </div>
  </div>
</div>