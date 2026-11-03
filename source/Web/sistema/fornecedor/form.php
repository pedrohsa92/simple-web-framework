<?php
  $fornecedor = $fornecedor ?? null;
  $isUpdate = $fornecedor !== null && (int) ($fornecedor->for_id ?? 0) > 0;
  $valor = static function (string $campo, string $padrao = '') use ($fornecedor): string {
    return htmlspecialchars((string) ($fornecedor->{$campo} ?? $padrao), ENT_QUOTES, 'UTF-8');
  };
  $sexoData = ($getSexo && $getSexo->status) ? ($getSexo->data ?? []) : [];
  $estadoCivilData = ($getEstadoCivil && $getEstadoCivil->status) ? ($getEstadoCivil->data ?? []) : [];
  $logsFornecedor = json_decode((string) ($fornecedor->for_log ?? '[]'), true);
  if (!is_array($logsFornecedor)) $logsFornecedor = [];
  $documentoFornecedor = Helpers::somenteNumeros((string) ($fornecedor->for_cnpjcpf ?? ''));
  $documentoFornecedorMascara = Helpers::adicionaMascaraCnpjCPf($documentoFornecedor);
?>

<?php $this->layout('sistema::sistema'); ?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <h4 class="m-0"><?= $isUpdate ? $valor('for_razaosocial', 'Fornecedor') : 'Novo fornecedor' ?></h4>
        <?php if ($isUpdate): ?>
          <div
            onclick="fgbCopiarParaAreaDeTransferencia(<?= htmlspecialchars(json_encode($documentoFornecedor, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>, 'documento')"
            class="text-phsa small mt-1 d-flex align-items-center gap-2"
            title="Clique para copiar"
            style="cursor: pointer;"
          >
            <span><?= htmlspecialchars($documentoFornecedorMascara ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
            <i class="fa-regular fa-copy"></i>
          </div>
        <?php else: ?>
          <div class="text-muted small mt-1">Cadastre um fornecedor para utilizar no módulo financeiro.</div>
        <?php endif; ?>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if ($isUpdate): ?>
          <button
            type="button"
            class="badge bg-<?= htmlspecialchars($fornecedor->for_statusclass ?? 'secondary', ENT_QUOTES, 'UTF-8') ?> border-0"
            onclick="abrirModalStatusFornecedorHistorico(<?= (int) $fornecedor->for_id ?>, <?= htmlspecialchars(json_encode((string) ($fornecedor->for_status ?? 'Ativo'), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode((string) ($fornecedor->for_razaosocial ?? ''), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>)"
            title="Alterar status"
          >
            <?= htmlspecialchars((string) ($fornecedor->for_status ?? 'Ativo'), ENT_QUOTES, 'UTF-8') ?>
          </button>
        <?php endif; ?>
        <a href="<?= Helpers::url('fornecedor/list') ?>" class="bto bto-phsa-light"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a>
      </div>
    </div>

    <header class="phsaScrollHeader">
      <nav class="phsaScrollNav" data-menu="tabFornecedor">
        <ul class="phsaScrollList">
          <li class="phsaScrollItem active" data-target="cadastroFornecedor"><i class="fa-solid fa-id-card"></i>&nbsp;Cadastro</li>
          <?php if ($isUpdate): ?>
            <li class="phsaScrollItem" data-target="acessoFornecedor"><i class="fa-solid fa-user-shield"></i>&nbsp;Acesso</li>
            <li class="phsaScrollItem" data-target="configuracoesFornecedor"><i class="fa-solid fa-sliders"></i>&nbsp;Configurações</li>
            <li class="phsaScrollItem" data-target="historicoFornecedor"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li>
          <?php endif; ?>
        </ul>
      </nav>
    </header>

    <div class="phsaTabContent phsaTabSubContent">
      <div class="phsaTabItem active" id="cadastroFornecedor">
        <header class="phsaScrollHeader mb-3">
          <nav class="phsaScrollNav" data-menu="tabCadastroFornecedor">
            <ul class="phsaScrollList">
              <li class="phsaScrollItem active" data-target="dadosBasicosFornecedor"><i class="fa-solid fa-id-card"></i>&nbsp;Dados básicos</li>
              <?php if ($isUpdate): ?>
                <li class="phsaScrollItem" data-target="enderecoFornecedor"><i class="fa-solid fa-map-marker-alt"></i>&nbsp;Endereço</li>
                <li class="phsaScrollItem" data-target="contatoFornecedor"><i class="fa-solid fa-phone"></i>&nbsp;Contato</li>
                <li class="phsaScrollItem" data-target="arquivoFornecedor"><i class="fa-solid fa-file-circle-check"></i>&nbsp;Arquivo</li>
              <?php endif; ?>
            </ul>
          </nav>
        </header>

        <div class="phsaTabContent">
          <div class="phsaTabItem active" id="dadosBasicosFornecedor">
            <form id="formFornecedor" class="needs-validation" novalidate>
              <input type="hidden" name="for_id" value="<?= $isUpdate ? (int) $fornecedor->for_id : 0 ?>">
              <div class="row">
                <div class="col-12 col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover" id="for_cnpjcpf" name="for_cnpjcpf" value="<?= $valor('for_cnpjcpf') ?>" required minlength="11" maxlength="18"><label for="for_cnpjcpf">CPF/CNPJ *</label><div class="invalid-feedback">Informe um CPF ou CNPJ válido.</div></div></div>
                <div class="col-12 col-md-8 mb-3"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover" id="for_razaosocial" name="for_razaosocial" value="<?= $valor('for_razaosocial') ?>" required maxlength="255"><label for="for_razaosocial">Nome/Razão social *</label><div class="invalid-feedback">Informe o nome ou razão social.</div></div></div>
                <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover" id="for_nomefantasia" name="for_nomefantasia" value="<?= $valor('for_nomefantasia') ?>" maxlength="255"><label for="for_nomefantasia">Nome fantasia</label></div></div>
                <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input type="date" class="form-control input-phsa-focus input-phsa-hover" id="for_dtnasc" name="for_dtnasc" value="<?= $valor('for_dtnasc') ?>"><label for="for_dtnasc">Data</label></div></div>
                <div class="col-12 campo-pessoa-juridica-area">
                  <div class="row">
                    <div class="col-12 text-phsa"><h5>Dados de pessoa jurídica</h5></div>
                    <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica" id="for_im" name="for_im" value="<?= $valor('for_im') ?>" maxlength="50"><label for="for_im">Inscrição municipal</label></div></div>
                    <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-juridica" id="for_ie" name="for_ie" value="<?= $valor('for_ie') ?>" maxlength="50"><label for="for_ie">Inscrição estadual</label></div></div>
                  </div>
                </div>
                <div class="col-12 campo-pessoa-fisica-area">
                  <div class="row">
                    <div class="col-12 text-phsa"><h5>Dados de pessoa física</h5></div>
                    <div class="col-12 col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover campo-pessoa-fisica" id="for_rg" name="for_rg" value="<?= $valor('for_rg') ?>" maxlength="30"><label for="for_rg">RG</label></div></div>
                    <div class="col-12 col-md-4 mb-3"><div class="form-floating"><select class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica" id="for_idsexo" name="for_idsexo"><option value="">Não informado</option><?php foreach ($sexoData as $item): ?><option value="<?= (int) ($item->id ?? 0) ?>" <?= (int) ($fornecedor->for_idsexo ?? 0) === (int) ($item->id ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($item->sex_nome ?? '', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><label for="for_idsexo">Sexo</label></div></div>
                    <div class="col-12 col-md-4 mb-3"><div class="form-floating"><select class="form-select input-phsa-focus input-phsa-hover campo-pessoa-fisica" id="for_idestadocivil" name="for_idestadocivil"><option value="">Não informado</option><?php foreach ($estadoCivilData as $item): ?><option value="<?= (int) ($item->id ?? 0) ?>" <?= (int) ($fornecedor->for_idestadocivil ?? 0) === (int) ($item->id ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($item->ecv_nome ?? '', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><label for="for_idestadocivil">Estado civil</label></div></div>
                  </div>
                </div>
                <?php if (!$isUpdate): ?>
                  <div class="col-12 text-phsa"><h5>Contato e acesso inicial</h5></div>
                  <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control input-phsa-focus input-phsa-hover" id="for_celular" name="for_celular" value="<?= $valor('for_celular') ?>" maxlength="15"><label for="for_celular">Celular</label></div></div>
                  <div class="col-12 col-md-6 mb-3"><div class="form-floating"><input type="email" class="form-control input-phsa-focus input-phsa-hover" id="for_email" name="for_email" value="<?= $valor('for_email') ?>" maxlength="100" required><label for="for_email">E-mail</label><div class="invalid-feedback">Informe um e-mail válido.</div></div></div>
                <?php endif; ?>
                <div class="col-12 mb-3"><div class="form-floating"><textarea class="form-control input-phsa-focus input-phsa-hover" id="for_observacao" name="for_observacao" style="height: 130px;"><?= $valor('for_observacao') ?></textarea><label for="for_observacao">Observação</label></div></div>
              </div>
              <div class="d-flex justify-content-end"><button type="submit" class="bto bto-phsa"><i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar fornecedor</button></div>
            </form>
          </div>

          <?php if ($isUpdate): ?>
            <div class="phsaTabItem" id="enderecoFornecedor"><?php $enderecoConfig = $enderecoConfig ?? null; include 'source/Web/sistema/endereco/form.php'; ?></div>
            <div class="phsaTabItem" id="contatoFornecedor"><?php $contatoConfig = $contatoConfig ?? null; include 'source/Web/sistema/contato/form.php'; ?></div>
            <div class="phsaTabItem" id="arquivoFornecedor"><?php $arquivoConfig = $arquivoConfig ?? null; include 'source/Web/sistema/arquivo/arquivo.php'; ?></div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($isUpdate): ?>
        <div class="phsaTabItem" id="acessoFornecedor">
          <?php include 'source/Web/sistema/fornecedor/acesso.php'; ?>
        </div>
        <div class="phsaTabItem" id="configuracoesFornecedor"><div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>As configurações específicas do fornecedor serão adicionadas posteriormente.</div></div>
        <div class="phsaTabItem" id="historicoFornecedor">
          <?php include 'source/Web/sistema/fornecedor/historico.php'; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php $this->push('script'); ?>
  <script>
    $(document).ready(function () {
      function limparDesabilitarPessoaFisica() {
        $('.campo-pessoa-fisica').each(function () {
          $(this).val('').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Física/CPF');
        });
        $('.campo-pessoa-fisica-area').hide();
      }

      function limparDesabilitarPessoaJuridica() {
        $('.campo-pessoa-juridica').each(function () {
          $(this).val('').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Jurídica/CNPJ');
        });
        $('.campo-pessoa-juridica-area').hide();
      }

      function habilitarPessoaFisica() {
        $('.campo-pessoa-fisica-area').show();
        $('.campo-pessoa-fisica').removeAttr('disabled').removeAttr('title');
      }

      function habilitarPessoaJuridica() {
        $('.campo-pessoa-juridica-area').show();
        $('.campo-pessoa-juridica').removeAttr('disabled').removeAttr('title');
      }

      function ajustarDocumentoFornecedor(limparCampos = true, aplicarMascara = true) {
        const documento = String($('#for_cnpjcpf').val() || '').replace(/\D/g, '');
        $('#for_cnpjcpf').unmask();

        if (documento.length === 11) {
          if (aplicarMascara && typeof $('#for_cnpjcpf').mask === 'function') {
            $('#for_cnpjcpf').mask('999.999.999-99');
          }

          $('label[for="for_cnpjcpf"]').text('CPF');
          $('label[for="for_razaosocial"]').text('Nome completo');
          $('label[for="for_nomefantasia"]').text('Nome social');
          $('label[for="for_dtnasc"]').text('Data de nascimento');
          habilitarPessoaFisica();

          if (limparCampos) {
            limparDesabilitarPessoaJuridica();
          } else {
            $('.campo-pessoa-juridica-area').hide();
            $('.campo-pessoa-juridica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Jurídica/CNPJ');
          }
          return;
        }

        if (documento.length === 14) {
          if (aplicarMascara && typeof $('#for_cnpjcpf').mask === 'function') {
            $('#for_cnpjcpf').mask('99.999.999/9999-99');
          }

          $('label[for="for_cnpjcpf"]').text('CNPJ');
          $('label[for="for_razaosocial"]').text('Razão social');
          $('label[for="for_nomefantasia"]').text('Nome fantasia');
          $('label[for="for_dtnasc"]').text('Data de fundação');
          habilitarPessoaJuridica();

          if (limparCampos) {
            limparDesabilitarPessoaFisica();
          } else {
            $('.campo-pessoa-fisica-area').hide();
            $('.campo-pessoa-fisica').attr('disabled', true).attr('title', 'Disponível apenas para Pessoa Física/CPF');
          }
          return;
        }

        $('label[for="for_cnpjcpf"]').text('Documento');
        $('label[for="for_razaosocial"]').text('Nome / Razão social');
        $('label[for="for_nomefantasia"]').text('Nome fantasia');
        $('label[for="for_dtnasc"]').text('Data');

        if (limparCampos) {
          limparDesabilitarPessoaFisica();
          limparDesabilitarPessoaJuridica();
        } else {
          $('.campo-pessoa-fisica-area, .campo-pessoa-juridica-area').hide();
          $('.campo-pessoa-fisica, .campo-pessoa-juridica').attr('disabled', true);
        }
      }

      ajustarDocumentoFornecedor(false, true);

      $('#for_cnpjcpf').on('focus keypress', function () {
        $(this).unmask();
      });

      $('#for_cnpjcpf').on('input', function () {
        ajustarDocumentoFornecedor(true, false);
      });

      $('#for_cnpjcpf').on('blur change', function () {
        ajustarDocumentoFornecedor(true, true);
      });

      <?php if (!$isUpdate): ?>
        $('#for_cnpjcpf').focus();
      <?php endif; ?>
    });

    $(document).off('submit.fornecedor').on('submit.fornecedor', '#formFornecedor', function (event) {
      event.preventDefault();
      const form = $(this);
      if (!form[0].checkValidity()) {
        form.addClass('was-validated');
        return;
      }

      const botao = form.find('button[type="submit"]');
      botao.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Salvando');

      $.ajax({
        url: '<?= Helpers::url('fornecedor/salvarFornecedor') ?>',
        method: 'POST',
        dataType: 'json',
        data: form.serialize(),
        success: function (response) {
          swResponse(response, { timer: null });
          if (response.status && response.location) {
            setTimeout(function () {
              window.location.replace(response.location);
            }, 2000);
            return;
          }

          botao.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar fornecedor');
        },
        error: function () {
          botao.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar fornecedor');
          swError('Não foi possível salvar o fornecedor.');
        }
      });
    });

  </script>
<?php $this->end(); ?>
