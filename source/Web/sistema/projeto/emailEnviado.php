<?php
  $this->layout('sistema::sistema');
  $envio = $envio ?? null;
  $enviosData = ($envios && $envios->status) ? ($envios->data ?? []) : [];
  $esc = static fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
  $data = static function ($value, string $formato = 'd/m/Y H:i'): string {
    return empty($value) ? '-' : date($formato, strtotime((string) $value));
  };
  $json = static function ($value): string {
    $texto = trim((string) ($value ?? ''));
    $decodificado = json_decode($texto, true);
    return is_array($decodificado)
      ? (string) json_encode($decodificado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      : $texto;
  };
?>

<?php if ($envio === null): ?>
  <div class="card border shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
          <div class="text-uppercase small text-phsa fw-semibold">Projeto · E-mails</div>
          <h4 class="m-0">Envios de e-mail</h4>
          <div class="text-muted small mt-1">Consulte as tentativas processadas pelo projeto.</div>
        </div>
        <a href="<?= Helpers::url('projeto/email') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Hub de e-mails</a>
      </div>

      <div class="row mb-3">
        <div class="col-12 mb-3">
          <div class="form-floating">
            <input id="pesquisaEmailEnviado" class="form-control input-phsa-focus input-phsa-hover" placeholder="Pesquisar">
            <label for="pesquisaEmailEnviado">Pesquisar por mensagem, destinatário, template, status ou ID</label>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-2">
        <span class="small text-muted" id="contadorEmailsEnviados"><?= count($enviosData) ?> envio<?= count($enviosData) === 1 ? '' : 's' ?> encontrado<?= count($enviosData) === 1 ? '' : 's' ?></span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle" id="tabelaEmailsEnviados">
          <thead><tr><th>Mensagem</th><th>Destinatário</th><th>Status</th><th>Tentativa</th><th>Cadastro</th><th>Ação</th></tr></thead>
          <tbody id="linhasEmailsEnviados">
            <?php foreach ($enviosData as $item): ?>
              <tr>
                <td><a class="text-phsa fw-semibold" href="<?= Helpers::url('projeto/email/enviado/' . (int) $item->eme_id) ?>"><?= $esc($item->eme_title ?? '-') ?></a><div class="small text-muted">#<?= (int) $item->eme_id ?><?= !empty($item->emt_nome) ? ' · ' . $esc($item->emt_nome) : '' ?></div></td>
                <td><div><?= $esc($item->eme_destinarionome ?? '-') ?></div><div class="small text-muted"><?= $esc($item->eme_destinatarioemail ?? '-') ?></div></td>
                <td><span class="badge bg-<?= $esc($item->eme_statusclass ?? 'secondary') ?>"><?= $esc($item->eme_status ?? '-') ?></span></td>
                <td><?= (int) ($item->eme_tentativa ?? 1) ?></td>
                <td><?= $data($item->eme_dtcad) ?></td>
                <td><a href="<?= Helpers::url('projeto/email/enviado/' . (int) $item->eme_id) ?>" class="bto btn-sm bto-phsa-light" title="Visualizar envio"><i class="fa-solid fa-eye"></i></a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($enviosData)): ?><tr><td colspan="6" class="text-center text-muted py-4">Nenhum envio registrado.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php $this->push('script'); ?><script>
    $(function () {
      const $campo = $('#pesquisaEmailEnviado');
      const $linhas = $('#linhasEmailsEnviados');
      const $contador = $('#contadorEmailsEnviados');
      let timer = null;

      function escapar(valor) { return $('<div>').text(valor ?? '').html(); }
      function dataBr(valor) {
        if (!valor) return '-';
        const data = new Date(String(valor).replace(' ', 'T'));
        return Number.isNaN(data.getTime()) ? '-' : data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
      }
      function renderizar(itens) {
        $contador.text(itens.length + (itens.length === 1 ? ' envio encontrado' : ' envios encontrados'));
        if (!itens.length) {
          $linhas.html('<tr><td colspan="6" class="text-center text-muted py-4">Nenhum envio encontrado.</td></tr>');
          return;
        }
        $linhas.html(itens.map(function (item) {
          const id = Number(item.eme_id || 0);
          const url = '<?= Helpers::url('projeto/email/enviado') ?>/' + id;
          const template = item.emt_nome ? ' · ' + escapar(item.emt_nome) : '';
          return '<tr><td><a class="text-phsa fw-semibold" href="' + url + '">' + escapar(item.eme_title || '-') + '</a><div class="small text-muted">#' + id + template + '</div></td><td><div>' + escapar(item.eme_destinarionome || '-') + '</div><div class="small text-muted">' + escapar(item.eme_destinatarioemail || '-') + '</div></td><td><span class="badge bg-' + escapar(item.eme_statusclass || 'secondary') + '">' + escapar(item.eme_status || '-') + '</span></td><td>' + Number(item.eme_tentativa || 1) + '</td><td>' + dataBr(item.eme_dtcad) + '</td><td><a href="' + url + '" class="bto btn-sm bto-phsa-light" title="Visualizar envio"><i class="fa-solid fa-eye"></i></a></td></tr>';
        }).join(''));
      }
      function pesquisar() {
        $.ajax({ url: '<?= Helpers::url('projeto/pesquisarEmailsEnviados') ?>', type: 'POST', dataType: 'json', data: { pesquisa: $campo.val() || '' } })
          .done(function (response) { if (response.status) renderizar(response.data || []); else swError(response.text || 'Não foi possível pesquisar os envios.'); })
          .fail(function () { swError('Não foi possível pesquisar os envios.'); });
      }
      $campo.on('input', function () { clearTimeout(timer); timer = setTimeout(pesquisar, 250); });
    });
  </script><?php $this->end(); ?>
