<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div id="planodefundo" class="card-header cardPlanoDefundo" style="background-image: url('<?= Helpers::storage($origem->planodefundo, 'storage/global/planodefundo.jpg') ?>');">
    <div id="divCardLogo" class="divCardLogoContainer molduraExterna molduraExterna-cardlogo">
      <div class="molduraInterna">
        <img id="imgLogo" class="origem_logo imgLogo" src="<?= Helpers::storage($origem->logo, 'storage/global/semperfil.png') ?>" alt="Logo da origem">
      </div>
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-sm-12 mb-3 pb-2 text-uppercase border-bottom">
		    <h4 class="m-0">
		      <?=Helpers::adicionaMascaraCnpjCPf($origem->cnpjcpf)?> - <?=$origem->razaosocial?>
		    </h4>
		    <h5 class="m-0 text-secondary">
		      <?=$origem->nomefantasia?>
		    </h5>
		  </div>
		</div>

    <div class="row gx-0">
      <!-- Coluna 1 - Lista de Conversas -->
      <div class="col-sm-4 col-md-4 chat-sidebar border border-muted p-2">
        <div class="d-flex justify-content-between align-items-center border-bottom mb-1 pb-2">
          <label class="form-label text-uppercase fw-bold m-0">Mensagens</label>
          <button id="novaConversa" onclick="abrirNovaConversa()" class="btn btn-sm bto-phsa" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Iniciar conversa" data-bs-delay='{"show":500}'>
            <i class="fa-solid fa-comment-medical"></i>
          </button>
        </div>
        <div class="chat-list">
          <?php for ($i = 1; $i <= 15; $i++): ?>
            <div class="chat-item" data-chat-id="<?= $i ?>">
              <img src="https://randomuser.me/api/portraits/<?= rand(0, 1) ? 'men' : 'women' ?>/<?= rand(1, 99) ?>.jpg" class="chat-avatar">
              <div class="chat-content">
                <div class="d-flex justify-content-between align-items-center">
                  <strong class="chat-name">Contato <?=$i?></strong>
                  <small class="chat-time text-muted">14:00</small>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="chat-last-message text-secondary">Última mensagem enviada...</span>
                  <span class="badge bg-phsa-light chat-unread float-end"><?=$i?></span>
                </div>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Coluna 2 - Janela de Conversa -->
      <div class="col-sm-8 col-md-8 chat-window border border-muted">
        <!-- Cabeçalho fixo -->
        <div class="chat-header d-flex align-items-center p-2 border-bottom">
          <img src="https://randomuser.me/api/portraits/men/1.jpg" class="chat-avatar">
          <strong class="chat-header-name ms-2">Contato 1</strong>
        </div>

        <!-- Mensagens da conversa -->
        <div class="chat-messages">
          <div class="message received">
            <p>Olá, tudo bem?</p>
            <small class="text-muted">14:01</small>
          </div>
          <div class="message sent">
            <p>Oi! Tudo ótimo, e com você?</p>
            <small class="text-muted">14:02</small>
          </div>
          <div class="message received">
            <p>Também! Precisamos marcar um encontro para discutir os detalhes do projeto.</p>
            <small class="text-muted">14:05</small>
          </div>
          <div class="message sent">
            <p>Com certeza, qual o melhor dia para você?</p>
            <small class="text-muted">14:07</small>
          </div>
          <div class="message sent">
            <p>Não me deixa no vácuo!</p>
            <small class="text-muted">15:22</small>
          </div>
          <div class="message received">
            <p>Desculpe, estava no trânsito, pode ser semana que vem, segunda às 09horas, vou enviar um email com todas informações necessárias</p>
            <small class="text-muted">16:00</small>
          </div>
        </div>

        <!-- Formulário de envio -->
        <form class="chat-form">
          <button type="button" class="bto bto-phsa-light"><i class="fa-solid fa-paperclip"></i></button>
          <input type="text" class="form-control" placeholder="Escreva aqui sua mensagem">
          <button type="submit" class="bto bto-phsa"><i class="fa-regular fa-paper-plane"></i></button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->push("script"); ?>
  <script type="text/javascript">
		$(document).ready(function () {
		  $('#pesquisarMensagem').on('input', function () {
		    const search = $(this).val().toLowerCase();
		    $('.chat-item').each(function () {
		      const name = $(this).find('.chat-name').text().toLowerCase();
		      if (name.includes(search)) {
		        $(this).show();
		      } else {
		        $(this).hide();
		      }
		    });
		  });
		});
  </script>
<?php $this->end(); ?>

<?php $this->push("style"); ?>
<style type="text/css">
  /* Estilos gerais */
  .chat-sidebar {
    border-right: 1px solid #ddd;
    height: 500px;
    overflow-y: auto;
    padding: 15px;
    background-color: #f8f9fa;
  }

  .chat-sidebar label.form-label {
    margin-bottom: 0;
  }

  .chat-sidebar .btn.bto-phsa {
    margin: 0;
    padding: 5px 10px;
  }

  .chat-window {
    height: 500px;
    display: flex;
    flex-direction: column;
    padding: 15px;
  }

  .chat-header {
    height: 60px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #ddd;
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 1000;
  }

  .chat-header .chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
  }

  .chat-header .chat-header-name {
    font-weight: bold;
    font-size: 1rem;
    color: #333;
  }

  /* Mensagens */
  .chat-messages {
  	padding-top: 1rem!important;
    flex-grow: 1;
    overflow-y: auto;
    border-left: solid 1px #ddd!important;
    border-right: solid 1px #ddd!important;
  }

  .message {
    margin-bottom: 10px;
  }

  .message p {
    margin: 0px!important;
  }

  .message.received {
    text-align: left;
    padding-left: 0.7rem!important;
  }

  .message.sent {
    text-align: right;
    padding-right: 0.7rem!important;
  }

  .message p {
    display: inline-block;
    padding: 10px;
    background-color: #e9ecef;
    border-radius: 10px;
    max-width: 70%;
  }

  .message.sent p {
    background-color: #007bff;
    color: #fff;
  }

  .message small {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
  }

  /* Lista de Conversas */
  .chat-item {
    display: flex;
    align-items: flex-start;
    padding: 10px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
  }

  .chat-item:hover {
    background-color: #f1f1f1;
  }

  .chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
  }

  .chat-content {
    flex-grow: 1;
  }

  .chat-name {
    font-weight: bold;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
    max-width: calc(100% - 50px);
  }

  .chat-time {
    font-size: 0.85rem;
  }

  .chat-last-message {
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: calc(100% - 50px);
  }

  .chat-unread {
    margin-left: auto;
  }

  /* Formulário de envio */
  .chat-form {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-top: 1px solid #ddd;
  }

  .chat-form .bto {
    padding: 5px 10px;
  }

  .chat-form .form-control {
    flex-grow: 1;
  }
</style>
<?php $this->end(); ?>