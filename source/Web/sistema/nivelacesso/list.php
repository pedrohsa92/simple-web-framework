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
    'nomeArquivo' => $nomeArquivo
  ]);
?>

<?php $this->start("listBase_pesquisaPersonalizada"); ?>
  <div class="row">
    <div class="col-12 col-md-3 mb-3">
      <div class="form-floating">
        <select id="nva_escopo" name="nva_escopo" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="Projeto">Projeto</option>
          <option value="Organizacao">Organização</option>
          <option value="Empresa">Empresa</option>
        </select>
        <label for="nva_escopo">Escopo</label>
      </div>
    </div>

    <div class="col-12 col-md-3 mb-3">
      <div class="form-floating">
        <select id="nva_status" name="nva_status" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="Ativo">Ativo</option>
          <option value="Inativo">Inativo</option>
        </select>
        <label for="nva_status">Status</label>
      </div>
    </div>

    <div class="col-12 col-md-3 mb-3">
      <div class="form-floating">
        <select id="nva_imutavel" name="nva_imutavel" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="1">Sim</option>
          <option value="0">Não</option>
        </select>
        <label for="nva_imutavel">Imutável</label>
      </div>
    </div>

    <div class="col-12 col-md-3 mb-3">
      <div class="form-floating">
        <select id="nva_padrao_inicial" name="nva_padrao_inicial" class="form-control input-phsa-focus input-phsa-hover">
          <option value="">Todos</option>
          <option value="1">Sim</option>
          <option value="0">Não</option>
        </select>
        <label for="nva_padrao_inicial">Padrão inicial</label>
      </div>
    </div>
  </div>
<?php $this->end(); ?>

<?php $this->start("listBase_camposOrdenacao"); ?>
  <option value="nva_id">Número de identificação</option>
  <option value="nva_nome">Nome</option>
  <option value="nva_escopo">Escopo</option>
  <option value="nva_status">Status</option>
  <option value="nva_qtdusuario">Quantidade de usuários</option>
<?php $this->end(); ?>

<?php $this->start("listBase_resultado"); ?>
  <div class="table-responsive">
    <table id="tableList" class="table table-hover align-middle">
      <thead>
        <tr>
          <th class="colPrimaria">Nível de acesso</th>
          <th class="colPrimaria">Escopo</th>
          <th class="colPrimaria">Status</th>
          <th class="colPrimaria text-center">Usuários</th>
          <th class="colSecundaria text-center">Padrão inicial</th>
          <th class="colSecundaria text-center">Imutável</th>
          <th class="colSecundaria">Datas</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>

      <tbody id="tbodyListBase">
        <tr>
          <td colspan="100" class="text-center text-muted py-4">
            Aguardando pesquisa...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
<?php $this->end(); ?>

<?php $this->start("listBase_script"); ?>
  <script>
    const nivelAcessoPodeAtualizar = <?= !empty($usuarioLogado->permissoes->{'nivelacesso.atualizar'}) ? 'true' : 'false' ?>;
    const nivelAcessoUrlForm = '<?= Helpers::url('nivelacesso/form') ?>';

    function renderizarLinhaListBase(item, index) {
      let nvaId = parseInt(item.nva_id || 0);
      let nvaNome = String(item.nva_nome || '');
      let nvaDescricao = String(item.nva_descricao || '');
      let nvaEscopo = String(item.nva_escopo || '');
      let nvaStatus = String(item.nva_status || '');
      let nvaStatusClass = String(item.nva_statusclass || getStatusClassNivelAcesso(nvaStatus));
      let qtdUsuario = parseInt(item.nva_qtdusuario || item.qtdUsuario || item.qntUsuario || item.qnt_usuario || 0);
      let padraoInicial = parseInt(item.nva_padrao_inicial || 0) === 1;
      let imutavel = parseInt(item.nva_imutavel || 0) === 1;

      return `
        <tr title="ID: ${nvaId}" class="text-align-top text-truncate">
          <td class="colPrimaria">
            <div class="fw-semibold">
              ${
                nivelAcessoPodeAtualizar
                  ? `<a href="${nivelAcessoUrlForm}/${nvaId}" class="text-phsa" title="Editar nível de acesso">
                      ${fgbEscapeHtml(nvaNome)}
                    </a>`
                  : `${fgbEscapeHtml(nvaNome)}`
              }
            </div>

            <div class="text-muted small">
              ${nvaDescricao ? fgbEscapeHtml(nvaDescricao) : 'Sem descrição'}
            </div>
          </td>

          <td class="colPrimaria">
            <span class="badge bg-secondary">
              ${fgbEscapeHtml(getEscopoLabelNivelAcesso(nvaEscopo))}
            </span>
          </td>

          <td class="colPrimaria">
            <span class="badge bg-${fgbEscapeHtml(nvaStatusClass)}">
              ${fgbEscapeHtml(nvaStatus)}
            </span>
          </td>

          <td class="colPrimaria text-center">
            ${qtdUsuario}
          </td>

          <td class="colSecundaria text-center">
            ${
              padraoInicial
                ? '<span class="badge bg-success">Sim</span>'
                : '<span class="text-muted">Não</span>'
            }
          </td>

          <td class="colSecundaria text-center">
            ${
              imutavel
                ? '<span class="badge bg-dark">Sim</span>'
                : '<span class="text-muted">Não</span>'
            }
          </td>

          <td class="colSecundaria">
            <div class="text-muted small">
              Cadastro: ${fgbEscapeHtml(fgbFormatarDataHoraUsaParaBr(item.nva_dtcad) || '')}<br>
              Alterado: ${fgbEscapeHtml(fgbFormatarDataHoraUsaParaBr(item.nva_dtalter) || '')}
            </div>
          </td>

          <td class="text-end">
            ${
              nivelAcessoPodeAtualizar
                ? `<a href="${nivelAcessoUrlForm}/${nvaId}" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Editar nível de acesso">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>`
                : `<span class="text-muted small">Sem ação</span>`
            }
          </td>
        </tr>
      `;
    }

    function getEscopoLabelNivelAcesso(escopo) {
      switch (String(escopo || '')) {
        case 'Projeto':
          return 'Projeto';

        case 'Organizacao':
          return 'Organização';

        case 'Empresa':
          return 'Empresa';

        default:
          return 'Não definido';
      }
    }

    function getStatusClassNivelAcesso(status) {
      switch (String(status || '')) {
        case 'Ativo':
          return 'success';

        case 'Inativo':
          return 'danger';

        default:
          return 'secondary';
      }
    }
  </script>
<?php $this->end(); ?>