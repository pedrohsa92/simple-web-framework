<?php
  $plano = $plano ?? ($contratacao ?? (isset($getPlanoVigente->data[0]) ? $getPlanoVigente->data[0] : null));
  $abaPrefix = $abaPrefix ?? 'planoOrganizacao';
  $mostrarPagamento = $mostrarPagamento ?? false;
  $podeGerarNovoLink = $podeGerarNovoLink ?? false;
  $esc = $esc ?? static fn($valor) => htmlspecialchars((string) ($valor ?? '-'), ENT_QUOTES, 'UTF-8');
  $formatarData = $formatarData ?? static fn($valor) => empty($valor) ? '-' : date('d/m/Y H:i', strtotime((string) $valor));
  $formatarValor = $formatarValor ?? static fn($valor) => 'R$ ' . number_format((float) ($valor ?? 0), 2, ',', '.');
  $statusClass = $statusClass ?? static function ($status): string {
    return match ((string) $status) {
      'Ativo', 'Pago', 'Pago Manual', 'Teste' => 'success',
      'Agendado' => 'info',
      'Pendente', 'Em aberto' => 'warning',
      'Falhou', 'Expirado', 'Cancelado' => 'danger',
      default => 'secondary'
    };
  };

  $json = static function ($valor): string {
    $texto = trim((string) ($valor ?? ''));
    $decodificado = json_decode($texto, true);
    return is_array($decodificado)
      ? (string) json_encode($decodificado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      : ($texto !== '' ? $texto : '-');
  };
  $logs = json_decode((string) ($plano->plc_log ?? '[]'), true);
  $logs = is_array($logs) ? array_reverse($logs) : [];
  $valorLog = static fn($log, string $chave, string $padrao = '-') => is_array($log) ? ($log[$chave] ?? $padrao) : $padrao;
  $requestPagarme = $json($plano->plc_pagarme_request ?? '');
  $responsePagarme = $json($plano->plc_pagarme_response ?? '');
  $urlPagarme = trim((string) ($plano->plc_pagarme_url ?? ''));
?>

<style>
  .lh-plano-detalhes .phsaTabItem { padding: 1rem; }
  .lh-plano-detalhes .jsonPlanoBox { max-height: 320px; overflow: auto; white-space: pre-wrap; background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color); border-radius: .65rem; padding: .85rem; }
</style>

<?php if (!$plano): ?>
  <div class="alert alert-warning mb-0"><i class="fa-solid fa-circle-info me-1"></i>Nenhum plano vigente encontrado para esta organização.</div>
