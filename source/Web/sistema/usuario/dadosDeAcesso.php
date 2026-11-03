





<div class="row">
  <div class="col-sm-12 mb-3">
    <select
      id="niveisacesso"
      class="form-select input-phsa-focus input-phsa-hover"
      multiple
      required
      data-total="<?= $getNivelAcesso->total ?>"
    >
      <?php if ($getNivelAcesso->total > 0): ?>
        <?php foreach ($getNivelAcesso->data as $nivelAcesso): ?>
          <option
            value="<?= $nivelAcesso->nva_id ?>"
            data-nome="<?= $nivelAcesso->nva_nome ?>"
            <?= ($getNivelAcesso->total == 1) ? 'selected' : '' ?>
          >
            <?= $nivelAcesso->nva_nome ?>
          </option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>

    <input id="usu_niveisacesso" name="usu_niveisacesso" value="" hidden required>
    <div class="invalid-feedback">Selecione pelo menos um nível de acesso</div>
  </div>
</div>

<div class="row">
  <div class="col-sm-4 mb-3">
    <div class="form-floating">
      <input
        id="usu_email"
        name="usu_email"
        value="<?= $usuario->usu_email ?>"
        type="email"
        class="form-control input-phsa-focus input-phsa-hover"
        placeholder="E-mail"
        autocomplete="new-password"
        required
      >
      <label for="usu_email">E-mail</label>
      <div class="invalid-feedback">Campo obrigatório</div>
    </div>
  </div>

  <div class="col-sm-4 mb-3">
    <div class="form-floating">
      <input
        id="usu_celular"
        name="usu_celular"
        value="<?= $usuario->usu_celular ?>"
        class="form-control input-phsa-focus input-phsa-hover whatsapp"
        placeholder="Celular"
        autocomplete="new-password"
      >
      <label for="usu_celular">Celular</label>
    </div>
  </div>

  <div class="col-sm-4 mb-3">
    <div class="form-floating">
      <input
        id="usu_username"
        name="usu_username"
        value="<?= $usuario->usu_username ?>"
        class="form-control input-phsa-focus input-phsa-hover"
        placeholder="Usuário"
        maxlength="50"
        pattern="[A-Za-z0-9_]+"
        autocomplete="new-password"
      >
      <label for="usu_username">Usuário</label>
      <div class="invalid-feedback">Use apenas letras, números e underline</div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6 mb-3">
    <div class="form-floating position-relative">
      <input
        id="usu_senha"
        name="usu_senha"
        minlength="8"
        type="password"
        class="form-control input-phsa-focus input-phsa-hover pe-5"
        placeholder="Senha"
        autocomplete="new-password"
        required
      >
      <label for="usu_senha">Senha</label>
      <button
        type="button"
        class="btn-toggle-senha border-0 bg-transparent position-absolute top-50 end-0 translate-middle-y me-2 text-phsa"
        data-target="usu_senha"
        title="Mostrar senha"
        tabindex="-1"
      >
        <i class="fa-solid fa-eye"></i>
      </button>
      <div class="invalid-feedback">Informe uma senha válida</div>
    </div>
    <div id="senhaInfo" class="mt-1"></div>
  </div>

  <div class="col-sm-6 mb-3">
    <div class="form-floating position-relative">
      <input
        id="confirmarsenha"
        minlength="8"
        type="password"
        class="form-control input-phsa-focus input-phsa-hover pe-5"
        placeholder="Confirmar Senha"
        autocomplete="new-password"
        required
      >
      <label for="confirmarsenha">Confirmar Senha</label>
      <button
        type="button"
        class="btn-toggle-senha border-0 bg-transparent position-absolute top-50 end-0 translate-middle-y me-2 text-phsa"
        data-target="confirmarsenha"
        title="Mostrar senha"
        tabindex="-1"
      >
        <i class="fa-solid fa-eye"></i>
      </button>
      <div class="invalid-feedback">Confirme a senha corretamente</div>
    </div>
  </div>

  <div class="col-12 mb-3">
    <div id="senhaRegra" class="small"></div>
  </div>
</div>

