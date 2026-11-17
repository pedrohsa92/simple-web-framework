





<div class="card-header cardPlanoDefundo" style="background-image: url('<?= Helpers::storage($usuarioLogado->usu_planodefundo, 'storage/global/planodefundo.jpg') ?>');">
  <div class="position-absolute top-0 start-0 m-2">
    <a id="btnAppVoltarPara" href="<?= Helpers::url('app/feed') ?>" class="bto bto-sm bto-phsa-light" title="Voltar para o feed">
      <i class="fas fa-arrow-left"></i>
    </a>
  </div>

  <div class="position-absolute top-0 end-0 m-2">
    <?php if (!is_null($usuarioLogado->usu_id)): ?>
      <a href="<?= Helpers::url('app/perfil/' . $usuarioLogado->usu_id) ?>" class="bto bto-sm bto-phsa-light" title="Ir para o Perfil" type="button">
        <i class="fa-solid fa-user"></i>
      </a>
      <button onclick="fgbDesconectar(`<?=Helpers::url('app')?>`)" class="bto bto-sm bto-phsa-light" title="Sair do app" type="button">
        <i class="fa-solid fa-xmark"></i>
      </button>
    <?php endif; ?>
  </div>

  <div class="divCardLogoContainer molduraExterna molduraExterna-cardlogo">
    <div class="molduraInterna">
      <img id="imgLogo" class="usu_logo imgLogo" src="<?= Helpers::storage($usuarioLogado->usu_logo) ?>" alt="Imagem do perfil">
    </div>
  </div>
</div>