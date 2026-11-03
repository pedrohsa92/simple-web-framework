<?php
  $this->layout("sistema::listBase", [
    'getInfo' => $getInfo,
    'titulo' => $titulo,
    'descricao' => $descricao,
    'urlCadastrar' => $urlCadastrar,
    'urlPesquisar' => $urlPesquisar,
    'ocultarCadastrar' => $ocultarCadastrar,
    'labelPesquisaSimples' => $labelPesquisaSimples,
    'labelTotal' => $labelTotal,
    'nomeArquivo' => $nomeArquivo,
    'prefixo' => $prefixo
  ]);
?>

<?php $this->start("listBase_pesquisaPersonalizada"); ?>
  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="form-floating">
        <select id="for_status" name="for_status" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="Ativo">Ativo</option>
          <option value="Inativo">Inativo</option>
        </select>
        <label for="for_status">Status</label>
      </div>
    </div>
  </div>
<?php $this->end(); ?>

<?php $this->start("listBase_camposFiltroData"); ?>
  <option value="for_dtnasc">Data de nascimento</option>
<?php $this->end(); ?>

<?php $this->start("listBase_camposOrdenacao"); ?>
  <option value="for_id">Número de identificação</option>
  <option value="for_chave">Chave</option>
  <option value="for_cnpjcpf">Documento</option>
  <option value="for_razaosocial">Razão social</option>
  <option value="for_nomefantasia">Nome fantasia</option>
  <option value="for_status">Status</option>
<?php $this->end(); ?>

<?php $this->start("listBase_resultado"); ?>
  <div class="table-responsive">
    <table id="tableList" class="table table-hover align-middle">
      <thead>
        <tr>
          <th class="colPrimaria">Fornecedor</th>
          <th class="colPrimaria">Contato</th>
          <th class="colPrimaria">Documento</th>
          <th class="colPrimaria">Status</th>
          <th class="colSecundaria">Chave</th>
          <th class="colSecundaria">Cadastro</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody id="tbodyListBase">
        <tr><td colspan="100" class="text-center text-muted py-4">Aguardando pesquisa...</td></tr>
      </tbody>
    </table>
  </div>
<?php $this->end(); ?>

<?php $this->start("listBase_script"); ?>
  <script>
    const fornecedorPodeAtualizar = <?= !empty($usuarioLogado->permissoes->{'fornecedor.atualizar'}) ? 'true' : 'false' ?>;
    const fornecedorUrlForm = '<?= Helpers::url('fornecedor/form') ?>';

    function formatarDocumentoFornecedor(documento) {
      const valor = String(documento || '').replace(/\D/g, '');
      if (valor.length === 11) return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
      if (valor.length === 14) return valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
      return String(documento || '');
    }

    function formatarCelularFornecedor(celular) {
      const valor = String(celular || '').replace(/\D/g, '');
      if (valor.length === 11) return valor.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
      if (valor.length === 10) return valor.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
      return String(celular || '');
    }

    function renderizarLinhaListBase(item, index) {
      const id = parseInt(item.for_id || 0);
      const chave = String(item.for_chave || '');
      const nome = String(item.for_razaosocial || item.for_nomefantasia || 'Fornecedor');
      const fantasia = String(item.for_nomefantasia || '');
      const email = String(item.for_email || '');
      const celular = String(item.for_celular || '').replace(/\D/g, '');
      const documento = formatarDocumentoFornecedor(item.for_cnpjcpf);
      const contato = [email, formatarCelularFornecedor(celular)].filter(Boolean);
      const status = String(item.for_status || '');
      const statusClass = String(item.for_statusclass || (status === 'Ativo' ? 'success' : 'danger'));
      const url = `${fornecedorUrlForm}/${id}`;
      const nomeHtml = fornecedorPodeAtualizar
        ? `<a href="${url}" class="text-phsa" title="Editar fornecedor">${fgbEscapeHtml(nome)}</a>`
        : fgbEscapeHtml(nome);

      return `
        <tr title="ID: ${id}-${fgbEscapeHtml(chave)}" class="text-align-top text-truncate">
          <td class="colPrimaria">
            <div class="fw-semibold">${nomeHtml}</div>
            ${fantasia && fantasia !== nome ? `<div class="text-muted small">${fgbEscapeHtml(fantasia)}</div>` : ''}
            <div class="text-muted small">ID ${id}</div>
          </td>
          <td class="colPrimaria">
            ${contato[0]
              ? `<button type="button" class="btn p-0 text-phsa" title="Copiar e-mail" onclick="fgbCopiarParaAreaDeTransferencia('${fgbEscapeHtml(email)}', 'E-mail')">${fgbEscapeHtml(contato[0])}</button>`
              : '<span class="text-muted">Sem e-mail</span>'}
            ${contato[1]
              ? `<div class="text-muted small mt-1"><button type="button" class="btn p-0 text-muted small" title="Copiar celular" onclick="fgbCopiarParaAreaDeTransferencia('${celular}', 'Celular')">${fgbEscapeHtml(contato[1])}</button></div>`
              : ''}
          </td>
          <td class="colPrimaria">
            ${documento
              ? `<button type="button" class="btn p-0 text-phsa" title="Copiar documento" onclick="fgbCopiarParaAreaDeTransferencia('${String(item.for_cnpjcpf || '').replace(/\D/g, '')}', 'Documento')">${fgbEscapeHtml(documento)}</button>`
              : '<span class="text-muted">Sem documento</span>'}
          </td>
          <td class="colPrimaria"><span class="badge bg-${fgbEscapeHtml(statusClass)}">${fgbEscapeHtml(status || 'Não definido')}</span></td>
          <td class="colSecundaria">${fgbEscapeHtml(chave || 'Sem chave')}</td>
          <td class="colSecundaria">${item.for_dtcad ? fgbEscapeHtml(String(item.for_dtcad)) : '-'}</td>
          <td class="text-end">
            ${fornecedorPodeAtualizar
              ? `<a href="${url}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Editar fornecedor"><i class="fa-solid fa-pen-to-square"></i></a>`
              : '<span class="text-muted small">Sem ação</span>'}
          </td>
        </tr>
      `;
    }
  </script>
<?php $this->end(); ?>
