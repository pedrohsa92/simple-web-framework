<?php
  $logsUsuario = [];

  if (!empty($usuario->usu_log)) {
    $logsUsuario = json_decode($usuario->usu_log);

  if (!is_array($logsUsuario)) {
      $logsUsuario = [];
    }
  }

  $statusMotivoHistoricoUsuario = [];
  if (!empty($usuario->usu_status_motivo)) {
    $statusMotivoHistoricoUsuario = json_decode($usuario->usu_status_motivo, true);
    if (!is_array($statusMotivoHistoricoUsuario)) {
      $statusMotivoHistoricoUsuario = ['motivo' => $usuario->usu_status_motivo];
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
              <i class="fa-solid fa-id-card"></i>&nbsp;Identificação
            </h6>

            <div class="row">
              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">ID</div>
                  <div class="phsaFakeInputValue"><?= $usuario->usu_id ?></div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Chave</div>
                  <div onclick="fgbCopiarParaAreaDeTransferencia('<?= $usuario->usu_chave ?>', 'chave')" class="phsaFakeInputValue d-flex align-items-center justify-content-between gap-2 text-phsa">
                    <span class="text-break"><?= $usuario->usu_chave ?></span>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Status</div>
                  <div class="phsaFakeInputValue">
                    <span class="badge bg-<?= htmlspecialchars($usuario->usu_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                      <?= $usuario->usu_status ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <?php if (($usuario->usu_status ?? '') === 'Ativo' && !empty($statusMotivoHistoricoUsuario['motivo'])): ?>
              <div class="alert alert-light border mb-0 small">
                <strong>Último motivo de status:</strong>
                <?= htmlspecialchars($statusMotivoHistoricoUsuario['motivo'], ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($statusMotivoHistoricoUsuario['data'])): ?>
                  <span class="text-muted">· <?= htmlspecialchars($statusMotivoHistoricoUsuario['data'], ENT_QUOTES, 'UTF-8') ?></span>
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
              <i class="fa-solid fa-shield-halved"></i>&nbsp;Sessão e segurança
            </h6>

            <div class="row">
              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Online</div>
                  <div class="phsaFakeInputValue">
                    <?= ((int) $usuario->usu_online === 1) ? 'Sim' : 'Não' ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Última atividade</div>
                  <div class="phsaFakeInputValue">
                    <?= empty($usuario->usu_dtonline) ? '-' : date('d/m/Y H:i', strtotime($usuario->usu_dtonline)) ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Último login</div>
                  <div class="phsaFakeInputValue">
                    <?= empty($usuario->usu_dtlogin) ? '-' : date('d/m/Y H:i', strtotime($usuario->usu_dtlogin)) ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-3 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">IP remoto</div>
                  <div class="phsaFakeInputValue text-break">
                    <?= empty($usuario->usu_remote_addr) ? '-' : $usuario->usu_remote_addr ?>
                  </div>
                </div>
              </div>

              <div class="col-12 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">User agent</div>
                  <div class="phsaFakeInputValue text-break">
                    <?= empty($usuario->usu_http_user_agent) ? '-' : $usuario->usu_http_user_agent ?>
                  </div>
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
                <?= count($logsUsuario) ?> alteração<?= count($logsUsuario) == 1 ? '' : 'es' ?>
              </span>
            </div>

            <div class="row">
              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Cadastrado em</div>
                  <div class="phsaFakeInputValue w-100 text-center">
                    <?= empty($usuario->usu_dtcad) ? '-' : date('d/m/Y H:i', strtotime($usuario->usu_dtcad)) ?>
                  </div>
                </div>
              </div>

              <div class="col-sm-6 mb-3">
                <div class="phsaFakeInput">
                  <div class="phsaFakeInputLabel">Última alteração</div>
                  <div class="phsaFakeInputValue w-100 text-center">
                    <?= empty($usuario->usu_dtalter) ? '-' : date('d/m/Y H:i', strtotime($usuario->usu_dtalter)) ?>
                  </div>
                </div>
              </div>
            </div>

            <?php if (empty($logsUsuario)): ?>
              <div class="alert alert-light border mb-0 small">
                Nenhum histórico registrado para este usuário.
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
                    <?php foreach ($logsUsuario as $log): ?>
                      <tr>
                        <td>
                          <?php
                            $nomeLog = $log->usu_razaosocial ?? '-';
                            $nomeLogLimitado = mb_strlen($nomeLog) > 20 ? mb_substr($nomeLog, 0, 20) . '...' : $nomeLog;
                            $logUsuId = (int) ($log->usu_id ?? 0);
                          ?>

                          <?php if ($logUsuId > 0 && isset($this) && method_exists($this, 'verificarPermissao') && $this->verificarPermissao('usuario.visualizar')): ?>
                            <a
                              href="<?= Helpers::url('usuario/form/' . $logUsuId) ?>"
                              target="_blank"
                              class="text-phsa text-decoration-none"
                              title="<?= $nomeLog ?>"
                            >
                              <?= $nomeLogLimitado ?>
                            </a>
                          <?php elseif ($logUsuId > 0 && isset($usuarioLogado->permissoes->{'usuario.visualizar'})): ?>
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
