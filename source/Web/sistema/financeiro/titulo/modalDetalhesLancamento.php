<?php
  $lan = $lancamento ?? null;
  $titulo = $titulo ?? null;
  $esc = static fn($v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
  $dt = static function ($v, string $fmt = 'd/m/Y'): string {
    $v = trim((string) ($v ?? ''));
    return $v !== '' && strtotime($v) ? date($fmt, strtotime($v)) : '—';
  };
  $money = static fn($v): string => 'R$ ' . number_format((float) ($v ?? 0), 2, ',', '.');
  $status = trim((string) ($lan->lan_status ?? ''));
  if ($status === '' && ($lan->lan_tipoacao ?? '') === 'Correcao') $status = 'Corrigido';
  $status = $status !== '' ? $status : '—';
  $anexoSomenteVisualizacao = in_array($status, ['Estornado', 'Corrigido'], true);
  $entrada = ($lan->lan_natureza ?? '') === 'Entrada';
  $icon = [
    'A pagar' => 'fa-money-bill-wave', 'A receber' => 'fa-hand-holding-dollar',
    'Pago' => 'fa-money-bill-wave', 'Recebido' => 'fa-hand-holding-dollar',
    'Estornado' => 'fa-rotate-left', 'Corrigido' => 'fa-pen-to-square'
  ][$status] ?? 'fa-circle-info';
  $logs = json_decode((string) ($lan->lan_log ?? '[]'), true);
  $logs = is_array($logs) ? $logs : [];
  $baixadoPor = trim((string) ($lan->lan_usu_nome_baixa ?? ''));
  if ($baixadoPor === '' && !empty($lan->lan_dtbaixa) && !empty($logs)) {
    $ultimoLog = $logs[0] ?? [];
    $baixadoPor = trim((string) ($ultimoLog['usu_razaosocial'] ?? $ultimoLog['usuario'] ?? ''));
  }
  $baixadoPor = $baixadoPor !== '' ? $baixadoPor : '—';
  $arquivos = is_array($arquivos ?? null) ? $arquivos : [];
  $collapse = 'logLancamento' . (int) ($lan->lan_id ?? 0);
  $origem = is_object($titulo) && is_object($titulo->origem_dados ?? null) ? $titulo->origem_dados : null;
  $info = static function (string $label, string $value, string $valueClass = '') use ($esc): string {
    return '<div class="phsaFakeInput"><div class="phsaFakeInputLabel">' . $esc($label) . '</div><div class="phsaFakeInputValue text-break ' . $esc($valueClass) . '">' . $value . '</div></div>';
  };
  $origemDocumento = trim((string) ($origem->documento ?? ''));
  $origemCelular = trim((string) ($origem->celular ?? ''));
  $origemEmail = trim((string) ($origem->email ?? ''));
  $origemWhatsapp = preg_replace('/D+/', '', $origemCelular);
  $origemFotoPadrao = ($origem->origem ?? '') === 'fornecedor' ? 'storage/global/semimagem.jpg' : 'storage/global/semperfil.png';
  $origemFoto = Helpers::storage($origem->logo ?? null, $origemFotoPadrao);
?>
<div id="detalhesLancamento" class="icliv-detalhes-lancamento">
  <?php if ($titulo): ?>
    <section class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
        <div><div class="small text-uppercase text-muted">Título nº <?= (int) ($titulo->tit_id ?? 0) ?></div><div class="h4 text-phsa fw-bold mb-0"><?= $esc($titulo->tit_nome ?? 'Título') ?></div></div>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge bg-<?= $esc($titulo->tit_statusclass ?? 'secondary') ?>"><i class="fa-solid fa-flag me-1"></i><?= $esc($titulo->tit_status ?? '—') ?></span>
          <span class="badge bg-<?= $esc($titulo->tit_statusfinanceiroclass ?? 'secondary') ?>"><i class="fa-solid fa-coins me-1"></i><?= $esc($titulo->tit_statusfinanceiro ?? '—') ?></span>
        </div>
      </div>
      <div class="row g-2 small">
        <div class="col-12 col-md-3"><?= $info('Plano de conta', $esc($titulo->tit_pct_nome ?? '—')) ?></div>
        <div class="col-6 col-md-2"><?= $info('Referência', $dt($titulo->tit_referencia ?? null), 'text-center') ?></div>
        <div class="col-6 col-md-2"><?= $info('Vencimento', $dt($titulo->tit_dtvencimento ?? null), 'text-center') ?></div>
        <div class="col-6 col-md-2"><?= $info('Valor total', '<strong class="text-' . ($entrada ? 'success' : 'danger') . '">' . $money($titulo->tit_valortotal ?? 0) . '</strong>', 'text-end') ?></div>
        <div class="col-6 col-md-3"><?= $info('Movimentado', '<strong class="text-phsa">' . $money($titulo->tit_valormovimentado ?? 0) . '</strong>', 'text-end') ?></div>
      </div>
      <?php if ($origem): ?>
        <div class="alert alert-primary border mb-0 mt-3">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <img src="<?= $esc($origemFoto) ?>" alt="Perfil" class="rounded-circle" width="52" height="52" style="object-fit:cover;">
            <div>
              <div class="fw-semibold text-uppercase"><?= $esc($origem->nome ?? '—') ?></div>
              <?php if (!empty($origem->razaoSocial)): ?><div class="small text-muted"><?= $esc($origem->razaoSocial) ?></div><?php endif; ?>
              <div class="small text-muted mb-1 fw-bold"><?= $esc($origem->tipo ?? 'Responsável') ?></div>
              <div class="small text-muted d-flex flex-wrap align-items-center gap-2">
                <?php if ($origemDocumento): ?><span>Documento: <?= $esc($origem->documentoMascara ?? $origemDocumento) ?></span><button type="button" class="btn btn-default btn-sm text-phsa p-1 js-copiar-vinculo" data-valor="<?= $esc($origemDocumento) ?>" data-campo="Documento" title="Copiar documento"><i class="fa-regular fa-copy"></i></button><?php endif; ?>
                <?php if ($origemDocumento && $origemCelular): ?><span class="mx-1">|</span><?php endif; ?>
                <?php if ($origemCelular): ?><span><?= $esc($origem->celularMascara ?? $origemCelular) ?></span><?php if ($origemWhatsapp): ?><a href="https://wa.me/<?= $esc($origemWhatsapp) ?>" target="_blank" rel="noopener" class="text-success p-1" title="Abrir no WhatsApp"><i class="fa-brands fa-whatsapp"></i></a><?php endif; ?><button type="button" class="btn btn-default btn-sm text-phsa p-1 js-copiar-vinculo" data-valor="<?= $esc($origemCelular) ?>" data-campo="Celular" title="Copiar celular"><i class="fa-regular fa-copy"></i></button><?php endif; ?>
                <?php if (($origemDocumento || $origemCelular) && $origemEmail): ?><span class="mx-1">|</span><?php endif; ?>
                <?php if ($origemEmail): ?><span><?= $esc($origemEmail) ?></span><button type="button" class="btn btn-default btn-sm text-phsa p-1 js-copiar-vinculo" data-valor="<?= $esc($origemEmail) ?>" data-campo="E-mail" title="Copiar e-mail"><i class="fa-regular fa-copy"></i></button><?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div></section>
  <?php endif; ?>

  <section class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
      <div class="text-uppercase text-phsa fw-bold">Lançamento nº <?= (int) ($lan->lan_id ?? 0) ?></div>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-<?= $esc($lan->lan_statusclass ?? 'secondary') ?>"><i class="fa-solid <?= $icon ?> me-1"></i><?= $esc($status) ?></span>
        <span class="badge bg-<?= $entrada ? 'success' : 'danger' ?>"><i class="fa-solid <?= $entrada ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?> me-1"></i><?= $entrada ? 'Entrada' : 'Saída' ?></span>
      </div>
    </div>
    <div class="row g-2 small">
      <div class="col-12 col-md-3"><?= $info('Centro de custo', $esc($lan->lan_cdc_nome ?? '—')) ?></div>
      <div class="col-12 col-md-3"><?= $info('Conta financeira', $esc($lan->lan_ctf_nome ?? '—')) ?></div>
      <div class="col-12 col-md-3"><?= $info('Forma / Condição', $esc(($lan->lan_fpg_nome ?? '—') . ' / ' . ($lan->lan_cpg_nome ?? '—'))) ?></div>
      <div class="col-12 col-md-3"><?= $info('Parcela', (int) ($lan->lan_parcela ?? 1) . '/' . (int) ($lan->lan_totalparcela ?? 1)) ?></div>
    </div>
    <div class="row g-2 small mt-1">
      <div class="col-12 col-md-4"><?= $info('Vencimento', $dt($lan->lan_dtvencimento ?? null), 'text-center') ?></div>
      <div class="col-12 col-md-4"><?= $info('Data baixa', $dt($lan->lan_dtbaixa ?? null), 'text-center') ?></div>
      <div class="col-12 col-md-4"><?= $info('Baixado por', $esc($baixadoPor)) ?></div>
    </div>
    <div class="row g-2 small mt-1">
      <div class="col-12 col-md-3"><?= $info('Valor informado', $money($lan->lan_valorinformado ?? 0), 'text-end') ?></div>
      <div class="col-12 col-md-3"><?= $info('Desconto', $money($lan->lan_desconto ?? 0), 'text-end') ?></div>
      <div class="col-12 col-md-3"><?= $info('Acréscimo', $money($lan->lan_acrescimo ?? 0), 'text-end') ?></div>
      <div class="col-12 col-md-3"><?= $info('Valor final', '<strong class="text-' . ($entrada ? 'success' : 'danger') . '">' . $money($lan->lan_valor ?? 0) . '</strong>', 'text-end') ?></div>
    </div>
    <?php if (trim((string) ($lan->lan_observacao ?? '')) !== ''): ?><div class="row g-2 small mt-1"><div class="col-12"><?= $info('Observação', $esc($lan->lan_observacao)) ?></div></div><?php endif; ?>
    <?php if (!empty($lan->lan_tipoacao)): ?>
      <div class="alert alert-light border-danger mt-3 mb-0">
        <div class="fw-semibold text-danger"><i class="fa-solid fa-rotate-left me-1"></i><?= $esc($lan->lan_tipoacao) ?></div>
        <div class="small mt-2"><strong>Motivo:</strong> <?= $esc($lan->lan_motivoacao ?? '—') ?></div>
        <div class="small text-muted"><strong>Data:</strong> <?= $dt($lan->lan_dtacao ?? null, 'd/m/Y H:i') ?><?php if (!empty($lan->lan_idlancamento_origem)): ?> · <strong>Origem:</strong> #<?= (int) $lan->lan_idlancamento_origem ?><?php endif; ?></div>
      </div>
    <?php endif; ?>
  </div></section>

  <section class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
      <div><div class="fw-semibold"><i class="fa-solid fa-paperclip me-1"></i>Comprovante do lançamento</div><div class="small text-muted"><?= $arquivos ? count($arquivos) . ' arquivo(s) anexado(s).' : 'Nenhum comprovante anexado a este lançamento.' ?></div></div>
      <?php if (!$anexoSomenteVisualizacao): ?>
        <label class="bto bto-phsa mb-0" for="arquivoLancamentoInput"><i class="fa-solid fa-paperclip"></i>&nbsp;Anexar arquivo</label>
        <input type="file" id="arquivoLancamentoInput" data-id="<?= (int) ($lan->lan_id ?? 0) ?>" class="d-none" accept=".pdf,.txt,.doc,.docx,.xls,.xlsx,.json,.xml,.jpg,.jpeg,.png,.webp">
      <?php endif; ?>
    </div>
    <?php if ($arquivos): ?><div class="d-flex flex-column gap-2 mt-3"><?php foreach ($arquivos as $arquivo): ?><div class="d-flex align-items-center justify-content-between gap-2 border rounded p-2 small"><span><i class="fa-solid fa-file me-1"></i><?= $esc($arquivo->arq_nomeoriginal ?? $arquivo->arq_nome ?? 'Arquivo') ?></span><a class="bto bto-phsa-light btn-sm" href="<?= $esc(Helpers::storage($arquivo->arq_caminho ?? '')) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i>&nbsp;Visualizar</a></div><?php endforeach; ?></div><?php endif; ?>
  </div></section>

  <section class="card border-phsa-light box-phsa-shadow-light mb-3"><div class="card-body">
    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
      <div><div class="small text-uppercase text-muted">Log do lançamento</div><div class="small text-muted">Criado em <?= $dt($lan->lan_dtcad ?? null, 'd/m/Y H:i') ?> · Alterado em <?= $dt($lan->lan_dtalter ?? null, 'd/m/Y H:i') ?></div></div>
      <?php if ($logs): ?><button type="button" class="bto bto-phsa-light btn-sm" data-bs-toggle="collapse" data-bs-target="#<?= $collapse ?>"><i class="fa-solid fa-code"></i>&nbsp;Ver log</button><?php endif; ?>
    </div>
    <?php if ($logs): ?><div class="collapse mt-3" id="<?= $collapse ?>"><div class="d-flex flex-column gap-2"><?php foreach (array_reverse($logs) as $log): ?><div class="border rounded p-2 small bg-light"><div class="d-flex justify-content-between gap-2"><span class="fw-semibold"><?= $esc($log['acao'] ?? $log['text'] ?? 'Alteração') ?></span><span class="text-muted"><?= $esc($log['data'] ?? $log['date'] ?? '—') ?></span></div><div class="text-muted"><?= $esc($log['usu_razaosocial'] ?? $log['usuario'] ?? 'Sistema') ?></div></div><?php endforeach; ?></div></div><?php endif; ?>
  </div></section>

  <div id="footerDetalhesLancamento" class="d-flex align-items-center justify-content-between gap-2 flex-wrap hideOnPrint">
    <button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</button>
    <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
      <button type="button" class="bto bto-phsa-light js-imprimir-detalhes-lancamento"><i class="fa-solid fa-print"></i>&nbsp;Imprimir</button>
      <?php if ($podeCorrigir ?? false): ?><button type="button" class="bto bto-phsa-dark js-corrigir-lancamento" data-id="<?= (int) $lan->lan_id ?>"><i class="fa-solid fa-pen-to-square"></i>&nbsp;Corrigir</button><?php endif; ?>
      <?php if ($podeEstornar ?? false): ?><button type="button" class="bto text-danger js-estornar-lancamento" data-id="<?= (int) $lan->lan_id ?>"><i class="fa-solid fa-rotate-left"></i>&nbsp;Estornar</button><?php endif; ?>
      <?php if ($podeBaixar ?? false): ?><button type="button" class="bto bto-phsa js-baixar-lancamento" data-id="<?= (int) $lan->lan_id ?>" data-status="<?= $esc($status) ?>"><i class="fa-solid <?= $status === 'A receber' ? 'fa-hand-holding-dollar' : 'fa-money-bill-wave' ?>"></i>&nbsp;<?= $status === 'A receber' ? 'Receber' : 'Pagar' ?></button><?php endif; ?>
    </div>
  </div>
</div>
