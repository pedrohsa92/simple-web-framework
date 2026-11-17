<?php
  $this->layout("sistema::sistema");

  $modo = $modo ?? ($contratacao ? 'detalhe' : 'lista');
  $listaContratacoes = (array) ($contratacoes->data ?? []);
  $planosRenovacao = (array) ($planosRenovacao ?? []);
  $esc = static fn($valor) => htmlspecialchars((string) ($valor ?? '-'), ENT_QUOTES, 'UTF-8');
  $formatarData = static fn($valor) => empty($valor) ? '-' : date('d/m/Y H:i', strtotime((string) $valor));
  $formatarValor = static fn($valor) => 'R$ ' . number_format((float) ($valor ?? 0), 2, ',', '.');
  $statusClass = static function ($status): string {
    return match ((string) $status) {
      'Ativo', 'Pago', 'Pago Manual', 'Teste' => 'success',
      'Agendado' => 'info',
      'Pendente', 'Em aberto' => 'warning',
      'Falhou', 'Expirado', 'Cancelado' => 'danger',
      default => 'secondary'
    };
  };
  $statusPagamentoAtual = strtolower(trim((string) ($contratacao->plc_pagarme_status ?? '')));
  $statusPagamentoNumerico = is_numeric($statusPagamentoAtual) ? (int) $statusPagamentoAtual : 0;
  $linkPagamentoComErro = in_array($statusPagamentoAtual, ['expired', 'canceled', 'cancelled', 'failed', 'error', 'inactive'], true)
    || $statusPagamentoNumerico >= 400;
  $podeGerarNovoLink = empty($contratacao->plc_pagarme_url) || $linkPagamentoComErro;
?>

