<section id="resultadoRelatorio" class="mt-4">
  <?php $limiteResultado = ($filtros->limit ?? '') === 'sem_limite' ? 'Sem limite' : number_format((int) ($filtros->limit ?? 0), 0, ',', '.') . ' registros'; ?>
  <?php $dataHora = static function ($valor): string { $valor = trim((string) ($valor ?? '')); return $valor !== '' && strtotime($valor) ? date('d/m/Y H:i', strtotime($valor)) : '—'; }; ?>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 small text-phsa text-uppercase"><span><?= number_format((int) ($resumo->quantidadeExibida ?? 0), 0, ',', '.') ?> lançamento(s) encontrado(s)</span><span>Campo de data: <?= $e($campoData[$filtros->campoData ?? 'lan_dtvencimento'] ?? 'Vencimento do lançamento') ?> | Ordenação: <?= $e($ordenacaoAplicada[0] ?? 'Ordem padrão') ?> | Limite: <?= $e($limiteResultado) ?></span></div>
  <div class="table-responsive"><table id="tableRelatorio" class="table table-sm table-striped table-hover align-middle small relatorioAnaliticoTabela"><thead><tr><th>ID</th><th>Título</th><th>Descrição</th><th>Conta</th><th>Centro de custo</th><th>Plano de conta</th><th>Forma</th><th class="text-center">Natureza</th><th class="text-center">Status</th><th class="text-center">Vencimento</th><th class="text-center">Baixa</th><th class="text-end">Valor informado</th><th class="text-end">Desconto</th><th class="text-end">Acréscimo</th><th class="text-end">Valor final</th></tr></thead><tbody>
    <?php if (!$linhas): ?><tr><td colspan="100" class="text-center text-muted py-4"><i class="fa-solid fa-circle-info"></i>&nbsp;Nenhum lançamento encontrado para os filtros informados.</td></tr><?php endif; ?>
    <?php foreach ($linhas as $linha): ?><?php
      $entrada = ($linha->lan_natureza ?? '') === 'Entrada';
      $tituloId = (int) ($linha->lan_idtitulo ?? 0);
      $tituloNome = trim((string) ($linha->tit_nome ?? '')) ?: '—';
      $forma = trim((string) ($linha->lan_fpg_nome ?? '')) ?: '—';
      $condicao = trim((string) ($linha->lan_cpg_nome ?? ''));
      $parcela = max(1, (int) ($linha->lan_parcela ?? 1));
      $totalParcela = max(1, (int) ($linha->lan_totalparcela ?? 1));
    ?><tr>
        <td class="text-phsa fw-bold"><?= (int) ($linha->lan_id ?? 0) ?></td>
      <td><?php if ($tituloId > 0): ?><a href="<?= Helpers::url('titulo/form/' . $tituloId) ?>" class="text-phsa fw-semibold text-decoration-none">#<?= $tituloId ?> - <?= $e($tituloNome) ?></a><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
      <td><div class="fw-semibold"><?= $e($linha->lan_descricao ?? '—') ?></div><?php if (!empty($linha->lan_observacao)): ?><div class="small text-muted"><?= $e($linha->lan_observacao) ?></div><?php endif; ?></td>
      <td><?= $e($linha->lan_ctf_nome ?? '—') ?></td>
      <td><?= $e($linha->lan_cdc_nome ?? '—') ?></td>
      <td><?= $e($linha->lan_pct_nome ?? '—') ?></td>
      <td><div><?= $e($forma) ?></div><?php if ($condicao !== ''): ?><div class="small text-muted"><?= $e($condicao) ?></div><?php endif; ?><div class="small">Parcela <?= $parcela ?>/<?= $totalParcela ?></div></td>
      <td class="text-center text-<?= $entrada ? 'success' : 'danger' ?>"><span class="fw-semibold text-nowrap"><i class="fa-solid <?= $entrada ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?> me-1"></i><?= $entrada ? 'Entrada' : 'Saída' ?></span></td>
      <td class="text-center"><span class="badge bg-<?= $e($linha->lan_statusclass ?? 'secondary') ?>"><?= $e($linha->lan_status ?? '—') ?></span></td>
      <td class="text-center text-nowrap"><?= $data($linha->lan_dtvencimento ?? null) ?></td>
      <td class="text-center text-nowrap"><?= $dataHora($linha->lan_dtbaixa ?? null) ?></td>
        <td class="text-end text-nowrap"><?= $moeda($linha->lan_valorinformado ?? 0) ?></td>
        <td class="text-end text-nowrap"><?= $moeda($linha->lan_desconto ?? 0) ?></td>
        <td class="text-end text-nowrap"><?= $moeda($linha->lan_acrescimo ?? 0) ?></td>
        <td class="text-end text-nowrap fw-bold text-<?= $entrada ? 'success' : 'danger' ?>"><?= $moeda($linha->lan_valor ?? 0) ?></td>
    </tr><?php endforeach; ?>
  </tbody></table></div>
</section>

<?php $this->push('style'); ?><style>
  .relatorioAnaliticoTabela th { white-space: nowrap; }
  .relatorioAnaliticoTabela td { vertical-align: middle; }
  .relatorioAnaliticoTabela td:nth-child(2) { min-width: 175px; max-width: 240px; }
  .relatorioAnaliticoTabela td:nth-child(3) { min-width: 150px; max-width: 230px; }
  .relatorioAnaliticoTabela td:nth-child(4) { min-width: 130px; max-width: 180px; }
</style><?php $this->end(); ?>
