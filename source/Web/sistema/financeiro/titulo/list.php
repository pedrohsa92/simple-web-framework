<?php
  $podeAtualizarTitulo = Helpers::pode('titulo.atualizar');
  $urlFormTitulo = Helpers::url('titulo/form');
  $urlFornecedor = Helpers::url('fornecedor/form');
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
        <select id="tit_status" name="tit_status" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="Cadastrado">Cadastrado</option>
          <option value="Confirmado">Confirmado</option>
          <option value="Cancelado">Cancelado</option>
        </select>
        <label for="tit_status">Status do título</label>
      </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
      <div class="form-floating">
        <select id="tit_statusfinanceiro" name="tit_statusfinanceiro" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="Aguardando confirmação">Aguardando confirmação</option>
          <option value="Aguardando lançamento">Aguardando lançamento</option>
          <option value="Lançamento registrado">Lançamento registrado</option>
          <option value="Parcial">Parcial</option>
          <option value="Pago">Pago</option>
          <option value="Recebido">Recebido</option>
          <option value="Cancelado">Cancelado</option>
        </select>
        <label for="tit_statusfinanceiro">Status financeiro</label>
      </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
      <div class="form-floating">
        <select id="tit_natureza" name="tit_natureza" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todas</option>
          <option value="Entrada">Entrada</option>
          <option value="Saida">Saída</option>
        </select>
        <label for="tit_natureza">Natureza</label>
      </div>
    </div>
  </div>
<?php $this->end(); ?>

<?php $this->start('listBase_camposFiltroData'); ?>
  <option value="tit_referencia">Referência</option>
  <option value="tit_dtvencimento">Vencimento</option>
<?php $this->end(); ?>

<?php $this->start('listBase_camposOrdenacao'); ?>
  <option value="tit_id">Número</option>
  <option value="tit_nome">Título</option>
  <option value="tit_pct_nome">Plano de conta</option>
  <option value="tit_natureza">Natureza</option>
  <option value="tit_referencia">Referência</option>
  <option value="tit_dtvencimento">Vencimento</option>
  <option value="tit_valortotal">Valor total</option>
  <option value="tit_valormovimentado">Movimentado</option>
  <option value="tit_status">Status</option>
  <option value="tit_statusfinanceiro">Status financeiro</option>
<?php $this->end(); ?>

