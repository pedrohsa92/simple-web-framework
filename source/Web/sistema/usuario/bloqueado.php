<?php $this->layout("sistema::login"); ?>

<div class="cover-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-sm-3"></div>
      <div class="col-sm-6">
        <div class="card overflow-hidden bg-opacity-25">
          <div class="row g-0">
            <div class="col-sm-12">
              <div class="d-flex flex-column h-100">
                <div class="auth-brand p-2">
                  <center>
                    <div class="molduraExterna molduraExterna-50">
                      <div class="molduraInterna">
                        <img src="<?= Helpers::storage($usuario->usu_logo) ?>" alt="logo">
                      </div>
                    </div>
                  </center>
                </div>
                <div class="p-4 my-auto">
                  <div class="text-center mb-3">
                    <h5 class="text-phsa mb-1"><?= $usuario->usu_razaosocial ?></h5>
                    <h6 class="text-muted mb-1">Sessão Bloqueada</h6>
                  </div>

                  <form id="formUnlock" class="needs-validation" novalidate>
                    <div class="form-floating mb-3">
                      <input type="password" class="form-control" name="usu_senha" placeholder="Senha" required autofocus>
                      <label for="usu_senha">Senha</label>
                      <div class="w-100 text-secondary text-center small">Informe sua senha para continuar</div>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="bto bto-phsa w-100">
                        <i class="fa-solid fa-lock-open me-2"></i>Desbloquear
                      </button>
                    </div>
                  </form>

                  <div class="text-center mt-3">
                    <button onclick="fgbDesconectar()" class="bto small text-danger w-100">
                      <i class="fa-solid fa-arrow-right-from-bracket"></i> Sair do sistema
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-3"></div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
<script type="text/javascript">
  $(document).ready(function() {
    $('#formUnlock').submit(function(e) {
      e.preventDefault();
      if (this.checkValidity() === false) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return false;
      }
      this.classList.add('was-validated');

      $.ajax({
        url: '<?= Helpers::url("usuario/desbloquearUsuario") ?>',
        type: 'post',
        dataType: 'json',
        data: $(this).serialize(),
        success: function(response) {
        	console.log(response);
        	swResponse(response);
          if (response.status) {
            setTimeout(function(){
            	location.href = '<?= Helpers::url("dashboard") ?>';
            }, 2000);
          }
        }
      });
    });
  });

  function fgbDesconectar(destino = null) {
    if (destino === null) {
      destino = fgbUrl('login');
    }

    Swal.fire({
      title: 'Deseja sair do sistema?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-check"></i> Sim',
      cancelButtonText: '<i class="fas fa-times"></i> Não',
      customClass: {
        confirmButton: 'bto bto-phsa',
        cancelButton: 'bto bto-phsa-light'
      },
      reverseButtons: true,
      allowOutsideClick: false
    }).then((result) => {
      if (!result.isConfirmed) {
        return;
      }

      swAguarde({
        title: 'Desconectando...',
        html: '<b>Por favor, aguarde...</b>'
      });

      $.ajax({
        url: fgbUrl('usuario/logout'),
        type: 'post',
        dataType: 'json',
        success: function (response) {
          swResponse(response);

          if (response.status) {
            setTimeout(function () {
              location.replace(destino);
            }, 1000);
          }
        },
        error: function (xhr) {
          console.error(xhr.responseText);
          swError('Erro ao tentar desconectar. Por favor, tente novamente.');
        }
      });
    });
  }
</script>
<?php $this->end(); ?>

<style>
  .cover-page {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    background: linear-gradient(135deg, #a8edea, #fed6e3);
    overflow: hidden;
  }
</style>
