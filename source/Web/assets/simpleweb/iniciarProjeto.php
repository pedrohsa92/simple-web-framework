<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuração de novo sistema</title>
  <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' rel='stylesheet'>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body class="bg-light align-items-center" style="height: 100vh;">

  <div class="container mt-5">
    <div class="alert alert-danger d-flex align-items-center" role="alert">
      <div class="col-sm-1 p-2 text-center">
        <i class="fas fa-exclamation-triangle fa-3x"></i>
      </div>
      <div class="col-sm p-2">
        <h4 class="alert-heading">Erro de Configuração</h4>
        <p>O projeto não foi devidamente configurado</p>
      </div>
    </div>

    <hr>

    <h2 class="text-center">Configure aqui um novo sistema</h2>

    <form id="formIniciarProjeto" class="mt-4 needs-validation" novalidate>
      <input hidden name="iniciarProjeto" value="sim">

      <div class="row">
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="emp_cnpjcpf" name="emp_cnpjcpf" placeholder="CNPJ/CPF" required>
            <label for="emp_cnpjcpf">CNPJ/CPF *</label>
            <div class="invalid-feedback w-100">CNPJ/CPF é obrigatório.</div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="emp_razaosocial" name="emp_razaosocial" placeholder="Razão Social" required minlength="4" maxlength="255">
            <label for="emp_razaosocial">Razão Social *</label>
            <div class="invalid-feedback w-100">Razão Social é obrigatória.</div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-floating mb-3">
            <input type="date" class="form-control text-center" id="emp_dtnasc" name="emp_dtnasc" placeholder="Data de nascimento ou fundação" required>
            <label for="emp_dtnasc">Data de Nascimento/Fundação</label>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="emp_nomefantasia" name="emp_nomefantasia" placeholder="Nome Fantasia" minlength="4" maxlength="255">
            <label for="emp_nomefantasia">Nome Fantasia</label>
          </div>
        </div>
        <div class="col-sm-8">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="emp_descricaoresumida" name="emp_descricaoresumida" placeholder="Descrição Resumida" maxlength="160">
            <label for="emp_descricaoresumida">Descrição Resumida</label>
          </div>
        </div>
      </div>

      <hr>

      <div class="row mt-4">
        <h3>Usuário Responsável</h3>
      </div>

      <div class="row">
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="usu_cnpjcpf" name="usu_cnpjcpf" placeholder="CNPJ/CPF" required>
            <label for="usu_cnpjcpf">CNPJ/CPF *</label>
            <div class="invalid-feedback w-100">CNPJ/CPF é obrigatório.</div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="usu_razaosocial" name="usu_razaosocial" placeholder="Nome Completo" required minlength="4" maxlength="255">
            <label for="usu_razaosocial">Nome Completo *</label>
            <div class="invalid-feedback w-100">Nome completo é obrigatório.</div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-floating mb-3">
            <input type="date" class="form-control text-center" id="usu_dtnasc" name="usu_dtnasc" placeholder="Data de nascimento" required>
            <label for="usu_dtnasc">Data de Nascimento</label>
          </div>
        </div>

        <div class="col-sm-12">
          <span id="span-info"></span>
        </div>
      </div>

      <div class="row">
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="email" class="form-control" id="usu_email" name="usu_email" placeholder="Email" required>
            <label for="usu_email">Email *</label>
            <div class="invalid-feedback w-100">Informe um email válido.</div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="password" class="form-control" id="usu_senha" name="usu_senha" placeholder="Senha" required autocomplete="new-password">
            <label for="usu_senha">Senha *</label>
            <div class="invalid-feedback w-100">Senha é obrigatória.</div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-floating mb-3">
            <input type="password" class="form-control" id="usu_senhaconfirme" placeholder="Confirme a Senha" required autocomplete="new-password">
            <label for="usu_senhaconfirme">Confirme a Senha *</label>
            <div class="invalid-feedback w-100">Confirmação de senha é obrigatória.</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm text-end">
          <button id="btnStart" type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Salvar
          </button>
        </div>
      </div>
    </form>

    <div id="errorContainer"></div>
  </div>

  <script>
    $(document).ready(function() {
      $('#emp_cnpjcpf').on('change keypress focus focusin', function() {
        $(this).unmask();
      }).on('blur', function() {
        const cnpjcpf = $(this).val().replace(/\D/g, '');
        if ( cnpjcpf.length==11 ) {
          $(this).mask('000.000.000-00');
          $('#emp_dtnasc').siblings('label').text('Data de nascimento');
        } else if ( cnpjcpf.length==14 ) {
          $(this).mask('00.000.000/0000-00');
          $('#emp_dtnasc').siblings('label').text('Data de fundação');
        } else {
          $(this).unmask();
          $('#emp_dtnasc').siblings('label').text('Corrija CNPJ/CPF');
        }
      });

      $('#usu_cnpjcpf').on('change keypress focus focusin', function() {
        $(this).unmask();
      }).on('blur', function() {
        const cnpjcpf = $(this).val().replace(/\D/g, '');
        if (cnpjcpf.length == 11) {
          $(this).mask('000.000.000-00');
          $('#usu_dtnasc').siblings('label').text('Data de nascimento');
        } else if (cnpjcpf.length == 14) {
          $(this).mask('00.000.000/0000-00');
          $('#usu_dtnasc').siblings('label').text('Data de fundação');
        } else {
          $(this).unmask();
          $('#usu_dtnasc').siblings('label').text('Corrija CNPJ/CPF');
        }
      });

      $("#formIniciarProjeto").submit(function(event) {
        event.preventDefault();
        if (this.checkValidity() === false) {
          event.stopPropagation();
          this.classList.add('was-validated');
          return false;
        }

        if ( $('#usu_senha').val()!=$('#usu_senhaconfirme').val() ) {
          $('#errorContainer').html(`
            <div class="alert alert-danger mt-3">
              <h4>Atenção</h4>
              <p>As senhas informadas devem ser identicas!</p>
            </div>
          `);
          return false;
        }

        this.classList.add('was-validated');

        var form = $('#formIniciarProjeto').serialize();
        $('#formIniciarProjeto').find('input, button, select, textarea').attr('disabled', true);

        $.ajax({
          url: '<?=Helpers::url("projeto/salvarProjeto")?>',
          type: 'post',
          dataType: 'json',
          data: form,
          success: function (response) {
            if (response.status) {
              $('#errorContainer').html(`
                <div class="alert alert-success mt-3">
                  <h4>${response.title}</h4>
                  <p>${response.text}</p>
                </div>
              `);
              setTimeout(function(){
                location.reload();
              }, 2000);
            } else {
              $('#errorContainer').html(`
                <div class="alert alert-danger mt-3">
                  <h4>${response.title}</h4>
                  <p>${response.text}</p>
                </div>
              `);
              $('#formIniciarProjeto').find('input, button, select, textarea').attr('disabled', false);
            }
          }
        });
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>