<?php $this->start('listBase_resultado'); ?>
  <div class="table-responsive">
    <table id="tableList" class="table table-hover align-middle small">
      <thead>
        <tr>
          <th>Título</th>
          <th>Responsável</th>
          <th class="colSecundaria text-center text-nowrap">Datas</th>
          <th class="text-center text-nowrap">Status</th>
          <th class="colSecundaria text-center text-nowrap">Natureza</th>
          <th class="text-center text-nowrap">Valores</th>
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
    const tituloPodeAtualizar = <?= $podeAtualizarTitulo ? 'true' : 'false' ?>;
    const tituloUrlForm = '<?= $urlFormTitulo ?>';
    const tituloUrlFornecedor = '<?= $urlFornecedor ?>';

    function tituloEscape(valor) {
      return typeof fgbEscapeHtml === 'function' ? fgbEscapeHtml(valor == null ? '' : valor) : $('<div>').text(valor == null ? '' : valor).html();
    }

    function tituloData(valor) {
      const texto = String(valor || '').slice(0, 10);
      if (!/^\d{4}-\d{2}-\d{2}$/.test(texto)) return '—';
      const partes = texto.split('-');
      return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    function tituloDinheiro(valor) {
      return Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function tituloNatureza(valor) {
      return String(valor || '') === 'Saida' ? 'Saída' : 'Entrada';
    }

    function renderizarLinhaListBase(item) {
      const id = Number(item.tit_id || 0);
      const nome = String(item.tit_nome || 'Título sem nome');
      const nomeComId = `${id} • ${nome}`;
      const linkNome = tituloPodeAtualizar
        ? `<a href="${tituloUrlForm}/${id}" class="text-phsa fw-semibold">${tituloEscape(nomeComId)}</a>`
        : `<span class="fw-semibold">${tituloEscape(nomeComId)}</span>`;
      const natureza = String(item.tit_natureza || '');
      const naturezaClasse = natureza === 'Entrada' ? 'success' : 'danger';
      const naturezaIcone = natureza === 'Entrada' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
      const responsavel = item.tit_responsavel || null;
      const documento = responsavel ? String(responsavel.documentoMascara || responsavel.documento || '').trim() : '';
      const celular = responsavel ? String(responsavel.celularMascara || responsavel.celular || '').trim() : '';
      const celularNumeros = responsavel ? String(responsavel.celular || '').replace(/\D/g, '') : '';
      const urlResponsavel = `${tituloUrlFornecedor}/${Number(responsavel && responsavel.id || 0)}`;
      const nomeResponsavel = responsavel && responsavel.nome ? `<a href="${urlResponsavel}" target="_blank" rel="noopener" class="text-phsa fw-semibold">${tituloEscape(responsavel.nome)}</a>` : '—';
      const responsavelHtml = responsavel && responsavel.nome
        ? `<div class="text-truncate" style="max-width:240px">${nomeResponsavel}</div><div class="small text-secondary">Fornecedor</div>${documento ? `<div class="small text-secondary mt-1" style="font-size:.72rem;line-height:1.15"><button type="button" class="btn p-0 text-phsa js-copiar-responsavel" style="font-size:inherit" data-valor="${tituloEscape(responsavel.documento || '')}" data-campo="Documento" title="Copiar documento">${tituloEscape(documento)} <i class="fa-regular fa-copy ms-1"></i></button></div>` : ''}${celular ? `<div class="small text-secondary" style="font-size:.72rem;line-height:1.15"><button type="button" class="btn p-0 text-secondary js-copiar-responsavel" style="font-size:inherit" data-valor="${tituloEscape(responsavel.celular || '')}" data-campo="Celular" title="Copiar celular">${tituloEscape(celular)} <i class="fa-regular fa-copy ms-1"></i></button>${celularNumeros ? ` <a href="https://wa.me/${celularNumeros}" target="_blank" rel="noopener" class="text-success ms-1" title="Abrir no WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>` : ''}</div>` : ''}`
        : '<span class="text-muted">—</span>';
      const status = `<span class="badge bg-${tituloEscape(item.tit_statusclass || 'secondary')}"><i class="fa-solid fa-flag me-1"></i>${tituloEscape(item.tit_status || '—')}</span>`;
      const statusFinanceiro = `<span class="badge bg-${tituloEscape(item.tit_statusfinanceiroclass || 'secondary')}"><i class="fa-solid fa-coins me-1"></i>${tituloEscape(item.tit_statusfinanceiro || '—')}</span>`;
      const statusUnificado = `<div class="d-flex flex-column align-items-start gap-1">${status}${statusFinanceiro}</div>`;
      const datasHtml = `<div class="text-nowrap text-center"><div><div class="small text-secondary">Referência:</div><div>${tituloData(item.tit_referencia)}</div></div><div class="mt-1"><div class="small text-secondary">Vencimento:</div><div>${tituloData(item.tit_dtvencimento)}</div></div></div>`;
      const valoresHtml = `<div class="d-flex justify-content-center align-items-center gap-3 text-nowrap"><div class="text-end"><div class="small text-secondary">Total</div><div class="fw-semibold">R$ ${tituloDinheiro(item.tit_valortotal)}</div></div><div class="border-start ps-3 text-end"><div class="small text-secondary">Movimentado</div><div>R$ ${tituloDinheiro(item.tit_valormovimentado)}</div></div></div>`;
      const acao = tituloPodeAtualizar
        ? `<a href="${tituloUrlForm}/${id}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Abrir título" aria-label="Abrir título"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>`
        : '<span class="text-muted">—</span>';

      return `<tr>
        <td><div>${linkNome}</div><div class="small text-${naturezaClasse} text-truncate" style="max-width:260px" title="${tituloEscape(item.tit_pct_nome || 'Plano de conta não informado')}"><i class="fa-solid ${naturezaIcone} me-1"></i>${tituloEscape(item.tit_pct_nome || 'Plano de conta não informado')}</div>${item.tit_descricao ? `<div class="small text-muted text-truncate" style="max-width:260px" title="${tituloEscape(item.tit_descricao)}">${tituloEscape(item.tit_descricao)}</div>` : ''}</td>
        <td>${responsavelHtml}</td>
        <td class="colSecundaria">${datasHtml}</td>
        <td class="text-center text-nowrap">${statusUnificado}</td>
        <td class="colSecundaria text-center text-${naturezaClasse} text-nowrap"><i class="fas ${naturezaIcone} me-1"></i>${tituloEscape(tituloNatureza(natureza))}</td>
        <td>${valoresHtml}</td>
        <td class="text-end text-nowrap">${acao}</td>
      </tr>`;
    }

    $(document).off('click.tituloResponsavel').on('click.tituloResponsavel', '.js-copiar-responsavel', function () {
      if (typeof fgbCopiarParaAreaDeTransferencia === 'function') {
        fgbCopiarParaAreaDeTransferencia($(this).data('valor') || '', $(this).data('campo') || 'Texto');
      }
    });
  </script>
<?php $this->end(); ?>
