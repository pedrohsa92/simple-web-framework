<?php $this->layout('sistema::sistema'); ?>

<?php if ($plano === null): ?>
  <?php $planosData = ($planos && $planos->status) ? ($planos->data ?? []) : []; ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
          <h4 class="m-0">Planos</h4>
          <div class="text-muted small mt-1">Catálogo de planos disponíveis para as organizações do icliv.</div>
        </div>
        <span class="badge bg-phsa-dark"><?= count($planosData) ?> plano<?= count($planosData) === 1 ? '' : 's' ?></span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Plano</th>
              <th>Tipo</th>
              <th>Ambiente</th>
              <th>Mensal</th>
              <th>Anual</th>
              <th>Status</th>
              <th class="text-end">Ação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($planosData as $item): ?>
              <tr>
                <td>
                  <a href="<?= Helpers::url('projeto/plano/' . (int) $item->pla_id) ?>" class="text-phsa text-decoration-none fw-semibold" title="Editar plano">
                    <?= htmlspecialchars($item->pla_nome ?? '-', ENT_QUOTES, 'UTF-8') ?>
                  </a>
                  <?php $descricaoPlanoLista = trim((string) ($item->pla_descricaoresumida ?? '')); if ($descricaoPlanoLista === '') $descricaoPlanoLista = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($item->pla_descricao ?? '')))); ?>
                  <div class="small text-muted"><?= htmlspecialchars(mb_strlen($descricaoPlanoLista) > 150 ? mb_substr($descricaoPlanoLista, 0, 147) . '...' : ($descricaoPlanoLista ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
                </td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($item->pla_tipo ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars($item->pla_ambiente ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $item->pla_valormensal === null ? '-' : 'R$ ' . number_format((float) $item->pla_valormensal, 2, ',', '.') ?></td>
                <td><?= $item->pla_valoranual === null ? '-' : 'R$ ' . number_format((float) $item->pla_valoranual, 2, ',', '.') ?></td>
                <td><span class="badge bg-<?= htmlspecialchars($item->pla_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item->pla_status ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td class="text-end"><a href="<?= Helpers::url('projeto/plano/' . (int) $item->pla_id) ?>" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Editar plano"><i class="fa-solid fa-pen-to-square"></i></a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($planosData)): ?><tr><td colspan="7" class="text-center text-muted py-4">Nenhum plano encontrado.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php else: ?>
  <?php
    $logsPlano = json_decode($plano->pla_log ?? '[]');
    if (!is_array($logsPlano)) $logsPlano = [];

    $beneficiosPlano = json_decode($plano->pla_beneficios ?? '[]', true);
    if (!is_array($beneficiosPlano)) $beneficiosPlano = [];
    $beneficiosPlano = array_values($beneficiosPlano);

    $tipoPlano = (string) ($plano->pla_tipo ?? 'Comercial');
    $contatosComerciais = json_decode($plano->pla_contatocomercial ?? '[]', true);
    if (!is_array($contatosComerciais)) $contatosComerciais = [];
    $contatosComerciais = array_values(array_filter($contatosComerciais, static fn($contato) => is_array($contato)));

    $podeEditarPlano = $podeAtualizar;
    $controlePlano = $podeEditarPlano ? '' : 'disabled';
    $diasTesteValor = $tipoPlano === 'Teste' ? (int) ($plano->pla_dias_teste ?? 0) : '';
    $comercialControle = $tipoPlano === 'Comercial' && $podeEditarPlano ? '' : 'disabled readonly';
    $testeControle = $tipoPlano === 'Teste' && $podeEditarPlano ? '' : 'disabled readonly';

    $parametroPlano = json_decode((string) ($plano->pla_parametro ?? ''), true);
    if (!is_array($parametroPlano)) $parametroPlano = [];
    $parametroUsuario = is_array($parametroPlano['usuario'] ?? null) ? $parametroPlano['usuario'] : [];
    $parametroEmpresa = is_array($parametroPlano['empresa'] ?? null) ? $parametroPlano['empresa'] : [];
    $parametroNivelAcesso = is_array($parametroPlano['nivelacesso'] ?? null) ? $parametroPlano['nivelacesso'] : [];
    $parametroStorage = is_array($parametroPlano['storage'] ?? null) ? $parametroPlano['storage'] : [];
    $valorLimite = static function (array $grupo, string $chave): int {
      return max(0, (int) ($grupo[$chave] ?? 0));
    };
    $statusParametro = static function (array $grupo): bool {
      return array_key_exists('status', $grupo) ? (bool) $grupo['status'] : true;
    };
  ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
          <h4 class="m-0">Plano: <?= htmlspecialchars($plano->pla_nome ?? '-', ENT_QUOTES, 'UTF-8') ?></h4>
          <div class="text-muted small mt-1">Cadastro comercial, configuração e histórico do plano selecionado.</div>
        </div>
        <a href="<?= Helpers::url('projeto/plano') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar para planos</a>
      </div>

      <header class="phsaScrollHeader">
        <nav class="phsaScrollNav" data-menu="tabPlano">
          <ul class="phsaScrollList">
            <li class="phsaScrollItem active" data-target="cadastroPlano"><i class="fa-solid fa-id-card"></i>&nbsp;Cadastro</li>
            <li class="phsaScrollItem" data-target="historicoPlano"><i class="fa-solid fa-clock"></i>&nbsp;Histórico</li>
          </ul>
        </nav>
      </header>

      <div class="phsaTabContent phsaTabSubContent">
        <div class="phsaTabItem active" id="cadastroPlano">
          <header class="phsaScrollHeader mb-3">
            <nav class="phsaScrollNav" data-menu="tabCadastroPlano">
              <ul class="phsaScrollList">
                <li class="phsaScrollItem active" data-target="dadosBasicosPlano"><i class="fa-solid fa-tags"></i>&nbsp;Dados básicos</li>
                <li class="phsaScrollItem" data-target="detalhesPlano"><i class="fa-solid fa-file-lines"></i>&nbsp;Detalhes</li>
                <li class="phsaScrollItem" data-target="configuracaoPlano"><i class="fa-solid fa-sliders"></i>&nbsp;Configuração</li>
              </ul>
            </nav>
          </header>

          <div class="phsaTabContent">
            <div class="phsaTabItem active" id="dadosBasicosPlano">
              <form id="formPlanoComercial" class="needs-validation" novalidate>
                <input type="hidden" name="pla_id" value="<?= (int) $plano->pla_id ?>">

                <div class="row">
                  <div class="col-12 col-lg-4 mb-3"><div class="form-floating"><select name="pla_status" class="form-select input-phsa-focus input-phsa-hover" <?= $controlePlano ?>><option value="Ativo" <?= ($plano->pla_status ?? '') === 'Ativo' ? 'selected' : '' ?>>Ativo</option><option value="Inativo" <?= ($plano->pla_status ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option></select><label>Status</label></div></div>
                  <div class="col-12 col-lg-4 mb-3"><div class="form-floating"><select name="pla_tipo" id="pla_tipo" class="form-select input-phsa-focus input-phsa-hover" <?= $controlePlano ?>><option value="Teste" <?= $tipoPlano === 'Teste' ? 'selected' : '' ?>>Teste</option><option value="Comercial" <?= $tipoPlano === 'Comercial' ? 'selected' : '' ?>>Comercial</option><option value="Dedicado" <?= $tipoPlano === 'Dedicado' ? 'selected' : '' ?>>Dedicado</option><option value="Interno" <?= $tipoPlano === 'Interno' ? 'selected' : '' ?>>Interno</option></select><label>Tipo</label></div></div>
                  <div class="col-12 col-lg-4 mb-3"><div class="form-floating"><input name="pla_dias_teste" id="pla_dias_teste" type="number" min="0" value="<?= $diasTesteValor ?>" class="form-control input-phsa-focus input-phsa-hover" data-regra-tipo="Teste" <?= $testeControle ?>><label>Dias de teste</label></div><div class="form-text">Disponível somente para planos do tipo Teste.</div></div>
                </div>

                <div class="row">
                  <div class="col-12 col-lg-6 mb-3"><div class="form-floating"><input name="pla_nome" value="<?= htmlspecialchars($plano->pla_nome ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control input-phsa-focus input-phsa-hover" required maxlength="50" <?= $podeEditarPlano ? '' : 'readonly' ?>><label>Nome do plano</label><div class="invalid-feedback">Informe o nome do plano.</div></div></div>
                  <div class="col-12 col-lg-3 mb-3"><div class="form-floating"><input name="pla_rotulo" value="<?= htmlspecialchars($plano->pla_rotulo ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="80" <?= $podeEditarPlano ? '' : 'readonly' ?>><label>Rótulo comercial</label></div></div>
                  <div class="col-12 col-lg-3 mb-3"><div class="form-floating"><input name="pla_ordem" type="number" min="0" value="<?= (int) ($plano->pla_ordem ?? 0) ?>" class="form-control input-phsa-focus input-phsa-hover" <?= $controlePlano ?>><label>Ordem de exibição</label></div></div>
                </div>

                <div class="row">
                  <div class="col-12 mb-3"><div class="form-floating"><textarea name="pla_descricaoresumida" class="form-control input-phsa-focus input-phsa-hover" style="height: 82px;" maxlength="255" <?= $podeEditarPlano ? '' : 'readonly' ?>><?= htmlspecialchars($plano->pla_descricaoresumida ?? '', ENT_QUOTES, 'UTF-8') ?></textarea><label>Descrição resumida</label></div><div class="form-text">Exibida nos cards e nas listas públicas. Para conteúdo formatado, use a aba Detalhes.</div></div>
                </div>

                <div class="card border-phsa-light box-phsa-shadow-light mb-4">
                  <div class="card-body">
                    <h6 class="text-phsa mb-1">Apresentação da oferta</h6>
                    <div class="small text-muted mb-3">Conteúdo usado no card do plano na landing page e na pesquisa do sistema.</div>
                    <div class="row">
                      <div class="col-12 col-lg-6 mb-3"><div class="form-floating"><input name="pla_selo" value="<?= $tipoPlano === 'Comercial' ? htmlspecialchars($plano->pla_selo ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="80" placeholder="Mais escolhido" data-regra-tipo="Comercial" <?= $comercialControle ?>><label>Selo do plano</label></div><div class="form-text">Aplicável somente a planos Comerciais. Apenas um plano pode possuir selo.</div></div>
                      <div class="col-12 col-lg-6 mb-3"><div class="form-floating"><input name="pla_textobotao" value="<?= htmlspecialchars($plano->pla_textobotao ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="80" <?= $podeEditarPlano ? '' : 'readonly' ?>><label>Texto do botão</label></div></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                      <div><h6 class="text-phsa mb-1">Benefícios do plano</h6><div class="small text-muted">Cadastre de 3 a 30 benefícios curtos para exibição no plano.</div></div>
                      <?php if ($podeEditarPlano): ?><button type="button" id="btnAdicionarBeneficioPlano" class="bto bto-phsa-light" title="Adicionar benefício"><i class="fa-solid fa-plus"></i></button><?php endif; ?>
                    </div>
                    <input type="hidden" name="pla_beneficios" id="pla_beneficios" value="<?= htmlspecialchars(json_encode($beneficiosPlano, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle small mb-0" id="tabelaBeneficiosPlano">
                        <thead><tr><th>#</th><th>Benefício</th><?php if ($podeEditarPlano): ?><th class="text-end">Ações</th><?php endif; ?></tr></thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <div class="card border-phsa-light box-phsa-shadow-light mb-4">
                  <div class="card-body">
                    <h6 class="text-phsa mb-1">Preços comerciais</h6>
                    <div class="small text-muted mb-3">O valor atual é exibido como “por”. O valor original permite apresentar “de ... por ...” em uma campanha.</div>
                    <div class="row">
                      <div class="col-12 col-lg-3 mb-3"><div class="form-floating"><input name="pla_valormensal_original" type="number" step="0.01" min="0" value="<?= $tipoPlano === 'Comercial' && $plano->pla_valormensal_original !== null ? number_format((float) $plano->pla_valormensal_original, 2, '.', '') : '' ?>" class="form-control input-phsa-focus input-phsa-hover" data-regra-tipo="Comercial" <?= $comercialControle ?>><label>Mensal de</label></div></div>
                      <div class="col-12 col-lg-3 mb-3"><div class="form-floating"><input name="pla_valormensal" type="number" step="0.01" min="0" value="<?= $tipoPlano === 'Comercial' && $plano->pla_valormensal !== null ? number_format((float) $plano->pla_valormensal, 2, '.', '') : '' ?>" class="form-control input-phsa-focus input-phsa-hover" data-regra-tipo="Comercial" <?= $comercialControle ?>><label>Mensal por</label></div></div>
                      <div class="col-12 col-lg-3 mb-3"><div class="form-floating"><input name="pla_valoranual_original" type="number" step="0.01" min="0" value="<?= $tipoPlano === 'Comercial' && $plano->pla_valoranual_original !== null ? number_format((float) $plano->pla_valoranual_original, 2, '.', '') : '' ?>" class="form-control input-phsa-focus input-phsa-hover" data-regra-tipo="Comercial" <?= $comercialControle ?>><label>Anual de</label></div></div>
                      <div class="col-12 col-lg-3 mb-3"><div class="form-floating"><input name="pla_valoranual" type="number" step="0.01" min="0" value="<?= $tipoPlano === 'Comercial' && $plano->pla_valoranual !== null ? number_format((float) $plano->pla_valoranual, 2, '.', '') : '' ?>" class="form-control input-phsa-focus input-phsa-hover" data-regra-tipo="Comercial" <?= $comercialControle ?>><label>Anual por</label></div></div>
                    </div>
                  </div>
                </div>

                <div id="blocoContatosComerciais" class="card border-phsa-light box-phsa-shadow-light mb-4" <?= $tipoPlano === 'Dedicado' ? '' : 'style="display:none"' ?>>
                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                      <div><h6 class="text-phsa mb-1">Contatos comerciais</h6><div class="small text-muted">Equipe que será apresentada quando o interessado escolher o plano Dedicado.</div></div>
                      <?php if ($podeEditarPlano): ?><button type="button" id="btnAdicionarContatoComercial" class="bto bto-phsa-light" title="Adicionar contato"><i class="fa-solid fa-plus"></i></button><?php endif; ?>
                    </div>
                    <input type="hidden" name="pla_contatocomercial" id="pla_contatocomercial" value="<?= htmlspecialchars(json_encode($contatosComerciais, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="table-responsive">
                      <table class="table table-hover align-middle small mb-0" id="tabelaContatosComerciais">
                        <thead><tr><th>Nome</th><th>Celular</th><th>E-mail</th><?php if ($podeEditarPlano): ?><th class="text-end">Ações</th><?php endif; ?></tr></thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <?php if ($podeEditarPlano): ?><div class="d-flex justify-content-end"><button type="submit" class="bto bto-phsa"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar dados básicos</button></div><?php endif; ?>
              </form>
            </div>

            <div class="phsaTabItem" id="detalhesPlano">
              <form id="formPlanoDetalhes" class="needs-validation" novalidate>
                <input type="hidden" name="pla_id" value="<?= (int) $plano->pla_id ?>">
                <div class="mb-3">
                  <label class="form-label fw-semibold text-phsa" for="pla_descricao">Detalhes do plano</label>
                  <textarea name="pla_descricao" id="pla_descricao" class="form-control input-phsa-focus input-phsa-hover" style="min-height: 320px;" <?= $podeEditarPlano ? '' : 'readonly' ?>><?= htmlspecialchars($plano->pla_descricao ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                  <div class="form-text">Este conteúdo será exibido ao interessado quando ele clicar em “Saiba mais”.</div>
                </div>
                <?php if ($podeEditarPlano): ?><div class="d-flex justify-content-end"><button type="submit" class="bto bto-phsa"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar detalhes</button></div><?php endif; ?>
              </form>
            </div>

            <div class="phsaTabItem" id="configuracaoPlano">
              <form id="formPlanoConfiguracao" class="needs-validation" novalidate>
                <input type="hidden" name="pla_id" value="<?= (int) $plano->pla_id ?>">
                <div class="alert alert-info small"><i class="fa-solid fa-circle-info me-2"></i>Configure os recursos liberados para o icliv. O valor <strong>0</strong> significa ilimitado.</div>

                <div id="boxParametrosPlano" class="accordion">
                  <div class="accordion-item mb-2 border rounded overflow-hidden">
                    <h2 class="accordion-header" id="headingParametroUsuario">
                      <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParametroUsuario" aria-expanded="true" aria-controls="collapseParametroUsuario"><i class="fa-solid fa-users text-phsa me-2"></i>Usuários</button>
                    </h2>
                    <div id="collapseParametroUsuario" class="accordion-collapse collapse show" aria-labelledby="headingParametroUsuario">
                      <div class="accordion-body">
                        <div class="row align-items-center border rounded p-2 mb-3">
                          <div class="col"><label class="fw-semibold mb-0" for="pla_config_usuario_status">Recurso ativo</label><div class="text-muted small">Permite utilizar o cadastro de usuários.</div></div>
                          <div class="col-auto"><div class="form-check form-switch mb-0"><input id="pla_config_usuario_status" class="form-check-input statusParametroPlano" data-alvo="#camposParametroUsuario" type="checkbox" role="switch" name="pla_config_usuario_status" value="1" <?= $statusParametro($parametroUsuario) ? 'checked' : '' ?> <?= $controlePlano ?>></div></div>
                        </div>
                        <div id="camposParametroUsuario" class="row">
                          <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input name="pla_config_usuario_limite" type="number" min="0" value="<?= $valorLimite($parametroUsuario, 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Total de usuários</label></div></div>
                          <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input name="pla_config_usuario_arquivo" type="number" min="0" value="<?= $valorLimite(is_array($parametroUsuario['arquivo'] ?? null) ? $parametroUsuario['arquivo'] : [], 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Arquivos por usuário</label></div></div>
                          <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input name="pla_config_usuario_contato" type="number" min="0" value="<?= $valorLimite(is_array($parametroUsuario['contato'] ?? null) ? $parametroUsuario['contato'] : [], 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Contatos por usuário</label></div></div>
                          <div class="col-12 col-md-6"><div class="form-floating"><input name="pla_config_usuario_endereco" type="number" min="0" value="<?= $valorLimite(is_array($parametroUsuario['endereco'] ?? null) ? $parametroUsuario['endereco'] : [], 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Endereços por usuário</label></div></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="accordion-item mb-2 border rounded overflow-hidden">
                    <h2 class="accordion-header" id="headingParametroEmpresa">
                      <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParametroEmpresa" aria-expanded="false" aria-controls="collapseParametroEmpresa"><i class="fa-solid fa-building text-phsa me-2"></i>Empresas</button>
                    </h2>
                    <div id="collapseParametroEmpresa" class="accordion-collapse collapse" aria-labelledby="headingParametroEmpresa">
                      <div class="accordion-body">
                        <div class="row align-items-center border rounded p-2 mb-3">
                          <div class="col"><label class="fw-semibold mb-0" for="pla_config_empresa_status">Recurso ativo</label><div class="text-muted small">Permite cadastrar e administrar empresas.</div></div>
                          <div class="col-auto"><div class="form-check form-switch mb-0"><input id="pla_config_empresa_status" class="form-check-input statusParametroPlano" data-alvo="#camposParametroEmpresa" type="checkbox" role="switch" name="pla_config_empresa_status" value="1" <?= $statusParametro($parametroEmpresa) ? 'checked' : '' ?> <?= $controlePlano ?>></div></div>
                        </div>
                        <div id="camposParametroEmpresa" class="row">
                          <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input name="pla_config_empresa_limite" type="number" min="0" value="<?= $valorLimite($parametroEmpresa, 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Total de empresas</label></div></div>
                          <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input name="pla_config_empresa_arquivo" type="number" min="0" value="<?= $valorLimite(is_array($parametroEmpresa['arquivo'] ?? null) ? $parametroEmpresa['arquivo'] : [], 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Arquivos por empresa</label></div></div>
                          <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input name="pla_config_empresa_contato" type="number" min="0" value="<?= $valorLimite(is_array($parametroEmpresa['contato'] ?? null) ? $parametroEmpresa['contato'] : [], 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Contatos por empresa</label></div></div>
                          <div class="col-12 col-md-6"><div class="form-floating"><input name="pla_config_empresa_endereco" type="number" min="0" value="<?= $valorLimite(is_array($parametroEmpresa['endereco'] ?? null) ? $parametroEmpresa['endereco'] : [], 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Endereços por empresa</label></div></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="accordion-item mb-2 border rounded overflow-hidden">
                    <h2 class="accordion-header" id="headingParametroNivelAcesso">
                      <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParametroNivelAcesso" aria-expanded="false" aria-controls="collapseParametroNivelAcesso"><i class="fa-solid fa-user-shield text-phsa me-2"></i>Níveis de acesso</button>
                    </h2>
                    <div id="collapseParametroNivelAcesso" class="accordion-collapse collapse" aria-labelledby="headingParametroNivelAcesso">
                      <div class="accordion-body">
                        <div class="row align-items-center border rounded p-2 mb-3">
                          <div class="col"><label class="fw-semibold mb-0" for="pla_config_nivelacesso_status">Recurso ativo</label><div class="text-muted small">Permite criar e utilizar níveis de acesso.</div></div>
                          <div class="col-auto"><div class="form-check form-switch mb-0"><input id="pla_config_nivelacesso_status" class="form-check-input statusParametroPlano" data-alvo="#camposParametroNivelAcesso" type="checkbox" role="switch" name="pla_config_nivelacesso_status" value="1" <?= $statusParametro($parametroNivelAcesso) ? 'checked' : '' ?> <?= $controlePlano ?>></div></div>
                        </div>
                        <div id="camposParametroNivelAcesso" class="row"><div class="col-12 col-md-6"><div class="form-floating"><input name="pla_config_nivelacesso_limite" type="number" min="0" value="<?= $valorLimite($parametroNivelAcesso, 'limite') ?>" class="form-control input-phsa-focus input-phsa-hover campoParametroPlano" <?= $controlePlano ?>><label>Total de níveis de acesso</label></div></div></div>
                      </div>
                    </div>
                  </div>

                  <div class="accordion-item mb-2 border rounded overflow-hidden">
                    <h2 class="accordion-header" id="headingParametroStorage">
                      <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParametroStorage" aria-expanded="false" aria-controls="collapseParametroStorage"><i class="fa-solid fa-hard-drive text-phsa me-2"></i>Armazenamento</button>
                    </h2>
                    <div id="collapseParametroStorage" class="accordion-collapse collapse" aria-labelledby="headingParametroStorage">
                      <div class="accordion-body">
                        <div class="row"><div class="col-12 col-md-6"><div class="form-floating"><input name="pla_config_storage_limitemb" type="number" min="0" value="<?= $valorLimite($parametroStorage, 'limiteMb') ?>" class="form-control input-phsa-focus input-phsa-hover" <?= $controlePlano ?>><label>Limite de armazenamento (MB)</label></div></div></div>
                        <div class="form-text">Informe 0 para armazenamento ilimitado.</div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php if ($podeEditarPlano): ?><div class="d-flex justify-content-end"><button type="submit" class="bto bto-phsa"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar configuração</button></div><?php endif; ?>
              </form>
            </div>
          </div>
        </div>

        <div class="phsaTabItem" id="historicoPlano">
          <?php
            $historicoRegistro = $plano;
            $historicoPrefixo = 'pla_';
            $historicoId = (int) ($plano->pla_id ?? 0);
            $historicoStatus = (string) ($plano->pla_status ?? '');
            $historicoStatusClass = (string) ($plano->pla_statusclass ?? 'secondary');
            $historicoLogs = $logsPlano;
            include __DIR__ . '/../historicoPadrao.php';
          ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!$podeEditarPlano): ?><div class="alert alert-info mt-3 mb-0">Você possui somente permissão de visualização para este plano.</div><?php endif; ?>

  <?php $this->push('script'); ?>
    <script>
      $(function () {
        let contatosComerciais = <?= json_encode($contatosComerciais, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        let beneficiosPlano = <?= json_encode($beneficiosPlano, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const podeEditarPlano = <?= $podeEditarPlano ? 'true' : 'false' ?>;

        function escaparHtml(valor) {
          return $('<div>').text(String(valor ?? '')).html();
        }

        function sincronizarContatosComerciais() {
          $('#pla_contatocomercial').val(JSON.stringify(contatosComerciais));
          const tbody = $('#tabelaContatosComerciais tbody');

          if (!contatosComerciais.length) {
            tbody.html('<tr><td colspan="4" class="text-muted text-center">Nenhum contato comercial cadastrado.</td></tr>');
            return;
          }

          tbody.html(contatosComerciais.map(function (contato, indice) {
            return `<tr>
              <td class="fw-semibold">${escaparHtml(contato.nome)}</td>
              <td>${escaparHtml(contato.celular)}</td>
              <td>${escaparHtml(contato.email)}</td>
              <?php if ($podeEditarPlano): ?><td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btnRemoverContatoComercial" data-indice="${indice}" title="Remover contato"><i class="fa-solid fa-trash"></i></button></td><?php endif; ?>
            </tr>`;
          }).join(''));
        }

        function sincronizarBeneficiosPlano() {
          $('#pla_beneficios').val(JSON.stringify(beneficiosPlano));
          const tbody = $('#tabelaBeneficiosPlano tbody');

          if (!beneficiosPlano.length) {
            tbody.html('<tr><td colspan="3" class="text-muted text-center">Nenhum benefício cadastrado.</td></tr>');
            return;
          }

          tbody.html(beneficiosPlano.map(function (beneficio, indice) {
            return `<tr>
              <td class="text-muted">${indice + 1}</td>
              <td>${escaparHtml(beneficio)}</td>
              <?php if ($podeEditarPlano): ?><td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btnRemoverBeneficioPlano" data-indice="${indice}" title="Remover benefício"><i class="fa-solid fa-trash"></i></button></td><?php endif; ?>
            </tr>`;
          }).join(''));
        }

        function atualizarStatusParametro(campoStatus) {
          const alvo = $(campoStatus).data('alvo');
          $(alvo).find('.campoParametroPlano').prop('disabled', !$(campoStatus).is(':checked'));
        }

        $('#formPlanoConfiguracao .statusParametroPlano').on('change', function () {
          atualizarStatusParametro(this);
        }).each(function () {
          atualizarStatusParametro(this);
        });

        function atualizarCamposPorTipo() {
          const tipo = $('#pla_tipo').val();
          const ehTeste = tipo === 'Teste';
          const ehComercial = tipo === 'Comercial';
          const ehDedicado = tipo === 'Dedicado';

          $('#blocoContatosComerciais').toggle(ehDedicado);
          if (!podeEditarPlano) return;

          $('[data-regra-tipo="Teste"]').each(function () {
            $(this).prop('disabled', !ehTeste).prop('readonly', !ehTeste);
            if (!ehTeste) $(this).val('');
          });

          $('[data-regra-tipo="Comercial"]').each(function () {
            $(this).prop('disabled', !ehComercial).prop('readonly', !ehComercial);
            if (!ehComercial) $(this).val('');
          });

        }

        $('#pla_tipo').on('change', atualizarCamposPorTipo);
        atualizarCamposPorTipo();
        sincronizarContatosComerciais();
        sincronizarBeneficiosPlano();

        $('#btnAdicionarBeneficioPlano').on('click', function () {
          const modalId = SimpleModal.mostrar({
            titulo: '<i class="fa-solid fa-list-check"></i> Adicionar benefício',
            tamanho: 'modal-md',
            corpo: `
              <form id="formBeneficioPlano" class="needs-validation" novalidate>
                <div class="form-floating"><input name="beneficio" class="form-control" maxlength="80" required><label>Benefício</label><div class="invalid-feedback">Informe o benefício.</div></div>
                <div class="form-text">Você pode cadastrar até 30 benefícios.</div>
              </form>
            `,
            rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-times"></i>&nbsp;Cancelar</button><button type="submit" form="formBeneficioPlano" class="bto bto-phsa"><i class="fa-solid fa-plus"></i>&nbsp;Adicionar</button>'
          });

          $('#' + modalId).on('submit', '#formBeneficioPlano', function (event) {
            event.preventDefault();
            if (!this.checkValidity()) {
              $(this).addClass('was-validated');
              return;
            }

            if (beneficiosPlano.length >= 30) {
              swWarning('O plano pode ter no máximo 30 benefícios.', {title: 'Limite atingido'});
              return;
            }

            beneficiosPlano.push($(this).find('[name="beneficio"]').val().trim());
            sincronizarBeneficiosPlano();
            SimpleModal.fechar(modalId);
          });
        });

        $('#tabelaBeneficiosPlano').on('click', '.btnRemoverBeneficioPlano', function () {
          if (beneficiosPlano.length <= 3) {
            swWarning('O plano precisa manter pelo menos 3 benefícios.', {title: 'Mínimo de benefícios'});
            return;
          }

          beneficiosPlano.splice(parseInt($(this).data('indice'), 10), 1);
          sincronizarBeneficiosPlano();
        });

        $('#btnAdicionarContatoComercial').on('click', function () {
          const modalId = SimpleModal.mostrar({
            titulo: '<i class="fa-solid fa-address-book"></i> Adicionar contato comercial',
            tamanho: 'modal-md',
            corpo: `
              <form id="formContatoComercial" class="needs-validation" novalidate>
                <div class="form-floating mb-3"><input name="nome" class="form-control" maxlength="120" required><label>Nome</label><div class="invalid-feedback">Informe o nome.</div></div>
                <div class="form-floating mb-3"><input name="celular" class="form-control" maxlength="30" required><label>Celular</label><div class="invalid-feedback">Informe o celular.</div></div>
                <div class="form-floating"><input name="email" type="email" class="form-control" maxlength="160" required><label>E-mail</label><div class="invalid-feedback">Informe um e-mail válido.</div></div>
              </form>
            `,
            rodape: '<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-times"></i>&nbsp;Cancelar</button><button type="submit" form="formContatoComercial" class="bto bto-phsa"><i class="fa-solid fa-plus"></i>&nbsp;Adicionar</button>'
          });

          $('#' + modalId).on('submit', '#formContatoComercial', function (event) {
            event.preventDefault();
            if (!this.checkValidity()) {
              $(this).addClass('was-validated');
              return;
            }

            const form = $(this);
            contatosComerciais.push({
              nome: form.find('[name="nome"]').val().trim(),
              celular: form.find('[name="celular"]').val().trim(),
              email: form.find('[name="email"]').val().trim()
            });
            sincronizarContatosComerciais();
            SimpleModal.fechar(modalId);
          });
        });

        $('#tabelaContatosComerciais').on('click', '.btnRemoverContatoComercial', function () {
          contatosComerciais.splice(parseInt($(this).data('indice'), 10), 1);
          sincronizarContatosComerciais();
        });

        function salvarPlano(form) {
          if (typeof tinymce !== 'undefined') tinymce.triggerSave();

          if (!form[0].checkValidity()) {
            form.addClass('was-validated');
            return;
          }

          let camposParametroDesabilitados = $();
          if (form.attr('id') === 'formPlanoConfiguracao') {
            camposParametroDesabilitados = form.find('.campoParametroPlano:disabled');
            camposParametroDesabilitados.prop('disabled', false);
          }

          const dados = form.serializeArray();
          camposParametroDesabilitados.prop('disabled', true);

          swAguarde({title: 'Salvando plano...', text: 'Aguarde enquanto processamos os dados.', timer: null});
          $.ajax({
            url: '<?= Helpers::url('projeto/salvarPlano') ?>',
            type: 'POST',
            dataType: 'json',
            data: dados,
            success: function (response) { swResponse(response); },
            error: function () { swError('Não foi possível salvar o plano.'); }
          });
        }

        if (podeEditarPlano) {
          $('#formPlanoComercial').on('submit', function (event) {
            event.preventDefault();
            salvarPlano($(this));
          });

          $('#formPlanoConfiguracao').on('submit', function (event) {
            event.preventDefault();
            salvarPlano($(this));
          });

          $('#formPlanoDetalhes').on('submit', function (event) {
            event.preventDefault();
            salvarPlano($(this));
          });
        }

        if (typeof tinymce !== 'undefined' && $('#pla_descricao').length) {
          tinymce.init({
            selector: '#pla_descricao',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link',
            menubar: false,
            branding: false,
            height: 320,
            readonly: <?= $podeEditarPlano ? 'false' : 'true' ?>
          });
        }
      });
    </script>
    <?php $this->end(); ?>
<?php endif; ?>
