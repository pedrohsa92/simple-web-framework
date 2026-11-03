<?php
  $historicoRegistro = $historicoRegistro ?? (object) [];
  $historicoPrefixo = (string) ($historicoPrefixo ?? '');
  $historicoId = (int) ($historicoId ?? 0);
  $historicoStatus = (string) ($historicoStatus ?? '');
  $historicoTemStatus = trim($historicoStatus) !== '';
  $historicoStatusClass = (string) ($historicoStatusClass ?? 'secondary');
  $historicoChave = trim((string) ($historicoChave ?? ''));
  $historicoLogs = $historicoLogs ?? [];
  if (is_string($historicoLogs)) $historicoLogs = json_decode($historicoLogs, true);
  if (!is_array($historicoLogs)) $historicoLogs = [];
  $historicoEsc = static fn($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
  $historicoData = static function ($valor) use ($historicoEsc): string {
    if (empty($valor)) return '—';
    $timestamp = strtotime((string) $valor);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $historicoEsc($valor);
  };
  $historicoStatusClass = preg_match('/^[a-z0-9_-]+$/i', $historicoStatusClass) ? $historicoStatusClass : 'secondary';
  $historicoChaveJs = json_encode($historicoChave, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<div class="row g-3">
  <div class="col-12">
    <section class="card border-phsa-light box-phsa-shadow-light">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-id-card"></i>&nbsp;Identificação</h6></div>
        <div class="row g-3">
          <div class="col-12 col-sm-<?= $historicoChave !== '' && $historicoTemStatus ? '3' : ($historicoChave !== '' || $historicoTemStatus ? '6' : '12') ?>"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">ID</div><div class="phsaFakeInputValue"><?= $historicoId > 0 ? $historicoId : '—' ?></div></div></div>
          <?php if ($historicoChave !== ''): ?><div class="col-12 col-sm-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Chave</div><div onclick="fgbCopiarParaAreaDeTransferencia(<?= $historicoChaveJs ?>, 'chave')" class="phsaFakeInputValue text-phsa text-break" title="Clique para copiar a chave" role="button" tabindex="0"><?= $historicoEsc($historicoChave) ?></div></div></div><?php endif; ?>
          <?php if ($historicoTemStatus): ?><div class="col-12 col-sm-<?= $historicoChave !== '' ? '3' : '6' ?>"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Status</div><div class="phsaFakeInputValue"><span class="badge bg-<?= $historicoEsc($historicoStatusClass) ?>"><?= $historicoEsc($historicoStatus) ?></span></div></div></div><?php endif; ?>
        </div>
      </div>
    </section>
  </div>
  <div class="col-12">
    <section class="card border-phsa-light box-phsa-shadow-light">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Log de alterações</h6><span class="small text-muted"><?= count($historicoLogs) ?> alteração(ões)</span></div>
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Cadastrado em</div><div class="phsaFakeInputValue w-100 text-center"><?= $historicoData($historicoRegistro->{$historicoPrefixo . 'dtcad'} ?? null) ?></div></div></div>
          <div class="col-12 col-sm-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Última alteração</div><div class="phsaFakeInputValue w-100 text-center"><?= $historicoData($historicoRegistro->{$historicoPrefixo . 'dtalter'} ?? null) ?></div></div></div>
        </div>
        <?php if (!$historicoLogs): ?>
          <div class="alert alert-light border mb-0 small">Nenhum histórico registrado para este cadastro.</div>
        <?php else: ?>
          <div class="table-responsive"><table class="table table-hover align-middle mb-0 small"><thead><tr><th>Usuário</th><th>Ação</th><th>Data</th><th>Detalhes</th></tr></thead><tbody>
            <?php foreach (array_reverse($historicoLogs) as $log): $log = (array) $log; $usuarioId = (int) ($log['usu_id'] ?? 0); $nome = (string) ($log['usu_razaosocial'] ?? $log['usu_nome'] ?? $log['usuario'] ?? 'Sistema'); $detalhes = $log['detalhe'] ?? null; if ($detalhes === null && !empty($log['text'])) $detalhes = $log['text']; $detalhes = is_array($detalhes) ? $detalhes : (is_scalar($detalhes) && trim((string) $detalhes) !== '' ? [$detalhes] : []); ?>
              <tr><td><?php if ($usuarioId > 0 && Helpers::pode('usuario.visualizar')): ?><a class="text-phsa text-decoration-none" href="<?= Helpers::url('usuario/form/' . $usuarioId) ?>" target="_blank" rel="noopener"><?= $historicoEsc($nome) ?></a><?php else: ?><?= $historicoEsc($nome) ?><?php endif; ?></td><td><?= $historicoEsc($log['acao'] ?? (!empty($log['text']) ? 'Alteração registrada' : '—')) ?></td><td class="text-nowrap"><?= $historicoData($log['data'] ?? $log['date'] ?? null) ?></td><td><?php if ($detalhes): ?><ul class="mb-0 ps-3"><?php foreach ($detalhes as $detalhe): ?><li><?= $historicoEsc(is_scalar($detalhe) ? $detalhe : json_encode($detalhe, JSON_UNESCAPED_UNICODE)) ?></li><?php endforeach; ?></ul><?php else: ?><span class="text-muted">-</span><?php endif; ?></td></tr>
            <?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>
