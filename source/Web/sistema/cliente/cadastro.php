





<header class="phsaScrollHeader">
  <nav class="phsaScrollNav" data-menu="tabCadastro">
    <ul class="phsaScrollList">
      <li class="phsaScrollItem active" data-target="dadosbasicos">
        <i class="fa-solid fa-id-card"></i>&nbsp;Dados básicos
      </li>
      <?php if ( !is_null($cliente->cli_id) ): ?>
        <li class="phsaScrollItem" data-target="endereco">
          <i class="fa-solid fa-map-marker-alt"></i>&nbsp;Endereço
        </li>
        <li class="phsaScrollItem" data-target="contato">
          <i class="fa-solid fa-phone"></i>&nbsp;Contato
        </li>
        <li class="phsaScrollItem" data-target="dadoscomplementares">
          <i class="fa-solid fa-folder-plus"></i>&nbsp;Dados complementares
        </li>
      <?php endif ?>
    </ul>
  </nav>
</header>
<div class="phsaTabContent">
  <div class="phsaTabItem active" id="dadosbasicos">
    <?php include 'source/Web/sistema/cliente/cadastroDadosBasicos.php'; ?>
  </div>
  <?php if ( !is_null($cliente->cli_id) ): ?>
    <div class="phsaTabItem" id="endereco">
      <?php 
        $getEndereco = $cliente->endereco;
        include 'source/Web/sistema/endereco/form.php';
      ?>
    </div>
    <div class="phsaTabItem" id="contato">
      <?php
        $getContato = $cliente->contato;
        include 'source/Web/sistema/contato/form.php';
      ?>
    </div>
    <div class="phsaTabItem" id="dadoscomplementares">
      <?php include 'source/Web/sistema/cliente/cadastroDadosComplementares.php'; ?>
    </div>
  <?php endif ?>
</div>

<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(document).ready(function () {
    });
  </script>
<?php $this->end(); ?>