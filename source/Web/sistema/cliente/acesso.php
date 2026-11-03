





<div class="phsaTabContent">
  <form id="formAcesso" class="needs-validation" novalidate>
    <input name="cli_id" value="<?=$cliente->cli_id?>" hidden>
    <input name="get" value="/acesso/" hidden>

    <div class="row">
      <div class="col-sm-6 mb-3">
        <div class="form-floating">
          <input id="cli_username" name="cli_username" value="<?=$cliente->cli_username?>" maxlength="10" pattern="[A-Za-z0-9]{1,10}" class="form-control input-phsa-focus input-phsa-hover" placeholder="Username">
          <label for="cli_username">Username</label>
          <div class="invalid-feedback">Máximo de 10 caracteres alfanuméricos</div>
        </div>
      </div>
      <div class="col-sm-6 mb-3">
        <div class="form-floating">
          <input id="cli_email" name="cli_email" value="<?=$cliente->cli_email?>" type="email" class="form-control input-phsa-focus input-phsa-hover" placeholder="Email" required>
          <label for="cli_email">Email</label>
          <div class="invalid-feedback">Campo obrigatório</div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6 mb-3">
        <div class="form-floating">
          <input id="cli_senha" name="cli_senha" type="password" class="form-control input-phsa-focus input-phsa-hover" placeholder="Senha">
          <label for="cli_senha">Senha</label>
          <div class="invalid-feedback">Campo obrigatório</div>
        </div>
      </div>
      <div class="col-sm-6 mb-3">
        <div class="form-floating">
          <input id="confirmarsenha" type="password" class="form-control input-phsa-focus input-phsa-hover" placeholder="Confirmar Senha">
          <label for="confirmarsenha">Confirmar Senha</label>
          <div class="invalid-feedback">Campo obrigatório</div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12 mb-3 text-end">
        <button class="bto bto-phsa" type="submit">
          <i class="fas fa-save"></i>&nbsp;Salvar
        </button>
      </div>
    </div>
  </form>
</div>


<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      $("#formAcesso").on("submit", function (event) {
        event.preventDefault();

        if ($("#cli_senha").val() !== $("#confirmarsenha").val()) {
          swWarning("As senhas não coincidem!");
          return;
        }
        SimpleForm.bindForm('#formAcesso', {
          urlDestino: '<?= Helpers::url("cliente/salvarCliente") ?>'
        });
      });
    });
  </script>
<?php $this->end(); ?>