<?php
  $logsOrganizacao = [];

  if (!empty($organizacao->org_log)) {
    $logsOrganizacao = json_decode($organizacao->org_log);

    if (!is_array($logsOrganizacao)) {
      $logsOrganizacao = [];
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
              <i class="fa-solid fa-sitemap"></i>&nbsp;Identificação
            </h6>

            <div class="row">
              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">ID</div>
                  <div class="phsaFakeInputValue"><?= $organizacao->org_id ?></div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Chave</div>
                  <div
                    onclick="fgbCopiarParaAreaDeTransferencia('<?= $organizacao->org_chave ?>', 'chave')"
                    class="phsaFakeInputValue d-flex align-items-center justify-content-between gap-2 text-phsa"
                    title="Clique para copiar"
                  >
                    <span class="text-break"><?= $organizacao->org_chave ?></span>
                    <i class="fa-regular fa-copy"></i>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Status</div>
                  <div class="phsaFakeInputValue">
                    <span class="badge bg-<?= htmlspecialchars($organizacao->org_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                      <?= $organizacao->org_status ?>
                    </span>
                  </div>
                </div>
              </div>

              <div class="col-sm-4 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">
                    <?= strlen(Helpers::somenteNumeros($organizacao->org_cnpjcpf)) === 11 ? 'CPF' : 'CNPJ' ?>
                  </div>

                  <div
                    onclick="fgbCopiarParaAreaDeTransferencia('<?= Helpers::somenteNumeros($organizacao->org_cnpjcpf) ?>', 'documento')"
                    class="phsaFakeInputValue d-flex align-items-center justify-content-between gap-2 text-phsa"
                    title="Clique para copiar"
                  >
                    <span><?= Helpers::adicionaMascaraCnpjCPf($organizacao->org_cnpjcpf) ?></span>
                    <i class="fa-regular fa-copy"></i>
                  </div>
                </div>
              </div>

              <div class="col-sm-8 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel text-phsa">
                    <?= strlen(Helpers::somenteNumeros($organizacao->org_cnpjcpf)) === 11 ? 'Nome completo' : 'Razão social' ?>
                  </div>

                  <div class="phsaFakeInputValue text-break">
                    <?= $organizacao->org_razaosocial ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-4 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Ambiente</div>
                  <div class="phsaFakeInputValue"><?= $organizacao->org_ambiente ?></div>
                </div>
              </div>

              <div class="col-sm-4 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Status do ambiente</div>
                  <div class="phsaFakeInputValue"><?= $organizacao->org_statusambiente ?></div>
                </div>
              </div>

              <div class="col-sm-4 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Empresas vinculadas</div>
                  <div class="phsaFakeInputValue"><?= (int) ($getEmpresas->total ?? 0) ?></div>
                </div>
              </div>
            </div>
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
                <?= count($logsOrganizacao) ?> <?= count($logsOrganizacao) === 1 ? 'alteração' : 'alterações' ?>
              </span>
            </div>

            <div class="row">
              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Cadastrado em</div>
                  <div class="phsaFakeInputValue w-100 text-center">
                    <?= empty($organizacao->org_dtcad) ? '-' : date('d/m/Y H:i', strtotime($organizacao->org_dtcad)) ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Última alteração</div>
                  <div class="phsaFakeInputValue w-100 text-center">
                    <?= empty($organizacao->org_dtalter) ? '-' : date('d/m/Y H:i', strtotime($organizacao->org_dtalter)) ?>
                  </div>
                </div>
              </div>
            </div>

            <?php if (empty($logsOrganizacao)): ?>
              <div class="alert alert-light border mb-0 small">
                Nenhum histórico registrado para esta organização.
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
                    <?php foreach ($logsOrganizacao as $log): ?>
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

                        <td><?= $log->acao ?? '-' ?></td>

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