<style>
  .lh-plan-page .lh-renew-alert { border-left: 4px solid currentColor; }
  .lh-plan-page .lh-renew-option { height: 100%; border: 1px solid #dce5f4; border-radius: .8rem; padding: 1rem; background: #fff; }
  .lh-plan-page .lh-renew-option:hover { border-color: #3b64aa; box-shadow: 0 .35rem 1.1rem rgba(36, 72, 132, .08); }
  .lh-plan-page .lh-plan-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
  .lh-plan-page .lh-plan-actions .bto { flex: 1 1 10rem; }
</style>

<main class="lh-plan-page">
  <?php if ($renovacao): ?>
    <div class="alert alert-<?= $esc($renovacao->classe) ?> lh-renew-alert d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <strong><i class="fa-solid fa-circle-info me-1"></i><?= $esc($renovacao->titulo) ?></strong>
        <div class="small mt-1"><?= $esc($renovacao->texto) ?></div>
      </div>
      <a href="<?= $esc($renovacao->url) ?>" class="bto bto-phsa-light">
        <i class="fa-solid fa-rotate me-1"></i><?= $esc($renovacao->botao) ?>
      </a>
    </div>
  <?php endif; ?>

  <?php if ($modo === 'renovar'): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
          <div>
            <div class="text-phsa small fw-bold text-uppercase">Continuidade da organização</div>
            <h2 class="m-0 mt-1">Renovar plano</h2>
            <p class="text-secondary mb-0 mt-2">Escolha o plano e a periodicidade para criar a próxima contratação da organização.</p>
          </div>
          <a href="<?= Helpers::url('organizacao/plano') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
        </div>

        <?php if (($getRenovacaoProgramada->status ?? false) && !empty($getRenovacaoProgramada->data[0])): ?>
          <?php $programada = $getRenovacaoProgramada->data[0]; ?>
          <div class="alert alert-success">
            <strong>Esta organização já possui uma renovação confirmada.</strong>
            <div class="mt-2">A próxima contratação começará em <?= $formatarData($programada->plc_dtinicio) ?>.</div>
            <a href="<?= Helpers::url('organizacao/plano/' . (int) $programada->plc_id) ?>" class="bto bto-phsa-light mt-3"><i class="fa-solid fa-file-contract me-1"></i>Ver renovação</a>
          </div>
        <?php elseif ($getRenovacao->status && !empty($getRenovacao->data[0])): ?>
          <?php $pendente = $getRenovacao->data[0]; ?>
          <div class="alert alert-warning">
            <strong>Já existe uma renovação aguardando pagamento.</strong>
            <div class="mt-2">Para evitar cobranças duplicadas, continue pelo contrato #<?= (int) $pendente->plc_id ?>.</div>
            <a href="<?= Helpers::url('organizacao/plano/' . (int) $pendente->plc_id) ?>" class="bto bto-phsa-light mt-3"><i class="fa-solid fa-file-invoice-dollar me-1"></i>Continuar pagamento</a>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($planosRenovacao as $plano): ?>
              <?php $dedicado = ($plano->pla_tipo ?? '') === 'Dedicado'; ?>
              <div class="col-12 col-lg-6">
                <div class="lh-renew-option">
                  <div class="d-flex justify-content-between gap-3">
                    <div>
                      <span class="badge rounded-pill bg-phsa-light text-phsa"><?= $esc($plano->pla_rotulo ?: $plano->pla_tipo) ?></span>
                      <h4 class="text-phsa mt-2 mb-1"><?= $esc($plano->pla_nome) ?></h4>
                      <p class="text-secondary small mb-3"><?= $esc($plano->pla_descricaoresumida) ?></p>
                    </div>
                    <?php if (!empty($plano->pla_selo)): ?><span class="badge bg-phsa align-self-start"><?= $esc($plano->pla_selo) ?></span><?php endif; ?>
                  </div>

                  <?php if ($dedicado): ?>
                    <div class="alert alert-info small mb-0"><i class="fa-solid fa-comments me-1"></i>Este plano possui configuração personalizada. Fale com o time comercial para renovar.</div>
                  <?php else: ?>
                    <div class="lh-plan-actions">
                      <form method="post" action="<?= Helpers::url('organizacao/salvarRenovacao') ?>" class="flex-grow-1 js-form-renovacao">
                        <input type="hidden" name="pla_id" value="<?= (int) $plano->pla_id ?>">
                        <button type="submit" name="plc_ciclo" value="Mensal" class="bto bto-phsa-light w-100"><i class="fa-regular fa-calendar me-1"></i>Mensal · <?= $formatarValor($plano->pla_valormensal) ?></button>
                      </form>
                      <form method="post" action="<?= Helpers::url('organizacao/salvarRenovacao') ?>" class="flex-grow-1 js-form-renovacao">
                        <input type="hidden" name="pla_id" value="<?= (int) $plano->pla_id ?>">
                        <button type="submit" name="plc_ciclo" value="Anual" class="bto bto-phsa-light w-100"><i class="fa-solid fa-calendar-days me-1"></i>Anual · <?= $formatarValor($plano->pla_valoranual) ?></button>
                      </form>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php elseif ($contratacao): ?>
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 p-3">
          <div>
            <div class="text-phsa small fw-bold text-uppercase">Organização · Plano</div>
            <h2 class="m-0 mt-1">Contratação #<?= (int) $contratacao->plc_id ?></h2>
            <div class="text-secondary mt-1"><?= $esc($contratacao->pla_nome) ?> · <?= $esc($contratacao->plc_ciclo) ?></div>
          </div>
          <a href="<?= Helpers::url('organizacao/plano') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
        </div>
        <?php
        $plano = $contratacao;
        $mostrarPagamento = true;
        include 'source/Web/sistema/organizacao/planoDetalhes.php';
        ?>
      </div>
    </div>
  <?php else: ?>
    <div id="organizacaoPlanosCard" class="card shadow-sm">
      <div class="card-body">
        <div id="organizacaoPlanosHeader" class="row align-items-center">
          <div class="col-12 col-md mb-3 text-uppercase">
            <h4 class="m-0">Planos contratados</h4>
            <div class="small text-muted mt-1">Consulte contratos, pagamentos, períodos e renovações da organização.</div>
          </div>

          <div class="col-12 col-md-auto mb-3 text-end">
            <?php if ($renovacao && Helpers::pode('organizacao.plano.contratar')): ?>
              <a href="<?= Helpers::url('organizacao/plano/renovar') ?>" class="bto bto-phsa-light">
                <i class="fa-solid fa-rotate"></i>&nbsp;Renovar plano
              </a>
            <?php endif; ?>
          </div>
        </div>

        <form id="formOrganizacaoPlanos" method="get" action="<?= Helpers::url('organizacao/plano') ?>" class="needs-validation" novalidate>
          <div class="row">
            <div class="col-12 col-sm-3 mb-3">
              <div class="form-floating">
                <select id="statusOrganizacaoPlanos" name="status" class="form-control input-phsa-focus input-phsa-hover">
                  <option value="">Todos</option>
                  <?php foreach (['Pendente', 'Agendado', 'Teste', 'Ativo', 'Expirado', 'Cancelado'] as $item): ?>
                    <option value="<?= $esc($item) ?>" <?= ($filtros['status'] ?? '') === $item ? 'selected' : '' ?>><?= $esc($item) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="statusOrganizacaoPlanos">Status</label>
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-3">
              <div class="form-floating">
                <select id="financeiroOrganizacaoPlanos" name="financeiro" class="form-control input-phsa-focus input-phsa-hover">
                  <option value="">Todos</option>
                  <?php foreach (['Isento', 'Em aberto', 'Pago', 'Pago Manual', 'Falhou', 'Vencido', 'Cancelado'] as $item): ?>
                    <option value="<?= $esc($item) ?>" <?= ($filtros['financeiro'] ?? '') === $item ? 'selected' : '' ?>><?= $esc($item) ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="financeiroOrganizacaoPlanos">Status financeiro</label>
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-3">
              <div class="form-floating">
                <input type="date" id="dataInicioOrganizacaoPlanos" name="data_inicio" value="<?= $esc($filtros['data_inicio'] ?? '') ?>" class="form-control input-phsa-focus input-phsa-hover">
                <label for="dataInicioOrganizacaoPlanos">Cadastro a partir de</label>
              </div>
            </div>

            <div class="col-12 col-sm-3 mb-3">
              <div class="form-floating">
                <input type="date" id="dataFimOrganizacaoPlanos" name="data_fim" value="<?= $esc($filtros['data_fim'] ?? '') ?>" class="form-control input-phsa-focus input-phsa-hover">
                <label for="dataFimOrganizacaoPlanos">Cadastro até</label>
              </div>
            </div>

            <div class="col-12 mb-3 text-end">
              <button class="bto bto-phsa" type="submit"><i class="fas fa-search"></i>&nbsp;Pesquisar</button>
            </div>
          </div>
        </form>

        <div class="row" id="organizacaoPlanosAcoesResultado">
          <div class="col-12 mb-3 text-end">
            <div class="d-flex flex-column flex-md-row gap-2 justify-content-end">
              <a href="<?= Helpers::url('organizacao/plano') ?>" class="bto btn-sm bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-eraser"></i>&nbsp;Limpar</a>
              <button type="button" onclick="alternarVisualizacaoOrganizacaoPlanos()" class="bto btn-sm bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-table-columns"></i>&nbsp;<span id="labelVisualizacaoOrganizacaoPlanos">Modo analítico</span></button>
              <button type="button" onclick="fgbDatatable('tableOrganizacaoPlanos')" class="bto btn-sm bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar</button>
              <button type="button" onclick="fgbConverterTabelaEmXlsx('tableOrganizacaoPlanos', 'planos-contratados')" class="bto btn-sm bto-phsa-light bto-phsa-hover"><i class="fas fa-file-excel"></i>&nbsp;Exportar</button>
              <button type="button" onclick="fgbGerarImpressao('resultadoOrganizacaoPlanos')" class="bto btn-sm bto-phsa-light bto-phsa-hover"><i class="fas fa-print"></i>&nbsp;Imprimir</button>
            </div>
          </div>
        </div>

        <hr>

        <section id="resultadoOrganizacaoPlanos">
          <div class="row">
            <div class="col-12 col-md-6 mb-2 text-muted small text-uppercase">
              <?= count($listaContratacoes) ?> contratação(ões) cadastrada(s)
            </div>
            <div class="col-12 col-md-6 mb-2 text-muted small text-uppercase text-md-end">
              <?= count($listaContratacoes) ?> contratação(ões) encontrada(s)
            </div>
          </div>

          <div class="table-responsive">
            <table id="tableOrganizacaoPlanos" class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Contratação</th>
                  <th>Plano</th>
                  <th>Período</th>
                  <th>Status</th>
                  <th>Financeiro</th>
                  <th>Valor</th>
                  <th class="colSecundariaOrganizacaoPlanos d-none">Cadastro</th>
                  <th class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$listaContratacoes): ?>
                  <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma contratação encontrada.</td></tr>
                <?php else: ?>
                  <?php foreach ($listaContratacoes as $item): ?>
                    <tr>
                      <td>
                        <a class="text-phsa fw-semibold" href="<?= Helpers::url('organizacao/plano/' . (int) $item->plc_id) ?>">#<?= (int) $item->plc_id ?></a>
                        <div class="text-muted small"><?= $formatarData($item->plc_dtcad) ?></div>
                      </td>
                      <td>
                        <a class="text-phsa fw-semibold" href="<?= Helpers::url('organizacao/plano/' . (int) $item->plc_id) ?>"><?= $esc($item->pla_nome) ?></a>
                        <div class="text-muted small"><?= $esc($item->pla_tipo) ?> · <?= $esc($item->plc_ciclo) ?></div>
                      </td>
                      <td>
                        <div><?= $formatarData($item->plc_dtinicio) ?></div>
                        <div class="text-muted small">até <?= $formatarData($item->plc_dtfim) ?></div>
                      </td>
                      <td><span class="badge bg-<?= $esc($statusClass($item->plc_status)) ?>"><?= $esc($item->plc_status) ?></span></td>
                      <td><span class="badge bg-<?= $esc($statusClass($item->plc_statusfinanceiro)) ?>"><?= $esc($item->plc_statusfinanceiro) ?></span></td>
                      <td><?= $formatarValor($item->plc_valor) ?></td>
                      <td class="colSecundariaOrganizacaoPlanos d-none"><div class="text-muted small">Cadastro: <?= $formatarData($item->plc_dtcad) ?></div><div class="text-muted small">Alterado: <?= $formatarData($item->plc_dtalter) ?></div></td>
                      <td class="text-end"><a href="<?= Helpers::url('organizacao/plano/' . (int) $item->plc_id) ?>" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Visualizar contratação"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php $this->push('script'); ?>
<script>
  let organizacaoPlanosModoAnalitico = false;

  function alternarVisualizacaoOrganizacaoPlanos() {
    organizacaoPlanosModoAnalitico = !organizacaoPlanosModoAnalitico;
    document.querySelectorAll('.colSecundariaOrganizacaoPlanos').forEach(function (elemento) {
      elemento.classList.toggle('d-none', !organizacaoPlanosModoAnalitico);
    });

    const label = document.getElementById('labelVisualizacaoOrganizacaoPlanos');
    if (label) label.textContent = organizacaoPlanosModoAnalitico ? 'Modo sintético' : 'Modo analítico';
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-form-renovacao').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button) { button.disabled = true; button.dataset.texto = button.innerHTML; button.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Processando...'; }
        fetch(form.action, { method: 'POST', body: new FormData(form), headers: {'X-Requested-With': 'XMLHttpRequest'} })
          .then(response => response.json())
          .then(response => {
            if (response.location) { window.location.href = response.location; return; }
            if (typeof swResponse === 'function') swResponse(response); else alert(response.text || 'Não foi possível registrar a renovação.');
            if (button) { button.disabled = false; button.innerHTML = button.dataset.texto; }
          })
          .catch(() => { if (button) { button.disabled = false; button.innerHTML = button.dataset.texto; } alert('Não foi possível concluir a renovação agora.'); });
      });
    });

    const btnPagamento = document.getElementById('btnGerarPagamentoOrganizacao');
    if (btnPagamento) {
      btnPagamento.addEventListener('click', function () {
        btnPagamento.disabled = true;
        btnPagamento.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Gerando link...';
        const dados = new FormData();
        dados.append('plc_id', btnPagamento.dataset.id || '');
        fetch('<?= Helpers::url('organizacao/gerarPagamento') ?>', { method: 'POST', body: dados, headers: {'X-Requested-With': 'XMLHttpRequest'} })
          .then(response => response.json())
          .then(response => { if (response.location) { window.location.href = response.location; return; } if (typeof swResponse === 'function') swResponse(response); btnPagamento.disabled = false; btnPagamento.innerHTML = '<i class="fa-solid fa-rotate me-1"></i>Gerar novo link de pagamento'; })
          .catch(() => { btnPagamento.disabled = false; btnPagamento.innerHTML = '<i class="fa-solid fa-rotate me-1"></i>Gerar novo link de pagamento'; });
      });
    }
  });
</script>
<?php $this->end(); ?>
