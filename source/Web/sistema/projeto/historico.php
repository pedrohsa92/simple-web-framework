<?php
  $logsProjeto = json_decode($projeto->prj_log ?? '[]');
  if (!is_array($logsProjeto)) $logsProjeto = [];

  $valor = static function ($valor, string $padrao = '-'): string {
    $valor = trim((string) $valor);
    return htmlspecialchars($valor === '' ? $padrao : $valor, ENT_QUOTES, 'UTF-8');
  };

  $dataHora = static function ($valor): string {
    return empty($valor) ? '-' : date('d/m/Y H:i', strtotime($valor));
  };
?>

<section class="p-3">
  <div class="col-12 text-phsa mb-3">
    <h5 class="m-0">Histórico do projeto</h5>
    <div class="small text-muted">Informações técnicas e registro das alterações realizadas no cadastro principal.</div>
  </div>

  <div class="row">
    <div class="col-12 mb-4">
      <div class="card border-phsa-light box-phsa-shadow-light">
        <div class="card-body">
          <h6 class="text-phsa mb-3">Identificação e operação</h6>
          <div class="row">
            <div class="col-12 col-md-3 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">ID</div><div class="phsaFakeInputValue">#<?= (int) ($projeto->prj_id ?? 1) ?></div></div></div>
            <div class="col-12 col-md-3 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Status</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_status) ?></div></div></div>
            <div class="col-12 col-md-3 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Ambiente</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_tipo) ?></div></div></div>
            <div class="col-12 col-md-3 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Idioma</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_lang) ?></div></div></div>
          </div>
          <div class="row">
            <div class="col-12 col-md-6 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Autor / identificação</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_author) ?></div></div></div>
            <div class="col-12 col-md-6 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Robots</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_robots) ?></div></div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-4">
      <div class="card h-100 border-phsa-light box-phsa-shadow-light">
        <div class="card-body">
          <h6 class="text-phsa mb-3">Domínio e central</h6>
          <div class="row">
            <div class="col-12 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Domínio principal</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_dominio) ?></div></div></div>
            <div class="col-12 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">URL central</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_url_central) ?></div></div></div>
            <div class="col-12 col-md-6 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Organização central</div><div class="phsaFakeInputValue">#<?= $valor($projeto->prj_idorganizacao_central, 'Não definida') ?></div></div></div>
            <div class="col-12 col-md-6 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Chave do projeto</div><div class="phsaFakeInputValue"><?= empty($projeto->prj_chave) ? 'Não definida' : 'Configurada' ?></div></div></div>
          </div>
          <div class="small text-muted">Última sincronização: <?= $dataHora($projeto->prj_dtultimasincronizacao) ?></div>
        </div>
      </div>
    </div>

    <div class="col-12 mb-4">
      <div class="card border-phsa-light box-phsa-shadow-light">
        <div class="card-body">
          <h6 class="text-phsa mb-3">Controle do registro</h6>
          <div class="row">
            <div class="col-12 col-md-4 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Cadastrado em</div><div class="phsaFakeInputValue"><?= $dataHora($projeto->prj_dtcad) ?></div></div></div>
            <div class="col-12 col-md-4 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Última alteração</div><div class="phsaFakeInputValue"><?= $dataHora($projeto->prj_dtalter) ?></div></div></div>
            <div class="col-12 col-md-4 mb-3"><div class="phsaFakeInput"><div class="phsaFakeInputLabel">Google Tag</div><div class="phsaFakeInputValue"><?= $valor($projeto->prj_googletag, 'Não configurado') ?></div></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-phsa-light box-phsa-shadow-light">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h6 class="text-phsa mb-0"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Log de alterações</h6><span class="small text-muted"><?= count($logsProjeto) ?> alteração(ões)</span></div>

      <?php if (empty($logsProjeto)): ?>
        <div class="alert alert-light border mb-0">Nenhum histórico registrado para este projeto.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle small mb-0">
            <thead><tr><th>Usuário</th><th>Ação</th><th>Data</th><th>Detalhes</th></tr></thead>
            <tbody>
              <?php foreach ($logsProjeto as $log): ?>
                <tr>
                  <td><?= $valor($log->usu_razaosocial ?? null) ?></td>
                  <td><?= $valor($log->acao ?? null) ?></td>
                  <td><?= $valor($log->data ?? null) ?></td>
                  <td><?= $valor(is_array($log->detalhe ?? null) ? implode(', ', $log->detalhe) : ($log->detalhe ?? null)) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
