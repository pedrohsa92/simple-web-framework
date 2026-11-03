<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <?php
    $statusMotivoEmpresa = [];
    if (!empty($empresa->emp_status_motivo)) {
      $statusMotivoEmpresa = json_decode($empresa->emp_status_motivo, true);
      if (!is_array($statusMotivoEmpresa)) {
        $statusMotivoEmpresa = ['motivo' => $empresa->emp_status_motivo];
      }
    }
  ?>
  <?php if (!is_null($empresa) && !is_null($empresa->emp_id)): ?>
    <div id="emp_planodefundo" class="card-header cardPlanoDefundo" style="background-image: url('<?= Helpers::storage($empresa->emp_planodefundo, 'storage/global/planodefundo.jpg') ?>');">
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
          <img id="imgLogo" class="emp_logo-<?= $empresa->emp_id ?> imgLogo" src="<?= Helpers::storage($empresa->emp_logo, 'storage/global/semperfil.png') ?>" alt="Imagem principal">

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
        <?php if (is_null($empresa) || is_null($empresa->emp_id)): ?>
          <h4 class="m-0">
            Cadastro de nova empresa
          </h4>
        <?php else: ?>
          <h4 class="m-0">
            <?= Helpers::adicionaMascaraCnpjCPf($empresa->emp_cnpjcpf) ?> - <?= $empresa->emp_razaosocial ?>
          </h4>

          <h5 class="m-0 text-secondary">
            <?= $empresa->emp_nomefantasia ?>
          </h5>
        <?php endif ?>
      </div>

      <?php if (!is_null($empresa) && !is_null($empresa->emp_id) && ($empresa->emp_status ?? '') === 'Inativo'): ?>
        <div class="col-sm-12 mb-3">
          <div class="alert alert-danger mb-0">
            <i class="fa-solid fa-circle-exclamation"></i>
            <strong>Status inativo:</strong>
            <?= htmlspecialchars($statusMotivoEmpresa['motivo'] ?? 'Sem motivo informado.', ENT_QUOTES, 'UTF-8') ?>
          </div>
        </div>
      <?php endif ?>
    </div>

    <div class="row">
      <div class="col-12">
        <header class="phsaScrollHeader">
          <nav class="phsaScrollNav" data-menu="tabEmpresa">
            <ul class="phsaScrollList">
              <li class="phsaScrollItem active" data-target="cadastro">
                <i class="fa-solid fa-building"></i>&nbsp;Cadastro
              </li>

              <?php if (!is_null($empresa) && !is_null($empresa->emp_id)): ?>
                <li class="phsaScrollItem" data-target="webapp">
                  <i class="fa-solid fa-globe"></i>&nbsp;Webapp
                </li>

                <li class="phsaScrollItem" data-target="configuracao">
                  <i class="fa-solid fa-gear"></i>&nbsp;Configuração
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
            <?php include 'source/Web/sistema/empresa/cadastro.php'; ?>
          </div>

          <?php if (!is_null($empresa) && !is_null($empresa->emp_id)): ?>
            <div class="phsaTabItem" id="webapp">
              <?php include 'source/Web/sistema/empresa/webapp.php'; ?>
            </div>

            <div class="phsaTabItem" id="configuracao">
              <?php include 'source/Web/sistema/empresa/configuracao.php'; ?>
            </div>

            <div class="phsaTabItem" id="historico">
              <?php include 'source/Web/sistema/empresa/historico.php'; ?>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
    <?php if (!is_null($empresa) && !is_null($empresa->emp_id)): ?>
      var objEmpPlanoDeFundo = {};
      var objEmpLogo = {};

      $(document).ready(function () {
        $('#btnUploadPlanoDeFundo').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objEmpPlanoDeFundo = response;

              if (!objEmpPlanoDeFundo.status) {
                swResponse(objEmpPlanoDeFundo);
                return;
              }

              $('#emp_planodefundo').css('background-image', `url('${objEmpPlanoDeFundo.data}')`);

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

          objEmpPlanoDeFundo = {};
        });

        $('#btnSalvarPlanoDeFundo').on('click', function () {
          if (!objEmpPlanoDeFundo || !objEmpPlanoDeFundo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("empresa/salvarEmpresa") ?>',
            method: 'POST',
            dataType: 'json',
            data: {
              emp_id: <?= $empresa->emp_id ?>,
              emp_planodefundo: objEmpPlanoDeFundo.data
            },
            success: function (response) {
              swResponse(response);

              $('#btnSalvarPlanoDeFundo, #btnCancelarPlanoDeFundo').hide(100, function () {
                $('#btnAnexarPlanoDeFundo').show(500);
              });

              objEmpPlanoDeFundo = {};
            }
          });
        });

        $('#btnUploadLogo').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objEmpLogo = response;

              if (!objEmpLogo.status) {
                swResponse(objEmpLogo);
                return;
              }

              $('.emp_logo-<?= $empresa->emp_id ?>').attr('src', objEmpLogo.data);

              $('#btnUploadLogo').hide(100, function () {
                $('#btnSalvarLogo').show(500);
              });
            }
          });
        });

        $('#btnSalvarLogo').on('click', function () {
          if (!objEmpLogo || !objEmpLogo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("empresa/salvarEmpresa") ?>',
            method: 'POST',
            dataType: 'json',
            data: {
              emp_id: <?= $empresa->emp_id ?>,
              emp_logo: objEmpLogo.data
            },
            success: function (response) {
              swResponse(response);

              $('#btnSalvarLogo').hide(100, function () {
                $('#btnUploadLogo').show(500);
              });

              objEmpLogo = {};
            }
          });
        });
      });
    <?php endif ?>
  </script>
<?php $this->end(); ?>
