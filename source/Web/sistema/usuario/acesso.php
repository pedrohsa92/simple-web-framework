





<div class="phsaTabContent">
  <form id="formAcesso" class="needs-validation" novalidate>
    <input name="usu_id" value="<?= $usuario->usu_id ?>" hidden>
    <input name="location" value="/acesso" hidden>

    <section>
      <div class="row">
        <div class="col-12 text-phsa mb-3">
          <h5>Dados de acesso</h5>
        </div>
      </div>

      <?php include 'source/Web/sistema/usuario/dadosDeAcesso.php'; ?>
    </section>

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
      $('#formAcesso #usu_senha, #formAcesso #confirmarsenha').removeAttr('required');

      function validarSenhaAcesso() {
        const senha = $('#formAcesso #usu_senha').val();
        const confirmarSenha = $('#formAcesso #confirmarsenha').val();

        $('#formAcesso #usu_senha')[0].setCustomValidity('');
        $('#formAcesso #confirmarsenha')[0].setCustomValidity('');

        if (senha === '' && confirmarSenha === '') {
          return true;
        }

        $('#formAcesso #confirmarsenha').attr('required', true);

        if (senha !== '' && confirmarSenha === '') {
          $('#formAcesso #confirmarsenha')[0].setCustomValidity('Confirme a senha');
          return false;
        }

        if (senha !== confirmarSenha) {
          $('#formAcesso #confirmarsenha')[0].setCustomValidity('As senhas não conferem');
          return false;
        }

        const temMinimo = senha.length >= 8;
        const temLetra = /[A-Za-z]/.test(senha);
        const temNumero = /[0-9]/.test(senha);
        const temEspecial = /[^A-Za-z0-9]/.test(senha);

        phsaPassForce($('#formAcesso #usu_senha'), {
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
          $('#formAcesso #usu_senha')[0].setCustomValidity('Senha fraca');
          return false;
        }

        return true;
      }

      $('#formAcesso #usu_senha, #formAcesso #confirmarsenha').on('input blur', function () {
        if ($('#formAcesso #usu_senha').val() === '' && $('#formAcesso #confirmarsenha').val() === '') {
          $('#formAcesso #confirmarsenha').removeAttr('required');
          $('#formAcesso #usu_senha')[0].setCustomValidity('');
          $('#formAcesso #confirmarsenha')[0].setCustomValidity('');
          $('#senhaInfo').html('');
          $('#senhaRegra').html('');
          return;
        }

        validarSenhaAcesso();
      });

      $('#formAcesso').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const form = $(this);
        const buttons = form.find('button[type="submit"], input[type="submit"]');

        if (form.data('enviando') === true) {
          return;
        }

        if (typeof validarNiveisAcessoUsuario === 'function' && !validarNiveisAcessoUsuario()) {
          form.addClass('was-validated');
          return;
        }

        if (!validarSenhaAcesso()) {
          form.addClass('was-validated');
          return;
        }

        if (!this.checkValidity()) {
          form.addClass('was-validated');
          return;
        }

        form.data('enviando', true);
        buttons.attr('disabled', true);

        swAguarde({
          title: 'Salvando acesso...',
          text: 'Aguarde enquanto processamos os dados.',
          timer: null
        });

        $.ajax({
          url: '<?= Helpers::url("usuario/salvarUsuario") ?>',
          type: 'POST',
          dataType: 'json',
          data: form.serialize(),
          success: function (response) {
            swResponse(response).then(function () {
              setTimeout(function(){
                if (response.status && response.location) {
                  window.location.href = response.location;
                }
              }, 2000)

              form.data('enviando', false);
              buttons.attr('disabled', false);
            });
          },
          error: function () {
            form.data('enviando', false);
            buttons.attr('disabled', false);

            swError('Não foi possível salvar os dados de acesso do usuário.');
          }
        });
      });
    });
  </script>
<?php $this->end(); ?>