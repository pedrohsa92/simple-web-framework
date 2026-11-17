<?php
  $this->layout('sistema::sistema');
  $templates = $templates ?? [];
  $indicadores = $indicadores ?? (object) [];
  $ultimos = $ultimos ?? [];
  $totalTemplates = count($templates);
  $templatesAtivos = count(array_filter($templates, static fn($item) => ($item->emt_status ?? '') === 'Ativo'));
  $podeAtualizar = (bool) ($podeAtualizar ?? false);
  $esc = static fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
  $numero = static fn($value) => number_format((int) ($value ?? 0), 0, ',', '.');
?>

<div class="card border shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <div class="text-uppercase small text-phsa fw-semibold">Projeto · Módulo</div>
        <h4 class="m-0">Gestão de e-mails</h4>
        <div class="text-muted small mt-1">Centralize templates, campanhas e o acompanhamento das comunicações do projeto.</div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if ($podeAtualizar): ?>
          <a href="<?= Helpers::url('projeto/email/configuracao') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-sliders"></i>&nbsp;Configuração</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border h-100"><div class="card-body">
          <div class="small text-muted">Templates cadastrados</div>
          <div class="fs-3 fw-semibold text-phsa"><?= $numero($totalTemplates) ?></div>
          <div class="small text-muted"><?= $numero($templatesAtivos) ?> ativo<?= $templatesAtivos === 1 ? '' : 's' ?></div>
        </div></div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border h-100"><div class="card-body">
          <div class="small text-muted">E-mails enviados</div>
          <div class="fs-3 fw-semibold text-phsa"><?= $numero($indicadores->enviados ?? 0) ?></div>
          <div class="small text-muted">Processados com sucesso</div>
        </div></div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border h-100"><div class="card-body">
          <div class="small text-muted">Pendentes</div>
          <div class="fs-3 fw-semibold text-phsa"><?= $numero($indicadores->pendentes ?? 0) ?></div>
          <div class="small text-muted"><?= $numero($indicadores->agendados ?? 0) ?> agendado<?= (int) ($indicadores->agendados ?? 0) === 1 ? '' : 's' ?></div>
        </div></div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border h-100"><div class="card-body">
          <div class="small text-muted">Falhas</div>
          <div class="fs-3 fw-semibold <?= (int) ($indicadores->falhos ?? 0) > 0 ? 'text-danger' : 'text-phsa' ?>"><?= $numero($indicadores->falhos ?? 0) ?></div>
          <div class="small text-muted">E-mails que exigem atenção</div>
        </div></div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12 col-lg-4">
        <div class="card border h-100">
          <div class="card-body">
            <h5 class="text-phsa mb-2"><i class="fa-solid fa-layer-group"></i>&nbsp; Templates</h5>
            <p class="text-muted small">Configure mensagens, remetentes SMTP e palavras-chave disponíveis para cada template.</p>
            <div class="d-flex flex-wrap gap-2">
              <a href="<?= Helpers::url('projeto/email/template') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-list"></i>&nbsp;Gerenciar templates</a>
              <?php if ($templatesAtivos === 0): ?><span class="small text-warning align-self-center"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;Nenhum ativo</span><?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="card border h-100">
          <div class="card-body">
            <h5 class="text-phsa mb-2"><i class="fa-solid fa-bullhorn"></i>&nbsp; Campanhas</h5>
            <p class="text-muted small">Planeje comunicações para diferentes públicos e acompanhe seus disparos.</p>
            <a href="<?= Helpers::url('projeto/email/campanha') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-right"></i>&nbsp;Acessar campanhas</a>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="card border h-100">
          <div class="card-body">
            <h5 class="text-phsa mb-2"><i class="fa-solid fa-paper-plane"></i>&nbsp; Envios</h5>
            <p class="text-muted small">Consulte o histórico e acompanhe o processamento das mensagens enviadas.</p>
            <a href="<?= Helpers::url('projeto/email/enviado') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-right"></i>&nbsp;Gerenciar envios</a>
          </div>
        </div>
      </div>
    </div>

    <div class="card border">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
          <div><h5 class="m-0 text-phsa">Últimos envios</h5><div class="small text-muted mt-1">Acompanhe as comunicações mais recentes do projeto.</div></div>
          <a href="<?= Helpers::url('projeto/email/enviado') ?>" class="text-phsa small">Ver todos&nbsp;<i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Mensagem</th><th>Destinatário</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
              <?php foreach ($ultimos as $item): ?>
                <tr>
                  <td><a class="text-phsa fw-semibold" href="<?= Helpers::url('projeto/email/enviado/' . (int) ($item->eme_id ?? 0)) ?>"><?= $esc($item->eme_title ?? '-') ?></a><div class="small text-muted">#<?= (int) ($item->eme_id ?? 0) ?></div></td>
                  <td><div><?= $esc($item->eme_destinarionome ?? '-') ?></div><div class="small text-muted"><?= $esc($item->eme_destinatarioemail ?? '-') ?></div></td>
                  <td><span class="badge bg-<?= $esc($item->eme_statusclass ?? 'secondary') ?>"><?= $esc($item->eme_status ?? '-') ?></span></td>
                  <td><?= empty($item->eme_dtcad) ? '-' : date('d/m/Y H:i', strtotime($item->eme_dtcad)) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($ultimos)): ?><tr><td colspan="4" class="text-center text-muted py-4">Nenhum envio registrado até o momento.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