<?php else: ?>
  <div class="lh-plano-detalhes">
    <header class="phsaScrollHeader">
      <nav class="phsaScrollNav" data-menu="<?= $esc($abaPrefix) ?>">
        <ul class="phsaScrollList">
          <li class="phsaScrollItem active" data-target="<?= $esc($abaPrefix) ?>Detalhes"><i class="fa-solid fa-file-contract"></i>&nbsp;Detalhes</li>
          <li class="phsaScrollItem" data-target="<?= $esc($abaPrefix) ?>Financeiro"><i class="fa-solid fa-credit-card"></i>&nbsp;Financeiro</li>
          <li class="phsaScrollItem" data-target="<?= $esc($abaPrefix) ?>Historico"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li>
        </ul>
      </nav>
    </header>

    <div class="phsaTabContent phsaTabSubContent">
      <div class="phsaTabItem active" id="<?= $esc($abaPrefix) ?>Detalhes">
        <section>
          <h6 class="text-phsa mb-3"><i class="fa-solid fa-list-check me-1"></i><?= $mostrarPagamento ? 'Resumo da contratação' : 'Resumo do plano vigente' ?></h6>
          <div class="row g-3">
            <?php foreach ([
              'Plano' => $plano->pla_nome ?? '-',
              'Tipo' => $plano->pla_tipo ?? '-',
              'Periodicidade' => $plano->plc_ciclo ?? '-',
              'Ambiente' => $plano->plc_ambiente ?? '-',
              'Status' => $plano->plc_status ?? '-',
              'Status financeiro' => $plano->plc_statusfinanceiro ?? '-',
              'Valor contratado' => $formatarValor($plano->plc_valor ?? 0),
              'Cadastro' => $formatarData($plano->plc_dtcad ?? null),
              'Início' => $formatarData($plano->plc_dtinicio ?? null),
              'Fim' => $formatarData($plano->plc_dtfim ?? null),
              'Vencimento' => $formatarData($plano->plc_dtvencimento ?? null),
              'Pagamento' => $formatarData($plano->plc_dtpagamento ?? null)
            ] as $rotulo => $valor): ?>
              <div class="col-12 col-sm-6 col-xl-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel"><?= $esc($rotulo) ?></div><div class="phsaFakeInputValue"><?php if (in_array($rotulo, ['Status', 'Status financeiro'], true)): ?><span class="badge bg-<?= $esc($statusClass($valor)) ?>"><?= $esc($valor) ?></span><?php else: ?><?= $esc($valor) ?><?php endif; ?></div></div></div>
            <?php endforeach; ?>
            <?php if (!empty($plano->plc_idcontratacaoanterior)): ?><div class="col-12 col-sm-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Contratação anterior</div><div class="phsaFakeInputValue"><a class="text-phsa text-decoration-none" href="<?= Helpers::url('organizacao/plano/' . (int) $plano->plc_idcontratacaoanterior) ?>">#<?= (int) $plano->plc_idcontratacaoanterior ?></a></div></div></div><?php endif; ?>
            <div class="col-12"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Sobre o plano</div><div class="phsaFakeInputValue text-break"><?= $esc($plano->pla_descricaoresumida ?? '-') ?></div></div></div>
          </div>
        </section>
      </div>

      <div class="phsaTabItem" id="<?= $esc($abaPrefix) ?>Financeiro">
        <section>
          <h6 class="text-phsa mb-3"><i class="fa-solid fa-money-check-dollar me-1"></i>Informações financeiras</h6>
          <div class="row g-3">
            <?php foreach ([
              'Status financeiro' => $plano->plc_statusfinanceiro ?? '-',
              'ID Pagar.me' => $plano->plc_pagarme_id ?? '-',
              'Tipo Pagar.me' => $plano->plc_pagarme_tipo ?? '-',
              'Status Pagar.me' => $plano->plc_pagarme_status ?? '-',
              'Último evento' => $plano->plc_pagarme_ultimoevento ?? '-',
              'Última sincronização' => $formatarData($plano->plc_pagarme_dtultimasync ?? null)
            ] as $rotulo => $valor): ?>
              <div class="col-12 col-md-6 col-xl-4"><div class="phsaFakeInput"><div class="phsaFakeInputLabel"><?= $esc($rotulo) ?></div><div class="phsaFakeInputValue"><?= $rotulo === 'Status financeiro' ? '<span class="badge bg-' . $esc($statusClass($valor)) . '">' . $esc($valor) . '</span>' : $esc($valor) ?></div></div></div>
            <?php endforeach; ?>
            <div class="col-12"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">URL de pagamento</div><div class="phsaFakeInputValue text-break"><?php if ($urlPagarme !== '' && filter_var($urlPagarme, FILTER_VALIDATE_URL)): ?><a href="<?= $esc($urlPagarme) ?>" target="_blank" rel="noopener noreferrer" class="text-phsa text-decoration-none"><?= $esc($urlPagarme) ?><i class="fa-solid fa-arrow-up-right-from-square float-end mt-1" title="Abrir em nova aba"></i></a><?php else: ?><?= $esc($urlPagarme !== '' ? $urlPagarme : 'Nenhum link gerado') ?><?php endif; ?></div></div></div>
          </div>
          <?php if ($mostrarPagamento && ($plano->pla_tipo ?? '') === 'Comercial' && !in_array($plano->plc_statusfinanceiro ?? '', ['Pago', 'Pago Manual', 'Isento'], true) && $podeGerarNovoLink): ?><button type="button" class="bto bto-phsa mt-3" id="btnGerarPagamentoOrganizacao" data-id="<?= (int) $plano->plc_id ?>"><i class="fa-solid fa-rotate me-1"></i>Gerar novo link de pagamento</button><?php endif; ?>
          <?php if ($mostrarPagamento && (!empty($plano->plc_pagarme_request) || !empty($plano->plc_pagarme_response))): ?>
            <div class="row g-3 mt-1">
              <?php if (!empty($plano->plc_pagarme_request)): ?><div class="col-12 col-lg-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Request</div><div class="phsaFakeInputValue"><pre class="jsonPlanoBox mb-0 small"><?= $esc($requestPagarme) ?></pre></div></div></div><?php endif; ?>
              <?php if (!empty($plano->plc_pagarme_response)): ?><div class="col-12 col-lg-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Response</div><div class="phsaFakeInputValue"><pre class="jsonPlanoBox mb-0 small"><?= $esc($responsePagarme) ?></pre></div></div></div><?php endif; ?>
            </div>
          <?php endif; ?>
        </section>
      </div>

      <div class="phsaTabItem" id="<?= $esc($abaPrefix) ?>Historico">
        <section>
          <h5 class="lh-plano-secao-titulo"><i class="fa-solid fa-clock-rotate-left me-1"></i>Histórico</h5>
          <div class="row g-3 mb-4"><div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Cadastrado em</div><div class="phsaFakeInputValue"><?= $esc($formatarData($plano->plc_dtcad ?? null)) ?></div></div></div><div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Última alteração</div><div class="phsaFakeInputValue"><?= $esc($formatarData($plano->plc_dtalter ?? null)) ?></div></div></div></div>
          <?php if (!$logs): ?><p class="text-muted mb-0">Nenhum histórico registrado para esta contratação.</p><?php else: ?><div class="table-responsive"><table class="table table-hover align-middle small mb-0"><thead><tr><th>Usuário</th><th>Ação</th><th>Data</th><th>Detalhes</th></tr></thead><tbody><?php foreach ($logs as $log): ?><tr><td><?= $esc($valorLog($log, 'usu_razaosocial', 'Sistema')) ?></td><td><?= $esc($valorLog($log, 'acao', 'Evento')) ?></td><td><?= $esc($valorLog($log, 'data', $valorLog($log, 'criado_em', '-'))) ?></td><td><?= $esc(is_array($log) && isset($log['detalhe']) ? (is_array($log['detalhe']) ? implode(' | ', $log['detalhe']) : $log['detalhe']) : '-') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>
      </div>
    </div>
  </div>
<?php endif; ?>
