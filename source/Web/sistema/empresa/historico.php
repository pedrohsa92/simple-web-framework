





<?php
  $logsEmpresa = [];

  if (!empty($empresa->emp_log)) {
    $logsEmpresa = json_decode($empresa->emp_log);

  if (!is_array($logsEmpresa)) {
      $logsEmpresa = [];
    }
  }

  $statusMotivoHistoricoEmpresa = [];
  if (!empty($empresa->emp_status_motivo)) {
    $statusMotivoHistoricoEmpresa = json_decode($empresa->emp_status_motivo, true);
    if (!is_array($statusMotivoHistoricoEmpresa)) {
      $statusMotivoHistoricoEmpresa = ['motivo' => $empresa->emp_status_motivo];
    }
  }
?>

<div class="phsaTabContent">
  <section>
    <div class="row">
      <div class="col-12 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light">
          <div class="card-body">
            <h6 class="text-phsa mb-0">
              <i class="fa-solid fa-building"></i>&nbsp;Identificação
            </h6>

            <div class="row">
              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">ID</div>
                  <div class="phsaFakeInputValue"><?= $empresa->emp_id ?></div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Chave</div>
                  <div
                    onclick="fgbCopiarParaAreaDeTransferencia('<?= $empresa->emp_chave ?>', 'chave')"
                    class="phsaFakeInputValue d-flex align-items-center justify-content-between gap-2 text-phsa"
                    title="Clique para copiar"
                  >
                    <span class="text-break"><?= $empresa->emp_chave ?></span>
                    <i class="fa-regular fa-copy"></i>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Status</div>
                  <div class="phsaFakeInputValue">
                    <span class="badge bg-<?= htmlspecialchars($empresa->emp_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                      <?= $empresa->emp_status ?>
                    </span>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Tipo</div>
                  <div class="phsaFakeInputValue">
                    <?= $empresa->emp_tipo ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">
                    <?= strlen(Helpers::somenteNumeros($empresa->emp_cnpjcpf)) === 11 ? 'CPF' : 'CNPJ' ?>
                  </div>

                  <div
                    onclick="fgbCopiarParaAreaDeTransferencia('<?= Helpers::somenteNumeros($empresa->emp_cnpjcpf) ?>', 'documento')"
                    class="phsaFakeInputValue d-flex align-items-center justify-content-between gap-2 text-phsa"
                    title="Clique para copiar"
                  >
                    <span><?= Helpers::adicionaMascaraCnpjCPf($empresa->emp_cnpjcpf) ?></span>
                    <i class="fa-regular fa-copy"></i>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel text-phsa">
                    <?= strlen(Helpers::somenteNumeros($empresa->emp_cnpjcpf)) === 11 ? 'Nome completo' : 'Razão social' ?>
                  </div>

                  <div class="phsaFakeInputValue text-break">
                    <?= $empresa->emp_razaosocial ?>
                  </div>
                </div>
              </div>
            </div>

            <?php if (($empresa->emp_status ?? '') === 'Ativo' && !empty($statusMotivoHistoricoEmpresa['motivo'])): ?>
              <div class="alert alert-light border mb-0 small">
                <strong>Último motivo de status:</strong>
                <?= htmlspecialchars($statusMotivoHistoricoEmpresa['motivo'], ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($statusMotivoHistoricoEmpresa['data'])): ?>
                  <span class="text-muted">· <?= htmlspecialchars($statusMotivoHistoricoEmpresa['data'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif ?>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light">
          <div class="card-body">
            <h6 class="text-phsa mb-0">
              <i class="fa-solid fa-sitemap"></i>&nbsp;Organização
            </h6>

            <div class="row">
              <div class="col-sm-4 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">
                    <?= strlen(Helpers::somenteNumeros($empresa->organizacao->org_cnpjcpf)) === 11 ? 'CPF da organização' : 'CNPJ da organização' ?>
                  </div>

                  <div
                    onclick="fgbCopiarParaAreaDeTransferencia('<?= Helpers::somenteNumeros($empresa->organizacao->org_cnpjcpf) ?>', 'documento da organização')"
                    class="phsaFakeInputValue d-flex align-items-center justify-content-between gap-2 text-phsa"
                    title="Clique para copiar"
                  >
                    <span><?= Helpers::adicionaMascaraCnpjCPf($empresa->organizacao->org_cnpjcpf) ?></span>
                    <i class="fa-regular fa-copy"></i>
                  </div>
                </div>
              </div>

              <div class="col-sm-8 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Nome da organização</div>

                  <div class="phsaFakeInputValue text-break">
                    <?php if (isset($usuarioLogado->permissoes->{'organizacao.visualizar'})): ?>
                      <a
                        href="<?= Helpers::url('organizacao') ?>"
                        target="_blank"
                        class="text-phsa text-decoration-none"
                      >
                        <?= $empresa->organizacao->org_nomefantasia ?>
                      </a>
                    <?php else: ?>
                      <?= $empresa->organizacao->org_nomefantasia ?>
                    <?php endif ?>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($empresa->emp_tipo === 'Filial'): ?>
              <div class="row" title="Empresa do tipo: <?= $empresa->matriz->emp_tipo ?>">
                <div class="col-12 text-phsa mb-3">
                  <h6 class="mb-0">
                    <i class="fa-solid fa-building-columns"></i>&nbsp;Matriz
                  </h6>
                </div>

                <div class="col-sm-4 mb-3">
                  <div class="phsaFakeInput">
                    <div class="phsaFakeInputLabel">
                      <?= strlen(Helpers::somenteNumeros($empresa->matriz->emp_cnpjcpf)) === 11 ? 'CPF' : 'CNPJ' ?>
                    </div>

                    <div
                      onclick="fgbCopiarParaAreaDeTransferencia('<?= Helpers::somenteNumeros($empresa->matriz->emp_cnpjcpf) ?>', 'documento da matriz')"
                      class="phsaFakeInputValue d-flex align-items-center justify-content-between gap-2 text-phsa"
                      title="Clique para copiar"
                    >
                      <span><?= Helpers::adicionaMascaraCnpjCPf($empresa->matriz->emp_cnpjcpf) ?></span>
                      <i class="fa-regular fa-copy"></i>
                    </div>
                  </div>
                </div>

                <div class="col-sm-8 mb-3">
                  <div class="phsaFakeInput">
                    <div class="phsaFakeInputLabel text-phsa">
                      <?= strlen(Helpers::somenteNumeros($empresa->matriz->emp_cnpjcpf)) === 11 ? 'Nome completo' : 'Razão social' ?>
                    </div>

                    <div class="phsaFakeInputValue text-break">
                      <?php if (isset($usuarioLogado->permissoes->{'empresa.visualizar'})): ?>
                        <a
                          href="<?= Helpers::url('empresa/form/' . $empresa->matriz->emp_id) ?>"
                          target="_blank"
                          class="text-phsa text-decoration-none"
                        >
                          <?= $empresa->matriz->emp_razaosocial ?>
                        </a>
                      <?php else: ?>
                        <?= $empresa->matriz->emp_razaosocial ?>
                      <?php endif ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 mb-3">
        <div class="card border-phsa-light box-phsa-shadow-light">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
              <h6 class="text-phsa mb-0">
                <i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Log de alterações
              </h6>

              <span class="small text-muted">
                <?= count($logsEmpresa) ?> alteração<?= count($logsEmpresa) == 1 ? '' : 'es' ?>
              </span>
            </div>

            <div class="row">
              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Cadastrado em</div>
                  <div class="phsaFakeInputValue w-100 text-center">
                    <?= empty($empresa->emp_dtcad) ? '-' : date('d/m/Y H:i', strtotime($empresa->emp_dtcad)) ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Última alteração</div>
                  <div class="phsaFakeInputValue w-100 text-center">
                    <?= empty($empresa->emp_dtalter) ? '-' : date('d/m/Y H:i', strtotime($empresa->emp_dtalter)) ?>
                  </div>
                </div>
              </div>
            </div>

            <?php if (empty($logsEmpresa)): ?>
              <div class="alert alert-light border mb-0 small">
                Nenhum histórico registrado para esta empresa.
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                  <thead>
                    <tr>
                      <th>Usuário</th>
                      <th>Ação</th>
                      <th style="width: 150px;">Data</th>
                      <th>Detalhes</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php foreach ($logsEmpresa as $log): ?>
                      <tr>
                        <td>
                          <?php
                            $nomeLog = $log->usu_razaosocial ?? '-';
                            $nomeLogLimitado = mb_strlen($nomeLog) > 20 ? mb_substr($nomeLog, 0, 20) . '...' : $nomeLog;
                            $logUsuId = (int) ($log->usu_id ?? 0);
                          ?>

                          <?php if ($logUsuId > 0 && isset($usuarioLogado->permissoes->{'usuario.visualizar'})): ?>
                            <a
                              href="<?= Helpers::url('usuario/form/' . $logUsuId) ?>"
                              target="_blank"
                              class="text-phsa text-decoration-none"
                              title="<?= $nomeLog ?>"
                            >
                              <?= $nomeLogLimitado ?>
                            </a>
                          <?php else: ?>
                            <span title="<?= $nomeLog ?>"><?= $nomeLogLimitado ?></span>
                          <?php endif ?>
                        </td>

                        <td>
                          <?= $log->acao ?? '-' ?>
                        </td>

                        <td>
                          <?php
                            $dataLog = $log->data ?? '';

                            if (!empty($dataLog)) {
                              $dataLog = str_replace('/', '-', $dataLog);
                              $timestampLog = strtotime($dataLog);
                            } else {
                              $timestampLog = false;
                            }
                          ?>

                          <?= $timestampLog ? date('d/m/Y H:i', $timestampLog) : ($log->data ?? '-') ?>
                        </td>

                        <td>
                          <?php if (!empty($log->detalhe) && is_array($log->detalhe)): ?>
                            <ul class="mb-0 ps-3">
                              <?php foreach ($log->detalhe as $detalhe): ?>
                                <li><?= $detalhe ?></li>
                              <?php endforeach; ?>
                            </ul>
                          <?php else: ?>
                            <span class="text-muted">Sem detalhes</span>
                          <?php endif ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
