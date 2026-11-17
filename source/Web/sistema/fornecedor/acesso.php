<div class="phsaTabContent">
  <form id="formAcessoFornecedor" class="needs-validation" novalidate autocomplete="off">
    <input type="hidden" name="for_id" value="<?= (int) ($fornecedor->for_id ?? 0) ?>">

    <section>
      <div class="row">
        <div class="col-12 text-phsa mb-3">
          <h5>Dados de acesso</h5>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4 mb-3">
          <div class="form-floating">
            <input
              id="for_email_acesso"
              name="for_email"
              value="<?= $valor('for_email') ?>"
              type="email"
              class="form-control input-phsa-focus input-phsa-hover"
              placeholder="E-mail"
              maxlength="100"
              autocomplete="off"
              required
            >
            <label for="for_email_acesso">E-mail</label>
            <div class="invalid-feedback">Informe um e-mail válido.</div>
          </div>
        </div>

        <div class="col-sm-4 mb-3">
          <div class="form-floating">
            <input
              id="for_celular_acesso"
              name="for_celular"
              value="<?= $valor('for_celular') ?>"
              class="form-control input-phsa-focus input-phsa-hover whatsapp"
              placeholder="Celular"
              maxlength="15"
              autocomplete="off"
            >
            <label for="for_celular_acesso">Celular</label>
          </div>
        </div>

        <div class="col-sm-4 mb-3">
          <div class="form-floating">
            <input
              id="for_username"
              name="for_username"
              value="<?= $valor('for_username') ?>"
              class="form-control input-phsa-focus input-phsa-hover"
              placeholder="Usuário"
              maxlength="50"
              pattern="[a-z0-9_]+"
              required
              autocomplete="off"
            >
            <label for="for_username">Usuário</label>
            <div class="invalid-feedback">Use apenas letras, números e underline.</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 mb-3">
          <button type="button" id="btnTrocarSenhaAcessoFornecedor" class="bto bto-phsa-light w-100">
            <i class="fa-solid fa-key"></i>&nbsp;Trocar senha de acesso?
          </button>
        </div>
      </div>

      <div id="blocoTrocaSenhaAcessoFornecedor" style="display: none;">
        <div class="row">
          <div class="col-sm-6 mb-3">
            <div class="form-floating position-relative">
              <input
                id="for_senha"
                name="for_senha"
                minlength="8"
                type="password"
                class="form-control input-phsa-focus input-phsa-hover pe-5"
                placeholder="Senha"
                autocomplete="new-password"
              >
              <label for="for_senha">Senha</label>
              <button type="button" class="btn-toggle-senha-fornecedor border-0 bg-transparent position-absolute top-50 end-0 translate-middle-y me-2 text-phsa" data-target="for_senha" tabindex="-1" title="Mostrar senha">
                <i class="fa-solid fa-eye"></i>
              </button>
              <div class="invalid-feedback">Informe uma senha válida.</div>
            </div>
          </div>

          <div class="col-sm-6 mb-3">
            <div class="form-floating position-relative">
              <input
                id="for_senha_confirmacao"
                name="for_senha_confirmacao"
                minlength="8"
                type="password"
                class="form-control input-phsa-focus input-phsa-hover pe-5"
                placeholder="Confirmar senha"
                autocomplete="new-password"
              >
              <label for="for_senha_confirmacao">Confirmar senha</label>
              <button type="button" class="btn-toggle-senha-fornecedor border-0 bg-transparent position-absolute top-50 end-0 translate-middle-y me-2 text-phsa" data-target="for_senha_confirmacao" tabindex="-1" title="Mostrar senha">
                <i class="fa-solid fa-eye"></i>
              </button>
              <div class="invalid-feedback">Confirme a senha corretamente.</div>
            </div>
          </div>
        </div>
        <div class="small text-muted mb-3">A senha inicial é gerada automaticamente com os 6 primeiros dígitos do documento ou celular.</div>
      </div>

      <div class="row">
        <div class="col-sm-12 mb-3 text-end">
          <button id="btnSalvarAcessoFornecedor" class="bto bto-phsa" type="submit">
            <i class="fas fa-save"></i>&nbsp;<span>Salvar</span>
          </button>
        </div>
      </div>
    </section>
  </form>
</div>

<?php $this->push('script'); ?>
  <script type="text/javascript">
    $(document).ready(function () {
      const form = $('#formAcessoFornecedor');

      function validarSenhaAcessoFornecedor() {
        const senha = $('#for_senha').val() || '';
        const confirmacao = $('#for_senha_confirmacao').val() || '';
        const trocaAtiva = $('#blocoTrocaSenhaAcessoFornecedor').is(':visible');

        $('#for_senha')[0].setCustomValidity('');
        $('#for_senha_confirmacao')[0].setCustomValidity('');

        if (!trocaAtiva && senha === '' && confirmacao === '') {
          return true;
        }

        if (senha !== confirmacao) {
          $('#for_senha_confirmacao')[0].setCustomValidity('As senhas não conferem');
          return false;
        }

        const senhaForte = senha.length >= 8
          && /[A-Za-z]/.test(senha)
          && /[0-9]/.test(senha)
          && /[^A-Za-z0-9]/.test(senha);

        if (!senhaForte) {
          $('#for_senha')[0].setCustomValidity('Senha fraca');
          return false;
        }

        return true;
      }

      $('#btnTrocarSenhaAcessoFornecedor').on('click', function () {
        $(this).hide();
        $('#blocoTrocaSenhaAcessoFornecedor').slideDown(200);
        $('#for_senha, #for_senha_confirmacao').attr('required', true);
      });

      $('#for_username').on('input', function () {
        this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
      });

      $('#for_senha, #for_senha_confirmacao').on('input blur', validarSenhaAcessoFornecedor);

      $('.btn-toggle-senha-fornecedor').on('click', function () {
        const button = $(this);
        const input = $('#' + button.data('target'));

        if (input.attr('type') === 'password') {
          input.attr('type', 'text');
          button.html('<i class="fa-solid fa-eye-slash"></i>');
          button.attr('title', 'Ocultar senha');
          return;
        }

        input.attr('type', 'password');
        button.html('<i class="fa-solid fa-eye"></i>');
        button.attr('title', 'Mostrar senha');
      });

      form.on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if (!validarSenhaAcessoFornecedor() || !this.checkValidity()) {
          form.addClass('was-validated');
          if (typeof window.alertarFormularioInvalido === 'function') {
            window.alertarFormularioInvalido(this);
          }
          return;
        }

        if (form.data('enviando') === true) return;

        form.data('enviando', true);
        const button = $('#btnSalvarAcessoFornecedor').prop('disabled', true);
        swAguarde({ title: 'Salvando acesso...', text: 'Aguarde enquanto processamos os dados.', timer: null });

        $.ajax({
          url: '<?= Helpers::url('fornecedor/salvarAcessoFornecedor') ?>',
          type: 'POST',
          dataType: 'json',
          data: form.serialize(),
          success: function (response) {
            swResponse(response, { timer: null });

            if (response.status && response.location) {
              setTimeout(function () {
                window.location.replace(response.location);
              }, 2000);
              return;
            }

            form.data('enviando', false);
            button.prop('disabled', false);
          },
          error: function () {
            form.data('enviando', false);
            button.prop('disabled', false);
            swError('Não foi possível salvar os dados de acesso.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>
