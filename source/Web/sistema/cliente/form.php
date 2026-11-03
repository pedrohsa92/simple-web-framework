<?php $this->layout("sistema::sistema"); ?>



<div class="card">
  <?php if ( !is_null($cliente->cli_id) ): ?>
    <div id="cli_planodefundo" class="card-header cardPlanoDefundo" style="background-image: url('<?= Helpers::storage($cliente->cli_planodefundo, 'storage/global/planodefundo.jpg') ?>');">
      <div class="action-buttons">
        <button id="btnAnexarPlanoDeFundo" class="btn btn-sm btn-primary">
          <i class="bx bx-paperclip"></i>
        </button>
        <button id="btnSalvarPlanoDeFundo" class="bto bto-sm bto-phsa" style="display: none;">
          <i class="fa-solid fa-check-double"></i>
        </button>
        <button id="btnUploadPlanoDeFundo" class="bto bto-sm bto-phsa-border bto-phsa-hover" style="display: none;">
          <i class="bx bx-upload"></i>
        </button>
        <button id="btnCancelarPlanoDeFundo" class="bto bto-sm bto-phsa-border bto-phsa-hover-light" style="display: none;">
          <i class="bx bx-x"></i>
        </button>
      </div>

      <div id="divCardLogo" class="divCardLogoContainer molduraExterna molduraExterna-cardlogo">
        <div class="molduraInterna">
        <img id="imgLogo" class="cli_logo imgLogo" src="<?= Helpers::storage($cliente->cli_logo) ?>" alt="Imagem principal">
          <div class="action-buttons-logo">
            <button id="btnUploadLogo" class="bto bto-sm bto-phsa-border bto-phsa-hover">
              <i class="bx bx-upload"></i>
            </button>
            <button id="btnSalvarLogo" class="bto bto-sm bto-phsa" style="display: none;">
              <i class="fa-solid fa-check-double"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  <?php endif ?>

  <div class="card-body">
    <div class="row">
      <div class="col-sm-12 mb-3 text-uppercase">
        <?php if ( is_null($cliente->cli_id) ): ?>
          <h4 class="m-0">
            Cadastro de novo Cliente
          </h4>
        <?php else: ?>
          <h4 class="m-0">
            <?=Helpers::adicionaMascaraCnpjCPf($cliente->cli_cnpjcpf)?> - <?=$cliente->cli_razaosocial?>
          </h4>
          <h5 class="m-0 text-secondary">
            <?=$cliente->cli_nomefantasia?>
          </h5>
        <?php endif ?>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <header class="phsaScrollHeader">
          <nav class="phsaScrollNav" data-menu="tabcliente">
            <ul class="phsaScrollList">
              <li class="phsaScrollItem active" data-target="cadastro">
                <i class="fa-solid fa-user-plus"></i>&nbsp;Cadastro
              </li>
              <?php if ( !is_null($cliente->cli_id) ): ?>
                <li class="phsaScrollItem" data-target="acesso">
                  <i class="fa-solid fa-key"></i>&nbsp;Acesso
                </li>
                <li class="phsaScrollItem" data-target="parametro">
                  <i class="fa-solid fa-coins"></i>&nbsp;Parâmetro
                </li>
                <li class="phsaScrollItem" data-target="historico">
                  <i class="fa-solid fa-clock"></i>&nbsp;Histórico
                </li>
              <?php endif ?>
            </ul>
          </nav>
        </header>
      </div>
      <div class="col-12 mb-3">
        <div class="phsaTabContent phsaTabSubContent">
          <div class="phsaTabItem active" id="cadastro">
            <?php include 'source/Web/sistema/cliente/cadastro.php'; ?>
          </div>
          <?php if ( !is_null($cliente->cli_id) ): ?>
            <div class="phsaTabItem" id="acesso">
              <?php include 'source/Web/sistema/cliente/acesso.php'; ?>
            </div>

            <div class="phsaTabItem" id="parametro">
              Conteúdo de Parâmetro
            </div>

            <div class="phsaTabItem" id="historico">
              <?php include 'source/Web/sistema/cliente/historico.php'; ?>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
    <?php if ( !is_null($cliente->cli_id) ): ?>
      var objCliPlanoDeFundo = {};
      var objCliLogo = {};

      $(document).ready(function () {
        $('#btnUploadPlanoDeFundo').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objCliPlanoDeFundo = response;

              if (!objCliPlanoDeFundo.status) {
                swResponse(objCliPlanoDeFundo);
                return;
              }
              $('#cli_planodefundo').css('background-image', `url('${objCliPlanoDeFundo.data}')`);
              $('#btnUploadPlanoDeFundo').hide(100, function () {
                $('#btnSalvarPlanoDeFundo, #btnCancelarPlanoDeFundo').show(500);
              });
            }
          });
        });

        $('#btnAnexarPlanoDeFundo').on('click', function () {
          $(this).hide(100, function () {
            $('#btnUploadPlanoDeFundo, #btnCancelarPlanoDeFundo').show(500);
          });
        });

        $('#btnCancelarPlanoDeFundo').on('click', function () {
          $('#btnSalvarPlanoDeFundo, #btnUploadPlanoDeFundo, #btnCancelarPlanoDeFundo').hide(100, function () {
            $('#btnAnexarPlanoDeFundo').show(500);
          });
          objCliPlanoDeFundo = {};
        });

        $('#btnSalvarPlanoDeFundo').on('click', function () {
          if (!objCliPlanoDeFundo || !objCliPlanoDeFundo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("cliente/salvarCliente") ?>',
            method: 'POST',
            data: { 
              cli_id: <?=$cliente->cli_id?>,
              cli_planodefundo: objCliPlanoDeFundo.data
            },
            success: function (response) {
              swResponse(response);
              $('#btnSalvarPlanoDeFundo, #btnCancelarPlanoDeFundo').hide(100, function () {
                $('#btnAnexarPlanoDeFundo').show(500);
              });
              objCliPlanoDeFundo = {};
            }
          });
        });

        $('#btnUploadLogo').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objCliLogo = response;

              if (!objCliLogo.status) {
                swResponse(objCliLogo);
                return;
              }

              $('.cli_logo').attr('src', objCliLogo.data);
              $('#btnUploadLogo').hide(100, function () {
                $('#btnSalvarLogo').show(500);
              });
            }
          });
        });

        $('#btnSalvarLogo').on('click', function () {
          if (!objCliLogo || !objCliLogo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("cliente/salvarCliente") ?>',
            method: 'POST',
            data: { 
              cli_id: <?=$cliente->cli_id?>,
              cli_logo: objCliLogo.data
            },
            success: function (response) {
              swResponse(response);
              $('#btnSalvarLogo').hide(100, function () {
                $('#btnUploadLogo').show(500);
              });
              objCliLogo = {};
            }
          });
        });
      });
    <?php endif ?>
  </script>
<?php $this->end(); ?>

<?php $this->push("style"); ?>
<style type="text/css">
</style>
<?php $this->end(); ?>