<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      function iniciarMultiselectNivelAcesso() {
        if (typeof $.fn.multiselect === 'function') {
          $('#niveisacesso').multiselect({
            templates: {
              button: '<button type="button" class="multiselect dropdown-toggle bto bto-phsa-light" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span></button>',
            },
            buttonWidth: '100%',
            nonSelectedText: 'Selecione os níveis de acesso',
            nSelectedText: 'níveis selecionados',
            allSelectedText: 'Todos selecionados',
            numberDisplayed: 2,
            includeSelectAllOption: false,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            filterPlaceholder: 'Pesquisar',
            onChange: function () {
              validarNiveisAcessoUsuario();
            }
          });
        }
      }

      function montarNiveisAcessoUsuario() {
        const niveis = [];

        $('#niveisacesso option:selected').each(function () {
          niveis.push({
            nva_id: parseInt($(this).val()),
            nva_nome: $(this).data('nome')
          });
        });

        $('#usu_niveisacesso').val(JSON.stringify(niveis));

        return niveis;
      }

      function atualizarMultiselectNivelAcesso() {
        if (typeof $.fn.multiselect === 'function') {
          $('#niveisacesso').multiselect('refresh');
        }
      }

      function validarNiveisAcessoUsuario() {
        const total = parseInt($('#niveisacesso').data('total'));
        const niveis = montarNiveisAcessoUsuario();

        if (total === 1 && niveis.length === 0) {
          $('#niveisacesso option:first').prop('selected', true);
          atualizarMultiselectNivelAcesso();
          montarNiveisAcessoUsuario();
        }

        if (montarNiveisAcessoUsuario().length === 0) {
          $('#niveisacesso')[0].setCustomValidity('Selecione pelo menos um nível de acesso');
          return false;
        }

        $('#niveisacesso')[0].setCustomValidity('');
        return true;
      }

      function validarForcaSenhaUsuario() {
        const senha = $('#usu_senha').val();
        const temMinimo = senha.length >= 8;
        const temLetra = /[A-Za-z]/.test(senha);
        const temNumero = /[0-9]/.test(senha);
        const temEspecial = /[^A-Za-z0-9]/.test(senha);

        phsaPassForce($('#usu_senha'), {
          outputInfo: $('#senhaInfo'),
          outputRule: $('#senhaRegra'),
          passTrim: true,
          minLenght: 8,
          maxLenght: 50,
          typePass: 'alphanumeric',
          notSymbolKey: false,
          notUnknownKey: false,
          rule: {
            minLowKey: 1,
            minUpperKey: 0,
            minNumberKey: 1,
            minSymbolKey: 1
          }
        });

        if (!temMinimo || !temLetra || !temNumero || !temEspecial) {
          $('#usu_senha')[0].setCustomValidity('Senha fraca');
          return false;
        }

        $('#usu_senha')[0].setCustomValidity('');
        return true;
      }

      function validarConfirmacaoSenhaUsuario() {
        const senha = $('#usu_senha').val();
        const confirmarSenha = $('#confirmarsenha').val();

        if (senha === '' || confirmarSenha === '' || senha !== confirmarSenha) {
          $('#confirmarsenha')[0].setCustomValidity('As senhas não conferem');
          return false;
        }

        $('#confirmarsenha')[0].setCustomValidity('');
        return true;
      }

      function validarSenhaUsuario() {
        const forcaSenha = validarForcaSenhaUsuario();
        const confirmacaoSenha = validarConfirmacaoSenhaUsuario();

        return forcaSenha && confirmacaoSenha;
      }

      $('#usu_username').on('input', function () {
        this.value = this.value.replace(/[^A-Za-z0-9_]/g, '');
      });

      $('#niveisacesso').on('change', function () {
        validarNiveisAcessoUsuario();
      });

      $('#usu_senha').on('input blur', function () {
        validarSenhaUsuario();
      });

      $('#confirmarsenha').on('input blur', function () {
        validarConfirmacaoSenhaUsuario();
      });

      $('#formDadosBasicos').on('submit', function () {
        validarNiveisAcessoUsuario();
        validarSenhaUsuario();
      });

      $('.btn-toggle-senha').on('click', function () {
        const button = $(this);
        const input = $('#' + button.data('target'));

        if (input.attr('type') === 'password') {
          input.attr('type', 'text');
          button.html('<i class="fa-solid fa-eye-slash"></i>');
          button.attr('title', 'Ocultar senha');
          button.attr('aria-label', 'Ocultar senha');
          return;
        }

        input.attr('type', 'password');
        button.html('<i class="fa-solid fa-eye"></i>');
        button.attr('title', 'Mostrar senha');
        button.attr('aria-label', 'Mostrar senha');
      });

      iniciarMultiselectNivelAcesso();

      if (parseInt($('#niveisacesso').data('total')) === 1) {
        $('#niveisacesso option:first').prop('selected', true);
        $('#niveisacesso').attr('title', 'Existe apenas um nível disponível para este cadastro');
        atualizarMultiselectNivelAcesso();
      }

      validarNiveisAcessoUsuario();
      validarSenhaUsuario();
    });
  </script>
<?php $this->end(); ?>