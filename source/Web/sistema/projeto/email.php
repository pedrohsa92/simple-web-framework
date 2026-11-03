<?php
  $this->layout('sistema::sistema');
  $podeAtualizar = !empty($podeAtualizar);
  $emailData = ($emails && $emails->status) ? ($emails->data ?? []) : [];
  $email = $email ?? null;
  $esc = static fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>

<?php if ($email === null): ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
          <h4 class="m-0">Templates de e-mail</h4>
          <div class="text-muted small mt-1">Gerencie os templates de comunicação usados pelo LevHub.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= Helpers::url('projeto/email') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Hub de e-mails</a>
          <?php if ($podeAtualizar): ?>
            <a href="<?= Helpers::url('projeto/email/template/novo') ?>" class="bto bto-phsa">
              <i class="fa-solid fa-plus"></i>&nbsp;Novo template
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-12">
          <div class="form-floating">
            <input id="pesquisaEmailProjeto" type="search" class="form-control input-phsa-focus input-phsa-hover" placeholder="Pesquisar">
            <label for="pesquisaEmailProjeto">Pesquisar por nome, assunto, template ou status</label>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabelaEmailsProjeto">
          <thead><tr><th>Nome</th><th>Template do sistema</th><th>Assunto</th><th>Remetente</th><th>Status</th><th>Alteração</th><th class="text-end">Ações</th></tr></thead>
          <tbody id="tbodyEmailsProjeto">
            <?php foreach ($emailData as $item): ?>
              <tr class="linhaEmailProjeto" data-busca="<?= $esc(mb_strtolower(implode(' ', [$item->emt_nome ?? '', $item->emt_templateprojeto ?? '', $item->emt_title ?? '', $item->emt_status ?? '']))) ?>">
                <td><a class="text-phsa fw-semibold" href="<?= Helpers::url('projeto/email/template/' . (int) $item->emt_id) ?>"><?= $esc($item->emt_nome) ?></a></td>
                <td><?= $item->emt_templateprojeto ? '<code>' . $esc($item->emt_templateprojeto) . '</code>' : '<span class="text-muted">Personalizado</span>' ?></td>
                <td><?= $esc($item->emt_title) ?></td>
                <td><div><?= $esc($item->emt_remetentenome ?: '-') ?></div><div class="small text-muted"><?= $esc($item->emt_remetenteemail ?: '-') ?></div></td>
                <td><span class="badge bg-<?= $esc($item->emt_statusclass ?: 'secondary') ?>"><?= $esc($item->emt_status) ?></span></td>
                <td><?= empty($item->emt_dtalter) ? '-' : date('d/m/Y H:i', strtotime($item->emt_dtalter)) ?></td>
                <td class="text-end text-nowrap">
                  <a href="<?= Helpers::url('projeto/email/template/' . (int) $item->emt_id) ?>" class="bto btn-sm bto-phsa-light" title="Editar template"><i class="fa-solid fa-pen-to-square"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($emailData)): ?><tr><td colspan="7" class="text-center text-muted py-4">Nenhum template de e-mail encontrado.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php $this->push('script'); ?>
    <script>
      $(function () {
        $('#pesquisaEmailProjeto').on('input', function () {
          const termo = String($(this).val() || '').toLowerCase().trim();
          $('.linhaEmailProjeto').each(function () { $(this).toggle(!termo || String($(this).data('busca') || '').includes(termo)); });
        });

      });
    </script>
  <?php $this->end(); ?>
