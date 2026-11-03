<?php
  $this->layout("sistema::sistema");

  $totalEmpresas = (int) ($getEmpresas->total ?? 0);
  $labelEmpresas = $totalEmpresas > 1 ? 'Empresas' : 'Empresa';
?>

<style>
  .phsaOrgEmpresaCount {
    min-width: 1.15rem;
    padding: .2rem .38rem;
    font-size: .68rem;
    line-height: 1;
    position: relative;
    top: -.35em;
    margin-left: .2rem;
  }
</style>

<div class="card">
  <?php if (!is_null($organizacao) && !is_null($organizacao->org_id)): ?>
    <div id="org_planodefundo" class="card-header cardPlanoDefundo" style="background-image: url('<?= Helpers::storage($organizacao->org_planodefundo, 'storage/global/planodefundo.jpg') ?>');">
      <div class="action-buttons">
        <button id="btnAnexarPlanoDeFundoOrganizacao" class="btn btn-sm btn-primary">
          <i class="bx bx-paperclip"></i>
        </button>

        <button id="btnSalvarPlanoDeFundoOrganizacao" class="bto bto-sm bto-phsa" style="display: none;">
          <i class="fa-solid fa-check-double"></i>
        </button>

        <button id="btnUploadPlanoDeFundoOrganizacao" class="bto bto-sm bto-phsa-border bto-phsa-hover" style="display: none;">
          <i class="bx bx-upload"></i>
        </button>

        <button id="btnCancelarPlanoDeFundoOrganizacao" class="bto bto-sm bto-phsa-border bto-phsa-hover-light" style="display: none;">
          <i class="bx bx-x"></i>
        </button>
      </div>

      <div id="divCardLogo" class="divCardLogoContainer molduraExterna molduraExterna-cardlogo">
        <div class="molduraInterna">
          <img id="imgLogo" class="org_logo-<?= $organizacao->org_id ?> imgLogo" src="<?= Helpers::storage($organizacao->org_logo, 'storage/global/semperfil.png') ?>" alt="Imagem principal">

          <div class="action-buttons-logo">
            <button id="btnUploadLogoOrganizacao" class="bto bto-sm bto-phsa-border bto-phsa-hover">
              <i class="bx bx-upload"></i>
            </button>

            <button id="btnSalvarLogoOrganizacao" class="bto bto-sm bto-phsa" style="display: none;">
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
        <?php if (is_null($organizacao) || is_null($organizacao->org_id)): ?>
          <h4 class="m-0">Cadastro de nova organização</h4>
        <?php else: ?>
          <h4 class="m-0">
            <?= Helpers::adicionaMascaraCnpjCPf($organizacao->org_cnpjcpf) ?> - <?= $organizacao->org_razaosocial ?>
          </h4>

          <h5 class="m-0 text-secondary">
            <?= $organizacao->org_nomefantasia ?>
          </h5>
        <?php endif ?>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <header class="phsaScrollHeader">
          <nav class="phsaScrollNav" data-menu="tabOrganizacao">
            <ul class="phsaScrollList">
              <li class="phsaScrollItem active" data-target="cadastro">
                <i class="fa-solid fa-sitemap"></i>&nbsp;Cadastro
              </li>

              <?php if (!is_null($organizacao) && !is_null($organizacao->org_id)): ?>
                <li class="phsaScrollItem" data-target="webapp">
                  <i class="fa-solid fa-globe"></i>&nbsp;Webapp
                </li>

                <li class="phsaScrollItem" data-target="configuracao">
                  <i class="fa-solid fa-gear"></i>&nbsp;Configuração
                </li>

                <li class="phsaScrollItem" data-target="plano">
                  <i class="fa-solid fa-file-contract"></i>&nbsp;Plano vigente
                </li>

                <li class="phsaScrollItem" data-target="empresas">
                  <i class="fa-solid fa-building"></i>&nbsp;<?= $labelEmpresas ?>
                  <span class="badge rounded-pill bg-phsa-light text-phsa border border-white shadow-sm phsaOrgEmpresaCount"><?= $totalEmpresas ?></span>
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
            <?php include 'source/Web/sistema/organizacao/cadastro.php'; ?>
          </div>

          <?php if (!is_null($organizacao) && !is_null($organizacao->org_id)): ?>
            <div class="phsaTabItem" id="webapp">
              <?php include 'source/Web/sistema/organizacao/webapp.php'; ?>
            </div>

            <div class="phsaTabItem" id="configuracao">
              <?php include 'source/Web/sistema/organizacao/configuracao.php'; ?>
            </div>

            <div class="phsaTabItem" id="plano">
              <?php include 'source/Web/sistema/organizacao/plano.php'; ?>
            </div>

            <div class="phsaTabItem" id="empresas">
              <?php include 'source/Web/sistema/organizacao/empresas.php'; ?>
            </div>

            <div class="phsaTabItem" id="historico">
              <?php include 'source/Web/sistema/organizacao/historico.php'; ?>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
    <?php if (!is_null($organizacao) && !is_null($organizacao->org_id)): ?>
      var objOrgPlanoDeFundo = {};
      var objOrgLogo = {};

      $(document).ready(function () {
        $('#btnUploadPlanoDeFundoOrganizacao').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objOrgPlanoDeFundo = response;

              if (!objOrgPlanoDeFundo.status) {
                swResponse(objOrgPlanoDeFundo);
                return;
              }

              $('#org_planodefundo').css('background-image', `url('${objOrgPlanoDeFundo.data}')`);

              $('#btnUploadPlanoDeFundoOrganizacao').hide(100, function () {
                $('#btnSalvarPlanoDeFundoOrganizacao, #btnCancelarPlanoDeFundoOrganizacao').show(500);
              });
            }
          });
        });

        $('#btnAnexarPlanoDeFundoOrganizacao').on('click', function () {
          $(this).hide(100, function () {
            $('#btnUploadPlanoDeFundoOrganizacao, #btnCancelarPlanoDeFundoOrganizacao').show(500);
          });
        });

        $('#btnCancelarPlanoDeFundoOrganizacao').on('click', function () {
          $('#btnSalvarPlanoDeFundoOrganizacao, #btnUploadPlanoDeFundoOrganizacao, #btnCancelarPlanoDeFundoOrganizacao').hide(100, function () {
            $('#btnAnexarPlanoDeFundoOrganizacao').show(500);
          });

          objOrgPlanoDeFundo = {};
        });

        $('#btnSalvarPlanoDeFundoOrganizacao').on('click', function () {
          if (!objOrgPlanoDeFundo || !objOrgPlanoDeFundo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("organizacao/salvarOrganizacao") ?>',
            method: 'POST',
            dataType: 'json',
            data: {
              org_id: <?= $organizacao->org_id ?>,
              org_planodefundo: objOrgPlanoDeFundo.data
            },
            success: function (response) {
              swResponse(response);

              $('#btnSalvarPlanoDeFundoOrganizacao, #btnCancelarPlanoDeFundoOrganizacao').hide(100, function () {
                $('#btnAnexarPlanoDeFundoOrganizacao').show(500);
              });

              objOrgPlanoDeFundo = {};
            }
          });
        });

        $('#btnUploadLogoOrganizacao').on('click', function () {
          fgbSelecionarImagem({
            onFileSelect: (response) => {
              objOrgLogo = response;

              if (!objOrgLogo.status) {
                swResponse(objOrgLogo);
                return;
              }

              $('.org_logo-<?= $organizacao->org_id ?>').attr('src', objOrgLogo.data);

              $('#btnUploadLogoOrganizacao').hide(100, function () {
                $('#btnSalvarLogoOrganizacao').show(500);
              });
            }
          });
        });

        $('#btnSalvarLogoOrganizacao').on('click', function () {
          if (!objOrgLogo || !objOrgLogo.data) {
            swToastError('Nenhuma imagem para salvar!');
            return;
          }

          swAguarde();

          $.ajax({
            url: '<?= Helpers::url("organizacao/salvarOrganizacao") ?>',
            method: 'POST',
            dataType: 'json',
            data: {
              org_id: <?= $organizacao->org_id ?>,
              org_logo: objOrgLogo.data
            },
            success: function (response) {
              swResponse(response);

              $('#btnSalvarLogoOrganizacao').hide(100, function () {
                $('#btnUploadLogoOrganizacao').show(500);
              });

              objOrgLogo = {};
            }
          });
        });
      });
    <?php endif ?>
  </script>
<?php $this->end(); ?>
