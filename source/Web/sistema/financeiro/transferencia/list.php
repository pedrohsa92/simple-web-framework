<?php
  $urlForm = Helpers::url('financeiro/transferencia/form');
  $this->layout('sistema::listBase', [
    'getInfo' => $getInfo,
    'titulo' => $titulo,
    'descricao' => $descricao,
    'urlCadastrar' => $urlCadastrar,
    'urlPesquisar' => $urlPesquisar,
    'ocultarCadastrar' => $ocultarCadastrar ?? false,
    'labelPesquisaSimples' => $labelPesquisaSimples,
    'labelTotal' => $labelTotal,
    'nomeArquivo' => $nomeArquivo,
    'prefixo' => $prefixo
  ]);
?>

<?php $this->start('listBase_pesquisaPersonalizada'); ?>
  <div class="row">
    <div class="col-12 col-md-4 mb-3">
      <div class="form-floating">
        <select id="trf_status" name="trf_status" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todas</option>
          <option value="Concluida">Concluídas</option>
          <option value="Estornada">Estornadas</option>
        </select>
        <label for="trf_status">Situação</label>
      </div>
    </div>
  </div>
<?php $this->end(); ?>

<?php $this->start('listBase_camposFiltroData'); ?>
  <option value="lan_dtbaixa">Data da transferência</option>
<?php $this->end(); ?>

<?php $this->start('listBase_camposOrdenacao'); ?>
  <option value="lan_id">Número</option>
  <option value="lan_dtbaixa">Data da transferência</option>
  <option value="lan_valor">Valor</option>
  <option value="lan_status">Situação</option>
<?php $this->end(); ?>

<?php $this->start('listBase_resultado'); ?>
  <div class="table-responsive">
    <table id="tableList" class="table table-hover align-middle small">
      <thead>
        <tr>
          <th>Transferência</th>
          <th>Contas</th>
          <th class="text-center text-nowrap">Situação</th>
          <th class="text-end text-nowrap">Ação</th>
        </tr>
      </thead>
      <tbody id="tbodyListBase">
        <tr><td colspan="100" class="text-center text-muted py-4">Aguardando pesquisa...</td></tr>
      </tbody>
    </table>
  </div>
<?php $this->end(); ?>

<?php $this->start('listBase_script'); ?>
  <script>
    const transferenciaUrlForm = '<?= $urlForm ?>';

    function transferenciaEscape(valor) {
      return typeof fgbEscapeHtml === 'function' ? fgbEscapeHtml(valor == null ? '' : valor) : $('<div>').text(valor == null ? '' : valor).html();
    }

    function transferenciaData(valor) {
      const texto = String(valor || '').slice(0, 10);
      if (!/^\d{4}-\d{2}-\d{2}$/.test(texto)) return '—';
      const [ano, mes, dia] = texto.split('-');
      return `${dia}/${mes}/${ano}`;
    }

    function transferenciaDinheiro(valor) {
      return Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderizarLinhaListBase(item) {
      const id = Number(item.trf_id || 0);
      const estornada = String(item.trf_status || '') === 'Estornado';
      const status = estornada
        ? '<span class="badge bg-danger"><i class="fa-solid fa-arrow-rotate-left me-1"></i>Estornada</span>'
        : '<span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Concluída</span>';
      const observacao = String(item.trf_observacao || '').trim();
      const origem = transferenciaEscape(item.trf_conta_origem || 'Conta de origem');
      const destino = transferenciaEscape(item.trf_conta_destino || 'Conta de destino');

      return `<tr>
        <td><a href="${transferenciaUrlForm}/${id}" class="text-phsa fw-semibold">#${id}</a><div class="small text-muted">Movimentação interna</div>${observacao ? `<div class="small text-secondary text-truncate mt-1" style="max-width:250px" title="${transferenciaEscape(observacao)}">${transferenciaEscape(observacao)}</div>` : ''}</td>
        <td><div class="d-flex align-items-center gap-2 text-nowrap"><span class="text-danger"><i class="fa-solid fa-arrow-trend-down me-1"></i>${origem}</span><i class="fa-solid fa-arrow-right text-muted"></i><span class="fw-semibold text-phsa">R$ ${transferenciaDinheiro(item.trf_valor)}</span><i class="fa-solid fa-arrow-right text-muted"></i><span class="text-success"><i class="fa-solid fa-arrow-trend-up me-1"></i>${destino}</span></div><div class="small text-secondary mt-1"><i class="fa-regular fa-calendar me-1"></i>${transferenciaData(item.trf_data)}</div></td>
        <td class="text-center text-nowrap">${status}</td>
        <td class="text-end text-nowrap"><a href="${transferenciaUrlForm}/${id}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Abrir transferência" aria-label="Abrir transferência"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></td>
      </tr>`;
    }
  </script>
<?php $this->end(); ?>