<?php else: ?>
  <?php
    $logsEmail = json_decode($email->emt_log ?? '[]');
    if (!is_array($logsEmail)) $logsEmail = [];
    $gruposSelecionados = json_decode($email->emt_palavrachave ?? '[]', true);
    $gruposSelecionados = is_array($gruposSelecionados) ? array_values($gruposSelecionados) : [];
    $palavrasJson = json_encode($palavrasChavesAtivas ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $catalogoJson = json_encode($catalogoPalavrasChaves ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
  ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
          <h4 class="m-0"><?= $email->emt_id ? $esc($email->emt_nome) : 'Novo template de e-mail' ?></h4>
          <div class="text-muted small mt-1">Configure o remetente, a mensagem e as palavras-chave disponíveis.</div>
        </div>
        <a href="<?= Helpers::url('projeto/email/template') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar para templates</a>
      </div>

      <header class="phsaScrollHeader">
        <nav class="phsaScrollNav" data-menu="tabEmailProjeto">
          <ul class="phsaScrollList">
            <li class="phsaScrollItem active" data-target="cadastroEmail"><i class="fa-solid fa-id-card"></i>&nbsp;Cadastro</li>
            <li class="phsaScrollItem" data-target="historicoEmail"><i class="fa-solid fa-clock"></i>&nbsp;Histórico</li>
            <li class="phsaScrollItem" data-target="palavrasEmail"><i class="fa-solid fa-code"></i>&nbsp;Palavra-chave</li>
          </ul>
        </nav>
      </header>

      <form id="formEmailProjeto" class="needs-validation" novalidate autocomplete="off">
        <input type="hidden" name="emt_id" value="<?= (int) ($email->emt_id ?? 0) ?>">
        <input type="hidden" id="emt_palavrachave" name="emt_palavrachave" value="<?= $esc(json_encode($gruposSelecionados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">

        <div class="phsaTabContent phsaTabSubContent">
          <div class="phsaTabItem active" id="cadastroEmail">
            <header class="phsaScrollHeader">
              <nav class="phsaScrollNav" data-menu="subtabEmailProjeto">
                <ul class="phsaScrollList">
                  <li class="phsaScrollItem active" data-target="dadosBasicosEmail"><i class="fa-solid fa-sliders"></i>&nbsp;Dados básicos</li>
                  <li class="phsaScrollItem" data-target="mensagemEmail"><i class="fa-solid fa-message"></i>&nbsp;Mensagem</li>
                </ul>
              </nav>
            </header>

            <div class="phsaTabContent phsaTabSubContent">
              <div class="phsaTabItem active" id="dadosBasicosEmail">
                <div class="row g-3 p-3">
                  <div class="col-12 col-lg-8"><div class="form-floating"><input name="emt_nome" id="emt_nome" class="form-control" value="<?= $esc($email->emt_nome ?? '') ?>" required <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_nome">Nome *</label><div class="invalid-feedback">Informe o nome do template.</div></div></div>
                  <div class="col-12 col-lg-4"><div class="form-floating"><select name="emt_status" id="emt_status" class="form-select" required <?= !$podeAtualizar ? 'disabled' : '' ?>><option value="Ativo" <?= ($email->emt_status ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option><option value="Inativo" <?= ($email->emt_status ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option></select><label for="emt_status">Status</label></div></div>
                  <div class="col-12 col-lg-6"><div class="form-floating"><input name="emt_host" id="emt_host" class="form-control" value="<?= $esc($email->emt_host ?? '') ?>" <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_host">Host SMTP</label></div></div>
                  <div class="col-12 col-lg-6"><div class="form-floating"><input name="emt_port" id="emt_port" type="number" min="1" max="65535" class="form-control" value="<?= (int) ($email->emt_port ?? 587) ?>" <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_port">Porta</label></div></div>
                  <div class="col-12 col-lg-6"><div class="form-floating"><input name="emt_user" id="emt_user" class="form-control" value="<?= $esc($email->emt_user ?? '') ?>" <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_user">Usuário SMTP</label></div></div>
                  <div class="col-12 col-lg-6"><div class="form-floating"><input name="emt_pass" id="emt_pass" type="password" class="form-control" value="" placeholder=" " <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_pass">Senha SMTP <?= $email->emt_id ? '(vazia mantém a atual)' : '' ?></label></div></div>
                  <div class="col-12 col-lg-6"><div class="form-floating"><input name="emt_remetentenome" id="emt_remetentenome" class="form-control" value="<?= $esc($email->emt_remetentenome ?? '') ?>" <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_remetentenome">Nome do remetente</label></div></div>
                  <div class="col-12 col-lg-6"><div class="form-floating"><input name="emt_remetenteemail" id="emt_remetenteemail" type="email" class="form-control" value="<?= $esc($email->emt_remetenteemail ?? '') ?>" <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_remetenteemail">E-mail do remetente</label></div></div>
                  <div class="col-12"><div class="form-floating"><input name="emt_replyto" id="emt_replyto" class="form-control" value="<?= $esc($email->emt_replyto ?? '') ?>" <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_replyto">Reply-to</label></div></div>
                  <div class="col-12"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Template do sistema</div><div class="phsaFakeInputValue"><?= $email->emt_templateprojeto ? '<code>' . $esc($email->emt_templateprojeto) . '</code>' : '<span class="text-muted">Personalizado</span>' ?></div></div></div>
                </div>
              </div>

              <div class="phsaTabItem" id="mensagemEmail">
                <div class="row g-3 p-3">
                  <div class="col-12"><div class="form-floating"><input name="emt_title" id="emt_title" class="form-control" value="<?= $esc($email->emt_title ?? '') ?>" required <?= !$podeAtualizar ? 'readonly' : '' ?>><label for="emt_title">Assunto do e-mail *</label><div class="invalid-feedback">Informe o assunto.</div></div></div>
                  <div class="col-12">
                    <label for="emt_conteudo" class="form-label">Conteúdo da mensagem *</label>
                    <div class="position-relative">
                      <textarea name="emt_conteudo" id="emt_conteudo" class="form-control" rows="18" required <?= !$podeAtualizar ? 'readonly' : '' ?>><?= $esc($email->emt_conteudo ?? '') ?></textarea>
                      <div id="sugestoesPalavrasEmail" class="list-group shadow-sm position-absolute d-none" style="z-index: 20; left: 0; right: 0; max-height: 260px; overflow-y: auto;"></div>
                    </div>
                    <div class="form-text">Aceita HTML. Digite <code>{us</code> para encontrar palavras-chave autorizadas ao template.</div>
                    <div class="invalid-feedback">Informe o conteúdo da mensagem.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="phsaTabItem" id="historicoEmail">
            <?php
              $historicoRegistro = $email;
              $historicoPrefixo = 'emt_';
              $historicoId = (int) ($email->emt_id ?? 0);
              $historicoStatus = (string) ($email->emt_status ?? '');
              $historicoStatusClass = (string) ($email->emt_statusclass ?? 'secondary');
              $historicoLogs = $logsEmail;
              include __DIR__ . '/../historicoPadrao.php';
            ?>
          </div>

          <div class="phsaTabItem" id="palavrasEmail">
            <div class="p-3">
              <div class="alert alert-light border"><i class="fa-solid fa-circle-info text-phsa"></i>&nbsp;Selecione os grupos de palavras-chave que este template poderá interpretar. A mensagem só sugerirá palavras dos grupos selecionados.</div>
              <?php if (!$email->emt_id): ?>
                <div class="text-muted">Salve o template antes de definir as palavras-chave disponíveis.</div>
              <?php else: ?>
                <div class="row g-3">
                  <?php foreach (($catalogoPalavrasChaves ?? []) as $grupo => $config): ?>
                    <div class="col-12 col-md-6 col-xl-3"><label class="border rounded p-3 w-100 h-100"><input type="checkbox" class="form-check-input me-2 checkboxGrupoEmail" value="<?= $esc($grupo) ?>" <?= in_array($grupo, $gruposSelecionados, true) ? 'checked' : '' ?> <?= !$podeAtualizar ? 'disabled' : '' ?>><?= $esc($config['label'] ?? $grupo) ?><div class="small text-muted mt-1"><?= count($config['colunas'] ?? []) ?> campos disponíveis</div></label></div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </form>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <?php if (!empty($email->emt_id)): ?><button type="button" id="btnVisualizarMensagemEmail" class="bto bto-phsa-light"><i class="fa-solid fa-eye"></i>&nbsp;Visualizar mensagem</button><button type="button" id="btnTestarConexaoEmail" class="bto bto-phsa-light"><i class="fa-solid fa-plug"></i>&nbsp;Testar conexão</button><?php endif; ?>
        <?php if ($podeAtualizar): ?><button type="submit" form="formEmailProjeto" class="bto bto-phsa"><i class="fa-solid fa-save"></i>&nbsp;Salvar template</button><?php endif; ?>
      </div>
    </div>
  </div>

  <?php $this->push('script'); ?>
    <script>
      $(function () {
        const palavrasChavesAtivas = <?= $palavrasJson ?: '[]' ?>;
        const catalogoPalavrasChaves = <?= $catalogoJson ?: '{}' ?>;
        const podeAtualizar = <?= $podeAtualizar ? 'true' : 'false' ?>;
        let gruposPermitidos = <?= json_encode($gruposSelecionados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]' ?>;
        const $form = $('#formEmailProjeto');
        const $campoMensagem = $('#emt_conteudo');
        const $sugestoes = $('#sugestoesPalavrasEmail');
        let editorEmail = null;
        let sugestaoAtual = null;

        if (typeof tinymce !== 'undefined' && $campoMensagem.length) {
          tinymce.init({
            selector: '#emt_conteudo',
            plugins: 'link lists table code',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table | removeformat code',
            menubar: false,
            branding: false,
            promotion: false,
            height: 520,
            readonly: <?= !$podeAtualizar ? 'true' : 'false' ?>,
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; color: #263957; padding: 12px; }',
            setup: function (editor) {
              editorEmail = editor;
              editor.on('init', function () { posicionarSugestoes(); });
              editor.on('keyup input click', function () { mostrarSugestoes(editor); });
              editor.on('keydown', function (event) {
                if (event.key === 'Escape') esconderSugestoes();
                if (event.key !== 'Enter' || $sugestoes.hasClass('d-none')) return;
                const $item = $sugestoes.find('.itemSugestaoPalavraEmail').first();
                if ($item.length) {
                  event.preventDefault();
                  completarPalavra(editor, $item.data('palavra'));
                }
              });
            }
          });
        }

        $('#btnTestarConexaoEmail').on('click', function () {
          const $button = $(this);
          const id = Number($form.find('[name="emt_id"]').val() || 0);
          if (!id) return;
          $button.prop('disabled', true);
          swAguarde({ title: 'Testando conexão...', text: 'Aguarde enquanto validamos o acesso ao servidor SMTP.', timer: null });
          $.ajax({ url: '<?= Helpers::url('projeto/testarConexaoEmail') ?>', type: 'POST', dataType: 'json', data: { emt_id: id } })
            .done(function (response) { swResponse(response).then(function () { $button.prop('disabled', false); }); })
            .fail(function () { $button.prop('disabled', false); swError('Não foi possível testar a conexão SMTP.'); });
        });

        function escapar(valor) { return $('<div>').text(valor ?? '').html(); }
        function atualizarGrupos() {
          gruposPermitidos = $('.checkboxGrupoEmail:checked').map(function () { return $(this).val(); }).get();
          $('#emt_palavrachave').val(JSON.stringify(gruposPermitidos));
        }
        $('.checkboxGrupoEmail').on('change', atualizarGrupos);

        function posicionarSugestoes() {
          const alturaEditor = $('.tox-tinymce').outerHeight() || 0;
          $sugestoes.css('top', (alturaEditor + 6) + 'px');
        }
        function esconderSugestoes() { sugestaoAtual = null; $sugestoes.addClass('d-none').empty(); }
        function obterTokenAtual(editor) {
          if (!editor || !gruposPermitidos.length) return null;
          const range = editor.selection.getRng();
          let node = range.startContainer;
          let offset = range.startOffset;
          if (node.nodeType !== 3) {
            const child = node.childNodes[Math.max(0, offset - 1)];
            if (!child || child.nodeType !== 3) return null;
            node = child;
            offset = node.textContent.length;
          }
          const antes = node.textContent.slice(0, offset);
          const encontrado = antes.match(/\{([a-zA-Z0-9_.]*)$/);
          if (!encontrado || encontrado[1].length < 2) return null;
          return { node: node, start: offset - encontrado[0].length, end: offset, termo: encontrado[1] };
        }
        function mostrarSugestoes(editor) {
          const token = obterTokenAtual(editor);
          if (!token) return esconderSugestoes();
          sugestaoAtual = token;
          const termo = token.termo.toLowerCase();
          const itens = palavrasChavesAtivas.filter(function (item) {
            return gruposPermitidos.includes(item.pch_grupo) && String(item.pch_palavra || '').toLowerCase().includes(termo);
          }).slice(0, 10);
          if (!itens.length) return esconderSugestoes();
          $sugestoes.html(itens.map(function (item) {
            return `<button type="button" class="list-group-item list-group-item-action itemSugestaoPalavraEmail" data-palavra="${escapar(item.pch_palavra)}"><span class="text-phsa fw-semibold">{${escapar(item.pch_palavra)}}</span><span class="small text-muted d-block">${escapar(item.pch_descricao || catalogoPalavrasChaves[item.pch_grupo]?.label || '')}</span></button>`;
          }).join('')).removeClass('d-none');
          posicionarSugestoes();
        }
        function completarPalavra(editor, palavra) {
          const token = obterTokenAtual(editor) || sugestaoAtual;
          if (!token) return esconderSugestoes();
          const range = editor.dom.createRng();
          range.setStart(token.node, token.start);
          range.setEnd(token.node, token.end);
          editor.selection.setRng(range);
          editor.insertContent(`{${palavra}}`);
          editor.focus();
          esconderSugestoes();
        }
        $sugestoes.on('mousedown', '.itemSugestaoPalavraEmail', function (event) {
          event.preventDefault();
          if (editorEmail) completarPalavra(editorEmail, $(this).data('palavra'));
        });
        $(document).on('click.emailSugestoes', function (event) { if (!$(event.target).closest('#emt_conteudo, #sugestoesPalavrasEmail, .tox-tinymce').length) esconderSugestoes(); });

        $('#btnVisualizarMensagemEmail').on('click', function () {
          if (typeof tinymce !== 'undefined') tinymce.triggerSave();
          let mensagem = editorEmail ? editorEmail.getContent() : String($campoMensagem.val() || '');
          let assunto = String($('#emt_title').val() || 'Mensagem do LevHub');
          const amostras = {
            'projeto.nome': 'LevHub',
            'projeto.titulo': 'LevHub - Gestão para igrejas',
            'projeto.dominio': 'levhub.com.br',
            'projeto.url': window.location.origin,
            'organizacao.nome': 'Igreja Exemplo',
            'organizacao.nomefantasia': 'Igreja Exemplo',
            'empresa.nome': 'Igreja Exemplo',
            'empresa.nomefantasia': 'Igreja Exemplo',
            'usuario.nome': 'Pedro HSA',
            'usuario.email': 'usuario@exemplo.com.br',
            'usuario.celular': '(31) 99999-9999'
          };
          function substituirAmostras(texto) {
            return texto.replace(/\{([a-zA-Z0-9_.]+)\}/g, function (completo, chave) {
              return Object.prototype.hasOwnProperty.call(amostras, chave) ? amostras[chave] : completo;
            });
          }
          mensagem = substituirAmostras(mensagem).replace(/<script[\s\S]*?<\/script>/gi, '');
          assunto = substituirAmostras(assunto);
          SimpleModal.mostrar({
            titulo: 'Visualizar mensagem',
            tamanho: 'modal-xl',
            corpo: `<div class="border rounded bg-white shadow-sm overflow-hidden"><div class="border-bottom bg-light px-3 py-2"><div class="small text-muted">Assunto</div><div class="fw-semibold">${escapar(assunto)}</div></div><div class="p-4" style="min-height: 280px;">${mensagem || '<p class="text-muted">A mensagem está vazia.</p>'}</div></div>`,
            rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-times"></i>&nbsp;Fechar</button>'
          });
        });

        $form.on('submit', function (event) {
          event.preventDefault(); event.stopPropagation();
          if (typeof tinymce !== 'undefined') tinymce.triggerSave();
          if (!podeAtualizar || !this.checkValidity()) { $form.addClass('was-validated'); return; }
          atualizarGrupos();
          const $button = $('button[form="formEmailProjeto"]');
          $button.prop('disabled', true);
          swAguarde({ title: 'Salvando template...', text: 'Aguarde enquanto processamos os dados.', timer: null });
          $.ajax({ url: '<?= Helpers::url('projeto/salvarEmail') ?>', type: 'POST', dataType: 'json', data: $form.serialize() })
            .done(function (response) {
              swResponse(response).then(function () {
                if (response.status) {
                  const emailId = Number($form.find('[name="emt_id"]').val() || response.lastInsertId || 0);
                  window.location.href = emailId > 0
                    ? '<?= Helpers::url('projeto/email/template') ?>/' + emailId
                    : '<?= Helpers::url('projeto/email/template') ?>';
                  return;
                }
                $button.prop('disabled', false);
              });
            })
            .fail(function () { $button.prop('disabled', false); swError('Não foi possível salvar o template.'); });
        });
      });
    </script>
  <?php $this->end(); ?>
<?php endif; ?>
