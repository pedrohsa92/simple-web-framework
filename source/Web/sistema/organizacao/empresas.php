<section class="p-3">
  <h5 class="text-phsa mb-3">
    <i class="fa-solid fa-building me-1"></i>Lista de empresas
  </h5>

  <?php if (!$getEmpresas->status): ?>
    <div class="alert alert-warning mb-0">
      <?= $getEmpresas->text ?? 'Não foi possível carregar as empresas desta organização.' ?>
    </div>
  <?php elseif (empty($getEmpresas->data)): ?>
    <div class="alert alert-light border mb-0">
      Nenhuma empresa vinculada a esta organização.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Empresa</th>
                      <th>Documento</th>
                      <th>Tipo</th>
                      <th>Status</th>
                      <th class="colSecundaria">Datas</th>
                      <th class="text-end">Ação</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php foreach ($getEmpresas->data as $empresaOrganizacao): ?>
                      <tr>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle overflow-hidden border bg-light flex-shrink-0" style="width: 42px; height: 42px;">
                              <img
                                src="<?= Helpers::storage($empresaOrganizacao->emp_logo, 'storage/global/semperfil.png') ?>"
                                alt="<?= $empresaOrganizacao->emp_razaosocial ?>"
                                style="width: 100%; height: 100%; object-fit: cover;"
                              >
                            </div>

                            <div class="min-w-0">
                              <div class="fw-semibold text-truncate" style="max-width: 300px;">
                                <?php if (isset($usuarioLogado->permissoes->{'empresa.visualizar'})): ?>
                                  <a
                                    href="<?= Helpers::url('empresa/form/' . $empresaOrganizacao->emp_id) ?>"
                                    target="_blank"
                                    class="text-phsa text-decoration-none"
                                    title="Abrir empresa em nova aba"
                                  >
                                    <?= $empresaOrganizacao->emp_razaosocial ?>
                                  </a>
                                <?php else: ?>
                                  <?= $empresaOrganizacao->emp_razaosocial ?>
                                <?php endif ?>
                              </div>

                              <div class="text-muted small text-truncate" style="max-width: 300px;">
                                <?= $empresaOrganizacao->emp_nomefantasia ?: 'Sem nome fantasia' ?>
                              </div>
                            </div>
                          </div>
                        </td>

                        <td>
                          <?php if (!empty($empresaOrganizacao->emp_cnpjcpf)): ?>
                            <button
                              type="button"
                              class="btn p-0 text-phsa"
                              title="Copiar documento"
                              onclick="fgbCopiarParaAreaDeTransferencia('<?= Helpers::somenteNumeros($empresaOrganizacao->emp_cnpjcpf) ?>', 'documento da empresa')"
                            >
                              <?= Helpers::adicionaMascaraCnpjCPf($empresaOrganizacao->emp_cnpjcpf) ?>
                            </button>
                          <?php else: ?>
                            <span class="text-muted">Sem documento</span>
                          <?php endif ?>
                        </td>

                        <td>
                          <span class="badge bg-secondary"><?= $empresaOrganizacao->emp_tipo ?></span>
                        </td>

                        <td>
                          <span class="badge bg-<?= $empresaOrganizacao->emp_statusclass ?>">
                            <?= $empresaOrganizacao->emp_status ?>
                          </span>
                        </td>

                        <td class="colSecundaria">
                          <div class="text-muted small">
                            Cadastro: <?= empty($empresaOrganizacao->emp_dtcad) ? '-' : date('d/m/Y H:i', strtotime($empresaOrganizacao->emp_dtcad)) ?><br>
                            Alterado: <?= empty($empresaOrganizacao->emp_dtalter) ? '-' : date('d/m/Y H:i', strtotime($empresaOrganizacao->emp_dtalter)) ?>
                          </div>
                        </td>

                        <td class="text-end">
                          <?php if (isset($usuarioLogado->permissoes->{'empresa.visualizar'})): ?>
                            <a
                              href="<?= Helpers::url('empresa/form/' . $empresaOrganizacao->emp_id) ?>"
                              target="_blank"
                              class="bto btn-sm bto-phsa-light bto-phsa-hover"
                              title="Abrir empresa em nova aba"
                            >
                              <i class="fa-solid fa-up-right-from-square"></i>
                            </a>
                          <?php else: ?>
                            <span class="text-muted small">Sem permissão</span>
                          <?php endif ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
    </div>
  <?php endif ?>
</section>
