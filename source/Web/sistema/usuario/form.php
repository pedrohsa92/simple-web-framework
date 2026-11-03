<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <?php
    $statusMotivoUsuario = [];
    if (!empty($usuario->usu_status_motivo)) {
      $statusMotivoUsuario = json_decode($usuario->usu_status_motivo, true);
      if (!is_array($statusMotivoUsuario)) {
        $statusMotivoUsuario = ['motivo' => $usuario->usu_status_motivo];
      }
    }
  ?>
  <?php if ( !is_null($usuario->usu_id) ): ?>
    <div id="usu_planodefundo" class="card-header cardPlanoDefundo" style="background-image: url('<?= Helpers::storage($usuario->usu_planodefundo, 'storage/global/planodefundo.jpg') ?>');">
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
        <img id="imgLogo" class="usu_logo-<?=$usuario->usu_id?> imgLogo" src="<?= Helpers::storage($usuario->usu_logo) ?>" alt="Imagem principal">
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
        <?php if ( is_null($usuario->usu_id) ): ?>
          <h4 class="m-0">
            Cadastro de novo usuario
          </h4>
        <?php else: ?>
          <h4 class="m-0">
            <?=Helpers::adicionaMascaraCnpjCPf($usuario->usu_cnpjcpf)?> - <?=$usuario->usu_razaosocial?>
          </h4>
          <h5 class="m-0 text-secondary">
            <?=$usuario->usu_nomefantasia?>
          </h5>
        <?php endif ?>
      </div>

      <?php if (!is_null($usuario->usu_id) && ($usuario->usu_status ?? '') === 'Inativo'): ?>
        <div class="col-sm-12 mb-3">
          <div class="alert alert-danger mb-0">
            <i class="fa-solid fa-circle-exclamation"></i>
            <strong>Status inativo:</strong>
            <?= htmlspecialchars($statusMotivoUsuario['motivo'] ?? 'Sem motivo informado.', ENT_QUOTES, 'UTF-8') ?>
          </div>
        </div>
      <?php endif ?>
    </div>

    <div class="row">
      <div class="col-12">
        <header class="phsaScrollHeader">
          <nav class="phsaScrollNav" data-menu="tabusuario">
            <ul class="phsaScrollList">
              <li class="phsaScrollItem active" data-target="cadastro">
                <i class="fa-solid fa-user-plus"></i>&nbsp;Cadastro
              </li>
              <?php if ( !is_null($usuario->usu_id) ): ?>
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
            <?php include 'source/Web/sistema/usuario/cadastro.php'; ?>
          </div>
          <?php if ( !is_null($usuario->usu_id) ): ?>
            <div class="phsaTabItem" id="acesso">
              <?php include 'source/Web/sistema/usuario/acesso.php'; ?>
            </div>

            <div class="phsaTabItem" id="parametro">
              <?php include 'source/Web/sistema/usuario/parametro.php'; ?>
            </div>

            <div class="phsaTabItem" id="historico">
              <?php include 'source/Web/sistema/usuario/historico.php'; ?>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
    <?php if ( !is_null($usuario->usu_id) ): ?>
      var objUsuPlanoDeFundo = {};
      var objUsuLogo = {};

      $(document).ready(function () {
        $('#btnUploadPlanoDeFundo').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objUsuPlanoDeFundo = response;

              if (!objUsuPlanoDeFundo.status) {
                swResponse(objUsuPlanoDeFundo);
                return;
              }
              $('#usu_planodefundo').css('background-image', `url('${objUsuPlanoDeFundo.data}')`);
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
          objUsuPlanoDeFundo = {};
        });

        $('#btnSalvarPlanoDeFundo').on('click', function () {
          if (!objUsuPlanoDeFundo || !objUsuPlanoDeFundo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("usuario/salvarUsuario") ?>',
            method: 'POST',
            data: { 
              usu_id: <?=$usuario->usu_id?>,
              usu_planodefundo: objUsuPlanoDeFundo.data
            },
            success: function (response) {
              swResponse(response);
              $('#btnSalvarPlanoDeFundo, #btnCancelarPlanoDeFundo').hide(100, function () {
                $('#btnAnexarPlanoDeFundo').show(500);
              });
              objUsuPlanoDeFundo = {};
            }
          });
        });

        $('#btnUploadLogo').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objUsuLogo = response;

              if (!objUsuLogo.status) {
                swResponse(objUsuLogo);
                return;
              }

              $('.usu_logo-<?=$usuario->usu_id?>').attr('src', objUsuLogo.data);
              $('#btnUploadLogo').hide(100, function () {
                $('#btnSalvarLogo').show(500);
              });
            }
          });
        });

        $('#btnSalvarLogo').on('click', function () {
          if (!objUsuLogo || !objUsuLogo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("usuario/salvarUsuario") ?>',
            method: 'POST',
            data: { 
              usu_id: <?=$usuario->usu_id?>,
              usu_logo: objUsuLogo.data
            },
            success: function (response) {
              swResponse(response);
              $('#btnSalvarLogo').hide(100, function () {
                $('#btnUploadLogo').show(500);
              });
              objUsuLogo = {};
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
