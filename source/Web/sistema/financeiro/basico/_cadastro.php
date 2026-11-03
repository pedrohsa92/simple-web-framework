<?php
  $prefixo = $config->prefixo;
  $idCampo = $prefixo . '_id';
  $statusCampo = $prefixo . '_status';
  $padraoCampo = $prefixo . '_padrao';
  $podeCriar = Helpers::pode('financeiro.basico.criar');
  $podeAtualizar = Helpers::pode('financeiro.basico.atualizar');
  $podeAlterarStatus = $podeAtualizar;
  $formularioEmRota = (bool) ($formularioEmRota ?? false);
  $registroAtual = $registroAtual ?? null;
  // A rota é a fonte confiável para distinguir edição de novo cadastro.
  // Mantém fallbacks para chamadas da view que não passem o ID explicitamente.
  $registroAtualId = (int) ($registroAtualId ?? $registroAtual->{$idCampo} ?? $registroAtual->id ?? 0);
  $alertaFlashJson = json_encode($alertaFlash ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
  $registroAtualJson = json_encode($registroAtual, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
      <div>
        <?php if ($formularioEmRota): ?>
          <h4 class="m-0"><?= $registroAtualId > 0 ? 'Editando registro' : 'Novo registro' ?></h4>
          <div class="text-muted small"><?= htmlspecialchars($registroAtual ? (string) ($registroAtual->{$prefixo . '_nome'} ?? '') : 'Informe os dados do cadastro financeiro.', ENT_QUOTES, 'UTF-8') ?></div>
        <?php else: ?>
          <h4 class="m-0 text-uppercase"><?= htmlspecialchars($config->titulo, ENT_QUOTES, 'UTF-8') ?></h4>
          <div class="text-muted small"><?= htmlspecialchars($config->descricao, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
      </div>

      <div class="d-flex gap-2">
        <?php if ($formularioEmRota): ?>
          <a href="<?= htmlspecialchars((string) $urlListaCadastro, ENT_QUOTES, 'UTF-8') ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-arrow-left"></i>&nbsp;Cancelar</a>
        <?php else: ?>
          <a href="<?= Helpers::url('financeiro/basico') ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a>
          <?php if ($podeCriar): ?>
            <a href="<?= htmlspecialchars((string) $urlNovoCadastro, ENT_QUOTES, 'UTF-8') ?>" class="bto bto-phsa"><i class="fa-solid fa-plus"></i>&nbsp;Novo</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$formularioEmRota): ?>
    <div class="row mb-2"><div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
      <button type="button" onclick="alternarModoFinanceiroBasico()" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-table-columns"></i>&nbsp;<span id="labelModoFinanceiroBasico">Modo analítico</span></button>
      <button type="button" onclick="fgbDatatable('tableFinanceiroBasico')" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar</button>
      <button type="button" onclick="fgbConverterTabelaEmXlsx('tableFinanceiroBasico', '<?= htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8') ?>')" class="bto bto-phsa-light bto-phsa-hover"><i class="fas fa-file-excel"></i>&nbsp;Exportar</button>
      <button type="button" onclick="fgbGerarImpressao('resultadoFinanceiroBasico', '<?= htmlspecialchars($config->titulo, ENT_QUOTES, 'UTF-8') ?>')" class="bto bto-phsa-light bto-phsa-hover"><i class="fas fa-print"></i>&nbsp;Imprimir</button>
    </div></div>
    <section id="resultadoFinanceiroBasico">
    <div id="secaoListagemFinanceiroBasico">
      <div class="table-responsive">
        <table id="tableFinanceiroBasico" class="table table-hover align-middle">
          <thead>
            <tr>
              <?php foreach ($colunas as $coluna): ?>
                <th><?= $coluna['label'] ?></th>
              <?php endforeach; ?>
            <th>Escopo</th>
            <th>Status</th>
            <th class="text-center colSecundariaFinanceiroBasico d-none">Padrão</th>
            <th class="text-end hideOnPrint hideOnExcel">Ação</th>
            </tr>
          </thead>

          <tbody>
            <?php if (!empty($registros)): ?>
              <?php foreach ($registros as $registro): ?>
                <?php $idRegistroLinha = (int) ($registro->{$idCampo} ?? 0); $podeEditarLinha = $podeAtualizar && !empty($registro->pode_editar_escopo); $statusLinha = (string) ($registro->{$statusCampo} ?? 'Ativo'); $podeAlterarStatusLinha = $podeEditarLinha && $podeAlterarStatus; ?>
                <tr title="ID: <?= $idRegistroLinha ?>">
                  <?php foreach ($colunas as $coluna): ?>
                    <?php
                      $valorColuna = $registro->{$coluna['campo']} ?? null;
                      if (isset($coluna['valores'][$valorColuna])) {
                        $valorColuna = $coluna['valores'][$valorColuna];
                      } elseif (($coluna['tipo'] ?? '') === 'booleano') {
                        $valorColuna = !empty($valorColuna) ? 'Sim' : 'Não';
                      }
                    ?>
                    <td><?php if ($coluna['campo'] === $prefixo . '_nome' && $podeEditarLinha): ?><a class="text-phsa fw-semibold text-decoration-none" href="<?= htmlspecialchars($urlEditarCadastroBase . '/' . $idRegistroLinha, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) (($valorColuna === null || $valorColuna === '') ? '-' : $valorColuna), ENT_QUOTES, 'UTF-8') ?></a><?php else: ?><?= htmlspecialchars((string) (($valorColuna === null || $valorColuna === '') ? '-' : $valorColuna), ENT_QUOTES, 'UTF-8') ?><?php endif; ?></td>
                  <?php endforeach; ?>

                  <td>
                    <?php
                      $rotulosEscopo = ['Organizacao' => 'Organização', 'Empresa' => 'Empresa'];
                      $escopo = $registro->escopo_financeiro ?? 'Empresa';
                    ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars((string) ($rotulosEscopo[$escopo] ?? $escopo), ENT_QUOTES, 'UTF-8') ?></span>
                  </td>

                  <td>
                    <?php if ($podeAlterarStatusLinha): ?>
                      <button type="button" class="badge bg-<?= ($registro->{$statusCampo} ?? 'Ativo') === 'Ativo' ? 'success' : 'danger' ?> border-0 btnStatusFinanceiroBasico" data-id="<?= $idRegistroLinha ?>" data-status="<?= htmlspecialchars((string) ($registro->{$statusCampo} ?? 'Ativo'), ENT_QUOTES, 'UTF-8') ?>" data-nome="<?= htmlspecialchars((string) ($registro->{$prefixo . '_nome'} ?? ''), ENT_QUOTES, 'UTF-8') ?>" title="Alterar status"><?= htmlspecialchars((string) ($registro->{$statusCampo} ?? 'Ativo'), ENT_QUOTES, 'UTF-8') ?></button>
                    <?php else: ?><span class="badge bg-<?= ($registro->{$statusCampo} ?? 'Ativo') === 'Ativo' ? 'success' : 'danger' ?>"><?= htmlspecialchars((string) ($registro->{$statusCampo} ?? 'Ativo'), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                  </td>

                  <td class="text-center colSecundariaFinanceiroBasico d-none">
                    <?= !empty($registro->{$padraoCampo}) ? '<i class="fa-solid fa-check text-success"></i>' : '-' ?>
                  </td>

                  <td class="text-end hideOnPrint hideOnExcel">
                    <?php if ($podeEditarLinha): ?>
                      <a href="<?= htmlspecialchars($urlEditarCadastroBase . '/' . $idRegistroLinha, ENT_QUOTES, 'UTF-8') ?>" class="bto btn-sm bto-phsa-light bto-phsa-hover hideOnPrint hideOnExcel" title="Editar registro">
                        <i class="fa-regular fa-pen-to-square"></i>
                      </a>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="100" class="text-center text-muted py-4">
                  Nenhum registro financeiro encontrado.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    </section>
    <?php endif; ?>

    <?php if ($formularioEmRota): ?>
    <header class="phsaScrollHeader">
      <nav class="phsaScrollNav" data-menu="tabFinanceiroBasico">
        <ul class="phsaScrollList">
          <li class="phsaScrollItem active" id="abaCadastroFinanceiroBasico" data-target="painelCadastroFinanceiroBasico"><i class="fa-solid fa-id-card"></i>&nbsp;Cadastro</li>
          <?php if ($registroAtualId > 0): ?><li class="phsaScrollItem" id="abaHistoricoFinanceiroBasico" data-target="painelHistoricoFinanceiroBasico"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li><?php endif; ?>
        </ul>
      </nav>
    </header>
    <div class="phsaTabContent phsaTabSubContent">
      <div class="phsaTabItem active p-3" id="painelCadastroFinanceiroBasico">
    <div id="secaoFormularioFinanceiroBasico">
      <form id="formFinanceiroBasico" class="needs-validation" novalidate>
        <input type="hidden" name="cadastro" value="<?= htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" id="<?= $idCampo ?>" name="<?= $idCampo ?>" value="0">
        <input type="hidden" name="<?= $statusCampo ?>" value="<?= htmlspecialchars((string) ($registroAtual->{$statusCampo} ?? 'Ativo'), ENT_QUOTES, 'UTF-8') ?>">

        <div class="row g-3">
          <div class="<?= $escopoClass ?? 'col-12 col-md-4' ?>">
        <div class="form-floating">
              <select id="escopo_financeiro" name="escopo_financeiro" class="form-control input-phsa-focus input-phsa-hover" required>
                <?php foreach (($escoposFinanceiros ?? []) as $escopo): ?>
                  <option value="<?= htmlspecialchars($escopo->value, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($escopo->label, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
              <label for="escopo_financeiro">Escopo financeiro</label>
              <div class="invalid-feedback">Selecione o escopo financeiro.</div>
            </div>
          </div>

          <?php foreach ($campos as $campo): ?>
            <div class="<?= $campo['class'] ?? 'col-12 col-md-4' ?>">
              <?php if (($campo['type'] ?? 'text') === 'checkbox'): ?>
                <div class="form-check form-switch">
                  <input
                    class="form-check-input campoFinanceiroBasico"
                    type="checkbox"
                    id="<?= $campo['name'] ?>"
                    name="<?= $campo['name'] ?>"
                    value="1"
                    data-field="<?= $campo['name'] ?>"
                  >
                  <label class="form-check-label" for="<?= $campo['name'] ?>"><?= $campo['label'] ?></label>
                </div>
              <?php else: ?>
                <?php
                  $inputClasses = trim('form-control input-phsa-focus input-phsa-hover campoFinanceiroBasico ' . ($campo['inputClass'] ?? ''));
                ?>
                <div class="form-floating">
                  <?php if (($campo['type'] ?? 'text') === 'select'): ?>
                    <select
                      id="<?= $campo['name'] ?>"
                      name="<?= $campo['name'] ?>"
                      class="form-control input-phsa-focus input-phsa-hover campoFinanceiroBasico"
                      data-field="<?= $campo['name'] ?>"
                      <?= !empty($campo['disabledOnUpdate']) ? 'data-disabled-on-update="1"' : '' ?>
                      <?= $campo['name'] === $statusCampo ? 'disabled' : '' ?>
                      <?= !empty($campo['required']) ? 'required' : '' ?>
                    >
                      <option value="" <?= !empty($campo['required']) ? 'selected' : '' ?> <?= empty($campo['required']) ? 'disabled' : '' ?>><?= !empty($campo['required']) ? 'Selecione' : 'Não informado' ?></option>
                      <?php foreach (($campo['options'] ?? []) as $option): ?>
                        <option value="<?= htmlspecialchars((string) $option->value, ENT_QUOTES, 'UTF-8') ?>">
                          <?= htmlspecialchars((string) $option->label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <?php if (!empty($campo['required'])): ?><div class="invalid-feedback"><?= ($campo['type'] ?? '') === 'select' ? 'Selecione ' : 'Informe ' ?><?= htmlspecialchars(mb_strtolower($campo['label']), ENT_QUOTES, 'UTF-8') ?>.</div><?php endif; ?>
                  <?php elseif (($campo['type'] ?? 'text') === 'textarea'): ?>
                    <textarea
                      id="<?= $campo['name'] ?>"
                      name="<?= $campo['name'] ?>"
                      class="<?= $inputClasses ?>"
                      data-field="<?= $campo['name'] ?>"
                      placeholder="<?= htmlspecialchars($campo['label'], ENT_QUOTES, 'UTF-8') ?>"
                      style="height: 96px"
                      <?= !empty($campo['disabledOnUpdate']) ? 'data-disabled-on-update="1"' : '' ?>
                      <?= isset($campo['maxlength']) ? 'maxlength="' . (int) $campo['maxlength'] . '"' : '' ?>
                      <?= !empty($campo['required']) ? 'required' : '' ?>
                    ></textarea>
                    <?php if (!empty($campo['required'])): ?><div class="invalid-feedback">Informe <?= htmlspecialchars(mb_strtolower($campo['label']), ENT_QUOTES, 'UTF-8') ?>.</div><?php endif; ?>
                  <?php else: ?>
                    <input
                      id="<?= $campo['name'] ?>"
                      name="<?= $campo['name'] ?>"
                      type="<?= $campo['type'] ?? 'text' ?>"
                      class="<?= $inputClasses ?>"
                      data-field="<?= $campo['name'] ?>"
                      value="<?= htmlspecialchars((string) ($campo['default'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                      placeholder="<?= htmlspecialchars($campo['label'], ENT_QUOTES, 'UTF-8') ?>"
                      <?= isset($campo['min']) ? 'min="' . htmlspecialchars((string) $campo['min'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                      <?= isset($campo['max']) ? 'max="' . htmlspecialchars((string) $campo['max'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                      <?= isset($campo['maxlength']) ? 'maxlength="' . (int) $campo['maxlength'] . '"' : '' ?>
                      <?= !empty($campo['disabledOnUpdate']) ? 'data-disabled-on-update="1"' : '' ?>
                      <?= isset($campo['step']) ? 'step="' . htmlspecialchars((string) $campo['step'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                      <?= !empty($campo['required']) ? 'required' : '' ?>
                    >
                    <?php if (!empty($campo['required'])): ?><div class="invalid-feedback">Informe <?= htmlspecialchars(mb_strtolower($campo['label']), ENT_QUOTES, 'UTF-8') ?>.</div><?php endif; ?>
                  <?php endif; ?>

                  <label for="<?= $campo['name'] ?>"><?= $campo['label'] ?></label>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <?php if ($podeCriar || $podeAtualizar): ?>
            <button type="submit" id="btnSalvarFinanceiroBasico" class="bto bto-phsa">
              <i class="fa-solid fa-check-double"></i>&nbsp;Salvar
            </button>
          <?php endif; ?>
        </div>
      </form>
    </div>
      </div>
      <?php if ($registroAtualId > 0): ?>
      <div class="phsaTabItem p-3" id="painelHistoricoFinanceiroBasico">
        <div class="row g-3">
          <div class="col-12"><section class="card border-phsa-light box-phsa-shadow-light"><div class="card-body">
            <h6 class="text-phsa mb-0"><i class="fa-solid fa-id-card"></i>&nbsp;Identificação</h6>
            <div class="row g-3"><div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">ID</div><div class="phsaFakeInputValue" id="historicoFinanceiroId">—</div></div></div>
              <div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Status</div><div class="phsaFakeInputValue" id="historicoFinanceiroStatus">—</div></div></div>
            </div>
          </div></section></div>
          <div class="col-12"><section class="card border-phsa-light box-phsa-shadow-light"><div class="card-body">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Log de alterações</h6><span id="totalLogsFinanceiro" class="small text-muted">0 alterações</span></div>
            <div class="row g-3 mb-3"><div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Cadastrado em</div><div class="phsaFakeInputValue w-100 text-center" id="historicoFinanceiroDtCad">—</div></div></div><div class="col-12 col-md-6"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Última alteração</div><div class="phsaFakeInputValue w-100 text-center" id="historicoFinanceiroDtAlter">—</div></div></div></div>
            <div id="semLogsFinanceiro" class="alert alert-light border mb-0 small" style="display:none">Nenhum histórico registrado para este cadastro.</div>
            <div id="tabelaLogsFinanceiro" class="table-responsive" style="display:none"><table class="table table-hover align-middle mb-0 small"><thead><tr><th>Usuário</th><th>Ação</th><th>Data</th><th>Detalhes</th></tr></thead><tbody id="corpoLogsFinanceiro"></tbody></table></div>
          </div></section></div>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php $this->push('script'); ?>
  <script>
    const financeiroBasicoRegistros = <?= json_encode($registros ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const financeiroBasicoIdCampo = '<?= $idCampo ?>';
    const financeiroBasicoStatusCampo = '<?= $statusCampo ?>';
    const financeiroBasicoPodeCriar = <?= $podeCriar ? 'true' : 'false' ?>;
    const financeiroBasicoPodeAtualizar = <?= $podeAtualizar ? 'true' : 'false' ?>;
    const financeiroBasicoPodeAlterarStatus = <?= $podeAlterarStatus ? 'true' : 'false' ?>;
    const financeiroBasicoFormularioEmRota = <?= $formularioEmRota ? 'true' : 'false' ?>;
    const financeiroBasicoRegistroAtual = <?= $registroAtualJson ?: 'null' ?>;
    const financeiroBasicoUrlSalvar = <?= json_encode(Helpers::url('financeiro/salvarBasico'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const financeiroBasicoUrlStatus = <?= json_encode(Helpers::url('financeiro/basico/alterarStatus'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const financeiroBasicoUrlUsuario = <?= json_encode(Helpers::url('usuario/form/'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const financeiroBasicoPodeVincularUsuario = <?= Helpers::pode('usuario.atualizar') ? 'true' : 'false' ?>;
    const financeiroBasicoAlertaFlash = <?= $alertaFlashJson ?: 'null' ?>;

    function alternarModoFinanceiroBasico() {
      const analitico = $('#labelModoFinanceiroBasico').text() === 'Modo analítico';
      $('.colSecundariaFinanceiroBasico').toggleClass('d-none', !analitico);
      $('#labelModoFinanceiroBasico').text(analitico ? 'Modo sintético' : 'Modo analítico');
    }

    function dataHoraFinanceiro(valor) {
      const texto = String(valor ?? '').trim();
      const iso = texto.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::\d{2})?)?/);
      if (iso) return `${iso[3]}/${iso[2]}/${iso[1]} ${iso[4] || '00'}:${iso[5] || '00'}`;
      const br = texto.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2})(?::\d{2})?)?/);
      if (br) return `${br[1]}/${br[2]}/${br[3]} ${br[4] || '00'}:${br[5] || '00'}`;
      return texto || '—';
    }

    function renderizarHistoricoFinanceiro(registro) {
      const esc = (valor) => $('<div>').text(valor == null ? '' : String(valor)).html();
      $('#historicoFinanceiroId').text(registro.id || registro[financeiroBasicoIdCampo] || '—');
      $('#historicoFinanceiroStatus').html(`<span class="badge bg-${registro[financeiroBasicoStatusCampo] === 'Ativo' ? 'success' : 'danger'}">${esc(registro[financeiroBasicoStatusCampo] || '—')}</span>`);
      $('#historicoFinanceiroDtCad').text(dataHoraFinanceiro(registro[financeiroBasicoIdCampo.replace(/_id$/, '_dtcad')]));
      $('#historicoFinanceiroDtAlter').text(dataHoraFinanceiro(registro[financeiroBasicoIdCampo.replace(/_id$/, '_dtalter')]));
      let logs = [];
      try { logs = Array.isArray(registro[financeiroBasicoIdCampo.replace(/_id$/, '_log')]) ? registro[financeiroBasicoIdCampo.replace(/_id$/, '_log')] : JSON.parse(registro[financeiroBasicoIdCampo.replace(/_id$/, '_log')] || '[]'); } catch (_) { logs = []; }
      logs = Array.isArray(logs) ? logs : [];
      $('#totalLogsFinanceiro').text(`${logs.length} ${logs.length === 1 ? 'alteração' : 'alterações'}`);
      if (!logs.length) { $('#corpoLogsFinanceiro').empty(); $('#tabelaLogsFinanceiro').hide(); $('#semLogsFinanceiro').show(); return; }
      const linhas = logs.map((log) => {
        log = log && typeof log === 'object' ? log : {};
        const usuario = String(log.usu_razaosocial || 'Sistema');
        const idUsuario = parseInt(log.usu_id || 0, 10);
        const nomeHtml = financeiroBasicoPodeVincularUsuario && idUsuario > 0
          ? `<a class="text-phsa text-decoration-none" href="${esc(financeiroBasicoUrlUsuario + idUsuario)}" title="${esc(usuario)}">${esc(usuario)}</a>`
          : `<span class="text-phsa" title="${esc(usuario)}">${esc(usuario)}</span>`;
        const detalhes = Array.isArray(log.detalhe) ? log.detalhe : (log.detalhe ? [log.detalhe] : []);
        const detalheHtml = detalhes.length ? `<ul class="mb-0 ps-3">${detalhes.map(item => `<li>${esc(item)}</li>`).join('')}</ul>` : '<span class="text-muted">-</span>';
        return `<tr><td>${nomeHtml}</td><td>${esc(log.acao || '—')}</td><td class="text-nowrap">${esc(dataHoraFinanceiro(log.data))}</td><td>${detalheHtml}</td></tr>`;
      }).join('');
      $('#corpoLogsFinanceiro').html(linhas); $('#semLogsFinanceiro').hide(); $('#tabelaLogsFinanceiro').show();
    }

    function abrirModalStatusFinanceiro(botao) {
      const id = parseInt(botao.data('id') || 0, 10);
      const statusAtual = String(botao.data('status') || 'Ativo');
      const novoStatus = statusAtual === 'Ativo' ? 'Inativo' : 'Ativo';
      if (!financeiroBasicoPodeAlterarStatus) { swError('Você não possui permissão para alterar o status de cadastros financeiros.'); return; }
      const modalId = SimpleModal.mostrar({
        titulo: '<i class="fa-solid fa-toggle-on"></i>&nbsp;Alterar status financeiro',
        corpo: `<form id="formStatusFinanceiroBasico" class="needs-validation" novalidate autocomplete="off"><input type="hidden" name="cadastro" value="<?= htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="<?= $idCampo ?>" value="${id}"><input type="hidden" name="<?= $statusCampo ?>" value="${novoStatus}"><div class="alert alert-light border small"><strong>${$('<div>').text(String(botao.data('nome') || 'Registro financeiro')).html()}</strong><br>Status atual: ${statusAtual}<br>Novo status: <span class="badge bg-${novoStatus === 'Ativo' ? 'success' : 'danger'}">${novoStatus}</span></div><div class="form-floating"><textarea id="motivoStatusFinanceiro" name="motivo" class="form-control" style="height:120px" minlength="10" ${novoStatus === 'Inativo' ? 'required' : ''}></textarea><label for="motivoStatusFinanceiro">Motivo ${novoStatus === 'Inativo' ? '*' : '(opcional)'}</label><div class="invalid-feedback">Informe pelo menos 10 caracteres para inativar.</div></div></form>`,
        rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal">Cancelar</button><button type="button" class="bto bto-phsa" id="confirmarStatusFinanceiro">Confirmar</button>'
      });
      $('#confirmarStatusFinanceiro').off('click').on('click', function () {
        const form = $('#formStatusFinanceiroBasico');
        if (!form[0].checkValidity()) { form.addClass('was-validated'); return; }
        const button = $(this).prop('disabled', true);
        $.post(financeiroBasicoUrlStatus, form.serialize(), null, 'json').done(function (response) {
          swResponse(response);
          if (response.status) { SimpleModal.fechar(modalId); setTimeout(() => location.reload(), 700); }
          else button.prop('disabled', false);
        }).fail(function () { button.prop('disabled', false); swError('Não foi possível alterar o status.'); });
      });
    }

    $(document).on('click', '.btnStatusFinanceiroBasico', function () { abrirModalStatusFinanceiro($(this)); });
    if (financeiroBasicoAlertaFlash && financeiroBasicoAlertaFlash.mensagem) Swal.fire({icon: 'error', title: financeiroBasicoAlertaFlash.titulo || 'Registro não encontrado', text: financeiroBasicoAlertaFlash.mensagem, timer: 3000, timerProgressBar: true, showConfirmButton: false});
    if (financeiroBasicoFormularioEmRota && financeiroBasicoRegistroAtual) {
      const registro = financeiroBasicoRegistroAtual;
      $('#' + financeiroBasicoIdCampo).val(registro[financeiroBasicoIdCampo] || registro.id || 0);
      $('#escopo_financeiro').val(registro.escopo_financeiro || 'Empresa').prop('disabled', true);
      $('.campoFinanceiroBasico').each(function () {
        const field = $(this).data('field'); const value = registro[field];
        if ($(this).attr('type') === 'checkbox') $(this).prop('checked', parseInt(value || 0, 10) === 1);
        else if ($(this).hasClass('dinheiro')) $(this).val(formatarDinheiroFinanceiroBasico(value));
        else $(this).val(value ?? '');
      });
      $('[data-disabled-on-update="1"]').prop('disabled', true);
      $('#'+financeiroBasicoStatusCampo).prop('disabled', true);
      renderizarHistoricoFinanceiro(registro);
      atualizarPlanoContaPorNivel();
    }
    if (financeiroBasicoFormularioEmRota) {
      $('#' + financeiroBasicoStatusCampo).val(financeiroBasicoRegistroAtual?.[financeiroBasicoStatusCampo] || 'Ativo');
    }

    function exibirFormularioFinanceiroBasico() {
      $('#secaoListagemFinanceiroBasico').hide();
      $('#secaoFormularioFinanceiroBasico').fadeIn(150);
    }

    function exibirListagemFinanceiroBasico() {
      $('#secaoFormularioFinanceiroBasico').hide();
      $('#secaoListagemFinanceiroBasico').fadeIn(150);
    }

    function limparFormularioFinanceiroBasico() {
      const form = $('#formFinanceiroBasico')[0];
      form.reset();
      $('#formFinanceiroBasico').removeClass('was-validated');
      $('#' + financeiroBasicoIdCampo).val(0);
      $('#escopo_financeiro').prop('disabled', false);
      $('#' + financeiroBasicoStatusCampo).val('Ativo').prop('disabled', false);
      $('.campoFinanceiroBasico[type="checkbox"]').prop('checked', false);
      $('[data-disabled-on-update="1"]').prop('disabled', false);
      $('#btnSalvarFinanceiroBasico').prop('disabled', !financeiroBasicoPodeCriar).show();
      atualizarPlanoContaPorNivel();
    }

    function editarFinanceiroBasico(id) {
      const registro = financeiroBasicoRegistros.find(item => parseInt(item[financeiroBasicoIdCampo] || item.id || 0) === parseInt(id));

      if (!registro) {
        swError('Registro não encontrado.');
        return;
      }

      if (!registro.pode_editar_escopo || !financeiroBasicoPodeAtualizar) {
        swError('Você não possui permissão para editar este registro.');
        return;
      }

      limparFormularioFinanceiroBasico();
      $('#' + financeiroBasicoIdCampo).val(registro[financeiroBasicoIdCampo] || registro.id || 0);
      $('#escopo_financeiro').val(registro.escopo_financeiro || 'Empresa').prop('disabled', true);
      $('[data-disabled-on-update="1"]').prop('disabled', true);

      $('.campoFinanceiroBasico').each(function () {
        const field = $(this).data('field');
        const value = registro[field];

        if ($(this).attr('type') === 'checkbox') {
          $(this).prop('checked', parseInt(value || 0) === 1);
          return;
        }

        if ($(this).hasClass('dinheiro')) {
          $(this).val(formatarDinheiroFinanceiroBasico(value));
          return;
        }

        $(this).val(value ?? '');
      });

      atualizarPlanoContaPorNivel();
      $('#btnSalvarFinanceiroBasico').prop('disabled', false).show();
      exibirFormularioFinanceiroBasico();
    }

    function formatarDinheiroFinanceiroBasico(value) {
      const numberValue = parseFloat(String(value ?? '0').replace(',', '.'));

      if (Number.isNaN(numberValue)) {
        return '';
      }

      return numberValue.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function atualizarPlanoContaPorNivel() {
      if ($('#pct_nivel').length <= 0 || $('#pct_idpai').length <= 0) {
        return;
      }

      const isGrupo = $('#pct_nivel').val() === 'Grupo';
      const isEdicao = parseInt($('#' + financeiroBasicoIdCampo).val() || 0) > 0;
      const paiBloqueado = isEdicao && $('#pct_idpai').data('disabled-on-update') === 1;

      $('#pct_idpai')
        .prop('disabled', isGrupo || paiBloqueado)
        .prop('required', !isGrupo && !paiBloqueado)
        .val(isGrupo ? '' : $('#pct_idpai').val());
    }

    $('#btnNovoFinanceiroBasico').on('click', function () {
      limparFormularioFinanceiroBasico();
      exibirFormularioFinanceiroBasico();
    });

    $('#btnCancelarFinanceiroBasico').on('click', function () {
      exibirListagemFinanceiroBasico();
    });

    $('#pct_nivel').on('change', function () {
      atualizarPlanoContaPorNivel();
    });

    $('.btnEditarFinanceiroBasico').on('click', function () {
      editarFinanceiroBasico($(this).data('id'));
    });

    $('#formFinanceiroBasico').on('submit', function (event) {
      event.preventDefault();

      if (!this.checkValidity()) {
        event.stopPropagation();
        $(this).addClass('was-validated');
        return;
      }

      const botao = $('#btnSalvarFinanceiroBasico');
      botao.prop('disabled', true);

      $.ajax({
        url: financeiroBasicoUrlSalvar,
        type: 'post',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (response) {
          swResponse(response);

          if (response.status && response.location) {
            setTimeout(function () {
              location.replace(response.location);
            }, 1500);
            return;
          }

          botao.prop('disabled', false);
        },
        error: function (xhr) {
          console.error(xhr.responseText);
          swError('Erro ao salvar cadastro financeiro.');
          botao.prop('disabled', false);
        }
      });
    });
  </script>
<?php $this->end(); ?>
