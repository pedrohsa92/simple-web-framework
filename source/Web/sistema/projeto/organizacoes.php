<?php $organizacoesData = ($organizacoes && $organizacoes->status) ? ($organizacoes->data ?? []) : []; ?>

<section class="p-3">
  <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
    <div>
      <h5 class="text-phsa mb-1">Organizações</h5>
      <div class="small text-muted">Visão consolidada das organizações, planos vigentes e igrejas vinculadas.</div>
    </div>

    <div class="text-end">
      <div class="small text-muted">Total</div>
      <strong id="totalOrganizacoesVisiveis"><?= count($organizacoesData) ?></strong>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12 mb-3">
      <div class="form-floating">
        <input id="pesquisaOrganizacaoProjeto" class="form-control input-phsa-focus input-phsa-hover" placeholder="Pesquisar">
        <label for="pesquisaOrganizacaoProjeto">Pesquisar por organização, documento ou plano</label>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle" id="tabelaOrganizacoesProjeto">
      <thead>
        <tr>
          <th>Organização</th>
          <th>Documento</th>
          <th>Status</th>
          <th>Plano vigente</th>
          <th>Valor / situação</th>
          <th>Cadastro</th>
          <th>Total de igrejas</th>
          <th>Empresas</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($organizacoesData as $organizacao): ?>
          <?php
            $empresasBusca = [];
            foreach (($organizacao->empresas ?? []) as $empresa) {
              $empresasBusca[] = ($empresa->emp_razaosocial ?? '') . ' ' . ($empresa->emp_cnpjcpf ?? '');
            }

            $busca = mb_strtolower(implode(' ', [
              $organizacao->org_razaosocial ?? '',
              $organizacao->org_nomefantasia ?? '',
              $organizacao->org_cnpjcpf ?? '',
              $organizacao->pla_nome ?? '',
              implode(' ', $empresasBusca)
            ]));
          ?>
          <tr class="linhaOrganizacaoProjeto" data-busca="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>">
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle overflow-hidden border bg-light flex-shrink-0" style="width: 42px; height: 42px;">
                  <img src="<?= Helpers::storage($organizacao->org_logo, 'storage/global/semperfil.png') ?>" alt="Organização" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div>
                  <div class="fw-semibold"><?= htmlspecialchars($organizacao->org_razaosocial ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($organizacao->org_nomefantasia ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </div>
            </td>
            <td><?= Helpers::adicionaMascaraCnpjCPf($organizacao->org_cnpjcpf ?? '') ?: '-' ?></td>
            <td><span class="badge bg-<?= htmlspecialchars($organizacao->org_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($organizacao->org_status ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($organizacao->pla_nome ?? 'Sem plano vigente', ENT_QUOTES, 'UTF-8') ?></div>
              <?php if (!empty($organizacao->plc_ciclo)): ?><div class="small text-muted"><?= htmlspecialchars($organizacao->plc_ciclo, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($organizacao->plc_ambiente ?? '-', ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            </td>
            <td>
              <div><?= $organizacao->plc_id ? 'R$ ' . number_format((float) $organizacao->plc_valor, 2, ',', '.') : '-' ?></div>
              <?php if ($organizacao->plc_id): ?>
                <span class="badge bg-<?= htmlspecialchars($organizacao->plc_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($organizacao->plc_status ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge bg-secondary"><?= htmlspecialchars($organizacao->plc_statusfinanceiro ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
              <?php endif; ?>
            </td>
            <td><?= empty($organizacao->org_dtcad) ? '-' : date('d/m/Y', strtotime($organizacao->org_dtcad)) ?></td>
            <td>
              <div class="fw-semibold"><?= (int) ($organizacao->total_empresas ?? 0) ?></div>
              <div class="small text-secondary">igreja<?= (int) ($organizacao->total_empresas ?? 0) === 1 ? '' : 's' ?></div>
            </td>
            <td>
              <?php if (!empty($organizacao->empresas)): ?>
                <div class="small text-secondary" style="min-width: 280px; max-width: 390px;">
                  <?php foreach ($organizacao->empresas as $empresa): ?>
                    <div class="mb-2">
                      <div>• <?= htmlspecialchars($empresa->emp_razaosocial ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                      <div class="ps-3"><?= Helpers::adicionaMascaraCnpjCPf($empresa->emp_cnpjcpf ?? '') ?: '-' ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="small text-secondary">Nenhuma igreja cadastrada.</div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($organizacoesData)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma organização encontrada.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php $this->push('script'); ?>
<script>
  $(function () {
    $('#pesquisaOrganizacaoProjeto').on('input', function () {
      const termo = String($(this).val() || '').toLowerCase().trim();
      let total = 0;

      $('.linhaOrganizacaoProjeto').each(function () {
        const encontrado = termo === '' || String($(this).data('busca') || '').includes(termo);
        $(this).toggle(encontrado);
        if (encontrado) total++;
      });

      $('#totalOrganizacoesVisiveis').text(total);
    });
  });
</script>
<?php $this->end(); ?>
