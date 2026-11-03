<?php $this->layout('sistema::sistema'); ?>

<?php if ($contratacao === null): ?>
  <?php $contratacoesData = ($contratacoes && $contratacoes->status) ? ($contratacoes->data ?? []) : []; ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div><h4 class="m-0">Contratações</h4><div class="text-muted small mt-1">Histórico de testes, contratações e renovações por organização.</div></div>
        <span class="badge bg-phsa-dark"><?= count($contratacoesData) ?> registro<?= count($contratacoesData) === 1 ? '' : 's' ?></span>
      </div>

      <div class="row mb-3"><div class="col-12 mb-3"><div class="form-floating"><input id="pesquisaContratacao" class="form-control input-phsa-focus input-phsa-hover" placeholder="Pesquisar"><label for="pesquisaContratacao">Pesquisar por organização, plano, status ou ID</label></div></div></div>

      <div class="table-responsive">
        <table class="table table-hover align-middle" id="tabelaContratacoes">
          <thead><tr><th>Contrato</th><th>Plano</th><th>Período</th><th>Status</th><th>Financeiro</th><th>Valor</th><th>Ação</th></tr></thead>
          <tbody>
            <?php foreach ($contratacoesData as $item): ?>
              <tr class="linhaContratacao" data-busca="<?= htmlspecialchars(mb_strtolower(implode(' ', [$item->plc_id ?? '', $item->org_razaosocial ?? '', $item->pla_nome ?? '', $item->plc_status ?? '', $item->plc_statusfinanceiro ?? ''])), ENT_QUOTES, 'UTF-8') ?>">
                <td><a href="<?= Helpers::url('projeto/contratacao/' . (int) $item->plc_id) ?>" class="text-phsa text-decoration-none" title="Contrato #<?= (int) $item->plc_id ?>"><?= htmlspecialchars($item->org_razaosocial ?? '-', ENT_QUOTES, 'UTF-8') ?></a><div class="small text-muted"><?= (int) $item->plc_id ?> <span aria-hidden="true">•</span> <?= htmlspecialchars($item->plc_grupo_contratacao ?? '-', ENT_QUOTES, 'UTF-8') ?></div></td>
                <td><div class="fw-semibold"><?= htmlspecialchars($item->pla_nome ?? '-', ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted"><?= htmlspecialchars($item->plc_ciclo ?? '-', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($item->plc_ambiente ?? '-', ENT_QUOTES, 'UTF-8') ?></div></td>
                <td><div><?= empty($item->plc_dtinicio) ? '-' : date('d/m/Y', strtotime($item->plc_dtinicio)) ?></div><div class="small text-muted">até <?= empty($item->plc_dtfim) ? '-' : date('d/m/Y', strtotime($item->plc_dtfim)) ?></div></td>
                <td><span class="badge bg-<?= htmlspecialchars($item->plc_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item->plc_status ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($item->plc_statusfinanceiro ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>R$ <?= number_format((float) ($item->plc_valor ?? 0), 2, ',', '.') ?></td>
                <td class="text-end"><a href="<?= Helpers::url('projeto/contratacao/' . (int) $item->plc_id) ?>" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Visualizar contratação"><i class="fa-solid fa-eye"></i></a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($contratacoesData)): ?><tr><td colspan="7" class="text-center text-muted py-4">Nenhuma contratação encontrada.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php $this->push('script'); ?><script>$(function () { $('#pesquisaContratacao').on('input', function () { const termo = String($(this).val() || '').toLowerCase().trim(); $('.linhaContratacao').each(function () { $(this).toggle(termo === '' || String($(this).data('busca') || '').includes(termo)); }); }); });</script><?php $this->end(); ?>
<?php else: ?>
  <?php
    $logsContratacao = json_decode($contratacao->plc_log ?? '[]');
    if (!is_array($logsContratacao)) $logsContratacao = [];
    $esc = static fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    $json = static function ($value): string {
      $texto = trim((string) ($value ?? ''));
      $decodificado = json_decode($texto, true);
      return is_array($decodificado)
        ? (string) json_encode($decodificado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : $texto;
    };
    $pagarmeRequest = $json($contratacao->plc_pagarme_request ?? '');
    $pagarmeResponse = $json($contratacao->plc_pagarme_response ?? '');
    $recursosContratacao = json_decode((string) ($contratacao->plc_parametro ?? ''), true);
    $recursosContratacao = is_array($recursosContratacao) ? $recursosContratacao : [];
    $grupoRecurso = static fn($chave) => is_array($recursosContratacao[$chave] ?? null) ? $recursosContratacao[$chave] : [];
    $statusRecurso = static fn(array $grupo) => array_key_exists('status', $grupo) ? (bool) $grupo['status'] : true;
    $limiteRecurso = static function (array $grupo, string $chave): string {
      $valor = max(0, (int) ($grupo[$chave] ?? 0));
      return $valor === 0 ? 'Ilimitado' : number_format($valor, 0, ',', '.');
    };
  ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div><h4 class="m-0">Contratação #<?= (int) $contratacao->plc_id ?></h4><div class="text-muted small mt-1"><?= htmlspecialchars($contratacao->org_razaosocial ?? '-', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($contratacao->pla_nome ?? '-', ENT_QUOTES, 'UTF-8') ?></div></div>
        <a href="<?= Helpers::url('projeto/contratacao') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar para contratações</a>
      </div>

      <header class="phsaScrollHeader"><nav class="phsaScrollNav" data-menu="tabContratacao"><ul class="phsaScrollList"><li class="phsaScrollItem active" data-target="contratado"><i class="fa-solid fa-file-contract"></i>&nbsp;Contratado</li><li class="phsaScrollItem" data-target="pagamentoContratacao"><i class="fa-solid fa-credit-card"></i>&nbsp;Pagamento</li><li class="phsaScrollItem" data-target="historicoContratacao"><i class="fa-solid fa-clock"></i>&nbsp;Histórico</li></ul></nav></header>

      <div class="phsaTabContent phsaTabSubContent">
        <div class="phsaTabItem active" id="contratado">
          <header class="phsaScrollHeader mb-3"><nav class="phsaScrollNav" data-menu="subtabContratacao"><ul class="phsaScrollList"><li class="phsaScrollItem active" data-target="detalhesContratacao"><i class="fa-solid fa-circle-info"></i>&nbsp;Detalhes</li><li class="phsaScrollItem" data-target="recursosContratacao"><i class="fa-solid fa-sliders"></i>&nbsp;Recursos</li></ul></nav></header>

          <div class="phsaTabContent phsaTabSubContent">
            <div class="phsaTabItem p-3 active" id="detalhesContratacao">
              <div class="row">
                <?php foreach ([
                  'Organização' => $contratacao->org_razaosocial ?? '-',
                  'Plano' => $contratacao->pla_nome ?? '-',
                  'Grupo' => $contratacao->plc_grupo_contratacao ?? '-',
                  'Ciclo' => $contratacao->plc_ciclo ?? '-',
                  'Ambiente' => $contratacao->plc_ambiente ?? '-',
                  'Status' => $contratacao->plc_status ?? '-',
                  'Status financeiro' => $contratacao->plc_statusfinanceiro ?? '-',
                  'Origem' => $contratacao->plc_origem ?? '-',
                  'Valor' => 'R$ ' . number_format((float) ($contratacao->plc_valor ?? 0), 2, ',', '.'),
                  'Início' => empty($contratacao->plc_dtinicio) ? '-' : date('d/m/Y H:i', strtotime($contratacao->plc_dtinicio)),
                  'Fim' => empty($contratacao->plc_dtfim) ? '-' : date('d/m/Y H:i', strtotime($contratacao->plc_dtfim)),
                  'Vencimento' => empty($contratacao->plc_dtvencimento) ? '-' : date('d/m/Y H:i', strtotime($contratacao->plc_dtvencimento)),
                  'Pagamento' => empty($contratacao->plc_dtpagamento) ? '-' : date('d/m/Y H:i', strtotime($contratacao->plc_dtpagamento))
                ] as $label => $value): ?>
                  <div class="col-12 col-md-6 col-xl-4 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel"><?= $label ?></div><div class="phsaFakeInputValue"><?= $esc($value) ?></div></div></div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="phsaTabItem" id="recursosContratacao">
              <div class="p-3">
                <div class="alert alert-info small"><i class="fa-solid fa-circle-info me-2"></i>Recursos e limites registrados no momento da contratação. O valor <strong>0</strong> foi apresentado como <strong>Ilimitado</strong>.</div>
                <?php if (empty($recursosContratacao)): ?>
                  <div class="alert alert-light border mb-0">Nenhuma configuração de recursos foi registrada nesta contratação.</div>
                <?php else: ?>
                  <div class="accordion" id="accordionRecursosContratacao">
                    <?php
                      $recursos = [
                        'usuario' => ['titulo' => 'Usuários', 'icone' => 'fa-users', 'campos' => ['limite' => 'Total de usuários', 'arquivo.limite' => 'Arquivos por usuário', 'contato.limite' => 'Contatos por usuário', 'endereco.limite' => 'Endereços por usuário']],
                        'empresa' => ['titulo' => 'Empresas e igrejas', 'icone' => 'fa-building', 'campos' => ['limite' => 'Total de empresas/igrejas', 'arquivo.limite' => 'Arquivos por empresa/igreja', 'contato.limite' => 'Contatos por empresa/igreja', 'endereco.limite' => 'Endereços por empresa/igreja']],
                        'membro' => ['titulo' => 'Membros', 'icone' => 'fa-user-group', 'campos' => ['limite' => 'Total de membros', 'contato.limite' => 'Contatos por membro', 'endereco.limite' => 'Endereços por membro']],
                        'evento' => ['titulo' => 'Eventos', 'icone' => 'fa-calendar-days', 'campos' => ['periodo_futuro.limite' => 'Período futuro (dias)', 'mes.limite' => 'Eventos por mês', 'tipo.limite' => 'Tipos de evento', 'local.limite' => 'Locais de evento']],
                        'nivelacesso' => ['titulo' => 'Níveis de acesso', 'icone' => 'fa-user-lock', 'campos' => ['limite' => 'Total de níveis de acesso']],
                        'storage' => ['titulo' => 'Armazenamento', 'icone' => 'fa-hard-drive', 'campos' => ['limiteMb' => 'Limite de armazenamento (MB)']]
                      ];
                    ?>
                    <?php foreach ($recursos as $chave => $config): $grupo = $grupoRecurso($chave); $collapseId = 'collapseRecurso' . ucfirst($chave); $headingId = 'headingRecurso' . ucfirst($chave); ?>
                      <div class="accordion-item mb-2 border rounded overflow-hidden">
                        <h2 class="accordion-header" id="<?= $headingId ?>"><button class="accordion-button <?= $statusRecurso($grupo) ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $statusRecurso($grupo) ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>"><i class="fa-solid <?= $config['icone'] ?> text-phsa me-2"></i><?= $config['titulo'] ?><span class="badge bg-<?= $statusRecurso($grupo) ? 'success' : 'secondary' ?> ms-2"><?= $statusRecurso($grupo) ? 'Ativo' : 'Inativo' ?></span></button></h2>
                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $statusRecurso($grupo) ? 'show' : '' ?>" aria-labelledby="<?= $headingId ?>"><div class="accordion-body"><div class="row">
                          <?php foreach ($config['campos'] as $campo => $label): $partes = explode('.', $campo); $valorGrupo = $grupo; foreach ($partes as $parte) { $valorGrupo = is_array($valorGrupo[$parte] ?? null) ? $valorGrupo[$parte] : ($valorGrupo[$parte] ?? 0); } ?>
                            <div class="col-12 col-md-6 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel"><?= $label ?></div><div class="phsaFakeInputValue"><?= $esc($limiteRecurso(is_array($valorGrupo) ? $valorGrupo : [$partes[count($partes) - 1] => $valorGrupo], $partes[count($partes) - 1])) ?></div></div></div>
                          <?php endforeach; ?>
                        </div></div></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="phsaTabItem" id="pagamentoContratacao">
          <section class="p-3">
            <h5 class="text-phsa">Integração Pagar.me</h5>
            <div class="row">
              <?php foreach ([
                'ID externo' => $contratacao->plc_pagarme_id ?? '-',
                'Tipo' => $contratacao->plc_pagarme_tipo ?? '-',
                'Status Pagar.me' => $contratacao->plc_pagarme_status ?? '-',
                'Último evento' => $contratacao->plc_pagarme_ultimoevento ?? '-',
                'Última sincronização' => empty($contratacao->plc_pagarme_dtultimasync) ? '-' : date('d/m/Y H:i', strtotime($contratacao->plc_pagarme_dtultimasync)),
                'URL' => $contratacao->plc_pagarme_url ?? '-'
              ] as $label => $value): ?>
                <div class="col-12 col-md-6 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel"><?= $label ?></div><div class="phsaFakeInputValue text-break"><?php if ($label === 'URL' && filter_var((string) $value, FILTER_VALIDATE_URL)): ?><a href="<?= $esc($value) ?>" target="_blank" rel="noopener noreferrer" class="text-phsa text-decoration-none"><?= $esc($value) ?><i class="fa-solid fa-arrow-up-right-from-square float-end mt-1" title="Abrir em nova aba"></i></a><?php else: ?><?= $esc($value) ?><?php endif; ?></div></div></div>
              <?php endforeach; ?>
            </div>

            <?php if (!empty($contratacao->plc_pagarme_request) || !empty($contratacao->plc_pagarme_response)): ?>
              <div class="row">
                <div class="col-12 col-lg-6 mb-3"><div class="card border-phsa-light box-phsa-shadow-light h-100"><div class="card-body"><div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-arrow-up-right-from-square"></i>&nbsp;Request</h6><button type="button" class="bto btn-sm bto-phsa-light btnCopiarJsonContratacao" data-copiar-target="contratacaoRequestJson" data-copiar-label="Request do Pagar.me" title="Copiar request"><i class="fa-solid fa-copy"></i></button></div><pre id="contratacaoRequestJson" class="jsonContratacaoBox mb-0 small text-break"><?= $esc($pagarmeRequest !== '' ? $pagarmeRequest : '-') ?></pre></div></div></div>
                <div class="col-12 col-lg-6 mb-3"><div class="card border-phsa-light box-phsa-shadow-light h-100"><div class="card-body"><div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-reply"></i>&nbsp;Response</h6><button type="button" class="bto btn-sm bto-phsa-light btnCopiarJsonContratacao" data-copiar-target="contratacaoResponseJson" data-copiar-label="Response do Pagar.me" title="Copiar response"><i class="fa-solid fa-copy"></i></button></div><pre id="contratacaoResponseJson" class="jsonContratacaoBox mb-0 small text-break"><?= $esc($pagarmeResponse !== '' ? $pagarmeResponse : '-') ?></pre></div></div></div>
              </div>
            <?php else: ?>
              <div class="alert alert-light border mb-0">Nenhum registro de integração financeira foi armazenado para esta contratação.</div>
            <?php endif; ?>
          </section>
        </div>

        <div class="phsaTabItem" id="historicoContratacao">
          <?php
            $historicoRegistro = $contratacao;
            $historicoPrefixo = 'plc_';
            $historicoId = (int) ($contratacao->plc_id ?? 0);
            $historicoStatus = (string) ($contratacao->plc_status ?? '');
            $historicoStatusClass = (string) ($contratacao->plc_statusclass ?? 'secondary');
            $historicoLogs = $logsContratacao;
            include __DIR__ . '/../historicoPadrao.php';
          ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($contratacao !== null): ?>
  <?php $this->push('style'); ?>
    <style>
      .jsonContratacaoBox {
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
        $('.btnCopiarJsonContratacao').on('click', function () {
          const $botao = $(this);
          const alvo = document.getElementById($botao.data('copiar-target'));
          if (!alvo || typeof fgbCopiarParaAreaDeTransferencia !== 'function') return;
          fgbCopiarParaAreaDeTransferencia(alvo.textContent.trim(), $botao.data('copiar-label') || 'Conteúdo');
        });
      });
    </script>
  <?php $this->end(); ?>
<?php endif; ?>