<?php else: ?>
  <?php
    $logsEnvio = json_decode((string) ($envio->eme_log ?? ''), true);
    $logsEnvio = is_array($logsEnvio) ? array_values($logsEnvio) : [];
    if (!$logsEnvio) {
      $logsEnvio[] = [
        'usu_razaosocial' => 'Sistema',
        'acao' => 'Registrou o envio',
        'data' => $envio->eme_dtcad ?? null,
        'detalhe' => 'Registro inicial da tentativa de envio.'
      ];
    }
    $statusClass = $esc($envio->eme_statusclass ?? 'secondary');
    $snapshot = $json($envio->eme_snapshot ?? '');
    $resposta = $json($envio->eme_response ?? '');
  ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
          <div class="text-uppercase small text-phsa fw-semibold">Projeto · E-mails · Envio</div>
          <h4 class="m-0">Envio #<?= (int) ($envio->eme_id ?? 0) ?></h4>
          <div class="text-muted small mt-1"><?= $esc($envio->eme_title ?? '-') ?></div>
        </div>
        <a href="<?= Helpers::url('projeto/email/enviado') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar para envios</a>
      </div>

      <header class="phsaScrollHeader">
        <nav class="phsaScrollNav" data-menu="tabEmailEnviado">
          <ul class="phsaScrollList">
            <li class="phsaScrollItem active" data-target="detalhesEmailEnviado"><i class="fa-solid fa-circle-info"></i>&nbsp;Detalhes</li>
            <li class="phsaScrollItem" data-target="mensagemEmailEnviado"><i class="fa-solid fa-envelope-open-text"></i>&nbsp;Mensagem</li>
            <li class="phsaScrollItem" data-target="historicoEmailEnviado"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li>
          </ul>
        </nav>
      </header>

      <div class="phsaTabContent phsaTabSubContent">
        <div class="phsaTabItem active" id="detalhesEmailEnviado">
          <div class="row p-3">
            <?php foreach ([
              'ID' => $envio->eme_id ?? '-',
              'Status' => '<span class="badge bg-' . $statusClass . '">' . $esc($envio->eme_status ?? '-') . '</span>',
              'Destinatário' => ($envio->eme_destinarionome ?? '-') . ' · ' . ($envio->eme_destinatarioemail ?? '-'),
              'Template' => $envio->emt_nome ?? ($envio->emt_templateprojeto ?? '-'),
              'Tentativa' => $envio->eme_tentativa ?? 1,
              'Cadastro' => $data($envio->eme_dtcad),
              'Envio' => $data($envio->eme_dtenvio),
              'Agendamento' => $data($envio->eme_dtagendada),
              'Última alteração' => $data($envio->eme_dtalter)
            ] as $label => $value): ?>
              <div class="col-12 col-md-6 col-xl-3 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel"><?= $esc($label) ?></div><div class="phsaFakeInputValue text-break"><?= $label === 'Status' ? $value : $esc($value) ?></div></div></div>
            <?php endforeach; ?>
          </div>

          <div class="row px-3">
            <div class="col-12 col-xl-6 mb-3"><div class="card border-phsa-light box-phsa-shadow-light h-100"><div class="card-body"><div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-camera"></i>&nbsp;Snapshot do template</h6><button type="button" class="bto btn-sm bto-phsa-light btnCopiarJsonEnvio" data-copiar-target="snapshotEnvio" data-copiar-label="Snapshot do template" title="Copiar snapshot"><i class="fa-solid fa-copy"></i></button></div><pre id="snapshotEnvio" class="jsonEnvioBox mb-0 small text-break"><?= $esc($snapshot !== '' ? $snapshot : '-') ?></pre></div></div></div>
            <div class="col-12 col-xl-6 mb-3"><div class="card border-phsa-light box-phsa-shadow-light h-100"><div class="card-body"><div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-server"></i>&nbsp;Resposta do processamento</h6><button type="button" class="bto btn-sm bto-phsa-light btnCopiarJsonEnvio" data-copiar-target="respostaEnvio" data-copiar-label="Resposta do processamento" title="Copiar resposta"><i class="fa-solid fa-copy"></i></button></div><pre id="respostaEnvio" class="jsonEnvioBox mb-0 small text-break"><?= $esc($resposta !== '' ? $resposta : '-') ?></pre></div></div></div>
            <?php if (!empty($envio->eme_erro)): ?><div class="col-12 mb-3"><div class="alert alert-danger mb-0"><strong><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;Erro do envio</strong><div class="mt-1 text-break"><?= $esc($envio->eme_erro) ?></div></div></div><?php endif; ?>
          </div>
        </div>

        <div class="phsaTabItem" id="mensagemEmailEnviado">
          <div class="p-3">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3"><h5 class="m-0 text-phsa"><i class="fa-solid fa-envelope-open-text"></i>&nbsp;Mensagem renderizada</h5><span class="small text-muted">Conteúdo efetivamente preparado para o envio.</span></div>
            <div class="border rounded overflow-hidden bg-light"><iframe title="Pré-visualização do e-mail" srcdoc="<?= $esc($envio->eme_mensagem ?? '') ?>" sandbox="" style="display:block;width:100%;height:620px;border:0;background:#fff;"></iframe></div>
          </div>
        </div>

        <div class="phsaTabItem" id="historicoEmailEnviado">
          <?php
            $historicoRegistro = $envio;
            $historicoPrefixo = 'eme_';
            $historicoId = (int) ($envio->eme_id ?? 0);
            $historicoStatus = (string) ($envio->eme_status ?? '');
            $historicoStatusClass = (string) ($envio->eme_statusclass ?? 'secondary');
            $historicoLogs = $logsEnvio;
            include __DIR__ . '/../historicoPadrao.php';
          ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($envio !== null): ?>
  <?php $this->push('style'); ?>
    <style>
      .jsonEnvioBox {
        max-height: 360px;
        overflow: auto;
        white-space: pre-wrap;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.65rem;
        padding: 0.85rem;
      }
    </style>
  <?php $this->end(); ?>

  <?php $this->push('script'); ?>
    <script>
      $(function () {
        $('.btnCopiarJsonEnvio').on('click', function () {
          const $botao = $(this);
          const alvo = document.getElementById($botao.data('copiar-target'));
          if (!alvo || typeof fgbCopiarParaAreaDeTransferencia !== 'function') return;
          fgbCopiarParaAreaDeTransferencia(alvo.textContent.trim(), $botao.data('copiar-label') || 'Conteúdo');
        });
      });
    </script>
  <?php $this->end(); ?>
<?php endif; ?>
